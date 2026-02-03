<?php
/**
 * The template for displaying category archives
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$category = get_queried_object();
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$posts_per_page = 10;

// Custom query for category posts
$posts_query = new WP_Query( array(
    'post_type'      => 'post',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'cat'            => $category->term_id,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

$total_posts = $posts_query->found_posts;
$total_pages = ceil( $total_posts / $posts_per_page );
$base_url = home_url( '/writing/category/' . $category->slug . '/' );
?>

<header class="category-archive__header">
        <h1 class="page-title"><?php echo esc_html( $category->name ); ?></h1>
        <p class="category-archive__intro">
            <?php
            printf(
                _n( '%d post in this category', '%d posts in this category', $total_posts, 'fabian-theme' ),
                $total_posts
            );
            ?>
        </p>
    </header>

    <?php if ( $posts_query->have_posts() ) : ?>
        <div id="writing-posts" class="category-archive__posts">
            <?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'post-preview' ); ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'fabian-theme' ); ?>">
                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo esc_url( ( $paged === 2 ? $base_url : $base_url . 'page/' . ( $paged - 1 ) . '/' ) . '#writing-posts' ); ?>" class="pagination__prev">
                        <?php _e( 'Previous', 'fabian-theme' ); ?>
                    </a>
                    <span class="pagination__separator" aria-hidden="true"></span>
                <?php endif; ?>

                <ul class="pagination__list">
                    <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                        <li>
                            <?php if ( $i === $paged ) : ?>
                                <span class="pagination__page pagination__page--current" aria-current="page">
                                    <?php echo esc_html( $i ); ?>
                                </span>
                            <?php else : ?>
                                <a href="<?php echo esc_url( ( $i === 1 ? $base_url : $base_url . 'page/' . $i . '/' ) . '#writing-posts' ); ?>" class="pagination__page">
                                    <?php echo esc_html( $i ); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>

                <?php if ( $paged < $total_pages ) : ?>
                    <span class="pagination__separator" aria-hidden="true"></span>
                    <a href="<?php echo esc_url( $base_url . 'page/' . ( $paged + 1 ) . '/' . '#writing-posts' ); ?>" class="pagination__next">
                        <?php _e( 'Next', 'fabian-theme' ); ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else : ?>
        <p class="page-content"><?php _e( 'No posts found in this category.', 'fabian-theme' ); ?></p>
        <div style="margin-top: 24px;">
            <a href="<?php echo esc_url( home_url( '/writing/' ) ); ?>" class="btn btn-primary">
                <?php _e( 'Back to Writing', 'fabian-theme' ); ?>
            </a>
        </div>
    <?php endif; ?>

<?php
get_footer();
