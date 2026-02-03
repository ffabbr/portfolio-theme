<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<h1 class="page-title"><?php _e( 'Page not found', 'fabian-theme' ); ?></h1>
<p class="page-content">
    <?php _e( "The page you are looking for doesn't exist, or it has been moved. Please try searching using the form below or visit the homepage.", 'fabian-theme' ); ?>
</p>
<div style="margin-top: 24px;">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
        <?php _e( 'Go to Homepage', 'fabian-theme' ); ?>
    </a>
</div>

<?php
get_footer();
