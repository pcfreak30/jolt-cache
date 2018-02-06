<?php

namespace JoltCache\Abstracts;


use ComposePress\Core\Abstracts\Component;
use ComposePress\Settings\Abstracts\Page;

/**
 * Class Store
 *
 * @package JoltCache\Abstracts
 * @property string $friendly_name
 * @property string $name
 */
abstract class Store extends Component {
	/**
	 *
	 */
	const NAME = '';
	/**
	 *
	 */
	const FRIENDLY_NAME = '';

	const TYPE = '';

	const TYPE_INTERNAL = 'internal';

	const TYPE_EXTERNAL = 'external';

	const TYPE_3RDPARTY = '3rdparty';

	/**
	 *
	 */
	public function init() {
		if ( $this->is_supported() ) {
			add_filter( "{$this->plugin->safe_slug}_stores", [ $this, 'register' ] );
		}
	}

	/**
	 * @param $stores
	 *
	 * @return mixed
	 */
	public function register( $stores ) {
		$stores[ $this->get_name() ] = $this;

		return $stores;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return static::NAME;
	}

	/**
	 * @return string
	 */
	public function get_friendly_name() {
		return __( static::FRIENDLY_NAME, $this->plugin->safe_slug );
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return __( static::TYPE, $this->plugin->safe_slug );
	}

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
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

	/**
	 * @param \ComposePress\Settings\Abstracts\Page $page
	 *
	 * @return void
	 */
	abstract public function register_settings( Page $page );
}