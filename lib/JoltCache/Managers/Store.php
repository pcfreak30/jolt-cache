<?php

namespace JoltCache\Managers;

use ComposePress\Core\Abstracts\Manager;


/**
 * Class Manager
 */
class Store extends Manager {

	const MODULE_NAMESPACE = '\JoltCache\Store';

	protected $modules = [ 'File' ];

	/**
	 * @param string|null $name
	 *
	 * @return bool|\JoltCache\Abstracts\Store
	 * @throws \Exception
	 */
	public function get_cache_store( $name = null ) {
		if ( null === $name ) {
			$name = JOLT_CACHE_STORE;
		}
		$store = $this->get_module( ucfirst( strtolower( $name ) ) );
		if ( empty( $store ) ) {
			throw new \Exception( sprintf( __( 'Cache store % does not exist' ), $name ) );
		}

		return $store;
	}
}