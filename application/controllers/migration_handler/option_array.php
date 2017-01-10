<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;


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
     * @inheritdoc
     *
	 * @param e\IMigration_Data $migration_data
	 * @return \Toolset_Result_Set
     * @throws \InvalidArgumentException
     * @since 1.0
	 */
	function import( $migration_data ) {

	    if( ! $migration_data instanceof e\Migration_Data_Nested_Array ) {
            throw new \InvalidArgumentException( 'Wrong data type for options import' );
        }

        $migration_array = $migration_data->to_array();

	    $results = new \Toolset_Result_Set();
        $options = $this->get_option_list();
        foreach( $options as $option ) {

            if( array_key_exists( $option->get_name(), $migration_array ) ) {
                $option_data = e\Migration_Data_Nested_Array::from_array( $migration_array[ $option->get_name() ] );
                $results->add( $option->import( $option_data ) );
            } else {
                $results->add( false, sprintf( __( 'Option %s was missing in the import data.', 'toolset-advanced-export' ), $option->get_name() ) );
            }

        }

        return $results;
	}

}