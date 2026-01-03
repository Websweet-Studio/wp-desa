<?php

namespace WpDesa\Frontend;

class Shortcode {
    public function register() {
        add_shortcode('wp_desa_layanan', [$this, 'render_layanan']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        // Enqueue Alpine.js for frontend
        wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);
        
        // Tailwind (Optional, but makes styling easier, or just inline styles)
        // Using inline styles to avoid dependency for now.
    }

    public function render_layanan() {
        ob_start();
        ?>
        <div id="wp-desa-layanan" x-data="layananSurat()" style="max-width: 800px; margin: 20px auto; font-family: sans-serif;">
            
            <!-- Tabs -->
            <div style="display: flex; border-bottom: 2px solid #eee; margin-bottom: 20px;">
                <button @click="tab = 'request'" :style="tab === 'request' ? 'border-bottom: 2px solid #0073aa; color: #0073aa; font-weight: bold;' : 'color: #555;'" style="background: none; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px;">Ajukan Surat</button>
                <button @click="tab = 'track'" :style="tab === 'track' ? 'border-bottom: 2px solid #0073aa; color: #0073aa; font-weight: bold;' : 'color: #555;'" style="background: none; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px;">Cek Status</button>
            </div>

            <!-- Request Form -->
            <div x-show="tab === 'request'">
                <h3 style="margin-top: 0;">Form Permohonan Surat</h3>
                
                <div x-show="message.content" :style="message.type === 'success' ? 'background: #d4edda; color: #155724; border-color: #c3e6cb;' : 'background: #f8d7da; color: #721c24; border-color: #f5c6cb;'" style="padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px;">
                    <span x-text="message.content"></span>
                    <template x-if="trackingCode">
                        <div>
                            <strong>Kode Tracking Anda: </strong>
                            <span x-text="trackingCode" style="font-size: 1.2em; font-weight: bold; font-family: monospace;"></span>
                            <p style="margin-bottom: 0; font-size: 0.9em;">Simpan kode ini untuk mengecek status surat.</p>
                        </div>
                    </template>
                </div>

                <form @submit.prevent="submitRequest">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">NIK</label>
                        <input type="text" x-model="form.nik" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <small style="color: #666;">Pastikan NIK sudah terdaftar di data desa.</small>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" x-model="form.name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">No. HP / WhatsApp</label>
                        <input type="text" x-model="form.phone" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Jenis Surat</label>
                        <select x-model="form.letter_type_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">-- Pilih Jenis Surat --</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id" x-text="type.name"></option>
                            </template>
                        </select>
                        <template x-if="selectedTypeDescription">
                            <p style="margin-top: 5px; color: #666; font-style: italic;" x-text="selectedTypeDescription"></p>
                        </template>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Detail Keperluan</label>
                        <textarea x-model="form.details" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Contoh: Untuk persyaratan melamar pekerjaan"></textarea>
                    </div>

                    <button type="submit" :disabled="submitting" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                        <span x-show="!submitting">Kirim Permohonan</span>
                        <span x-show="submitting">Mengirim...</span>
                    </button>
                </form>
            </div>

            <!-- Tracking Form -->
            <div x-show="tab === 'track'">
                <h3 style="margin-top: 0;">Cek Status Surat</h3>
                
                <form @submit.prevent="checkStatus" style="margin-bottom: 20px;">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" x-model="trackCode" placeholder="Masukkan Kode Tracking" required style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <button type="submit" :disabled="tracking" style="background: #0073aa; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">
                            <span x-show="!tracking">Cek</span>
                            <span x-show="tracking">...</span>
                        </button>
                    </div>
                </form>

                <div x-show="trackResult" style="border: 1px solid #eee; padding: 20px; border-radius: 4px; background: #fafafa;">
                    <h4 style="margin-top: 0;">Status Permohonan</h4>
                    <p><strong>Jenis Surat:</strong> <span x-text="trackResult.type_name"></span></p>
                    <p><strong>Status:</strong> 
                        <span x-text="trackResult.status" 
                              style="padding: 2px 6px; border-radius: 3px; font-weight: bold; text-transform: uppercase;"
                              :style="{
                                  'pending': 'background: #ddd; color: #555;',
                                  'processed': 'background: #fff3cd; color: #856404;',
                                  'completed': 'background: #d4edda; color: #155724;',
                                  'rejected': 'background: #f8d7da; color: #721c24;'
                              }[trackResult.status]">
                        </span>
                    </p>
                    <p><strong>Tanggal Diajukan:</strong> <span x-text="formatDate(trackResult.created_at)"></span></p>
                    <p><strong>Nama Pemohon:</strong> <span x-text="trackResult.name"></span></p>
                </div>
                
                <div x-show="trackError" style="color: red; margin-top: 10px;" x-text="trackError"></div>
            </div>

        </div>

        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('layananSurat', () => ({
                tab: 'request',
                types: [],
                form: {
                    nik: '',
                    name: '',
                    phone: '',
                    letter_type_id: '',
                    details: ''
                },
                message: { type: '', content: '' },
                trackingCode: null,
                submitting: false,
                
                trackCode: '',
                trackResult: null,
                trackError: null,
                tracking: false,

                init() {
                    this.fetchTypes();
                },

                fetchTypes() {
                    fetch('/wp-json/wp-desa/v1/letters/types')
                        .then(res => res.json())
                        .then(data => this.types = data);
                },

                get selectedTypeDescription() {
                    const type = this.types.find(t => t.id == this.form.letter_type_id);
                    return type ? type.description : '';
                },

                submitRequest() {
                    this.submitting = true;
                    this.message = { type: '', content: '' };
                    this.trackingCode = null;

                    fetch('/wp-json/wp-desa/v1/letters/request', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.submitting = false;
                        if (data.success) {
                            this.message = { type: 'success', content: data.message };
                            this.trackingCode = data.tracking_code;
                            this.form = { nik: '', name: '', phone: '', letter_type_id: '', details: '' }; // Reset
                        } else {
                            this.message = { type: 'error', content: data.message || 'Terjadi kesalahan.' };
                        }
                    })
                    .catch(err => {
                        this.submitting = false;
                        this.message = { type: 'error', content: 'Gagal menghubungi server.' };
                    });
                },

                checkStatus() {
                    this.tracking = true;
                    this.trackResult = null;
                    this.trackError = null;

                    fetch('/wp-json/wp-desa/v1/letters/track?code=' + this.trackCode)
                    .then(res => res.json())
                    .then(data => {
                        this.tracking = false;
                        if (data.id) {
                            this.trackResult = data;
                        } else {
                            this.trackError = data.message || 'Data tidak ditemukan.';
                        }
                    })
                    .catch(err => {
                        this.tracking = false;
                        this.trackError = 'Gagal menghubungi server.';
                    });
                },

                formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                }
            }));
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
