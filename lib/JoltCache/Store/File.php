<?php


namespace JoltCache\Store;


use ComposePress\Settings\Abstracts\Page;
use JoltCache\Abstracts\Store;

class File extends Store {
	const NAME = 'file';
	const FRIENDLY_NAME = 'Filesystem';

	public function purge_url( $url ) {
		if ( $this->plugin->wp_filesystem->is_file( $url ) ) {
			$this->plugin->wp_filesystem->delete( $this->plugin->config->cache_path . $this->sanitize_url( $url ) );
		}
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public function sanitize_url( $url ) {
		$url = str_replace( [ 'http://', 'https://', '.' ], [ '', '', '_' ], $url );

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public function get_url( $url ) {
		return @file_get_contents( $this->get_url_path( $url ) );
	}

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	public function get_url_path( $url ) {
		$filename         = 'index';
		$request_uri_path = $this->plugin->config->cache_path . $this->plugin->config->cache_host . rtrim( $url, '/' );
		$request_uri_path = preg_replace_callback( '/%[0-9A-F]{2}/', function ( $match ) {
			return array_shift( $match );
		}, $request_uri_path );

		return $request_uri_path . '/' . $filename . '.html';
	}

	/**
	 * @param $url
	 *
	 * @return bool|int
	 */
	public function get_url_modified_time( $url ) {
		return @filemtime( $this->get_url_path( $url ) );
	}

	/**
	 * @param string $url
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function save_url( $url, $content ) {
		$filename = $this->get_url_path( $url );
		$dirname  = dirname( $filename );

		if ( ! $this->plugin->wp_filesystem->is_dir( $dirname ) ) {
			$this->mkdir( $dirname );
		}
		$this->plugin->wp_filesystem->put_contents( $filename, $content );
		if ( function_exists( 'gzencode' ) ) {
			$this->plugin->wp_filesystem->put_contents( $filename . '_gzip', gzencode( $content, apply_filters( 'jolt_cache_gzencode_level_compression', 3 ) ) );
		}
	}

	private function mkdir( $target ) {
		$filesystem = $this->plugin->wp_filesystem;
		// from php.net/mkdir user contributed notes.
		$target = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $target );

		// safe mode fails with a trailing slash under certain PHP versions.
		$target = untrailingslashit( $target );
		if ( empty( $target ) ) {
			$target = DIRECTORY_SEPARATOR;
		}

		if ( $filesystem->exists( $target ) ) {
			return $filesystem->is_dir( $target );
		}

		// Attempting to create the directory may clutter up our display.
		if ( $filesystem->mkdir( $target ) ) {
			return true;
		}
		if ( $filesystem->is_dir( dirname( $target ) ) ) {
			return false;
		}

		// If the above failed, attempt to create the parent node, then try again.
		if ( ( DIRECTORY_SEPARATOR !== $target ) && ( $this->mkdir( dirname( $target ) ) ) ) {
			return $filesystem->mkdir( $target );
		}

		return false;
	}

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	public function url_exists( $url ) {
		$filename = $this->get_url_path( $url );

		return $this->plugin->wp_filesystem->is_file( $filename );
	}

	/**
	 * @return bool
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * @param \ComposePress\Settings\Abstracts\Page $page
	 *
	 * @return void
	 */
	public function register_settings( Page $page ) {
		// TODO: Implement register_settings() method.
	}
}