<div class="wrap" x-data="lettersManager()">
    <h1 class="wp-heading-inline">Layanan Surat Online</h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <ul class="subsubsub">
        <li class="all"><a href="#" @click.prevent="filterStatus('')" :class="{'current': currentStatus === ''}">Semua <span class="count" x-text="'(' + letters.length + ')'"></span></a> |</li>
        <li class="pending"><a href="#" @click.prevent="filterStatus('pending')" :class="{'current': currentStatus === 'pending'}">Pending</a> |</li>
        <li class="processed"><a href="#" @click.prevent="filterStatus('processed')" :class="{'current': currentStatus === 'processed'}">Diproses</a> |</li>
        <li class="completed"><a href="#" @click.prevent="filterStatus('completed')" :class="{'current': currentStatus === 'completed'}">Selesai</a> |</li>
        <li class="rejected"><a href="#" @click.prevent="filterStatus('rejected')" :class="{'current': currentStatus === 'rejected'}">Ditolak</a></li>
    </ul>

    <!-- Table -->
    <table class="wp-list-table widefat fixed striped table-view-list posts" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Tracking</th>
                <th>Jenis Surat</th>
                <th>Pemohon</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <template x-if="loading">
                <tr>
                    <td colspan="6">Memuat data...</td>
                </tr>
            </template>
            <template x-if="!loading && filteredLetters.length === 0">
                <tr>
                    <td colspan="6">Tidak ada permohonan surat.</td>
                </tr>
            </template>
            <template x-for="letter in filteredLetters" :key="letter.id">
                <tr>
                    <td x-text="formatDate(letter.created_at)"></td>
                    <td>
                        <strong x-text="letter.tracking_code"></strong>
                    </td>
                    <td x-text="letter.type_name"></td>
                    <td>
                        <div x-text="letter.name"></div>
                        <small x-text="'NIK: ' + letter.nik"></small><br>
                        <small x-text="'HP: ' + letter.phone"></small>
                    </td>
                    <td>
                        <span :class="'badge badge-' + letter.status" x-text="letter.status"></span>
                    </td>
                    <td>
                        <button @click="openDetail(letter)" class="button button-small">Lihat Detail</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- Detail Modal -->
    <div x-show="isModalOpen"
        class="wp-desa-modal-overlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center;"
        :style="isModalOpen ? 'display: flex' : 'display: none'">
        
        <div class="wp-desa-modal" style="background: white; padding: 20px; width: 500px; max-width: 90%; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;">Detail Permohonan</h2>
            <template x-if="selectedLetter">
                <div>
                    <p><strong>Jenis:</strong> <span x-text="selectedLetter.type_name"></span></p>
                    <p><strong>Pemohon:</strong> <span x-text="selectedLetter.name"></span> (<span x-text="selectedLetter.nik"></span>)</p>
                    <p><strong>Keperluan/Detail:</strong></p>
                    <div style="background: #f0f0f1; padding: 10px; border-radius: 4px; margin-bottom: 15px;" x-text="selectedLetter.details || '-'"></div>
                    
                    <div style="margin-bottom: 15px;">
                        <label><strong>Update Status:</strong></label>
                        <select x-model="selectedLetter.status" @change="updateStatus(selectedLetter.id, $event.target.value)" style="width: 100%;">
                            <option value="pending">Pending</option>
                            <option value="processed">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>
            </template>
            <div style="text-align: right; margin-top: 20px;">
                <button @click="isModalOpen = false" class="button">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-pending { background: #f0f0f1; color: #646970; }
    .badge-processed { background: #fff8e5; color: #996800; }
    .badge-completed { background: #e7f5ea; color: #00a32a; }
    .badge-rejected { background: #fbeaea; color: #d63638; }
</style>

<script>
function lettersManager() {
    return {
        letters: [],
        loading: true,
        currentStatus: '',
        isModalOpen: false,
        selectedLetter: null,
        
        init() {
            this.fetchLetters();
        },
        
        fetchLetters() {
            this.loading = true;
            fetch('/wp-json/wp-desa/v1/letters', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.letters = data;
                this.loading = false;
            });
        },
        
        get filteredLetters() {
            if (this.currentStatus === '') return this.letters;
            return this.letters.filter(l => l.status === this.currentStatus);
        },
        
        filterStatus(status) {
            this.currentStatus = status;
        },
        
        openDetail(letter) {
            this.selectedLetter = {...letter}; // Copy object
            this.isModalOpen = true;
        },
        
        updateStatus(id, newStatus) {
            fetch('/wp-json/wp-desa/v1/letters/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update local data
                    const index = this.letters.findIndex(l => l.id === id);
                    if (index !== -1) {
                        this.letters[index].status = newStatus;
                    }
                    alert('Status berhasil diperbarui');
                } else {
                    alert('Gagal update status');
                }
            });
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
