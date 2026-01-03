<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat - <?php echo esc_html($letter->tracking_code); ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background: #eee;
        }
        .paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm 25mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double black;
            padding-bottom: 10px;
            margin-bottom: 30px;
            position: relative;
        }
        .kop-surat img {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
            height: auto;
        }
        .kop-surat h3, .kop-surat h2, .kop-surat p {
            margin: 0;
            text-transform: uppercase;
        }
        .kop-surat h3 { font-size: 14pt; font-weight: normal; }
        .kop-surat h2 { font-size: 16pt; font-weight: bold; }
        .kop-surat p { font-size: 10pt; font-style: italic; text-transform: none; }
        
        .judul-surat {
            text-align: center;
            margin-bottom: 30px;
        }
        .judul-surat h1 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .judul-surat p {
            margin: 0;
            font-size: 11pt;
        }

        .isi-surat {
            text-align: justify;
        }
        .identitas {
            margin: 10px 0 10px 30px;
        }
        .identitas table {
            width: 100%;
        }
        .identitas td {
            vertical-align: top;
            padding: 2px 0;
        }
        .identitas td:first-child {
            width: 150px;
        }
        .identitas td:nth-child(2) {
            width: 10px;
        }

        .ttd-area {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .ttd-area p {
            margin: 0;
        }
        .ttd-area .jabatan {
            margin-bottom: 70px;
        }
        .ttd-area .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            z-index: 1000;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                margin: 0;
                width: auto;
                height: auto;
            }
            .no-print {
                display: none;
            }
        }
    </style>
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
                        <td><?php echo esc_html($letter->tempat_lahir . ', ' . $letter->tanggal_lahir); ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>:</td>
                        <td><?php echo esc_html($letter->jenis_kelamin); ?></td>
                    </tr>
                    <tr>
                        <td>Pekerjaan</td>
                        <td>:</td>
                        <td><?php echo esc_html($letter->pekerjaan); ?></td>
                    </tr>
                    <tr>
                        <td>Status Perkawinan</td>
                        <td>:</td>
                        <td><?php echo esc_html($letter->status_perkawinan); ?></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td><?php echo esc_html($letter->alamat); ?></td>
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
