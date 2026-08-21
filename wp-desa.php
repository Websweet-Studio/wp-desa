<?php

/**
 * Plugin Name: WP Desa
 * Plugin URI:  https://websweetstudio.com/wp-desa
 * Description: Plugin WordPress untuk fitur web desa dengan REST API dan Alpine.js
 * Version:     1.0.2
 * Author:      Aditya Kristyanto
 * Author URI:  https://websweetstudio.com
 * License:     GPL-2.0+
 * Text Domain: wp-desa
 */

if (! defined('ABSPATH')) {
  exit;
}

// Define Plugin Constants
define('WP_DESA_VERSION', '1.0.2');
define('WP_DESA_PATH', plugin_dir_path(__FILE__));
define('WP_DESA_URL', plugin_dir_url(__FILE__));

// Simple Autoloader
spl_autoload_register(function ($class) {
  $prefix = 'WpDesa\\';
  $base_dir = WP_DESA_PATH . 'src/';

  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  $relative_class = substr($class, $len);
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});

// Initialize Plugin
function wp_desa_init()
{
  $plugin = new \WpDesa\Core\Plugin();
  $plugin->run();
}
add_action('plugins_loaded', 'wp_desa_init');

// Activation Hook
register_activation_hook(__FILE__, function () {
  \WpDesa\Database\Activator::activate();
  update_option('wp_desa_db_version', WP_DESA_VERSION);
});

// DB Upgrade Check: ensure schema changes are applied to existing installs
function wp_desa_check_upgrade()
{
  $installed = get_option('wp_desa_db_version', '0');
  if (version_compare($installed, WP_DESA_VERSION, '<')) {
    \WpDesa\Database\Activator::activate();
    update_option('wp_desa_db_version', WP_DESA_VERSION);
  }
}
add_action('init', 'wp_desa_check_upgrade');
