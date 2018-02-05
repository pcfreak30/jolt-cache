<?php

namespace JoltCache;

use ComposePress\Core\Abstracts\Component;
use QueryPath\DOMQuery;

/**
 * Class Request
 *
 * @package Jolt\Cache
 * @property \JoltCache $plugin
 */
class Request extends Component {

	private $request_uri;

	/**
	 *
	 */
	public function init() {

	}

	/**
	 *
	 */
	public function process() {
		$stop = false;
		// Don't cache robots.txt && .htaccess directory (it's happened sometimes with weird server configuration).
		if ( false !== stripos( $_SERVER['REQUEST_URI'], 'robots.txt' ) || false !== stripos( $_SERVER['REQUEST_URI'], '.htaccess' ) ) {
			$stop = true;
		}

		$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		// Don't cache disallowed extensions.
		if ( strtolower( $_SERVER['REQUEST_URI'] ) !== '/index.php' && in_array( pathinfo( $request_uri, PATHINFO_EXTENSION ), apply_filters( 'jolt_cache_dallowed_extensions', [
				'php',
				'xml',
				'xsl',
			] ), true ) ) {
			$stop = true;
		}

		// Don't cache if user is in admin.
		if ( is_admin() ) {
			$stop = true;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$stop = true;
		}
		if ( $stop ) {
			return;
		}
		$this->request_uri = $request_uri;
		/** @var \JoltCache\Abstracts\Store $store */
		$store = $this->plugin->cache_manager->get_cache_store();
		$cache = $store->get_url( $this->request_uri );
		if ( ! empty( $cache ) ) {
			$time = $store->get_url_modified_time( $this->request_uri );
			$this->serve_cache( $cache, $time );
		}
		ob_start( [ $this, 'process_cache_buffer' ] );
	}

	private function serve_cache( $cache, $time ) {

		$http_if_modified_since = false;
		$fragment_cache         = $cache;
		if ( has_filter( 'jolt_cache_fragment_buffer' ) ) {
			/** @var \QueryPath\DOMQuery $fragment_cache */
			$fragment_cache = apply_filters( 'jolt_cache_fragment_buffer', html5qp( $cache ) );
			$fragment_cache = apply_filters( 'jolt_cache_after_fragment_buffer', $fragment_cache->html5() );
			/** @var string $fragment_cache */
		}

		if ( $fragment_cache === $cache ) {
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers                = apache_request_headers();
				$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			} else {
				$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			}

		}

		// Getting If-Modified-Since headers sent by the client.

		// Checking if the client is validating his cache and if it is current.
		if ( $http_if_modified_since && ( new \DateTime( $http_if_modified_since ) >= new \DateTime( "@{$time}" ) ) ) {
			// Client's cache is current, so we just respond '304 Not Modified'.
			header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
			exit;
		}

		// Serve the cache if browse cache last fetch is newer oe equal to the last modification time.
		echo $fragment_cache;
		exit;
	}

	/**
	 * @param string $cache
	 */
	private function process_cache_buffer( $cache ) {
		$is_html = false;

		if ( preg_match( '/(<\/html>)/i', $cache ) ) {
			$cache   = apply_filters( 'jolt_cache_buffer', html5qp( $cache ) );
			$is_html = true;
		}
		if ( $is_html && ! ( $cache instanceof DOMQuery ) ) {
			$cache = html5qp( $cache );
		}
		if ( apply_filters( 'jolt_cache_store_cache', true ) ) {
			/** @var \JoltCache\Abstracts\Store $store */
			$store = $this->plugin->cache_manager->get_cache_store();

			$cache = $cache->html5();
			$cache = apply_filters( 'jolt_cache_post_buffer', $cache );

			/** @var string $cache */
			if ( $is_html ) {
				$cache = preg_replace( '/(<\/html>.*)/is', $this->get_cache_footprint() . '$1', $cache );
			}

			$store->save_url( $this->request_uri, $cache );

			if ( $store->url_exists( $this->request_uri ) ) {
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $store->get_url_modified_time( $this->request_uri ) ) . ' GMT' );
			}
		}
		/** @var DOMQuery $cache */
		if ( $is_html && ( $cache instanceof DOMQuery ) ) {
			$cache = $cache->html5();
		}
		if ( ! $is_html ) {
			$cache .= $this->get_cache_footprint( false );
		}

		return $cache;
	}

	private function get_cache_footprint( $debug = true ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$footprint =
			"\n" . sprintf( __( '<!-- This website is like lightning don\'t you think? Performance maxed out by %s!', $this->plugin->get_safe_slug() ), $this->plugin->get_plugin_info( 'Name' ) );
		if ( $debug ) {
			$footprint .= ' - Debug: cached@' . time();
		}
		$footprint .= ' -->';

		return $footprint;
	}
}