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

                // Check if no_kk column exists before querying to avoid errors on older DB versions
                $has_kk = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'no_kk'");
                if (!empty($has_kk)) {
                    $families = (int) $wpdb->get_var("SELECT COUNT(DISTINCT no_kk) FROM $table WHERE no_kk != ''");
                } else {
                    $families = 0;
                }

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

        $chart_id = 'wpDesaStatChart_' . uniqid();

        ob_start();
?>
        <div class="wp-desa-wrapper">
            <!-- CSS moved to assets/css/frontend/style.css -->

            <!-- Chart Section -->
            <div class="wp-desa-chart-container">
                <h3 style="text-align: center; margin-top: 0; color: #1e293b; font-size: 1.1em; margin-bottom: 15px;">Komposisi Penduduk</h3>
                <div style="position: relative; height: 250px;">
                    <canvas id="<?php echo esc_attr($chart_id); ?>"></canvas>
                </div>
            </div>

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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('<?php echo esc_js($chart_id); ?>');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Laki-laki', 'Perempuan'],
                            datasets: [{
                                data: [<?php echo (int)$male_val; ?>, <?php echo (int)$female_val; ?>],
                                backgroundColor: [
                                    '#0ea5e9', // Blue for Male
                                    '#ec4899' // Pink for Female
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
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
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                    <?php while ($query->have_posts()): $query->the_post();
                        $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
                        $location = get_post_meta(get_the_ID(), '_desa_umkm_location', true);
                        $categories = get_the_terms(get_the_ID(), 'desa_umkm_cat');
                        $cat_name = !empty($categories) ? $categories[0]->name : 'UMKM';
                    ?>
                        <div class="wp-desa-stat-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; text-align: left; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #f1f5f9; background: white; border-radius: 12px;">
                            <div style="height: 200px; background: #f8fafc; overflow: hidden; position: relative;">
                                <div style="position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.9); padding: 4px 10px; border-radius: 20px; font-size: 0.75em; font-weight: 600; color: #475569; z-index: 2; backdrop-filter: blur(4px);">
                                    <?php echo esc_html($cat_name); ?>
                                </div>
                                <?php if (has_post_thumbnail()): ?>
                                    <a href="<?php the_permalink(); ?>" style="display: block; width: 100%; height: 100%;">
                                        <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;']); ?>
                                    </a>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #cbd5e1; background: #f1f5f9;">
                                        <span class="dashicons dashicons-store" style="font-size: 64px; width: 64px; height: 64px; opacity: 0.5;"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <h3 style="margin: 0 0 10px 0; font-size: 1.15em; line-height: 1.4;">
                                    <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #1e293b; font-weight: 700; transition: color 0.2s;"><?php the_title(); ?></a>
                                </h3>
                                <div style="font-size: 0.9em; color: #64748b; margin-bottom: 20px; flex: 1; line-height: 1.6;">
                                    <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                </div>

                                <div style="border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: auto; display: flex; justify-content: space-between; align-items: center;">
                                    <a href="<?php the_permalink(); ?>" style="font-size: 0.9em; font-weight: 500; color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                        Detail <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
                                    </a>

                                    <?php if ($phone):
                                        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                                        if (substr($clean_phone, 0, 1) == '0') {
                                            $clean_phone = '62' . substr($clean_phone, 1);
                                        }
                                    ?>
                                        <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" target="_blank" style="background: #25D366; color: white; border: none; font-size: 0.85em; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-weight: 500; transition: background 0.2s;">
                                            <span class="dashicons dashicons-whatsapp" style="font-size: 16px; width: 16px; height: 16px;"></span> Chat
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; color: #94a3b8;">
                    <span class="dashicons dashicons-store" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></span>
                    <p style="margin: 0; font-size: 1.1em;">Belum ada data UMKM yang ditampilkan.</p>
                </div>
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
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <div class="wp-desa-stat-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; text-align: left; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #f1f5f9; background: white; border-radius: 12px;">
                            <div style="height: 200px; background: #f8fafc; overflow: hidden; position: relative;">
                                <?php if (has_post_thumbnail()): ?>
                                    <a href="<?php the_permalink(); ?>" style="display: block; width: 100%; height: 100%;">
                                        <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;']); ?>
                                    </a>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #cbd5e1; background: #f1f5f9;">
                                        <span class="dashicons dashicons-carrot" style="font-size: 64px; width: 64px; height: 64px; opacity: 0.5;"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                <h3 style="margin: 0 0 10px 0; font-size: 1.15em; line-height: 1.4;">
                                    <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #1e293b; font-weight: 700; transition: color 0.2s;"><?php the_title(); ?></a>
                                </h3>
                                <div style="font-size: 0.9em; color: #64748b; margin-bottom: 20px; flex: 1; line-height: 1.6;">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" style="font-size: 0.9em; font-weight: 500; color: #2563eb; text-decoration: none; display: flex; align-items: center; gap: 4px; margin-top: auto;">
                                    Baca Selengkapnya <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; color: #94a3b8;">
                    <span class="dashicons dashicons-carrot" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></span>
                    <p style="margin: 0; font-size: 1.1em;">Belum ada data Potensi Desa.</p>
                </div>
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
            <div class="wp-desa-stat-card" style="text-align: center; padding: 40px 30px; position: relative; overflow: hidden;">
                <!-- Decorative Background -->
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #2563eb, #06b6d4);"></div>

                <?php if ($logo): ?>
                    <img src="<?php echo esc_url($logo); ?>" alt="Logo Kabupaten" style="max-width: 100px; height: auto; margin-bottom: 25px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                <?php endif; ?>

                <h2 style="margin: 0 0 5px 0; color: #1e293b; font-weight: 800; font-size: 1.8em;"><?php echo esc_html('Desa ' . $nama_desa); ?></h2>
                <h4 style="margin: 0 0 30px 0; color: #64748b; font-weight: 500; font-size: 1.1em;">
                    <?php echo esc_html('Kecamatan ' . $nama_kecamatan . ', ' . $nama_kabupaten); ?>
                </h4>

                <div style="display: inline-flex; flex-direction: column; gap: 15px; text-align: left; background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; max-width: 500px;">
                    <?php if ($alamat): ?>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 32px; height: 32px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
                                <span class="dashicons dashicons-location-alt" style="font-size: 18px; width: 18px; height: 18px;"></span>
                            </div>
                            <div>
                                <div style="font-size: 0.85em; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Alamat Kantor</div>
                                <div style="color: #334155; line-height: 1.5;"><?php echo esc_html($alamat); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($email): ?>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 32px; height: 32px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
                                <span class="dashicons dashicons-email" style="font-size: 18px; width: 18px; height: 18px;"></span>
                            </div>
                            <div>
                                <div style="font-size: 0.85em; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Email</div>
                                <a href="mailto:<?php echo esc_attr($email); ?>" style="color: #2563eb; text-decoration: none; font-weight: 500;"><?php echo esc_html($email); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($telepon): ?>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 32px; height: 32px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0;">
                                <span class="dashicons dashicons-phone" style="font-size: 18px; width: 18px; height: 18px;"></span>
                            </div>
                            <div>
                                <div style="font-size: 0.85em; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Telepon</div>
                                <a href="tel:<?php echo esc_attr($telepon); ?>" style="color: #2563eb; text-decoration: none; font-weight: 500;"><?php echo esc_html($telepon); ?></a>
                            </div>
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
            <div class="wp-desa-stat-card" style="text-align: center; padding: 40px 30px; max-width: 400px; margin: 0 auto; position: relative;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 80px; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border-radius: 12px 12px 0 0;"></div>

                <div style="width: 160px; height: 160px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px auto; border: 5px solid #fff; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); position: relative; z-index: 1;">
                    <?php if ($foto_kades): ?>
                        <img src="<?php echo esc_url($foto_kades); ?>" alt="Foto Kepala Desa" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: #cbd5e1; display: flex; align-items: center; justify-content: center;">
                            <span class="dashicons dashicons-admin-users" style="font-size: 80px; width: 80px; height: 80px; color: #94a3b8;"></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="position: relative; z-index: 1;">
                    <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.5em; font-weight: 700;"><?php echo esc_html($nama_kades); ?></h3>
                    <p style="margin: 0 0 15px 0; color: #2563eb; font-weight: 600;">Kepala Desa <?php echo esc_html($nama_desa); ?></p>

                    <?php if ($nip_kades): ?>
                        <div style="display: inline-block; padding: 6px 16px; border-radius: 20px; background: #f1f5f9; color: #64748b; font-size: 0.9em; font-weight: 500;">
                            NIP. <?php echo esc_html($nip_kades); ?>
                        </div>
                    <?php endif; ?>
                </div>
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
            <h2 class="wp-desa-title" style="text-align:center; margin-bottom: 30px; font-size: 2em; color: #1e293b;">Program & Bantuan Sosial</h2>

            <!-- Program List -->
            <div style="display: grid; gap: 20px;">
                <template x-for="p in programs" :key="p.id">
                    <div class="wp-desa-stat-card" style="text-align: left; padding: 0; overflow: hidden; border: 1px solid #f1f5f9;">
                        <div style="padding: 25px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 250px;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                        <span class="dashicons dashicons-awards" style="color: #2563eb; font-size: 24px; width: 24px; height: 24px;"></span>
                                        <h3 style="margin: 0; color: #1e293b; font-size: 1.25em;" x-text="p.name"></h3>
                                    </div>
                                    <p style="margin: 0 0 15px 0; color: #64748b; line-height: 1.6;" x-text="p.description"></p>
                                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                        <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-location" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            <span x-text="p.origin"></span>
                                        </span>
                                        <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            <span x-text="p.year"></span>
                                        </span>
                                    </div>
                                </div>
                                <div style="text-align: right; min-width: 150px; display: flex; flex-direction: column; align-items: flex-end;">
                                    <div style="font-weight: 700; font-size: 1.5em; color: #059669;" x-text="formatCurrency(p.amount_per_recipient)"></div>
                                    <div style="font-size: 0.9em; color: #64748b; margin-top: 5px; margin-bottom: 15px;" x-text="'Kuota: ' + p.quota + ' Penerima'"></div>

                                    <button @click="viewRecipients(p)" class="wp-desa-btn" :class="activeProgramId === p.id ? 'wp-desa-btn-secondary' : 'wp-desa-btn-primary'" style="font-size: 0.9em; padding: 8px 16px;">
                                        <span x-text="activeProgramId === p.id ? 'Tutup Daftar' : 'Lihat Penerima'"></span>
                                        <span class="dashicons" :class="activeProgramId === p.id ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2'" style="margin-left: 5px; font-size: 14px; width: 14px; height: 14px; margin-top: 3px;"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Recipients List (Collapsible) -->
                        <div x-show="activeProgramId === p.id" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-90" x-transition:enter-end="opacity-100 transform scale-y-100" style="border-top: 1px solid #f1f5f9; background: #f8fafc;">
                            <div style="padding: 20px;">
                                <h4 style="margin: 0 0 15px 0; color: #334155;">Daftar Penerima Bantuan</h4>
                                <div style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 0.95em;">
                                        <thead>
                                            <tr style="background: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 0.85em; letter-spacing: 0.5px;">
                                                <th style="text-align: left; padding: 12px 15px; font-weight: 600;">Nama</th>
                                                <th style="text-align: left; padding: 12px 15px; font-weight: 600;">Alamat</th>
                                                <th style="text-align: center; padding: 12px 15px; font-weight: 600;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(r, index) in recipients" :key="r.id">
                                                <tr :style="index % 2 === 0 ? 'background: white;' : 'background: #fcfcfc;'" style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding: 12px 15px; color: #1e293b; font-weight: 500;" x-text="r.nama_lengkap"></td>
                                                    <td style="padding: 12px 15px; color: #64748b;" x-text="r.alamat"></td>
                                                    <td style="text-align: center; padding: 12px 15px;">
                                                        <span :class="'status-badge status-' + r.status" x-text="formatStatus(r.status)"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="recipients.length === 0">
                                                <tr>
                                                    <td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada data penerima yang ditampilkan.</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="programs.length === 0">
                    <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; color: #94a3b8;">
                        <span class="dashicons dashicons-awards" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5;"></span>
                        <p style="margin: 0; font-size: 1.1em;">Belum ada program bantuan aktif saat ini.</p>
                    </div>
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

        <!-- CSS moved to assets/css/frontend/style.css -->
    <?php
        return ob_get_clean();
    }

    public function render_keuangan()
    {
        ob_start();
    ?>
        <div id="wp-desa-keuangan" class="wp-desa-wrapper" x-data="keuanganDesa()">
            <div class="wp-desa-header">
                <h2 class="wp-desa-title">Transparansi Keuangan</h2>
                <div class="wp-desa-filter">
                    <label class="wp-desa-filter-label">Tahun Anggaran:</label>
                    <select x-model="filterYear" @change="fetchSummary" class="wp-desa-select wp-desa-select-year">
                        <template x-for="y in years" :key="y">
                            <option :value="y" x-text="y"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="wp-desa-summary-grid">
                <!-- Pendapatan -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon-bg">
                        <span class="dashicons dashicons-money-alt" style="color: #2271b1;"></span>
                    </div>
                    <h4 class="wp-desa-stat-label">Total Pendapatan</h4>
                    <h3 class="wp-desa-stat-value" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></h3>
                    <div class="wp-desa-stat-sub">
                        Target: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></span>
                    </div>
                </div>

                <!-- Belanja -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon-bg">
                        <span class="dashicons dashicons-cart" style="color: #d63638;"></span>
                    </div>
                    <h4 class="wp-desa-stat-label">Total Belanja</h4>
                    <h3 class="wp-desa-stat-value" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></h3>
                    <div class="wp-desa-stat-sub">
                        Pagu: <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></span>
                    </div>
                </div>

                <!-- Surplus/Defisit -->
                <div class="wp-desa-stat-card">
                    <div class="wp-desa-stat-icon-bg">
                        <span class="dashicons dashicons-chart-line" style="color: #00a32a;"></span>
                    </div>
                    <h4 class="wp-desa-stat-label">Sisa Lebih (SiLPA)</h4>
                    <h3 class="wp-desa-stat-value" :style="{color: getSurplus() >= 0 ? '#16a34a' : '#dc2626'}" x-text="formatCurrency(getSurplus())"></h3>
                    <div style="margin-top: 5px; font-size: 0.85em; color: #64748b;">
                        Realisasi Pendapatan - Belanja
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="wp-desa-chart-wrapper">
                <div class="wp-desa-chart-container" style="margin: 0; max-width: none;">
                    <h4 class="wp-desa-chart-title">Sumber Pendapatan</h4>
                    <div class="wp-desa-chart-box">
                        <canvas id="publicIncomeChart"></canvas>
                    </div>
                </div>
                <div class="wp-desa-chart-container" style="margin: 0; max-width: none;">
                    <h4 class="wp-desa-chart-title">Penggunaan Anggaran</h4>
                    <div class="wp-desa-chart-box">
                        <canvas id="publicExpenseChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="wp-desa-stat-card" style="padding: 0;">
                <div class="wp-desa-table-header">
                    <h4 class="wp-desa-table-title">Rincian Realisasi APBDes</h4>
                </div>
                <div class="wp-desa-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: left;">Uraian</th>
                                <th style="text-align: right;">Anggaran</th>
                                <th style="text-align: right;">Realisasi</th>
                                <th style="text-align: center;">%</th>
                            </tr>
                        </thead>
                        <tbody style="color: #334155;">
                            <template x-for="(item, index) in items" :key="item.id">
                                <tr :style="index % 2 === 0 ? 'background: white;' : 'background: #fcfcfc;'" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                    <td style="padding: 15px 20px;">
                                        <div style="font-weight: 600; color: #1e293b;" x-text="item.category"></div>
                                        <div style="font-size: 0.9em; color: #64748b; margin-top: 4px;" x-text="item.description"></div>
                                    </td>
                                    <td style="text-align: right; padding: 15px 20px; white-space: nowrap;" x-text="formatCurrency(item.budget_amount)"></td>
                                    <td style="text-align: right; padding: 15px 20px; white-space: nowrap; font-weight: 500;" x-text="formatCurrency(item.realization_amount)"></td>
                                    <td style="text-align: center; padding: 15px 20px;">
                                        <div class="wp-desa-percentage"
                                            :style="{
                                                 background: calculatePercentage(item.realization_amount, item.budget_amount) > 90 ? '#dcfce7' : (calculatePercentage(item.realization_amount, item.budget_amount) > 50 ? '#fef9c3' : '#fee2e2'),
                                                 color: calculatePercentage(item.realization_amount, item.budget_amount) > 90 ? '#166534' : (calculatePercentage(item.realization_amount, item.budget_amount) > 50 ? '#854d0e' : '#991b1b')
                                             }"
                                            x-text="calculatePercentage(item.realization_amount, item.budget_amount) + '%'">
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="items.length === 0">
                                <tr>
                                    <td colspan="4" class="wp-desa-empty-state">
                                        Belum ada data keuangan untuk tahun ini.
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
            <!-- CSS moved to assets/css/frontend/style.css -->

            <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px;">
                <button @click="tab = 'form'" :class="{'active': tab === 'form'}" class="wp-desa-tab-btn">
                    <span class="dashicons dashicons-edit"></span> Buat Laporan
                </button>
                <button @click="tab = 'track'" :class="{'active': tab === 'track'}" class="wp-desa-tab-btn">
                    <span class="dashicons dashicons-search"></span> Cek Status Laporan
                </button>
            </div>

            <div class="wp-desa-content">
                <!-- Form Aduan -->
                <div x-show="tab === 'form'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div x-show="message.content"
                        style="padding: 15px; border-radius: 8px; margin-bottom: 20px;"
                        :style="message.type === 'success' ? 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;' : 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;'">
                        <span x-text="message.content" style="font-weight: 500;"></span>
                        <template x-if="trackingCode">
                            <div style="margin-top: 15px; background: white; padding: 15px; border-radius: 8px; border: 1px dashed #166534;">
                                <div style="font-size: 0.9em; margin-bottom: 5px; color: #166534;">Kode Tracking Anda:</div>
                                <div class="wp-desa-tracking-code" x-text="trackingCode" style="font-family: monospace; font-size: 1.5em; font-weight: 700; color: #1e293b; letter-spacing: 1px;"></div>
                                <p class="wp-desa-helper" style="margin: 5px 0 0 0;">Simpan kode ini untuk mengecek status laporan.</p>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="submitComplaint" enctype="multipart/form-data" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9;">
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
                            <div style="border: 2px dashed #cbd5e1; padding: 20px; border-radius: 8px; text-align: center; background: #f8fafc; transition: all 0.2s;" class="wp-desa-upload-area">
                                <input type="file" @change="handleFileUpload" accept="image/*" class="wp-desa-input" style="border: none; padding: 0; background: transparent; width: auto;">
                                <small class="wp-desa-helper">Format: JPG, PNG. Maks 2MB.</small>
                            </div>
                        </div>

                        <button type="submit" :disabled="submitting" class="wp-desa-btn wp-desa-btn-primary" style="width: 100%;">
                            <span x-show="!submitting">Kirim Laporan</span>
                            <span x-show="submitting" style="display: flex; align-items: center; gap: 8px;">
                                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> Mengirim...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Tracking Form -->
                <div x-show="tab === 'track'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <form @submit.prevent="checkStatus" style="margin-bottom: 1.5rem; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9;">
                        <label class="wp-desa-label" style="margin-bottom: 12px;">Masukkan Kode Tracking</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" x-model="trackCode" placeholder="Contoh: ADU-XXXXXX" required class="wp-desa-input" style="flex: 1; font-family: monospace; letter-spacing: 1px; font-weight: 600;">
                            <button type="submit" :disabled="tracking" class="wp-desa-btn wp-desa-btn-primary" style="width: auto; min-width: 100px;">
                                <span x-show="!tracking">Cek</span>
                                <span x-show="tracking" class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span>
                            </button>
                        </div>
                    </form>

                    <div x-show="trackResult" class="wp-desa-result-card">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="width: 60px; height: 60px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #2563eb;">
                                <span class="dashicons dashicons-clipboard" style="font-size: 30px; width: 30px; height: 30px;"></span>
                            </div>
                            <h4 style="margin: 0; color: #1e293b; font-size: 1.2em;">Status Laporan</h4>
                            <p style="margin: 5px 0 0 0; color: #64748b; font-family: monospace;" x-text="trackResult.code"></p>
                        </div>

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
                            <span :class="'wp-desa-badge wp-desa-badge-' + trackResult.status" x-text="formatStatus(trackResult.status)"
                                style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e2e8f0; color: #475569;"
                                :style="{'pending': 'background: #fef3c7; color: #92400e;', 'in_progress': 'background: #dbeafe; color: #1e40af;', 'resolved': 'background: #dcfce7; color: #166534;', 'rejected': 'background: #fee2e2; color: #991b1b;'}[trackResult.status]">
                            </span>
                        </div>

                        <template x-if="trackResult.response">
                            <div style="margin-top: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <strong style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #334155;">
                                    <span class="dashicons dashicons-admin-comments"></span> Tanggapan Admin:
                                </strong>
                                <p style="margin: 0; color: #4b5563; line-height: 1.6;" x-text="trackResult.response"></p>
                            </div>
                        </template>
                    </div>

                    <div x-show="trackError" style="padding: 15px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px;" x-text="trackError"></div>
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
                            'pending': 'Menunggu',
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
            <!-- CSS moved to assets/css/frontend/style.css -->

            <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px;">
                <button class="wp-desa-tab-btn" :class="{ 'active': tab === 'request' }" @click="tab = 'request'">
                    <span class="dashicons dashicons-edit"></span> Buat Permohonan
                </button>
                <button class="wp-desa-tab-btn" :class="{ 'active': tab === 'tracking' }" @click="tab = 'tracking'">
                    <span class="dashicons dashicons-search"></span> Cek Status
                </button>
            </div>

            <!-- Request Form -->
            <div x-show="tab === 'request'">
                <div x-show="message.content" :style="message.type === 'success' ? 'background: #dcfce7; color: #166534; border-color: #bbf7d0;' : 'background: #fee2e2; color: #991b1b; border-color: #fecaca;'" style="padding: 15px; border-radius: 8px; border: 1px solid; margin-bottom: 20px;" x-text="message.content"></div>

                <div x-show="trackingCode" style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                    <div style="color: #1e40af; font-weight: 500; margin-bottom: 10px;">Kode Tracking Anda:</div>
                    <div style="font-size: 1.5em; font-weight: 700; color: #1e3a8a; letter-spacing: 2px;" x-text="trackingCode"></div>
                    <div style="font-size: 0.9em; color: #60a5fa; margin-top: 10px;">Simpan kode ini untuk mengecek status permohonan.</div>
                </div>

                <form @submit.prevent="submitRequest">
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">NIK</label>
                        <input type="text" x-model="form.nik" class="wp-desa-input" required maxlength="16">
                    </div>

                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Nama Lengkap</label>
                        <input type="text" x-model="form.name" class="wp-desa-input" required>
                    </div>

                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Nomor WhatsApp</label>
                        <input type="text" x-model="form.phone" class="wp-desa-input" required placeholder="08...">
                    </div>

                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Jenis Surat</label>
                        <select x-model="form.letter_type_id" class="wp-desa-select" required>
                            <option value="">Pilih Jenis Surat</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id" x-text="type.name"></option>
                            </template>
                        </select>
                        <small class="wp-desa-helper" x-text="selectedTypeDescription"></small>
                    </div>

                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Keterangan / Keperluan</label>
                        <textarea x-model="form.details" class="wp-desa-textarea" rows="3"></textarea>
                    </div>

                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary" :disabled="submitting">
                        <span x-show="!submitting">Kirim Permohonan</span>
                        <span x-show="submitting">Mengirim...</span>
                    </button>
                </form>
            </div>

            <!-- Tracking Form -->
            <div x-show="tab === 'tracking'">
                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Masukkan Kode Tracking</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" x-model="trackCode" class="wp-desa-input" placeholder="Contoh: REQ-...">
                        <button type="button" @click="checkStatus" class="wp-desa-btn wp-desa-btn-primary" :disabled="tracking">
                            <span x-show="!tracking">Cek</span>
                            <span x-show="tracking">...</span>
                        </button>
                    </div>
                </div>

                <div x-show="trackResult" class="wp-desa-result-card">
                    <div class="wp-desa-card-row">
                        <span class="wp-desa-card-label">Nama Pengaju</span>
                        <span class="wp-desa-card-value" x-text="trackResult.name"></span>
                    </div>
                    <div class="wp-desa-card-row"><span class="wp-desa-card-label">Tanggal</span><span class="wp-desa-card-value" x-text="formatDate(trackResult.created_at)"></span></div>
                    <div class="wp-desa-card-row"><span class="wp-desa-card-label">Status</span>
                        <span: class="'wp-desa-badge wp-desa-badge-' + trackResult.status" x-text="formatStatus(trackResult.status)"
                            style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e2e8f0; color: #475569;"

                            :style="{'pending': 'background: #fef3c7; color: #92400e;', 'processed': 'background: #dbeafe; color: #1e40af;', 'ready': 'background: #dcfce7; color: #166534;', 'completed': 'background: #d1fae5; color: #065f46;', 'rejected': 'background: #fee2e2; color: #991b1b;'}[trackResult.status]"> </span>
                    </div>
                </div>
                <div x-show="trackError" style="padding: 15px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px;" x-text="trackError"></div>
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
                    }

                    ,
                    message: {
                        type: '',
                        content: ''
                    }

                    ,
                    trackingCode: null,
                    submitting: false,

                    trackCode: '',
                    trackResult: null,
                    trackError: null,
                    tracking: false,

                    init() {
                        this.fetchTypes();
                    }

                    ,

                    fetchTypes() {
                        fetch('/wp-json/wp-desa/v1/letters/types').then(res => res.json()).then(data => this.types = data);
                    }

                    ,

                    get selectedTypeDescription() {
                            const type = this.types.find(t => t.id == this.form.letter_type_id);
                            return type ? type.description : '';
                        }

                        ,

                    submitRequest() {
                        this.submitting = true;

                        this.message = {
                            type: '',
                            content: ''
                        }

                        ;
                        this.trackingCode = null;

                        fetch('/wp-json/wp-desa/v1/letters/request', {

                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            }

                            ,
                            body: JSON.stringify(this.form)

                        }).then(res => res.json()).then(data => {
                            this.submitting = false;

                            if (data.success) {
                                this.message = {
                                    type: 'success',
                                    content: data.message
                                }

                                ;
                                this.trackingCode = data.tracking_code;

                                this.form = {
                                    nik: '',
                                    name: '',
                                    phone: '',
                                    letter_type_id: '',
                                    details: ''
                                }

                                ; // Reset
                            } else {
                                this.message = {
                                    type: 'error',
                                    content: data.message || 'Terjadi kesalahan.'
                                }

                                ;
                            }

                        }).catch(err => {
                            this.submitting = false;

                            this.message = {
                                type: 'error',
                                content: 'Gagal menghubungi server.'
                            }

                            ;
                        });
                    }

                    ,

                    checkStatus() {
                        this.tracking = true;
                        this.trackResult = null;
                        this.trackError = null;

                        fetch('/wp-json/wp-desa/v1/letters/track?code=' + this.trackCode).then(res => res.json()).then(data => {
                            this.tracking = false;

                            if (data.id) {
                                this.trackResult = data;
                            } else {
                                this.trackError = data.message || 'Data tidak ditemukan.';
                            }

                        }).catch(err => {
                            this.tracking = false;
                            this.trackError = 'Gagal menghubungi server.';
                        });
                    }

                    ,

                    formatDate(dateString) {
                        if (!dateString) return '-';
                        const date = new Date(dateString);

                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    }

                    ,

                    formatStatus(status) {
                        const map = {
                            'pending': 'Menunggu',
                            'processed': 'Diproses',
                            'ready': 'Siap Diambil',
                            'completed': 'Selesai',
                            'rejected': 'Ditolak'
                        }

                        ;
                        return map[status] || status;
                    }
                }));
            });
        </script><?php
                    return ob_get_clean();
                }
            }
