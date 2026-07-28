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
            'code' => '[wp_desa_layanan]',
            'desc' => 'Menampilkan form permohonan surat untuk warga. Warga dapat memilih jenis surat, mengisi data diri, dan mendapatkan kode tracking.',
        ],
        [
            'code' => '[wp_desa_aduan]',
            'desc' => 'Form aspirasi dan pengaduan warga. Warga dapat mengirim laporan, upload foto, dan cek status laporan.',
        ],
        [
            'code' => '[wp_desa_keuangan]',
            'desc' => 'Menampilkan transparansi APBDes — ringkasan pendapatan & belanja, grafik, dan tabel rincian. Warga dapat memfilter berdasarkan tahun anggaran.',
        ],
        [
            'code' => '[wp_desa_bantuan]',
            'desc' => 'Menampilkan daftar program bantuan sosial desa dan daftar penerima manfaat per program.',
        ],
        [
            'code' => '[wp_desa_profil]',
            'desc' => 'Menampilkan profil desa — logo kabupaten, nama desa, alamat kantor, email, dan telepon (diambil dari Pengaturan).',
        ],
        [
            'code' => '[wp_desa_kepala_desa]',
            'desc' => 'Menampilkan foto, nama, dan NIP kepala desa (diambil dari Pengaturan).',
        ],
        [
            'code' => '[wp_desa_statistik style="classic"]',
            'desc' => 'Menampilkan statistik penduduk — total, jenis kelamin, KK, kelompok usia, pekerjaan, dan status perkawinan. Dilengkapi grafik donut. Tersedia 4 pilihan style: classic, grid, cards, minimal.',
            'params' => [
                ['style', 'classic', 'Tampilan: classic (bawaan), grid (2-kolom + bar gender), cards (horizontal ikon), minimal (baris sederhana)'],
            ],
        ],
        [
            'code' => '[wp_desa_umkm limit="6"]',
            'desc' => 'Menampilkan daftar UMKM desa dalam bentuk grid card.',
            'params' => [
                ['limit', '6', 'Jumlah UMKM ditampilkan'],
            ],
        ],
        [
            'code' => '[single-umkm id="123"]',
            'desc' => 'Menampilkan detail satu UMKM berdasarkan ID.',
            'params' => [
                ['id', '0 (auto)', 'ID post UMKM'],
            ],
        ],
        [
            'code' => '[wp_desa_potensi limit="3"]',
            'desc' => 'Menampilkan daftar potensi desa dalam bentuk grid card.',
            'params' => [
                ['limit', '3', 'Jumlah potensi ditampilkan'],
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
