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
		// Unicode-Dashes (En-Dash, Em-Dash) zu normalem Bindestrich vereinheitlichen.
		$path = str_replace( array( "\xE2\x80\x93", "\xE2\x80\x94" ), '-', $path );
		return trim( $path, '/' );
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
	 * Nur die PageFlip-Speisekarte innerhalb der Startseiten-ACF-Daten aktualisieren.
	 * Andere home_sections und andere Felder des Menu-Layouts bleiben unverändert.
	 *
	 * @param int    $post_id     Page-ID.
	 * @param string $source_root  Quellordner.
	 * @return bool
	 */
	public function fill_menu_book_only( $post_id, $source_root = '' ) {
		if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_sub_field' ) || ! $post_id ) {
			Leadwerk_Logger::log( 'MenuBook-Import übersprungen (ACF nicht aktiv oder keine Post-ID).' );
			return false;
		}

		$this->source_root = rtrim( (string) $source_root, '/\\' );
		$sections          = get_field( 'home_sections', $post_id, false );
		if ( ! is_array( $sections ) || empty( $sections ) ) {
			Leadwerk_Logger::log( 'MenuBook-Import abgebrochen: home_sections nicht gefunden oder leer (ID ' . (int) $post_id . ').' );
			return false;
		}

		$menu_index = null;
		foreach ( $sections as $index => $section ) {
			if ( is_array( $section ) && isset( $section['acf_fc_layout'] ) && 'menu_preview' === $section['acf_fc_layout'] ) {
				$menu_index = $index;
				break;
			}
		}

		if ( null === $menu_index ) {
			Leadwerk_Logger::log( 'MenuBook-Import abgebrochen: menu_preview-Layout nicht gefunden (ID ' . (int) $post_id . ').' );
			return false;
		}

		$menu_book_pages = $this->build_current_menu_book_pages();
		$updated         = update_sub_field(
			array( 'home_sections', $menu_index + 1, 'menu_book_pages' ),
			$menu_book_pages,
			$post_id
		);

		$state = $updated ? 'aktualisiert' : 'abgeglichen (keine Datenbankänderung nötig oder Wert unverändert)';
		Leadwerk_Logger::log( 'Nur MenuBook ' . $state . ': ' . count( $menu_book_pages ) . ' PageFlip-Seite(n) für Startseite (ID ' . (int) $post_id . '). Andere Sektionen blieben unverändert.' );
		return true;
	}

	/**
	 * Eröffnungsseite mit ACF eroeffnung_sections befüllen.
	 *
	 * @param int    $post_id     Page-ID.
	 * @param string $source_root  Quellordner.
	 * @return bool
	 */
	public function fill_eroeffnung_page( $post_id, $source_root ) {
		if ( ! function_exists( 'update_field' ) || ! $post_id ) {
			Leadwerk_Logger::log( 'ACF-Eröffnungsbefüllung übersprungen (ACF nicht aktiv oder keine Post-ID).' );
			return false;
		}
		$this->source_root = rtrim( (string) $source_root, '/\\' );
		$sections = $this->build_eroeffnung_sections();
		update_field( 'eroeffnung_sections', $sections, $post_id );
		Leadwerk_Logger::log( 'ACF eroeffnung_sections befüllt: ' . count( $sections ) . ' Layout(s) für Eröffnung (ID ' . $post_id . ').' );
		return true;
	}

	/**
	 * ACF-konformes Array für die Eröffnungs-Landingpage bauen.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	protected function build_eroeffnung_sections() {
		return array(
			array(
				'acf_fc_layout'         => 'hero',
				'background_image'      => $this->get_attachment_id_by_source( 'images/Capuccino.webp' ),
				'badge_text'            => 'Neueröffnung · Gernsbach · 11. Juli',
				'title_eyebrow'         => 'Die Brückenmühle erwacht',
				'title_main'            => 'Wo Magie',
				'title_script'          => 'auf CaFEE trifft',
				'subtitle'              => "Jahrhundertealte Mauern, frisch gemahlene Bohnen und ein Funke Feenstaub.\nAm <strong>11. Juli</strong> öffnen wir zum ersten Mal unsere Türen – und du bist von der ersten Tasse an dabei.",
				'opening_date'          => '2026-07-11T09:00:00+02:00',
				'primary_button_text'   => '10% Rabatt sichern',
				'primary_button_url'    => '#rabatt',
				'secondary_button_text' => "So funktioniert's",
				'secondary_button_url'  => '#ablauf',
				'note_text'             => 'Exklusiv für unsere Gäste der ersten Stunde',
				'scroll_text'           => 'Die Geschichte beginnt',
			),
			array(
				'acf_fc_layout'         => 'story',
				'section_badge'         => 'Es ist so weit',
				'section_title_display' => 'Ein Ort, der seit',
				'section_title_script'  => '1300 wartet',
				'content'               => '<p><span class="drop-cap">A</span>n der alten Brücke über die Murg, wo einst das Wasser die schweren Mühlsteine drehte, liegt ein Ort voller Geschichten. Über 700 Jahre haben diese Mauern erlebt – und doch waren sie nie so lebendig wie jetzt.</p><p>Denn hier mahlen wir keine Körner mehr, sondern feinste Kaffeebohnen. Hier wird gebacken, gebrüht und gelacht. Und über allem wacht unsere Namensgeberin: die <strong>Fee</strong>, die aus einer alten Mühle einen magischen Treffpunkt zaubert.</p><p class="ero-story-highlight">Am <strong>11. Juli</strong> ist es so weit. Wir öffnen zum ersten Mal die Türen – und schreiben das erste Kapitel gemeinsam mit dir.</p>',
			),
			array(
				'acf_fc_layout'  => 'discount',
				'section_badge'  => 'Dein Eröffnungsgeschenk',
				'percent'        => '10%',
				'title'          => 'Rabatt zur Eröffnung',
				'lead_text'      => '<p>Als Dankeschön, dass du von Anfang an dabei bist, schenken wir dir <strong>10% auf deine Bestellung</strong> – einlösbar <strong>nur am Eröffnungstag, dem 11. Juli</strong>. Egal ob Cappuccino, Frühstück, Kuchen oder ein Gläschen zum Anstoßen.</p>',
				'checklist'      => array(
					array( 'text' => 'Gültig ausschließlich am 11. Juli (Eröffnungstag)' ),
					array( 'text' => 'Auf dem Handy speichern oder herunterladen' ),
					array( 'text' => 'Am 11. Juli im CaFEE vorzeigen – fertig' ),
				),
				'qr_image'       => $this->get_attachment_id_by_source( 'images/rabatt-qr-eroeffnung.png' ),
				'qr_ribbon'      => '-10%',
				'qr_kicker'      => 'Dein persönlicher Rabattcode',
				'qr_caption'     => 'Im CaFEE vorzeigen & 10% sparen',
				'qr_validity'    => 'Nur gültig am Eröffnungstag · Sa, 11. Juli',
				'download_label' => 'QR-Code herunterladen',
				'bookmark_label' => 'Als Lesezeichen speichern',
				'share_label'    => 'Freunden schenken:',
			),
			array(
				'acf_fc_layout'         => 'steps',
				'section_badge'         => 'In 3 Schritten',
				'section_title_display' => 'So sicherst du dir',
				'section_title_script'  => 'deinen Rabatt',
				'steps'                 => array(
					array( 'number' => '1', 'icon' => 'download', 'title' => 'Speichern', 'text' => 'Lade den QR-Code herunter oder speichere diese Seite als Lesezeichen auf deinem Handy.' ),
					array( 'number' => '2', 'icon' => 'user', 'title' => 'Vorbeikommen', 'text' => 'Besuche uns zur Eröffnung am 11. Juli in der Brückenmühle in Gernsbach.' ),
					array( 'number' => '3', 'icon' => 'wallet', 'title' => '10% sparen', 'text' => 'Zeig deinen QR-Code am 11. Juli an der Theke vor – und genieße 10% Rabatt auf deine Bestellung.' ),
				),
			),
			array(
				'acf_fc_layout'         => 'taste',
				'section_badge'         => 'Was dich erwartet',
				'section_title_display' => 'Genuss für',
				'section_title_script'  => 'jeden Moment',
				'section_subtitle'      => 'Von der ersten Tasse am Morgen bis zum Aperitif zum Feierabend – ein kleiner Vorgeschmack auf das, was in der Brückenmühle auf dich wartet.',
				'cards'                 => array(
					array( 'icon' => $this->get_attachment_id_by_source( 'images/kaffee-1.svg' ), 'title' => 'Kaffeespezialitäten', 'text' => 'Espresso, Cappuccino, Flat White, Matcha & Co. – frisch gemahlen, mit Liebe aufgeschäumt.' ),
					array( 'icon' => $this->get_attachment_id_by_source( 'images/breakfast-1.svg' ), 'title' => 'Frühstück & Stullen', 'text' => 'Traumhafte Stullen auf warmem Sauerteigbrot, Eierzauber und Bowls für den perfekten Start.' ),
					array( 'icon' => $this->get_attachment_id_by_source( 'images/sweets-1.svg' ), 'title' => 'Kuchen & Süßes', 'text' => 'Hausgemachte Torten, Kuchen und flambierte Plotzer mit Vanilleeis – Zucker für die Seele.' ),
					array( 'icon' => $this->get_attachment_id_by_source( 'images/Kaffee Icon.webp' ), 'title' => 'Daydrinking & Afterwork', 'text' => 'Espresso Martini, Hugo, Aperoli & ausgewählte Weine – auch alkoholfrei zum Anstoßen.' ),
				),
				'button_text'           => 'Komplette Speisekarte ansehen',
				'button_url'            => '/#menu',
			),
			array(
				'acf_fc_layout' => 'details',
				'section_badge' => 'Save the Date',
				'title'         => 'Eröffnung am 11. Juli',
				'details'       => array(
					array( 'icon' => 'calendar', 'label' => 'Datum', 'value' => 'Samstag, 11. Juli', 'url' => '' ),
					array( 'icon' => 'clock', 'label' => 'Uhrzeit', 'value' => '9:00 – 17:00 Uhr', 'url' => '' ),
					array( 'icon' => 'location', 'label' => 'Adresse', 'value' => 'Hofstätte 2, 76593 Gernsbach', 'url' => '' ),
					array( 'icon' => 'phone', 'label' => 'Telefon', 'value' => '+49 151/103 100 59', 'url' => 'tel:+4915110310059' ),
				),
				'actions'       => array(
					array( 'text' => 'Route planen', 'url' => 'https://www.google.com/maps/search/?api=1&query=CaFEE+Br%C3%BCckenm%C3%BChle+Hofst%C3%A4tte+2+Gernsbach', 'style' => 'primary', 'new_tab' => true ),
					array( 'text' => 'Rabatt sichern', 'url' => '#rabatt', 'style' => 'secondary', 'new_tab' => false ),
				),
			),
			array(
				'acf_fc_layout'         => 'faq',
				'section_badge'         => 'Gut zu wissen',
				'section_title_display' => 'Häufige',
				'section_title_script'  => 'Fragen',
				'items'                 => array(
					array( 'question' => 'Wann eröffnet das CaFEE Brückenmühle?', 'answer' => '<p>Unsere große Eröffnung ist am <strong>Samstag, den 11. Juli, ab 9:00 Uhr</strong> in der Hofstätte 2 in 76593 Gernsbach. Wir freuen uns auf dich!</p>' ),
					array( 'question' => 'Wie erhalte ich den 10% Eröffnungsrabatt?', 'answer' => '<p>Ganz einfach: Lade den QR-Code von dieser Seite herunter oder speichere die Seite als Lesezeichen. Zeige den Code bei deinem Besuch an der Theke vor – und du erhältst 10% Rabatt auf deine Bestellung.</p>' ),
					array( 'question' => 'Muss ich den QR-Code ausdrucken?', 'answer' => '<p>Nein, ein Ausdruck ist nicht nötig. Du kannst den QR-Code bequem auf deinem Smartphone vorzeigen – als Lesezeichen oder als heruntergeladenes Bild.</p>' ),
					array( 'question' => 'Wann ist der Rabatt-QR-Code gültig?', 'answer' => '<p>Der QR-Code ist <strong>ausschließlich am Eröffnungstag, Samstag den 11. Juli</strong>, gültig.</p>' ),
					array( 'question' => 'Kann ich den Rabattcode weitergeben?', 'answer' => '<p>Unbedingt! Teile diese Seite per WhatsApp, E-Mail oder Link mit Freunden und Familie – so erhalten auch sie den 10% Eröffnungsrabatt.</p>' ),
					array( 'question' => 'Gibt es Parkmöglichkeiten?', 'answer' => '<p>Du findest uns zentral in Gernsbach in der Hofstätte 2. In der Umgebung stehen öffentliche Parkmöglichkeiten zur Verfügung.</p>' ),
				),
			),
			array(
				'acf_fc_layout'    => 'final_cta',
				'background_image' => $this->get_attachment_id_by_source( 'images/Tresen.webp' ),
				'title'            => 'Sei von der ersten Tasse an dabei',
				'subtitle'         => 'Am 11. Juli beginnt unsere Geschichte. Sichere dir jetzt deinen 10%-Rabatt und werde Teil der CaFEE-Familie.',
				'button_text'      => 'Jetzt 10% Rabatt sichern',
				'button_url'       => '#rabatt',
			),
		);
	}

	/**
	 * Aktuelle gedruckte Speisekarte als ACF-PageFlip-Daten.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	protected function build_current_menu_book_pages() {
		return array(
			$this->menu_page( 'left-page', array(
				$this->menu_section( "CaFEE's heiße Liebe", array(
					$this->menu_item( 'Espresso', '2,50' ),
					$this->menu_item( 'Espresso Macchiato', '3,00' ),
					$this->menu_item( 'Café Crema', '3,50', '180ml' ),
					$this->menu_item( 'Americano', '3,50', '180ml' ),
					$this->menu_item( 'Flat White', '4,50', '180ml' ),
					$this->menu_item( 'Cappuccino klein', '3,80', '180ml' ),
					$this->menu_item( 'Cappuccino groß', '5,00', '300ml' ),
					$this->menu_item( 'Latte Macchiato', '5,00', '300ml' ),
					$this->menu_item( 'Heiße Schoki', '4,50', '300ml' ),
					$this->menu_item( 'Babyccino', '1,50', '100ml' ),
					$this->menu_item( 'Iced', '+0,50' ),
					$this->menu_item( 'Hafer-, Soja-, Mandelmilch', '+0,00' ),
				) ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Matcha & mehr', array(
					$this->menu_item( 'Matcha Latte', '5,50' ),
					$this->menu_item( 'Cinnamon Matcha Latte', '6,20' ),
					$this->menu_item( 'Chai Latte', '5,00' ),
				), 'Heiß 300ml' ),
				$this->menu_section( 'Frostig', array(
					$this->menu_item( 'Mango Coco Matcha', '7,50' ),
					$this->menu_item( 'Berry Coco Matcha', '7,50' ),
				), '300ml' ),
				$this->menu_section( 'Magie für dein Getränk', array(
					$this->menu_item( 'Sirupe', '0,50', 'Vanille | Schoko | Hasel | Caramel' ),
				) ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_section( 'Teeträume', array(
					$this->menu_item( 'Im Beutel', '3,00', 'Pfefferminz | Schwarztee | Ingwer | Früchte | Grüntee' ),
				) ),
				$this->menu_section( 'Frisch aus der Saftpresse', array(
					$this->menu_item( 'Bis zu drei Wunschzutaten', '6,50', 'Orange | Zitrone | Karotte | Ingwer | Banane | Apfel' ),
					$this->menu_item( 'Extra Ingwershot', '+2,00' ),
					$this->menu_item( 'Frisch gepresster O-Saft', '5,50' ),
				), 'Wird dir frisch gemixt.' ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Täglicher Feenstaub', array(
					$this->menu_item( 'Espresso Martini', '12,00', 'Espresso | Wodka 2cl | Kaffeelikör 2cl | Orangenlikör 2cl | Zuckersirup' ),
					$this->menu_item( 'Holy Aperoli', '8,50', 'Zinos Aperitivo 6cl | Prosecco 9cl | Soda' ),
					$this->menu_item( 'Sarti Spritz', '8,50', 'Sarti 6cl | Prosecco 9cl | Soda' ),
					$this->menu_item( 'Limoncello Spritz', '8,50', 'Limoncello 6cl | Prosecco 9cl | Soda' ),
					$this->menu_item( 'Lillet Wildberry', '8,50', 'Lillet 5cl | Schweppes Wildberry' ),
					$this->menu_item( 'Hugo*', '8,50', 'Prosecco 150ml | Holunderblütensirup | Soda' ),
					$this->menu_item( 'Wein & Tonic Rosé', '7,50', 'Wein & Tonic Rosé 0,2l' ),
				) ),
				$this->menu_text_section( '', '*auch ohne Feenstaub' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_section( 'Hopfenlust', array(
					$this->menu_item( 'Weizen', '4,50', '0,5l' ),
					$this->menu_item( 'Pils*, Radler', '4,00', '0,33l | *auch ohne Feenstaub' ),
				) ),
				$this->menu_section( 'Ohne Feenstaub', array(
					$this->menu_item( 'Wasser laut, leise klein', '3,00', '0,5l' ),
					$this->menu_item( 'Wasser laut, leise groß', '4,50', '1,0l' ),
					$this->menu_item( 'Cola (Classic & Zero)', '4,00', '0,33l' ),
					$this->menu_item( 'Fanta', '4,00', '0,33l' ),
					$this->menu_item( 'Sprite', '4,00', '0,33l' ),
					$this->menu_item( 'Paulaner Spezi', '4,00', '0,33l' ),
					$this->menu_item( 'Teinacher Genusslimonade', '4,50', '0,33l | Apfelschorle | Johannisbeer-Holunder | Limette-Minze | Mango-Maracuja-Orange | Grapefruit-Himbeer' ),
				) ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_text_section( '', 'Täglich von 9:00 bis 12:00 Uhr.' ),
				$this->menu_section( 'Märchenhafte Bowls', array(
					$this->menu_item( 'Banane & Beeren Fantasie', '10,00', 'Beerenmix | Haferflocken | Naturjoghurt | Granola | Nussmix | Topping Apfel & Banane | vegan' ),
					$this->menu_item( 'Apfelglück', '10,00', 'Apfel | Apfelkompott | Haferflocken | Zimt | Granola | Nussmix | Topping | vegan' ),
				) ),
				$this->menu_section( 'Süßer Start in den Tag', array(
					$this->menu_item( 'Fee-nomenal', '16,00', 'Bircher Müsli | Joghurt | Honig | Marmelade | Butter | Croissant' ),
				) ),
				$this->menu_section( 'Eierzauber', array(
					$this->menu_item( 'Eierzauber', '10,00', 'Rührei oder Spiegelei aus drei Eiern' ),
					$this->menu_item( 'Paprika', '' ),
					$this->menu_item( 'Tomaten', '' ),
					$this->menu_item( 'Zwiebel', '' ),
					$this->menu_item( 'Pilze', '' ),
					$this->menu_item( 'Avocado', '+3,00' ),
					$this->menu_item( 'Prosciutto Crudo', '+4,00' ),
					$this->menu_item( 'Geräucherter Lachs', '+5,00' ),
					$this->menu_item( 'Bacon', '+2,00' ),
					$this->menu_item( 'Cheddar', '+2,00' ),
					$this->menu_item( 'Sauerteig Brot', '' ),
				), 'Wünsche dir einen Eierzauber aus drei Eiern & drei Wunschzutaten mit Sauerteigbrot.' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_section( 'Traumhafte Stullen', array(
					$this->menu_item( 'Morgensonne', '12,00', 'Ricotta | Brie | Honig | Walnussbruch | frische Feigen' ),
					$this->menu_item( 'Burrataglück', '14,00', 'Pesto | Prosciutto Crudo | Burrata | Basilikum | Ricotta | Trüffelöl' ),
					$this->menu_item( 'Murgbrise', '14,00', 'Guacamole | Balsamico Zwiebeln | Smoked Lachs | Parmesan | Zitrone' ),
					$this->menu_item( 'Croissant Royal', '14,00', 'Croissant | Smoked Lachs | pochiertes Ei | Hollandaise | Rucola' ),
					$this->menu_item( 'Bauerntraum', '14,00', 'Schwarzwälder Schinken | Parmesan | Frischkäsecreme | Hollandaise | Rucola' ),
					$this->menu_item( 'Mühlenzauber', '14,00', 'Curry Dattel Creme | Feta | Artischocken | Walnussbruch | Agavendicksaft | Chilissosse' ),
				), 'Alle Stullen auf warmem Sauerteigbrot. Täglich von 9:00 Uhr bis 13:00 Uhr.' ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Selbstgemachte Leckereien', array(
					$this->menu_item( 'Stück Torte', '4,00' ),
					$this->menu_item( 'Stück Kuchen', '3,70' ),
					$this->menu_item( 'Cupcake', '3,80' ),
					$this->menu_item( 'Muffin', '3,20' ),
					$this->menu_item( 'Affogato', '5,00' ),
				), 'Alle Leckereien gibt es den ganzen Tag.' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_text_section( '', 'Mittagstisch täglich von 12:00 bis 15:00 Uhr.' ),
				$this->menu_section( 'Salate', array(
					$this->menu_item( 'Salat Ziegenkäse', '18,00', 'Bunter Salat | Ziegenkäse | Walnüsse | Honigdressing' ),
					$this->menu_item( 'Salat Gambas', '20,00', 'Bunter Salat | Gambas | Parmesan | Tomaten | Zitronendressing' ),
					$this->menu_item( 'Beilagensalat', '8,00', 'Bunter Salat | Balsamicodressing | Karotten- & Krautsalat' ),
					$this->menu_item( 'Wurstsalat', '12,00', 'Essiggurken | Zwiebeln' ),
				) ),
				$this->menu_text_section( '', 'Alle Salate mit frischem Brot.' ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Sattmacher', array(
					$this->menu_item( 'Tagessuppe', '7,00', 'Wechselnde köstliche Suppen' ),
					$this->menu_item( 'Ravioli in Hummerjus', '20,00', 'Ravioli gefüllt mit Ziegenkäse | Hummerjus' ),
					$this->menu_item( 'Ravioli in Pilzfond', '18,00', 'Ravioli Panselli e Menta | Pilzfond' ),
				), 'Mittagstisch täglich von 12:00 bis 15:00 Uhr.' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_section( 'Für die kleinen Zauberer und Feen', array(
					$this->menu_item( 'Spaghetti Tomatensosse', '9,00', 'Als Kinderportion' ),
					$this->menu_item( 'Chicken Nuggets', '11,00', 'Knusprige Chicken Nuggets | Kartoffelbrei' ),
				), 'Nur für Kinder bis 12 Jahre' ),
				$this->menu_text_section( 'Weil Tapas verbinden', 'Tapas täglich ab 15:00 Uhr.' ),
				$this->menu_section( 'Tapas', array(
					$this->menu_item( 'Knoblauch Garnelen', '14,00', 'Gambas | Knoblauch | Olivenöl' ),
					$this->menu_item( 'Sardellen', '8,00', 'Feinkostsardellen' ),
					$this->menu_item( 'Oliven', '4,50', 'Zitronige Oliven Castelvetrano' ),
					$this->menu_item( 'Aioli und Brot', '+2,00' ),
				) ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Tapas', array(
					$this->menu_item( 'Datteln im Speckmantel', '9,00', 'Datteln | Speck' ),
				) ),
				$this->menu_text_section( '', 'Bei Unverträglichkeiten oder Allergien sprechen Sie bitte unser Servicepersonal an.' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_text_section( 'Wine not?', 'Man muss auch mal Wein sagen können.' ),
				$this->menu_section( 'Rot', array(
					$this->menu_item( 'Azulejo Tinto de Lisboa', '6,00 / 17,00', 'Portugal. Samtiger Rotwein. Dunkle Fruchtaromen. Feine Würze. Weich, ausgewogen.' ),
					$this->menu_item( 'Vinhas da Invejosa Reserva Tinto', '8,00 / 22,50', 'Portugal. Kräftiger Rotwein. Kirsche, Pflaume. Gewürznoten, Vanille. Vollmundig, elegant.' ),
					$this->menu_item( 'Quinta da Invejosa Reserva Tinto', '16,00 / 44,00', 'Portugal. Kirschen, Himbeeren. Vanille, dunkle Schokolade. Harmonisch, rund.' ),
					$this->menu_item( 'Trulli Primitivo Zacena', '12,00 / 34,00', 'Italien. Kräftiger Rotwein. Kirschen, Pflaumen und dunkle Beeren. Würznoten. Vollmundig, weich.' ),
				), 'Glas 200ml / Flasche' ),
			) ),
			$this->menu_page( 'right-page', array(
				$this->menu_section( 'Rosé', array(
					$this->menu_item( 'Estreia Rosé Vinho Verde', '6,50 / 18,50', 'Portugal. Himbeeren, Erdbeere. Prickelnd, fruchtig. Erfrischend, weich.' ),
					$this->menu_item( 'Bon Ventos Rosé', '6,50 / 18,50', 'Portugal. Erdbeeren, rote Johannisbeere, Zitrusnoten. Fruchtig, lebendig und leicht.' ),
					$this->menu_item( 'Dalva Rosé Port', '15,00 / 42,50', 'Portugal. Portwein. Erdbeeren. Fruchtig, leicht, aromatisch, süffig. Idealer Aperitif.' ),
					$this->menu_item( 'Nardelli Diamant Rosato', '10,50 / 30,00', 'Italien. Reife rote Beeren. Fruchtnoten. Leicht und ausgewogen.' ),
				), 'Glas 200ml / Flasche' ),
				$this->menu_section( 'Weiß', array(
					$this->menu_item( 'Meia Serra Encruzado Branco', '12,50 / 34,00', 'Portugal. Limette, Orangenblüte, Vanille. Mineralisch. Cremig, frisch.' ),
					$this->menu_item( 'Schatz vom Vulkan Chardonnay', '60,00', 'Deutschland. Reife gelbe Früchte. Fein mineralisch. Vielschichtig. Preisgekrönt.' ),
				), 'Glas 200ml / Flasche' ),
			) ),
			$this->menu_page( 'left-page', array(
				$this->menu_section( 'Weiß', array(
					$this->menu_item( 'Bon Ventos Branco', '6,50 / 18,00', 'Portugal. Zitrusfrüchte, grüner Apfel, Maracuja, florale Noten. Lebendig, ausgewogen.' ),
					$this->menu_item( 'La Magnoli Sauvignon', '12,50 / 35,00', 'Italien. Paprika, Tomatenblätter. Floral, feine Fruchtaromen. Ausgewogen. Langer Abgang.' ),
				), 'Glas 200ml / Flasche' ),
				$this->menu_section( 'So schön Prickelndes', array(
					$this->menu_item( 'Civa Prosecco Rosé Millesimato', '9,00 / 25,00', 'Italien. Rote Beeren, floral. Zarte Perlage. Fruchtig, elegant, erfrischend.' ),
					$this->menu_item( 'Racco Sparkling (ohne Feenstaub)', '7,00 / 18,50', 'Deutschland. Alkoholfrei. Rote Äpfel, rote Beeren. Frisch, prickelnd, harmonisch.' ),
					$this->menu_item( 'Moet Iced Imperial', '120,00', 'Frankreich. Champagner. Tropische Früchte, Mango und Grapefruit. Fruchtig, elegant, erfrischend.' ),
					$this->menu_item( 'Frisante Baggio Dela Luna', '6,00 / 22,00', 'Italien. Frizzante. Grüne Äpfel, Birnen & weiße Blüten. Feine Perlage. Frisch, fruchtig, lebendig.' ),
				), 'Glas 100ml / Flasche' ),
			) ),
			$this->menu_page( 'right-page', array() ),
		);
	}

	/**
	 * ACF-Zeile für eine MenuBook-Seite.
	 *
	 * @param string $page_class left-page|right-page.
	 * @param array  $sections   Seiten-Sektionen.
	 * @return array<string,mixed>
	 */
	protected function menu_page( $page_class, array $sections ) {
		return array(
			'page_class'    => ( 'right-page' === $page_class ) ? 'right-page' : 'left-page',
			'section_title' => '',
			'page_sections' => $sections,
			'row_items'     => array(),
			'page_quote'    => '',
			'page_image'    => 0,
		);
	}

	/**
	 * ACF-Zeile für eine MenuBook-Menüsektion.
	 *
	 * @param string $title       Sektionstitel.
	 * @param array  $items       Menüeinträge.
	 * @param string $description Beschreibung.
	 * @param string $body        Freitext.
	 * @return array<string,mixed>
	 */
	protected function menu_section( $title, array $items, $description = '', $body = '' ) {
		return array(
			'section_type'        => 'menu_items',
			'section_title'       => $title,
			'section_description' => $description,
			'section_body'        => $body,
			'section_items'       => $items,
		);
	}

	/**
	 * ACF-Zeile für eine Textsektion.
	 *
	 * @param string $title Titel.
	 * @param string $body  Text.
	 * @return array<string,mixed>
	 */
	protected function menu_text_section( $title, $body ) {
		return array(
			'section_type'        => 'text',
			'section_title'       => $title,
			'section_description' => '',
			'section_body'        => $body,
			'section_items'       => array(),
		);
	}

	/**
	 * ACF-Zeile für einen Menüeintrag.
	 *
	 * @param string $name        Name.
	 * @param string $price       Preis.
	 * @param string $description Beschreibung.
	 * @param bool   $featured    Hervorgehoben.
	 * @return array<string,mixed>
	 */
	protected function menu_item( $name, $price = '', $description = '', $featured = false ) {
		return array(
			'name'        => $name,
			'price'       => $price,
			'description' => $description,
			'featured'    => (bool) $featured,
		);
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
			$hero_video_src = $this->attr( $xpath, './/video[contains(@class,"hero-bg-video")]//source', 'src', $hero );
			$hero_video_id  = $hero_video_src ? $this->get_attachment_id_by_source( $hero_video_src ) : 0;
			$sections[] = array(
				'acf_fc_layout'   => 'hero',
				'badge_text'      => $this->text( $xpath, './/div[contains(@class,"hero-badge")]/span', $hero ),
				'title_line_1'    => $this->text( $xpath, './/h1[contains(@class,"hero-title")]/span[contains(@class,"title-line")][1]', $hero ),
				'title_line_2_accent' => $this->text( $xpath, './/h1[contains(@class,"hero-title")]/span[contains(@class,"accent")]', $hero ),
				'subtitle'        => $this->text( $xpath, './/p[contains(@class,"hero-subtitle")]', $hero ),
				'background_video' => $hero_video_id,
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
			$menu_highlights = array();
			$hl_nodes = $xpath->query( './/div[contains(@class,"menu-highlight-card")]', $menu );
			foreach ( $hl_nodes as $hl ) {
				$list_items = array();
				$li_nodes = $xpath->query( './/ul[contains(@class,"menu-highlight-list")]//li', $hl );
				foreach ( $li_nodes as $li ) {
					$iname = $this->text( $xpath, './/span[contains(@class,"item-name")]', $li );
					if ( $iname === '' ) {
						continue;
					}
					$list_items[] = array(
						'name'  => $iname,
						'price' => $this->text( $xpath, './/span[contains(@class,"item-price")]', $li ),
					);
				}
				$icon_src = $this->attr( $xpath, './/img[contains(@class,"menu-highlight-icon-img")]', 'src', $hl );
				$menu_highlights[] = array(
					'tag'         => $this->text( $xpath, './/span[contains(@class,"menu-highlight-tag")]', $hl ),
					'icon'        => $icon_src ? $this->get_attachment_id_by_source( $icon_src ) : 0,
					'title'       => $this->text( $xpath, './/h3[contains(@class,"menu-highlight-title")]', $hl ),
					'list_items'  => $list_items,
				);
			}

			$menu_book_pages = array();
			$book_container  = $xpath->query( './/*[@id="bookPagesContainer"]', $menu )->item( 0 );
			if ( $book_container ) {
				$flat_pages = $xpath->query( './div[contains(@class,"book-page")]', $book_container );
				foreach ( $flat_pages as $page_el ) {
					$cls           = $page_el instanceof DOMElement ? $page_el->getAttribute( 'class' ) : '';
					$page_class    = ( strpos( $cls, 'right-page' ) !== false ) ? 'right-page' : 'left-page';
					$page_sections = array();
					$row_items     = array();
					$legacy_title  = '';
					$section_nodes = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " menu-page-section ")]', $page_el );

					foreach ( $section_nodes as $section_el ) {
						$section_class = $section_el instanceof DOMElement ? trim( $section_el->getAttribute( 'data-section-type' ) ) : '';
						$section_type  = ( 'text' === $section_class ) ? 'text' : 'menu_items';
						$section_items = array();

						if ( 'menu_items' === $section_type ) {
							$menu_items = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " menu-item ")]', $section_el );
							foreach ( $menu_items as $item ) {
								$name = $this->text( $xpath, './/span[contains(@class,"item-name")]', $item );
								if ( $name === '' ) {
									continue;
								}
								$item_class = $item instanceof DOMElement ? $item->getAttribute( 'class' ) : '';
								$section_items[] = array(
									'name'        => $name,
									'price'       => $this->text( $xpath, './/span[contains(@class,"item-price")]', $item ),
									'description' => $this->text( $xpath, './/p[contains(@class,"item-desc")]', $item ),
									'featured'    => strpos( $item_class, 'featured' ) !== false,
								);
							}
						}

						$body_node = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " page-section-body ")]', $section_el )->item( 0 );
						$section_row = array(
							'section_type'        => $section_type,
							'section_title'       => $this->text( $xpath, './/*[contains(@class,"menu-section-title")]', $section_el ),
							'section_description' => $this->text( $xpath, './/*[contains(@class,"page-section-description")]', $section_el ),
							'section_body'        => $body_node ? $this->multiline_text_from_node( $body_node ) : '',
							'section_items'       => $section_items,
						);

						if (
							$section_row['section_title'] === '' &&
							$section_row['section_description'] === '' &&
							$section_row['section_body'] === '' &&
							empty( $section_row['section_items'] )
						) {
							continue;
						}

						$page_sections[] = $section_row;
					}

					if ( ! empty( $page_sections ) ) {
						foreach ( $page_sections as $section_row ) {
							if ( 'menu_items' !== $section_row['section_type'] ) {
								continue;
							}
							$legacy_title = $section_row['section_title'];
							$row_items    = $section_row['section_items'];
							break;
						}
					} else {
						$legacy_title = $this->text( $xpath, './/div[contains(@class,"page-header")]/h3', $page_el );
						$menu_items = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " menu-item ")]', $page_el );
						foreach ( $menu_items as $item ) {
							$name = $this->text( $xpath, './/span[contains(@class,"item-name")]', $item );
							if ( $name === '' ) {
								continue;
							}
							$row_items[] = array(
								'name'        => $name,
								'price'       => $this->text( $xpath, './/span[contains(@class,"item-price")]', $item ),
								'description' => $this->text( $xpath, './/p[contains(@class,"item-desc")]', $item ),
								'featured'    => false,
							);
						}
					}

					$page_quote_raw = $this->text( $xpath, './/p[contains(@class,"page-quote")]', $page_el );
					$img_src        = $this->attr( $xpath, './/div[contains(@class,"page-image")]//img', 'src', $page_el );
					$menu_book_pages[] = array(
						'page_class'     => $page_class,
						'section_title'  => $legacy_title,
						'page_sections'  => $page_sections,
						'row_items'      => $row_items,
						'page_quote'     => $page_quote_raw,
						'page_image'     => $img_src ? $this->get_attachment_id_by_source( $img_src ) : 0,
					);
				}
			}

			$menu_categories = array();
			$menu_quote      = '';
			$menu_quote_image = 0;
				if ( empty( $menu_book_pages ) ) {
					$spreads = $xpath->query( './/div[contains(@class,"book-spread")]', $menu );
					foreach ( $spreads as $spread ) {
						$pages = $xpath->query( './/div[contains(@class,"book-page")]', $spread );
						foreach ( $pages as $page ) {
							$quote_el = $xpath->query( './/p[contains(@class,"page-quote")]', $page )->item( 0 );
							if ( $quote_el ) {
								$menu_quote       = trim( $quote_el->textContent );
								$quote_img_src    = $this->attr( $xpath, './/div[contains(@class,"page-image")]//img', 'src', $page );
								$menu_quote_image = $quote_img_src ? $this->get_attachment_id_by_source( $quote_img_src ) : 0;
								continue;
							}
							$cat_title  = $this->text( $xpath, './/div[contains(@class,"page-header")]/h3', $page );
							$items      = array();
							$menu_items = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " menu-item ")]', $page );
							foreach ( $menu_items as $item ) {
								$name = $this->text( $xpath, './/span[contains(@class,"item-name")]', $item );
								if ( $name === '' ) {
									continue;
								}
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
				}

				$cover_logo_src  = $this->attr( $xpath, './/div[contains(@class,"book-cover")]//img', 'src', $menu );
				$pdf_href        = $this->attr( $xpath, './/div[contains(@class,"menu-download")]//a', 'href', $menu );
				$pdf_path        = ( $pdf_href && strpos( $pdf_href, 'Speisekarte' ) !== false ) ? 'Speisekarte.pdf' : $pdf_href;
				$menu_book_pages = $this->build_current_menu_book_pages();
				$sections[]      = array(
					'acf_fc_layout'            => 'menu_preview',
					'section_badge'            => $this->text( $xpath, './/div[contains(@class,"menu-header")]//div[contains(@class,"section-badge")]', $menu ),
					'section_title_display'    => $this->text( $xpath, './/div[contains(@class,"menu-header")]//span[contains(@class,"title-display")]', $menu ),
					'section_title_script'     => $this->text( $xpath, './/div[contains(@class,"menu-header")]//span[contains(@class,"title-script")]', $menu ),
					'section_subtitle'         => $this->text( $xpath, './/div[contains(@class,"menu-header")]//p[contains(@class,"section-subtitle")]', $menu ),
					'menu_highlights'          => $menu_highlights,
					'menu_book_cover_logo'     => $cover_logo_src ? $this->get_attachment_id_by_source( $cover_logo_src ) : 0,
					'menu_book_cover_title'    => $this->text( $xpath, './/div[contains(@class,"book-cover")]//h3', $menu ),
					'menu_book_cover_subtitle' => $this->text( $xpath, './/div[contains(@class,"book-cover")]//p', $menu ),
					'menu_book_pages'          => $menu_book_pages,
					'menu_categories'          => $menu_categories,
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
			$street = isset( $addr_parts[0] ) ? trim( $addr_parts[0] ) : 'Hofstätte 2';
			$city   = isset( $addr_parts[1] ) ? trim( $addr_parts[1] ) : '76593 Gernsbach';
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

	protected function multiline_text_from_node( DOMNode $node ) {
		$lines = array();

		foreach ( $node->childNodes as $child ) {
			$text = trim( preg_replace( '/\s+/u', ' ', $child->textContent ) );
			if ( '' !== $text ) {
				$lines[] = $text;
			}
		}

		if ( empty( $lines ) ) {
			return trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
		}

		return implode( "\n", $lines );
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
