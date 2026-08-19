<?php

namespace WpDesa\Admin;

class Menu
{
    /**
     * Nama plugin whitelabel (default 'WP Desa').
     */
    public static function plugin_name()
    {
        $settings = get_option('wp_desa_settings', []);
        $name = isset($settings['wl_plugin_name']) ? trim($settings['wl_plugin_name']) : '';

        return $name !== '' ? $name : 'WP Desa';
    }

    public function register_menus()
    {
        add_action('admin_init', [$this, 'handle_seed_clear']);
        add_action('admin_init', [$this, 'handle_page_generate']);
        add_action('admin_init', [$this, 'handle_settings_submit']);

        $plugin_name = self::plugin_name();

        // Main Menu
        add_menu_page(
            $plugin_name,
            $plugin_name,
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
        if (!$screen || strpos($screen->id, 'wp-desa') === false) {
            return;
        }

        $this->filter_admin_notices();

        echo '<style>
            .wp-desa-card .wp-desa-tab-content { padding:0; }
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

    /**
     * Hapus callback admin notice dari plugin yang tidak masuk whitelist,
     * sehingga hanya notice milik WP Desa dan plugin yang di-whitelist yang tampil.
     */
    private function filter_admin_notices()
    {
        $whitelist = $this->notice_whitelist();

        foreach (['admin_notices', 'all_admin_notices'] as $hook) {
            global $wp_filter;

            if (!isset($wp_filter[$hook]) || !($wp_filter[$hook] instanceof \WP_Hook)) {
                continue;
            }

            $to_remove = [];

            foreach ($wp_filter[$hook]->callbacks as $priority => $group) {
                foreach ($group as $cb) {
                    $plugin = $this->callback_plugin($cb['function']);
                    $slug   = isset($plugin['slug']) ? $plugin['slug'] : '';
                    $title  = isset($plugin['title']) ? $plugin['title'] : '';

                    $keep = ($slug === 'wp-desa' || stripos($title, 'wp-desa') !== false);

                    if (!$keep && $this->matches_whitelist($slug, $title, $whitelist)) {
                        $keep = true;
                    }

                    if (!$keep) {
                        $to_remove[] = [$cb['function'], $priority];
                    }
                }
            }

            foreach ($to_remove as $item) {
                remove_action($hook, $item[0], $item[1]);
            }
        }
    }

    /**
     * Ambil daftar whitelist (nama plugin/slug dan judul) dari pengaturan.
     */
    private function notice_whitelist()
    {
        $settings = get_option('wp_desa_settings', []);
        $raw      = isset($settings['notice_whitelist']) ? $settings['notice_whitelist'] : '';

        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $raw))));
    }

    /**
     * Cek apakah slug/nama plugin cocok dengan salah satu kata kunci whitelist.
     */
    private function matches_whitelist($slug, $title, $whitelist)
    {
        if (empty($whitelist)) {
            return false;
        }

        $haystacks = array_filter([strtolower((string) $slug), strtolower((string) $title)]);

        foreach ($whitelist as $keyword) {
            $keyword = strtolower(trim((string) $keyword));
            if ($keyword === '') {
                continue;
            }

            foreach ($haystacks as $haystack) {
                if (strpos($haystack, $keyword) !== false || strpos($keyword, $haystack) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tentukan plugin asal sebuah callback notice berdasarkan lokasi file.
     */
    private function callback_plugin($callback)
    {
        try {
            if ($callback instanceof \Closure) {
                $ref = new \ReflectionFunction($callback);
            } elseif (is_array($callback) && (is_object($callback[0]) || is_string($callback[0]))) {
                $ref = new \ReflectionMethod($callback[0], $callback[1]);
            } elseif (is_string($callback) && function_exists($callback)) {
                $ref = new \ReflectionFunction($callback);
            } else {
                return null;
            }

            $file = $ref->getFileName();
            if (!$file) {
                return null;
            }

            return $this->plugin_from_file($file);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Petakan path file ke slug & judul plugin.
     */
    private function plugin_from_file($file)
    {
        $file = wp_normalize_path($file);
        $file = str_replace('\\', '/', $file);

        // Must-use plugins.
        if (defined('WPMU_PLUGIN_DIR')) {
            $mu_dir = str_replace('\\', '/', wp_normalize_path(WPMU_PLUGIN_DIR));
            if (strpos($file, $mu_dir . '/') === 0) {
                $rel  = trim(substr($file, strlen($mu_dir)), '/');
                $slug = strtok($rel, '/');

                return ['slug' => $slug, 'title' => $slug];
            }
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_dir = str_replace('\\', '/', wp_normalize_path(WP_PLUGIN_DIR));

        foreach (get_plugins() as $plugin_file => $data) {
            $main = $plugin_dir . '/' . str_replace('\\', '/', $plugin_file);

            if ($file === $main) {
                return ['slug' => $this->plugin_slug($plugin_file), 'title' => $data['Name']];
            }

            $dir = $plugin_dir . '/' . str_replace('\\', '/', dirname($plugin_file));
            if (strpos($file, $dir . '/') === 0) {
                return ['slug' => $this->plugin_slug($plugin_file), 'title' => $data['Name']];
            }
        }

        return null;
    }

    private function plugin_slug($plugin_file)
    {
        $dirname = dirname($plugin_file);

        return ($dirname === '.' || $dirname === '') ? basename($plugin_file, '.php') : $dirname;
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
            delete_transient('wp_desa_quick_stats');

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

    public function handle_page_generate()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_POST['wp_desa_generate_page'])) {
            return;
        }

        check_admin_referer('wp_desa_generate_page_action', 'wp_desa_generate_page_nonce');

        $features = self::feature_pages();
        $keys = !empty($_POST['page_key'])
            ? [sanitize_key($_POST['page_key'])]
            : array_keys($features);

        $publish = !empty($_POST['publish']);
        $post_status = $publish ? 'publish' : 'draft';

        $saved = get_option('wp_desa_pages', []);

        foreach ($keys as $key) {
            if (!isset($features[$key])) {
                continue;
            }

            // Sudah dibuat & masih ada (bukan trash) → publikasikan kalau diminta, lalu lewati.
            if (!empty($saved[$key])) {
                $existing_id = (int) $saved[$key];
                $status = get_post_status($existing_id);
                if ($status && $status !== 'trash') {
                    if ($publish && $status !== 'publish') {
                        wp_update_post(['ID' => $existing_id, 'post_status' => 'publish']);
                    }
                    continue;
                }
            }

            // Kalau sudah ada halaman dengan slug sama, pakai yang itu.
            $existing_page = get_page_by_path('wp-desa-' . $key, OBJECT, 'page');
            if ($existing_page) {
                $saved[$key] = (int) $existing_page->ID;
                if ($publish && $existing_page->post_status !== 'publish') {
                    wp_update_post(['ID' => $existing_page->ID, 'post_status' => 'publish']);
                }
                continue;
            }

            $page_id = wp_insert_post([
                'post_type'    => 'page',
                'post_status'  => $post_status,
                'post_title'   => $features[$key]['title'],
                'post_name'    => 'wp-desa-' . $key,
                'post_content' => $features[$key]['shortcode'],
            ]);

            if (is_wp_error($page_id)) {
                continue;
            }

            $saved[$key] = (int) $page_id;
        }

        update_option('wp_desa_pages', $saved);

        wp_redirect(admin_url('admin.php?page=wp-desa-settings&tab=sistem&pages_done=1'));
        exit;
    }

    public static function feature_pages()
    {
        return [
            'layanan'      => ['title' => 'Layanan Surat', 'shortcode' => '[wp_desa_layanan]'],
            'aduan'        => ['title' => 'Pengaduan Masyarakat', 'shortcode' => '[wp_desa_aduan]'],
            'keuangan'     => ['title' => 'Keuangan Desa', 'shortcode' => '[wp_desa_keuangan]'],
            'bantuan'      => ['title' => 'Bantuan Sosial', 'shortcode' => '[wp_desa_bantuan]'],
            'profil'       => ['title' => 'Profil Desa', 'shortcode' => '[wp_desa_profil]'],
            'kepala_desa'  => ['title' => 'Kepala Desa', 'shortcode' => '[wp_desa_kepala_desa]'],
            'statistik'    => ['title' => 'Statistik Penduduk', 'shortcode' => '[wp_desa_statistik]'],
            'umkm'         => ['title' => 'UMKM Desa', 'shortcode' => '[wp_desa_umkm]'],
            'potensi'      => ['title' => 'Potensi Desa', 'shortcode' => '[wp_desa_potensi]'],
            'struktur'     => ['title' => 'Struktur Organisasi', 'shortcode' => '[wp_desa_struktur]'],
            'produk_hukum' => ['title' => 'Produk Hukum', 'shortcode' => '[wp_desa_produk_hukum]'],
            'berita'       => ['title' => 'Berita Desa', 'shortcode' => '[wp_desa_berita]'],
            'agenda'       => ['title' => 'Agenda Kegiatan', 'shortcode' => '[wp_desa_agenda]'],
            'galeri'       => ['title' => 'Galeri Desa', 'shortcode' => '[wp_desa_galeri]'],
            'peta'         => ['title' => 'Peta Desa', 'shortcode' => '[wp_desa_peta]'],
            'wisata'       => ['title' => 'Destinasi Wisata', 'shortcode' => '[wp_desa_wisata]'],
            'jam_kerja'    => ['title' => 'Jam Kerja', 'shortcode' => '[wp_desa_jam_kerja]'],
        ];
    }

    public static function page_status($key)
    {
        $saved = get_option('wp_desa_pages', []);
        if (empty($saved[$key])) {
            return 0;
        }

        $page_id = (int) $saved[$key];
        $status = get_post_status($page_id);

        if (!$status || $status === 'trash') {
            return 0;
        }

        return $page_id;
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
            'notice_whitelist' => sanitize_textarea_field($_POST['notice_whitelist'] ?? ''),
            'wl_plugin_name' => sanitize_text_field($_POST['wl_plugin_name'] ?? ''),
        ];

        update_option('wp_desa_settings', $data);

        // Save jam kerja if tab is jam-kerja
        if (isset($_POST['_current_tab']) && $_POST['_current_tab'] === 'jam-kerja') {
            $jam_raw = isset($_POST['temadesa_jam_kerja']) ? $_POST['temadesa_jam_kerja'] : [];
            $jam_sanitized = [];
            $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
            foreach ($hari as $h) {
                $item = isset($jam_raw[$h]) ? $jam_raw[$h] : [];
                $jam_sanitized[$h] = [
                    'buka'  => sanitize_text_field($item['buka'] ?? ''),
                    'tutup' => sanitize_text_field($item['tutup'] ?? ''),
                    'libur' => !empty($item['libur']),
                ];
            }
            update_option('temadesa_jam_kerja', $jam_sanitized);
        }

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
            ['tab' => 'jam-kerja', 'label' => 'Jam Kerja'],
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
