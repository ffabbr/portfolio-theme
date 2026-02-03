<?php
/**
 * Template Name: Photography
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get photography collections
$collections = fabian_get_photography_collections();

// Stack positions for preview photos
$stack_positions = array(
    array('x' => -15, 'y' => -10, 'rotate' => -8, 'scale' => 0.85),
    array('x' => 0, 'y' => -5, 'rotate' => 4, 'scale' => 0.9),
    array('x' => 15, 'y' => 0, 'rotate' => -3, 'scale' => 0.95),
    array('x' => -10, 'y' => 5, 'rotate' => 6, 'scale' => 0.88),
);
?>

<div class="folders-view" id="folders-view">
        <header class="folders-header">
            <?php while (have_posts()):
                the_post(); ?>
                <h1 class="folders-title"><?php the_title(); ?></h1>
                <?php if (has_excerpt()): ?>
                    <p class="folders-subtitle"><?php echo get_the_excerpt(); ?></p>
                <?php else: ?>
                    <p class="folders-subtitle"><?php _e('Select a collection to explore', 'fabian-theme'); ?></p>
                <?php endif; ?>
            <?php endwhile; ?>
        </header>

        <div class="folders-grid">
            <?php if (!empty($collections)): ?>
                <?php foreach ($collections as $index => $collection): ?>
                    <button class="folder"
                        style="--folder-color: <?php echo esc_attr($collection['color']); ?>; --index: <?php echo esc_attr($index); ?>;"
                        data-collection-id="<?php echo esc_attr($collection['id']); ?>"
                        data-collection-name="<?php echo esc_attr($collection['name']); ?>"
                        data-collection-color="<?php echo esc_attr($collection['color']); ?>"
                        data-photos='<?php echo esc_attr(json_encode($collection['photos'])); ?>'>
                        <div class="folder__front">
                            <div class="folder__tab"></div>
                            <div class="folder__body">
                                <div class="folder__preview"
                                    style="background-color: <?php echo esc_attr($collection['color']); ?>22;">
                                    <?php
                                    $preview_photos = array_slice($collection['photos'], 0, 4);
                                    foreach ($preview_photos as $i => $photo):
                                        $pos = $stack_positions[$i % count($stack_positions)];
                                        ?>
                                        <img class="folder__preview-photo"
                                            style="--stack-x: <?php echo esc_attr($pos['x']); ?>%; --stack-y: <?php echo esc_attr($pos['y']); ?>%; --stack-rotate: <?php echo esc_attr($pos['rotate']); ?>deg; --stack-scale: <?php echo esc_attr($pos['scale']); ?>; --photo-index: <?php echo esc_attr($i); ?>;"
                                            src="<?php echo esc_url($photo['thumbnail']); ?>" alt="" draggable="false">
                                    <?php endforeach; ?>
                                </div>
                                <div class="folder__info">
                                    <span class="folder__name"><?php echo esc_html($collection['name']); ?></span>
                                    <span class="folder__count"><?php echo esc_html($collection['count']); ?>
                                        <?php _e('photos', 'fabian-theme'); ?></span>
                                </div>
                            </div>
                        </div>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="folders-empty"><?php _e('No photography collections yet.', 'fabian-theme'); ?></p>
            <?php endif; ?>
        </div>
    </div>

<!-- GSAP Scroll Container - dynamically inserted by JS -->
<div id="gsap-scroll-container"></div>

<?php
get_footer();
