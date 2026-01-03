<div class="wrap wp-desa-wrapper" x-data="complaintsManager()">

    <!-- CSS moved to assets/css/admin/style.css -->

    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Aspirasi & Pengaduan Warga</h1>
            <p style="color: #64748b; margin: 4px 0 0 0; font-size: 14px;">Kelola aspirasi dan pengaduan dari warga.</p>
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
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'in_progress'}" @click="filterStatus('in_progress')">
                Diproses <span class="wp-desa-tab-count" x-text="counts.in_progress"></span>
            </div>
            <div class="wp-desa-tab" :class="{'active': currentStatus === 'resolved'}" @click="filterStatus('resolved')">
                Selesai <span class="wp-desa-tab-count" x-text="counts.resolved"></span>
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
                    <th>Kategori</th>
                    <th>Pelapor</th>
                    <th>Judul</th>
                    <th>Status</th>
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
                <template x-if="!loading && complaints.length === 0">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                            <div>Tidak ada aduan.</div>
                        </td>
                    </tr>
                </template>
                <template x-for="item in complaints" :key="item.id">
                    <tr>
                        <td>
                            <div x-text="formatDate(item.created_at)"></div>
                            <div style="font-size: 11px; color: #94a3b8;" x-text="timeAgo(item.created_at)"></div>
                        </td>
                        <td class="font-mono text-xs" style="font-family: monospace; color: #64748b;">
                            <strong x-text="item.tracking_code" style="color: #1e293b;"></strong>
                        </td>
                        <td x-text="item.category"></td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;" x-text="item.reporter_name"></div>
                            <div style="font-size: 12px; color: #64748b;" x-text="item.reporter_contact"></div>
                        </td>
                        <td x-text="item.subject"></td>
                        <td>
                            <span class="wp-desa-badge"
                                :class="'wp-desa-badge-' + item.status"
                                x-text="getStatusLabel(item.status)">
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button @click="openDetail(item)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                    Lihat Detail
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination" x-show="!loading && complaints.length > 0">
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
                <h2 class="wp-desa-modal-title">Detail Aduan</h2>
                <button type="button" @click="isModalOpen = false" style="background:none; border:none; cursor:pointer; color: #94a3b8; display: flex;">
                    <span class="dashicons dashicons-no-alt" style="font-size: 20px;"></span>
                </button>
            </div>

            <template x-if="selectedItem">
                <div class="wp-desa-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <div class="wp-desa-info-row">
                                <span class="wp-desa-info-label">Pelapor:</span>
                                <span class="wp-desa-info-value" x-text="selectedItem.reporter_name"></span>
                            </div>
                            <div class="wp-desa-info-row">
                                <span class="wp-desa-info-label">Kontak:</span>
                                <span class="wp-desa-info-value" x-text="selectedItem.reporter_contact || '-'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="wp-desa-info-row">
                                <span class="wp-desa-info-label">Kategori:</span>
                                <span class="wp-desa-info-value" x-text="selectedItem.category"></span>
                            </div>
                            <div class="wp-desa-info-row">
                                <span class="wp-desa-info-label">Tanggal:</span>
                                <span class="wp-desa-info-value" x-text="formatDate(selectedItem.created_at)"></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Judul:</span>
                        <div class="wp-desa-info-value" style="font-weight: 500;" x-text="selectedItem.subject"></div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Isi Laporan:</span>
                        <div class="wp-desa-detail-box" style="white-space: pre-wrap;" x-text="selectedItem.description"></div>
                    </div>

                    <template x-if="selectedItem.photo_url">
                        <div style="margin-bottom: 20px;">
                            <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Foto Lampiran:</span>
                            <a :href="selectedItem.photo_url" target="_blank" style="display: block;">
                                <img :src="selectedItem.photo_url" style="max-width: 100%; max-height: 200px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            </a>
                        </div>
                    </template>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

                    <div class="wp-desa-form-grid">
                        <div>
                            <label class="wp-desa-label">Update Status:</label>
                            <select x-model="selectedItem.status" class="wp-desa-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">Diproses</option>
                                <option value="resolved">Selesai</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>

                        <div>
                            <label class="wp-desa-label">Tanggapan Admin:</label>
                            <textarea x-model="selectedItem.response" rows="3" class="wp-desa-textarea" placeholder="Tulis tanggapan..."></textarea>
                        </div>
                    </div>
                </div>
            </template>

            <div class="wp-desa-modal-footer">
                <button @click="updateStatus()" class="wp-desa-btn wp-desa-btn-primary">Simpan Perubahan</button>
                <button @click="isModalOpen = false" class="wp-desa-btn wp-desa-btn-secondary">Tutup</button>
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
        <span class="dashicons" :class="notification.type === 'error' ? 'dashicons-warning' : 'dashicons-yes'"></span>
        <span x-text="notification.message"></span>
        <button @click="notification.show = false" style="background:none; border:none; color:white; cursor:pointer; margin-left: 10px; opacity: 0.8;">
            <span class="dashicons dashicons-no"></span>
        </button>
    </div>

</div>

<script>
    function complaintsManager() {
        return {
            complaints: [],
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
                in_progress: 0,
                resolved: 0,
                rejected: 0
            },
            isModalOpen: false,
            selectedItem: null,
            notification: {
                show: false,
                message: '',
                type: 'success'
            },

            init() {
                this.fetchComplaints();
            },

            fetchComplaints() {
                this.loading = true;

                let url = '<?php echo esc_url_raw(rest_url('wp-desa/v1/complaints')); ?>';
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
                        this.complaints = data.data;
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
                this.fetchComplaints();
            },

            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.pagination.currentPage++;
                    this.fetchComplaints();
                }
            },

            prevPage() {
                if (this.pagination.currentPage > 1) {
                    this.pagination.currentPage--;
                    this.fetchComplaints();
                }
            },

            openDetail(item) {
                this.selectedItem = {
                    ...item
                }; // Copy object
                this.isModalOpen = true;
            },

            updateStatus() {
                const id = this.selectedItem.id;
                const newStatus = this.selectedItem.status;
                const response = this.selectedItem.response;

                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/complaints/')); ?>' + id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            response: response
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showNotification('Status berhasil diperbarui');
                            this.fetchComplaints();
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
                if (!confirm('Buat 20 data aduan dummy?')) return;

                this.loading = true;
                fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/complaints/seed')); ?>', {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showNotification(data.message);
                            this.fetchComplaints();
                        } else {
                            this.showNotification('Gagal generate dummy', 'error');
                            this.loading = false;
                        }
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

            getStatusLabel(status) {
                const map = {
                    'pending': 'Pending',
                    'in_progress': 'Diproses',
                    'resolved': 'Selesai',
                    'rejected': 'Ditolak'
                };
                return map[status] || status;
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