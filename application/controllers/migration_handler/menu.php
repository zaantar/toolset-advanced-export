<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;
use ToolsetAdvancedExport\Utils as utils;

/**
 * Migrates menus, menu items and menu locations.
 *
 * On import, changing IDs are handled.
 *
 * @since 1.0
 */
class Menu implements IMigration_Handler {

    /**
     * @inheritdoc
     *
     * @return e\IMigration_Data
     * @since 1.0
     */
    function export() {

        $menus = $this->export_menus();

        $output = [
            'menus' => $menus,
            'locations' => $this->export_menu_locations(),
            'items' => $this->export_menu_items( array_map( function( $menu ) { return $menu['id']; }, $menus ) )
        ];

        return e\Migration_Data_Nested_Array::from_array( $output );
    }


    /**
     * Export existing WordPress menus as arrays of name, id and slug.
     *
     * @return array
     * @since 1.0
     */
    private function export_menus() {
        /** @var \WP_Term[] $menu_terms */
        $menu_terms = wp_get_nav_menus();

        $menus = array_map( function( $menu_term ) {
            /** @var \WP_Term $menu_term */
            return [
                'name' => $menu_term->name,
                'id' => $menu_term->term_id,
                'slug' => $menu_term->slug
            ];
        }, $menu_terms );

        return $menus;
    }


    /**
     * Export menu locations
     * @return array Location -> Menu ID array.
     */
    private function export_menu_locations() {
        return get_nav_menu_locations();
    }


    /**
     * Export menu items for provided set of menu IDs.
     *
     * @param int[] $menu_ids IDs of existing menus.
     * @return array For each menu, there will be one array of its items, with menu ID as the key.
     * @since 1.0
     */
    private function export_menu_items( $menu_ids ) {
        return array_reduce( $menu_ids, function( $result, $menu_id ) {
            $result[ $menu_id ] = $this->export_single_menu_items( $menu_id );
            return $result;
        }, [] );
    }


    /**
     * Export all items of one menu.
     *
     * @param int $menu_id
     * @return array Array of item arrays.
     * @since 1.0
     */
    private function export_single_menu_items( $menu_id ) {

        /** @var \WP_Post[] $menu_item_posts Posts representing menu items, decorated by \wp_setup_nav_menu_item(). */
        $menu_item_posts = array_filter( wp_get_nav_menu_items( $menu_id ), function( $menu_item_post ) {
            // Exclude items with invalid targets.
            return ( !isset( $menu_item_post->_invalid ) || false == $menu_item_post->_invalid );
        } );

        // Turn \WP_Post objects into arrays with needed elements only.
        $menu_items = array_map( function( $item ) use( $menu_id ) {

            $item_export = [
                // ID of the menu item
                'db_id' => (int) $item->db_id,
                'menu_id' => $menu_id,
                'is_public' => ( 'publish' == $item->post_status ),
                'type' => $item->type,
                'position' => $item->menu_order,
                'parent' => $item->menu_item_parent,
                'title' => $item->title,
                'attr_target' => $item->target,
                'attr_title' => $item->attr_title,
                'classes' => $item->classes,
                'xfn' => $item->xfn,
                'description' => $item->post_content,

                // Meaning of these values differs, depending on item type.
                'object_id' => (int) $item->object_id,
                'object' => $item->object,
                'url' => $item->url
            ];

            // Append extra data so that, on import, we can link to the correct target with more certainty.
            if( 'post_type' == $item->type ) {
                $item_export['portable_target_data'] = utils\get_portable_post_data( (int) $item->object_id );
            } elseif( 'taxonomy' == $item->type ) {
            	// For "taxonomy" menu item, the object_id is a term ID and object holds the taxonomy slug.
	            $item_export['portable_target_data'] = utils\get_portable_term_data( (int) $item->object_id, $item->object );
            }

            /**
             * toolset_export_menu_item
             *
             * Modify a single menu item on export.
             *
             * @param array $item_export Associative array with export data
             * @param \WP_Post $item The original menu item coming from \wp_get_nav_menu_items().
             * @param int $menu_id ID of the menu where the item belongs.
             * @return array Associative array with export data.
             * @since 1.0
             */
            $item_export = apply_filters( 'toolset_export_menu_item', $item_export, $item, $menu_id );

            return $item_export;

        }, $menu_item_posts );

        /**
         * toolset_export_menu_items
         *
         * Modify the array of one menu's items on export.
         *
         * @param array $menu_items Array of arrays of item export data.
         * @param int $menu_id ID of the menu where the items belong.
         * @return array $menu_items Array of arrays of item export data.
         * @since 1.0
         */
        $menu_items = apply_filters( 'toolset_export_menu_items', $menu_items, $menu_id );

        return $menu_items;
    }


