<?php

namespace JoltCache;

use ComposePress\Core\Abstracts\Component;


/**
 * Class Installer
 *
 * @package Jolt\Cache
 * @property \JoltCache $plugin
 */
class Installer extends Component {
	private $advanced_cache_path;

	/**
	 *
	 */
	public function init() {
		$this->advanced_cache_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'advanced-cache.php';
	}

	public function install() {
		$advanced_cache = $this->get_advanced_cache();
		if ( ! $this->plugin->wp_filesystem->is_file( $this->advanced_cache_path ) || hash_file( 'sha256', $this->advanced_cache_path ) !== hash( 'sha256', $advanced_cache ) ) {
			$this->plugin->wp_filesystem->put_contents( $this->advanced_cache_path, $advanced_cache );
		}
		$this->set_wp_cache_define( true );
	}

	public function get_advanced_cache() {
		return $this->plugin->templates->get( 'advanced-cache', [
			'bootstrap' => dirname( $this->plugin->plugin_file ) . DIRECTORY_SEPARATOR . 'bootstrap.php',
		] );
	}

	private function set_wp_cache_define( $value ) {
		if ( $value && ( defined( 'WP_CACHE' ) && WP_CACHE ) ) {
			return;
		}
		$wp_config_path = $this->plugin->config->wp_config_path;
		$wp_config      = file( $wp_config_path, FILE_IGNORE_NEW_LINES );
		$string_value   = $value ? 'true' : 'false';

		$wp_cache_constant = sprintf( "define('WP_CACHE', %s); // %s", $string_value, sprintf( __( 'Added by %s' ), $this->plugin->get_plugin_info( 'Name' ) ) ) . PHP_EOL;
		$tokens            = ( new \ArrayObject( token_get_all( implode( PHP_EOL, $wp_config ) ) ) )->getIterator();
		$wp_cache_line     = false;
		$php_tag_line      = false;
		while ( $tokens->valid() ) {
			$token = $tokens->current();
			if ( ! is_array( $token ) ) {
				$tokens->next();
				continue;
			}
			if ( T_OPEN_TAG === $token[0] && ! $php_tag_line ) {
				$php_tag_line = $token[2];
			}
			if ( T_STRING === $token[0] && 'define' === $token[1] ) {
				do {
					$tokens->next();
					$token = $tokens->current();
					if ( T_OPEN_TAG === $token[0] && ! $php_tag_line ) {
						$php_tag_line = $token[2];
					}
				} while ( $tokens->valid() && T_CONSTANT_ENCAPSED_STRING !== $token[0] );
				if ( T_CONSTANT_ENCAPSED_STRING === $token[0] && 'WP_CACHE' === trim( $token[1], '"' . "'" ) ) {
					$wp_cache_line = $token[2];
					break;
				}
			}
			$tokens->next();
		}
		$whitespace_start = $php_tag_line;
		if ( ! $wp_cache_line && $value ) {
			array_splice( $wp_config, $php_tag_line, 0, '' );
			$wp_cache_line = $php_tag_line + 1;
		}

		if ( $value ) {
			$wp_config[ $wp_cache_line - 1 ] = $wp_cache_constant;
			$whitespace_start                = $wp_cache_line;
		}
		if ( ! $value ) {
			unset( $wp_config[ $wp_cache_line - 1 ] );
			$wp_cache_line = false;
		}

		while ( ( $pos = key( $wp_config ) ) < count( $wp_config ) ) {
			$line = current( $wp_config );
			if ( $pos <= $whitespace_start - 1 && ! $wp_cache_line ) {
				next( $wp_config );
				continue;
			}
			$line = trim( $line );
			if ( empty( $line ) ) {
				unset( $wp_config[ $pos ] );
				continue;
			}
			if ( ! empty( $line ) && ( ( $wp_cache_line && $pos > $wp_cache_line - 1 ) || ! $wp_cache_line ) ) {
				break;
			}
			next( $wp_config );
		}
		reset( $wp_config );
		array_splice( $wp_config, $wp_cache_line - 1, 0, '' );


		$this->plugin->wp_filesystem->put_contents( $wp_config_path, implode( PHP_EOL, $wp_config ) );

		$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
		$this->plugin->wp_filesystem->chmod( $wp_config_path, $chmod );
	}

	public function uninstall() {
		if ( $this->plugin->wp_filesystem->is_file( $this->advanced_cache_path ) ) {
			$this->plugin->wp_filesystem->delete( $this->advanced_cache_path );
		}
		$this->set_wp_cache_define( false );
	}

	public function deactivate() {
		$this->set_wp_cache_define( false );
	}
}