<div class="wp-desa-docs">
    <div class="wp-desa-card" style="padding: 30px; margin-bottom: 25px;">
        <h2 style="margin-top: 0; color: #1a1a1a; font-size: 1.5em;">Daftar Shortcode</h2>
        <p style="color: #636363; line-height: 1.8;">
            Gunakan shortcode berikut di halaman atau post WordPress untuk menampilkan fitur WP Desa di halaman publik.
            Cukup salin shortcode ke dalam editor WordPress (Gutenberg shortcode block atau editor klasik).
        </p>
    </div>

    <?php
    $shortcodes = [
        [
            'code' => '[wp_desa_layanan style="classic"]',
            'desc' => 'Menampilkan form permohonan surat untuk warga (Formulir & Cek Status tabs). Warga memilih jenis surat, isi data, dapat kode tracking untuk cek status.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card + pill tabs), compact (line tabs, tanpa card), minimal (input underline, tanpa tabs)'],
                ['view', 'request', 'Tab default saat load: request (Formulir), tracking (Cek Status). Berlaku untuk classic, compact & minimal.'],
            ],
        ],
        [
            'code' => '[wp_desa_aduan style="classic"]',
            'desc' => 'Form aspirasi dan pengaduan warga. Warga dapat mengirim laporan, upload foto, dan cek status laporan.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card + pill tabs), compact (line tabs, tanpa card), minimal (input underline, tanpa tabs)'],
                ['view', 'form', 'Tab default saat load: form (Buat Laporan), track (Cek Status). Berlaku untuk classic, compact & minimal.'],
            ],
        ],
        [
            'code' => '[wp_desa_keuangan style="classic"]',
            'desc' => 'Menampilkan transparansi APBDes — ringkasan pendapatan & belanja, grafik, dan tabel rincian. Warga dapat memfilter berdasarkan tahun anggaran.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (lengkap — statistik, grafik, tabel), compact (ringkas — statistik & grafik), minimal (hanya ringkasan statistik)'],
            ],
        ],
        [
            'code' => '[wp_desa_bantuan style="classic"]',
            'desc' => 'Menampilkan daftar program bantuan sosial desa dan daftar penerima manfaat per program.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card penuh + tabel penerima), compact (card ringkas + tabel rapat), minimal (list row + daftar penerima sederhana)'],
            ],
        ],
        [
            'code' => '[wp_desa_profil style="classic"]',
            'desc' => 'Menampilkan profil desa — logo kabupaten, nama desa, alamat kantor, email, dan telepon (diambil dari Pengaturan).',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card tengah + grid kontak), horizontal (logo kiri, info kanan), minimal (baris kompak tanpa card)'],
            ],
        ],
        [
            'code' => '[wp_desa_kepala_desa style="card"]',
            'desc' => 'Menampilkan foto, nama, dan NIP kepala desa (diambil dari Pengaturan).',
            'params' => [
                ['style', 'card', 'Tampilan: card (foto besar bulat di tengah), horizontal (foto kiri, teks kanan), minimal (foto kecil, ringkas)'],
            ],
        ],
        [
            'code' => '[wp_desa_statistik style="classic"]',
            'desc' => 'Menampilkan statistik penduduk — total, jenis kelamin, KK, kelompok usia, pekerjaan, dan status perkawinan. Dilengkapi grafik donut. Tersedia 4 pilihan style: classic, grid, cards, minimal.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (bawaan), grid (2-kolom + bar gender), cards (horizontal ikon), minimal (baris sederhana)'],
            ],
        ],
        [
            'code' => '[wp_desa_umkm style="classic" limit="6"]',
            'desc' => 'Menampilkan daftar UMKM desa dalam bentuk grid card.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card besar + gambar 200px), compact (card kecil + gambar 120px), minimal (list baris tanpa gambar besar)'],
                ['limit', '6', 'Jumlah UMKM ditampilkan'],
            ],
        ],
        [
            'code' => '[single-umkm style="full" id="123"]',
            'desc' => 'Menampilkan detail satu UMKM berdasarkan ID.',
            'params' => [
                ['style', 'full', 'Tampilan: full (gambar header 400px + sidebar lengkap), compact (gambar 200px + sidebar rapat), minimal (tanpa gambar header, konten single column)'],
                ['id', '0 (auto)', 'ID post UMKM'],
            ],
        ],
        [
            'code' => '[wp_desa_potensi style="classic" limit="3"]',
            'desc' => 'Menampilkan daftar potensi desa dalam bentuk grid card.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card + gambar 200px), compact (card kecil + gambar 120px), minimal (list baris tanpa gambar)'],
                ['limit', '3', 'Jumlah potensi ditampilkan'],
            ],
        ],
        [
            'code' => '[wp_desa_struktur style="tree"]',
            'desc' => 'Menampilkan bagan struktur organisasi perangkat desa. Mendukung 5 mode tampilan.',
            'params' => [
                ['style', 'tree', 'Tampilan: tree (hierarki), tabel/table, card/cards, carousel, list'],
            ],
        ],
        [
            'code' => '[wp_desa_produk_hukum style="classic" limit="10"]',
            'desc' => 'Menampilkan daftar produk hukum desa (Perdes, SK Kades) dalam bentuk list.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card per-item + badge kategori), compact (border rapat tanpa bayangan), minimal (list row border-bottom saja)'],
                ['limit', '10', 'Jumlah produk hukum ditampilkan'],
                ['category', '', 'Filter berdasarkan slug kategori'],
            ],
        ],
        [
            'code' => '[wp_desa_berita style="classic" limit="6"]',
            'desc' => 'Menampilkan daftar berita desa dalam bentuk grid card dengan thumbnail.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card + thumbnail 200px), compact (card kecil + thumbnail 120px), minimal (list row tanpa thumbnail)'],
                ['limit', '6', 'Jumlah berita ditampilkan'],
                ['category', '', 'Filter berdasarkan slug kategori'],
            ],
        ],
        [
            'code' => '[wp_desa_agenda style="classic" limit="5"]',
            'desc' => 'Menampilkan agenda desa mendatang dalam bentuk list kronologis.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card per-item + tanggal blok), compact (border rapat tanpa bayangan), minimal (list row border-bottom saja)'],
                ['limit', '5', 'Jumlah agenda ditampilkan'],
                ['category', '', 'Filter berdasarkan slug kategori'],
            ],
        ],
        [
            'code' => '[wp_desa_galeri style="classic" limit="12"]',
            'desc' => 'Menampilkan galeri foto desa dalam bentuk grid.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (grid card + thumbnail seragam), compact (grid rapat tanpa bayangan), minimal (list row thumbnail kecil)'],
                ['limit', '12', 'Jumlah galeri ditampilkan'],
                ['category', '', 'Filter berdasarkan slug kategori'],
            ],
        ],
        [
            'code' => '[wp_desa_peta style="classic" height="500"]',
            'desc' => 'Menampilkan peta interaktif desa (Leaflet/OpenStreetMap) dengan marker lokasi penting.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (border + border-radius), minimal (tanpa border, tinggi 300px)'],
                ['height', '500', 'Tinggi peta dalam piksel (hanya berlaku untuk style classic)'],
            ],
        ],
        [
            'code' => '[wp_desa_wisata style="classic" limit="6"]',
            'desc' => 'Menampilkan daftar destinasi wisata desa dalam bentuk grid card.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (card + gambar 200px), compact (card kecil + gambar 120px), minimal (list row tanpa gambar)'],
                ['limit', '6', 'Jumlah destinasi ditampilkan'],
                ['category', '', 'Filter berdasarkan slug kategori wisata'],
            ],
        ],
    ];

    foreach ($shortcodes as $sc):
    ?>
    <div class="wp-desa-card" style="padding: 30px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <h3 style="margin: 0 0 8px 0; font-family: monospace; color: #1a1a1a; font-size: 1.1em;"><?php echo esc_html($sc['code']); ?></h3>
                <p style="color: #636363; line-height: 1.8; margin: 0 0 8px 0;"><?php echo esc_html($sc['desc']); ?></p>
                <?php if (!empty($sc['params'])): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em;">
                        <thead>
                            <tr style="background: #f7f7f7;">
                                <th style="text-align: left; padding: 8px 12px; border: 1px solid #e8e8e8;">Parameter</th>
                                <th style="text-align: left; padding: 8px 12px; border: 1px solid #e8e8e8;">Default</th>
                                <th style="text-align: left; padding: 8px 12px; border: 1px solid #e8e8e8;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sc['params'] as $param): ?>
                            <tr>
                                <td style="padding: 8px 12px; border: 1px solid #e8e8e8;"><code><?php echo esc_html($param[0]); ?></code></td>
                                <td style="padding: 8px 12px; border: 1px solid #e8e8e8;"><?php echo esc_html($param[1]); ?></td>
                                <td style="padding: 8px 12px; border: 1px solid #e8e8e8;"><?php echo esc_html($param[2]); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <button class="wp-desa-btn wp-desa-btn-secondary btn-copy-sc" data-sc="<?php echo esc_attr($sc['code']); ?>" style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span class="btn-copy-sc-text">Salin</span>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
jQuery(function($) {
    $('.btn-copy-sc').on('click', function() {
        var code = $(this).data('sc');
        var $btn = $(this);
        var $text = $btn.find('.btn-copy-sc-text');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(function() {
                $text.text('Tersalin!');
                setTimeout(function() { $text.text('Salin'); }, 2000);
            });
        } else {
            // Fallback
            var $ta = $('<textarea>').val(code).appendTo('body').select();
            document.execCommand('copy');
            $ta.remove();
            $text.text('Tersalin!');
            setTimeout(function() { $text.text('Salin'); }, 2000);
        }
    });
});
</script>