    /**
     * @inheritdoc
     *
     * @param e\IMigration_Data $data Correct migration data for the section
     *
     * @return \Toolset_Result_Set
     * @throws \InvalidArgumentException
     */
    function import( $data ) {

        $data = $data->to_array();
        $results = new \Toolset_Result_Set();

        // First import menus and save the mapping of old IDs to new ones.
	    //
	    // We need that for the next import phases.
        /** @var array $menu_id_map For each imported menu, there is old id => new ID. */
        list( $menu_import_result, $menu_id_map ) = $this->import_menus(
            toolset_ensarr( toolset_getarr( $data, 'menus' ) )
        );
        $results->add( $menu_import_result );

        // Import individual menu items and connect them to the right menus.
        $results->add(
            $this->import_menu_items( toolset_ensarr( toolset_getarr( $data, 'items' ) ), $menu_id_map )
        );

        // Connect menus to the locations in the theme.
        $results->add(
            $this->import_menu_locations( toolset_ensarr( toolset_getarr( $data, 'locations' ) ), $menu_id_map )
        );

        return $results;
    }


	/**
	 * Import WordPress menus.
	 *
	 * @param array $menus Array with menu import data (generated by self::export_menus()).
	 *
	 * @return array A list with the result (a \Toolset_Result_Set instance) and the mapping from old to new menu IDs.
	 * @since 1.0
	 */
    private function import_menus( $menus ) {

        $results = new \Toolset_Result_Set();

        // Process all menus and collect the ID mapping.
        $menu_id_map = array_reduce( $menus, function( $menu_id_map, $menu_data ) use( $results ) {

            $menu_name = toolset_getarr( $menu_data, 'name' );
            $old_menu_id = toolset_getarr( $menu_data, 'id' );

	        $unique_menu_slug = $this->get_unique_menu_slug( sanitize_title( $menu_name ) );
	        $term_creation_result = wp_insert_term( $menu_name, 'nav_menu', [ 'slug' => $unique_menu_slug ] );

            if( ! $term_creation_result instanceof \WP_Error ) {
	            $menu_id_map[ $old_menu_id ] = $term_creation_result['term_id'];
	            $results->add( true, sprintf( __( 'Created menu "%s".', 'toolset-advanced-export' ), sanitize_text_field( $menu_name ) ) );
            } else {
	            $results->add( $term_creation_result );
            }

            return $menu_id_map;

        }, [] );

        return [ $results, $menu_id_map ];
    }


	/**
	 * Generate an unique slug for a menu.
	 *
	 * @param string $term_slug Candidate value. If not unique, a numeric prefix will be appended / increased.
	 * @return string Unique term slug for the nav_menu taxonomy.
	 * @since 1.0
	 */
    private function get_unique_menu_slug( $term_slug ) {
    	$slug_candidate = $term_slug;

    	while( term_exists( $slug_candidate ) ) {
    		$slug_parts = explode( '-', $slug_candidate );

    		$last_slug_part_index = count( $slug_parts ) - 1;
    		$last_slug_part = $slug_parts[ $last_slug_part_index ];

    		if( is_numeric( $last_slug_part ) ) {
    			$slug_parts[ $last_slug_part_index ] = $last_slug_part + 1;
    			$slug_candidate = implode( '-', $slug_parts );
		    } else {
    			$slug_candidate .= '-2';
		    }
	    }

	    return $slug_candidate;
    }


