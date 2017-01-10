<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;

/**
 * Handles migration of arbitrary WordPress options.
 *
 * When instantiating, a set of Option migration must be provided. This set is stored and used for export and import.
 *
 * @since 1.0
 */
class Option_Array_Custom extends Option_Array {

    /** @var Option[] */
    protected $options;


    /**
     * Option_Array_Custom constructor.
     *
     * @param Option[] $options
     * @throws \InvalidArgumentException
     */
    public function __construct( $options ) {
        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $options, function( $option, $ignored ) {
            if( ! $option instanceof Option ) {
                throw new \InvalidArgumentException( 'Invalid option definition.' );
            }
        } );

        $this->options = $options;
    }


    /**
     * Retrieves a list of option handlers.
     *
     * @return Option[]
     * @since 1.0
     */
    protected function get_option_list() {
        return $this->options;
    }


}