<?php

namespace Jolt\Cache\Store;

use pcfreak30\ComposePress\ComponentAbstract;

class StoreAbstract extends ComponentAbstract {
	const NAME = '';
	const FRIENDLY_NAME = '';

	/**
	 *
	 */
	public function init() {
		add_filter( 'jolt_cache_stores', [ $this, 'register' ] );
	}

	public function register( $stores ) {
		$stores[ $this->get_name() ] = $this->get_friendly_name();

		return $stores;
	}

	public function get_name() {
		return static::NAME;
	}

	public function get_friendly_name() {
		return __( static::FRIENDLY_NAME, $this->plugin->get_safe_slug() );
	}
}