<?php
/**
 * The template for displaying archive pages
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
?>

<header class="writing__header">
        <?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
        <?php the_archive_description( '<p class="writing__intro">', '</p>' ); ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div id="writing-posts" class="category-archive__posts writing__posts">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'post-preview' ); ?>
            <?php endwhile; ?>
        </div>

        <?php get_template_part( 'template-parts/pagination' ); ?>
    <?php else : ?>
        <p class="page-content"><?php _e( 'No posts found.', 'fabian-theme' ); ?></p>
    <?php endif; ?>

<?php
get_footer();
