<?php

namespace WP_Desa\Frontend;

class Shortcodes {

	public function register() {
		add_shortcode( 'wp_desa_info', [ $this, 'render_info' ] );
	}

	public function render_info( $atts ) {
		return '
		<div x-data="villageInfo" class="wp-desa-container">
			<template x-if="loading">
				<p>Loading data...</p>
			</template>
			<template x-if="!loading">
				<div>
					<h2 x-text="info.name"></h2>
					<p x-text="info.description"></p>
					<p x-text="info.message"></p>
				</div>
			</template>
		</div>
		';
	}

}
