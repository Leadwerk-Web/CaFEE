<?php
/**
 * ACF Flexible Content "eroeffnung_sections" ausgeben.
 *
 * @package Leadwerk_Theme
 */

if ( ! function_exists( 'leadwerk_eroeffnung_default_sections' ) ) {
	function leadwerk_eroeffnung_default_sections() {
		return array(
			array(
				'acf_fc_layout'        => 'hero',
				'background_image'     => 0,
				'badge_text'           => 'Neueröffnung · Gernsbach · 11. Juli',
				'title_eyebrow'        => 'Die Brückenmühle erwacht',
				'title_main'           => 'Wo Magie',
				'title_script'         => 'auf CaFEE trifft',
				'subtitle'             => "Jahrhundertealte Mauern, frisch gemahlene Bohnen und ein Funke Feenstaub.\nAm <strong>11. Juli</strong> öffnen wir zum ersten Mal unsere Türen – und du bist von der ersten Tasse an dabei.",
				'opening_date'         => '2026-07-11T09:00:00+02:00',
				'primary_button_text'  => '10% Rabatt sichern',
				'primary_button_url'   => '#rabatt',
				'secondary_button_text' => "So funktioniert's",
				'secondary_button_url' => '#ablauf',
				'note_text'            => 'Exklusiv für unsere Gäste der ersten Stunde',
				'scroll_text'          => 'Die Geschichte beginnt',
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
				'qr_image'       => 0,
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
					array( 'icon' => 0, 'icon_fallback' => 'kaffee-1.svg', 'title' => 'Kaffeespezialitäten', 'text' => 'Espresso, Cappuccino, Flat White, Matcha & Co. – frisch gemahlen, mit Liebe aufgeschäumt.' ),
					array( 'icon' => 0, 'icon_fallback' => 'breakfast-1.svg', 'title' => 'Frühstück & Stullen', 'text' => 'Traumhafte Stullen auf warmem Sauerteigbrot, Eierzauber und Bowls für den perfekten Start.' ),
					array( 'icon' => 0, 'icon_fallback' => 'sweets-1.svg', 'title' => 'Kuchen & Süßes', 'text' => 'Hausgemachte Torten, Kuchen und flambierte Plotzer mit Vanilleeis – Zucker für die Seele.' ),
					array( 'icon' => 0, 'icon_fallback' => 'Kaffee Icon.webp', 'title' => 'Daydrinking & Afterwork', 'text' => 'Espresso Martini, Hugo, Aperoli & ausgewählte Weine – auch alkoholfrei zum Anstoßen.' ),
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
				'background_image' => 0,
				'title'            => 'Sei von der ersten Tasse an dabei',
				'subtitle'         => 'Am 11. Juli beginnt unsere Geschichte. Sichere dir jetzt deinen 10%-Rabatt und werde Teil der CaFEE-Familie.',
				'button_text'      => 'Jetzt 10% Rabatt sichern',
				'button_url'       => '#rabatt',
			),
		);
	}
}

if ( ! function_exists( 'leadwerk_ero_value' ) ) {
	function leadwerk_ero_value( $row, $key, $default = '' ) {
		if ( ! is_array( $row ) ) {
			return $default;
		}
		return isset( $row[ $key ] ) && '' !== $row[ $key ] && null !== $row[ $key ] ? $row[ $key ] : $default;
	}
}

if ( ! function_exists( 'leadwerk_ero_asset_url' ) ) {
	function leadwerk_ero_asset_url( $filename ) {
		return LEADWERK_THEME_URI . '/assets/images/' . ltrim( (string) $filename, '/' );
	}
}

if ( ! function_exists( 'leadwerk_ero_image_url' ) ) {
	function leadwerk_ero_image_url( $img, $fallback = '', $size = 'large' ) {
		$url = '';
		if ( function_exists( 'leadwerk_theme_resolve_acf_image_url' ) ) {
			$url = leadwerk_theme_resolve_acf_image_url( $img, $size );
		}
		if ( ! $url && is_string( $img ) && filter_var( $img, FILTER_VALIDATE_URL ) ) {
			$url = esc_url_raw( $img );
		}
		return $url ? $url : ( $fallback ? leadwerk_ero_asset_url( $fallback ) : '' );
	}
}

