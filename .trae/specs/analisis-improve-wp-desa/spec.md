# Analisis & Perbaikan WP Desa Plugin

## Why
Plugin WP Desa versi 1.0.2 memiliki beberapa celah keamanan, bug sintaks, masalah performa, dan melanggar praktik terbaik WordPress yang perlu diperbaiki untuk stabilitas dan keamanan jangka panjang.

## Temuan & Kategori

### 🔴 Critical (Harus Segera Diperbaiki)

#### C1. Bug Sintaks - Template Shortcode Rusak
- **Lokasi**: [Shortcode.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Frontend/Shortcode.php) line 1511
- **Masalah**: `<span:>` bukan sintaks HTML/JS yang valid — seharusnya `<span :class="...">`. Ini memicu parse error yang merusak fitur Alpine.js tracking surat.
- **Dampak**: Tracking status surat di frontend tidak berfungsi.

#### C2. SQL Query Referensi Kolom Tidak Ada
- **Lokasi**: [PrintHandler.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Admin/PrintHandler.php) line 27
- **Masalah**: SQL JOIN `r.agama` merujuk kolom `agama` yang **tidak ada** di tabel `wp_desa_residents`.
- **Dampak**: Cetak surat akan error SQL jika kolom tidak ada.

#### C3. Self-healing Paksa Pada Setiap API Write
- **Lokasi**: [ResidentController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/ResidentController.php) line 112, [LetterController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/LetterController.php) line 120, [ComplaintController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/ComplaintController.php) line 73, [FinanceController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/FinanceController.php) line 177
- **Masalah**: Setiap endpoint CREATE memanggil `Activator::activate()` yang menjalankan semua perintah `CREATE TABLE` — sangat berat dan tidak perlu.
- **Dampak**: Slow response time pada operasi CRUD harian.

### 🟠 High

#### H1. Nonce/CSRF Hilang Pada Endpoint Penting
- **Lokasi**: [ResidentController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/ResidentController.php) — `export_items()` line 196, [PrintHandler.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Admin/PrintHandler.php) — `handle_print()` line 7
- **Masalah**: Endpoint export CSV dan cetak surat tidak memverifikasi nonce. Export GET bisa dipanggil langsung tanpa otentikasi CSRF.
- **Dampak**: Kerentanan CSRF — bisa dipicu dari situs eksternal.

#### H2. Seed Endpoint Tidak Cek Dev Mode
- **Lokasi**: Semua seed endpoint di setiap Controller — hanya cek `manage_options`
- **Masalah**: Admin bisa generate data dummy kapan saja meskipun `dev_mode` nonaktif. Seharusnya dicek dulu.
- **Dampak**: Risiko data sampah di produksi.

#### H3. CDN Dependencies Tanpa Fallback Lokal
- **Lokasi**: [Menu.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Admin/Menu.php) line 96-109, [Shortcode.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Frontend/Shortcode.php) line 1397-1423
- **Masalah**: Alpine.js, Chart.js, Lucide, Glightbox semuanya dari CDN tanpa fallback. Jika CDN down, plugin rusak total.
- **Dampak**: Plugin jadi tidak berfungsi tanpa koneksi internet / CDN down.

#### H4. Chart.js & Library Berat Dimuat di SEMUA Halaman Frontend
- **Lokasi**: [Shortcode.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Frontend/Shortcode.php) — `enqueue_scripts()` line 1394-1423
- **Masalah**: Chart.js, Glightbox, Lucide dimuat global tanpa memeriksa apakah shortcode yang membutuhkannya aktif.
- **Dampak**: Page weight tidak perlu di halaman yang tidak pakai shortcode WP Desa.

#### H5. ORDER BY RAND() di Seeder — Performa Buruk
- **Lokasi**: [Seeder.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Database/Seeder.php) line 108
- **Masalah**: `ORDER BY RAND()` tidak scalable — akan sangat lambat untuk tabel dengan ribuan baris.
- **Dampak**: Generate data dummy bisa timeout untuk dataset besar.

### 🟡 Medium

