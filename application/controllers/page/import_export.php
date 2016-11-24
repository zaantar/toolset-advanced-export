<?php

namespace ToolsetExtraExport;

/**
 * Base page for rendering the import/export GUI.
 *
 * It can be used on a standalone page or in the Toolset Import/Export menu.
 *
 * @package ToolsetExtraExport
 */
abstract class Page_Import_Export {

	protected static $instance;

	protected function __construct() { }

	/**
	 * @return Page_Import_Export
	 */
	final public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	final private function __clone() { }


	public static function render() {

		$instance = static::get_instance();
		$instance->render_page();

	}


	protected function render_page() {



		echo 'rendered d\'oh!';
	}



}