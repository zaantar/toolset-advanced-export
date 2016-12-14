<?php

namespace {

    /**
     * Safely retrieve a key from given array.
     *
     * Checks if the key is set in the source array. If not, default value is returned. Optionally validates against array
     * of allowed values and returns default value if the validation fails.
     *
     * @param array $source The source array.
     * @param string $key The key to be retrieved from the source array.
     * @param mixed $default Default value to be returned if key is not set or the value is invalid. Optional.
     *     Default is empty string.
     * @param null|array $valid If an array is provided, the value will be validated against it's elements.
     *
     * @return mixed The value of the given key or $default.
     */
    if ( ! function_exists( 'toolset_getarr' ) ) {

        function toolset_getarr( &$source, $key, $default = '', $valid = null ) {
            if ( isset( $source[ $key ] ) ) {
                $val = $source[ $key ];
                if ( is_array( $valid ) && ! in_array( $val, $valid ) ) {
                    return $default;
                }

                return $val;
            } else {
                return $default;
            }
        }

    }

}


namespace ToolsetExtraExport\Utils {

    /**
     * @param bool|\WP_Error|\Exception $value Result value. For boolean, true determines a success, false
     *     determines a failure. WP_Error and Exception are interpreted as failures.
     * @param string|null $display_message Optional display message that will be used if a boolean result is
     *     provided.
     *
     * @return \Toolset_Result
     * @throws \InvalidArgumentException
     */
    function create_result( $value, $display_message = null ) {
        return new \Toolset_Result( $value, $display_message );
    }


    /**
     * Get additional information for identifying a post even after its ID changes.
     *
     * @param int $post_id
     * @return array Contains at least the "exists" key (boolean).
     * @since 1.0
     */
    function get_portable_post_data( $post_id ) {

        $post = get_post( $post_id );
        if( ! $post instanceof \WP_Post ) {
            return [ 'exists' => false ];
        }

        $portable_post_data = [
            'exists' => true,
            'original_id' => $post->ID,
            'slug' => $post->post_name,
            'guid' => $post->guid,
            'post_type' => $post->post_type
        ];

        return $portable_post_data;
    }



}