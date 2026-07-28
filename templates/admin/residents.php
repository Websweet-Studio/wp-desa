<?php
global $wpdb;
$table_name = $wpdb->prefix . 'desa_residents';
$settings   = get_option('wp_desa_settings', []);
$per_page   = 20;
$action     = isset($_GET['action']) ? $_GET['action'] : 'list';

// ============================================================
// Handle POST: save / update resident
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_save_resident'])) {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nik      = isset($_POST['nik']) ? sanitize_text_field($_POST['nik']) : '';
    $no_kk    = isset($_POST['no_kk']) ? sanitize_text_field($_POST['no_kk']) : '';
    $nama     = isset($_POST['nama_lengkap']) ? sanitize_text_field($_POST['nama_lengkap']) : '';
    $jk       = isset($_POST['jenis_kelamin']) ? sanitize_text_field($_POST['jenis_kelamin']) : '';
    $sp       = isset($_POST['status_perkawinan']) ? sanitize_text_field($_POST['status_perkawinan']) : '';
    $tl       = isset($_POST['tempat_lahir']) ? sanitize_text_field($_POST['tempat_lahir']) : '';
    $tgl      = isset($_POST['tanggal_lahir']) ? sanitize_text_field($_POST['tanggal_lahir']) : '';
    $pkj      = isset($_POST['pekerjaan']) ? sanitize_text_field($_POST['pekerjaan']) : '';
    $alamat   = isset($_POST['alamat']) ? sanitize_textarea_field($_POST['alamat']) : '';
    $errors   = [];

    if (empty($nik)) $errors[] = 'NIK wajib diisi.';
    if (empty($nama)) $errors[] = 'Nama lengkap wajib diisi.';

    // Check duplicate NIK
    if (empty($errors) && $id > 0) {
        $dup = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE nik = %s AND id != %d", $nik, $id));
    } elseif (empty($errors)) {
        $dup = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE nik = %s", $nik));
    }
    if (!empty($dup)) $errors[] = 'NIK sudah terdaftar.';

    if (empty($errors)) {
        $data = [
            'nik'               => $nik,
            'no_kk'             => $no_kk,
            'nama_lengkap'      => $nama,
            'jenis_kelamin'     => $jk,
            'status_perkawinan' => $sp,
            'tempat_lahir'      => $tl,
            'tanggal_lahir'     => $tgl,
            'pekerjaan'         => $pkj,
            'alamat'            => $alamat,
        ];

        if ($id > 0) {
            $wpdb->update($table_name, $data, ['id' => $id]);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
        }

        wp_redirect(admin_url('admin.php?page=wp-desa-residents&saved=1'));
        exit;
    }
}

// ============================================================
// Edit mode: fetch resident
// ============================================================
$edit_resident = null;
if ($action === 'edit') {
    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($edit_id > 0) {
        $edit_resident = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id));
    }
    if (!$edit_resident) {
        wp_redirect(admin_url('admin.php?page=wp-desa-residents'));
        exit;
    }
}

// ============================================================
// List mode: query residents with pagination
// ============================================================
$paged      = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset     = ($paged - 1) * $per_page;
$total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = max(1, ceil($total_items / $per_page));
$residents   = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

