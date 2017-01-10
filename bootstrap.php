<?php

// initial constants
define( 'TOOLSET_ADVANCED_EXPORT_VERSION', '1.0' );
define( 'TOOLSET_ADVANCED_EXPORT_ABSPATH', dirname( __FILE__ ) );
define( 'TOOLSET_ADVANCED_EXPORT_ABSURL', plugins_url() . '/' . basename( TOOLSET_ADVANCED_EXPORT_ABSPATH ) );

// kickstart the plugin
require_once TOOLSET_ADVANCED_EXPORT_ABSPATH . '/application/controllers/main.php';
\ToolsetAdvancedExport\Main::initialize();