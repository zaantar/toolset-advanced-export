<?php

namespace ToolsetExtraExport\MigrationHandler;

use ToolsetExtraExport as e;
use ToolsetExtraExport\Utils as utils;

/**
 * Handles the import and export of a WordPress option with a post ID.
 *
 * When exporting, it stores additional information for identifying a post even after its ID changes.
 *
 * @since 1.0
 */
class Post_Option extends Option {


    public function __construct( $option_name, $default_value = 0 ) {
        parent::__construct( $option_name, 'absint', $default_value );
    }


    public function export() {
        $option_value = call_user_func( $this->sanitization_method, get_option( $this->option_name, $this->default_value ) );
        $post_data = utils\get_portable_post_data( $option_value );
        return e\Migration_Data_Nested_Array::from_array( $post_data );
    }


    /**
     * @param e\Migration_Data_Nested_Array $data
     *
     * @return \Toolset_Result
     */
    public function import( $data ) {

        if( ! $data instanceof e\Migration_Data_Nested_Array ) {
            throw new \InvalidArgumentException( 'Wrong data type for option import' );
        }

        $option_value = $this->sanitize( $this->calculate_value_on_import( $data ) );

        $previous_value = get_option( $this->option_name );
        $is_same = ( $previous_value == $option_value );

        $is_updated = update_option( $this->option_name, $option_value );

        return utils\create_result( $is_updated || $is_same );
    }


    /**
     * @param e\Migration_Data_Nested_Array $data
     * @return int
     */
    private function calculate_value_on_import( $data ) {

        $data = $data->to_array();
        if( ! toolset_getarr( $data, 'exists', false ) ) {
            return 0;
        }

        $post_id = $this->get_post_id_by_guid( toolset_getarr( $data, 'guid', null ) );

        if( 0 === $post_id ) {
            $post_id = $this->get_post_id_by_slug(
                toolset_getarr( $data, 'slug', null ),
                toolset_getarr( $data, 'post_type', null )
            );
        }

        return $post_id;
    }


    private function get_post_id_by_guid( $guid ) {
        global $wpdb;

        $post_id = $wpdb->get_var(
            $wpdb->prepare( "SELECT ID from {$wpdb->posts} WHERE guid = %s", $guid )
        );

        return (int) $post_id;
    }


    private function get_post_id_by_slug( $slug, $post_type ) {

        if( ! is_string( $slug ) || ! is_string( $post_type ) ) {
            return 0;
        }

        $query = new \WP_Query( [
            'post_type' => $post_type,
            'name' => $slug,
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'ignore_sticky_posts' => true,
            'orderby' => 'none',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'suppress_filters' => true
        ] );

        if( $query->post_count != 1 ) {
            return 0;
        }

        return $query->posts[0];
    }

}