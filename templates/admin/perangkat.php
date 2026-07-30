<?php
/**
 * Template: Struktur Organisasi Perangkat Desa
 *
 * @package WP_Desa
 */

global $wpdb;
$table_perangkat = $wpdb->prefix . 'desa_perangkat';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ============================================================
// Handle POST: save / update
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_desa_save_perangkat'])) {
    check_admin_referer('wp_desa_perangkat_action', 'wp_desa_perangkat_nonce');

    $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nama     = sanitize_text_field($_POST['nama']);
    $jabatan  = sanitize_text_field($_POST['jabatan']);
    $nip      = sanitize_text_field($_POST['nip']);
    $foto     = esc_url_raw($_POST['foto']);
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    $urutan   = isset($_POST['urutan']) ? intval($_POST['urutan']) : 0;

    $data = [
        'nama'      => $nama,
        'jabatan'   => $jabatan,
        'nip'       => $nip,
        'foto'      => $foto,
        'parent_id' => $parent_id,
        'urutan'    => $urutan,
    ];

    if ($id > 0) {
        $wpdb->update($table_perangkat, $data, ['id' => $id]);
    } else {
        $wpdb->insert($table_perangkat, $data);
    }

    wp_redirect(admin_url('admin.php?page=wp-desa-pemerintahan&tab=struktur&saved=1'));
    exit;
}

// ============================================================
// Handle delete via GET (simple approach)
// ============================================================
if (isset($_GET['delete']) && $edit_id > 0) {
    check_admin_referer('wp_desa_delete_perangkat_' . $edit_id);
    $wpdb->delete($table_perangkat, ['id' => $edit_id]);
    wp_redirect(admin_url('admin.php?page=wp-desa-pemerintahan&tab=struktur&deleted=1'));
    exit;
}

// ============================================================
// Edit mode: fetch record
// ============================================================
$edit_item = null;
if ($action === 'edit' && $edit_id > 0) {
    $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_perangkat WHERE id = %d", $edit_id));
    if (!$edit_item) {
        wp_redirect(admin_url('admin.php?page=wp-desa-pemerintahan&tab=struktur'));
        exit;
    }
}

