<?php

// Mapping feature_type to shortcode
switch ($settings->feature_type) {
    case 'profil':
        echo do_shortcode('[wp_desa_profil]');
        break;
    
    case 'kepala_desa':
        echo do_shortcode('[wp_desa_kepala_desa]');
        break;

    case 'statistik':
        echo do_shortcode('[wp_desa_statistik]');
        break;

    case 'umkm':
        $limit = $settings->umkm_limit ? $settings->umkm_limit : 6;
        $cols = $settings->umkm_cols ? $settings->umkm_cols : 3;
        echo do_shortcode("[wp_desa_umkm limit='{$limit}' cols='{$cols}']");
        break;

    case 'potensi':
        $limit = $settings->potensi_limit ? $settings->potensi_limit : 3;
        echo do_shortcode("[wp_desa_potensi limit='{$limit}']");
        break;

    case 'layanan':
        echo do_shortcode('[wp_desa_layanan]');
        break;

    case 'aduan':
        echo do_shortcode('[wp_desa_aduan]');
        break;

    case 'keuangan':
        echo do_shortcode('[wp_desa_keuangan]');
        break;

    case 'bantuan':
        echo do_shortcode('[wp_desa_bantuan]');
        break;
        
    default:
        echo 'Pilih fitur yang ingin ditampilkan.';
        break;
}
