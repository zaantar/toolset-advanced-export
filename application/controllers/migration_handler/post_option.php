<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;
use ToolsetAdvancedExport\Utils as utils;

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

    	$portable_post = e\Migration_Data_Portable_Post::from_array( $data->to_array() );

        return $portable_post->to_post_id();
    }



}