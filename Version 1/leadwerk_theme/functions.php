<?php
/**
 * Leadwerk Theme – CaFEE Brückenmühle
 * Minimale PHP-Integration: Asset-Enqueue, ACF-Block für Startseiten-Sektionen.
 *
 * @package Leadwerk_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEADWERK_THEME_VERSION', '1.0.0' );
define( 'LEADWERK_THEME_DIR', get_template_directory() );
define( 'LEADWERK_THEME_URI', get_template_directory_uri() );

/**
 * Assets enqueuen: style.css (vollständig im Theme) + JS.
 * Alle Styles liegen in style.css; Bilder/Assets in assets/images/ (mit im Theme).
 */
function leadwerk_theme_enqueue_assets() {
	wp_enqueue_style(
		'leadwerk-theme-style',
		get_stylesheet_uri(),
		array(),
		LEADWERK_THEME_VERSION
	);
	wp_enqueue_script(
		'leadwerk-theme-main',
		LEADWERK_THEME_URI . '/assets/js/main.js',
		array(),
		LEADWERK_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_enqueue_assets' );

/**
 * ACF-JSON Speicherort setzen (Theme-Verzeichnis).
 */
function leadwerk_theme_acf_json_save_point( $path ) {
	return LEADWERK_THEME_DIR . '/acf-json';
}
function leadwerk_theme_acf_json_load_point( $paths ) {
	unset( $paths[0] );
	$paths[] = LEADWERK_THEME_DIR . '/acf-json';
	return $paths;
}
add_filter( 'acf/settings/save_json', 'leadwerk_theme_acf_json_save_point' );
add_filter( 'acf/settings/load_json', 'leadwerk_theme_acf_json_load_point' );

/**
 * Block: Logo (aus ACF-Option).
 */
function leadwerk_theme_register_logo_block() {
	register_block_type(
		LEADWERK_THEME_DIR . '/inc/block-logo',
		array( 'render_callback' => 'leadwerk_theme_render_logo_block' )
	);
}
function leadwerk_theme_render_logo_block() {
	$logo = function_exists( 'get_field' ) ? get_field( 'logo', 'option' ) : null;
	$url  = get_template_directory_uri() . '/assets/images/logo.svg'; // Fallback: CaFEE-Logo
	if ( $logo ) {
		$id = is_array( $logo ) ? (int) ( $logo['ID'] ?? 0 ) : (int) $logo;
		if ( $id ) {
			$u = wp_get_attachment_image_url( $id, 'medium' );
			if ( $u ) {
				$url = $u;
			}
		}
	}
	return '<a href="' . esc_url( home_url( '/' ) ) . '#home" class="nav-logo">' .
		'<img src="' . esc_url( $url ) . '" alt="CaFEE Brückenmühle Logo" class="nav-logo-img" width="120" height="auto"/>' .
		'</a>';
}
add_action( 'init', 'leadwerk_theme_register_logo_block' );

/**
 * ACF-Block "Home-Sektionen" registrieren (Flexible Content Ausgabe).
 */
function leadwerk_theme_register_blocks() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}
	acf_register_block_type( array(
		'name'            => 'cafee-home-sections',
		'title'           => __( 'CaFEE Startseiten-Sektionen', 'leadwerk-theme' ),
		'description'     => __( 'Hero, Story, Speisekarte, Erlebnis, Interviews, Team, Reservierung', 'leadwerk-theme' ),
		'render_callback' => 'leadwerk_theme_render_home_sections',
		'category'        => 'theme',
		'icon'            => 'coffee',
		'supports'        => array( 'align' => false ),
	) );
}
add_action( 'acf/init', 'leadwerk_theme_register_blocks' );

/**
 * ACF-Optionsseite für globale Leadwerk-Daten.
 */
function leadwerk_theme_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page( array(
		'page_title' => __( 'Leadwerk Optionen', 'leadwerk-theme' ),
		'menu_title' => __( 'Leadwerk Optionen', 'leadwerk-theme' ),
		'menu_slug'  => 'acf-options',
		'capability' => 'edit_posts',
	) );
}
add_action( 'acf/init', 'leadwerk_theme_acf_options_page' );

/**
 * Render-Callback: ACF Flexible Content "home_sections" ausgeben.
 */
function leadwerk_theme_render_home_sections() {
	$post_id = get_the_ID();
	if ( ! $post_id || ! function_exists( 'get_field' ) ) {
		return;
	}
	$sections = get_field( 'home_sections', $post_id );
	if ( ! is_array( $sections ) || empty( $sections ) ) {
		return;
	}
	include LEADWERK_THEME_DIR . '/inc/block-home-sections.php';
}
