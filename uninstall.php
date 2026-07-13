<?php
/**
 * WP Desa Uninstall
 *
 * Deletes all custom database tables, options, post meta, posts, and terms
 * created by the plugin.
 *
 * @package WP_Desa
 */

// If uninstall not called from WordPress, exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// 1. Delete custom database tables.
$tables = [
    $wpdb->prefix . 'desa_residents',
    $wpdb->prefix . 'desa_letter_types',
    $wpdb->prefix . 'desa_letters',
    $wpdb->prefix . 'desa_complaints',
    $wpdb->prefix . 'desa_finances',
    $wpdb->prefix . 'desa_programs',
    $wpdb->prefix . 'desa_program_recipients',
];

foreach ($tables as $table) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// 2. Delete plugin options.
delete_option('wp_desa_settings');

// 3. Delete UMKM post meta.
$meta_keys = [
    '_desa_umkm_phone',
    '_desa_umkm_location',
    '_desa_umkm_gallery',
];

foreach ($meta_keys as $meta_key) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->delete(
        $wpdb->postmeta,
        ['meta_key' => $meta_key],
        ['%s']
    );
}

// 4. Delete all posts of custom post types.
$post_types = ['desa_umkm', 'desa_potensi'];

foreach ($post_types as $post_type) {
    $posts = get_posts(
        [
            'post_type'      => $post_type,
            'numberposts'    => -1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ]
    );

    foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
    }
}

// 5. Delete all terms of custom taxonomies.
$taxonomies = ['desa_umkm_cat', 'desa_potensi_cat'];

foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
        ]
    );

    if (! is_wp_error($terms) && ! empty($terms)) {
        foreach ($terms as $term_id) {
            wp_delete_term($term_id, $taxonomy);
        }
    }
}
