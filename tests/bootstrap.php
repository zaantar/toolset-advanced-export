<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	// This needs to match the default directory in install.sh.
	$_tests_dir = dirname( __FILE__ ) . '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// this needs to go before including the bootstrap file below
function _manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../toolset-extra-export.php';
}

require_once $_tests_dir . '/includes/bootstrap.php';

// WP_Mock requires composer autoloader
require_once dirname( __FILE__ ) . '/../vendor/autoload.php';
WP_Mock::bootstrap();

require_once dirname( __FILE__ ) . '/test_case.php';


