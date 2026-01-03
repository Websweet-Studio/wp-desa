<div class="wrap" x-data="financeManager()">
    <h1 class="wp-heading-inline">Keuangan Desa</h1>
    <button @click="generateDummy" class="page-title-action">Generate Dummy</button>
    <hr class="wp-header-end">

    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab" :class="{'nav-tab-active': tab === 'dashboard'}" @click.prevent="tab = 'dashboard'">Dashboard & Grafik</a>
        <a href="#" class="nav-tab" :class="{'nav-tab-active': tab === 'data'}" @click.prevent="tab = 'data'">Data APBDes</a>
    </h2>

    <!-- Tab Dashboard -->
    <div x-show="tab === 'dashboard'" class="wp-desa-dashboard">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
            <!-- Summary Cards -->
            <div class="card" style="padding: 20px; text-align: center;">
                <h3>Total Anggaran Pendapatan</h3>
                <h2 style="color: #2271b1;" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></h2>
                <small>Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></span></small>
            </div>
            <div class="card" style="padding: 20px; text-align: center;">
                <h3>Total Anggaran Belanja</h3>
                <h2 style="color: #d63638;" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></h2>
                <small>Realisasi: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></span></small>
            </div>
            <div class="card" style="padding: 20px; text-align: center;">
                <h3>Surplus/Defisit (Realisasi)</h3>
                <h2 :style="{color: (getSurplus() >= 0 ? '#00a32a' : '#d63638')}" x-text="formatCurrency(getSurplus())"></h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="card" style="padding: 20px;">
                <h3>Sumber Pendapatan Desa</h3>
                <canvas id="incomeChart"></canvas>
            </div>
            <div class="card" style="padding: 20px;">
                <h3>Penggunaan Dana (Belanja)</h3>
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tab Data -->
    <div x-show="tab === 'data'" style="margin-top: 20px;">
        <!-- Filters & Add -->
        <div class="tablenav top">
            <div class="alignleft actions">
                <select x-model="filterYear" @change="fetchData">
                    <option value="">Semua Tahun</option>
                    <template x-for="y in years" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
                <select x-model="filterType" @change="fetchData">
                    <option value="">Semua Jenis</option>
                    <option value="income">Pendapatan</option>
                    <option value="expense">Belanja</option>
                </select>
                <button @click="openModal()" class="button button-primary">Tambah Data</button>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tahun</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Uraian</th>
                    <th>Anggaran</th>
                    <th>Realisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in items" :key="item.id">
                    <tr>
                        <td x-text="item.transaction_date"></td>
                        <td x-text="item.year"></td>
                        <td>
                            <span :class="item.type === 'income' ? 'wp-desa-badge success' : 'wp-desa-badge error'" x-text="item.type === 'income' ? 'Pendapatan' : 'Belanja'"></span>
                        </td>
                        <td x-text="item.category"></td>
                        <td x-text="item.description"></td>
                        <td x-text="formatCurrency(item.budget_amount)"></td>
                        <td x-text="formatCurrency(item.realization_amount)"></td>
                        <td>
                            <button @click="editItem(item)" class="button button-small">Edit</button>
                            <button @click="deleteItem(item.id)" class="button button-small button-link-delete">Hapus</button>
                        </td>
                    </tr>
                </template>
                <template x-if="items.length === 0">
                    <tr><td colspan="8">Tidak ada data.</td></tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen" class="wp-desa-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center;" :style="isModalOpen ? 'display: flex' : 'display: none'">
        <div class="wp-desa-modal" style="background: white; padding: 20px; width: 500px; max-width: 90%; border-radius: 5px;">
            <h2 x-text="editMode ? 'Edit Data' : 'Tambah Data'"></h2>
            <form @submit.prevent="saveItem">
                <table class="form-table">
                    <tr>
                        <th>Tahun</th>
                        <td><input type="number" x-model="form.year" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>
                            <select x-model="form.type" required>
                                <option value="income">Pendapatan</option>
                                <option value="expense">Belanja</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td><input type="text" x-model="form.category" required class="regular-text" placeholder="Contoh: Dana Desa"></td>
                    </tr>
                    <tr>
                        <th>Uraian</th>
                        <td><textarea x-model="form.description" class="large-text" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <th>Anggaran (Rp)</th>
                        <td><input type="number" x-model="form.budget_amount" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Realisasi (Rp)</th>
                        <td><input type="number" x-model="form.realization_amount" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td><input type="date" x-model="form.transaction_date" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Simpan</button>
                    <button type="button" @click="isModalOpen = false" class="button">Batal</button>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function financeManager() {
    return {
        tab: 'dashboard',
        items: [],
        summary: { totals: [], income_sources: [], expense_sources: [] },
        filterYear: new Date().getFullYear(),
        filterType: '',
        years: [],
        isModalOpen: false,
        editMode: false,
        form: {
            id: null, year: new Date().getFullYear(), type: 'income', category: '', description: '', budget_amount: 0, realization_amount: 0, transaction_date: new Date().toISOString().slice(0, 10)
        },
        incomeChart: null,
        expenseChart: null,

        init() {
            // Generate year list
            const currentYear = new Date().getFullYear();
            for(let i = currentYear; i >= currentYear - 5; i--) {
                this.years.push(i);
            }
            
            this.fetchData();
            this.fetchSummary();

            this.$watch('tab', (val) => {
                if(val === 'dashboard') {
                    this.$nextTick(() => {
                        this.fetchSummary();
                    });
                }
            });
        },

        fetchData() {
            let url = '/wp-json/wp-desa/v1/finances?_t=' + Date.now();
            if (this.filterYear) url += '&year=' + this.filterYear;
            if (this.filterType) url += '&type=' + this.filterType;

            fetch(url).then(res => res.json()).then(data => {
                this.items = data;
            });
        },

        fetchSummary() {
            fetch('/wp-json/wp-desa/v1/finances/summary?year=' + this.filterYear)
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
                            backgroundColor: ['#4bc0c0', '#36a2eb', '#ffcd56', '#ff9f40', '#9966ff']
                        }]
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
                            backgroundColor: ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0', '#36a2eb']
                        }]
                    }
                });
            }
        },

        openModal() {
            this.form = { id: null, year: new Date().getFullYear(), type: 'income', category: '', description: '', budget_amount: 0, realization_amount: 0, transaction_date: new Date().toISOString().slice(0, 10) };
            this.editMode = false;
            this.isModalOpen = true;
        },

        editItem(item) {
            this.form = { ...item };
            this.editMode = true;
            this.isModalOpen = true;
        },

        saveItem() {
            const url = this.editMode ? 
                '/wp-json/wp-desa/v1/finances/' + this.form.id : 
                '/wp-json/wp-desa/v1/finances';

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
                    this.fetchData();
                    this.fetchSummary();
                } else {
                    alert('Gagal menyimpan data');
                }
            });
        },

        deleteItem(id) {
            if (!confirm('Yakin ingin menghapus data ini?')) return;
            fetch('/wp-json/wp-desa/v1/finances/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.fetchData();
                    this.fetchSummary();
                }
            });
        },

        generateDummy() {
            if (!confirm('Buat data keuangan dummy?')) return;
            fetch('/wp-json/wp-desa/v1/finances/seed', {
                method: 'POST',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.fetchData();
                this.fetchSummary();
            });
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
        },
        
        getSurplus() {
            const income = this.summary.totals.find(t => t.type === 'income')?.total_realization || 0;
            const expense = this.summary.totals.find(t => t.type === 'expense')?.total_realization || 0;
            return income - expense;
        }
    }
}
</script>

<style>
.wp-desa-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
}
.wp-desa-badge.success { background: #d1fae5; color: #065f46; }
.wp-desa-badge.error { background: #fee2e2; color: #991b1b; }
</style>
