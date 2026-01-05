<?php
$settings = get_option('wp_desa_settings', []);
?>
<div class="wrap wp-desa-wrapper" x-data="settingsManager()">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Pengaturan Identitas Desa</h1>
            <p class="wp-desa-helper">Kelola informasi dasar desa, kontak, dan pejabat desa.</p>
        </div>
    </div>

    <div class="wp-desa-card wp-desa-card-settings">
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
            <div x-show="activeTab === 'identitas'" class="wp-desa-tab-content" x-cloak>
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
            <div x-show="activeTab === 'media'" class="wp-desa-tab-content" x-cloak>
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
            <div x-show="activeTab === 'pejabat'" class="wp-desa-tab-content" x-cloak>
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
            <div x-show="activeTab === 'sistem'" class="wp-desa-tab-content" x-cloak>
                <div class="wp-desa-form-grid">
                    <div class="wp-desa-box-gray">
                        <div class="wp-desa-flex-between-center">
                            <div>
                                <label class="wp-desa-label wp-desa-label-lg" for="dev_mode">Development Mode</label>
                                <p class="wp-desa-helper wp-desa-m-0">Aktifkan fitur pengembangan seperti tombol Generate Dummy Data.</p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="dev_mode" id="dev_mode" value="1" <?php checked($settings['dev_mode'] ?? 0, 1); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wp-desa-form-actions">
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
        x-cloak>
        <span class="dashicons dashicons-yes-alt wp-desa-icon-20"></span>
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