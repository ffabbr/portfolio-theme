<?php
/**
 * The template for displaying all pages
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
    <h1 class="page-title"><?php the_title(); ?></h1>
    <div class="page-content">
        <?php the_content(); ?>
    </div>
<?php endwhile; ?>

<?php
get_footer();
