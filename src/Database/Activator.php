<?php

namespace WpDesa\Database;

class Activator {
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nik varchar(20) NOT NULL,
            nama_lengkap varchar(100) NOT NULL,
            jenis_kelamin enum('Laki-laki', 'Perempuan') NOT NULL,
            tempat_lahir varchar(100) NOT NULL,
            tanggal_lahir date NOT NULL,
            alamat text NOT NULL,
            status_perkawinan varchar(50) NOT NULL,
            pekerjaan varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY nik (nik)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        error_log('WP Desa Activator dbDelta result: ' . print_r($result, true));
    }
}
