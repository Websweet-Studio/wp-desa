<?php

namespace WpDesa\Admin;

class AdminLayout
{
    public static function get_pages()
    {
        return [
            ['page' => 'wp-desa', 'label' => 'Dashboard'],
            ['page' => 'wp-desa-residents', 'label' => 'Penduduk'],
            ['page' => 'wp-desa-letters', 'label' => 'Surat'],
            ['page' => 'wp-desa-complaints', 'label' => 'Aduan'],
            ['page' => 'wp-desa-finances', 'label' => 'Keuangan'],
            ['page' => 'wp-desa-aid', 'label' => 'Bantuan'],
            ['page' => 'wp-desa-settings', 'label' => 'Pengaturan'],
        ];
    }

    public static function open($page_title, $active_page, $subnav = [])
    {
        $pages = self::get_pages();
        $plugin_name = 'WP Desa';
?>
        <div class="wrap wp-desa-dashboard wp-desa">
            <div class="wp-desa__globalnav">
                <div class="wp-desa__brand">
                    <span class="dashicons dashicons-admin-home"></span>
                    <span><?php echo esc_html($plugin_name); ?></span>
                </div>
                <nav class="wp-desa__nav" aria-label="WP Desa Navigation">
                    <?php foreach ($pages as $item) :
                        $url = admin_url('admin.php?page=' . $item['page']);
                        $is_active = ($active_page === $item['page']);
                    ?>
                        <a class="wp-desa__navlink <?php echo $is_active ? 'is-active' : ''; ?>" href="<?php echo esc_url($url); ?>">
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="wp-desa__subnav">
                <div class="wp-desa__subnav-title"><?php echo esc_html($page_title); ?></div>
                <?php if (!empty($subnav)) : ?>
                    <nav class="wp-desa__subnav-tabs" aria-label="Sub Navigation">
                        <?php
                        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
                        $default_tab = $subnav[0]['tab'];
                        foreach ($subnav as $tab) :
                            $tab_url = admin_url('admin.php?page=' . $active_page . '&tab=' . $tab['tab']);
                            $tab_active = ($current_tab === $tab['tab']) || (empty($current_tab) && $tab['tab'] === $default_tab);
                        ?>
                            <a class="wp-desa__subnav-tab <?php echo $tab_active ? 'is-active' : ''; ?>" href="<?php echo esc_url($tab_url); ?>">
                                <?php echo esc_html($tab['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="wp-desa__content">
            <?php
    }

    public static function close()
    {
            ?>
            </div>
        </div>
<?php
    }

    /**
     * Fix WordPress admin menu active state
     */
    public static function fix_parent_file($parent_file)
    {
        global $plugin_page;

        $wp_desa_pages = ['wp-desa', 'wp-desa-residents', 'wp-desa-letters', 'wp-desa-complaints', 'wp-desa-finances', 'wp-desa-aid', 'wp-desa-settings'];

        if (in_array($plugin_page, $wp_desa_pages)) {
            $parent_file = 'wp-desa';
        }

        return $parent_file;
    }

    /**
     * Fix submenu active state
     */
    public static function submenu_file($submenu_file)
    {
        global $plugin_page;

        $wp_desa_pages = ['wp-desa', 'wp-desa-residents', 'wp-desa-letters', 'wp-desa-complaints', 'wp-desa-finances', 'wp-desa-aid', 'wp-desa-settings'];

        if (in_array($plugin_page, $wp_desa_pages)) {
            $submenu_file = $plugin_page;
        }

        return $submenu_file;
    }
}
