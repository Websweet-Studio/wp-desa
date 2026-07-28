<?php
global $wpdb;
$programs_table   = $wpdb->prefix . 'desa_programs';
$recipients_table = $wpdb->prefix . 'desa_program_recipients';
$residents_table  = $wpdb->prefix . 'desa_residents';
$settings         = get_option('wp_desa_settings', []);
$per_page         = 20;
$action           = isset($_GET['action']) ? $_GET['action'] : 'list';

// ============================================================
// Handle POST: save / update program
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_save_program'])) {
    $id                  = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name                = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $description         = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $origin              = isset($_POST['origin']) ? sanitize_text_field($_POST['origin']) : '';
    $year                = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $quota               = isset($_POST['quota']) ? intval($_POST['quota']) : 0;
    $amount_per_recipient = isset($_POST['amount_per_recipient']) ? floatval($_POST['amount_per_recipient']) : 0;
    $status              = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';

    $data = [
        'name'                 => $name,
        'description'          => $description,
        'origin'               => $origin,
        'year'                 => $year,
        'quota'                => $quota,
        'amount_per_recipient' => $amount_per_recipient,
        'status'               => $status,
    ];

    if ($id > 0) {
        $data['updated_at'] = current_time('mysql');
        $wpdb->update($programs_table, $data, ['id' => $id]);
    } else {
        $data['created_at'] = current_time('mysql');
        $wpdb->insert($programs_table, $data);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-aid&saved=1'));
    exit;
}

// ============================================================
// Handle POST: add recipient to program
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_add_recipient'])) {
    $program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
    $nik        = isset($_POST['nik']) ? sanitize_text_field($_POST['nik']) : '';

    if ($program_id > 0 && !empty($nik)) {
        $resident = $wpdb->get_row($wpdb->prepare("SELECT id FROM $residents_table WHERE nik = %s", $nik));
        if ($resident) {
            // Check duplicate
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $recipients_table WHERE program_id = %d AND resident_id = %d",
                $program_id,
                $resident->id
            ));
            if (!$exists) {
                $wpdb->insert($recipients_table, [
                    'program_id'  => $program_id,
                    'resident_id' => $resident->id,
                    'status'      => 'pending',
                    'created_at'  => current_time('mysql'),
                ]);
            }
        }
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-aid&action=recipients&program_id=' . $program_id));
    exit;
}

// ============================================================
// Handle POST: update recipient status
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_update_recipient_status'])) {
    $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
    $new_status   = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $program_id   = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

    if ($recipient_id > 0 && !empty($new_status)) {
        $update_data = ['status' => $new_status];
        if ($new_status === 'distributed') {
            $update_data['distributed_at'] = current_time('mysql');
        }
        $wpdb->update($recipients_table, $update_data, ['id' => $recipient_id]);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-aid&action=recipients&program_id=' . $program_id));
    exit;
}

// ============================================================
// Edit mode: fetch program
// ============================================================
$edit_program = null;
if ($action === 'edit') {
    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($edit_id > 0) {
        $edit_program = $wpdb->get_row($wpdb->prepare("SELECT * FROM $programs_table WHERE id = %d", $edit_id));
    }
    if (!$edit_program) {
        wp_redirect(admin_url('admin.php?page=wp-desa-aid'));
        exit;
    }
}

// ============================================================
// Recipients view: fetch program & recipients
// ============================================================
$program = null;
$recipients = [];
$recipient_total_items = 0;
$recipient_total_pages = 0;

if ($action === 'recipients') {
    $program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
    if ($program_id > 0) {
        $program = $wpdb->get_row($wpdb->prepare("SELECT * FROM $programs_table WHERE id = %d", $program_id));
    }
    if (!$program) {
        wp_redirect(admin_url('admin.php?page=wp-desa-aid'));
        exit;
    }

    $rpaged  = isset($_GET['rpaged']) ? max(1, intval($_GET['rpaged'])) : 1;
    $roffset = ($rpaged - 1) * $per_page;

    $recipient_total_items = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $recipients_table WHERE program_id = %d", $program_id
    ));
    $recipient_total_pages = max(1, ceil($recipient_total_items / $per_page));

    $recipients = $wpdb->get_results($wpdb->prepare(
        "SELECT r.*, res.nik, res.nama_lengkap, res.alamat, res.jenis_kelamin
         FROM $recipients_table r
         JOIN $residents_table res ON r.resident_id = res.id
         WHERE r.program_id = %d
         ORDER BY r.created_at DESC
         LIMIT %d OFFSET %d",
        $program_id,
        $per_page,
        $roffset
    ));
}

