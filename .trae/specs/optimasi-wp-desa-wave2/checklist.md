# Checklist Verifikasi — Optimasi WP Desa Wave 2

- [x] **N1**: Icon `mars` — `Icons::svg('mars')` tidak menghasilkan nested `<svg>`, HTML valid
- [x] **N2**: REST API URL — semua AJAX call di JS menggunakan `rest_url()` dari PHP, bukan hardcoded `/wp-json/...`
- [x] **N3**: Admin notices — `remove_all_actions('admin_notices')` sudah dihapus, hanya CSS hide yang tersisa
- [x] **N4**: Dashboard cache — `get_stats()` menggunakan transient, response time dashboard lebih cepat
- [x] **N5**: Cache busting — `filemtime()` hanya dipakai saat `WP_DEBUG` true; production pakai `WP_DESA_VERSION`
- [x] **N6**: DRY helpers — `wp-desa-frontend.js` tidak punya duplikasi fungsi helper
- [x] **N7**: Double-prepare — `get_complaints()` dan `get_letters()` hanya memanggil `$wpdb->prepare()` satu kali
