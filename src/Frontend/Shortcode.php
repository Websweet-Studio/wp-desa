<?php

namespace WpDesa\Frontend;

class Shortcode
{
    public function register()
    {
        add_shortcode('wp_desa_layanan', [$this, 'render_layanan']);
        add_shortcode('wp_desa_aduan', [$this, 'render_aduan']);
        add_shortcode('wp_desa_keuangan', [$this, 'render_keuangan']);
        add_shortcode('wp_desa_bantuan', [$this, 'render_bantuan']);
        add_shortcode('wp_desa_profil', [$this, 'render_profil']);
        add_shortcode('wp_desa_kepala_desa', [$this, 'render_kepala_desa']);
        add_shortcode('wp_desa_statistik', [$this, 'render_statistik']);
        add_shortcode('wp_desa_umkm', [$this, 'render_umkm']);
        add_shortcode('wp_desa_potensi', [$this, 'render_potensi']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function render_statistik()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_residents';

        // Cache results for 1 hour to reduce DB load
        $stats = get_transient('wp_desa_quick_stats');

        if (false === $stats) {
            // Check if table exists to prevent errors on fresh install
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                $stats = [
                    'total' => 0,
                    'male' => 0,
                    'female' => 0,
                    'families' => 0
                ];
            } else {
                $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
                $male = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE jenis_kelamin = 'Laki-laki'");
                $female = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE jenis_kelamin = 'Perempuan'");
                $families = (int) $wpdb->get_var("SELECT COUNT(DISTINCT no_kk) FROM $table WHERE no_kk != ''");

                $stats = [
                    'total' => $total,
                    'male' => $male,
                    'female' => $female,
                    'families' => $families
                ];

                set_transient('wp_desa_quick_stats', $stats, HOUR_IN_SECONDS);
            }
        }

        // Ensure stats are integers and exist
        $total_val = isset($stats['total']) ? (int) $stats['total'] : 0;
        $families_val = isset($stats['families']) ? (int) $stats['families'] : 0;
        $male_val = isset($stats['male']) ? (int) $stats['male'] : 0;
        $female_val = isset($stats['female']) ? (int) $stats['female'] : 0;

        ob_start();
?>
        <div class="wp-desa-wrapper">
            <style>
                .wp-desa-stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 25px;
                    margin: 20px 0;
                }

                .wp-desa-stat-card {
                    background: #fff;
                    border-radius: 12px;
                    padding: 25px 20px;
                    text-align: center;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                    transition: transform 0.2s, box-shadow 0.2s;
                    border: 1px solid #f1f5f9;
                }

