<?php
/**
 * Single template for Produk Hukum Desa
 */

get_header();
?>

<div class="wp-desa-single produk-hukum-single" style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $categories = get_the_terms(get_the_ID(), 'desa_produk_hukum_cat');
        $cat_name = $categories && !is_wp_error($categories) ? $categories[0]->name : '';
        ?>
        <div style="margin-bottom: 24px;">
            <?php if ($cat_name) : ?>
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-size: 0.85em; font-weight: 600; text-transform: uppercase;"><?php echo esc_html($cat_name); ?></span>
            <?php endif; ?>
            <span style="color: #94a3b8; font-size: 0.9em; margin-left: 12px;"><?php echo get_the_date(); ?></span>
        </div>

        <h1 style="font-size: 2em; color: #1e293b; margin: 0 0 8px 0; line-height: 1.3;">
            <?php the_title(); ?>
        </h1>

        <?php if (has_excerpt()) : ?>
            <p style="font-size: 1.15em; color: #475569; margin-bottom: 24px; line-height: 1.6;">
                <?php echo get_the_excerpt(); ?>
            </p>
        <?php endif; ?>

        <?php if (has_post_thumbnail()) : ?>
            <div style="margin-bottom: 24px; border-radius: 8px; overflow: hidden;">
                <?php the_post_thumbnail('large', ['style' => 'width: 100%; height: auto; display: block;']); ?>
            </div>
        <?php endif; ?>

        <div class="wp-desa-single-content" style="font-size: 1.05em; line-height: 1.8; color: #334155;">
            <?php the_content(); ?>
        </div>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="<?php echo get_post_type_archive_link('desa_produk_hukum'); ?>" style="display: inline-flex; align-items: center; gap: 6px; color: #2563eb; text-decoration: none; font-weight: 500;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Kembali ke Daftar Produk Hukum
            </a>
        </div>
    <?php endwhile; ?>
</div>

<?php
get_footer();
