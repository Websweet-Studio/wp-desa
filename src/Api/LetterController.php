<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class LetterController extends WP_REST_Controller
{
    public function register_routes()
    {
        $namespace = 'wp-desa/v1';
        $base = 'letters';

        // Public: Get Letter Types
        register_rest_route($namespace, '/' . $base . '/types', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_types'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Public: Request Letter
        register_rest_route($namespace, '/' . $base . '/request', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_letter'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Public: Track Letter
        register_rest_route($namespace, '/' . $base . '/track', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'track_letter'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Admin: List Letters
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_letters'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Admin: Update Status
        register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_status'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Admin: Seed Dummy Data
        register_rest_route($namespace, '/' . $base . '/seed', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'seed_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    public function permissions_check()
    {
        return current_user_can('manage_options');
    }

    public function seed_items($request)
    {
        global $wpdb;
        $table_residents = $wpdb->prefix . 'desa_residents';
        $table_types = $wpdb->prefix . 'desa_letter_types';

        // Diagnose why seeding might fail
        $res_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_residents");
        if ($res_count == 0) {
            // Auto-seed residents if empty
            \WpDesa\Database\Seeder::run(50); // This seeds residents
            $res_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_residents");
        }

        $type_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_types");
        if ($type_count == 0) {
            // Try to auto-fix types
            \WpDesa\Database\Activator::activate();
            $type_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_types");
            if ($type_count == 0) {
                return new \WP_Error('no_types', 'Gagal: Data jenis surat kosong.', ['status' => 400]);
            }
        }

        $count = 20; // Default seed count for letters
        $inserted = \WpDesa\Database\Seeder::seed_letters($count);
        return rest_ensure_response(['message' => "$inserted data permohonan surat berhasil dibuat.", 'count' => $inserted]);
    }

    public function get_types()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_letter_types';
        $results = $wpdb->get_results("SELECT * FROM $table_name");
        return rest_ensure_response($results);
    }

    public function create_letter($request)
    {
        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_residents = $wpdb->prefix . 'desa_residents';

        // Self-healing: Ensure tables exist
        \WpDesa\Database\Activator::activate();

        $params = $request->get_params();

        // Validation
        if (empty($params['nik']) || empty($params['letter_type_id']) || empty($params['phone'])) {
            return new WP_Error('missing_params', 'NIK, Jenis Surat, dan No. HP wajib diisi', ['status' => 400]);
        }

        $nik = sanitize_text_field($params['nik']);

        // Verify Resident Exists (Optional, but good practice)
        // If resident doesn't exist, we might still allow request but mark as 'Unverified' or require name input.
        // For now, let's require name input if NIK not found, or just fetch name if found.
        $resident = $wpdb->get_row($wpdb->prepare("SELECT nama_lengkap FROM $table_residents WHERE nik = %s", $nik));

        $name = '';
        if ($resident) {
            $name = $resident->nama_lengkap;
        } elseif (!empty($params['name'])) {
            $name = sanitize_text_field($params['name']);
        } else {
            return new WP_Error('resident_not_found', 'NIK tidak ditemukan. Harap isi Nama Lengkap jika belum terdaftar.', ['status' => 400]);
        }

        $tracking_code = strtoupper(wp_generate_password(8, false));

        $data = [
            'tracking_code' => $tracking_code,
            'letter_type_id' => intval($params['letter_type_id']),
            'nik' => $nik,
            'name' => $name,
            'phone' => sanitize_text_field($params['phone']),
            'details' => isset($params['details']) ? sanitize_textarea_field($params['details']) : '',
            'status' => 'pending',
            'created_at' => current_time('mysql'),
        ];

        $result = $wpdb->insert($table_letters, $data);

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Gagal menyimpan permohonan', ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Permohonan berhasil dikirim.',
            'tracking_code' => $tracking_code
        ]);
    }

    public function track_letter($request)
    {
        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_types = $wpdb->prefix . 'desa_letter_types';

        $code = $request->get_param('code');
        if (empty($code)) {
            return new WP_Error('missing_code', 'Kode tracking wajib diisi', ['status' => 400]);
        }

        $sql = "SELECT l.*, t.name as type_name 
                FROM $table_letters l 
                JOIN $table_types t ON l.letter_type_id = t.id 
                WHERE l.tracking_code = %s";

        $letter = $wpdb->get_row($wpdb->prepare($sql, $code));

        if (!$letter) {
            return new WP_Error('not_found', 'Permohonan tidak ditemukan', ['status' => 404]);
        }

        return rest_ensure_response($letter);
    }

    public function get_letters($request)
    {
        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $table_types = $wpdb->prefix . 'desa_letter_types';

        $status = $request->get_param('status');
        $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;
        $per_page = $request->get_param('per_page') ? intval($request->get_param('per_page')) : 20;
        $offset = ($page - 1) * $per_page;

        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare("WHERE l.status = %s", $status);
        }

        // Count total items
        $count_sql = "SELECT COUNT(*) FROM $table_letters l $where";
        $total_items = (int) $wpdb->get_var($count_sql);
        $total_pages = ceil($total_items / $per_page);

        $sql = "SELECT l.*, t.name as type_name 
                FROM $table_letters l 
                JOIN $table_types t ON l.letter_type_id = t.id 
                $where
                ORDER BY l.created_at DESC
                LIMIT %d OFFSET %d";

        $prepared_sql = $wpdb->prepare($sql, $per_page, $offset);
        $results = $wpdb->get_results($prepared_sql);

        // Get counts for all statuses
        $counts_sql = "SELECT status, COUNT(*) as count FROM $table_letters GROUP BY status";
        $counts_results = $wpdb->get_results($counts_sql);
        $status_counts = [
            'all' => 0,
            'pending' => 0,
            'processed' => 0,
            'completed' => 0,
            'rejected' => 0
        ];
        foreach ($counts_results as $row) {
            if (isset($status_counts[$row->status])) {
                $status_counts[$row->status] = (int)$row->count;
            }
            $status_counts['all'] += (int)$row->count;
        }

        return rest_ensure_response([
            'data' => $results,
            'meta' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => $total_items,
                'total_pages' => $total_pages
            ],
            'counts' => $status_counts
        ]);
    }

    public function update_status($request)
    {
        global $wpdb;
        $table_letters = $wpdb->prefix . 'desa_letters';
        $id = $request['id'];
        $status = $request->get_param('status');

        if (!in_array($status, ['pending', 'processed', 'completed', 'rejected'])) {
            return new WP_Error('invalid_status', 'Status tidak valid', ['status' => 400]);
        }

        $updated = $wpdb->update(
            $table_letters,
            ['status' => $status],
            ['id' => $id]
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Gagal update status', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true, 'id' => $id, 'status' => $status]);
    }
}
