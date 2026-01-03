<?php
$settings = get_option('wp_desa_settings', []);
?>
<div class="wrap wp-desa-wrapper" x-data="settingsManager()">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Pengaturan Identitas Desa</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola informasi dasar desa, kontak, dan pejabat desa.</p>
        </div>
    </div>

    <div class="wp-desa-card" style="max-width: 800px;">
        <!-- Tabs Navigation -->
        <div class="wp-desa-tabs">
            <div class="wp-desa-tab" :class="{'active': activeTab === 'identitas'}" @click="activeTab = 'identitas'">
                Identitas & Kontak
            </div>
            <div class="wp-desa-tab" :class="{'active': activeTab === 'media'}" @click="activeTab = 'media'">
                Logo & Media
            </div>
            <div class="wp-desa-tab" :class="{'active': activeTab === 'pejabat'}" @click="activeTab = 'pejabat'">
                Kepala Desa
            </div>
            <div class="wp-desa-tab" :class="{'active': activeTab === 'sistem'}" @click="activeTab = 'sistem'">
                Pengaturan Sistem
            </div>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('wp_desa_settings_action', 'wp_desa_settings_nonce'); ?>

            <!-- Tab: Identitas & Kontak -->
            <div x-show="activeTab === 'identitas'" class="wp-desa-tab-content" style="display: none;">
                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label" for="nama_desa">Nama Desa</label>
                        <input name="nama_desa" type="text" id="nama_desa" value="<?php echo esc_attr($settings['nama_desa'] ?? ''); ?>" class="wp-desa-input" placeholder="Contoh: Sukamaju">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
            <div x-show="activeTab === 'media'" class="wp-desa-tab-content" style="display: none;">
                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label">Logo Kabupaten</label>
                        <p class="wp-desa-helper" style="margin-bottom: 12px;">Digunakan pada kop surat resmi.</p>

                        <input type="hidden" name="logo_kabupaten" id="logo_kabupaten" value="<?php echo esc_attr($settings['logo_kabupaten'] ?? ''); ?>">

                        <div id="logo-preview-wrapper" class="wp-desa-image-preview">
                            <?php if (!empty($settings['logo_kabupaten'])): ?>
                                <img src="<?php echo esc_url($settings['logo_kabupaten']); ?>">
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image" style="color: #cbd5e1; font-size: 32px; width: 32px; height: 32px;"></span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="wp-desa-btn wp-desa-btn-secondary" id="upload-logo-btn">
                                <span class="dashicons dashicons-upload"></span> Pilih Logo
                            </button>
                            <button type="button" class="wp-desa-btn wp-desa-btn-danger" id="remove-logo-btn" style="<?php echo empty($settings['logo_kabupaten']) ? 'display:none;' : ''; ?>">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Kepala Desa -->
            <div x-show="activeTab === 'pejabat'" class="wp-desa-tab-content" style="display: none;">
                <div class="wp-desa-form-grid">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
                                <span class="dashicons dashicons-format-image" style="color: #cbd5e1; font-size: 32px; width: 32px; height: 32px;"></span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="wp-desa-btn wp-desa-btn-secondary" id="upload-foto-kades-btn">
                                <span class="dashicons dashicons-upload"></span> Pilih Foto
                            </button>
                            <button type="button" class="wp-desa-btn wp-desa-btn-danger" id="remove-foto-kades-btn" style="<?php echo empty($settings['foto_kepala_desa']) ? 'display:none;' : ''; ?>">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Pengaturan Sistem -->
            <div x-show="activeTab === 'sistem'" class="wp-desa-tab-content" style="display: none;">
                <div class="wp-desa-form-grid">
                    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <label class="wp-desa-label" for="dev_mode" style="font-size: 16px; margin-bottom: 4px;">Development Mode</label>
                                <p class="wp-desa-helper" style="margin: 0;">Aktifkan fitur pengembangan seperti tombol Generate Dummy Data.</p>
                            </div>
                            <div>
                                <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                                    <input type="checkbox" name="dev_mode" id="dev_mode" value="1" <?php checked($settings['dev_mode'] ?? 0, 1); ?> style="opacity: 0; width: 0; height: 0;">
                                    <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px;"></span>
                                </label>
                                <style>
                                    input:checked + .slider {
                                        background-color: #2563eb;
                                    }
                                    input:focus + .slider {
                                        box-shadow: 0 0 1px #2563eb;
                                    }
                                    .slider:before {
                                        position: absolute;
                                        content: "";
                                        height: 18px;
                                        width: 18px;
                                        left: 4px;
                                        bottom: 4px;
                                        background-color: white;
                                        transition: .4s;
                                        border-radius: 50%;
                                    }
                                    input:checked + .slider:before {
                                        transform: translateX(24px);
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding: 20px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: right;">
                <button type="submit" name="wp_desa_settings_submit" id="submit" class="wp-desa-btn wp-desa-btn-primary">
                    <span class="dashicons dashicons-saved"></span> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- Notification Toast -->
    <div x-show="notification.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="wp-desa-toast"
        :class="notification.type"
        style="display: none;">
        <span class="dashicons dashicons-yes-alt" style="font-size: 20px;"></span>
        <span x-text="notification.message"></span>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsManager', () => ({
            activeTab: 'identitas',
            notification: {
                show: false,
                message: '',
                type: 'success'
            },
            init() {
                // Check for settings-updated query param
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('settings-updated') === 'true') {
                    this.showNotification('Pengaturan berhasil disimpan!', 'success');

                    // Remove param from URL without reload
                    const newUrl = window.location.pathname + '?page=wp-desa-settings';
                    window.history.replaceState({}, document.title, newUrl);
                }
            },
            showNotification(message, type = 'success') {
                this.notification.message = message;
                this.notification.type = type;
                this.notification.show = true;

                setTimeout(() => {
                    this.notification.show = false;
                }, 3000);
            }
        }));
    });

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
                    $(removeBtnId).show();
                });
                mediaUploader.open();
            });

            $(removeBtnId).click(function(e) {
                e.preventDefault();
                $(inputId).val('');
                $(previewId).html('<span class="dashicons dashicons-format-image" style="color: #cbd5e1; font-size: 32px; width: 32px; height: 32px;"></span>');
                $(this).hide();
            });
        }

        setupMediaUploader('#upload-logo-btn', '#logo_kabupaten', '#logo-preview-wrapper', '#remove-logo-btn');
        setupMediaUploader('#upload-foto-kades-btn', '#foto_kepala_desa', '#foto-kades-preview-wrapper', '#remove-foto-kades-btn');
    });
</script>