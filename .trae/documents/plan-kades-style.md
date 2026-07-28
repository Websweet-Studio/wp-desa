# Plan: Tambahkan `style` Attribute ke [wp_desa_kepala_desa]

## Summary
Shortcode `[wp_desa_kepala_desa]` saat ini hanya punya satu layout (card centered) dengan semua styling inline. Tambahkan `style` attribute dengan 3 varian, mirip pola `[wp_desa_statistik]`.

## Current State
- [render_kepala_desa](file:///g:/DEV/wp-desa/src/Frontend/Shortcode.php#L781-L821) — tidak menerima `$atts`, semua styling hardcoded inline
- Tidak ada CSS khusus untuk kepala_desa di [style.css](file:///g:/DEV/wp-desa/assets/css/frontend/style.css)
- Hanya pakai class generic `.wp-desa-wrapper` dan `.wp-desa-stat-card`

## Proposed Changes

### 1. Shortcode.php — `render_kepala_desa` (line 781-821)

**a) Tambah `$atts` parsing:**
```php
$atts = shortcode_atts([
  'style' => 'card',
], $atts);
```
Wrapper div pakai class modifier: `wp-desa-kades--card` / `wp-desa-kades--horizontal` / `wp-desa-kades--minimal`

**b) Pertahankan layout existing sebagai `style="card"` (default, backward compatible)** — refactor inline styles ke class CSS baru.

**c) Tambah `style="horizontal"`** — foto di kiri, nama & detail di kanan (flexbox row).

**d) Tambah `style="minimal"`** — foto kecil, nama + jabatan dalam satu baris, ringkas.

### 2. style.css — Tambah CSS baru

Tiga blok CSS baru:
```css
/* --- Kepala Desa: Card (default) --- */
.wp-desa-kades--card { /* ... */ }

/* --- Kepala Desa: Horizontal --- */
.wp-desa-kades--horizontal { /* ... */ }

/* --- Kepala Desa: Minimal --- */
.wp-desa-kades--minimal { /* ... */ }
```

### Files Modified
| File | Change |
|------|--------|
| [Shortcode.php](file:///g:/DEV/wp-desa/src/Frontend/Shortcode.php#L781) | Tambah `$atts`, refactor 3 style branches, pindahkan inline styles ke class |
| [style.css](file:///g:/DEV/wp-desa/assets/css/frontend/style.css) | Tambah CSS untuk `.wp-desa-kades--card`, `--horizontal`, `--minimal` |

## Verification
- `[wp_desa_kepala_desa]` — tampil card centered (backward compatible)
- `[wp_desa_kepala_desa style="card"]` — sama dengan default
- `[wp_desa_kepala_desa style="horizontal"]` — foto kiri, teks kanan
- `[wp_desa_kepala_desa style="minimal"]` — ringkas
