<?php
/**
 * Talks custom post type, sessions, meta, and admin UI.
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Talk CPT.
 */
function fabian_register_talk_cpt()
{
    $labels = array(
        'name' => _x('Talks', 'Post Type General Name', 'fabian-theme'),
        'singular_name' => _x('Talk', 'Post Type Singular Name', 'fabian-theme'),
        'menu_name' => __('Talks', 'fabian-theme'),
        'name_admin_bar' => __('Talk', 'fabian-theme'),
        'archives' => __('Talk Archives', 'fabian-theme'),
        'all_items' => __('All Talks', 'fabian-theme'),
        'add_new_item' => __('Add New Talk', 'fabian-theme'),
        'add_new' => __('Add New', 'fabian-theme'),
        'new_item' => __('New Talk', 'fabian-theme'),
        'edit_item' => __('Edit Talk', 'fabian-theme'),
        'update_item' => __('Update Talk', 'fabian-theme'),
        'view_item' => __('View Talk', 'fabian-theme'),
        'search_items' => __('Search Talks', 'fabian-theme'),
    );
    $args = array(
        'label' => __('Talk', 'fabian-theme'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 7,
        'menu_icon' => 'dashicons-microphone',
        'has_archive' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'talks'),
    );
    register_post_type('talk', $args);
}
add_action('init', 'fabian_register_talk_cpt', 0);

/**
 * Register Talk Session CPT (admin-only, embedded inside talks).
 */
