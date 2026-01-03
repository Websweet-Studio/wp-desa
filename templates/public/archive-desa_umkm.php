<?php get_header(); ?>

<div class="wp-desa-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <header class="page-header" style="text-align: center; margin-bottom: 40px;">
        <h1 class="page-title" style="font-size: 2.5rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
            <?php post_type_archive_title(); ?>
        </h1>
        <?php if (term_description()): ?>
            <div class="taxonomy-description" style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                <?php echo term_description(); ?>
            </div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="wp-desa-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden; transition: transform 0.2s; display: flex; flex-direction: column;">
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail" style="height: 200px; overflow: hidden;">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium_large', ['style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;']); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="post-thumbnail" style="height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <span class="dashicons dashicons-store" style="font-size: 48px; width: 48px; height: 48px;"></span>
                        </div>
                    <?php endif; ?>

                    <div class="entry-content" style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
                        <h2 class="entry-title" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 10px; line-height: 1.4;">
                            <a href="<?php the_permalink(); ?>" style="color: #0f172a; text-decoration: none;"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="entry-excerpt" style="color: #64748b; font-size: 0.95rem; margin-bottom: 20px; flex-grow: 1;">
                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                        </div>

                        <div class="entry-meta" style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                            <?php
                            $terms = get_the_terms(get_the_ID(), 'desa_umkm_cat');
                            if ($terms && !is_wp_error($terms)) :
                                $term = array_shift($terms);
                            ?>
                                <span class="term-badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 9999px; font-size: 0.8rem; font-weight: 500;">
                                    <?php echo $term->name; ?>
                                </span>
                            <?php endif; ?>

                            <a href="<?php the_permalink(); ?>" style="color: #2563eb; font-weight: 500; text-decoration: none; font-size: 0.9rem;">
                                Lihat Detail &rarr;
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
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 20px;">Belum ada data UMKM yang ditemukan.</p>
            <a href="<?php echo home_url(); ?>" style="display: inline-block; padding: 10px 20px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 500;">Kembali ke Beranda</a>
        </div>
    <?php endif; ?>
</div>

<style>
    .wp-desa-grid article:hover { transform: translateY(-5px); }
    .wp-desa-grid article:hover img { transform: scale(1.05); }
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
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
    .pagination .page-numbers.dots {
        border: none;
        background: transparent;
    }
</style>

<?php get_footer(); ?>
