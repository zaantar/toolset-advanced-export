<?php

namespace ToolsetAdvancedExport;

/**
 * Represents a section of WordPress data.
 *
 * @since 1.0
 */
interface IMigration_Data {

	/**
	 * @param array $array_input
	 * @return IMigration_Data
	 * @throws \InvalidArgumentException
	 */
	public static function from_array( $array_input );


	/**
	 * @param string $json_input
	 * @return IMigration_Data
	 * @throws \InvalidArgumentException
	 */
	public static function from_json( $json_input );


	/**
	 * @return array
	 */
	public function to_array();


	/**
	 * @return string
	 */
	public function to_json();

}