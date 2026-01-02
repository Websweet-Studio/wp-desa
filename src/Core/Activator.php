<?php

namespace WP_Desa\Core;

class Activator {

	public static function activate() {
		// Code to execute on plugin activation (e.g. create database tables)
		flush_rewrite_rules();
	}

}
