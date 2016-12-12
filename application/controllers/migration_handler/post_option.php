<?php

namespace ToolsetExtraExport\MigrationHandler;

use ToolsetExtraExport as e;


/**
 * Handles the import and export of a WordPress option with a post ID.
 *
 * When exporting, it stores additional information for identifying a post even after its ID changes.
 *
 * @since 1.0
 */
class Post_Option extends Option {


    public function __construct( $option_name, $default_value = 0 ) {
        parent::__construct( $option_name, 'intval', $default_value );
    }


    public function export() {
        $option_value = call_user_func( $this->sanitization_method, get_option( $this->option_name, $this->default_value ) );
        $post_data = $this->get_portable_post_data( $option_value );
        return e\Migration_Data_Nested_Array::from_array( $post_data );
    }


    public function import( $data ) {
        throw new \RuntimeException( 'Not implemented.' );
    }


    /**
     * Get additional information for identifying a post even after its ID changes.
     *
     * @param int $post_id
     * @return array Contains at least the "exists" key (boolean).
     * @since 1.0
     */
    private function get_portable_post_data( $post_id ) {

        $post = get_post( $post_id );
        if( ! $post instanceof \WP_Post ) {
            return [ 'exists' => false ];
        }

        $portable_post_data = [
            'exists' => true,
            'original_id' => $post->ID,
            'slug' => $post->post_name,
            'guid' => $post->guid
        ];

        return $portable_post_data;
    }

}