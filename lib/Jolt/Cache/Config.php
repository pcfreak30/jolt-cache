<?php


namespace Jolt\Cache;

use pcfreak30\ComposePress\ComponentAbstract;


/**
 * Class Config
 *
 * @package Jolt\Cache
 * @property \Jolt  $plugin
 * @property string $cache_base_path
 * @property string $cache_path
 * @property string $cache_host
 * @property string $wp_config_path
 */
class Config extends ComponentAbstract {
	/**
	 * @var string
	 */
	private $cache_path;

	/**
	 * @var string
	 */
	private $cache_base_path;

	/**
	 * @var string
	 */
	private $wp_config_path;
	/**
	 * @var string
	 */
	private $cache_host;

	/**
	 *
	 */
	public function init() {
		$this->cache_base_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'jolt' . DIRECTORY_SEPARATOR;
		$this->cache_path      = $this->cache_base_path . 'files' . DIRECTORY_SEPARATOR;
		$this->wp_config_path  = $this->find_wp_config();
		$host                  = ( isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : time();
		$host                  = trim( strtolower( $host ), '.' );
		$this->cache_host      = urlencode( $host );

	}

	/**
	 * @return bool|string
	 */
	private function find_wp_config() {
		if ( $this->plugin->early_load ) {
			return $this->find_wp_config_early();
		}
		$config_file     = ABSPATH . 'wp-config.php';
		$config_file_alt = dirname( ABSPATH ) . DIRECTORY_SEPARATOR . 'wp-config.php';
		if ( file_exists( $config_file ) && $this->plugin->get_wp_filesystem()->is_writable( $config_file ) ) {
			return $config_file;
		}
		if ( file_exists( $config_file_alt ) && $this->plugin->get_wp_filesystem()->is_writable( $config_file_alt ) && ! $this->plugin->get_wp_filesystem()->is_file( dirname( ABSPATH ) . DIRECTORY_SEPARATOR . 'wp-settings.php' ) ) {
			return $config_file_alt;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	private function find_wp_config_early() {
		$config_file     = ABSPATH . 'wp-config.php';
		$config_file_alt = dirname( ABSPATH ) . DIRECTORY_SEPARATOR . 'wp-config.php';
		if ( file_exists( $config_file ) && @is_writable( $config_file ) ) {
			return $config_file;
		}
		if ( file_exists( $config_file_alt ) && @is_writable( $config_file_alt ) && ! @is_file( dirname( ABSPATH ) . DIRECTORY_SEPARATOR . 'wp-settings.php' ) ) {
			return $config_file_alt;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function get_cache_path() {
		return $this->cache_path;
	}

	/**
	 * @return mixed
	 */
	public function get_wp_config_path() {
		return $this->wp_config_path;
	}

	/**
	 * @return string
	 */
	public function get_cache_base_path() {
		return $this->cache_base_path;
	}

	/**
	 * @return string
	 */
	public function get_cache_host() {
		return $this->cache_host;
	}
}