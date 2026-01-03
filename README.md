# WP Desa

Sistem Informasi Desa berbasis WordPress yang modern, cepat, dan terintegrasi. Dibangun dengan arsitektur OOP, REST API, dan Alpine.js untuk performa maksimal.

## 🚀 Fitur Utama

Plugin ini menyediakan solusi lengkap untuk digitalisasi desa:

### 1. Dashboard Eksekutif
- **Statistik Real-time**: Ringkasan jumlah penduduk, surat, aduan, dan keuangan.
- **Visualisasi Data**: Grafik status surat (Pending/Proses/Selesai) dan chart lainnya menggunakan Chart.js.
- **Widget Keuangan**: Pantau pemasukan dan pengeluaran desa tahun berjalan.
- **Aspirasi Terbaru**: Daftar aduan warga terbaru yang perlu ditindaklanjuti.

### 2. Layanan Mandiri & Surat Online
- **Pengajuan Surat**: Warga dapat mengajukan surat secara online (SKTM, Surat Pengantar, dll).
- **Tracking Status**: Cek status permohonan surat secara real-time.
- **Cetak Otomatis**: Template surat siap cetak.

### 3. Manajemen Kependudukan
- **Database Penduduk**: Pengelolaan data penduduk terpusat.
- **Import/Export**: Fitur import/export data format CSV untuk kemudahan migrasi data.
- **Generator Data Dummy**: Fitur built-in untuk mengisi data dummy saat testing.

### 4. Transparansi Keuangan (APBDes)
- **Pencatatan Anggaran**: Kelola Pemasukan dan Belanja desa.
- **Grafik Realisasi**: Visualisasi persentase realisasi anggaran.
- **Publikasi**: Shortcode untuk menampilkan transparansi anggaran di website.

### 5. Aspirasi & Pengaduan Warga
- **Kanal Pengaduan**: Form pelaporan masalah/aspirasi dengan dukungan upload foto.
- **Manajemen Tiket**: Status tracking (Pending -> In Progress -> Resolved).
- **Respon Admin**: Admin dapat memberikan tanggapan langsung pada aduan.

### 6. Program Bantuan Sosial
- **Manajemen Program**: Kelola data program bantuan (BLT, PKH, dll).
- **Data Penerima**: Daftar penerima bantuan by name/address (masked for public).
- **Transparansi**: Publikasi daftar penerima bantuan untuk akuntabilitas.

### 7. Potensi & UMKM Desa
- **Promosi UMKM**: Direktori UMKM desa dengan galeri foto dan kontak.
- **Potensi Wilayah**: Pemetaan potensi pertanian, wisata, dll.

## 🛠️ Teknologi

- **Backend**: PHP 7.4+ (OOP Concept), WordPress REST API.
- **Frontend**: Alpine.js (Reactive UI), Tailwind-like CSS (Clean UI), Chart.js.
- **Database**: Custom Tables (`wp_desa_residents`, `wp_desa_letters`, `wp_desa_complaints`, `wp_desa_finances`, etc) untuk performa tinggi.

## 📦 Instalasi

1. Upload folder `wp-desa` ke direktori `/wp-content/plugins/`.
2. Aktifkan plugin melalui menu **Plugins** di WordPress.
3. Tabel database akan otomatis dibuat saat aktivasi.
4. (Opsional) Gunakan fitur **Seed Data** di Dashboard untuk mengisi data contoh.

## 💻 Penggunaan Shortcode

Pasang shortcode berikut di Halaman (Page) WordPress:

| Fitur | Shortcode | Keterangan |
|-------|-----------|------------|
| **Layanan Surat** | `[wp_desa_layanan]` | Form pengajuan & tracking surat |
| **Aspirasi Warga** | `[wp_desa_aduan]` | Form pengaduan & cek status |
| **Transparansi Keuangan** | `[wp_desa_keuangan]` | Tabel & grafik APBDes |
| **Program Bantuan** | `[wp_desa_bantuan]` | Daftar program & penerima bantuan |

## 📂 Struktur Folder

```
wp-desa/
├── assets/             # CSS, JS (Admin & Frontend)
├── src/                # Source Code
│   ├── Admin/          # Menu & Meta Boxes
│   ├── Api/            # REST API Controllers
│   ├── Core/           # Core Logic (Activator, PostTypes)
│   ├── Database/       # Migrations & Seeders
│   └── Frontend/       # Shortcodes & Views
├── templates/          # Admin Views (Dashboard, Residents, etc)
└── wp-desa.php         # Main File
```

## 📄 Lisensi

GPL-2.0+
