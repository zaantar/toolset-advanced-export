<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport as e;


/**
 * Handler for the toolset_export_extra_wordpress_data filter hook.
 *
 * The hook exports selected WordPress sections and returns them as one (nested) associative array.
 *
 * toolset_export_extra_wordpress_data
 * @param null
 * @param string[] $sections_to_export Names of the sections to export.
 * @return array Exported data, one element for each section, indexed by section name.
 *
 * @since 1.0
 */
class Export_Extra_Wordpress_Data extends Export_Extra_Wordpress_Data_Raw {

    /**
     * @param array $arguments Original action/filter arguments.
     *
     * @return array
     */
    function process_call( $arguments ) {

        $raw_data = parent::process_call( $arguments );

        $output = [];
        foreach( $raw_data as $section_name => $raw_section ) {
            $output[ $section_name ] = $raw_section->to_array();
        }

        return $output;
    }


}