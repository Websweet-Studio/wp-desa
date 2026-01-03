<div class="wrap wp-desa-wrapper" x-data="financeManager()">

    <style>
        /* Scoped Styles mimicking Tailwind */
        .wp-desa-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #1e293b;
        }

        .wp-desa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-top: 10px;
        }

        .wp-desa-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .wp-desa-actions {
            display: flex;
            gap: 10px;
        }

        .wp-desa-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 20px;
        }

        /* Buttons */
        .wp-desa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
            line-height: 1.25;
            gap: 6px;
        }

        .wp-desa-btn-primary {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .wp-desa-btn-primary:hover {
            background-color: #1d4ed8;
            color: white;
        }

        .wp-desa-btn-secondary {
            background-color: white;
            color: #475569;
            border-color: #cbd5e1;
        }

        .wp-desa-btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #1e293b;
        }

        .wp-desa-btn-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .wp-desa-btn-danger:hover {
            background-color: #fecaca;
            color: #7f1d1d;
        }

        .wp-desa-btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }

        /* Tabs/Filters */
        .wp-desa-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0 16px;
            gap: 24px;
        }

        .wp-desa-tab {
            padding: 16px 0;
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }

        .wp-desa-tab:hover {
            color: #1e293b;
        }

        .wp-desa-tab.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        /* Table */
        .wp-desa-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .wp-desa-table th {
            background-color: #f8fafc;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
        }

        .wp-desa-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 14px;
        }

        .wp-desa-table tr:last-child td {
            border-bottom: none;
        }

        .wp-desa-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges */
        .wp-desa-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .wp-desa-badge-income {
            background: #dcfce7;
            color: #166534;
        }

        .wp-desa-badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Form Elements */
        .wp-desa-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .wp-desa-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 6px;
        }

        .wp-desa-input,
        .wp-desa-select,
        .wp-desa-textarea {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            color: #1e293b;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            background-color: #fff;
            /* Ensure white background */
        }

        .wp-desa-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .wp-desa-input:focus,
        .wp-desa-select:focus,
        .wp-desa-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        /* Modal */
        .wp-desa-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .wp-desa-modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.2s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wp-desa-modal-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .wp-desa-modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .wp-desa-modal-body {
            padding: 20px;
        }

        .wp-desa-modal-footer {
            padding: 20px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        /* Dashboard Cards */
        .wp-desa-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .wp-desa-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .wp-desa-stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .wp-desa-stat-title {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .wp-desa-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .wp-desa-stat-desc {
            font-size: 13px;
            color: #94a3b8;
        }

        /* Notification */
        .wp-desa-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 24px;
            border-radius: 8px;
            background: #1e293b;
            color: white;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10001;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .wp-desa-toast.error {
            background: #ef4444;
        }

        /* Pagination */
        .wp-desa-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-top: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .wp-desa-pagination-info {
            font-size: 13px;
            color: #64748b;
        }

        .wp-desa-pagination-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wp-desa-pagination-page {
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        /* Filter Bar */
        .wp-desa-filter-bar {
            display: flex;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            align-items: center;
        }
    </style>

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Keuangan Desa</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola anggaran dan realisasi APBDes.</p>
        </div>
        <div class="wp-desa-actions">
            <?php 
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): 
            ?>
            <button @click="generateDummy" class="wp-desa-btn wp-desa-btn-danger" style="background: #fff1f2; color: #e11d48; border-color: #fecdd3;">
                <span class="dashicons dashicons-database"></span> Generate Dummy
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div style="margin-bottom: 20px;">
        <div class="wp-desa-tabs" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; background: white; padding: 0;">
            <div class="wp-desa-tab" :class="{'active': tab === 'dashboard'}" @click="tab = 'dashboard'" style="padding: 16px 24px; flex: 1; text-align: center;">
                <span class="dashicons dashicons-chart-pie" style="margin-right: 6px;"></span> Dashboard & Grafik
            </div>
            <div class="wp-desa-tab" :class="{'active': tab === 'data'}" @click="tab = 'data'" style="padding: 16px 24px; flex: 1; text-align: center;">
                <span class="dashicons dashicons-list-view" style="margin-right: 6px;"></span> Data APBDes
            </div>
        </div>
    </div>

    <!-- Tab Dashboard -->
    <div x-show="tab === 'dashboard'" class="wp-desa-dashboard">
        <div class="wp-desa-stats-grid">
            <!-- Summary Cards -->
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Pendapatan</div>
                <div class="wp-desa-stat-value" style="color: #059669;" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Belanja</div>
                <div class="wp-desa-stat-value" style="color: #dc2626;" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Surplus/Defisit (Realisasi)</div>
                <div class="wp-desa-stat-value" :style="{color: (getSurplus() >= 0 ? '#059669' : '#dc2626')}" x-text="formatCurrency(getSurplus())"></div>
                <div class="wp-desa-stat-desc">Tahun Anggaran <span x-text="filterYear"></span></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="wp-desa-card" style="padding: 20px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color: #1e293b;">Sumber Pendapatan Desa</h3>
                <canvas id="incomeChart" style="max-height: 300px;"></canvas>
            </div>
            <div class="wp-desa-card" style="padding: 20px;">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; color: #1e293b;">Penggunaan Dana (Belanja)</h3>
                <canvas id="expenseChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Tab Data -->
    <div x-show="tab === 'data'">
        <div class="wp-desa-card">
            <!-- Filters -->
            <div class="wp-desa-filter-bar">
                <select x-model="filterYear" @change="resetPaginationAndFetch" class="wp-desa-select" style="width: auto;">
                    <template x-for="y in years" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
                <select x-model="filterType" @change="resetPaginationAndFetch" class="wp-desa-select" style="width: auto;">
                    <option value="">Semua Jenis</option>
                    <option value="income">Pendapatan</option>
                    <option value="expense">Belanja</option>
                </select>
                <div style="flex-grow: 1;"></div>
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
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span>
                                <div style="margin-top: 8px;">Memuat data...</div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
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
                            <td style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <button @click="editItem(item)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                        Edit
                                    </button>
                                    <button @click="deleteItem(item.id)" class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm" style="background: white; color: #dc2626; border-color: #fecaca;">
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
                    <button @click="prevPage()" :disabled="pagination.currentPage === 1" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="pagination.currentPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="wp-desa-pagination-page">
                        Halaman <span x-text="pagination.currentPage"></span> dari <span x-text="pagination.totalPages"></span>
                    </span>
                    <button @click="nextPage()" :disabled="pagination.currentPage === pagination.totalPages" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="pagination.currentPage === pagination.totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none;"
        x-transition.opacity>

        <div class="wp-desa-modal-content" @click.outside="isModalOpen = false">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title" x-text="editMode ? 'Edit Data Keuangan' : 'Tambah Data Keuangan'"></h2>
                <button type="button" @click="isModalOpen = false" style="background:none; border:none; cursor:pointer; color: #94a3b8; display: flex;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 20px;"></span>
                </button>
            </div>

            <div class="wp-desa-modal-body">
                <form @submit.prevent="saveItem" id="financeForm">
                    <div class="wp-desa-form-grid">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
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

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
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
        class="wp-desa-toast"
        :class="{'error': notification.type === 'error'}"
        style="display: none;">
        <span class="dashicons" :class="notification.type === 'error' ? 'dashicons-warning' : 'dashicons-yes-alt'"></span>
        <span x-text="notification.message"></span>
        <button @click="notification.show = false" style="background:none; border:none; color:white; cursor:pointer; margin-left: 10px; opacity: 0.8;">
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