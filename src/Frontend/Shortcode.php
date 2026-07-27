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
    add_shortcode('single-umkm', [$this, 'render_single_umkm']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  public function render_statistik()
  {
    global $wpdb;
    $table = $wpdb->prefix . 'desa_residents';

    // Cache results for 1 hour to reduce DB load
    $stats = get_transient('wp_desa_quick_stats');

    $needs_refresh = false;
    if ($stats === false || !is_array($stats)) {
      $needs_refresh = true;
    } else {
      $required_keys = ['total', 'male', 'female', 'families', 'jobs', 'maritals', 'age_groups'];
      foreach ($required_keys as $key) {
        if (!array_key_exists($key, $stats)) {
          $needs_refresh = true;
          break;
        }
      }
    }

    if ($needs_refresh) {
      if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $stats = [
          'total' => 0,
          'male' => 0,
          'female' => 0,
          'families' => 0,
          'jobs' => [],
          'maritals' => [],
          'age_groups' => [
            'anak' => 0,
            'dewasa' => 0,
          ],
        ];
      } else {
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $male = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE jenis_kelamin = 'Laki-laki'");
        $female = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE jenis_kelamin = 'Perempuan'");

        $has_kk = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'no_kk'");
        if (!empty($has_kk)) {
          $families = (int) $wpdb->get_var("SELECT COUNT(DISTINCT no_kk) FROM $table WHERE no_kk != ''");
        } else {
          $families = 0;
        }

        $job_stats = $wpdb->get_results("SELECT pekerjaan as label, COUNT(*) as count FROM $table GROUP BY pekerjaan ORDER BY count DESC LIMIT 6");
        $marital_stats = $wpdb->get_results("SELECT status_perkawinan as label, COUNT(*) as count FROM $table GROUP BY status_perkawinan");
        $age_groups = $wpdb->get_row("
                    SELECT
                        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 18 THEN 1 ELSE 0 END) AS anak,
                        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 18 THEN 1 ELSE 0 END) AS dewasa
                    FROM $table
                ");

        $stats = [
          'total' => $total,
          'male' => $male,
          'female' => $female,
          'families' => $families,
          'jobs' => $job_stats,
          'maritals' => $marital_stats,
          'age_groups' => $age_groups,
        ];

        set_transient('wp_desa_quick_stats', $stats, HOUR_IN_SECONDS);
      }
    }

    $total_val = isset($stats['total']) ? (int) $stats['total'] : 0;
    $families_val = isset($stats['families']) ? (int) $stats['families'] : 0;
    $male_val = isset($stats['male']) ? (int) $stats['male'] : 0;
    $female_val = isset($stats['female']) ? (int) $stats['female'] : 0;
    $job_stats = isset($stats['jobs']) && is_array($stats['jobs']) ? $stats['jobs'] : [];
    $marital_stats = isset($stats['maritals']) && is_array($stats['maritals']) ? $stats['maritals'] : [];
    $age_groups_raw = isset($stats['age_groups']) ? $stats['age_groups'] : null;
    if (is_object($age_groups_raw)) {
      $age_anak = isset($age_groups_raw->anak) ? (int) $age_groups_raw->anak : 0;
      $age_dewasa = isset($age_groups_raw->dewasa) ? (int) $age_groups_raw->dewasa : 0;
    } elseif (is_array($age_groups_raw)) {
      $age_anak = isset($age_groups_raw['anak']) ? (int) $age_groups_raw['anak'] : 0;
      $age_dewasa = isset($age_groups_raw['dewasa']) ? (int) $age_groups_raw['dewasa'] : 0;
    } else {
      $age_anak = 0;
      $age_dewasa = 0;
    }

    ob_start();
