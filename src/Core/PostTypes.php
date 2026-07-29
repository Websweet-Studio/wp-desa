<?php

namespace WpDesa\Core;

class PostTypes
{
    public function register()
    {
        add_action('init', [$this, 'register_potensi_desa']);
        add_action('init', [$this, 'register_umkm_desa']);
        add_action('init', [$this, 'register_produk_hukum']);
        add_action('init', [$this, 'register_berita']);
        add_action('init', [$this, 'register_agenda']);
        add_action('init', [$this, 'register_galeri']);
        add_action('init', [$this, 'register_wisata']);
    }

    public function register_umkm_desa()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori UMKM',
            'singular_name'     => 'Kategori UMKM',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Induk Kategori',
            'parent_item_colon' => 'Induk Kategori:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_umkm_cat', ['desa_umkm'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-umkm'],
            'show_in_rest'      => true,
        ]);

        // Register Post Type
        $labels = [
            'name'                  => 'UMKM Desa',
            'singular_name'         => 'UMKM Desa',
            'menu_name'             => 'UMKM Desa',
            'name_admin_bar'        => 'UMKM Desa',
            'add_new'               => 'Tambah Baru',
            'add_new_item'          => 'Tambah UMKM Baru',
            'new_item'              => 'UMKM Baru',
            'edit_item'             => 'Edit UMKM',
            'view_item'             => 'Lihat UMKM',
            'all_items'             => 'Semua UMKM',
            'search_items'          => 'Cari UMKM',
            'parent_item_colon'     => 'Induk UMKM:',
            'not_found'             => 'Tidak ditemukan UMKM.',
            'not_found_in_trash'    => 'Tidak ditemukan di tempat sampah.',
            'featured_image'        => 'Foto Produk Utama',
            'set_featured_image'    => 'Atur foto produk',
            'remove_featured_image' => 'Hapus foto produk',
            'use_featured_image'    => 'Gunakan sebagai foto produk',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'umkm-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-store',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'       => true,
        ];

        register_post_type('desa_umkm', $args);
    }

    public function register_potensi_desa()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori Potensi',
            'singular_name'     => 'Kategori Potensi',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'parent_item'       => 'Induk Kategori',
            'parent_item_colon' => 'Induk Kategori:',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_potensi_cat', ['desa_potensi'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-potensi'],
            'show_in_rest'      => true,
        ]);

        // Register Post Type
        $labels = [
            'name'                  => 'Potensi Desa',
            'singular_name'         => 'Potensi Desa',
            'menu_name'             => 'Potensi Desa',
            'name_admin_bar'        => 'Potensi Desa',
            'add_new'               => 'Tambah Baru',
            'add_new_item'          => 'Tambah Potensi Baru',
            'new_item'              => 'Potensi Baru',
            'edit_item'             => 'Edit Potensi',
            'view_item'             => 'Lihat Potensi',
            'all_items'             => 'Semua Potensi',
            'search_items'          => 'Cari Potensi',
            'parent_item_colon'     => 'Induk Potensi:',
            'not_found'             => 'Tidak ditemukan potensi.',
            'not_found_in_trash'    => 'Tidak ditemukan di tempat sampah.',
            'featured_image'        => 'Gambar Utama',
            'set_featured_image'    => 'Atur gambar utama',
            'remove_featured_image' => 'Hapus gambar utama',
            'use_featured_image'    => 'Gunakan sebagai gambar utama',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'potensi-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-chart-pie',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'       => true,
        ];

        register_post_type('desa_potensi', $args);
    }

    public function register_produk_hukum()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori Produk Hukum',
            'singular_name'     => 'Kategori Produk Hukum',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_produk_hukum_cat', ['desa_produk_hukum'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-produk-hukum'],
            'show_in_rest'      => true,
        ]);

        // Register Post Type
        $labels = [
            'name'                  => 'Produk Hukum',
            'singular_name'         => 'Produk Hukum',
            'menu_name'             => 'Produk Hukum',
            'name_admin_bar'        => 'Produk Hukum',
            'add_new'               => 'Tambah Baru',
            'add_new_item'          => 'Tambah Produk Hukum Baru',
            'new_item'              => 'Produk Hukum Baru',
            'edit_item'             => 'Edit Produk Hukum',
            'view_item'             => 'Lihat Produk Hukum',
            'all_items'             => 'Semua Produk Hukum',
            'search_items'          => 'Cari Produk Hukum',
            'not_found'             => 'Tidak ditemukan produk hukum.',
            'not_found_in_trash'    => 'Tidak ditemukan di tempat sampah.',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'produk-hukum-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 8,
            'menu_icon'          => 'dashicons-media-document',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest'       => true,
        ];

        register_post_type('desa_produk_hukum', $args);
    }

    public function register_berita()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori Berita',
            'singular_name'     => 'Kategori Berita',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_berita_cat', ['desa_berita'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-berita'],
            'show_in_rest'      => true,
        ]);

        $labels = [
            'name'                  => 'Berita Desa',
            'singular_name'         => 'Berita Desa',
            'menu_name'             => 'Berita Desa',
            'add_new'               => 'Tambah Berita',
            'add_new_item'          => 'Tambah Berita Baru',
            'edit_item'             => 'Edit Berita',
            'view_item'             => 'Lihat Berita',
            'all_items'             => 'Semua Berita',
            'search_items'          => 'Cari Berita',
            'not_found'             => 'Tidak ditemukan.',
            'not_found_in_trash'    => 'Tidak ditemukan di sampah.',
        ];

        register_post_type('desa_berita', [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'berita-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 9,
            'menu_icon'          => 'dashicons-megaphone',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest'       => true,
        ]);
    }

    public function register_agenda()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori Agenda',
            'singular_name'     => 'Kategori Agenda',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_agenda_cat', ['desa_agenda'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-agenda'],
            'show_in_rest'      => true,
        ]);

        $labels = [
            'name'                  => 'Agenda Desa',
            'singular_name'         => 'Agenda Desa',
            'menu_name'             => 'Agenda Desa',
            'add_new'               => 'Tambah Agenda',
            'add_new_item'          => 'Tambah Agenda Baru',
            'edit_item'             => 'Edit Agenda',
            'view_item'             => 'Lihat Agenda',
            'all_items'             => 'Semua Agenda',
            'search_items'          => 'Cari Agenda',
            'not_found'             => 'Tidak ditemukan.',
            'not_found_in_trash'    => 'Tidak ditemukan di sampah.',
        ];

        register_post_type('desa_agenda', [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'agenda-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 10,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => ['title', 'editor', 'thumbnail'],
            'show_in_rest'       => true,
        ]);
    }

    public function register_galeri()
    {
        // Register Taxonomy
        $labels_cat = [
            'name'              => 'Kategori Galeri',
            'singular_name'     => 'Kategori Galeri',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_galeri_cat', ['desa_galeri'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-galeri'],
            'show_in_rest'      => true,
        ]);

        $labels = [
            'name'                  => 'Galeri Desa',
            'singular_name'         => 'Galeri Desa',
            'menu_name'             => 'Galeri Desa',
            'add_new'               => 'Tambah Galeri',
            'add_new_item'          => 'Tambah Galeri Baru',
            'edit_item'             => 'Edit Galeri',
            'view_item'             => 'Lihat Galeri',
            'all_items'             => 'Semua Galeri',
            'search_items'          => 'Cari Galeri',
            'not_found'             => 'Tidak ditemukan.',
            'not_found_in_trash'    => 'Tidak ditemukan di sampah.',
        ];

        register_post_type('desa_galeri', [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'galeri-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 11,
            'menu_icon'          => 'dashicons-format-gallery',
            'supports'           => ['title', 'editor', 'thumbnail'],
            'show_in_rest'       => true,
        ]);
    }

    public function register_wisata()
    {
        $labels_cat = [
            'name'              => 'Kategori Wisata',
            'singular_name'     => 'Kategori Wisata',
            'search_items'      => 'Cari Kategori',
            'all_items'         => 'Semua Kategori',
            'edit_item'         => 'Edit Kategori',
            'update_item'       => 'Update Kategori',
            'add_new_item'      => 'Tambah Kategori Baru',
            'new_item_name'     => 'Nama Kategori Baru',
            'menu_name'         => 'Kategori',
        ];

        register_taxonomy('desa_wisata_cat', ['desa_wisata'], [
            'hierarchical'      => true,
            'labels'            => $labels_cat,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'kategori-wisata'],
            'show_in_rest'      => true,
        ]);

        $labels = [
            'name'                  => 'Destinasi Wisata',
            'singular_name'         => 'Destinasi Wisata',
            'menu_name'             => 'Destinasi Wisata',
            'add_new'               => 'Tambah Baru',
            'add_new_item'          => 'Tambah Destinasi Baru',
            'edit_item'             => 'Edit Destinasi',
            'view_item'             => 'Lihat Destinasi',
            'all_items'             => 'Semua Destinasi',
            'search_items'          => 'Cari Destinasi',
            'not_found'             => 'Tidak ditemukan.',
            'not_found_in_trash'    => 'Tidak ditemukan di sampah.',
            'featured_image'        => 'Foto Utama',
            'set_featured_image'    => 'Atur foto utama',
            'remove_featured_image' => 'Hapus foto utama',
            'use_featured_image'    => 'Gunakan sebagai foto utama',
        ];

        register_post_type('desa_wisata', [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'wisata-desa'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 12,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest'       => true,
        ]);
    }
}
