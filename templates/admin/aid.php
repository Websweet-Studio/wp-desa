<div class="wrap wp-desa-wrapper">



    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Program & Bantuan Sosial</h1>
            <p class="wp-desa-helper">Kelola program bantuan dan penerima manfaat.</p>
        </div>
        <div class="wp-desa-actions">
            <?php
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1):
            ?>
                <button class="wp-desa-btn wp-desa-btn-danger-soft">
                    <span class="dashicons dashicons-database"></span> Generate Dummy
                </button>
            <?php endif; ?>
            <button class="wp-desa-btn wp-desa-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span> Tambah Program
            </button>
            <button class="wp-desa-btn wp-desa-btn-secondary">
                &larr; Kembali
            </button>
            <button class="wp-desa-btn wp-desa-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span> Tambah Penerima
            </button>
        </div>
    </div>

    <!-- View: Programs List -->
    <div class="wp-desa-card">
        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>Nama Program</th>
                    <th>Asal Dana</th>
                    <th>Tahun</th>
                    <th>Kuota</th>
                    <th>Nominal / Penerima</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong class="wp-desa-row-title"></strong>
                        <span class="wp-desa-row-subtitle"></span>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <span class="wp-desa-badge wp-desa-badge-success"></span>
                    </td>
                    <td>
                        <div class="wp-desa-inline-actions">
                            <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Kelola Penerima</button>
                            <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="wp-desa-empty-state">Belum ada program bantuan.</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Programs -->
        <div class="wp-desa-pagination">
            <div class="wp-desa-pagination-info">
                Menampilkan <span></span> sampai <span></span> dari <span></span> data
            </div>
            <div class="wp-desa-pagination-controls">
                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                    &larr; Sebelumnya
                </button>
                <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                    Selanjutnya &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- View: Recipients List -->
    <div>
        <div class="wp-desa-mb-20">
            <h2 class="wp-desa-section-title">
                Penerima: <span class="wp-desa-accent-blue"></span>
            </h2>
            <p class="wp-desa-helper">
                Total Penerima: <strong></strong> / Kuota: <strong></strong>
            </p>
        </div>

        <div class="wp-desa-card">
            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Alamat</th>
                        <th>Jenis Kelamin</th>
                        <th>Status</th>
                        <th>Tgl Disalurkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <select class="wp-desa-select wp-desa-select-sm">
                                <option value="pending">Pending</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                                <option value="distributed">Disalurkan</option>
                            </select>
                        </td>
                        <td></td>
                        <td>
                            <button class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="wp-desa-empty-state">Belum ada penerima terdaftar.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Recipients -->
            <div class="wp-desa-pagination">
                <div class="wp-desa-pagination-info">
                    Menampilkan <span></span> sampai <span></span> dari <span></span> data
                </div>
                <div class="wp-desa-pagination-controls">
                    <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        &larr; Sebelumnya
                    </button>
                    <button class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                        Selanjutnya &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Program -->
    <div class="wp-desa-modal-overlay">
        <div class="wp-desa-modal-content">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title"></h2>
                <button class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-lg"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <form>
                    <div class="wp-desa-form-grid">
                        <div>
                            <label class="wp-desa-label">Nama Program</label>
                            <input type="text" class="wp-desa-input" required placeholder="Contoh: BLT Dana Desa">
                        </div>
                        <div>
                            <label class="wp-desa-label">Asal Dana</label>
                            <input type="text" class="wp-desa-input" required placeholder="Contoh: Dana Desa / Kemensos">
                        </div>
                        <div class="wp-desa-grid-2-16">
                            <div>
                                <label class="wp-desa-label">Tahun Anggaran</label>
                                <input type="number" class="wp-desa-input" required>
                            </div>
                            <div>
                                <label class="wp-desa-label">Kuota Penerima</label>
                                <input type="number" class="wp-desa-input" required>
                            </div>
                        </div>
                        <div>
                            <label class="wp-desa-label">Nominal Bantuan (Rp)</label>
                            <input type="number" class="wp-desa-input" required>
                        </div>
                        <div>
                            <label class="wp-desa-label">Deskripsi</label>
                            <textarea class="wp-desa-textarea" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="wp-desa-label">Status</label>
                            <select class="wp-desa-select">
                                <option value="active">Aktif</option>
                                <option value="closed">Tutup</option>
                            </select>
                        </div>
                    </div>
                    <div class="wp-desa-modal-footer">
                        <button type="button" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Recipient -->
    <div class="wp-desa-modal-overlay">
        <div class="wp-desa-modal-content">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">Tambah Penerima</h2>
                <button class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-lg"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <p class="wp-desa-helper">Masukkan NIK Penduduk yang akan menerima bantuan ini.</p>
                <form>
                    <div class="wp-desa-form-grid">
                        <div>
                            <label class="wp-desa-label">NIK Penduduk</label>
                            <input type="text" class="wp-desa-input" required placeholder="16 digit NIK">
                            <p class="wp-desa-helper-sm">Pastikan penduduk sudah terdaftar di data kependudukan.</p>
                        </div>
                    </div>
                    <div class="wp-desa-modal-footer">
                        <button type="button" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="wp-desa-toast">
        <span class="dashicons"></span>
        <span></span>
    </div>
</div>
