# Plan: Update README.md & Rencana Fitur WP Desa

## Summary

Plan ini mencakup dua bagian:
1. **Update README.md** — perbaiki informasi usang (Alpine.js → jQuery, Wave 1 & 2) + tambahkan changelog
2. **Rencana 3 fitur baru** — Struktur Organisasi + Produk Hukum, Berita + Agenda + Galeri, Peta GIS + Wisata (dikerjakan bertahap dalam 3 wave)

---

## Current State Analysis

### Plugin WP Desa v1.0.2
- Plugin sistem informasi desa untuk WordPress dengan 6 modul admin + 9 shortcode + 2 CPT
- Arsitektur: OOP service-oriented, namespace `WpDesa\`, REST API controllers, server-rendered admin templates
- **Roadmap di FITUR.md**: 7 kategori fitur, 44% selesai (11/25 item checked)
- **Perbaikan selesai 2 wave**: 17 task fixes (security, performance, code quality, bug fixes)
- **README saat ini**: menyebut Alpine.js padahal sudah jQuery, belum ada changelog, belum mencerminkan perbaikan Wave 1 & 2

### Struktur kunci plugin
| Komponen | File/Lokasi | Pola |
|---|---|---|
| Entry point | `wp-desa.php` | Constants + autoloader |
| Orchestrator | `src/Core/Plugin.php` | Wiring semua module |
| Post Types | `src/Core/PostTypes.php` | `register_post_type()` + `register_taxonomy()` |
| Admin Menu | `src/Admin/Menu.php` | `add_menu_page()` + `add_submenu_page()` + subnav via query param |
| REST API | `src/Api/*Controller.php` | Extends `WP_REST_Controller` |
| Shortcodes | `src/Frontend/Shortcode.php` | `add_shortcode()` + enqueue JS |
| Templates | `templates/admin/*.php` | Server-rendered PHP, dipanggil via `require_once` |
| Database | `src/Database/Activator.php` | `dbDelta()` via `register_activation_hook` |
| CSS | `assets/css/admin/style.css` + `assets/css/frontend/style.css` | Tailwind-like utility |
| JS | `assets/js/wp-desa-admin.js` + `assets/js/wp-desa-frontend.js` | jQuery-based |

---

## Part A: Update README.md

### File: `README.md`

### What to change

1. **Perbaiki deskripsi teknologi** (line 3, 61)
   - Ganti "Alpine.js" → "jQuery + REST API"
   - Tambahkan "Chart.js" dan "GLightbox" yang sudah di-load via CDN (dengan fallback lokal)
   - Tetap sebut "Tailwind-like CSS" karena benar adanya

2. **Tambahkan section "Riwayat Perbaikan" (changelog)** setelah fitur, sebelum instalasi
   - Rangkum perbaikan Wave 1 (10 task: syntax fix, SQL fix, CSRF, CDN fallback, uninstall, dll)
   - Rangkum perbaikan Wave 2 (7 task: nested SVG fix, REST URL subdirectory, admin notice, cache, filemtime, DRY JS, double-prepare)
   - Format: daftar bullet dengan kategori (Bug Fix, Security, Performance, Code Quality)

3. **Update section "Fitur Utama"**
   - Tambahkan poin tentang GitHub Updater (fitur sudah ada tapi tidak disebut di README)
   - Tambahkan poin tentang Seed & Clear Data untuk development

4. **Perbaiki struktur folder** di README agar sesuai struktur aktual (sudah benar, tapi pastikan)

5. **Update versi** di header comment dari 1.0.2 (tidak perlu di README, hanya di wp-desa.php — tapi disebutkan di changelog)

6. **Tambahkan section "Dokumentasi"** — reference ke menu Dokumentasi di admin panel

### Tidak perlu diubah
- Shortcode table sudah lengkap
- Fitur utama section sudah mencakup 8 kategori
- Struktur folder sudah cukup akurat
- Instalasi section sudah jelas

---

## Part B: Rencana Fitur Baru (3 Wave)

Semua fitur baru mengikuti pola arsitektur yang sudah ada di plugin.

---

### Wave 3: Struktur Organisasi & Produk Hukum (Pemerintahan)

**Rasional**: Melengkapi profil desa dengan struktur perangkat dan regulasi yang berlaku.

#### 3A. Struktur Organisasi Desa

**DB Table**: `{prefix}desa_perangkat` (dibuat via Activator)

| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| nama | VARCHAR(200) | Nama perangkat |
| jabatan | VARCHAR(100) | enum-like: Kepala Desa, Sekretaris Desa, Kasi Pemerintahan, Kasi Kesejahteraan, Kasi Pelayanan, Kaur Keuangan, Kaur Umum, Kaur Perencanaan, Kadus |
| nip | VARCHAR(50) | nullable |
| foto | VARCHAR(500) | URL foto (media library) |
| parent_id | INT | nullable, FK ke id (untuk hierarki) |
| urutan | INT | Default 0, untuk sort order |

**File baru:**
- `src/Api/PerangkatController.php` — CRUD REST API (admin only), GET public
- `templates/admin/perangkat.php` — Admin page: list + form tambah/edit perangkat
- Tidak perlu template frontend terpisah — render shortcode via PHP saja

**File yang diubah:**
- `src/Core/Plugin.php` — register PerangkatController route + admin menu
- `src/Admin/Menu.php` — tambah submenu "Pemerintahan" → subnav "Struktur Organisasi", "Produk Hukum"
- `src/Database/Activator.php` — CREATE TABLE `desa_perangkat`
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_struktur]` shortcode
- `assets/css/frontend/style.css` — CSS untuk org chart (tree view)
- `assets/js/wp-desa-admin.js` — JS untuk CRUD perangkat (ajax form submit)
- `uninstall.php` — tambah DROP TABLE `desa_perangkat`

**Shortcode `[wp_desa_struktur]`**: Menampilkan struktur organisasi dengan visual hierarki (CSS-based tree), klik foto/ nama perangkat tampil detail.

**Admin Page**: "Pemerintahan" sebagai submenu baru dengan tab "Struktur Organisasi" — form tambah/edit (nama, jabatan dropdown, NIP, foto via media uploader, parent selection, urutan), tabel list, drag-to-reorder.

#### 3B. Produk Hukum Desa

**CPT**: `desa_produk_hukum` (slug: `produk-hukum-desa`)

| Setting | Value |
|---|---|
| supports | title, editor, excerpt, thumbnail |
| taxonomy | `desa_produk_hukum_cat` (Kategori: Perdes, SK Kades, Peraturan Bersama Kades) |
| public | true, show_in_rest true |

**File baru:**
- `templates/public/archive-desa_produk_hukum.php` — archive template (list dengan download link)
- `templates/public/single-desa_produk_hukum.php` — single post template

**File yang diubah:**
- `src/Core/PostTypes.php` — tambah `register_produk_hukum()`
- `src/Core/TemplateLoader.php` — tambah template override untuk CPT baru
- `src/Database/Activator.php` — seed taxonomy terms untuk produk hukum
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_produk_hukum]` shortcode (list + filter by category)
- `assets/css/frontend/style.css` — CSS untuk list produk hukum
- `uninstall.php` — tambah cleanup post type + post meta + taxonomy terms

**Shortcode `[wp_desa_produk_hukum]`**: List produk hukum dengan filter kategori, search, download link (jika ada lampiran file).

---

### Wave 4: Berita Desa, Agenda Kegiatan & Galeri

**Rasional**: Menjadikan website desa sebagai pusat informasi & komunikasi warga.

#### 4A. Berita Desa

**CPT**: `desa_berita` (slug: `berita-desa`)

| Setting | Value |
|---|---|
| supports | title, editor, excerpt, thumbnail |
| taxonomy | `desa_berita_cat` (Kategori berita) |
| public | true, show_in_rest true |

**File yang diubah:**
- `src/Core/PostTypes.php` — tambah `register_berita()`
- `src/Core/TemplateLoader.php` — tambah template override
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_berita]` (grid/list berita dengan pagination, filter kategori)
- `assets/css/frontend/style.css` — CSS untuk berita grid/list
- `uninstall.php` — tambah cleanup

**Shortcode `[wp_desa_berita]`**: Menampilkan daftar berita desa dengan thumbnail, excerpt, tanggal, pagination, filter kategori. Parameter: `limit`, `category`.

#### 4B. Agenda Kegiatan

**CPT**: `desa_agenda` (slug: `agenda-desa`)

| Setting | Value |
|---|---|
| supports | title, editor, thumbnail |
| taxonomy | `desa_agenda_cat` (Kategori agenda) |
| custom meta | `_desa_agenda_date` (date), `_desa_agenda_time` (time), `_desa_agenda_location` (text), `_desa_agenda_end_date` (date, optional) |
| public | true, show_in_rest true |

**File yang diubah:**
- `src/Core/PostTypes.php` — tambah `register_agenda()`
- `src/Core/TemplateLoader.php` — tambah template override
- `src/Admin/MetaBoxes.php` — tambah meta box untuk tanggal, waktu, lokasi agenda
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_agenda]` (kalender/list view)
- `assets/css/frontend/style.css` — CSS agenda
- `uninstall.php` — tambah cleanup

**Shortcode `[wp_desa_agenda]`**: Menampilkan agenda dalam list kronologis (upcoming). Parameter: `limit`, `view` (list/calendar). Kalender sederhana HTML/CSS.

#### 4C. Galeri Desa

**CPT**: `desa_galeri` (slug: `galeri-desa`)

| Setting | Value |
|---|---|
| supports | title, editor, thumbnail |
| custom meta | `_desa_galeri_images` (array of image URLs), `_desa_galeri_type` (foto/video) |
| taxonomy | `desa_galeri_cat` (Kategori galeri: Kegiatan, Infrastruktur, dll) |
| public | true, show_in_rest true |

**File yang diubah:**
- `src/Core/PostTypes.php` — tambah `register_galeri()`
- `src/Core/TemplateLoader.php` — tambah template override
- `src/Admin/MetaBoxes.php` — tambah meta box galeri (multi-image upload, reuse `umkm-gallery.js`)
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_galeri]` (grid foto + lightbox)
- `assets/js/wp-desa-frontend.js` — frontend lightbox untuk galeri
- `assets/css/frontend/style.css` — CSS galeri grid
- `uninstall.php` — tambah cleanup

**Shortcode `[wp_desa_galeri]`**: Grid foto dengan GLightbox (sudah ada di frontend). Parameter: `limit`, `category`.

---

### Wave 5: Peta Desa GIS & Destinasi Wisata

**Rasional**: Menampilkan peta interaktif wilayah desa dan promosi destinasi wisata.

#### 5A. Peta Desa GIS

**Pendekatan**: Menggunakan Leaflet.js (library open-source, ringan) + OpenStreetMap tiles.

**Data yang disimpan** (opsi simpel — sebagai meta post atau settings):
- Koordinat center desa (lat, lng, zoom)
- Array marker (nama, lat, lng, icon_type, deskripsi) — untuk titik penting (kantor desa, sekolah, masjid, puskesmas, dll)
- Polygon batas wilayah (GeoJSON)

**Penyimpanan**: Opsi terbaik — sebagai settings `wp_desa_settings` tambahan (simple, tidak perlu tabel baru).

**File baru:**
- `templates/admin/peta.php` — Admin page: Leaflet map editor (add/edit/delete markers, set center & zoom)

**File yang diubah:**
- `src/Admin/Menu.php` — menu "Peta Desa" → subnav "Peta Wilayah", "Destinasi Wisata", atau masuk ke "Pemerintahan"
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_peta]` (interactive Leaflet map)
- `assets/css/frontend/style.css` — CSS peta container
- `assets/js/wp-desa-admin.js` — Admin Leaflet map editor
- `assets/js/wp-desa-frontend.js` — Frontend Leaflet map (load Leaflet from CDN dengan fallback, hanya jika shortcode aktif)

**Shortcode `[wp_desa_peta]`**: Peta interaktif wilayah desa dengan marker untuk sarana prasarana. Parameter: `height` (default 500px).

#### 5B. Destinasi Wisata

**CPT**: `desa_wisata` (slug: `wisata-desa`)

| Setting | Value |
|---|---|
| supports | title, editor, excerpt, thumbnail |
| custom meta | `_desa_wisata_location` (lat,lng string), `_desa_wisata_address` (text), `_desa_wisata_phone` (text) |
| taxonomy | `desa_wisata_cat` (Kategori: Alam, Budaya, Kuliner, Buatan) |
| public | true, show_in_rest true |

**File yang diubah:**
- `src/Core/PostTypes.php` — tambah `register_wisata()`
- `src/Core/TemplateLoader.php` — tambah template override
- `src/Admin/MetaBoxes.php` — tambah meta box lokasi (latitude/longitude)
- `src/Frontend/Shortcode.php` — tambah `[wp_desa_wisata]` (grid + map view)
- `assets/css/frontend/style.css` — CSS wisata grid
- `assets/js/wp-desa-frontend.js` — integrasi Leaflet di shortcode wisata
- `uninstall.php` — tambah cleanup

**Shortcode `[wp_desa_wisata]`**: Grid destinasi wisata dengan foto, deskripsi, dan link ke halaman detail. Parameter: `limit`, `view` (grid/map).

---

## Assumptions & Decisions

1. **Tidak ada login warga** — sesuai input user: "Tunda dulu"
2. **Tidak ada Mutasi Penduduk** — tidak dipilih user
3. **Semua CPT baru mengikuti pola PostTypes.php** — register CPT + taxonomy + template loader
4. **Shortcode baru mengikuti pola Shortcode.php** — method terpisah per shortcode, register via `add_shortcode()`
5. **Admin pages baru mengikuti pola Menu.php** — subnav via `$current_tab = isset($_GET['tab'])`, template via `require_once`
6. **REST API baru mengikuti pola Controller yang ada** — extends `WP_REST_Controller`, register di Plugin.php
7. **Tidak menambah composer dependency** — mengikuti konvensi plugin tanpa composer
8. **Leaflet.js untuk peta** — open-source, ringan (~40KB), tidak perlu API key seperti Google Maps
9. **Asset management mengikuti perbaikan Wave 2** — `WP_DESA_VERSION` di production, conditional loading, CDN fallback
10. **Uninstall.php diperbarui setiap wave** — tambah cleanup untuk CPT, post meta, taxonomy terms, dan tabel baru

---

## Verification Steps

### README Update
- Buka README.md, pastikan:
  - Teknologi: jQuery (bukan Alpine.js), Chart.js, GLightbox, Tailwind-like CSS
  - Ada section "Riwayat Perbaikan" dengan daftar Wave 1 & 2
  - Struktur folder akurat
  - Ada mention GitHub Updater

### Wave 3 (Struktur Organisasi + Produk Hukum)
- Aktivasi plugin → tabel `desa_perangkat` terbuat
- Menu "Pemerintahan" muncul di admin sidebar
- Subnav "Struktur Organisasi" → bisa CRUD perangkat
- Subnav "Produk Hukum" → CPT muncul di admin menu
- `[wp_desa_struktur]` di frontend → tampil org chart
- `[wp_desa_produk_hukum]` di frontend → tampil list produk hukum

### Wave 4 (Berita + Agenda + Galeri)
- CPT berita, agenda, galeri muncul di admin menu
- Meta box agenda (tanggal, lokasi) berfungsi
- Meta box galeri (multi-image upload) berfungsi
- `[wp_desa_berita]`, `[wp_desa_agenda]`, `[wp_desa_galeri]` di frontend → tampil dengan benar

### Wave 5 (Peta + Wisata)
- Menu "Peta Desa" muncul → Leaflet map editor dengan marker CRUD
- `[wp_desa_peta]` di frontend → Leaflet map interaktif
- CPT wisata dengan meta lokasi berfungsi
- `[wp_desa_wisata]` di frontend → grid + link detail

### Uninstall
- Uninstall plugin → semua tabel baru, CPT posts, meta, taxonomy terms terhapus

---

## Sequence

1. **Part A**: Update README.md (1 task, langsung)
2. **Part B Wave 3**: Struktur Organisasi + Produk Hukum (~8 tasks)
3. **Part B Wave 4**: Berita + Agenda + Galeri (~8 tasks)
4. **Part B Wave 5**: Peta GIS + Wisata (~8 tasks)

Setiap wave bisa dikerjakan secara independen, tapi disarankan berurutan karena Wave 4 & 5 bisa me-reuse pola dari Wave 3.
