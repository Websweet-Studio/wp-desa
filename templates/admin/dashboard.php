<div class="wrap wp-desa-wrapper" x-data="dashboardManager()" style="background-color: #f1f5f9; min-height: 100vh; padding: 20px;">
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-6" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="text-2xl font-bold text-slate-800" style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">Dashboard WP Desa</h1>
            <p class="text-slate-500" style="color: #64748b; margin: 4px 0 0 0;">Ringkasan data dan statistik desa terkini.</p>
        </div>
        <button @click="generateAllDummy" class="button button-primary" style="background-color: #2563eb; border-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-database" style="line-height: inherit;"></span> Generate Dummy Data
        </button>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px;">
        
        <!-- Total Penduduk -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 4px 0; text-transform: uppercase;">Total Penduduk</p>
                    <h3 class="text-3xl font-bold text-slate-800" style="font-size: 30px; font-weight: 700; color: #1e293b; margin: 0;" x-text="stats.total_residents || 0">0</h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-full text-blue-600" style="background: #eff6ff; padding: 12px; border-radius: 9999px; color: #3b82f6;">
                    <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600" style="margin-top: 16px; display: flex; align-items: center; font-size: 13px; color: #16a34a;">
                <span class="dashicons dashicons-arrow-up-alt" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px;"></span>
                <span>Data terupdate</span>
            </div>
        </div>

        <!-- Total Potensi -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 4px 0; text-transform: uppercase;">Potensi Desa</p>
                    <h3 class="text-3xl font-bold text-slate-800" style="font-size: 30px; font-weight: 700; color: #1e293b; margin: 0;" x-text="stats.total_potensi || 0">0</h3>
                </div>
                <div class="p-3 bg-green-50 rounded-full text-green-600" style="background: #f0fdf4; padding: 12px; border-radius: 9999px; color: #22c55e;">
                    <span class="dashicons dashicons-carrot" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div class="mt-4" style="margin-top: 16px;">
                <a href="<?php echo admin_url('edit.php?post_type=desa_potensi'); ?>" class="text-sm text-blue-600 hover:text-blue-800" style="color: #2563eb; text-decoration: none; font-size: 13px;">Kelola Potensi &rarr;</a>
            </div>
        </div>

        <!-- Total UMKM -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 4px 0; text-transform: uppercase;">UMKM Desa</p>
                    <h3 class="text-3xl font-bold text-slate-800" style="font-size: 30px; font-weight: 700; color: #1e293b; margin: 0;" x-text="stats.total_umkm || 0">0</h3>
                </div>
                <div class="p-3 bg-purple-50 rounded-full text-purple-600" style="background: #f5f3ff; padding: 12px; border-radius: 9999px; color: #8b5cf6;">
                    <span class="dashicons dashicons-store" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div class="mt-4" style="margin-top: 16px;">
                <a href="<?php echo admin_url('edit.php?post_type=desa_umkm'); ?>" class="text-sm text-blue-600 hover:text-blue-800" style="color: #2563eb; text-decoration: none; font-size: 13px;">Kelola UMKM &rarr;</a>
            </div>
        </div>

        <!-- Layanan Surat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 4px 0; text-transform: uppercase;">Layanan Surat</p>
                    <h3 class="text-3xl font-bold text-slate-800" style="font-size: 30px; font-weight: 700; color: #1e293b; margin: 0;" x-text="stats.total_letters || 0">0</h3>
                </div>
                <div class="p-3 bg-yellow-50 rounded-full text-yellow-600" style="background: #fefce8; padding: 12px; border-radius: 9999px; color: #ca8a04;">
                    <span class="dashicons dashicons-email-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-slate-600" style="margin-top: 16px; display: flex; align-items: center; font-size: 13px; color: #475569;">
                <span class="dashicons dashicons-clock" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px;"></span>
                <span x-text="(stats.pending_letters || 0) + ' Menunggu'"></span>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 12px 0; text-transform: uppercase;">Aksi Cepat</p>
                <div class="grid grid-cols-1 gap-2" style="display: grid; gap: 8px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=desa_potensi'); ?>" class="button" style="width: 100%; text-align: left; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-plus"></span> Tambah Potensi
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=desa_umkm'); ?>" class="button" style="width: 100%; text-align: left; padding: 6px 12px; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-plus"></span> Tambah UMKM
                    </a>
                </div>
            </div>
        </div>

        <!-- Keuangan Desa Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div class="flex items-center justify-between mb-4" style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 4px 0; text-transform: uppercase;">Keuangan Desa <span x-text="stats.finance_stats?.year"></span></p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-full text-emerald-600" style="background: #ecfdf5; padding: 12px; border-radius: 9999px; color: #059669;">
                    <span class="dashicons dashicons-money-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            </div>
            
            <div class="space-y-3" style="display: grid; gap: 12px;">
                <div>
                    <div class="flex justify-between text-xs mb-1" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                        <span class="text-slate-500">Pemasukan</span>
                        <span class="font-medium text-emerald-600" x-text="formatRupiah(stats.finance_stats?.income || 0)">Rp 0</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2" style="width: 100%; background: #f1f5f9; border-radius: 9999px; height: 8px;">
                        <div class="bg-emerald-500 h-2 rounded-full" style="background: #10b981; height: 8px; width: 100%;"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                        <span class="text-slate-500">Pengeluaran</span>
                        <span class="font-medium text-red-600" x-text="formatRupiah(stats.finance_stats?.expense || 0)">Rp 0</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2" style="width: 100%; background: #f1f5f9; border-radius: 9999px; height: 8px;">
                        <div class="bg-red-500 h-2 rounded-full" style="background: #ef4444; height: 8px;" :style="'width: ' + calculatePercentage(stats.finance_stats?.expense, stats.finance_stats?.income) + '%'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aspirasi Warga Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 md:col-span-2" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); grid-column: span 2;">
            <div class="flex items-center justify-between mb-4" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider" style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.05em; margin: 0; text-transform: uppercase;">Aspirasi Warga Terbaru</p>
                <a href="<?php echo admin_url('admin.php?page=wp-desa-complaints'); ?>" class="text-xs text-blue-600 hover:text-blue-800" style="font-size: 12px; color: #2563eb; text-decoration: none;">Lihat Semua</a>
            </div>
            
            <div class="space-y-3" style="display: grid; gap: 12px;">
                <template x-if="stats.recent_complaints && stats.recent_complaints.length > 0">
                    <template x-for="complaint in stats.recent_complaints" :key="complaint.id">
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors" style="display: flex; align-items: start; gap: 12px; padding: 8px; border-radius: 8px; border: 1px solid transparent; cursor: default;">
                            <div class="w-2 h-2 mt-2 rounded-full flex-shrink-0" 
                                :class="{
                                    'bg-yellow-400': complaint.status === 'pending',
                                    'bg-blue-400': complaint.status === 'in_progress',
                                    'bg-green-400': complaint.status === 'resolved',
                                    'bg-red-400': complaint.status === 'rejected'
                                }"
                                :style="{
                                    backgroundColor: complaint.status === 'pending' ? '#facc15' : 
                                                    (complaint.status === 'in_progress' ? '#60a5fa' : 
                                                    (complaint.status === 'resolved' ? '#4ade80' : '#f87171'))
                                }">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate" style="font-size: 14px; font-weight: 500; color: #1e293b; margin: 0;" x-text="complaint.subject">Judul Aduan</p>
                                <p class="text-xs text-slate-500" style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;" x-text="formatDate(complaint.created_at)"></p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full capitalize" 
                                :style="{
                                    backgroundColor: complaint.status === 'pending' ? '#fefce8' : (complaint.status === 'in_progress' ? '#eff6ff' : (complaint.status === 'resolved' ? '#f0fdf4' : '#fef2f2')),
                                    color: complaint.status === 'pending' ? '#ca8a04' : (complaint.status === 'in_progress' ? '#2563eb' : (complaint.status === 'resolved' ? '#16a34a' : '#dc2626'))
                                }"
                                x-text="complaint.status.replace('_', ' ')">
                            </span>
                        </div>
                    </template>
                </template>
                <template x-if="!stats.recent_complaints || stats.recent_complaints.length === 0">
                    <p class="text-sm text-slate-400 text-center py-4" style="color: #94a3b8; text-align: center; padding: 16px 0;">Belum ada aspirasi masuk.</p>
                </template>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
        
        <!-- Gender Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 class="text-lg font-semibold text-slate-800 mb-4" style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Demografi Jenis Kelamin</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <!-- Marital Status Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 class="text-lg font-semibold text-slate-800 mb-4" style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Status Perkawinan</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="maritalChart"></canvas>
            </div>
        </div>

        <!-- Job Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 class="text-lg font-semibold text-slate-800 mb-4" style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Pekerjaan Utama (Top 5)</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="jobChart"></canvas>
            </div>
        </div>

        <!-- Letter Status Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 class="text-lg font-semibold text-slate-800 mb-4" style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Status Layanan Surat</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="letterChart"></canvas>
            </div>
        </div>

        <!-- Program Aid Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h3 class="text-lg font-semibold text-slate-800 mb-4" style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 16px 0;">Realisasi Bantuan Sosial</h3>
            <div style="height: 300px; position: relative;">
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
                // Destroy existing charts if any
                if (this.charts.gender) this.charts.gender.destroy();
                if (this.charts.marital) this.charts.marital.destroy();
                if (this.charts.job) this.charts.job.destroy();
                if (this.charts.letter) this.charts.letter.destroy();
                if (this.charts.aid) this.charts.aid.destroy();

                // Gender Chart
                this.charts.gender = this.createChart('genderChart', 'doughnut', 
                    this.stats.gender_stats.map(i => i.label), 
                    this.stats.gender_stats.map(i => i.count),
                    ['#3b82f6', '#ec4899']
                );

                // Marital Status Chart
                this.charts.marital = this.createChart('maritalChart', 'pie', 
                    this.stats.marital_stats.map(i => i.label), 
                    this.stats.marital_stats.map(i => i.count),
                    ['#10b981', '#f59e0b', '#6366f1', '#ef4444']
                );

                // Job Chart
                this.charts.job = this.createChart('jobChart', 'bar', 
                    this.stats.job_stats.map(i => i.label), 
                    this.stats.job_stats.map(i => i.count),
                    ['#6366f1']
                );

                // Letter Status Chart
                const letterColors = {
                    'pending': '#f59e0b',   // Yellow
                    'processed': '#3b82f6', // Blue
                    'completed': '#10b981', // Green
                    'rejected': '#ef4444'   // Red
                };
                
                this.charts.letter = this.createChart('letterChart', 'doughnut', 
                    this.stats.letter_stats.map(i => i.label.charAt(0).toUpperCase() + i.label.slice(1)), 
                    this.stats.letter_stats.map(i => i.count),
                    this.stats.letter_stats.map(i => letterColors[i.label] || '#cbd5e1')
                );

                // Aid Program Chart
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
                        datasets: [
                            {
                                label: 'Kuota',
                                data: data1,
                                backgroundColor: '#94a3b8', // Slate 400
                                borderWidth: 0
                            },
                            {
                                label: 'Tersalurkan',
                                data: data2,
                                backgroundColor: '#10b981', // Green 500
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
                                        // Truncate long labels
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
                let percent = (value / total) * 100;
                return Math.min(percent, 100); // Max 100% width
            }
        }));
    });
</script>
