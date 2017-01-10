<?php

namespace ToolsetAdvancedExport;

/**
 * Public hook API.
 *
 * This should be the only point where other plugins (incl. Toolset) interact with Toolset Extra Export directly.
 *
 * Note: The API is initialized on init with priority 10.
 *
 * When implementing filter hooks, please follow these rules:
 *
 * 1.  All filter names are automatically prefixed with 'toolset_'. Only lowercase characters and underscores
 *     can be used.
 * 2.  Filter names (without a prefix) should be defined in self::$callbacks.
 * 3.  For each filter, there should be a dedicated class implementing the
 *     \ToolsetAdvancedExport\ApiHandlers\Api_Handler_Interface. Name of the class must be
 *     \ToolsetAdvancedExport\ApiHandlers\{$capitalized_filter_name}. So for example, for a hook to
 *     'toolset_import_from_zip_file' you need to create a class
 *     '\ToolsetAdvancedExport\ApiHandlers\Import_From_Zip_File'.
 *
 * @since 1.0
 */
final class Api {

	private static $instance;

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __clone() { }

	private function __construct() { }



	public static function initialize() {
		$instance = self::get_instance();

		$instance->register_callbacks();
	}


	/** Prefix for the callback method name */
	const CALLBACK_PREFIX = 'callback_';

	/** Prefix for the handler class name */
	const HANDLER_CLASS_PREFIX = '\\ToolsetAdvancedExport\\ApiHandlers\\';

	const HOOK_PREFIX = 'toolset_';

	const DELIMITER = '_';


	private $callbacks_registered = false;


	/**
	 * @var array Filter names (without prefix) as keys, filter parameters as values:
	 *     - int $args: Number of arguments of the filter
	 */
	private static $callbacks = [
		'export_extra_wordpress_data_raw' => [ 'args' => 2 ],
		'export_extra_wordpress_data' => [ 'args' => 2 ],
		'export_extra_wordpress_data_json' => [ 'args' => 2 ],
        'import_extra_wordpress_data' => [ 'args' => 3 ],
		'import_extra_wordpress_data_zip' => [ 'args' => 3 ]
	];


	private function register_callbacks() {

		if( $this->callbacks_registered ) {
			return;
		}

		foreach( self::$callbacks as $callback_name => $args ) {

			$argument_count = toolset_getarr( $args, 'args', 1 );

			add_filter( self::HOOK_PREFIX . $callback_name, array( $this, self::CALLBACK_PREFIX . $callback_name ), 10, $argument_count );
		}

		$this->callbacks_registered = true;

	}


	/**
	 * Handle a call to undefined method on this class, hopefully an action/filter call.
	 *
	 * @param string $name Method name.
	 * @param array $parameters Method parameters.
	 * @since 2.1
	 * @return mixed
	 */
	public function __call( $name, $parameters ) {

		$default_return_value = toolset_getarr( $parameters, 0, null );

		// Check for the callback prefix in the method name
		$name_parts = explode( self::DELIMITER, $name );
		if( 0 !== strcmp( $name_parts[0] . self::DELIMITER, self::CALLBACK_PREFIX ) ) {
			// Not a callback, resign.
			return $default_return_value;
		}

		// Deduct the handler class name from the callback name
		unset( $name_parts[0] );
		$class_name = implode( self::DELIMITER, $name_parts );
		$class_name = strtolower( $class_name );
		$class_name = mb_convert_case( $class_name, MB_CASE_TITLE );
		$class_name = self::HANDLER_CLASS_PREFIX . $class_name;

		// Obtain an instance of the handler class.
		try {
			/** @var \ToolsetAdvancedExport\ApiHandlers\Api_Handler_Interface $handler */
			$handler = new $class_name();
		} catch( \Exception $e ) {
			// The handler class could not have been instantiated, resign.
			return $default_return_value;
		}

		// Success
		return $handler->process_call( $parameters );
	}

}
