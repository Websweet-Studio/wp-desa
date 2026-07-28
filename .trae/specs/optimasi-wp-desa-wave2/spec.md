# Optimasi WP Desa Wave 2 — Analisis & Perbaikan Lanjutan

## Why
Plugin WP Desa versi 1.0.2 telah menjalani perbaikan wave 1 (sintaks, SQL, CSRF, CDN fallback, uninstall). Namun analisis mendalam menemukan 7 isu baru: bug nested SVG, hardcoded API path yang break di subdirectory WP, admin notice masih di-nuke total, dashboard stats tanpa cache, `filemtime()` di production, duplikasi kode frontend JS, dan double-prepare SQL yang rawan error.

## What Changes
- **BUG** — Icons.php: icon `mars` punya double nested `<svg>` (invalid HTML)
- **BUG** — JS frontend & admin: hardcoded `/wp-json/...` path, tidak pakai `rest_url()`, break di subdirectory install
- **BUG** — Menu.php: `remove_all_actions('admin_notices')` tetap aktif — notice kritis WordPress hilang
- **PERF** — DashboardController: stats tanpa transient cache, 8+ query setiap buka dashboard
- **PERF** — `filemtime()` dipakai di production untuk cache-busting CSS/JS, I/O disk tidak perlu
- **CODE** — Frontend JS: 4 fungsi helper diduplikasi di setiap komponen (formatCurrency, escapeHtml, formatDate, formatStatus)
- **CODE** — ComplaintController & LetterController: double `$wpdb->prepare()` pattern rawan error

## Impact
- Affected specs: Bug Fix, Performance, Code Quality
- Affected code: [Icons.php](file:///g:/DEV/wp-desa/src/Frontend/Icons.php), [wp-desa-frontend.js](file:///g:/DEV/wp-desa/assets/js/wp-desa-frontend.js), [wp-desa-admin.js](file:///g:/DEV/wp-desa/assets/js/wp-desa-admin.js), [Menu.php](file:///g:/DEV/wp-desa/src/Admin/Menu.php), [DashboardController.php](file:///g:/DEV/wp-desa/src/Api/DashboardController.php), [ComplaintController.php](file:///g:/DEV/wp-desa/src/Api/ComplaintController.php), [LetterController.php](file:///g:/DEV/wp-desa/src/Api/LetterController.php), [Shortcode.php](file:///g:/DEV/wp-desa/src/Frontend/Shortcode.php)

## ADDED Requirements

### Requirement: N1 — Fix Nested SVG Bug di Icons::mars
Icon `mars` di Icons.php memiliki full `<svg>` element sebagai value, bukan hanya path. Method `Icons::svg()` membungkusnya lagi dengan `<svg>`, menghasilkan nested SVG yang invalid HTML.

#### Scenario: Render mars icon
- **WHEN** `Icons::svg('mars')` dipanggil
- **THEN** output adalah single `<svg>` element dengan path yang benar, bukan nested

### Requirement: N2 — REST API URL dari PHP, Bukan Hardcoded
Semua AJAX call di JS (admin & frontend) menggunakan path hardcoded seperti `/wp-json/wp-desa/v1/...`. Ini break di WordPress yang diinstal di subdirectory (e.g. `example.com/blog/`).

#### Scenario: Subdirectory WordPress
- **WHEN** WordPress diinstal di `/blog/`
- **THEN** AJAX call ke REST API tetap berfungsi karena menggunakan `rest_url()` dari PHP

### Requirement: N3 — Selective Admin Notice Removal
`Menu::remove_notices()` masih memanggil `remove_all_actions('admin_notices')` dan `remove_all_actions('all_admin_notices')`, menghilangkan SEMUA notice termasuk error kritis WordPress (update plugin failed, disk quota, dsb).

#### Scenario: WordPress core notice
- **WHEN** WordPress menampilkan notice "Plugin update failed" di halaman WP Desa
- **THEN** notice tersebut tetap terlihat, hanya notice dari plugin lain yang tidak relevan yang disembunyikan via CSS

### Requirement: N4 — Cache Dashboard Stats
`DashboardController::get_stats()` melakukan 8+ query database setiap kali dashboard dibuka tanpa caching.

#### Scenario: Dashboard load berulang
- **WHEN** admin membuka dashboard 3 kali dalam 5 menit
- **THEN** query ke database hanya terjadi sekali; request berikutnya mengambil dari transient cache (TTL 5 menit)

### Requirement: N5 — Replace filemtime() with Version Constant in Production
`filemtime()` dipanggil di `Menu::enqueue_scripts()` dan `Shortcode::enqueue_scripts()` setiap page load untuk cache-busting. Ini melakukan filesystem I/O yang tidak perlu di production.

#### Scenario: Production environment
- **WHEN** `WP_DEBUG` false
- **THEN** versi asset menggunakan `WP_DESA_VERSION` constant, bukan `filemtime()`
- **WHEN** `WP_DEBUG` true
- **THEN** tetap menggunakan `filemtime()` untuk development convenience

### Requirement: N6 — DRY Frontend JS Helpers
`wp-desa-frontend.js` menduplikasi fungsi `formatCurrency`, `escapeHtml`, `formatDate`, `formatStatus` di setiap komponen (keuangan, bantuan, aduan, layanan).

#### Scenario: Single source of truth
- **WHEN** helper functions perlu diubah
- **THEN** perubahan hanya dilakukan di satu tempat (top-level scope), tidak di 4 tempat berbeda

### Requirement: N7 — Fix Double-prepare SQL Pattern
`ComplaintController::get_complaints()` dan `LetterController::get_letters()` menggunakan pattern: prepare WHERE clause dulu, lalu prepare full SQL lagi dengan LIMIT/OFFSET. Ini fragile dan bisa menghasilkan query error jika WHERE mengandung placeholder.

#### Scenario: Filter by status
- **WHEN** request dengan parameter `status=pending`
- **THEN** SQL dieksekusi dengan benar tanpa double-prepare
