# Plan: Terapkan Hide Notices ke Semua Halaman Admin Plugin

## Ringkasan
Hapus inline style dari `residents.php` dan perkuat CSS global di `Menu.php::remove_notices()` agar semua halaman admin plugin WP Desa (dashboard, residents, layanan, keuangan, settings, dokumentasi) tidak menampilkan WordPress admin notices.

## Current State Analysis

- **Menu.php** (baris 105-111) sudah punya `remove_notices()`:
  ```php
  public function remove_notices()
  {
      $screen = get_current_screen();
      if ($screen && strpos($screen->id, 'wp-desa') !== false) {
          echo '<style>.wp-desa-dashboard .notice { display: none; }</style>';
      }
  }
  ```
  - Hook: `add_action('in_admin_header', [$menu, 'remove_notices'])` (di `Plugin.php` baris 51)
  - CSS target: `.wp-desa-dashboard .notice`
  - **Masalah**: CSS rule ini tanpa `!important`, dan cuma target `.wp-desa-dashboard`, sementara halaman residents pakai `.wp-desa-wrapper` di dalamnya. Selector `.wp-desa-dashboard .notice` sebenarnya tetap nge-match karena nesting, tapi mungkin kalah specificity.

- **residents.php** (baris 99) punya inline style sendiri:
  ```php
  <style>.wrap.wp-desa-wrapper .notice { display:none !important; }</style>
  ```
  - **Masalah**: Hanya berlaku di halaman residents, tidak di dashboard/layanan/keuangan/dll.

- Semua halaman admin plugin WP Desa lewat `AdminLayout::open()` yang render wrapper class `.wp-desa-dashboard`.

- Halaman residents dipanggil via `AdminLayout::open()` → render subnav → lalu load `residents.php`. Jadi residents.php ada DI DALAM `.wp-desa-dashboard`.

## Proposed Changes

### 1. `src/Admin/Menu.php` — Perkuat rule CSS di `remove_notices()`

**Apa**: Ganti CSS rule dari `.wp-desa-dashboard .notice` jadi selector lebih kuat dengan `!important`.

**Mengapa**: Supaya rule nge-match semua wrapping class yang dipakai di berbagai halaman dan tidak kalah specificity dari CSS lain.

**Bagaimana**:
```php
// Sebelum:
echo '<style>.wp-desa-dashboard .notice { display: none; }</style>';

// Sesudah:
echo '<style>.wp-desa-dashboard .notice, .wp-desa-wrapper .notice { display:none !important; }</style>';
```

Ini mencakup:
- `.wp-desa-dashboard .notice` — untuk halaman yang langsung di dalam AdminLayout (dashboard, layanan, keuangan, settings, dokumentasi)
- `.wp-desa-wrapper .notice` — untuk halaman residents yang punya wrapper sendiri di dalam AdminLayout

### 2. `templates/admin/residents.php` — Hapus inline style

**Apa**: Hapus baris `<style>.wrap.wp-desa-wrapper .notice { display:none !important; }</style>` dari template.

**Mengapa**: Supaya rule notifikasi terpusat di satu tempat (Menu.php) dan tidak ada duplikasi.

### 3. Tidak ada perubahan lain

- Semua halaman lain (dashboard, layanan, keuangan, settings, dokumentasi) sudah menggunakan AdminLayout wrapper `.wp-desa-dashboard`, jadi otomatis kena rule global.
- Residents juga akan kena karena `.wp-desa-wrapper` ditambahkan ke selector.

## Verification

1. Buka `admin.php?page=wp-desa` — cek tidak ada notice
2. Buka `admin.php?page=wp-desa-residents` — cek tidak ada notice
3. Buka `admin.php?page=wp-desa-residents&action=add` — cek tidak ada notice
4. Buka `admin.php?page=wp-desa-layanan` — cek tidak ada notice
5. Buka `admin.php?page=wp-desa-keuangan` — cek tidak ada notice
6. Buka `admin.php?page=wp-desa-settings` — cek tidak ada notice
7. Buka `admin.php?page=wp-desa-dokumentasi` — cek tidak ada notice
