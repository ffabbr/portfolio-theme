<?php
/**
 * Fabian Theme functions and definitions
 *
 * @package Fabian_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function fabian_theme_setup()
{
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add custom image sizes
    add_image_size('photo-thumbnail', 400, 600, true);
    add_image_size('photo-medium', 800, 1200, true);
    add_image_size('photo-large', 1200, 1800, true);

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'fabian-theme'),
    ));

    // Switch default core markup to output valid HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'fabian_theme_setup');

/**
 * Custom nav walker to output simple anchor links with theme classes.
 */
class Fabian_Nav_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $is_active = in_array('current-menu-item', $classes, true)
            || in_array('current-menu-ancestor', $classes, true)
            || in_array('current-menu-parent', $classes, true);

        $link_classes = array('nav-link');
        if ($is_active) {
            $link_classes[] = 'active';
        }

        $atts = array();
        $atts['href'] = !empty($item->url) ? $item->url : '';
        $atts['class'] = implode(' ', $link_classes);
        if (!empty($item->target)) {
            $atts['target'] = $item->target;
        }
        if (!empty($item->xfn)) {
            $atts['rel'] = $item->xfn;
        }

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);
        $output .= sprintf('<a%s>%s</a>', $attributes, esc_html($title));
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "\n";
    }
}

/**
 * Enqueue scripts and styles - Optimized with bundled CSS and conditional loading
 */
function fabian_theme_scripts()
{
    $theme_uri = get_template_directory_uri();
    $theme_path = get_template_directory();

    // Choose between minified and original files
    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

    // Helper function to get file version
    $get_version = function ($rel_path) use ($theme_path) {
        $file = trailingslashit($theme_path) . $rel_path;
        return file_exists($file) ? filemtime($file) : '1.0.0';
    };

    // ==========================================================================
    // CORE BUNDLE - Loaded on ALL pages
    // Contains: base, layout, header, footer, buttons, pages, utilities, pagination
    // ==========================================================================
    $core_css = "css/core{$suffix}.css";
    wp_enqueue_style(
        'fabian-theme-core',
        trailingslashit($theme_uri) . $core_css,
        array(),
        $get_version($core_css)
    );

    // ==========================================================================
    // CONDITIONAL CSS BUNDLES - Load only what's needed per page
    // ==========================================================================

    // HOME BUNDLE - Front page only
    // Contains: hero, projects, photography-home
    if (is_front_page()) {
        $home_css = "css/home{$suffix}.css";
        wp_enqueue_style(
            'fabian-theme-home',
            trailingslashit($theme_uri) . $home_css,
            array('fabian-theme-core'),
            $get_version($home_css)
        );
    }

    // BLOG BUNDLE - Blog posts and archives
    // Contains: blog, category-archive, writing
    if (
        is_singular('post') ||
        is_category() ||
        is_tag() ||
        (is_archive() && !is_post_type_archive('project') && !is_post_type_archive('photography')) ||
        is_page_template('page-writing.php') ||
        is_home()
    ) {
        $blog_css = "css/blog{$suffix}.css";
        wp_enqueue_style(
            'fabian-theme-blog',
            trailingslashit($theme_uri) . $blog_css,
            array('fabian-theme-core'),
            $get_version($blog_css)
        );
    }

    // PROJECT BUNDLE - Single project pages and project archive
    // Contains: project
    if (is_singular('project') || is_post_type_archive('project')) {
        $project_css = "css/project{$suffix}.css";
        wp_enqueue_style(
            'fabian-theme-project',
            trailingslashit($theme_uri) . $project_css,
            array('fabian-theme-core'),
            $get_version($project_css)
        );
    }

    // PHOTOGRAPHY BUNDLE - Photography template only
    // Contains: photography-immersive
    if (is_page_template('template-photography.php') || is_tax('photo_collection')) {
        $photography_css = "css/photography{$suffix}.css";
        wp_enqueue_style(
            'fabian-theme-photography',
            trailingslashit($theme_uri) . $photography_css,
            array('fabian-theme-core'),
            $get_version($photography_css)
        );
    }

    // ==========================================================================
    // JAVASCRIPT
    // ==========================================================================

    // Lenis smooth scroll (local) - loaded on all pages
    wp_enqueue_script('lenis', trailingslashit($theme_uri) . 'js/vendor/lenis.min.js', array(), '1.1.13', true);

    // Check if we need GSAP (only on Photography template)
    $needs_gsap = is_page_template('template-photography.php');

    // Main script dependencies
    $main_deps = array('lenis');

    if ($needs_gsap) {
        // GSAP for animations (local) - only on pages that need it
        wp_enqueue_script('gsap', trailingslashit($theme_uri) . 'js/vendor/gsap.min.js', array(), '3.12.5', true);
        wp_enqueue_script('gsap-scrolltrigger', trailingslashit($theme_uri) . 'js/vendor/ScrollTrigger.min.js', array('gsap'), '3.12.5', true);
        $main_deps = array('lenis', 'gsap', 'gsap-scrolltrigger');
    }

    // Main theme script
    $main_js = "js/main{$suffix}.js";
    wp_enqueue_script('fabian-theme-main', trailingslashit($theme_uri) . $main_js, $main_deps, $get_version($main_js), true);
}
add_action('wp_enqueue_scripts', 'fabian_theme_scripts');

