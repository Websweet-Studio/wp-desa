<div class="wrap" x-data="dashboardManager()">
    <h1 class="wp-heading-inline">Dashboard WP Desa</h1>
    <hr class="wp-header-end">

    <div class="wp-desa-dashboard-widgets" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <!-- Stat Card: Total Penduduk -->
        <div class="wp-desa-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #64748b; font-size: 14px; font-weight: 600; text-transform: uppercase;">Total Penduduk</h3>
            <p style="font-size: 36px; font-weight: 700; color: #0f172a; margin: 10px 0;" x-text="stats.total_residents || 0"></p>
        </div>

        <!-- Chart: Jenis Kelamin -->
        <div class="wp-desa-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #0f172a; font-size: 16px; font-weight: 600;">Jenis Kelamin</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <!-- Chart: Status Perkawinan -->
        <div class="wp-desa-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #0f172a; font-size: 16px; font-weight: 600;">Status Perkawinan</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="maritalChart"></canvas>
            </div>
        </div>

        <!-- Chart: Pekerjaan (Top 5) -->
        <div class="wp-desa-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); grid-column: 1 / -1;">
            <h3 style="margin-top: 0; color: #0f172a; font-size: 16px; font-weight: 600;">Pekerjaan (Top 5)</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="jobChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardManager', () => ({
            stats: {},
            charts: {},

            init() {
                this.fetchStats();
            },

            fetchStats() {
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/dashboard/stats')); ?>', {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.stats = data;
                    this.initCharts();
                });
            },

            initCharts() {
                // Gender Chart
                this.createChart('genderChart', 'doughnut', 
                    this.stats.gender_stats.map(i => i.label), 
                    this.stats.gender_stats.map(i => i.count),
                    ['#3b82f6', '#ec4899']
                );

                // Marital Status Chart
                this.createChart('maritalChart', 'pie', 
                    this.stats.marital_stats.map(i => i.label), 
                    this.stats.marital_stats.map(i => i.count),
                    ['#10b981', '#f59e0b', '#6366f1', '#ef4444']
                );

                // Job Chart
                this.createChart('jobChart', 'bar', 
                    this.stats.job_stats.map(i => i.label), 
                    this.stats.job_stats.map(i => i.count),
                    '#8b5cf6'
                );
            },

            createChart(id, type, labels, data, colors) {
                const ctx = document.getElementById(id).getContext('2d');
                if (this.charts[id]) {
                    this.charts[id].destroy();
                }

                this.charts[id] = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah',
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: type === 'bar' ? 'none' : 'bottom',
                            }
                        }
                    }
                });
            }
        }));
    });
</script>
