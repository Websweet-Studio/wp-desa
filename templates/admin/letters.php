<div class="wrap wp-desa-wrapper" x-data="lettersManager()">

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

        .wp-desa-tab-count {
            background: #e2e8f0;
            color: #475569;
            padding: 2px 6px;
            border-radius: 99px;
            font-size: 11px;
            margin-left: 4px;
        }

        .wp-desa-tab.active .wp-desa-tab-count {
            background: #eff6ff;
            color: #2563eb;
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

        .wp-desa-badge-pending {
            background: #f1f5f9;
            color: #475569;
        }

        .wp-desa-badge-processed {
            background: #fef3c7;
            color: #d97706;
        }

        .wp-desa-badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .wp-desa-badge-rejected {
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

        .wp-desa-info-row {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .wp-desa-info-label {
            font-weight: 600;
            color: #64748b;
            width: 120px;
            display: inline-block;
        }

        .wp-desa-info-value {
            color: #1e293b;
        }

        .wp-desa-detail-box {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
            margin-top: 4px;
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
            <h1 class="wp-desa-title">Layanan Surat Online</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola permohonan surat dari warga desa.</p>
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

    <!-- Main Content Card -->
    <div class="wp-desa-card">
        <!-- Tabs/Filters -->
        <div class="wp-desa-tabs">
            <div class="wp-desa-tab" :class="{'active': currentStatus === ''}" @click="filterStatus('')">
                Semua <span class="wp-desa-tab-count" x-text="counts.all"></span>
            </div>
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'pending'}" @click="filterStatus('pending')">
                Pending <span class="wp-desa-tab-count" x-text="counts.pending"></span>
            </div>
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'processed'}" @click="filterStatus('processed')">
                Diproses <span class="wp-desa-tab-count" x-text="counts.processed"></span>
            </div>
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'completed'}" @click="filterStatus('completed')">
                Selesai <span class="wp-desa-tab-count" x-text="counts.completed"></span>
            </div>
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'rejected'}" @click="filterStatus('rejected')">
                Ditolak <span class="wp-desa-tab-count" x-text="counts.rejected"></span>
            </div>
        </div>

        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Tracking</th>
                    <th>Jenis Surat</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                            <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; font-size: 24px; width: 24px; height: 24px;"></span>
                            <div style="margin-top: 8px;">Memuat data...</div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && letters.length === 0">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                            <div>Tidak ada permohonan surat.</div>
                        </td>
                    </tr>
                </template>
                <template x-for="letter in letters" :key="letter.id">
                    <tr>
                        <td>
                            <div x-text="formatDate(letter.created_at)"></div>
                            <div style="font-size: 11px; color: #94a3b8;" x-text="timeAgo(letter.created_at)"></div>
                        </td>
                        <td class="font-mono text-xs" style="font-family: monospace; color: #64748b;">
                            <strong x-text="letter.tracking_code" style="color: #1e293b;"></strong>
                        </td>
                        <td x-text="letter.type_name"></td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;" x-text="letter.name"></div>
                            <div style="font-size: 12px; color: #64748b;">
                                NIK: <span x-text="letter.nik"></span>
                            </div>
                        </td>
                        <td>
                            <span class="wp-desa-badge"
                                :class="'wp-desa-badge-' + letter.status"
                                x-text="getStatusLabel(letter.status)">
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button @click="openDetail(letter)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                    Lihat Detail
                                </button>
                                <button @click="printLetter(letter.id)" class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-sm" title="Cetak Surat">
                                    <span class="dashicons dashicons-printer"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination" x-show="!loading && letters.length > 0">
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

    <!-- Detail Modal -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none;"
        x-transition.opacity>

        <div class="wp-desa-modal-content" @click.outside="isModalOpen = false">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">Detail Permohonan</h2>
                <button type="button" @click="isModalOpen = false" style="background:none; border:none; cursor:pointer; color: #94a3b8; display: flex;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 20px;"></span>
                </button>
            </div>

            <template x-if="selectedLetter">
                <div class="wp-desa-modal-body">
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Jenis Surat:</span>
                        <span class="wp-desa-info-value" x-text="selectedLetter.type_name"></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Kode Tracking:</span>
                        <span class="wp-desa-info-value" style="font-family: monospace;" x-text="selectedLetter.tracking_code"></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Pemohon:</span>
                        <span class="wp-desa-info-value">
                            <span x-text="selectedLetter.name"></span>
                            <span style="color: #64748b;">(NIK: <span x-text="selectedLetter.nik"></span>)</span>
                        </span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">No. HP:</span>
                        <span class="wp-desa-info-value" x-text="selectedLetter.phone || '-'"></span>
                    </div>

                    <div style="margin-top: 20px; margin-bottom: 20px;">
                        <label class="wp-desa-label">Keperluan / Keterangan:</label>
                        <div class="wp-desa-detail-box" x-text="selectedLetter.details || '-'"></div>
                    </div>

                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Update Status:</label>
                        <select x-model="selectedLetter.status" @change="updateStatus(selectedLetter.id, $event.target.value)" class="wp-desa-select">
                            <option value="pending">Pending (Menunggu)</option>
                            <option value="processed">Processed (Sedang Diproses)</option>
                            <option value="completed">Completed (Selesai/Siap Ambil)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                        </select>
                    </div>
                </div>
            </template>

            <div class="wp-desa-modal-footer">
                <template x-if="selectedLetter">
                    <button @click="printLetter(selectedLetter.id)" class="wp-desa-btn wp-desa-btn-secondary">
                        <span class="dashicons dashicons-printer"></span> Cetak Surat
                    </button>
                </template>
                <button @click="isModalOpen = false" class="wp-desa-btn wp-desa-btn-primary">Tutup</button>
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
    function lettersManager() {
        return {
            letters: [],
            loading: true,
            currentStatus: '',
            pagination: {
                currentPage: 1,
                perPage: 20,
                totalItems: 0,
                totalPages: 0
            },
            counts: {
                all: 0,
                pending: 0,
                processed: 0,
                completed: 0,
                rejected: 0
            },
            isModalOpen: false,
            selectedLetter: null,
            notification: {
                show: false,
                message: '',
                type: 'success'
            },

            init() {
                this.fetchLetters();
            },

            fetchLetters() {
                this.loading = true;

                let url = '<?php echo esc_url_raw(rest_url('wp-desa/v1/letters')); ?>';
                const params = new URLSearchParams({
                    page: this.pagination.currentPage,
                    per_page: this.pagination.perPage
                });

                if (this.currentStatus) {
                    params.append('status', this.currentStatus);
                }

                url += '?' + params.toString();

                fetch(url, {
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.letters = data.data;
                        this.pagination = {
                            currentPage: parseInt(data.meta.current_page),
                            perPage: parseInt(data.meta.per_page),
                            totalItems: parseInt(data.meta.total_items),
                            totalPages: parseInt(data.meta.total_pages)
                        };
                        this.counts = data.counts;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                        this.showNotification('Gagal memuat data.', 'error');
                    });
            },

            filterStatus(status) {
                this.currentStatus = status;
                this.pagination.currentPage = 1;
                this.fetchLetters();
            },

            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.pagination.currentPage++;
                    this.fetchLetters();
                }
            },

            prevPage() {
                if (this.pagination.currentPage > 1) {
                    this.pagination.currentPage--;
                    this.fetchLetters();
                }
            },

            getStatusLabel(status) {
                const labels = {
                    'pending': 'Pending',
                    'processed': 'Diproses',
                    'completed': 'Selesai',
                    'rejected': 'Ditolak'
                };
                return labels[status] || status;
            },

            openDetail(letter) {
                this.selectedLetter = {
                    ...letter
                }; // Copy object
                this.isModalOpen = true;
            },

            updateStatus(id, newStatus) {
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/letters/')); ?>' + id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showNotification('Status berhasil diperbarui');
                            this.fetchLetters();
                            this.isModalOpen = false;
                        } else {
                            this.showNotification('Gagal update status', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.showNotification('Terjadi kesalahan sistem', 'error');
                    });
            },

            generateDummy() {
                if (!confirm('Buat 20 data permohonan surat dummy? Pastikan sudah ada data penduduk.')) return;

                this.loading = true;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/letters/seed')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.showNotification(data.message || 'Berhasil generate dummy data.');
                        this.fetchLetters();
                    })
                    .catch(err => {
                        console.error(err);
                        this.showNotification('Terjadi kesalahan.', 'error');
                        this.loading = false;
                    });
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);

                let interval = seconds / 31536000;
                if (interval > 1) return Math.floor(interval) + " tahun lalu";
                interval = seconds / 2592000;
                if (interval > 1) return Math.floor(interval) + " bulan lalu";
                interval = seconds / 86400;
                if (interval > 1) return Math.floor(interval) + " hari lalu";
                interval = seconds / 3600;
                if (interval > 1) return Math.floor(interval) + " jam lalu";
                interval = seconds / 60;
                if (interval > 1) return Math.floor(interval) + " menit lalu";
                return Math.floor(seconds) + " detik lalu";
            },

            printLetter(id) {
                const url = '<?php echo admin_url('admin-post.php'); ?>?action=wp_desa_print_letter&id=' + id;
                window.open(url, '_blank');
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