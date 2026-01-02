<?php

namespace WpDesa\Core;

use WpDesa\Admin\Menu;
use WpDesa\Api\ResidentController;
use WpDesa\Api\DashboardController;

class Plugin {
    public function run() {
        $this->load_admin();
        $this->load_api();
    }

    private function load_admin() {
        if (is_admin()) {
            $menu = new Menu();
            add_action('admin_menu', [$menu, 'register_menus']);
            add_action('admin_enqueue_scripts', [$menu, 'enqueue_scripts']);
        }
    }

    private function load_api() {
        $api = new ResidentController();
        add_action('rest_api_init', [$api, 'register_routes']);
        
        $dashboard = new DashboardController();
        add_action('rest_api_init', [$dashboard, 'register_routes']);
    }
}
