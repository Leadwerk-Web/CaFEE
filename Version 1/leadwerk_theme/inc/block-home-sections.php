<?php
/**
 * ACF Flexible Content "home_sections" ausgeben.
 * Erwartet Variable $sections (Array der Layouts).
 *
 * @package Leadwerk_Theme
 */

if ( ! isset( $sections ) || ! is_array( $sections ) ) {
	return;
}

foreach ( $sections as $section ) {
	$layout = isset( $section['acf_fc_layout'] ) ? $section['acf_fc_layout'] : '';
	switch ( $layout ) {
		case 'hero':
			leadwerk_render_hero( $section );
			break;
		case 'story':
			leadwerk_render_story( $section );
			break;
		case 'menu_preview':
			leadwerk_render_menu_preview( $section );
			break;
		case 'experience':
			leadwerk_render_experience( $section );
			break;
		case 'interviews':
			leadwerk_render_interviews( $section );
			break;
		case 'team':
			leadwerk_render_team( $section );
			break;
		case 'reservation':
			leadwerk_render_reservation( $section );
			break;
	}
}

function leadwerk_esc_html_attr( $v ) {
	return $v !== null && $v !== '' ? esc_attr( (string) $v ) : '';
}
function leadwerk_esc_html( $v ) {
	return $v !== null && $v !== '' ? wp_kses_post( (string) $v ) : '';
}

function leadwerk_render_hero( $f ) {
	$badge   = isset( $f['badge_text'] ) ? $f['badge_text'] : 'Willkommen in der Brückenmühle';
	$line1   = isset( $f['title_line_1'] ) ? $f['title_line_1'] : 'Wo Magie';
	$line2   = isset( $f['title_line_2_accent'] ) ? $f['title_line_2_accent'] : 'auf Kaffee trifft';
	$sub     = isset( $f['subtitle'] ) ? $f['subtitle'] : '';
	$btn1_t  = isset( $f['button_1_text'] ) ? $f['button_1_text'] : 'Tisch reservieren';
	$btn1_u  = isset( $f['button_1_url'] ) ? $f['button_1_url'] : '#reservation';
	$btn2_t  = isset( $f['button_2_text'] ) ? $f['button_2_text'] : 'Speisekarte entdecken';
	$btn2_u  = isset( $f['button_2_url'] ) ? $f['button_2_url'] : '#menu';
	?>
	<section class="hero" id="home">
		<div class="hero-parallax-bg"></div>
		<div class="hero-overlay"></div>
		<div class="hero-fairy-element"></div>
		<div class="hero-content">
			<div class="hero-badge animate-fade-in"><span><?php echo esc_html( $badge ); ?></span></div>
			<h1 class="hero-title animate-slide-up">
				<span class="title-line"><?php echo esc_html( $line1 ); ?></span>
				<span class="title-line accent"><?php echo esc_html( $line2 ); ?></span>
			</h1>
			<?php if ( $sub ) : ?>
				<p class="hero-subtitle animate-fade-in-delay"><?php echo wp_kses_post( nl2br( $sub ) ); ?></p>
			<?php endif; ?>
			<div class="hero-buttons animate-fade-in-delay-2">
				<a href="<?php echo esc_url( $btn1_u ); ?>" class="btn btn-primary"><span class="btn-glow"></span><span class="btn-text"><?php echo esc_html( $btn1_t ); ?></span></a>
				<a href="<?php echo esc_url( $btn2_u ); ?>" class="btn btn-secondary"><?php echo esc_html( $btn2_t ); ?></a>
			</div>
		</div>
		<div class="hero-scroll-indicator">
			<div class="scroll-arrow-circle">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
			</div>
			<span>Scrollen zum Entdecken</span>
		</div>
	</section>
	<?php
}

