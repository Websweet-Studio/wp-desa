/**
 * Main JS file for WP Desa
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('villageInfo', () => ({
        info: {},
        loading: true,

        init() {
            console.log('WP Desa Alpine Component Initialized');
            this.fetchInfo();
        },

        fetchInfo() {
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
    }))
})
