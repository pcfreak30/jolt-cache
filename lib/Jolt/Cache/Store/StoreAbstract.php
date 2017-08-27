<?php

namespace Jolt\Cache\Store;

use pcfreak30\ComposePress\ComponentAbstract;

abstract class StoreAbstract extends ComponentAbstract {
	const NAME = '';
	const FRIENDLY_NAME = '';

	/**
	 *
	 */
	public function init() {
		if ( $this->is_supported() ) {
			add_filter( 'jolt_cache_stores', [ $this, 'register' ] );
		}
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

	abstract public function purge_url( $url );

	/**
	 * @param $url
	 *
	 * @return string
	 */
	abstract public function sanitize_url( $url );

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	abstract public function get_url( $url );

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	abstract public function url_exists( $url );

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	abstract public function get_url_modified_time( $url );

	/**
	 * @param string $url
	 *
	 * @param string $content
	 *
	 * @return mixed
	 */
	abstract public function save_url( $url, $content );

	/**
	 * @return bool
	 */
	abstract public function is_supported();
}