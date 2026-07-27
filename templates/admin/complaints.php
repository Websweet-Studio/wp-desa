<div class="wrap wp-desa-wrapper">

    <!-- CSS moved to assets/css/admin/style.css -->

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Aspirasi & Pengaduan Warga</h1>
            <p class="wp-desa-helper">Kelola aspirasi dan pengaduan dari warga.</p>
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
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="wp-desa-card">
        <!-- Tabs/Filters -->
        <div class="wp-desa-tabs">
            <div class="wp-desa-tab active">
                Semua <span class="wp-desa-tab-count">0</span>
            </div>
            <div class="wp-desa-tab">
                Pending <span class="wp-desa-tab-count">0</span>
            </div>
            <div class="wp-desa-tab">
                Diproses <span class="wp-desa-tab-count">0</span>
            </div>
            <div class="wp-desa-tab">
                Selesai <span class="wp-desa-tab-count">0</span>
            </div>
            <div class="wp-desa-tab">
                Ditolak <span class="wp-desa-tab-count">0</span>
            </div>
        </div>

        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Tracking</th>
                    <th>Kategori</th>
                    <th>Pelapor</th>
                    <th>Judul</th>
                    <th>Status</th>
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

    <!-- Detail Modal -->
    <div class="wp-desa-modal-overlay" style="display: none;">

        <div class="wp-desa-modal-content">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">Detail Aduan</h2>
                <button type="button" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <div class="wp-desa-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <div class="wp-desa-info-row">
                            <span class="wp-desa-info-label">Pelapor:</span>
                            <span class="wp-desa-info-value"></span>
                        </div>
                        <div class="wp-desa-info-row">
                            <span class="wp-desa-info-label">Kontak:</span>
                            <span class="wp-desa-info-value"></span>
                        </div>
                    </div>
                    <div>
                        <div class="wp-desa-info-row">
                            <span class="wp-desa-info-label">Kategori:</span>
                            <span class="wp-desa-info-value"></span>
                        </div>
                        <div class="wp-desa-info-row">
                            <span class="wp-desa-info-label">Tanggal:</span>
                            <span class="wp-desa-info-value"></span>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Judul:</span>
                    <div class="wp-desa-info-value" style="font-weight: 500;"></div>
                </div>

                <div style="margin-bottom: 20px;">
                    <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Isi Laporan:</span>
                    <div class="wp-desa-detail-box" style="white-space: pre-wrap;"></div>
                </div>

                <div style="margin-bottom: 20px; display: none;">
                    <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Foto Lampiran:</span>
                    <a href="#" target="_blank" style="display: block;">
                        <img src="#" style="max-width: 100%; max-height: 200px; border-radius: var(--r-sm); border: 1px solid var(--hairline);">
                    </a>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--hairline); margin: 20px 0;">

                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label">Update Status:</label>
                        <select class="wp-desa-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">Diproses</option>
                            <option value="resolved">Selesai</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="wp-desa-label">Tanggapan Admin:</label>
                        <textarea rows="3" class="wp-desa-textarea" placeholder="Tulis tanggapan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="wp-desa-modal-footer">
                <button class="wp-desa-btn wp-desa-btn-primary">Simpan Perubahan</button>
                <button class="wp-desa-btn wp-desa-btn-secondary">Tutup</button>
            </div>
        </div>
    </div>

</div>
