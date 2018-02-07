<?php

function jolt_cache_init_autoloader() {
	static $axel;
	if ( empty( $axel ) ) {
		$ds = DIRECTORY_SEPARATOR;
		require_once __DIR__ . "{$ds}vendor{$ds}level-2{$ds}axel{$ds}axel.php";
		require_once __DIR__ . "{$ds}vendor{$ds}level-2{$ds}simplecache{$ds}SimpleCache.php";
		require_once __DIR__ . "{$ds}init.php";
		$dir = getcwd();
		chdir( __DIR__ );
		$cache_dir = __DIR__ . "{$ds}vendor{$ds}cache";
		$use_cache = ! ( ! is_dir( $cache_dir ) && ! mkdir( $cache_dir, 0755 ) && ! is_dir( $cache_dir ) );
		$cache     = $use_cache ? new SimpleCache\SimpleCache( __DIR__ . "{$ds}vendor{$ds}cache" ) : null;
		$axel      = new \Axel\Axel( $cache, 'autoload_paths.dat' );
		$axel->addModule( new Axel\Module\Composer( $axel, "./{$ds}vendor", 'composer.json' ) );
		$axel->addModule( new Axel\Module\PSR4( 'lib', 'JoltCache\JoltCache' ) );
		chdir( $dir );
	}
}

jolt_cache_init_autoloader();