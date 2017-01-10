<?php

namespace ToolsetAdvancedExport;

/**
 * Higher level API for performing export.
 *
 * Usage example: $exporter = new Exporter( [ 'sections' => [ ... ], ... ] ); $data = $exporter->get_data();
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
	 *     - 'sections': array of names of sections that should be exported.
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
			/** @var MigrationHandler\IMigration_Handler $migration_handler */
			$migration_handler = Migration_Handler_Factory::get( $section_name );

			$migration_data = $migration_handler->export();

			$results[ $section_name ] = $migration_data;
		}

		return $results;

	}


    /**
     * Export data as a single JSON string.
     *
     * @return string
     * @throws \RuntimeException on failure.
     * @since 1.0
     */
	public function get_json() {

	    $raw_data = $this->get_data();

        $output = [];
        foreach( $raw_data as $section_name => $raw_section ) {
            $output[ $section_name ] = $raw_section->to_array();
        }

        $output = json_encode( $output );

        if( false === $output ) {
            throw new \RuntimeException( 'Unable to produce valid JSON output.' );
        }

        return $output;

    }


    /**
     * Export data as a ZIP file.
     *
     * Resulting ZIP archive will contain a file 'settings.json' with the exported data in a JSON string.
     *
     * @param bool $preserve_file
     * @param null|string $forced_file_name
     *
     * @return array Content of the ZIP file as 'file' and eventually the absolute path to the file as 'path'.
     * @since 1.0
     */
	public function get_zip( $preserve_file = false, $forced_file_name = null ) {

        $temp_dir = $this->get_temp_dir( $preserve_file );

        // Determine absolute file path and create the file (sic!)
        if( null == $forced_file_name ) {
            $file_path = tempnam( $temp_dir, 'zip' );
        } else {
            $file_path = trailingslashit( $temp_dir ) . $forced_file_name;
            touch( $file_path );
        }

        // Create the archive
        $zip = new \ZipArchive();
        $zip->open( $file_path, \ZipArchive::OVERWRITE );
        if ( empty( $zip->filename ) ) {
            throw new \RuntimeException( 'Could not create temporary zip file.' );
        }

        // Add the exported data
	    $zip->addFromString( 'settings.json', $this->get_json() );
        $zip->close();

        // Get the ZIP content and delete the temporary file
        $file_contents = file_get_contents( $file_path );

        $result = [
            'file' => $file_contents,
            'path' => ( $preserve_file ? $file_path : null )
        ];

        if( ! $preserve_file ) {
            unlink( $file_path );
        }

        return $result;
    }


    /**
     * Get a path to the directory for temporary files.
     *
     * Uses the system temp dir if available, otherwise falls back to the WordPress upload directory.
     *
     * @param bool $force_wp_upload_dir If true, the WordPress upload directory will be always used.
     *
     * @return string Full path.
     * @since 1.0
     */
    private function get_temp_dir( $force_wp_upload_dir = false ) {

        if( ! $force_wp_upload_dir ) {
            $temporary_directory = sys_get_temp_dir();
            if ( ! empty( $temporary_directory ) && is_dir( $temporary_directory ) && is_writable( $temporary_directory ) ) {
                return $temporary_directory;
            }
        }

        $temporary_directory = wp_upload_dir();
        $temporary_directory = $temporary_directory['basedir'];
        return $temporary_directory;
    }

}