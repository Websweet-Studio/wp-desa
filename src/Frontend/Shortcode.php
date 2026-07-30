<?php

namespace WpDesa\Frontend;

class Shortcode
{
  private static $instance_counts = [];

  private function instance_id($key)
  {
    if (!isset(self::$instance_counts[$key])) {
      self::$instance_counts[$key] = 0;
    }
    self::$instance_counts[$key]++;
    return $key . '-' . self::$instance_counts[$key];
  }

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
    add_shortcode('wp_desa_struktur', [$this, 'render_struktur']);
    add_shortcode('wp_desa_produk_hukum', [$this, 'render_produk_hukum_frontend']);
    add_shortcode('wp_desa_berita', [$this, 'render_berita']);
    add_shortcode('wp_desa_agenda', [$this, 'render_agenda']);
    add_shortcode('wp_desa_galeri', [$this, 'render_galeri']);
    add_shortcode('wp_desa_peta', [$this, 'render_peta']);
    add_shortcode('wp_desa_wisata', [$this, 'render_wisata']);
    add_shortcode('temadesa_jam_kerja', [$this, 'render_jam_kerja']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  public function render_statistik($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
      'hide_demografi' => 'no',
    ], $atts);

    $hide_demografi = $atts['hide_demografi'] === 'yes';

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
    $style = $atts['style'];
?>
    <div class="wp-desa-wrapper wp-desa-stats--<?php echo esc_attr($style); ?>">

      <?php
      $total_gender = $male_val + $female_val;
      $male_pct = $total_gender > 0 ? round(($male_val / $total_gender) * 100) : 0;
      $female_pct = $total_gender > 0 ? round(($female_val / $total_gender) * 100) : 0;

      $r = 60;
      $sw = 14;
      $c = 2 * M_PI * $r;
      $male_dash = ($male_pct / 100) * $c;
      $female_dash = ($female_pct / 100) * $c;
      ?>

