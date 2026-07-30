<?php
global $wpdb;
$letters_table = $wpdb->prefix . 'desa_letters';
$types_table   = $wpdb->prefix . 'desa_letter_types';
$settings      = get_option('wp_desa_settings', []);
$per_page      = 20;
$action        = isset($_GET['action']) ? $_GET['action'] : 'list';

// ============================================================
// Helper: status badge
// ============================================================
function wp_desa_letter_badge($status)
{
    $map = [
        'pending'   => ['label' => 'Pending',   'class' => 'wp-desa-badge-pending'],
        'processed' => ['label' => 'Diproses',  'class' => 'wp-desa-badge-warning'],
        'completed' => ['label' => 'Selesai',   'class' => 'wp-desa-badge-success'],
        'rejected'  => ['label' => 'Ditolak',   'class' => 'wp-desa-badge-danger'],
    ];
    $s = isset($map[$status]) ? $map[$status] : ['label' => ucfirst($status), 'class' => ''];
    return '<span class="wp-desa-badge ' . $s['class'] . '">' . esc_html($s['label']) . '</span>';
}

// ============================================================
// POST: update status
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_update_letter_status'])) {
    $id         = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    if ($id > 0 && in_array($new_status, ['pending', 'processed', 'completed', 'rejected'])) {
        $wpdb->update($letters_table, ['status' => $new_status, 'updated_at' => current_time('mysql')], ['id' => $id]);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=surat&updated=1'));
    exit;
}

// ============================================================
// Detail mode
// ============================================================
if ($action === 'detail') {
    $detail_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $letter    = $wpdb->get_row($wpdb->prepare(
        "SELECT l.*, lt.name as type_name FROM $letters_table l LEFT JOIN $types_table lt ON l.letter_type_id = lt.id WHERE l.id = %d",
        $detail_id
    ));
    if (!$letter) {
        wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=surat'));
        exit;
    }
}

// ============================================================
// List mode: query with pagination
// ============================================================
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$paged         = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset        = ($paged - 1) * $per_page;

$count_where = '';
$list_where  = '';
if (in_array($status_filter, ['pending', 'processed', 'completed', 'rejected'])) {
    $count_where = $wpdb->prepare("WHERE status = %s", $status_filter);
    $list_where  = $wpdb->prepare("WHERE l.status = %s", $status_filter);
}

$total_items  = (int) $wpdb->get_var("SELECT COUNT(*) FROM $letters_table $count_where");
$total_pages  = max(1, ceil($total_items / $per_page));

