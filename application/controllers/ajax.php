<?php

namespace ToolsetExtraExport;

use Mockery\CountValidator\Exception;

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


    const EXPORT_NONCE = 'toolset_ee_export';


    private function __construct() {
        $this->register_callbacks();
    }


    private function register_callbacks() {

        add_action( 'wp_ajax_toolset_ee_export', [ $this, 'export' ] );

    }


    /**
     * toolset_ee_export callback
     *
     * Generates an JSON export file in a ZIP archive and renders the contents of the ZIP archive as
     * a base64-encoded string.
     *
     * Expects following POST variables:
     * - wpnonce: A valid toolset_ee_export nonce.
     * - selected_sections: An array of section names that should be exported.
     *
     * @since 1.0
     */
    public function export() {

        if( ! wp_verify_nonce( toolset_getarr( $_POST, 'wpnonce' ), self::EXPORT_NONCE ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'toolset-ee' ) ] );
            die;
        };

        try{

            $selected_sections = array_map( 'sanitize_text_field', toolset_ensarr( toolset_getarr( $_POST, 'selected_sections', [] ) ) );

            /**
             * toolset_extra_export_preserve_file
             *
             * Allow for forcing us to preserve the export file and provide a link to it.
             */
            $preserve_file = (bool) apply_filters(
                'toolset_extra_export_preserve_file',
                ( 'link' == toolset_getarr( $_POST, 'export_method', 'link' ) )
            );

            $exporter = new Exporter( [ Exporter::ARGUMENT_SECTIONS => $selected_sections ] );

            // Force file name if we need a link afterwards.
            $zip = $exporter->get_zip( $preserve_file, ( $preserve_file ? 'toolset_extra_export.zip' : null ) );

            $results = [
                'message' => __( 'Export successful' ),
                'output' => base64_encode( $zip['file'] )
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

        } catch( Exception $e ) {
            wp_send_json_error( [
                'message' => sprintf( __( 'An error ocurred during the export: %s', 'toolset-ee' ), $e->getMessage() )
            ] );
        }

        die;
    }

}