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

define( 'LEADWERK_THEME_VERSION', '1.0.1' );
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
			'themeUri'         => esc_url( LEADWERK_THEME_URI ),
			'fairySvgUrl'      => esc_url( LEADWERK_THEME_URI . '/assets/images/Fee CaFEE_favicon_ohne Dampf.svg' ),
			'pageTurnSoundUrl' => $page_turn_url ? esc_url( $page_turn_url ) : '',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_enqueue_assets' );

/**
 * Truncate a human-readable SEO title for Yoast pixel/width hints (character-based heuristic).
 *
 * @param string $title      Raw title.
 * @param int    $max_chars  Maximum characters before ellipsis.
 * @return string
 */
function leadwerk_theme_truncate_seo_title_for_yoast( $title, $max_chars = 58 ) {
	$title = trim( (string) $title );
	if ( '' === $title ) {
		return '';
	}
	if ( $max_chars < 8 ) {
		$max_chars = 8;
	}
	if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $title ) > $max_chars ) {
		return rtrim( mb_substr( $title, 0, $max_chars - 1 ) ) . '…';
	}
	if ( strlen( $title ) > $max_chars ) {
		return rtrim( substr( $title, 0, $max_chars - 1 ) ) . '…';
	}

	return $title;
}

/**
 * Build rendered HTML for Yoast (CaFEE: ACF home_sections or block editor content).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function leadwerk_theme_get_yoast_analysis_content( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}

	$source_key = (string) get_post_meta( $post_id, 'leadwerk_source_key', true );

	$post_before = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
	$GLOBALS['post'] = get_post( $post_id );
	if ( ! ( $GLOBALS['post'] instanceof WP_Post ) ) {
		$GLOBALS['post'] = $post_before;

		return '';
	}
	setup_postdata( $GLOBALS['post'] );

	$html = '';

	if ( 'cafee-home-v1' === $source_key && function_exists( 'get_field' ) ) {
		$sections = get_field( 'home_sections', $post_id );
		if ( is_array( $sections ) && ! empty( $sections ) ) {
			ob_start();
			include LEADWERK_THEME_DIR . '/inc/block-home-sections.php';
			$html = (string) ob_get_clean();
		}
	}

	if ( '' === $html ) {
		$post_obj = get_post( $post_id );
		if ( $post_obj instanceof WP_Post && '' !== trim( (string) $post_obj->post_content ) ) {
			$html = (string) apply_filters( 'the_content', $post_obj->post_content );
		}
	}

	wp_reset_postdata();
	$GLOBALS['post'] = $post_before;

	if ( '' === trim( wp_strip_all_tags( $html ) ) && false === strpos( $html, '<img' ) ) {
		return '';
	}

	$html = (string) preg_replace( '#<script[^>]*>.*?</script>#is', '', $html );
	$html = (string) preg_replace( '#<style[^>]*>.*?</style>#is', '', $html );

	$clean = wp_kses_post( $html );
	$clean = (string) str_replace( array( "\r", "\n", "\t" ), ' ', $clean );
	$clean = (string) preg_replace( '/\s+/', ' ', $clean );

	return trim( $clean );
}

/**
 * Rebuild Yoast SEO Indexable for one post (admin list dots, admin bar) after meta-only changes.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function leadwerk_theme_rebuild_yoast_post_indexable( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || ! function_exists( 'YoastSEO' ) ) {
		return;
	}
	if ( ! class_exists( '\Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher', false ) ) {
		return;
	}

	try {
		$yoast = YoastSEO();
		if ( ! is_object( $yoast ) || ! isset( $yoast->classes ) || ! is_object( $yoast->classes ) || ! method_exists( $yoast->classes, 'get' ) ) {
			return;
		}

		$watcher = $yoast->classes->get( \Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher::class );
		if ( is_object( $watcher ) && method_exists( $watcher, 'build_indexable' ) ) {
			$watcher->build_indexable( $post_id );
		}
	} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		return;
	}
}

/**
 * After saving a Leadwerk-managed page, refresh Yoast indexables.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post.
 * @return void
 */
function leadwerk_theme_leadwerk_page_yoast_indexable_touch( $post_id, $post, $update ) {
	unset( $update );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( '' === (string) get_post_meta( $post_id, 'leadwerk_source_key', true ) ) {
		return;
	}

	leadwerk_theme_rebuild_yoast_post_indexable( $post_id );
}

add_action( 'save_post', 'leadwerk_theme_leadwerk_page_yoast_indexable_touch', 99, 3 );

/**
 * Feed rendered page content into Yoast's content analysis (admin).
 *
 * @param string $hook_suffix Current admin hook.
 * @return void
 */
