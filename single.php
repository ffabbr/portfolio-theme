<?php
/**
 * The template for displaying single posts
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()):
    the_post(); ?>
    <?php
    // Process content for TOC
    $content = get_the_content();
    $content = apply_filters('the_content', $content);
    $processed = fabian_process_content_with_toc($content);
    $toc_items = $processed['toc'];
    $processed_content = $processed['content'];

    // Get categories
    $categories = get_the_category();
    ?>

    <article class="blog-post">
        <header class="blog-post__header">
            <h1 class="blog-post__title">
                <?php the_title(); ?> <?php
                      $subtitle = get_post_meta(get_the_ID(), '_fabian_subtitle', true);
                      if ($subtitle):
                          ?><span class="blog-post__subtitle"><?php echo esc_html($subtitle); ?></span><?php
                      endif;
                      ?>
            </h1>
            <div class="blog-post__meta">
                <time class="blog-post__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(fabian_format_date()); ?>
                </time>
                <span class="blog-post__separator">&middot;</span>
                <span class="blog-post__read-time"><?php echo esc_html(fabian_get_read_time()); ?></span>
                <?php if (!empty($categories)): ?>
                    <span class="blog-post__separator">&middot;</span>
                    <span class="blog-post__categories">
                        <?php
                        $cat_links = array();
                        foreach ($categories as $cat) {
                            $cat_links[] = '<a href="' . esc_url(home_url('/writing/category/' . $cat->slug . '/')) . '">' . esc_html($cat->name) . '</a>';
                        }
                        echo implode(', ', $cat_links);
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($toc_items)): ?>
                <nav class="blog-post__toc" aria-label="<?php esc_attr_e('Table of contents', 'fabian-theme'); ?>">
                    <div class="toc-title"><?php _e('Contents', 'fabian-theme'); ?></div>
                    <ul>
                        <?php foreach ($toc_items as $item): ?>
                            <li>
                                <a href="#<?php echo esc_attr($item['id']); ?>" class="toc-link">
                                    <?php echo esc_html($item['text']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <details class="blog-post__toc-mobile">
                    <summary><?php _e('Contents', 'fabian-theme'); ?></summary>
                    <ul>
                        <?php foreach ($toc_items as $item): ?>
                            <li>
                                <a href="#<?php echo esc_attr($item['id']); ?>" class="toc-link-mobile">
                                    <?php echo esc_html($item['text']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>

            <a href="<?php echo esc_url(home_url('/writing/')); ?>" class="blog-post__all-posts">&larr; All posts</a>
        </header>

        <div class="blog-post__content">
            <?php echo $processed_content; ?>

            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            if ($prev_post || $next_post): ?>
                <nav class="post-navigation">
                    <?php if ($prev_post): ?>
                        <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" class="post-navigation__link post-navigation__link--prev">
                            <span class="post-navigation__arrow">&larr;</span>
                            <span class="post-navigation__title"><?php echo esc_html($prev_post->post_title); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($next_post): ?>
                        <a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="post-navigation__link post-navigation__link--next">
                            <span class="post-navigation__title"><?php echo esc_html($next_post->post_title); ?></span>
                            <span class="post-navigation__arrow">&rarr;</span>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </article>
<?php endwhile; ?>

<?php
get_footer();
