<?php

namespace WpDesa\Core;

use WpDesa\Admin\Menu;
use WpDesa\Api\ResidentController;
use WpDesa\Api\DashboardController;

class Plugin
{
    public function run()
    {
        $this->load_core();
        $this->load_admin();
        $this->load_api();
        $this->load_frontend();
    }

    private function load_core()
    {
        $post_types = new PostTypes();
        $post_types->register();
    }

    private function load_admin()
    {
        if (is_admin()) {
            $menu = new Menu();
            add_action('admin_menu', [$menu, 'register_menus']);
            add_action('admin_enqueue_scripts', [$menu, 'enqueue_scripts']);
            add_action('in_admin_header', [$menu, 'remove_notices']);

            $meta_boxes = new \WpDesa\Admin\MetaBoxes();
            $meta_boxes->register();

            $printer = new \WpDesa\Admin\PrintHandler();
            add_action('admin_post_wp_desa_print_letter', [$printer, 'handle_print']);
        }
    }

    private function load_api()
    {
        $api = new ResidentController();
        add_action('rest_api_init', [$api, 'register_routes']);

        $dashboard = new DashboardController();
        add_action('rest_api_init', [$dashboard, 'register_routes']);

        $letters = new \WpDesa\Api\LetterController();
        add_action('rest_api_init', [$letters, 'register_routes']);

        $complaints = new \WpDesa\Api\ComplaintController();
        add_action('rest_api_init', [$complaints, 'register_routes']);

        $finances = new \WpDesa\Api\FinanceController();
        add_action('rest_api_init', [$finances, 'register_routes']);

        $aid = new \WpDesa\Api\AidController();
        add_action('rest_api_init', [$aid, 'register_routes']);
    }

    private function load_frontend()
    {
        $shortcode = new \WpDesa\Frontend\Shortcode();
        $shortcode->register();

        $template_loader = new \WpDesa\Core\TemplateLoader();
        $template_loader->register();

        $bb_loader = new \WpDesa\Integrations\BeaverBuilder\Loader();
        add_action('init', [$bb_loader, 'load']);

        $elementor_loader = new \WpDesa\Integrations\Elementor\Loader();
        add_action('init', [$elementor_loader, 'load']);
    }
}
