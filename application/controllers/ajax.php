<?php

namespace ToolsetAdvancedExport;

use ToolsetAdvancedExport\Utils as utils;

/**
 * Registers and handles AJAX callbacks.
 *
 * @since 1.0
 */
class Ajax {

    private static $instance;

    public static function initialize() {
        if( null == self::$instance ) {
            self::$instance = new self();
        }

        // Not giving away the instance on purpose.
    }


    private function __clone() { }


    const EXPORT_NONCE = 'toolset_advanced_export_do_export';


    private function __construct() {
        $this->register_callbacks();
    }


    private function register_callbacks() {
        add_action( 'wp_ajax_toolset_advanced_export_do_export', [ $this, 'export' ] );
        add_action( 'wp_ajax_toolset_advanced_export_do_import', [ $this, 'import' ] );
    }


    private function verify_nonce() {
	    if( ! wp_verify_nonce( toolset_getarr( $_POST, 'wpnonce' ), self::EXPORT_NONCE ) ) {
		    wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'toolset-advanced-export' ) ] );
		    die;
	    };
    }


    /**
     * toolset_advanced_export_do_export callback
     *
     * Generates an JSON export file in a ZIP archive and renders the contents of the ZIP archive as
     * a base64-encoded string.
     *
     * Expects following POST variables:
     * - wpnonce: A valid toolset_advanced_export_do_export nonce.
     * - selected_sections: An array of section names that should be exported.
     *
     * @since 1.0
     */
    public function export() {

        $this->verify_nonce();

        try{

            $selected_sections = array_map( 'sanitize_text_field', toolset_ensarr( toolset_getarr( $_POST, 'selected_sections', [] ) ) );

            /**
             * toolset_advanced_export_preserve_file
             *
             * Allow for forcing us to preserve the export file and provide a link to it.
             */
            $preserve_file = (bool) apply_filters(
                'toolset_advanced_export_preserve_file',
                ( 'link' == toolset_getarr( $_POST, 'export_method', 'link' ) )
            );

            $exporter = new Exporter( [ Exporter::ARGUMENT_SECTIONS => $selected_sections ] );

            // Force file name if we need a link afterwards.
            $zip = $exporter->get_zip( $preserve_file, ( $preserve_file ? utils\generate_export_file_name() : null ) );

            $results = [
                'message' => __( 'Export successful' ),
                'output' => base64_encode( $zip['file'] ),
	            'fileName' => utils\generate_export_file_name()
            ];

            // If we have a link, we need to convert its absolute path to URL and send it as well.
            if( $preserve_file ) {
                $full_path = $zip['path'];
                if( substr( $full_path, 0, strlen( ABSPATH ) ) !== ABSPATH ) {
                    throw new \RuntimeException( 'Cannot generate download link.' );
                }
                $relative_path = substr( $full_path, strlen( untrailingslashit( ABSPATH ) ) );
                $file_url = site_url() . $relative_path;
                $results['link'] = $file_url;
            }

            wp_send_json_success( $results );

        } catch( \Exception $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'An error ocurred during the export: %s', 'toolset-advanced-export' ), $e->getMessage() )
            ] );
        }

        die;
    }


	/**
	 * toolset_advanced_export_do_import callback
	 *
	 * Grabs a ZIP file previously uploaded as an attachment, uses the API hook to import it and deletes it after.
	 *
	 * Expects following POST variables:
	 * - wpnonce: A valid toolset_advanced_export_do_export nonce.
	 * - attachment_id: ID of the attachment with the import zip file.
	 *
	 * @since 1.0
	 */
    public function import() {

    	$this->verify_nonce();

	    try{

	    	// Get the path and check the file exists
	    	$zip_attachment_id = (int) toolset_getarr( $_POST, 'attachment_id' );
			$zip_path = get_attached_file( $zip_attachment_id );

			if( false == $zip_path || ! file_exists( $zip_path ) ) {
				throw new \InvalidArgumentException( sprintf(
					__( 'The file was not correctly uploaded. Cannot locate the attachment %d in "%s".', 'toolset-advanced-export' ),
					$zip_attachment_id,
					$zip_path
				) );
			}

			$results = new \Toolset_Result_Set();

			$results->add(
				apply_filters( 'toolset_import_extra_wordpress_data_zip', false, $zip_path, [ 'all' ] )
			);

			// Clean up always
		    wp_delete_attachment( $zip_attachment_id );

	    	wp_send_json([
	    		'success' => $results->is_complete_success(),
			    'data' => [
				    'message' => wp_kses_post( $results->concat_messages( '<br />' ) )
			    ]
		    ]);

	    } catch( \Exception $e ) {
		    wp_send_json_error( [
			    'message' => sprintf( __( 'An error ocurred during the export: %s', 'toolset-advanced-export' ), $e->getMessage() )
		    ] );
	    }

	    die;
    }

}