<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport as e;

/**
 * Handler for the toolset_export_extra_wordpress_data_raw filter hook.
 *
 * The hook exports selected WordPress sections and returns them as raw data stored in plugin's internal objects.
 * It is recommended to use or create a more specific, dedicated API hook instead of this one.
 *
 * toolset_export_extra_wordpress_data_raw
 * @param null
 * @param string[] $sections_to_export Names of the sections to export.
 * @return e\IMigration_Data[] See Exporter::get_data() for further details.
 *
 * @since 1.0
 */
class Export_Extra_Wordpress_Data_Raw implements Api_Handler_Interface {

	public function __construct() { }

	/**
	 * @param array $arguments Original action/filter arguments.
	 *
	 * @return e\IMigration_Data[]
	 */
	function process_call( $arguments ) {

		$sections_to_export = toolset_getarr( $arguments, 1, [] );

		if( ! is_array( $sections_to_export ) || empty( $sections_to_export ) ) {
			return [];
		}

		$exporter = new e\Exporter( [ e\Exporter::ARGUMENT_SECTIONS => $sections_to_export ] );

		$output = $exporter->get_data();

		return $output;
	}

}