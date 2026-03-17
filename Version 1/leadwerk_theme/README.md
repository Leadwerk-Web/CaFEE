# Leadwerk Theme (CaFEE Brückenmühle)

Block-Theme für die Café-Website CaFEE Brückenmühle. Erfordert **ACF PRO**.

## Installation

1. Theme als ZIP hochladen oder Ordner `leadwerk_theme` nach `wp-content/themes/` legen.
2. Theme unter „Design → Themes“ aktivieren.
3. ACF PRO installieren und aktivieren. Die Feldgruppen werden aus `acf-json/` geladen.

## Theme-Vorschaubild (screenshot.png)

Das Theme-Vorschaubild basiert auf dem bereitgestellten **Leadwerk-Logo**: orangefarbenes Sechseck-Symbol mit dem Schriftzug „leadwerk“ auf dunkelblauem Hintergrund. Das Bild wurde für die WordPress-Theme-Vorschau (z. B. 1200×900 px) übernommen und liegt als `screenshot.png` im Theme-Root.

## Struktur (alles im Theme – kein lokaler Zugriff nötig)

- **style.css** – vollständiges Stylesheet (alle Styles, Bildpfade auf `assets/images/`). Wird auf dem Server aus dem Theme geladen.
- **assets/images/** – mitgelieferte Bilder (Logo, Favicon, Icons). Zusätzliche Bilder (z. B. „Cafe blurred.png“, „Frühstück.png“, „Kaffebohnen.png“) können hier ergänzt werden für Hero-Hintergrund und Galerie.
- **assets/js/main.js** – JavaScript (Navigation, Menü-Buch, Lightbox, etc.)
- **templates/** – Block-Templates (front-page, page, index, 404)
- **parts/** – Header, Navigation, Footer (inkl. Video-Lightbox-Markup)
- **acf-json/** – ACF-Feldgruppen (Startseite, Optionen)
- **inc/** – ACF-Block „Home-Sektionen“, Logo-Block

## Startseite

Die Startseite nutzt das Template „Front Page“ und den ACF-Block „CaFEE Startseiten-Sektionen“. Die Inhalte (Hero, Story, Speisekarte, Erlebnis, Interviews, Team, Reservierung) werden aus dem ACF Flexible Content-Feld `home_sections` der Startseiten-Page geladen.

## Optionen

Unter **Leadwerk Optionen** (ACF) können Sie Logo, Footer-Text, Telefon, E-Mail, Öffnungszeiten, Social Links und Copyright pflegen. Das Logo im Header wird aus der Option „Logo (Header)“ ausgegeben.

## Barrierefreiheit

Das Theme übernimmt das Verhalten des statischen Originals (u. a. Custom Cursor, Fokus-Styles). Es wurden keine zusätzlichen Barrierefreiheitsänderungen vorgenommen.
