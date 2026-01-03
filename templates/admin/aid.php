<div class="wrap" x-data="aidManager()">
    <h1 class="wp-heading-inline">Program & Bantuan Sosial</h1>
    <button @click="openProgramModal()" class="page-title-action">Tambah Program</button>
    <button @click="seedData" class="page-title-action" style="margin-left: 10px;">Generate Dummy</button>
    <hr class="wp-header-end">

    <!-- View: Programs List -->
    <div x-show="view === 'programs'" style="margin-top: 20px;">
        <table class="wp-list-table widefat fixed striped">
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
                <template x-for="p in programs" :key="p.id">
                    <tr>
                        <td>
                            <strong x-text="p.name" style="font-size: 1.1em; display: block; margin-bottom: 4px;"></strong>
                            <span x-text="p.description" style="color: #64748b;"></span>
                        </td>
                        <td x-text="p.origin"></td>
                        <td x-text="p.year"></td>
                        <td x-text="p.quota"></td>
                        <td x-text="formatCurrency(p.amount_per_recipient)"></td>
                        <td>
                            <span :class="p.status === 'active' ? 'wp-desa-badge success' : 'wp-desa-badge default'" x-text="p.status === 'active' ? 'Aktif' : 'Tutup'"></span>
                        </td>
                        <td>
                            <button @click="viewRecipients(p)" class="button button-primary button-small">Kelola Penerima</button>
                            <button @click="editProgram(p)" class="button button-small">Edit</button>
                            <button @click="deleteProgram(p.id)" class="button button-small button-link-delete">Hapus</button>
                        </td>
                    </tr>
                </template>
                <template x-if="programs.length === 0">
                    <tr><td colspan="7">Belum ada program bantuan.</td></tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- View: Recipients List -->
    <div x-show="view === 'recipients'" style="margin-top: 20px;">
        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <button @click="view = 'programs'" class="button">&larr; Kembali</button>
            <h2 style="margin: 0;">Penerima: <span x-text="activeProgram?.name"></span></h2>
            <button @click="openRecipientModal()" class="button button-primary">Tambah Penerima</button>
        </div>

        <div class="notice notice-info inline">
            <p>Total Penerima: <strong x-text="recipients.length"></strong> / Kuota: <strong x-text="activeProgram?.quota"></strong></p>
        </div>

        <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
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
                <template x-for="r in recipients" :key="r.id">
                    <tr>
                        <td x-text="r.nik"></td>
                        <td x-text="r.nama_lengkap"></td>
                        <td x-text="r.alamat"></td>
                        <td x-text="r.jenis_kelamin"></td>
                        <td>
                            <select x-model="r.status" @change="updateStatus(r)" 
                                :class="{
                                    'status-pending': r.status === 'pending',
                                    'status-approved': r.status === 'approved',
                                    'status-rejected': r.status === 'rejected',
                                    'status-distributed': r.status === 'distributed'
                                }"
                                style="font-size: 12px; padding: 0 24px 0 8px; min-height: 28px;">
                                <option value="pending">Pending</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                                <option value="distributed">Disalurkan</option>
                            </select>
                        </td>
                        <td x-text="r.distributed_at ? formatDate(r.distributed_at) : '-'"></td>
                        <td>
                            <button @click="deleteRecipient(r.id)" class="button button-small button-link-delete">Hapus</button>
                        </td>
                    </tr>
                </template>
                <template x-if="recipients.length === 0">
                    <tr><td colspan="7">Belum ada penerima terdaftar.</td></tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal Program -->
    <div x-show="showProgramModal" class="wp-desa-modal">
        <div class="wp-desa-modal-content">
            <h2 x-text="editMode ? 'Edit Program' : 'Tambah Program'"></h2>
            <form @submit.prevent="saveProgram">
                <table class="form-table">
                    <tr>
                        <th>Nama Program</th>
                        <td><input type="text" x-model="form.name" class="regular-text" required placeholder="Contoh: BLT Dana Desa"></td>
                    </tr>
                    <tr>
                        <th>Asal Dana</th>
                        <td><input type="text" x-model="form.origin" class="regular-text" required placeholder="Contoh: Dana Desa / Kemensos"></td>
                    </tr>
                    <tr>
                        <th>Tahun Anggaran</th>
                        <td><input type="number" x-model="form.year" class="small-text" required></td>
                    </tr>
                    <tr>
                        <th>Kuota Penerima</th>
                        <td><input type="number" x-model="form.quota" class="small-text" required></td>
                    </tr>
                    <tr>
                        <th>Nominal Bantuan (Rp)</th>
                        <td><input type="number" x-model="form.amount_per_recipient" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td><textarea x-model="form.description" class="large-text" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <select x-model="form.status">
                                <option value="active">Aktif</option>
                                <option value="closed">Tutup</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" @click="showProgramModal = false" class="button">Batal</button>
                    <button type="submit" class="button button-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Recipient -->
    <div x-show="showRecipientModal" class="wp-desa-modal">
        <div class="wp-desa-modal-content">
            <h2>Tambah Penerima</h2>
            <p>Masukkan NIK Penduduk yang akan menerima bantuan ini.</p>
            <form @submit.prevent="addRecipient">
                <table class="form-table">
                    <tr>
                        <th>NIK Penduduk</th>
                        <td>
                            <input type="text" x-model="recipientForm.nik" class="regular-text" required placeholder="16 digit NIK">
                            <p class="description">Pastikan penduduk sudah terdaftar di data kependudukan.</p>
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" @click="showRecipientModal = false" class="button">Batal</button>
                    <button type="submit" class="button button-primary">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.wp-desa-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}