$letters = $wpdb->get_results($wpdb->prepare(
    "SELECT l.*, lt.name as type_name FROM $letters_table l LEFT JOIN $types_table lt ON l.letter_type_id = lt.id $list_where ORDER BY l.created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

// Count per status for tabs
$counts = ['all' => 0, 'pending' => 0, 'processed' => 0, 'completed' => 0, 'rejected' => 0];
$count_results = $wpdb->get_results("SELECT status, COUNT(*) as count FROM $letters_table GROUP BY status");
foreach ($count_results as $row) {
    if (isset($counts[$row->status])) {
        $counts[$row->status] = (int) $row->count;
    }
}
$counts['all'] = array_sum($counts);
?>
<div class="wrap wp-desa-wrapper">

    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="notice notice-success is-dismissible"><p>Status berhasil diperbarui.</p></div>
    <?php endif; ?>

    <?php if ($action === 'detail'): ?>

        <!-- ======== DETAIL VIEW ======== -->
        <div class="wp-desa-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Jenis Surat:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($letter->type_name); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Kode Tracking:</span>
                        <span class="wp-desa-info-value wp-desa-mono"><?php echo esc_html($letter->tracking_code); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Tanggal Pengajuan:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($letter->created_at); ?></span>
                    </div>
                </div>
                <div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Pemohon:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($letter->name); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">NIK:</span>
                        <span class="wp-desa-info-value wp-desa-mono"><?php echo esc_html($letter->nik); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">No. HP:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($letter->phone); ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Keperluan / Keterangan:</span>
                <div class="wp-desa-detail-box"><?php echo esc_html($letter->details ?: '-'); ?></div>
            </div>

            <div style="margin-bottom: 20px;">
                <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Status:</span>
                <div><?php echo wp_desa_letter_badge($letter->status); ?></div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--hairline); margin: 20px 0;">

            <form method="post">
                <input type="hidden" name="wp_desa_update_letter_status" value="1">
                <input type="hidden" name="id" value="<?php echo (int) $letter->id; ?>">

                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label" for="letter-status">Update Status:</label>
                        <select name="status" id="letter-status" class="wp-desa-select">
                            <option value="pending"   <?php selected($letter->status, 'pending'); ?>>Pending (Menunggu)</option>
                            <option value="processed" <?php selected($letter->status, 'processed'); ?>>Processed (Sedang Diproses)</option>
                            <option value="completed" <?php selected($letter->status, 'completed'); ?>>Completed (Selesai/Siap Ambil)</option>
                            <option value="rejected"  <?php selected($letter->status, 'rejected'); ?>>Rejected (Ditolak)</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: flex-end; gap: 8px;">
                        <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Perubahan</button>
                        <a href="<?php echo admin_url('admin-post.php?action=wp_desa_print_letter&id=' . (int) $letter->id . '&_wpnonce=' . wp_create_nonce('wp_desa_print_letter')); ?>" class="wp-desa-btn wp-desa-btn-secondary" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak Surat
                        </a>
                    </div>
                </div>
            </form>
        </div>

    <?php else: ?>

        <!-- ======== LIST VIEW ======== -->
        <div class="wp-desa-card">
            <!-- Filter Tabs -->
            <div class="wp-desa-tabs">
                <?php
                $tab_map = [
                    ''          => 'Semua',
                    'pending'   => 'Pending',
                    'processed' => 'Diproses',
                    'completed' => 'Selesai',
                    'rejected'  => 'Ditolak',
                ];
                foreach ($tab_map as $val => $label):
                    $active = ($status_filter === $val) ? 'active' : '';
                    $href   = '?page=wp-desa-layanan&tab=surat' . ($val ? '&status=' . $val : '');
                    $cnt    = isset($counts[$val]) ? (int) $counts[$val] : 0;
                ?>
                    <a href="<?php echo $href; ?>" class="wp-desa-tab <?php echo $active; ?>">
                        <?php echo $label; ?> <span class="wp-desa-tab-count"><?php echo $cnt; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Tracking</th>
                        <th>Jenis Surat</th>
                        <th>Pemohon</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($letters)): ?>
                        <?php foreach ($letters as $l): ?>
                            <tr>
                                <td><?php echo esc_html($l->created_at); ?></td>
                                <td class="wp-desa-mono"><?php echo esc_html($l->tracking_code); ?></td>
                                <td><?php echo esc_html($l->type_name); ?></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--ink);"><?php echo esc_html($l->name); ?></div>
                                    <div class="wp-desa-row-subtitle">NIK: <?php echo esc_html($l->nik); ?></div>
                                </td>
                                <td><?php echo wp_desa_letter_badge($l->status); ?></td>
                                <td style="text-align: right;">
                                    <div class="wp-desa-inline-actions-end">
                                        <a href="?page=wp-desa-layanan&tab=surat&action=detail&id=<?php echo (int) $l->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Lihat Detail"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                        <a href="<?php echo admin_url('admin-post.php?action=wp_desa_print_letter&id=' . (int) $l->id . '&_wpnonce=' . wp_create_nonce('wp_desa_print_letter')); ?>" class="wp-desa-btn wp-desa-btn-primary wp-desa-btn-sm" title="Cetak Surat" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="wp-desa-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--graphite);margin-bottom:var(--sp-md);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <div class="wp-desa-mt-8">Belum ada permohonan surat.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_items > $per_page): ?>
                <div class="wp-desa-pagination">
                    <div class="wp-desa-pagination-info">
                        Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?> dari <?php echo $total_items; ?> data
                    </div>
                    <div class="wp-desa-pagination-controls">
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-layanan&tab=surat<?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                        <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-layanan&tab=surat&paged=<?php echo $paged + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>
