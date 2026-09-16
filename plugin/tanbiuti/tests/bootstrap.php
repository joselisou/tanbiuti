<?php
/**
 * PHPUnit bootstrap — loads the WP core test suite (provided by wp-env's tests-cli container)
 * and this plugin, following the standard wp-cli `scaffold plugin-tests` layout.
 *
 * @package Tanbiuti
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Loads the plugin under test.
 */
function _tanbiuti_manually_load_plugin() {
	require dirname( __DIR__ ) . '/tanbiuti.php';
}
tests_add_filter( 'muplugins_loaded', '_tanbiuti_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
