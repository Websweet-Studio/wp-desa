<?php

namespace WP_Desa\Api\Controllers;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class VillageController extends BaseController {

	public function __construct( $namespace ) {
		parent::__construct( $namespace );
		$this->rest_base = 'info';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return true; // Public access for now
	}

	public function get_items( $request ) {
		$data = [
			'name' => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
            'message' => 'Hello from WP Desa API'
		];

		return rest_ensure_response( $data );
	}

}
