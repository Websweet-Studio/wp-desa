<div class="wrap wp-desa-wrapper">

    <!-- Hero Band (Cohere dark-feature-band) -->
    <section class="wp-desa-hero">
        <div class="wp-desa-hero__head">
            <span class="wp-desa-hero__eyebrow"><?php echo esc_html(\WpDesa\Admin\Menu::plugin_name()); ?> · Overview</span>
            <h1 class="wp-desa-hero__title">Dashboard Desa</h1>
            <p class="wp-desa-hero__sub">Ringkasan data dan statistik desa terkini — dalam satu pandangan.</p>
        </div>

        <div class="wp-desa-hero__metrics">
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value">0</span>
                <span class="wp-desa-hero__label">Penduduk</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value">0</span>
                <span class="wp-desa-hero__label">Surat Masuk</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value">0</span>
                <span class="wp-desa-hero__label">Menunggu</span>
            </div>
            <div class="wp-desa-hero__metric">
                <span class="wp-desa-hero__value">0</span>
                <span class="wp-desa-hero__label">Aspirasi</span>
            </div>
        </div>
    </section>

    <!-- Stat Cards (flat on white canvas) -->
    <div class="wp-desa-stats-grid">

        <!-- Total Potensi -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Potensi Desa</p>
                    <h3 class="wp-desa-stat-value">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-carrot"></span>
                </div>
            </div>
            <a href="<?php echo admin_url('edit.php?post_type=desa_potensi'); ?>" class="wp-desa-stat-link">Kelola Potensi &rarr;</a>
        </div>

        <!-- Total UMKM -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">UMKM Desa</p>
                    <h3 class="wp-desa-stat-value">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-store"></span>
                </div>
            </div>
            <a href="<?php echo admin_url('edit.php?post_type=desa_umkm'); ?>" class="wp-desa-stat-link">Kelola UMKM &rarr;</a>
        </div>

        <!-- Keuangan Desa -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Keuangan Desa <span></span></p>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
            </div>

            <div class="wp-desa-stat-bars">
                <div>
                    <div class="wp-desa-stat-bar-row">
                        <span>Pemasukan</span>
                        <span class="wp-desa-text-green">Rp 0</span>
                    </div>
                    <div class="wp-desa-bar">
                        <div class="wp-desa-bar-fill wp-desa-bar-income" style="width: 100%;"></div>
                    </div>
                </div>
                <div>
                    <div class="wp-desa-stat-bar-row">
                        <span>Pengeluaran</span>
                        <span class="wp-desa-text-red">Rp 0</span>
                    </div>
                    <div class="wp-desa-bar">
                        <div class="wp-desa-bar-fill wp-desa-bar-expense" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Layanan Surat -->
        <div class="wp-desa-stat-card">
            <div class="wp-desa-stat-top">
                <div>
                    <p class="wp-desa-stat-title">Status Layanan Surat</p>
                    <h3 class="wp-desa-stat-value">0</h3>
                </div>
                <div class="wp-desa-stat-icon">
                    <span class="dashicons dashicons-email-alt"></span>
                </div>
            </div>
            <div class="wp-desa-stat-list wp-desa-stat-list--status">
                <div class="wp-desa-status-row">
                    <span class="wp-desa-list-dot"></span>
                    <span class="wp-desa-status-name"></span>
                    <span class="wp-desa-status-count"></span>
                </div>
            </div>
        </div>

        <!-- Aspirasi Warga -->
        <div class="wp-desa-stat-card wp-desa-stat-wide">
            <div class="wp-desa-stat-top">
                <p class="wp-desa-stat-title">Aspirasi Warga Terbaru</p>
                <a href="<?php echo admin_url('admin.php?page=wp-desa-layanan&tab=aduan'); ?>" class="wp-desa-stat-link">Lihat Semua</a>
            </div>

            <div class="wp-desa-stat-list">
                <div class="wp-desa-list-row">
                    <span class="wp-desa-list-dot"></span>
                    <div class="wp-desa-list-main">
                        <p class="wp-desa-list-title">Judul Aduan</p>
                        <p class="wp-desa-row-subtitle"></p>
                    </div>
                    <span class="wp-desa-badge"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="wp-desa-charts-grid">

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Demografi Jenis Kelamin</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Status Perkawinan</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="maritalChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Pekerjaan Utama (Top 5)</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="jobChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Status Layanan Surat</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="letterChart"></canvas>
            </div>
        </div>

        <div class="wp-desa-card wp-desa-card-pad">
            <h3 class="wp-desa-section-title">Realisasi Bantuan Sosial</h3>
            <div class="wp-desa-chart-wrap">
                <canvas id="aidChart"></canvas>
            </div>
        </div>
    </div>
</div>