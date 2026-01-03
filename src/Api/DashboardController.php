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

        return rest_ensure_response([
            'total_residents' => $total_residents,
            'gender_stats' => $gender_stats,
            'job_stats' => $job_stats,
            'marital_stats' => $marital_stats,
        ]);
    }
}
