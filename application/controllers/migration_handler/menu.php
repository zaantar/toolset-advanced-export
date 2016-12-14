<?php

namespace ToolsetExtraExport\MigrationHandler;

use ToolsetExtraExport as e;
use ToolsetExtraExport\Utils as utils;

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
                'is_public' => ( 'publish' == $item->post_status ),
                'type' => $item->type,
                'position' => $item->menu_order,
                'parent' => $item->menu_item_parent,
                'title' => $item->title,
                'target' => $item->target,
                'attr_title' => $item->attr_title,
                'classes' => $item->classes,
                'xfn' => $item->xfn,
                'description' => $item->post_content,

                // Meaning of these values differs, depending on item type.
                'object_id' => (int) $item->object_id,
                'object' => $item->object,
                'url' => $item->url
            ];

            // Append extra data so that, on import, we can link to the correct post with more certainty.
            if( 'post_type' == $item->type ) {
                $item_export['portable_post_data'] = utils\get_portable_post_data( (int) $item->object_id );
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
     * @param e\IMigration_Data $data Correct migration data for the section
     *
     * @return \Toolset_Result|\Toolset_Result_Set
     * @throws \InvalidArgumentException
     */
    function import( $data ) {
        // TODO: Implement import() method.
        throw new \RuntimeException( 'Not implemented.' );
    }

}