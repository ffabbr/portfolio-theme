<?php
/**
 * Template part for displaying photo cards
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$image_url = get_the_post_thumbnail_url( get_the_ID(), 'photo-medium' );
$alt = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title();
?>

<div class="photo-card">
    <?php if ( $image_url ) : ?>
        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
    <?php endif; ?>
</div>
