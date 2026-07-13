<div class="wrap wp-desa-wrapper" x-data="residentsStats()">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Statistik Penduduk</h1>
            <p class="wp-desa-helper">Ringkasan demografi dan komposisi penduduk desa.</p>
        </div>
    </div>

    <!-- Cards Ringkasan -->
    <div class="wp-desa-stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Total Penduduk</p>
                    <h3 class="wp-desa-stat-value" x-text="formatNumber(stats.total)">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #eff6ff; color: #3b82f6;">
                    <span class="dashicons dashicons-groups"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Kartu Keluarga</p>
                    <h3 class="wp-desa-stat-value" x-text="formatNumber(stats.families)">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #fffbeb; color: #f59e0b;">
                    <span class="dashicons dashicons-admin-home"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Laki-laki</p>
                    <h3 class="wp-desa-stat-value" x-text="formatNumber(stats.male)">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #e0f2fe; color: #0ea5e9;">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
            </div>
        </div>

        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Perempuan</p>
                    <h3 class="wp-desa-stat-value" x-text="formatNumber(stats.female)">-</h3>
                </div>
                <div class="wp-desa-stat-icon" style="background: #fce7f3; color: #ec4899;">
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
                <template x-if="stats.jobs && stats.jobs.length > 0">
                    <table class="wp-desa-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Pekerjaan</th>
                                <th style="text-align: right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="job in stats.jobs" :key="job.label">
                                <tr>
                                    <td x-text="job.label || 'Tidak Diisi'"></td>
                                    <td style="text-align: right; font-weight: 600;" x-text="formatNumber(job.count)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
                <template x-if="!stats.jobs || stats.jobs.length === 0">
                    <p style="text-align: center; color: #94a3b8; padding: 30px 0; margin: 0;">Belum ada data.</p>
                </template>
            </div>
        </div>

        <div class="wp-desa-card">
            <div style="padding: 20px;">
                <p class="wp-desa-stat-title" style="margin-bottom: 12px;">Status Perkawinan</p>
                <template x-if="stats.maritals && stats.maritals.length > 0">
                    <table class="wp-desa-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th style="text-align: right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="m in stats.maritals" :key="m.label">
                                <tr>
                                    <td x-text="m.label"></td>
                                    <td style="text-align: right; font-weight: 600;" x-text="formatNumber(m.count)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
                <template x-if="!stats.maritals || stats.maritals.length === 0">
                    <p style="text-align: center; color: #94a3b8; padding: 30px 0; margin: 0;">Belum ada data.</p>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    const wpDesaResidentsStats = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('residentsStats', () => ({
            stats: {
                total: 0,
                male: 0,
                female: 0,
                families: 0,
                age_groups: null,
                jobs: [],
                maritals: []
            },
            genderChart: null,
            ageChart: null,

            init() {
                this.fetchStats();
            },

            fetchStats() {
                fetch(wpDesaResidentsStats.apiUrl + '/stats', {
                        headers: {
                            'X-WP-Nonce': wpDesaResidentsStats.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.stats = data;
                        this.$nextTick(() => this.renderCharts());
                    })
                    .catch(err => console.error(err));
            },

            renderCharts() {
                if (this.genderChart) this.genderChart.destroy();
                if (this.ageChart) this.ageChart.destroy();

                if (typeof Chart === 'undefined') {
                    setTimeout(() => this.renderCharts(), 500);
                    return;
                }

                const genderCtx = document.getElementById('genderChart');
                if (genderCtx) {
                    this.genderChart = new Chart(genderCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Laki-laki', 'Perempuan'],
                            datasets: [{
                                data: [this.stats.male || 0, this.stats.female || 0],
                                backgroundColor: ['#0ea5e9', '#ec4899'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }

                const ageCtx = document.getElementById('ageChart');
                if (ageCtx && this.stats.age_groups) {
                    const ag = this.stats.age_groups;
                    this.ageChart = new Chart(ageCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Anak (<18)', 'Dewasa (18-60)', 'Lansia (>60)'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [parseInt(ag.anak) || 0, parseInt(ag.dewasa) || 0, parseInt(ag.lansia) || 0],
                                backgroundColor: ['#93c5fd', '#3b82f6', '#1e40af'],
                                borderRadius: 6,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            },

            formatNumber(num) {
                if (num === null || num === undefined) return '0';
                return new Intl.NumberFormat('id-ID').format(num);
            }
        }));
    });
</script>