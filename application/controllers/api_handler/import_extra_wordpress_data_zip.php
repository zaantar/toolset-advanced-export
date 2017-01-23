<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport as e;

/**
 * Handler for the toolset_import_extra_wordpress_data_zip filter hook.
 *
 * The hook imports WordPress sections provided in a ZIP file.
 *
 * toolset_import_extra_wordpress_data_zip
 * @param null
 * @param string $zip_path Absolute path to the ZIP file.
 * @param string[] $sections_to_import Names of the sections to import.
 * @return \Toolset_Result_Set|\Toolset_Result Operation results.
 *
 * @since 1.0
 */
class Import_Extra_Wordpress_Data_Zip implements Api_Handler_Interface {

	public function __construct() {	}


	/**
	 * @inheritdoc
	 * @param array $arguments Original action/filter arguments.
	 *
	 * @return mixed
	 */
	function process_call( $arguments ) {

		// Fail if the file doesn't exist
		$zip_path = toolset_getarr( $arguments, 1 );
		if( ! file_exists( $zip_path ) ) {
			return new \Toolset_Result( false, sprintf(
				__( 'File "%s" was not found.', 'toolset-advanced-export' ),
				$zip_path
			) );
		}

		$import_json = @file_get_contents( sprintf( 'zip://%s#settings.json', $zip_path ) );
		if( false === $import_json ) {

			$error = error_get_last();
			$error = explode( ': ', $error['message'] );
			$error = trim( $error[2] );

			return new \Toolset_Result( false, sprintf(
				__( 'Cannot read import file "%s" (%s).', 'toolset-advanced-export' ),
				$zip_path,
				$error
			) );
		}

		$import_data = json_decode( $import_json, true );
		if( ! is_array( $import_data ) ) {
			return new \Toolset_Result( false, sprintf(
				__( 'Cannot parse import file "%s".', 'toolset-advanced-export' ),
				basename( $zip_path )
			) );
		}

		$sections_to_import = toolset_ensarr( toolset_getarr( $arguments, 2 ) );
		if( in_array( 'all', $sections_to_import ) ) {
			$sections_to_import = array_keys( $import_data );
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