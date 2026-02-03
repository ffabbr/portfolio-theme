<?php
/**
 * The header for our theme
 *
 * @package Fabian_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Determine header classes based on page type and settings
$header_classes = array( 'header', 'header--dark' );

if ( is_singular( 'project' ) ) {
    $hero_style = get_post_meta( get_the_ID(), '_fabian_hero_style', true ) ?: 'plain';
    $hero_theme = get_post_meta( get_the_ID(), '_fabian_hero_theme', true ) ?: 'auto';

    // Determine theme
    if ( $hero_theme === 'auto' ) {
        $hero_theme = in_array( $hero_style, array( 'image', 'video' ) ) ? 'light' : 'dark';
    }

    $header_classes[] = 'header--transparent';
    $header_classes = array_diff( $header_classes, array( 'header--dark' ) );
    $header_classes[] = 'header--' . $hero_theme;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="lenis lenis-smooth">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'app' ); ?>>
<?php wp_body_open(); ?>

<header class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>">
    <div class="header-content">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo"><?php bloginfo( 'name' ); ?></a>

        <button
            class="mobile-menu-toggle"
            type="button"
            aria-label="<?php esc_attr_e( 'Toggle navigation', 'fabian-theme' ); ?>"
            aria-expanded="false"
            aria-controls="primary-navigation"
        >
            <span class="mobile-menu-toggle__bar"></span>
            <span class="mobile-menu-toggle__bar"></span>
        </button>

        <nav id="primary-navigation" class="nav">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'fallback_cb' => false,
                    'items_wrap' => '%3$s',
                    'depth' => 1,
                    'walker' => new Fabian_Nav_Walker(),
                )
            );
            ?>
        </nav>

        <a href="mailto:hi@rohlik.net" class="email">hi@rohlik.net</a>
    </div>
</header>

<?php
// Determine main content classes based on page type
$main_classes = array();

if ( is_front_page() ) {
    $main_classes[] = 'home';
} elseif ( is_page_template( 'template-photography.php' ) ) {
    $main_classes[] = 'photography-page';
} elseif ( is_404() ) {
    $main_classes[] = 'page';
    $main_classes[] = 'not-found';
} elseif ( is_category() ) {
    $main_classes[] = 'page';
    $main_classes[] = 'category-archive';
} elseif ( is_home() || is_page_template( 'page-writing.php' ) || ( is_archive() && ! is_post_type_archive( 'project' ) ) ) {
    $main_classes[] = 'page';
    $main_classes[] = 'writing';
} elseif ( is_singular( 'post' ) || is_singular( 'project' ) ) {
    // Single posts/projects use article wrappers, no main class needed
} else {
    $main_classes[] = 'page';
}

$main_class_string = ! empty( $main_classes ) ? ' class="' . esc_attr( implode( ' ', $main_classes ) ) . '"' : '';
?>
<main<?php echo $main_class_string; ?>>
