<?php
/**
 * The front page template file
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get latest projects
$projects_query = new WP_Query(array(
    'post_type' => 'project',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
));

$projects = array();
if ($projects_query->have_posts()) {
    while ($projects_query->have_posts()) {
        $projects_query->the_post();
        $projects[] = array(
            'id' => get_the_ID(),
            'slug' => get_post_field('post_name', get_the_ID()),
            'title' => get_the_title(),
            'url' => get_permalink(),
        );
    }
    wp_reset_postdata();
}

// Get latest photos
$photos_query = new WP_Query(array(
    'post_type' => 'photography',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
));

$photos = array();
if ($photos_query->have_posts()) {
    while ($photos_query->have_posts()) {
        $photos_query->the_post();
        $photos[] = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'photo-large'),
            'alt' => get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) ?: get_the_title(),
        );
    }
    wp_reset_postdata();
}

// Split projects for layout logic handled directly in the loop below
?>

<!-- Hero Section -->
<section class="hero">
    <?php
    $front_page_id = get_queried_object_id();
    if ( ! $front_page_id ) {
        $front_page_id = (int) get_option( 'page_on_front' );
    }

    if ( $front_page_id && has_post_thumbnail( $front_page_id ) ) {
        echo wp_get_attachment_image(
            get_post_thumbnail_id( $front_page_id ),
            'thumbnail',
            false,
            array(
                'class' => 'hero-avatar',
                'loading' => 'lazy',
                'decoding' => 'async',
            )
        );
    }
    ?>
    <h1 class="hero-title">
        <span class="hero-name"><?php _e("Hi, I'm Fabian", 'fabian-theme'); ?></span>
        <span
            class="hero-description"><?php _e('– interested in the intersection of technology, design, and human behavior.', 'fabian-theme'); ?></span>
    </h1>
    <div class="hero-buttons">
        <?php if (!empty($projects)): ?>
            <a href="<?php echo esc_url(home_url('#projects')); ?>"
                class="btn btn-primary"><?php _e('Projects', 'fabian-theme'); ?></a>
        <?php else: ?>
            <a href="mailto:hi@rohlik.net" class="btn btn-primary"><?php _e('Contact', 'fabian-theme'); ?></a>
        <?php endif; ?>
        <a href="<?php echo esc_url(home_url('/writing/')); ?>"
            class="btn btn-text"><?php _e('Writing', 'fabian-theme'); ?></a>
    </div>
    <?php
    if (have_posts()) {
        the_post();
        $home_content = trim((string) get_the_content());

        if ($home_content !== ''): ?>
            <div style="max-width: unset;" class="hero-paragraph page-content blog-post__content">
                <?php the_content(); ?>
            </div>
        <?php endif;

        rewind_posts();
    }
    ?>
</section>

<?php if (!empty($projects)): ?>
    <!-- Projects Grid -->
    <section id="projects" class="projects">
        <div class="projects-grid">
            <?php
            $total_projects = count($projects);
            foreach ($projects as $index => $project):
                $is_full_width = false;
                // Pattern: 2 small, 1 large, 2 small, 1 large...
                // Indices: 0,1 (small), 2 (large), 3,4 (small), 5 (large)
                // But if total is 1, it should be full width.
                if ($total_projects === 1) {
                    $is_full_width = true;
                } elseif (($index + 1) % 3 === 0) {
                    $is_full_width = true;
                }

                $card_class = $is_full_width ? 'project-card-large project-span-full' : 'project-card-small';
                ?>
                <a href="<?php echo esc_url($project['url']); ?>" class="project-card <?php echo esc_attr($card_class); ?>">
                    <div class="project-card-content">
                        <span class="project-title"><?php echo esc_html($project['title']); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($photos)): ?>
    <!-- Rings Animation -->
    <div class="rings-wrapper" id="rings-wrapper">
        <div class="rings-decoration">
            <svg viewBox="0 0 1600 800" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <ellipse cx="800" cy="100" rx="780" ry="500" stroke="currentColor" stroke-width="1" fill="none" />
                <ellipse cx="800" cy="100" rx="760" ry="480" stroke="currentColor" stroke-width="1" fill="none" />
                <ellipse cx="800" cy="100" rx="740" ry="460" stroke="currentColor" stroke-width="1" fill="none" />
            </svg>
        </div>
    </div>

    <!-- Photography Section -->
    <section class="photography">
        <h2 class="section-title"><?php _e('Photography', 'fabian-theme'); ?></h2>
        <p class="section-subtitle"><?php _e('Latest', 'fabian-theme'); ?></p>
        <div class="photo-grid">
       <?php foreach ($photos as $photo): ?>
                <div class="photo-card">
                    <?php if ($photo['image_url']): ?>
                        <img src="<?php echo esc_url($photo['image_url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="photo-cta">
            <a href="<?php echo esc_url(home_url('/photography/')); ?>"
                class="btn btn-primary"><?php _e('View more Photos', 'fabian-theme'); ?></a>
        </div>
    </section>
<?php endif; ?>

<?php
get_footer();
