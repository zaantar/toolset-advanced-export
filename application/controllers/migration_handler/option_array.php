<?php

namespace ToolsetExtraExport\MigrationHandler;

use ToolsetExtraExport as e;


/**
 * Handles migration of some WordPress options.
 *
 * Depending on self::get_option_list(), which must be overridden, gathers and sanitizes data from WordPress options.
 *
 * @since 1.0
 */
abstract class Option_Array implements IMigration_Handler {


	/**
	 * Retrieves a list of option handlers.
	 *
	 * @return Option[]
     * @since 1.0
	 */
	abstract protected function get_option_list();


	/**
	 * Export WordPress options into an Migration_Data_Nested_Array object.
	 *
	 * @return e\IMigration_Data
	 * @since 1.0
	 */
	function export() {

		$options = $this->get_option_list();

		$output = [];
		foreach( $options as $option ) {
		    $option_value = $option->export()->to_array();
			$output[ $option->get_name() ] = $option_value;
		}

		$migration_data = e\Migration_Data_Nested_Array::from_array( $output );

		return $migration_data;
	}


	/**
	 * @param e\IMigration_Data $migration_data
	 *
	 * @return mixed
	 */
	function import( $migration_data ) {
		// todo
		throw new \RuntimeException( 'Not implemented.' );
	}

}