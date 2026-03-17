<?php
/**
 * ACF-Befüllung der Startseite aus index.html (Hero, Story, Menu, Experience, Interviews, Team, Reservation).
 *
 * @package Leadwerk_Importer
 */
class Leadwerk_ACF_Filler {

	protected $source_root = '';
	protected $attachment_cache = array();

	/**
	 * Attachment-ID anhand des Quellpfads ermitteln (Meta leadwerk_source_path, Fallback: Dateiname).
	 */
	public function get_attachment_id_by_source( $path ) {
		$norm = $this->normalize_path( $path );
		if ( isset( $this->attachment_cache[ $norm ] ) ) {
			return $this->attachment_cache[ $norm ];
		}
		$id = 0;
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
		} else {
			// Fallback: nach Dateiname suchen (für vor dem Fix importierte Medien ohne Meta).
			$basename = wp_basename( $path );
			$args = array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'posts_per_page' => 1,
				'meta_query'  => array(
					array( 'key' => '_wp_attached_file', 'value' => $basename, 'compare' => 'LIKE' ),
				),
			);
			$q2 = new WP_Query( $args );
			$ids2 = $q2->get_posts();
			if ( ! empty( $ids2 ) ) {
				$id = (int) $ids2[0];
				update_post_meta( $id, 'leadwerk_source_path', $norm );
			}
		}
		$this->attachment_cache[ $norm ] = $id;
		return $id;
	}

	protected function normalize_path( $path ) {
		$path = str_replace( array( '\\', '//' ), array( '/', '/' ), $path );
		return trim( trim( $path ), '/' );
	}

	/**
	 * Startseite mit ACF home_sections befüllen (nur wenn ACF aktiv und Post existiert).
	 */
	public function fill_front_page( $post_id, $source_root ) {
		if ( ! function_exists( 'update_field' ) || ! $post_id ) {
			Leadwerk_Logger::log( 'ACF-Befüllung übersprungen (ACF nicht aktiv oder keine Post-ID).' );
			return false;
		}
		$this->source_root = rtrim( $source_root, '/\\' );
		$index_path        = $this->source_root . DIRECTORY_SEPARATOR . 'index.html';
		if ( ! is_file( $index_path ) ) {
			Leadwerk_Logger::log( 'index.html nicht gefunden: ' . $index_path );
			return false;
		}
		$html  = file_get_contents( $index_path );
		$sections = $this->build_home_sections_from_html( $html );
		if ( empty( $sections ) ) {
			Leadwerk_Logger::log( 'Keine Sektionen aus index.html extrahiert.' );
			return false;
		}
		update_field( 'home_sections', $sections, $post_id );
		Leadwerk_Logger::log( 'ACF home_sections befüllt: ' . count( $sections ) . ' Layout(s) für Startseite (ID ' . $post_id . ').' );
		return true;
	}

	/**
	 * HTML parsen und ACF-konformes Array für Flexible Content home_sections bauen.
	 */
	protected function build_home_sections_from_html( $html ) {
		$sections = array();
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		$xpath = new DOMXPath( $dom );

		// Hero
		$hero = $this->xpath_section( $xpath, 'home' );
		if ( $hero ) {
			$sections[] = array(
				'acf_fc_layout'   => 'hero',
				'badge_text'      => $this->text( $xpath, './/div[contains(@class,"hero-badge")]/span', $hero ),
				'title_line_1'    => $this->text( $xpath, './/h1[contains(@class,"hero-title")]/span[contains(@class,"title-line")][1]', $hero ),
				'title_line_2_accent' => $this->text( $xpath, './/h1[contains(@class,"hero-title")]/span[contains(@class,"accent")]', $hero ),
				'subtitle'        => $this->text( $xpath, './/p[contains(@class,"hero-subtitle")]', $hero ),
				'button_1_text'   => $this->text( $xpath, './/div[contains(@class,"hero-buttons")]/a[1]//span[contains(@class,"btn-text")]', $hero ) ?: 'Tisch reservieren',
				'button_1_url'    => $this->attr( $xpath, './/div[contains(@class,"hero-buttons")]/a[1]', 'href', $hero ) ?: '#reservation',
				'button_2_text'   => $this->text( $xpath, './/div[contains(@class,"hero-buttons")]/a[2]', $hero ) ?: 'Speisekarte entdecken',
				'button_2_url'    => $this->attr( $xpath, './/div[contains(@class,"hero-buttons")]/a[2]', 'href', $hero ) ?: '#menu',
			);
		}

		// Story
		$story = $this->xpath_section( $xpath, 'story' );
		if ( $story ) {
			$video_src = $this->attr( $xpath, './/video/source', 'src', $story );
			$video_id  = $video_src ? $this->get_attachment_id_by_source( $video_src ) : 0;
			$content   = '';
			$ps = $xpath->query( './/div[contains(@class,"story-text")]//p', $story );
			foreach ( $ps as $p ) {
				$content .= $dom->saveHTML( $p );
			}
			$cta_node = $xpath->query( './/div[contains(@class,"story-cta")]/a', $story )->item( 0 );
			$cta_text = $cta_node ? trim( preg_replace( '/<svg.*?<\/svg>/s', '', $dom->saveHTML( $cta_node ) ) ) : '';
			$cta_text = wp_strip_all_tags( $cta_text );
			$sections[] = array(
				'acf_fc_layout'   => 'story',
				'video'           => $video_id,
				'headline_prefix' => $this->text( $xpath, './/h2[contains(@class,"story-title")]/span[1]', $story ),
				'headline_accent' => $this->text( $xpath, './/h2[contains(@class,"story-title")]/span[contains(@class,"title-accent")]', $story ),
				'content'         => $content,
				'cta_text'        => $cta_text ?: 'Mehr über unser Café erfahren',
				'cta_url'         => $cta_node ? $this->attr_node( $cta_node, 'href' ) : '#experience',
			);
		}

		// Menu Preview
		$menu = $this->xpath_section( $xpath, 'menu' );
		if ( $menu ) {
			$menu_categories = array();
			$menu_quote = '';
			$menu_quote_image = 0;
			$spreads = $xpath->query( './/div[contains(@class,"book-spread")]', $menu );
			foreach ( $spreads as $spread ) {
				$pages = $xpath->query( './/div[contains(@class,"book-page")]', $spread );
				foreach ( $pages as $page ) {
					$quote_el = $xpath->query( './/p[contains(@class,"page-quote")]', $page )->item( 0 );
					if ( $quote_el ) {
						$menu_quote = trim( $quote_el->textContent );
						$quote_img_src = $this->attr( $xpath, './/div[contains(@class,"page-image")]//img', 'src', $page );
						$menu_quote_image = $quote_img_src ? $this->get_attachment_id_by_source( $quote_img_src ) : 0;
						continue;
					}
					$cat_title = $this->text( $xpath, './/div[contains(@class,"page-header")]/h3', $page );
					$items = array();
					$menu_items = $xpath->query( './/div[contains(@class,"menu-item")]', $page );
					foreach ( $menu_items as $item ) {
						$name = $this->text( $xpath, './/span[contains(@class,"item-name")]', $item );
						if ( $name === '' ) continue;
						$items[] = array(
							'name'        => $name,
							'price'       => $this->text( $xpath, './/span[contains(@class,"item-price")]', $item ),
							'description' => $this->text( $xpath, './/p[contains(@class,"item-desc")]', $item ),
							'featured'    => false,
						);
					}
					if ( $cat_title || ! empty( $items ) ) {
						$menu_categories[] = array( 'category_title' => $cat_title, 'items' => $items );
					}
				}
			}
			$cover_logo_src = $this->attr( $xpath, './/div[contains(@class,"book-cover")]//img', 'src', $menu );
			$pdf_href = $this->attr( $xpath, './/div[contains(@class,"menu-download")]//a', 'href', $menu );
			$pdf_path = ( $pdf_href && strpos( $pdf_href, 'Speisekarte' ) !== false ) ? 'Speisekarte.pdf' : $pdf_href;
			$sections[] = array(
				'acf_fc_layout'            => 'menu_preview',
				'section_badge'            => $this->text( $xpath, './/div[contains(@class,"menu-header")]//div[contains(@class,"section-badge")]', $menu ),
				'section_title_display'    => $this->text( $xpath, './/div[contains(@class,"menu-header")]//span[contains(@class,"title-display")]', $menu ),
				'section_title_script'     => $this->text( $xpath, './/div[contains(@class,"menu-header")]//span[contains(@class,"title-script")]', $menu ),
				'section_subtitle'         => $this->text( $xpath, './/div[contains(@class,"menu-header")]//p[contains(@class,"section-subtitle")]', $menu ),
				'menu_book_cover_logo'     => $cover_logo_src ? $this->get_attachment_id_by_source( $cover_logo_src ) : 0,
				'menu_book_cover_title'    => $this->text( $xpath, './/div[contains(@class,"book-cover")]//h3', $menu ),
				'menu_book_cover_subtitle' => $this->text( $xpath, './/div[contains(@class,"book-cover")]//p', $menu ),
				'menu_categories'         => $menu_categories,
				'menu_quote'               => $menu_quote,
				'menu_quote_image'         => $menu_quote_image,
				'pdf_download'             => $pdf_path ? $this->get_attachment_id_by_source( $pdf_path ) : 0,
			);
		}

		// Experience
		$exp = $this->xpath_section( $xpath, 'experience' );
		if ( $exp ) {
			$cards = array();
			$card_nodes = $xpath->query( './/div[contains(@class,"experience-card")]', $exp );
			foreach ( $card_nodes as $card ) {
				$img = $xpath->query( './/div[contains(@class,"card-icon")]/img', $card )->item( 0 );
				$icon_id = 0;
				if ( $img && $img->hasAttribute( 'src' ) ) {
					$icon_id = $this->get_attachment_id_by_source( $img->getAttribute( 'src' ) );
				}
				$cards[] = array(
					'icon'  => $icon_id,
					'title' => $this->text( $xpath, './/h3', $card ),
					'text'  => $this->text( $xpath, './/p', $card ),
				);
			}
			$gallery = array();
			$large_img = $this->attr( $xpath, './/div[contains(@class,"insta-large")]//img', 'src', $exp );
			if ( $large_img ) {
				$gallery[] = $this->get_attachment_id_by_source( $large_img );
			}
			$grid_imgs = $xpath->query( './/div[contains(@class,"insta-grid")]//img', $exp );
			foreach ( $grid_imgs as $img ) {
				if ( $img->hasAttribute( 'src' ) ) {
					$id = $this->get_attachment_id_by_source( $img->getAttribute( 'src' ) );
					if ( $id ) $gallery[] = $id;
				}
			}
			$sections[] = array(
				'acf_fc_layout'         => 'experience',
				'section_badge'         => $this->text( $xpath, './/div[contains(@class,"experience-header")]//div[contains(@class,"section-badge")]', $exp ),
				'section_title_display' => $this->text( $xpath, './/div[contains(@class,"experience-header")]//span[contains(@class,"title-display")]', $exp ),
				'section_title_script'  => $this->text( $xpath, './/div[contains(@class,"experience-header")]//span[contains(@class,"title-script")]', $exp ),
				'experience_cards'      => $cards,
				'gallery'               => $gallery,
				'instagram_url'         => $this->attr( $xpath, './/a[contains(@class,"insta-btn")]', 'href', $exp ),
			);
		}

		// Interviews
		$int = $this->xpath_section( $xpath, 'interviews' );
		if ( $int ) {
			$slides = array();
			$slide_nodes = $xpath->query( './/div[contains(@class,"interview-slide")]', $int );
			foreach ( $slide_nodes as $slide ) {
				$vid_src = $this->attr( $xpath, './/video/source', 'src', $slide );
				$slides[] = array(
					'video'          => $vid_src ? $this->get_attachment_id_by_source( $vid_src ) : 0,
					'quote'          => $this->text( $xpath, './/p[contains(@class,"interview-quote")]', $slide ),
					'author_name'    => $this->text( $xpath, './/span[contains(@class,"interview-name")]', $slide ),
					'author_detail'  => $this->text( $xpath, './/span[contains(@class,"interview-detail")]', $slide ),
				);
			}
			$sections[] = array(
				'acf_fc_layout'         => 'interviews',
				'section_badge'         => $this->text( $xpath, './/div[contains(@class,"interviews-header")]//div[contains(@class,"section-badge")]', $int ),
				'section_title_display' => $this->text( $xpath, './/div[contains(@class,"interviews-header")]//span[contains(@class,"title-display")]', $int ),
				'section_title_script'  => $this->text( $xpath, './/div[contains(@class,"interviews-header")]//span[contains(@class,"title-script")]', $int ),
				'slides'                => $slides,
			);
		}

		// Team
		$team = $this->xpath_section( $xpath, 'team' );
		if ( $team ) {
			$members = array();
			$card_nodes = $xpath->query( './/div[contains(@class,"team-card")]', $team );
			foreach ( $card_nodes as $card ) {
				$img = $xpath->query( './/div[contains(@class,"card-image")]//img', $card )->item( 0 );
				$img_id = 0;
				if ( $img && $img->hasAttribute( 'src' ) ) {
					$img_id = $this->get_attachment_id_by_source( $img->getAttribute( 'src' ) );
				}
				$name = $this->text( $xpath, './/div[contains(@class,"card-content")]/h3', $card );
				if ( $name === '' && $img_id === 0 ) continue;
				$members[] = array(
					'image' => $img_id,
					'name'  => $name,
					'role'  => $this->text( $xpath, './/div[contains(@class,"card-content")]/span[contains(@class,"role")]', $card ),
				);
			}
			$sections[] = array(
				'acf_fc_layout'         => 'team',
				'section_badge'         => $this->text( $xpath, './/div[contains(@class,"team-header")]//div[contains(@class,"section-badge")]', $team ),
				'section_title_display' => $this->text( $xpath, './/div[contains(@class,"team-header")]//span[contains(@class,"title-display")]', $team ),
				'section_title_script'  => $this->text( $xpath, './/div[contains(@class,"team-header")]//span[contains(@class,"title-script")]', $team ),
				'section_subtitle'      => $this->text( $xpath, './/div[contains(@class,"team-header")]//p[contains(@class,"section-subtitle")]', $team ),
				'team_members'          => $members,
			);
		}

		// Reservation
		$res = $this->xpath_section( $xpath, 'reservation' );
		if ( $res ) {
			$addr_node = $xpath->query( './/div[contains(@class,"reservation-info")]//div[.//strong[contains(text(),"Adresse")]]/span', $res )->item( 0 );
			$addr_raw  = $addr_node ? $addr_node->textContent : '';
			$addr_parts = preg_split( '/,\s*|\n/', $addr_raw, 2 );
			$street = isset( $addr_parts[0] ) ? trim( $addr_parts[0] ) : 'Brückenstraße 12';
			$city   = isset( $addr_parts[1] ) ? trim( $addr_parts[1] ) : '12345 Musterstadt';
			$sections[] = array(
				'acf_fc_layout'           => 'reservation',
				'section_badge'           => $this->text( $xpath, './/div[contains(@class,"section-badge")]', $res ),
				'title_line_1'            => $this->text( $xpath, './/h2[contains(@class,"reservation-title")]/span[1]', $res ),
				'title_line_2_accent'     => $this->text( $xpath, './/h2[contains(@class,"reservation-title")]/span[contains(@class,"title-accent")]', $res ),
				'intro_text'             => $this->text( $xpath, './/p[contains(@class,"reservation-text")]', $res ),
				'address_block'           => array( 'street' => $street, 'city' => $city ),
				'opening_hours'           => $this->text( $xpath, './/div[.//strong[contains(text(),"Öffnungszeiten")]]/span', $res ),
				'phone'                  => $this->text( $xpath, './/div[.//strong[contains(text(),"Telefon")]]/span', $res ),
				'email'                  => $this->attr( $xpath, './/a[contains(@href,"mailto:")]', 'href', $res ),
				'form_alias'             => 'reservation_contact',
				'button_phone_label'     => 'Jetzt anrufen',
				'button_email_label'     => 'E-Mail schreiben',
			);
			if ( $sections[ count( $sections ) - 1 ]['email'] ) {
				$sections[ count( $sections ) - 1 ]['email'] = str_replace( 'mailto:', '', $sections[ count( $sections ) - 1 ]['email'] );
			}
		}

		return $sections;
	}

	protected function xpath_section( DOMXPath $xpath, $id ) {
		$nodes = $xpath->query( "//section[@id='" . $id . "']" );
		return $nodes->length > 0 ? $nodes->item( 0 ) : null;
	}

	protected function text( DOMXPath $xpath, $expr, $context ) {
		$nodes = $xpath->query( $expr, $context );
		if ( $nodes->length === 0 ) return '';
		return trim( $nodes->item( 0 )->textContent );
	}

	protected function attr( DOMXPath $xpath, $expr, $attr, $context ) {
		$nodes = $xpath->query( $expr, $context );
		if ( $nodes->length === 0 ) return '';
		return $this->attr_node( $nodes->item( 0 ), $attr );
	}

	protected function attr_node( DOMNode $node, $attr ) {
		if ( ! $node->hasAttribute( $attr ) ) return '';
		return trim( $node->getAttribute( $attr ) );
	}
}