// ============================================================
// List mode: query programs with pagination
// ============================================================
$paged       = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset      = ($paged - 1) * $per_page;
$total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $programs_table");
$total_pages = max(1, ceil($total_items / $per_page));
$programs    = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $programs_table ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

// ============================================================
// Helper functions
// ============================================================
function wp_desa_aid_status_badge($status)
{
    $map = [
        'active' => ['success', 'Aktif'],
        'closed' => ['secondary', 'Tutup'],
    ];
    $s = isset($map[$status]) ? $map[$status] : ['default', $status];
    return '<span class="wp-desa-badge wp-desa-badge-' . $s[0] . '">' . esc_html($s[1]) . '</span>';
}

function wp_desa_recipient_status_badge($status)
{
    $map = [
        'pending'     => ['warning', 'Pending'],
        'approved'    => ['success', 'Disetujui'],
        'rejected'    => ['danger', 'Ditolak'],
        'distributed' => ['info', 'Disalurkan'],
    ];
    $s = isset($map[$status]) ? $map[$status] : ['default', $status];
    return '<span class="wp-desa-badge wp-desa-badge-' . $s[0] . '">' . esc_html($s[1]) . '</span>';
}

function wp_desa_format_rp($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}
?>
<div class="wrap wp-desa-wrapper">

    <?php if (isset($_GET['saved']) && $_GET['saved'] == 1): ?>
        <div class="notice notice-success is-dismissible">
            <p>Data berhasil disimpan.</p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'recipients' && $program): ?>
        <!-- ======== RECIPIENTS VIEW ======== -->
        <!-- Header -->
        <div class="wp-desa-header">
            <div>
                <a href="?page=wp-desa-aid" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="margin-bottom:8px;">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> Kembali
                </a>
                <h1 class="wp-desa-title">Penerima: <?php echo esc_html($program->name); ?></h1>
                <p class="wp-desa-helper">
                    Total Penerima: <strong><?php echo (int) $recipient_total_items; ?></strong> / Kuota: <strong><?php echo (int) $program->quota; ?></strong>
                </p>
            </div>
            <div class="wp-desa-actions">
                <?php if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): ?>
                    <button class="wp-desa-btn wp-desa-btn-danger btn-generate-dummy">
                        <span class="dashicons dashicons-database"></span> Generate Dummy
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Recipient Form -->
        <div class="wp-desa-card wp-desa-mb-20">
            <form method="post" action="?page=wp-desa-aid&action=add-recipient&program_id=<?php echo (int) $program->id; ?>" class="wp-desa-filter-bar">
                <input type="hidden" name="wp_desa_add_recipient" value="1">
                <label class="wp-desa-label" style="margin:0;white-space:nowrap;">Tambah Penerima via NIK</label>
                <input type="text" name="nik" required class="wp-desa-input" placeholder="16 digit NIK" maxlength="16" style="max-width:200px;">
                <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Tambahkan</button>
            </form>
        </div>

        <!-- Recipients Table -->
        <div class="wp-desa-card">
            <div style="overflow-x:auto">
                <table class="wp-desa-table">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Alamat</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th>Tgl Disalurkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recipients)): ?>
                            <?php foreach ($recipients as $r): ?>
                                <tr>
                                    <td class="wp-desa-mono"><?php echo esc_html($r->nik); ?></td>
                                    <td><?php echo esc_html($r->nama_lengkap); ?></td>
                                    <td><?php echo esc_html($r->alamat); ?></td>
                                    <td><?php echo esc_html($r->jenis_kelamin); ?></td>
                                    <td>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="wp_desa_update_recipient_status" value="1">
                                            <input type="hidden" name="recipient_id" value="<?php echo (int) $r->id; ?>">
                                            <select name="status" class="wp-desa-select wp-desa-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php selected($r->status, 'pending'); ?>>Pending</option>
                                                <option value="approved" <?php selected($r->status, 'approved'); ?>>Disetujui</option>
                                                <option value="rejected" <?php selected($r->status, 'rejected'); ?>>Ditolak</option>
                                                <option value="distributed" <?php selected($r->status, 'distributed'); ?>>Disalurkan</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo $r->distributed_at ? esc_html($r->distributed_at) : '-'; ?></td>
                                    <td>
                                        <button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm btn-delete-recipient" data-id="<?php echo (int) $r->id; ?>">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="wp-desa-empty-state">
                                    <span class="dashicons dashicons-warning"></span>
                                    <div class="wp-desa-mt-8">Belum ada penerima terdaftar.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($recipient_total_items > $per_page): ?>
                <div class="wp-desa-pagination">
                    <div class="wp-desa-pagination-info">
                        Menampilkan <?php echo $roffset + 1; ?>–<?php echo min($roffset + $per_page, $recipient_total_items); ?> dari <?php echo $recipient_total_items; ?> data
                    </div>
                    <div class="wp-desa-pagination-controls">
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $rpaged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>"
                           href="?page=wp-desa-aid&action=recipients&program_id=<?php echo (int) $program->id; ?><?php echo $rpaged > 2 ? '&rpaged=' . ($rpaged - 1) : ''; ?>">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </a>
                        <span class="wp-desa-pagination-page">Halaman <?php echo $rpaged; ?> dari <?php echo $recipient_total_pages; ?></span>
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $rpaged >= $recipient_total_pages ? 'wp-desa-btn-disabled' : ''; ?>"
                           href="?page=wp-desa-aid&action=recipients&program_id=<?php echo (int) $program->id; ?>&rpaged=<?php echo $rpaged + 1; ?>">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- ======== PROGRAM FORM VIEW ======== -->
        <div class="wp-desa-header">
            <div>
                <a href="?page=wp-desa-aid" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="margin-bottom:8px;">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> Kembali
                </a>
                <h1 class="wp-desa-title"><?php echo $action === 'edit' ? 'Edit Program' : 'Tambah Program'; ?></h1>
                <p class="wp-desa-helper"><?php echo $action === 'edit' ? 'Perbarui data program bantuan.' : 'Buat program bantuan sosial baru.'; ?></p>
            </div>
        </div>

        <div class="wp-desa-card">
            <form method="post">
                <input type="hidden" name="wp_desa_save_program" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_program ? (int) $edit_program->id : 0; ?>">

                <div class="wp-desa-form-grid">
                    <div class="wp-desa-form-group full-width">
                        <label class="wp-desa-label" for="prog-name">Nama Program <span class="wp-desa-req">*</span></label>
                        <input type="text" name="name" id="prog-name" required class="wp-desa-input" placeholder="Contoh: BLT Dana Desa"
                            value="<?php echo $edit_program ? esc_attr($edit_program->name) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="prog-origin">Asal Dana <span class="wp-desa-req">*</span></label>
                        <input type="text" name="origin" id="prog-origin" required class="wp-desa-input" placeholder="Contoh: Dana Desa / Kemensos"
                            value="<?php echo $edit_program ? esc_attr($edit_program->origin) : ''; ?>">
                    </div>
                    <div class="wp-desa-grid-2-16">
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="prog-year">Tahun Anggaran <span class="wp-desa-req">*</span></label>
                            <input type="number" name="year" id="prog-year" required class="wp-desa-input"
                                value="<?php echo $edit_program ? esc_attr($edit_program->year) : date('Y'); ?>">
                        </div>
                        <div class="wp-desa-form-group">
                            <label class="wp-desa-label" for="prog-quota">Kuota Penerima <span class="wp-desa-req">*</span></label>
                            <input type="number" name="quota" id="prog-quota" required class="wp-desa-input"
                                value="<?php echo $edit_program ? esc_attr($edit_program->quota) : ''; ?>">
                        </div>
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="prog-amount">Nominal Bantuan (Rp) <span class="wp-desa-req">*</span></label>
                        <input type="number" name="amount_per_recipient" id="prog-amount" required class="wp-desa-input" step="0.01"
                            value="<?php echo $edit_program ? esc_attr($edit_program->amount_per_recipient) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group full-width">
                        <label class="wp-desa-label" for="prog-desc">Deskripsi</label>
                        <textarea name="description" id="prog-desc" class="wp-desa-textarea" rows="3"><?php echo $edit_program ? esc_textarea($edit_program->description) : ''; ?></textarea>
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="prog-status">Status</label>
                        <select name="status" id="prog-status" class="wp-desa-select">
                            <option value="active" <?php selected($edit_program && $edit_program->status === 'active'); ?>>Aktif</option>
                            <option value="closed" <?php selected($edit_program && $edit_program->status === 'closed'); ?>>Tutup</option>
                        </select>
                    </div>
                </div>

                <div class="wp-desa-form-actions">
                    <a href="?page=wp-desa-aid" class="wp-desa-btn wp-desa-btn-secondary">Batal</a>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Program</button>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- ======== PROGRAM LIST VIEW ======== -->
        <!-- Header -->
        <div class="wp-desa-header">
            <div>
                <h1 class="wp-desa-title">Program & Bantuan Sosial</h1>
                <p class="wp-desa-helper">Kelola program bantuan dan penerima manfaat.</p>
            </div>
            <div class="wp-desa-actions">
                <?php if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): ?>
                    <button class="wp-desa-btn wp-desa-btn-danger btn-generate-dummy">
                        <span class="dashicons dashicons-database"></span> Generate Dummy
                    </button>
                <?php endif; ?>
                <a href="?page=wp-desa-aid&action=add" class="wp-desa-btn wp-desa-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Tambah Program
                </a>
            </div>
        </div>

        <div class="wp-desa-card">
            <div style="overflow-x:auto">
                <table class="wp-desa-table">
                    <thead>
                        <tr>
                            <th>Nama Program</th>
                            <th>Asal Dana</th>
                            <th>Tahun</th>
                            <th>Kuota</th>
                            <th>Nominal / Penerima</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($programs)): ?>
                            <?php foreach ($programs as $p): ?>
                                <tr>
                                    <td>
                                        <strong class="wp-desa-row-title"><?php echo esc_html($p->name); ?></strong>
                                        <?php if ($p->description): ?>
                                            <span class="wp-desa-row-subtitle"><?php echo esc_html($p->description); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($p->origin); ?></td>
                                    <td><?php echo (int) $p->year; ?></td>
                                    <td><?php echo (int) $p->quota; ?> orang</td>
                                    <td><?php echo wp_desa_format_rp($p->amount_per_recipient); ?></td>
                                    <td><?php echo wp_desa_aid_status_badge($p->status); ?></td>
                                    <td>
                                        <div class="wp-desa-inline-actions">
                                            <a href="?page=wp-desa-aid&action=recipients&program_id=<?php echo (int) $p->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Kelola Penerima</a>
                                            <a href="?page=wp-desa-aid&action=edit&id=<?php echo (int) $p->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">
                                                <span class="dashicons dashicons-edit"></span>
                                            </a>
                                            <button class="wp-desa-btn wp-desa-btn-danger wp-desa-btn-sm btn-delete-aid" data-id="<?php echo (int) $p->id; ?>">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="wp-desa-empty-state">
                                    <span class="dashicons dashicons-warning"></span>
                                    <div class="wp-desa-mt-8">Belum ada program bantuan.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_items > $per_page): ?>
                <div class="wp-desa-pagination">
                    <div class="wp-desa-pagination-info">
                        Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?> dari <?php echo $total_items; ?> data
                    </div>
                    <div class="wp-desa-pagination-controls">
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>"
                           href="?page=wp-desa-aid<?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?>">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </a>
                        <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>"
                           href="?page=wp-desa-aid&paged=<?php echo $paged + 1; ?>">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