	/**
	 * Import items for all menus.
	 *
	 * @param array $items Import data for menu items. An associative array indexed by menu IDs, each item is an array
	 *      of actual menu items. See self::export_menu_items() for details.
	 * @param int[int] $menu_id_map Mapping from old to new menu IDs.
	 * @return \Toolset_Result_Set
	 * @since 1.0
	 */
    private function import_menu_items( $items, $menu_id_map ) {

        $item_id_map = [];
	    $results = new \Toolset_Result_Set();

	    // First, import the actual items.
	    //
	    // While doing that, we'll create the ID mapping for menu items in the same way as we did for menus earlier.
	    // After this, the items are imported and connected to the right menus, but their hierarchy is wrong (still
	    // with old IDs).
	    foreach( $items as $old_menu_id => $menu_items ) {
		    $results->add( array_map(
			    function( $item ) use( $menu_id_map, &$item_id_map ) {
				    return $this->import_single_menu_item( $item, $menu_id_map, $item_id_map );
			    }, $menu_items
		    ) );
	    }

	    // Use the menu item ID map for updating IDs of item parents.
	    //
	    // Manually updating the postmeta because for this one value, there's no need to go through the
	    // wp_update_nav_menu_item() ordeal again.
	    foreach( $item_id_map as $old_item_id => $new_item_id ) {

		    $old_parent_id = get_post_meta( $new_item_id, '_menu_item_menu_item_parent', true );
			if( (int) $old_parent_id <= 0) {
				continue;
			}

			$new_parent_id = (int) toolset_getarr( $item_id_map, $old_parent_id, 0 );

			// If there's an error (no mapping available), we'll record it but still update the item.
		    // It will lose its hierarchy but otherwise it should be intact.
			if( 0 === $new_parent_id ) {
				$results->add( new \Toolset_Result( false, sprintf(
					__( 'Cannot update the parent of menu item %d (previously %d) because there is no replacement available (check previous errors). The previous parent ID was %d.', 'toolset-advanced-export' ),
					$new_item_id, $old_item_id, $old_parent_id
				) ) );
			}

			update_post_meta( $new_item_id, '_menu_item_menu_item_parent', $new_parent_id );
	    }

	    return $results;
    }