// ============================================================
// List mode: fetch all items
// ============================================================
$all_perangkat = $wpdb->get_results("SELECT * FROM $table_perangkat ORDER BY urutan ASC, id ASC");
$success_msg = '';
if (isset($_GET['saved'])) $success_msg = 'Data perangkat berhasil disimpan.';
if (isset($_GET['deleted'])) $success_msg = 'Data perangkat berhasil dihapus.';
?>
<div class="wp-desa-wrapper">

    <?php if ($success_msg): ?>
        <div class="wp-desa-card" style="padding:16px;margin-bottom:20px;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;">
            <?php echo esc_html($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (in_array($action, ['add', 'edit'])): ?>
        <!-- ======== FORM VIEW ======== -->
        <div class="wp-desa-card" style="padding:var(--sp-xl);">
            <h3 style="margin:0 0 var(--sp-lg) 0;"><?php echo $edit_item ? 'Edit Perangkat' : 'Tambah Perangkat'; ?></h3>
            <form method="post" id="wp-desa-perangkat-form">
                <?php wp_nonce_field('wp_desa_perangkat_action', 'wp_desa_perangkat_nonce'); ?>
                <input type="hidden" name="wp_desa_save_perangkat" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_item ? (int) $edit_item->id : 0; ?>">

                <div class="wp-desa-form-grid">
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" class="wp-desa-input" required
                            value="<?php echo $edit_item ? esc_attr($edit_item->nama) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Jabatan <span class="required">*</span></label>
                        <select name="jabatan" class="wp-desa-select" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php
                            $jabatan_list = ['Kepala Desa', 'Sekretaris Desa', 'Kasi Pemerintahan', 'Kasi Kesejahteraan', 'Kasi Pelayanan', 'Kaur Keuangan', 'Kaur Umum', 'Kaur Perencanaan', 'Kadus'];
                            foreach ($jabatan_list as $j) {
                                $selected = ($edit_item && $edit_item->jabatan === $j) ? 'selected' : '';
                                echo '<option value="' . esc_attr($j) . '" ' . $selected . '>' . esc_html($j) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">NIP</label>
                        <input type="text" name="nip" class="wp-desa-input" placeholder="Opsional"
                            value="<?php echo $edit_item ? esc_attr($edit_item->nip) : ''; ?>">
                    </div>
                    <div class="wp-desa-form-group">
                        <label class="wp-desa-label">Urutan Tampil</label>
                        <input type="number" name="urutan" class="wp-desa-input" value="<?php echo $edit_item ? esc_attr($edit_item->urutan) : '0'; ?>" min="0">
                    </div>
                </div>

                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Foto</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="hidden" name="foto" id="wp-desa-perangkat-foto" value="<?php echo $edit_item ? esc_attr($edit_item->foto) : ''; ?>">
                        <img id="wp-desa-perangkat-foto-preview" src="<?php echo $edit_item && $edit_item->foto ? esc_attr($edit_item->foto) : ''; ?>"
                            style="width:80px;height:80px;border-radius:4px;object-fit:cover;background:#f0f0f0;<?php echo $edit_item && $edit_item->foto ? '' : 'display:none;'; ?>">
                        <button type="button" class="button" id="wp-desa-perangkat-foto-btn">Pilih Foto</button>
                        <button type="button" class="button button-link-delete" id="wp-desa-perangkat-foto-remove" style="<?php echo $edit_item && $edit_item->foto ? '' : 'display:none;'; ?>">Hapus</button>
                    </div>
                </div>

                <div class="wp-desa-form-group">
                    <label class="wp-desa-label">Atasan Langsung</label>
                    <select name="parent_id" class="wp-desa-select">
                        <option value="">-- Tidak ada (Jabatan Puncak) --</option>
                        <?php
                        $except_id = $edit_item ? (int) $edit_item->id : 0;
                        foreach ($all_perangkat as $p) {
                            if ((int) $p->id === $except_id) continue;
                            $selected = ($edit_item && (int) $edit_item->parent_id === (int) $p->id) ? 'selected' : '';
                            echo '<option value="' . (int) $p->id . '" ' . $selected . '>' . esc_html($p->nama) . ' (' . esc_html($p->jabatan) . ')</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="wp-desa-form-actions" style="margin-top:var(--sp-lg);">
                    <a href="?page=wp-desa-pemerintahan&tab=struktur" class="wp-desa-btn wp-desa-btn-secondary">Batal</a>
                    <button type="submit" class="wp-desa-btn wp-desa-btn-primary">Simpan</button>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- ======== LIST VIEW ======== -->
        <div class="wp-desa-header-actions">
            <h2 style="margin:0;">Daftar Perangkat Desa</h2>
            <a href="?page=wp-desa-pemerintahan&tab=struktur&action=add" class="wp-desa-btn wp-desa-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Perangkat
            </a>
        </div>

        <div class="wp-desa-card" style="margin-top:20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:60px;">Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>NIP</th>
                        <th style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_perangkat)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($all_perangkat as $p): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php if ($p->foto): ?>
                                        <img src="<?php echo esc_url($p->foto); ?>" style="width:40px;height:40px;border-radius:4px;object-fit:cover;">
                                    <?php else: ?>
                                        <span class="dashicons dashicons-admin-users" style="font-size:40px;width:40px;height:40px;color:#ccc;"></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($p->nama); ?></strong></td>
                                <td><?php echo esc_html($p->jabatan); ?></td>
                                <td><?php echo $p->nip ? esc_html($p->nip) : '-'; ?></td>
                                <td>
                                    <a href="?page=wp-desa-pemerintahan&tab=struktur&action=edit&id=<?php echo (int) $p->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm">Edit</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-desa-pemerintahan&tab=struktur&action=edit&delete=1&id=' . (int) $p->id), 'wp_desa_delete_perangkat_' . (int) $p->id); ?>" class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm" onclick="return confirm('Yakin hapus perangkat ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:30px;">
                                Belum ada data perangkat. <a href="?page=wp-desa-pemerintahan&tab=struktur&action=add">Tambah sekarang</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (in_array($action, ['add', 'edit'])): ?>
<script>
jQuery(function($) {
    var mediaFrame;
    $('#wp-desa-perangkat-foto-btn').on('click', function(e) {
        e.preventDefault();
        if (mediaFrame) { mediaFrame.open(); return; }
        mediaFrame = wp.media({ title: 'Pilih Foto Perangkat', button: { text: 'Gunakan Foto' }, multiple: false });
        mediaFrame.on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#wp-desa-perangkat-foto').val(attachment.url);
            $('#wp-desa-perangkat-foto-preview').attr('src', attachment.url).show();
            $('#wp-desa-perangkat-foto-remove').show();
        });
        mediaFrame.open();
    });
    $('#wp-desa-perangkat-foto-remove').on('click', function() {
        $('#wp-desa-perangkat-foto').val('');
        $('#wp-desa-perangkat-foto-preview').hide();
        $(this).hide();
    });
});
</script>
<?php endif; ?>
