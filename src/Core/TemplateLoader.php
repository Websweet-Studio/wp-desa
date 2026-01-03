<?php

namespace WpDesa\Core;

class TemplateLoader
{
    public function register()
    {
        add_filter('template_include', [$this, 'load_template']);
    }

    public function load_template($template)
    {
        // UMKM Desa
        if (is_post_type_archive('desa_umkm') || is_tax('desa_umkm_cat')) {
            $theme_file = locate_template(['archive-desa_umkm.php']);
            if ($theme_file) {
                return $theme_file;
            }
            return WP_DESA_PATH . 'templates/public/archive-desa_umkm.php';
        }

        if (is_singular('desa_umkm')) {
            $theme_file = locate_template(['single-desa_umkm.php']);
            if ($theme_file) {
                return $theme_file;
            }
            return WP_DESA_PATH . 'templates/public/single-desa_umkm.php';
        }

        // Potensi Desa
        if (is_post_type_archive('desa_potensi') || is_tax('desa_potensi_cat')) {
            $theme_file = locate_template(['archive-desa_potensi.php']);
            if ($theme_file) {
                return $theme_file;
            }
            return WP_DESA_PATH . 'templates/public/archive-desa_potensi.php';
        }

        if (is_singular('desa_potensi')) {
            $theme_file = locate_template(['single-desa_potensi.php']);
            if ($theme_file) {
                return $theme_file;
            }
            return WP_DESA_PATH . 'templates/public/single-desa_potensi.php';
        }

        return $template;
    }
}
