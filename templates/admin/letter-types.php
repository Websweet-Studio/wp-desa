<?php
global $wpdb;
$types_table = $wpdb->prefix . 'desa_letter_types';

// ============================================================
// POST: simpan perubahan / tambah jenis surat
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_save_letter_types'])) {
    check_admin_referer('wp_desa_letter_types');

    $names   = isset($_POST['name']) ? (array) $_POST['name'] : [];
    $descs   = isset($_POST['description']) ? (array) $_POST['description'] : [];
    $berlaku = isset($_POST['berlaku_bulan']) ? (array) $_POST['berlaku_bulan'] : [];

    foreach ($names as $id => $name) {
        $id = intval($id);
        if ($id <= 0) {
            continue;
        }
        $wpdb->update(
            $types_table,
            [
                'name'          => sanitize_text_field($name),
                'description'   => isset($descs[$id]) ? sanitize_textarea_field($descs[$id]) : '',
                'berlaku_bulan' => isset($berlaku[$id]) ? max(1, intval($berlaku[$id])) : 1,
            ],
            ['id' => $id]
        );
    }

    $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
    if ($new_name !== '') {
        $wpdb->insert($types_table, [
            'code'          => strtoupper(sanitize_text_field(isset($_POST['new_code']) ? $_POST['new_code'] : '')),
            'name'          => $new_name,
            'description'   => isset($_POST['new_description']) ? sanitize_textarea_field($_POST['new_description']) : '',
            'berlaku_bulan' => isset($_POST['new_berlaku_bulan']) ? max(1, intval($_POST['new_berlaku_bulan'])) : 1,
            'created_at'    => current_time('mysql'),
        ]);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=jenis&saved=1'));
    exit;
}

// ============================================================
// Hapus jenis surat
// ============================================================
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    check_admin_referer('wp_desa_delete_letter_type_' . $del_id);
    $wpdb->delete($types_table, ['id' => $del_id]);
    wp_redirect(admin_url('admin.php?page=wp-desa-layanan&tab=jenis&deleted=1'));
    exit;
}

$types = $wpdb->get_results("SELECT * FROM $types_table ORDER BY id ASC");
?>
<div class="wrap wp-desa-wrapper">

    <?php if (isset($_GET['saved']) && $_GET['saved'] == 1): ?>
        <div class="notice notice-success is-dismissible"><p>Jenis surat berhasil disimpan.</p></div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div class="notice notice-success is-dismissible"><p>Jenis surat berhasil dihapus.</p></div>
    <?php endif; ?>

    <div class="wp-desa-card">
        <h3 style="margin-top:0;">Jenis Surat &amp; Masa Berlaku</h3>
        <p class="wp-desa-row-subtitle">Atur masa berlaku (dalam bulan) tiap jenis surat. Surat yang diajukan warga otomatis berlaku sesuai jumlah bulan ini sejak tanggal pengajuan.</p>

        <form method="post">
            <?php wp_nonce_field('wp_desa_letter_types'); ?>
            <input type="hidden" name="wp_desa_save_letter_types" value="1">

            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th style="width:90px;">Kode</th>
                        <th>Nama Surat</th>
                        <th>Deskripsi</th>
                        <th style="width:130px;">Berlaku (bulan)</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($types)): ?>
                        <?php foreach ($types as $t): ?>
                            <tr>
                                <td class="wp-desa-mono"><?php echo esc_html($t->code); ?></td>
                                <td><input type="text" class="wp-desa-input" name="name[<?php echo (int) $t->id; ?>]" value="<?php echo esc_attr($t->name); ?>"></td>
                                <td><input type="text" class="wp-desa-input" name="description[<?php echo (int) $t->id; ?>]" value="<?php echo esc_attr($t->description); ?>"></td>
                                <td><input type="number" min="1" class="wp-desa-input" name="berlaku_bulan[<?php echo (int) $t->id; ?>]" value="<?php echo (int) $t->berlaku_bulan; ?>"></td>
                                <td style="text-align:right;">
                                    <a class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-desa-layanan&tab=jenis&delete=' . (int) $t->id), 'wp_desa_delete_letter_type_' . (int) $t->id)); ?>" onclick="return confirm('Hapus jenis surat ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="wp-desa-empty-state">Belum ada jenis surat.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="wp-desa-form-actions" style="margin-top:16px;">
                <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <div class="wp-desa-card" style="margin-top:20px;">
        <h3 style="margin-top:0;">Tambah Jenis Surat</h3>
        <form method="post">
            <?php wp_nonce_field('wp_desa_letter_types'); ?>
            <input type="hidden" name="wp_desa_save_letter_types" value="1">
            <div class="wp-desa-form-grid">
                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Kode</label>
                    <input type="text" name="new_code" class="wp-desa-input" placeholder="Contoh: SKTM" maxlength="10">
                </div>
                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Nama Surat</label>
                    <input type="text" name="new_name" class="wp-desa-input" placeholder="Nama jenis surat">
                </div>
                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Berlaku (bulan)</label>
                    <input type="number" min="1" name="new_berlaku_bulan" class="wp-desa-input" value="1">
                </div>
                <div class="wp-desa-form-group full-width">
                    <label class="wp-desa-label">Deskripsi</label>
                    <input type="text" name="new_description" class="wp-desa-input" placeholder="Keterangan singkat">
                </div>
            </div>
            <div class="wp-desa-form-actions" style="margin-top:12px;">
                <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Tambah</button>
            </div>
        </form>
    </div>
</div>
