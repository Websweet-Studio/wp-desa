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

        // Submenu Aspirasi & Pengaduan
        add_submenu_page(
            'wp-desa',
            'Aspirasi & Pengaduan',
            'Aspirasi & Pengaduan',
            'manage_options',
            'wp-desa-complaints',
            [$this, 'render_complaints_page']
        );

        // Submenu Keuangan Desa
        add_submenu_page(
            'wp-desa',
            'Keuangan Desa',
            'Keuangan Desa',
            'manage_options',
            'wp-desa-finances',
            [$this, 'render_finances_page']
        );

        // Submenu Program Bantuan
        add_submenu_page(
            'wp-desa',
            'Program Bantuan',
            'Program Bantuan',
            'manage_options',
            'wp-desa-aid',
            [$this, 'render_aid_page']
        );
    }

    public function enqueue_scripts($hook)
    {
        // Enqueue on Dashboard, Residents, and Letters page
        $allowed_pages = [
            'toplevel_page_wp-desa',
            'wp-desa_page_wp-desa-residents',
            'wp-desa_page_wp-desa-letters',
            'wp-desa_page_wp-desa-complaints',
            'wp-desa_page_wp-desa-finances',
            'wp-desa_page_wp-desa-aid'
        ];

        if (in_array($hook, $allowed_pages)) {
            // Alpine.js
            wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

            // Admin CSS
            wp_enqueue_style('wp-desa-admin-css', WP_DESA_URL . 'assets/css/admin/style.css', [], '1.0.0');
        }

        // Dashboard and Finance (Need Chart.js)
        if ($hook === 'toplevel_page_wp-desa' || $hook === 'wp-desa_page_wp-desa-finances') {
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

    public function render_complaints_page()
    {
        require_once WP_DESA_PATH . 'templates/admin/complaints.php';
    }

    public function render_finances_page()
    {
        require_once WP_DESA_PATH . 'templates/admin/finances.php';
    }

    public function render_aid_page()
    {
        require_once WP_DESA_PATH . 'templates/admin/aid.php';
    }
}
