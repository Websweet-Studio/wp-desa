<?php
/**
 * Template: Struktur Organisasi Perangkat Desa
 *
 * @package WP_Desa
 */
?>
<div class="wp-desa-wrapper">
    <div class="wp-desa-header-actions">
        <h2 style="margin: 0;">Daftar Perangkat Desa</h2>
        <button type="button" class="button button-primary" id="wp-desa-add-perangkat-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Perangkat
        </button>
    </div>

    <!-- Table -->
    <div class="wp-desa-card" style="margin-top: 20px;">
        <table class="wp-list-table widefat fixed striped" id="wp-desa-perangkat-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 60px;">Foto</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>NIP</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px;">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div id="wp-desa-perangkat-modal" class="wp-desa-modal" style="display: none;">
    <div class="wp-desa-modal-overlay"></div>
    <div class="wp-desa-modal-content">
        <div class="wp-desa-modal-header">
            <h3 id="wp-desa-perangkat-modal-title">Tambah Perangkat</h3>
            <button type="button" class="wp-desa-modal-close" id="wp-desa-perangkat-modal-close">&times;</button>
        </div>
        <form id="wp-desa-perangkat-form">
            <input type="hidden" id="wp-desa-perangkat-id" name="id" value="">
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="wp-desa-perangkat-nama" name="nama" class="wp-desa-input" required>
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Jabatan <span class="required">*</span></label>
                <select id="wp-desa-perangkat-jabatan" name="jabatan" class="wp-desa-select" required>
                    <option value="">-- Pilih Jabatan --</option>
                    <option value="Kepala Desa">Kepala Desa</option>
                    <option value="Sekretaris Desa">Sekretaris Desa</option>
                    <option value="Kasi Pemerintahan">Kasi Pemerintahan</option>
                    <option value="Kasi Kesejahteraan">Kasi Kesejahteraan</option>
                    <option value="Kasi Pelayanan">Kasi Pelayanan</option>
                    <option value="Kaur Keuangan">Kaur Keuangan</option>
                    <option value="Kaur Umum">Kaur Umum</option>
                    <option value="Kaur Perencanaan">Kaur Perencanaan</option>
                    <option value="Kadus">Kadus</option>
                </select>
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">NIP</label>
                <input type="text" id="wp-desa-perangkat-nip" name="nip" class="wp-desa-input" placeholder="Opsional">
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Foto</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" id="wp-desa-perangkat-foto" name="foto" value="">
                    <img id="wp-desa-perangkat-foto-preview" src="" style="width: 80px; height: 80px; border-radius: 4px; object-fit: cover; background: #f0f0f0; display: none;">
                    <button type="button" class="button" id="wp-desa-perangkat-foto-btn">Pilih Foto</button>
                    <button type="button" class="button button-link-delete" id="wp-desa-perangkat-foto-remove" style="display: none;">Hapus</button>
                </div>
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Atasan Langsung</label>
                <select id="wp-desa-perangkat-parent" name="parent_id" class="wp-desa-select">
                    <option value="">-- Tidak ada (Jabatan Puncak) --</option>
                </select>
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Urutan Tampil</label>
                <input type="number" id="wp-desa-perangkat-urutan" name="urutan" class="wp-desa-input" value="0" min="0">
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="button" id="wp-desa-perangkat-cancel">Batal</button>
                <button type="submit" class="button button-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(function($) {
    var $table = $('#wp-desa-perangkat-table tbody');
    var $modal = $('#wp-desa-perangkat-modal');
    var $form = $('#wp-desa-perangkat-form');
    var apiBase = wpDesaSettings.restBase + '/perangkat';
    var nonce = wpDesaSettings.nonce;
    var mediaFrame;

    function loadData() {
        $table.html('<tr><td colspan="6" style="text-align:center;padding:30px;">Memuat data...</td></tr>');
        $.ajax({
            url: apiBase,
            method: 'GET',
            headers: { 'X-WP-Nonce': nonce },
            success: function(res) {
                var items = res || [];
                if (!items.length) {
                    $table.html('<tr><td colspan="6" style="text-align:center;padding:30px;">Belum ada data perangkat. Klik "Tambah Perangkat" untuk menambahkan.</td></tr>');
                    return;
                }
                var rows = '';
                $.each(items, function(i, item) {
                    var foto = item.foto ? '<img src="' + escapeHtml(item.foto) + '" style="width:40px;height:40px;border-radius:4px;object-fit:cover;">' : '<span class="dashicons dashicons-admin-users" style="font-size:40px;width:40px;height:40px;color:#ccc;"></span>';
                    rows += '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + foto + '</td>' +
                        '<td><strong>' + escapeHtml(item.nama) + '</strong></td>' +
                        '<td>' + escapeHtml(item.jabatan) + '</td>' +
                        '<td>' + (item.nip || '-') + '</td>' +
                        '<td>' +
                            '<button type="button" class="button button-small wp-desa-edit-perangkat" data-id="' + item.id + '">Edit</button> ' +
                            '<button type="button" class="button button-small button-link-delete wp-desa-del-perangkat" data-id="' + item.id + '">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                });
                $table.html(rows);
            },
            error: function() {
                $table.html('<tr><td colspan="6" style="text-align:center;padding:30px;color:red;">Gagal memuat data.</td></tr>');
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<span>').text(text).html();
    }

    function loadParentOptions(exceptId) {
        var $parent = $('#wp-desa-perangkat-parent');
        $parent.html('<option value="">-- Tidak ada (Jabatan Puncak) --</option>');
        $.get(apiBase, function(res) {
            $.each(res || [], function(i, item) {
                if (item.id == exceptId) return;
                $parent.append('<option value="' + item.id + '">' + escapeHtml(item.nama) + ' (' + escapeHtml(item.jabatan) + ')</option>');
            });
        });
    }

    function resetForm() {
        $form[0].reset();
        $('#wp-desa-perangkat-id').val('');
        $('#wp-desa-perangkat-foto').val('');
        $('#wp-desa-perangkat-foto-preview').hide();
        $('#wp-desa-perangkat-foto-remove').hide();
        $('#wp-desa-perangkat-modal-title').text('Tambah Perangkat');
        loadParentOptions();
    }

    // Add
    $('#wp-desa-add-perangkat-btn').on('click', function() {
        resetForm();
        $modal.show();
    });

    // Close modal
    $('#wp-desa-perangkat-modal-close, #wp-desa-perangkat-cancel, .wp-desa-modal-overlay').on('click', function() {
        $modal.hide();
    });

    // Media uploader
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

    // Edit
    $table.on('click', '.wp-desa-edit-perangkat', function() {
        var id = $(this).data('id');
        resetForm();
        $('#wp-desa-perangkat-modal-title').text('Edit Perangkat');
        $.get(apiBase, function(items) {
            var item = $.grep(items, function(o) { return o.id == id; })[0];
            if (!item) return;
            $('#wp-desa-perangkat-id').val(item.id);
            $('#wp-desa-perangkat-nama').val(item.nama);
            $('#wp-desa-perangkat-jabatan').val(item.jabatan);
            $('#wp-desa-perangkat-nip').val(item.nip);
            if (item.foto) {
                $('#wp-desa-perangkat-foto').val(item.foto);
                $('#wp-desa-perangkat-foto-preview').attr('src', item.foto).show();
                $('#wp-desa-perangkat-foto-remove').show();
            }
            loadParentOptions(id);
            if (item.parent_id) $('#wp-desa-perangkat-parent').val(item.parent_id);
            $('#wp-desa-perangkat-urutan').val(item.urutan || 0);
            $modal.show();
        });
    });

    // Delete
    $table.on('click', '.wp-desa-del-perangkat', function() {
        if (!confirm('Yakin hapus perangkat ini?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: apiBase + '/' + id,
            method: 'DELETE',
            headers: { 'X-WP-Nonce': nonce },
            success: function() { loadData(); },
            error: function() { alert('Gagal menghapus data.'); }
        });
    });

    // Submit form
    $form.on('submit', function(e) {
        e.preventDefault();
        var id = $('#wp-desa-perangkat-id').val();
        var data = {
            nama: $('#wp-desa-perangkat-nama').val(),
            jabatan: $('#wp-desa-perangkat-jabatan').val(),
            nip: $('#wp-desa-perangkat-nip').val(),
            foto: $('#wp-desa-perangkat-foto').val(),
            parent_id: $('#wp-desa-perangkat-parent').val() || 0,
            urutan: $('#wp-desa-perangkat-urutan').val() || 0,
        };

        var method = id ? 'PUT' : 'POST';
        var url = id ? (apiBase + '/' + id) : apiBase;

        $.ajax({
            url: url,
            method: method,
            headers: { 'X-WP-Nonce': nonce },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function() {
                $modal.hide();
                loadData();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan data.';
                alert(msg);
            }
        });
    });

    // Initial load
    loadData();
});
</script>

<style>
.wp-desa-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000; }
.wp-desa-modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.wp-desa-modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 24px; border-radius: 8px; width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.wp-desa-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.wp-desa-modal-header h3 { margin: 0; }
.wp-desa-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; line-height: 1; }
.wp-desa-modal-close:hover { color: #000; }
.wp-desa-header-actions { display: flex; justify-content: space-between; align-items: center; }
</style>
