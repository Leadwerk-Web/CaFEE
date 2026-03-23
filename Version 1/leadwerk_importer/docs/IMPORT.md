# Leadwerk Importer – Anleitung (CaFEE Brückenmühle)

## Voraussetzungen

- WordPress 6.0+
- ACF PRO (für Startseiten-Inhalte und Optionen)
- Theme „Leadwerk Theme“ aktiv
- Optional: **WPForms** (für das Kontaktformular in der Reservierungs-Sektion)

## Installation

1. Plugin als ZIP hochladen oder Ordner `leadwerk_importer` nach `wp-content/plugins/` legen.
2. Plugin unter „Plugins“ aktivieren.

## Quellordner / Medien (ohne lokalen Zugriff)

Der Importer funktioniert **auf dem Webserver ohne lokalen Zugriff**: Im Plugin ist der Ordner **source_assets** mitgeliefert. Darin liegen u. a. Bilder, ggf. Videos und die **index.html** als Quelle für den ACF-Filler. Wenn in der Manifest-Datei kein `source_root` gesetzt ist (oder der Pfad auf dem Server nicht existiert), nutzt der Importer automatisch `leadwerk_importer/source_assets`.

Optional können Sie einen eigenen Quellordner setzen (z. B. mit vollständiger „Version 1“ inkl. aller Medien):

- In `wp-config.php`:  
  `define( 'LEADWERK_IMPORT_SOURCE_ROOT', 'C:/pfad/zum/Version 1' );`
- Oder per Filter:  
  `add_filter( 'leadwerk_import_source_root', fn() => 'C:/pfad/zum/Version 1' );`

## Ablauf

1. **Tools → Leadwerk Import** im Backend öffnen.
2. **Dry-Run**: Es wird nur geloggt, was passieren würde (keine Datenbankänderungen).
3. **Import ausführen**: Es werden u. a.:
   - **vier Seiten** angelegt bzw. aktualisiert: Startseite (als Front Page), **Impressum**, **Datenschutz**, **Danke**
   - Inhalte für Impressum, Datenschutz und Danke aus den Dateien unter `manifest/` geladen (`*-content.html`)
   - Site-Titel und Tagline aus `mapping.json` gesetzt
   - Medien aus `source_assets` (oder `source_root`) in die Mediathek importiert
   - **ACF-Optionen** (Logo, Kontakt, Footer, Social, …) befüllt
   - **Startseite:** Feld `home_sections` per **ACF-Filler** aus `source_assets/index.html` befüllt (nicht manuell nötig, sofern die HTML-Struktur zum Parser passt)
   - **WPForms:** siehe Abschnitt unten

Die Texte in `manifest/impressum-content.html`, `manifest/datenschutz-content.html` und `manifest/danke-content.html` sind **Projektvorlagen für CaFEE** – bitte **rechtlich prüfen** und an Ihre finalen Angaben anpassen.

## Re-Import

Ein erneuter Lauf aktualisiert bestehende Seiten anhand von `leadwerk_source_key` (Post-Meta). Es werden keine doppelten Seiten angelegt.

## Log

Nach dem Lauf „Log“ aufrufen (Link auf der Import-Seite), um das Protokoll zu sehen.

## Timeout / „Maximum execution time exceeded“

Beim Medienimport erzeugt WordPress Thumbnails (u. a. über Imagick). Bei vielen oder großen Bildern kann das Standard-PHP-Limit überschritten werden. Der Importer setzt für den Import-Lauf automatisch ein höheres Zeitlimit (5 Minuten). Reicht das nicht, auf dem Server `max_execution_time` in `php.ini` oder `.user.ini` erhöhen (z. B. `300`).

## WPForms und `mapping.json`

Im Root von `mapping.json` kann **`wpforms_reservation_form_id`** gesetzt sein (z. B. `171`):

- Existiert auf der Website ein **WPForms-Eintrag** mit dieser ID (Post-Typ `wpforms`), schreibt der Importer diese ID in die ACF-Option **`wpforms_reservation_id`** und legt **kein** neues Formular an.
- Existiert die ID **nicht** (frische Installation), protokolliert der Importer einen Hinweis und legt – sofern WPForms aktiv ist und noch keine gültige Option gesetzt ist – ein **neues** Formular an und speichert dessen ID in ACF.

Im Theme gilt: Wenn die ACF-Option leer ist, wird die Konstante **`LEADWERK_WPFORMS_RESERVATION_DEFAULT_ID`** in `functions.php` verwendet (Standard **171**).

## Startseite & ACF

Die Feldgruppen liegen im Theme unter `acf-json/`. Nach dem Import die Startseite unter „Seiten“ prüfen; die Sektionen sollten durch den Import aus `index.html` vorbefüllt sein. Bei abweichender HTML-Struktur ggf. einzelne ACF-Felder im Backend ergänzen.

### Speisekarte / PageFlip-Buch

Der ACF-Filler liest die interaktiven Buchseiten aus **`#bookPagesContainer`** (jeweils `div.book-page` mit links/rechts, Menüzeilen, Zitat, Seitenbild) und schreibt sie in **`menu_book_pages`**. Gibt es keinen solchen Container, greift der **Legacy-Pfad** über **`book-spread`** nach wie vor auf **`menu_categories`**. Optional: Datei **`page-turn.mp3`** ins Theme unter `assets/audio/` legen (Blätterton im Modal).

### Hero-Hintergrundvideo (MP4)

Der ACF-Filler liest die Quelle aus `video.hero-bg-video > source[src]` (z. B. `images/Imagevideo_CaFEE kurz.mp4`) und setzt das Feld **Hintergrund-Video (MP4)**. Die Datei muss im Quellordner liegen und mit den übrigen Medien importiert werden. Enthält das mitgelieferte `source_assets` keine MP4s, legen Sie die Videos in denselben relativen Pfad unter Ihrem `source_root` ab oder erweitern Sie `source_assets/images/` entsprechend.

### Open-Graph-Bild (`mapping.json`)

`seo.og_image_source` verweist auf eine **im Import vorhandene** Datei relativ zum Quellordner (z. B. `images/Cupcake.svg`). Für Social-Vorschauen empfehlen sich JPG/PNG (ca. 1200×630 px); bei Bedarf `og_image_source` auf ein eigenes Bild ändern und die Datei im Quellordner bereitstellen.
