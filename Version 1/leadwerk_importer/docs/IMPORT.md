# Leadwerk Importer – Anleitung

## Voraussetzungen

- WordPress 6.0+
- ACF PRO (für Startseiten-Inhalte)
- Theme „Leadwerk Theme“ aktiv

## Installation

1. Plugin als ZIP hochladen oder Ordner `leadwerk_importer` nach `wp-content/plugins/` legen.
2. Plugin unter „Plugins“ aktivieren.

## Quellordner / Medien (ohne lokalen Zugriff)

Der Importer funktioniert **auf dem Webserver ohne lokalen Zugriff**: Im Plugin ist der Ordner **source_assets** mitgeliefert. Darin liegen die Bilder, ggf. Videos und die Speisekarte-PDF aus dem Ursprungsprojekt. Wenn in der Manifest-Datei kein `source_root` gesetzt ist (oder der Pfad auf dem Server nicht existiert), nutzt der Importer automatisch `leadwerk_importer/source_assets`. So können Medien auch nach dem Upload von Theme + Plugin auf den Server importiert werden.

Optional können Sie einen eigenen Quellordner setzen (z. B. wenn Sie weitere Dateien lokal haben):

- In `wp-config.php`:  
  `define( 'LEADWERK_IMPORT_SOURCE_ROOT', 'C:/pfad/zum/Version 1' );`
- Oder per Filter:  
  `add_filter( 'leadwerk_import_source_root', fn() => 'C:/pfad/zum/Version 1' );`

## Ablauf

1. **Tools → Leadwerk Import** im Backend öffnen.
2. **Dry-Run**: Klicken Sie auf „Dry-Run (keine Änderungen)“. Es wird nur geloggt, was passieren würde.
3. **Import ausführen**: Klicken Sie auf „Import ausführen“. Es werden:
   - Seiten angelegt/aktualisiert (Startseite, Impressum, Datenschutz)
   - Startseite als Front-Page gesetzt
   - Inhalte für Impressum und Datenschutz aus den Manifest-Dateien geladen (Quelle: juventa-pflege.de)

## Re-Import

Ein erneuter Lauf aktualisiert bestehende Seiten anhand von `leadwerk_source_key` (post_meta). Es werden keine doppelten Seiten angelegt.

## Log

Nach dem Lauf „Log“ aufrufen (Link auf der Import-Seite), um das Protokoll zu sehen.

## Timeout / „Maximum execution time exceeded“

Beim Medienimport erzeugt WordPress Thumbnails (u. a. über Imagick). Bei vielen oder großen Bildern kann das Standard-PHP-Limit (z. B. 30 Sekunden) überschritten werden. Der Importer setzt für den Import-Lauf automatisch ein höheres Zeitlimit (5 Minuten). Reicht das nicht, auf dem Server in `php.ini` oder per `.user.ini` erhöhen: `max_execution_time = 300` (oder höher).

## Impressum & Datenschutz

Die Texte stammen von https://juventa-pflege.de/impressum/ und https://juventa-pflege.de/datenschutz/ und liegen im Plugin unter:

- `manifest/impressum-content.html`
- `manifest/datenschutz-content.html`

Bitte nach dem Import prüfen und an Ihr Projekt anpassen.

## Startseite & ACF

Die Startseite wird mit Titel und Slug angelegt und als Front-Page gesetzt. Die ACF-Felder (Hero, Story, Speisekarte, …) müssen Sie im Backend unter „Seiten → Startseite“ befüllen oder über einen erweiterten Import (Parsing von index.html) automatisch befüllen. Die Feldgruppen liegen im Theme unter `acf-json/`.

## WPForms

Das Reservierungsformular wird im Theme per Shortcode-Platzhalter oder Option „Contact Form Shortcode“ eingebunden. Legen Sie in WPForms ein Formular an und tragen Sie die Form-ID in den ACF-Optionen (z. B. unter „Leadwerk Optionen“) oder in der Option `leadwerk_form_reservation_contact` ein.
