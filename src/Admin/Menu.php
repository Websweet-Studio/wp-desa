<?php

namespace WpDesa\Admin;

class Menu
{
    public function register_menus()
    {
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

        // Submenu Layanan Surat
        add_submenu_page(
            'wp-desa',
            'Layanan Surat',
            'Layanan Surat',
            'manage_options',
            'wp-desa-letters',
            [$this, 'render_letters_page']
        );
    }

    public function enqueue_scripts($hook)
    {
        // Enqueue on Dashboard, Residents, and Letters page
        $allowed_pages = [
            'toplevel_page_wp-desa',
            'wp-desa_page_wp-desa-residents',
            'wp-desa_page_wp-desa-letters'
        ];

        if (in_array($hook, $allowed_pages)) {
            // Alpine.js
            wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

            // Admin CSS
            wp_enqueue_style('wp-desa-admin-css', WP_DESA_URL . 'assets/css/admin/style.css', [], '1.0.0');
        }

        // Only for Dashboard
        if ($hook === 'toplevel_page_wp-desa') {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.0.0', true);
        }
    }

    public function render_dashboard()
    {
        require_once WP_DESA_PATH . 'templates/admin/dashboard.php';
    }

    public function render_residents_page()
    {
        require_once WP_DESA_PATH . 'templates/admin/residents.php';
    }

    public function render_letters_page()
    {
        require_once WP_DESA_PATH . 'templates/admin/letters.php';
    }
}
