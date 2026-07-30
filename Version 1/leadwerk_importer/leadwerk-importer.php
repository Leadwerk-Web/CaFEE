<?php
/**
 * Plugin Name: Leadwerk Importer
 * Description: Import statischer CaFEE-Inhalte in WordPress (Pages, Medien, ACF). Dry-Run, Re-Import, Logging.
 * Version: 1.0.0
 * Author: Leadwerk
 * Text Domain: leadwerk-importer
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package Leadwerk_Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEADWERK_IMPORTER_VERSION', '1.0.0' );
define( 'LEADWERK_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEADWERK_IMPORTER_URL', plugin_dir_url( __FILE__ ) );

require_once LEADWERK_IMPORTER_PATH . 'includes/class-leadwerk-importer.php';
require_once LEADWERK_IMPORTER_PATH . 'includes/class-leadwerk-media-importer.php';
require_once LEADWERK_IMPORTER_PATH . 'includes/class-leadwerk-logger.php';
require_once LEADWERK_IMPORTER_PATH . 'includes/class-leadwerk-acf-filler.php';

/**
 * Admin-Menü und Ausführung.
 */
function leadwerk_importer_menu() {
	add_management_page(
		__( 'Leadwerk Import', 'leadwerk-importer' ),
		__( 'Leadwerk Import', 'leadwerk-importer' ),
		'manage_options',
		'leadwerk-import',
		'leadwerk_importer_admin_page'
	);
}
add_action( 'admin_menu', 'leadwerk_importer_menu' );

/**
 * Manifest-Seiten fuer die Einzelimport-Auswahl laden.
 *
 * @return array<int,array<string,mixed>>
 */
function leadwerk_importer_get_manifest_pages() {
	$path = LEADWERK_IMPORTER_PATH . 'manifest/mapping.json';
	if ( ! is_file( $path ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $data ) || empty( $data['pages'] ) || ! is_array( $data['pages'] ) ) {
		return array();
	}
	return $data['pages'];
}

