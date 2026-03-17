<?php
/**
 * Haupt-Importer: Manifest einlesen, Pages anlegen/aktualisieren, Medien importieren.
 *
 * @package Leadwerk_Importer
 */
class Leadwerk_Importer {

	protected $dry_run = true;
	protected $manifest = array();
	protected $manifest_dir = '';
	protected $source_root = '';
	protected $media_importer = null;

	public function __construct( $apply = false ) {
		$this->dry_run      = ! $apply;
		$this->manifest_dir = LEADWERK_IMPORTER_PATH . 'manifest/';
		$this->load_manifest();
		$this->source_root = $this->manifest['source_root'] ?? '';
		if ( $this->source_root === '' && defined( 'LEADWERK_IMPORT_SOURCE_ROOT' ) ) {
			$this->source_root = LEADWERK_IMPORT_SOURCE_ROOT;
		}
		// Ohne lokalen Zugriff: gebündelte source_assets im Plugin nutzen (mitgelieferte Bilder/Medien).
		if ( $this->source_root === '' || ! is_dir( $this->source_root ) ) {
			$bundled = LEADWERK_IMPORTER_PATH . 'source_assets';
			if ( is_dir( $bundled ) ) {
				$this->source_root = $bundled;
				Leadwerk_Logger::log( 'Quellordner: Plugin-eigene source_assets (kein lokaler Pfad).' );
			}
		}
		$this->source_root = (string) apply_filters( 'leadwerk_import_source_root', $this->source_root );
		if ( $this->source_root !== '' && is_dir( $this->source_root ) ) {
			$this->media_importer = new Leadwerk_Media_Importer( $this->source_root, $this->dry_run );
		}
	}

	protected function load_manifest() {
		$path = $this->manifest_dir . 'mapping.json';
		if ( ! is_file( $path ) ) {
			Leadwerk_Logger::log( 'Manifest nicht gefunden: ' . $path );
			$this->manifest = array( 'pages' => array() );
			return;
		}
		$json = file_get_contents( $path );
		$data = json_decode( $json, true );
		$this->manifest = is_array( $data ) ? $data : array( 'pages' => array() );
		Leadwerk_Logger::log( 'Manifest geladen: ' . count( $this->manifest['pages'] ?? array() ) . ' Seiten' );
	}

	public function run() {
		// Max. Ausführungszeit für Medienimport (Thumbnails/Imagick) anheben.
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}
		Leadwerk_Logger::log( $this->dry_run ? '--- Dry-Run ---' : '--- Import (Apply) ---' );
		$pages = $this->manifest['pages'] ?? array();
		foreach ( $pages as $page_config ) {
			$this->process_page( $page_config );
		}
		// Medien aus source_assets auflisten (Dry-Run) bzw. in Mediathek importieren (Apply).
		$this->run_media_import();
		// Startseite: ACF home_sections aus index.html befüllen (nur bei Apply).
		if ( ! $this->dry_run && $this->source_root !== '' ) {
			$front_page_id = $this->find_page_by_source_key( 'cafee-home-v1' );
			if ( $front_page_id ) {
				$filler = new Leadwerk_ACF_Filler();
				$filler->fill_front_page( $front_page_id, $this->source_root );
			}
		}
		Leadwerk_Logger::save();
	}

	/**
	 * Durchsucht source_assets rekursiv und listet Dateien auf (Dry-Run) bzw. importiert sie (Apply).
	 */
	protected function run_media_import() {
		if ( $this->source_root === '' || ! is_dir( $this->source_root ) ) {
			return;
		}
		$files = $this->collect_media_files( $this->source_root, '' );
		$count = count( $files );
		Leadwerk_Logger::log( "Medien in source_assets: $count Datei(en)" );
		if ( $count === 0 ) {
			return;
		}
		foreach ( $files as $relative_path ) {
			if ( $this->media_importer ) {
				$this->media_importer->import_file( $relative_path );
			} else {
				Leadwerk_Logger::log( "Media would import: $relative_path" );
			}
		}
	}

	/**
	 * Sammelt Medien-Dateien rekursiv (relativer Pfad zum source_root).
	 *
	 * @param string $dir   Absoluter Verzeichnispfad.
	 * @param string $base  Relativer Basis-Pfad (für Rekursion).
	 * @return array Liste relativer Pfade (z. B. images/logo.svg, images/Team/Fee.jpg).
	 */
	protected function collect_media_files( $dir, $base ) {
		$allowed = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'webm', 'mov', 'mp3', 'wav', 'ico', 'woff', 'woff2' );
		$out     = array();
		if ( ! is_dir( $dir ) ) {
			return $out;
		}
		$items = @scandir( $dir );
		if ( ! is_array( $items ) ) {
			return $out;
		}
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			$full = $dir . DIRECTORY_SEPARATOR . $item;
			$rel  = $base === '' ? $item : $base . '/' . $item;
			if ( is_dir( $full ) ) {
				$out = array_merge( $out, $this->collect_media_files( $full, $rel ) );
			} elseif ( is_file( $full ) ) {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( in_array( $ext, $allowed, true ) ) {
					$out[] = $rel;
				}
			}
		}
		return $out;
	}

	protected function process_page( $config ) {
		$source_key = $config['source_key'] ?? '';
		if ( $source_key === '' ) {
			Leadwerk_Logger::log( 'Page ohne source_key übersprungen' );
			return;
		}
		$existing = $this->find_page_by_source_key( $source_key );
		$title    = $config['title'] ?? 'Untitled';
		$slug     = $config['slug'] ?? sanitize_title( $title );
		$status   = $config['post_status'] ?? 'publish';
		$content  = '';
		if ( ! empty( $config['content_file'] ) ) {
			$content_path = $this->manifest_dir . $config['content_file'];
			if ( is_file( $content_path ) ) {
				$content = file_get_contents( $content_path );
			}
		}
		$post_data = array(
			'post_type'    => $config['target_type'] ?? 'page',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => $status,
			'post_content' => $content,
		);
		if ( $existing ) {
			$post_data['ID'] = $existing;
			if ( ! $this->dry_run ) {
				wp_update_post( $post_data );
				Leadwerk_Logger::log( "Page aktualisiert: $title (ID $existing)" );
			} else {
				Leadwerk_Logger::log( "Page würde aktualisiert: $title (ID $existing)" );
			}
		} else {
			if ( ! $this->dry_run ) {
				$id = wp_insert_post( $post_data );
				if ( $id && ! is_wp_error( $id ) ) {
					update_post_meta( $id, 'leadwerk_source_key', $source_key );
					Leadwerk_Logger::log( "Page angelegt: $title (ID $id)" );
					$existing = $id;
				} else {
					Leadwerk_Logger::log( "Fehler beim Anlegen: $title" );
					return;
				}
			} else {
				Leadwerk_Logger::log( "Page würde angelegt: $title (Slug: $slug)" );
				return;
			}
		}
		if ( ! empty( $config['is_front_page'] ) && $existing && ! $this->dry_run ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $existing );
			Leadwerk_Logger::log( "Startseite gesetzt: ID $existing" );
		}
	}

	protected function find_page_by_source_key( $source_key ) {
		$q = new WP_Query( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => 'leadwerk_source_key',
			'meta_value'     => $source_key,
			'fields'         => 'ids',
			'posts_per_page' => 1,
		) );
		$ids = $q->get_posts();
		return ! empty( $ids ) ? (int) $ids[0] : 0;
	}
}
