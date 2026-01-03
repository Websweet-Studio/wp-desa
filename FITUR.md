# Roadmap Fitur WP Desa

Dokumen ini berisi daftar rencana fitur yang akan dikembangkan untuk sistem informasi web desa.

## 1. Manajemen Kependudukan

- [x] **Database Penduduk**: CRUD data penduduk (NIK, Nama, TTL, Pekerjaan, Pendidikan, dll).
- [ ] **Kartu Keluarga (KK)**: Pengelompokan penduduk berdasarkan KK.
- [x] **Statistik Penduduk**: Visualisasi data (piramida penduduk, statistik pekerjaan, pendidikan) menggunakan Chart.js/Alpine.js.
- [ ] **Mutasi Penduduk**: Pencatatan kelahiran, kematian, pindah masuk, dan pindah keluar.

## 2. Layanan Surat Menyurat (E-Surat)

- [x] **Template Surat**: Generator surat otomatis (SKTM, Surat Pengantar, Surat Domisili, dll).
- [x] **Permohonan Surat Online**: Form frontend bagi warga untuk mengajukan surat.
- [x] **Tracking Surat**: Status permohonan surat (Diajukan -> Diproses -> Selesai).
- [ ] **Tanda Tangan Digital/QR Code**: Verifikasi keaslian surat.

## 3. Transparansi & Pemerintahan

- [x] **APBDes**: Visualisasi Anggaran Pendapatan dan Belanja Desa.
- [ ] **Struktur Organisasi**: Bagan perangkat desa (Kepala Desa, Sekdes, Kasi, Kaur, Kadus).
- [ ] **Produk Hukum**: Repositori Perdes (Peraturan Desa) dan SK Kepala Desa.

## 4. Potensi & Ekonomi Desa

- [x] **Lapak Desa**: Katalog produk UMKM warga desa (CPT `desa_umkm`).
- [ ] **Destinasi Wisata**: Profil dan galeri tempat wisata desa.
- [x] **Potensi Pertanian/Perkebunan**: Data hasil bumi utama (CPT `desa_potensi`).

## 5. Informasi & Komunikasi

- [ ] **Berita Desa**: Sistem posting artikel/kabar desa (bisa menggunakan native Post Type WP).
- [ ] **Agenda Kegiatan**: Kalender kegiatan desa.
- [x] **Pengaduan Masyarakat**: Form aspirasi dan pengaduan warga.
- [ ] **Galeri**: Foto dan Video kegiatan.

## 6. Peta Desa (GIS)

- [ ] **Peta Wilayah**: Batas desa, dusun, RW/RT (integrasi Leaflet/Google Maps).
- [ ] **Peta Sarana Prasarana**: Lokasi kantor desa, sekolah, masjid, puskesmas, dll.

## 7. Keamanan & User Level

- [ ] **Role Management**: Admin Desa, Operator, Warga.
- [ ] **Verifikasi Warga**: Login warga menggunakan NIK/No. KK.
