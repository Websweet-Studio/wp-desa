<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class ResidentController extends WP_REST_Controller
{
    public function register_routes()
    {
        $namespace = 'wp-desa/v1';
        $base = 'residents';

        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Export Route
        register_rest_route($namespace, '/' . $base . '/export', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'export_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Import Route
        register_rest_route($namespace, '/' . $base . '/import', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'import_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
        // Seeder Route (Dev Only)
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

    public function get_items($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';

        // Pagination parameters
        $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;
        $per_page = $request->get_param('per_page') ? intval($request->get_param('per_page')) : 20;
        $offset = ($page - 1) * $per_page;

        // Count total items
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_items / $per_page);

        // Fetch items
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        return rest_ensure_response([
            'data' => $results,
            'meta' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => $total_items,
                'total_pages' => $total_pages
            ]
        ]);
    }

    public function create_item($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';

        // Self-healing: Ensure table structure is up to date
        \WpDesa\Database\Activator::activate();

        $params = $request->get_params();

        // Validation (Simple)
        if (empty($params['nik']) || empty($params['nama_lengkap'])) {
            return new WP_Error('missing_params', 'NIK dan Nama Lengkap wajib diisi', ['status' => 400]);
        }

        $data = [
            'nik' => sanitize_text_field($params['nik']),
            'no_kk' => sanitize_text_field($params['no_kk'] ?? ''),
            'nama_lengkap' => sanitize_text_field($params['nama_lengkap']),
            'jenis_kelamin' => sanitize_text_field($params['jenis_kelamin']),
            'tempat_lahir' => sanitize_text_field($params['tempat_lahir']),
            'tanggal_lahir' => sanitize_text_field($params['tanggal_lahir']),
            'alamat' => sanitize_textarea_field($params['alamat']),
            'status_perkawinan' => sanitize_text_field($params['status_perkawinan']),
            'pekerjaan' => sanitize_text_field($params['pekerjaan']),
            'created_at' => current_time('mysql'),
        ];

        // Debug Log
        error_log('WP Desa Insert Data: ' . print_r($data, true));

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            error_log('WP Desa Insert Error: ' . $wpdb->last_error);
            return new WP_Error('db_insert_error', 'Gagal menyimpan data: ' . $wpdb->last_error, ['status' => 500]);
        }

        $data['id'] = $wpdb->insert_id;

        return rest_ensure_response($data);
    }

    public function update_item($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';
        $id = $request['id'];

        $params = $request->get_params();

        $data = [
            'nik' => sanitize_text_field($params['nik']),
            'no_kk' => sanitize_text_field($params['no_kk'] ?? ''),
            'nama_lengkap' => sanitize_text_field($params['nama_lengkap']),
            'jenis_kelamin' => sanitize_text_field($params['jenis_kelamin']),
            'tempat_lahir' => sanitize_text_field($params['tempat_lahir']),
            'tanggal_lahir' => sanitize_text_field($params['tanggal_lahir']),
            'alamat' => sanitize_textarea_field($params['alamat']),
            'status_perkawinan' => sanitize_text_field($params['status_perkawinan']),
            'pekerjaan' => sanitize_text_field($params['pekerjaan']),
        ];

        $where = ['id' => $id];

        $updated = $wpdb->update($table_name, $data, $where);

        if ($updated === false) {
            return new WP_Error('db_error', 'Gagal update data', ['status' => 500]);
        }

        $data['id'] = $id;
        return rest_ensure_response($data);
    }

    public function delete_item($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';
        $id = $request['id'];

        $deleted = $wpdb->delete($table_name, ['id' => $id]);

        if (!$deleted) {
            return new WP_Error('db_error', 'Gagal menghapus data', ['status' => 500]);
        }

        return rest_ensure_response(['deleted' => true, 'id' => $id]);
    }

    public function export_items($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);

        if (empty($results)) {
            return new WP_Error('no_data', 'Tidak ada data untuk diexport', ['status' => 404]);
        }

        $filename = 'penduduk-export-' . date('Y-m-d') . '.csv';

        // Clean buffer
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, ['NIK', 'No. KK', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Status Perkawinan', 'Pekerjaan']);

        foreach ($results as $row) {
            fputcsv($output, [
                $row['nik'],
                $row['no_kk'],
                $row['nama_lengkap'],
                $row['jenis_kelamin'],
                $row['tempat_lahir'],
                $row['tanggal_lahir'],
                $row['alamat'],
                $row['status_perkawinan'],
                $row['pekerjaan']
            ]);
        }

        fclose($output);
        exit;
    }

    public function import_items($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';

        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_Error('no_file', 'File tidak ditemukan', ['status' => 400]);
        }

        $file = $files['file'];

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return new WP_Error('read_error', 'Gagal membaca file', ['status' => 500]);
        }

        // Skip header
        fgetcsv($handle);

        $success_count = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 8) continue; // Basic validation

            // Map data (assuming same order as export)
            $insert_data = [
                'nik' => sanitize_text_field($data[0]),
                'no_kk' => sanitize_text_field($data[1] ?? ''),
                'nama_lengkap' => sanitize_text_field($data[2]),
                'jenis_kelamin' => sanitize_text_field($data[3]),
                'tempat_lahir' => sanitize_text_field($data[4]),
                'tanggal_lahir' => sanitize_text_field($data[5]),
                'alamat' => sanitize_textarea_field($data[6]),
                'status_perkawinan' => sanitize_text_field($data[7]),
                'pekerjaan' => sanitize_text_field($data[8]),
                'created_at' => current_time('mysql'),
            ];

            // Check if NIK exists
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE nik = %s", $insert_data['nik']));

            if ($exists) {
                $errors[] = "NIK {$insert_data['nik']} sudah ada (Dilewati)";
                continue;
            }

            $result = $wpdb->insert($table_name, $insert_data);
            if ($result) {
                $success_count++;
            } else {
                $errors[] = "Gagal simpan {$insert_data['nik']}: " . $wpdb->last_error;
            }
        }

        fclose($handle);

        return rest_ensure_response([
            'success' => true,
            'message' => "Import selesai. $success_count data berhasil diimport.",
            'errors' => $errors
        ]);
    }

    public function seed_items($request)
    {
        $count = $request->get_param('count') ?: 100;

        require_once WP_DESA_PATH . 'src/Database/Seeder.php';

        $inserted = \WpDesa\Database\Seeder::run($count);

        return rest_ensure_response([
            'success' => true,
            'message' => "Berhasil membuat $inserted data dummy.",
            'count' => $inserted
        ]);
    }
}
