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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11v6"/><path d="M20 13h2"/><path d="M3 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 2.072.578"/><circle cx="10" cy="7" r="4"/><circle cx="20" cy="19" r="2"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="m21 3-6.75 6.75"/><circle cx="10" cy="14" r="6"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15v7"/><path d="M9 19h6"/><circle cx="12" cy="9" r="6"/></svg>
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