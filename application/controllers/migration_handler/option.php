<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;
use ToolsetAdvancedExport\Utils as utils;

/**
 * Handles the import and export of a single WordPress option.
 *
 * @since 1.0
 */
class Option implements IMigration_Handler {


    protected $option_name;
    protected $default_value;
    protected $sanitization_method;


    /**
     * Option constructor.
     *
     * Note: Default default value is false as per get_option() signature; defaultception!
     *
     * @param string $option_name
     * @param callable $sanitization_method Function that will accept the option value as a first parameter
     *       and sanitize it.
     * @param mixed $default_value
     */
    public function __construct( $option_name, $sanitization_method, $default_value = false ) {
        if(
            ! is_string( $option_name )
            || sanitize_text_field( $option_name ) != $option_name
            || ! is_callable( $sanitization_method )
        ) {
            throw new \InvalidArgumentException();
        }

        $this->option_name = $option_name;
        $this->default_value = $default_value;
        $this->sanitization_method = $sanitization_method;
    }


    public function get_name() {
        return $this->option_name;
    }


    /**
     * @return e\IMigration_Data
     */
    function export() {
        $option_value = $this->sanitize( get_option( $this->option_name, $this->default_value ) );
        return new e\Migration_Data_Raw( $option_value );
    }


    /**
     * @param e\Migration_Data_Raw|e\Migration_Data_Nested_Array $data Correct migration data for the section
     *
     * @return \Toolset_Result
     * @throws \InvalidArgumentException
     */
    function import( $data ) {

        $option_value = $this->get_raw_migration_data( $data )->get_raw_value();
        $option_value = $this->sanitize( $option_value );

        $previous_value = get_option( $this->option_name );
        $is_same = ( $previous_value == $option_value );

        $is_updated = update_option( $this->option_name, $option_value );

        return utils\create_result( $is_updated || $is_same );
    }


    protected function sanitize( $value ) {
        return call_user_func( $this->sanitization_method, $value );
    }


    /**
     * If migration data in the form of a nested array is provided, checks that it can be interpreted as a raw value,
     * and return this value instead. Raw value will be returned without further modifications.
     *
     * @param e\Migration_Data_Raw|e\Migration_Data_Nested_Array $data
     * @return e\Migration_Data_Raw
     * @throws \InvalidArgumentException
     */
    private function get_raw_migration_data( $data ) {
        if( $data instanceof e\Migration_Data_Raw ) {
            return $data;
        } elseif( $data instanceof e\Migration_Data_Nested_Array ) {
            $values = $data->to_array();
            if( 1 === count( $values ) ) {
                return new e\Migration_Data_Raw( $values[0] );
            }
        }

        throw new \InvalidArgumentException( 'Wrong data type for option import' );
    }
}