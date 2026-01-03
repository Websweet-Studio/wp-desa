<?php
$settings = get_option('wp_desa_settings', []);
?>
<div class="wrap">
    <h1>Pengaturan Identitas Desa</h1>
    <p>Pengaturan ini akan digunakan untuk kop surat, informasi kontak, dan laporan.</p>
    
    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 20px; max-width: 800px; border: 1px solid #ccc; border-radius: 5px;">
        <form method="post" action="">
            <?php wp_nonce_field('wp_desa_settings_action', 'wp_desa_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nama_desa">Nama Desa</label></th>
                    <td>
                        <input name="nama_desa" type="text" id="nama_desa" value="<?php echo esc_attr($settings['nama_desa'] ?? ''); ?>" class="regular-text" placeholder="Contoh: Sukamaju">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="nama_kecamatan">Kecamatan</label></th>
                    <td>
                        <input name="nama_kecamatan" type="text" id="nama_kecamatan" value="<?php echo esc_attr($settings['nama_kecamatan'] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="nama_kabupaten">Kabupaten/Kota</label></th>
                    <td>
                        <input name="nama_kabupaten" type="text" id="nama_kabupaten" value="<?php echo esc_attr($settings['nama_kabupaten'] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="alamat_kantor">Alamat Kantor</label></th>
                    <td>
                        <textarea name="alamat_kantor" id="alamat_kantor" class="large-text" rows="3"><?php echo esc_textarea($settings['alamat_kantor'] ?? ''); ?></textarea>
                        <p class="description">Alamat lengkap kantor desa untuk kop surat.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="email_desa">Email Desa</label></th>
                    <td>
                        <input name="email_desa" type="email" id="email_desa" value="<?php echo esc_attr($settings['email_desa'] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="telepon_desa">Telepon/WA</label></th>
                    <td>
                        <input name="telepon_desa" type="text" id="telepon_desa" value="<?php echo esc_attr($settings['telepon_desa'] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="logo_kabupaten">Logo Kabupaten</label></th>
                    <td>
                        <input type="hidden" name="logo_kabupaten" id="logo_kabupaten" value="<?php echo esc_attr($settings['logo_kabupaten'] ?? ''); ?>">
                        <div id="logo-preview-wrapper" style="margin-bottom: 10px;">
                            <?php if (!empty($settings['logo_kabupaten'])): ?>
                                <img src="<?php echo esc_url($settings['logo_kabupaten']); ?>" style="max-width: 100px; height: auto;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="upload-logo-btn">Pilih Logo</button>
                        <button type="button" class="button" id="remove-logo-btn" style="<?php echo empty($settings['logo_kabupaten']) ? 'display:none;' : ''; ?>">Hapus</button>
                    </td>
                </tr>

                <tr><td colspan="2"><hr></td></tr>
                
                <tr>
                    <th scope="row"><label for="kepala_desa">Nama Kepala Desa</label></th>
                    <td>
                        <input name="kepala_desa" type="text" id="kepala_desa" value="<?php echo esc_attr($settings['kepala_desa'] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="nip_kepala_desa">NIP Kepala Desa</label></th>
                    <td>
                        <input name="nip_kepala_desa" type="text" id="nip_kepala_desa" value="<?php echo esc_attr($settings['nip_kepala_desa'] ?? ''); ?>" class="regular-text">
                        <p class="description">Kosongkan jika tidak ada.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="foto_kepala_desa">Foto Kepala Desa</label></th>
                    <td>
                        <input type="hidden" name="foto_kepala_desa" id="foto_kepala_desa" value="<?php echo esc_attr($settings['foto_kepala_desa'] ?? ''); ?>">
                        <div id="foto-kades-preview-wrapper" style="margin-bottom: 10px;">
                            <?php if (!empty($settings['foto_kepala_desa'])): ?>
                                <img src="<?php echo esc_url($settings['foto_kepala_desa']); ?>" style="max-width: 100px; height: auto;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="upload-foto-kades-btn">Pilih Foto</button>
                        <button type="button" class="button" id="remove-foto-kades-btn" style="<?php echo empty($settings['foto_kepala_desa']) ? 'display:none;' : ''; ?>">Hapus</button>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="wp_desa_settings_submit" id="submit" class="button button-primary" value="Simpan Pengaturan">
            </p>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    function setupMediaUploader(btnId, inputId, previewId, removeBtnId) {
        var mediaUploader;
        
        $(btnId).click(function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Pilih Gambar',
                button: {
                    text: 'Pilih Gambar'
                },
                multiple: false
            });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $(inputId).val(attachment.url);
                $(previewId).html('<img src="' + attachment.url + '" style="max-width: 100px; height: auto;">');
                $(removeBtnId).show();
            });
            mediaUploader.open();
        });
        
        $(removeBtnId).click(function(e) {
            e.preventDefault();
            $(inputId).val('');
            $(previewId).html('');
            $(this).hide();
        });
    }

    setupMediaUploader('#upload-logo-btn', '#logo_kabupaten', '#logo-preview-wrapper', '#remove-logo-btn');
    setupMediaUploader('#upload-foto-kades-btn', '#foto_kepala_desa', '#foto-kades-preview-wrapper', '#remove-foto-kades-btn');
});
</script>
