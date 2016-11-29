<?php

namespace ToolsetExtraExport;

/**
 * Handles migratin (import and export) of a particular WordPress data section.
 *
 * @since 1.0
 */
interface IMigration_Handler {

	/**
	 * @return IMigration_Data
	 */
	function export();

	/**
	 * @param IMigration_Data $data Correct migration data for the section
	 * @return mixed @todo Toolset_Result or similar
	 * @throws \InvalidArgumentException
	 */
	function import( $data );

}