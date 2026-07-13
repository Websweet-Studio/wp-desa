<div class="wrap wp-desa-wrapper" x-data="dashboardManager()">

    <!-- Hero Band (Cohere dark-feature-band) -->
    <section class="wp-desa-hero">
        <div class="wp-desa-hero__head">
            <span class="wp-desa-hero__eyebrow">WP Desa · Overview</span>
            <h1 class="wp-desa-hero__title">Dashboard Desa</h1>
            <p class="wp-desa-hero__sub">Ringkasan data dan statistik desa terkini — dalam satu pandangan.</p>
        </div>

        <div class="wp-desa-hero__metrics">
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value" x-text="stats.total_residents || 0">0</span>
                <span class="wp-desa-hero__label">Penduduk</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value" x-text="stats.total_letters || 0">0</span>
                <span class="wp-desa-hero__label">Surat Masuk</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value" x-text="stats.pending_letters || 0">0</span>
                <span class="wp-desa-hero__label">Menunggu</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value" x-text="stats.total_complaints || 0">0</span>
                <span class="wp-desa-hero__label">Aspirasi</span>
            </div>
            <?php
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1):
            ?>
                <div class="wp-desa-hero__metric wp-desa-hero__metric--cta">
                    <button @click="generateAllDummy" class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-primary-invert">
                        <span class="dashicons dashicons-database"></span> Generate Dummy
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Stat Cards (flat on white canvas) -->
    <div class="wp-desa-stats-grid">

        <!-- Total Potensi -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Potensi Desa</p>
                    <h3 class="wp-desa-stat-value" x-text="stats.total_potensi || 0">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-carrot"></span>
                </div>
            </div>
            <a href="<?php echo admin_url('edit.php?post_type=desa_potensi'); ?>" class="wp-desa-stat-link">Kelola Potensi &rarr;</a>
        </div>

        <!-- Total UMKM -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">UMKM Desa</p>
                    <h3 class="wp-desa-stat-value" x-text="stats.total_umkm || 0">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-store"></span>
                </div>
            </div>
            <a href="<?php echo admin_url('edit.php?post_type=desa_umkm'); ?>" class="wp-desa-stat-link">Kelola UMKM &rarr;</a>
        </div>

        <!-- Keuangan Desa -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Keuangan Desa <span x-text="stats.finance_stats?.year"></span></p>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
            </div>

            <div class="wp-desa-stat-bars">
                <div>
                    <div class="wp-desa-stat-bar-row">
                        <span>Pemasukan</span>
                        <span class="wp-desa-text-green" x-text="formatRupiah(stats.finance_stats?.income || 0)">Rp 0</span>
                    </div>
                    <div class="wp-desa-bar">
                        <div class="wp-desa-bar-fill wp-desa-bar-income" style="width: 100%;"></div>
                    </div>
                </div>
                <div>
                    <div class="wp-desa-stat-bar-row">
                        <span>Pengeluaran</span>
                        <span class="wp-desa-text-red" x-text="formatRupiah(stats.finance_stats?.expense || 0)">Rp 0</span>
                    </div>
                    <div class="wp-desa-bar">
                        <div class="wp-desa-bar-fill wp-desa-bar-expense" :style="'width: ' + calculatePercentage(stats.finance_stats?.expense, stats.finance_stats?.income) + '%'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Layanan Surat -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Status Layanan Surat</p>
                    <h3 class="wp-desa-stat-value" x-text="stats.total_letters || 0">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-email-alt"></span>
                </div>
            </div>
            <div class="wp-desa-stat-list wp-desa-stat-list--status">
                <template x-if="stats.letter_stats && stats.letter_stats.length > 0">
                    <template x-for="row in stats.letter_stats" :key="row.label">
                        <div class="wp-desa-status-row">
                            <span class="wp-desa-list-dot"
                                :class="{
                                    'is-pending': row.label === 'pending',
                                    'is-progress': row.label === 'processed',
                                    'is-resolved': row.label === 'completed',
                                    'is-rejected': row.label === 'rejected'
                                }">
                            </span>
                            <span class="wp-desa-status-name" x-text="letterStatusLabel(row.label)"></span>
                            <span class="wp-desa-status-count" x-text="row.count"></span>
                        </div>
                    </template>
                </template>
                <template x-if="!stats.letter_stats || stats.letter_stats.length === 0">
                    <p class="wp-desa-empty-state">Belum ada permohonan surat.</p>
                </template>
            </div>
        </div>

        <!-- Aspirasi Warga -->
        <div class="wp-desa-stat-card wp-desa-stat-wide">
            <div class="wp-desa-stat-top">
                <p class="wp-desa-stat-title">Aspirasi Warga Terbaru</p>
                <a href="<?php echo admin_url('admin.php?page=wp-desa-complaints'); ?>" class="wp-desa-stat-link">Lihat Semua</a>
            </div>

            <div class="wp-desa-stat-list">
                <template x-if="stats.recent_complaints && stats.recent_complaints.length > 0">
                    <template x-for="complaint in stats.recent_complaints" :key="complaint.id">
                        <div class="wp-desa-list-row">
                            <span class="wp-desa-list-dot"
                                :class="{
                                    'is-pending': complaint.status === 'pending',
                                    'is-progress': complaint.status === 'in_progress',
                                    'is-resolved': complaint.status === 'resolved',
                                    'is-rejected': complaint.status === 'rejected'
                                }">
                            </span>
                            <div class="wp-desa-list-main">
                                <p class="wp-desa-list-title" x-text="complaint.subject">Judul Aduan</p>
                                <p class="wp-desa-row-subtitle" x-text="formatDate(complaint.created_at)"></p>
                            </div>
                            <span class="wp-desa-badge"
                                :class="{
                                    'wp-desa-badge-pending': complaint.status === 'pending',
                                    'wp-desa-badge-warning': complaint.status === 'in_progress',
                                    'wp-desa-badge-success': complaint.status === 'resolved',
                                    'wp-desa-badge-danger': complaint.status === 'rejected'
                                }"
                                x-text="complaint.status.replace('_', ' ')">
                            </span>
                        </div>
                    </template>
                </template>
                <template x-if="!stats.recent_complaints || stats.recent_complaints.length === 0">
                    <p class="wp-desa-empty-state">Belum ada aspirasi masuk.</p>
                </template>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="wp-desa-charts-grid">

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Demografi Jenis Kelamin</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Status Perkawinan</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="maritalChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Pekerjaan Utama (Top 5)</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="jobChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Status Layanan Surat</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="letterChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Realisasi Bantuan Sosial</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="aidChart"></canvas>
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

            generateAllDummy() {
                if (!confirm('Apakah Anda yakin ingin membuat data dummy untuk SEMUA fitur (Penduduk, Surat, Aduan, Keuangan)?')) return;

                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/dashboard/seed-all')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            this.fetchStats();
                        } else {
                            alert('Gagal membuat data dummy.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat request.');
                    });
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
                if (this.charts.gender) this.charts.gender.destroy();
                if (this.charts.marital) this.charts.marital.destroy();
                if (this.charts.job) this.charts.job.destroy();
                if (this.charts.letter) this.charts.letter.destroy();
                if (this.charts.aid) this.charts.aid.destroy();

                this.charts.gender = this.createChart('genderChart', 'doughnut',
                    this.stats.gender_stats.map(i => i.label),
                    this.stats.gender_stats.map(i => i.count),
                    ['#1863dc', '#ff7759']
                );

                this.charts.marital = this.createChart('maritalChart', 'pie',
                    this.stats.marital_stats.map(i => i.label),
                    this.stats.marital_stats.map(i => i.count),
                    ['#003c33', '#ff7759', '#1863dc', '#93939f']
                );

                this.charts.job = this.createChart('jobChart', 'bar',
                    this.stats.job_stats.map(i => i.label),
                    this.stats.job_stats.map(i => i.count),
                    ['#17171c']
                );

                const letterColors = {
                    'pending': '#ff7759',
                    'processed': '#1863dc',
                    'completed': '#003c33',
                    'rejected': '#b30000'
                };

                this.charts.letter = this.createChart('letterChart', 'doughnut',
                    this.stats.letter_stats.map(i => i.label.charAt(0).toUpperCase() + i.label.slice(1)),
                    this.stats.letter_stats.map(i => i.count),
                    this.stats.letter_stats.map(i => letterColors[i.label] || '#93939f')
                );

                if (this.stats.program_stats) {
                    this.charts.aid = this.createDualBarChart('aidChart',
                        this.stats.program_stats.map(i => i.name),
                        this.stats.program_stats.map(i => i.quota),
                        this.stats.program_stats.map(i => i.distributed)
                    );
                }
            },

            createDualBarChart(id, labels, data1, data2) {
                const ctx = document.getElementById(id).getContext('2d');
                return new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Kuota',
                                data: data1,
                                backgroundColor: '#93939f',
                                borderWidth: 0
                            },
                            {
                                label: 'Tersalurkan',
                                data: data2,
                                backgroundColor: '#003c33',
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: {
                                ticks: {
                                    callback: function(val, index) {
                                        let label = this.getLabelForValue(val);
                                        return label.length > 15 ? label.substr(0, 15) + '...' : label;
                                    }
                                }
                            }
                        }
                    }
                });
            },

            createChart(id, type, labels, data, colors) {
                const ctx = document.getElementById(id).getContext('2d');
                return new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah',
                            data: data,
                            backgroundColor: colors,
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
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        scales: type === 'bar' ? {
                            y: {
                                beginAtZero: true
                            }
                        } : {}
                    }
                });
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return new Intl.DateTimeFormat('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                }).format(date);
            },

            calculatePercentage(value, total) {
                if (!total || total == 0) return 0;
                return Math.min((value / total) * 100, 100);
            },

            letterStatusLabel(status) {
                const labels = {
                    'pending': 'Pending',
                    'processed': 'Diproses',
                    'completed': 'Selesai',
                    'rejected': 'Ditolak'
                };
                return labels[status] || status;
            }
        }));
    });
</script>