function leadwerk_render_story( $f ) {
	$video_id   = isset( $f['video'] ) && is_array( $f['video'] ) ? (int) $f['video']['ID'] : ( isset( $f['video'] ) ? (int) $f['video'] : 0 );
	$prefix     = isset( $f['headline_prefix'] ) ? $f['headline_prefix'] : 'Es war einmal';
	$accent     = isset( $f['headline_accent'] ) ? $f['headline_accent'] : 'eine Kaffeefee';
	$content    = isset( $f['content'] ) ? $f['content'] : '';
	$cta_text   = isset( $f['cta_text'] ) ? $f['cta_text'] : 'Mehr über unser Café erfahren';
	$cta_url    = isset( $f['cta_url'] ) ? $f['cta_url'] : '#experience';
	$video_url  = $video_id ? wp_get_attachment_url( $video_id ) : '';
	?>
	<section class="story" id="story">
		<div class="story-container">
			<div class="story-visual">
				<div class="story-image-wrapper">
					<?php if ( $video_url ) : ?>
						<video autoplay muted loop playsinline class="story-image">
							<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
						</video>
					<?php endif; ?>
					<div class="story-image-frame"></div>
				</div>
				<?php if ( $video_url ) : ?>
					<div class="story-play-wrapper parallax-element" data-speed="-0.15">
						<button class="play-button" aria-label="Video ganz ansehen" id="openVideoBtn">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
						</button>
					</div>
				<?php endif; ?>
			</div>
			<div class="story-content">
				<div class="section-badge scroll-animate">Die Geschichte</div>
				<h2 class="story-title scroll-animate">
					<span><?php echo esc_html( $prefix ); ?></span>
					<span class="title-accent"><?php echo esc_html( $accent ); ?></span>
				</h2>
				<div class="story-text scroll-animate"><?php echo wp_kses_post( $content ); ?></div>
				<div class="story-cta scroll-animate">
					<a href="<?php echo esc_url( $cta_url ); ?>" class="inline-link"><?php echo esc_html( $cta_text ); ?>
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				</div>
			</div>
		</div>
		<div class="story-decoration story-decoration-1"></div>
		<div class="story-decoration story-decoration-2"></div>
	</section>
	<?php
}

