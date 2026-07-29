<?php
/**
 * Archive template for Produk Hukum Desa
 */

get_header();
?>

<div class="wp-desa-archive produk-hukum-archive" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <h1 class="wp-desa-archive-title" style="font-size: 2em; margin-bottom: 10px; color: #1e293b;">
        Produk Hukum Desa
    </h1>
    <p class="wp-desa-archive-desc" style="color: #64748b; margin-bottom: 30px; font-size: 1.05em;">
        Kumpulan peraturan desa, surat keputusan, dan produk hukum lainnya.
    </p>

    <?php if (have_posts()) : ?>
        <div class="wp-desa-produk-hukum-list" style="display: flex; flex-direction: column; gap: 16px;">
            <?php while (have_posts()) : the_post(); ?>
                <?php
                $categories = get_the_terms(get_the_ID(), 'desa_produk_hukum_cat');
                $cat_name = $categories && !is_wp_error($categories) ? $categories[0]->name : '';
                ?>
                <div class="wp-desa-produk-hukum-item" style="display: flex; gap: 20px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; align-items: center;">
                    <div style="flex-shrink: 0; width: 56px; height: 56px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 6px 0; font-size: 1.1em;">
                            <a href="<?php the_permalink(); ?>" style="color: #1e293b; text-decoration: none;">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        <div style="display: flex; gap: 16px; color: #94a3b8; font-size: 0.9em;">
                            <?php if ($cat_name) : ?>
                                <span style="background: #dbeafe; color: #1e40af; padding: 2px 10px; border-radius: 4px; font-size: 0.85em; font-weight: 500;"><?php echo esc_html($cat_name); ?></span>
                            <?php endif; ?>
                            <span><?php echo get_the_date(); ?></span>
                        </div>
                        <?php if (has_excerpt()) : ?>
                            <p style="margin: 8px 0 0 0; color: #64748b; font-size: 0.95em;"><?php echo get_the_excerpt(); ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="flex-shrink: 0;">
                        <a href="<?php the_permalink(); ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; font-size: 0.9em; font-weight: 500;">
                            Baca Selengkapnya
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="wp-desa-pagination" style="margin-top: 30px; text-align: center;">
            <?php the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => '&laquo; Sebelumnya',
                'next_text' => 'Berikutnya &raquo;',
            ]); ?>
        </div>
    <?php else : ?>
        <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 16px;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            <p style="font-size: 1.1em;">Belum ada produk hukum yang dipublikasikan.</p>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
