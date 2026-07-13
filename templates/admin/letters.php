<div class="wrap wp-desa-wrapper" x-data="lettersManager()">



    <!-- Header -->
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Layanan Surat Online</h1>
            <p class="wp-desa-helper">Kelola permohonan surat dari warga desa.</p>
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
                        <td colspan="6" class="wp-desa-empty-state">
                            <span class="dashicons dashicons-update wp-desa-spinner"></span>
                            <div class="wp-desa-mt-8">Memuat data...</div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && letters.length === 0">
                    <tr>
                        <td colspan="6" class="wp-desa-empty-state">
                            <div class="wp-desa-empty-icon">📭</div>
                            <div>Tidak ada permohonan surat.</div>
                        </td>
                    </tr>
                </template>
                <template x-for="letter in letters" :key="letter.id">
                    <tr>
                        <td>
                            <div x-text="formatDate(letter.created_at)"></div>
                            <div class="wp-desa-row-subtitle" x-text="timeAgo(letter.created_at)"></div>
                        </td>
                        <td class="wp-desa-mono">
                            <strong x-text="letter.tracking_code" style="color: var(--ink);"></strong>
                        </td>
                        <td x-text="letter.type_name"></td>
                        <td>
                            <div style="font-weight: 600; color: var(--ink);" x-text="letter.name"></div>
                            <div class="wp-desa-row-subtitle">
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
                            <div class="wp-desa-inline-actions-end">
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
                <button type="button" @click="isModalOpen = false" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
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
                            <span class="wp-desa-row-subtitle">(NIK: <span x-text="selectedLetter.nik"></span>)</span>
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
        <button @click="notification.show = false" class="wp-desa-toast-close">
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
                const url = '<?php echo admin_url('admin-post.php'); ?>?action=wp_desa_print_letter&id=' + id + '&_wpnonce=<?php echo wp_create_nonce('wp_desa_print_letter'); ?>';
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