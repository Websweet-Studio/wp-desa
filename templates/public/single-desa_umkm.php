<?php get_header(); ?>

<div class="wp-desa-container" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">
    <?php while (have_posts()) : the_post(); ?>
        
        <div class="breadcrumb" style="margin-bottom: 20px; color: #64748b; font-size: 0.9rem;">
            <a href="<?php echo home_url(); ?>" style="color: #64748b; text-decoration: none;">Beranda</a>
            <span style="margin: 0 8px;">/</span>
            <a href="<?php echo get_post_type_archive_link('desa_umkm'); ?>" style="color: #64748b; text-decoration: none;">UMKM Desa</a>
            <span style="margin: 0 8px;">/</span>
            <span style="color: #1e293b;"><?php the_title(); ?></span>
        </div>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
            
            <?php if (has_post_thumbnail()) : ?>
                <div class="post-hero" style="height: 400px; position: relative; overflow: hidden;">
                    <?php the_post_thumbnail('full', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                    <div class="hero-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 40px;"></div>
                </div>
            <?php endif; ?>

            <div class="entry-wrapper" style="padding: 40px;">
                <header class="entry-header" style="margin-bottom: 30px;">
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'desa_umkm_cat');
                    if ($terms && !is_wp_error($terms)) :
                        $term = array_shift($terms);
                    ?>
                        <span class="term-badge" style="background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; display: inline-block; margin-bottom: 15px;">
                            <?php echo $term->name; ?>
                        </span>
                    <?php endif; ?>

                    <h1 class="entry-title" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin: 0 0 15px 0;">
                        <?php the_title(); ?>
                    </h1>

                    <div class="meta-info" style="display: flex; gap: 20px; color: #64748b; font-size: 0.95rem;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo get_the_date(); ?>
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span class="dashicons dashicons-admin-users"></span>
                            <?php the_author(); ?>
                        </span>
                    </div>
                </header>

                <div class="entry-content" style="color: #334155; line-height: 1.8; font-size: 1.1rem;">
                    <?php the_content(); ?>
                </div>

                <?php
                // Get Meta Data
                $phone = get_post_meta(get_the_ID(), '_desa_umkm_phone', true);
                $location = get_post_meta(get_the_ID(), '_desa_umkm_location', true);
                ?>

                <?php if ($phone || $location) : ?>
                    <div class="umkm-details" style="margin-top: 40px; padding: 30px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; color: #1e293b;">Informasi Kontak</h3>
                        
                        <?php if ($phone) : ?>
                            <div class="contact-item" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <span class="dashicons dashicons-whatsapp" style="color: #25d366; font-size: 24px; width: 24px; height: 24px;"></span>
                                <div>
                                    <div style="font-size: 0.85rem; color: #64748b;">WhatsApp</div>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $phone); ?>" target="_blank" style="color: #0f172a; font-weight: 600; text-decoration: none;">
                                        <?php echo esc_html($phone); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($location) : ?>
                            <div class="contact-item" style="display: flex; align-items: center; gap: 10px;">
                                <span class="dashicons dashicons-location" style="color: #ef4444; font-size: 24px; width: 24px; height: 24px;"></span>
                                <div>
                                    <div style="font-size: 0.85rem; color: #64748b;">Lokasi</div>
                                    <a href="https://maps.google.com/?q=<?php echo esc_attr($location); ?>" target="_blank" style="color: #0f172a; font-weight: 600; text-decoration: none;">
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </article>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
