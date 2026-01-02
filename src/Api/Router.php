<?php

namespace WP_Desa\Api;

use WP_Desa\Api\Controllers\VillageController;

class Router {

	private $namespace = 'wp-desa/v1';

	public function register_routes() {
		$controllers = [
			new VillageController( $this->namespace )
		];

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

}
