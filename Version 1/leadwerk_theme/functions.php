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

function leadwerk_theme_dequeue_block_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_dequeue_block_styles', 100 );

/**
 * Favicon: SVG-Favicon aus Theme-Assets einbinden, wenn kein site_icon gesetzt.
 */
function leadwerk_theme_favicon() {
	if ( get_option( 'site_icon' ) ) {
		return;
	}
	$favicon_url = LEADWERK_THEME_URI . '/assets/images/Fee CaFEE_favicon_ohne Dampf.svg';
	echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( $favicon_url ) . '">' . "\n";
}
add_action( 'wp_head', 'leadwerk_theme_favicon', 1 );

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
 * Footer: ACF-Options dynamisch in den Footer-Template-Part einsetzen.
 */
function leadwerk_theme_dynamic_footer( $content ) {
	if ( strpos( $content, 'data-logo-field="true"' ) === false && strpos( $content, 'data-field=' ) === false ) {
		return $content;
	}
	$has_acf = function_exists( 'get_field' );

	// Logo
	$logo_url = LEADWERK_THEME_URI . '/assets/images/logo.svg';
	if ( $has_acf ) {
		$footer_logo = get_field( 'footer_logo', 'option' );
		$logo_field  = $footer_logo ?: get_field( 'logo', 'option' );
		$id = is_array( $logo_field ) ? (int) ( $logo_field['ID'] ?? 0 ) : (int) $logo_field;
		if ( $id ) {
			$u = wp_get_attachment_image_url( $id, 'medium' );
			if ( $u ) {
				$logo_url = $u;
			}
		}
	}
	$content = str_replace(
		'<img src="" alt="CaFEE Brückenmühle Logo" class="footer-logo" data-logo-field="true">',
		'<img src="' . esc_url( $logo_url ) . '" alt="CaFEE Brückenmühle Logo" class="footer-logo">',
		$content
	);

	if ( ! $has_acf ) {
		return $content;
	}

	// Footer-Text
	$footer_text = get_field( 'footer_text', 'option' );
	if ( $footer_text ) {
		$content = preg_replace(
			'/<p data-field="footer_text">[^<]*<\/p>/',
			'<p>' . esc_html( $footer_text ) . '</p>',
			$content
		);
	}

	// Kontakt
	$street = get_field( 'street', 'option' );
	$city   = get_field( 'city', 'option' );
	$phone  = get_field( 'phone', 'option' );
	$email  = get_field( 'email', 'option' );

	if ( $street ) {
		$content = preg_replace( '/<li data-field="street">[^<]*<\/li>/', '<li>' . esc_html( $street ) . '</li>', $content );
	}
	if ( $city ) {
		$content = preg_replace( '/<li data-field="city">[^<]*<\/li>/', '<li>' . esc_html( $city ) . '</li>', $content );
	}
	if ( $phone ) {
		$tel = preg_replace( '/[^0-9+]/', '', $phone );
		$content = preg_replace(
			'/<li data-field="phone_link">.*?<\/li>/s',
			'<li><a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $phone ) . '</a></li>',
			$content
		);
	}
	if ( $email ) {
		$content = preg_replace(
			'/<li data-field="email_link">.*?<\/li>/s',
			'<li><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></li>',
			$content
		);
	}

	// Öffnungszeiten
	$hours = get_field( 'opening_hours', 'option' );
	if ( $hours ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", $hours ) ) );
		$li    = '';
		foreach ( $lines as $line ) {
			$li .= '<li>' . esc_html( $line ) . '</li>';
		}
		$content = preg_replace(
			'/<ul data-field="opening_hours">.*?<\/ul>/s',
			'<ul>' . $li . '</ul>',
			$content
		);
	}

	// Copyright
	$copy = get_field( 'copyright_text', 'option' );
	if ( $copy ) {
		$content = preg_replace(
			'/<p data-field="copyright">.*?<\/p>/s',
			'<p>' . wp_kses_post( $copy ) . '</p>',
			$content
		);
	}

	// Social Links
	$insta = get_field( 'instagram_url', 'option' );
	$fb    = get_field( 'facebook_url', 'option' );
	if ( $insta || $fb ) {
		$svg_ig = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="6" r="1" fill="currentColor"/></svg>';
		$svg_fb = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$social_html = '';
		if ( $insta ) {
			$social_html .= '<a href="' . esc_url( $insta ) . '" aria-label="Instagram" target="_blank" rel="noopener">' . $svg_ig . '</a>';
		}
		if ( $fb ) {
			$social_html .= '<a href="' . esc_url( $fb ) . '" aria-label="Facebook" target="_blank" rel="noopener">' . $svg_fb . '</a>';
		}
		$content = preg_replace(
			'/<div class="footer-social"[^>]*>.*?<\/div>/s',
			'<div class="footer-social">' . $social_html . '</div>',
			$content
		);
	}

	return $content;
}
add_filter( 'render_block', 'leadwerk_theme_dynamic_footer' );

/**
 * Theme-Support: title-tag (WordPress + Yoast übernehmen <title>).
 */
function leadwerk_theme_setup() {
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'leadwerk_theme_setup' );

/**
 * Site-Titel und Tagline beim Import setzen.
 */
function leadwerk_theme_bloginfo_defaults() {
	if ( ! get_option( 'leadwerk_bloginfo_set' ) ) {
		return;
	}
}

/**
 * SEO-Fallback: Meta-Tags aus Yoast-Feldern ausgeben, wenn Yoast nicht aktiv.
 * Wenn Yoast aktiv ist, macht Yoast alles selbst – diese Funktion greift dann nicht.
 */
function leadwerk_theme_seo_fallback() {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}
	$meta_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
	if ( $meta_desc ) {
		echo '<meta name="description" content="' . esc_attr( $meta_desc ) . '">' . "\n";
	}
	$robots = '';
	$noindex  = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
	$nofollow = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', true );
	if ( $noindex === '1' ) {
		$robots .= 'noindex';
	}
	if ( $nofollow === '1' ) {
		$robots .= ( $robots ? ', ' : '' ) . 'nofollow';
	}
	if ( $robots ) {
		echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
	}
	echo '<link rel="canonical" href="' . esc_url( get_permalink( $post_id ) ) . '">' . "\n";
	$og_title = get_post_meta( $post_id, '_yoast_wpseo_opengraph-title', true );
	$og_desc  = get_post_meta( $post_id, '_yoast_wpseo_opengraph-description', true );
	$og_image = get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true );
	if ( $og_title || $og_desc || $og_image ) {
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( get_permalink( $post_id ) ) . '">' . "\n";
		if ( $og_title ) {
			echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		}
		if ( $og_desc ) {
			echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		}
		if ( $og_image ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
		}
		echo '<meta property="og:locale" content="de_DE">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		$tw_title = get_post_meta( $post_id, '_yoast_wpseo_twitter-title', true ) ?: $og_title;
		$tw_desc  = get_post_meta( $post_id, '_yoast_wpseo_twitter-description', true ) ?: $og_desc;
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		if ( $tw_title ) {
			echo '<meta name="twitter:title" content="' . esc_attr( $tw_title ) . '">' . "\n";
		}
		if ( $tw_desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $tw_desc ) . '">' . "\n";
		}
		if ( $og_image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'leadwerk_theme_seo_fallback', 2 );

/**
 * Title-Tag Fallback: Yoast-Title nutzen, wenn Yoast nicht aktiv.
 */
function leadwerk_theme_document_title( $title ) {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return $title;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return $title;
	}
	$seo_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
	if ( $seo_title ) {
		return $seo_title;
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'leadwerk_theme_document_title', 20 );

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
