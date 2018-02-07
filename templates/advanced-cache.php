<?php
/* @var string $composer */
echo '<?php'; ?>

defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
	return;
}

define( 'JOLT_ADVANCED_CACHE', true );
if ( file_exists( '<?= $bootstrap ?>' ) ) {
	require_once '<?= $bootstrap ?>';
}
if ( ! file_exists( '<?= $bootstrap ?>' ) ) {
	define( 'JOLT_ADVANCED_CACHE_FAIL', true );
}

if ( is_admin() ) {
	return false;
}

jolt_cache()->init()->request->process();