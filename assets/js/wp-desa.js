/**
 * Main JS file for WP Desa
 */

// Debug log to ensure file is loaded
console.log('WP Desa JS Loaded');

function initAlpineComponents() {
    console.log('Initializing Alpine Components');

    // Frontend Component
    Alpine.data('villageInfo', () => ({
        info: {},
        loading: true,

        init() {
            console.log('villageInfo initialized');
            this.fetchInfo();
        },

        fetchInfo() {
            if (typeof wpDesaSettings === 'undefined') {
                console.error('wpDesaSettings not defined');
                return;
            }
            fetch(wpDesaSettings.root + 'wp-desa/v1/info', {
                headers: {
                    'X-WP-Nonce': wpDesaSettings.nonce
                }
            })
            .then(response => response.json())
            .then(data => {
                this.info = data;
                this.loading = false;
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                this.loading = false;
            });
        }
    }));

    // Admin Residents Manager Component
    Alpine.data('residentsManager', () => ({
        residents: [],
        loading: false,
        showModal: false,
        modalMode: 'add', // 'add' or 'edit'
        form: {
            id: null,
            nik: '',
            nama_lengkap: '',
            jenis_kelamin: 'L',
            pekerjaan: ''
        },

        init() {
            console.log('residentsManager initialized');
            if (typeof wpDesaSettings === 'undefined') {
                console.error('wpDesaSettings is missing!');
                return;
            }
            this.fetchResidents();
        },

        fetchResidents() {
            console.log('Fetching residents...');
            this.loading = true;
            fetch(wpDesaSettings.root + 'wp-desa/v1/residents', {
                headers: { 'X-WP-Nonce': wpDesaSettings.nonce }
            })
            .then(res => {
                if (!res.ok) throw new Error('API Error: ' + res.status);
                return res.json();
            })
            .then(data => {
                console.log('Residents data:', data);
                this.residents = data;
                this.loading = false;
            })
            .catch(err => {
                console.error('Fetch residents failed:', err);
                this.loading = false;
            });
        },

        openModal(mode, data = null) {
            console.log('Opening modal:', mode, data);
            this.modalMode = mode;
            if (mode === 'edit' && data) {
                this.form = { ...data };
            } else {
                this.resetForm();
            }
            this.showModal = true;
            console.log('showModal is now:', this.showModal);
        },

        resetForm() {
            this.form = {
                id: null,
                nik: '',
                nama_lengkap: '',
                jenis_kelamin: 'L',
                pekerjaan: ''
            };
        },

        editResident(resident) {
            this.openModal('edit', resident);
        },

        saveResident() {
            console.log('Saving resident...');
            const url = this.modalMode === 'add' 
                ? wpDesaSettings.root + 'wp-desa/v1/residents'
                : wpDesaSettings.root + 'wp-desa/v1/residents/' + this.form.id;
            
            const method = this.modalMode === 'add' ? 'POST' : 'PUT';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpDesaSettings.nonce
                },
                body: JSON.stringify(this.form)
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed');
                return res.json();
            })
            .then(() => {
                this.showModal = false;
                this.fetchResidents();
                this.resetForm();
            })
            .catch(err => {
                alert('Terjadi kesalahan saat menyimpan data.');
                console.error(err);
            });
        },

        deleteResident(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

            fetch(wpDesaSettings.root + 'wp-desa/v1/residents/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': wpDesaSettings.nonce }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed');
                return res.json();
            })
            .then(() => {
                this.fetchResidents();
            })
            .catch(err => {
                alert('Gagal menghapus data.');
                console.error(err);
            });
        }
    }));
}

// Handle both cases: Alpine already loaded or not
if (window.Alpine) {
    initAlpineComponents();
} else {
    document.addEventListener('alpine:init', initAlpineComponents);
}
