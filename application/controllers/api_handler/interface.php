<?php

namespace ToolsetExtraExport;

/**
 * Interface for handlers of hook API calls.
 */
interface Api_Handler_Interface {

	function __construct();

	/**
	 * @param array $arguments Original action/filter arguments.
	 * @return mixed
	 */
	function process_call( $arguments );

}