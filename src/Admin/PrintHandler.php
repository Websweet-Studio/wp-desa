<?php

namespace WpDesa\Admin;

class PrintHandler
{
    public function handle_print()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            wp_die('Invalid ID');
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wp_desa_print_letter')) {
            wp_die('Security check failed.');
        }

        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_types = $wpdb->prefix . 'desa_letter_types';
        $table_residents = $wpdb->prefix . 'desa_residents';

        // Fetch Letter + Type + Resident Details (Full JOIN to get resident details)
        // We join residents table on NIK to get details for the letter body
        $sql = "SELECT l.*, t.name as type_name, t.code as type_code, 
                       r.tempat_lahir, r.tanggal_lahir, r.jenis_kelamin, r.pekerjaan, r.alamat, r.status_perkawinan
                FROM $table_letters l 
                LEFT JOIN $table_types t ON l.letter_type_id = t.id 
                LEFT JOIN $table_residents r ON l.nik = r.nik
                WHERE l.id = %d";
        
        $letter = $wpdb->get_row($wpdb->prepare($sql, $id));

        if (!$letter) {
            wp_die('Data surat tidak ditemukan.');
        }

        // Include Template
        require_once WP_DESA_PATH . 'templates/admin/print-letter.php';
        exit;
    }
}
