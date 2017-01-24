<?php
/*
Plugin Name: Toolset Advanced Export
Plugin URI: https://github.com/zaantar/toolset-advanced-export
Description: Export and import additional site settings and content that is not included in the standard WordPress export file.
Version: 1.0
Author: OnTheGoSystems
Author URI: http://toolset.com
Text Domain: toolset-advanced-export
Domain Path: /languages
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: zaantar/toolset-advanced-export
*/


function toolset_advanced_export_is_environment_compatible() {
	return ( PHP_VERSION_ID >= 50400 );
}


function toolset_advanced_export_low_php_version_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php _e( 'Toolset Advanced Export requires PHP 5.4 or higher.', 'toolset-advanced-export' ); ?></p>
	</div>
	<?php
}


function toolset_advanced_export_environment_compatibility_check() {

	if( ! toolset_advanced_export_is_environment_compatible() ) {
		add_action( 'admin_notices', 'toolset_advanced_export_low_php_version_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

}


register_activation_hook( __FILE__, 'toolset_advanced_export_environment_compatibility_check' );

add_action( 'admin_init', 'toolset_advanced_export_environment_compatibility_check' );

if( toolset_advanced_export_is_environment_compatible() ) {
	require_once dirname( __FILE__ ) . '/bootstrap.php';
}