<?php


namespace Jolt\Cache;


use pcfreak30\ComposePress\ComponentAbstract;

/**
 * Class Templates
 *
 * @package Jolt\Cache
 * @property \Jolt $plugin
 */
class Templates extends ComponentAbstract {

	private $template_dir;

	/**
	 *
	 */
	public function init() {
		$this->template_dir = dirname( $this->plugin->plugin_file ) . DIRECTORY_SEPARATOR . 'templates';
	}

	/**
	 * @param  string $name
	 * @param array   $data
	 */
	public function get( $name, $data = [] ) {
		$template = $this->template_dir . DIRECTORY_SEPARATOR . $name . '.php';
		if ( ! $this->plugin->get_wp_filesystem()->is_file( $template ) ) {
			return '';
		}
		foreach ( $data as $key => $item ) {
			$$key = $item;
		}
		ob_start();
		include $template;
		$template = ob_get_clean();

		/**
		 * Allows the template to be modified
		 *
		 * @since 0.1.0
		 *
		 * @param array The template data
		 */
		$template = apply_filters( "jolt_cache_template_{$name}", $template, $data );

		/**
		 * Allows the template to be modified
		 *
		 * @since 0.1.0
		 *
		 * @param string The template name
		 * @param array  The template data
		 */
		$template = apply_filters( "jolt_cache_template", $template, $name, $data );

		return $template;
	}

}