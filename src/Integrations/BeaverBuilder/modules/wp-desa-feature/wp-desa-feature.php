<?php

class WpDesaFeatureModule extends FLBuilderModule
{
    public function __construct()
    {
        parent::__construct([
            'name'          => 'Fitur WP Desa',
            'description'   => 'Menampilkan berbagai fitur WP Desa (Profil, Statistik, UMKM, Layanan, dll)',
            'category'      => 'WP Desa',
            'dir'           => WP_DESA_PATH . 'src/Integrations/BeaverBuilder/modules/wp-desa-feature/',
            'url'           => WP_DESA_URL . 'src/Integrations/BeaverBuilder/modules/wp-desa-feature/',
            'icon'          => 'admin-home', // Dashicon without 'dashicons-' prefix
            'editor_export' => true,
            'enabled'       => true,
            'partial_refresh' => true,
        ]);
    }
}

FLBuilder::register_module('WpDesaFeatureModule', [
    'general' => [
        'title'    => 'Pengaturan',
        'sections' => [
            'general' => [
                'title'  => 'Pilih Fitur',
                'fields' => [
                    'feature_type' => [
                        'type'    => 'select',
                        'label'   => 'Jenis Fitur',
                        'default' => 'profil',
                        'options' => [
                            'profil'      => 'Profil Desa',
                            'kepala_desa' => 'Kepala Desa',
                            'statistik'   => 'Statistik Penduduk',
                            'umkm'        => 'UMKM Desa',
                            'potensi'     => 'Potensi Desa',
                            'layanan'     => 'Layanan Surat',
                            'aduan'       => 'Aspirasi & Pengaduan',
                            'keuangan'    => 'Keuangan Desa',
                            'bantuan'     => 'Program Bantuan',
                        ],
                        'toggle'  => [
                            'umkm'    => ['fields' => ['umkm_limit', 'umkm_cols']],
                            'potensi' => ['fields' => ['potensi_limit']],
                        ]
                    ],
                    'umkm_limit' => [
                        'type'    => 'unit',
                        'label'   => 'Jumlah UMKM',
                        'default' => 6
                    ],
                    'umkm_cols' => [
                        'type'    => 'select',
                        'label'   => 'Kolom Grid',
                        'default' => 3,
                        'options' => [
                            2 => '2 Kolom',
                            3 => '3 Kolom',
                            4 => '4 Kolom'
                        ]
                    ],
                    'potensi_limit' => [
                        'type'    => 'unit',
                        'label'   => 'Jumlah Potensi',
                        'default' => 3
                    ]
                ]
            ]
        ]
    ]
]);
