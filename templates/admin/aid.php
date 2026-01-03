<div class="wrap wp-desa-wrapper" x-data="aidManager()">

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

        .wp-desa-badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .wp-desa-badge-default {
            background: #f1f5f9;
            color: #475569;
        }

        .wp-desa-badge-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .wp-desa-badge-danger {
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
    </style>

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Program & Bantuan Sosial</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola program bantuan dan penerima manfaat.</p>
        </div>
        <div class="wp-desa-actions">
            <template x-if="view === 'programs'">
                <?php 
                $settings = get_option('wp_desa_settings', []);
                if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): 
                ?>
                <button @click="seedData" class="wp-desa-btn wp-desa-btn-danger" style="background: #fff1f2; color: #e11d48; border-color: #fecdd3;">
                    <span class="dashicons dashicons-database"></span> Generate Dummy
                </button>
                <?php endif; ?>
            </template>
            <template x-if="view === 'programs'">
                <button @click="openProgramModal()" class="wp-desa-btn wp-desa-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Tambah Program
                </button>
            </template>
            <template x-if="view === 'recipients'">
                <button @click="view = 'programs'" class="wp-desa-btn wp-desa-btn-secondary">
                    &larr; Kembali
                </button>
            </template>
            <template x-if="view === 'recipients'">
                <button @click="openRecipientModal()" class="wp-desa-btn wp-desa-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Tambah Penerima
                </button>
            </template>
        </div>
    </div>

    <!-- View: Programs List -->
    <div x-show="view === 'programs'" class="wp-desa-card">
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
                <template x-for="p in programs" :key="p.id">
                    <tr>
                        <td>
                            <strong x-text="p.name" style="font-size: 14px; display: block; margin-bottom: 2px;"></strong>
                            <span x-text="p.description" style="color: #64748b; font-size: 13px;"></span>
                        </td>
                        <td x-text="p.origin"></td>
                        <td x-text="p.year"></td>
                        <td x-text="p.quota"></td>
                        <td x-text="formatCurrency(p.amount_per_recipient)"></td>
                        <td>
                            <span :class="p.status === 'active' ? 'wp-desa-badge wp-desa-badge-success' : 'wp-desa-badge wp-desa-badge-default'" x-text="p.status === 'active' ? 'Aktif' : 'Tutup'"></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <button @click="viewRecipients(p)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Kelola Penerima</button>
                                <button @click="editProgram(p)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="padding: 4px 8px;">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button @click="deleteProgram(p.id)" class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm" style="padding: 4px 8px;">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="programs.length === 0">
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 40px;">Belum ada program bantuan.</td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination Programs -->
        <div class="wp-desa-pagination" x-show="programsTotalItems > 0">
            <div class="wp-desa-pagination-info">
                Menampilkan <span x-text="(programsPage - 1) * programsPerPage + 1"></span> sampai <span x-text="Math.min(programsPage * programsPerPage, programsTotalItems)"></span> dari <span x-text="programsTotalItems"></span> data
            </div>
            <div class="wp-desa-pagination-controls">
                <button @click="prevPagePrograms" :disabled="programsPage === 1" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="programsPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                    &larr; Sebelumnya
                </button>
                <button @click="nextPagePrograms" :disabled="programsPage === programsTotalPages" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="programsPage === programsTotalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                    Selanjutnya &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- View: Recipients List -->
    <div x-show="view === 'recipients'">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0;">
                Penerima: <span x-text="activeProgram?.name" style="color: #2563eb;"></span>
            </h2>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 14px;">
                Total Penerima: <strong x-text="recipientsTotalItems"></strong> / Kuota: <strong x-text="activeProgram?.quota"></strong>
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
                    <template x-for="r in recipients" :key="r.id">
                        <tr>
                            <td x-text="r.nik"></td>
                            <td x-text="r.nama_lengkap"></td>
                            <td x-text="r.alamat"></td>
                            <td x-text="r.jenis_kelamin"></td>
                            <td>
                                <select x-model="r.status" @change="updateStatus(r)"
                                    class="wp-desa-select"
                                    style="padding-top: 4px; padding-bottom: 4px; font-size: 13px; width: auto;">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="distributed">Disalurkan</option>
                                </select>
                            </td>
                            <td x-text="r.distributed_at ? formatDate(r.distributed_at) : '-'"></td>
                            <td>
                                <button @click="deleteRecipient(r.id)" class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm" style="padding: 4px 8px;">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="recipients.length === 0">
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 40px;">Belum ada penerima terdaftar.</td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Pagination Recipients -->
            <div class="wp-desa-pagination" x-show="recipientsTotalItems > 0">
                <div class="wp-desa-pagination-info">
                    Menampilkan <span x-text="(recipientsPage - 1) * recipientsPerPage + 1"></span> sampai <span x-text="Math.min(recipientsPage * recipientsPerPage, recipientsTotalItems)"></span> dari <span x-text="recipientsTotalItems"></span> data
                </div>
                <div class="wp-desa-pagination-controls">
                    <button @click="prevPageRecipients" :disabled="recipientsPage === 1" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="recipientsPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                        &larr; Sebelumnya
                    </button>
                    <button @click="nextPageRecipients" :disabled="recipientsPage === recipientsTotalPages" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" :style="recipientsPage === recipientsTotalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                        Selanjutnya &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Program -->
    <div x-show="showProgramModal" class="wp-desa-modal-overlay" style="display: none;" x-transition.opacity>
        <div class="wp-desa-modal-content" @click.away="showProgramModal = false">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title" x-text="editMode ? 'Edit Program' : 'Tambah Program'"></h2>
                <button @click="showProgramModal = false" style="background: none; border: none; cursor: pointer; color: #64748b;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 24px;"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <form @submit.prevent="saveProgram">
                    <div class="wp-desa-form-grid">
                        <div>
                            <label class="wp-desa-label">Nama Program</label>
                            <input type="text" x-model="form.name" class="wp-desa-input" required placeholder="Contoh: BLT Dana Desa">
                        </div>
                        <div>
                            <label class="wp-desa-label">Asal Dana</label>
                            <input type="text" x-model="form.origin" class="wp-desa-input" required placeholder="Contoh: Dana Desa / Kemensos">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label class="wp-desa-label">Tahun Anggaran</label>
                                <input type="number" x-model="form.year" class="wp-desa-input" required>
                            </div>
                            <div>
                                <label class="wp-desa-label">Kuota Penerima</label>
                                <input type="number" x-model="form.quota" class="wp-desa-input" required>
                            </div>
                        </div>
                        <div>
                            <label class="wp-desa-label">Nominal Bantuan (Rp)</label>
                            <input type="number" x-model="form.amount_per_recipient" class="wp-desa-input" required>
                        </div>
                        <div>
                            <label class="wp-desa-label">Deskripsi</label>
                            <textarea x-model="form.description" class="wp-desa-textarea" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="wp-desa-label">Status</label>
                            <select x-model="form.status" class="wp-desa-select">
                                <option value="active">Aktif</option>
                                <option value="closed">Tutup</option>
                            </select>
                        </div>
                    </div>
                    <div class="wp-desa-modal-footer" style="margin-top: 20px;">
                        <button type="button" @click="showProgramModal = false" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Recipient -->
    <div x-show="showRecipientModal" class="wp-desa-modal-overlay" style="display: none;" x-transition.opacity>
        <div class="wp-desa-modal-content" @click.away="showRecipientModal = false">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">Tambah Penerima</h2>
                <button @click="showRecipientModal = false" style="background: none; border: none; cursor: pointer; color: #64748b;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 24px;"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <p style="margin-bottom: 16px; color: #64748b;">Masukkan NIK Penduduk yang akan menerima bantuan ini.</p>
                <form @submit.prevent="addRecipient">
                    <div class="wp-desa-form-grid">
                        <div>
                            <label class="wp-desa-label">NIK Penduduk</label>
                            <input type="text" x-model="recipientForm.nik" class="wp-desa-input" required placeholder="16 digit NIK">
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">Pastikan penduduk sudah terdaftar di data kependudukan.</p>
                        </div>
                    </div>
                    <div class="wp-desa-modal-footer" style="margin-top: 20px;">
                        <button type="button" @click="showRecipientModal = false" class="wp-desa-btn wp-desa-btn-secondary">Batal</button>
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-8"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-8"
        class="wp-desa-toast"
        :class="{'error': toast.type === 'error'}"
        style="display: none;">
        <span class="dashicons" :class="toast.type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning'"></span>
        <span x-text="toast.message"></span>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('aidManager', () => ({
            view: 'programs', // programs | recipients
            programs: [],
            recipients: [],
            activeProgram: null,

            // Pagination Programs
            programsPage: 1,
            programsPerPage: 20,
            programsTotalItems: 0,
            programsTotalPages: 0,

            // Pagination Recipients
            recipientsPage: 1,
            recipientsPerPage: 20,
            recipientsTotalItems: 0,
            recipientsTotalPages: 0,

            showProgramModal: false,
            showRecipientModal: false,
            editMode: false,

            form: {
                id: null,
                name: '',
                origin: '',
                year: new Date().getFullYear(),
                quota: 0,
                amount_per_recipient: 0,
                description: '',
                status: 'active'
            },
            recipientForm: {
                nik: ''
            },

            toast: {
                visible: false,
                message: '',
                type: 'success'
            },

            init() {
                this.fetchPrograms();
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.visible = true;
                setTimeout(() => {
                    this.toast.visible = false;
                }, 3000);
            },

            fetchPrograms() {
                let url = '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs')); ?>';
                url += `?page=${this.programsPage}&per_page=${this.programsPerPage}`;

                fetch(url, {
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.meta) {
                            this.programs = response.data;
                            this.programsTotalItems = response.meta.total_items;
                            this.programsTotalPages = response.meta.total_pages;
                        } else {
                            // Fallback if backend not updated yet
                            this.programs = response;
                        }
                    });
            },

            nextPagePrograms() {
                if (this.programsPage < this.programsTotalPages) {
                    this.programsPage++;
                    this.fetchPrograms();
                }
            },

            prevPagePrograms() {
                if (this.programsPage > 1) {
                    this.programsPage--;
                    this.fetchPrograms();
                }
            },

            viewRecipients(program) {
                this.activeProgram = program;
                this.view = 'recipients';
                this.recipientsPage = 1; // Reset to page 1
                this.fetchRecipients();
            },

            fetchRecipients() {
                if (!this.activeProgram) return;

                let url = '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + this.activeProgram.id + '/recipients';
                url += `?page=${this.recipientsPage}&per_page=${this.recipientsPerPage}`;

                fetch(url, {
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.meta) {
                            this.recipients = response.data;
                            this.recipientsTotalItems = response.meta.total_items;
                            this.recipientsTotalPages = response.meta.total_pages;
                        } else {
                            // Fallback
                            this.recipients = response;
                        }
                    });
            },

            nextPageRecipients() {
                if (this.recipientsPage < this.recipientsTotalPages) {
                    this.recipientsPage++;
                    this.fetchRecipients();
                }
            },

            prevPageRecipients() {
                if (this.recipientsPage > 1) {
                    this.recipientsPage--;
                    this.fetchRecipients();
                }
            },

            openProgramModal() {
                this.editMode = false;
                this.form = {
                    id: null,
                    name: '',
                    origin: '',
                    year: new Date().getFullYear(),
                    quota: 0,
                    amount_per_recipient: 0,
                    description: '',
                    status: 'active'
                };
                this.showProgramModal = true;
            },

            editProgram(p) {
                this.editMode = true;
                this.form = {
                    ...p
                };
                this.showProgramModal = true;
            },

            saveProgram() {
                const url = this.editMode ?
                    '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + this.form.id :
                    '<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs')); ?>';

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
                            this.showToast('Program berhasil disimpan');
                            this.fetchPrograms();
                        } else {
                            this.showToast('Gagal menyimpan program', 'error');
                        }
                    });
            },

            deleteProgram(id) {
                if (!confirm('Hapus program ini beserta semua data penerimanya?')) return;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(() => {
                        this.showToast('Program berhasil dihapus');
                        this.fetchPrograms();
                    });
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
                            this.showToast(data.message, 'error');
                        } else {
                            this.showRecipientModal = false;
                            this.showToast('Penerima berhasil ditambahkan');
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
                        body: JSON.stringify({
                            status: recipient.status
                        })
                    })
                    .then(() => {
                        this.showToast('Status penerima diperbarui');
                        this.fetchRecipients(); // Refresh to update distributed_at
                    });
            },

            deleteRecipient(id) {
                if (!confirm('Hapus penerima ini?')) return;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-recipients/')); ?>' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(() => {
                        this.showToast('Penerima dihapus');
                        this.fetchRecipients();
                    });
            },

            seedData() {
                if (!confirm('Buat data dummy bantuan?')) return;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid/seed')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.showToast(data.message);
                        this.fetchPrograms();
                    });
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(value);
            },

            formatDate(dateStr) {
                if (!dateStr) return '-';
                return new Date(dateStr).toLocaleDateString('id-ID');
            }
        }));
    });
</script>