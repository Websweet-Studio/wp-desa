# WP Desa

Sistem Informasi Desa berbasis WordPress yang modern, cepat, dan terintegrasi. Dibangun dengan arsitektur OOP, REST API, dan jQuery untuk performa maksimal.

## 🚀 Fitur Utama

Plugin ini menyediakan solusi lengkap untuk digitalisasi desa:

### 1. Dashboard Eksekutif

- **Statistik Real-time**: Ringkasan jumlah penduduk, surat, aduan, dan keuangan (dengan transient cache 5 menit).
- **Visualisasi Data**: Grafik status surat, demografi penduduk, APBDes, dan bantuan sosial menggunakan Chart.js.
- **Widget Keuangan**: Pantau pemasukan dan pengeluaran desa tahun berjalan.
- **Aspirasi Terbaru**: Daftar aduan warga terbaru yang perlu ditindaklanjuti.

### 2. Layanan Mandiri & Surat Online

- **Pengajuan Surat**: Warga dapat mengajukan surat secara online (SKTM, Surat Pengantar, dll).
- **Tracking Status**: Cek status permohonan surat secara real-time.
- **Cetak Otomatis**: Template surat siap cetak dengan data dinamis.

### 3. Manajemen Kependudukan

- **Database Penduduk**: Pengelolaan data penduduk terpusat.
- **Kartu Keluarga (KK)**: Pengelompokan penduduk berdasarkan Nomor KK.
- **Import/Export**: Fitur import/export data format CSV untuk kemudahan migrasi data.
- **Statistik Penduduk**: Visualisasi demografi (jenis kelamin, usia, status perkawinan, pekerjaan).

### 4. Transparansi Keuangan (APBDes)

- **Pencatatan Anggaran**: Kelola Pemasukan dan Belanja desa.
- **Grafik Realisasi**: Visualisasi persentase realisasi anggaran.
- **Publikasi Shortcode**: Tampilkan transparansi APBDes di halaman publik.

### 5. Aspirasi & Pengaduan Warga

- **Kanal Pengaduan**: Form pelaporan masalah/aspirasi dengan dukungan upload foto.
- **Manajemen Tiket**: Status tracking (Pending → In Progress → Resolved).
- **Respon Admin**: Admin dapat memberikan tanggapan langsung pada aduan.

### 6. Program Bantuan Sosial

- **Manajemen Program**: Kelola data program bantuan (BLT, PKH, dll).
- **Data Penerima**: Daftar penerima bantuan (NIK masked, alamat disamarkan untuk publik).
- **Transparansi**: Publikasi daftar penerima bantuan untuk akuntabilitas.

### 7. Potensi & UMKM Desa

- **Promosi UMKM**: Direktori UMKM desa dengan galeri foto, kontak WhatsApp, dan lokasi.
- **Potensi Wilayah**: Pemetaan potensi pertanian, peternakan, perikanan, dan pariwisata.

### 8. Pengaturan & Kustomisasi

- **Identitas Desa**: Pengaturan global untuk nama desa, alamat, logo, dan kepala desa.
- **Development Mode**: Generator data dummy untuk testing dan development.
- **Seed & Clear Data**: Fitur generate data dummy dan hapus semua data untuk kemudahan testing.
- **Auto Update**: GitHub Updater — periksa dan instal update plugin langsung dari GitHub releases.
- **Beaver Builder Integration**: Modul drag-and-drop khusus untuk pengguna Beaver Builder.
- **Elementor Integration**: Widget khusus untuk pengguna Elementor Page Builder.

## 🛠️ Teknologi

- **Backend**: PHP 7.4+ (OOP), WordPress REST API.
- **Frontend**: jQuery (AJAX + DOM Manipulation), Chart.js (visualisasi data), GLightbox (lightbox galeri), Tailwind-like CSS.
- **Database**: Custom Tables (`wp_desa_residents`, `wp_desa_letters`, `wp_desa_complaints`, `wp_desa_finances`, dll) untuk performa tinggi.

## 📋 Riwayat Perbaikan

### Wave 2 (v1.0.3-dev)

| Kategori         | Perbaikan                                                                                                                            |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| **Bug Fix**      | Icon `mars` nested SVG diperbaiki (Icons.php)                                                                                        |
| **Bug Fix**      | REST API URL di JS menggunakan `rest_url()` dari PHP — tidak lagi hardcoded `/wp-json/...`, kompatibel dengan WordPress subdirectory |
| **Bug Fix**      | Double-prepare SQL di ComplaintController & LetterController diperbaiki                                                              |
| **Security**     | Admin notice selective removal via CSS, bukan `remove_all_actions()` — notice kritis WordPress tetap muncul                          |
| **Performance**  | Dashboard stats di-cache dengan WordPress transients (TTL 5 menit)                                                                   |
| **Performance**  | `filemtime()` hanya dipakai saat `WP_DEBUG` aktif; production menggunakan `WP_DESA_VERSION`                                          |
| **Code Quality** | Helper functions (`formatCurrency`, `escapeHtml`, `formatDate`, `formatStatus`) di-DRY ke global scope di `wp-desa-frontend.js`      |

### Wave 1 (v1.0.2 → v1.0.3-dev)

