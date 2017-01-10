<?php

namespace ToolsetAdvancedExport\ApiHandlers;

use ToolsetAdvancedExport;


/**
 * Interface for handlers of hook API calls.
 *
 * @since 1.0
 */
interface Api_Handler_Interface {

	function __construct();

	/**
	 * @param array $arguments Original action/filter arguments.
	 * @return mixed
	 */
	function process_call( $arguments );

}