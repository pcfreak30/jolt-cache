<?php


use JoltCache\Config;
use JoltCache\Installer;
use JoltCache\Managers\Store as CacheManager;
use JoltCache\Request;
use JoltCache\Templates;
use JoltCache\UI;
use ComposePress\Core\Abstracts\Plugin;
use ComposePress\Settings;

/**
 * Class Jolt
 *
 * @property \JoltCache\Config         $config
 * @property \JoltCache\Installer      $installer
 * @property \JoltCache\Request        $request
 * @property \JoltCache\Templates      $templates
 * @property \JoltCache\Managers\Store $cache_manager
 * @property \\ComposePress\Core $settings
 * @property \JoltCache\UI             $admin_ui
 * @property bool                      $early_load
 */
class JoltCache extends Plugin {

	/**
	 *
	 */
	const PLUGIN_SLUG = 'jolt-cache';

	/**
	 *
	 */
	const VERSION = '0.1.0';
	/**
	 * @var \JoltCache\Config
	 */
	private $config;
	/**
	 * @var \JoltCache\Installer
	 */
	private $installer;
	/**
	 * @var \JoltCache\Request
	 */
	private $request;
	/**
	 * @var \JoltCache\Templates
	 */
	private $templates;

	/**
	 * @var bool
	 */
	private $early_load = null;
	/**
	 * @var \JoltCache\Managers\Store
	 */
	private $cache_manager;
	/**
	 * @var \JoltCache\UI
	 */
	private $admin_ui;
	/**
	 * @var \ComposePress\Settings
	 */
	private $settings;

	private $init = false;


	/**
	 * Jolt constructor.
	 *
	 * @param \JoltCache\Config    $config
	 * @param \JoltCache\Templates $templates
	 * @param \JoltCache\Installer $installer
	 * @param \JoltCache\Request   $request
	 */
	/** @noinspection PhpMissingParentConstructorInspection */
	/** @noinspection MagicMethodsValidityInspection
	 * @param \JoltCache\Config         $config
	 * @param \JoltCache\Templates      $templates
	 * @param \JoltCache\Installer      $installer
	 * @param \JoltCache\Request        $request
	 * @param \JoltCache\Managers\Store $cache_manager
	 * @param \ComposePress\Settings    $settings
	 * @param \JoltCache\UI             $admin_ui
	 *
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 * @internal param \Jolt\Cache\Admin\Settings\UI $ui
	 */
	public function __construct(
		Config $config,
		Templates $templates,
		Installer $installer,
		Request $request,
		CacheManager $cache_manager,
		Settings $settings,
		UI $admin_ui
	) {
		$this->config        = $config;
		$this->templates     = $templates;
		$this->installer     = $installer;
		$this->request       = $request;
		$this->cache_manager = $cache_manager;
		$this->admin_ui      = $admin_ui;
		$this->settings      = $settings;
		$this->maybe_early_load();
		$this->find_plugin_file();
		$this->set_container();
	}

	/**
	 *
	 */
	private function maybe_early_load() {
		if ( null !== $this->early_load ) {
			return;
		}
		if ( ! doing_action( 'plugins_loaded' ) ) {
			$this->early_load = true;
			return;
		}
		$this->early_load = false;
	}

	public function get_wp_filesystem( $args = [] ) {
		if ( $this->early_load ) {
			return false;
		}

		return parent::get_wp_filesystem( $args );
	}

	/**
	 *
	 */
	protected function find_plugin_file() {
		if ( $this->early_load ) {
			$dir  = dirname( ( new \ReflectionClass( $this ) )->getFileName() );
			$file = null;
			do {
				$last_dir = $dir;
				$dir      = dirname( $dir );
				$file     = $dir . DIRECTORY_SEPARATOR . $this->plugin->get_slug() . '.php';
			} while ( ! @is_file( $file ) && $dir !== $last_dir );
			$this->plugin_file = $file;

			return;
		}
		parent::find_plugin_file();
	}

	/**
	 * @return void
	 */
	public function activate() {
		$this->early_load = false;
		$this->installer->install();
	}

	/**
	 * @return $this
	 */
	public function init() {
		if ( $this->init ) {
			return $this;
		}
		$this->maybe_early_load();
		do_action( 'jolt_cache_before_init' );

		parent::init();

		if ( $this->early_load ) {
			do_action( 'jolt_cache_loaded_early' );
			add_action( 'plugins_loaded', [ $this, 'do_loaded_delayed' ], 12 );
		}
		if ( ! $this->early_load && ! has_action( 'plugins_loaded', [ $this, 'do_loaded_delayed' ] ) ) {
			do_action( 'jolt_cache_loaded' );
		}
		$this->init = true;

		return $this;
	}

	public function do_loaded_delayed() {
		$this->early_load = false;
		do_action( 'jolt_cache_loaded' );
	}

	/**
	 * @return void
	 */
	public function deactivate() {
		$this->installer->deactivate();
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		$this->installer->uninstall();
	}

	/**
	 * @return \JoltCache\Installer
	 */
	public function get_installer() {
		return $this->installer;
	}

	/**
	 * @return \JoltCache\Request
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * @return \JoltCache\Config
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * @return \JoltCache\Templates
	 */
	public function get_templates() {
		return $this->templates;
	}

	/**
	 * @return bool
	 */
	public function is_early_load() {
		return $this->early_load;
	}

	/**
	 * @param bool $early_load
	 */
	public function set_early_load( $early_load ) {
		$this->early_load = $early_load;
	}

	/**
	 * @return \JoltCache\Managers\Store
	 */
	public function get_cache_manager() {
		return $this->cache_manager;
	}

	/**
	 * @return \JoltCache\UI
	 */
	public function get_admin_ui() {
		return $this->admin_ui;
	}

	/**
	 * @return Settings
	 */
	public function get_settings() {
		return $this->settings;
	}
}