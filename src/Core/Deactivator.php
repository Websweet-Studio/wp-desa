<?php

namespace WP_Desa\Core;

class Deactivator {

	public static function deactivate() {
		// Code to execute on plugin deactivation
		flush_rewrite_rules();
	}

}
