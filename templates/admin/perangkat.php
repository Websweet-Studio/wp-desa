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
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html($success_msg); ?></p></div>
    <?php endif; ?>

    <?php if (in_array($action, ['add', 'edit'])): ?>
        <!-- ======== FORM VIEW ======== -->
        <a href="?page=wp-desa-pemerintahan&tab=struktur" style="display:inline-flex;align-items:center;gap:4px;text-decoration:none;color:var(--graphite);font-size:13px;margin-bottom:var(--sp-sm);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="m15 18-6-6 6-6"/></svg> Kembali
        </a>
        <div class="wp-desa-card">
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
        <div class="wp-desa-card">
            <div class="wp-desa-filter-bar" style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-md);border-bottom:1px solid var(--fog);">
                <div style="display:flex;gap:var(--sp-sm);align-items:center;">
                    <span class="wp-desa-pagination-info">Total: <?php echo count($all_perangkat); ?> perangkat</span>
                </div>
                <div>
                    <a href="?page=wp-desa-pemerintahan&tab=struktur&action=add" class="wp-desa-btn wp-desa-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 .83.18 2 2 0 0 0 .83-.18l8.58-3.9a1 1 0 0 0 0-1.831z"/><path d="M16 17h6"/><path d="M19 14v6"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 .825.178"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l2.116-.962"/></svg> Tambah Perangkat
                    </a>
                </div>
            </div>
            <div style="overflow-x:auto">
            <table class="wp-desa-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:60px;">Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>NIP</th>
                        <th style="text-align:right;">Aksi</th>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;display:block;margin:0 auto;"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($p->nama); ?></strong></td>
                                <td><?php echo esc_html($p->jabatan); ?></td>
                                <td><?php echo $p->nip ? esc_html($p->nip) : '-'; ?></td>
                                <td style="text-align:right;">
                                    <div class="wp-desa-inline-actions-end">
                                    <a href="?page=wp-desa-pemerintahan&tab=struktur&action=edit&id=<?php echo (int) $p->id; ?>" class="wp-desa-btn wp-desa-btn-secondary wp-desa-btn-sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v2"/><path d="M21.34 15.664a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><path d="M8 22H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-desa-pemerintahan&tab=struktur&action=edit&delete=1&id=' . (int) $p->id), 'wp_desa_delete_perangkat_' . (int) $p->id); ?>" class="wp-desa-btn wp-desa-btn-danger-outline wp-desa-btn-sm" title="Hapus" onclick="return confirm('Yakin hapus perangkat ini?')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="wp-desa-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                                <div class="wp-desa-mt-8">Belum ada data perangkat. <a href="?page=wp-desa-pemerintahan&tab=struktur&action=add" style="text-decoration:underline;">Tambah sekarang</a>.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
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
