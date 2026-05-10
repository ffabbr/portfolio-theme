<?php
/**
 * Talks archive (default /talks/ URL).
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="writing__header">
    <h1 class="page-title"><?php _e('Talks', 'fabian-theme'); ?></h1>
    <p class="writing__intro">
        <?php _e('A living archive of talks, workshops, and ongoing series.', 'fabian-theme'); ?>
    </p>
</header>

<?php if (have_posts()):
    $talk_count = (int) $wp_query->found_posts;
    $grid_mod = $talk_count === 1 ? 'talks-grid--1' : ($talk_count === 2 ? 'talks-grid--2' : 'talks-grid--3plus');
    ?>
    <section class="talks">
        <div class="talks-grid <?php echo esc_attr($grid_mod); ?>">
            <?php while (have_posts()):
                the_post();
                $date = get_post_meta(get_the_ID(), '_fabian_talk_date', true);
                $location = get_post_meta(get_the_ID(), '_fabian_talk_location', true);
                $format = get_post_meta(get_the_ID(), '_fabian_talk_format', true) ?: 'oneoff';
                $sessions = ($format === 'ongoing') ? fabian_get_talk_sessions(get_the_ID()) : array();
                ?>
                <a class="talk-card talk-card--<?php echo esc_attr($format); ?>" href="<?php the_permalink(); ?>">
                    <div class="talk-card__content">
                        <span class="talk-card__title"><?php the_title(); ?></span>
                        <div class="talk-card__meta">
                            <span class="talk-card__format">
                                <?php echo $format === 'ongoing' ? __('Ongoing', 'fabian-theme') : __('One-off', 'fabian-theme'); ?>
                            </span>
                            <?php if ($date): ?>
                                <span class="talk-card__date">
                                    <?php echo esc_html(date_i18n('M j, Y', strtotime($date))); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($location): ?>
                                <span class="talk-card__location"><?php echo esc_html($location); ?></span>
                            <?php endif; ?>
                            <?php if ($format === 'ongoing'): ?>
                                <span class="talk-card__sessions">
                                    <?php
                                    printf(
                                        _n('%d session', '%d sessions', count($sessions), 'fabian-theme'),
                                        count($sessions)
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
<?php else: ?>
    <p class="page-content"><?php _e('No talks yet.', 'fabian-theme'); ?></p>
<?php endif; ?>

<?php
get_footer();
