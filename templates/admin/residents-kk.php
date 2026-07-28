<?php
global $wpdb;
$table_name = $wpdb->prefix . 'desa_residents';
$per_page   = 20;
$action     = isset($_GET['action']) ? $_GET['action'] : 'list';
$base_url   = '?page=wp-desa-residents&tab=kk';

// ============================================================
// Detail view
// ============================================================
if ($action === 'detail' && isset($_GET['no_kk'])) {
    $no_kk   = sanitize_text_field($_GET['no_kk']);
    $members = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE no_kk = %s ORDER BY jenis_kelamin ASC, nama_lengkap ASC",
        $no_kk
    ));

    function wp_desa_status_badge_kk($status)
    {
        $status = $status ?: 'Belum Kawin';
        $cls = 'default';
        if ($status === 'Kawin') $cls = 'success';
        elseif (in_array($status, ['Cerai Hidup', 'Cerai Mati'])) $cls = 'danger';
        return '<span class="wp-desa-badge wp-desa-badge-' . $cls . '">' . esc_html($status) . '</span>';
    }
?>
<div class="wrap wp-desa-wrapper">
    <div class="wp-desa-header">
        <div>
            <a href="<?php echo $base_url; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="margin-bottom:8px;">
                <span class="dashicons dashicons-arrow-left-alt2"></span> Kembali
            </a>
            <h1 class="wp-desa-title">Anggota KK: <span class="wp-desa-mono"><?php echo esc_html($no_kk); ?></span></h1>
            <p class="wp-desa-helper">Daftar anggota keluarga dalam Kartu Keluarga ini.</p>
        </div>
    </div>

    <div class="wp-desa-card">
        <div style="overflow-x:auto">
            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Jenis Kelamin</th>
                        <th>Tempat/Tgl Lahir</th>
                        <th>Status</th>
                        <th>Pekerjaan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($members)): ?>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="wp-desa-mono"><?php echo esc_html($m->nik); ?></td>
                                <td><?php echo esc_html($m->nama_lengkap); ?></td>
                                <td><?php echo esc_html($m->jenis_kelamin); ?></td>
                                <td><?php echo esc_html($m->tempat_lahir ? $m->tempat_lahir . ', ' . $m->tanggal_lahir : ($m->tanggal_lahir ?: '-')); ?></td>
                                <td><?php echo wp_desa_status_badge_kk($m->status_perkawinan); ?></td>
                                <td><?php echo esc_html($m->pekerjaan ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="wp-desa-empty-state">
                                <span class="dashicons dashicons-warning"></span>
                                <div class="wp-desa-mt-8">Tidak ditemukan anggota untuk KK ini.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
    return;
}

// ============================================================
// List mode: query KK grouped with pagination
// ============================================================
$paged       = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset      = ($paged - 1) * $per_page;

$total_items = (int) $wpdb->get_var("SELECT COUNT(DISTINCT no_kk) FROM $table_name WHERE no_kk IS NOT NULL AND no_kk != ''");
$total_pages = max(1, ceil($total_items / $per_page));

$kk_list = $wpdb->get_results($wpdb->prepare(
    "SELECT no_kk, alamat, COUNT(*) as jumlah_anggota,
            MIN(CASE WHEN jenis_kelamin = 'Laki-laki' THEN nama_lengkap END) as kepala_keluarga
     FROM $table_name
     WHERE no_kk IS NOT NULL AND no_kk != ''
     GROUP BY no_kk
     ORDER BY no_kk DESC
     LIMIT %d OFFSET %d",
    $per_page,
    $offset
));
?>
<div class="wrap wp-desa-wrapper">
    <div class="wp-desa-header">
        <div>
            <h1 class="wp-desa-title">Kartu Keluarga</h1>
            <p class="wp-desa-helper">Kelompokkan penduduk berdasarkan Nomor Kartu Keluarga.</p>
        </div>
    </div>

    <div class="wp-desa-card">
        <div style="overflow-x:auto">
            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th>No. KK</th>
                        <th>Kepala Keluarga</th>
                        <th>Alamat</th>
                        <th style="text-align: center;">Jumlah Anggota</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($kk_list)): ?>
                        <?php foreach ($kk_list as $kk): ?>
                            <tr>
                                <td class="wp-desa-mono"><?php echo esc_html($kk->no_kk); ?></td>
                                <td><?php echo esc_html($kk->kepala_keluarga ?: '-'); ?></td>
                                <td><?php echo esc_html($kk->alamat ?: '-'); ?></td>
                                <td style="text-align: center;"><?php echo (int) $kk->jumlah_anggota; ?></td>
                                <td style="text-align: right;">
                                    <a href="<?php echo $base_url; ?>&action=detail&no_kk=<?php echo urlencode($kk->no_kk); ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Lihat Anggota</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="wp-desa-empty-state">
                                <span class="dashicons dashicons-warning"></span>
                                <div class="wp-desa-mt-8">Belum ada data Kartu Keluarga.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_items > $per_page): ?>
            <div class="wp-desa-pagination">
                <div class="wp-desa-pagination-info">
                    Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?> dari <?php echo $total_items; ?> KK
                </div>
                <div class="wp-desa-pagination-controls">
                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>" href="<?php echo $base_url; ?><?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?>">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </a>
                    <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="<?php echo $base_url; ?>&paged=<?php echo $paged + 1; ?>">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
