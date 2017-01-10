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


	if( !function_exists( 'toolset_getnest' ) ) {

		/**
		 * Get a value from nested associative array.
		 *
		 * This function will try to traverse a nested associative array by the set of keys provided.
		 *
		 * E.g. if you have $source = array( 'a' => array( 'b' => array( 'c' => 'my_value' ) ) ) and want to reach 'my_value',
		 * you need to write: $my_value = wpcf_getnest( $source, array( 'a', 'b', 'c' ) );
		 *
		 * @param mixed|array $source The source array.
		 * @param string[] $keys Keys which will be used to access the final value.
		 * @param null|mixed $default Default value to return when the keys cannot be followed.
		 *
		 * @return mixed|null Value in the nested structure defined by keys or default value.
		 *
		 * @since 1.0
		 */
		function toolset_getnest( &$source, $keys = array(), $default = null ) {

			$current_value = $source;

			// For detecting if a value is missing in a sub-array, we'll use this temporary object.
			// We cannot just use $default on every level of the nesting, because if $default is an
			// (possibly nested) array itself, it might mess with the value retrieval in an unexpected way.
			$missing_value = new stdClass();

			while( ! empty( $keys ) ) {
				$current_key = array_shift( $keys );
				$is_last_key = empty( $keys );

				$current_value = toolset_getarr( $current_value, $current_key, $missing_value );

				if ( $is_last_key ) {
					// Apply given default value.
					if( $missing_value === $current_value ) {
						return $default;
					} else {
						return $current_value;
					}
				} elseif ( ! is_array( $current_value ) ) {
					return $default;
				}
			}

			return $default;
		}

	}


	if( !function_exists( 'toolset_ensarr' ) ) {

		/**
		 * Ensure that a variable is an array.
		 *
		 * @param mixed $array The original value.
		 * @param array $default Default value to use when no array is provided. This one should definitely be an array,
		 *     otherwise the function doesn't make much sense.
		 *
		 * @return array The original array or a default value if no array is provided.
		 *
		 * @since 1.9
		 */
		function toolset_ensarr( $array, $default = array() ) {
			return ( is_array( $array ) ? $array : $default );
		}

	}

}


namespace ToolsetAdvancedExport\Utils {

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
	 *
	 * @return array Contains at least the "exists" key (boolean).
	 * @since 1.0
	 */
	function get_portable_post_data( $post_id ) {

		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post ) {
			return [ 'exists' => false ];
		}

		$portable_post_data = [
			'exists'      => true,
			'original_id' => $post->ID,
			'slug'        => $post->post_name,
			'guid'        => $post->guid,
			'post_type'   => $post->post_type
		];

		return $portable_post_data;
	}


	/**
	 * Get additional information for identifying a term even after its ID changes.
	 *
	 * @param int $term_id Term ID.
	 * @param string $taxonomy Slug of the taxonomy, where the term belongs.
	 *
	 * @return array Contains at least the "exists" key (boolean).
	 * @since 1.0
	 */
	function get_portable_term_data( $term_id, $taxonomy ) {

		$term = get_term( $term_id, $taxonomy );
		if ( null == $term || $term instanceof \WP_Error ) {
			return [ 'exists' => false ];
		}

		$portable_term_data = [
			'exists'      => true,
			'original_id' => $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'taxonomy'    => $term->taxonomy
		];

		return $portable_term_data;
	}


	/**
	 * Generate a name for the export file matching Toolset standards.
	 *
	 * THEMENAME-PLUGIN-SLUG-Y-M-D.zip
	 *
	 * @return string
	 * @since 1.0
	 */
	function generate_export_file_name() {

		return sprintf(
			'%s-toolset-wp-settings-export-%s.zip',

			// Theme slug without slashes or underscores
			str_replace( '_', '', str_replace( '-', '', sanitize_title( get_stylesheet() ) ) ),

			date( 'Y-m-d' )
		);
	}

}