.wp-desa-badge.success { background: #d1fae5; color: #065f46; }
.wp-desa-badge.default { background: #f1f5f9; color: #475569; }

.status-pending { color: #d97706; font-weight: 600; }
.status-approved { color: #2563eb; font-weight: 600; }
.status-rejected { color: #dc2626; font-weight: 600; }
.status-distributed { color: #059669; font-weight: 600; }

.wp-desa-modal {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); z-index: 9999;
    display: flex; align-items: center; justify-content: center;
}
.wp-desa-modal-content {
    background: #fff; padding: 20px; border-radius: 8px;
    width: 500px; max-width: 90%;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aidManager', () => ({
        view: 'programs', // programs | recipients
        programs: [],
        recipients: [],
        activeProgram: null,
        
        showProgramModal: false,
        showRecipientModal: false,
        editMode: false,
        
        form: { id: null, name: '', origin: '', year: new Date().getFullYear(), quota: 0, amount_per_recipient: 0, description: '', status: 'active' },
        recipientForm: { nik: '' },

        init() {
            this.fetchPrograms();
        },

        fetchPrograms() {
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs')); ?>', {
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(res => res.json())
            .then(data => this.programs = data);
        },

        viewRecipients(program) {
            this.activeProgram = program;
            this.view = 'recipients';
            this.fetchRecipients();
        },

        fetchRecipients() {
            if (!this.activeProgram) return;
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + this.activeProgram.id + '/recipients', {
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(res => res.json())
            .then(data => this.recipients = data);
        },

        openProgramModal() {
            this.editMode = false;
            this.form = { id: null, name: '', origin: '', year: new Date().getFullYear(), quota: 0, amount_per_recipient: 0, description: '', status: 'active' };
            this.showProgramModal = true;
        },

        editProgram(p) {
            this.editMode = true;
            this.form = { ...p };
            this.showProgramModal = true;
        },

        saveProgram() {
            const url = this.editMode 
                ? '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + this.form.id
                : '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs')); ?>';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.id) {
                    this.showProgramModal = false;
                    this.fetchPrograms();
                } else {
                    alert('Gagal menyimpan program.');
                }
            });
        },

        deleteProgram(id) {
            if (!confirm('Hapus program ini beserta semua data penerimanya?')) return;
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(() => this.fetchPrograms());
        },

        openRecipientModal() {
            this.recipientForm.nik = '';
            this.showRecipientModal = true;
        },

        addRecipient() {
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + this.activeProgram.id + '/recipients', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(this.recipientForm)
            })
            .then(res => res.json())
            .then(data => {
                if (data.code) { // Error
                    alert(data.message);
                } else {
                    this.showRecipientModal = false;
                    this.fetchRecipients();
                }
            });
        },

        updateStatus(recipient) {
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-recipients/')); ?>' + recipient.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({ status: recipient.status })
            })
            .then(() => this.fetchRecipients()); // Refresh to update distributed_at
        },

        deleteRecipient(id) {
            if (!confirm('Hapus penerima ini?')) return;
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-recipients/')); ?>' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(() => this.fetchRecipients());
        },

        seedData() {
            if (!confirm('Buat data dummy bantuan?')) return;
            fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid/seed')); ?>', {
                method: 'POST',
                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                this.fetchPrograms();
            });
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('id-ID');
        }
    }));
});
</script>