	/**
	 * Import one menu item and bind it to the correct menu.
	 *
	 * At this point we assume that the menu as well as the target object (post, taxonomy term, etc.) already exists.
	 * Menu item parents are not handled yet, they will contain old (wrong) IDs.
	 *
	 * @param array $item Item data coming from self::export_single_menu_items().
	 * @param int[int] $menu_id_map Mapping from old menu IDs to new ones.
	 * @param int[int] $item_id_map Mapping from old menu item IDs to new ones.
	 *
	 * @return \Toolset_Result
	 *
	 * @since 1.0
	 */
	private function import_single_menu_item( $item, $menu_id_map, &$item_id_map ) {

		$item = (array) $item;

		// Helper function for safely accessing the data we're importing.
		$get = function( $what, $default = '', $valid = null ) use( $item ) {
			return toolset_getarr( $item, $what, $default, $valid );
		};

		$menu_id = (int) toolset_getarr( $menu_id_map, $get( 'menu_id' ), null );
		if( 0 === $menu_id ) {
			return new \Toolset_Result( false, sprintf( __( 'Missing menu item ID mapping for "%d".', 'toolset-advanced-export' ), $get( 'menu_id' ) ) );
		}

		/**
		 * @var array|\Toolset_Result $target_object What does the menu item point to?
		 *     Always contains 'id' and 'object_type'.
		 */
		$target_object = $this->get_current_target_object( $item, $get( 'type' ), $get( 'portable_target_data', null ) );
		if( $target_object instanceof \Toolset_Result ) {
			// We have an error
			return $target_object;
		}

		// Create the menu item.
		//
		// In most cases, we can just grab the information from the import and use it without
		// sanitization. wp_update_nav_menu_item() sanitizes everything for us.
		$item_id_or_error = wp_update_nav_menu_item(
			$menu_id,
			"0", // always create a new menu item
			[
				'menu-item-status' => ( $get( 'is_public' ) ? 'publish' : 'draft' ),
				'menu-item-type' => $get( 'type', '', [ 'custom', 'taxonomy', 'post_type', 'post_type_archive' ] ),
				'menu-item-position' => (int) $get( 'position' ),
				'menu-item-parent-id' => (int) $get( 'parent' ),
				'menu-item-title' => $get( 'title' ),
				'menu-item-target' => $get( 'attr_target', '', [ '', '_blank' ] ),
				'menu-item-attr-title' => $get( 'attr_title' ),

				// The function expects a string, but converts it back to an array immediately.
				'menu-item-classes' => implode( ' ', toolset_ensarr( $get( 'classes' ) ) ),

				'menu-item-xfn' => $get( 'xfn' ),
				'menu-item-description' => $get( 'description' ),

				'menu-item-object-id' => $target_object['id'],
				'menu-item-object' => $target_object['object_type'],

				// For custom links, we don't want to change anything here. For taxonomies / posts,
				// the URL will probably no longer work, but wp_update_nav_menu() ignores it in these cases.
				'menu-item-url' => $get( 'url' )
			]
		);

		if( $item_id_or_error instanceof \WP_Error ) {
			return new \Toolset_Result( $item_id_or_error );
		}

		$item_id_map[ $get( 'db_id' ) ] = $item_id_or_error;

		return new \Toolset_Result( true );
	}


	/**
	 * Calculate the menu item target from the imported data.
	 *
	 * @param array $item Item data from the import.
	 * @param string $menu_item_type Menu item type, coming from WordPress.
	 * @param null|array $portable_target_data Additional data collected on import that can help to find the target,
	 *     not depending on its ID.
	 *
	 * @return array Always should have the 'id' and 'object_type' elements.
	 */
    private function get_current_target_object( $item, $menu_item_type, $portable_target_data = null ) {
        switch( $menu_item_type ) {
            case 'custom':
                // This is ignored by wp_update_nav_menu_item().
                $result = [
                	'id' => '',
	                'object_type' => ''
                ];
                break;

            case 'taxonomy':
				$result = $this->get_current_target_term( $portable_target_data );
                break;

            case 'post_type':
            	$result = $this->get_current_target_post( $portable_target_data );
            	break;

            case 'post_type_archive':
		        $result = [
		        	'id' => '', // Ignored for this item type.
			        'object_type' => toolset_getarr( $item, 'object' )
		        ];
		        break;

            default:
                $result = new \Toolset_Result( false, sprintf( __( 'Unknown type of a menu item target "%s".', 'toolset-advanced-export' ), sanitize_text_field( $menu_item_type ) ) );
                break;
        }

	    /**
	     * toolset_get_menu_item_target
	     *
	     * Filter the target of a menu item on import.
	     *
	     * @param \Toolset_Result|array $result
	     * @param string $menu_item_type
	     * @param array $item Item import data.
	     * @param null|array $portable_target_data Additional data collected on import that can help to find the target,
	     *     not depending on its ID.
	     * @return \Toolset_Result|array The result object on error, otherwise an array with target information. It
	     *     must have the 'id' and 'object_type' elements which will be used directly on the menu item.
	     * @since 1.0
	     */
        return apply_filters( 'toolset_get_menu_item_target', $result, $menu_item_type, $item, $portable_target_data );
    }


