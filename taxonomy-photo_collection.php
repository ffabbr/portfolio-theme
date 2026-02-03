<?php
/**
 * The template for displaying photo collection taxonomy archives
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$term = get_queried_object();
$color = fabian_string_to_color( $term->slug );
?>

<header class="writing__header">
        <h1 class="page-title"><?php echo esc_html( $term->name ); ?></h1>
        <?php if ( $term->description ) : ?>
            <p class="writing__intro"><?php echo esc_html( $term->description ); ?></p>
        <?php endif; ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="photo-grid" style="margin-bottom: 48px;">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="photo-card">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <img src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'photo-medium' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <?php get_template_part( 'template-parts/pagination' ); ?>
    <?php else : ?>
        <p class="page-content"><?php _e( 'No photos found in this collection.', 'fabian-theme' ); ?></p>
    <?php endif; ?>

    <div style="margin-top: 48px;">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'photography' ) ); ?>" class="btn btn-primary">
            <?php _e( 'Back to Photography', 'fabian-theme' ); ?>
        </a>
    </div>

<?php
get_footer();
