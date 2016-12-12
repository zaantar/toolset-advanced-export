<?php

namespace ToolsetExtraExport;

/**
 * Handles migration of some WordPress options.
 *
 * Depending on self::get_option_list(), which must be overridden, gathers and sanitizes data from WordPress options.
 *
 * @since 1.0
 */
abstract class Migration_Handler_Option_Array implements IMigration_Handler {


	/**
	 * Retrieves a list of option handlers.
	 *
	 * @return Migration_Handler_Option[]
     * @since 1.0
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
		foreach( $options as $option ) {
		    $option_value = $option->export()->to_array();
			$output[ $option->get_name() ] = $option_value;
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