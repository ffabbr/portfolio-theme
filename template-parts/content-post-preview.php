<?php
/**
 * Template part for displaying post previews
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get optimized archive preview - limited paragraphs with media stripped
$processed = fabian_get_archive_preview( 3 );
$toc_items = $processed['toc'];
$processed_content = $processed['content'];

// Get categories
$categories = get_the_category();
$category_names = array();
foreach ( $categories as $cat ) {
    $category_names[] = $cat->name;
}
?>

<article class="category-archive__post">
    <header class="category-archive__post-header">
        <h2 class="category-archive__post-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>
        <div class="blog-post__meta">
            <time class="blog-post__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( fabian_format_date() ); ?>
            </time>
            <span class="blog-post__separator">&middot;</span>
            <span class="blog-post__read-time"><?php echo esc_html( fabian_get_read_time() ); ?></span>
            <?php if ( ! empty( $category_names ) ) : ?>
                <span class="blog-post__separator">&middot;</span>
                <span class="blog-post__categories">
                    <?php echo esc_html( implode( ', ', $category_names ) ); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $toc_items ) ) : ?>
            <nav class="blog-post__toc" aria-label="<?php esc_attr_e( 'Table of contents', 'fabian-theme' ); ?>">
                <div class="toc-title"><?php _e( 'Contents', 'fabian-theme' ); ?></div>
                <ul>
                    <?php foreach ( $toc_items as $item ) : ?>
                        <li>
                            <span class="toc-item-disabled"><?php echo esc_html( $item['text'] ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </header>

    <div class="category-archive__post-content">
        <div class="blog-post__content category-archive__content-fade">
            <?php echo $processed_content; ?>
        </div>
        <div class="category-archive__read-more">
            <a href="<?php the_permalink(); ?>" class="category-archive__read-more-link">
                <?php _e( 'Open post', 'fabian-theme' ); ?>
            </a>
        </div>
    </div>
</article>
