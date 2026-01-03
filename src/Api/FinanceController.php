<?php

namespace WpDesa\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class FinanceController extends WP_REST_Controller {
    public function register_routes() {
        $namespace = 'wp-desa/v1';
        $base = 'finances';

        // Get All Finances (with filters)
        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => '__return_true', // Allow public access for transparency
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Get Summary (for Charts)
        register_rest_route($namespace, '/' . $base . '/summary', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_summary'],
                'permission_callback' => '__return_true',
            ],
        ]);

        // Seed
        register_rest_route($namespace, '/' . $base . '/seed', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'seed_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Single Item Operations
        register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => '__return_true',
            ],
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

    public function permissions_check() {
        return current_user_can('manage_options');
    }

    public function get_items($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_finances';
        
        $year = $request->get_param('year');
        $type = $request->get_param('type');
        
        $where = "WHERE 1=1";
        $args = [];

        if (!empty($year)) {
            $where .= " AND year = %d";
            $args[] = $year;
        }

        if (!empty($type)) {
            $where .= " AND type = %s";
            $args[] = $type;
        }

        $sql = "SELECT * FROM $table $where ORDER BY transaction_date DESC";
        
        if (!empty($args)) {
            $sql = $wpdb->prepare($sql, $args);
        }

        $results = $wpdb->get_results($sql);
        return rest_ensure_response($results);
    }

    public function get_summary($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_finances';
        
        $year = $request->get_param('year') ?: date('Y');

        // Total Income vs Expense (Budget vs Realization)
        $totals = $wpdb->get_results($wpdb->prepare("
            SELECT type, SUM(budget_amount) as total_budget, SUM(realization_amount) as total_realization 
            FROM $table 
            WHERE year = %d 
            GROUP BY type
        ", $year));

        // Income Sources Breakdown
        $income_sources = $wpdb->get_results($wpdb->prepare("
            SELECT category, SUM(realization_amount) as total 
            FROM $table 
            WHERE year = %d AND type = 'income' 
            GROUP BY category
        ", $year));

        // Expense Breakdown
        $expense_sources = $wpdb->get_results($wpdb->prepare("
            SELECT category, SUM(realization_amount) as total 
            FROM $table 
            WHERE year = %d AND type = 'expense' 
            GROUP BY category
        ", $year));

        return rest_ensure_response([
            'year' => $year,
            'totals' => $totals,
            'income_sources' => $income_sources,
            'expense_sources' => $expense_sources
        ]);
    }

    public function create_item($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_finances';
        
        // Ensure table exists
        \WpDesa\Database\Activator::activate();

        $params = $request->get_json_params();
        
        $data = [
            'year' => isset($params['year']) ? intval($params['year']) : date('Y'),
            'type' => sanitize_text_field($params['type']),
            'category' => sanitize_text_field($params['category']),
            'description' => sanitize_textarea_field($params['description']),
            'budget_amount' => floatval($params['budget_amount']),
            'realization_amount' => floatval($params['realization_amount']),
            'transaction_date' => isset($params['transaction_date']) ? sanitize_text_field($params['transaction_date']) : current_time('Y-m-d'),
            'created_at' => current_time('mysql'),
        ];

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Gagal menyimpan data keuangan', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true, 'id' => $wpdb->insert_id]);
    }

    public function update_item($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_finances';
        $id = $request['id'];
        $params = $request->get_json_params();

        $data = [];
        if (isset($params['year'])) $data['year'] = intval($params['year']);
        if (isset($params['type'])) $data['type'] = sanitize_text_field($params['type']);
        if (isset($params['category'])) $data['category'] = sanitize_text_field($params['category']);
        if (isset($params['description'])) $data['description'] = sanitize_textarea_field($params['description']);
        if (isset($params['budget_amount'])) $data['budget_amount'] = floatval($params['budget_amount']);
        if (isset($params['realization_amount'])) $data['realization_amount'] = floatval($params['realization_amount']);
        if (isset($params['transaction_date'])) $data['transaction_date'] = sanitize_text_field($params['transaction_date']);

        $result = $wpdb->update($table, $data, ['id' => $id]);

        if ($result === false) {
            return new WP_Error('db_update_error', 'Gagal update data', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true]);
    }

    public function delete_item($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'desa_finances';
        $id = $request['id'];

        $result = $wpdb->delete($table, ['id' => $id]);

        if ($result === false) {
            return new WP_Error('db_delete_error', 'Gagal hapus data', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true]);
    }

    public function seed_items($request) {
        require_once plugin_dir_path(dirname(__FILE__)) . 'Database/Seeder.php';
        
        $count = \WpDesa\Database\Seeder::seed_finances(50);
        
        return rest_ensure_response([
            'success' => true, 
            'message' => "$count dummy finance records created.",
            'count' => $count
        ]);
    }
}
