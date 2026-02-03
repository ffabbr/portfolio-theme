<?php
/**
 * The template for displaying project archives
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<header class="writing__header">
        <h1 class="page-title"><?php _e( 'Projects', 'fabian-theme' ); ?></h1>
        <p class="writing__intro">
            <?php _e( 'A selection of my work.', 'fabian-theme' ); ?>
        </p>
    </header>

    <?php if ( have_posts() ) : ?>
        <section class="projects">
            <div class="projects-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <a href="<?php the_permalink(); ?>" class="project-card project-card-small">
                        <div class="project-card-content">
                            <span class="project-title"><?php the_title(); ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </section>

        <?php get_template_part( 'template-parts/pagination' ); ?>
    <?php else : ?>
        <p class="page-content"><?php _e( 'No projects found.', 'fabian-theme' ); ?></p>
    <?php endif; ?>

<?php
get_footer();
