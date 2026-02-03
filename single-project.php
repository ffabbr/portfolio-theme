<?php
/**
 * The template for displaying single project posts
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()):
    the_post(); ?>
    <?php
    // Get project meta
    $hero_style = get_post_meta(get_the_ID(), '_fabian_hero_style', true) ?: 'plain';
    $hero_theme = get_post_meta(get_the_ID(), '_fabian_hero_theme', true) ?: 'auto';
    $subtitle = get_post_meta(get_the_ID(), '_fabian_subtitle', true);
    $background_video = get_post_meta(get_the_ID(), '_fabian_background_video', true);

    // Get featured image for image style
    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');

    // Determine theme for header
    if ($hero_theme === 'auto') {
        $hero_theme = in_array($hero_style, array('image', 'video')) ? 'light' : 'dark';
    }
    ?>

    <article class="project">
        <!-- Hero Section -->
        <section class="project-hero project-hero--<?php echo esc_attr($hero_style); ?>" id="project-hero">
            <?php if ($hero_style === 'image' && $featured_image): ?>
                <div class="project-hero__background" id="project-hero-bg">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="">
                </div>
            <?php endif; ?>

            <?php if ($hero_style === 'video' && $background_video): ?>
                <div class="project-hero__background" id="project-hero-bg">
                    <video autoplay muted loop playsinline>
                        <source src="<?php echo esc_url($background_video); ?>" type="video/mp4">
                    </video>
                </div>
            <?php endif; ?>

            <?php
            if ($hero_style === 'annotations') {
                $raw_annotations = get_post_meta(get_the_ID(), '_fabian_annotations', true);

                // Explode by newline to get items
                $items = array_filter(array_map('trim', explode("\n", $raw_annotations)));

                if (!empty($items)):
                    // Seed random generator for consistency based on content
                    mt_srand(crc32(implode('', $items) . get_the_ID()));

                    // Shuffle items to randomize which word goes to which sector
                    shuffle($items);

                    $count = count($items);
                    $angle_step = 360 / max(1, $count);
                    $rotation_offset = mt_rand(0, 360); // Rotate the whole ring randomly
                    ?>
                    <div class="project-hero__annotations">
                        <?php
                        foreach ($items as $index => $text):
                            // Calculate angle: distribute evenly + small jitter
                            $angle_deg = $rotation_offset + ($index * $angle_step) + mt_rand(-10, 10);
                            $angle_rad = deg2rad($angle_deg);

                            // Radius (distance from center in %)
                            // Inner radius 25% (clears center title), Outer 45% (stays on screen)
                            // Using slightly different X/Y ranges to fit typical aspect ratios (ellipse)
                            $radius_x = mt_rand(30, 45);
                            $radius_y = mt_rand(25, 40);

                            $left = 50 + ($radius_x * cos($angle_rad));
                            $top = 50 + ($radius_y * sin($angle_rad));
                            ?>
                            <div class="annotation annotation--text" style="top: <?php echo $top; ?>%; left: <?php echo $left; ?>%;">
                                <span><?php echo esc_html($text); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                endif;
            }
            ?>

            <div class="project-hero__content">
                <h1 class="project-hero__title"><?php the_title(); ?></h1>
                <?php if ($subtitle): ?>
                    <p class="project-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Content Section -->
        <section class="project-content">
            <div class="project-body">
                <?php the_content(); ?>
            </div>
        </section>
    </article>
<?php endwhile; ?>

<?php
get_footer();
