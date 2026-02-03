<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php if ( have_posts() ) : ?>
    <header class="writing__header">
            <h1 class="page-title"><?php _e( 'Writing', 'fabian-theme' ); ?></h1>
            <p class="writing__intro">
                <?php _e( 'Thoughts on design, creativity, and building things for the web.', 'fabian-theme' ); ?>
            </p>
        </header>

        <div id="writing-posts" class="category-archive__posts writing__posts">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'template-parts/content', 'post-preview' ); ?>
            <?php endwhile; ?>
        </div>

        <?php get_template_part( 'template-parts/pagination' ); ?>

<?php else : ?>
    <h1 class="page-title"><?php _e( 'Nothing Found', 'fabian-theme' ); ?></h1>
    <p class="page-content"><?php _e( 'No posts found.', 'fabian-theme' ); ?></p>
<?php endif; ?>

<?php
get_footer();
