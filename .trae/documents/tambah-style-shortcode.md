# Plan: Tambah Style Options ke Shortcode yang Belum Punya

## Ringkasan

Tambahkan parameter `style` (classic/compact/minimal) ke 7 shortcode yang belum memiliki opsi tampilan. Mengikuti pattern yang sudah ada di shortcode lain (`wp_desa_umkm`, `wp_desa_profil`, dll).

## Current State

**Sudah punya style (10 shortcode):** layanan, aduan, keuangan, bantuan, profil, kepala_desa, statistik, umkm, single-umkm, struktur

**Belum punya style (7 shortcode):**
| Shortcode | Current Layout | File Lines |
|---|---|---|
| `wp_desa_potensi` | Grid cards (300px min), thumbnail+title+excerpt | Shortcode.php:771-825 |
| `wp_desa_produk_hukum` | Flex list, icon+title+category+date | Shortcode.php:1795-1855 |
| `wp_desa_berita` | Grid cards (300px min), thumbnail+date+title+excerpt | Shortcode.php:1857-1919 |
| `wp_desa_agenda` | Flex list, date block+title+time+location | Shortcode.php:1921-1997 |
| `wp_desa_galeri` | Grid (250px min), thumbnail+title | Shortcode.php:1999-2068 |
| `wp_desa_wisata` | Grid cards (300px min), thumbnail+title+address | Shortcode.php:2137-2206 |
| `wp_desa_peta` | Leaflet map container, no style options | Shortcode.php:2070-2135 |

Semua pure PHP-rendered (kecuali peta pakai Leaflet JS inline). Sebagian masih hardcoded inline colors, belum pakai CSS variables.

## Proposed Changes

### Pattern Umum

Setiap shortcode akan:
1. Accept `style` attribute di `shortcode_atts` dengan default `'classic'`
2. Validasi nilai: `classic`, `compact`, `minimal` (kecuali `peta`)
3. Output wrapper dengan class `wp-desa-{name}--{style}`
4. `classic` = layout saat ini
5. `compact` = card lebih kecil / padding lebih rapat
6. `minimal` = list row tanpa card/image

### File yang Diubah

1. **`src/Frontend/Shortcode.php`** — 7 render methods
2. **`templates/admin/docs-shortcode.php`** — 7 entries + style params

---

### 1. `render_potensi` (line 771-825)

**Style:**
- **classic** (current): Grid repeat(auto-fill, minmax(300px, 1fr)), gap 25px. Card: thumbnail 200px, title (20px), excerpt (20 kata), link "Baca Selengkapnya".
- **compact**: Grid repeat(auto-fill, minmax(240px, 1fr)), gap 15px. Thumbnail 120px. Padding smaller (var(--sp-md)). Title 16px, excerpt 10 kata.
- **minimal**: Flex column list. Per item: row with 48px thumbnail + title + excerpt inline + link. No card border, just border-bottom.

Approach: Variables like render_umkm. $img_h, $pad, $title_sz, $excerpt_w, $gap, $min_w based on $style.

---

### 2. `render_produk_hukum_frontend` (line 1795-1855)

Perlu refactor mayor — saat ini hardcoded inline colors (`#2563eb`, `#e2e8f0`, dll). Konversi ke CSS variables.

**Style:**
- **classic** (current-ish): Card layout per item. Icon + title + category badge + date + link "Baca →"
- **compact**: Tighter padding, smaller font, smaller icon (32px instead of 48px)
- **minimal**: Simple row without icon, just title + category + date inline, no card

Approach: Variables: $pad, $icon_sz, $title_sz, $use_card (boolean for classic/compact). Minimal uses distinct simpler structure.

---

### 3. `render_berita` (line 1857-1919)

**Style:**
- **classic** (current): Grid repeat(auto-fill, minmax(300px, 1fr)), gap 24px. Card: thumbnail 200px + date + category + title + excerpt. "Lihat Semua Berita" button.
- **compact**: Grid repeat(auto-fill, minmax(240px, 1fr)), gap 15px. Thumbnail 120px. Smaller padding, smaller font.
- **minimal**: List rows. No image. Per item: date + title (link) + category badge. border-bottom separator. No excerpt, no "Lihat Semua" card.

Approach: Variables + separate minimal block.

---

### 4. `render_agenda` (line 1921-1997)

**Style:**
- **classic** (current): Flex list. Each item: date block (56x56, yellow bg, day/month) + title + time (icon clock) + location (icon pin) + excerpt (15 words).
- **compact**: Smaller date block (40x40). Less gap. Smaller font.
- **minimal**: No date block, just inline date text + title + time. border-bottom separator. No excerpt.

Approach: Variables for date block size, font-size, gap. Minimal uses inline structure.

---

### 5. `render_galeri` (line 1999-2068)

**Style:**
- **classic** (current): Grid repeat(auto-fill, minmax(250px, 1fr)), gap 16px. Card: thumbnail (aspect-ratio 1) + title.
- **compact**: Grid repeat(auto-fill, minmax(150px, 1fr)), gap 10px. Smaller thumbnails, smaller title font.
- **minimal**: Grid repeat(auto-fill, minmax(80px, 1fr)), gap 6px. Thumbnails only, no title, no card.

Approach: Variables for minmax, gap, $show_title (false for minimal).

---

### 6. `render_wisata` (line 2137-2206)

Catatan: Sudah ada param `view` di shortcode_atts tapi tidak digunakan dalam logika. Saat ini selalu grid.

**Style:**
- **classic** (current): Grid repeat(auto-fill, minmax(300px, 1fr)), gap 24px. Card: thumbnail 200px + title + address (icon map-pin) + excerpt.
- **compact**: Grid repeat(auto-fill, minmax(240px, 1fr)). Thumbnail 120px. Smaller padding.
- **minimal**: List rows, no image. Per item: title + address inline + excerpt (1 line truncated). No card.

Approach: Same variable pattern as potensi/umkm.

---

### 7. `render_peta` (line 2070-2135)

Paling sederhana — hanya map container. Style minimalis:
- **classic** (current): Container with border-radius 8px, border 1px solid #e2e8f0, full height.
- **minimal**: Container without border, no border-radius, height 300px (fixed smaller).

Peta menggunakan Leaflet JS inline. Only height changes + container styling.

---

### Dokumen: `docs-shortcode.php`

Update 7 entries: tambahkan `params` style untuk masing-masing. Ubah `code` jadi `[shortcode style="classic"]`.

---

## Asumsi & Keputusan

- **Peta** hanya punya 2 style (classic/minimal) — map tidak punya banyak variasi layout.
- **Semua shortcode** (kecuali peta) akan menggunakan pattern variable-based rendering seperti render_umkm untuk menghindari duplikasi HTML.
- **Minimal style** untuk grid-based shortcodes (berita, potensi, wisata) akan pakai list row layout (flex, border-bottom).
- **Refactor colors**: Produk_hukum, berita, agenda, galeri, wisata masih hardcoded colors — akan dikonversi ke CSS variables (`var(--ink)`, `var(--graphite)`, `var(--primary)`, `var(--cloud)`, `var(--fog)`) sambil menambahkan style.
- **Dokumentasi**: Hanya update di docs-shortcode.php (sudah ada). Tidak perlu buat file baru.

## Verifikasi

1. Buka halaman publik yang menggunakan masing-masing shortcode dengan `style="compact"` dan `style="minimal"`
2. Pastikan empty state tetap muncul saat tidak ada data
3. Untuk berita/agenda/galeri/wisata, pastikan tombol "Lihat Semua" masih muncul dengan benar di classic & compact
4. Peta minimal: tidak ada error JS, map tetap inisialisasi
