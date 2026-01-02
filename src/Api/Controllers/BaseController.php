<?php

namespace WP_Desa\Api\Controllers;

use WP_REST_Controller;

abstract class BaseController extends WP_REST_Controller {

	protected $namespace;
	protected $rest_base;

	public function __construct( $namespace ) {
		$this->namespace = $namespace;
	}

	abstract public function register_routes();

}