| Kategori         | Perbaikan                                                                              |
| ---------------- | -------------------------------------------------------------------------------------- |
| **Bug Fix**      | Sintaks `<span:>` invalid di Shortcode.php diperbaiki menjadi `<span :class="...">`    |
| **Bug Fix**      | SQL JOIN ke kolom `agama` yang tidak ada di PrintHandler.php dihapus                   |
| **Performance**  | Self-healing `Activator::activate()` dihapus dari semua API Controller CREATE endpoint |
| **Security**     | Nonce/CSRF verification ditambahkan di endpoint export CSV dan print letter            |
| **Performance**  | Chart.js, Glightbox, Lucide hanya dimuat jika shortcode terkait aktif di halaman       |
| **Security**     | Admin notice removal dari `remove_all_actions` diganti selective CSS hide              |
| **Security**     | `error_log()` dengan data sensitif dibalut `WP_DEBUG` check                            |
| **Maintenance**  | File `uninstall.php` ditambahkan untuk cleanup database tables & options               |
| **Reliability**  | CDN dependencies (Chart.js, Glightbox, Lucide) ditambahkan fallback lokal              |
| **Code Quality** | Settings redirect dari JavaScript diganti `wp_redirect()` + `exit`                     |

## 📦 Instalasi

1. Upload folder `wp-desa` ke direktori `/wp-content/plugins/`.
2. Aktifkan plugin melalui menu **Plugins** di WordPress.
3. Tabel database akan otomatis dibuat saat aktivasi.
4. Buka menu **WP Desa > Pengaturan** untuk melengkapi identitas desa.
5. (Opsional) Buka **WP Desa > Pengaturan > Pengaturan Sistem** untuk generate data dummy.

## 💻 Penggunaan Shortcode

Pasang shortcode berikut di Halaman (Page) WordPress:

| Fitur                     | Shortcode                | Keterangan                        |
| ------------------------- | ------------------------ | --------------------------------- |
| **Layanan Surat**         | `[wp_desa_layanan]`      | Form pengajuan & tracking surat   |
| **Aspirasi Warga**        | `[wp_desa_aduan]`        | Form pengaduan & cek status       |
| **Transparansi Keuangan** | `[wp_desa_keuangan]`     | Tabel & grafik APBDes             |
| **Program Bantuan**       | `[wp_desa_bantuan]`      | Daftar program & penerima bantuan |
| **Statistik Desa**        | `[wp_desa_statistik]`    | Ringkasan demografi penduduk      |
| **UMKM Desa**             | `[wp_desa_umkm]`         | Direktori UMKM (Grid Layout)      |
| **Potensi Desa**          | `[wp_desa_potensi]`      | Daftar Potensi Desa               |
| **Profil Desa**           | `[wp_desa_profil]`       | Informasi identitas & kontak desa |
| **Kepala Desa**           | `[wp_desa_kepala_desa]`  | Foto & nama Kepala Desa           |
| **Struktur Organisasi**   | `[wp_desa_struktur]`     | Bagan perangkat desa              |
| **Produk Hukum**          | `[wp_desa_produk_hukum]` | Daftar Perdes & SK Kepala Desa    |
| **Berita Desa**           | `[wp_desa_berita]`       | Daftar berita & artikel desa      |
| **Agenda Kegiatan**       | `[wp_desa_agenda]`       | Kalender & daftar kegiatan desa   |
| **Galeri Desa**           | `[wp_desa_galeri]`       | Galeri foto & video kegiatan      |
| **Peta Desa**             | `[wp_desa_peta]`         | Peta interaktif wilayah desa      |
| **Destinasi Wisata**      | `[wp_desa_wisata]`       | Direktori tempat wisata desa      |

## 📖 Dokumentasi

Dokumentasi lengkap tersedia di menu **WP Desa > Dokumentasi** di WordPress Admin, mencakup:

- **Cara Penggunaan** — panduan langkah demi langkah untuk setiap modul
- **Referensi Shortcode** — daftar lengkap shortcode dengan parameter dan contoh penggunaan

## 📂 Struktur Folder

```
wp-desa/
├── assets/             # CSS, JS (Admin & Frontend)
│   ├── css/
│   │   ├── admin/      # Admin panel CSS + print stylesheet
│   │   └── frontend/   # Frontend shortcode CSS
│   └── js/
│       ├── admin/      # Admin-only JS (gallery meta box)
│       ├── wp-desa-admin.js
│       └── wp-desa-frontend.js
├── src/                # Source Code (PHP OOP)
│   ├── Admin/          # Menu, Admin Layout, Meta Boxes, Print Handler
│   ├── Api/            # REST API Controllers (extends WP_REST_Controller)
│   ├── Core/           # Plugin Orchestrator, Post Types, Template Loader, GitHub Updater
│   ├── Database/       # Table Activator & Data Seeder
│   ├── Frontend/       # Shortcodes & SVG Icons
│   └── Integrations/   # Beaver Builder & Elementor Widgets
├── templates/          # View Templates
│   ├── admin/          # Server-rendered Admin Pages (PHP)
│   └── public/         # CPT Archive & Single Templates
├── uninstall.php       # Cleanup on plugin deletion
└── wp-desa.php         # Plugin Entry Point
```

## 📄 Lisensi

GPL-2.0+
