<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport as e;

/**
 * Handler for the toolset_export_extra_wordpress_data_json filter hook.
 *
 * The hook exports selected WordPress sections and returns them as one (nested) associative array.
 *
 * toolset_export_extra_wordpress_data
 * @param null
 * @param string[] $sections_to_export Names of the sections to export.
 * @return string|null Exported data as a JSON strings, one property for each section. In case of an error, null is returned.
 *
 * @since 1.0
 */
class Export_Extra_Wordpress_Data_Json extends Export_Extra_Wordpress_Data_Raw {


    /**
     * @param array $arguments Original action/filter arguments.
     *
     * @return string|null
     */
    function process_call( $arguments ) {

        $raw_data = parent::process_call( $arguments );

        $output = [];
        foreach( $raw_data as $section_name => $raw_section ) {
            try {
                $output[ $section_name ] = $raw_section->to_array();
            } catch( \Exception $e ) {
                // Just fail without killing anyone.
                return null;
            }
        }

        $output = json_encode( $output );

        if( false === $output ) {
            return null;
        }

        return $output;
    }


}