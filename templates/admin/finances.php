<div class="wrap wp-desa-wrapper" x-data="financeManager()">

    

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Keuangan Desa</h1>
            <p class="wp-desa-helper">Kelola anggaran dan realisasi APBDes.</p>
        </div>
        <div class="wp-desa-actions">
            <?php 
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): 
            ?>
            <button @click="generateDummy" class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-danger-soft">
                <span class="dashicons dashicons-database"></span> Generate Dummy
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="wp-desa-mb-20">
        <div class="wp-desa-tabs wp-desa-tabs-box">
            <div class="wp-desa-tab wp-desa-tab-fill" :class="{'active': tab === 'dashboard'}" @click="tab = 'dashboard'">
                <span class="dashicons dashicons-chart-pie wp-desa-icon-gap"></span> Dashboard & Grafik
            </div>
            <div class="wp-desa-tab wp-desa-tab-fill" :class="{'active': tab === 'data'}" @click="tab = 'data'">
                <span class="dashicons dashicons-list-view wp-desa-icon-gap"></span> Data APBDes
            </div>
        </div>
    </div>

    <!-- Tab Dashboard -->
    <div x-show="tab === 'dashboard'" class="wp-desa-dashboard">
        <div class="wp-desa-stats-grid">
            <!-- Summary Cards -->
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Pendapatan</div>
                <div class="wp-desa-stat-value wp-desa-text-green" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Belanja</div>
                <div class="wp-desa-stat-value wp-desa-text-red" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Surplus/Defisit (Realisasi)</div>
                <div class="wp-desa-stat-value" :class="getSurplus() >= 0 ? 'wp-desa-text-green' : 'wp-desa-text-red'" x-text="formatCurrency(getSurplus())"></div>
                <div class="wp-desa-stat-desc">Tahun Anggaran <span x-text="filterYear"></span></div>
            </div>
        </div>

        <div class="wp-desa-grid-2">
            <div class="wp-desa-card wp-desa-card-pad">
                <h3 class="wp-desa-section-title">Sumber Pendapatan Desa</h3>
                <canvas id="incomeChart" class="wp-desa-chart"></canvas>
            </div>
            <div class="wp-desa-card wp-desa-card-pad">
                <h3 class="wp-desa-section-title">Penggunaan Dana (Belanja)</h3>
                <canvas id="expenseChart" class="wp-desa-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tab Data -->
    <div x-show="tab === 'data'">
        <div class="wp-desa-card">
            <!-- Filters -->
            <div class="wp-desa-filter-bar">
                <select x-model="filterYear" @change="resetPaginationAndFetch" class="wp-desa-select wp-desa-select-sm">
                    <template x-for="y in years" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
                <select x-model="filterType" @change="resetPaginationAndFetch" class="wp-desa-select wp-desa-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="income">Pendapatan</option>
                    <option value="expense">Belanja</option>
                </select>
                <div class="wp-desa-flex-grow"></div>
                <button @click="openModal()" class="wp-desa-btn wp-desa-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Tambah Data
                </button>
            </div>

            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tahun</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Uraian</th>
                        <th>Anggaran</th>
                        <th>Realisasi</th>
                        <th class="wp-desa-text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="wp-desa-empty-state">
                                <span class="dashicons dashicons-update wp-desa-spinner"></span>
                                <div class="wp-desa-mt-8">Memuat data...</div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr>
                            <td colspan="8" class="wp-desa-empty-state">
                                <div class="wp-desa-empty-icon">📊</div>
                                <div>Tidak ada data keuangan.</div>
                            </td>
                        </tr>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td x-text="formatDate(item.transaction_date)"></td>
                            <td x-text="item.year"></td>
                            <td>
                                <span class="wp-desa-badge"
                                    :class="item.type === 'income' ? 'wp-desa-badge-income' : 'wp-desa-badge-expense'"
                                    x-text="item.type === 'income' ? 'Pendapatan' : 'Belanja'">
                                </span>
                            </td>
                            <td x-text="item.category"></td>
                            <td x-text="item.description"></td>
                            <td x-text="formatCurrency(item.budget_amount)"></td>
                            <td x-text="formatCurrency(item.realization_amount)"></td>
                            <td class="wp-desa-text-right">
                                <div class="wp-desa-inline-actions-end">
                                    <button @click="editItem(item)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                        Edit
                                    </button>
                                    <button @click="deleteItem(item.id)" class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="wp-desa-pagination" x-show="!loading && items.length > 0">
                <div class="wp-desa-pagination-info">
                    Menampilkan <span x-text="(pagination.currentPage - 1) * pagination.perPage + 1"></span>
                    sampai <span x-text="Math.min(pagination.currentPage * pagination.perPage, pagination.totalItems)"></span>
                    dari <span x-text="pagination.totalItems"></span> data
                </div>
                <div class="wp-desa-pagination-controls">
                    <button @click="prevPage()" :disabled="pagination.currentPage === 1" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="wp-desa-pagination-page">
                        Halaman <span x-text="pagination.currentPage"></span> dari <span x-text="pagination.totalPages"></span>
                    </span>
                    <button @click="nextPage()" :disabled="pagination.currentPage === pagination.totalPages" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay wp-desa-hidden"
        x-transition.opacity>

        <div class="wp-desa-modal-content" @click.outside="isModalOpen = false">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title" x-text="editMode ? 'Edit Data Keuangan' : 'Tambah Data Keuangan'"></h2>
                <button type="button" @click="isModalOpen = false" class="wp-desa-icon-btn wp-desa-text-muted">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <div class="wp-desa-modal-body">
                <form @submit.prevent="saveItem" id="financeForm">
                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-grid-2-16">
                            <div>
                                <label class="wp-desa-label">Tahun Anggaran</label>
                                <input type="number" x-model="form.year" required class="wp-desa-input">
                            </div>
                            <div>
                                <label class="wp-desa-label">Tanggal Transaksi</label>
                                <input type="date" x-model="form.transaction_date" required class="wp-desa-input">
                            </div>
                        </div>

                        <div>
                            <label class="wp-desa-label">Jenis Transaksi</label>
                            <select x-model="form.type" required class="wp-desa-select">
                                <option value="income">Pendapatan</option>
                                <option value="expense">Belanja</option>
                            </select>
                        </div>

                        <div>
                            <label class="wp-desa-label">Kategori</label>
                            <input type="text" x-model="form.category" required class="wp-desa-input" placeholder="Contoh: Dana Desa, ADD, Belanja Pegawai">
                        </div>

                        <div>
                            <label class="wp-desa-label">Uraian / Keterangan</label>
                            <textarea x-model="form.description" class="wp-desa-textarea" rows="3" placeholder="Deskripsi detail transaksi..."></textarea>
                        </div>

                        <div class="wp-desa-grid-2-16">
                            <div>
                                <label class="wp-desa-label">Jumlah Anggaran (Rp)</label>
                                <input type="number" x-model="form.budget_amount" required class="wp-desa-input">
                            </div>
                            <div>
                                <label class="wp-desa-label">Realisasi (Rp)</label>
                                <input type="number" x-model="form.realization_amount" required class="wp-desa-input">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="wp-desa-modal-footer">
                <button type="submit" form="financeForm" class="wp-desa-btn wp-desa-btn-primary">Simpan</button>
                <button type="button" @click="isModalOpen = false" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="notification.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="wp-desa-toast wp-desa-hidden"
        :class="{'error': notification.type === 'error'}">
        <span class="dashicons" :class="notification.type === 'error' ? 'dashicons-warning' : 'dashicons-yes-alt'"></span>
        <span x-text="notification.message"></span>
        <button @click="notification.show = false" class="wp-desa-toast-close">
            <span class="dashicons dashicons-no"></span>
        </button>
    </div>

