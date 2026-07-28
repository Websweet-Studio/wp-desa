<div class="wrap wp-desa-wrapper">

    <!-- Cards Ringkasan -->
    <div class="wp-desa-stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Total Penduduk</p>
                    <h3 class="wp-desa-stat-value">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
                    <span class="dashicons dashicons-groups"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Kartu Keluarga</p>
                    <h3 class="wp-desa-stat-value">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #e6f4ea; color: #1f6b3c;">
                    <span class="dashicons dashicons-admin-home"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Laki-laki</p>
                    <h3 class="wp-desa-stat-value">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Perempuan</p>
                    <h3 class="wp-desa-stat-value">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #f9d4d2; color: #b3262b;">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="wp-desa-grid-2" style="margin-bottom: var(--sp-16);">
        <div class="wp-desa-card">
            <div style="padding: 20px;">
                <p class="wp-desa-stat-title" style="text-align: center; margin-bottom: 12px;">Komposisi Gender</p>
                <div style="position: relative; height: 260px;">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>

        <div class="wp-desa-card">
            <div style="padding: 20px;">
                <p class="wp-desa-stat-title" style="text-align: center; margin-bottom: 12px;">Kelompok Usia</p>
                <div style="position: relative; height: 260px;">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Tables -->
    <div class="wp-desa-grid-2" style="margin-bottom: var(--sp-16);">
        <div class="wp-desa-card">
            <div style="padding: 20px;">
                <p class="wp-desa-stat-title" style="margin-bottom: 12px;">Pekerjaan Terbanyak</p>
                <table class="wp-desa-table">
                    <thead>
                        <tr><th>Pekerjaan</th><th style="text-align:right">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <tr class="wp-desa-empty-state"><td colspan="2">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="wp-desa-card">
            <div style="padding: 20px;">
                <p class="wp-desa-stat-title" style="margin-bottom: 12px;">Status Perkawinan</p>
                <table class="wp-desa-table">
                    <thead>
                        <tr><th>Status</th><th style="text-align:right">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <tr class="wp-desa-empty-state"><td colspan="2">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const wpDesaResidentsStats = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };
</script>