function fabian_register_talk_session_cpt()
{
    $labels = array(
        'name' => _x('Sessions', 'Post Type General Name', 'fabian-theme'),
        'singular_name' => _x('Session', 'Post Type Singular Name', 'fabian-theme'),
        'menu_name' => __('Sessions', 'fabian-theme'),
        'all_items' => __('All Sessions', 'fabian-theme'),
        'add_new_item' => __('Add New Session', 'fabian-theme'),
        'add_new' => __('Add New Session', 'fabian-theme'),
        'new_item' => __('New Session', 'fabian-theme'),
        'edit_item' => __('Edit Session', 'fabian-theme'),
    );
    $args = array(
        'label' => __('Session', 'fabian-theme'),
        'labels' => $labels,
        'supports' => array('title', 'editor'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=talk',
        'menu_icon' => 'dashicons-calendar-alt',
        'has_archive' => false,
        'show_in_rest' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
    );
    register_post_type('talk_session', $args);
}
add_action('init', 'fabian_register_talk_session_cpt', 0);

/**
 * Register talk & session meta fields.
 */
function fabian_register_talk_meta()
{
    $auth = function () {
        return current_user_can('edit_posts');
    };

    register_post_meta('talk', '_fabian_talk_date', array(
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth,
    ));
    register_post_meta('talk', '_fabian_talk_location', array(
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth,
    ));
    register_post_meta('talk', '_fabian_talk_format', array(
        'show_in_rest' => true, 'single' => true, 'type' => 'string',
        'default' => 'oneoff',
        'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth,
    ));

    register_post_meta('talk', '_fabian_talk_layout', array(
        'single' => true, 'type' => 'string',
        'default' => 'sidebar',
        'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth,
    ));

    register_post_meta('talk', '_fabian_talk_resources', array(
        'single' => true, 'type' => 'string',
        'sanitize_callback' => 'fabian_sanitize_resources_json', 'auth_callback' => $auth,
    ));

    register_post_meta('talk_session', '_fabian_session_date', array(
        'single' => true, 'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth,
    ));
    register_post_meta('talk_session', '_fabian_session_parent', array(
        'single' => true, 'type' => 'integer',
        'sanitize_callback' => 'absint', 'auth_callback' => $auth,
    ));
    register_post_meta('talk_session', '_fabian_session_resources', array(
        'single' => true, 'type' => 'string',
        'sanitize_callback' => 'fabian_sanitize_resources_json', 'auth_callback' => $auth,
    ));
    register_post_meta('talk_session', '_fabian_session_open_default', array(
        'single' => true, 'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean', 'auth_callback' => $auth,
    ));
}
add_action('init', 'fabian_register_talk_meta');

/**
 * Sanitize resources JSON.
 */
function fabian_sanitize_resources_json($value)
{
    $decoded = json_decode(wp_unslash($value), true);
    if (!is_array($decoded)) {
        return '[]';
    }
    $clean = array();
    foreach ($decoded as $item) {
        if (!is_array($item)) {
            continue;
        }
        $url = isset($item['url']) ? esc_url_raw($item['url']) : '';
        if (empty($url)) {
            continue;
        }
        $clean[] = array(
            'type' => 'link',
            'label' => isset($item['label']) ? sanitize_text_field($item['label']) : '',
            'url' => $url,
        );
    }
    return wp_json_encode($clean);
}

/**
 * Meta box: Talk details.
 */
function fabian_add_talk_meta_boxes()
{
    add_meta_box(
        'fabian_talk_details',
        __('Talk Details', 'fabian-theme'),
        'fabian_render_talk_details_box',
        'talk',
        'side',
        'high'
    );

    add_meta_box(
        'fabian_talk_sessions',
        __('Sessions', 'fabian-theme'),
        'fabian_render_talk_sessions_box',
        'talk',
        'normal',
        'default'
    );

    add_meta_box(
        'fabian_talk_resources',
        __('Resources (one-off)', 'fabian-theme'),
        'fabian_render_talk_resources_box',
        'talk',
        'normal',
        'default'
    );

    add_meta_box(
        'fabian_session_details',
        __('Session Details', 'fabian-theme'),
        'fabian_render_session_details_box',
        'talk_session',
        'side',
        'high'
    );

    add_meta_box(
        'fabian_session_resources',
        __('Resources', 'fabian-theme'),
        'fabian_render_session_resources_box',
        'talk_session',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'fabian_add_talk_meta_boxes');

/**
 * Render Talk Details meta box.
 */
function fabian_render_talk_details_box($post)
{
    wp_nonce_field('fabian_talk_save', 'fabian_talk_nonce');
    $date = get_post_meta($post->ID, '_fabian_talk_date', true);
    $location = get_post_meta($post->ID, '_fabian_talk_location', true);
    $format = get_post_meta($post->ID, '_fabian_talk_format', true) ?: 'oneoff';
    $layout = get_post_meta($post->ID, '_fabian_talk_layout', true) ?: 'sidebar';
    ?>
    <p>
        <label for="fabian_talk_date"><?php _e('Date', 'fabian-theme'); ?></label>
        <input type="date" id="fabian_talk_date" name="fabian_talk_date"
               value="<?php echo esc_attr($date); ?>" class="widefat">
    </p>
    <p>
        <label for="fabian_talk_location"><?php _e('Location', 'fabian-theme'); ?></label>
        <input type="text" id="fabian_talk_location" name="fabian_talk_location"
               value="<?php echo esc_attr($location); ?>" class="widefat"
               placeholder="<?php esc_attr_e('e.g. Zurich, Online, ETH HG F1', 'fabian-theme'); ?>">
    </p>
    <fieldset>
        <legend><?php _e('Format', 'fabian-theme'); ?></legend>
        <p>
            <label>
                <input type="radio" name="fabian_talk_format" value="oneoff" <?php checked($format, 'oneoff'); ?>>
                <?php _e('One-off', 'fabian-theme'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="fabian_talk_format" value="ongoing" <?php checked($format, 'ongoing'); ?>>
                <?php _e('Ongoing (with sessions)', 'fabian-theme'); ?>
            </label>
        </p>
    </fieldset>
    <fieldset style="margin-top:12px;">
        <legend><?php _e('Layout', 'fabian-theme'); ?></legend>
        <p>
            <label>
                <input type="radio" name="fabian_talk_layout" value="sidebar" <?php checked($layout, 'sidebar'); ?>>
                <?php _e('Sidebar (sticky left column)', 'fabian-theme'); ?>
            </label>
            <br>
            <label>
                <input type="radio" name="fabian_talk_layout" value="centered" <?php checked($layout, 'centered'); ?>>
                <?php _e('Centered (title on top)', 'fabian-theme'); ?>
            </label>
        </p>
    </fieldset>
    <?php
}

/**
 * Render Talk Sessions meta box (list + add).
 */
function fabian_render_talk_sessions_box($post)
{
    $sessions = get_posts(array(
        'post_type' => 'talk_session',
        'posts_per_page' => -1,
        'meta_key' => '_fabian_session_parent',
        'meta_value' => $post->ID,
        'orderby' => 'meta_value',
        'meta_query' => array(
            array('key' => '_fabian_session_date', 'compare' => 'EXISTS'),
        ),
        'post_status' => array('publish', 'draft', 'pending', 'private'),
    ));

    // Fallback: also fetch without date if any
    if (empty($sessions)) {
        $sessions = get_posts(array(
            'post_type' => 'talk_session',
            'posts_per_page' => -1,
            'meta_key' => '_fabian_session_parent',
            'meta_value' => $post->ID,
            'post_status' => array('publish', 'draft', 'pending', 'private'),
        ));
    }

    $add_url = admin_url('post-new.php?post_type=talk_session&parent_talk=' . $post->ID);
    ?>
    <p class="description">
        <?php _e('Sessions are listed on the talk page when the format is set to "Ongoing". Add as many sessions as you need — each can have its own resources.', 'fabian-theme'); ?>
    </p>
    <?php if (!empty($sessions)): ?>
        <table class="widefat striped" style="margin-top:12px;">
            <thead>
                <tr>
                    <th><?php _e('Title', 'fabian-theme'); ?></th>
                    <th><?php _e('Date', 'fabian-theme'); ?></th>
                    <th><?php _e('Status', 'fabian-theme'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session):
                    $sdate = get_post_meta($session->ID, '_fabian_session_date', true);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html(get_the_title($session)); ?></strong></td>
                        <td><?php echo $sdate ? esc_html(date_i18n('M j, Y', strtotime($sdate))) : '—'; ?></td>
                        <td><?php echo esc_html(ucfirst($session->post_status)); ?></td>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($session->ID)); ?>">
                                <?php _e('Edit', 'fabian-theme'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><em><?php _e('No sessions yet.', 'fabian-theme'); ?></em></p>
    <?php endif; ?>
    <p style="margin-top:12px;">
        <a href="<?php echo esc_url($add_url); ?>" class="button button-primary">
            <?php _e('Add Session', 'fabian-theme'); ?>
        </a>
    </p>
    <?php
}

/**
 * Render Session Details meta box.
 */
function fabian_render_session_details_box($post)
{
    wp_nonce_field('fabian_session_save', 'fabian_session_nonce');
    $parent_id = get_post_meta($post->ID, '_fabian_session_parent', true);
    if (!$parent_id && isset($_GET['parent_talk'])) {
        $parent_id = absint($_GET['parent_talk']);
    }
    $date = get_post_meta($post->ID, '_fabian_session_date', true);
    $open_default = (bool) get_post_meta($post->ID, '_fabian_session_open_default', true);

    $talks = get_posts(array(
        'post_type' => 'talk',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => array('publish', 'draft', 'pending', 'private'),
    ));
    ?>
    <p>
        <label for="fabian_session_parent">
            <?php if ($parent_id && get_post($parent_id)): ?>
                <a href="<?php echo esc_url(get_edit_post_link($parent_id)); ?>">
                    <?php _e('Parent Talk', 'fabian-theme'); ?>
                </a>
            <?php else: ?>
                <?php _e('Parent Talk', 'fabian-theme'); ?>
            <?php endif; ?>
        </label>
        <select id="fabian_session_parent" name="fabian_session_parent" class="widefat">
            <option value="">&mdash; <?php _e('Select', 'fabian-theme'); ?> &mdash;</option>
            <?php foreach ($talks as $talk): ?>
                <option value="<?php echo esc_attr($talk->ID); ?>" <?php selected((int)$parent_id, $talk->ID); ?>>
                    <?php echo esc_html(get_the_title($talk)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="fabian_session_date"><?php _e('Session Date', 'fabian-theme'); ?></label>
        <input type="date" id="fabian_session_date" name="fabian_session_date"
               value="<?php echo esc_attr($date); ?>" class="widefat">
    </p>
    <p>
        <label for="fabian_session_open_default">
            <input type="checkbox" id="fabian_session_open_default" name="fabian_session_open_default"
                   value="1" <?php checked($open_default, true); ?>>
            <?php _e('Open by default on the public page', 'fabian-theme'); ?>
        </label>
    </p>
    <?php
}

/**
 * Render Session Resources repeater.
 */
function fabian_render_session_resources_box($post)
{
    $raw = get_post_meta($post->ID, '_fabian_session_resources', true);
    $resources = $raw ? json_decode($raw, true) : array();
    if (!is_array($resources)) {
        $resources = array();
    }
    ?>
    <p class="description">
        <?php _e('Add links relevant to this session.', 'fabian-theme'); ?>
    </p>
    <div id="fabian-resources" class="fabian-resources"
         data-initial="<?php echo esc_attr(wp_json_encode($resources)); ?>"></div>
    <input type="hidden" name="fabian_session_resources" id="fabian_session_resources_input" value="">
    <p style="margin-top:10px;">
        <button type="button" class="button" data-fabian-resource-add="link"><?php _e('+ Add Link', 'fabian-theme'); ?></button>
    </p>
    <?php
}

/**
 * Render Talk Resources repeater (one-off).
 */
function fabian_render_talk_resources_box($post)
{
    $raw = get_post_meta($post->ID, '_fabian_talk_resources', true);
    $resources = $raw ? json_decode($raw, true) : array();
    if (!is_array($resources)) {
        $resources = array();
    }
    ?>
    <p class="description">
        <?php _e('Add links shown on the public talk page when format is "One-off" — e.g. slides, recording, write-up.', 'fabian-theme'); ?>
    </p>
    <div id="fabian-resources" class="fabian-resources"
         data-initial="<?php echo esc_attr(wp_json_encode($resources)); ?>"></div>
    <input type="hidden" name="fabian_talk_resources" id="fabian_session_resources_input" value="">
    <p style="margin-top:10px;">
        <button type="button" class="button" data-fabian-resource-add="link"><?php _e('+ Add Link', 'fabian-theme'); ?></button>
    </p>
    <?php
}

/**
 * Save Talk meta.
 */
function fabian_save_talk_meta($post_id, $post)
{
    if ($post->post_type !== 'talk') {
        return;
    }
    if (!isset($_POST['fabian_talk_nonce']) || !wp_verify_nonce($_POST['fabian_talk_nonce'], 'fabian_talk_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_fabian_talk_date', sanitize_text_field($_POST['fabian_talk_date'] ?? ''));
    update_post_meta($post_id, '_fabian_talk_location', sanitize_text_field($_POST['fabian_talk_location'] ?? ''));
    $format = isset($_POST['fabian_talk_format']) && $_POST['fabian_talk_format'] === 'ongoing' ? 'ongoing' : 'oneoff';
    update_post_meta($post_id, '_fabian_talk_format', $format);
    $layout = isset($_POST['fabian_talk_layout']) && $_POST['fabian_talk_layout'] === 'centered' ? 'centered' : 'sidebar';
    update_post_meta($post_id, '_fabian_talk_layout', $layout);

    if (isset($_POST['fabian_talk_resources'])) {
        $json = fabian_sanitize_resources_json($_POST['fabian_talk_resources']);
        update_post_meta($post_id, '_fabian_talk_resources', wp_slash($json));
    }
}
add_action('save_post', 'fabian_save_talk_meta', 10, 2);

/**
 * Save Session meta.
 */
function fabian_save_session_meta($post_id, $post)
{
    if ($post->post_type !== 'talk_session') {
        return;
    }
    if (!isset($_POST['fabian_session_nonce']) || !wp_verify_nonce($_POST['fabian_session_nonce'], 'fabian_session_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $parent = isset($_POST['fabian_session_parent']) ? absint($_POST['fabian_session_parent']) : 0;
    update_post_meta($post_id, '_fabian_session_parent', $parent);
    update_post_meta($post_id, '_fabian_session_date', sanitize_text_field($_POST['fabian_session_date'] ?? ''));
    update_post_meta($post_id, '_fabian_session_open_default', !empty($_POST['fabian_session_open_default']) ? 1 : 0);

    if (isset($_POST['fabian_session_resources'])) {
        $json = fabian_sanitize_resources_json($_POST['fabian_session_resources']);
        update_post_meta($post_id, '_fabian_session_resources', wp_slash($json));
    }
}
add_action('save_post', 'fabian_save_session_meta', 10, 2);

/**
 * Enqueue session admin repeater JS.
 */
function fabian_talks_admin_assets($hook)
{
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    if (in_array($screen->post_type, array('talk_session', 'talk'), true) && in_array($hook, array('post.php', 'post-new.php'), true)) {
        wp_enqueue_script(
            'fabian-talks-admin',
            get_template_directory_uri() . '/js/admin/talks-admin.js',
            array(),
            '1.0.0',
            true
        );

        $inline_css = '
            #fabian_session_details .inside,
            #fabian_session_details .inside p,
            #fabian_session_details .inside select,
            #fabian_session_details .inside input {
                max-width: 100%;
                box-sizing: border-box;
            }
            #fabian_session_details .inside select {
                width: 100%;
            }
        ';
        wp_register_style('fabian-talks-admin', false);
        wp_enqueue_style('fabian-talks-admin');
        wp_add_inline_style('fabian-talks-admin', $inline_css);
    }
}
add_action('admin_enqueue_scripts', 'fabian_talks_admin_assets');

/**
 * Flush rewrite rules once after the Talks CPT is introduced.
 */
function fabian_talks_maybe_flush_rewrites()
{
    if (get_option('fabian_talks_rewrites_flushed') !== '1') {
        flush_rewrite_rules(false);
        update_option('fabian_talks_rewrites_flushed', '1');
    }
}
add_action('init', 'fabian_talks_maybe_flush_rewrites', 20);

/**
 * Helper: get sessions for a given talk.
 */
function fabian_get_talk_sessions($talk_id)
{
    $sessions = get_posts(array(
        'post_type' => 'talk_session',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => '_fabian_session_parent',
        'meta_value' => $talk_id,
        'orderby' => 'meta_value',
        'order' => 'ASC',
    ));
    // Sort by session date if present
    usort($sessions, function ($a, $b) {
        $da = get_post_meta($a->ID, '_fabian_session_date', true);
        $db = get_post_meta($b->ID, '_fabian_session_date', true);
        return strcmp($da, $db);
    });
    return $sessions;
}
