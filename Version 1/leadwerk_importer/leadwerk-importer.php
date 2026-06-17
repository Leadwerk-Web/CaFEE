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

function leadwerk_importer_admin_page() {
	$dry_run = isset( $_GET['dry_run'] ) && $_GET['dry_run'] === '1';
	$run     = isset( $_GET['run'] ) && $_GET['run'] === '1' && current_user_can( 'manage_options' );
	$import_404 = isset( $_GET['import_404'] ) && '1' === (string) $_GET['import_404'] && current_user_can( 'manage_options' );
	$not_found_source_key = '404-v1';
	if ( $run && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'leadwerk_import_run' ) ) {
		// Zeitlimit anheben: Thumbnail-Erzeugung (Imagick) kann bei vielen/großen Bildern > 30s dauern.
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}
		$importer = new Leadwerk_Importer( ! $dry_run );
		$importer->run();
		echo '<div class="notice notice-success"><p>Import ausgeführt. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
	}
	if ( $import_404 && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'leadwerk_import_404' ) ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$importer = new Leadwerk_Importer( true );
		$result   = $importer->run_page_by_source_key( $not_found_source_key );

		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>404 Import abgeschlossen fuer <code>' . esc_html( $not_found_source_key ) . '</code>. Siehe <a href="' . esc_url( admin_url( 'admin.php?page=leadwerk-import&log=1' ) ) . '">Log</a>.</p></div>';
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
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'import_404' => '1' ), admin_url( 'admin.php?page=leadwerk-import' ) ), 'leadwerk_import_404' ) ); ?>" class="button">Nur 404 importieren</a>
		</p>
		<?php if ( isset( $_GET['log'] ) ) : ?>
			<pre style="background:#f5f5f5;padding:1em;max-height:400px;overflow:auto;"><?php echo esc_html( get_option( 'leadwerk_import_log', '' ) ); ?></pre>
		<?php endif; ?>
	</div>
	<?php
}
