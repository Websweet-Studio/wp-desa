<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat - <?php echo esc_html($letter->tracking_code); ?></title>
    <link rel="stylesheet" href="<?php echo WP_DESA_URL; ?>assets/css/admin/print.css">
</head>
<body>

    <a onclick="window.print()" class="no-print">🖨️ Cetak Dokumen</a>

    <div class="paper">
        <?php 
        $settings = get_option('wp_desa_settings', []); 
        $nama_desa = $settings['nama_desa'] ?? '[Nama Desa]';
        $nama_kecamatan = $settings['nama_kecamatan'] ?? '[Nama Kecamatan]';
        $nama_kabupaten = $settings['nama_kabupaten'] ?? '[Nama Kabupaten]';
        $alamat_kantor = $settings['alamat_kantor'] ?? 'Jl. Raya Desa No. 1';
        $kepala_desa = $settings['kepala_desa'] ?? '( Nama Kepala Desa )';

        // Ambil dari snapshot surat, fallback ke data penduduk bila kosong.
        $pick = function ($primary, $fallback) use ($letter) {
            $val = isset($letter->$primary) ? $letter->$primary : '';
            if ($val === '' || $val === null) {
                $val = isset($letter->$fallback) ? $letter->$fallback : '';
            }
            return $val;
        };
        $berlaku_mulai  = !empty($letter->berlaku_mulai) ? $letter->berlaku_mulai : $letter->created_at;
        $berlaku_sampai = !empty($letter->berlaku_sampai) ? $letter->berlaku_sampai : date('Y-m-d H:i:s', strtotime($letter->created_at . ' +1 month'));
        ?>

        <div class="kop-surat">
            <!-- Placeholder Logo -->
            <!-- <img src="https://via.placeholder.com/100" alt="Logo"> -->
            <h3>Pemerintah Kabupaten <?php echo esc_html($nama_kabupaten); ?></h3>
            <h3>Kecamatan <?php echo esc_html($nama_kecamatan); ?></h3>
            <h2>Desa <?php echo esc_html($nama_desa); ?></h2>
            <p>Alamat: <?php echo esc_html($alamat_kantor); ?></p>
        </div>

        <div class="judul-surat">
            <h1><?php echo esc_html($letter->type_name); ?></h1>
            <p>Nomor: 470 / <?php echo esc_html($letter->id); ?> / DS / <?php echo date('Y'); ?></p>
        </div>

        <div class="isi-surat">
            <p>Yang bertanda tangan di bawah ini Kepala Desa <?php echo esc_html($nama_desa); ?>, Kecamatan <?php echo esc_html($nama_kecamatan); ?>, Kabupaten <?php echo esc_html($nama_kabupaten); ?>, menerangkan bahwa:</p>

            <div class="identitas">
                <table>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>:</td>
                        <td><strong><?php echo esc_html($letter->name); ?></strong></td>
                    </tr>
                    <tr>
                        <td>NIK</td>
                        <td>:</td>
                        <td><?php echo esc_html($letter->nik); ?></td>
                    </tr>
                    <tr>
                        <td>Tempat/Tgl Lahir</td>
                        <td>:</td>
                        <td><?php echo esc_html(trim($pick('tempat_lahir', 'r_tempat_lahir') . ', ' . $pick('tanggal_lahir', 'r_tanggal_lahir'), ', ')); ?></td>
                    </tr>
                    <tr>
                        <td>Warga Negara</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('warganegara', 'r_warganegara') ?: 'Indonesia'); ?></td>
                    </tr>
                    <tr>
                        <td>Agama</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('agama', 'r_agama') ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('jenis_kelamin', 'r_jenis_kelamin')); ?></td>
                    </tr>
                    <tr>
                        <td>Pekerjaan</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('pekerjaan', 'r_pekerjaan')); ?></td>
                    </tr>
                    <tr>
                        <td>Status Perkawinan</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('status_perkawinan', 'r_status_perkawinan')); ?></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td><?php echo esc_html($pick('alamat', 'r_alamat')); ?></td>
                    </tr>
                    <tr>
                        <td>Berlaku</td>
                        <td>:</td>
                        <td><?php echo esc_html(date_i18n('d-m-Y', strtotime($berlaku_mulai)) . ' s/d ' . date_i18n('d-m-Y', strtotime($berlaku_sampai))); ?></td>
                    </tr>
                </table>
            </div>

            <p>Orang tersebut di atas adalah benar-benar warga penduduk Desa <?php echo esc_html($nama_desa); ?> yang berdomisili di alamat tersebut.</p>
            
            <p>Surat keterangan ini dibuat untuk keperluan: <strong><?php echo !empty($letter->details) ? esc_html($letter->details) : 'Administrasi'; ?></strong>.</p>

            <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <div class="ttd-area">
            <p><?php echo esc_html($nama_desa); ?>, <?php echo date_i18n('d F Y'); ?></p>
            <p class="jabatan">Kepala Desa</p>
            <p class="nama"><?php echo esc_html($kepala_desa); ?></p>
        </div>
    </div>

</body>
</html>
