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
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--graphite);margin-bottom:var(--sp-md);"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
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

    <div class="wp-desa-card">
        <div class="wp-desa-filter-bar" style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-sm) var(--sp-xl);border-bottom:1px solid var(--fog);">
            <div style="display:flex;gap:var(--sp-sm);align-items:center;">
                <span class="wp-desa-pagination-info">Total: <?php echo (int) $total_items; ?> KK</span>
            </div>
            <div>
                <a href="?page=wp-desa-residents&action=add" class="wp-desa-btn wp-desa-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 .83.18 2 2 0 0 0 .83-.18l8.58-3.9a1 1 0 0 0 0-1.831z"/><path d="M16 17h6"/><path d="M19 14v6"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 .825.178"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l2.116-.962"/></svg> Tambah Anggota
                </a>
            </div>
        </div>
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
                                <td style="text-align: center;"><span class="wp-desa-badge wp-desa-badge-default"><?php echo (int) $kk->jumlah_anggota; ?></span></td>
                                <td style="text-align: right;">
                                    <div class="wp-desa-inline-actions-end">
                                        <a href="<?php echo $base_url; ?>&action=detail&no_kk=<?php echo urlencode($kk->no_kk); ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Lihat Anggota"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="wp-desa-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--graphite);margin-bottom:var(--sp-md);"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="<?php echo $base_url; ?>&paged=<?php echo $paged + 1; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
