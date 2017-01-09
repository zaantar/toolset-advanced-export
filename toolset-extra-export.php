<?php
/*
Plugin Name: Toolset Extra Export
Plugin URI: https://github.com/zaantar/wordpress-extra-export
Description:
Version: 1.0-dev
Author: OnTheGoSystems
Author URI: http://toolset.com
Text Domain: toolset-ee
Domain Path: /languages
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: zaantar/wordpress-extra-export
*/


function toolset_ee_is_environment_compatible() {
	return ( PHP_VERSION_ID >= 50400 );
}


function toolset_ee_low_php_version_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><?php _e( 'Toolset Extra Export requires PHP 5.4 or higher.', 'toolset-ee' ); ?></p>
	</div>
	<?php
}


function toolset_ee_environment_compatibility_check() {

	if( ! toolset_ee_is_environment_compatible() ) {
		add_action( 'admin_notices', 'toolset_ee_low_php_version_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

}


register_activation_hook( __FILE__, 'toolset_ee_environment_compatibility_check' );

add_action( 'admin_init', 'toolset_ee_environment_compatibility_check' );

if( toolset_ee_is_environment_compatible() ) {
	require_once dirname( __FILE__ ) . '/bootstrap.php';
}