?>
    <div class="wp-desa-wrapper">
      <!-- CSS moved to assets/css/frontend/style.css -->

      <?php
      $total_gender = $male_val + $female_val;
      $male_pct = $total_gender > 0 ? round(($male_val / $total_gender) * 100) : 0;
      $female_pct = $total_gender > 0 ? round(($female_val / $total_gender) * 100) : 0;

      $r = 60;
      $sw = 14;
      $c = 2 * M_PI * $r;
      $male_dash = ($male_pct / 100) * $c;
      $female_dash = ($female_pct / 100) * $c;
      // Female segment starts where male ends; draw male on top so it renders first clockwise
      // Render: male first, then female. Female offset = male dash.
      ?>

      <div class="wp-desa-chart-container">
        <h3 style="text-align: center; margin-top: 0; color: #1a1a1a; font-size: 1.1em; margin-bottom: 15px;">Komposisi Penduduk</h3>
        <div class="wp-desa-doughnut">
          <svg viewBox="0 0 160 160" class="wp-desa-doughnut-svg">
            <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#e8e8e8" stroke-width="<?php echo $sw; ?>" />
            <!-- Laki-laki -->
            <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#024ad8" stroke-width="<?php echo $sw; ?>"
              stroke-dasharray="<?php echo round($male_dash, 1); ?> <?php echo round($c - $male_dash, 1); ?>" stroke-dashoffset="0"
              stroke-linecap="butt" transform="rotate(-90 80 80)" />
            <!-- Perempuan -->
            <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#b3262b" stroke-width="<?php echo $sw; ?>"
              stroke-dasharray="<?php echo round($female_dash, 1); ?> <?php echo round($c - $female_dash, 1); ?>" stroke-dashoffset="<?php echo round(-$male_dash, 1); ?>"
              stroke-linecap="butt" transform="rotate(-90 80 80)" />
            <text x="80" y="76" text-anchor="middle" font-family="Forma DJR Micro, Manrope, Inter, sans-serif" font-size="28" font-weight="500" fill="#1a1a1a"><?php echo number_format_i18n($total_gender); ?></text>
            <text x="80" y="96" text-anchor="middle" font-family="Forma DJR Micro, Manrope, Inter, sans-serif" font-size="12" fill="#636363" font-weight="500">Jiwa</text>
          </svg>
          <div class="wp-desa-doughnut-legend">
            <span class="wp-desa-doughnut-legend-item"><i style="background:#024ad8"></i> Laki-laki: <b><?php echo number_format_i18n($male_val); ?></b> (<?php echo $male_pct; ?>%)</span>
            <span class="wp-desa-doughnut-legend-item"><i style="background:#b3262b"></i> Perempuan: <b><?php echo number_format_i18n($female_val); ?></b> (<?php echo $female_pct; ?>%)</span>
          </div>
        </div>
      </div>

      <div class="wp-desa-stats-grid">
        <!-- Total -->
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
            <?php echo \WpDesa\Frontend\Icons::svg('users', 'width:24px;height:24px'); ?>
          </div>
          <div class="wp-desa-stat-number"><?php echo number_format_i18n($total_val); ?></div>
          <div class="wp-desa-stat-label">Total Penduduk</div>
        </div>

        <!-- KK -->
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon" style="background: #e6f4ea; color: #1f6b3c;">
            <?php echo \WpDesa\Frontend\Icons::svg('home', 'width: 24px; height: 24px;'); ?>
          </div>
          <div class="wp-desa-stat-number"><?php echo number_format_i18n($families_val); ?></div>
          <div class="wp-desa-stat-label">Kepala Keluarga</div>
        </div>

        <!-- Laki-laki -->
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
            <?php echo \WpDesa\Frontend\Icons::svg('mars', 'width: 24px; height: 24px;'); ?>
          </div>
          <div class="wp-desa-stat-number"><?php echo number_format_i18n($male_val); ?></div>
          <div class="wp-desa-stat-label">Laki-laki</div>
        </div>

        <!-- Perempuan -->
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon" style="background: #f9d4d2; color: #b3262b;">
            <?php echo \WpDesa\Frontend\Icons::svg('venus', 'width: 24px; height: 24px;'); ?>
          </div>
          <div class="wp-desa-stat-number"><?php echo number_format_i18n($female_val); ?></div>
          <div class="wp-desa-stat-label">Perempuan</div>
        </div>
      </div>

      <div class="wp-desa-demografi-card">
        <h4 class="wp-desa-demografi-title">Rincian Demografi</h4>

        <div class="wp-desa-demografi-grid">
          <div class="wp-desa-demografi-group">
            <h5 class="wp-desa-demografi-group-title">Jenis Kelamin</h5>
            <ul class="wp-desa-demografi-list">
              <li class="wp-desa-demografi-item">
                <span class="wp-desa-demografi-label">Laki-laki</span>
                <span class="wp-desa-demografi-value"><?php echo number_format_i18n($male_val); ?></span>
              </li>
              <li class="wp-desa-demografi-item">
                <span class="wp-desa-demografi-label">Perempuan</span>
                <span class="wp-desa-demografi-value"><?php echo number_format_i18n($female_val); ?></span>
              </li>
            </ul>
          </div>

          <div class="wp-desa-demografi-group">
            <h5 class="wp-desa-demografi-group-title">Kelompok Usia</h5>
            <ul class="wp-desa-demografi-list">
              <li class="wp-desa-demografi-item">
                <span class="wp-desa-demografi-label">Anak (&lt; 18 tahun)</span>
                <span class="wp-desa-demografi-value"><?php echo number_format_i18n($age_anak); ?></span>
              </li>
              <li class="wp-desa-demografi-item">
                <span class="wp-desa-demografi-label">Dewasa (&ge; 18 tahun)</span>
                <span class="wp-desa-demografi-value"><?php echo number_format_i18n($age_dewasa); ?></span>
              </li>
            </ul>
          </div>

          <?php if (!empty($job_stats)): ?>
            <div class="wp-desa-demografi-group">
              <h5 class="wp-desa-demografi-group-title">Pekerjaan Terbanyak</h5>
              <ul class="wp-desa-demografi-list">
                <?php foreach ($job_stats as $row): ?>
                  <li class="wp-desa-demografi-item">
                    <span class="wp-desa-demografi-label"><?php echo esc_html($row->label ?: 'Tidak Diisi'); ?></span>
                    <span class="wp-desa-demografi-value"><?php echo number_format_i18n((int) $row->count); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($marital_stats)): ?>
            <div class="wp-desa-demografi-group">
              <h5 class="wp-desa-demografi-group-title">Status Perkawinan</h5>
              <ul class="wp-desa-demografi-list">
                <?php foreach ($marital_stats as $row): ?>
                  <li class="wp-desa-demografi-item">
                    <span class="wp-desa-demografi-label"><?php echo esc_html($row->label ?: 'Tidak Diisi'); ?></span>
                    <span class="wp-desa-demografi-value"><?php echo number_format_i18n((int) $row->count); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
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
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
          <?php while ($query->have_posts()): $query->the_post();
            $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
            $location = get_post_meta(get_the_ID(), '_desa_umkm_location', true);
            $categories = get_the_terms(get_the_ID(), 'desa_umkm_cat');
            $cat_name = !empty($categories) ? $categories[0]->name : 'UMKM';
          ?>
            <div class="wp-desa-stat-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; text-align: left; border: 1px solid var(--fog); background: var(--canvas); border-radius: var(--rounded-xl);">
              <div style="height: 200px; background: var(--cloud); overflow: hidden; position: relative;">
                <div style="position: absolute; top: var(--sp-sm); right: var(--sp-sm); background: rgba(255, 255, 255, 0.9); padding: 4px 10px; border-radius: var(--rounded-pill); font-size: 12px; font-weight: 600; color: var(--ink); z-index: 2;">
                  <?php echo esc_html($cat_name); ?>
                </div>
                <?php if (has_post_thumbnail()): ?>
                  <a href="<?php the_permalink(); ?>" style="display: block; width: 100%; height: 100%;">
                    <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                  </a>
                <?php else: ?>
                  <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--graphite); background: var(--cloud);">
                    <?php echo \WpDesa\Frontend\Icons::svg('store', 'width: 48px; height: 48px;'); ?>
                  </div>
                <?php endif; ?>
              </div>
              <div style="padding: var(--sp-xl); flex: 1; display: flex; flex-direction: column;">
                <h3 style="margin: 0 0 var(--sp-xs) 0; font-family: var(--font-display); font-size: 20px; font-weight: 500; line-height: 1.0;">
                  <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: var(--ink);"><?php the_title(); ?></a>
                </h3>
                <div style="font-size: 14px; color: var(--graphite); margin-bottom: var(--sp-lg); flex: 1; line-height: 1.5;">
                  <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                </div>

                <div style="border-top: 1px solid var(--fog); padding-top: var(--sp-sm); margin-top: auto; display: flex; justify-content: space-between; align-items: center;">
                  <a href="<?php the_permalink(); ?>" style="font-size: 14px; font-weight: 500; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 4px;">
                    Detail <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width: 16px; height: 16px; margin-top: 2px;'); ?>
                  </a>

                  <?php if ($phone):
                    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                    if (substr($clean_phone, 0, 1) == '0') {
                      $clean_phone = '62' . substr($clean_phone, 1);
                    }
                  ?>
                    <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" target="_blank" style="background: #25D366; color: #ffffff; border: none; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--rounded-md); text-decoration: none;">
                      <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width: 16px; height: 16px;'); ?> Chat
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--cloud); border-radius: var(--rounded-xl); border: 1px solid var(--fog); color: var(--graphite);">
          <?php echo \WpDesa\Frontend\Icons::svg('store', 'width: 48px; height: 48px; margin-bottom: 10px;'); ?>
          <p style="margin: 0; font-size: 1.1em;">Belum ada data UMKM yang ditampilkan.</p>
        </div>
      <?php endif;
      wp_reset_postdata(); ?>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_single_umkm($atts)
  {
    $atts = shortcode_atts([
      'id' => 0
    ], $atts);

    $post_id = $atts['id'] ? intval($atts['id']) : get_the_ID();

    if (!$post_id || get_post_type($post_id) !== 'desa_umkm') {
      return '';
    }

    $post = get_post($post_id);

    $phone = get_post_meta($post_id, '_desa_umkm_phone', true);
    $location = get_post_meta($post_id, '_desa_umkm_location', true);
    $gallery_ids = get_post_meta($post_id, '_desa_umkm_gallery', true);
    $categories = get_the_terms($post_id, 'desa_umkm_cat');
    $cat_name = !empty($categories) ? $categories[0]->name : 'UMKM';

    $thumb_url = get_the_post_thumbnail_url($post_id, 'large');

    ob_start();
  ?>
    <div class="wp-desa-single-umkm wp-desa-wrapper">
      <div style="background: var(--canvas); border-radius: var(--rounded-xl); overflow: hidden; border: 1px solid var(--fog); box-shadow: var(--shadow-soft-lift);">

        <!-- Header Image / Featured -->
        <?php if ($thumb_url): ?>
          <div style="width: 100%; height: 400px; overflow: hidden; position: relative;">
            <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; top: 20px; right: 20px; background: var(--canvas); padding: 6px 14px; border-radius: 20px; font-weight: 600; color: #3d3d3d; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              <?php echo esc_html($cat_name); ?>
            </div>
          </div>
        <?php endif; ?>

        <div style="padding: var(--sp-xl);">
          <h1 style="margin: 0 0 15px 0; color: #1a1a1a; font-size: 2em;"><?php echo esc_html($post->post_title); ?></h1>

          <div style="display: flex; flex-wrap: wrap; gap: 40px; margin-top: 30px;">
            <!-- Main Content -->
            <div style="flex: 2; min-width: 300px;">
              <div style="color: #3d3d3d; line-height: 1.8; font-size: 1.1em;">
                <?php echo wpautop($post->post_content); ?>
              </div>

              <!-- Gallery -->
              <?php if ($gallery_ids):
                $ids = explode(',', $gallery_ids);
              ?>
                <h3 style="margin: 40px 0 20px 0; color: #1a1a1a; font-size: 1.3em; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f7f7f7; padding-bottom: 10px;">
                  <?php echo \WpDesa\Frontend\Icons::svg('image', 'width: 24px; height: 24px;'); ?> Galeri Produk
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px;">
                  <?php foreach ($ids as $id):
                    $img = wp_get_attachment_image_url($id, 'medium');
                    $full = wp_get_attachment_image_url($id, 'full');
                    if (!$img) continue;
                  ?>
                    <a href="<?php echo esc_url($full); ?>" class="glightbox" data-gallery="umkm-gallery" style="display: block; aspect-ratio: 1; border-radius: var(--rounded-xl); overflow: hidden; border: 1px solid var(--fog); position: relative; box-shadow: var(--shadow-soft-lift);">
                      <img src="<?php echo esc_url($img); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Sidebar Info -->
            <div style="flex: 1; min-width: 280px;">
              <div style="background: var(--cloud); padding: var(--sp-xl); border-radius: var(--rounded-xl); border: 1px solid var(--fog); position: sticky; top: 20px;">
                <h3 style="margin-top: 0; color: #1a1a1a; font-size: 1.2em; margin-bottom: 20px; border-bottom: 1px solid #e8e8e8; padding-bottom: 15px; font-weight: 700;">Informasi Kontak</h3>

                <?php if ($phone):
                  $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                  if (substr($clean_phone, 0, 1) == '0') {
                    $clean_phone = '62' . substr($clean_phone, 1);
                  }
                ?>
                  <div style="margin-bottom: 25px;">
                    <div style="font-size: 0.9em; color: #636363; margin-bottom: 8px; font-weight: 600;">WhatsApp</div>
                    <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" target="_blank" style="display: flex; align-items: center; gap: 10px; text-decoration: none; background: #e6f4ea; color: #1f6b3c; padding: 12px 15px; border-radius: 8px; font-weight: 600; transition: background 0.2s; justify-content: center;" onmouseover="this.style.background='#c3e6cb'" onmouseout="this.style.background='#e6f4ea'">
                      <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width: 20px; height: 20px;'); ?>
                      Hubungi Penjual
                    </a>
                  </div>
                <?php endif; ?>

                <?php if ($location):
                  $lat = '';
                  $lon = '';
                  $parts = explode(',', $location);
                  if (count($parts) >= 2) {
                    $lat = trim($parts[0]);
                    $lon = trim($parts[1]);
                  }
                ?>
                  <div style="margin-bottom: 25px;">
                    <div style="font-size: 0.9em; color: #636363; margin-bottom: 8px; font-weight: 600;">Lokasi</div>
                    <div style="color: #1a1a1a;">

                      <?php if ($lat && $lon): ?>
                        <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--fog);">
                          <iframe
                            width="100%"
                            height="200"
                            frameborder="0"
                            scrolling="no"
                            marginheight="0"
                            marginwidth="0"
                            src="https://maps.google.com/maps?q=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lon); ?>&hl=es&z=14&amp;output=embed">
                          </iframe>
                        </div>
                      <?php else: ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($location); ?>" target="_blank" style="font-size: 0.9em; color: #024ad8; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                          Lihat di Google Maps <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width: 14px; height: 14px;'); ?>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Share -->
                <div>
                  <div style="font-size: 0.9em; color: #636363; margin-bottom: 10px; font-weight: 600;">Bagikan</div>
                  <div style="display: flex; gap: 10px;">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink($post_id); ?>" target="_blank" style="width: 40px; height: 40px; background: #1877f2; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                      <?php echo \WpDesa\Frontend\Icons::svg('facebook', 'width: 20px; height: 20px;'); ?>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php the_permalink($post_id); ?>&text=<?php echo urlencode($post->post_title); ?>" target="_blank" style="width: 40px; height: 40px; background: #000000; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                      <?php echo \WpDesa\Frontend\Icons::svg('twitter', 'width: 20px; height: 20px;'); ?>
                    </a>
                    <button onclick="navigator.clipboard.writeText('<?php the_permalink($post_id); ?>'); alert('Link disalin!');" style="width: 40px; height: 40px; background: #636363; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: none; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#3d3d3d'" onmouseout="this.style.background='#636363'">
                      <?php echo \WpDesa\Frontend\Icons::svg('link', 'width: 20px; height: 20px;'); ?>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
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
            <div class="wp-desa-stat-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; text-align: left; border: 1px solid var(--fog); background: var(--canvas); border-radius: var(--rounded-xl);">
              <div style="height: 200px; background: var(--cloud); overflow: hidden; position: relative;">
                <?php if (has_post_thumbnail()): ?>
                  <a href="<?php the_permalink(); ?>" style="display: block; width: 100%; height: 100%;">
                    <?php the_post_thumbnail('medium', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                  </a>
                <?php else: ?>
                  <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--graphite); background: var(--cloud);">
                    <?php echo \WpDesa\Frontend\Icons::svg('carrot', 'width: 48px; height: 48px;'); ?>
                  </div>
                <?php endif; ?>
              </div>
              <div style="padding: var(--sp-xl); flex: 1; display: flex; flex-direction: column;">
                <h3 style="margin: 0 0 var(--sp-xs) 0; font-family: var(--font-display); font-size: 20px; font-weight: 500; line-height: 1.0;">
                  <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: var(--ink);"><?php the_title(); ?></a>
                </h3>
                <div style="font-size: 14px; color: var(--graphite); margin-bottom: var(--sp-lg); flex: 1; line-height: 1.5;">
                  <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                </div>
                <a href="<?php the_permalink(); ?>" style="font-size: 14px; font-weight: 500; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 4px; margin-top: auto;">
                  Baca Selengkapnya <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width: 16px; height: 16px; margin-top: 2px;'); ?>
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--cloud); border-radius: var(--rounded-xl); border: 1px solid var(--fog); color: var(--graphite);">
          <?php echo \WpDesa\Frontend\Icons::svg('carrot', 'width: 48px; height: 48px; margin-bottom: 10px;'); ?>
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
      <div class="wp-desa-profil-card">
        <div class="wp-desa-profil-header">
          <?php if ($logo): ?>
            <img src="<?php echo esc_url($logo); ?>" alt="Logo Kabupaten" class="wp-desa-profil-logo">
          <?php endif; ?>
          <h2 class="wp-desa-profil-name"><?php echo esc_html('Desa ' . $nama_desa); ?></h2>
          <p class="wp-desa-profil-subtitle">
            <?php echo esc_html('Kecamatan ' . $nama_kecamatan . ', ' . $nama_kabupaten); ?>
          </p>
        </div>

        <div class="wp-desa-profil-contact-grid">
          <?php if ($alamat): ?>
            <div class="wp-desa-profil-contact-item">
              <div class="wp-desa-profil-contact-icon">
                <?php echo \WpDesa\Frontend\Icons::svg('map-pin', 'width: 18px; height: 18px;'); ?>
              </div>
              <div>
                <div class="wp-desa-profil-contact-label">Alamat Kantor</div>
                <div class="wp-desa-profil-contact-value"><?php echo esc_html($alamat); ?></div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($email): ?>
            <div class="wp-desa-profil-contact-item">
              <div class="wp-desa-profil-contact-icon">
                <?php echo \WpDesa\Frontend\Icons::svg('mail', 'width: 18px; height: 18px;'); ?>
              </div>
              <div>
                <div class="wp-desa-profil-contact-label">Email</div>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="wp-desa-profil-contact-link"><?php echo esc_html($email); ?></a>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($telepon): ?>
            <div class="wp-desa-profil-contact-item">
              <div class="wp-desa-profil-contact-icon">
                <?php echo \WpDesa\Frontend\Icons::svg('phone', 'width: 18px; height: 18px;'); ?>
              </div>
              <div>
                <div class="wp-desa-profil-contact-label">Telepon</div>
                <a href="tel:<?php echo esc_attr($telepon); ?>" class="wp-desa-profil-contact-link"><?php echo esc_html($telepon); ?></a>
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
      <div class="wp-desa-stat-card" style="text-align: center; max-width: 400px; margin: 0 auto;">
        <div style="width: 160px; height: 160px; border-radius: 50%; overflow: hidden; margin: 0 auto var(--sp-lg) auto; border: 4px solid var(--fog); position: relative;">
          <?php if ($foto_kades): ?>
            <img src="<?php echo esc_url($foto_kades); ?>" alt="Foto Kepala Desa" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <div style="width: 100%; height: 100%; background: var(--cloud); display: flex; align-items: center; justify-content: center;">
              <?php echo \WpDesa\Frontend\Icons::svg('user', 'width: 80px; height: 80px; color: var(--graphite);'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div>
          <h3 style="margin: 0 0 var(--sp-xxs) 0; font-family: var(--font-display); font-size: 24px; font-weight: 500; line-height: 1.17; color: var(--ink);"><?php echo esc_html($nama_kades); ?></h3>
          <p style="margin: 0 0 var(--sp-sm) 0; font-size: 14px; font-weight: 500; color: var(--primary);">Kepala Desa <?php echo esc_html($nama_desa); ?></p>

          <?php if ($nip_kades): ?>
            <div style="display: inline-block; padding: 6px 16px; border-radius: var(--rounded-pill); background: var(--cloud); color: var(--graphite); font-size: 14px; font-weight: 500;">
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
    <div id="wp-desa-bantuan" class="wp-desa-wrapper">
      <h2 class="wp-desa-title" style="text-align:center; margin-bottom: 30px; font-size: 2em; color: #1a1a1a;">Program & Bantuan Sosial</h2>
      <div style="display: grid; gap: 20px;" class="wp-desa-bantuan-grid">
      </div>
    </div>

    <!-- CSS moved to assets/css/frontend/style.css -->
  <?php
    return ob_get_clean();
  }

  public function render_keuangan()
  {
    ob_start();
  ?>
    <div id="wp-desa-keuangan" class="wp-desa-wrapper">
      <div class="wp-desa-header">
        <div>
          <h2 class="wp-desa-title">Transparansi Keuangan</h2>
          <p class="wp-desa-subtitle">Ringkasan realisasi APBDes per tahun anggaran.</p>
        </div>
        <div class="wp-desa-filter">
          <label class="wp-desa-filter-label">Tahun Anggaran</label>
          <div class="wp-desa-filter-control">
            <select class="wp-desa-select wp-desa-select-year" id="wp-desa-keuangan-year">
            </select>
          </div>
        </div>
      </div>

      <div class="wp-desa-summary-grid">
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('banknote', ''); ?>
          </div>
          <h4 class="wp-desa-stat-label">Total Pendapatan</h4>
          <h3 class="wp-desa-stat-value" id="wp-desa-keu-income-real"></h3>
          <div class="wp-desa-stat-sub">
            Target <span id="wp-desa-keu-income-budget"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('shopping-cart', ''); ?>
          </div>
          <h4 class="wp-desa-stat-label">Total Belanja</h4>
          <h3 class="wp-desa-stat-value" id="wp-desa-keu-expense-real"></h3>
          <div class="wp-desa-stat-sub">
            Pagu <span id="wp-desa-keu-expense-budget"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card wp-desa-stat-card-surplus">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('trending-up', ''); ?>
          </div>
          <h4 class="wp-desa-stat-label">Sisa Lebih (SiLPA)</h4>
          <h3 class="wp-desa-stat-value" id="wp-desa-keu-surplus"></h3>
          <div class="wp-desa-stat-sub wp-desa-stat-sub-muted">
            Realisasi pendapatan dikurangi belanja
          </div>
        </div>
      </div>

      <div class="wp-desa-chart-wrapper">
        <div class="wp-desa-chart-container">
          <h4 class="wp-desa-chart-title">Sumber Pendapatan</h4>
          <div class="wp-desa-chart-box">
            <canvas id="publicIncomeChart"></canvas>
          </div>
        </div>
        <div class="wp-desa-chart-container">
          <h4 class="wp-desa-chart-title">Penggunaan Anggaran</h4>
          <div class="wp-desa-chart-box">
            <canvas id="publicExpenseChart"></canvas>
          </div>
        </div>
        <div class="wp-desa-chart-container">
          <h4 class="wp-desa-chart-title">Tren Realisasi per Tahun</h4>
          <div class="wp-desa-chart-box">
            <canvas id="publicTrendChart"></canvas>
          </div>
        </div>
      </div>

      <div class="wp-desa-stat-card wp-desa-table-card">
        <div class="wp-desa-table-header">
          <div>
            <h4 class="wp-desa-table-title">Rincian Realisasi APBDes</h4>
            <p class="wp-desa-table-subtitle">Per kategori belanja dan pendapatan desa.</p>
          </div>
        </div>
        <div class="wp-desa-table-wrapper">
          <table>
            <thead>
              <tr>
                <th class="wp-desa-col-title">Uraian</th>
                <th class="wp-desa-col-number">Anggaran</th>
                <th class="wp-desa-col-number">Realisasi</th>
                <th class="wp-desa-col-percentage">Realisasi</th>
              </tr>
            </thead>
            <tbody id="wp-desa-keu-table-body">
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_aduan()
  {
    ob_start();
  ?>
    <div id="wp-desa-aduan" class="wp-desa-wrapper">
      <!-- CSS moved to assets/css/frontend/style.css -->

      <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e8e8e8; margin-bottom: 30px;">
        <button class="wp-desa-tab-btn active" data-tab="form">
          <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width: 18px; height: 18px;'); ?> Buat Laporan
        </button>
        <button class="wp-desa-tab-btn" data-tab="track">
          <?php echo \WpDesa\Frontend\Icons::svg('search', 'width: 18px; height: 18px;'); ?> Cek Status Laporan
        </button>
      </div>

      <div class="wp-desa-content">
        <!-- Form Aduan -->
        <div id="wp-desa-aduan-form-tab" class="wp-desa-tab-panel">
          <div id="wp-desa-aduan-message" style="padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none;"></div>
          <div id="wp-desa-aduan-tracking-code" style="margin-top: 15px; background: var(--canvas); padding: 15px; border-radius: 8px; border: 1px dashed #1f6b3c; display: none;">
            <div style="font-size: 0.9em; margin-bottom: 5px; color: #1f6b3c;">Kode Tracking Anda:</div>
            <div class="wp-desa-tracking-code" style="font-family: monospace; font-size: 1.5em; font-weight: 700; color: #1a1a1a; letter-spacing: 1px;"></div>
            <p class="wp-desa-helper" style="margin: 5px 0 0 0;">Simpan kode ini untuk mengecek status laporan.</p>
          </div>

          <form id="wp-desa-aduan-form" enctype="multipart/form-data" style="background: var(--canvas); padding: var(--sp-xl); border-radius: var(--rounded-xl); box-shadow: var(--shadow-soft-lift); border: 1px solid var(--fog);">
            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Nama Pelapor (Opsional)</label>
              <input type="text" id="wp-desa-aduan-reporter_name" name="reporter_name" class="wp-desa-input" placeholder="Nama Anda (Boleh dikosongkan)">
            </div>

            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Kontak (HP/Email)</label>
              <input type="text" id="wp-desa-aduan-reporter_contact" name="reporter_contact" class="wp-desa-input" placeholder="Untuk konfirmasi status">
            </div>

            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Kategori Masalah</label>
              <select id="wp-desa-aduan-category" name="category" required class="wp-desa-select">
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
              <input type="text" id="wp-desa-aduan-subject" name="subject" required class="wp-desa-input" placeholder="Ringkasan masalah">
            </div>

            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Isi Laporan</label>
              <textarea id="wp-desa-aduan-description" name="description" required rows="5" class="wp-desa-textarea" placeholder="Jelaskan detail masalah, lokasi, dll"></textarea>
            </div>

            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Upload Foto Bukti</label>
              <div style="border: 2px dashed #c2c2c2; padding: 20px; border-radius: 8px; text-align: center; background: var(--cloud); transition: all 0.2s;" class="wp-desa-upload-area">
                <input type="file" id="wp-desa-aduan-photo" name="photo" accept="image/*" class="wp-desa-input" style="border: none; padding: 0; background: transparent; width: auto;">
                <small class="wp-desa-helper">Format: JPG, PNG. Maks 2MB.</small>
              </div>
            </div>

            <button type="submit" class="wp-desa-btn wp-desa-btn-primary" style="width: 100%;">
              Kirim Laporan
            </button>
          </form>
        </div>

        <!-- Tracking Form -->
        <div id="wp-desa-aduan-track-tab" class="wp-desa-tab-panel" style="display: none;">
          <form id="wp-desa-aduan-track-form" style="margin-bottom: 1.5rem; background: var(--canvas); padding: var(--sp-xl); border-radius: var(--rounded-xl); box-shadow: var(--shadow-soft-lift); border: 1px solid var(--fog);">
            <label class="wp-desa-label" style="margin-bottom: 12px;">Masukkan Kode Tracking</label>
            <div style="display: flex; gap: 10px;">
              <input type="text" id="wp-desa-aduan-track-code" name="track_code" placeholder="Contoh: ADU-XXXXXX" required class="wp-desa-input" style="flex: 1; font-family: monospace; letter-spacing: 1px; font-weight: 600;">
              <button type="submit" class="wp-desa-btn wp-desa-btn-primary" style="width: auto; min-width: 100px;">
                Cek
              </button>
            </div>
          </form>

          <div id="wp-desa-aduan-track-result" class="wp-desa-result-card" style="display: none;">
            <div style="text-align: center; margin-bottom: 20px;">
              <div style="width: 60px; height: 60px; background: #c9e0fc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #024ad8;">
                <?php echo \WpDesa\Frontend\Icons::svg('clipboard-list', 'width: 30px; height: 30px;'); ?>
              </div>
              <h4 style="margin: 0; color: #1a1a1a; font-size: 1.2em;">Status Laporan</h4>
              <p class="wp-desa-track-code-label" style="margin: 5px 0 0 0; color: #636363; font-family: monospace;"></p>
            </div>

            <div class="wp-desa-card-row">
              <span class="wp-desa-card-label">Judul</span>
              <span class="wp-desa-card-value wp-desa-track-subject"></span>
            </div>
            <div class="wp-desa-card-row">
              <span class="wp-desa-card-label">Kategori</span>
              <span class="wp-desa-card-value wp-desa-track-category"></span>
            </div>
            <div class="wp-desa-card-row">
              <span class="wp-desa-card-label">Tanggal</span>
              <span class="wp-desa-card-value wp-desa-track-date"></span>
            </div>
            <div class="wp-desa-card-row">
              <span class="wp-desa-card-label">Status</span>
              <span class="wp-desa-track-status" style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e8e8e8; color: #3d3d3d;"></span>
            </div>

            <div class="wp-desa-track-response" style="margin-top: 20px; background: var(--cloud); padding: 15px; border-radius: 8px; border: 1px solid var(--fog); display: none;">
              <strong style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #1a1a1a;">
                <?php echo \WpDesa\Frontend\Icons::svg('message-square-text', 'width: 18px; height: 18px;'); ?> Tanggapan Admin:
              </strong>
              <p style="margin: 0; color: #4b5563; line-height: 1.6;"></p>
            </div>
          </div>

          <div id="wp-desa-aduan-track-error" style="padding: 15px; background: #fce8e6; color: #b3262b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px; display: none;"></div>
        </div>
      </div>
    </div>
  <?php
    return ob_get_clean();
  }

  public function enqueue_scripts()
  {
    global $post;

    // Enqueue Frontend JS (replaces Alpine.js, requires jQuery)
    wp_enqueue_script('wp-desa-frontend', WP_DESA_URL . 'assets/js/wp-desa-frontend.js', ['jquery'], filemtime(WP_DESA_PATH . 'assets/js/wp-desa-frontend.js'), true);

    // Enqueue Frontend Styles
    wp_enqueue_style('wp-desa-frontend', WP_DESA_URL . 'assets/css/frontend/style.css', [], '1.0.0');

    // Load conditional assets only when shortcodes are present on the page
    if (is_a($post, 'WP_Post')) {
      $content = $post->post_content;

      // Chart.js - needed for statistik and keuangan
      if (has_shortcode($content, 'wp_desa_statistik') || has_shortcode($content, 'wp_desa_keuangan')) {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
      }

      // Glightbox - needed for single-umkm gallery and umkm listing
      if (has_shortcode($content, 'single-umkm') || has_shortcode($content, 'wp_desa_umkm')) {
        wp_enqueue_style('glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css', [], '3.3.0');
        wp_enqueue_script('glightbox', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', [], '3.3.0', true);
        wp_add_inline_script('glightbox', 'if(typeof GLightbox==="undefined"){var e=document.createElement("script");e.src="' . WP_DESA_URL . 'assets/js/glightbox.min.js";document.head.appendChild(e);}');
        wp_add_inline_script('glightbox', 'document.addEventListener("DOMContentLoaded", function() { if(typeof GLightbox !== "undefined") { const lightbox = GLightbox({ selector: ".glightbox" }); } });');
      }

      // Lucide no longer needed — all icons are inline SVGs via Icons::svg()
    }
  }

  public function render_layanan()
  {
    ob_start();
  ?>
    <div id="wp-desa-layanan" class="wp-desa-wrapper">
      <!-- CSS moved to assets/css/frontend/style.css -->

      <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e8e8e8; margin-bottom: 30px;">
        <button class="wp-desa-tab-btn active" data-tab="request">
          <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width: 18px; height: 18px;'); ?> Buat Permohonan
        </button>
        <button class="wp-desa-tab-btn" data-tab="tracking">
          <?php echo \WpDesa\Frontend\Icons::svg('search', 'width: 18px; height: 18px;'); ?> Cek Status
        </button>
      </div>

      <!-- Request Form -->
      <div id="wp-desa-layanan-request-tab" class="wp-desa-tab-panel">
        <div id="wp-desa-layanan-message" style="padding: 15px; border-radius: 8px; border: 1px solid; margin-bottom: 20px; display: none;"></div>

        <div id="wp-desa-layanan-tracking-code" style="background: #c9e0fc; border: 1px solid #bfdbfe; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px; display: none;">
          <div style="color: #1e40af; font-weight: 500; margin-bottom: 10px;">Kode Tracking Anda:</div>
          <div class="wp-desa-layanan-code-label" style="font-size: 1.5em; font-weight: 700; color: #1e3a8a; letter-spacing: 2px;"></div>
          <div style="font-size: 0.9em; color: #60a5fa; margin-top: 10px;">Simpan kode ini untuk mengecek status permohonan.</div>
        </div>

        <form id="wp-desa-layanan-form">
          <div class="wp-desa-form-group">
            <label class="wp-desa-label">NIK</label>
            <input type="text" id="wp-desa-layanan-nik" name="nik" class="wp-desa-input" required maxlength="16">
          </div>

          <div class="wp-desa-form-group">
            <label class="wp-desa-label">Nama Lengkap</label>
            <input type="text" id="wp-desa-layanan-name" name="name" class="wp-desa-input" required>
          </div>

          <div class="wp-desa-form-group">
            <label class="wp-desa-label">Nomor WhatsApp</label>
            <input type="text" id="wp-desa-layanan-phone" name="phone" class="wp-desa-input" required placeholder="08...">
          </div>

          <div class="wp-desa-form-group">
            <label class="wp-desa-label">Jenis Surat</label>
            <select id="wp-desa-layanan-letter_type_id" name="letter_type_id" class="wp-desa-select" required>
              <option value="">Pilih Jenis Surat</option>
            </select>
            <small id="wp-desa-layanan-type-desc" class="wp-desa-helper"></small>
          </div>

          <div class="wp-desa-form-group">
            <label class="wp-desa-label">Keterangan / Keperluan</label>
            <textarea id="wp-desa-layanan-details" name="details" class="wp-desa-textarea" rows="3"></textarea>
          </div>

          <button type="submit" class="wp-desa-btn wp-desa-btn-primary">
            Kirim Permohonan
          </button>
        </form>
      </div>

      <!-- Tracking Form -->
      <div id="wp-desa-layanan-tracking-tab" class="wp-desa-tab-panel" style="display: none;">
        <div class="wp-desa-form-group">
          <label class="wp-desa-label">Masukkan Kode Tracking</label>
          <div style="display: flex; gap: 10px;">
            <input type="text" id="wp-desa-layanan-track-code" class="wp-desa-input" placeholder="Contoh: REQ-...">
            <button type="button" id="wp-desa-layanan-track-btn" class="wp-desa-btn wp-desa-btn-primary">
              Cek
            </button>
          </div>
        </div>

        <div id="wp-desa-layanan-track-result" class="wp-desa-result-card" style="display: none;">
          <div class="wp-desa-card-row">
            <span class="wp-desa-card-label">Nama Pengaju</span>
            <span class="wp-desa-card-value wp-desa-layanan-track-name"></span>
          </div>
          <div class="wp-desa-card-row"><span class="wp-desa-card-label">Tanggal</span><span class="wp-desa-card-value wp-desa-layanan-track-date"></span></div>
          <div class="wp-desa-card-row"><span class="wp-desa-card-label">Status</span>
            <span class="wp-desa-layanan-track-status" style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e8e8e8; color: #3d3d3d;"></span>
          </div>
        </div>
        <div id="wp-desa-layanan-track-error" style="padding: 15px; background: #fce8e6; color: #b3262b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px; display: none;"></div>
      </div>
    </div><?php
          return ob_get_clean();
        }
      }
