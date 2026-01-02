<?php

/**
 * Plugin Name: WP Desa
 * Plugin URI:  https://websweetstudio.com/wp-desa
 * Description: Plugin WordPress untuk fitur web desa dengan REST API dan Alpine.js
 * Version:     1.0.0
 * Author:      Aditya Kristyanto
 * Author URI:  https://websweetstudio.com
 * License:     GPL-2.0+
 * Text Domain: wp-desa
 */

if (! defined('ABSPATH')) {
  exit;
}

// Define Plugin Constants
define('WP_DESA_VERSION', '1.0.0');
define('WP_DESA_PATH', plugin_dir_path(__FILE__));
define('WP_DESA_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once WP_DESA_PATH . 'inc/autoloader.php';

/**
 * The code that runs during plugin activation.
 */
function activate_wp_desa()
{
  \WP_Desa\Core\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_desa()
{
  \WP_Desa\Core\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_desa');
register_deactivation_hook(__FILE__, 'deactivate_wp_desa');

/**
 * Begins execution of the plugin.
 */
function run_wp_desa()
{
  $plugin = new \WP_Desa\Core\Plugin();
  $plugin->run();
}

run_wp_desa();
