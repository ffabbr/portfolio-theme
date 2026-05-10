<?php
/**
 * Single Talk template.
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()):
    the_post();

    $date = get_post_meta(get_the_ID(), '_fabian_talk_date', true);
    $location = get_post_meta(get_the_ID(), '_fabian_talk_location', true);
    $format = get_post_meta(get_the_ID(), '_fabian_talk_format', true) ?: 'oneoff';
    $sessions = ($format === 'ongoing') ? fabian_get_talk_sessions(get_the_ID()) : array();
    $layout = get_post_meta(get_the_ID(), '_fabian_talk_layout', true) ?: 'sidebar';

    $talk_resources = array();
    if ($format === 'oneoff') {
        $raw_talk_resources = get_post_meta(get_the_ID(), '_fabian_talk_resources', true);
        $decoded = $raw_talk_resources ? json_decode($raw_talk_resources, true) : array();
        if (is_array($decoded)) {
            $talk_resources = array_filter($decoded, function ($r) {
                return isset($r['url']) && !empty($r['url']);
            });
        }
    }
    ?>

    <article class="talk-post talk-post--<?php echo esc_attr($layout); ?> talk-post--<?php echo esc_attr($format); ?>">
        <header class="talk-post__header">
            <a href="<?php echo esc_url(get_post_type_archive_link('talk')); ?>" class="talk-post__all">
                <span class="arrow-left">&larr;</span> <?php _e('All talks', 'fabian-theme'); ?>
            </a>

            <h1 class="talk-post__title"><?php the_title(); ?></h1>

            <ul class="talk-post__meta">
                <?php if ($date): ?>
                    <li class="talk-post__meta-item"><?php echo esc_html(date_i18n('F j, Y', strtotime($date))); ?></li>
                <?php endif; ?>
                <?php if ($location): ?>
                    <li class="talk-post__meta-item"><?php echo esc_html($location); ?></li>
                <?php endif; ?>
                <?php if ($format === 'ongoing'): ?>
                    <li class="talk-post__meta-item">
                        <?php printf(_n('%d session', '%d sessions', count($sessions), 'fabian-theme'), count($sessions)); ?>
                    </li>
                <?php endif; ?>
            </ul>

            <?php $description_in_content = ($format === 'oneoff' && $layout === 'sidebar'); ?>
            <?php if (!$description_in_content && get_the_content()): ?>
                <div class="talk-post__description">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="talk-post__content">
            <?php if ($format === 'oneoff'): ?>
                <?php if ($description_in_content && get_the_content()): ?>
                    <div class="talk-post__description talk-post__description--in-content">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

                <?php if (has_post_thumbnail()): ?>
                    <figure class="talk-post__media">
                        <?php the_post_thumbnail('large', array('class' => 'talk-post__media-img')); ?>
                    </figure>
                <?php endif; ?>

                <?php if (!empty($talk_resources)): ?>
                    <ol class="sessions-accordion">
                        <?php foreach (array_values($talk_resources) as $i => $res):
                            $label = isset($res['label']) ? $res['label'] : '';
                            $host = parse_url($res['url'], PHP_URL_HOST);
                            ?>
                            <li class="session-item">
                                <a class="session-accordion__summary session-accordion__summary--link" href="<?php echo esc_url($res['url']); ?>" target="_blank" rel="noopener">
                                    <span class="session-accordion__index">
                                        <?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?>
                                    </span>
                                    <span class="session-accordion__title">
                                        <?php echo esc_html($label ?: $res['url']); ?>
                                    </span>
                                    <?php if ($host): ?>
                                        <span class="session-accordion__date">
                                            <?php echo esc_html($host); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="session-accordion__chevron" aria-hidden="true">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M4 10L10 4M10 4H5M10 4V9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>

                <?php if (!has_post_thumbnail() && empty($talk_resources)): ?>
                    <p class="talk-post__empty"><?php _e('Details for this talk will be posted soon.', 'fabian-theme'); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($format === 'ongoing'): ?>
                <?php if (!empty($sessions)): ?>
                    <ol class="sessions-accordion">
                        <?php foreach ($sessions as $i => $session):
                            $s_date = get_post_meta($session->ID, '_fabian_session_date', true);
                            $raw_resources = get_post_meta($session->ID, '_fabian_session_resources', true);
                            $resources = $raw_resources ? json_decode($raw_resources, true) : array();
                            if (!is_array($resources)) {
                                $resources = array();
                            }
                            $open_default = (bool) get_post_meta($session->ID, '_fabian_session_open_default', true);
                            ?>
                            <li class="session-item" id="session-<?php echo esc_attr($session->ID); ?>">
                                <details class="session-accordion"<?php echo $open_default ? ' open' : ''; ?>>
                                    <summary class="session-accordion__summary">
                                        <span class="session-accordion__index">
                                            <?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?>
                                        </span>
                                        <span class="session-accordion__title">
                                            <?php echo esc_html(get_the_title($session)); ?>
                                        </span>
                                        <?php if ($s_date): ?>
                                            <time class="session-accordion__date" datetime="<?php echo esc_attr($s_date); ?>">
                                                <?php echo esc_html(date_i18n('M j, Y', strtotime($s_date))); ?>
                                            </time>
                                        <?php endif; ?>
                                        <span class="session-accordion__chevron" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                <path d="M3 5.5L7 9.5L11 5.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </summary>

                                    <div class="session-accordion__body">
                                        <?php if ($session->post_content): ?>
                                            <div class="session-accordion__content">
                                                <?php echo apply_filters('the_content', $session->post_content); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        $link_resources = array_filter($resources, function ($r) {
                                            return isset($r['url']) && !empty($r['url']);
                                        });
                                        ?>
                                        <?php if (!empty($link_resources)): ?>
                                            <div class="session-accordion__resources">
                                                <ul class="resources-list">
                                                    <?php foreach ($link_resources as $res):
                                                        $label = isset($res['label']) ? $res['label'] : '';
                                                        ?>
                                                        <li class="resource">
                                                            <a href="<?php echo esc_url($res['url']); ?>" class="resource__link" target="_blank" rel="noopener">
                                                                <span class="resource__label">
                                                                    <?php echo esc_html($label ?: $res['url']); ?>
                                                                </span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <p class="talk-post__empty"><?php _e('No sessions scheduled yet. Check back soon.', 'fabian-theme'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </article>

<?php endwhile;

get_footer();
