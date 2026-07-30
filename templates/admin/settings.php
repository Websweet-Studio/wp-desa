<?php
$settings = get_option('wp_desa_settings', []);
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'identitas';
?>
<div class="wrap wp-desa-wrapper">

    <?php if (isset($_GET['seed_done'])): ?>
        <div class="notice notice-success is-dismissible"><p>Data dummy berhasil dibuat.</p></div>
    <?php elseif (isset($_GET['clear_done'])): ?>
        <div class="notice notice-success is-dismissible"><p>Semua data berhasil dihapus.</p></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('wp_desa_settings_action', 'wp_desa_settings_nonce'); ?>
        <input type="hidden" name="_current_tab" value="<?php echo esc_attr($current_tab); ?>">

        <div style="display:flex;gap:var(--sp-lg);align-items:flex-start;">

        <div class="wp-desa-card" style="flex:1;">

            <!-- Tab: Identitas & Kontak -->
            <div class="wp-desa-tab-content" style="<?php echo $current_tab !== 'identitas' ? 'display:none;' : ''; ?>">
                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label" for="nama_desa">Nama Desa</label>
                        <input name="nama_desa" type="text" id="nama_desa" value="<?php echo esc_attr($settings['nama_desa'] ?? ''); ?>" class="wp-desa-input" placeholder="Contoh: Sukamaju">
                    </div>

                    <div class="wp-desa-grid-2">
                        <div>
                            <label class="wp-desa-label" for="nama_kecamatan">Kecamatan</label>
                            <input name="nama_kecamatan" type="text" id="nama_kecamatan" value="<?php echo esc_attr($settings['nama_kecamatan'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                        <div>
                            <label class="wp-desa-label" for="nama_kabupaten">Kabupaten/Kota</label>
                            <input name="nama_kabupaten" type="text" id="nama_kabupaten" value="<?php echo esc_attr($settings['nama_kabupaten'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                    </div>

                    <div>
                        <label class="wp-desa-label" for="alamat_kantor">Alamat Kantor</label>
                        <textarea name="alamat_kantor" id="alamat_kantor" class="wp-desa-textarea" rows="3"><?php echo esc_textarea($settings['alamat_kantor'] ?? ''); ?></textarea>
                        <p class="wp-desa-helper">Alamat lengkap kantor desa untuk kop surat.</p>
                    </div>

                    <div class="wp-desa-grid-2">
                        <div>
                            <label class="wp-desa-label" for="email_desa">Email Desa</label>
                            <input name="email_desa" type="email" id="email_desa" value="<?php echo esc_attr($settings['email_desa'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                        <div>
                            <label class="wp-desa-label" for="telepon_desa">Telepon/WA</label>
                            <input name="telepon_desa" type="text" id="telepon_desa" value="<?php echo esc_attr($settings['telepon_desa'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Logo & Media -->
            <div class="wp-desa-tab-content" style="<?php echo $current_tab !== 'media' ? 'display:none;' : ''; ?>">
                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label">Logo Kabupaten</label>
                        <p class="wp-desa-helper wp-desa-mb-12">Digunakan pada kop surat resmi.</p>

                        <input type="hidden" name="logo_kabupaten" id="logo_kabupaten" value="<?php echo esc_attr($settings['logo_kabupaten'] ?? ''); ?>">

                        <div id="logo-preview-wrapper" class="wp-desa-image-preview">
                            <?php if (!empty($settings['logo_kabupaten'])): ?>
                                <img src="<?php echo esc_url($settings['logo_kabupaten']); ?>">
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image wp-desa-placeholder-icon"></span>
                            <?php endif; ?>
                        </div>

                        <div class="wp-desa-flex-gap-8">
                            <button type="button" class="wp-desa-btn wp-desa-btn-secondary" id="upload-logo-btn">
                                <span class="dashicons dashicons-upload"></span> Pilih Logo
                            </button>
                            <button type="button" class="wp-desa-btn wp-desa-btn-danger <?php echo empty($settings['logo_kabupaten']) ? 'wp-desa-hidden' : ''; ?>" id="remove-logo-btn">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Kepala Desa -->
            <div class="wp-desa-tab-content" style="<?php echo $current_tab !== 'pejabat' ? 'display:none;' : ''; ?>">
                <div class="wp-desa-form-grid">
                    <div class="wp-desa-grid-2">
                        <div>
                            <label class="wp-desa-label" for="kepala_desa">Nama Kepala Desa</label>
                            <input name="kepala_desa" type="text" id="kepala_desa" value="<?php echo esc_attr($settings['kepala_desa'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                        <div>
                            <label class="wp-desa-label" for="nip_kepala_desa">NIP Kepala Desa</label>
                            <input name="nip_kepala_desa" type="text" id="nip_kepala_desa" value="<?php echo esc_attr($settings['nip_kepala_desa'] ?? ''); ?>" class="wp-desa-input">
                        </div>
                    </div>

                    <div>
                        <label class="wp-desa-label">Foto Kepala Desa</label>
                        <input type="hidden" name="foto_kepala_desa" id="foto_kepala_desa" value="<?php echo esc_attr($settings['foto_kepala_desa'] ?? ''); ?>">

                        <div id="foto-kades-preview-wrapper" class="wp-desa-image-preview">
                            <?php if (!empty($settings['foto_kepala_desa'])): ?>
                                <img src="<?php echo esc_url($settings['foto_kepala_desa']); ?>">
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image wp-desa-placeholder-icon"></span>
                            <?php endif; ?>
                        </div>

                        <div class="wp-desa-flex-gap-8">
                            <button type="button" class="wp-desa-btn wp-desa-btn-secondary" id="upload-foto-kades-btn">
                                <span class="dashicons dashicons-upload"></span> Pilih Foto
                            </button>
                            <button type="button" class="wp-desa-btn wp-desa-btn-danger <?php echo empty($settings['foto_kepala_desa']) ? 'wp-desa-hidden' : ''; ?>" id="remove-foto-kades-btn">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Pengaturan Sistem -->
            <div class="wp-desa-tab-content" style="<?php echo $current_tab !== 'sistem' ? 'display:none;' : ''; ?>">
                <!-- Seed / Clear Data -->
                <div class="wp-desa-form-grid">
                    <div class="wp-desa-box-gray">
                        <div class="wp-desa-flex-between-center" style="margin-bottom:16px;">
                            <div>
                                <label class="wp-desa-label wp-desa-label-lg">Generate Data Dummy</label>
                                <p class="wp-desa-helper wp-desa-m-0">Buat data contoh untuk semua fitur (penduduk, surat, aduan, keuangan, bantuan).</p>
                            </div>
                            <form method="post" style="margin:0;" onsubmit="return confirm('Buat data dummy untuk SEMUA fitur?')">
                                <?php wp_nonce_field('wp_desa_seed_action', 'wp_desa_seed_nonce'); ?>
                                <input type="hidden" name="wp_desa_seed_data" value="1">
                                <button type="submit" class="wp-desa-btn wp-desa-btn-primary">
                                    <span class="dashicons dashicons-database"></span> Generate Dummy
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="wp-desa-box-gray">
                        <div class="wp-desa-flex-between-center">
                            <div>
                                <label class="wp-desa-label wp-desa-label-lg">Hapus Semua Data</label>
                                <p class="wp-desa-helper wp-desa-m-0">Kosongkan semua data dari tabel fitur plugin. Data pengaturan tidak dihapus.</p>
                            </div>
                            <form method="post" style="margin:0;" onsubmit="return confirm('Yakin hapus SEMUA data? Tindakan ini tidak bisa dibatalkan.')">
                                <?php wp_nonce_field('wp_desa_clear_action', 'wp_desa_clear_nonce'); ?>
                                <input type="hidden" name="wp_desa_clear_data" value="1">
                                <button type="submit" class="wp-desa-btn wp-desa-btn-danger">
                                    <span class="dashicons dashicons-trash"></span> Hapus Semua Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /left card -->

        <!-- Right: Save card -->
        <div class="wp-desa-card" style="width:260px;flex-shrink:0;">
            <div style="padding:var(--sp-md);">
                <h3 style="margin:0 0 var(--sp-xs);font-size:14px;font-weight:600;">Publikasikan</h3>
                <p style="color:var(--graphite);font-size:12px;margin:0 0 var(--sp-md);">Simpan perubahan pengaturan desa.</p>
                <button type="submit" name="wp_desa_settings_submit" id="submit" class="wp-desa-btn wp-desa-btn-primary" style="width:100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg> Simpan Pengaturan
                </button>
            </div>
        </div>

        </div><!-- /flex row -->
    </form>

    <!-- Notification Toast -->
    <div class="wp-desa-toast" style="display:none;">
        <span class="dashicons dashicons-yes-alt wp-desa-icon-20"></span>
        <span></span>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
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
                    $(previewId).html('<img src="' + attachment.url + '">');
                    $(removeBtnId).removeClass('wp-desa-hidden');
                });
                mediaUploader.open();
            });

            $(removeBtnId).click(function(e) {
                e.preventDefault();
                $(inputId).val('');
                $(previewId).html('<span class="dashicons dashicons-format-image wp-desa-placeholder-icon"></span>');
                $(this).addClass('wp-desa-hidden');
            });
        }

        setupMediaUploader('#upload-logo-btn', '#logo_kabupaten', '#logo-preview-wrapper', '#remove-logo-btn');
        setupMediaUploader('#upload-foto-kades-btn', '#foto_kepala_desa', '#foto-kades-preview-wrapper', '#remove-foto-kades-btn');
    });
</script>