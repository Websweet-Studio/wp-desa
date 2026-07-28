<?php
global $wpdb;
$table    = $wpdb->prefix . 'desa_complaints';
$settings = get_option('wp_desa_settings', []);
$per_page = 20;
$action   = isset($_GET['action']) ? $_GET['action'] : 'list';

// ============================================================
// Helper: status badge
// ============================================================
function wp_desa_complaint_badge($status)
{
    $map = [
        'pending'    => ['label' => 'Pending',    'class' => 'wp-desa-badge-pending'],
        'in_progress' => ['label' => 'Diproses',  'class' => 'wp-desa-badge-warning'],
        'resolved'   => ['label' => 'Selesai',    'class' => 'wp-desa-badge-success'],
        'rejected'   => ['label' => 'Ditolak',    'class' => 'wp-desa-badge-danger'],
    ];
    $s = isset($map[$status]) ? $map[$status] : ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => ''];
    return '<span class="wp-desa-badge ' . $s['class'] . '">' . esc_html($s['label']) . '</span>';
}

// ============================================================
// POST: update status + response
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_update_complaint'])) {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status   = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $response = isset($_POST['response']) ? sanitize_textarea_field($_POST['response']) : '';

    if ($id > 0 && in_array($status, ['pending', 'in_progress', 'resolved', 'rejected'])) {
        $wpdb->update($table, [
            'status'     => $status,
            'response'   => $response,
            'updated_at' => current_time('mysql'),
        ], ['id' => $id]);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=aduan&updated=1'));
    exit;
}

