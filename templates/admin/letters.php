<div class="wrap wp-desa-wrapper">



    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Layanan Surat Online</h1>
            <p class="wp-desa-helper">Kelola permohonan surat dari warga desa.</p>
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
                Semua <span class="wp-desa-tab-count"></span>
            </div>
            <div class="wp-desa-tab">
                Pending <span class="wp-desa-tab-count"></span>
            </div>
            <div class="wp-desa-tab">
                Diproses <span class="wp-desa-tab-count"></span>
            </div>
            <div class="wp-desa-tab">
                Selesai <span class="wp-desa-tab-count"></span>
            </div>
            <div class="wp-desa-tab">
                Ditolak <span class="wp-desa-tab-count"></span>
            </div>
        </div>

        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Tracking</th>
                    <th>Jenis Surat</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="wp-desa-empty-state">
                        <span class="dashicons dashicons-update wp-desa-spinner"></span>
                        <div class="wp-desa-mt-8">Memuat data...</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="wp-desa-empty-state">
                        <div class="wp-desa-empty-icon">📭</div>
                        <div>Tidak ada permohonan surat.</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div></div>
                        <div class="wp-desa-row-subtitle"></div>
                    </td>
                    <td class="wp-desa-mono">
                        <strong style="color: var(--ink);"></strong>
                    </td>
                    <td></td>
                    <td>
                        <div style="font-weight: 600; color: var(--ink);"></div>
                        <div class="wp-desa-row-subtitle">
                            NIK: <span></span>
                        </div>
                    </td>
                    <td>
                        <span class="wp-desa-badge">
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="wp-desa-inline-actions-end">
                            <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                Lihat Detail
                            </button>
                            <button class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-sm" title="Cetak Surat">
                                <span class="dashicons dashicons-printer"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination">
            <div class="wp-desa-pagination-info">
                Menampilkan <span></span>
                sampai <span></span>
                dari <span></span> data
            </div>
            <div class="wp-desa-pagination-controls">
                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <span class="wp-desa-pagination-page">
                    Halaman <span></span> dari <span></span>
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
                <h2 class="wp-desa-modal-title">Detail Permohonan</h2>
                <button type="button" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <div class="wp-desa-modal-body">
                <div class="wp-desa-info-row">
                    <span class="wp-desa-info-label">Jenis Surat:</span>
                    <span class="wp-desa-info-value"></span>
                </div>
                <div class="wp-desa-info-row">
                    <span class="wp-desa-info-label">Kode Tracking:</span>
                    <span class="wp-desa-info-value" style="font-family: monospace;"></span>
                </div>
                <div class="wp-desa-info-row">
                    <span class="wp-desa-info-label">Pemohon:</span>
                    <span class="wp-desa-info-value">
                        <span></span>
                        <span class="wp-desa-row-subtitle">(NIK: <span></span>)</span>
                    </span>
                </div>
                <div class="wp-desa-info-row">
                    <span class="wp-desa-info-label">No. HP:</span>
                    <span class="wp-desa-info-value"></span>
                </div>

                <div style="margin-top: 20px; margin-bottom: 20px;">
                    <label class="wp-desa-label">Keperluan / Keterangan:</label>
                    <div class="wp-desa-detail-box"></div>
                </div>

                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Update Status:</label>
                    <select class="wp-desa-select">
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="processed">Processed (Sedang Diproses)</option>
                        <option value="completed">Completed (Selesai/Siap Ambil)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                    </select>
                </div>
            </div>

            <div class="wp-desa-modal-footer">
                <button class="wp-desa-btn wp-desa-btn-secondary">
                    <span class="dashicons dashicons-printer"></span> Cetak Surat
                </button>
                <button class="wp-desa-btn wp-desa-btn-primary">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="wp-desa-toast" style="display: none;">
        <span class="dashicons"></span>
        <span></span>
        <button class="wp-desa-toast-close">
            <span class="dashicons dashicons-no"></span>
        </button>
    </div>

</div>
