<?php

namespace JoltCache\UI\Managers;

class Page extends \ComposePress\Settings\Managers\Page {
	const MODULE_NAMESPACE = '\JoltCache\UI\Pages';
	protected $modules = [ 'General' ];
}