<?php
/**
 * The template for displaying the blog posts index (when front page is set to static)
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$posts_per_page = 10;
$index_limit = 12;

// Get all posts for index
$all_posts_query = new WP_Query( array(
    'post_type'      => 'post',
    'posts_per_page' => 100,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

$all_posts = array();
if ( $all_posts_query->have_posts() ) {
    while ( $all_posts_query->have_posts() ) {
        $all_posts_query->the_post();
        $all_posts[] = array(
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'url'   => get_permalink(),
        );
    }
    wp_reset_postdata();
}

$index_posts = array_slice( $all_posts, 0, $index_limit );
$total_posts = count( $all_posts );
$total_pages = ceil( $total_posts / $posts_per_page );
?>

<header class="writing__header">
        <h1 class="page-title"><?php _e( 'Writing', 'fabian-theme' ); ?></h1>
        <p class="writing__intro">
            <?php _e( 'Thoughts on design, creativity, and building things for the web.', 'fabian-theme' ); ?>
        </p>
    </header>

    <?php if ( ! empty( $index_posts ) ) : ?>
        <nav class="writing__index">
            <ul class="writing__index-list">
                <?php foreach ( $index_posts as $post_item ) : ?>
                    <li>
                        <a href="<?php echo esc_url( $post_item['url'] ); ?>"><?php echo esc_html( $post_item['title'] ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <?php if ( have_posts() ) : ?>
        <div id="writing-posts" class="category-archive__posts writing__posts">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'post-preview' ); ?>
            <?php endwhile; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'fabian-theme' ); ?>">
                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) . '#writing-posts' ); ?>" class="pagination__prev">
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
                                <a href="<?php echo esc_url( get_pagenum_link( $i ) . '#writing-posts' ); ?>" class="pagination__page">
                                    <?php echo esc_html( $i ); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>

                <?php if ( $paged < $total_pages ) : ?>
                    <span class="pagination__separator" aria-hidden="true"></span>
                    <a href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) . '#writing-posts' ); ?>" class="pagination__next">
                        <?php _e( 'Next', 'fabian-theme' ); ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else : ?>
        <p class="page-content"><?php _e( 'No posts found.', 'fabian-theme' ); ?></p>
    <?php endif; ?>

<?php
get_footer();