	/**
	 * Determine target post for a menu item.
	 *
	 * @param array $portable_target_data
	 *
	 * @return array|\Toolset_Result
	 */
	private function get_current_target_post( $portable_target_data ) {
		if( false == toolset_getarr( $portable_target_data, 'exists', false ) ) {
			return new \Toolset_Result( false, __( 'Skipping a menu item pointing to a non-existent post.', 'toolset-advanced-export' ) );
		}

		/**
		 * toolset_import_post_menu_item
		 *
		 * Allow for overriding the data used to determine a post menu item target on import.
		 *
		 * @param null|array $portable_target_data
		 * @return null|array Additional data collected on import that can help to find the target,
		 *     not depending on its ID.
		 * @since 1.0
		 */
		$post_data = e\Migration_Data_Portable_Post::from_array(
			apply_filters( 'toolset_import_post_menu_item', $portable_target_data )
		);

		$post = get_post( $post_data->to_post_id() );

		if( ! $post instanceof \WP_Post ) {
			return new \Toolset_Result( false, __( 'Skipping a menu item pointing to a non-existent post.', 'toolset-advanced-export' ) );
		}

		return [
			'object_type' => $post->post_type,
			'id' => $post->ID
		];
    }


	/**
	 * Determine target taxonomy term for a menu item.
	 *
	 * @param array $portable_target_data
	 *
	 * @return array|\Toolset_Result
	 */
    private function get_current_target_term( $portable_target_data ) {

    	if( false == toolset_getarr( $portable_target_data, 'exists', false ) ) {
    		return new \Toolset_Result( false, __( 'Skipping a menu item pointing to a non-existent term.', 'toolset-advanced-export' ) );
	    }

	    /**
	     * toolset_import_taxonomy_term_menu_item
	     *
	     * Allow for overriding the data used to determine a taxonomy term menu item target on import.
	     *
	     * @param null|array $portable_target_data
	     * @return null|array Additional data collected on import that can help to find the target,
	     *     not depending on its ID.
	     * @since 1.0
	     */
	    $taxonomy_data = apply_filters( 'toolset_import_taxonomy_term_menu_item', $portable_target_data );

    	$term_slug = toolset_getarr( $taxonomy_data, 'slug' );
	    $taxonomy_slug = toolset_getarr( $taxonomy_data, 'taxonomy' );

	    /**
	     * toolset_import_menu_item_target_term
	     *
	     * Allow for overriding the target term of a menu item.
	     *
	     * @param false|\WP_Term $term
	     * @return false|\WP_Term
	     */
	    $term = apply_filters( 'toolset_import_menu_item_target_term', get_term_by( 'slug', $term_slug, $taxonomy_slug ) );

	    if( false === $term ) {
		    return new \Toolset_Result( false, sprintf(
			    __( 'The menu item targeting the term "%s" in the taxonomy "%s" could not be imported. No such term exists.', 'toolset-advanced-export' ),
			    $term_slug,
			    $taxonomy_slug
		    ) );
	    }

	    return [
		    'object_type' => $taxonomy_slug,
		    'id' => (int) $term->term_id
	    ];
    }


	/**
	 * Connect imported menu to their locations in the active theme.
	 *
	 * This assumes that the theme is the same as on export.
	 *
	 * @param array $locations Location slug => old menu ID
	 * @param array $menu_id_map Old menu ID => new menu ID
	 * @return \Toolset_Result
	 * @since 1.0
	 */
    private function import_menu_locations( $locations, $menu_id_map ) {

        $updated_locations = array_reduce(
            array_keys( $locations ),
            function( $carry, $location_slug ) use( $locations, $menu_id_map ) {
            	// Apply the mapping or set 0 if there is none.
                $carry[ $location_slug ] = toolset_getarr( $menu_id_map, $locations[ $location_slug ], 0 );
                return $carry;
            },
            []
        );

        set_theme_mod( 'nav_menu_locations', $updated_locations );

        return new \Toolset_Result( true );
    }

}