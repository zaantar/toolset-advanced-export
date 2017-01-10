<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport as e;

/**
 * Handler for the toolset_import_extra_wordpress_data filter hook.
 *
 * The hook imports provided WordPress sections.
 *
 * toolset_import_extra_wordpress_data
 * @param null
 * @param string[] $sections_to_import Names of the sections to import.
 * @param array $import_data Associative array with section data (as arrays), with section names as keys.
 * @return \Toolset_Result_Set Operation results.
 *
 * @since 1.0
 */
class Import_Extra_Wordpress_Data implements Api_Handler_Interface {


    public function __construct() { }

    /**
     * @param array $arguments Original action/filter arguments.
     *
     * @return mixed
     */
    function process_call( $arguments ) {

        $sections_to_import = toolset_getarr( $arguments, 1, [] );
        $import_data = toolset_getarr( $arguments, 2, [] );

        if( ! is_array( $sections_to_import ) || empty( $sections_to_import ) || ! is_array( $import_data ) ) {
            return new \Toolset_Result( false, __( 'Invalid arguments provided.', 'toolset-advanced-export' ) );
        }

        $results = new \Toolset_Result_Set();
        try {
            $importer = new e\Importer( $sections_to_import );
            $results->add( $importer->import_array( $import_data ) );
        } catch( \Exception $e ) {
            $results->add( $e );
        }

        return $results;
    }


}