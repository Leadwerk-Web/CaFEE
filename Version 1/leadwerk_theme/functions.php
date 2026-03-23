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
/** Standard-WPForms-ID für die Reservierungs-/Kontakt-Sektion, falls keine ACF-Option gesetzt ist. */
define( 'LEADWERK_WPFORMS_RESERVATION_DEFAULT_ID', 171 );

/**
 * WPForms-ID der Kontakt-/Reservierungs-Sektion (ACF-Option oder Theme-Konstante).
 *
 * @return int
 */
function leadwerk_theme_get_reservation_wpforms_id() {
	$id = 0;
	if ( function_exists( 'get_field' ) ) {
		$id = (int) get_field( 'wpforms_reservation_id', 'option' );
	}
	if ( ! $id && defined( 'LEADWERK_WPFORMS_RESERVATION_DEFAULT_ID' ) ) {
		$id = (int) LEADWERK_WPFORMS_RESERVATION_DEFAULT_ID;
	}
	return $id;
}

/**
 * Nach erfolgreicher Übermittlung zur Danke-Seite weiterleiten (statt Inline-Meldung).
 * Setzt die Bestätigung für das CaFEE-Kontaktformular per Filter, damit auch AJAX-Submit funktioniert.
 *
 * @param array $form_data Formular-Daten (dekodiert).
 * @param array $entry     Roh-Eintrag.
 * @return array
 */
