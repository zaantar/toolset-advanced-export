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
GitHub Plugin URI: zaantar/tcl-status
*/

// initial constants
define( 'TOOLSET_EXTRA_EXPORT_VERSION', '1.0' );
define( 'TOOLSET_EXTRA_EXPORT_ABSPATH', dirname( __FILE__ ) );


// kickstart the plugin
require_once TOOLSET_EXTRA_EXPORT_ABSPATH . '/application/controllers/main.php';
\ToolsetExtraExport\Main::initialize();