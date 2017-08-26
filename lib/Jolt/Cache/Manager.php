<?php

namespace Jolt\Cache;

use pcfreak30\ComposePress\ManagerAbstract;

/**
 * Class Manager
 */
class Manager extends ManagerAbstract {
	/**
	 * @param string|null $name
	 *
	 * @return bool|\Jolt\Cache\Store\StoreAbstract
	 * @throws \Exception
	 */
	public function get_cache_store( $name = null ) {
		if ( empty( $name ) ) {
			$name = JOLT_CACHE_STORE;
		}
		$store = $this->get_module( ucfirst( $name ) );
		if ( empty( $store ) ) {
			throw new \Exception( sprintf( __( 'Cache store % does not exist' ), $name ) );
		}

		return $store;
	}
}