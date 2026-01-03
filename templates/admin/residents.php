<div class="wrap wp-desa-wrapper" x-data="residentsManager()">

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

        /* Form Elements */
        .wp-desa-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .wp-desa-form-group {
            margin-bottom: 16px;
        }

        .wp-desa-form-group.full-width {
            grid-column: span 2;
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
            max-width: 600px;
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
    </style>

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Data Penduduk</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola data kependudukan desa dengan mudah.</p>
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
            <button @click="exportResidents" class="wp-desa-btn wp-desa-btn-secondary">
                <span class="dashicons dashicons-download"></span> Export
            </button>
            <button @click="$refs.importFile.click()" class="wp-desa-btn wp-desa-btn-secondary">
                <span class="dashicons dashicons-upload"></span> Import
            </button>
            <button @click="openModal('add')" class="wp-desa-btn wp-desa-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span> Tambah Penduduk
            </button>
            <input type="file" x-ref="importFile" @change="importResidents" style="display:none" accept=".csv">
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="wp-desa-card">
        <!-- Table Toolbar/Filter (Optional Future) -->
        <!-- <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 10px;">
             <input type="text" placeholder="Cari penduduk..." class="wp-desa-input" style="max-width: 300px;">
        </div> -->

        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>No. KK</th>
                    <th>Nama Lengkap</th>
                    <th>Jenis Kelamin</th>
                    <th>Tempat/Tgl Lahir</th>
                    <th>Pekerjaan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                            <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span>
                            <div style="margin-top: 8px;">Memuat data...</div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && residents.length === 0">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📂</div>
                            <div>Belum ada data penduduk.</div>
                            <button @click="openModal('add')" style="color: #2563eb; background: none; border: none; cursor: pointer; text-decoration: underline; margin-top: 8px;">Tambah sekarang</button>
                        </td>
                    </tr>
                </template>
                <template x-for="resident in residents" :key="resident.id">
                    <tr>
                        <td class="font-mono text-xs" style="font-family: monospace; color: #64748b;" x-text="resident.nik"></td>
                        <td class="font-mono text-xs" style="font-family: monospace; color: #64748b;" x-text="resident.no_kk || '-'"></td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;" x-text="resident.nama_lengkap"></div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;" x-text="resident.status_perkawinan"></div>
                        </td>
                        <td>
                            <span class="wp-desa-badge"
                                :style="resident.jenis_kelamin === 'Laki-laki' ? 'background: #eff6ff; color: #2563eb;' : 'background: #fdf2f8; color: #db2777;'"
                                style="padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600;"
                                x-text="resident.jenis_kelamin">
                            </span>
                        </td>
                        <td>
                            <div x-text="resident.tempat_lahir"></div>
                            <div style="font-size: 12px; color: #64748b;" x-text="formatDate(resident.tanggal_lahir)"></div>
                        </td>
                        <td x-text="resident.pekerjaan"></td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button @click="openModal('edit', resident)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Edit">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button @click="deleteResident(resident.id)" class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm" style="background: white; border-color: #fecaca; color: #ef4444;" title="Hapus">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination" x-show="!loading && residents.length > 0">
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

    <!-- Modal -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none;"
        x-transition.opacity>

        <div class="wp-desa-modal-content" @click.outside="closeModal">
            <div class="wp-desa-modal-header">
                <h2 x-text="modalMode === 'add' ? 'Tambah Penduduk' : 'Edit Penduduk'" class="wp-desa-modal-title"></h2>
                <button type="button" @click="closeModal" style="background:none; border:none; cursor:pointer; color: #94a3b8; display: flex;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 20px;"></span>
                </button>
            </div>

            <form @submit.prevent="saveResident">
                <div class="wp-desa-modal-body">
                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="nik">NIK <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="nik" x-model="form.nik" required class="wp-desa-input" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="no_kk">No. KK</label>
                            <input type="text" id="no_kk" x-model="form.no_kk" class="wp-desa-input" placeholder="16 digit No. KK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="nama_lengkap">Nama Lengkap <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="nama_lengkap" x-model="form.nama_lengkap" required class="wp-desa-input" placeholder="Sesuai KTP">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="jenis_kelamin">Jenis Kelamin</label>
                            <select id="jenis_kelamin" x-model="form.jenis_kelamin" class="wp-desa-select">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="status_perkawinan">Status Perkawinan</label>
                            <select id="status_perkawinan" x-model="form.status_perkawinan" class="wp-desa-select">
                                <option value="Belum Kawin">Belum Kawin</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="tempat_lahir">Tempat Lahir</label>
                            <input type="text" id="tempat_lahir" x-model="form.tempat_lahir" class="wp-desa-input">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" x-model="form.tanggal_lahir" class="wp-desa-input">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="pekerjaan">Pekerjaan</label>
                            <input type="text" id="pekerjaan" x-model="form.pekerjaan" class="wp-desa-input" placeholder="Contoh: Petani, PNS, Wiraswasta">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="alamat">Alamat Lengkap</label>
                            <textarea id="alamat" x-model="form.alamat" rows="3" class="wp-desa-textarea" placeholder="Jalan, RT/RW, Dusun..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="wp-desa-modal-footer">
                    <button type="button" @click="closeModal" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary" :disabled="saving">
                        <span x-show="saving" class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 16px;"></span>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Data'"></span>
                    </button>
                </div>
            </form>
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
    const wpDesaSettings = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('residentsManager', () => ({
            residents: [],
            loading: true,
            isModalOpen: false,
            modalMode: 'add', // 'add' or 'edit'
            saving: false,
            pagination: {
                currentPage: 1,
                perPage: 20,
                totalPages: 1,
                totalItems: 0
            },
            notification: {
                show: false,
                message: '',
                type: 'success'
            },
            form: {
                id: null,
                nik: '',
                no_kk: '',
                nama_lengkap: '',
                jenis_kelamin: 'Laki-laki',
                tempat_lahir: '',
                tanggal_lahir: '',
                alamat: '',
                status_perkawinan: 'Belum Kawin',
                pekerjaan: ''
            },

            init() {
                this.fetchResidents();
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                return new Date(dateString).toLocaleDateString('id-ID', options);
            },

            fetchResidents(page = 1) {
                this.loading = true;
                const url = new URL(wpDesaSettings.apiUrl);
                url.searchParams.append('page', page);
                url.searchParams.append('per_page', this.pagination.perPage);

                fetch(url.toString(), {
                        headers: {
                            'X-WP-Nonce': wpDesaSettings.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.meta) {
                            this.residents = data.data;
                            this.pagination.currentPage = data.meta.current_page;
                            this.pagination.totalPages = data.meta.total_pages;
                            this.pagination.totalItems = data.meta.total_items;
                            this.pagination.perPage = data.meta.per_page;
                        } else {
                            // Fallback for non-paginated response
                            this.residents = Array.isArray(data) ? data : [];
                            this.pagination.totalItems = this.residents.length;
                            this.pagination.totalPages = 1;
                        }
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                        this.showNotification('Gagal memuat data.', 'error');
                    });
            },

            prevPage() {
                if (this.pagination.currentPage > 1) {
                    this.fetchResidents(this.pagination.currentPage - 1);
                }
            },

            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.fetchResidents(this.pagination.currentPage + 1);
                }
            },

            openModal(mode, resident = null) {
                this.modalMode = mode;
                if (mode === 'edit' && resident) {
                    this.form = {
                        ...resident,
                        // Ensure fields exist even if null in DB
                        no_kk: resident.no_kk || '',
                    };
                } else {
                    this.resetForm();
                }
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.form = {
                    id: null,
                    nik: '',
                    no_kk: '',
                    nama_lengkap: '',
                    jenis_kelamin: 'Laki-laki',
                    tempat_lahir: '',
                    tanggal_lahir: '',
                    alamat: '',
                    status_perkawinan: 'Belum Kawin',
                    pekerjaan: ''
                };
            },

            saveResident() {
                this.saving = true;
                const isEdit = this.modalMode === 'edit';
                const url = isEdit ?
                    `${wpDesaSettings.apiUrl}/${this.form.id}` :
                    wpDesaSettings.apiUrl;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': wpDesaSettings.nonce
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.saving = false;
                        if (data.code) { // Error from WP_Error
                            throw new Error(data.message);
                        }

                        this.closeModal();
                        this.fetchResidents();
                        this.showNotification(isEdit ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');
                    })
                    .catch(err => {
                        this.saving = false;
                        this.showNotification(err.message || 'Terjadi kesalahan.', 'error');
                    });
            },

            deleteResident(id) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

                fetch(`${wpDesaSettings.apiUrl}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': wpDesaSettings.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.code) {
                            throw new Error(data.message);
                        }
                        this.fetchResidents();
                        this.showNotification('Data berhasil dihapus.');
                    })
                    .catch(err => {
                        this.showNotification(err.message || 'Gagal menghapus data.', 'error');
                    });
            },

            exportResidents() {
                const url = new URL(wpDesaSettings.apiUrl + '/export');
                url.searchParams.append('_wpnonce', wpDesaSettings.nonce);
                window.open(url.toString(), '_blank');
            },

            importResidents(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!confirm('Pastikan file CSV memiliki format yang benar. Lanjutkan import?')) {
                    event.target.value = '';
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                this.loading = true;

                fetch(wpDesaSettings.apiUrl + '/import', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': wpDesaSettings.nonce
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        event.target.value = ''; // Reset input
                        if (data.code) {
                            throw new Error(data.message);
                        }

                        this.fetchResidents();

                        if (data.errors && data.errors.length > 0) {
                            alert('Import selesai dengan catatan:\n- ' + data.errors.join('\n- '));
                            this.showNotification('Import selesai (dengan beberapa error).', 'warning');
                        } else {
                            this.showNotification(data.message);
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        event.target.value = '';
                        this.showNotification(err.message || 'Gagal import data.', 'error');
                    });
            },

            generateDummy() {
                if (!confirm('AWAS! Ini akan membuat 100 data penduduk acak. Lanjutkan?')) return;

                this.loading = true;

                fetch(wpDesaSettings.apiUrl + '/seed', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': wpDesaSettings.nonce
                        },
                        body: JSON.stringify({
                            count: 100
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if (data.code) {
                            throw new Error(data.message);
                        }
                        this.fetchResidents();
                        this.showNotification(data.message);
                    })
                    .catch(err => {
                        this.loading = false;
                        this.showNotification(err.message || 'Gagal generate dummy.', 'error');
                    });
            },

            showNotification(message, type = 'success') {
                this.notification.message = message;
                this.notification.type = type;
                this.notification.show = true;
                setTimeout(() => {
                    this.notification.show = false;
                }, 3000);
            }
        }));
    });
</script>