function wp_desa_status_badge($status)
{
    $status = $status ?: 'Belum Kawin';
    $cls = 'default';
    if ($status === 'Kawin') $cls = 'success';
    elseif (in_array($status, ['Cerai Hidup', 'Cerai Mati'])) $cls = 'danger';
    return '<span class="wp-desa-badge wp-desa-badge-' . $cls . '">' . esc_html($status) . '</span>';
}
?>
<div class="wrap wp-desa-wrapper">

    <?php if ($action !== 'add'): ?>
        <!-- Header -->
        <div class="wp-desa-header">
            <div>
                <?php if ($action === 'edit'): ?>
                    <a href="?page=wp-desa-residents" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" style="margin-bottom:8px;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> Kembali
                    </a>
                <?php endif; ?>
                <h1 class="wp-desa-title">
                    <?php echo $action === 'edit' ? 'Edit Penduduk' : 'Data Penduduk'; ?>
                </h1>
                <p class="wp-desa-helper">
                    <?php echo $action === 'edit' ? 'Perbarui data penduduk.' : 'Kelola data kependudukan desa dengan mudah.'; ?>
                </p>
            </div>
            <?php if ($action === 'list'): ?>
                <div class="wp-desa-actions">
                    <?php if (!empty($settings['dev_mode']) && $settings['dev_mode'] == 1): ?>
                        <button class="wp-desa-btn wp-desa-btn-danger">
                            <span class="dashicons dashicons-database"></span> Generate Dummy
                        </button>
                    <?php endif; ?>
                    <button class="wp-desa-btn wp-desa-btn-secondary">
                        <span class="dashicons dashicons-download"></span> Export
                    </button>
                    <button class="wp-desa-btn wp-desa-btn-secondary">
                        <span class="dashicons dashicons-upload"></span> Import
                    </button>
                    <a href="?page=wp-desa-residents&action=add" class="wp-desa-btn wp-desa-btn-primary">
                        <span class="dashicons dashicons-plus-alt2"></span> Tambah Penduduk
                    </a>
                    <input type="file" style="display:none" accept=".csv">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved']) && $_GET['saved'] == 1): ?>
        <div class="notice notice-success is-dismissible">
            <p>Data berhasil disimpan.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error is-dismissible">
            <?php foreach ($errors as $e): ?><p><?php echo esc_html($e); ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- ======== TABLE VIEW ======== -->
        <div class="wp-desa-card">
            <div style="overflow-x:auto">
                <table class="wp-desa-table">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>No. KK</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Tempat/Tgl Lahir</th>
                            <th>Status Perkawinan</th>
                            <th>Pekerjaan</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($residents)): ?>
                            <?php foreach ($residents as $r): ?>
                                <tr>
                                    <td class="wp-desa-mono"><?php echo esc_html($r->nik); ?></td>
                                    <td class="wp-desa-mono"><?php echo esc_html($r->no_kk ?? '-'); ?></td>
                                    <td><?php echo esc_html($r->nama_lengkap); ?></td>
                                    <td><?php echo esc_html($r->jenis_kelamin); ?></td>
                                    <td><?php echo esc_html($r->tempat_lahir ? $r->tempat_lahir . ', ' . $r->tanggal_lahir : ($r->tanggal_lahir ?: '-')); ?></td>
                                    <td><?php echo wp_desa_status_badge($r->status_perkawinan); ?></td>
                                    <td><?php echo esc_html($r->pekerjaan ?: '-'); ?></td>
                                    <td style="text-align:right;">
                                        <div class="wp-desa-inline-actions-end">
                                            <a href="?page=wp-desa-residents&action=edit&id=<?php echo (int)$r->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Edit</a>
                                            <button class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm btn-delete-resident" data-id="<?php echo (int)$r->id; ?>">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="wp-desa-empty-state">
                                    <span class="dashicons dashicons-warning"></span>
                                    <div class="wp-desa-mt-8">Belum ada data penduduk.</div>
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
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged <= 1 ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-residents<?php echo $paged > 2 ? '&paged=' . ($paged - 1) : ''; ?>">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </a>
                        <span class="wp-desa-pagination-page">Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                        <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm <?php echo $paged >= $total_pages ? 'wp-desa-btn-disabled' : ''; ?>" href="?page=wp-desa-residents&paged=<?php echo $paged + 1; ?>">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ======== FORM VIEW (add / edit) ======== -->
        <div class="wp-desa-card">
            <form method="post">
                <input type="hidden" name="wp_desa_save_resident" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_resident ? (int)$edit_resident->id : 0; ?>">

                <div class="wp-desa-form-grid">
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-nik">NIK <span class="wp-desa-req">*</span></label>
                        <input type="number" name="nik" id="res-nik" required class="wp-desa-input" placeholder="16 digit NIK" maxlength="16"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->nik) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-no_kk">No. KK</label>
                        <input type="number" name="no_kk" id="res-no_kk" class="wp-desa-input" placeholder="16 digit No. KK" maxlength="16"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->no_kk) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group full-width">
                        <label class="wp-desa-label" for="res-nama">Nama Lengkap <span class="wp-desa-req">*</span></label>
                        <input type="text" name="nama_lengkap" id="res-nama" required class="wp-desa-input" placeholder="Sesuai KTP"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->nama_lengkap) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-jk">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="res-jk" class="wp-desa-select">
                            <option value="Laki-laki" <?php selected($edit_resident && $edit_resident->jenis_kelamin === 'Laki-laki'); ?>>Laki-laki</option>
                            <option value="Perempuan" <?php selected($edit_resident && $edit_resident->jenis_kelamin === 'Perempuan'); ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-sp">Status Perkawinan</label>
                        <select name="status_perkawinan" id="res-sp" class="wp-desa-select">
                            <?php
                            $status_options = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
                            $current_sp = $edit_resident ? $edit_resident->status_perkawinan : 'Belum Kawin';
                            foreach ($status_options as $opt):
                            ?>
                                <option value="<?php echo $opt; ?>" <?php selected($current_sp === $opt); ?>><?php echo $opt; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-tl">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="res-tl" class="wp-desa-input"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->tempat_lahir) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label" for="res-tgl">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="res-tgl" class="wp-desa-input"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->tanggal_lahir) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group full-width">
                        <label class="wp-desa-label" for="res-pkj">Pekerjaan</label>
                        <input type="text" name="pekerjaan" id="res-pkj" class="wp-desa-input" placeholder="Contoh: Petani, PNS, Wiraswasta"
                            value="<?php echo $edit_resident ? esc_attr($edit_resident->pekerjaan) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group full-width">
                        <label class="wp-desa-label" for="res-alamat">Alamat Lengkap</label>
                        <textarea name="alamat" id="res-alamat" rows="3" class="wp-desa-textarea" placeholder="Jalan, RT/RW, Dusun..."><?php echo $edit_resident ? esc_textarea($edit_resident->alamat) : ''; ?></textarea>
                    </div>
                </div>

                <div class="wp-desa-form-actions">
                    <a href="?page=wp-desa-residents" class="wp-desa-btn wp-desa-btn-secondary">Batal</a>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

</div>