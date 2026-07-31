<?php

namespace WpDesa\Admin;

class MetaBoxes
{
    public function register()
    {
        add_action('add_meta_boxes', [$this, 'add_umkm_meta_boxes']);
        add_action('save_post', [$this, 'save_umkm_meta_boxes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Agenda meta boxes
        add_action('add_meta_boxes', [$this, 'add_agenda_meta_boxes']);
        add_action('save_post', [$this, 'save_agenda_meta_boxes']);

        // Galeri meta boxes
        add_action('add_meta_boxes', [$this, 'add_galeri_meta_boxes']);
        add_action('save_post', [$this, 'save_galeri_meta_boxes']);

        // Wisata meta boxes
        add_action('add_meta_boxes', [$this, 'add_wisata_meta_boxes']);
        add_action('save_post', [$this, 'save_wisata_meta_boxes']);
    }

    public function enqueue_scripts($hook)
    {
        global $post;
        if (($hook == 'post-new.php' || $hook == 'post.php') && 'desa_umkm' === $post->post_type) {
            wp_enqueue_media();
            wp_enqueue_script('desa-umkm-gallery', WP_DESA_URL . 'assets/js/admin/umkm-gallery.js', ['jquery'], '1.0.1', true);
        }
        if (($hook == 'post-new.php' || $hook == 'post.php') && 'desa_galeri' === $post->post_type) {
            wp_enqueue_media();
            wp_enqueue_script('desa-umkm-gallery', WP_DESA_URL . 'assets/js/admin/umkm-gallery.js', ['jquery'], '1.0.1', true);
        }
    }

    public function add_umkm_meta_boxes()
    {
        add_meta_box(
            'desa_umkm_details',
            'Detail UMKM',
            [$this, 'render_umkm_meta_box'],
            'desa_umkm',
            'normal',
            'high'
        );
    }

    public function render_umkm_meta_box($post)
    {
        // Get existing values
        $phone = get_post_meta($post->ID, '_desa_umkm_phone', true);
        $location = get_post_meta($post->ID, '_desa_umkm_location', true);
        $gallery_ids = get_post_meta($post->ID, '_desa_umkm_gallery', true);

        wp_nonce_field('save_desa_umkm_meta', 'desa_umkm_meta_nonce');
?>
        <table class="form-table">
            <tr>
                <th><label for="desa_umkm_phone">Nomor WhatsApp</label></th>
                <td>
                    <input type="text" id="desa_umkm_phone" name="desa_umkm_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text">
                    <p class="description">Contoh: 628123456789 (Gunakan format internasional tanpa +)</p>
                </td>
            </tr>
            <tr>
                <th><label for="desa_umkm_location">Lokasi (Koordinat)</label></th>
                <td>
                    <input type="text" id="desa_umkm_location" name="desa_umkm_location" value="<?php echo esc_attr($location); ?>" class="regular-text" placeholder="-7.123456, 110.123456">
                    <p class="description">Format: Latitude, Longitude. Bisa diambil dari Google Maps.</p>
                </td>
            </tr>
            <tr>
                <th><label>Katalog Produk (Gallery)</label></th>
                <td>
                    <div id="desa_umkm_gallery_container" style="margin-bottom: 10px;">
                        <?php
                        if ($gallery_ids) {
                            $ids = explode(',', $gallery_ids);
                            foreach ($ids as $id) {
                                $url = wp_get_attachment_thumb_url($id);
                                if ($url) {
                                    echo '<div class="gallery-item" data-id="' . $id . '" style="display: inline-block; margin: 5px; position: relative;">
                                            <img src="' . $url . '" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;">
                                            <button class="remove-image" style="position: absolute; top: 0; right: 0; background: red; color: white; border: none; cursor: pointer; padding: 2px 5px;">&times;</button>
                                          </div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <button id="desa_umkm_add_gallery" class="button">Tambah Gambar</button>
                    <input type="hidden" id="desa_umkm_gallery_ids" name="desa_umkm_gallery" value="<?php echo esc_attr($gallery_ids); ?>">
                    <p class="description">Upload foto produk lainnya untuk gallery.</p>
                </td>
            </tr>
        </table>
<?php
    }

    public function save_umkm_meta_boxes($post_id)
    {
        if (!isset($_POST['desa_umkm_meta_nonce']) || !wp_verify_nonce($_POST['desa_umkm_meta_nonce'], 'save_desa_umkm_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['desa_umkm_phone'])) {
            update_post_meta($post_id, '_desa_umkm_phone', sanitize_text_field($_POST['desa_umkm_phone']));
        }

        if (isset($_POST['desa_umkm_location'])) {
            update_post_meta($post_id, '_desa_umkm_location', sanitize_text_field($_POST['desa_umkm_location']));
        }

        if (isset($_POST['desa_umkm_gallery'])) {
            update_post_meta($post_id, '_desa_umkm_gallery', sanitize_text_field($_POST['desa_umkm_gallery']));
        }
    }

    // ─── Agenda Meta Boxes ───

    public function add_agenda_meta_boxes()
    {
        add_meta_box(
            'desa_agenda_details',
            'Detail Agenda',
            [$this, 'render_agenda_meta_box'],
            'desa_agenda',
            'normal',
            'high'
        );
    }

    public function render_agenda_meta_box($post)
    {
        $date = get_post_meta($post->ID, '_desa_agenda_date', true);
        $time = get_post_meta($post->ID, '_desa_agenda_time', true);
        $location = get_post_meta($post->ID, '_desa_agenda_location', true);
        $end_date = get_post_meta($post->ID, '_desa_agenda_end_date', true);

        wp_nonce_field('save_desa_agenda_meta', 'desa_agenda_meta_nonce');
?>
        <table class="form-table">
            <tr>
                <th><label for="desa_agenda_date">Tanggal Kegiatan <span style="color:red;">*</span></label></th>
                <td><input type="date" id="desa_agenda_date" name="desa_agenda_date" value="<?php echo esc_attr($date); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="desa_agenda_time">Waktu</label></th>
                <td><input type="time" id="desa_agenda_time" name="desa_agenda_time" value="<?php echo esc_attr($time); ?>" class="regular-text"><p class="description">Contoh: 08:00</p></td>
            </tr>
            <tr>
                <th><label for="desa_agenda_end_date">Tanggal Selesai</label></th>
                <td><input type="date" id="desa_agenda_end_date" name="desa_agenda_end_date" value="<?php echo esc_attr($end_date); ?>" class="regular-text"><p class="description">Opsional, untuk kegiatan multi-hari.</p></td>
            </tr>
            <tr>
                <th><label for="desa_agenda_location">Lokasi</label></th>
                <td><input type="text" id="desa_agenda_location" name="desa_agenda_location" value="<?php echo esc_attr($location); ?>" class="regular-text" placeholder="Contoh: Balai Desa"></td>
            </tr>
        </table>
<?php
    }

    public function save_agenda_meta_boxes($post_id)
    {
        if (!isset($_POST['desa_agenda_meta_nonce']) || !wp_verify_nonce($_POST['desa_agenda_meta_nonce'], 'save_desa_agenda_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['desa_agenda_date'])) {
            update_post_meta($post_id, '_desa_agenda_date', sanitize_text_field($_POST['desa_agenda_date']));
        }
        if (isset($_POST['desa_agenda_time'])) {
            update_post_meta($post_id, '_desa_agenda_time', sanitize_text_field($_POST['desa_agenda_time']));
        }
        if (isset($_POST['desa_agenda_end_date'])) {
            update_post_meta($post_id, '_desa_agenda_end_date', sanitize_text_field($_POST['desa_agenda_end_date']));
        }
        if (isset($_POST['desa_agenda_location'])) {
            update_post_meta($post_id, '_desa_agenda_location', sanitize_text_field($_POST['desa_agenda_location']));
        }
    }

    // ─── Galeri Meta Boxes ───

    public function add_galeri_meta_boxes()
    {
        add_meta_box(
            'desa_galeri_details',
            'Galeri Foto',
            [$this, 'render_galeri_meta_box'],
            'desa_galeri',
            'normal',
            'high'
        );
    }

    public function render_galeri_meta_box($post)
    {
        $gallery_ids = get_post_meta($post->ID, '_desa_galeri_images', true);
        $type = get_post_meta($post->ID, '_desa_galeri_type', true) ?: 'foto';

        wp_nonce_field('save_desa_galeri_meta', 'desa_galeri_meta_nonce');
?>
        <table class="form-table">
            <tr>
                <th><label for="desa_galeri_type">Tipe</label></th>
                <td>
                    <select id="desa_galeri_type" name="desa_galeri_type">
                        <option value="foto" <?php selected($type, 'foto'); ?>>Foto</option>
                        <option value="video" <?php selected($type, 'video'); ?>>Video</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Kumpulan Gambar</label></th>
                <td>
                    <div id="desa_galeri_images_container" style="margin-bottom: 10px;">
                        <?php
                        if ($gallery_ids) {
                            $ids = explode(',', $gallery_ids);
                            foreach ($ids as $id) {
                                $url = wp_get_attachment_thumb_url($id);
                                if ($url) {
                                    echo '<div class="gallery-item" data-id="' . $id . '" style="display: inline-block; margin: 5px; position: relative;">
                                            <img src="' . $url . '" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;">
                                            <button class="remove-image" style="position: absolute; top: 0; right: 0; background: red; color: white; border: none; cursor: pointer; padding: 2px 5px;">&times;</button>
                                          </div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <button id="desa_galeri_add_gallery" class="button">Tambah Gambar</button>
                    <input type="hidden" id="desa_galeri_gallery_ids" name="desa_galeri_images" value="<?php echo esc_attr($gallery_ids); ?>">
                    <p class="description">Upload foto-foto untuk galeri ini.</p>
                </td>
            </tr>
        </table>
<?php
    }

    public function save_galeri_meta_boxes($post_id)
    {
        if (!isset($_POST['desa_galeri_meta_nonce']) || !wp_verify_nonce($_POST['desa_galeri_meta_nonce'], 'save_desa_galeri_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['desa_galeri_type'])) {
            update_post_meta($post_id, '_desa_galeri_type', sanitize_text_field($_POST['desa_galeri_type']));
        }
        if (isset($_POST['desa_galeri_images'])) {
            update_post_meta($post_id, '_desa_galeri_images', sanitize_text_field($_POST['desa_galeri_images']));
        }
    }

    // ─── Wisata Meta Boxes ───

    public function add_wisata_meta_boxes()
    {
        add_meta_box(
            'desa_wisata_details',
            'Detail Wisata',
            [$this, 'render_wisata_meta_box'],
            'desa_wisata',
            'normal',
            'high'
        );
    }

    public function render_wisata_meta_box($post)
    {
        $location = get_post_meta($post->ID, '_desa_wisata_location', true);
        $address = get_post_meta($post->ID, '_desa_wisata_address', true);
        $phone = get_post_meta($post->ID, '_desa_wisata_phone', true);

        wp_nonce_field('save_desa_wisata_meta', 'desa_wisata_meta_nonce');
?>
        <table class="form-table">
            <tr>
                <th><label for="desa_wisata_location">Lokasi (Koordinat)</label></th>
                <td>
                    <input type="text" id="desa_wisata_location" name="desa_wisata_location" value="<?php echo esc_attr($location); ?>" class="regular-text" placeholder="-7.123456, 110.123456">
                    <p class="description">Format: Latitude, Longitude. Bisa diambil dari Google Maps.</p>
                </td>
            </tr>
            <tr>
                <th><label for="desa_wisata_address">Alamat Lengkap</label></th>
                <td><input type="text" id="desa_wisata_address" name="desa_wisata_address" value="<?php echo esc_attr($address); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="desa_wisata_phone">Kontak / Telepon</label></th>
                <td><input type="text" id="desa_wisata_phone" name="desa_wisata_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text"></td>
            </tr>
        </table>
<?php
    }

    public function save_wisata_meta_boxes($post_id)
    {
        if (!isset($_POST['desa_wisata_meta_nonce']) || !wp_verify_nonce($_POST['desa_wisata_meta_nonce'], 'save_desa_wisata_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['desa_wisata_location'])) {
            update_post_meta($post_id, '_desa_wisata_location', sanitize_text_field($_POST['desa_wisata_location']));
        }
        if (isset($_POST['desa_wisata_address'])) {
            update_post_meta($post_id, '_desa_wisata_address', sanitize_text_field($_POST['desa_wisata_address']));
        }
        if (isset($_POST['desa_wisata_phone'])) {
            update_post_meta($post_id, '_desa_wisata_phone', sanitize_text_field($_POST['desa_wisata_phone']));
        }
    }
}
