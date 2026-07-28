# Tasks — Optimasi WP Desa Wave 2

## Tasks

- [x] **Task 1**: Fix nested SVG bug di `Icons.php` — icon `mars`
  - File: [Icons.php](file:///g:/DEV/wp-desa/src/Frontend/Icons.php)
  - Ganti value `mars` dari full `<svg>` element menjadi hanya path attributes saja
  - Verifikasi: `Icons::svg('mars')` menghasilkan single `<svg>` element yang valid

- [x] **Task 2**: Ganti hardcoded REST API path di JS dengan `wpDesaSettings.apiUrl` dari PHP
  - Files: [wp-desa-frontend.js](file:///g:/DEV/wp-desa/assets/js/wp-desa-frontend.js), [wp-desa-admin.js](file:///g:/DEV/wp-desa/assets/js/wp-desa-admin.js)
  - Di wp-desa-admin.js: ganti `/wp-json/wp-desa/v1/dashboard/stats` → `wpApiUrl` dengan pola replace, atau tambahkan variabel terpisah per endpoint
  - Di wp-desa-frontend.js: terima `restUrl` dari `wpDesaSettings` atau hitung dari `wpApiUrl`
  - Pass `rest_url()` dari PHP via `wp_localize_script` atau inline script

- [x] **Task 3**: Selective admin notice — hapus `remove_all_actions`, pertahankan CSS-only hide
  - File: [Menu.php](file:///g:/DEV/wp-desa/src/Admin/Menu.php)
  - Hapus baris `remove_all_actions('admin_notices')` dan `remove_all_actions('all_admin_notices')`
  - Pertahankan CSS `.wp-desa-dashboard .notice { display:none !important; }`
  - Tambahkan exclusion untuk `.notice-error`, `.notice-warning` dari core WordPress atau biarkan `.updated` / `.error` standar

- [x] **Task 4**: Tambahkan transient cache di `DashboardController::get_stats()`
  - File: [DashboardController.php](file:///g:/DEV/wp-desa/src/Api/DashboardController.php)
  - Cache seluruh response stats dengan `set_transient('wp_desa_dashboard_stats', $data, 5 * MINUTE_IN_SECONDS)`
  - Invalidate cache saat ada data baru (opsional: invalidasi di seed/create endpoint)
  - Return cached data jika masih valid

- [x] **Task 5**: Gunakan `WP_DESA_VERSION` untuk cache-busting di production
  - Files: [Menu.php](file:///g:/DEV/wp-desa/src/Admin/Menu.php), [Shortcode.php](file:///g:/DEV/wp-desa/src/Frontend/Shortcode.php)
  - Di `Menu::enqueue_scripts()` dan `Shortcode::enqueue_scripts()`: ganti `filemtime()` dengan kondisi `WP_DEBUG`
  - `$ver = (defined('WP_DEBUG') && WP_DEBUG) ? filemtime(...) : WP_DESA_VERSION;`

- [x] **Task 6**: DRY — ekstrak shared helpers di `wp-desa-frontend.js` ke top-level scope
  - File: [wp-desa-frontend.js](file:///g:/DEV/wp-desa/assets/js/wp-desa-frontend.js)
  - Pindahkan `formatCurrency`, `escapeHtml`, `formatDate`, `formatStatus` ke global scope (di dalam IIFE, di atas semua init function)
  - Hapus duplikasi dari `initKeuanganDesa`, `initBantuanDesa`, `initAduanWarga`, `initLayananSurat`

- [x] **Task 7**: Fix double-prepare SQL di ComplaintController & LetterController
  - Files: [ComplaintController.php](file:///g:/DEV/wp-desa/src/Api/ComplaintController.php), [LetterController.php](file:///g:/DEV/wp-desa/src/Api/LetterController.php)
  - Method `get_complaints()` dan `get_letters()`: build args array dulu, lalu satu kali `$wpdb->prepare()` untuk full query
  - Hindari prepare WHERE terpisah lalu prepare full SQL lagi

## Task Dependencies
- Task 1–7 → semua independent, bisa dikerjakan paralel
