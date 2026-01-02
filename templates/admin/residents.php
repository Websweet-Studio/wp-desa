<div class="wrap" x-data="residentsManager()">
    <h1 class="wp-heading-inline">Data Penduduk</h1>
    <button @click="openModal('add')" class="page-title-action">Tambah Baru</button>
    <button @click="exportResidents" class="page-title-action">Export Excel</button>
    <button @click="$refs.importFile.click()" class="page-title-action">Import Excel</button>
    <button @click="generateDummy" class="page-title-action" style="margin-left: 10px; border-color: #d63638; color: #d63638;">Generate Dummy (Dev)</button>
    <input type="file" x-ref="importFile" @change="importResidents" style="display:none" accept=".csv">
    <hr class="wp-header-end">

    <!-- Notification -->
    <div x-show="notification.show"
        x-transition
        :class="notification.type === 'success' ? 'notice notice-success is-dismissible' : 'notice notice-error is-dismissible'"
        style="margin-top: 10px; display: none;">
        <p x-text="notification.message"></p>
        <button type="button" class="notice-dismiss" @click="notification.show = false">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
    </div>

    <!-- Table -->
    <table class="wp-list-table widefat fixed striped table-view-list posts" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>Jenis Kelamin</th>
                <th>Tempat/Tgl Lahir</th>
                <th>Pekerjaan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr>
                    <td colspan="6">Memuat data...</td>
                </tr>
            </template>
            <template x-if="!loading && residents.length === 0">
                <tr>
                    <td colspan="6">Belum ada data penduduk.</td>
                </tr>
            </template>
            <template x-for="resident in residents" :key="resident.id">
                <tr>
                    <td x-text="resident.nik"></td>
                    <td class="row-title">
                        <strong x-text="resident.nama_lengkap"></strong>
                    </td>
                    <td x-text="resident.jenis_kelamin"></td>
                    <td>
                        <span x-text="resident.tempat_lahir"></span>,
                        <span x-text="resident.tanggal_lahir"></span>
                    </td>
                    <td x-text="resident.pekerjaan"></td>
                    <td>
                        <button @click="openModal('edit', resident)" class="button button-small">Edit</button>
                        <button @click="deleteResident(resident.id)" class="button button-small button-link-delete">Hapus</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- Modal -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none;"
        x-transition.opacity>

        <div class="wp-desa-modal-content" @click.outside="closeModal">
            <div class="wp-desa-modal-header">
                <h2 x-text="modalMode === 'add' ? 'Tambah Penduduk' : 'Edit Penduduk'" class="wp-desa-modal-title"></h2>
                <button type="button" @click="closeModal" style="background:none;border:none;cursor:pointer;">
                    <span class="dashicons dashicons-no-alt" style="color:#6b7280;"></span>
                </button>
            </div>

            <form @submit.prevent="saveResident">
                <div class="wp-desa-modal-body">
                    <table class="form-table">
                        <tr>
                            <th><label for="nik">NIK</label></th>
                            <td><input type="text" id="nik" x-model="form.nik" required placeholder="16 digit NIK"></td>
                        </tr>
                        <tr>
                            <th><label for="nama_lengkap">Nama Lengkap</label></th>
                            <td><input type="text" id="nama_lengkap" x-model="form.nama_lengkap" required placeholder="Nama sesuai KTP"></td>
                        </tr>
                        <tr>
                            <th><label for="jenis_kelamin">Jenis Kelamin</label></th>
                            <td>
                                <select id="jenis_kelamin" x-model="form.jenis_kelamin">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tempat_lahir">Tempat Lahir</label></th>
                            <td><input type="text" id="tempat_lahir" x-model="form.tempat_lahir"></td>
                        </tr>
                        <tr>
                            <th><label for="tanggal_lahir">Tanggal Lahir</label></th>
                            <td><input type="date" id="tanggal_lahir" x-model="form.tanggal_lahir"></td>
                        </tr>
                        <tr>
                            <th><label for="alamat">Alamat</label></th>
                            <td><textarea id="alamat" x-model="form.alamat" rows="3"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="status_perkawinan">Status Perkawinan</label></th>
                            <td>
                                <select id="status_perkawinan" x-model="form.status_perkawinan">
                                    <option value="Belum Kawin">Belum Kawin</option>
                                    <option value="Kawin">Kawin</option>
                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                    <option value="Cerai Mati">Cerai Mati</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pekerjaan">Pekerjaan</label></th>
                            <td><input type="text" id="pekerjaan" x-model="form.pekerjaan"></td>
                        </tr>
                    </table>
                </div>

                <div class="wp-desa-modal-footer">
                    <button type="button" @click="closeModal" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary" x-text="saving ? 'Menyimpan...' : 'Simpan'" :disabled="saving"></button>
                </div>
            </form>
        </div>
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
            notification: {
                show: false,
                message: '',
                type: 'success'
            },
            form: {
                id: null,
                nik: '',
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

            fetchResidents() {
                this.loading = true;
                fetch(wpDesaSettings.apiUrl, {
                        headers: {
                            'X-WP-Nonce': wpDesaSettings.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.residents = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                        this.showNotification('Gagal memuat data.', 'error');
                    });
            },

            openModal(mode, resident = null) {
                this.modalMode = mode;
                if (mode === 'edit' && resident) {
                    this.form = {
                        ...resident
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
                        body: JSON.stringify({ count: 100 })
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