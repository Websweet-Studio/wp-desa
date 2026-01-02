<?php

namespace WP_Desa\Frontend;

class Assets {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, WP_DESA_URL . 'assets/css/wp-desa.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		// Enqueue Alpine.js from CDN (or local if preferred, but CDN is easier for start)
		wp_enqueue_script( 'alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.x.x', true );
        // Add defer attribute to Alpine.js
        add_filter( 'script_loader_tag', function( $tag, $handle ) {
            if ( 'alpinejs' !== $handle ) {
                return $tag;
            }
            return str_replace( ' src', ' defer src', $tag );
        }, 10, 2 );

		wp_enqueue_script( $this->plugin_name, WP_DESA_URL . 'assets/js/wp-desa.js', array( 'jquery', 'alpinejs' ), $this->version, true );
        
        // Localize script for API URL
        wp_localize_script( $this->plugin_name, 'wpDesaSettings', [
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ] );
	}

}
