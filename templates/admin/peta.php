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
    <h2>Peta Wilayah Desa</h2>
    <p style="color: #64748b; margin-bottom: 20px;">
        Atur peta interaktif desa. Tambahkan marker untuk lokasi penting (kantor desa, sekolah, masjid, puskesmas, dll).
    </p>

    <!-- Map Canvas -->
    <div id="wp-desa-peta-map" style="width: 100%; height: 500px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #e2e8f0;"></div>

    <!-- Marker List -->
    <div class="wp-desa-card" style="margin-top: 20px;">
        <div class="wp-desa-header-actions" style="margin-bottom: 16px;">
            <h3 style="margin: 0;">Daftar Marker</h3>
            <button type="button" class="button button-primary" id="wp-desa-peta-add-marker">Tambah Marker</button>
        </div>

        <table class="wp-list-table widefat fixed striped" id="wp-desa-peta-markers-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lokasi</th>
                    <th>Jenis</th>
                    <th>Koordinat</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <!-- Save Button -->
    <div style="margin-top: 20px;">
        <button type="button" class="button button-primary button-large" id="wp-desa-peta-save">Simpan Peta</button>
        <span id="wp-desa-peta-save-status" style="margin-left: 10px; display: none;"></span>
    </div>
</div>

<!-- Light Marker Form Modal -->
<div id="wp-desa-peta-marker-modal" style="display:none; position:fixed; top:0;left:0;width:100%;height:100%;z-index:100000;">
    <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);" onclick="jQuery('#wp-desa-peta-marker-modal').hide()"></div>
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:24px;border-radius:8px;width:90%;max-width:450px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h3 id="wp-desa-peta-marker-title" style="margin-top:0;">Tambah Marker</h3>
        <input type="hidden" id="wp-desa-peta-marker-index" value="">
        <div style="margin-bottom:12px;">
            <label>Nama Lokasi</label>
            <input type="text" id="wp-desa-peta-marker-name" class="wp-desa-input" style="width:100%;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Jenis</label>
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
        <div style="margin-bottom:12px;">
            <label>Latitude</label>
            <input type="text" id="wp-desa-peta-marker-lat" class="wp-desa-input" style="width:100%;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Longitude</label>
            <input type="text" id="wp-desa-peta-marker-lng" class="wp-desa-input" style="width:100%;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Deskripsi</label>
            <textarea id="wp-desa-peta-marker-desc" class="wp-desa-textarea" style="width:100%;" rows="2"></textarea>
        </div>
        <div style="text-align:right;">
            <button type="button" class="button" onclick="jQuery('#wp-desa-peta-marker-modal').hide()">Batal</button>
            <button type="button" class="button button-primary" id="wp-desa-peta-marker-save">Simpan</button>
        </div>
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

    // Init map
    function initMap() {
        map = L.map('wp-desa-peta-map').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        renderAllMarkers();

        // Click to add marker
        map.on('click', function(e) {
            $('#wp-desa-peta-marker-name').val('');
            $('#wp-desa-peta-marker-type').val('kantor-desa');
            $('#wp-desa-peta-marker-lat').val(e.latlng.lat.toFixed(6));
            $('#wp-desa-peta-marker-lng').val(e.latlng.lng.toFixed(6));
            $('#wp-desa-peta-marker-desc').val('');
            $('#wp-desa-peta-marker-index').val('');
            $('#wp-desa-peta-marker-title').text('Tambah Marker');
            $('#wp-desa-peta-marker-modal').show();
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
        $('#wp-desa-peta-marker-name').val('');
        $('#wp-desa-peta-marker-type').val('kantor-desa');
        $('#wp-desa-peta-marker-lat').val(center.lat.toFixed(6));
        $('#wp-desa-peta-marker-lng').val(center.lng.toFixed(6));
        $('#wp-desa-peta-marker-desc').val('');
        $('#wp-desa-peta-marker-index').val('');
        $('#wp-desa-peta-marker-title').text('Tambah Marker');
        $('#wp-desa-peta-marker-modal').show();
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
        if (!data.lat || !data.lng) { alert('Latitude dan Longitude wajib diisi.'); return; }
        if (index === '') {
            markers.push(data);
        } else {
            markers[parseInt(index)] = data;
        }
        renderAllMarkers();
        renderMarkerTable();
        $('#wp-desa-peta-marker-modal').hide();
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
        $('#wp-desa-peta-marker-modal').show();
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
                settings: data
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
