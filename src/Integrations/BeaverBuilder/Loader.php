<?php

namespace WpDesa\Integrations\BeaverBuilder;

class Loader
{
    public function load()
    {
        if (class_exists('FLBuilder')) {
            require_once WP_DESA_PATH . 'src/Integrations/BeaverBuilder/modules/wp-desa-feature/wp-desa-feature.php';
        }
    }
}