#### M1. Admin Notice Dihapus Total
- **Lokasi**: [Menu.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Admin/Menu.php) — `remove_notices()` line 113-121
- **Masalah**: `remove_all_actions('admin_notices')` menyembunyikan SEMUA notice WP termasuk error penting (update plugin, disk quota, dll).
- **Dampak**: Admin tidak sadar masalah server/WordPress.

#### M2. Settings Redirect Pakai JavaScript
- **Lokasi**: [Menu.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Admin/Menu.php) line 177
- **Masalah**: Redirect pakai `echo "<script>..."` berarti output sudah dikirim sebelum redirect. Seharusnya `wp_redirect()` setelah form processing di `admin_post` action.
- **Dampak**: Tidak sesuai WP coding standard, bisa break jika ada output buffering issue.

#### M3. Internationalization (i18n) Tidak Ada
- **Lokasi**: Seluruh plugin
- **Masalah**: Semua string hardcoded bahasa Indonesia, tidak ada `__()`, `_e()`, atau `load_plugin_textdomain()`.
- **Dampak**: Plugin tidak bisa diterjemahkan ke bahasa lain.

#### M4. Settings Race Condition (Single Option)
- **Lokasi**: [Menu.php](file:///g:/DEV/app/public-content/plugins-wp-desa/src/Admin/Menu.php) line 173
- **Masalah**: Semua settings disimpan sebagai satu opsi serialized `wp_desa_settings`. Update concurrent bisa timpa data.
- **Dampak**: Kehilangan data settings jika 2 admin save bersamaan.

#### M5. Error_log Tidak Dibalut WP_DEBUG
- **Lokasi**: [ResidentController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/ResidentController.php) lines 135, 140
- **Masalah**: `error_log()` dipanggil tanpa cek `WP_DEBUG` — log penuh data sensitif (NIK, alamat) di produksi.
- **Dampak**: Kebocoran data di log file.

#### M6. Tidak Ada Uninstall / Deactivation Cleanup
- **Masalah**: Tidak ada file `uninstall.php` atau hook `register_deactivation_hook()`. Tabel database & opsi tidak dibersihkan saat plugin dihapus.
- **Dampak**: Data sampah tinggal permanen di database.

### 🟢 Low

#### L1. Duplikasi CSS (Admin & Frontend Banyak Kode Sama)
- **Lokasi**: [admin/style.css](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/assets/css/admin/style.css) vs [frontend/style.css](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/assets/css/frontend/style.css)
- **Masalah**: Banyak ruleset CSS identik di kedua file.
- **Saran**: Ekstrak shared styles ke file bersama.

#### L2. GitHub Updater — Tidak Cek Asset Array
- **Lokasi**: [GithubUpdater.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Core/GithubUpdater.php) lines 78, 111
- **Masalah**: Akses `$release->assets[0]->browser_download_url` tanpa cek apakah assets array tidak kosong.
- **Dampak**: Warning/error jika release tidak punya asset.

#### L3. File Upload Hanya Cek MIME, Tidak Cek Ukuran
- **Lokasi**: [ComplaintController.php](file:///g:/DEV/app/public/wp-content/plugins/wp-desa/src/Api/ComplaintController.php) line 88
- **Masalah**: Upload foto cuma divalidasi MIME type, tidak ada batasan ukuran file.
- **Dampak**: File besar bisa memenuhi disk server.

## Impact
- Affected specs: Security, Performance, Reliability, Code Quality
- Affected code: All controllers, Shortcode.php, PrintHandler.php, Menu.php, Seeder.php, GithubUpdater.php, CSS assets

## RECOMMENDED PRIORITY ORDER
1. 🔴 C1 — Bug sintaks (Shortcode rusak)
2. 🔴 C2 — SQL kolom tidak ada (Print rusak)
3. 🔴 C3 — Self-healing berat
4. 🟠 H1 — CSRF pada export/print
5. 🟠 H4 — Script overload
6. 🟡 M1 — Admin notice dihapus
7. 🟡 M6 — Uninstall cleanup
8. 🟠 H3 — CDN fallback
9. 🟡 M2 — Settings redirect
10. Others
