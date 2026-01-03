<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class ComplaintController extends WP_REST_Controller {
    public function register_routes() {
        $namespace = 'wp-desa/v1';
        $base = 'complaints';

        // Public: Submit Complaint
        register_rest_route($namespace, '/' . $base . '/submit', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_complaint'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Public: Track Complaint
        register_rest_route($namespace, '/' . $base . '/track', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'track_complaint'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Admin: List Complaints
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_complaints'],
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

        // Admin: Seed Complaints
        register_rest_route($namespace, '/' . $base . '/seed', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'seed_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    public function permissions_check() {
        return current_user_can('manage_options');
    }

    public function create_complaint($request) {
        global $wpdb;
        $table_complaints = $wpdb->prefix . 'desa_complaints';

        // Self-healing
        \WpDesa\Database\Activator::activate();

        $params = $request->get_params();

        if (empty($params['category']) || empty($params['subject']) || empty($params['description'])) {
            return new WP_Error('missing_params', 'Kategori, Judul, dan Isi laporan wajib diisi', ['status' => 400]);
        }

        // Handle File Upload
        $photo_url = '';
        $files = $request->get_file_params();
        if (!empty($files['photo'])) {
            $file = $files['photo'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowed_types)) {
                return new WP_Error('invalid_file', 'Hanya file gambar (JPG, PNG) yang diperbolehkan', ['status' => 400]);
            }

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $photo_url = $movefile['url'];
            } else {
                return new WP_Error('upload_error', 'Gagal upload foto: ' . $movefile['error'], ['status' => 500]);
            }
        }

        $tracking_code = 'ADU-' . strtoupper(wp_generate_password(6, false));
        
        $data = [
            'tracking_code' => $tracking_code,
            'reporter_name' => !empty($params['reporter_name']) ? sanitize_text_field($params['reporter_name']) : 'Anonim',
            'reporter_contact' => !empty($params['reporter_contact']) ? sanitize_text_field($params['reporter_contact']) : '',
            'category' => sanitize_text_field($params['category']),
            'subject' => sanitize_text_field($params['subject']),
            'description' => sanitize_textarea_field($params['description']),
            'photo_url' => $photo_url,
            'status' => 'pending',
            'created_at' => current_time('mysql'),
        ];

        $result = $wpdb->insert($table_complaints, $data);

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Gagal menyimpan laporan', ['status' => 500]);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => 'Laporan berhasil dikirim.',
            'tracking_code' => $tracking_code
        ]);
    }

    public function track_complaint($request) {
        global $wpdb;
        $table_complaints = $wpdb->prefix . 'desa_complaints';

        $code = $request->get_param('code');
        if (empty($code)) {
            return new WP_Error('missing_code', 'Kode tracking wajib diisi', ['status' => 400]);
        }

        $complaint = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_complaints WHERE tracking_code = %s", $code));

        if (!$complaint) {
            return new WP_Error('not_found', 'Laporan tidak ditemukan', ['status' => 404]);
        }

        return rest_ensure_response($complaint);
    }

    public function get_complaints($request) {
        global $wpdb;
        $table_complaints = $wpdb->prefix . 'desa_complaints';

        $status = $request->get_param('status');
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare("WHERE status = %s", $status);
        }

        $results = $wpdb->get_results("SELECT * FROM $table_complaints $where ORDER BY created_at DESC");
        return rest_ensure_response($results);
    }

    public function update_status($request) {
        global $wpdb;
        $table_complaints = $wpdb->prefix . 'desa_complaints';
        $id = $request['id'];
        $params = $request->get_json_params();
        $status = isset($params['status']) ? $params['status'] : '';
        $response = isset($params['response']) ? $params['response'] : '';

        if (!in_array($status, ['pending', 'in_progress', 'resolved', 'rejected'])) {
            return new WP_Error('invalid_status', 'Status tidak valid', ['status' => 400]);
        }

        $data = ['status' => $status];
        if (!empty($response)) {
            $data['response'] = sanitize_textarea_field($response);
        }

        $updated = $wpdb->update(
            $table_complaints,
            $data,
            ['id' => $id]
        );

        if ($updated === false) {
            return new WP_Error('db_error', 'Gagal update status', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true, 'id' => $id, 'status' => $status]);
    }

    public function seed_items($request) {
        require_once plugin_dir_path(dirname(__FILE__)) . 'Database/Seeder.php';
        
        $count = \WpDesa\Database\Seeder::seed_complaints(20);
        
        return rest_ensure_response([
            'success' => true, 
            'message' => "$count dummy complaints created.",
            'count' => $count
        ]);
    }
}