function leadwerk_importer_admin_page() {
	$dry_run          = isset( $_GET['dry_run'] ) && $_GET['dry_run'] === '1';
	$run              = isset( $_GET['run'] ) && $_GET['run'] === '1' && current_user_can( 'manage_options' );
	$manifest_pages   = leadwerk_importer_get_manifest_pages();
	$single_page_run  = isset( $_POST['leadwerk_import_single_page'] ) && current_user_can( 'manage_options' );
	$menu_book_run    = isset( $_POST['leadwerk_import_menu_book'] ) && current_user_can( 'manage_options' );
	$gallery_run      = isset( $_POST['leadwerk_import_gallery'] ) && current_user_can( 'manage_options' );
	$karriere_run     = isset( $_POST['leadwerk_import_karriere'] ) && current_user_can( 'manage_options' );
	$posted_source_key = isset( $_POST['leadwerk_source_key'] ) && is_scalar( $_POST['leadwerk_source_key'] )
		? (string) wp_unslash( $_POST['leadwerk_source_key'] )
		: 'eroeffnung-v1';
	$single_source_key = sanitize_key( $posted_source_key );

	if ( $run && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'leadwerk_import_run' ) ) {
		// Zeitlimit anheben: Thumbnail-Erzeugung (Imagick) kann bei vielen/großen Bildern > 30s dauern.
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}
		$importer = new Leadwerk_Importer( ! $dry_run );
		$importer->run();
		echo '<div class="notice notice-success"><p>Import ausgeführt. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
	}
	if ( $single_page_run && check_admin_referer( 'leadwerk_import_single_page' ) ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$importer = new Leadwerk_Importer( true );
		$result   = $importer->run_page_by_source_key( $single_source_key );

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>Einzelimport abgeschlossen fuer <code>' . esc_html( $single_source_key ) . '</code>. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
		}
	}

	if ( $menu_book_run && check_admin_referer( 'leadwerk_import_menu_book' ) ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$importer = new Leadwerk_Importer( true );
		$result   = $importer->run_menu_book_only();

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>MenuBook-Import abgeschlossen. Nur die Online-Speisekarte im PageFlip-Buch wurde aktualisiert; andere Sektionen blieben unverändert. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
		}
	}

	if ( $gallery_run && check_admin_referer( 'leadwerk_import_gallery' ) ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$importer = new Leadwerk_Importer( true );
		$result   = $importer->run_gallery_only();

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>Galerie-Import abgeschlossen. Nur die Bildergalerie auf der Startseite wurde aktualisiert. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
		}
	}

	if ( $karriere_run && check_admin_referer( 'leadwerk_import_karriere' ) ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$importer = new Leadwerk_Importer( true );
		$result   = $importer->run_karriere_import();

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>Karriere-Import abgeschlossen. Seite und Formular wurden aktualisiert. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Leadwerk Import', 'leadwerk-importer' ); ?></h1>
		<p>Statische CaFEE-Inhalte (Pages, Medien, ACF) importieren. Quelle: Manifest + angegebener Quellordner.</p>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'run' => '1', 'dry_run' => '1' ), admin_url( 'admin.php?page=leadwerk-import' ) ), 'leadwerk_import_run' ) ); ?>" class="button">Dry-Run (keine Änderungen)</a>
			&nbsp;
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'run' => '1' ), admin_url( 'admin.php?page=leadwerk-import' ) ), 'leadwerk_import_run' ) ); ?>" class="button button-primary">Import ausführen</a>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=leadwerk-import' ) ); ?>" style="margin:1.5em 0;padding:1em;background:#fff;border:1px solid #ccd0d4;max-width:680px;">
			<?php wp_nonce_field( 'leadwerk_import_single_page' ); ?>
			<input type="hidden" name="leadwerk_import_single_page" value="1">
			<label for="leadwerk_source_key" style="display:block;font-weight:600;margin-bottom:.5em;">Einzelne Seite importieren</label>
			<select id="leadwerk_source_key" name="leadwerk_source_key" style="min-width:320px;max-width:100%;">
				<?php foreach ( $manifest_pages as $page_config ) : ?>
					<?php
					$page_source_key = sanitize_key( (string) ( $page_config['source_key'] ?? '' ) );
					if ( '' === $page_source_key ) {
						continue;
					}
					$page_title = (string) ( $page_config['title'] ?? $page_source_key );
					?>
					<option value="<?php echo esc_attr( $page_source_key ); ?>" <?php selected( $single_source_key, $page_source_key ); ?>>
						<?php echo esc_html( $page_title . ' (' . $page_source_key . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button" <?php disabled( empty( $manifest_pages ) ); ?>>Ausgewählte Seite importieren</button>
		<p class="description">Importiert oder aktualisiert nur die ausgewählte Manifest-Seite, inklusive Inhalt und SEO-Meta.</p>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=leadwerk-import' ) ); ?>" style="margin:1.5em 0;padding:1em;background:#fff;border:1px solid #ccd0d4;max-width:680px;">
		<?php wp_nonce_field( 'leadwerk_import_menu_book' ); ?>
		<input type="hidden" name="leadwerk_import_menu_book" value="1">
		<h2 style="margin-top:0;">Nur Online-Speisekarte importieren</h2>
		<p>Aktualisiert ausschließlich das PageFlip-MenuBook der Startseite anhand der aktuellen Speisekarte. Andere ACF-Sektionen, Seiten, Medien, Optionen und SEO-Felder werden nicht neu importiert.</p>
		<button type="submit" class="button button-primary">Nur MenuBook importieren</button>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=leadwerk-import' ) ); ?>" style="margin:1.5em 0;padding:1em;background:#fff;border:1px solid #ccd0d4;max-width:680px;">
		<?php wp_nonce_field( 'leadwerk_import_gallery' ); ?>
		<input type="hidden" name="leadwerk_import_gallery" value="1">
		<h2 style="margin-top:0;">Nur Galerie importieren</h2>
		<p>Aktualisiert ausschließlich die Bildergalerie auf der Startseite. Neue Bilder werden geladen und die Galerie aktualisiert. Andere Sektionen bleiben unverändert.</p>
		<button type="submit" class="button button-primary">Nur Galerie importieren</button>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=leadwerk-import' ) ); ?>" style="margin:1.5em 0;padding:1em;background:#fff;border:1px solid #ccd0d4;max-width:680px;">
		<?php wp_nonce_field( 'leadwerk_import_karriere' ); ?>
		<input type="hidden" name="leadwerk_import_karriere" value="1">
		<h2 style="margin-top:0;">Karriere-Seite importieren (Alle Neu)</h2>
		<p>Importiert die komplette Karriere-Seite inklusive Bilder. Erstellt außerdem automatisch das WPForms Formular für Bewerbungen mit Datei-Upload.</p>
		<button type="submit" class="button button-primary">Karriere-Seite importieren</button>
	</form>
	<?php if ( isset( $_GET['log'] ) ) : ?>
		<pre style="background:#f5f5f5;padding:1em;max-height:400px;overflow:auto;"><?php echo esc_html( get_option( 'leadwerk_import_log', '' ) ); ?></pre>
	<?php endif; ?>
	</div>
	<?php
}
