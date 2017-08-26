<?php

/*
Plugin Name: Jolt Cache
Plugin URI: https://github.com/pcfreak30/jolt-cache
Description: A modern and modular WordPress cache plugin
Version: 0.1.0
Author: Derrick Hammer
Author URI: https://www.derrickhammer.com
License: GPL3
*/

/**
 * Init function shortcut
 */
function jolt_cache_init() {
	jolt_cache()->init();
}

/**
 * Activate function shortcut
 */
function jolt_cache_activate() {
	jolt_cache()->init()->activate();
}

/**
 * Deactivate function shortcut
 */
function jolt_cache_deactivate() {
	jolt_cache()->init()->deactivate();
}

/**
 * Error for older php
 */
function jolt_cache_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s requires a minimum PHP version of 5.4.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

/**
 * Error if vendors autoload is missing
 */
function jolt_cache_php_vendor_missing() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s is corrupted it seems, please re-install the plugin.</p>
	</div>', $info['Name']
		)
	);
}

/*
 * We want to use a fairly modern php version, feel free to increase the minimum requirement
 */
if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
	add_action( 'admin_notices', 'jolt_cache_php_upgrade_notice' );
} else {
	$autoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
	if ( file_exists( $autoload ) ) {
		include_once $autoload;
		add_action( 'plugins_loaded', 'jolt_cache_init', 11 );
		register_activation_hook( __FILE__, 'jolt_cache_activate' );
		register_deactivation_hook( __FILE__, 'jolt_cache_deactivate' );
	} else {
		add_action( 'admin_notices', 'jolt_cache_php_vendor_missing' );
	}
}
