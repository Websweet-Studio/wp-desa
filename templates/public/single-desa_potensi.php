<?php get_header(); ?>

<div class="wp-desa-container" style="max-width: 900px; margin: 0 auto; padding: 60px 20px;">
    <?php while (have_posts()) : the_post(); ?>
        
        <div class="breadcrumb" style="margin-bottom: 30px; text-align: center; color: #64748b; font-size: 0.95rem;">
            <a href="<?php echo home_url(); ?>" style="color: #64748b; text-decoration: none;">Beranda</a>
            <span style="margin: 0 8px;">/</span>
            <a href="<?php echo get_post_type_archive_link('desa_potensi'); ?>" style="color: #64748b; text-decoration: none;">Potensi Desa</a>
            <span style="margin: 0 8px;">/</span>
            <span style="color: #1e293b;"><?php the_title(); ?></span>
        </div>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <header class="entry-header" style="text-align: center; margin-bottom: 40px;">
                <?php
                $terms = get_the_terms(get_the_ID(), 'desa_potensi_cat');
                if ($terms && !is_wp_error($terms)) :
                    $term = array_shift($terms);
                ?>
                    <span class="term-badge" style="background: #dcfce7; color: #166534; padding: 6px 16px; border-radius: 9999px; font-size: 0.9rem; font-weight: 600; display: inline-block; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <?php echo $term->name; ?>
                    </span>
                <?php endif; ?>

                <h1 class="entry-title" style="font-size: 3rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin: 0 0 20px 0;">
                    <?php the_title(); ?>
                </h1>

                <div class="entry-meta" style="color: #64748b;">
                    Diterbitkan pada <?php echo get_the_date(); ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="post-image" style="margin-bottom: 50px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                    <?php the_post_thumbnail('full', ['style' => 'width: 100%; height: auto; display: block;']); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content" style="font-size: 1.2rem; line-height: 1.8; color: #334155;">
                <?php the_content(); ?>
            </div>

            <footer class="entry-footer" style="margin-top: 60px; padding-top: 40px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div class="share-links">
                    <span style="font-weight: 600; color: #1e293b; margin-right: 15px;">Bagikan:</span>
                    <!-- Simple share links could be added here if needed -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" style="color: #3b5998; margin-right: 10px; text-decoration: none; font-weight: 500;">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>&text=<?php the_title(); ?>" target="_blank" style="color: #1da1f2; margin-right: 10px; text-decoration: none; font-weight: 500;">Twitter</a>
                    <a href="https://wa.me/?text=<?php the_title(); ?>%20<?php the_permalink(); ?>" target="_blank" style="color: #25d366; text-decoration: none; font-weight: 500;">WhatsApp</a>
                </div>
            </footer>

        </article>

    <?php endwhile; ?>
</div>

<style>
    .entry-content p { margin-bottom: 1.5em; }
    .entry-content h2 { font-size: 2rem; margin-top: 2em; margin-bottom: 0.75em; color: #1e293b; font-weight: 700; }
    .entry-content h3 { font-size: 1.5rem; margin-top: 2em; margin-bottom: 0.75em; color: #1e293b; font-weight: 600; }
    .entry-content ul, .entry-content ol { margin-bottom: 1.5em; padding-left: 1.5em; }
    .entry-content li { margin-bottom: 0.5em; }
    .entry-content blockquote { border-left: 4px solid #16a34a; padding-left: 20px; font-style: italic; color: #475569; margin: 2em 0; }
    .entry-content img { max-width: 100%; height: auto; border-radius: 12px; }
</style>

<?php get_footer(); ?>