function leadwerk_render_menu_preview( $f ) {
	$badge    = isset( $f['section_badge'] ) ? $f['section_badge'] : 'Genuss erleben';
	$title_d  = isset( $f['section_title_display'] ) ? $f['section_title_display'] : 'Unsere';
	$title_s  = isset( $f['section_title_script'] ) ? $f['section_title_script'] : 'Speisekarte';
	$sub      = isset( $f['section_subtitle'] ) ? $f['section_subtitle'] : '';
	$cover_logo = isset( $f['menu_book_cover_logo'] ) ? $f['menu_book_cover_logo'] : null;
	$cover_title = isset( $f['menu_book_cover_title'] ) ? $f['menu_book_cover_title'] : 'Speisekarte';
	$cover_sub  = isset( $f['menu_book_cover_subtitle'] ) ? $f['menu_book_cover_subtitle'] : 'Blättern Sie durch unsere Köstlichkeiten';
	$pdf_id   = isset( $f['pdf_download'] ) && is_array( $f['pdf_download'] ) ? (int) $f['pdf_download']['ID'] : ( isset( $f['pdf_download'] ) ? (int) $f['pdf_download'] : 0 );
	$pdf_url  = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
	$menu_categories = isset( $f['menu_categories'] ) && is_array( $f['menu_categories'] ) ? $f['menu_categories'] : array();
	$quote    = isset( $f['menu_quote'] ) ? $f['menu_quote'] : '';
	$quote_img = isset( $f['menu_quote_image'] ) ? $f['menu_quote_image'] : null;
	?>
	<section class="menu-preview" id="menu">
		<div class="menu-header">
			<div class="section-badge scroll-animate"><?php echo esc_html( $badge ); ?></div>
			<h2 class="section-title scroll-animate">
				<span class="title-display"><?php echo esc_html( $title_d ); ?></span>
				<span class="title-script"><?php echo esc_html( $title_s ); ?></span>
			</h2>
			<?php if ( $sub ) : ?>
				<p class="section-subtitle scroll-animate"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>
		<div class="menu-book-container scroll-animate">
			<div class="menu-book" id="menuBook">
				<div class="book-cover active" id="bookCover">
					<div class="cover-content">
						<?php if ( $cover_logo && ( is_array( $cover_logo ) ? ! empty( $cover_logo['ID'] ) : $cover_logo ) ) :
							$logo_id = is_array( $cover_logo ) ? (int) $cover_logo['ID'] : (int) $cover_logo;
							echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'cover-logo', 'alt' => 'CaFEE' ) );
						endif; ?>
						<h3><?php echo esc_html( $cover_title ); ?></h3>
						<p><?php echo esc_html( $cover_sub ); ?></p>
						<button class="open-book-btn" id="openBookBtn">
							<span>Karte öffnen</span>
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
					</div>
				</div>
				<div class="book-pages" id="bookPages">
					<?php
					$page_num = 0;
					foreach ( $menu_categories as $cat ) :
						$cat_title = isset( $cat['category_title'] ) ? $cat['category_title'] : '';
						$items    = isset( $cat['items'] ) && is_array( $cat['items'] ) ? $cat['items'] : array();
						$page_num++;
					?>
					<div class="book-spread" data-page="<?php echo (int) $page_num; ?>">
						<div class="book-page left-page">
							<div class="page-header">
								<h3><?php echo esc_html( $cat_title ); ?></h3>
								<div class="page-divider"></div>
							</div>
							<div class="menu-items">
								<?php foreach ( $items as $item ) :
									$name = isset( $item['name'] ) ? $item['name'] : '';
									$price = isset( $item['price'] ) ? $item['price'] : '';
									$desc = isset( $item['description'] ) ? $item['description'] : '';
									$feat = ! empty( $item['featured'] );
								?>
								<div class="menu-item<?php echo $feat ? ' featured' : ''; ?>">
									<div class="item-header">
										<span class="item-name"><?php echo esc_html( $name ); ?></span>
										<span class="item-dots"></span>
										<span class="item-price"><?php echo esc_html( $price ); ?></span>
									</div>
									<?php if ( $desc ) : ?><p class="item-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="book-page right-page">
							<?php if ( $quote_img && ( is_array( $quote_img ) ? ! empty( $quote_img['ID'] ) : $quote_img ) ) :
								$qi = is_array( $quote_img ) ? (int) $quote_img['ID'] : (int) $quote_img;
								echo '<div class="page-image">' . wp_get_attachment_image( $qi, 'medium_large', false, array( 'alt' => '' ) ) . '</div>';
							endif; ?>
							<?php if ( $quote ) : ?><p class="page-quote"><?php echo esc_html( $quote ); ?></p><?php endif; ?>
							<div class="page-number"><?php echo (int) $page_num; ?></div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="book-navigation" id="bookNav">
					<button class="nav-btn prev-btn" id="prevPage" disabled><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Zurück</span></button>
					<div class="page-indicator"><span id="currentPage">1</span> / <span id="totalPages"><?php echo max( 1, count( $menu_categories ) ); ?></span></div>
					<button class="nav-btn next-btn" id="nextPage"><span>Weiter</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
				</div>
			</div>
		</div>
		<?php if ( $pdf_url ) : ?>
		<div class="menu-download scroll-animate">
			<a href="<?php echo esc_url( $pdf_url ); ?>" download class="download-btn">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<span>Komplette Speisekarte als PDF</span>
			</a>
		</div>
		<?php endif; ?>
	</section>
	<?php
}

