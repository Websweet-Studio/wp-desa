<?php

namespace WpDesa\Database;

class Activator
{
    public static function activate()
    {
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

        // 4. Complaints/Aspirations Table
        $table_complaints = $wpdb->prefix . 'desa_complaints';
        $sql_complaints = "CREATE TABLE $table_complaints (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            tracking_code varchar(20) NOT NULL,
            reporter_name varchar(100) DEFAULT 'Anonim',
            reporter_contact varchar(50),
            category varchar(50) NOT NULL,
            subject varchar(200) NOT NULL,
            description longtext NOT NULL,
            photo_url varchar(255),
            status enum('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
            response longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY tracking_code (tracking_code)
        ) $charset_collate;";

        // 5. Finances Table
        $table_finances = $wpdb->prefix . 'desa_finances';
        $sql_finances = "CREATE TABLE $table_finances (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            year int(4) NOT NULL,
            type enum('income', 'expense') NOT NULL,
            category varchar(100) NOT NULL,
            description text,
            budget_amount decimal(15,2) DEFAULT 0,
            realization_amount decimal(15,2) DEFAULT 0,
            transaction_date date NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY year (year),
            KEY type (type)
        ) $charset_collate;";

        // 6. Aid Programs Table
        $table_programs = $wpdb->prefix . 'desa_programs';
        $sql_programs = "CREATE TABLE $table_programs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text,
            origin varchar(100) NOT NULL,
            year int(4) NOT NULL,
            status enum('active', 'closed') DEFAULT 'active',
            quota int(9) DEFAULT 0,
            amount_per_recipient decimal(15,2) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // 7. Aid Recipients Table
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        $sql_recipients = "CREATE TABLE $table_recipients (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            program_id mediumint(9) NOT NULL,
            resident_id mediumint(9) NOT NULL,
            status enum('pending', 'approved', 'rejected', 'distributed') DEFAULT 'pending',
            distributed_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY program_id (program_id),
            KEY resident_id (resident_id),
            UNIQUE KEY program_resident (program_id, resident_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_residents);
        dbDelta($sql_letter_types);
        dbDelta($sql_letters);
        dbDelta($sql_complaints);
        dbDelta($sql_finances);
        dbDelta($sql_programs);
        dbDelta($sql_recipients);
    }
}

        // Seed Letter Types if empty (Force check)
        // Using $wpdb->get_var directly sometimes fails in activation hook context if dbDelta just ran
        // But let's try to be robust.
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