// ============================================================
// Detail mode
// ============================================================
if ($action === 'detail') {
    $detail_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $complaint = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $detail_id));
    if (!$complaint) {
        wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=aduan'));
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
if (in_array($status_filter, ['pending', 'in_progress', 'resolved', 'rejected'])) {
    $count_where = $wpdb->prepare("WHERE status = %s", $status_filter);
    $list_where  = $wpdb->prepare("WHERE status = %s", $status_filter);
}

$total_items  = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $count_where");
$total_pages  = max(1, ceil($total_items / $per_page));

$complaints = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table $list_where ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

// Count per status for tabs
$counts = ['all' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0, 'rejected' => 0];
$count_results = $wpdb->get_results("SELECT status, COUNT(*) as count FROM $table GROUP BY status");
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
        <div class="wp-desa-header">
            <div>
                <a href="?page=wp-desa-layanan&tab=aduan" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="margin-bottom:8px;">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> Kembali
                </a>
                <h1 class="wp-desa-title">Detail Aduan</h1>
                <p class="wp-desa-helper">Kode Tracking: <strong><?php echo esc_html($complaint->tracking_code); ?></strong></p>
            </div>
        </div>

        <div class="wp-desa-card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Pelapor:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($complaint->reporter_name ?: 'Anonim'); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Kontak:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($complaint->reporter_contact ?: '-'); ?></span>
                    </div>
                </div>
                <div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Kategori:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($complaint->category); ?></span>
                    </div>
                    <div class="wp-desa-info-row">
                        <span class="wp-desa-info-label">Tanggal:</span>
                        <span class="wp-desa-info-value"><?php echo esc_html($complaint->created_at); ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Judul:</span>
                <div class="wp-desa-info-value" style="font-weight: 500;"><?php echo esc_html($complaint->subject); ?></div>
            </div>

            <div style="margin-bottom: 20px;">
                <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Isi Laporan:</span>
                <div class="wp-desa-detail-box" style="white-space: pre-wrap;"><?php echo esc_textarea($complaint->description); ?></div>
            </div>

            <?php if (!empty($complaint->photo_url)): ?>
                <div style="margin-bottom: 20px;">
                    <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Foto Lampiran:</span>
                    <a href="<?php echo esc_url($complaint->photo_url); ?>" target="_blank" style="display: block;">
                        <img src="<?php echo esc_url($complaint->photo_url); ?>" style="max-width: 100%; max-height: 200px; border-radius: var(--r-sm); border: 1px solid var(--hairline);">
                    </a>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Status:</span>
                <div><?php echo wp_desa_complaint_badge($complaint->status); ?></div>
            </div>

            <?php if (!empty($complaint->response)): ?>
                <div style="margin-bottom: 20px;">
                    <span class="wp-desa-info-label" style="margin-bottom: 4px; display: block;">Tanggapan Admin:</span>
                    <div class="wp-desa-detail-box" style="white-space: pre-wrap; background: var(--surface-tertiary);"><?php echo esc_textarea($complaint->response); ?></div>
                </div>
            <?php endif; ?>

            <hr style="border: 0; border-top: 1px solid var(--hairline); margin: 20px 0;">

            <form method="post">
                <input type="hidden" name="wp_desa_update_complaint" value="1">
                <input type="hidden" name="id" value="<?php echo (int) $complaint->id; ?>">

                <div class="wp-desa-form-grid">
                    <div>
                        <label class="wp-desa-label" for="complaint-status">Update Status:</label>
                        <select name="status" id="complaint-status" class="wp-desa-select">
                            <option value="pending"     <?php selected($complaint->status, 'pending'); ?>>Pending</option>
                            <option value="in_progress" <?php selected($complaint->status, 'in_progress'); ?>>Diproses</option>
                            <option value="resolved"    <?php selected($complaint->status, 'resolved'); ?>>Selesai</option>
                            <option value="rejected"    <?php selected($complaint->status, 'rejected'); ?>>Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="wp-desa-label" for="complaint-response">Tanggapan Admin:</label>
                        <textarea name="response" id="complaint-response" rows="3" class="wp-desa-textarea" placeholder="Tulis tanggapan..."><?php echo esc_textarea($complaint->response ?: ''); ?></textarea>
                    </div>
                </div>

                <div class="wp-desa-form-actions">
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

    <?php else: ?>

        <!-- ======== LIST VIEW ======== -->
        <div class="wp-desa-header">
            <div>
                <h1 class="wp-desa-title">Aspirasi & Pengaduan Warga</h1>
                <p class="wp-desa-helper">Kelola aspirasi dan pengaduan dari warga.</p>
            </div>
            <div class="wp-desa-actions">
                <?php if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): ?>
                    <button class="wp-desa-btn wp-desa-btn-danger btn-generate-dummy">
                        <span class="dashicons dashicons-database"></span> Generate Dummy
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="wp-desa-card">
            <!-- Filter Tabs -->
            <div class="wp-desa-tabs">
                <?php
                $tab_map = [
                    ''            => 'Semua',
                    'pending'     => 'Pending',
                    'in_progress' => 'Diproses',
                    'resolved'    => 'Selesai',
                    'rejected'    => 'Ditolak',
                ];
                foreach ($tab_map as $val => $label):
                    $active = ($status_filter === $val) ? 'active' : '';
                    $href   = '?page=wp-desa-layanan&tab=aduan' . ($val ? '&status=' . $val : '');
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
                        <th>Kategori</th>
                        <th>Pelapor</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($complaints)): ?>
                        <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td><?php echo esc_html($c->created_at); ?></td>
                                <td class="wp-desa-mono"><?php echo esc_html($c->tracking_code); ?></td>
                                <td><?php echo esc_html($c->category); ?></td>
                                <td><?php echo esc_html($c->reporter_name ?: 'Anonim'); ?></td>
                                <td><?php echo esc_html($c->subject); ?></td>
                                <td><?php echo wp_desa_complaint_badge($c->status); ?></td>
                                <td style="text-align: right;">
                                    <div class="wp-desa-inline-actions-end">
                                        <a href="?page=wp-desa-layanan&tab=aduan&action=detail&id=<?php echo (int) $c->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="wp-desa-empty-state">
                                <span class="dashicons dashicons-warning"></span>
                                <div class="wp-desa-mt-8">Belum ada aspirasi atau pengaduan.</div>
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
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-layanan&tab=aduan<?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </a>
                        <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-layanan&tab=aduan&paged=<?php echo $paged + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>
