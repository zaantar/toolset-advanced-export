<?php

namespace ToolsetExtraExport;

final class Main {

	private static $instance;

	public static function initialize() {
		if( null == self::$instance ) {
			self::$instance = new self();
		}
	}


	private function __construct() {

		// Register autoloaded classes.
		//
		// If toolset-common is available, use its autoloader to improve performance. Otherwise we'll register
	    // our own.
		//
		// Priority is set to 11 because Toolset Common is initialized at 10.
		add_action( 'after_setup_theme', function() {

			// It is possible to regenerate the classmap with Zend framework, for example:
			//
			// cd application
			// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
			$classmap = include( TOOLSET_EXTRA_EXPORT_ABSPATH . '/application/autoload_classmap.php' );

			if( apply_filters( 'toolset_is_toolset_common_available', false ) ) {
				// Use Toolset_Common_Autoloader to improve performace
				do_action( 'toolset_register_classmap', $classmap );
			} else {
				// Fallback to a standalone autoloader
				require_once TOOLSET_EXTRA_EXPORT_ABSPATH . '/application/autoloader.php';
				Autoloader::initialize();
				$autoloader = Autoloader::get_instance();
				$autoloader->register_classmap( $classmap );
			}

		}, 11 );

		add_filter( 'is_toolset_extra_export_available', '__return_true' );

		add_action( 'init', array( $this, 'on_init' ) );
	}


	private function __clone() { }


	public function on_init() {
		Api::initialize();
	}

}