<?php

namespace ToolsetExtraExport\Gui;

use ToolsetExtraExport as e;


/**
 * Base page for rendering the import/export GUI.
 *
 * It can be used on a standalone page or in the Toolset Import/Export menu.
 *
 * @since 1.0
 */
abstract class Page_Import_Export {

	protected static $instance;


	/**
	 * @return Page_Import_Export
	 */
	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	final private function __clone() { }


	protected function __construct() { }


	public static function initialize() {
        $instance = self::get_instance();

        $instance->enqueue_scripts();
	}


	/**
	 * Render the page's markup.
	 */
	public function render() {

		$context = $this->build_twig_context();

		$twig = $this->get_twig_environment();

		$output = $twig->render( 'import_export.twig', $context );

		echo $output;
	}


	/**
	 * Get the Twig context for the page.
	 *
	 * @return array
	 */
	protected function build_twig_context() {

        $js_model_data = [
            'preselected_sections' => e\Data_Section::values(),
            'ajax_nonce' => wp_create_nonce( e\Ajax::EXPORT_NONCE )
        ];

		$context = [
			'sections' => e\Data_Section::labels(),
            'js_model_data' => base64_encode( wp_json_encode( $js_model_data ) )
		];

		return $context;
	}


	/**
	 * Get a configured Twig environment.
	 *
	 * @return \Twig_Environment
	 */
	protected function get_twig_environment() {

		static $twig;

		if( null == $twig ) {

			// If there is no Twig instance loaded yet, use the one packed with the plugin.
			if ( ! class_exists( '\Twig_Environment' ) ) {
				e\Customized_Twig_Autoloader::register( false );
			}

			$loader = new \Twig_Loader_Filesystem();
			$loader->addPath( TOOLSET_EXTRA_EXPORT_ABSPATH . '/application/views/' );

			$twig = new \Twig_Environment( $loader );

			// Twig extensions
			//
			//
			$twig->addFunction( '__', new \Twig_SimpleFunction( '__', function( $text, $domain = 'types' ) {
				return __( $text, $domain );
			} ) );
		}

		return $twig;
	}


	protected function enqueue_scripts() {
        wp_enqueue_script(
            'toolset_extra_export_page',
            TOOLSET_EXTRA_EXPORT_ABSURL . '/public/js/import_export.js',
            [ 'jquery', 'knockout', 'underscore', 'toolset-utils' ],
            TOOLSET_EXTRA_EXPORT_VERSION
        );
    }



}