# Plan: Perbaiki & Tambah Seeder

## Summary

Memperbaiki bug di `Seeder.php` yang ada dan menambahkan seeder untuk entitas yang belum memiliki seeder: **Perangkat Desa** (tabel kustom) dan **7 Custom Post Types** (Potensi, UMKM, Produk Hukum, Berita, Agenda, Galeri, Wisata) beserta taxonomy terms dan meta fields-nya.

## Current State

- **1 file seeder**: `src/Database/Seeder.php` — berisi method `run()`, `seed_letters()`, `seed_complaints()`, `seed_finances()`, `seed_aid()`
- **Inline seed di Activator**: letter types (5) + potensi categories (4 taxonomy terms)
- **Tabel tanpa seeder**: `{prefix}desa_perangkat`
- **CPT tanpa seeder**: 7 post types (desa_potensi, desa_umkm, desa_produk_hukum, desa_berita, desa_agenda, desa_galeri, desa_wisata) — masing-masing dengan taxonomy + meta fields

## Issues di Existing Seeders

1. **`generate_nik()` — tanggal invalid**: bisa generate 30 Feb, 31 Apr, dll. Perlu validasi bulan vs hari.
2. **`run()` — infinite loop risk**: `$i--; continue;` saat NIK duplikat bisa loop forever kalau tabel sudah penuh. Perlu max retry (`$attempts++`).
3. **`seed_letters()` — tracking_code pakai `wp_generate_password`**: bisa menghasilkan karakter spesial (underscore, dll). Sebaiknya pake `wp_generate_password(8, false)` → `$length = 8, $special_chars = false` sudah benar, tapi perlu `wp_rand()` manual biar lebih predictable dan safe.
4. **`seed_letters()` — `$count` parameter bisa > total residents**: `$min($count, $total)` sudah benar.
5. **`seed_aid()` — program names hardcoded tahun 2024**: perlu dinamis menggunakan tahun berjalan.
6. **`seed_finances()` — cuma seed tahun berjalan**: baiknya seed 2-3 tahun terakhir biar ada data historis.

## Proposed Changes

### 1. `src/Database/Seeder.php` — Perbaiki Existing + Tambah Method Baru

#### A. Fix `generate_nik()` — validasi tanggal
- Gunakan `checkdate()` untuk validasi bulan vs hari.
- Jika invalid, generate ulang sampai valid (max 5 attempts).

#### B. Fix `run()` — tambah max retry untuk duplicate NIK
- Tambah counter `$duplicate_attempts`, max 50 retries berturut-turut, lalu skip.

#### C. Fix `seed_finances()` — multi-year
- Loop tahun dari `date('Y') - 2` sampai `date('Y')`.
- Bagi `$count` secara proporsional per tahun.

#### D. Fix `seed_aid()` — tahun dinamis
- Gunakan `date('Y')` untuk program baru, bukan hardcoded '2024'.

#### E. Tambah `seed_perangkat()`
- Seed data perangkat desa: Kepala Desa, Sekretaris, Kaur, Kadus, dll.
- `$count` parameter, parent_id untuk struktur organisasi.
- Data realistik: nama, jabatan, nip, urutan.

#### F. Tambah `seed_potensi()`
- Seed `desa_potensi` posts via `wp_insert_post()`.
- Assign ke kategori potensi (Pertanian, Peternakan, dll).
- `$count` parameter.

#### G. Tambah `seed_umkm()`
- Seed `desa_umkm` posts via `wp_insert_post()`.
- Set meta: `_desa_umkm_phone`, `_desa_umkm_location`, `_desa_umkm_gallery`.
- Assign ke kategori UMKM.
- `$count` parameter.

#### H. Tambah `seed_produk_hukum()`
- Seed `desa_produk_hukum` posts.
- Assign ke kategori produk hukum.
- `$count` parameter.

#### I. Tambah `seed_berita()`
- Seed `desa_berita` posts dengan konten realistik.
- Assign ke kategori berita.
- `$count` parameter.

#### J. Tambah `seed_agenda()`
- Seed `desa_agenda` posts.
- Set meta: `_desa_agenda_date`, `_desa_agenda_time`, `_desa_agenda_location`, `_desa_agenda_end_date`.
- Assign ke kategori agenda.
- `$count` parameter.

#### K. Tambah `seed_galeri()`
- Seed `desa_galeri` posts.
- Set meta: `_desa_galeri_images`, `_desa_galeri_type`.
- Assign ke kategori galeri.
- `$count` parameter.

#### L. Tambah `seed_wisata()`
- Seed `desa_wisata` posts.
- Set meta: `_desa_wisata_location`, `_desa_wisata_address`, `_desa_wisata_phone`.
- Assign ke kategori wisata.
- `$count` parameter.

#### M. Update `run()` — panggil semua seeder baru
- Urutan panggilan setelah seeder yang sudah ada:
  1. `seed_perangkat(intval($count / 5))`
  2. `seed_potensi(intval($count / 5))`
  3. `seed_umkm(intval($count / 5))`
  4. `seed_produk_hukum(intval($count / 4))`
  5. `seed_berita(intval($count / 3))`
  6. `seed_agenda(intval($count / 4))`
  7. `seed_galeri(intval($count / 5))`
  8. `seed_wisata(intval($count / 5))`

### 2. `src/Database/Activator.php` — Pindahkan inline seed letter_types ke method

- Extract inline seeding letter_types jadi method `seed_letter_types()` static.
- Panggil dari `activate()` dan dari `Seeder.php`.

### 3. File yang DIUBAH (tidak ada file baru)

| File | Perubahan |
|---|---|
| `src/Database/Seeder.php` | Fix generate_nik, run(), seed_finances(), seed_aid(). Tambah 8 method seeder baru. Update run() panggil semua. |
| `src/Database/Activator.php` | Extract inline letter_types seed ke method sendiri. |

No new files. Semua perubahan dalam 2 file yang sudah ada.

## Verification

1. Buka WordPress admin → jalankan seeder via API atau trigger.
2. Cek tabel `{prefix}desa_perangkat` — harus ada data.
3. Cek masing-masing CPT di admin — harus ada posts + taxonomy terms + meta.
4. Cek tabel `{prefix}desa_residents` — NIK harus valid (tidak ada 30 Feb).
5. Cek tabel `{prefix}desa_finances` — data dari 3 tahun terakhir.
6. Run multiple times — tidak boleh duplicate key error atau infinite loop.
