<?php

namespace WpDesa\Admin;

class Menu {
    public function register_menus() {
        // Main Menu
        add_menu_page(
            'WP Desa',
            'WP Desa',
            'manage_options',
            'wp-desa',
            [$this, 'render_dashboard'],
            'dashicons-admin-home',
            6
        );

        // Submenu Data Penduduk
        add_submenu_page(
            'wp-desa',
            'Data Penduduk',
            'Data Penduduk',
            'manage_options',
            'wp-desa-residents',
            [$this, 'render_residents_page']
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'wp-desa_page_wp-desa-residents') {
            return;
        }

        // Alpine.js
        wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);
    }

    public function render_dashboard() {
        echo '<div class="wrap"><h1>WP Desa Dashboard</h1><p>Selamat datang di sistem manajemen desa.</p></div>';
    }

    public function render_residents_page() {
        require_once WP_DESA_PATH . 'templates/admin/residents.php';
    }
}
