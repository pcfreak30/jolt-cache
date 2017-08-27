<?php


use Jolt\Cache\Config;
use Jolt\Cache\Installer;
use Jolt\Cache\Manager;
use Jolt\Cache\Request;
use Jolt\Cache\Templates;
use pcfreak30\ComposePress\PluginAbstract;

/**
 * Class Jolt
 *
 * @property \Jolt\Cache\Config    $config
 * @property \Jolt\Cache\Installer $installer
 * @property \Jolt\Cache\Request   $request
 * @property \Jolt\Cache\Templates $templates
 * @property \Jolt\Cache\Manager   $cache_manager
 * @property bool                  $early_load
 */
class Jolt extends PluginAbstract {

	/**
	 *
	 */
	const PLUGIN_SLUG = 'jolt-cache';

	/**
	 *
	 */
	const VERSION = '0.1.0';
	/**
	 * @var \Jolt\Cache\Config
	 */
	private $config;
	/**
	 * @var \Jolt\Cache\Installer
	 */
	private $installer;
	/**
	 * @var \Jolt\Cache\Request
	 */
	private $request;
	/**
	 * @var \Jolt\Cache\Templates
	 */
	private $templates;

	/**
	 * @var bool
	 */
	private $early_load = false;
	/**
	 * @var \Jolt\Cache\Manager
	 */
	private $cache_manager;


	/**
	 * Jolt constructor.
	 *
	 * @param \Jolt\Cache\Config    $config
	 * @param \Jolt\Cache\Templates $templates
	 * @param \Jolt\Cache\Installer $installer
	 * @param \Jolt\Cache\Request   $request
	 */
	/** @noinspection PhpMissingParentConstructorInspection */
	/** @noinspection MagicMethodsValidityInspection
	 * @param \Jolt\Cache\Config    $config
	 * @param \Jolt\Cache\Templates $templates
	 * @param \Jolt\Cache\Installer $installer
	 * @param \Jolt\Cache\Request   $request
	 * @param \Jolt\Cache\Manager   $cache_manager
	 */
	public function __construct(
		Config $config,
		Templates $templates,
		Installer $installer,
		Request $request,
		Manager $cache_manager
	) {
		$this->config        = $config;
		$this->templates     = $templates;
		$this->installer     = $installer;
		$this->request       = $request;
		$this->cache_manager = $cache_manager;
		$this->maybe_early_load();
		$this->find_plugin_file();
		$this->set_container();
	}

	/**
	 *
	 */
	private function maybe_early_load() {
		if ( ! doing_action( 'plugins_loaded' ) ) {
			$this->early_load = true;
			wp_set_lang_dir();
			wp_load_translations_early();
		}
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
		$this->installer->install();
	}

	/**
	 * @return $this
	 */
	public function init() {
		do_action( 'jolt_cache_before_init' );

		parent::init();

		if ( $this->early_load ) {
			do_action( 'jolt_cache_loaded_early' );
			add_action( 'plugins_loaded', function () {
				do_action( 'jolt_cache_loaded' );
			}, 11 );
		}
		if ( ! $this->early_load ) {
			do_action( 'jolt_cache_loaded' );
		}

		return $this;
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
	 * @return \Jolt\Cache\Installer
	 */
	public function get_installer() {
		return $this->installer;
	}

	/**
	 * @return \Jolt\Cache\Request
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 * @return \WP_Filesystem_Direct
	 */
	public function get_wp_filesystem() {
		return parent::get_wp_filesystem();
	}

	/**
	 * @return \Jolt\Cache\Config
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * @return \Jolt\Cache\Templates
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
	 * @return \Jolt\Cache\Manager
	 */
	public function get_cache_manager() {
		return $this->cache_manager;
	}
}