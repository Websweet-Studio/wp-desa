<?php
/**
 * Template: Peta Wilayah Desa
 *
 * @package WP_Desa
 */

$settings = get_option('wp_desa_settings');
$map_data = isset($settings['peta_desa']) ? $settings['peta_desa'] : [];
$center_lat = isset($map_data['center_lat']) ? $map_data['center_lat'] : '-7.0';
$center_lng = isset($map_data['center_lng']) ? $map_data['center_lng'] : '110.0';
$zoom = isset($map_data['zoom']) ? $map_data['zoom'] : 13;
$markers = isset($map_data['markers']) ? $map_data['markers'] : [];
$polygon = isset($map_data['polygon']) ? $map_data['polygon'] : '';
?>
<div class="wp-desa-wrapper">
    <div style="display:flex; gap:var(--sp-xl); align-items:flex-start;">
        <!-- Sidebar: Marker Form -->
        <div style="width:340px; flex-shrink:0; background:var(--canvas); border:1px solid var(--fog); border-radius:var(--rounded-xl); padding:var(--sp-xl);">
            <h3 class="wp-desa-section-title" id="wp-desa-peta-marker-title" style="margin-bottom:var(--sp-lg);">Tambah Marker</h3>
            <input type="hidden" id="wp-desa-peta-marker-index" value="">
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Nama Lokasi</label>
                <input type="text" id="wp-desa-peta-marker-name" class="wp-desa-input" style="width:100%;" placeholder="Contoh: Kantor Desa Sukamaju">
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Jenis</label>
                <select id="wp-desa-peta-marker-type" class="wp-desa-select" style="width:100%;">
                    <option value="kantor-desa">Kantor Desa</option>
                    <option value="sekolah">Sekolah</option>
                    <option value="masjid">Masjid</option>
                    <option value="puskesmas">Puskesmas / Klinik</option>
                    <option value="pasar">Pasar</option>
                    <option value="wisata">Wisata</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-sm);">
                <div class="wp-desa-form-group" style="margin:0;">
                    <label class="wp-desa-label">Latitude</label>
                    <input type="text" id="wp-desa-peta-marker-lat" class="wp-desa-input" style="width:100%;">
                </div>
                <div class="wp-desa-form-group" style="margin:0;">
                    <label class="wp-desa-label">Longitude</label>
                    <input type="text" id="wp-desa-peta-marker-lng" class="wp-desa-input" style="width:100%;">
                </div>
            </div>
            <div class="wp-desa-form-group">
                <label class="wp-desa-label">Deskripsi</label>
                <textarea id="wp-desa-peta-marker-desc" class="wp-desa-textarea" style="width:100%;" rows="2" placeholder="Keterangan singkat..."></textarea>
            </div>
            <div style="display:flex; gap:var(--sp-sm);">
                <button type="button" class="wp-desa-btn wp-desa-btn-primary" id="wp-desa-peta-marker-save" style="flex:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Marker
                </button>
                <button type="button" class="wp-desa-btn wp-desa-btn-secondary" id="wp-desa-peta-marker-cancel">Batal</button>
            </div>
        </div>

        <!-- Map Area -->
        <div style="flex:1; min-width:0;">
            <div id="wp-desa-peta-map" style="width:100%; height:520px; border-radius:var(--rounded-xl); border:1px solid var(--fog);"></div>
            <p style="font-size:13px; color:var(--graphite); margin-top:var(--sp-xs); display:flex; align-items:center; gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"/></svg>
                Klik peta untuk menempatkan marker baru, lalu isi detail di samping.
            </p>
        </div>
    </div>

    <!-- Marker List -->
    <div class="wp-desa-card" style="margin-top:var(--sp-xl); padding:var(--sp-xl);">
        <div class="wp-desa-header-actions" style="margin-bottom:16px;">
            <h3 style="margin:0;">Daftar Marker</h3>
            <button type="button" class="button button-primary" id="wp-desa-peta-add-marker">Tambah Marker</button>
        </div>
        <table class="wp-list-table widefat fixed striped" id="wp-desa-peta-markers-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lokasi</th>
                    <th>Jenis</th>
                    <th>Koordinat</th>
                    <th style="width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <!-- Save Button -->
    <div style="margin-top:var(--sp-lg); display:flex; align-items:center; gap:var(--sp-md);">
        <button type="button" class="wp-desa-btn wp-desa-btn-primary" id="wp-desa-peta-save">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Peta
        </button>
        <span id="wp-desa-peta-save-status" style="display:none; font-size:14px;"></span>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>

