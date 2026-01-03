<?php get_header(); ?>

<div class="wp-desa-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <header class="page-header" style="text-align: center; margin-bottom: 50px;">
        <h1 class="page-title" style="font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 15px;">
            <?php post_type_archive_title(); ?>
        </h1>
        <?php if (term_description()): ?>
            <div class="taxonomy-description" style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="wp-desa-list" style="display: flex; flex-direction: column; gap: 30px;">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; display: flex; flex-direction: row; align-items: stretch; transition: transform 0.2s;">
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail" style="width: 300px; flex-shrink: 0; overflow: hidden;">
                            <a href="<?php the_permalink(); ?>" style="display: block; height: 100%;">
                                <?php the_post_thumbnail('medium_large', ['style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;']); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="entry-content" style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
                        <div class="entry-meta" style="margin-bottom: 10px;">
                            <?php
                            $terms = get_the_terms(get_the_ID(), 'desa_potensi_cat');
                            if ($terms && !is_wp_error($terms)) :
                                $term = array_shift($terms);
                            ?>
                                <span class="term-badge" style="color: #16a34a; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?php echo $term->name; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="entry-title" style="font-size: 1.75rem; font-weight: 700; margin: 0 0 15px 0; line-height: 1.3;">
                            <a href="<?php the_permalink(); ?>" style="color: #1e293b; text-decoration: none;"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="entry-excerpt" style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 25px;">
                            <?php echo wp_trim_words(get_the_excerpt(), 30); ?>
                        </div>

                        <div class="entry-footer">
                            <a href="<?php the_permalink(); ?>" style="display: inline-flex; align-items: center; gap: 6px; color: #fff; background: #16a34a; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: background 0.2s;">
                                Selengkapnya <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 18px; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;"></span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="pagination" style="margin-top: 50px; text-align: center;">
            <?php
            the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => '&larr; Sebelumnya',
                'next_text' => 'Selanjutnya &rarr;',
            ]);
            ?>
        </div>

    <?php else : ?>
        <div class="no-results" style="text-align: center; padding: 60px 0; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 20px;">Belum ada data Potensi Desa yang ditemukan.</p>
        </div>
    <?php endif; ?>
</div>

<style>
    @media (max-width: 768px) {
        .wp-desa-list article { flex-direction: column; }
        .wp-desa-list .post-thumbnail { width: 100%; height: 200px; }
    }
    .wp-desa-list article:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .wp-desa-list article:hover .post-thumbnail img { transform: scale(1.05); }
    .pagination .page-numbers {
        display: inline-block;
        padding: 8px 16px;
        margin: 0 4px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    .pagination .page-numbers.current,
    .pagination .page-numbers:hover {
        background: #16a34a;
        color: #fff;
        border-color: #16a34a;
    }
    .pagination .page-numbers.dots {
        border: none;
        background: transparent;
    }
</style>

<?php get_footer(); ?>
