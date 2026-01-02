<?php

namespace WP_Desa\Core;

use WP_Desa\Frontend\Assets as PublicAssets;
use WP_Desa\Admin\Assets as AdminAssets;
use WP_Desa\Frontend\Shortcodes;
use WP_Desa\Api\Router;
use WP_Desa\Admin\Menu;

class Plugin {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'wp-desa';
		$this->version = WP_DESA_VERSION;
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_routes();
	}

	private function load_dependencies() {
		// Load other dependencies if needed
	}

	private function define_admin_hooks() {
		$plugin_admin = new Menu( $this->plugin_name, $this->version );
		add_action( 'admin_menu', [ $plugin_admin, 'add_plugin_admin_menu' ] );
		
		$assets = new AdminAssets( $this->plugin_name, $this->version );
		add_action( 'admin_enqueue_scripts', [ $assets, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $assets, 'enqueue_styles' ] );
	}

	private function define_public_hooks() {
		$assets = new PublicAssets( $this->plugin_name, $this->version );
		add_action( 'wp_enqueue_scripts', [ $assets, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $assets, 'enqueue_scripts' ] );

		$shortcodes = new Shortcodes();
		$shortcodes->register();
	}

	private function define_api_routes() {
		$router = new Router();
		add_action( 'rest_api_init', [ $router, 'register_routes' ] );
	}

	public function run() {
		// Run the loader to execute all of the hooks with WordPress.
        // For simplicity, we are adding actions directly in define_*_hooks, 
        // but typically a Loader class is used to aggregate them.
        // Since we added add_action directly, run() might be empty or used for other init logic.
	}

}
