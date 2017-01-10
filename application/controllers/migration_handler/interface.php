<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;

/**
 * Handles migratin (import and export) of a particular WordPress data section.
 *
 * @since 1.0
 */
interface IMigration_Handler {

	/**
	 * @return e\IMigration_Data
	 */
	function export();

	/**
	 * @param e\IMigration_Data $data Correct migration data for the section
	 * @return \Toolset_Result|\Toolset_Result_Set
	 * @throws \InvalidArgumentException
	 */
	function import( $data );

}