      <?php if ($style === 'grid'): ?>
        <!-- ============ GRID STYLE ============ -->
        <div class="wp-desa-stats-grid wp-desa-stats-grid--2col">
          <div class="wp-desa-stat-card wp-desa-stat-card--grid">
            <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
              <?php echo \WpDesa\Frontend\Icons::svg('users', 'width:32px;height:32px'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($total_val); ?></div>
            <div class="wp-desa-stat-label">Total Penduduk</div>
          </div>

          <div class="wp-desa-stat-card wp-desa-stat-card--grid wp-desa-stat-card--doughnut">
            <h4 class="wp-desa-stat-card--grid__title">Komposisi Penduduk</h4>
            <div class="wp-desa-doughnut">
              <svg viewBox="0 0 160 160" class="wp-desa-doughnut-svg">
                <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#e8e8e8" stroke-width="<?php echo $sw; ?>" />
                <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#024ad8" stroke-width="<?php echo $sw; ?>"
                  stroke-dasharray="<?php echo round($male_dash, 1); ?> <?php echo round($c - $male_dash, 1); ?>" stroke-dashoffset="0"
                  stroke-linecap="butt" transform="rotate(-90 80 80)" />
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

          <div class="wp-desa-stat-card wp-desa-stat-card--grid">
            <div class="wp-desa-stat-icon" style="background: #e6f4ea; color: #1f6b3c;">
              <?php echo \WpDesa\Frontend\Icons::svg('home', 'width:32px;height:32px'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($families_val); ?></div>
            <div class="wp-desa-stat-label">Kepala Keluarga</div>
          </div>

          <div class="wp-desa-stat-card wp-desa-stat-card--grid wp-desa-stat-card--gender">
            <h4 class="wp-desa-stat-card--grid__title">Jenis Kelamin</h4>
            <div class="wp-desa-gender-split">
              <div class="wp-desa-gender-bar">
                <div class="wp-desa-gender-bar--male" style="flex:<?php echo $male_pct; ?>">
                  <span><?php echo $male_pct; ?>%</span>
                </div>
                <div class="wp-desa-gender-bar--female" style="flex:<?php echo $female_pct; ?>">
                  <span><?php echo $female_pct; ?>%</span>
                </div>
              </div>
              <div class="wp-desa-gender-labels">
                <span><b style="color:#024ad8"><?php echo number_format_i18n($male_val); ?></b> Laki-laki</span>
                <span><b style="color:#b3262b"><?php echo number_format_i18n($female_val); ?></b> Perempuan</span>
              </div>
            </div>
          </div>
        </div>

      <?php elseif ($style === 'cards'): ?>
        <!-- ============ CARDS STYLE ============ -->
        <div class="wp-desa-stats-grid wp-desa-stats--cards-list">
          <div class="wp-desa-stats--cards-item">
            <div class="wp-desa-stats--cards-item__icon" style="background: #c9e0fc; color: #024ad8;">
              <?php echo \WpDesa\Frontend\Icons::svg('users', 'width:28px;height:28px'); ?>
            </div>
            <div class="wp-desa-stats--cards-item__info">
              <div class="wp-desa-stats--cards-item__number"><?php echo number_format_i18n($total_val); ?></div>
              <div class="wp-desa-stats--cards-item__label">Total Penduduk</div>
            </div>
          </div>

          <div class="wp-desa-stats--cards-item">
            <div class="wp-desa-stats--cards-item__icon" style="background: #e6f4ea; color: #1f6b3c;">
              <?php echo \WpDesa\Frontend\Icons::svg('home', 'width:28px;height:28px'); ?>
            </div>
            <div class="wp-desa-stats--cards-item__info">
              <div class="wp-desa-stats--cards-item__number"><?php echo number_format_i18n($families_val); ?></div>
              <div class="wp-desa-stats--cards-item__label">Kepala Keluarga</div>
            </div>
          </div>

          <div class="wp-desa-stats--cards-item">
            <div class="wp-desa-stats--cards-item__icon" style="background: #c9e0fc; color: #024ad8;">
              <?php echo \WpDesa\Frontend\Icons::svg('mars', 'width:28px;height:28px'); ?>
            </div>
            <div class="wp-desa-stats--cards-item__info">
              <div class="wp-desa-stats--cards-item__number"><?php echo number_format_i18n($male_val); ?></div>
              <div class="wp-desa-stats--cards-item__label">Laki-laki</div>
            </div>
          </div>

          <div class="wp-desa-stats--cards-item">
            <div class="wp-desa-stats--cards-item__icon" style="background: #f9d4d2; color: #b3262b;">
              <?php echo \WpDesa\Frontend\Icons::svg('venus', 'width:28px;height:28px'); ?>
            </div>
            <div class="wp-desa-stats--cards-item__info">
              <div class="wp-desa-stats--cards-item__number"><?php echo number_format_i18n($female_val); ?></div>
              <div class="wp-desa-stats--cards-item__label">Perempuan</div>
            </div>
          </div>
        </div>

        <div class="wp-desa-chart-container" style="margin-bottom: var(--sp-xl);">
          <h3 class="wp-desa-chart-title" style="text-align: center;">Komposisi Penduduk</h3>
          <div class="wp-desa-doughnut">
            <svg viewBox="0 0 160 160" class="wp-desa-doughnut-svg">
              <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#e8e8e8" stroke-width="<?php echo $sw; ?>" />
              <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#024ad8" stroke-width="<?php echo $sw; ?>"
                stroke-dasharray="<?php echo round($male_dash, 1); ?> <?php echo round($c - $male_dash, 1); ?>" stroke-dashoffset="0"
                stroke-linecap="butt" transform="rotate(-90 80 80)" />
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

      <?php elseif ($style === 'minimal'): ?>
        <!-- ============ MINIMAL STYLE ============ -->
        <div class="wp-desa-stats--minimal-section">
          <div class="wp-desa-stats--minimal-row">
            <div class="wp-desa-stats--minimal-row__left">
              <span class="wp-desa-stats--minimal-row__icon" style="color:#024ad8;"><?php echo \WpDesa\Frontend\Icons::svg('users', 'width:20px;height:20px'); ?></span>
              <span class="wp-desa-stats--minimal-row__label">Total Penduduk</span>
            </div>
            <span class="wp-desa-stats--minimal-row__value"><?php echo number_format_i18n($total_val); ?></span>
          </div>
          <div class="wp-desa-stats--minimal-row">
            <div class="wp-desa-stats--minimal-row__left">
              <span class="wp-desa-stats--minimal-row__icon" style="color:#1f6b3c;"><?php echo \WpDesa\Frontend\Icons::svg('home', 'width:20px;height:20px'); ?></span>
              <span class="wp-desa-stats--minimal-row__label">Kepala Keluarga</span>
            </div>
            <span class="wp-desa-stats--minimal-row__value"><?php echo number_format_i18n($families_val); ?></span>
          </div>
          <div class="wp-desa-stats--minimal-row">
            <div class="wp-desa-stats--minimal-row__left">
              <span class="wp-desa-stats--minimal-row__icon" style="color:#024ad8;"><?php echo \WpDesa\Frontend\Icons::svg('mars', 'width:20px;height:20px'); ?></span>
              <span class="wp-desa-stats--minimal-row__label">Laki-laki</span>
            </div>
            <span class="wp-desa-stats--minimal-row__value"><?php echo number_format_i18n($male_val); ?></span>
          </div>
          <div class="wp-desa-stats--minimal-row">
            <div class="wp-desa-stats--minimal-row__left">
              <span class="wp-desa-stats--minimal-row__icon" style="color:#b3262b;"><?php echo \WpDesa\Frontend\Icons::svg('venus', 'width:20px;height:20px'); ?></span>
              <span class="wp-desa-stats--minimal-row__label">Perempuan</span>
            </div>
            <span class="wp-desa-stats--minimal-row__value"><?php echo number_format_i18n($female_val); ?></span>
          </div>

          <div class="wp-desa-stats--minimal-divider"></div>

          <div class="wp-desa-stats--minimal-row wp-desa-stats--minimal-row--pct">
            <div class="wp-desa-stats--minimal-row__left">
              <span class="wp-desa-stats--minimal-row__label">Komposisi Gender</span>
            </div>
            <span class="wp-desa-stats--minimal-row__value">
              <span style="color:#024ad8; font-weight:600;"><?php echo $male_pct; ?>%</span>
              <span style="color:var(--steel); margin: 0 4px;">/</span>
              <span style="color:#b3262b; font-weight:600;"><?php echo $female_pct; ?>%</span>
            </span>
          </div>
        </div>

      <?php else: ?><!-- CLASSIC (default) -->
        <div class="wp-desa-chart-container">
          <h3 style="text-align: center; margin-top: 0; color: #1a1a1a; font-size: 1.1em; margin-bottom: 15px;">Komposisi Penduduk</h3>
          <div class="wp-desa-doughnut">
            <svg viewBox="0 0 160 160" class="wp-desa-doughnut-svg">
              <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#e8e8e8" stroke-width="<?php echo $sw; ?>" />
              <circle cx="80" cy="80" r="<?php echo $r; ?>" fill="none" stroke="#024ad8" stroke-width="<?php echo $sw; ?>"
                stroke-dasharray="<?php echo round($male_dash, 1); ?> <?php echo round($c - $male_dash, 1); ?>" stroke-dashoffset="0"
                stroke-linecap="butt" transform="rotate(-90 80 80)" />
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
          <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
              <?php echo \WpDesa\Frontend\Icons::svg('users', 'width:24px;height:24px'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($total_val); ?></div>
            <div class="wp-desa-stat-label">Total Penduduk</div>
          </div>

          <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-icon" style="background: #e6f4ea; color: #1f6b3c;">
              <?php echo \WpDesa\Frontend\Icons::svg('home', 'width: 24px; height: 24px;'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($families_val); ?></div>
            <div class="wp-desa-stat-label">Kepala Keluarga</div>
          </div>

          <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-icon" style="background: #c9e0fc; color: #024ad8;">
              <?php echo \WpDesa\Frontend\Icons::svg('mars', 'width: 24px; height: 24px;'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($male_val); ?></div>
            <div class="wp-desa-stat-label">Laki-laki</div>
          </div>

          <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-icon" style="background: #f9d4d2; color: #b3262b;">
              <?php echo \WpDesa\Frontend\Icons::svg('venus', 'width: 24px; height: 24px;'); ?>
            </div>
            <div class="wp-desa-stat-number"><?php echo number_format_i18n($female_val); ?></div>
            <div class="wp-desa-stat-label">Perempuan</div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!$hide_demografi): ?>
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
      <?php endif; ?>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_umkm($atts)
  {
    $atts = shortcode_atts([
      'limit' => 6,
      'cols' => 3,
      'style' => 'classic',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['classic', 'compact', 'minimal'])) {
      $style = 'classic';
    }

    $query = new \WP_Query([
      'post_type' => 'desa_umkm',
      'posts_per_page' => $atts['limit'],
      'status' => 'publish'
    ]);

    ob_start();
  ?>
    <div class="wp-desa-wrapper wp-desa-umkm--<?php echo esc_attr($style); ?>">
      <?php if ($query->have_posts()): ?>

        <?php if ($style !== 'minimal'): ?>
          <?php
          // Classic / Compact shared template with size variables
          $img_h    = $style === 'compact' ? 120 : 200;
          $pad      = $style === 'compact' ? 'var(--sp-md)' : 'var(--sp-xl)';
          $title_sz = $style === 'compact' ? 16 : 20;
          $excerpt_w = $style === 'compact' ? 10 : 15;
          $gap      = $style === 'compact' ? '15px' : '25px';
          $min_w    = $style === 'compact' ? '240px' : '280px';
          $rad      = $style === 'compact' ? 'var(--rounded-lg)' : 'var(--rounded-xl)';
          $icon_sz  = $style === 'compact' ? 32 : 48;
          $txt_sz   = $style === 'compact' ? 13 : 14;
          $badge_sz = $style === 'compact' ? 11 : 12;
          $btn_sz   = $style === 'compact' ? 12 : 14;
          $btn_pad  = $style === 'compact' ? '6px 12px' : '8px 16px';
          ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(<?php echo $min_w; ?>,1fr));gap:<?php echo $gap; ?>;">
            <?php while ($query->have_posts()): $query->the_post();
              $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
              $location = get_post_meta(get_the_ID(), '_desa_umkm_location', true);
              $categories = get_the_terms(get_the_ID(), 'desa_umkm_cat');
              $cat_name = !empty($categories) ? $categories[0]->name : 'UMKM';
            ?>
              <div class="wp-desa-stat-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;text-align:left;border:1px solid var(--fog);background:var(--canvas);border-radius:<?php echo $rad; ?>;">
                <div style="height:<?php echo $img_h; ?>px;background:var(--cloud);overflow:hidden;position:relative;">
                  <div style="position:absolute;top:<?php echo $style === 'compact' ? '6px' : 'var(--sp-sm)'; ?>;right:<?php echo $style === 'compact' ? '6px' : 'var(--sp-sm)'; ?>;background:rgba(255,255,255,0.9);padding:<?php echo $style === 'compact' ? '2px 8px' : '4px 10px'; ?>;border-radius:var(--rounded-pill);font-size:<?php echo $badge_sz; ?>px;font-weight:600;color:var(--ink);z-index:2;">
                    <?php echo esc_html($cat_name); ?>
                  </div>
                  <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" style="display:block;width:100%;height:100%;">
                      <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                    </a>
                  <?php else: ?>
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--graphite);background:var(--cloud);">
                      <?php echo \WpDesa\Frontend\Icons::svg('store', 'width:' . $icon_sz . 'px;height:' . $icon_sz . 'px;'); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div style="padding:<?php echo $pad; ?>;flex:1;display:flex;flex-direction:column;">
                  <h3 style="margin:0 0 <?php echo $style === 'compact' ? '4px' : 'var(--sp-xs)'; ?> 0;font-family:var(--font-display);font-size:<?php echo $title_sz; ?>px;font-weight:500;line-height:1.0;">
                    <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:var(--ink);"><?php the_title(); ?></a>
                  </h3>
                  <div style="font-size:<?php echo $txt_sz; ?>px;color:var(--graphite);margin-bottom:<?php echo $style === 'compact' ? 'var(--sp-sm)' : 'var(--sp-lg)'; ?>;flex:1;line-height:1.5;">
                    <?php echo wp_trim_words(get_the_excerpt(), $excerpt_w); ?>
                  </div>
                  <div style="border-top:1px solid var(--fog);padding-top:var(--sp-sm);margin-top:auto;display:flex;justify-content:space-between;align-items:center;">
                    <a href="<?php the_permalink(); ?>" style="font-size:<?php echo $txt_sz; ?>px;font-weight:500;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:4px;">
                      Detail <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width:16px;height:16px;'); ?>
                    </a>
                    <?php if ($phone):
                      $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                      if (substr($clean_phone, 0, 1) == '0') $clean_phone = '62' . substr($clean_phone, 1);
                    ?>
                      <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" target="_blank" style="background:#25D366;color:#fff;border:none;font-size:<?php echo $btn_sz; ?>px;font-weight:700;display:inline-flex;align-items:center;gap:6px;padding:<?php echo $btn_pad; ?>;border-radius:var(--rounded-md);text-decoration:none;">
                        <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width:16px;height:16px;'); ?> Chat
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

        <?php else: /* minimal */ ?>
          <div style="border:1px solid var(--fog);border-radius:var(--rounded-lg);overflow:hidden;">
            <?php while ($query->have_posts()): $query->the_post();
              $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
              $categories = get_the_terms(get_the_ID(), 'desa_umkm_cat');
              $cat_name = !empty($categories) ? $categories[0]->name : 'UMKM';
            ?>
              <div style="display:flex;align-items:center;gap:var(--sp-md);padding:var(--sp-md);border-bottom:1px solid var(--fog);background:var(--canvas);">
                <div style="flex-shrink:0;">
                  <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('thumbnail', ['style' => 'width:48px;height:48px;border-radius:var(--rounded-md);object-fit:cover;display:block;']); ?>
                  <?php else: ?>
                    <div style="width:48px;height:48px;border-radius:var(--rounded-md);background:var(--cloud);display:flex;align-items:center;justify-content:center;color:var(--graphite);">
                      <?php echo \WpDesa\Frontend\Icons::svg('store', 'width:24px;height:24px;'); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0;">
                  <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:15px;color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  <div style="font-size:12px;color:var(--graphite);margin-top:2px;">
                    <span style="background:var(--primary-soft);color:var(--primary-deep);padding:1px 8px;border-radius:var(--rounded-pill);font-size:11px;"><?php echo esc_html($cat_name); ?></span>
                    <?php if ($location): ?>
                      <span style="margin-left:var(--sp-xs);">📍 <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <?php if ($phone): ?>
                  <a href="https://wa.me/<?php echo esc_attr(preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $phone))); ?>" target="_blank" style="background:#25D366;color:#fff;border:none;font-size:12px;font-weight:700;padding:6px 12px;border-radius:var(--rounded-md);text-decoration:none;flex-shrink:0;">
                    <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width:14px;height:14px;'); ?> Chat
                  </a>
                <?php endif; ?>
                <a href="<?php the_permalink(); ?>" style="font-size:13px;font-weight:500;color:var(--primary);text-decoration:none;flex-shrink:0;">Detail →</a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; /* style end */ ?>

      <?php else: ?>
        <div style="text-align:center;padding:60px 20px;background:var(--cloud);border-radius:var(--rounded-xl);border:1px solid var(--fog);color:var(--graphite);">
          <?php echo \WpDesa\Frontend\Icons::svg('store', 'width:48px;height:48px;margin-bottom:10px;'); ?>
          <p style="margin:0;font-size:1.1em;">Belum ada data UMKM yang ditampilkan.</p>
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
      'id' => 0,
      'style' => 'full',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['full', 'compact', 'minimal'])) {
      $style = 'full';
    }

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

    $is_full    = $style === 'full';
    $is_compact = $style === 'compact';
    $is_minimal = $style === 'minimal';

    $img_h   = $is_compact ? 200 : 400;
    $pad     = $is_compact || $is_minimal ? 'var(--sp-lg)' : 'var(--sp-xl)';
    $title_sz = $is_minimal ? 22 : ($is_compact ? 26 : 32);

    ob_start();
  ?>
    <div class="wp-desa-single-umkm wp-desa-wrapper wp-desa-umkm-single--<?php echo esc_attr($style); ?>">

      <?php if ($is_minimal): ?>
        <!-- Minimal: single column, no header image, inline contact -->
        <div style="border-bottom:1px solid var(--fog);padding-bottom:var(--sp-lg);margin-bottom:var(--sp-lg);">
          <h1 style="margin:0 0 var(--sp-xs);color:var(--ink);font-size:<?php echo $title_sz; ?>px;font-weight:500;">
            <?php echo esc_html($post->post_title); ?>
          </h1>
          <div style="display:flex;flex-wrap:wrap;gap:var(--sp-xs);font-size:13px;color:var(--graphite);align-items:center;">
            <span style="background:var(--primary-soft);color:var(--primary-deep);padding:2px 10px;border-radius:var(--rounded-pill);font-weight:500;"><?php echo esc_html($cat_name); ?></span>
            <?php if ($phone): ?>
              <a href="https://wa.me/<?php echo esc_attr(preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $phone))); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:4px;color:#25D366;text-decoration:none;font-weight:500;">
                <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width:14px;height:14px;'); ?> WhatsApp
              </a>
            <?php endif; ?>
            <?php if ($location): ?>
              <span>📍 <?php echo esc_html($location); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div style="color:var(--graphite);line-height:1.8;font-size:15px;">
          <?php echo wpautop($post->post_content); ?>
        </div>

        <?php if ($gallery_ids): $ids = explode(',', $gallery_ids); ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;margin-top:var(--sp-lg);">
            <?php foreach ($ids as $id):
              $img = wp_get_attachment_image_url($id, 'thumbnail');
              $full = wp_get_attachment_image_url($id, 'full');
              if (!$img) continue;
            ?>
              <a href="<?php echo esc_url($full); ?>" class="glightbox" data-gallery="umkm-gallery" style="display:block;aspect-ratio:1;border-radius:var(--rounded-md);overflow:hidden;border:1px solid var(--fog);">
                <img src="<?php echo esc_url($img); ?>" style="width:100%;height:100%;object-fit:cover;">
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: /* full / compact */ ?>
        <div style="background:var(--canvas);border-radius:var(--rounded-xl);overflow:hidden;border:1px solid var(--fog);box-shadow:var(--shadow-soft-lift);">

          <!-- Header Image -->
          <?php if ($thumb_url): ?>
            <div style="width:100%;height:<?php echo $img_h; ?>px;overflow:hidden;position:relative;">
              <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="width:100%;height:100%;object-fit:cover;">
              <div style="position:absolute;top:20px;right:20px;background:var(--canvas);padding:6px 14px;border-radius:20px;font-weight:600;color:#3d3d3d;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <?php echo esc_html($cat_name); ?>
              </div>
            </div>
          <?php endif; ?>

          <div style="padding:<?php echo $pad; ?>;">
            <h1 style="margin:0 0 15px 0;color:#1a1a1a;font-size:<?php echo $title_sz; ?>px;font-weight:500;">
              <?php echo esc_html($post->post_title); ?>
            </h1>

            <div style="display:flex;flex-wrap:wrap;gap:40px;margin-top:30px;">
              <!-- Main -->
              <div style="flex:2;min-width:300px;">
                <div style="color:#3d3d3d;line-height:1.8;font-size:1.1em;">
                  <?php echo wpautop($post->post_content); ?>
                </div>

                <?php if ($gallery_ids): $ids = explode(',', $gallery_ids); ?>
                  <h3 style="margin:40px 0 20px;color:#1a1a1a;font-size:1.3em;display:flex;align-items:center;gap:10px;border-bottom:2px solid #f7f7f7;padding-bottom:10px;">
                    <?php echo \WpDesa\Frontend\Icons::svg('image', 'width:24px;height:24px;'); ?> Galeri Produk
                  </h3>
                  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:15px;">
                    <?php foreach ($ids as $id):
                      $img = wp_get_attachment_image_url($id, 'medium');
                      $full = wp_get_attachment_image_url($id, 'full');
                      if (!$img) continue;
                    ?>
                      <a href="<?php echo esc_url($full); ?>" class="glightbox" data-gallery="umkm-gallery" style="display:block;aspect-ratio:1;border-radius:var(--rounded-xl);overflow:hidden;border:1px solid var(--fog);position:relative;box-shadow:var(--shadow-soft-lift);">
                        <img src="<?php echo esc_url($img); ?>" style="width:100%;height:100%;object-fit:cover;">
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Sidebar -->
              <div style="flex:1;min-width:280px;">
                <div style="background:var(--cloud);padding:<?php echo $pad; ?>;border-radius:var(--rounded-xl);border:1px solid var(--fog);position:sticky;top:20px;">
                  <h3 style="margin-top:0;color:#1a1a1a;font-size:1.2em;margin-bottom:20px;border-bottom:1px solid #e8e8e8;padding-bottom:15px;font-weight:700;">Informasi Kontak</h3>

                  <?php if ($phone):
                    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                    if (substr($clean_phone, 0, 1) == '0') $clean_phone = '62' . substr($clean_phone, 1);
                  ?>
                    <div style="margin-bottom:25px;">
                      <div style="font-size:0.9em;color:#636363;margin-bottom:8px;font-weight:600;">WhatsApp</div>
                      <a href="https://wa.me/<?php echo esc_attr($clean_phone); ?>" target="_blank" style="display:flex;align-items:center;gap:10px;text-decoration:none;background:#e6f4ea;color:#1f6b3c;padding:12px 15px;border-radius:8px;font-weight:600;justify-content:center;">
                        <?php echo \WpDesa\Frontend\Icons::svg('message-circle', 'width:20px;height:20px;'); ?>
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
                    <div style="margin-bottom:25px;">
                      <div style="font-size:0.9em;color:#636363;margin-bottom:8px;font-weight:600;">Lokasi</div>
                      <div style="color:#1a1a1a;">
                        <?php if ($lat && $lon): ?>
                          <div style="border-radius:8px;overflow:hidden;border:1px solid var(--fog);">
                            <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                              src="https://maps.google.com/maps?q=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lon); ?>&hl=es&z=14&amp;output=embed">
                            </iframe>
                          </div>
                        <?php else: ?>
                          <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($location); ?>" target="_blank" style="font-size:0.9em;color:#024ad8;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                            Lihat di Google Maps <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width:14px;height:14px;'); ?>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Share -->
                  <div>
                    <div style="font-size:0.9em;color:#636363;margin-bottom:10px;font-weight:600;">Bagikan</div>
                    <div style="display:flex;gap:10px;">
                      <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink($post_id); ?>" target="_blank" style="width:40px;height:40px;background:#1877f2;color:white;display:flex;align-items:center;justify-content:center;border-radius:50%;text-decoration:none;">
                        <?php echo \WpDesa\Frontend\Icons::svg('facebook', 'width:20px;height:20px;'); ?>
                      </a>
                      <a href="https://twitter.com/intent/tweet?url=<?php the_permalink($post_id); ?>&text=<?php echo urlencode($post->post_title); ?>" target="_blank" style="width:40px;height:40px;background:#000;color:white;display:flex;align-items:center;justify-content:center;border-radius:50%;text-decoration:none;">
                        <?php echo \WpDesa\Frontend\Icons::svg('twitter', 'width:20px;height:20px;'); ?>
                      </a>
                      <button onclick="navigator.clipboard.writeText('<?php the_permalink($post_id); ?>');alert('Link disalin!');" style="width:40px;height:40px;background:#636363;color:white;display:flex;align-items:center;justify-content:center;border-radius:50%;border:none;cursor:pointer;">
                        <?php echo \WpDesa\Frontend\Icons::svg('link', 'width:20px;height:20px;'); ?>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; /* style end */ ?>

    </div>
  <?php
    return ob_get_clean();
  }

