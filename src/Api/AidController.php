<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class AidController extends WP_REST_Controller {
    public function register_routes() {
        $namespace = 'wp-desa/v1';
        $base = 'aid-programs';

        // Programs CRUD
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_programs'],
                'permission_callback' => '__return_true', // Public
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_program'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_program'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_program'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_program'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Recipients Management
        register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)/recipients', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_recipients'],
                'permission_callback' => '__return_true', // Public (masked) or Admin (full)
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'add_recipient'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        register_rest_route($namespace, '/aid-recipients/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_recipient'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_recipient'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
        
        // Seed
        register_rest_route($namespace, '/aid/seed', [
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

    // --- Programs ---

    public function get_programs($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_programs';
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        return rest_ensure_response($results);
    }

    public function get_program($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_programs';
        $id = $request['id'];
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        return rest_ensure_response($result);
    }

    public function create_program($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_programs';
        
        $data = [
            'name' => sanitize_text_field($request['name']),
            'description' => sanitize_textarea_field($request['description']),
            'origin' => sanitize_text_field($request['origin']),
            'year' => intval($request['year']),
            'quota' => intval($request['quota']),
            'amount_per_recipient' => floatval($request['amount_per_recipient']),
            'status' => 'active'
        ];

        if ($wpdb->insert($table, $data)) {
            return rest_ensure_response(['success' => true, 'id' => $wpdb->insert_id]);
        }
        return new WP_Error('db_error', 'Could not create program', ['status' => 500]);
    }

    public function update_program($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_programs';
        $id = $request['id'];
        
        $data = [];
        if (isset($request['name'])) $data['name'] = sanitize_text_field($request['name']);
        if (isset($request['description'])) $data['description'] = sanitize_textarea_field($request['description']);
        if (isset($request['origin'])) $data['origin'] = sanitize_text_field($request['origin']);
        if (isset($request['year'])) $data['year'] = intval($request['year']);
        if (isset($request['quota'])) $data['quota'] = intval($request['quota']);
        if (isset($request['amount_per_recipient'])) $data['amount_per_recipient'] = floatval($request['amount_per_recipient']);
        if (isset($request['status'])) $data['status'] = sanitize_text_field($request['status']);

        if (empty($data)) return rest_ensure_response(['success' => true]);

        if ($wpdb->update($table, $data, ['id' => $id])) {
            return rest_ensure_response(['success' => true]);
        }
        return rest_ensure_response(['success' => true]); // Return true even if no rows changed
    }

    public function delete_program($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_programs';
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        $id = $request['id'];

        // Delete recipients first
        $wpdb->delete($table_recipients, ['program_id' => $id]);
        
        if ($wpdb->delete($table, ['id' => $id])) {
            return rest_ensure_response(['success' => true]);
        }
        return new WP_Error('db_error', 'Could not delete program', ['status' => 500]);
    }

    // --- Recipients ---

    public function get_recipients($request) {
        global $wpdb;
        $program_id = $request['id'];
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        $table_residents = $wpdb->prefix . 'desa_residents';

        // Join with residents to get name and NIK
        $sql = "SELECT r.*, res.nama_lengkap, res.nik, res.alamat, res.jenis_kelamin 
                FROM $table_recipients r
                JOIN $table_residents res ON r.resident_id = res.id
                WHERE r.program_id = %d";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $program_id));

        // If public (not admin), mask NIK and Name partly
        if (!current_user_can('manage_options')) {
            foreach ($results as $row) {
                $row->nik = substr($row->nik, 0, 6) . '******' . substr($row->nik, -4);
                // Maybe mask name too? Requirement: "Daftar penerima (tanpa data sensitif)"
                // Usually name is public but NIK/Address details are sensitive. 
                // Let's just mask NIK.
                $row->alamat = 'Dusun ...'; // Mask address for privacy
            }
        }

        return rest_ensure_response($results);
    }

    public function add_recipient($request) {
        global $wpdb;
        $table_recipients = $wpdb->prefix . 'desa_program_recipients';
        $table_residents = $wpdb->prefix . 'desa_residents';
        
        $program_id = $request['id'];
        $nik = sanitize_text_field($request['nik']);

        // Find resident ID by NIK
        $resident = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_residents WHERE nik = %s", $nik));
        
        if (!$resident) {
            return new WP_Error('not_found', 'Penduduk dengan NIK tersebut tidak ditemukan', ['status' => 404]);
        }

        // Check if already in program
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_recipients WHERE program_id = %d AND resident_id = %d",
            $program_id, $resident->id
        ));

        if ($exists) {
            return new WP_Error('duplicate', 'Penduduk sudah terdaftar di program ini', ['status' => 400]);
        }

        $data = [
            'program_id' => $program_id,
            'resident_id' => $resident->id,
            'status' => 'pending'
        ];

        if ($wpdb->insert($table_recipients, $data)) {
            return rest_ensure_response(['success' => true, 'id' => $wpdb->insert_id]);
        }
        return new WP_Error('db_error', 'Gagal menambahkan penerima', ['status' => 500]);
    }

    public function update_recipient($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_program_recipients';
        $id = $request['id'];
        
        $status = sanitize_text_field($request['status']);
        $data = ['status' => $status];
        
        if ($status === 'distributed') {
            $data['distributed_at'] = current_time('mysql');
        }

        if ($wpdb->update($table, $data, ['id' => $id])) {
            return rest_ensure_response(['success' => true]);
        }
        return rest_ensure_response(['success' => true]);
    }

    public function delete_recipient($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_program_recipients';
        $id = $request['id'];

        if ($wpdb->delete($table, ['id' => $id])) {
            return rest_ensure_response(['success' => true]);
        }
        return new WP_Error('db_error', 'Gagal menghapus penerima', ['status' => 500]);
    }

    public function seed_items($request) {
        \WpDesa\Database\Seeder::seed_aid();
        return rest_ensure_response(['success' => true, 'message' => 'Data dummy Bantuan berhasil dibuat.']);
    }
}
