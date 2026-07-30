# Plan: Layout & UI Jam Kerja — Standarisasi

## Ringkasan
Tab "Jam Kerja" saat ini tidak menggunakan struktur `wp-desa-form-grid` dan proper class design system, sehingga tampil berbeda dari tab lain (Identitas, Logo, Kepala Desa, dll). Perbaikan akan menyelaraskan tata letak, form-label, dan styling card dengan halaman lain.

## File Target
Hanya **1 file**: `templates/admin/settings.php` (tab Jam Kerja, ~lines 126-184)

## Perubahan

### 1. Bungkus konten dengan `wp-desa-form-grid`
- Padding + spacing konsisten seperti tab Identitas
- Helper text tetap pakai `wp-desa-helper`

### 2. Ganti card-per-day style
- Hapus `border-bottom` separator di header card (tidak ada di tab lain)
- Label "Jam Buka" / "Jam Tutup" ganti `wp-desa-label` (bukan inline style)
- Label hari pakai `wp-desa-label` + `font-weight: 600`

### 3. Gunakan gap constant `var(--sp-lg)`
- Grid hari pakai gap `var(--sp-lg)` konsisten dengan `wp-desa-grid-2`

### 4. jQuery dipertahankan
- Hanya perbaiki selektor jika perlu

## File yang Diubah
- `templates/admin/settings.php`

## Verifikasi
- Tab Jam Kerja visual konsisten dengan tab Identitas
- Toggle Libur tetap berfungsi
- Simpan data tetap bekerja (redirect ke tab jam-kerja)
