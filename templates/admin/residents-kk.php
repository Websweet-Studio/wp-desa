<div class="wrap wp-desa-wrapper">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Kartu Keluarga</h1>
            <p class="wp-desa-helper">Kelompokkan penduduk berdasarkan Nomor Kartu Keluarga.</p>
        </div>
    </div>

    <div class="wp-desa-card">
        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>No. KK</th>
                    <th>Kepala Keluarga</th>
                    <th>Alamat</th>
                    <th style="text-align: center;">Jumlah Anggota</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="wp-desa-empty-state">
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
                dari <span>0</span> KK
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

    <!-- Modal Anggota KK -->
    <div class="wp-desa-modal-overlay" style="display: none;">
        <div class="wp-desa-modal-content" style="max-width: 900px;">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">
                    Anggota KK: <span class="wp-desa-mono"></span>
                </h2>
                <button type="button" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <table class="wp-desa-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Tempat/Tgl Lahir</th>
                            <th>Status</th>
                            <th>Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="wp-desa-modal-footer">
                <button type="button" class="wp-desa-btn wp-desa-btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const wpDesaResidents = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };
</script>