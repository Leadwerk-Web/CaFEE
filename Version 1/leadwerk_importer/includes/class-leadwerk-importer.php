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
		if ( ! $this->dry_run ) {
			$this->apply_site_identity();
		}
		$pages = $this->manifest['pages'] ?? array();
		foreach ( $pages as $page_config ) {
			$this->process_page( $page_config );
		}
		// Medien aus source_assets auflisten (Dry-Run) bzw. in Mediathek importieren (Apply).
		$this->run_media_import();
		// Favicon (site_icon) setzen.
		if ( ! $this->dry_run && $this->media_importer ) {
			$this->set_site_icon();
		}
		// Startseite: ACF home_sections aus index.html befüllen (nur bei Apply).
		if ( ! $this->dry_run && $this->source_root !== '' ) {
			$front_page_id = $this->find_page_by_source_key( 'cafee-home-v1' );
			if ( $front_page_id ) {
				$filler = new Leadwerk_ACF_Filler();
				$filler->fill_front_page( $front_page_id, $this->source_root );
			}
		}
		// ACF-Optionsseite befüllen (Logo, Kontakt, Social, Footer etc.).
		if ( ! $this->dry_run ) {
			$this->fill_acf_options();
			$this->create_wpforms_contact();
		}
		Leadwerk_Logger::save();
	}

	/**
	 * Import a single page from the manifest by source key.
	 *
	 * @param string $source_key Manifest source key.
	 * @return array<string,string>|WP_Error
	 */
	public function run_page_by_source_key( $source_key ) {
		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 300 );
		}

		$source_key  = sanitize_key( (string) $source_key );
		$page_config = array();

		foreach ( (array) ( $this->manifest['pages'] ?? array() ) as $candidate ) {
			$candidate = (array) $candidate;
			if ( $source_key === sanitize_key( (string) ( $candidate['source_key'] ?? '' ) ) ) {
				$page_config = $candidate;
				break;
			}
		}

		if ( empty( $page_config ) ) {
			return new WP_Error( 'leadwerk_page_not_found', 'Manifest enthaelt keine Seite fuer source_key ' . $source_key . '.' );
		}

		Leadwerk_Logger::log( $this->dry_run ? '--- Targeted Dry-Run: ' . $source_key . ' ---' : '--- Targeted Import: ' . $source_key . ' ---' );
		$this->process_page( $page_config );
		Leadwerk_Logger::save();

		return array(
			'status'     => 'completed',
			'source_key' => $source_key,
		);
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
		if ( $existing && ! $this->dry_run && ! empty( $config['seo'] ) ) {
			$this->apply_seo_meta( $existing, $config['seo'], $config );
		}
	}

	/**
	 * Truncate SEO title for Yoast width hints (uses theme helper when active).
	 *
	 * @param string $title      Title.
	 * @param int    $max_chars  Max length.
	 * @return string
	 */
	protected function truncate_seo_title_for_yoast( $title, $max_chars = 58 ) {
		if ( function_exists( 'leadwerk_theme_truncate_seo_title_for_yoast' ) ) {
			return leadwerk_theme_truncate_seo_title_for_yoast( $title, $max_chars );
		}

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
	 * Refresh Yoast indexables after SEO meta import.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	protected function maybe_rebuild_yoast_indexable( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 || $this->dry_run ) {
			return;
		}

		if ( function_exists( 'leadwerk_theme_rebuild_yoast_post_indexable' ) ) {
			leadwerk_theme_rebuild_yoast_post_indexable( $post_id );
			return;
		}

		if ( ! function_exists( 'YoastSEO' ) || ! class_exists( '\Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher', false ) ) {
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
	 * Resolve focus keyphrase from manifest-style keys (primary + English fallback).
	 *
	 * @param array<string,mixed> $seo SEO config.
	 * @return string
	 */
	protected function resolve_seo_focus_keyphrase( array $seo ) {
		$kw = isset( $seo['focus_keyphrase'] ) ? trim( (string) $seo['focus_keyphrase'] ) : '';
		if ( '' !== $kw ) {
			return $kw;
		}
		if ( ! empty( $seo['focus_keyphrase_en'] ) ) {
			return trim( (string) $seo['focus_keyphrase_en'] );
		}

		return '';
	}

	/**
	 * Yoast SEO Meta-Felder auf eine Seite schreiben.
	 * Funktioniert auch ohne Yoast (reines Post-Meta) – Yoast liest diese Felder automatisch.
	 */
	protected function apply_seo_meta( $post_id, $seo, $config = array() ) {
		unset( $config );
		$fields_written = array();

		if ( ! empty( $seo['title'] ) ) {
			$seo_title = $this->truncate_seo_title_for_yoast( (string) $seo['title'] );
			update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( $seo_title ) );
			$fields_written[] = 'title';
		}

		if ( ! empty( $seo['meta_description'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( $seo['meta_description'] ) );
			$fields_written[] = 'metadesc';
		}

		$focus_kw = $this->resolve_seo_focus_keyphrase( $seo );
		if ( '' !== $focus_kw ) {
			update_post_meta( $post_id, '_yoast_wpseo_focuskw', sanitize_text_field( $focus_kw ) );
			$fields_written[] = 'focuskw';
		}

		if ( array_key_exists( 'meta_robots', $seo ) ) {
			$robots         = sanitize_text_field( (string) $seo['meta_robots'] );
			$should_noindex = false !== strpos( $robots, 'noindex' );
			$should_nofollow = false !== strpos( $robots, 'nofollow' );

			if ( $should_noindex ) {
				update_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
				$fields_written[] = 'noindex';
			} else {
				delete_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex' );
				$fields_written[] = 'index';
			}

			if ( $should_nofollow ) {
				update_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', '1' );
				$fields_written[] = 'nofollow';
			} else {
				delete_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow' );
				$fields_written[] = 'follow';
			}
		}

		// Open Graph
		if ( ! empty( $seo['og_title'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_opengraph-title', sanitize_text_field( $seo['og_title'] ) );
			$fields_written[] = 'og:title';
		}
		if ( ! empty( $seo['og_description'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_opengraph-description', sanitize_text_field( $seo['og_description'] ) );
			$fields_written[] = 'og:description';
		}
		if ( ! empty( $seo['og_image_source'] ) && $this->media_importer ) {
			$og_img_id = $this->media_importer->get_attachment_id_by_source( $seo['og_image_source'] );
			if ( ! $og_img_id ) {
				$filler = new Leadwerk_ACF_Filler();
				$og_img_id = $filler->get_attachment_id_by_source( $seo['og_image_source'] );
			}
			if ( $og_img_id ) {
				$og_url = wp_get_attachment_url( $og_img_id );
				if ( $og_url ) {
					update_post_meta( $post_id, '_yoast_wpseo_opengraph-image', $og_url );
					update_post_meta( $post_id, '_yoast_wpseo_opengraph-image-id', $og_img_id );
					$fields_written[] = 'og:image';
				}
			}
		}

		// Twitter/X Cards (gleiche Werte wie OG falls nicht separat definiert)
		$tw_title = ! empty( $seo['twitter_title'] ) ? $seo['twitter_title'] : ( $seo['og_title'] ?? '' );
		$tw_desc  = ! empty( $seo['twitter_description'] ) ? $seo['twitter_description'] : ( $seo['og_description'] ?? '' );
		if ( $tw_title ) {
			update_post_meta( $post_id, '_yoast_wpseo_twitter-title', sanitize_text_field( $tw_title ) );
			$fields_written[] = 'twitter:title';
		}
		if ( $tw_desc ) {
			update_post_meta( $post_id, '_yoast_wpseo_twitter-description', sanitize_text_field( $tw_desc ) );
			$fields_written[] = 'twitter:description';
		}

		// Schema-Typ (Yoast Schema: page_type / article_type)
		if ( ! empty( $seo['schema_type'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_schema_page_type', sanitize_text_field( $seo['schema_type'] ) );
			$fields_written[] = 'schema:' . $seo['schema_type'];
		}

		if ( ! empty( $fields_written ) ) {
			Leadwerk_Logger::log( "SEO-Meta für ID $post_id: " . implode( ', ', $fields_written ) );
		}

		$this->maybe_rebuild_yoast_indexable( (int) $post_id );
	}

	/**
	 * ACF-Optionsseite befüllen: Logo, Kontakt, Öffnungszeiten, Social, Footer.
	 */
	protected function fill_acf_options() {
		if ( ! function_exists( 'update_field' ) ) {
			Leadwerk_Logger::log( 'ACF-Optionen übersprungen (ACF nicht aktiv).' );
			return;
		}
		$fields_set = array();

		// Logo (Header) – aus Mediathek per Quellpfad
		$logo_path = 'images/Logo CaFEE vektorisiert.svg';
		$logo_id   = $this->resolve_attachment( $logo_path );
		if ( $logo_id ) {
			update_field( 'logo', $logo_id, 'option' );
			$fields_set[] = 'logo=' . $logo_id;
		}

		// Footer-Logo (gleich wie Header-Logo, wenn nicht separat)
		if ( $logo_id ) {
			update_field( 'footer_logo', $logo_id, 'option' );
			$fields_set[] = 'footer_logo=' . $logo_id;
		}

		// Footer-Text
		update_field( 'footer_text', 'Ein magischer Ort für Kaffeeliebhaber. Wo Genuss Flügel hat.', 'option' );
		$fields_set[] = 'footer_text';

		// Copyright
		update_field( 'copyright_text', '© ' . date( 'Y' ) . ' CaFEE Brückenmühle. Alle Rechte vorbehalten.', 'option' );
		$fields_set[] = 'copyright_text';

		// Kontakt
		update_field( 'street', 'Hofstätte 2', 'option' );
		update_field( 'city', '76593 Gernsbach', 'option' );
		update_field( 'phone', '+49 151/103 100 59', 'option' );
		update_field( 'email', 'hallo@cafee-gernsbach.de', 'option' );
		$fields_set[] = 'kontakt';

		// Öffnungszeiten
		$hours = "Montag, Dienstag, Donnerstag – Sonntag: 9:00 – 17:00 Uhr\nMittwoch Ruhetag";
		update_field( 'opening_hours', $hours, 'option' );
		$fields_set[] = 'opening_hours';

		// Social Media
		update_field( 'instagram_url', 'https://www.instagram.com/cafeebrueckenmuehle/', 'option' );
		update_field( 'facebook_url', 'https://facebook.com', 'option' );
		$fields_set[] = 'social';

		Leadwerk_Logger::log( 'ACF-Optionen befüllt: ' . implode( ', ', $fields_set ) );
	}

	/**
	 * Attachment-ID über Medienimporter oder ACF-Filler-Fallback auflösen.
	 */
	protected function resolve_attachment( $source_path ) {
		if ( $this->media_importer ) {
			$id = $this->media_importer->get_attachment_id_by_source( $source_path );
			if ( $id ) {
				return $id;
			}
		}
		$filler = new Leadwerk_ACF_Filler();
		return $filler->get_attachment_id_by_source( $source_path );
	}

	/**
	 * WPForms-Kontaktformular erstellen (Name, E-Mail, Nachricht) und ID in ACF-Options speichern.
	 */
	protected function create_wpforms_contact() {
		if ( ! function_exists( 'wpforms' ) ) {
			Leadwerk_Logger::log( 'WPForms nicht installiert – Formular-Erstellung übersprungen.' );
			return;
		}
		$manifest_form_id = isset( $this->manifest['wpforms_reservation_form_id'] ) ? (int) $this->manifest['wpforms_reservation_form_id'] : 0;
		if ( $manifest_form_id > 0 ) {
			$post = get_post( $manifest_form_id );
			if ( $post && 'wpforms' === $post->post_type && in_array( $post->post_status, array( 'publish', 'draft' ), true ) ) {
				if ( function_exists( 'update_field' ) ) {
					update_field( 'wpforms_reservation_id', $manifest_form_id, 'option' );
					Leadwerk_Logger::log( "WPForms-ID aus Manifest übernommen: $manifest_form_id (kein neues Formular angelegt)" );
				}
				return;
			}
			Leadwerk_Logger::log( "Manifest-WPForms-ID $manifest_form_id: kein gültiger wpforms-Eintrag – Fallback: vorhandene Option oder Neuanlage." );
		}
		if ( function_exists( 'get_field' ) ) {
			$existing_id = (int) get_field( 'wpforms_reservation_id', 'option' );
			if ( $existing_id && get_post_status( $existing_id ) ) {
				Leadwerk_Logger::log( "WPForms-Formular bereits vorhanden: ID $existing_id" );
				return;
			}
		}
		$form_data = array(
			'fields' => array(
				'1' => array(
					'id'                => '1',
					'type'              => 'name',
					'label'             => 'Name',
					'format'            => 'first-last',
					'description'       => '',
					'required'          => '1',
					'size'              => 'large',
					'simple_placeholder' => '',
					'simple_default'   => '',
					'first_placeholder' => 'Vorname',
					'first_default'    => '',
					'middle_placeholder' => '',
					'middle_default'   => '',
					'last_placeholder' => 'Nachname',
					'last_default'     => '',
					'css'              => '',
				),
				'2' => array(
					'id'          => '2',
					'type'        => 'email',
					'label'       => 'E-Mail-Adresse',
					'required'    => '1',
					'size'        => 'large',
					'placeholder' => '',
				),
				'3' => array(
					'id'          => '3',
					'type'        => 'textarea',
					'label'       => 'Kommentar oder Nachricht',
					'required'    => '0',
					'size'        => 'large',
					'placeholder' => '',
				),
			),
			'settings' => array(
				'form_title'             => 'CaFEE Kontaktformular',
				'submit_text'            => 'Absenden',
				'submit_text_processing' => 'Wird gesendet …',
				'notification_enable'    => '1',
				'notifications'          => array(
					'1' => array(
						'email'          => '{admin_email}',
						'subject'        => 'Neue Nachricht von {field_id="1"} – CaFEE Brückenmühle',
						'sender_name'    => 'CaFEE Brückenmühle',
						'sender_address' => '{admin_email}',
						'replyto'        => '{field_id="2"}',
						'message'        => "Name: {field_id=\"1\"}\nE-Mail: {field_id=\"2\"}\n\nKommentar:\n{field_id=\"3\"}",
					),
				),
				'confirmations' => array(
					'1' => array(
						'type'           => 'redirect',
						'name'           => 'Standardbestätigung',
						'message'        => '',
						'message_scroll' => '1',
						'page'           => '0',
						'redirect'       => '/danke/',
					),
				),
				'antispam'    => '1',
				'form_class'  => 'contact-form',
			),
		);
		$form_id = wp_insert_post( array(
			'post_title'   => 'CaFEE Kontaktformular',
			'post_status'  => 'publish',
			'post_type'    => 'wpforms',
			'post_content' => wp_json_encode( $form_data ),
		) );
		if ( $form_id && ! is_wp_error( $form_id ) ) {
			if ( function_exists( 'update_field' ) ) {
				update_field( 'wpforms_reservation_id', $form_id, 'option' );
			}
			Leadwerk_Logger::log( "WPForms-Formular erstellt: ID $form_id (ACF-Option gesetzt)" );
		} else {
			Leadwerk_Logger::log( 'WPForms-Formular konnte nicht erstellt werden.' );
		}
	}

	/**
	 * WordPress Site-Titel und Tagline aus dem Manifest setzen.
	 */
	protected function apply_site_identity() {
		$site_title   = $this->manifest['site_title'] ?? '';
		$site_tagline = $this->manifest['site_tagline'] ?? '';
		if ( $site_title ) {
			update_option( 'blogname', $site_title );
			Leadwerk_Logger::log( "Site-Titel gesetzt: $site_title" );
		}
		if ( $site_tagline ) {
			update_option( 'blogdescription', $site_tagline );
			Leadwerk_Logger::log( "Site-Tagline gesetzt: $site_tagline" );
		}
	}

	/**
	 * Favicon als WordPress site_icon setzen (Attachment-ID aus Medienimport).
	 */
	protected function set_site_icon() {
		$favicon_path = 'images/Fee CaFEE_favicon_ohne Dampf.svg';
		$id = $this->media_importer->get_attachment_id_by_source( $favicon_path );
		if ( ! $id ) {
			$norm = trim( str_replace( array( '\\', '//' ), '/', $favicon_path ), '/' );
			$q = new WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'meta_key'       => 'leadwerk_source_path',
				'meta_value'     => $norm,
				'fields'         => 'ids',
				'posts_per_page' => 1,
			) );
			$ids = $q->get_posts();
			if ( ! empty( $ids ) ) {
				$id = (int) $ids[0];
			}
		}
		if ( $id ) {
			update_option( 'site_icon', $id );
			Leadwerk_Logger::log( "Favicon (site_icon) gesetzt: Attachment-ID $id" );
		} else {
			Leadwerk_Logger::log( 'Favicon: Attachment für Fee-SVG nicht gefunden.' );
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
