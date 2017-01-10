<?php

namespace ToolsetAdvancedExport\Gui;

use ToolsetAdvancedExport as e;


/**
 * Base page for rendering the import/export GUI.
 *
 * It can be used on a standalone page or in the Toolset Import/Export menu.
 *
 * @since 1.0
 */
abstract class Page_Import_Export {

	protected static $instance;

	private $is_js_model_data_rendered = false;


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
	 *
	 * @param string $what Determine what should be rendered: 'import'|'export'|'both'
	 * @since 1.0
	 */
	public function render( $what = 'both' ) {

		$context = $this->build_twig_context();

		$twig = $this->get_twig_environment();

		if( ! in_array( $what, ['import', 'export', 'both' ] ) ) {
			$what = 'both';
		}

		$output = $twig->render( "{$what}.twig", $context );

		echo $output;
	}


	/**
	 * Get the Twig context for the page.
	 *
	 * @return array
	 */
	private function build_twig_context() {

		$context = [
			'sections' => e\Data_Section::labels(),
		];

		// We need this only once even if the template is rendered twice.
		if( ! $this->is_js_model_data_rendered ) {
			$this->is_js_model_data_rendered = true;

			$js_model_data = [
				'preselected_sections' => e\Data_Section::values(),
				'ajax_nonce' => wp_create_nonce( e\Ajax::EXPORT_NONCE ),

				// Required for the async-upload.php
				'upload_nonce' => wp_create_nonce( 'media-form' ),
				'upload_url' => admin_url( 'async-upload.php' ),

				// Since we're already passing this to JS, no need for a separate wp_localize_script call and
				// another global JS variable.
				'l10n' => $this->localize_script()
			];

			$context['js_model_data'] = base64_encode( wp_json_encode( $js_model_data ) );
		}

		return $context;
	}


	private function localize_script() {
		return [
			'unknown_error' => __( 'An unknown error has happened.', 'toolset-advanced-export' ),
			'processing_import_file' => __( 'Processing the import file...', 'toolset-advanced-export' ),
			'uploading_import_file' => __( 'Uploading the import file...', 'toolset-advanced-export' )
		];
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
			$loader->addPath( TOOLSET_ADVANCED_EXPORT_ABSPATH . '/application/views/' );

			$twig = new \Twig_Environment( $loader );

			// Twig extensions
			//
			//
			$twig->addFunction( '__', new \Twig_SimpleFunction( '__', function( $text, $domain = 'toolset-advanced-export' ) {
				return __( $text, $domain );
			} ) );
		}

		return $twig;
	}


	protected function enqueue_scripts() {

        $is_script_debug_mode = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );

        $ko_version = '3.4.1';

        $ko_source = sprintf(
            '%s/public/knockout/knockout-%s%s.js',
            TOOLSET_ADVANCED_EXPORT_ABSURL,
            $ko_version,
            ( $is_script_debug_mode ? '.debug' : '' )
        );

        wp_register_script( 'knockout', $ko_source, [], $ko_version );

        // Provides the saveAs() function used in import_export.js
        wp_register_script(
            'filesaver',
            sprintf(
                '%s/public/filesaver/FileSaver%s.js',
                TOOLSET_ADVANCED_EXPORT_ABSURL,
                ( $is_script_debug_mode ? '' : '.min' )
            ),
            [],
            '1.3.3'
        );

        wp_register_script(
        	'knockout-file-bind',
	        TOOLSET_ADVANCED_EXPORT_ABSURL . '/public/knockout-file-bind.js',
	        [ 'knockout' ],
	        TOOLSET_ADVANCED_EXPORT_VERSION
        );

        wp_enqueue_script(
            'toolset_extra_export_page',
	        TOOLSET_ADVANCED_EXPORT_ABSURL . '/public/js/import_export.js',
            [ 'jquery', 'knockout', 'underscore', 'toolset-utils', 'filesaver', 'knockout-file-bind' ],
            TOOLSET_ADVANCED_EXPORT_VERSION
        );
    }



}