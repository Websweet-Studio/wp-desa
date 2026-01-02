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
    }

    public function permissions_check()
    {
        return current_user_can('manage_options');
    }

    public function get_items($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'desa_residents';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        return rest_ensure_response($results);
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
}