/**
 * Remove defer from theme scripts to ensure proper load order
 */
function fabian_remove_script_defer($tag, $handle, $src)
{
    $no_defer_scripts = array('lenis', 'gsap', 'gsap-scrolltrigger', 'fabian-theme-main');

    if (in_array($handle, $no_defer_scripts, true)) {
        $tag = str_replace(array(' defer', ' async', " defer='defer'", ' defer="defer"'), '', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'fabian_remove_script_defer', 10, 3);

/**
 * Register Custom Post Type: Project
 */
function fabian_register_project_cpt()
{
    $labels = array(
        'name' => _x('Projects', 'Post Type General Name', 'fabian-theme'),
        'singular_name' => _x('Project', 'Post Type Singular Name', 'fabian-theme'),
        'menu_name' => __('Projects', 'fabian-theme'),
        'name_admin_bar' => __('Project', 'fabian-theme'),
        'archives' => __('Project Archives', 'fabian-theme'),
        'attributes' => __('Project Attributes', 'fabian-theme'),
        'all_items' => __('All Projects', 'fabian-theme'),
        'add_new_item' => __('Add New Project', 'fabian-theme'),
        'add_new' => __('Add New', 'fabian-theme'),
        'new_item' => __('New Project', 'fabian-theme'),
        'edit_item' => __('Edit Project', 'fabian-theme'),
        'update_item' => __('Update Project', 'fabian-theme'),
        'view_item' => __('View Project', 'fabian-theme'),
        'view_items' => __('View Projects', 'fabian-theme'),
        'search_items' => __('Search Project', 'fabian-theme'),
    );
    $args = array(
        'label' => __('Project', 'fabian-theme'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-portfolio',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'projects'),
    );
    register_post_type('project', $args);
}
add_action('init', 'fabian_register_project_cpt', 0);

/**
 * Register Custom Post Type: Photography
 */
function fabian_register_photography_cpt()
{
    $labels = array(
        'name' => _x('Photography', 'Post Type General Name', 'fabian-theme'),
        'singular_name' => _x('Photo', 'Post Type Singular Name', 'fabian-theme'),
        'menu_name' => __('Photography', 'fabian-theme'),
        'name_admin_bar' => __('Photo', 'fabian-theme'),
        'archives' => __('Photo Archives', 'fabian-theme'),
        'attributes' => __('Photo Attributes', 'fabian-theme'),
        'all_items' => __('All Photos', 'fabian-theme'),
        'add_new_item' => __('Add New Photo', 'fabian-theme'),
        'add_new' => __('Add New', 'fabian-theme'),
        'new_item' => __('New Photo', 'fabian-theme'),
        'edit_item' => __('Edit Photo', 'fabian-theme'),
        'update_item' => __('Update Photo', 'fabian-theme'),
        'view_item' => __('View Photo', 'fabian-theme'),
        'view_items' => __('View Photos', 'fabian-theme'),
        'search_items' => __('Search Photo', 'fabian-theme'),
    );
    $args = array(
        'label' => __('Photo', 'fabian-theme'),
        'labels' => $labels,
        'supports' => array('title', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-camera',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'photography'),
    );
    register_post_type('photography', $args);
}
add_action('init', 'fabian_register_photography_cpt', 0);

/**
 * Register Custom Taxonomy: Collection (for Photography)
 */
function fabian_register_collection_taxonomy()
{
    $labels = array(
        'name' => _x('Collections', 'Taxonomy General Name', 'fabian-theme'),
        'singular_name' => _x('Collection', 'Taxonomy Singular Name', 'fabian-theme'),
        'menu_name' => __('Collections', 'fabian-theme'),
        'all_items' => __('All Collections', 'fabian-theme'),
        'parent_item' => __('Parent Collection', 'fabian-theme'),
        'parent_item_colon' => __('Parent Collection:', 'fabian-theme'),
        'new_item_name' => __('New Collection Name', 'fabian-theme'),
        'add_new_item' => __('Add New Collection', 'fabian-theme'),
        'edit_item' => __('Edit Collection', 'fabian-theme'),
        'update_item' => __('Update Collection', 'fabian-theme'),
        'view_item' => __('View Collection', 'fabian-theme'),
        'search_items' => __('Search Collections', 'fabian-theme'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => false,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'collection'),
    );
    register_taxonomy('photo_collection', array('photography'), $args);
}
add_action('init', 'fabian_register_collection_taxonomy', 0);

/**
 * Register meta fields for REST API (Block Editor)
 */
function fabian_register_meta_fields()
{
    // Subtitle for posts and projects
    register_post_meta('post', '_fabian_subtitle', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    register_post_meta('project', '_fabian_subtitle', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Hero style for projects
    register_post_meta('project', '_fabian_hero_style', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'plain',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Hero theme for projects
    register_post_meta('project', '_fabian_hero_theme', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'default' => 'auto',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Background video URL for projects
    register_post_meta('project', '_fabian_background_video', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));

    // Annotations for projects
    register_post_meta('project', '_fabian_annotations', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
}
add_action('init', 'fabian_register_meta_fields');

/**
 * Enqueue block editor sidebar script
 */
function fabian_enqueue_editor_sidebar()
{
    $screen = get_current_screen();

    // Only load on post and project edit screens
    if (!$screen || !in_array($screen->post_type, array('post', 'project'))) {
        return;
    }

    wp_enqueue_script(
        'fabian-editor-sidebar',
        get_template_directory_uri() . '/js/admin/editor-sidebar.js',
        array('wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element'),
        '1.0.1',
        true
    );

    wp_enqueue_style(
        'fabian-editor-sidebar-styles',
        get_template_directory_uri() . '/css/admin/editor-sidebar.css',
        array(),
        '1.0.0'
    );
}
add_action('enqueue_block_editor_assets', 'fabian_enqueue_editor_sidebar');

/**
 * Helper: Estimate read time
 */
function fabian_get_read_time($content = null)
{
    if (null === $content) {
        $content = get_the_content();
    }
    $word_count = str_word_count(wp_strip_all_tags($content));
    $minutes = max(1, round($word_count / 200));
    return sprintf(_n('%d min read', '%d min read', $minutes, 'fabian-theme'), $minutes);
}

/**
 * Helper: Generate heading ID from text
 */
function fabian_generate_heading_id($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = trim($text);
    $text = preg_replace('/\s+/', '-', $text);
    return $text ?: 'heading';
}

/**
 * Helper: Process content to add IDs to h2 headings and return TOC items
 */
function fabian_process_content_with_toc($content)
{
    if (empty($content)) {
        return array('content' => $content, 'toc' => array());
    }

    $toc = array();
    $id_counts = array();

    $processed = preg_replace_callback(
        '/<h2([^>]*)>([\s\S]*?)<\/h2>/i',
        function ($matches) use (&$toc, &$id_counts) {
            $attrs = $matches[1];
            $inner_html = $matches[2];
            $text = wp_strip_all_tags($inner_html);

            // Check for existing id
            if (preg_match('/id=["\']([^"\']*)["\']/', $attrs, $id_match)) {
                $toc[] = array('id' => $id_match[1], 'text' => $text);
                return $matches[0];
            }

            // Generate new id
            $base_id = fabian_generate_heading_id($text);
            if (!isset($id_counts[$base_id])) {
                $id_counts[$base_id] = 0;
            }
            $id_counts[$base_id]++;
            $id = $id_counts[$base_id] === 1 ? $base_id : $base_id . '-' . $id_counts[$base_id];

            $toc[] = array('id' => $id, 'text' => $text);
            return '<h2' . $attrs . ' id="' . esc_attr($id) . '">' . $inner_html . '</h2>';
        },
        $content
    );

    return array('content' => $processed, 'toc' => $toc);
}

/**
 * Helper: Format date
 */
function fabian_format_date($date = null)
{
    if (null === $date) {
        $date = get_the_date('c');
    }
    return date_i18n('F j, Y', strtotime($date));
}

/**
 * Helper: Get photography collections with photos
 */
function fabian_get_photography_collections()
{
    $collections = get_terms(array(
        'taxonomy' => 'photo_collection',
        'hide_empty' => true,
    ));

    if (is_wp_error($collections) || empty($collections)) {
        return array();
    }

    $result = array();

    foreach ($collections as $collection) {
        $photos_query = new WP_Query(array(
            'post_type' => 'photography',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'photo_collection',
                    'field' => 'term_id',
                    'terms' => $collection->term_id,
                ),
            ),
        ));

        $photos = array();
        if ($photos_query->have_posts()) {
            while ($photos_query->have_posts()) {
                $photos_query->the_post();
                $photos[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'photo-thumbnail'),
                    'medium' => get_the_post_thumbnail_url(get_the_ID(), 'photo-medium'),
                    'large' => get_the_post_thumbnail_url(get_the_ID(), 'photo-large'),
                    'full' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                    'alt' => get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true),
                );
            }
            wp_reset_postdata();
        }

        // Generate color from slug
        $color = fabian_string_to_color($collection->slug);

        $result[] = array(
            'id' => $collection->slug,
            'name' => $collection->name,
            'color' => $color,
            'photos' => $photos,
            'count' => count($photos),
        );
    }

    return $result;
}

/**
 * Helper: Generate color from string
 */
function fabian_string_to_color($str)
{
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }
    $color = dechex($hash & 0x00FFFFFF);
    return '#' . str_pad(strtoupper($color), 6, '0', STR_PAD_LEFT);
}

/**
 * Add body classes
 */
function fabian_body_classes($classes)
{
    // Add class for transparent header on project pages
    if (is_singular('project')) {
        $hero_style = get_post_meta(get_the_ID(), '_fabian_hero_style', true) ?: 'plain';
        $hero_theme = get_post_meta(get_the_ID(), '_fabian_hero_theme', true) ?: 'auto';

        // Determine theme
        if ($hero_theme === 'auto') {
            $hero_theme = in_array($hero_style, array('image', 'video')) ? 'light' : 'dark';
        }

        $classes[] = 'header-transparent';
        $classes[] = 'header-theme-' . $hero_theme;
    }

    return $classes;
}
add_filter('body_class', 'fabian_body_classes');

/**
 * Change SEO title separator to em dash
 */
function fabian_title_separator($sep)
{
    return '—';
}
add_filter('document_title_separator', 'fabian_title_separator');

/**
 * Customize excerpt length
 */
function fabian_excerpt_length($length)
{
    return 30;
}
add_filter('excerpt_length', 'fabian_excerpt_length');

/**
 * Customize excerpt more
 */
function fabian_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'fabian_excerpt_more');

/**
 * Get archive preview content - limited paragraphs with media stripped
 *
 * This function extracts only the first few paragraphs of post content
 * and removes images, videos, embeds, iframes, and other media elements
 * to optimize archive page loading.
 *
 * @param int $max_paragraphs Maximum number of paragraphs to include
 * @return array Array with 'content' and 'toc' keys
 */
function fabian_get_archive_preview($max_paragraphs = 3)
{
    $raw_content = get_the_content();

    if (empty($raw_content)) {
        return array('content' => '', 'toc' => array());
    }

    // Strip media blocks before processing to prevent loading
    // Remove Gutenberg blocks: image, video, embed, audio, gallery, cover, media-text
    $content = preg_replace(
        '/<!-- wp:(image|video|embed|audio|gallery|cover|media-text|core-embed\/[^\s]+)[^>]*-->.*?<!-- \/wp:\1 -->/s',
        '',
        $raw_content
    );

    // Remove HTML media elements: img, video, audio, iframe, embed, object, figure (with media)
    $content = preg_replace('/<img[^>]*>/i', '', $content);
    $content = preg_replace('/<video[^>]*>.*?<\/video>/is', '', $content);
    $content = preg_replace('/<audio[^>]*>.*?<\/audio>/is', '', $content);
    $content = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $content);
    $content = preg_replace('/<embed[^>]*>/i', '', $content);
    $content = preg_replace('/<object[^>]*>.*?<\/object>/is', '', $content);

    // Remove figure elements that contained media (now empty or with just figcaption)
    $content = preg_replace('/<figure[^>]*>\s*(<figcaption[^>]*>.*?<\/figcaption>)?\s*<\/figure>/is', '', $content);

    // Remove WordPress [embed] shortcodes
    $content = preg_replace('/\[embed[^\]]*\].*?\[\/embed\]/is', '', $content);
    $content = preg_replace('/\[video[^\]]*\].*?\[\/video\]/is', '', $content);
    $content = preg_replace('/\[audio[^\]]*\].*?\[\/audio\]/is', '', $content);
    $content = preg_replace('/\[gallery[^\]]*\]/i', '', $content);

    // Remove plain URLs on their own line (oEmbed URLs)
    $content = preg_replace('/^\s*https?:\/\/[^\s]+\s*$/m', '', $content);

    // Now apply the_content filters to process remaining content (typography, links, etc.)
    // But first, temporarily disable oEmbed processing
    if (isset($GLOBALS['wp_embed'])) {
        remove_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);
    }
    $content = apply_filters('the_content', $content);
    if (isset($GLOBALS['wp_embed'])) {
        add_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);
    }

    // Limit to first N paragraphs
    // Match paragraph blocks and standalone paragraphs
    if (preg_match_all('/<p[^>]*>.*?<\/p>/is', $content, $matches)) {
        $paragraphs = array_slice($matches[0], 0, $max_paragraphs);
        $content = implode("\n", $paragraphs);
    }

    // Process for TOC (extract h2 headings) from original raw content
    $toc = array();
    if (preg_match_all('/<h2[^>]*>([\s\S]*?)<\/h2>/i', $raw_content, $h2_matches)) {
        foreach ($h2_matches[1] as $heading) {
            $text = wp_strip_all_tags($heading);
            $id = fabian_generate_heading_id($text);
            $toc[] = array('id' => $id, 'text' => $text);
        }
    }

    return array('content' => $content, 'toc' => $toc);
}

