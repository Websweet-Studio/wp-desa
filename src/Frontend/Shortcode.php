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

      <div class="wp-desa-stat-card" style="margin-top: var(--sp-xl);">
        <h4 style="margin: 0 0 15px 0; color: #1a1a1a; font-size: 1.05em;">Rincian Demografi</h4>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <h5 style="margin: 0 0 10px 0; font-size: 0.95em; color: #636363; text-transform: uppercase; letter-spacing: 0.05em;">Jenis Kelamin</h5>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                <span>Laki-laki</span>
                <span style="font-weight: 600;"><?php echo number_format_i18n($male_val); ?></span>
              </li>
              <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                <span>Perempuan</span>
                <span style="font-weight: 600;"><?php echo number_format_i18n($female_val); ?></span>
              </li>
            </ul>
          </div>

          <div>
            <h5 style="margin: 0 0 10px 0; font-size: 0.95em; color: #636363; text-transform: uppercase; letter-spacing: 0.05em;">Kelompok Usia</h5>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                <span>Anak (&lt; 18 tahun)</span>
                <span style="font-weight: 600;"><?php echo number_format_i18n($age_anak); ?></span>
              </li>
              <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                <span>Dewasa (&ge; 18 tahun)</span>
                <span style="font-weight: 600;"><?php echo number_format_i18n($age_dewasa); ?></span>
              </li>
            </ul>
          </div>

          <?php if (!empty($job_stats)): ?>
            <div>
              <h5 style="margin: 0 0 10px 0; font-size: 0.95em; color: #636363; text-transform: uppercase; letter-spacing: 0.05em;">Pekerjaan Terbanyak</h5>
              <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($job_stats as $row): ?>
                  <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                    <span><?php echo esc_html($row->label ?: 'Tidak Diisi'); ?></span>
                    <span style="font-weight: 600;"><?php echo number_format_i18n((int) $row->count); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($marital_stats)): ?>
            <div>
              <h5 style="margin: 0 0 10px 0; font-size: 0.95em; color: #636363; text-transform: uppercase; letter-spacing: 0.05em;">Status Perkawinan</h5>
              <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($marital_stats as $row): ?>
                  <li style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95em;">
                    <span><?php echo esc_html($row->label ?: 'Tidak Diisi'); ?></span>
                    <span style="font-weight: 600;"><?php echo number_format_i18n((int) $row->count); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
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
    <div id="wp-desa-bantuan" class="wp-desa-wrapper" x-data="bantuanDesa()">
      <h2 class="wp-desa-title" style="text-align:center; margin-bottom: 30px; font-size: 2em; color: #1a1a1a;">Program & Bantuan Sosial</h2>

      <!-- Program List -->
      <div style="display: grid; gap: 20px;">
        <template x-for="p in programs" :key="p.id">
          <div class="wp-desa-stat-card" style="text-align: left; padding: 0; overflow: hidden; border: 1px solid var(--fog);">
            <div style="padding: var(--sp-xl);">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--sp-lg); flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                  <div style="display: flex; align-items: center; gap: var(--sp-xs); margin-bottom: var(--sp-xs);">
                    <?php echo \WpDesa\Frontend\Icons::svg('award', 'color: var(--primary); width: 24px; height: 24px;'); ?>
                    <h3 style="margin: 0; font-family: var(--font-display); font-size: 20px; font-weight: 500; line-height: 1.0; color: var(--ink);" x-text="p.name"></h3>
                  </div>
                  <p style="margin: 0 0 var(--sp-sm) 0; font-size: 14px; color: var(--graphite); line-height: 1.5;" x-text="p.description"></p>
                  <div style="display: flex; gap: var(--sp-xs); flex-wrap: wrap;">
                    <span style="background: var(--primary-soft); color: var(--primary-deep); padding: 4px 12px; border-radius: var(--rounded-pill); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                      <?php echo \WpDesa\Frontend\Icons::svg('map-pin', 'width: 14px; height: 14px;'); ?>
                      <span x-text="p.origin"></span>
                    </span>
                    <span style="background: var(--cloud); color: var(--ink); padding: 4px 12px; border-radius: var(--rounded-pill); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                      <?php echo \WpDesa\Frontend\Icons::svg('calendar', 'width: 14px; height: 14px;'); ?>
                      <span x-text="p.year"></span>
                    </span>
                  </div>
                </div>
                <div style="text-align: right; min-width: 150px; display: flex; flex-direction: column; align-items: flex-end;">
                  <div style="font-weight: 500; font-size: 24px; line-height: 1.17; color: var(--success);" x-text="formatCurrency(p.amount_per_recipient)"></div>
                  <div style="font-size: 14px; color: var(--graphite); margin-top: var(--sp-xxs); margin-bottom: var(--sp-sm);" x-text="'Kuota: ' + p.quota + ' Penerima'"></div>

                  <button @click="viewRecipients(p)" class="wp-desa-btn" :class="activeProgramId === p.id ? 'wp-desa-btn-secondary' : 'wp-desa-btn-primary'" style="font-size: 14px; padding: 8px 16px;">
                    <span x-text="activeProgramId === p.id ? 'Tutup Daftar' : 'Lihat Penerima'"></span>
                    <i x-show="activeProgramId !== p.id"><?php echo \WpDesa\Frontend\Icons::svg('chevron-down', 'width:14px;height:14px;margin-top:3px'); ?></i>
                    <i x-show="activeProgramId === p.id"><?php echo \WpDesa\Frontend\Icons::svg('chevron-up', 'width:14px;height:14px;margin-top:3px'); ?></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Recipients List (Collapsible) -->
            <div x-show="activeProgramId === p.id" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-90" x-transition:enter-end="opacity-100 transform scale-y-100" style="border-top: 1px solid var(--fog); background: var(--cloud);">
              <div style="padding: var(--sp-lg);">
                <h4 style="margin: 0 0 var(--sp-sm) 0; font-family: var(--font-display); font-size: 20px; font-weight: 500; color: var(--ink);">Daftar Penerima Bantuan</h4>
                <div style="overflow-x: auto; background: var(--canvas); border-radius: var(--rounded-lg); border: 1px solid var(--fog);">
                  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                      <tr style="background: var(--cloud); color: var(--graphite); text-transform: uppercase; font-size: 12px; font-weight: 600; letter-spacing: 0.7px;">
                        <th style="text-align: left; padding: 12px 15px;">Nama</th>
                        <th style="text-align: left; padding: 12px 15px;">Alamat</th>
                        <th style="text-align: center; padding: 12px 15px;">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template x-for="(r, index) in recipients" :key="r.id">
                        <tr :style="index % 2 === 0 ? 'background: var(--canvas);' : 'background: var(--cloud);'" style="border-bottom: 1px solid var(--fog);">
                          <td style="padding: 12px 15px; font-weight: 500; color: var(--ink);" x-text="r.nama_lengkap"></td>
                          <td style="padding: 12px 15px; color: var(--graphite);" x-text="r.alamat"></td>
                          <td style="text-align: center; padding: 12px 15px;">
                            <span :class="'status-badge status-' + r.status" x-text="formatStatus(r.status)"></span>
                          </td>
                        </tr>
                      </template>
                      <template x-if="recipients.length === 0">
                        <tr>
                          <td colspan="3" style="text-align: center; padding: var(--sp-xl); color: var(--graphite);">Belum ada data penerima yang ditampilkan.</td>
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
          <div style="text-align: center; padding: 60px 20px; background: var(--cloud); border-radius: var(--rounded-xl); border: 1px solid var(--fog); color: var(--graphite);">
            <?php echo \WpDesa\Frontend\Icons::svg('award', 'width: 48px; height: 48px; margin-bottom: 10px;'); ?>
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
        <div>
          <h2 class="wp-desa-title">Transparansi Keuangan</h2>
          <p class="wp-desa-subtitle">Ringkasan realisasi APBDes per tahun anggaran.</p>
        </div>
        <div class="wp-desa-filter">
          <label class="wp-desa-filter-label">Tahun Anggaran</label>
          <div class="wp-desa-filter-control">
            <select x-model="filterYear" @change="fetchSummary" class="wp-desa-select wp-desa-select-year">
              <template x-for="y in years" :key="y">
                <option :value="y" x-text="y"></option>
              </template>
            </select>
          </div>
        </div>
      </div>

      <div class="wp-desa-summary-grid">
        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('banknote', 'color: #024ad8; width: 24px; height: 24px;'); ?>
          </div>
          <h4 class="wp-desa-stat-label">Total Pendapatan</h4>
          <h3 class="wp-desa-stat-value" x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_realization || 0)"></h3>
          <div class="wp-desa-stat-sub">
            Target <span x-text="formatCurrency(summary.totals.find(t => t.type === 'income')?.total_budget || 0)"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('shopping-cart', 'color: #b3262b; width: 24px; height: 24px;'); ?>
          </div>
          <h4 class="wp-desa-stat-label">Total Belanja</h4>
          <h3 class="wp-desa-stat-value" x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_realization || 0)"></h3>
          <div class="wp-desa-stat-sub">
            Pagu <span x-text="formatCurrency(summary.totals.find(t => t.type === 'expense')?.total_budget || 0)"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card wp-desa-stat-card-surplus">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('trending-up', 'color: #1f6b3c; width: 24px; height: 24px;'); ?>
          </div>
          <h4 class="wp-desa-stat-label">Sisa Lebih (SiLPA)</h4>
          <h3 class="wp-desa-stat-value" :style="{color: getSurplus() >= 0 ? '#1f6b3c' : '#b3262b'}" x-text="formatCurrency(getSurplus())"></h3>
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
            <tbody>
              <template x-for="(item, index) in items" :key="item.id">
                <tr>
                  <td>
                    <div class="wp-desa-row-title" x-text="item.category"></div>
                    <div class="wp-desa-row-subtitle" x-text="item.description"></div>
                  </td>
                  <td class="wp-desa-cell-number" x-text="formatCurrency(item.budget_amount)"></td>
                  <td class="wp-desa-cell-number wp-desa-cell-number-strong" x-text="formatCurrency(item.realization_amount)"></td>
                  <td class="wp-desa-cell-percentage">
                    <div class="wp-desa-percentage"
                      :style="{
                                                 background: calculatePercentage(item.realization_amount, item.budget_amount) > 90 ? '#e6f4ea' : (calculatePercentage(item.realization_amount, item.budget_amount) > 50 ? '#fef3e4' : '#fce8e6'),
                                                 color: calculatePercentage(item.realization_amount, item.budget_amount) > 90 ? '#1f6b3c' : (calculatePercentage(item.realization_amount, item.budget_amount) > 50 ? '#9a5b1e' : '#b3262b')
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
            expense_sources: [],
            yearly_trend: []
          },
          items: [],
          incomeChart: null,
          expenseChart: null,
          trendChart: null,

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
                this.items = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
              });
          },

          renderCharts() {
            if (this.incomeChart) this.incomeChart.destroy();
            if (this.expenseChart) this.expenseChart.destroy();
            if (this.trendChart) this.trendChart.destroy();

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
                    backgroundColor: ['#636363', '#024ad8', '#ff5050', '#ff5050', '#636363']
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
                    backgroundColor: ['#ff5050', '#ff5050', '#ff5050', '#636363', '#024ad8']
                  }]
                },
                options: {
                  responsive: true
                }
              });
            }

            const trendCtx = document.getElementById('publicTrendChart');
            if (trendCtx && this.summary.yearly_trend.length > 0) {
              const years = [...new Set(this.summary.yearly_trend.map(i => i.year))].sort();
              const incomeMap = {};
              const expenseMap = {};
              this.summary.yearly_trend.forEach(item => {
                if (item.type === 'income') {
                  incomeMap[item.year] = item.total_realization;
                } else if (item.type === 'expense') {
                  expenseMap[item.year] = item.total_realization;
                }
              });
              const incomeData = years.map(y => incomeMap[y] || 0);
              const expenseData = years.map(y => expenseMap[y] || 0);

              this.trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                  labels: years,
                  datasets: [{
                      label: 'Pendapatan',
                      data: incomeData,
                      borderColor: '#1f6b3c',
                      backgroundColor: 'rgba(22, 163, 74, 0.1)',
                      borderWidth: 2,
                      tension: 0.3,
                      fill: true,
                      pointRadius: 3
                    },
                    {
                      label: 'Belanja',
                      data: expenseData,
                      borderColor: '#b3262b',
                      backgroundColor: 'rgba(220, 38, 38, 0.08)',
                      borderWidth: 2,
                      tension: 0.3,
                      fill: true,
                      pointRadius: 3
                    }
                  ]
                },
                options: {
                  responsive: true,
                  interaction: {
                    mode: 'index',
                    intersect: false
                  },
                  stacked: false,
                  plugins: {
                    legend: {
                      position: 'bottom'
                    },
                    tooltip: {
                      callbacks: {
                        label: function(context) {
                          const value = context.parsed.y || 0;
                          return context.dataset.label + ': ' + new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            maximumFractionDigits: 0
                          }).format(value);
                        }
                      }
                    }
                  },
                  scales: {
                    y: {
                      ticks: {
                        callback: function(value) {
                          return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            maximumFractionDigits: 0
                          }).format(value);
                        }
                      }
                    }
                  }
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

      <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e8e8e8; margin-bottom: 30px;">
        <button @click="tab = 'form'" :class="{'active': tab === 'form'}" class="wp-desa-tab-btn">
          <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width: 18px; height: 18px;'); ?> Buat Laporan
        </button>
        <button @click="tab = 'track'" :class="{'active': tab === 'track'}" class="wp-desa-tab-btn">
          <?php echo \WpDesa\Frontend\Icons::svg('search', 'width: 18px; height: 18px;'); ?> Cek Status Laporan
        </button>
      </div>

      <div class="wp-desa-content">
        <!-- Form Aduan -->
        <div x-show="tab === 'form'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
          <div x-show="message.content"
            style="padding: 15px; border-radius: 8px; margin-bottom: 20px;"
            :style="message.type === 'success' ? 'background: #e6f4ea; color: #1f6b3c; border: 1px solid #c3e6cb;' : 'background: #fce8e6; color: #b3262b; border: 1px solid #fecaca;'">
            <span x-text="message.content" style="font-weight: 500;"></span>
            <template x-if="trackingCode">
              <div style="margin-top: 15px; background: var(--canvas); padding: 15px; border-radius: 8px; border: 1px dashed #1f6b3c;">
                <div style="font-size: 0.9em; margin-bottom: 5px; color: #1f6b3c;">Kode Tracking Anda:</div>
                <div class="wp-desa-tracking-code" x-text="trackingCode" style="font-family: monospace; font-size: 1.5em; font-weight: 700; color: #1a1a1a; letter-spacing: 1px;"></div>
                <p class="wp-desa-helper" style="margin: 5px 0 0 0;">Simpan kode ini untuk mengecek status laporan.</p>
              </div>
            </template>
          </div>

          <form @submit.prevent="submitComplaint" enctype="multipart/form-data" style="background: var(--canvas); padding: var(--sp-xl); border-radius: var(--rounded-xl); box-shadow: var(--shadow-soft-lift); border: 1px solid var(--fog);">
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
              <div style="border: 2px dashed #c2c2c2; padding: 20px; border-radius: 8px; text-align: center; background: var(--cloud); transition: all 0.2s;" class="wp-desa-upload-area">
                <input type="file" @change="handleFileUpload" accept="image/*" class="wp-desa-input" style="border: none; padding: 0; background: transparent; width: auto;">
                <small class="wp-desa-helper">Format: JPG, PNG. Maks 2MB.</small>
              </div>
            </div>

            <button type="submit" :disabled="submitting" class="wp-desa-btn wp-desa-btn-primary" style="width: 100%;">
              <span x-show="!submitting">Kirim Laporan</span>
              <span x-show="submitting" style="display: flex; align-items: center; gap: 8px;">
                <?php echo \WpDesa\Frontend\Icons::svg('loader-2', 'animation: spin 2s linear infinite; width: 18px; height: 18px;'); ?> Mengirim...
              </span>
            </button>
          </form>
        </div>

        <!-- Tracking Form -->
        <div x-show="tab === 'track'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
          <form @submit.prevent="checkStatus" style="margin-bottom: 1.5rem; background: var(--canvas); padding: var(--sp-xl); border-radius: var(--rounded-xl); box-shadow: var(--shadow-soft-lift); border: 1px solid var(--fog);">
            <label class="wp-desa-label" style="margin-bottom: 12px;">Masukkan Kode Tracking</label>
            <div style="display: flex; gap: 10px;">
              <input type="text" x-model="trackCode" placeholder="Contoh: ADU-XXXXXX" required class="wp-desa-input" style="flex: 1; font-family: monospace; letter-spacing: 1px; font-weight: 600;">
              <button type="submit" :disabled="tracking" class="wp-desa-btn wp-desa-btn-primary" style="width: auto; min-width: 100px;">
                <span x-show="!tracking">Cek</span>
                <i x-show="tracking"><?php echo \WpDesa\Frontend\Icons::svg('loader-2', 'animation: spin 2s linear infinite; width: 18px; height: 18px;'); ?></i>
              </button>
            </div>
          </form>

          <div x-show="trackResult" class="wp-desa-result-card">
            <div style="text-align: center; margin-bottom: 20px;">
              <div style="width: 60px; height: 60px; background: #c9e0fc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: #024ad8;">
                <?php echo \WpDesa\Frontend\Icons::svg('clipboard-list', 'width: 30px; height: 30px;'); ?>
              </div>
              <h4 style="margin: 0; color: #1a1a1a; font-size: 1.2em;">Status Laporan</h4>
              <p style="margin: 5px 0 0 0; color: #636363; font-family: monospace;" x-text="trackResult.code"></p>
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
                style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e8e8e8; color: #3d3d3d;"
                :style="{'pending': 'background: #fef3c7; color: #92400e;', 'in_progress': 'background: #dbeafe; color: #1e40af;', 'resolved': 'background: #e6f4ea; color: #1f6b3c;', 'rejected': 'background: #fce8e6; color: #b3262b;'}[trackResult.status]">
              </span>
            </div>

            <template x-if="trackResult.response">
              <div style="margin-top: 20px; background: var(--cloud); padding: 15px; border-radius: 8px; border: 1px solid var(--fog);">
                <strong style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #1a1a1a;">
                  <?php echo \WpDesa\Frontend\Icons::svg('message-square-text', 'width: 18px; height: 18px;'); ?> Tanggapan Admin:
                </strong>
                <p style="margin: 0; color: #4b5563; line-height: 1.6;" x-text="trackResult.response"></p>
              </div>
            </template>
          </div>

          <div x-show="trackError" style="padding: 15px; background: #fce8e6; color: #b3262b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px;" x-text="trackError"></div>
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
    global $post;

    // Enqueue Alpine.js for frontend (always needed for interactive components)
    wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);
    // CDN fallback
    wp_add_inline_script('alpinejs', 'if(typeof Alpine==="undefined"){var e=document.createElement("script");e.src="' . WP_DESA_URL . 'assets/js/alpine.min.js";document.head.appendChild(e);}');

    add_filter('rocket_delay_js_exclusions', function ($excluded) {
      $excluded[] = 'alpinejs';
      $excluded[] = 'alpinejs@3.x.x/dist/cdn.min.js';
      return array_unique($excluded);
    });

    add_filter('rocket_exclude_defer_js', function ($excluded) {
      $excluded[] = 'alpinejs';
      $excluded[] = 'alpinejs@3.x.x/dist/cdn.min.js';
      return array_unique($excluded);
    });

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
    <div id="wp-desa-layanan" class="wp-desa-wrapper" x-data="layananSurat()">
      <!-- CSS moved to assets/css/frontend/style.css -->

      <div class="wp-desa-tabs" style="display: flex; border-bottom: 1px solid #e8e8e8; margin-bottom: 30px;">
        <button class="wp-desa-tab-btn" :class="{ 'active': tab === 'request' }" @click="tab = 'request'">
          <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width: 18px; height: 18px;'); ?> Buat Permohonan
        </button>
        <button class="wp-desa-tab-btn" :class="{ 'active': tab === 'tracking' }" @click="tab = 'tracking'">
          <?php echo \WpDesa\Frontend\Icons::svg('search', 'width: 18px; height: 18px;'); ?> Cek Status
        </button>
      </div>

      <!-- Request Form -->
      <div x-show="tab === 'request'">
        <div x-show="message.content" :style="message.type === 'success' ? 'background: #e6f4ea; color: #1f6b3c; border-color: #c3e6cb;' : 'background: #fce8e6; color: #b3262b; border-color: #fecaca;'" style="padding: 15px; border-radius: 8px; border: 1px solid; margin-bottom: 20px;" x-text="message.content"></div>

        <div x-show="trackingCode" style="background: #c9e0fc; border: 1px solid #bfdbfe; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
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
            <span :class="'wp-desa-badge wp-desa-badge-' + trackResult.status" x-text="formatStatus(trackResult.status)"
              style="padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; background: #e8e8e8; color: #3d3d3d;"
              :style="{'pending': 'background: #fef3c7; color: #92400e;', 'processed': 'background: #dbeafe; color: #1e40af;', 'ready': 'background: #e6f4ea; color: #1f6b3c;', 'completed': 'background: #d1fae5; color: #065f46;', 'rejected': 'background: #fce8e6; color: #b3262b;'}[trackResult.status]"></span>
          </div>
        </div>
        <div x-show="trackError" style="padding: 15px; background: #fce8e6; color: #b3262b; border: 1px solid #fecaca; border-radius: 8px; margin-top: 15px;" x-text="trackError"></div>
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