  public function render_potensi($atts)
  {
    $atts = shortcode_atts([
      'limit' => 3,
      'style' => 'classic',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['classic', 'compact', 'minimal'])) {
      $style = 'classic';
    }

    $query = new \WP_Query([
      'post_type' => 'desa_potensi',
      'posts_per_page' => $atts['limit'],
      'status' => 'publish'
    ]);

    ob_start();
  ?>
    <div class="wp-desa-wrapper wp-desa-potensi--<?php echo esc_attr($style); ?>">
      <?php if ($query->have_posts()): ?>

        <?php if ($style !== 'minimal'): ?>
          <?php
          $img_h     = $style === 'compact' ? 120 : 200;
          $pad       = $style === 'compact' ? 'var(--sp-md)' : 'var(--sp-xl)';
          $title_sz  = $style === 'compact' ? 16 : 20;
          $excerpt_w = $style === 'compact' ? 10 : 20;
          $gap       = $style === 'compact' ? '15px' : '25px';
          $min_w     = $style === 'compact' ? '240px' : '300px';
          $txt_sz    = $style === 'compact' ? 13 : 14;
          $icon_sz   = $style === 'compact' ? 32 : 48;
          ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(<?php echo $min_w; ?>,1fr));gap:<?php echo $gap; ?>;">
            <?php while ($query->have_posts()): $query->the_post(); ?>
              <div class="wp-desa-stat-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;text-align:left;border:1px solid var(--fog);background:var(--canvas);border-radius:var(--rounded-xl);">
                <div style="height:<?php echo $img_h; ?>px;background:var(--cloud);overflow:hidden;position:relative;">
                  <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" style="display:block;width:100%;height:100%;">
                      <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                    </a>
                  <?php else: ?>
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--graphite);background:var(--cloud);">
                      <?php echo \WpDesa\Frontend\Icons::svg('carrot', 'width:' . $icon_sz . 'px;height:' . $icon_sz . 'px;'); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div style="padding:<?php echo $pad; ?>;flex:1;display:flex;flex-direction:column;">
                  <h3 style="margin:0 0 <?php echo $style === 'compact' ? '4px' : 'var(--sp-xs)'; ?> 0;font-family:var(--font-display);font-size:<?php echo $title_sz; ?>px;font-weight:500;">
                    <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:var(--ink);"><?php the_title(); ?></a>
                  </h3>
                  <div style="font-size:<?php echo $txt_sz; ?>px;color:var(--graphite);margin-bottom:<?php echo $style === 'compact' ? 'var(--sp-sm)' : 'var(--sp-lg)'; ?>;flex:1;line-height:1.5;">
                    <?php echo wp_trim_words(get_the_excerpt(), $excerpt_w); ?>
                  </div>
                  <a href="<?php the_permalink(); ?>" style="font-size:<?php echo $txt_sz; ?>px;font-weight:500;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:4px;margin-top:auto;">
                    Baca Selengkapnya <?php echo \WpDesa\Frontend\Icons::svg('arrow-right', 'width:16px;height:16px;'); ?>
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

        <?php else: /* minimal */ ?>
          <div style="border:1px solid var(--fog);border-radius:var(--rounded-lg);overflow:hidden;">
            <?php while ($query->have_posts()): $query->the_post(); ?>
              <div style="display:flex;align-items:center;gap:var(--sp-md);padding:var(--sp-md);border-bottom:1px solid var(--fog);background:var(--canvas);">
                <div style="flex-shrink:0;">
                  <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('thumbnail', ['style' => 'width:48px;height:48px;object-fit:cover;border-radius:var(--rounded-md);display:block;']); ?>
                  <?php else: ?>
                    <div style="width:48px;height:48px;border-radius:var(--rounded-md);background:var(--cloud);display:flex;align-items:center;justify-content:center;color:var(--graphite);">
                      <?php echo \WpDesa\Frontend\Icons::svg('carrot', 'width:24px;height:24px;'); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0;">
                  <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:15px;color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  <div style="font-size:13px;color:var(--graphite);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                    <?php echo wp_trim_words(get_the_excerpt(), 8); ?>
                  </div>
                </div>
                <a href="<?php the_permalink(); ?>" style="font-size:13px;font-weight:500;color:var(--primary);text-decoration:none;flex-shrink:0;">Baca →</a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; /* style end */ ?>

      <?php else: ?>
        <div style="text-align:center;padding:60px 20px;background:var(--cloud);border-radius:var(--rounded-xl);border:1px solid var(--fog);color:var(--graphite);">
          <?php echo \WpDesa\Frontend\Icons::svg('carrot', 'width:48px;height:48px;margin-bottom:10px;'); ?>
          <p style="margin:0;font-size:1.1em;">Belum ada data Potensi Desa.</p>
        </div>
      <?php endif;
      wp_reset_postdata(); ?>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_profil($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['classic', 'horizontal', 'minimal'])) {
      $style = 'classic';
    }

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
    <div class="wp-desa-wrapper wp-desa-profil--<?php echo esc_attr($style); ?>">

      <?php if ($style === 'classic'): ?>
        <!-- Classic (default) -->
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

      <?php elseif ($style === 'horizontal'): ?>
        <!-- Horizontal: logo kiri, teks kanan -->
        <div class="wp-desa-profil-card" style="display:flex;align-items:center;gap:var(--sp-xl);padding:var(--sp-xl);">
          <?php if ($logo): ?>
            <div style="flex-shrink:0;text-align:center;">
              <img src="<?php echo esc_url($logo); ?>" alt="Logo Kabupaten" style="width:80px;height:auto;">
            </div>
          <?php endif; ?>
          <div style="flex:1;min-width:0;">
            <h2 style="margin:0;font-family:var(--font-display);font-size:22px;font-weight:500;color:var(--ink);"><?php echo esc_html('Desa ' . $nama_desa); ?></h2>
            <p style="margin:4px 0 var(--sp-sm);color:var(--graphite);font-size:14px;">
              <?php echo esc_html('Kecamatan ' . $nama_kecamatan . ', ' . $nama_kabupaten); ?>
            </p>
            <div style="display:flex;flex-direction:column;gap:var(--sp-xs);">
              <?php if ($alamat): ?>
                <div style="display:flex;align-items:center;gap:var(--sp-xs);font-size:14px;color:var(--ink);">
                  <?php echo \WpDesa\Frontend\Icons::svg('map-pin', 'width:16px;height:16px;flex-shrink:0;'); ?>
                  <span><?php echo esc_html($alamat); ?></span>
                </div>
              <?php endif; ?>
              <?php if ($email): ?>
                <div style="display:flex;align-items:center;gap:var(--sp-xs);font-size:14px;">
                  <?php echo \WpDesa\Frontend\Icons::svg('mail', 'width:16px;height:16px;flex-shrink:0;'); ?>
                  <a href="mailto:<?php echo esc_attr($email); ?>" style="color:var(--primary);text-decoration:none;"><?php echo esc_html($email); ?></a>
                </div>
              <?php endif; ?>
              <?php if ($telepon): ?>
                <div style="display:flex;align-items:center;gap:var(--sp-xs);font-size:14px;">
                  <?php echo \WpDesa\Frontend\Icons::svg('phone', 'width:16px;height:16px;flex-shrink:0;'); ?>
                  <a href="tel:<?php echo esc_attr($telepon); ?>" style="color:var(--primary);text-decoration:none;"><?php echo esc_html($telepon); ?></a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      <?php elseif ($style === 'minimal'): ?>
        <!-- Minimal: baris border-bottom dengan flex-wrap -->
        <div style="display:flex;align-items:center;gap:var(--sp-md);flex-wrap:wrap;padding:var(--sp-md) 0;border-bottom:1px solid var(--fog);">
          <div style="display:flex;align-items:center;gap:var(--sp-xs);flex-shrink:0;">
            <?php if ($logo): ?>
              <img src="<?php echo esc_url($logo); ?>" alt="Logo" style="width:28px;height:28px;object-fit:contain;">
            <?php endif; ?>
            <span style="font-weight:600;font-size:15px;color:var(--ink);"><?php echo esc_html('Desa ' . $nama_desa); ?></span>
            <span style="color:var(--graphite);font-size:13px;">— <?php echo esc_html(($nama_kecamatan ? 'Kec. ' . $nama_kecamatan . ', ' : '') . $nama_kabupaten); ?></span>
          </div>
          <?php if ($alamat): ?>
            <span style="display:flex;align-items:center;gap:4px;font-size:13px;color:var(--graphite);">
              <?php echo \WpDesa\Frontend\Icons::svg('map-pin', 'width:14px;height:14px;'); ?>
              <?php echo esc_html($alamat); ?>
            </span>
          <?php endif; ?>
          <?php if ($email): ?>
            <a href="mailto:<?php echo esc_attr($email); ?>" style="display:flex;align-items:center;gap:4px;font-size:13px;color:var(--primary);text-decoration:none;">
              <?php echo \WpDesa\Frontend\Icons::svg('mail', 'width:14px;height:14px;'); ?>
              <?php echo esc_html($email); ?>
            </a>
          <?php endif; ?>
          <?php if ($telepon): ?>
            <a href="tel:<?php echo esc_attr($telepon); ?>" style="display:flex;align-items:center;gap:4px;font-size:13px;color:var(--primary);text-decoration:none;">
              <?php echo \WpDesa\Frontend\Icons::svg('phone', 'width:14px;height:14px;'); ?>
              <?php echo esc_html($telepon); ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  <?php
    return ob_get_clean();
  }

  public function render_kepala_desa($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'card',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['card', 'horizontal', 'minimal'])) {
      $style = 'card';
    }

    $settings = get_option('wp_desa_settings');
    if (!$settings) return '';

    $nama_kades = isset($settings['kepala_desa']) ? $settings['kepala_desa'] : '';
    $nip_kades = isset($settings['nip_kepala_desa']) ? $settings['nip_kepala_desa'] : '';
    $foto_kades = isset($settings['foto_kepala_desa']) ? $settings['foto_kepala_desa'] : '';
    $nama_desa = isset($settings['nama_desa']) ? $settings['nama_desa'] : 'Desa';

    if (!$nama_kades) return '';

    ob_start();
  ?>
    <div class="wp-desa-wrapper wp-desa-kades--<?php echo esc_attr($style); ?>">
      <div class="wp-desa-kades-card">
        <div class="wp-desa-kades-photo">
          <?php if ($foto_kades): ?>
            <img src="<?php echo esc_url($foto_kades); ?>" alt="Foto Kepala Desa">
          <?php else: ?>
            <div class="wp-desa-kades-photo-placeholder">
              <?php echo \WpDesa\Frontend\Icons::svg('user', 'width: 80px; height: 80px; color: var(--graphite);'); ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="wp-desa-kades-info">
          <h3 class="wp-desa-kades-name"><?php echo esc_html($nama_kades); ?></h3>
          <p class="wp-desa-kades-role">Kepala Desa <?php echo esc_html($nama_desa); ?></p>

          <?php if ($nip_kades): ?>
            <div class="wp-desa-kades-nip">NIP. <?php echo esc_html($nip_kades); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_bantuan($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['classic', 'compact', 'minimal'])) {
      $style = 'classic';
    }

    $uid = $this->instance_id('wp-desa-bantuan');

    ob_start();
  ?>
    <div id="<?php echo esc_attr($uid); ?>" class="wp-desa-wrapper wp-desa-bantuan--<?php echo esc_attr($style); ?>" data-wp-desa="bantuan" data-style="<?php echo esc_attr($style); ?>">
      <h2 class="wp-desa-title" style="text-align:center; margin-bottom: 30px; font-size: 2em; color: #1a1a1a;">Program & Bantuan Sosial</h2>
      <div style="display: grid; gap: 20px;" class="wp-desa-bantuan-grid">
      </div>
    </div>

    <!-- CSS moved to assets/css/frontend/style.css -->
  <?php
    return ob_get_clean();
  }

  public function render_keuangan($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
    ], $atts);

    $style = $atts['style'];
    if (!in_array($style, ['classic', 'compact', 'minimal'])) {
      $style = 'classic';
    }

    $uid = $this->instance_id('wp-desa-keuangan');

    ob_start();
  ?>
    <div id="<?php echo esc_attr($uid); ?>" class="wp-desa-wrapper wp-desa-keu--<?php echo esc_attr($style); ?>" data-wp-desa="keuangan">
      <div class="wp-desa-header">
        <div>
          <h2 class="wp-desa-title">Transparansi Keuangan</h2>
          <p class="wp-desa-subtitle">Ringkasan realisasi APBDes per tahun anggaran.</p>
        </div>
        <div class="wp-desa-filter">
          <label class="wp-desa-filter-label">Tahun Anggaran</label>
          <div class="wp-desa-filter-control">
            <select class="wp-desa-select wp-desa-select-year" id="<?php echo esc_attr($uid); ?>-year">
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
          <h3 class="wp-desa-stat-value" id="<?php echo esc_attr($uid); ?>-income-real"></h3>
          <div class="wp-desa-stat-sub">
            Target <span id="<?php echo esc_attr($uid); ?>-income-budget"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('shopping-cart', ''); ?>
          </div>
          <h4 class="wp-desa-stat-label">Total Belanja</h4>
          <h3 class="wp-desa-stat-value" id="<?php echo esc_attr($uid); ?>-expense-real"></h3>
          <div class="wp-desa-stat-sub">
            Pagu <span id="<?php echo esc_attr($uid); ?>-expense-budget"></span>
          </div>
        </div>

        <div class="wp-desa-stat-card wp-desa-stat-card-surplus">
          <div class="wp-desa-stat-icon-bg">
            <?php echo \WpDesa\Frontend\Icons::svg('trending-up', ''); ?>
          </div>
          <h4 class="wp-desa-stat-label">Sisa Lebih (SiLPA)</h4>
          <h3 class="wp-desa-stat-value" id="<?php echo esc_attr($uid); ?>-surplus"></h3>
          <div class="wp-desa-stat-sub wp-desa-stat-sub-muted">
            Realisasi pendapatan dikurangi belanja
          </div>
        </div>
      </div>

      <?php if ($style !== 'minimal'): ?>
        <div class="wp-desa-chart-wrapper">
          <div class="wp-desa-chart-container">
            <h4 class="wp-desa-chart-title">Sumber Pendapatan</h4>
            <div class="wp-desa-chart-box">
              <canvas id="<?php echo esc_attr($uid); ?>-income-chart"></canvas>
            </div>
          </div>
          <div class="wp-desa-chart-container">
            <h4 class="wp-desa-chart-title">Penggunaan Anggaran</h4>
            <div class="wp-desa-chart-box">
              <canvas id="<?php echo esc_attr($uid); ?>-expense-chart"></canvas>
            </div>
          </div>
          <div class="wp-desa-chart-container">
            <h4 class="wp-desa-chart-title">Tren Realisasi per Tahun</h4>
            <div class="wp-desa-chart-box">
              <canvas id="<?php echo esc_attr($uid); ?>-trend-chart"></canvas>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($style === 'classic'): ?>
        <div class="wp-desa-table-card">
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
              <tbody id="<?php echo esc_attr($uid); ?>-table-body">
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php
    return ob_get_clean();
  }

  public function render_aduan($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
      'view'  => 'form',
    ], $atts);

    $style      = $atts['style'];
    $view       = $atts['view'] === 'track' ? 'track' : 'form';
    $active_tab = $view;
    $is_classic = $style === 'classic';
    $is_compact = $style === 'compact';
    $is_minimal = $style === 'minimal';
    $is_simple  = $is_minimal;

    $uid = $this->instance_id('wp-desa-aduan');

    // classic: full card with pill tabs
    $card_open  = $is_classic ? '<div class="wp-desa-card" style="overflow:visible;">' : '';
    $card_close = $is_classic ? '</div>' : '';
    $tab_style  = $is_classic ? 'pill' : ($is_compact ? 'line' : false);

    // minimal: border-bottom input style
    $label_style = $is_minimal ? 'font-size:13px;' : '';
    $input_style = $is_minimal ? 'border:none;border-bottom:1px solid var(--fog);border-radius:0;padding:var(--sp-xs) 0;background:transparent;' : '';

    // Panel initial visibility
    if ($is_simple) {
      $form_hide  = $view !== 'form'  ? 'display:none;' : '';
      $track_hide = $view !== 'track' ? 'display:none;' : '';
    } elseif ($tab_style) {
      $form_hide  = $active_tab !== 'form'  ? 'display:none;' : '';
      $track_hide = $active_tab !== 'track' ? 'display:none;' : '';
    } else {
      $form_hide = $track_hide = '';
    }

    ob_start();
  ?>
    <div id="<?php echo esc_attr($uid); ?>" class="wp-desa-wrapper wp-desa-aduan--<?php echo esc_attr($style); ?>" data-wp-desa="aduan" data-active-tab="<?php echo esc_attr($active_tab); ?>">

      <?php echo $card_open; ?>

      <?php if ($tab_style === 'pill'): ?>
        <!-- Pill Tabs (classic) -->
        <div style="display:flex;align-items:center;gap:var(--sp-xs);padding:var(--sp-md) var(--sp-xl);border-bottom:1px solid var(--fog);">
          <h3 style="margin:0;font-size:20px;font-weight:500;color:var(--ink);margin-right:auto;">Laporan Aduan</h3>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'form' ? ' active' : ''; ?>" data-tab="form" style="padding:6px 14px;border:none;border-radius:9999px;font-size:14px;font-weight:500;cursor:pointer;background:<?php echo $active_tab === 'form' ? 'var(--ink)' : 'transparent'; ?>;color:<?php echo $active_tab === 'form' ? 'var(--on-ink)' : 'var(--ink)'; ?>;">
            <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width:18px;height:18px;'); ?> Buat Laporan
          </button>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'track' ? ' active' : ''; ?>" data-tab="track" style="padding:6px 14px;border:none;border-radius:9999px;font-size:14px;font-weight:500;cursor:pointer;background:<?php echo $active_tab === 'track' ? 'var(--ink)' : 'transparent'; ?>;color:<?php echo $active_tab === 'track' ? 'var(--on-ink)' : 'var(--ink)'; ?>;">
            <?php echo \WpDesa\Frontend\Icons::svg('search', 'width:18px;height:18px;'); ?> Cek Status
          </button>
        </div>
      <?php elseif ($tab_style === 'line'): ?>
        <!-- Line Tabs (compact) -->
        <div style="display:flex;align-items:stretch;gap:0;border-bottom:1px solid var(--fog);margin-bottom:var(--sp-lg);">
          <button class="wp-desa-tab-line<?php echo $active_tab === 'form' ? ' active' : ''; ?>" data-tab="form" style="padding:8px 16px;border:none;border-bottom:2px solid <?php echo $active_tab === 'form' ? 'var(--ink)' : 'transparent'; ?>;font-size:14px;font-weight:500;cursor:pointer;background:transparent;color:<?php echo $active_tab === 'form' ? 'var(--ink)' : 'var(--graphite)'; ?>;margin-bottom:-1px;">
            <?php echo \WpDesa\Frontend\Icons::svg('edit', 'width:16px;height:16px;'); ?> Buat Laporan
          </button>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'track' ? ' active' : ''; ?>" data-tab="track" style="padding:8px 16px;border:none;border-bottom:2px solid <?php echo $active_tab === 'track' ? 'var(--ink)' : 'transparent'; ?>;font-size:14px;font-weight:500;cursor:pointer;background:transparent;color:<?php echo $active_tab === 'track' ? 'var(--ink)' : 'var(--graphite)'; ?>;margin-bottom:-1px;">
            <?php echo \WpDesa\Frontend\Icons::svg('search', 'width:16px;height:16px;'); ?> Cek Status
          </button>
        </div>
      <?php elseif ($is_simple): ?>
        <?php if ($view === 'form'): ?>
          <h3 style="margin:0 0 var(--sp-md);font-size:18px;font-weight:500;color:var(--ink);">Laporan Aduan</h3>
        <?php else: ?>
          <h3 style="margin:0 0 var(--sp-md);font-size:18px;font-weight:500;color:var(--ink);">Cek Status Laporan</h3>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Form Aduan -->
      <div class="wp-desa-tab-panel" x-show="tab === 'form'" <?php echo $form_hide ? ' style="' . $form_hide . '"' : ''; ?>>
        <div x-show="message.content" class="wp-desa-message" style="display: none;"></div>

        <div x-show="trackingCode" class="wp-desa-tracking-box" style="display: none;">
          <div class="wp-desa-tracking-label">Kode Tracking Anda:</div>
          <div class="wp-desa-tracking-number" x-text="trackingCode"></div>
          <div class="wp-desa-tracking-note">Simpan kode ini untuk mengecek status laporan.</div>
        </div>

        <form enctype="multipart/form-data">
          <div style="padding:<?php echo $is_classic ? 'var(--sp-xl)' : '0'; ?>;">
            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Nama Pelapor (Opsional)</label>
              <input type="text" x-model="form.reporter_name" name="reporter_name" class="wp-desa-input" placeholder="Nama Anda (Boleh dikosongkan)" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Kontak (HP/Email)</label>
              <input type="text" x-model="form.reporter_contact" name="reporter_contact" class="wp-desa-input" placeholder="Untuk konfirmasi status" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Kategori Masalah</label>
              <select x-model="form.category" name="category" required class="wp-desa-select" style="<?php echo $input_style; ?>">
                <option value="">-- Pilih Kategori --</option>
                <option value="Infrastruktur">Infrastruktur (Jalan, Jembatan, dll)</option>
                <option value="Pelayanan Publik">Pelayanan Publik</option>
                <option value="Keamanan">Keamanan & Ketertiban</option>
                <option value="Kebersihan">Kebersihan & Lingkungan</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Judul Laporan</label>
              <input type="text" x-model="form.subject" name="subject" required class="wp-desa-input" placeholder="Ringkasan masalah" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Isi Laporan</label>
              <textarea x-model="form.description" name="description" required rows="5" class="wp-desa-textarea" placeholder="Jelaskan detail masalah, lokasi, dll" style="<?php echo $input_style; ?>"></textarea>
            </div>

            <div class="wp-desa-form-group" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Upload Foto Bukti</label>
              <div style="border: 2px dashed #c2c2c2; padding: 20px; border-radius: 8px; text-align: center; background: var(--cloud); transition: all 0.2s;" class="wp-desa-upload-area">
                <input type="file" name="photo" accept="image/*" class="wp-desa-input" style="border: none; padding: 0; background: transparent; width: auto;">
                <small class="wp-desa-helper">Format: JPG, PNG. Maks 2MB.</small>
              </div>
            </div>

            <div style="margin-top:<?php echo $is_classic ? 'var(--sp-lg)' : '0'; ?>;">
              <button type="submit" class="wp-desa-btn wp-desa-btn-primary" style="width:100%;font-size:14px;font-weight:600;letter-spacing:0.7px;text-transform:uppercase;">
                <span>Kirim Laporan</span>
                <span style="display: none;">Mengirim...</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Tracking Panel -->
      <div class="wp-desa-tab-panel" x-show="tab === 'track'" <?php echo $track_hide ? ' style="' . $track_hide . '"' : ''; ?>>
        <form>
          <div style="padding:<?php echo $is_classic ? 'var(--sp-xl)' : '0'; ?>;">
            <div class="wp-desa-form-group">
              <label class="wp-desa-label" style="margin-bottom:12px;">Masukkan Kode Tracking</label>
              <div class="wp-desa-tracking-input-group" style="display:flex;gap:var(--sp-xs);">
                <input type="text" x-model="trackCode" placeholder="Contoh: ADU-XXXXXX" required class="wp-desa-input" style="flex:1;font-family:monospace;letter-spacing:1px;font-weight:600;">
                <button type="submit" class="wp-desa-btn wp-desa-btn-primary" style="font-size:14px;font-weight:600;letter-spacing:0.7px;text-transform:uppercase;">
                  <span>Cek</span>
                  <span style="display: none;">...</span>
                </button>
              </div>
            </div>
          </div>
        </form>

        <div x-show="trackResult" class="wp-desa-result-card" style="display: none;">
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
            <span class="wp-desa-track-status wp-desa-status-badge"></span>
          </div>

          <div class="wp-desa-track-response" style="margin-top:20px;background:var(--cloud);padding:15px;border-radius:8px;border:1px solid var(--fog);display:none;">
            <strong style="display:flex;align-items:center;gap:6px;margin-bottom:8px;color:var(--ink);">
              <?php echo \WpDesa\Frontend\Icons::svg('message-square-text', 'width:18px;height:18px;'); ?> Tanggapan Admin:
            </strong>
            <p style="margin:0;color:#4b5563;line-height:1.6;"></p>
          </div>
        </div>

        <div x-show="trackError" class="wp-desa-error-box" style="display: none;"></div>
      </div>

      <?php echo $card_close; ?>
    </div>
  <?php
    return ob_get_clean();
  }

  public function enqueue_scripts()
  {
    global $post;

    // Enqueue Frontend JS (replaces Alpine.js, requires jQuery)
    $ver = (defined('WP_DEBUG') && WP_DEBUG) ? filemtime(WP_DESA_PATH . 'assets/js/wp-desa-frontend.js') : WP_DESA_VERSION;
    wp_enqueue_script('wp-desa-frontend', WP_DESA_URL . 'assets/js/wp-desa-frontend.js', ['jquery'], $ver, true);
    wp_add_inline_script('wp-desa-frontend', 'var wpDesaFrontend={restBase:"' . esc_url_raw(rest_url('wp-desa/v1')) . '"};', 'before');

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

  public function render_layanan($atts = [])
  {
    $atts = shortcode_atts([
      'style' => 'classic',
      'view'  => 'request',
    ], $atts);

    $style      = $atts['style'];
    $view       = $atts['view'] === 'tracking' ? 'tracking' : 'request';
    $active_tab = $view; // 'request' or 'tracking'
    $is_classic = $style === 'classic';
    $is_compact = $style === 'compact';
    $is_minimal = $style === 'minimal';
    $is_simple  = $is_minimal;

    $uid = $this->instance_id('wp-desa-layanan');
    ob_start();

    // classic: full card with pills, hairline border, no shadow
    $form_style   = '';
    $card_open    = '';
    $card_close   = '';
    $tab_style    = false; // false | 'pill' | 'line'
    $btn_class    = 'wp-desa-btn wp-desa-btn-primary';
    $btn_width    = '';

    if ($is_classic) {
      $card_open  = '<div class="wp-desa-card" style="overflow:visible;">';
      $card_close = '</div>';
      $tab_style  = 'pill';
      $btn_width  = 'width:100%;';
    } elseif ($is_compact) {
      $tab_style  = 'line';
      $btn_class  = 'wp-desa-btn wp-desa-btn-primary';
    } elseif ($is_minimal) {
      // underline fields
      $btn_class = 'wp-desa-btn wp-desa-btn-primary';
    }

    // minimal: border-bottom input style
    $label_style = $is_minimal ? 'font-size:13px;' : '';
    $input_style = $is_minimal ? 'border:none; border-bottom:1px solid var(--fog); border-radius:0; padding:var(--sp-xs) 0; background:transparent;' : '';
    $gap_class   = $is_simple ? 'wp-desa-form-grid--tight' : '';
  ?>
    <div id="<?php echo esc_attr($uid); ?>" class="wp-desa-wrapper wp-desa-layanan--<?php echo esc_attr($style); ?>" data-wp-desa="layanan" data-active-tab="<?php echo esc_attr($active_tab); ?>" x-ignore>

      <?php echo $card_open; ?>

      <?php if ($tab_style === 'pill'): ?>
        <!-- Pill Tabs (classic) -->
        <div style="display:flex;align-items:center;gap:var(--sp-xs);padding:var(--sp-md) var(--sp-xl);border-bottom:1px solid var(--fog);">
          <h3 style="margin:0;font-size:20px;font-weight:500;color:var(--ink);margin-right:auto;">Permohonan Surat</h3>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'request' ? ' active' : ''; ?>" data-tab="request" style="padding:6px 14px;border:none;border-radius:9999px;font-size:14px;font-weight:500;cursor:pointer;background:<?php echo $active_tab === 'request' ? 'var(--ink)' : 'transparent'; ?>;color:<?php echo $active_tab === 'request' ? 'var(--on-ink)' : 'var(--ink)'; ?>;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
              <path d="M11.35 22H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.706.706l3.588 3.588A2.4 2.4 0 0 1 20 8v5.35" />
              <path d="M14 2v5a1 1 0 0 0 1 1h5" />
              <path d="M14 19h6" />
              <path d="M17 16v6" />
            </svg>
            Formulir
          </button>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'tracking' ? ' active' : ''; ?>" data-tab="tracking" style="padding:6px 14px;border:none;border-radius:9999px;font-size:14px;font-weight:500;cursor:pointer;background:<?php echo $active_tab === 'tracking' ? 'var(--ink)' : 'transparent'; ?>;color:<?php echo $active_tab === 'tracking' ? 'var(--on-ink)' : 'var(--ink)'; ?>;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
              <path d="M11.35 22H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.706.706l3.588 3.588A2.4 2.4 0 0 1 20 8v5.35" />
              <path d="M14 2v5a1 1 0 0 0 1 1h5" />
              <path d="M14 19h6" />
              <path d="M17 16v6" />
            </svg>
            Cek Status
          </button>
        </div>
      <?php elseif ($tab_style === 'line'): ?>
        <!-- Line Tabs (compact) -->
        <div style="display:flex;align-items:stretch;gap:0;border-bottom:1px solid var(--fog);margin-bottom:var(--sp-lg);">
          <button class="wp-desa-tab-line<?php echo $active_tab === 'request' ? ' active' : ''; ?>" data-tab="request" style="padding:8px 16px;border:none;border-bottom:2px solid <?php echo $active_tab === 'request' ? 'var(--ink)' : 'transparent'; ?>;font-size:14px;font-weight:500;cursor:pointer;background:transparent;color:<?php echo $active_tab === 'request' ? 'var(--ink)' : 'var(--graphite)'; ?>;margin-bottom:-1px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
              <path d="M11.35 22H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.706.706l3.588 3.588A2.4 2.4 0 0 1 20 8v5.35" />
              <path d="M14 2v5a1 1 0 0 0 1 1h5" />
              <path d="M14 19h6" />
              <path d="M17 16v6" />
            </svg>
            Formulir
          </button>
          <button class="wp-desa-tab-line<?php echo $active_tab === 'tracking' ? ' active' : ''; ?>" data-tab="tracking" style="padding:8px 16px;border:none;border-bottom:2px solid <?php echo $active_tab === 'tracking' ? 'var(--ink)' : 'transparent'; ?>;font-size:14px;font-weight:500;cursor:pointer;background:transparent;color:<?php echo $active_tab === 'tracking' ? 'var(--ink)' : 'var(--graphite)'; ?>;margin-bottom:-1px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
              <path d="M11.35 22H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.706.706l3.588 3.588A2.4 2.4 0 0 1 20 8v5.35" />
              <path d="M14 2v5a1 1 0 0 0 1 1h5" />
              <path d="M14 19h6" />
              <path d="M17 16v6" />
            </svg>
            Cek Status
          </button>
        </div>
      <?php elseif ($is_simple): ?>
        <?php if ($view === 'request'): ?>
          <h3 style="margin:0 0 var(--sp-md);font-size:18px;font-weight:500;color:var(--ink);">Permohonan Surat</h3>
        <?php else: ?>
          <h3 style="margin:0 0 var(--sp-md);font-size:18px;font-weight:500;color:var(--ink);">Cek Status Permohonan</h3>
        <?php endif; ?>
      <?php endif; ?>

      <?php
      // Panel initial visibility
      if ($is_simple) {
        $req_hide   = $view !== 'request'   ? 'display:none;' : '';
        $track_hide = $view !== 'tracking'  ? 'display:none;' : '';
      } elseif ($tab_style) {
        $req_hide   = $active_tab !== 'request'  ? 'display:none;' : '';
        $track_hide = $active_tab !== 'tracking' ? 'display:none;' : '';
      } else {
        $req_hide = $track_hide = '';
      }
      ?>

      <!-- Request Form -->
      <div class="wp-desa-tab-panel" x-show="tab === 'request'" <?php echo $req_hide ? ' style="' . $req_hide . '"' : ''; ?>>
        <div x-show="message.content" class="wp-desa-message" style="display: none;"></div>

        <div x-show="trackingCode" class="wp-desa-tracking-box" style="display: none;">
          <div class="wp-desa-tracking-label">Kode Tracking Anda:</div>
          <div class="wp-desa-tracking-number" x-text="trackingCode"></div>
          <div class="wp-desa-tracking-note">Simpan kode ini untuk mengecek status permohonan.</div>
        </div>

        <form style="<?php echo $form_style; ?>">
          <div style="padding:<?php echo $is_classic ? 'var(--sp-xl)' : '0'; ?>;">
            <div class="wp-desa-form-group <?php echo $gap_class; ?>" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">NIK</label>
              <input type="text" x-model="form.nik" name="nik" class="wp-desa-input" required maxlength="16" placeholder="16 digit NIK" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group <?php echo $gap_class; ?>" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Nama Lengkap</label>
              <input type="text" x-model="form.name" name="name" class="wp-desa-input" required placeholder="Sesuai KTP" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group <?php echo $gap_class; ?>" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Nomor WhatsApp</label>
              <input type="text" x-model="form.phone" name="phone" class="wp-desa-input" required placeholder="08..." maxlength="15" style="<?php echo $input_style; ?>">
            </div>

            <div class="wp-desa-form-group <?php echo $gap_class; ?>" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Jenis Surat</label>
              <select x-model="form.letter_type_id" name="letter_type_id" class="wp-desa-select" required style="<?php echo $input_style; ?>">
                <option value="">Pilih Jenis Surat</option>
              </select>
              <small class="wp-desa-helper wp-desa-layanan-type-desc"></small>
            </div>

            <div class="wp-desa-form-group <?php echo $gap_class; ?>" style="<?php echo $is_minimal ? 'margin-bottom:var(--sp-md);' : ''; ?>">
              <label class="wp-desa-label" style="<?php echo $label_style; ?>">Keterangan / Keperluan</label>
              <textarea x-model="form.details" name="details" class="wp-desa-textarea" rows="3" placeholder="Jelaskan keperluan surat..." style="<?php echo $input_style; ?>"></textarea>
            </div>

            <div style="margin-top:<?php echo $is_classic ? 'var(--sp-lg)' : '0'; ?>;">
              <button type="submit" class="<?php echo $btn_class; ?>" style="<?php echo $btn_width; ?>font-size:14px;font-weight:600;letter-spacing:0.7px;text-transform:uppercase;">
                <span>Kirim Permohonan</span>
                <span style="display: none;">Mengirim...</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Tracking Form -->
      <div class="wp-desa-tab-panel" x-show="tab === 'tracking'" style="<?php echo $track_hide; ?>">
        <form style="<?php echo $form_style; ?>">
          <div style="padding:<?php echo $is_classic ? 'var(--sp-xl)' : '0'; ?>;">
            <div class="wp-desa-form-group">
              <label class="wp-desa-label">Masukkan Kode Tracking</label>
              <div class="wp-desa-tracking-input-group" style="display:flex;gap:var(--sp-xs);">
                <input type="text" x-model="trackCode" class="wp-desa-input" placeholder="Contoh: REQ-..." style="flex:1;">
                <button type="button" class="<?php echo $btn_class; ?>" style="font-size:14px;font-weight:600;letter-spacing:0.7px;text-transform:uppercase;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
                    <path d="m21 21-4.34-4.34" />
                    <circle cx="11" cy="11" r="8" />
                  </svg>
                  <span>Cek</span>
                  <span style="display: none;">...</span>
                </button>
              </div>
            </div>
          </div>
        </form>

        <div x-show="trackResult" class="wp-desa-result-card" style="display: none; margin-top: var(--sp-lg);">
          <div class="wp-desa-card-row">
            <span class="wp-desa-card-label">Nama Pengaju</span>
            <span class="wp-desa-card-value wp-desa-layanan-track-name"></span>
          </div>
          <div class="wp-desa-card-row">
            <span class="wp-desa-card-label">Tanggal</span>
            <span class="wp-desa-card-value wp-desa-layanan-track-date"></span>
          </div>
          <div class="wp-desa-card-row">
            <span class="wp-desa-card-label">Status</span>
            <span class="wp-desa-layanan-track-status wp-desa-status-badge"></span>
          </div>
        </div>
        <div x-show="trackError" class="wp-desa-error-box" style="display: none;"></div>
      </div>

      <?php echo $card_close; ?>

    </div><?php
          return ob_get_clean();
        }

        public function render_struktur($atts = [])
        {
          $atts = shortcode_atts([
            'style' => 'tree',
          ], $atts);

          global $wpdb;
          $table = $wpdb->prefix . 'desa_perangkat';
          $items = $wpdb->get_results("SELECT * FROM $table ORDER BY parent_id ASC, urutan ASC, id ASC");

          if (empty($items)) {
            return '<div style="text-align:center;padding:40px 20px;color:#94a3b8;">Belum ada data perangkat desa.</div>';
          }

          switch ($atts['style']) {
            case 'tabel':
            case 'table':
              return $this->render_struktur_tabel($items);
            case 'card':
            case 'cards':
              return $this->render_struktur_cards($items);
            case 'carousel':
              return $this->render_struktur_carousel($items);
            case 'list':
              return $this->render_struktur_list($items);
            case 'tree':
            default:
              return $this->render_struktur_tree($items);
          }
        }

        private function render_struktur_tree($items)
        {
          $tree = [];
          $by_id = [];
          foreach ($items as $item) {
            $item->children = [];
            $by_id[$item->id] = $item;
          }
          foreach ($items as $item) {
            if ($item->parent_id && isset($by_id[$item->parent_id])) {
              $by_id[$item->parent_id]->children[] = $item;
            } else {
              $tree[] = $item;
            }
          }

          ob_start();
          ?>
    <div class="wp-desa-struktur-wrapper" style="max-width: 100%; overflow-x: auto; padding: 20px 0;">
      <div class="wp-desa-struktur" style="display: flex; flex-direction: column; align-items: center; gap: 0;">
        <?php foreach ($tree as $root) : ?>
          <div class="wp-desa-struktur-node-root" style="text-align: center;">
            <?php echo $this->render_struktur_node_card($root); ?>
            <?php if ($root->children) : ?>
              <div class="wp-desa-struktur-children" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; padding-top: 20px; position: relative; border-top: 2px solid #e2e8f0; flex-wrap: wrap;">
                <?php foreach ($root->children as $child) : ?>
                  <div class="wp-desa-struktur-branch" style="display: flex; flex-direction: column; align-items: center; gap: 0;">
                    <div style="width: 2px; height: 20px; background: #e2e8f0;"></div>
                    <?php echo $this->render_struktur_node_card($child); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <style>
      .wp-desa-struktur-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        min-width: 180px;
        max-width: 220px;
        text-align: center;
        transition: box-shadow 0.2s;
      }

      .wp-desa-struktur-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      }

      .wp-desa-struktur-card .foto {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        background: #f0f0f0;
      }

      .wp-desa-struktur-card .foto-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        margin: 0 auto 10px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
      }

      .wp-desa-struktur-card .nama {
        font-weight: 600;
        font-size: 0.95em;
        color: #1e293b;
        margin-bottom: 4px;
      }

      .wp-desa-struktur-card .jabatan {
        font-size: 0.85em;
        font-weight: 500;
        color: #2563eb;
        margin-bottom: 4px;
      }

      .wp-desa-struktur-card .nip {
        font-size: 0.8em;
        color: #94a3b8;
      }
    </style>
  <?php
          return ob_get_clean();
        }

        private function render_struktur_node_card($item)
        {
          $foto = !empty($item->foto)
            ? '<img src="' . esc_url($item->foto) . '" class="foto" alt="' . esc_attr($item->nama) . '">'
            : '<div class="foto-placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>';

          $html = '<div class="wp-desa-struktur-card">';
          $html .= $foto;
          $html .= '<div class="nama">' . esc_html($item->nama) . '</div>';
          $html .= '<div class="jabatan">' . esc_html($item->jabatan) . '</div>';
          if (!empty($item->nip)) {
            $html .= '<div class="nip">NIP: ' . esc_html($item->nip) . '</div>';
          }
          $html .= '</div>';
          return $html;
        }

        private function render_struktur_tabel($items)
        {
          $no = 0;
          ob_start();
  ?>
    <div class="wp-desa-struktur-wrapper" style="overflow-x: auto; padding: 10px 0;">
      <table style="width:100%;border-collapse:collapse;font-size:0.95em;">
        <thead>
          <tr style="background:#1e293b;color:#fff;">
            <th style="padding:12px 16px;text-align:left;">No</th>
            <th style="padding:12px 16px;text-align:left;">Foto</th>
            <th style="padding:12px 16px;text-align:left;">Nama</th>
            <th style="padding:12px 16px;text-align:left;">Jabatan</th>
            <th style="padding:12px 16px;text-align:left;">NIP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item) : $no++; ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
              <td style="padding:10px 16px;"><?php echo $no; ?></td>
              <td style="padding:10px 16px;">
                <?php if (!empty($item->foto)) : ?>
                  <img src="<?php echo esc_url($item->foto); ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                <?php else : ?>
                  <span style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;background:#e2e8f0;border-radius:50%;color:#94a3b8;">?</span>
                <?php endif; ?>
              </td>
              <td style="padding:10px 16px;font-weight:600;"><?php echo esc_html($item->nama); ?></td>
              <td style="padding:10px 16px;"><span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:4px;font-size:0.85em;"><?php echo esc_html($item->jabatan); ?></span></td>
              <td style="padding:10px 16px;color:#64748b;"><?php echo !empty($item->nip) ? esc_html($item->nip) : '-'; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php
          return ob_get_clean();
        }

        private function render_struktur_cards($items)
        {
          ob_start();
  ?>
    <div class="wp-desa-struktur-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px;padding:10px 0;">
      <?php foreach ($items as $item) : ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;text-align:center;transition:box-shadow 0.2s;">
          <?php if (!empty($item->foto)) : ?>
            <img src="<?php echo esc_url($item->foto); ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;">
          <?php else : ?>
            <div style="width:80px;height:80px;border-radius:50%;margin:0 auto 12px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          <?php endif; ?>
          <div style="font-weight:600;font-size:1.05em;color:#1e293b;margin-bottom:4px;"><?php echo esc_html($item->nama); ?></div>
          <div style="font-size:0.85em;font-weight:500;color:#2563eb;margin-bottom:4px;"><?php echo esc_html($item->jabatan); ?></div>
          <?php if (!empty($item->nip)) : ?>
            <div style="font-size:0.8em;color:#94a3b8;">NIP: <?php echo esc_html($item->nip); ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php
          return ob_get_clean();
        }

        private function render_struktur_carousel($items)
        {
          $id = 'wp-desa-carousel-' . uniqid();
          ob_start();
  ?>
    <div class="wp-desa-carousel-wrapper" style="position:relative;padding:10px 40px;">
      <div id="<?php echo $id; ?>" class="wp-desa-carousel-track" style="display:flex;gap:24px;overflow-x:auto;scroll-behavior:smooth;scrollbar-width:none;padding:10px 4px;scroll-snap-type:x mandatory;">
        <?php foreach ($items as $item) : ?>
          <div style="flex:0 0 250px;scroll-snap-align:start;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;text-align:center;">
            <?php if (!empty($item->foto)) : ?>
              <img src="<?php echo esc_url($item->foto); ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;">
            <?php else : ?>
              <div style="width:80px;height:80px;border-radius:50%;margin:0 auto 12px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
              </div>
            <?php endif; ?>
            <div style="font-weight:600;font-size:1em;color:#1e293b;margin-bottom:4px;"><?php echo esc_html($item->nama); ?></div>
            <div style="font-size:0.85em;font-weight:500;color:#2563eb;"><?php echo esc_html($item->jabatan); ?></div>
            <?php if (!empty($item->nip)) : ?>
              <div style="font-size:0.8em;color:#94a3b8;margin-top:4px;"><?php echo esc_html($item->nip); ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="wp-desa-carousel-btn wp-desa-carousel-prev" style="position:absolute;top:50%;left:0;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;border:1px solid #e2e8f0;background:#fff;cursor:pointer;font-size:18px;line-height:1;z-index:2;box-shadow:0 2px 8px rgba(0,0,0,0.1);" onclick="document.getElementById('<?php echo $id; ?>').scrollBy({left:-290,behavior:'smooth'})">‹</button>
      <button type="button" class="wp-desa-carousel-btn wp-desa-carousel-next" style="position:absolute;top:50%;right:0;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;border:1px solid #e2e8f0;background:#fff;cursor:pointer;font-size:18px;line-height:1;z-index:2;box-shadow:0 2px 8px rgba(0,0,0,0.1);" onclick="document.getElementById('<?php echo $id; ?>').scrollBy({left:290,behavior:'smooth'})">›</button>
    </div>
  <?php
          return ob_get_clean();
        }

        private function render_struktur_list($items)
        {
          ob_start();
  ?>
    <div class="wp-desa-struktur-list" style="display:flex;flex-direction:column;gap:8px;padding:10px 0;">
      <?php foreach ($items as $item) : ?>
        <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;">
          <?php if (!empty($item->foto)) : ?>
            <img src="<?php echo esc_url($item->foto); ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0;">
          <?php else : ?>
            <div style="width:48px;height:48px;border-radius:50%;flex-shrink:0;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          <?php endif; ?>
          <div style="flex:1;">
            <div style="font-weight:600;color:#1e293b;"><?php echo esc_html($item->nama); ?></div>
            <div style="display:flex;gap:12px;font-size:0.85em;color:#64748b;margin-top:2px;">
              <span style="background:#dbeafe;color:#1e40af;padding:1px 8px;border-radius:4px;"><?php echo esc_html($item->jabatan); ?></span>
              <?php if (!empty($item->nip)) : ?><span>NIP: <?php echo esc_html($item->nip); ?></span><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
          return ob_get_clean();
        }

        public function render_produk_hukum_frontend($atts = [])
        {
          $atts = shortcode_atts([
            'limit' => 10,
            'category' => '',
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'compact', 'minimal'])) {
            $style = 'classic';
          }

          $args = [
            'post_type' => 'desa_produk_hukum',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'DESC',
          ];

          if (!empty($atts['category'])) {
            $args['tax_query'] = [
              [
                'taxonomy' => 'desa_produk_hukum_cat',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['category']),
              ],
            ];
          }

          $query = new \WP_Query($args);

          ob_start();
          if ($query->have_posts()) :
    ?>
      <div class="wp-desa-produk-hukum-frontend wp-desa-ph--<?php echo esc_attr($style); ?>">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <?php $cats = get_the_terms(get_the_ID(), 'desa_produk_hukum_cat'); ?>
          <?php if ($style === 'minimal'): ?>
            <div style="display:flex;align-items:center;gap:var(--sp-sm);padding:var(--sp-sm) 0;border-bottom:1px solid var(--fog);">
              <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:14px;color:var(--ink);text-decoration:none;flex:1;min-width:0;"><?php the_title(); ?></a>
              <?php if ($cats && !is_wp_error($cats)) : ?>
                <span style="background:var(--primary-soft);color:var(--primary-deep);padding:1px 8px;border-radius:4px;font-size:12px;white-space:nowrap;"><?php echo esc_html($cats[0]->name); ?></span>
              <?php endif; ?>
              <span style="font-size:12px;color:var(--graphite);white-space:nowrap;"><?php echo get_the_date(); ?></span>
              <a href="<?php the_permalink(); ?>" style="color:var(--primary);text-decoration:none;font-size:13px;font-weight:500;flex-shrink:0;">Baca →</a>
            </div>
          <?php elseif ($style === 'compact'): ?>
            <div style="display:flex;align-items:center;gap:var(--sp-sm);padding:var(--sp-sm);border:1px solid var(--fog);border-radius:var(--rounded-lg);background:var(--canvas);">
              <div style="flex-shrink:0;width:32px;height:32px;background:var(--primary-soft);border-radius:6px;display:flex;align-items:center;justify-content:center;">
                <?php echo \WpDesa\Frontend\Icons::svg('file-text', 'width:16px;height:16px;color:var(--primary-deep);'); ?>
              </div>
              <div style="flex:1;min-width:0;">
                <a href="<?php the_permalink(); ?>" style="font-weight:500;color:var(--ink);text-decoration:none;font-size:14px;"><?php the_title(); ?></a>
                <div style="display:flex;gap:var(--sp-xs);margin-top:2px;font-size:12px;color:var(--graphite);">
                  <?php if ($cats && !is_wp_error($cats)) : ?>
                    <span style="background:var(--primary-soft);color:var(--primary-deep);padding:1px 6px;border-radius:4px;"><?php echo esc_html($cats[0]->name); ?></span>
                  <?php endif; ?>
                  <span><?php echo get_the_date(); ?></span>
                </div>
              </div>
              <a href="<?php the_permalink(); ?>" style="color:var(--primary);text-decoration:none;font-size:13px;font-weight:500;flex-shrink:0;">Baca →</a>
            </div>
          <?php else: /* classic */ ?>
            <div style="display:flex;align-items:center;gap:var(--sp-md);padding:var(--sp-md);border:1px solid var(--fog);border-radius:var(--rounded-lg);background:var(--canvas);">
              <div style="flex-shrink:0;width:48px;height:48px;background:var(--primary-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <?php echo \WpDesa\Frontend\Icons::svg('file-text', 'width:24px;height:24px;color:var(--primary-deep);'); ?>
              </div>
              <div style="flex:1;min-width:0;">
                <a href="<?php the_permalink(); ?>" style="font-weight:600;color:var(--ink);text-decoration:none;font-size:1em;"><?php the_title(); ?></a>
                <div style="display:flex;gap:var(--sp-sm);margin-top:4px;font-size:0.85em;color:var(--graphite);">
                  <?php if ($cats && !is_wp_error($cats)) : ?>
                    <span style="background:var(--primary-soft);color:var(--primary-deep);padding:1px 8px;border-radius:4px;"><?php echo esc_html($cats[0]->name); ?></span>
                  <?php endif; ?>
                  <span><?php echo get_the_date(); ?></span>
                </div>
              </div>
              <a href="<?php the_permalink(); ?>" style="color:var(--primary);text-decoration:none;font-size:0.9em;font-weight:500;flex-shrink:0;">Baca &rarr;</a>
            </div>
          <?php endif; ?>
        <?php endwhile; ?>
      </div>
      <?php if ($query->found_posts > intval($atts['limit'])) : ?>
        <div style="text-align:center;margin-top:var(--sp-md);">
          <a href="<?php echo get_post_type_archive_link('desa_produk_hukum'); ?>" style="display:inline-block;padding:8px 24px;background:var(--primary);color:var(--on-primary);border-radius:6px;text-decoration:none;font-weight:500;">Lihat Semua Produk Hukum</a>
        </div>
      <?php endif; ?>
    <?php
          else :
            echo '<div style="text-align:center;padding:30px;color:var(--graphite);">Belum ada produk hukum yang dipublikasikan.</div>';
          endif;
          wp_reset_postdata();
          return ob_get_clean();
        }

        public function render_berita($atts = [])
        {
          $atts = shortcode_atts([
            'limit' => 6,
            'category' => '',
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'compact', 'minimal'])) {
            $style = 'classic';
          }

          $args = [
            'post_type' => 'desa_berita',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'DESC',
          ];

          if (!empty($atts['category'])) {
            $args['tax_query'] = [[
              'taxonomy' => 'desa_berita_cat',
              'field' => 'slug',
              'terms' => sanitize_text_field($atts['category']),
            ]];
          }

          $query = new \WP_Query($args);

          ob_start();
          if ($query->have_posts()) :
    ?>
      <div class="wp-desa-berita-grid wp-desa-berita--<?php echo esc_attr($style); ?>">
        <?php if ($style === 'minimal'): ?>
          <div style="border:1px solid var(--fog);border-radius:var(--rounded-lg);overflow:hidden;">
            <?php while ($query->have_posts()) : $query->the_post();
                $cats = get_the_terms(get_the_ID(), 'desa_berita_cat');
            ?>
              <div style="display:flex;align-items:center;gap:var(--sp-md);padding:var(--sp-sm) 0;border-bottom:1px solid var(--fog);">
                <span style="font-size:12px;color:var(--graphite);white-space:nowrap;min-width:80px;"><?php echo get_the_date('d M Y'); ?></span>
                <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:14px;color:var(--ink);text-decoration:none;flex:1;min-width:0;"><?php the_title(); ?></a>
                <?php if ($cats && !is_wp_error($cats)) : ?>
                  <span style="background:var(--primary-soft);color:var(--primary-deep);padding:1px 8px;border-radius:4px;font-size:11px;white-space:nowrap;"><?php echo esc_html($cats[0]->name); ?></span>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          </div>

        <?php elseif ($style === 'compact'): ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:15px;">
            <?php while ($query->have_posts()) : $query->the_post();
                $cats = get_the_terms(get_the_ID(), 'desa_berita_cat');
            ?>
              <div style="border:1px solid var(--fog);border-radius:var(--rounded-lg);overflow:hidden;background:var(--canvas);">
                <?php if (has_post_thumbnail()) : ?>
                  <a href="<?php the_permalink(); ?>" style="display:block;height:120px;overflow:hidden;">
                    <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                  </a>
                <?php endif; ?>
                <div style="padding:var(--sp-md);">
                  <div style="font-size:12px;color:var(--graphite);margin-bottom:4px;">
                    <?php echo get_the_date(); ?>
                    <?php if ($cats && !is_wp_error($cats)) : ?>
                      <span style="margin-left:6px;background:var(--primary-soft);color:var(--primary-deep);padding:1px 6px;border-radius:4px;"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                  </div>
                  <h3 style="margin:0;font-size:15px;line-height:1.4;">
                    <a href="<?php the_permalink(); ?>" style="color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  </h3>
                  <p style="color:var(--graphite);font-size:13px;line-height:1.5;margin:6px 0 0;display:-webkit-box;-webkit-line-clamp:2;overflow:hidden;"><?php echo wp_trim_words(get_the_excerpt(), 12); ?></p>
                </div>
              </div>
            <?php endwhile; ?>
          </div>

        <?php else: /* classic */ ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;">
            <?php while ($query->have_posts()) : $query->the_post();
                $cats = get_the_terms(get_the_ID(), 'desa_berita_cat');
            ?>
              <div style="border:1px solid var(--fog);border-radius:var(--rounded-xl);overflow:hidden;background:var(--canvas);">
                <?php if (has_post_thumbnail()) : ?>
                  <a href="<?php the_permalink(); ?>" style="display:block;height:200px;overflow:hidden;">
                    <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                  </a>
                <?php endif; ?>
                <div style="padding:var(--sp-lg);">
                  <div style="font-size:0.85em;color:var(--graphite);margin-bottom:8px;">
                    <?php echo get_the_date(); ?>
                    <?php if ($cats && !is_wp_error($cats)) : ?>
                      <span style="margin-left:8px;background:var(--primary-soft);color:var(--primary-deep);padding:2px 8px;border-radius:4px;"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                  </div>
                  <h3 style="margin:0 0 8px;font-size:1.1em;line-height:1.4;">
                    <a href="<?php the_permalink(); ?>" style="color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  </h3>
                  <p style="color:var(--graphite);font-size:0.95em;line-height:1.6;margin:0;"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($query->found_posts > intval($atts['limit'])) : ?>
        <div style="text-align:center;margin-top:var(--sp-lg);">
          <a href="<?php echo get_post_type_archive_link('desa_berita'); ?>" style="display:inline-block;padding:10px 28px;background:var(--primary);color:var(--on-primary);border-radius:6px;text-decoration:none;font-weight:500;">Lihat Semua Berita</a>
        </div>
      <?php endif; ?>
    <?php
          else :
            echo '<div style="text-align:center;padding:30px;color:var(--graphite);">Belum ada berita.</div>';
          endif;
          wp_reset_postdata();
          return ob_get_clean();
        }

        public function render_agenda($atts = [])
        {
          $atts = shortcode_atts([
            'limit' => 5,
            'category' => '',
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'compact', 'minimal'])) {
            $style = 'classic';
          }

          $args = [
            'post_type' => 'desa_agenda',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => [[
              'key' => '_desa_agenda_date',
              'value' => date('Y-m-d'),
              'compare' => '>=',
              'type' => 'DATE',
            ]],
          ];

          if (!empty($atts['category'])) {
            $args['tax_query'] = [[
              'taxonomy' => 'desa_agenda_cat',
              'field' => 'slug',
              'terms' => sanitize_text_field($atts['category']),
            ]];
          }

          $query = new \WP_Query($args);

          ob_start();
          if ($query->have_posts()) :
    ?>
      <div class="wp-desa-agenda-list wp-desa-agenda--<?php echo esc_attr($style); ?>" style="display:flex;flex-direction:column;gap:<?php echo $style === 'compact' ? '10px' : ($style === 'minimal' ? '0' : '12px'); ?>;">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <?php
              $date = get_post_meta(get_the_ID(), '_desa_agenda_date', true);
              $time = get_post_meta(get_the_ID(), '_desa_agenda_time', true);
              $location = get_post_meta(get_the_ID(), '_desa_agenda_location', true);
              $end_date = get_post_meta(get_the_ID(), '_desa_agenda_end_date', true);
              $date_display = $date ? date_i18n('j M Y', strtotime($date)) : '';
              if ($end_date && $end_date !== $date) {
                $date_display .= ' - ' . date_i18n('j M Y', strtotime($end_date));
              }
          ?>

          <?php if ($style === 'minimal'): ?>
            <div style="display:flex;align-items:center;gap:var(--sp-sm);padding:var(--sp-sm) 0;border-bottom:1px solid var(--fog);">
              <span style="font-size:13px;font-weight:600;color:var(--ink);min-width:75px;"><?php echo $date ? date_i18n('d M', strtotime($date)) : '?'; ?></span>
              <div style="flex:1;min-width:0;">
                <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:14px;color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                <?php if ($time || $location): ?>
                  <div style="font-size:12px;color:var(--graphite);margin-top:1px;">
                    <?php if ($time) : ?><span>🕐 <?php echo esc_html($time); ?></span><?php endif; ?>
                    <?php if ($location) : ?><span style="margin-left:8px;">📍 <?php echo esc_html($location); ?></span><?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

          <?php elseif ($style === 'compact'): ?>
            <div style="display:flex;gap:12px;padding:var(--sp-sm);border:1px solid var(--fog);border-radius:var(--rounded-lg);background:var(--canvas);align-items:flex-start;">
              <div style="flex-shrink:0;width:40px;height:40px;background:var(--warning-soft);border-radius:6px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
                <span style="font-weight:700;color:var(--warning-deep);font-size:1em;line-height:1;"><?php echo $date ? date_i18n('d', strtotime($date)) : '?'; ?></span>
                <span style="font-size:0.65em;color:var(--warning-deep);"><?php echo $date ? date_i18n('M', strtotime($date)) : ''; ?></span>
              </div>
              <div style="flex:1;">
                <h4 style="margin:0 0 2px;font-size:0.95em;color:var(--ink);"><?php the_title(); ?></h4>
                <div style="display:flex;gap:12px;font-size:0.8em;color:var(--graphite);">
                  <?php if ($time) : ?><span>🕐 <?php echo esc_html($time); ?></span><?php endif; ?>
                  <?php if ($location) : ?><span>📍 <?php echo esc_html($location); ?></span><?php endif; ?>
                </div>
              </div>
            </div>

          <?php else: /* classic */ ?>
            <div style="display:flex;gap:16px;padding:var(--sp-md);border:1px solid var(--fog);border-radius:var(--rounded-lg);background:var(--canvas);align-items:flex-start;">
              <div style="flex-shrink:0;width:56px;height:56px;background:var(--warning-soft);border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
                <span style="font-weight:700;color:var(--warning-deep);font-size:1.2em;line-height:1;"><?php echo $date ? date_i18n('d', strtotime($date)) : '?'; ?></span>
                <span style="font-size:0.75em;color:var(--warning-deep);"><?php echo $date ? date_i18n('M', strtotime($date)) : ''; ?></span>
              </div>
              <div style="flex:1;">
                <h4 style="margin:0 0 4px;font-size:1.05em;color:var(--ink);"><?php the_title(); ?></h4>
                <div style="display:flex;gap:16px;font-size:0.85em;color:var(--graphite);">
                  <?php if ($time) : ?><span>🕐 <?php echo esc_html($time); ?></span><?php endif; ?>
                  <?php if ($location) : ?><span>📍 <?php echo esc_html($location); ?></span><?php endif; ?>
                </div>
                <?php if (get_the_content()) : ?>
                  <p style="margin:8px 0 0;font-size:0.9em;color:var(--graphite);line-height:1.5;"><?php echo wp_trim_words(get_the_content(), 15); ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endwhile; ?>
      </div>
      <?php if ($query->found_posts > intval($atts['limit'])) : ?>
        <div style="text-align:center;margin-top:var(--sp-lg);">
          <a href="<?php echo get_post_type_archive_link('desa_agenda'); ?>" style="display:inline-block;padding:8px 24px;background:var(--primary);color:var(--on-primary);border-radius:6px;text-decoration:none;font-weight:500;">Lihat Semua Agenda</a>
        </div>
      <?php endif; ?>
    <?php
          else :
            echo '<div style="text-align:center;padding:30px;color:var(--graphite);">Tidak ada agenda mendatang.</div>';
          endif;
          wp_reset_postdata();
          return ob_get_clean();
        }

        public function render_galeri($atts = [])
        {
          $atts = shortcode_atts([
            'limit' => 12,
            'category' => '',
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'compact', 'minimal'])) {
            $style = 'classic';
          }

          $args = [
            'post_type' => 'desa_galeri',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'DESC',
          ];

          if (!empty($atts['category'])) {
            $args['tax_query'] = [[
              'taxonomy' => 'desa_galeri_cat',
              'field' => 'slug',
              'terms' => sanitize_text_field($atts['category']),
            ]];
          }

          $query = new \WP_Query($args);

          ob_start();
          if ($query->have_posts()) :
            $min_w = $style === 'compact' ? '150px' : ($style === 'minimal' ? '100px' : '250px');
            $gap   = $style === 'compact' ? '10px' : ($style === 'minimal' ? '6px' : '16px');
            $img_h = $style === 'compact' ? 140 : ($style === 'minimal' ? 80 : 200);
            $show_title = $style !== 'minimal';
    ?>
      <div class="wp-desa-galeri-grid wp-desa-galeri--<?php echo esc_attr($style); ?>" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(<?php echo $min_w; ?>,1fr));gap:<?php echo $gap; ?>;">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
          <?php
              $gallery_ids = get_post_meta(get_the_ID(), '_desa_galeri_images', true);
              $thumb_url = '';
              if ($gallery_ids) {
                $ids = explode(',', $gallery_ids);
                $thumb_url = wp_get_attachment_thumb_url(trim($ids[0]));
              }
              if (!$thumb_url && has_post_thumbnail()) {
                $thumb_url = get_the_post_thumbnail_url(null, 'medium');
              }
          ?>
          <div style="border:1px solid var(--fog);border-radius:<?php echo $style === 'minimal' ? 'var(--rounded-md)' : 'var(--rounded-lg)'; ?>;overflow:hidden;background:var(--canvas);">
            <a href="<?php the_permalink(); ?>" style="display:block;aspect-ratio:1;overflow:hidden;">
              <?php if ($thumb_url) : ?>
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else : ?>
                <div style="width:100%;height:100%;background:var(--cloud);display:flex;align-items:center;justify-content:center;color:var(--graphite);">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                  </svg>
                </div>
              <?php endif; ?>
            </a>
            <?php if ($show_title): ?>
              <div style="padding:<?php echo $style === 'compact' ? '8px 10px' : '12px 16px'; ?>;">
                <h4 style="margin:0;font-size:<?php echo $style === 'compact' ? '0.85em' : '0.95em'; ?>;">
                  <a href="<?php the_permalink(); ?>" style="color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                </h4>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
      <?php if ($query->found_posts > intval($atts['limit'])) : ?>
        <div style="text-align:center;margin-top:var(--sp-lg);">
          <a href="<?php echo get_post_type_archive_link('desa_galeri'); ?>" style="display:inline-block;padding:8px 24px;background:var(--primary);color:var(--on-primary);border-radius:6px;text-decoration:none;font-weight:500;">Lihat Semua Galeri</a>
        </div>
      <?php endif; ?>
    <?php
          else :
            echo '<div style="text-align:center;padding:30px;color:var(--graphite);">Belum ada galeri.</div>';
          endif;
          wp_reset_postdata();
          return ob_get_clean();
        }

        public function render_peta($atts = [])
        {
          $atts = shortcode_atts([
            'height' => 500,
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'minimal'])) {
            $style = 'classic';
          }

          $settings = get_option('wp_desa_settings');
          $map_data = isset($settings['peta_desa']) ? $settings['peta_desa'] : [];
          $center_lat = isset($map_data['center_lat']) ? $map_data['center_lat'] : '-7.0';
          $center_lng = isset($map_data['center_lng']) ? $map_data['center_lng'] : '110.0';
          $zoom = isset($map_data['zoom']) ? $map_data['zoom'] : 13;
          $markers = isset($map_data['markers']) ? $map_data['markers'] : [];
          $height = $style === 'minimal' ? 300 : intval($atts['height']);
          $container_style = $style === 'minimal'
            ? 'width:100%;height:' . $height . 'px;'
            : 'width:100%;height:' . $height . 'px;border-radius:var(--rounded-lg);border:1px solid var(--fog);';

          ob_start();
    ?>
    <div id="wp-desa-peta-frontend" style="<?php echo $container_style; ?>"></div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
    <script>
      (function() {
        if (document.getElementById('wp-desa-leaflet-loaded')) return;
        var s = document.createElement('script');
        s.id = 'wp-desa-leaflet-loaded';
        s.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js';
        s.onload = initPetaDesa;
        document.head.appendChild(s);

        function fallback() {
          var ss = document.createElement('script');
          ss.src = '<?php echo esc_js(WP_DESA_URL . 'assets/js/leaflet/leaflet.min.js'); ?>';
          document.head.appendChild(ss);
        }
        s.onerror = fallback;
        setTimeout(function() {
          if (!window.L) fallback();
        }, 5000);

        function initPetaDesa() {
          if (!window.L) return setTimeout(initPetaDesa, 200);
          var map = L.map('wp-desa-peta-frontend').setView([<?php echo esc_js($center_lat); ?>, <?php echo esc_js($center_lng); ?>], <?php echo esc_js($zoom); ?>);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
          }).addTo(map);

          var colorMap = {
            'kantor-desa': '#2563eb',
            'sekolah': '#059669',
            'masjid': '#7c3aed',
            'puskesmas': '#dc2626',
            'pasar': '#d97706',
            'wisata': '#0891b2',
            'lainnya': '#6b7280'
          };
          var markers = <?php echo json_encode($markers); ?>;
          markers.forEach(function(m) {
            var color = colorMap[m.type] || '#6b7280';
            var icon = L.divIcon({
              className: '',
              html: '<div style="width:22px;height:22px;background:' + color + ';border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
              iconSize: [22, 22],
              iconAnchor: [11, 11],
              popupAnchor: [0, -13]
            });
            L.marker([parseFloat(m.lat), parseFloat(m.lng)], {
                icon: icon
              })
              .addTo(map)
              .bindPopup('<b>' + (m.name || '') + '</b><br>' + (m.desc || ''));
          });
        }
      })();
    </script>
    <?php
          return ob_get_clean();
        }

        public function render_wisata($atts = [])
        {
          $atts = shortcode_atts([
            'limit' => 6,
            'category' => '',
            'style' => 'classic',
          ], $atts);

          $style = $atts['style'];
          if (!in_array($style, ['classic', 'compact', 'minimal'])) {
            $style = 'classic';
          }

          $args = [
            'post_type' => 'desa_wisata',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'date',
            'order' => 'DESC',
          ];

          if (!empty($atts['category'])) {
            $args['tax_query'] = [[
              'taxonomy' => 'desa_wisata_cat',
              'field' => 'slug',
              'terms' => sanitize_text_field($atts['category']),
            ]];
          }

          $query = new \WP_Query($args);

          $img_h   = $style === 'compact' ? 120 : 200;
          $pad     = $style === 'compact' ? 'var(--sp-md)' : 'var(--sp-lg)';
          $gap     = $style === 'compact' ? '15px' : '24px';
          $min_w   = $style === 'compact' ? '240px' : '300px';
          $title_sz = $style === 'compact' ? 15 : 17;
          $txt_sz  = $style === 'compact' ? 13 : 14;

          ob_start();
          if ($query->have_posts()) :
    ?>
      <div class="wp-desa-wisata-grid wp-desa-wisata--<?php echo esc_attr($style); ?>">
        <?php if ($style === 'minimal'): ?>
          <div style="border:1px solid var(--fog);border-radius:var(--rounded-lg);overflow:hidden;">
            <?php while ($query->have_posts()) : $query->the_post();
                $address = get_post_meta(get_the_ID(), '_desa_wisata_address', true);
            ?>
              <div style="display:flex;align-items:center;gap:var(--sp-md);padding:var(--sp-md);border-bottom:1px solid var(--fog);background:var(--canvas);">
                <div style="flex:1;min-width:0;">
                  <a href="<?php the_permalink(); ?>" style="font-weight:500;font-size:15px;color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  <?php if ($address): ?>
                    <div style="font-size:12px;color:var(--graphite);margin-top:2px;">📍 <?php echo esc_html($address); ?></div>
                  <?php endif; ?>
                </div>
                <a href="<?php the_permalink(); ?>" style="font-size:13px;font-weight:500;color:var(--primary);text-decoration:none;flex-shrink:0;">Detail →</a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: /* classic / compact */ ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(<?php echo $min_w; ?>,1fr));gap:<?php echo $gap; ?>;">
            <?php while ($query->have_posts()) : $query->the_post();
                $address = get_post_meta(get_the_ID(), '_desa_wisata_address', true);
            ?>
              <div style="border:1px solid var(--fog);border-radius:var(--rounded-xl);overflow:hidden;background:var(--canvas);">
                <?php if (has_post_thumbnail()) : ?>
                  <a href="<?php the_permalink(); ?>" style="display:block;height:<?php echo $img_h; ?>px;overflow:hidden;">
                    <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                  </a>
                <?php else : ?>
                  <div style="height:<?php echo $img_h; ?>px;background:var(--cloud);display:flex;align-items:center;justify-content:center;color:var(--graphite);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                      <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                  </div>
                <?php endif; ?>
                <div style="padding:<?php echo $pad; ?>;">
                  <h3 style="margin:0 0 <?php echo $style === 'compact' ? '4px' : '8px'; ?>;font-size:<?php echo $title_sz; ?>px;">
                    <a href="<?php the_permalink(); ?>" style="color:var(--ink);text-decoration:none;"><?php the_title(); ?></a>
                  </h3>
                  <?php if ($address) : ?>
                    <div style="font-size:<?php echo $txt_sz - 1; ?>px;color:var(--graphite);margin-bottom:4px;">📍 <?php echo esc_html($address); ?></div>
                  <?php endif; ?>
                  <?php if (has_excerpt()) : ?>
                    <p style="color:var(--graphite);font-size:<?php echo $txt_sz; ?>px;line-height:1.5;margin:<?php echo $style === 'compact' ? '4px' : '8px'; ?> 0 0;"><?php echo get_the_excerpt(); ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($query->found_posts > intval($atts['limit'])) : ?>
        <div style="text-align:center;margin-top:var(--sp-lg);">
          <a href="<?php echo get_post_type_archive_link('desa_wisata'); ?>" style="display:inline-block;padding:10px 28px;background:var(--primary);color:var(--on-primary);border-radius:6px;text-decoration:none;font-weight:500;">Lihat Semua Destinasi Wisata</a>
        </div>
      <?php endif; ?>
