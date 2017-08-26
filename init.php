<?php

use Dice\Dice;


/**
 * Singleton instance function. We will not use a global at all as that defeats the purpose of a singleton and is a bad design overall
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @return \Jolt
 */
function jolt_cache() {
	return jolt_cache_container()->create( '\Jolt' );
}

/**
 * This container singleton enables you to setup unit testing by passing an environment filw to map classes in Dice
 *
 * @param string $env
 *
 * @return \Dice\Dice
 */
function jolt_cache_container( $env = 'prod' ) {
	static $container;
	if ( empty( $container ) ) {
		$container = new Dice();
		include __DIR__ . "/config_{$env}.php";
	}

	return $container;
}