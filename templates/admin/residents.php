<div class="wrap wp-desa-wrapper" x-data="residentsManager()">

    <!-- CSS moved to assets/css/admin/style.css -->

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Data Penduduk</h1>
            <p class="wp-desa-helper">Kelola data kependudukan desa dengan mudah.</p>
        </div>
        <div class="wp-desa-actions">
            <?php
            $settings = get_option('wp_desa_settings', []);
            if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1):
            ?>
                <button @click="generateDummy" class="wp-desa-btn wp-desa-btn-danger">
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
                        <td colspan="7" class="wp-desa-empty-state">
                            <span class="dashicons dashicons-update wp-desa-spinner"></span>
                            <div class="wp-desa-mt-8">Memuat data...</div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && residents.length === 0">
                    <tr>
                        <td colspan="7" class="wp-desa-empty-state">
                            <div class="wp-desa-empty-icon">📂</div>
                            <div>Belum ada data penduduk.</div>
                            <button @click="openModal('add')" class="wp-desa-btn wp-desa-btn-primary" style="margin-top: 8px;">Tambah sekarang</button>
                        </td>
                    </tr>
                </template>
                <template x-for="resident in residents" :key="resident.id">
                    <tr>
                        <td class="wp-desa-mono" x-text="resident.nik"></td>
                        <td class="wp-desa-mono" x-text="resident.no_kk || '-'"></td>
                        <td>
                            <div style="font-weight: 600; color: var(--ink);" x-text="resident.nama_lengkap"></div>
                            <div class="wp-desa-row-subtitle" x-text="resident.status_perkawinan"></div>
                        </td>
                        <td>
                            <span class="wp-desa-badge"
                                :class="resident.jenis_kelamin === 'Laki-laki' ? 'wp-desa-badge-default' : 'wp-desa-badge-danger'"
                                x-text="resident.jenis_kelamin">
                            </span>
                        </td>
                        <td>
                            <div x-text="resident.tempat_lahir"></div>
                            <div class="wp-desa-row-subtitle" x-text="formatDate(resident.tanggal_lahir)"></div>
                        </td>
                        <td x-text="resident.pekerjaan"></td>
                        <td style="text-align: right;">
                            <div class="wp-desa-inline-actions-end">
                                <button @click="openModal('edit', resident)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Edit">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button @click="deleteResident(resident.id)" class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm" title="Hapus">
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
                <button type="button" @click="closeModal" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>

            <form @submit.prevent="saveResident">
                <div class="wp-desa-modal-body">
                    <div class="wp-desa-form-grid">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="nik">NIK <span class="wp-desa-req">*</span></label>
                            <input type="text" id="nik" x-model="form.nik" required class="wp-desa-input" placeholder="16 digit NIK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="no_kk">No. KK</label>
                            <input type="text" id="no_kk" x-model="form.no_kk" class="wp-desa-input" placeholder="16 digit No. KK" maxlength="16">
                        </div>
                        <div class="wp-desa-form-group full-width">
                            <label class="wp-desa-label" for="nama_lengkap">Nama Lengkap <span class="wp-desa-req">*</span></label>
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
        <button @click="notification.show = false" class="wp-desa-toast-close">
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