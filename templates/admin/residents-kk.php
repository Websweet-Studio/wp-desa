<div class="wrap wp-desa-wrapper" x-data="kkManager()">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Kartu Keluarga</h1>
            <p class="wp-desa-helper">Kelompokkan penduduk berdasarkan Nomor Kartu Keluarga.</p>
        </div>
    </div>

    <div class="wp-desa-card">
        <table class="wp-desa-table">
            <thead>
                <tr>
                    <th>No. KK</th>
                    <th>Kepala Keluarga</th>
                    <th>Alamat</th>
                    <th style="text-align: center;">Jumlah Anggota</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr>
                        <td colspan="5" class="wp-desa-empty-state">
                            <span class="dashicons dashicons-update wp-desa-spinner"></span>
                            <div class="wp-desa-mt-8">Memuat data...</div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && kkList.length === 0">
                    <tr>
                        <td colspan="5" class="wp-desa-empty-state">
                            <div class="wp-desa-empty-icon">📋</div>
                            <div>Belum ada data Kartu Keluarga.</div>
                            <p style="color: #94a3b8; font-size: 0.9em; margin-top: 5px;">Data KK akan muncul otomatis saat penduduk diisi dengan No. KK yang sama.</p>
                        </td>
                    </tr>
                </template>
                <template x-for="kk in kkList" :key="kk.no_kk">
                    <tr>
                        <td class="wp-desa-mono" x-text="kk.no_kk"></td>
                        <td>
                            <div style="font-weight: 600; color: var(--ink);" x-text="kk.nama_kepala"></div>
                        </td>
                        <td x-text="kk.alamat || '-'"></td>
                        <td style="text-align: center;">
                            <span class="wp-desa-badge wp-desa-badge-default" x-text="kk.anggota + ' orang'"></span>
                        </td>
                        <td style="text-align: right;">
                            <button @click="viewAnggota(kk.no_kk)" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                <span class="dashicons dashicons-visibility"></span> Lihat Anggota
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="wp-desa-pagination" x-show="!loading && kkList.length > 0">
            <div class="wp-desa-pagination-info">
                Menampilkan <span x-text="(pagination.currentPage - 1) * pagination.perPage + 1"></span>
                sampai <span x-text="Math.min(pagination.currentPage * pagination.perPage, pagination.totalItems)"></span>
                dari <span x-text="pagination.totalItems"></span> KK
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

    <!-- Modal Anggota KK -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none;"
        x-transition.opacity>
        <div class="wp-desa-modal-content" @click.outside="closeModal" style="max-width: 900px;">
            <div class="wp-desa-modal-header">
                <h2 class="wp-desa-modal-title">
                    Anggota KK: <span class="wp-desa-mono" x-text="selectedKK"></span>
                </h2>
                <button type="button" @click="closeModal" class="wp-desa-icon-btn">
                    <span class="dashicons dashicons-no-alt wp-desa-icon-md"></span>
                </button>
            </div>
            <div class="wp-desa-modal-body">
                <template x-if="anggotaLoading">
                    <div style="text-align: center; padding: 40px;">
                        <span class="dashicons dashicons-update wp-desa-spinner"></span>
                        <div class="wp-desa-mt-8">Memuat anggota...</div>
                    </div>
                </template>
                <template x-if="!anggotaLoading">
                    <table class="wp-desa-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Tempat/Tgl Lahir</th>
                                <th>Status</th>
                                <th>Pekerjaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(a, index) in anggotaList" :key="a.id">
                                <tr>
                                    <td class="wp-desa-mono" x-text="a.nik"></td>
                                    <td style="font-weight: 600;" x-text="a.nama_lengkap"></td>
                                    <td>
                                        <span class="wp-desa-badge" :class="a.jenis_kelamin === 'Laki-laki' ? 'wp-desa-badge-default' : 'wp-desa-badge-danger'" x-text="a.jenis_kelamin"></span>
                                    </td>
                                    <td>
                                        <div x-text="a.tempat_lahir"></div>
                                        <div class="wp-desa-row-subtitle" x-text="a.tanggal_lahir"></div>
                                    </td>
                                    <td x-text="a.status_perkawinan"></td>
                                    <td x-text="a.pekerjaan || '-'"></td>
                                </tr>
                            </template>
                            <template x-if="anggotaList.length === 0">
                                <tr>
                                    <td colspan="6" class="wp-desa-empty-state">Tidak ada anggota ditemukan.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
            </div>
            <div class="wp-desa-modal-footer">
                <button type="button" @click="closeModal" class="wp-desa-btn wp-desa-btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const wpDesaResidents = {
        apiUrl: '<?php echo esc_url_raw(rest_url('wp-desa/v1/residents')); ?>',
        nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
    };

    document.addEventListener('alpine:init', () => {
        Alpine.data('kkManager', () => ({
            kkList: [],
            loading: true,
            pagination: {
                currentPage: 1,
                perPage: 20,
                totalPages: 1,
                totalItems: 0
            },
            isModalOpen: false,
            selectedKK: '',
            anggotaList: [],
            anggotaLoading: false,

            init() {
                this.fetchKK();
            },

            fetchKK(page = 1) {
                this.loading = true;
                const url = new URL(wpDesaResidents.apiUrl + '/kk');
                url.searchParams.append('page', page);
                url.searchParams.append('per_page', this.pagination.perPage);

                fetch(url.toString(), {
                        headers: {
                            'X-WP-Nonce': wpDesaResidents.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.kkList = data.data || [];
                        if (data.meta) {
                            this.pagination.currentPage = data.meta.current_page;
                            this.pagination.totalPages = data.meta.total_pages;
                            this.pagination.totalItems = data.meta.total_items;
                        }
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
            },

            prevPage() {
                if (this.pagination.currentPage > 1) {
                    this.fetchKK(this.pagination.currentPage - 1);
                }
            },

            nextPage() {
                if (this.pagination.currentPage < this.pagination.totalPages) {
                    this.fetchKK(this.pagination.currentPage + 1);
                }
            },

            viewAnggota(no_kk) {
                this.selectedKK = no_kk;
                this.isModalOpen = true;
                this.anggotaLoading = true;
                this.anggotaList = [];

                fetch(wpDesaResidents.apiUrl + '/kk/' + no_kk, {
                        headers: {
                            'X-WP-Nonce': wpDesaResidents.nonce
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.anggotaList = Array.isArray(data) ? data : [];
                        this.anggotaLoading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.anggotaLoading = false;
                    });
            },

            closeModal() {
                this.isModalOpen = false;
                this.selectedKK = '';
                this.anggotaList = [];
            }
        }));
    });
</script>