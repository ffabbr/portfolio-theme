<?php
/**
 * Template part for displaying project cards
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$card_class = isset( $args['size'] ) && $args['size'] === 'large' ? 'project-card-large' : 'project-card-small';
?>

<a href="<?php the_permalink(); ?>" class="project-card <?php echo esc_attr( $card_class ); ?>">
    <div class="project-card-content">
        <span class="project-title"><?php the_title(); ?></span>
    </div>
</a>
