<?php
/**
 * Template part for displaying pagination
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wp_query;

$total_pages = $wp_query->max_num_pages;

if ( $total_pages <= 1 ) {
    return;
}

$current_page = max( 1, get_query_var( 'paged' ) );
$post_type = $wp_query->get( 'post_type' );

if ( is_array( $post_type ) ) {
    $post_type = $post_type[0] ?? '';
}

// For the main blog (posts), jump back to the posts list after pagination.
// Exclude taxonomy archives (e.g. photo collections) which don't use this anchor.
$pagination_fragment = ( ! is_tax() && ( empty( $post_type ) || $post_type === 'post' ) ) ? '#writing-posts' : '';
?>

<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'fabian-theme' ); ?>">
    <?php if ( $current_page > 1 ) : ?>
        <a href="<?php echo esc_url( get_pagenum_link( $current_page - 1 ) . $pagination_fragment ); ?>" class="pagination__prev">
            <?php _e( 'Previous', 'fabian-theme' ); ?>
        </a>
        <span class="pagination__separator" aria-hidden="true"></span>
    <?php endif; ?>

    <ul class="pagination__list">
        <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
            <li>
                <?php if ( $i === $current_page ) : ?>
                    <span class="pagination__page pagination__page--current" aria-current="page">
                        <?php echo esc_html( $i ); ?>
                    </span>
                <?php else : ?>
                    <a href="<?php echo esc_url( get_pagenum_link( $i ) . $pagination_fragment ); ?>" class="pagination__page">
                        <?php echo esc_html( $i ); ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endfor; ?>
    </ul>

    <?php if ( $current_page < $total_pages ) : ?>
        <span class="pagination__separator" aria-hidden="true"></span>
        <a href="<?php echo esc_url( get_pagenum_link( $current_page + 1 ) . $pagination_fragment ); ?>" class="pagination__next">
            <?php _e( 'Next', 'fabian-theme' ); ?>
        </a>
    <?php endif; ?>
</nav>