<?php
          else :
            echo '<div style="text-align:center;padding:30px;color:var(--graphite);">Belum ada destinasi wisata.</div>';
          endif;
          wp_reset_postdata();
          return ob_get_clean();
        }

        public function render_jam_kerja($atts = [])
        {
          $jam_kerja = get_option('temadesa_jam_kerja', []);
          $hari_label = [
            'senin'  => 'Senin',
            'selasa' => 'Selasa',
            'rabu'   => 'Rabu',
            'kamis'  => 'Kamis',
            'jumat'  => 'Jumat',
            'sabtu'  => 'Sabtu',
            'minggu' => 'Minggu',
          ];

          $atts = shortcode_atts([
            'class' => '',
          ], $atts);

          $rows = '';
          $hari_ini = strtolower(date('l'));
          $hari_map = [
            'monday'    => 'senin',
            'tuesday'   => 'selasa',
            'wednesday' => 'rabu',
            'thursday'  => 'kamis',
            'friday'    => 'jumat',
            'saturday'  => 'sabtu',
            'sunday'    => 'minggu',
          ];
          $today_key = $hari_map[$hari_ini] ?? '';

          foreach ($hari_label as $key => $label) {
            $val   = isset($jam_kerja[$key]) ? $jam_kerja[$key] : ['buka' => '08:00', 'tutup' => '16:00', 'libur' => false];
            $today_class = ($key === $today_key) ? 'desa-jam-today' : '';
            $libur = !empty($val['libur']);

            if ($libur) {
              $jam = '<span class="desa-jam-libur">Libur</span>';
            } else {
              $buka  = esc_html($val['buka'] ?? '');
              $tutup = esc_html($val['tutup'] ?? '');
              $jam   = $buka && $tutup ? "<span class=\"desa-jam-waktu\">{$buka} — {$tutup}</span>" : '<span class="desa-jam-libur">—</span>';
            }

            $rows .= sprintf(
              '<tr class="%s"><td class="desa-jam-hari">%s</td><td class="desa-jam-jam">%s</td></tr>',
              esc_attr($today_class),
              esc_html($label),
              $jam
            );
          }

          $class = $atts['class'] ? ' class="' . esc_attr($atts['class']) . '"' : '';
          return sprintf(
            '<div%s><table class="desa-jam-table"><tbody>%s</tbody></table></div>',
            $class,
            $rows
          );
        }
      }
