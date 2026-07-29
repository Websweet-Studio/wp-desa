<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;

class PerangkatController extends WP_REST_Controller
{
    public function register_routes()
    {
        $namespace = 'wp-desa/v1';
        $base = 'perangkat';

        // Public GET all
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => '__return_true',
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

    private function table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'desa_perangkat';
    }

    public function get_items($request)
    {
        global $wpdb;
        $table = $this->table_name();

        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY urutan ASC, id ASC");

        return rest_ensure_response($results ?: []);
    }

    public function create_item($request)
    {
        global $wpdb;
        $table = $this->table_name();

        $data = [
            'nama' => sanitize_text_field($request->get_param('nama')),
            'jabatan' => sanitize_text_field($request->get_param('jabatan')),
            'nip' => sanitize_text_field($request->get_param('nip')),
            'foto' => esc_url_raw($request->get_param('foto')),
            'parent_id' => intval($request->get_param('parent_id')),
            'urutan' => intval($request->get_param('urutan')),
        ];

        if (empty($data['nama']) || empty($data['jabatan'])) {
            return new \WP_Error('missing_fields', 'Nama dan Jabatan wajib diisi.', ['status' => 400]);
        }

        $wpdb->insert($table, $data);

        return rest_ensure_response([
            'success' => true,
            'id' => $wpdb->insert_id,
        ]);
    }

    public function update_item($request)
    {
        global $wpdb;
        $table = $this->table_name();
        $id = intval($request->get_param('id'));

        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new \WP_Error('not_found', 'Data perangkat tidak ditemukan.', ['status' => 404]);
        }

        $data = [];
        if ($request->has_param('nama')) {
            $data['nama'] = sanitize_text_field($request->get_param('nama'));
        }
        if ($request->has_param('jabatan')) {
            $data['jabatan'] = sanitize_text_field($request->get_param('jabatan'));
        }
        if ($request->has_param('nip')) {
            $data['nip'] = sanitize_text_field($request->get_param('nip'));
        }
        if ($request->has_param('foto')) {
            $data['foto'] = esc_url_raw($request->get_param('foto'));
        }
        if ($request->has_param('parent_id')) {
            $data['parent_id'] = intval($request->get_param('parent_id'));
        }
        if ($request->has_param('urutan')) {
            $data['urutan'] = intval($request->get_param('urutan'));
        }

        if (empty($data)) {
            return new \WP_Error('no_fields', 'Tidak ada data yang diubah.', ['status' => 400]);
        }

        $wpdb->update($table, $data, ['id' => $id]);

        return rest_ensure_response(['success' => true]);
    }

    public function delete_item($request)
    {
        global $wpdb;
        $table = $this->table_name();
        $id = intval($request->get_param('id'));

        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new \WP_Error('not_found', 'Data perangkat tidak ditemukan.', ['status' => 404]);
        }

        $wpdb->delete($table, ['id' => $id]);

        return rest_ensure_response(['success' => true]);
    }
}
