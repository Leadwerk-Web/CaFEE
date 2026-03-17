<?php
/**
 * Medienimport: Dateien als Attachments anlegen, Deduplizierung über Pfad/Meta.
 *
 * @package Leadwerk_Importer
 */
class Leadwerk_Media_Importer {

	protected $source_root = '';
	protected $attachment_map = array(); // source_path => attachment_id
	protected $dry_run = false;

	public function __construct( $source_root, $dry_run = false ) {
		$this->source_root = rtrim( $source_root, '/\\' );
		$this->dry_run     = $dry_run;
	}

	/**
	 * Importiert eine Datei und gibt Attachment-ID zurück. Bei Duplikat vorhandene ID.
	 *
	 * @param string $relative_path Pfad relativ zu source_root (z. B. images/logo.svg).
	 * @return int 0 bei Fehler oder Dry-Run ohne Schreiben, sonst attachment_id.
	 */
	public function import_file( $relative_path ) {
		$full_path = $this->source_root . DIRECTORY_SEPARATOR . str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $relative_path );
		if ( ! is_file( $full_path ) ) {
			Leadwerk_Logger::log( "Media skip (missing): $relative_path" );
			return 0;
		}
		$norm = $this->normalize_path( $relative_path );
		if ( isset( $this->attachment_map[ $norm ] ) ) {
			return (int) $this->attachment_map[ $norm ];
		}
		// Bereits importiert? (Re-Import: vorhandenes Attachment nutzen.)
		$existing = $this->find_attachment_by_source_path( $norm );
		if ( $existing ) {
			$this->attachment_map[ $norm ] = $existing;
			Leadwerk_Logger::log( "Media bereits vorhanden: $relative_path => $existing" );
			return (int) $existing;
		}
		if ( $this->dry_run ) {
			Leadwerk_Logger::log( "Media would import: $relative_path" );
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = wp_tempnam( wp_basename( $full_path ) );
		copy( $full_path, $tmp );
		$file_array = array(
			'name'     => wp_basename( $full_path ),
			'tmp_name' => $tmp,
		);
		$id = media_handle_sideload( $file_array, 0, null );
		if ( file_exists( $tmp ) ) {
			@unlink( $tmp );
		}
		if ( is_wp_error( $id ) ) {
			Leadwerk_Logger::log( "Media error $relative_path: " . $id->get_error_message() );
			return 0;
		}
		// Meta setzen, damit ACF-Filler Attachments per Quellpfad finden kann.
		update_post_meta( $id, 'leadwerk_source_path', $norm );
		$this->attachment_map[ $norm ] = $id;
		Leadwerk_Logger::log( "Media imported: $relative_path => $id" );
		return (int) $id;
	}

	public function get_attachment_id_by_source( $relative_path ) {
		$norm = $this->normalize_path( $relative_path );
		return isset( $this->attachment_map[ $norm ] ) ? (int) $this->attachment_map[ $norm ] : 0;
	}

	protected function normalize_path( $path ) {
		return trim( str_replace( array( '\\', '//' ), array( '/', '/' ), $path ), '/' );
	}

	/**
	 * Attachment-ID anhand leadwerk_source_path finden (für Re-Import / Lookup).
	 */
	protected function find_attachment_by_source_path( $norm ) {
		$q = new WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'meta_key'       => 'leadwerk_source_path',
			'meta_value'     => $norm,
			'fields'         => 'ids',
			'posts_per_page' => 1,
		) );
		$ids = $q->get_posts();
		return ! empty( $ids ) ? (int) $ids[0] : 0;
	}
}
