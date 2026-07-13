# Checklist Verifikasi

- [x] **C1**: Shortcode tracking surat — sintaks `<span:>` sudah diperbaiki menjadi binding `:class` yang valid, template tidak error
- [x] **C2**: Print letter — query SQL tidak lagi merujuk kolom `agama` yang tidak ada, cetak surat berhasil
- [x] **C3**: API Create — tidak ada panggilan `Activator::activate()` di method create, response time lebih cepat
- [x] **H1**: Export CSV & Print Letter — memiliki nonce verification, tidak bisa dipanggil tanpa CSRF token
- [x] **H2**: Seed endpoint — cek `dev_mode` setting sebelum mengizinkan generate dummy data
- [x] **H3**: CDN dependencies — memiliki local fallback atau script tidak rusak saat CDN offline
- [x] **H4**: Script loading — Chart.js, Glightbox, Lucide hanya dimuat di halaman yang memiliki shortcode terkait
- [x] **M1**: Admin notice — tidak dihapus total, hanya notice spesifik WP Desa yang diatur
- [x] **M2**: Settings — menggunakan `wp_redirect()` bukan JS redirect, output tidak dikirim sebelum redirect
- [ ] **M3**: i18n — (opsional, dilewati — tidak diminta user)
- [x] **M4**: Error log — semua `error_log()` dibalut `WP_DEBUG` check
- [x] **M5**: Uninstall — file `uninstall.php` ada dan membersihkan semua tabel custom + opsi
- [x] **M6**: Seeder — `ORDER BY RAND()` diganti dengan random offset yang lebih efisien
