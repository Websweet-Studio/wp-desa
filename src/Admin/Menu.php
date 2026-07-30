<?php

namespace WpDesa\Admin;

class Menu
{
    public function register_menus()
    {
        add_action('admin_init', [$this, 'handle_seed_clear']);
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

        // Submenu Pemerintahan (Struktur Organisasi & Produk Hukum)
        add_submenu_page(
            'wp-desa',
            'Pemerintahan',
            'Pemerintahan',
            'manage_options',
            'wp-desa-pemerintahan',
            [$this, 'render_pemerintahan_page']
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

        // Redirect old wp-desa-peta page to settings tab
        if (isset($_GET['page']) && $_GET['page'] === 'wp-desa-peta') {
            wp_redirect(admin_url('admin.php?page=wp-desa-settings&tab=peta'));
            exit;
        }
    }

    public function enqueue_scripts($hook)
    {
        // Enqueue on all WP Desa admin pages
        $allowed_pages = [
            'toplevel_page_wp-desa',
            'wp-desa_page_wp-desa-residents',
            'wp-desa_page_wp-desa-layanan',
            'wp-desa_page_wp-desa-keuangan',
            'wp-desa_page_wp-desa-pemerintahan',
            'wp-desa_page_wp-desa-settings',
            'wp-desa_page_wp-desa-dokumentasi',
        ];

        if (in_array($hook, $allowed_pages)) {
            // jQuery-dependent admin JS
            $js_ver = (defined('WP_DEBUG') && WP_DEBUG) ? filemtime(WP_DESA_PATH . 'assets/js/wp-desa-admin.js') : WP_DESA_VERSION;
            wp_enqueue_script('wp-desa-admin', WP_DESA_URL . 'assets/js/wp-desa-admin.js', ['jquery'], $js_ver, true);
            wp_add_inline_script('wp-desa-admin', 'var wpDesaSettings={nonce:"' . wp_create_nonce('wp_rest') . '",apiUrl:"' . esc_url_raw(rest_url('wp-desa/v1/residents')) . '",restBase:"' . esc_url_raw(rest_url('wp-desa/v1')) . '"};', 'before');

            // Admin CSS
            $css_ver = (defined('WP_DEBUG') && WP_DEBUG) ? filemtime(WP_DESA_PATH . 'assets/css/admin/style.css') : WP_DESA_VERSION;
            wp_enqueue_style('wp-desa-admin-css', WP_DESA_URL . 'assets/css/admin/style.css', [], $css_ver);
        }

        // Media Uploader for Settings Page
        if ($hook === 'wp-desa_page_wp-desa-settings') {
            wp_enqueue_media();
        }

        // Dashboard and Finance (Need Chart.js)
        if ($hook === 'toplevel_page_wp-desa' || $hook === 'wp-desa_page_wp-desa-residents' || $hook === 'wp-desa_page_wp-desa-keuangan') {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
        }
    }

    public function remove_notices()
    {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'wp-desa') !== false) {
            echo '<style>
                .notice { display:none !important; }
                .wp-desa-header-actions > h2,
                .wp-desa-header-actions > h3 { display:none !important; }
                .wp-desa-hero__head { display:none !important; }
                .wp-desa-wrapper > h2:first-child { display:none !important; }
                .wp-desa-wrapper > h2:first-child + p { display:none !important; }
                #adminmenuwrap { z-index:1001 !important; }
                .wp-desa__globalnav { z-index:1000 !important; }
                #wpadminbar { z-index:1002 !important; }
            </style>';
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
        $subnav = [
            ['tab' => 'daftar', 'label' => 'Daftar'],
            ['tab' => 'kk', 'label' => 'Kartu Keluarga'],
            ['tab' => 'statistik', 'label' => 'Statistik'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'daftar';

        AdminLayout::open('Data Penduduk', 'wp-desa-residents', $subnav);

        if ($current_tab === 'kk') {
            require_once WP_DESA_PATH . 'templates/admin/residents-kk.php';
        } elseif ($current_tab === 'statistik') {
            require_once WP_DESA_PATH . 'templates/admin/residents-statistik.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/residents.php';
        }

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

    public function handle_seed_clear()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        // Seed Data
        if (isset($_POST['wp_desa_seed_data'])) {
            check_admin_referer('wp_desa_seed_action', 'wp_desa_seed_nonce');

            require_once WP_DESA_PATH . 'src/Database/Seeder.php';
            \WpDesa\Database\Seeder::run(100);

            wp_redirect(admin_url('admin.php?page=wp-desa-settings&tab=sistem&seed_done=1'));
            exit;
        }

        // Clear All Data
        if (isset($_POST['wp_desa_clear_data'])) {
            check_admin_referer('wp_desa_clear_action', 'wp_desa_clear_nonce');

            $prefix = $wpdb->prefix;
            $tables = [
                'desa_residents',
                'desa_letter_types',
                'desa_letters',
                'desa_complaints',
                'desa_finances',
                'desa_programs',
                'desa_program_recipients',
            ];

            foreach ($tables as $table) {
                $wpdb->query("TRUNCATE TABLE {$prefix}{$table}");
            }

            wp_redirect(admin_url('admin.php?page=wp-desa-settings&tab=sistem&clear_done=1'));
            exit;
        }
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
            ['tab' => 'peta', 'label' => 'Peta Desa'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'identitas';

        AdminLayout::open('Pengaturan', 'wp-desa-settings', $subnav);

        if ($current_tab === 'peta') {
            require_once WP_DESA_PATH . 'templates/admin/peta.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/settings.php';
        }

        AdminLayout::close();
    }

    public function render_pemerintahan_page()
    {
        $subnav = [
            ['tab' => 'struktur', 'label' => 'Struktur Organisasi'],
            ['tab' => 'produk-hukum', 'label' => 'Produk Hukum'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'struktur';

        AdminLayout::open('Pemerintahan', 'wp-desa-pemerintahan', $subnav);

        if ($current_tab === 'produk-hukum') {
            require_once WP_DESA_PATH . 'templates/admin/produk-hukum.php';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/perangkat.php';
        }

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

    public function register_ajax_handlers()
    {
        add_action('wp_ajax_wp_desa_save_peta', [$this, 'ajax_save_peta']);
    }

    public function ajax_save_peta()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_ajax_referer('wp_rest');

        if (!isset($_POST['settings'])) {
            wp_die('No data');
        }

        $existing = get_option('wp_desa_settings', []);
        $new_data = json_decode(stripslashes($_POST['settings']), true);

        if (is_array($new_data)) {
            $merged = array_merge($existing, $new_data);
            update_option('wp_desa_settings', $merged);
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function render_peta_page()
    {
        $subnav = [
            ['tab' => 'wilayah', 'label' => 'Peta Wilayah'],
            ['tab' => 'wisata', 'label' => 'Destinasi Wisata'],
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'wilayah';

        AdminLayout::open('Peta Desa', 'wp-desa-peta', $subnav);

        if ($current_tab === 'wisata') {
            $edit_url = admin_url('edit.php?post_type=desa_wisata');
            echo '<div class="wp-desa-wrapper">';
            echo '<div class="wp-desa-header-actions" style="margin-bottom:20px;">';
            echo '<h2>Destinasi Wisata Desa</h2>';
            echo '<a href="' . esc_url(admin_url('post-new.php?post_type=desa_wisata')) . '" class="button button-primary">Tambah Destinasi Wisata</a>';
            echo '</div>';
            echo '<p style="color:#64748b;">Kelola destinasi wisata desa melalui custom post type WordPress. Setiap destinasi dapat dilengkapi dengan foto, deskripsi, dan lokasi koordinat.</p>';
            echo '<a href="' . esc_url($edit_url) . '" class="button">Buka Daftar Destinasi Wisata</a>';
            echo '</div>';
        } else {
            require_once WP_DESA_PATH . 'templates/admin/peta.php';
        }

        AdminLayout::close();
    }
}