function leadwerk_theme_enqueue_admin_yoast_analysis( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || ! class_exists( 'WPSEO_Options' ) || ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = (int) $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = (int) $_POST['post_ID'];
	}

	if ( $post_id <= 0 ) {
		return;
	}

	$analysis_content = leadwerk_theme_get_yoast_analysis_content( $post_id );
	if ( '' === $analysis_content ) {
		return;
	}

	$max_bytes = (int) apply_filters( 'leadwerk_yoast_analysis_inline_max_bytes', 350000 );
	if ( $max_bytes > 0 && strlen( $analysis_content ) > $max_bytes ) {
		$analysis_content = substr( $analysis_content, 0, $max_bytes );
	}

	$payload = array(
		'postId'          => $post_id,
		'renderedContent' => $analysis_content,
	);
	$json    = wp_json_encode(
		$payload,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);
	if ( false === $json ) {
		$payload['renderedContent'] = substr( wp_strip_all_tags( $analysis_content ), 0, 60000 );
		$json                       = wp_json_encode(
			$payload,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);
	}
	if ( false === $json ) {
		return;
	}

	wp_enqueue_script(
		'leadwerk-admin-yoast-analysis',
		LEADWERK_THEME_URI . '/js/admin-yoast-analysis.js',
		array(),
		LEADWERK_THEME_VERSION,
		true
	);

	wp_add_inline_script(
		'leadwerk-admin-yoast-analysis',
		'window.leadwerkYoastAnalysis = ' . $json . ';',
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'leadwerk_theme_enqueue_admin_yoast_analysis', 100 );

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
	if ( is_404() || is_page( '404' ) ) {
		$classes[] = 'page-404';
		$classes[] = 'header-scrolled';
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
	$toggle_close_label = esc_attr__( 'Navigation schließen', 'leadwerk-theme' );
	return '<nav class="wp-block-group nav" id="mainNav" role="navigation" aria-label="' . $nav_label . '">' .
		'<div class="nav-container">' .
		$logo_html .
		'<button type="button" class="nav-toggle" id="navToggle" aria-controls="navMenu" aria-expanded="false" aria-label="' . $toggle_label . '" data-label-open="' . $toggle_label . '" data-label-close="' . $toggle_close_label . '">' .
		'<span></span><span></span><span></span>' .
		'</button>' .
		'<ul class="nav-menu" id="navMenu" aria-hidden="true">' .
		'<li><a href="' . $home_href . '">Home</a></li>' .
		'</ul>' .
		'</div>' .
		'<div class="nav-backdrop" id="navBackdrop" aria-hidden="true"></div>' .
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
 * Kanonische URL für ACF-Datei-Felder (Video/Audio): bevorzugt Anhang-ID → wp_get_attachment_url (identisch mit Preload).
 *
 * @param mixed $vid ACF file (array|int|string).
 * @return string Absolute URL oder leer.
 */
function leadwerk_theme_resolve_acf_file_video_url( $vid ) {
	if ( $vid === null || $vid === '' || ( is_array( $vid ) && $vid === array() ) ) {
		return '';
	}
	if ( is_array( $vid ) ) {
		$id = isset( $vid['ID'] ) ? (int) $vid['ID'] : 0;
		if ( $id ) {
			$u = wp_get_attachment_url( $id );
			return $u ? $u : '';
		}
		if ( ! empty( $vid['url'] ) ) {
			return esc_url_raw( (string) $vid['url'] );
		}
		return '';
	}
	if ( is_numeric( $vid ) ) {
		$u = wp_get_attachment_url( (int) $vid );
		return $u ? $u : '';
	}
	$v = trim( (string) $vid );
	if ( $v !== '' && filter_var( $v, FILTER_VALIDATE_URL ) ) {
		return esc_url_raw( $v );
	}
	return '';
}

/**
 * Kanonische Bild-URL für ACF-Bildfelder (Poster o. ä.).
 *
 * @param mixed  $img  ACF image (array|int).
 * @param string $size Bildgröße.
 * @return string
 */
function leadwerk_theme_resolve_acf_image_url( $img, $size = 'large' ) {
	if ( $img === null || $img === '' || ( is_array( $img ) && $img === array() ) ) {
		return '';
	}
	if ( ! is_array( $img ) && ! is_numeric( $img ) ) {
		return '';
	}
	if ( is_array( $img ) ) {
		$id = isset( $img['ID'] ) ? (int) $img['ID'] : 0;
		if ( $id ) {
			$u = wp_get_attachment_image_url( $id, $size );
			return $u ? $u : '';
		}
		if ( ! empty( $img['url'] ) ) {
			return esc_url_raw( (string) $img['url'] );
		}
		return '';
	}
	$u = wp_get_attachment_image_url( (int) $img, $size );
	return $u ? $u : '';
}

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
/**
 * Noindex-Status einer Seite anhand der importierten Yoast-Metadaten ermitteln.
 *
 * @param int $post_id Beitrag-ID.
 * @return bool
 */
function leadwerk_theme_post_is_noindex( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return false;
	}
	return '1' === (string) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
}