if ( ! function_exists( 'leadwerk_ero_section_title' ) ) {
	function leadwerk_ero_section_title( $display, $script ) {
		?>
		<h2 class="ero-section-title scroll-animate">
			<span class="title-display"><?php echo esc_html( $display ); ?></span>
			<span class="title-script"><?php echo esc_html( $script ); ?></span>
		</h2>
		<?php
	}
}

if ( ! function_exists( 'leadwerk_ero_icon' ) ) {
	function leadwerk_ero_icon( $name, $size = 32 ) {
		$size = (int) $size;
		switch ( $name ) {
			case 'user':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11a3 3 0 1 0 6 0 3 3 0 0 0-6 0z"/><path d="M20 21a8 8 0 0 0-16 0" stroke-linecap="round"/></svg>';
			case 'wallet':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 12V8H6a2 2 0 0 1 0-4h12v4M4 6v12a2 2 0 0 0 2 2h14v-4M18 12a2 2 0 0 0 0 4h4v-4z" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			case 'calendar':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/></svg>';
			case 'clock':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l4 2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			case 'location':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3"/></svg>';
			case 'phone':
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
			case 'download':
			default:
				return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}
	}
}

if ( ! isset( $sections ) || ! is_array( $sections ) || empty( $sections ) ) {
	$sections = leadwerk_eroeffnung_default_sections();
}

