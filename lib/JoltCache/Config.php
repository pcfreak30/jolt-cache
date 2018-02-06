<?php


namespace JoltCache;

use ComposePress\Core\Abstracts\Component;

/**
 * Class Config
 *
 * @package Jolt\Cache
 * @property \JoltCache $plugin
 * @property string     $cache_base_path
 * @property string     $cache_path
 * @property string     $cache_host
 * @property string     $wp_config_path
 */
class Config extends Component {
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
	 * @var string
	 */
	private $config_folder;

	/**
	 *
	 */
	public function init() {
		$this->cache_base_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'jolt-cache' . DIRECTORY_SEPARATOR;
		$this->cache_path      = $this->cache_base_path . 'files' . DIRECTORY_SEPARATOR;
		$this->config_folder   = realpath( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'jolt-cache-config' ) . DIRECTORY_SEPARATOR;
		$this->wp_config_path  = $this->find_wp_config();
		$host                  = ( isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : '';
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

	/**
	 *
	 */
	public function save() {
		$options = [
			'store' => $this->plugin->settings->get( 'general.cache_store' ),
		];

		$options = apply_filters( "{$this->plugin->safe_slug}_build_config", $options );

		$config = $this->plugin->templates->get( 'config', [
			'options' => $options,
		] );

		$domain = strtolower( parse_url( site_url(), PHP_URL_HOST ) );
		$path   = trim( parse_url( site_url(), PHP_URL_PATH ) );
		if ( '/' === $path ) {
			$path = '';
		}
		$path = str_replace( '/', '.', $path );
		if ( ! $this->plugin->wp_filesystem->is_dir( $this->config_folder ) ) {
			$this->plugin->wp_filesystem->mkdir( $this->config_folder );
		}
		$config_file = $this->config_folder . $domain . $path . '.php';

		$this->plugin->wp_filesystem->put_contents( $config_file, $config );
	}

	/**
	 * @return bool|mixed
	 */
	public function load() {
		$config_base = $this->config_folder . $this->cache_host;
		$config_file = $config_base . '.php';
		if ( $config_file && 0 === stripos( $config_file, $this->config_folder ) ) {
			return include $config_file;
		}
		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		$path = explode( '%2F', trim( rawurlencode( $path ), '%2F' ) );

		$directory = '';

		foreach ( $path as $p ) {
			$files = array_filter( [
				$config_base . '.' . $p . '.php',
				$config_base . '.' . $directory . $p . '.php',
			], 'is_file' );
			if ( 0 < count( $files ) ) {
				$config_file = array_shift( $files );

				return include $config_file;
			}
			$directory .= $p . '.';
		}

		return false;
	}
}