function leadwerk_theme_wpforms_force_danke_redirect( $form_data, $entry ) {
	if ( empty( $form_data['id'] ) ) {
		return $form_data;
	}
	$target = leadwerk_theme_get_reservation_wpforms_id();
	if ( ! $target || absint( $form_data['id'] ) !== $target ) {
		return $form_data;
	}
	if ( empty( $form_data['settings']['confirmations'] ) || ! is_array( $form_data['settings']['confirmations'] ) ) {
		return $form_data;
	}
	$danke = home_url( '/danke/' );
	foreach ( $form_data['settings']['confirmations'] as $key => $conf ) {
		if ( ! is_array( $conf ) ) {
			continue;
		}
		$form_data['settings']['confirmations'][ $key ]['type']     = 'redirect';
		$form_data['settings']['confirmations'][ $key ]['redirect'] = $danke;
		$form_data['settings']['confirmations'][ $key ]['message']  = '';
		break;
	}
	return $form_data;
}
add_filter( 'wpforms_process_before_form_data', 'leadwerk_theme_wpforms_force_danke_redirect', 5, 2 );

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
		'leadwerk-page-flip',
		'https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.js',
		array(),
		'2.0.7',
		true
	);
	wp_enqueue_script(
		'leadwerk-theme-main',
		LEADWERK_THEME_URI . '/assets/js/main.js',
		array( 'leadwerk-page-flip' ),
		LEADWERK_THEME_VERSION,
		true
	);
	$page_turn_path = LEADWERK_THEME_DIR . '/assets/audio/page-turn.mp3';
	$page_turn_url  = is_readable( $page_turn_path )
		? LEADWERK_THEME_URI . '/assets/audio/page-turn.mp3'
		: '';
	wp_localize_script(
		'leadwerk-theme-main',
		'cafeeTheme',
		array(
			'fairySvgUrl'      => esc_url( LEADWERK_THEME_URI . '/assets/images/Fee CaFEE_favicon_ohne Dampf.svg' ),
			'pageTurnSoundUrl' => $page_turn_url ? esc_url( $page_turn_url ) : '',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_enqueue_assets' );

/**
 * Theme-style.css nach WPForms-Frontend-CSS laden.
 * Sonst setzen div.wpforms-container-full * und ähnliche Resets unsere .wpforms-cafee-wrap-Regeln außer Kraft (Reihenfolge im HTML).
 */
function leadwerk_theme_enqueue_style_after_wpforms() {
	$theme_handle = 'leadwerk-theme-style';
	if ( ! wp_style_is( $theme_handle, 'enqueued' ) ) {
		return;
	}
	$wpforms_handles = array(
		'wpforms-full',
		'wpforms-pro-full',
		'wpforms-modern-full',
	);
	$deps = array();
	foreach ( $wpforms_handles as $h ) {
		if ( wp_style_is( $h, 'enqueued' ) ) {
			$deps[] = $h;
		}
	}
	if ( empty( $deps ) ) {
		return;
	}
	wp_dequeue_style( $theme_handle );
	wp_enqueue_style(
		$theme_handle,
		get_stylesheet_uri(),
		$deps,
		LEADWERK_THEME_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_enqueue_style_after_wpforms', 2000 );

function leadwerk_theme_dequeue_block_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_dequeue_block_styles', 100 );

/**
 * Rechtstexte & Danke: Unterseiten-Klassen; Fee auf allen Seiten (Startseite: Kanten-Modus).
 *
 * @param string[] $classes Body-Klassen.
 * @return string[]
 */
function leadwerk_theme_body_class_subpages( $classes ) {
	if ( ! is_front_page() ) {
		$classes[] = 'is-subpage';
	}
	if ( is_front_page() || is_page( array( 'impressum', 'datenschutz' ) ) ) {
		$classes[] = 'has-ambient-fairy-home';
	} else {
		$classes[] = 'has-ambient-fairy';
	}
	return $classes;
}
add_filter( 'body_class', 'leadwerk_theme_body_class_subpages' );

/**
 * Unterseiten: Navigation nur „Home“ (zentriert per CSS), Link zur Startseite /#home.
 *
 * @param string $block_content Gerendertes Markup des Template-Parts.
 * @param array  $block       Block-Payload.
 * @return string
 */
function leadwerk_theme_render_navigation_subpage( $block_content, $block ) {
	$slug = isset( $block['attrs']['slug'] ) ? (string) $block['attrs']['slug'] : '';
	if ( $slug !== 'navigation' || is_front_page() ) {
		return $block_content;
	}
	$logo_html = do_blocks( '<!-- wp:leadwerk/logo /-->' );
	$home_href = esc_url( home_url( '/' ) ) . '#home';
	$nav_label = esc_attr__( 'Hauptmenü', 'leadwerk-theme' );
	$toggle_label = esc_attr__( 'Navigation öffnen', 'leadwerk-theme' );
	return '<nav class="wp-block-group nav" id="mainNav" role="navigation" aria-label="' . $nav_label . '">' .
		'<div class="nav-container">' .
		$logo_html .
		'<button type="button" class="nav-toggle" id="navToggle" aria-label="' . $toggle_label . '">' .
		'<span></span><span></span><span></span>' .
		'</button>' .
		'<ul class="nav-menu" id="navMenu">' .
		'<li><a href="' . $home_href . '">Home</a></li>' .
		'</ul>' .
		'</div>' .
		'</nav>';
}
add_filter( 'render_block_core/template-part', 'leadwerk_theme_render_navigation_subpage', 10, 2 );

/**
 * Impressum & Datenschutz: „Zurück zur Startseite“ wie auf der Danke-Seite, falls im Inhalt noch fehlt.
 *
 * @param string $content Beitrags-/Seiteninhalt (gerendert).
 * @return string
 */
function leadwerk_theme_legal_pages_back_link( $content ) {
	if ( ! is_singular( 'page' ) || is_feed() || is_admin() ) {
		return $content;
	}
	if ( ! is_page( array( 'impressum', 'datenschutz' ) ) ) {
		return $content;
	}
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( strpos( $content, 'legal-back' ) !== false ) {
		return $content;
	}
	$href  = esc_url( home_url( '/' ) ) . '#home';
	$label = esc_html__( 'Zurück zur Startseite', 'leadwerk-theme' );
	return $content . '<p class="legal-back"><a class="btn btn-primary" href="' . $href . '">' . $label . '</a></p>';
}
add_filter( 'the_content', 'leadwerk_theme_legal_pages_back_link', 20 );

/**
 * URL des Hero-Hintergrundvideos (erstes hero-Layout in home_sections der Startseite).
 *
 * @return string
 */
function leadwerk_theme_get_hero_background_video_url() {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$front_id = (int) get_option( 'page_on_front' );
	if ( ! $front_id ) {
		return '';
	}
	$sections = get_field( 'home_sections', $front_id );
	if ( ! is_array( $sections ) ) {
		return '';
	}
	foreach ( $sections as $section ) {
		if ( ! is_array( $section ) || ( isset( $section['acf_fc_layout'] ) ? $section['acf_fc_layout'] : '' ) !== 'hero' ) {
			continue;
		}
		$vid = isset( $section['background_video'] ) ? $section['background_video'] : null;
		if ( is_array( $vid ) ) {
			if ( ! empty( $vid['url'] ) ) {
				return (string) $vid['url'];
			}
			$id = isset( $vid['ID'] ) ? (int) $vid['ID'] : 0;
			if ( $id ) {
				$u = wp_get_attachment_url( $id );
				return $u ? $u : '';
			}
			return '';
		}
		if ( is_numeric( $vid ) ) {
			$u = wp_get_attachment_url( (int) $vid );
			return $u ? $u : '';
		}
		return '';
	}
	return '';
}

/**
 * Hero-Video früh laden (Preload im head, damit der Download parallel zu CSS/Fonts startet).
 */
function leadwerk_theme_preload_hero_background_video() {
	if ( ! is_front_page() ) {
		return;
	}
	$url = leadwerk_theme_get_hero_background_video_url();
	if ( ! $url ) {
		return;
	}
	printf(
		'<link rel="preload" href="%s" as="video" type="video/mp4" fetchpriority="high" />' . "\n",
		esc_url( $url )
	);
}
add_action( 'wp_head', 'leadwerk_theme_preload_hero_background_video', 0 );

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
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-gutenberg-reset.css' );
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
 * Kanonische URL der Startseite (für Formular-Action und Redirects).
 *
 * @return string
 */
function leadwerk_theme_get_front_url() {
	if ( get_option( 'show_on_front' ) === 'page' ) {
		$fp = (int) get_option( 'page_on_front' );
		if ( $fp ) {
			return get_permalink( $fp );
		}
	}
	return home_url( '/' );
}

/**
 * Kontaktformular (PHP-Fallback): POST vor der Ausgabe verarbeiten, E-Mail senden, nach /danke/ umleiten.
 */
function leadwerk_theme_contact_form_template_redirect() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}
	if ( ! isset( $_POST['leadwerk_contact_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['leadwerk_contact_nonce'] ), 'leadwerk_contact' ) ) {
		return;
	}
	if ( ! is_front_page() ) {
		return;
	}
	$name    = isset( $_POST['leadwerk_name'] ) ? sanitize_text_field( wp_unslash( $_POST['leadwerk_name'] ) ) : '';
	$email   = isset( $_POST['leadwerk_email'] ) ? sanitize_email( wp_unslash( $_POST['leadwerk_email'] ) ) : '';
	$message = isset( $_POST['leadwerk_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['leadwerk_message'] ) ) : '';
	$back    = add_query_arg( 'contact_error', '1', leadwerk_theme_get_front_url() ) . '#reservation';
	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( $back );
		exit;
	}
	$to      = function_exists( 'get_field' ) ? get_field( 'email', 'option' ) : get_option( 'admin_email' );
	$to      = $to ? $to : get_option( 'admin_email' );
	$subject = 'Neue Nachricht von ' . $name . ' – CaFEE Brückenmühle';
	$body    = "Name: $name\nE-Mail: $email\n\nNachricht:\n$message";
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent    = wp_mail( $to, $subject, $body, $headers );
	if ( $sent ) {
		wp_safe_redirect( home_url( '/danke/' ) );
		exit;
	}
	wp_safe_redirect( $back );
	exit;
}
add_action( 'template_redirect', 'leadwerk_theme_contact_form_template_redirect', 0 );

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
