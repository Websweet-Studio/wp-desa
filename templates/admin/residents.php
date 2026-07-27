<div class="wrap wp-desa-wrapper">

    <!-- CSS moved to assets/css/admin/style.css -->

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Data Penduduk</h1>
            <p class="wp-desa-helper">Kelola data kependudukan desa dengan mudah.</p>
        </div>
        <div class="wp-desa-actions">
            <?php
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1):
            ?>
                <button class="wp-desa-btn wp-desa-btn-danger">
                    <span class="dashicons dashicons-database"></span> Generate Dummy
                </button>
            <?php endif; ?>
            <button class="wp-desa-btn wp-desa-btn-secondary">
                <span class="dashicons dashicons-download"></span> Export
            </button>
            <button class="wp-desa-btn wp-desa-btn-secondary">
                <span class="dashicons dashicons-upload"></span> Import
            </button>
            <button class="wp-desa-btn wp-desa-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span> Tambah Penduduk
            </button>
            <input type="file" style="display:none" accept=".csv">
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="wp-desa-card">
        <!-- Table Toolbar/Filter (Optional Future) -->
        <!-- <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 10px;">
             <input type="text" placeholder="Cari penduduk..." class="wp-desa-input" style="max-width: 300px;">
        </div> -->

        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>No. KK</th>
                    <th>Nama Lengkap</th>
                    <th>Jenis Kelamin</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>Pekerjaan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="wp-desa-empty-state">
                        <span class="dashicons dashicons-update wp-desa-spinner"></span>
                        <div class="wp-desa-mt-8">Memuat data...</div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination" style="display: none;">
            <div class="wp-desa-pagination-info">
                Menampilkan <span>1</span>
                sampai <span>20</span>
                dari <span>0</span> data
            </div>
            <div class="wp-desa-pagination-controls">
                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <span class="wp-desa-pagination-page">
                    Halaman <span>1</span> dari <span>1</span>
                </span>
                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="wp-desa-modal-overlay" style="display: none;">

        <div class="wp-desa-modal-content">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">Tambah Penduduk</h2>
                <button type="button" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <form>
                <div class="wp-desa-modal-body">
                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-nik">NIK <span class="wp-desa-req">*</span></label>
                            <input type="text" id="res-nik" required class="wp-desa-input" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-no_kk">No. KK</label>
                            <input type="text" id="res-no_kk" class="wp-desa-input" placeholder="16 digit No. KK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="res-nama">Nama Lengkap <span class="wp-desa-req">*</span></label>
                            <input type="text" id="res-nama" required class="wp-desa-input" placeholder="Sesuai KTP">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-jk">Jenis Kelamin</label>
                            <select id="res-jk" class="wp-desa-select">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-sp">Status Perkawinan</label>
                            <select id="res-sp" class="wp-desa-select">
                                <option value="Belum Kawin">Belum Kawin</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-tl">Tempat Lahir</label>
                            <input type="text" id="res-tl" class="wp-desa-input">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="res-tgl">Tanggal Lahir</label>
                            <input type="date" id="res-tgl" class="wp-desa-input">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="res-pkj">Pekerjaan</label>
                            <input type="text" id="res-pkj" class="wp-desa-input" placeholder="Contoh: Petani, PNS, Wiraswasta">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="res-alamat">Alamat Lengkap</label>
                            <textarea id="res-alamat" rows="3" class="wp-desa-textarea" placeholder="Jalan, RT/RW, Dusun..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="wp-desa-modal-footer">
                    <button type="button" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    const wpDesaSettings = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };
</script>
