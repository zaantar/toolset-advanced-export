<?php

namespace ToolsetAdvancedExport;

/**
 * Holds a "raw" value (that is not necessarily an array).
 *
 * It's possible to obtain the raw value via get_raw_value().
 *
 * @since 1.0
 */
class Migration_Data_Raw implements IMigration_Data {

    protected $value;


    public function __construct( $value ) {
        $this->value = $value;
    }


    public function get_raw_value() {
        return $this->value;
    }


    /**
     * @param array $array_input
     *
     * @return IMigration_Data
     * @throws \InvalidArgumentException
     */
    public static function from_array( $array_input ) {
        if( !is_array( $array_input ) || 1 != count( $array_input ) ) {
            throw new \InvalidArgumentException( 'Expected an array with one option value' );
        }

        return new self( $array_input[0] );
    }

    /**
     * @param string $json_input
     *
     * @return IMigration_Data
     * @throws \InvalidArgumentException
     */
    public static function from_json( $json_input ) {
        throw new \RuntimeException( 'Not implemented.' );
    }

    /**
     * @return array
     */
    public function to_array() {
        return [ $this->value ];
    }

    /**
     * @return string
     */
    public function to_json() {
        throw new \RuntimeException( 'Not implemented.' );
    }
}