/**
 * Frontpage explizit indexierbar halten und Noindex-Seiten konsistent markieren.
 *
 * @param array $robots Robots-Direktiven.
 * @return array
 */
function leadwerk_theme_wp_robots( $robots ) {
	if ( is_admin() || is_feed() || is_robots() ) {
		return $robots;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return $robots;
	}
	if ( leadwerk_theme_post_is_noindex( $post_id ) ) {
		unset( $robots['index'], $robots['nofollow'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
		return $robots;
	}
	if ( is_front_page() && '0' !== (string) get_option( 'blog_public', '1' ) ) {
		unset( $robots['noindex'], $robots['nofollow'] );
		$robots['index']             = true;
		$robots['follow']            = true;
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = -1;
		$robots['max-video-preview'] = -1;
	}
	return $robots;
}
add_filter( 'wp_robots', 'leadwerk_theme_wp_robots' );

/**
 * Veroeffentlichte Noindex-Seiten nicht in die Core-Sitemap aufnehmen.
 *
 * @param array  $args      Query-Argumente.
 * @param string $post_type Post-Type.
 * @return array
 */
function leadwerk_theme_exclude_noindex_from_wp_sitemap( $args, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $args;
	}
	$noindex_query = array(
		'relation' => 'OR',
		array(
			'key'     => '_yoast_wpseo_meta-robots-noindex',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_yoast_wpseo_meta-robots-noindex',
			'value'   => '1',
			'compare' => '!=',
		),
	);
	if ( empty( $args['meta_query'] ) ) {
		$args['meta_query'] = $noindex_query;
		return $args;
	}
	$args['meta_query'] = array(
		'relation' => 'AND',
		$args['meta_query'],
		$noindex_query,
	);
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'leadwerk_theme_exclude_noindex_from_wp_sitemap', 10, 2 );

/**
 * Yoast-Sitemap ebenfalls um Noindex-Seiten bereinigen.
 *
 * @param int[] $excluded Bereits ausgeschlossene IDs.
 * @return int[]
 */
function leadwerk_theme_exclude_noindex_from_yoast_sitemap( $excluded ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => '_yoast_wpseo_meta-robots-noindex',
			'meta_value'             => '1',
		)
	);
	if ( empty( $query->posts ) ) {
		return $excluded;
	}
	return array_values( array_unique( array_merge( $excluded, array_map( 'intval', $query->posts ) ) ) );
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'leadwerk_theme_exclude_noindex_from_yoast_sitemap' );

/**
 * Strukturierte Daten fuer die indexierbare Startseite ausgeben.
 */
function leadwerk_theme_local_business_schema() {
	if ( ! is_front_page() ) {
		return;
	}
	$base_url = trailingslashit( home_url( '/' ) );
	$logo_url = LEADWERK_THEME_URI . '/assets/images/Fee CaFEE_favicon_ohne Dampf.svg';
	$phone    = function_exists( 'get_field' ) ? (string) get_field( 'phone', 'option' ) : '';
	$email    = function_exists( 'get_field' ) ? (string) get_field( 'email', 'option' ) : '';
	$street   = function_exists( 'get_field' ) ? (string) get_field( 'street', 'option' ) : '';
	$city_raw = function_exists( 'get_field' ) ? (string) get_field( 'city', 'option' ) : '';
	$city     = trim( preg_replace( '/^\d{4,5}\s+/', '', $city_raw ) );
	$postal   = '';
	if ( preg_match( '/(\d{4,5})/', $city_raw, $matches ) ) {
		$postal = $matches[1];
	}
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'CafeOrCoffeeShop',
		'@id'      => $base_url . '#organization',
		'name'     => get_bloginfo( 'name' ),
		'url'      => $base_url,
		'image'    => esc_url_raw( $logo_url ),
		'telephone' => $phone ?: '+49 151/103 100 59',
		'email'    => $email ?: 'hallo@cafee-gernsbach.de',
		'address'  => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street ?: 'Hofstaette 2',
			'postalCode'      => $postal ?: '76593',
			'addressLocality' => $city ?: 'Gernsbach',
			'addressCountry'  => 'DE',
		),
		'sameAs'   => array(
			'https://www.instagram.com/cafeebrueckenmuehle/',
		),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
				'opens'     => '09:00',
				'closes'    => '17:00',
			),
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'leadwerk_theme_local_business_schema', 25 );

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