                .wp-desa-stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                }

                .wp-desa-stat-icon {
                    width: 60px;
                    height: 60px;
                    margin: 0 auto 15px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .wp-desa-stat-icon .dashicons {
                    font-size: 30px;
                    width: 30px;
                    height: 30px;
                }

                .wp-desa-stat-number {
                    font-size: 2.5em;
                    font-weight: 700;
                    color: #1e293b;
                    line-height: 1;
                    margin-bottom: 5px;
                }

                .wp-desa-stat-label {
                    color: #64748b;
                    font-size: 0.95em;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
            </style>

            <div class="wp-desa-stats-grid">
                <!-- Total -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon" style="background: #eff6ff; color: #3b82f6;">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="wp-desa-stat-number"><?php echo number_format_i18n($total_val); ?></div>
                    <div class="wp-desa-stat-label">Total Penduduk</div>
                </div>

                <!-- KK -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon" style="background: #fffbeb; color: #f59e0b;">
                        <span class="dashicons dashicons-admin-home"></span>
                    </div>
                    <div class="wp-desa-stat-number"><?php echo number_format_i18n($families_val); ?></div>
                    <div class="wp-desa-stat-label">Kepala Keluarga</div>
                </div>

                <!-- Laki-laki -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon" style="background: #e0f2fe; color: #0ea5e9;">
                        <span class="dashicons dashicons-businessman"></span>
                    </div>
                    <div class="wp-desa-stat-number"><?php echo number_format_i18n($male_val); ?></div>
                    <div class="wp-desa-stat-label">Laki-laki</div>
                </div>

                <!-- Perempuan -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon" style="background: #fce7f3; color: #ec4899;">
                        <span class="dashicons dashicons-businesswoman"></span>
                    </div>
                    <div class="wp-desa-stat-number"><?php echo number_format_i18n($female_val); ?></div>
                    <div class="wp-desa-stat-label">Perempuan</div>
                </div>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_umkm($atts)
    {
        $atts = shortcode_atts([
            'limit' => 6,
            'cols' => 3
        ], $atts);

        $query = new \WP_Query([
            'post_type' => 'desa_umkm',
            'posts_per_page' => $atts['limit'],
            'status' => 'publish'
        ]);

        ob_start();
    ?>
        <div class="wp-desa-wrapper">
            <?php if ($query->have_posts()): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php while ($query->have_posts()): $query->the_post();
                        $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
                        $location = get_post_meta(get_the_ID(), '_desa_umkm_location', true);
                    ?>
                        <div class="wp-desa-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                            <div style="height: 180px; background: #f1f5f9; overflow: hidden;">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #cbd5e1;">
                                        <span class="dashicons dashicons-store" style="font-size: 48px; width: 48px; height: 48px;"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <h3 style="margin: 0 0 10px 0; font-size: 1.2em;">
                                    <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #1e293b;"><?php the_title(); ?></a>
                                </h3>
                                <div style="font-size: 0.9em; color: #64748b; margin-bottom: 15px; flex: 1;">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                </div>
                                <div style="border-top: 1px solid #f1f5f9; pt: 15px; margin-top: auto; display: flex; gap: 10px;">
                                    <?php if ($phone): ?>
                                        <a href="https://wa.me/<?php echo esc_attr($phone); ?>" target="_blank" class="button" style="background: #25D366; color: white; border: none; font-size: 0.85em; display: flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 4px; text-decoration: none;">
                                            <span class="dashicons dashicons-whatsapp"></span> WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666;">Belum ada data UMKM.</p>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_potensi($atts)
    {
        $atts = shortcode_atts([
            'limit' => 3
        ], $atts);

        $query = new \WP_Query([
            'post_type' => 'desa_potensi',
            'posts_per_page' => $atts['limit'],
            'status' => 'publish'
        ]);

        ob_start();
    ?>
        <div class="wp-desa-wrapper">
            <?php if ($query->have_posts()): ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <div class="wp-desa-card" style="padding: 20px; display: flex; gap: 20px; align-items: start; flex-wrap: wrap;">
                            <div style="width: 200px; height: 150px; background: #f1f5f9; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #cbd5e1;">
                                        <span class="dashicons dashicons-carrot" style="font-size: 48px; width: 48px; height: 48px;"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h3 style="margin: 0 0 10px 0;">
                                    <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #2271b1;"><?php the_title(); ?></a>
                                </h3>
                                <div style="color: #64748b; line-height: 1.6;">
                                    <?php echo wp_trim_words(get_the_excerpt(), 30); ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" style="display: inline-block; margin-top: 10px; font-weight: 500; color: #2563eb; text-decoration: none;">Baca Selengkapnya &rarr;</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666;">Belum ada data Potensi Desa.</p>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_profil()
    {
        $settings = get_option('wp_desa_settings');
        if (!$settings) return '';

        $logo = isset($settings['logo_kabupaten']) ? $settings['logo_kabupaten'] : '';
        $nama_desa = isset($settings['nama_desa']) ? $settings['nama_desa'] : 'Desa';
        $nama_kecamatan = isset($settings['nama_kecamatan']) ? $settings['nama_kecamatan'] : '';
        $nama_kabupaten = isset($settings['nama_kabupaten']) ? $settings['nama_kabupaten'] : '';
        $alamat = isset($settings['alamat_kantor']) ? $settings['alamat_kantor'] : '';
        $email = isset($settings['email_desa']) ? $settings['email_desa'] : '';
        $telepon = isset($settings['telepon_desa']) ? $settings['telepon_desa'] : '';

        ob_start();
    ?>
        <div class="wp-desa-wrapper">
            <div class="wp-desa-card" style="text-align: center; padding: 30px;">
                <?php if ($logo): ?>
                    <img src="<?php echo esc_url($logo); ?>" alt="Logo Kabupaten" style="max-width: 100px; height: auto; margin-bottom: 20px;">
                <?php endif; ?>

                <h2 style="margin: 0; color: #2271b1;"><?php echo esc_html('Desa ' . $nama_desa); ?></h2>
                <h4 style="margin: 5px 0 20px 0; color: #666;">
                    <?php echo esc_html('Kecamatan ' . $nama_kecamatan . ', ' . $nama_kabupaten); ?>
                </h4>

                <div style="display: flex; flex-direction: column; gap: 10px; align-items: center; max-width: 600px; margin: 0 auto;">
                    <?php if ($alamat): ?>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span class="dashicons dashicons-location-alt" style="color: #666;"></span>
                            <span><?php echo esc_html($alamat); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($email): ?>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span class="dashicons dashicons-email" style="color: #666;"></span>
                            <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #2271b1; text-decoration: none;"><?php echo esc_html($email); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php if ($telepon): ?>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span class="dashicons dashicons-phone" style="color: #666;"></span>
                            <a href="tel:<?php echo esc_attr($telepon); ?>" style="color: #2271b1; text-decoration: none;"><?php echo esc_html($telepon); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_kepala_desa()
    {
        $settings = get_option('wp_desa_settings');
        if (!$settings) return '';

        $nama_kades = isset($settings['kepala_desa']) ? $settings['kepala_desa'] : '';
        $nip_kades = isset($settings['nip_kepala_desa']) ? $settings['nip_kepala_desa'] : '';
        $foto_kades = isset($settings['foto_kepala_desa']) ? $settings['foto_kepala_desa'] : '';
        $nama_desa = isset($settings['nama_desa']) ? $settings['nama_desa'] : 'Desa';

        if (!$nama_kades) return '';

        ob_start();
    ?>
        <div class="wp-desa-wrapper">
            <div class="wp-desa-card" style="text-align: center; padding: 30px; max-width: 400px; margin: 0 auto;">
                <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px auto; border: 4px solid #f1f5f9;">
                    <?php if ($foto_kades): ?>
                        <img src="<?php echo esc_url($foto_kades); ?>" alt="Foto Kepala Desa" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                            <span class="dashicons dashicons-admin-users" style="font-size: 64px; width: 64px; height: 64px; color: #94a3b8;"></span>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 style="margin: 0; color: #1e293b;"><?php echo esc_html($nama_kades); ?></h3>
                <p style="margin: 5px 0; color: #2271b1; font-weight: 500;">Kepala Desa <?php echo esc_html($nama_desa); ?></p>

                <?php if ($nip_kades): ?>
                    <p style="margin-top: 10px; color: #64748b; font-size: 0.9em; background: #f8fafc; display: inline-block; padding: 4px 12px; border-radius: 12px;">
                        NIP: <?php echo esc_html($nip_kades); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_bantuan()
    {
        ob_start();
    ?>
        <div id="wp-desa-bantuan" class="wp-desa-wrapper" x-data="bantuanDesa()">
            <h2 class="wp-desa-title" style="text-align:center;">Program & Bantuan Sosial</h2>

            <!-- Program List -->
            <div style="margin-bottom: 30px;">
                <template x-for="p in programs" :key="p.id">
                    <div class="wp-desa-card" style="margin-bottom: 15px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h3 style="margin: 0; color: #2271b1;" x-text="p.name"></h3>
                                <p style="margin: 5px 0; color: #666;" x-text="p.description"></p>
                                <div style="margin-top: 10px; font-size: 0.9em; color: #555;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; margin-right: 10px;" x-text="p.origin"></span>
                                    <span x-text="'Tahun: ' + p.year"></span>
                                </div>
                            </div>
                            <div style="text-align: right; min-width: 150px;">
                                <div style="font-weight: bold; font-size: 1.2em; color: #059669;" x-text="formatCurrency(p.amount_per_recipient)"></div>
                                <div style="font-size: 0.9em; color: #666; margin-top: 5px;" x-text="'Kuota: ' + p.quota"></div>
                                <button @click="viewRecipients(p)" class="wp-desa-btn" style="margin-top: 10px; font-size: 0.9em;">
                                    <span x-text="activeProgramId === p.id ? 'Tutup' : 'Lihat Penerima'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Recipients List (Collapsible) -->
                        <div x-show="activeProgramId === p.id" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                            <h4 style="margin-top: 0;">Daftar Penerima</h4>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th style="text-align: left; padding: 8px;">Nama</th>
                                            <th style="text-align: left; padding: 8px;">Alamat</th>
                                            <th style="text-align: center; padding: 8px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="r in recipients" :key="r.id">
                                            <tr style="border-bottom: 1px solid #eee;">
                                                <td style="padding: 8px;" x-text="r.nama_lengkap"></td>
                                                <td style="padding: 8px;" x-text="r.alamat"></td>
                                                <td style="text-align: center; padding: 8px;">
                                                    <span :class="'status-badge status-' + r.status" x-text="formatStatus(r.status)"></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="recipients.length === 0">
                                            <tr>
                                                <td colspan="3" style="text-align: center; padding: 15px;">Belum ada data penerima.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="programs.length === 0">
                    <div style="text-align: center; padding: 30px; color: #666;">Belum ada program bantuan aktif.</div>
                </template>
            </div>
        </div>

        <script>
            function bantuanDesa() {
                return {
                    programs: [],
                    activeProgramId: null,
                    recipients: [],

                    init() {
                        this.fetchPrograms();
                    },

                    fetchPrograms() {
                        fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs')); ?>')
                            .then(res => res.json())
                            .then(data => this.programs = data);
                    },

                    viewRecipients(program) {
                        if (this.activeProgramId === program.id) {
                            this.activeProgramId = null;
                            return;
                        }
                        this.activeProgramId = program.id;
                        this.recipients = []; // Clear

                        fetch('<?php echo esc_url_raw(rest_url('wp-desa/v1/aid-programs/')); ?>' + program.id + '/recipients')
                            .then(res => res.json())
                            .then(data => this.recipients = data);
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(value);
                    },

                    formatStatus(status) {
                        const map = {
                            'pending': 'Menunggu',
                            'approved': 'Disetujui',
                            'rejected': 'Ditolak',
                            'distributed': 'Disalurkan'
                        };
                        return map[status] || status;
                    }
                }
            }
        </script>

        <style>
            .status-badge {
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 0.8em;
                font-weight: 500;
            }

            .status-pending {
                background: #fef3c7;
                color: #92400e;
            }

            .status-approved {
                background: #dbeafe;
                color: #1e40af;
            }

            .status-rejected {
                background: #fee2e2;
                color: #991b1b;
            }

            .status-distributed {
                background: #d1fae5;
                color: #065f46;
            }
        </style>
    <?php
        return ob_get_clean();
    }

    public function render_keuangan()
    {
        ob_start();
    ?>
        <div id="wp-desa-keuangan" class="wp-desa-wrapper" x-data="keuanganDesa()">
            <h2 class="wp-desa-title" style="text-align:center;">Transparansi Keuangan Desa</h2>

            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <select x-model="filterYear" @change="fetchSummary" class="wp-desa-select" style="width: auto;">
                    <template x-for="y in years" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
            </div>

            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="wp-desa-card" style="text-align: center;">
                    <h4 style="margin: 0; color: #555;">Pendapatan</h4>
                    <h3 style="margin: 10px 0; color: #2271b1; font-size: 1.5rem;" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></h3>
                    <small style="color: #777;">Anggaran: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></span></small>
                </div>
                <div class="wp-desa-card" style="text-align: center;">
                    <h4 style="margin: 0; color: #555;">Belanja</h4>
                    <h3 style="margin: 10px 0; color: #d63638; font-size: 1.5rem;" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></h3>
                    <small style="color: #777;">Anggaran: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></span></small>
                </div>
                <div class="wp-desa-card" style="text-align: center;">
                    <h4 style="margin: 0; color: #555;">Surplus/Defisit</h4>
                    <h3 style="margin: 10px 0; font-size: 1.5rem;" :style="{color: getSurplus() >= 0 ? '#00a32a' : '#d63638'}" x-text="formatCurrency(getSurplus())"></h3>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 30px;">
                <div class="wp-desa-card">
                    <h4 style="text-align: center; margin-bottom: 15px;">Sumber Pendapatan</h4>
                    <canvas id="publicIncomeChart"></canvas>
                </div>
                <div class="wp-desa-card">
                    <h4 style="text-align: center; margin-bottom: 15px;">Penggunaan Dana</h4>
                    <canvas id="publicExpenseChart"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="wp-desa-card">
                <h4 style="margin-top:0; margin-bottom: 15px;">Rincian Realisasi APBDes</h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee;">
                                <th style="text-align: left; padding: 10px;">Uraian</th>
                                <th style="text-align: right; padding: 10px;">Anggaran</th>
                                <th style="text-align: right; padding: 10px;">Realisasi</th>
                                <th style="text-align: right; padding: 10px;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in items" :key="item.id">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px;">
                                        <strong x-text="item.category"></strong><br>
                                        <small x-text="item.description"></small>
                                    </td>
                                    <td style="text-align: right; padding: 10px;" x-text="formatCurrency(item.budget_amount)"></td>
                                    <td style="text-align: right; padding: 10px;" x-text="formatCurrency(item.realization_amount)"></td>
                                    <td style="text-align: right; padding: 10px;">
                                        <span x-text="calculatePercentage(item.realization_amount, item.budget_amount) + '%'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            function keuanganDesa() {
                return {
                    filterYear: new Date().getFullYear(),
                    years: [],
                    summary: {
                        totals: [],
                        income_sources: [],
                        expense_sources: []
                    },
                    items: [],
                    incomeChart: null,
                    expenseChart: null,

                    init() {
                        const currentYear = new Date().getFullYear();
                        for (let i = currentYear; i >= currentYear - 5; i--) {
                            this.years.push(i);
                        }
                        this.fetchSummary();
                        this.fetchData();
                    },

                    fetchSummary() {
                        fetch('/wp-json/wp-desa/v1/finances/summary?year=' + this.filterYear)
                            .then(res => res.json())
                            .then(data => {
                                this.summary = data;
                                this.renderCharts();
                            });
                    },

                    fetchData() {
                        fetch('/wp-json/wp-desa/v1/finances?year=' + this.filterYear)
                            .then(res => res.json())
                            .then(data => {
                                this.items = data;
                            });
                    },

                    renderCharts() {
                        if (this.incomeChart) this.incomeChart.destroy();
                        if (this.expenseChart) this.expenseChart.destroy();

                        // Wait for Chart.js
                        if (typeof Chart === 'undefined') {
                            setTimeout(() => this.renderCharts(), 500);
                            return;
                        }

                        const incomeCtx = document.getElementById('publicIncomeChart');
                        if (incomeCtx && this.summary.income_sources.length > 0) {
                            this.incomeChart = new Chart(incomeCtx, {
                                type: 'pie',
                                data: {
                                    labels: this.summary.income_sources.map(i => i.category),
                                    datasets: [{
                                        data: this.summary.income_sources.map(i => i.total),
                                        backgroundColor: ['#4bc0c0', '#36a2eb', '#ffcd56', '#ff9f40', '#9966ff']
                                    }]
                                },
                                options: {
                                    responsive: true
                                }
                            });
                        }

                        const expenseCtx = document.getElementById('publicExpenseChart');
                        if (expenseCtx && this.summary.expense_sources.length > 0) {
                            this.expenseChart = new Chart(expenseCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: this.summary.expense_sources.map(i => i.category),
                                    datasets: [{
                                        data: this.summary.expense_sources.map(i => i.total),
                                        backgroundColor: ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0', '#36a2eb']
                                    }]
                                },
                                options: {
                                    responsive: true
                                }
                            });
                        }
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            maximumFractionDigits: 0
                        }).format(value);
                    },

                    getSurplus() {
                        const income = this.summary.totals.find(t => t.type === 'income')?.total_realization || 0;
                        const expense = this.summary.totals.find(t => t.type === 'expense')?.total_realization || 0;
                        return income - expense;
                    },

                    calculatePercentage(realization, budget) {
                        if (!budget || budget == 0) return 0;
                        return Math.round((realization / budget) * 100);
                    }
                }
            }
        </script>
    <?php
        return ob_get_clean();
    }

    public function render_aduan()
    {
        ob_start();
    ?>
        <div id="wp-desa-aduan" class="wp-desa-wrapper" x-data="aduanWarga()">
            <div class="wp-desa-tabs">
                <button @click="tab = 'form'" :class="{'active': tab === 'form'}" class="wp-desa-tab-btn">Buat Laporan</button>
                <button @click="tab = 'track'" :class="{'active': tab === 'track'}" class="wp-desa-tab-btn">Cek Status Laporan</button>
            </div>

            <div class="wp-desa-content">
                <!-- Form Aduan -->
                <div x-show="tab === 'form'">
                    <div x-show="message.content"
                        :class="message.type === 'success' ? 'wp-desa-alert wp-desa-alert-success' : 'wp-desa-alert wp-desa-alert-error'">
                        <span x-text="message.content"></span>
                        <template x-if="trackingCode">
                            <div style="margin-top: 10px;">
                                <div style="font-size: 0.9em; margin-bottom: 5px;">Kode Tracking Anda:</div>
                                <div class="wp-desa-tracking-code" x-text="trackingCode"></div>
                                <p class="wp-desa-helper">Simpan kode ini untuk mengecek status laporan.</p>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="submitComplaint" enctype="multipart/form-data">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Nama Pelapor (Opsional)</label>
                            <input type="text" x-model="form.reporter_name" class="wp-desa-input" placeholder="Nama Anda (Boleh dikosongkan)">
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Kontak (HP/Email)</label>
                            <input type="text" x-model="form.reporter_contact" class="wp-desa-input" placeholder="Untuk konfirmasi status">
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Kategori Masalah</label>
                            <select x-model="form.category" required class="wp-desa-select">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Infrastruktur">Infrastruktur (Jalan, Jembatan, dll)</option>
                                <option value="Pelayanan Publik">Pelayanan Publik</option>
                                <option value="Keamanan">Keamanan & Ketertiban</option>
                                <option value="Kebersihan">Kebersihan & Lingkungan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Judul Laporan</label>
                            <input type="text" x-model="form.subject" required class="wp-desa-input" placeholder="Ringkasan masalah">
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Isi Laporan</label>
                            <textarea x-model="form.description" required rows="5" class="wp-desa-textarea" placeholder="Jelaskan detail masalah, lokasi, dll"></textarea>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Upload Foto Bukti</label>
                            <input type="file" @change="handleFileUpload" accept="image/*" class="wp-desa-input">
                            <small class="wp-desa-helper">Format: JPG, PNG. Maks 2MB.</small>
                        </div>

                        <button type="submit" :disabled="submitting" class="wp-desa-btn wp-desa-btn-primary">
                            <span x-show="!submitting">Kirim Laporan</span>
                            <span x-show="submitting">Mengirim...</span>
                        </button>
                    </form>
                </div>

                <!-- Tracking Form -->
                <div x-show="tab === 'track'">
                    <form @submit.prevent="checkStatus" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; gap: 0.75rem;">
                            <input type="text" x-model="trackCode" placeholder="Masukkan Kode Tracking (Contoh: ADU-XXXXXX)" required class="wp-desa-input" style="flex: 1;">
                            <button type="submit" :disabled="tracking" class="wp-desa-btn wp-desa-btn-primary" style="width: auto;">
                                <span x-show="!tracking">Cek</span>
                                <span x-show="tracking">...</span>
                            </button>
                        </div>
                    </form>

                    <div x-show="trackResult" class="wp-desa-card">
                        <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">Status Laporan</h4>

                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Judul</span>
                            <span class="wp-desa-card-value" x-text="trackResult.subject"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Kategori</span>
                            <span class="wp-desa-card-value" x-text="trackResult.category"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Tanggal</span>
                            <span class="wp-desa-card-value" x-text="formatDate(trackResult.created_at)"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Status</span>
                            <span :class="'wp-desa-badge wp-desa-badge-' + trackResult.status" x-text="formatStatus(trackResult.status)"></span>
                        </div>

                        <template x-if="trackResult.response">
                            <div style="margin-top: 1rem; background: #f9fafb; padding: 1rem; border-radius: 0.5rem;">
                                <strong style="display: block; margin-bottom: 0.5rem; color: #374151;">Tanggapan Admin:</strong>
                                <p style="margin: 0; color: #4b5563;" x-text="trackResult.response"></p>
                            </div>
                        </template>
                    </div>

                    <div x-show="trackError" class="wp-desa-alert wp-desa-alert-error" x-text="trackError"></div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('aduanWarga', () => ({
                    tab: 'form',
                    form: {
                        reporter_name: '',
                        reporter_contact: '',
                        category: '',
                        subject: '',
                        description: '',
                        photo: null
                    },
                    message: {
                        type: '',
                        content: ''
                    },
                    trackingCode: null,
                    submitting: false,

                    trackCode: '',
                    trackResult: null,
                    trackError: null,
                    tracking: false,

                    handleFileUpload(event) {
                        this.form.photo = event.target.files[0];
                    },

                    submitComplaint() {
                        this.submitting = true;
                        this.message = {
                            type: '',
                            content: ''
                        };
                        this.trackingCode = null;

                        const formData = new FormData();
                        formData.append('reporter_name', this.form.reporter_name);
                        formData.append('reporter_contact', this.form.reporter_contact);
                        formData.append('category', this.form.category);
                        formData.append('subject', this.form.subject);
                        formData.append('description', this.form.description);
                        if (this.form.photo) {
                            formData.append('photo', this.form.photo);
                        }

                        fetch('/wp-json/wp-desa/v1/complaints/submit', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.submitting = false;
                                if (data.success) {
                                    this.message = {
                                        type: 'success',
                                        content: data.message
                                    };
                                    this.trackingCode = data.tracking_code;
                                    this.form = {
                                        reporter_name: '',
                                        reporter_contact: '',
                                        category: '',
                                        subject: '',
                                        description: '',
                                        photo: null
                                    }; // Reset
                                    // Reset file input manually if needed
                                } else {
                                    this.message = {
                                        type: 'error',
                                        content: data.message || 'Terjadi kesalahan.'
                                    };
                                }
                            })
                            .catch(err => {
                                this.submitting = false;
                                this.message = {
                                    type: 'error',
                                    content: 'Gagal menghubungi server.'
                                };
                            });
                    },

                    checkStatus() {
                        this.tracking = true;
                        this.trackResult = null;
                        this.trackError = null;

                        fetch('/wp-json/wp-desa/v1/complaints/track?code=' + this.trackCode)
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
                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    },

                    formatStatus(status) {
                        const map = {
                            'pending': 'Pending',
                            'in_progress': 'Diproses',
                            'resolved': 'Selesai',
                            'rejected': 'Ditolak'
                        };
                        return map[status] || status;
                    }
                }));
            });
        </script>
    <?php
        return ob_get_clean();
    }

    public function enqueue_scripts()
    {
        // Enqueue Alpine.js for frontend
        wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

        // Enqueue Frontend Styles
        wp_enqueue_style('wp-desa-frontend', WP_DESA_URL . 'assets/css/frontend/style.css', [], '1.0.0');

        // Enqueue Chart.js for Finances (conditionally ideally, but globally for now to ensure it works)
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.0.0', true);
    }

    public function render_layanan()
    {
        ob_start();
    ?>
        <div id="wp-desa-layanan" class="wp-desa-wrapper" x-data="layananSurat()">

            <!-- Tabs -->
            <div class="wp-desa-tabs">
                <button @click="tab = 'request'" :class="{'active': tab === 'request'}" class="wp-desa-tab-btn">Ajukan Surat</button>
                <button @click="tab = 'track'" :class="{'active': tab === 'track'}" class="wp-desa-tab-btn">Cek Status</button>
            </div>

            <div class="wp-desa-content">
                <!-- Request Form -->
                <div x-show="tab === 'request'">
                    <h3 class="wp-desa-title">Form Permohonan Surat</h3>

                    <div x-show="message.content"
                        :class="message.type === 'success' ? 'wp-desa-alert wp-desa-alert-success' : 'wp-desa-alert wp-desa-alert-error'">
                        <span x-text="message.content"></span>
                        <template x-if="trackingCode">
                            <div style="margin-top: 10px;">
                                <div style="font-size: 0.9em; margin-bottom: 5px;">Kode Tracking Anda:</div>
                                <div class="wp-desa-tracking-code" x-text="trackingCode"></div>
                                <p class="wp-desa-helper">Simpan kode ini untuk mengecek status surat.</p>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="submitRequest">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">NIK</label>
                            <input type="text" x-model="form.nik" required class="wp-desa-input" placeholder="Masukkan 16 digit NIK">
                            <small class="wp-desa-helper">Pastikan NIK sudah terdaftar di data desa.</small>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Nama Lengkap (Sesuai KTP)</label>
                            <input type="text" x-model="form.name" required class="wp-desa-input" placeholder="Nama Lengkap">
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">No. HP / WhatsApp</label>
                            <input type="text" x-model="form.phone" required class="wp-desa-input" placeholder="Contoh: 08123456789">
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Jenis Surat</label>
                            <select x-model="form.letter_type_id" required class="wp-desa-select">
                                <option value="">-- Pilih Jenis Surat --</option>
                                <template x-for="type in types" :key="type.id">
                                    <option :value="type.id" x-text="type.name"></option>
                                </template>
                            </select>
                            <template x-if="selectedTypeDescription">
                                <p class="wp-desa-helper" x-text="selectedTypeDescription"></p>
                            </template>
                        </div>

                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label">Detail Keperluan</label>
                            <textarea x-model="form.details" rows="4" class="wp-desa-textarea" placeholder="Contoh: Untuk persyaratan melamar pekerjaan"></textarea>
                        </div>

                        <button type="submit" :disabled="submitting" class="wp-desa-btn wp-desa-btn-primary">
                            <span x-show="!submitting">Kirim Permohonan</span>
                            <span x-show="submitting">Mengirim...</span>
                        </button>
                    </form>
                </div>

                <!-- Tracking Form -->
                <div x-show="tab === 'track'">
                    <h3 class="wp-desa-title">Cek Status Surat</h3>

                    <form @submit.prevent="checkStatus" style="margin-bottom: 1.5rem;">
                        <div style="display: flex; gap: 0.75rem;">
                            <input type="text" x-model="trackCode" placeholder="Masukkan Kode Tracking" required class="wp-desa-input" style="flex: 1;">
                            <button type="submit" :disabled="tracking" class="wp-desa-btn wp-desa-btn-primary" style="width: auto;">
                                <span x-show="!tracking">Cek</span>
                                <span x-show="tracking">...</span>
                            </button>
                        </div>
                    </form>

                    <div x-show="trackResult" class="wp-desa-card">
                        <h4 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">Status Permohonan</h4>

                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Jenis Surat</span>
                            <span class="wp-desa-card-value" x-text="trackResult.type_name"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Pemohon</span>
                            <span class="wp-desa-card-value" x-text="trackResult.name"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Tanggal</span>
                            <span class="wp-desa-card-value" x-text="formatDate(trackResult.created_at)"></span>
                        </div>
                        <div class="wp-desa-card-row">
                            <span class="wp-desa-card-label">Status</span>
                            <span :class="'wp-desa-badge wp-desa-badge-' + trackResult.status" x-text="trackResult.status"></span>
                        </div>
                    </div>

                    <div x-show="trackError" class="wp-desa-alert wp-desa-alert-error" x-text="trackError"></div>
                </div>
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
                    message: {
                        type: '',
                        content: ''
                    },
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
                        this.message = {
                            type: '',
                            content: ''
                        };
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
                                    this.message = {
                                        type: 'success',
                                        content: data.message
                                    };
                                    this.trackingCode = data.tracking_code;
                                    this.form = {
                                        nik: '',
                                        name: '',
                                        phone: '',
                                        letter_type_id: '',
                                        details: ''
                                    }; // Reset
                                } else {
                                    this.message = {
                                        type: 'error',
                                        content: data.message || 'Terjadi kesalahan.'
                                    };
                                }
                            })
                            .catch(err => {
                                this.submitting = false;
                                this.message = {
                                    type: 'error',
                                    content: 'Gagal menghubungi server.'
                                };
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
                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    }
                }));
            });
        </script>
<?php
        return ob_get_clean();
    }
}
