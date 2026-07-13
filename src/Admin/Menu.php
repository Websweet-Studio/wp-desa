<?php

namespace WpDesa\Admin;

class Menu
{
    public function register_menus()
    {
        add_action('admin_init', [$this, 'handle_settings_submit']);

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

        // Submenu Layanan (Surat & Aduan)
        add_submenu_page(
            'wp-desa',
            'Layanan',
            'Layanan',
            'manage_options',
            'wp-desa-layanan',
            [$this, 'render_layanan_page']
        );

        // Submenu Keuangan (Keuangan & Bantuan)
        add_submenu_page(
            'wp-desa',
            'Keuangan',
            'Keuangan',
            'manage_options',
            'wp-desa-keuangan',
            [$this, 'render_keuangan_page']
        );

        // Submenu Pengaturan
        add_submenu_page(
            'wp-desa',
            'Pengaturan Desa',
            'Pengaturan',
            'manage_options',
            'wp-desa-settings',
            [$this, 'render_settings_page']
        );

        // Submenu Dokumentasi
        add_submenu_page(
            'wp-desa',
            'Dokumentasi',
            'Dokumentasi',
            'manage_options',
            'wp-desa-dokumentasi',
            [$this, 'render_dokumentasi_page']
        );
    }

    public function enqueue_scripts($hook)
    {
        // Enqueue on all WP Desa admin pages
        $allowed_pages = [
            'toplevel_page_wp-desa',
            'wp-desa_page_wp-desa-residents',
            'wp-desa_page_wp-desa-layanan',
            'wp-desa_page_wp-desa-keuangan',
            'wp-desa_page_wp-desa-settings',
            'wp-desa_page_wp-desa-dokumentasi'
        ];

        if (in_array($hook, $allowed_pages)) {
            // Alpine.js
            wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);
            // CDN fallback
            wp_add_inline_script('alpinejs', 'if(typeof Alpine==="undefined"){var e=document.createElement("script");e.src="' . WP_DESA_URL . 'assets/js/alpine.min.js";document.head.appendChild(e);}');

            // Admin CSS
            wp_enqueue_style('wp-desa-admin-css', WP_DESA_URL . 'assets/css/admin/style.css', [], filemtime(WP_DESA_PATH . 'assets/css/admin/style.css'));
        }

        // Media Uploader for Settings Page
        if ($hook === 'wp-desa_page_wp-desa-settings') {
            wp_enqueue_media();
        }

        // Dashboard and Finance (Need Chart.js)
        if ($hook === 'toplevel_page_wp-desa' || $hook === 'wp-desa_page_wp-desa-keuangan') {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.0.0', true);
            // CDN fallback
            wp_add_inline_script('chartjs', 'if(typeof Chart==="undefined"){var e=document.createElement("script");e.src="' . WP_DESA_URL . 'assets/js/chart.min.js";document.head.appendChild(e);}');
        }
    }

    public function remove_notices()
    {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'wp-desa') !== false) {
            echo '<style>.wp-desa-dashboard .notice { display: none; }</style>';
        }
    }

    public function render_dashboard()
    {
        AdminLayout::open('Dashboard', 'wp-desa');
        require_once WP_DESA_PATH . 'templates/admin/dashboard.php';
        AdminLayout::close();
    }

    public function render_residents_page()
    {
        AdminLayout::open('Data Penduduk', 'wp-desa-residents');
        require_once WP_DESA_PATH . 'templates/admin/residents.php';
        AdminLayout::close();
    }

    public function render_layanan_page()
    {
        $subnav = [
            ['tab' => 'surat', 'label' => 'Surat'],
            ['tab' => 'aduan', 'label' => 'Aduan'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'surat';

        AdminLayout::open('Layanan', 'wp-desa-layanan', $subnav);

        if ($current_tab === 'aduan') {
            require_once WP_DESA_PATH . 'templates/admin/complaints.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/letters.php';
        }

        AdminLayout::close();
    }

    public function render_keuangan_page()
    {
        $subnav = [
            ['tab' => 'keuangan', 'label' => 'Keuangan'],
            ['tab' => 'bantuan', 'label' => 'Bantuan'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'keuangan';

        AdminLayout::open('Keuangan', 'wp-desa-keuangan', $subnav);

        if ($current_tab === 'bantuan') {
            require_once WP_DESA_PATH . 'templates/admin/aid.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/finances.php';
        }

        AdminLayout::close();
    }

    public function handle_settings_submit()
    {
        if (!isset($_POST['wp_desa_settings_submit'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('wp_desa_settings_action', 'wp_desa_settings_nonce');

        $data = [
            'nama_desa' => sanitize_text_field($_POST['nama_desa']),
            'nama_kecamatan' => sanitize_text_field($_POST['nama_kecamatan']),
            'nama_kabupaten' => sanitize_text_field($_POST['nama_kabupaten']),
            'alamat_kantor' => sanitize_textarea_field($_POST['alamat_kantor']),
            'email_desa' => sanitize_email($_POST['email_desa']),
            'telepon_desa' => sanitize_text_field($_POST['telepon_desa']),
            'logo_kabupaten' => esc_url_raw($_POST['logo_kabupaten']),
            'kepala_desa' => sanitize_text_field($_POST['kepala_desa']),
            'nip_kepala_desa' => sanitize_text_field($_POST['nip_kepala_desa']),
            'foto_kepala_desa' => esc_url_raw($_POST['foto_kepala_desa']),
            'dev_mode' => isset($_POST['dev_mode']) ? 1 : 0,
        ];

        update_option('wp_desa_settings', $data);

        $redirect_args = [
            'page' => 'wp-desa-settings',
            'settings-updated' => 'true',
        ];

        if (!empty($_POST['_current_tab'])) {
            $redirect_args['tab'] = sanitize_key($_POST['_current_tab']);
        }

        $redirect_url = add_query_arg($redirect_args, admin_url('admin.php'));

        wp_redirect($redirect_url);
        exit;
    }

    public function render_settings_page()
    {
        $subnav = [
            ['tab' => 'identitas', 'label' => 'Identitas & Kontak'],
            ['tab' => 'media', 'label' => 'Logo & Media'],
            ['tab' => 'pejabat', 'label' => 'Kepala Desa'],
            ['tab' => 'sistem', 'label' => 'Pengaturan Sistem'],
        ];

        AdminLayout::open('Pengaturan', 'wp-desa-settings', $subnav);
        require_once WP_DESA_PATH . 'templates/admin/settings.php';
        AdminLayout::close();
    }

    public function render_dokumentasi_page()
    {
        $subnav = [
            ['tab' => 'penggunaan', 'label' => 'Cara Penggunaan'],
            ['tab' => 'shortcode', 'label' => 'Shortcode'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'penggunaan';

        AdminLayout::open('Dokumentasi', 'wp-desa-dokumentasi', $subnav);

        if ($current_tab === 'shortcode') {
            require_once WP_DESA_PATH . 'templates/admin/docs-shortcode.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/docs-penggunaan.php';
        }

        AdminLayout::close();
    }
}
