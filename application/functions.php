<?php


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