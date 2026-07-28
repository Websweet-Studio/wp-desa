# Plan: Migrasi Semua Halaman Admin ke PHP Server-Side

## Ringkasan
Migrasi 5 halaman admin WP Desa dari JS AJAX SPA ke PHP server-side render (pola `residents.php`):
- Add/edit via PHP form POST (bukan modal JS)
- Pagination via PHP `<a>` link (bukan AJAX)
- Delete via AJAX (tetap, karena perlu konfirmasi native + reload)
- Halaman read-only (statistik, dashboard) dibiarkan JS karena tanpa CRUD/pagination

## Current State

| Halaman | Render | Add/Edit | Pagination | Modal |
|---------|--------|----------|------------|-------|
| residents.php | ✅ PHP | ✅ PHP POST | ✅ PHP link | ✅ None |
| residents-kk.php | ❌ JS AJAX | - | ❌ AJAX | ✅ Anggota detail |
| letters.php | ❌ JS AJAX | - (readonly) | ❌ AJAX | ✅ Update status |
| complaints.php | ❌ JS AJAX | - (readonly) | ❌ AJAX | ✅ Update + response |
| finances.php | ❌ JS AJAX | ❌ Modal AJAX | ❌ AJAX | ✅ Add/edit form |
| aid.php | ❌ JS AJAX | ❌ Modal AJAX | ❌ AJAX | ✅ Program + Recipients |
| settings.php | ✅ PHP | ✅ PHP POST | - | ✅ None |
| dashboard.php | ❌ JS AJAX | - (readonly) | - | ✅ None |
| statistik.php | ❌ JS AJAX | - (readonly) | - | ✅ None |

## Pages that DON'T need migration (read-only, no CRUD/pagination)
- **dashboard.php** — Read-only, Chart.js stats, no pagination. Keep JS AJAX.
- **residents-statistik.php** — Read-only, Chart.js, no pagination. Keep JS AJAX.
- **settings.php** — Already PHP. ✅

## Proposed Changes

### 1. residents-kk.php — Kartu Keluarga
**Apa:** Ubah dari JS AJAX ke PHP render.
- PHP query `SELECT no_kk, COUNT(*), GROUP_CONCAT(nama_lengkap)... FROM desa_residents GROUP BY no_kk` dengan pagination
- Tabel di-render PHP
- Pagination via `<a>` link
- "Lihat Anggota" → link ke `?action=detail&no_kk=XXXXX` (halaman detail dengan daftar anggota)
- Atau: modal anggota tetap pakai PHP + AJAX fetch (halaman detail lebih bersih)
- Delete: none (read-only)

### 2. letters.php — Layanan Surat
**Apa:** Ubah dari JS AJAX ke PHP render.
- PHP query dengan filter status (`$_GET['status']`)
- Tabel di-render PHP
- Pagination via `<a>` link
- Filter tabs via PHP + `<a>` link (bukan JS)
- "Detail" button → link ke `?action=detail&id=N`
- Halaman detail: form update status (POST), tombol cetak
- Delete: none (read-only)

### 3. complaints.php — Aspirasi & Pengaduan
**Apa:** Ubah dari JS AJAX ke PHP render.
- PHP query dengan filter status
- Tabel di-render PHP
- Pagination via `<a>` link
- Filter tabs via PHP + `<a>` link
- "Detail" button → link ke `?action=detail&id=N`
- Halaman detail: form update status + textarea response (POST)
- Upload foto: tampilkan di halaman detail

### 4. finances.php — Keuangan Desa
**Apa:** Ubah dari JS AJAX ke PHP render.
- Dua tab: Dashboard (summary + charts, tetap JS) + Data APBDes (PHP)
- Data tab: PHP query dengan filter `year` dan `type` via GET
- Tabel di-render PHP
- Pagination via `<a>` link
- Add/Edit via `?action=add|edit&id=N` (PHP form)
- Delete via AJAX (seperti residents)
- Dashboard tab tetap JS AJAX + Chart.js

### 5. aid.php — Program Bantuan
**Apa:** Ubah dari JS AJAX ke PHP render.
- Program list: PHP query + card layout (atau table) dengan pagination
- Add/Edit program via `?action=add|edit&id=N` (PHP form)
- Recipients: link ke `?action=recipients&program_id=N`
- Recipients table: PHP render + pagination
- Delete program/recipient via AJAX

### 6. wp-desa-admin.js — Bersihkan dead code
- Hapus `initKK()`, `initLetters()`, `initComplaints()`, `initFinances()`, `initAid()`
- Atau simpan fungsi yang masih dipake (Chart.js untuk dashboard, statistik, keuangan dashboard tab)
- Hapus modal-related code di masing-masing fungsi

### 7. REST API — Tetap dipertahankan
- Endpoint tetap ada untuk: export, import, seed, delete AJAX, chart data

## File Changes Detail

### Template files (6 files modified):
1. `templates/admin/residents-kk.php` — Rewrite ke PHP render
2. `templates/admin/letters.php` — Rewrite ke PHP render  
3. `templates/admin/complaints.php` — Rewrite ke PHP render
4. `templates/admin/finances.php` — Rewrite: tab Data jadi PHP, tab Dashboard tetap JS
5. `templates/admin/aid.php` — Rewrite ke PHP render

### JS file (1 file modified):
6. `assets/js/wp-desa-admin.js` — Hapus initKK, initLetters, initComplaints; sederhanakan initFinances, initAid

### PHP file (no changes needed):
- `src/Admin/Menu.php` — render methods already call templates
- REST controllers — no changes needed
- DB schema — no changes needed

## Complexity Per Page (1-5):
- residents-kk.php: ⭐⭐ (read-only, simple query)
- letters.php: ⭐⭐⭐ (filter + detail page)
- complaints.php: ⭐⭐⭐ (filter + detail + response)
- finances.php: ⭐⭐⭐⭐⭐ (two tabs, filter year/type, charts, CRUD)
- aid.php: ⭐⭐⭐⭐⭐ (two views, card layout, CRUD program + recipients)

## Assumptions
- User ingin DELETE tetap via AJAX (konfirmasi native + reload), seperti residents.php
- Chart.js di dashboard dan statistik tetap JS (tidak bisa PHP)
- Export/Import tetap via AJAX
- Filter tabs untuk letters/complaints pake PHP + GET params

## Verification
1. Buka setiap halaman, cek tabel muncul tanpa JS error
2. Cek pagination klik link, halaman berubah
3. Cek add/edit form submit, data tersimpan
4. Cek delete, konfirmasi + reload
5. Cek filter tabs (letters/complaints)
6. Cek tidak ada JS error di console
