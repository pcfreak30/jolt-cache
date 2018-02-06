<?php


namespace JoltCache\UI\Pages;

use ComposePress\Settings\Abstracts\Page;
use ComposePress\Settings\UI\Factory;
use ComposePress\Settings\UI\Fields\Image;
use ComposePress\Settings\UI\Fields\Select;
use ComposePress\Settings\UI\Fields\Wysiwyg;

class General extends Page {

	const NAME = 'general';
	const TITLE = 'Jolt Cache';
	const CAPABILITY = 'manage_options';
	const NETWORK_CAPABILITY = 'manage_options';


	protected $default = true;

	public function register_settings() {
		$cache_options_section = Factory::section( 'cache_options', __( 'Cache Options', $this->plugin->safe_slug ), $this );
		$cache_stores          = apply_filters( 'jolt_cache_stores', [] );
		$cache_store_options   = [];
		/** @var \JoltCache\Abstracts\Store $store */
		foreach ( $cache_stores as $name => $store ) {
			$cache_store_options[ $name ] = $store->friendly_name;
		}
		Factory::field( 'cache_store', __( 'Cache Storage', $this->plugin->safe_slug ), Select::NAME, $cache_options_section, [
			'options' => $cache_store_options,
		] );
	}
}