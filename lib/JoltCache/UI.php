<?php


namespace JoltCache;


class UI extends \ComposePress\Settings\Abstracts\UI {
	protected $parent_menu = 'options-general.php';

	public function init() {
		parent::init();
		if ( is_admin() ) {
			add_action( "{$this->plugin->safe_slug}_settings_saved", [ $this->plugin->config, 'save' ] );
		}
	}
}