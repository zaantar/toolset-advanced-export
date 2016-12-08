<?php

namespace ToolsetExtraExport;

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

        $selected_sections = array_map( 'sanitize_text_field', toolset_ensarr( toolset_getarr( $_POST, 'selected_sections', [] ) ) );
        $output = apply_filters( 'toolset_export_extra_wordpress_data_json', null, $selected_sections );

        wp_send_json_success( [ 'message' => __( 'Export successful' ), 'output' => $output ] );
    }

}