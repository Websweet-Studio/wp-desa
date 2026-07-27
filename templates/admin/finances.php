<div class="wrap wp-desa-wrapper">



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
                <button class="wp-desa-btn wp-desa-btn-danger">
                    <span class="dashicons dashicons-database"></span> Generate Dummy
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="wp-desa-mb-20">
        <div class="wp-desa-tabs wp-desa-tabs-box">
            <div class="wp-desa-tab wp-desa-tab-fill active">
                <span class="dashicons dashicons-chart-pie wp-desa-icon-gap"></span> Dashboard & Grafik
            </div>
            <div class="wp-desa-tab wp-desa-tab-fill">
                <span class="dashicons dashicons-list-view wp-desa-icon-gap"></span> Data APBDes
            </div>
        </div>
    </div>

    <!-- Tab Dashboard -->
    <div class="wp-desa-dashboard">
        <div class="wp-desa-stats-grid">
            <!-- Summary Cards -->
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Pendapatan</div>
                <div class="wp-desa-stat-value wp-desa-text-green"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Total Belanja</div>
                <div class="wp-desa-stat-value wp-desa-text-red"></div>
                <div class="wp-desa-stat-desc">Realisasi: <span></span></div>
            </div>
            <div class="wp-desa-stat-card">
                <div class="wp-desa-stat-title">Surplus/Defisit (Realisasi)</div>
                <div class="wp-desa-stat-value"></div>
                <div class="wp-desa-stat-desc">Tahun Anggaran <span></span></div>
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
    <div>
        <div class="wp-desa-card">
            <!-- Filters -->
            <div class="wp-desa-filter-bar">
                <select class="wp-desa-select wp-desa-select-sm">
                    <option></option>
                </select>
                <select class="wp-desa-select wp-desa-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="income">Pendapatan</option>
                    <option value="expense">Belanja</option>
                </select>
                <div class="wp-desa-flex-grow"></div>
                <button class="wp-desa-btn wp-desa-btn-primary">
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
                    <tr>
                        <td colspan="8" class="wp-desa-empty-state">
                            <span class="dashicons dashicons-update wp-desa-spinner"></span>
                            <div class="wp-desa-mt-8">Memuat data...</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" class="wp-desa-empty-state">
                            <div class="wp-desa-empty-icon">📊</div>
                            <div>Tidak ada data keuangan.</div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            <span class="wp-desa-badge">
                            </span>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="wp-desa-text-right">
                            <div class="wp-desa-inline-actions-end">
                                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                    Edit
                                </button>
                                <button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="wp-desa-pagination">
                <div class="wp-desa-pagination-info">
                    Menampilkan <span></span>
                    sampai <span></span>
                    dari <span></span> data
                </div>
                <div class="wp-desa-pagination-controls">
                    <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="wp-desa-pagination-page">
                        Halaman <span></span> dari <span></span>
                    </span>
                    <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="wp-desa-modal-overlay wp-desa-hidden">

        <div class="wp-desa-modal-content">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title"></h2>
                <button type="button" class="wp-desa-icon-btn wp-desa-text-muted">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <div class="wp-desa-modal-body">
                <form id="financeForm">
                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-grid-2-16">
                            <div>
                                <label class="wp-desa-label">Tahun Anggaran</label>
                                <input type="number" required class="wp-desa-input">
                            </div>
                            <div>
                                <label class="wp-desa-label">Tanggal Transaksi</label>
                                <input type="date" required class="wp-desa-input">
                            </div>
                        </div>

                        <div>
                            <label class="wp-desa-label">Jenis Transaksi</label>
                            <select required class="wp-desa-select">
                                <option value="income">Pendapatan</option>
                                <option value="expense">Belanja</option>
                            </select>
                        </div>

                        <div>
                            <label class="wp-desa-label">Kategori</label>
                            <input type="text" required class="wp-desa-input" placeholder="Contoh: Dana Desa, ADD, Belanja Pegawai">
                        </div>

                        <div>
                            <label class="wp-desa-label">Uraian / Keterangan</label>
                            <textarea class="wp-desa-textarea" rows="3" placeholder="Deskripsi detail transaksi..."></textarea>
                        </div>

                        <div class="wp-desa-grid-2-16">
                            <div>
                                <label class="wp-desa-label">Jumlah Anggaran (Rp)</label>
                                <input type="number" required class="wp-desa-input">
                            </div>
                            <div>
                                <label class="wp-desa-label">Realisasi (Rp)</label>
                                <input type="number" required class="wp-desa-input">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="wp-desa-modal-footer">
                <button type="submit" form="financeForm" class="wp-desa-btn wp-desa-btn-primary">Simpan</button>
                <button type="button" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="wp-desa-toast wp-desa-hidden">
        <span class="dashicons"></span>
        <span></span>
        <button class="wp-desa-toast-close">
            <span class="dashicons dashicons-no"></span>
        </button>
    </div>

</div>