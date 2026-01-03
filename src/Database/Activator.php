<?php

namespace WpDesa\Database;

class Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Residents Table
        $table_residents = $wpdb->prefix . 'desa_residents';
        $sql_residents = "CREATE TABLE $table_residents (
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

        // 2. Letter Types Table
        $table_letter_types = $wpdb->prefix . 'desa_letter_types';
        $sql_letter_types = "CREATE TABLE $table_letter_types (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code varchar(10) NOT NULL,
            name varchar(100) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) $charset_collate;";

        // 3. Letters/Requests Table
        $table_letters = $wpdb->prefix . 'desa_letters';
        $sql_letters = "CREATE TABLE $table_letters (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            tracking_code varchar(20) NOT NULL,
            letter_type_id mediumint(9) NOT NULL,
            nik varchar(20) NOT NULL,
            name varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            details longtext,
            status enum('pending', 'processed', 'completed', 'rejected') DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY tracking_code (tracking_code),
            KEY nik (nik)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_residents);
        dbDelta($sql_letter_types);
        dbDelta($sql_letters);

        // Seed Letter Types if empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_letter_types");
        if ($count == 0) {
            $types = [
                ['SKD', 'Surat Keterangan Domisili', 'Surat untuk menerangkan domisili penduduk.'],
                ['SP', 'Surat Pengantar', 'Surat pengantar untuk berbagai keperluan.'],
                ['SKU', 'Surat Keterangan Usaha', 'Surat keterangan memiliki usaha di desa.'],
                ['SKL', 'Surat Keterangan Kelahiran', 'Surat keterangan kelahiran anak.'],
                ['SKM', 'Surat Keterangan Kematian', 'Surat keterangan kematian penduduk.'],
            ];
            foreach ($types as $type) {
                $wpdb->insert($table_letter_types, [
                    'code' => $type[0],
                    'name' => $type[1],
                    'description' => $type[2],
                    'created_at' => current_time('mysql')
                ]);
            }
        }
    }
}