/**
 * Add custom rewrite rules for Writing (blog) pages
 */
function fabian_rewrite_rules()
{
    add_rewrite_rule(
        '^writing/?$',
        'index.php?pagename=writing',
        'top'
    );
    add_rewrite_rule(
        '^writing/page/([0-9]+)/?$',
        'index.php?pagename=writing&paged=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^writing/category/([^/]+)/?$',
        'index.php?category_name=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^writing/category/([^/]+)/page/([0-9]+)/?$',
        'index.php?category_name=$matches[1]&paged=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^writing/([^/]+)/?$',
        'index.php?name=$matches[1]',
        'top'
    );
}
add_action('init', 'fabian_rewrite_rules');

/**
 * Flush rewrite rules on theme activation
 */
function fabian_theme_activation()
{
    fabian_register_project_cpt();
    fabian_register_photography_cpt();
    fabian_register_collection_taxonomy();
    fabian_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'fabian_theme_activation');

/**
 * Remove unnecessary WordPress defaults that may cause layout issues
 */
function fabian_cleanup_head()
{
    // Remove WordPress version
    remove_action('wp_head', 'wp_generator');

    // Remove wlwmanifest link
    remove_action('wp_head', 'wlwmanifest_link');

    // Remove RSD link
    remove_action('wp_head', 'rsd_link');

    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');

    // Remove REST API link
    remove_action('wp_head', 'rest_output_link_wp_head');

    // Remove oEmbed discovery links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove emoji scripts and styles
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('init', 'fabian_cleanup_head');


/**
 * Add custom styles to override block editor margins
 */
function fabian_block_editor_overrides()
{
    $custom_css = '
        .entry-content > *:first-child,
        .wp-block-group > *:first-child,
        .page-content > *:first-child {
            margin-top: 0 !important;
        }
        .entry-content > *:last-child,
        .wp-block-group > *:last-child,
        .page-content > *:last-child {
            margin-bottom: 0 !important;
        }
    ';
    // Attach overrides to base stylesheet to ensure availability
    wp_add_inline_style('fabian-theme-core', $custom_css);
}
add_action('wp_enqueue_scripts', 'fabian_block_editor_overrides', 20);
