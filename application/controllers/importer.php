<?php

namespace ToolsetAdvancedExport;

/**
 * Higher level API for performing import.
 *
 * Usage example: $importer = new Importer( [ ...sections to import... ] ); $results = $importer->import_array( $data );
 *
 * @since 1.0
 */
class Importer {

    private $sections_to_import;

    /**
     * Importer constructor.
     *
     * @param string[] $sections_to_import Names of sections that should be exported.
     * @since 1.0
     */
    public function __construct( $sections_to_import ) {

        $this->sections_to_import = array_filter(
            $sections_to_import,
            'is_string'
        );

    }


    /**
     * Import the data from an associative array.
     *
     * @param array $data Each item of the input represents a single section. The key should be a section name and
     *     value should be section data in the form of an associative array. Only sections configured in the constructor
     *     will be imported.
     *
     * @return \Toolset_Result_Set Operation results.
     * @since 1.0
     */
    public function import_array( $data ) {

	    $results = new \Toolset_Result_Set();

        if( !is_array( $data ) ) {
        	$results->add( false, __( 'Import data have an incorrect type.' ) );
            return $results;
        }

        foreach( $this->sections_to_import as $section_name ) {

            // Get a dedicated handler for the section.
            /** @var MigrationHandler\IMigration_Handler $migration_handler */
            $migration_handler = Migration_Handler_Factory::get( $section_name );

            if( ! array_key_exists( $section_name, $data ) ) {
                throw new \InvalidArgumentException();
            }

            $section_data = Migration_Data_Nested_Array::from_array( $data[ $section_name ] );
            $results->add( $migration_handler->import( $section_data ) );

        }

        return $results;
    }


}