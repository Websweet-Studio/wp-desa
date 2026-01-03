<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;

class DashboardController extends WP_REST_Controller {
    public function register_routes() {
        $namespace = 'wp-desa/v1';
        $base = 'dashboard';

        register_rest_route($namespace, '/' . $base . '/stats', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_stats'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/seed-all', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'seed_all'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    public function permissions_check() {
        return current_user_can('manage_options');
    }

    public function seed_all() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'Database/Seeder.php';
        $count = \WpDesa\Database\Seeder::run(50);
        return rest_ensure_response([
            'success' => true,
            'message' => 'Berhasil membuat data dummy (Penduduk, Surat, Aduan, Keuangan, Bantuan).',
            'count' => $count
        ]);
    }

    public function get_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';

        // Total Residents
        $total_residents = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        // Gender Stats
        $gender_stats = $wpdb->get_results("SELECT jenis_kelamin as label, COUNT(*) as count FROM $table_name GROUP BY jenis_kelamin");

        // Job Stats
        $job_stats = $wpdb->get_results("SELECT pekerjaan as label, COUNT(*) as count FROM $table_name GROUP BY pekerjaan ORDER BY count DESC LIMIT 5");

        // Marital Status Stats
        $marital_stats = $wpdb->get_results("SELECT status_perkawinan as label, COUNT(*) as count FROM $table_name GROUP BY status_perkawinan");

        // Potensi Desa Stats
        $total_potensi = wp_count_posts('desa_potensi')->publish;

        // UMKM Desa Stats
        $total_umkm = wp_count_posts('desa_umkm')->publish;

        // Letter Stats
        $table_letters = $wpdb->prefix . 'desa_letters';
        $total_letters = $wpdb->get_var("SELECT COUNT(*) FROM $table_letters");
        $pending_letters = $wpdb->get_var("SELECT COUNT(*) FROM $table_letters WHERE status = 'pending'");
        $letter_stats = $wpdb->get_results("SELECT status as label, COUNT(*) as count FROM $table_letters GROUP BY status");

        // Complaints Stats (Recent 5)
        $table_complaints = $wpdb->prefix . 'desa_complaints';
        $recent_complaints = $wpdb->get_results("SELECT id, subject, status, created_at FROM $table_complaints ORDER BY created_at DESC LIMIT 5");
        
        // Financial Stats (Current Year)
        $table_finances = $wpdb->prefix . 'desa_finances';
        $current_year = date('Y');
        $finance_summary = $wpdb->get_results($wpdb->prepare(
            "SELECT type, SUM(realization_amount) as total FROM $table_finances WHERE year = %d GROUP BY type",
            $current_year
        ));

        $income = 0;
        $expense = 0;
        foreach ($finance_summary as $row) {
            if ($row->type === 'income') $income = $row->total;
            if ($row->type === 'expense') $expense = $row->total;
        }

        // Program Aid Stats
        $table_programs = $wpdb->prefix . 'desa_programs';
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        
        $program_stats = $wpdb->get_results("
            SELECT 
                p.name, 
                p.quota, 
                COUNT(r.id) as distributed
            FROM $table_programs p 
            LEFT JOIN $table_recipients r ON p.id = r.program_id AND r.status = 'distributed'
            WHERE p.status = 'active'
            GROUP BY p.id
            LIMIT 5
        ");

        return rest_ensure_response([
            'total_residents' => $total_residents,
            'gender_stats' => $gender_stats,
            'job_stats' => $job_stats,
            'marital_stats' => $marital_stats,
            'total_potensi' => $total_potensi,
            'total_umkm' => $total_umkm,
            'total_letters' => $total_letters,
            'pending_letters' => $pending_letters,
            'letter_stats' => $letter_stats,
            'recent_complaints' => $recent_complaints,
            'finance_stats' => [
                'income' => $income,
                'expense' => $expense,
                'year' => $current_year
            ],
            'program_stats' => $program_stats,
        ]);
    }
}