</div>

<script>
    function financeManager() {
        return {
            tab: 'dashboard',
            items: [],
            summary: {
                totals: [],
                income_sources: [],
                expense_sources: []
            },
            filterYear: new Date().getFullYear(),
            filterType: '',
            years: [],
            loading: true,
            pagination: {
                currentPage: 1,
                perPage: 20,
                totalItems: 0,
                totalPages: 0
            },
            isModalOpen: false,
            editMode: false,
            form: {
                id: null,
                year: new Date().getFullYear(),
                type: 'income',
                category: '',
                description: '',
                budget_amount: 0,
                realization_amount: 0,
                transaction_date: new Date().toISOString().slice(0, 10)
            },
            incomeChart: null,
            expenseChart: null,
            notification: {
                show: false,
                message: '',
                type: 'success'
            },

            init() {
                // Generate year list
                const currentYear = new Date().getFullYear();
                for (let i = currentYear; i >= currentYear - 5; i--) {
                    this.years.push(i);
                }

                this.fetchData();
                this.fetchSummary();

                this.$watch('tab', (val) => {
                    if (val === 'dashboard') {
                        this.$nextTick(() => {
                            this.fetchSummary();
                        });
                    }
                });
            },

            resetPaginationAndFetch() {
                this.pagination.currentPage = 1;
                this.fetchData();
                if (this.tab === 'dashboard') {
                    this.fetchSummary();
                }
            },

            fetchData() {
                this.loading = true;
                let url = '<?php echo esc_url_raw(rest_url('wp-desa/v1/finances')); ?>';

                const params = new URLSearchParams({
                    page: this.pagination.currentPage,
                    per_page: this.pagination.perPage,
                    _t: Date.now()
                });

                if (this.filterYear) params.append('year', this.filterYear);
                if (this.filterType) params.append('type', this.filterType);

                url += '?' + params.toString();

                fetch(url).then(res => res.json())
                    .then(data => {
                        if (data.data && data.meta) {
                            this.items = data.data;
                            this.pagination = {
                                currentPage: parseInt(data.meta.current_page),
                                perPage: parseInt(data.meta.per_page),
                                totalItems: parseInt(data.meta.total_items),
                                totalPages: parseInt(data.meta.total_pages)
                            };
                        } else {
                            // Fallback for older API or empty result
                            this.items = Array.isArray(data) ? data : [];
                        }
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.showNotification('Gagal memuat data.', 'error');
                        this.loading = false;
                    });
            },

            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.pagination.currentPage++;
                    this.fetchData();
                }
            },

            prevPage() {
                if (this.pagination.currentPage > 1) {
                    this.pagination.currentPage--;
                    this.fetchData();
                }
            },

            fetchSummary() {
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/finances/summary')); ?>?year=' + this.filterYear)
                    .then(res => res.json())
                    .then(data => {
                        this.summary = data;
                        this.renderCharts();
                    });
            },

            renderCharts() {
                if (this.incomeChart) this.incomeChart.destroy();
                if (this.expenseChart) this.expenseChart.destroy();

                const incomeCtx = document.getElementById('incomeChart');
                if (incomeCtx && this.summary.income_sources.length > 0) {
                    this.incomeChart = new Chart(incomeCtx, {
                        type: 'pie',
                        data: {
                            labels: this.summary.income_sources.map(i => i.category),
                            datasets: [{
                                data: this.summary.income_sources.map(i => i.total),
                                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }

                const expenseCtx = document.getElementById('expenseChart');
                if (expenseCtx && this.summary.expense_sources.length > 0) {
                    this.expenseChart = new Chart(expenseCtx, {
                        type: 'doughnut',
                        data: {
                            labels: this.summary.expense_sources.map(i => i.category),
                            datasets: [{
                                data: this.summary.expense_sources.map(i => i.total),
                                backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            },

            openModal() {
                this.form = {
                    id: null,
                    year: new Date().getFullYear(),
                    type: 'income',
                    category: '',
                    description: '',
                    budget_amount: 0,
                    realization_amount: 0,
                    transaction_date: new Date().toISOString().slice(0, 10)
                };
                this.editMode = false;
                this.isModalOpen = true;
            },

            editItem(item) {
                this.form = {
                    ...item
                };
                this.editMode = true;
                this.isModalOpen = true;
            },

            saveItem() {
                const url = this.editMode ?
                    '<?php echo esc_url_raw(rest_url('wp-desa/v1/finances/')); ?>' + this.form.id :
                    '<?php echo esc_url_raw(rest_url('wp-desa/v1/finances')); ?>';

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.isModalOpen = false;
                            this.showNotification('Data berhasil disimpan');
                            this.fetchData();
                            this.fetchSummary();
                        } else {
                            this.showNotification('Gagal menyimpan data', 'error');
                        }
                    })
                    .catch(err => {
                        this.showNotification('Terjadi kesalahan sistem', 'error');
                    });
            },

            deleteItem(id) {
                if (!confirm('Yakin ingin menghapus data ini?')) return;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/finances/')); ?>' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showNotification('Data berhasil dihapus');
                            this.fetchData();
                            this.fetchSummary();
                        }
                    });
            },

            generateDummy() {
                if (!confirm('Buat data keuangan dummy?')) return;
                this.loading = true;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/finances/seed')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.showNotification(data.message);
                        this.fetchData();
                        this.fetchSummary();
                    })
                    .catch(err => {
                        this.showNotification('Gagal generate dummy', 'error');
                        this.loading = false;
                    });
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(value);
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            },

            getSurplus() {
                const income = this.summary.totals.find(t => t.type === 'income')?.total_realization || 0;
                const expense = this.summary.totals.find(t => t.type === 'expense')?.total_realization || 0;
                return income - expense;
            },

            showNotification(message, type = 'success') {
                this.notification.message = message;
                this.notification.type = type;
                this.notification.show = true;
                setTimeout(() => {
                    this.notification.show = false;
                }, 3000);
            }
        }
    }
</script>