function leadwerk_render_experience( $f ) {
	$badge   = isset( $f['section_badge'] ) ? $f['section_badge'] : 'Das Café Erlebnis';
	$title_d = isset( $f['section_title_display'] ) ? $f['section_title_display'] : 'Mehr als';
	$title_s = isset( $f['section_title_script'] ) ? $f['section_title_script'] : 'nur Kaffee';
	$cards   = isset( $f['experience_cards'] ) && is_array( $f['experience_cards'] ) ? $f['experience_cards'] : array();
	$gallery = isset( $f['gallery'] ) ? $f['gallery'] : array();
	$insta   = isset( $f['instagram_url'] ) ? $f['instagram_url'] : '';
	if ( ! is_array( $gallery ) && ! empty( $gallery ) ) {
		$gallery = array( array( 'ID' => $gallery ) );
	}
	?>
	<section class="experience" id="experience">
		<div class="experience-bg-parallax"></div>
		<div class="experience-container">
			<div class="experience-header">
				<div class="section-badge scroll-animate"><?php echo esc_html( $badge ); ?></div>
				<h2 class="section-title scroll-animate">
					<span class="title-display"><?php echo esc_html( $title_d ); ?></span>
					<span class="title-script"><?php echo esc_html( $title_s ); ?></span>
				</h2>
			</div>
			<div class="experience-grid">
				<?php
				$delay = 0;
				foreach ( $cards as $card ) :
					$icon = isset( $card['icon'] ) ? $card['icon'] : null;
					$title = isset( $card['title'] ) ? $card['title'] : '';
					$text  = isset( $card['text'] ) ? $card['text'] : '';
				?>
				<div class="experience-card scroll-animate" data-delay="<?php echo (int) $delay; ?>">
					<div class="card-icon">
						<?php if ( $icon && ( is_array( $icon ) ? ! empty( $icon['ID'] ) : $icon ) ) :
							$icon_id = is_array( $icon ) ? (int) $icon['ID'] : (int) $icon;
							echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'class' => 'card-icon-img card-icon-tint', 'alt' => '', 'aria-hidden' => 'true' ) );
						else : ?>
							<svg viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="2"/><path d="M32 20v12l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( $title ); ?></h3>
					<p><?php echo wp_kses_post( $text ); ?></p>
				</div>
				<?php $delay += 100; endforeach; ?>
			</div>
			<?php if ( ! empty( $gallery ) ) : ?>
			<div class="insta-gallery scroll-animate">
				<?php
				$first = reset( $gallery );
				$first_id = is_array( $first ) ? (int) ( $first['ID'] ?? 0 ) : (int) $first;
				if ( $first_id ) : ?>
					<div class="insta-large"><?php echo wp_get_attachment_image( $first_id, 'large', false, array( 'alt' => 'CaFEE Moment' ) ); ?></div>
				<?php endif; ?>
				<div class="insta-grid">
					<?php foreach ( array_slice( $gallery, 1, 4 ) as $img ) :
						$id = is_array( $img ) ? (int) ( $img['ID'] ?? 0 ) : (int) $img;
						if ( ! $id ) continue;
					?>
					<div class="insta-item"><?php echo wp_get_attachment_image( $id, 'medium', false, array( 'alt' => '' ) ); ?></div>
					<?php endforeach; ?>
				</div>
				<?php if ( $insta ) : ?>
					<a href="<?php echo esc_url( $insta ); ?>" target="_blank" rel="noopener" class="insta-btn" aria-label="Besuche uns auf Instagram">
						<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
					</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function leadwerk_render_interviews( $f ) {
	$badge   = isset( $f['section_badge'] ) ? $f['section_badge'] : 'Stimmen unserer Gäste';
	$title_d = isset( $f['section_title_display'] ) ? $f['section_title_display'] : 'Was unsere';
	$title_s = isset( $f['section_title_script'] ) ? $f['section_title_script'] : 'Gäste sagen';
	$slides  = isset( $f['slides'] ) && is_array( $f['slides'] ) ? $f['slides'] : array();
	?>
	<section class="interviews" id="interviews">
		<div class="interviews-container">
			<div class="interviews-header">
				<div class="section-badge scroll-animate"><?php echo esc_html( $badge ); ?></div>
				<h2 class="section-title scroll-animate">
					<span class="title-display"><?php echo esc_html( $title_d ); ?></span>
					<span class="title-script"><?php echo esc_html( $title_s ); ?></span>
				</h2>
			</div>
			<div class="interview-slider scroll-animate" id="interviewSlider">
				<div class="interview-slides" id="interviewSlides">
					<?php foreach ( $slides as $i => $slide ) :
						$vid   = isset( $slide['video'] ) ? $slide['video'] : null;
						$vid_id = is_array( $vid ) ? (int) ( $vid['ID'] ?? 0 ) : (int) $vid;
						$vid_url = $vid_id ? wp_get_attachment_url( $vid_id ) : '';
						$quote = isset( $slide['quote'] ) ? $slide['quote'] : '';
						$name  = isset( $slide['author_name'] ) ? $slide['author_name'] : '';
						$detail = isset( $slide['author_detail'] ) ? $slide['author_detail'] : '';
					?>
					<div class="interview-slide <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo (int) $i; ?>">
						<div class="interview-video-side">
							<div class="interview-video-wrapper">
								<?php if ( $vid_url ) : ?>
									<video muted loop playsinline preload="metadata" class="interview-video">
										<source src="<?php echo esc_url( $vid_url ); ?>" type="video/mp4">
									</video>
								<?php endif; ?>
							</div>
							<div class="interview-play-wrapper">
								<button type="button" class="play-button interview-play-btn" aria-label="Interview mit Ton in Lightbox ansehen">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
								</button>
							</div>
						</div>
						<div class="interview-text-side">
							<div class="interview-quote-mark">&ldquo;</div>
							<p class="interview-quote"><?php echo esc_html( $quote ); ?></p>
							<div class="interview-author">
								<span class="interview-name"><?php echo esc_html( $name ); ?></span>
								<span class="interview-detail"><?php echo esc_html( $detail ); ?></span>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="interview-slider-nav">
					<button class="interview-nav-btn interview-prev" id="interviewPrev" aria-label="Vorheriges Interview">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<div class="interview-dots" id="interviewDots">
						<?php foreach ( $slides as $i => $s ) : ?>
							<button type="button" class="interview-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo (int) $i; ?>" aria-label="Interview <?php echo (int) $i + 1; ?>"></button>
						<?php endforeach; ?>
					</div>
					<button class="interview-nav-btn interview-next" id="interviewNext" aria-label="Nächstes Interview">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function leadwerk_render_team( $f ) {
	$badge   = isset( $f['section_badge'] ) ? $f['section_badge'] : 'Die Menschen dahinter';
	$title_d = isset( $f['section_title_display'] ) ? $f['section_title_display'] : 'Unser';
	$title_s = isset( $f['section_title_script'] ) ? $f['section_title_script'] : 'Team';
	$sub     = isset( $f['section_subtitle'] ) ? $f['section_subtitle'] : '';
	$members = isset( $f['team_members'] ) && is_array( $f['team_members'] ) ? $f['team_members'] : array();
	?>
	<section class="team" id="team">
		<div class="team-container">
			<div class="team-header">
				<div class="section-badge scroll-animate"><?php echo esc_html( $badge ); ?></div>
				<h2 class="section-title scroll-animate">
					<span class="title-display"><?php echo esc_html( $title_d ); ?></span>
					<span class="title-script"><?php echo esc_html( $title_s ); ?></span>
				</h2>
				<?php if ( $sub ) : ?><p class="section-subtitle scroll-animate"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
			<div class="team-grid">
				<?php
				$delay = 0;
				foreach ( $members as $m ) :
					$img_id = isset( $m['image'] ) ? $m['image'] : null;
					$img_id = is_array( $img_id ) ? (int) ( $img_id['ID'] ?? 0 ) : (int) $img_id;
					$name = isset( $m['name'] ) ? $m['name'] : '';
					$role = isset( $m['role'] ) ? $m['role'] : '';
				?>
				<div class="team-card scroll-animate" data-delay="<?php echo (int) $delay; ?>">
					<div class="card-image">
						<?php if ( $img_id ) : echo wp_get_attachment_image( $img_id, 'medium_large', false, array( 'alt' => esc_attr( $name ) ) ); endif; ?>
					</div>
					<div class="card-content">
						<h3><?php echo esc_html( $name ); ?></h3>
						<span class="role"><?php echo esc_html( $role ); ?></span>
					</div>
				</div>
				<?php $delay += 100; endforeach; ?>
			</div>
		</div>
		<div class="team-pattern"></div>
	</section>
	<?php
}

function leadwerk_render_reservation( $f ) {
	$badge   = isset( $f['section_badge'] ) ? $f['section_badge'] : 'Reservierung';
	$line1   = isset( $f['title_line_1'] ) ? $f['title_line_1'] : 'Werden Sie Teil';
	$line2   = isset( $f['title_line_2_accent'] ) ? $f['title_line_2_accent'] : 'unserer Geschichte';
	$intro   = isset( $f['intro_text'] ) ? $f['intro_text'] : '';
	$address = isset( $f['address_block'] ) ? $f['address_block'] : array();
	$street  = is_array( $address ) && isset( $address['street'] ) ? $address['street'] : 'Brückenstraße 12';
	$city    = is_array( $address ) && isset( $address['city'] ) ? $address['city'] : '12345 Musterstadt';
	$hours   = isset( $f['opening_hours'] ) ? $f['opening_hours'] : 'Di – Fr: 8 – 18 Uhr | Sa – So: 9 – 18 Uhr';
	$phone   = isset( $f['phone'] ) ? $f['phone'] : '+49 123 456 789';
	$email   = isset( $f['email'] ) ? $f['email'] : 'hallo@cafee-brueckenmuehle.de';
	$btn_phone = isset( $f['button_phone_label'] ) ? $f['button_phone_label'] : 'Jetzt anrufen';
	$btn_email = isset( $f['button_email_label'] ) ? $f['button_email_label'] : 'E-Mail schreiben';
	$form_alias = isset( $f['form_alias'] ) ? $f['form_alias'] : '';
	$tel_link = preg_replace( '/[^0-9+]/', '', $phone );
	?>
	<section class="reservation" id="reservation">
		<div class="reservation-bg"></div>
		<div class="reservation-container">
			<div class="reservation-content scroll-animate">
				<div class="section-badge light"><?php echo esc_html( $badge ); ?></div>
				<h2 class="reservation-title">
					<span><?php echo esc_html( $line1 ); ?></span>
					<span class="title-accent"><?php echo esc_html( $line2 ); ?></span>
				</h2>
				<?php if ( $intro ) : ?><p class="reservation-text"><?php echo wp_kses_post( nl2br( $intro ) ); ?></p><?php endif; ?>
				<div class="reservation-info">
					<div class="info-item">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<div><strong>Adresse</strong><span><?php echo esc_html( $street ); ?>,<br><?php echo esc_html( $city ); ?></span></div>
					</div>
					<div class="info-item">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<div><strong>Öffnungszeiten</strong><span><?php echo esc_html( $hours ); ?></span></div>
					</div>
					<div class="info-item">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<div><strong>Telefon</strong><span><?php echo esc_html( $phone ); ?></span></div>
					</div>
				</div>
				<div class="reservation-buttons">
					<a href="tel:<?php echo esc_attr( $tel_link ); ?>" class="btn btn-primary"><span class="btn-glow"></span><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2"/></svg><span class="btn-text"><?php echo esc_html( $btn_phone ); ?></span></a>
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="btn btn-secondary-light"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/><path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span><?php echo esc_html( $btn_email ); ?></span></a>
				</div>
			</div>
			<div class="contact-form-wrapper scroll-animate">
				<?php
				if ( $form_alias && function_exists( 'wpforms_display' ) ) {
					// WPForms: Form-ID über Option oder Mapping ermitteln; hier Platzhalter.
					$form_id = get_option( 'leadwerk_form_' . $form_alias, 0 );
					if ( (int) $form_id > 0 ) {
						echo do_shortcode( '[wpforms id="' . (int) $form_id . '"]' );
					} else {
						// Fallback: einfaches Formular wie im Original (ohne Backend-Versand)
						leadwerk_render_contact_form_placeholder();
					}
				} else {
					leadwerk_render_contact_form_placeholder();
				}
				?>
			</div>
		</div>
	</section>
	<?php
}

function leadwerk_render_contact_form_placeholder() {
	?>
	<form class="contact-form" method="post" action="">
		<?php wp_nonce_field( 'leadwerk_contact', 'leadwerk_contact_nonce' ); ?>
		<div class="form-group">
			<label for="leadwerk-name" class="sr-only">Name</label>
			<input type="text" id="leadwerk-name" name="name" placeholder="Ihr Name" required>
		</div>
		<div class="form-group">
			<label for="leadwerk-email" class="sr-only">E-Mail</label>
			<input type="email" id="leadwerk-email" name="email" placeholder="Ihre E-Mail" required>
		</div>
		<div class="form-group">
			<label for="leadwerk-message" class="sr-only">Nachricht</label>
			<textarea id="leadwerk-message" name="message" rows="4" placeholder="Ihre Nachricht an uns..." required></textarea>
		</div>
		<button type="submit" class="btn btn-primary btn-full"><span>Nachricht senden</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
	</form>
	<?php
}