<script>
jQuery(function($) {
    var map, markersLayer;
    var mapData = <?php echo json_encode($map_data); ?>;
    var markers = mapData.markers || [];
    var polygonData = mapData.polygon || '';
    var DEFAULT_LAT = parseFloat('<?php echo esc_js($center_lat); ?>');
    var DEFAULT_LNG = parseFloat('<?php echo esc_js($center_lng); ?>');
    var DEFAULT_ZOOM = parseInt('<?php echo esc_js($zoom); ?>');
    var editingIndex = -1;

    function resetForm() {
        $('#wp-desa-peta-marker-index').val('');
        $('#wp-desa-peta-marker-name').val('');
        $('#wp-desa-peta-marker-type').val('kantor-desa');
        $('#wp-desa-peta-marker-lat').val('');
        $('#wp-desa-peta-marker-lng').val('');
        $('#wp-desa-peta-marker-desc').val('');
        $('#wp-desa-peta-marker-title').text('Tambah Marker');
    }

    // Init map
    function initMap() {
        map = L.map('wp-desa-peta-map').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        renderAllMarkers();

        // Click on map to place marker
        map.on('click', function(e) {
            resetForm();
            $('#wp-desa-peta-marker-lat').val(e.latlng.lat.toFixed(6));
            $('#wp-desa-peta-marker-lng').val(e.latlng.lng.toFixed(6));
            // Scroll sidebar into view on mobile
            $('.wp-desa-wrapper > div:first-child').get(0).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    function getMarkerIcon(type) {
        var colorMap = {
            'kantor-desa': '#2563eb',
            'sekolah': '#059669',
            'masjid': '#7c3aed',
            'puskesmas': '#dc2626',
            'pasar': '#d97706',
            'wisata': '#0891b2',
            'lainnya': '#6b7280'
        };
        var color = colorMap[type] || '#6b7280';
        return L.divIcon({
            className: 'wp-desa-marker-icon',
            html: '<div style="width:24px;height:24px;background:' + color + ';border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -14]
        });
    }

    function renderAllMarkers() {
        if (markersLayer) map.removeLayer(markersLayer);
        markersLayer = L.layerGroup().addTo(map);
        markers.forEach(function(m, i) {
            var marker = L.marker([parseFloat(m.lat), parseFloat(m.lng)], { icon: getMarkerIcon(m.type) })
                .bindPopup('<b>' + escapeHtml(m.name) + '</b><br>' + escapeHtml(m.desc || ''));
            marker.on('click', function() {
                editMarker(i);
            });
            markersLayer.addLayer(marker);
        });
    }

    function renderMarkerTable() {
        var $tbody = $('#wp-desa-peta-markers-table tbody');
        if (!markers.length) {
            $tbody.html('<tr><td colspan="5" style="text-align:center;padding:30px;">Belum ada marker. Klik peta untuk menambah.</td></tr>');
            return;
        }
        var rows = '';
        $.each(markers, function(i, m) {
            rows += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + escapeHtml(m.name || 'Tanpa Nama') + '</td>' +
                '<td>' + escapeHtml(m.type || '') + '</td>' +
                '<td>' + m.lat + ', ' + m.lng + '</td>' +
                '<td><button type="button" class="button button-small wp-desa-peta-edit" data-index="' + i + '">Edit</button> ' +
                '<button type="button" class="button button-small button-link-delete wp-desa-peta-del" data-index="' + i + '">Hapus</button></td>' +
                '</tr>';
        });
        $tbody.html(rows);
    }

    function escapeHtml(t) { return $('<span>').text(t).html(); }

    // Add marker button
    $('#wp-desa-peta-add-marker').on('click', function() {
        var center = map.getCenter();
        resetForm();
        $('#wp-desa-peta-marker-lat').val(center.lat.toFixed(6));
        $('#wp-desa-peta-marker-lng').val(center.lng.toFixed(6));
        $('.wp-desa-wrapper > div:first-child').get(0).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    // Save marker form
    $('#wp-desa-peta-marker-save').on('click', function() {
        var index = $('#wp-desa-peta-marker-index').val();
        var data = {
            name: $('#wp-desa-peta-marker-name').val() || 'Tanpa Nama',
            type: $('#wp-desa-peta-marker-type').val(),
            lat: $('#wp-desa-peta-marker-lat').val(),
            lng: $('#wp-desa-peta-marker-lng').val(),
            desc: $('#wp-desa-peta-marker-desc').val(),
        };
        if (!data.lat || !data.lng) { alert('Latitude dan Longitude wajib diisi. Klik peta untuk menentukan lokasi.'); return; }
        if (index === '') {
            markers.push(data);
        } else {
            markers[parseInt(index)] = data;
        }
        renderAllMarkers();
        renderMarkerTable();
        resetForm();
    });

    // Cancel
    $('#wp-desa-peta-marker-cancel').on('click', function() {
        resetForm();
    });

    // Edit / Delete from table
    $('#wp-desa-peta-markers-table').on('click', '.wp-desa-peta-edit', function() {
        editMarker(parseInt($(this).data('index')));
    });
    $('#wp-desa-peta-markers-table').on('click', '.wp-desa-peta-del', function() {
        if (!confirm('Hapus marker ini?')) return;
        markers.splice(parseInt($(this).data('index')), 1);
        renderAllMarkers();
        renderMarkerTable();
    });

    function editMarker(i) {
        var m = markers[i];
        $('#wp-desa-peta-marker-index').val(i);
        $('#wp-desa-peta-marker-name').val(m.name);
        $('#wp-desa-peta-marker-type').val(m.type || 'kantor-desa');
        $('#wp-desa-peta-marker-lat').val(m.lat);
        $('#wp-desa-peta-marker-lng').val(m.lng);
        $('#wp-desa-peta-marker-desc').val(m.desc || '');
        $('#wp-desa-peta-marker-title').text('Edit Marker');
        $('.wp-desa-wrapper > div:first-child').get(0).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Save all
    $('#wp-desa-peta-save').on('click', function() {
        var center = map.getCenter();
        var data = {
            peta_desa: {
                center_lat: center.lat,
                center_lng: center.lng,
                zoom: map.getZoom(),
                markers: markers,
                polygon: polygonData,
            }
        };

        $('#wp-desa-peta-save-status').show().css('color', '#2563eb').text('Menyimpan...');

        $.ajax({
            url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            method: 'POST',
            data: {
                action: 'wp_desa_save_peta',
                _ajax_nonce: wpDesaSettings.nonce,
                settings: JSON.stringify(data)
            },
            success: function() {
                $('#wp-desa-peta-save-status').css('color', '#059669').text('Peta berhasil disimpan!').fadeOut(3000);
            },
            error: function() {
                $('#wp-desa-peta-save-status').css('color', '#dc2626').text('Gagal menyimpan, coba lagi.');
            }
        });
    });

    renderMarkerTable();
    initMap();
});
</script>
