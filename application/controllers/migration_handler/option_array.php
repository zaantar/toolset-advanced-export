<?php

namespace ToolsetExtraExport;

/**
 * Handles migration of some WordPress options.
 *
 * Depending on self::get_option_list(), which must be overridden, gathers and sanitizes data from WordPress options.
 *
 * @since 1.1
 */
abstract class Migration_Handler_Option_Array implements IMigration_Handler {


	/**
	 * Retrieves a list of options in an associative array.
	 *
	 * Keys are option names and elements are argument arrays for each option:
	 *     - 'default_value': string|int|array
	 *     - 'sanitize_callback': callable|null Function that will accept the option value as a first parameter
	 *       and sanitize it.
	 *
	 * @return array[string]
	 */
	abstract protected function get_option_list();


	/**
	 * Export WordPress options into an Migration_Data_Nested_Array object.
	 *
	 * @return IMigration_Data
	 * @since 1.0
	 */
	function export() {

		$options = $this->get_option_list();

		$output = [];
		foreach( $options as $option_name => $option_settings ) {

			// default default value is false as per get_option() signature; defaultception!
			$default_value = toolset_getarr( $option_settings, 'default_value', false );

			$option_value = get_option( $option_name, $default_value );

			$sanitize_callback = toolset_getarr( $option_settings, 'sanitize_callback', null );
			if( is_callable( $sanitize_callback ) ) {
				$option_value = call_user_func( $sanitize_callback, $option_value );
			}

			$output[ $option_name ] = $option_value;
		}

		$migration_data = Migration_Data_Nested_Array::from_array( $output );

		return $migration_data;
	}


	/**
	 * @param IMigration_Data $migration_data
	 *
	 * @return mixed
	 */
	function import( $migration_data ) {
		// todo
		throw new \RuntimeException( 'Not implemented.' );
	}

}