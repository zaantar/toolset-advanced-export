<?php

namespace ToolsetExtraExport;

/**
 * Higher level API for performing export.
 *
 * Usage example: $exported = new Exporter( [ 'sections' => [ ... ], ... ] ); $data = $exporter->get_data();
 *
 * @since 1.0
 */
class Exporter {


	const ARGUMENT_SECTIONS = 'sections';


	/**
	 * @var string[]
	 */
	private $sections_to_export;


	/**
	 * Exporter constructor.
	 *
	 * @param [] $args Following arguments are accepted:
	 *     - 'sections': array of names of sections that should be exported. If a section is
	 *       not supported, it will be skipped silently.
	 * @throws \InvalidArgumentException
	 * @since 1.0
	 */
	public function __construct( $args ) {

		$this->sections_to_export = array_filter(
			toolset_getarr( $args, self::ARGUMENT_SECTIONS, array() ),
			'is_string'
		);

		if( !is_array( $this->sections_to_export ) ) {
			throw new \InvalidArgumentException();
		}

	}


	/**
	 * Get the data in "raw" form without further post-processing.
	 *
	 * Returns the plugin's internal "migration data" objects holding the exported data.
	 *
	 * @return IMigration_Data[] Indexed by section names, each exported section will be represented by
	 *     an IMigration_Data object.
	 * @since 1.0
	 */
	public function get_data() {

		$results = [];

		foreach( $this->sections_to_export as $section_name ) {

			// Get a dedicated handler for the section.
			try {
				/** @var IMigration_Handler $migration_handler */
				$migration_handler = Migration_Handler_Factory::get( $section_name );
			} catch( \Exception $e ) {
				continue;
			}

			$migration_data = $migration_handler->export();

			$results[ $section_name ] = $migration_data;
		}

		return $results;

	}

}