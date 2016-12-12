<?php

namespace ToolsetExtraExport;

/**
 * Handles the import and export of a single WordPress option.
 *
 * @since 1.0
 */
class Migration_Handler_Option implements IMigration_Handler {


    protected $option_name;
    protected $default_value;
    protected $sanitization_method;


    /**
     * Migration_Handler_Option constructor.
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
     * @return IMigration_Data
     */
    function export() {

        $option_value = call_user_func( $this->sanitization_method, get_option( $this->option_name, $this->default_value ) );
        return new Migration_Data_Raw( $option_value );

    }

    /**
     * @param IMigration_Data $data Correct migration data for the section
     *
     * @return mixed @todo Toolset_Result or similar
     * @throws \InvalidArgumentException
     */
    function import( $data ) {
        // TODO: Implement import() method.
    }

}