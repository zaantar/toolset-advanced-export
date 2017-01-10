<?php

namespace ToolsetAdvancedExport;

/**
 * Main plugin controller.
 *
 * Note that this is loaded very early but other classes become available only at after_setup_theme:11 because
 * of the autoloader.
 *
 * @since 1.0
 */
final class Main {

	private static $instance;

	public static function initialize() {
		if( null == self::$instance ) {
			self::$instance = new self();
		}
	}


	// Slug of the admin menu item
	const MENU_SLUG = 'toolset-extra-export';


	private function __construct() {


		require_once TOOLSET_ADVANCED_EXPORT_ABSPATH . '/application/functions.php';


		// Register autoloaded classes.
		//
		// If toolset-common is available, use its autoloader to improve performance. Otherwise we'll register
	    // our own.
		//
		// Priority is set to 11 because Toolset Common is initialized at 10.
		\add_action( 'after_setup_theme', function() {

			// It is possible to regenerate the classmap with Zend framework, for example:
			//
			// cd application
			// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
			$classmap = include( TOOLSET_ADVANCED_EXPORT_ABSPATH . '/application/autoload_classmap.php' );

			if( \apply_filters( 'toolset_is_toolset_common_available', false ) ) {
				// Use Toolset_Common_Autoloader to improve performace
				\do_action( 'toolset_register_classmap', $classmap );
			} else {
				// Fallback to a standalone autoloader
				require_once TOOLSET_ADVANCED_EXPORT_ABSPATH . '/application/autoloader.php';
				Autoloader::initialize();
				$autoloader = Autoloader::get_instance();
				$autoloader->register_classmap( $classmap );
			}

			$this->finish_initialization();

		}, 11 );
	}


	private function finish_initialization() {

        // On every request, we only need to initialize the filter hook API and AJAX callback handlers.
        //
        //
        \add_action( 'init', function() {
            Api::initialize();

            if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                Ajax::initialize();
            }
        } );


        // Indicate that the API is available
        //
        //
        \add_filter( 'is_toolset_extra_export_available', '__return_true' );


        // Add a standalone (sub)menu item under Tools
        //
        // Not yet.
        /*\add_action( 'admin_menu', function() {

            $import_export_page_hook = \add_submenu_page(
                'tools.php',
                __( 'Toolset Extra Export', 'toolset-advanced-export' ),
                __( 'Toolset Extra Export', 'toolset-advanced-export' ),
                'manage_options',
                // Not referencing the page controller class directly so its file is loaded only when we actually need it
                self::MENU_SLUG,
                function() {
                    $page = Page_Tools::get_instance();
                    $page->render();
                }
            );

            \add_action( 'admin_enqueue_scripts', function( $hook ) use( $import_export_page_hook ) {
                if( $import_export_page_hook == $hook ) {
                    Page_Tools::initialize();
                }
            } );

        } );*/


        // Filter priority determines the order of tabs, this is documented in Toolset_Export_Import_Screen.
        //
        //
        \add_filter( 'toolset_filter_register_export_import_section', function( $sections ) {
            Gui\Toolset_Ie_Section::initialize();

            return Gui\Toolset_Ie_Section::get_instance()->register( $sections );
        }, 100 );


        // TODO also consider hooking into register_importer in some way

    }


	private function __clone() { }

}