foreach ( $sections as $section ) {
	if ( ! is_array( $section ) ) {
		continue;
	}
	$layout = isset( $section['acf_fc_layout'] ) ? $section['acf_fc_layout'] : '';
	switch ( $layout ) {
		case 'hero':
			$bg_url   = leadwerk_ero_image_url( leadwerk_ero_value( $section, 'background_image', 0 ), 'Capuccino.webp' );
			$bg_style = $bg_url ? '--ero-hero-bg-image:url(' . esc_url( $bg_url ) . ');' : '';
			?>
			<header class="ero-hero" id="home">
				<div class="ero-hero-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?> aria-hidden="true"></div>
				<div class="ero-hero-overlay" aria-hidden="true"></div>
				<div class="ero-hero-inner">
					<div class="ero-badge ero-animate"><span class="ero-badge-dot" aria-hidden="true"></span><span><?php echo esc_html( leadwerk_ero_value( $section, 'badge_text' ) ); ?></span></div>
					<h1 class="ero-hero-title ero-animate">
						<span class="ero-hero-eyebrow"><?php echo esc_html( leadwerk_ero_value( $section, 'title_eyebrow' ) ); ?></span>
						<span class="ero-hero-main"><?php echo esc_html( leadwerk_ero_value( $section, 'title_main' ) ); ?></span>
						<span class="ero-hero-script"><?php echo esc_html( leadwerk_ero_value( $section, 'title_script' ) ); ?></span>
					</h1>
					<p class="ero-hero-sub ero-animate"><?php echo wp_kses_post( nl2br( (string) leadwerk_ero_value( $section, 'subtitle' ) ) ); ?></p>
					<div class="ero-countdown ero-animate" id="countdown" data-opening-date="<?php echo esc_attr( leadwerk_ero_value( $section, 'opening_date', '2026-07-11T09:00:00+02:00' ) ); ?>" aria-label="Countdown bis zur Eröffnung">
						<div class="cd-unit"><span class="cd-num" id="cdDays">–</span><span class="cd-label">Tage</span></div>
						<div class="cd-sep" aria-hidden="true">:</div>
						<div class="cd-unit"><span class="cd-num" id="cdHours">–</span><span class="cd-label">Stunden</span></div>
						<div class="cd-sep" aria-hidden="true">:</div>
						<div class="cd-unit"><span class="cd-num" id="cdMins">–</span><span class="cd-label">Minuten</span></div>
						<div class="cd-sep" aria-hidden="true">:</div>
						<div class="cd-unit"><span class="cd-num" id="cdSecs">–</span><span class="cd-label">Sekunden</span></div>
					</div>
					<div class="ero-hero-cta ero-animate">
						<a href="<?php echo esc_url( leadwerk_ero_value( $section, 'primary_button_url', '#rabatt' ) ); ?>" class="btn btn-primary ero-btn-glow"><span class="btn-glow"></span><span class="btn-text"><?php echo esc_html( leadwerk_ero_value( $section, 'primary_button_text' ) ); ?></span></a>
						<a href="<?php echo esc_url( leadwerk_ero_value( $section, 'secondary_button_url', '#ablauf' ) ); ?>" class="btn btn-secondary-light"><?php echo esc_html( leadwerk_ero_value( $section, 'secondary_button_text' ) ); ?></a>
					</div>
					<p class="ero-hero-note ero-animate">
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<?php echo esc_html( leadwerk_ero_value( $section, 'note_text' ) ); ?>
					</p>
				</div>
				<div class="ero-scroll-hint" aria-hidden="true">
					<span><?php echo esc_html( leadwerk_ero_value( $section, 'scroll_text' ) ); ?></span>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
				</div>
			</header>
			<?php
			break;

		case 'story':
			?>
			<section class="ero-story" id="story">
				<div class="ero-story-decor ero-story-decor-1" aria-hidden="true"></div>
				<div class="ero-story-decor ero-story-decor-2" aria-hidden="true"></div>
				<div class="ero-container ero-story-inner">
					<div class="section-badge scroll-animate"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div>
					<?php leadwerk_ero_section_title( leadwerk_ero_value( $section, 'section_title_display' ), leadwerk_ero_value( $section, 'section_title_script' ) ); ?>
					<div class="ero-story-text scroll-animate"><?php echo wp_kses_post( leadwerk_ero_value( $section, 'content' ) ); ?></div>
				</div>
			</section>
			<?php
			break;

		case 'discount':
			$qr_url = leadwerk_ero_image_url( leadwerk_ero_value( $section, 'qr_image', 0 ), 'rabatt-qr-eroeffnung.png', 'full' );
			?>
			<section class="ero-rabatt" id="rabatt">
				<div class="ero-container">
					<div class="ero-rabatt-grid">
						<div class="ero-rabatt-pitch scroll-animate">
							<div class="section-badge light"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div>
							<h2 class="ero-rabatt-title"><span class="ero-rabatt-percent"><?php echo esc_html( leadwerk_ero_value( $section, 'percent' ) ); ?></span><span class="ero-rabatt-word"><?php echo esc_html( leadwerk_ero_value( $section, 'title' ) ); ?></span></h2>
							<div class="ero-rabatt-lead"><?php echo wp_kses_post( leadwerk_ero_value( $section, 'lead_text' ) ); ?></div>
							<?php $items = leadwerk_ero_value( $section, 'checklist', array() ); ?>
							<?php if ( is_array( $items ) && ! empty( $items ) ) : ?>
								<ul class="ero-rabatt-checklist">
									<?php foreach ( $items as $item ) : ?>
										<li><span class="ero-check" aria-hidden="true"></span> <?php echo esc_html( leadwerk_ero_value( $item, 'text' ) ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
						<div class="ero-qr-card scroll-animate" id="qr">
							<div class="ero-qr-card-glow" aria-hidden="true"></div>
							<div class="ero-qr-ribbon"><?php echo esc_html( leadwerk_ero_value( $section, 'qr_ribbon' ) ); ?></div>
							<p class="ero-qr-kicker"><?php echo esc_html( leadwerk_ero_value( $section, 'qr_kicker' ) ); ?></p>
							<div class="ero-qr-frame"><img src="<?php echo esc_url( $qr_url ); ?>" alt="QR-Code für 10% Eröffnungsrabatt im CaFEE Brückenmühle" id="qrImage" width="320" height="320" loading="lazy"></div>
							<p class="ero-qr-caption"><?php echo esc_html( leadwerk_ero_value( $section, 'qr_caption' ) ); ?></p>
							<p class="ero-qr-validity"><?php echo leadwerk_ero_icon( 'calendar', 18 ); ?><span><?php echo esc_html( leadwerk_ero_value( $section, 'qr_validity' ) ); ?></span></p>
							<div class="ero-qr-actions">
								<a href="<?php echo esc_url( $qr_url ); ?>" download="CaFEE-Eroeffnungsrabatt-QR.png" class="ero-action ero-action-primary" id="downloadQr"><?php echo leadwerk_ero_icon( 'download', 20 ); ?><span><?php echo esc_html( leadwerk_ero_value( $section, 'download_label' ) ); ?></span></a>
								<button type="button" class="ero-action" id="bookmarkBtn"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg><span><?php echo esc_html( leadwerk_ero_value( $section, 'bookmark_label' ) ); ?></span></button>
							</div>
							<div class="ero-share">
								<span class="ero-share-label"><?php echo esc_html( leadwerk_ero_value( $section, 'share_label' ) ); ?></span>
								<div class="ero-share-btns">
									<a class="ero-share-btn ero-share-wa" id="shareWhatsApp" href="#" target="_blank" rel="noopener" aria-label="Per WhatsApp teilen"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg></a>
									<a class="ero-share-btn ero-share-mail" id="shareMail" href="#" aria-label="Per E-Mail teilen"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
									<button type="button" class="ero-share-btn ero-share-copy" id="copyLink" aria-label="Link kopieren"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
									<button type="button" class="ero-share-btn ero-share-native" id="shareNative" aria-label="Teilen" hidden><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ero-toast" id="toast" role="status" aria-live="polite"></div>
			</section>
			<?php
			break;

		case 'steps':
			?>
			<section class="ero-steps" id="ablauf">
				<div class="ero-container">
					<div class="ero-steps-header">
						<div class="section-badge scroll-animate"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div>
						<?php leadwerk_ero_section_title( leadwerk_ero_value( $section, 'section_title_display' ), leadwerk_ero_value( $section, 'section_title_script' ) ); ?>
					</div>
					<div class="ero-steps-grid">
						<?php foreach ( (array) leadwerk_ero_value( $section, 'steps', array() ) as $index => $step ) : ?>
							<div class="ero-step scroll-animate" data-delay="<?php echo esc_attr( $index * 120 ); ?>">
								<div class="ero-step-num"><?php echo esc_html( leadwerk_ero_value( $step, 'number', (string) ( $index + 1 ) ) ); ?></div>
								<div class="ero-step-icon" aria-hidden="true"><?php echo leadwerk_ero_icon( leadwerk_ero_value( $step, 'icon', 'download' ) ); ?></div>
								<h3><?php echo esc_html( leadwerk_ero_value( $step, 'title' ) ); ?></h3>
								<p><?php echo esc_html( leadwerk_ero_value( $step, 'text' ) ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'taste':
			?>
			<section class="ero-taste" id="erwartet">
				<div class="ero-container">
					<div class="ero-steps-header">
						<div class="section-badge scroll-animate"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div>
						<?php leadwerk_ero_section_title( leadwerk_ero_value( $section, 'section_title_display' ), leadwerk_ero_value( $section, 'section_title_script' ) ); ?>
						<p class="ero-section-sub scroll-animate"><?php echo esc_html( leadwerk_ero_value( $section, 'section_subtitle' ) ); ?></p>
					</div>
					<div class="ero-taste-grid">
						<?php $taste_icon_fallbacks = array( 'kaffee-1.svg', 'breakfast-1.svg', 'sweets-1.svg', 'Kaffee Icon.webp' ); ?>
						<?php foreach ( (array) leadwerk_ero_value( $section, 'cards', array() ) as $card_index => $card ) : ?>
							<?php $icon_url = leadwerk_ero_image_url( leadwerk_ero_value( $card, 'icon', 0 ), leadwerk_ero_value( $card, 'icon_fallback', $taste_icon_fallbacks[ $card_index ] ?? '' ), 'thumbnail' ); ?>
							<div class="ero-taste-card scroll-animate">
								<?php if ( $icon_url ) : ?><div class="ero-taste-icon"><img src="<?php echo esc_url( $icon_url ); ?>" alt="" aria-hidden="true"></div><?php endif; ?>
								<h3><?php echo esc_html( leadwerk_ero_value( $card, 'title' ) ); ?></h3>
								<p><?php echo esc_html( leadwerk_ero_value( $card, 'text' ) ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="ero-taste-cta scroll-animate"><a href="<?php echo esc_url( leadwerk_ero_value( $section, 'button_url', '/#menu' ) ); ?>" class="btn btn-secondary"><?php echo esc_html( leadwerk_ero_value( $section, 'button_text' ) ); ?></a></div>
				</div>
			</section>
			<?php
			break;

		case 'details':
			?>
			<section class="ero-details" id="details">
				<div class="ero-container">
					<div class="ero-details-card scroll-animate">
						<div class="ero-details-head"><div class="section-badge"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div><h2 class="ero-details-title"><?php echo esc_html( leadwerk_ero_value( $section, 'title' ) ); ?></h2></div>
						<div class="ero-details-grid">
							<?php foreach ( (array) leadwerk_ero_value( $section, 'details', array() ) as $detail ) : ?>
								<div class="ero-detail">
									<?php echo leadwerk_ero_icon( leadwerk_ero_value( $detail, 'icon', 'calendar' ), 26 ); ?>
									<div><strong><?php echo esc_html( leadwerk_ero_value( $detail, 'label' ) ); ?></strong><span>
										<?php if ( leadwerk_ero_value( $detail, 'url' ) ) : ?><a href="<?php echo esc_url( leadwerk_ero_value( $detail, 'url' ) ); ?>"><?php echo esc_html( leadwerk_ero_value( $detail, 'value' ) ); ?></a><?php else : ?><?php echo esc_html( leadwerk_ero_value( $detail, 'value' ) ); ?><?php endif; ?>
									</span></div>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="ero-details-actions">
							<?php foreach ( (array) leadwerk_ero_value( $section, 'actions', array() ) as $action ) : ?>
								<?php
								$is_primary = 'secondary' !== leadwerk_ero_value( $action, 'style', 'primary' );
								$new_tab    = ! empty( $action['new_tab'] );
								?>
								<a class="btn <?php echo $is_primary ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo esc_url( leadwerk_ero_value( $action, 'url' ) ); ?>"<?php echo $new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
									<?php if ( $is_primary ) : ?><span class="btn-glow"></span><span class="btn-text"><?php echo esc_html( leadwerk_ero_value( $action, 'text' ) ); ?></span><?php else : ?><?php echo esc_html( leadwerk_ero_value( $action, 'text' ) ); ?><?php endif; ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'faq':
			?>
			<section class="ero-faq" id="faq">
				<div class="ero-container ero-faq-inner">
					<div class="ero-steps-header">
						<div class="section-badge scroll-animate"><?php echo esc_html( leadwerk_ero_value( $section, 'section_badge' ) ); ?></div>
						<?php leadwerk_ero_section_title( leadwerk_ero_value( $section, 'section_title_display' ), leadwerk_ero_value( $section, 'section_title_script' ) ); ?>
					</div>
					<div class="ero-faq-list scroll-animate" id="faqList">
						<?php foreach ( (array) leadwerk_ero_value( $section, 'items', array() ) as $item ) : ?>
							<details class="ero-faq-item"><summary><?php echo esc_html( leadwerk_ero_value( $item, 'question' ) ); ?><span class="ero-faq-icon" aria-hidden="true"></span></summary><div class="ero-faq-answer"><?php echo wp_kses_post( leadwerk_ero_value( $item, 'answer' ) ); ?></div></details>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'final_cta':
			$bg_url   = leadwerk_ero_image_url( leadwerk_ero_value( $section, 'background_image', 0 ), 'Tresen.webp' );
			$bg_style = $bg_url ? '--ero-final-bg-image:url(' . esc_url( $bg_url ) . ');' : '';
			?>
			<section class="ero-final"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>>
				<div class="ero-final-overlay" aria-hidden="true"></div>
				<div class="ero-container ero-final-inner scroll-animate">
					<h2 class="ero-final-title"><?php echo esc_html( leadwerk_ero_value( $section, 'title' ) ); ?></h2>
					<p class="ero-final-sub"><?php echo esc_html( leadwerk_ero_value( $section, 'subtitle' ) ); ?></p>
					<a href="<?php echo esc_url( leadwerk_ero_value( $section, 'button_url', '#rabatt' ) ); ?>" class="btn btn-primary ero-final-btn"><span class="btn-glow"></span><span class="btn-text"><?php echo esc_html( leadwerk_ero_value( $section, 'button_text' ) ); ?></span></a>
				</div>
			</section>
			<?php
			break;
	}
}
