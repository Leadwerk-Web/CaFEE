# Leadwerk Theme (CaFEE Brückenmühle)

Block-Theme für die Café-Website CaFEE Brückenmühle. Erfordert **ACF PRO**.

## Installation

### Per ZIP in WordPress hochladen (Design → Themes → Theme hochladen)

WordPress erwartet, dass **`style.css` direkt im ersten Ordner des ZIP** liegt (nicht erst in `Version 1/leadwerk_theme/…`).

**Richtig:**

- Ordner **`leadwerk_theme`** markieren → als ZIP packen (Windows: Rechtsklick → **Komprimieren**). Im Archiv muss der Pfad so aussehen: `leadwerk_theme/style.css`.
- Alternativ: Nur den **Inhalt** von `leadwerk_theme` zippen; dann heißt der entpackte Ordner wie die ZIP-Datei, und darin liegt `style.css` auf der obersten Ebene.

**Falsch (Fehler: „Dem Theme fehlt das Stylesheet style.css“):**

- Das **gesamte Git-Repository**, den Ordner **`Version 1`** oder mehrere verschachtelte Projektordner zippen. Dann liegt `style.css` zu tief, WordPress findet sie nicht.

### Per FTP / Dateimanager

Ordner **`leadwerk_theme`** komplett nach **`wp-content/themes/`** kopieren (Nebenordner zu anderen Themes).

### Danach

1. Theme unter „Design → Themes“ aktivieren.
2. ACF PRO installieren und aktivieren. Die Feldgruppen werden aus `acf-json/` geladen.

## Theme-Vorschaubild (screenshot.png)

Ein **optionales** Vorschaubild für die Theme-Liste in WordPress: Datei `screenshot.png` (empfohlen z. B. 1200×900 px) direkt im Theme-Root ablegen. Ohne diese Datei zeigt WordPress nur den Theme-Namen.

## Struktur (alles im Theme – kein lokaler Zugriff nötig)

- **style.css** – vollständiges Stylesheet (alle Styles inkl. WPForms-Anpassungen für die Reservierungs-Sektion; Bildpfade auf `assets/images/`). Wird auf dem Server aus dem Theme geladen. Es gibt **keine** separate `assets/css/main.css` mehr.
- **assets/images/** – mitgelieferte Bilder (Logo, Favicon, Icons). Weitere Medien für Hero, Galerie usw. können hier ergänzt werden.
- **assets/js/main.js** – JavaScript (Navigation, Menü-Buch, Lightbox, etc.)
- **templates/** – Block-Templates (front-page, page, index, 404)
- **parts/** – Header, Navigation, Footer (inkl. Video-Lightbox-Markup)
- **acf-json/** – ACF-Feldgruppen (Startseite, Optionen)
- **inc/** – ACF-Block „Home-Sektionen“, Logo-Block

## Startseite

Die Startseite nutzt das Template „Front Page“ und den ACF-Block „CaFEE Startseiten-Sektionen“. Die Inhalte (Hero, Story, Speisekarte, Erlebnis, Interviews, Team, Reservierung) kommen aus dem ACF Flexible Content-Feld `home_sections` der Startseiten-Page.

## Optionen

Unter **Leadwerk Optionen** (ACF) können Sie Logo, Footer-Text, Telefon, E-Mail, Öffnungszeiten, Social Links, Copyright und die **WPForms Formular-ID (Reservierung)** pflegen.

## Reservierung / Kontaktformular

- **WPForms:** In der Sektion „Reservieren“ wird das Formular per Shortcode ausgegeben (`[wpforms id="…"]`). Die ID kommt aus der ACF-Option `wpforms_reservation_id`. Ist diese leer, verwendet das Theme die Konstante **`LEADWERK_WPFORMS_RESERVATION_DEFAULT_ID`** in `functions.php` (Standard **171**).
- **Styling:** Formulare im Wrapper `.wpforms-cafee-wrap` sind in `style.css` an das ursprüngliche CaFEE-Formular angeglichen.
- **Fallback:** Ohne WPForms-Plugin verarbeitet `functions.php` ein klassisches POST-Kontaktformular und leitet bei Erfolg nach **`/danke/`** um.

## Unterseiten

Rechtstexte und Dankeseite: **Impressum** (`/impressum/`), **Datenschutz** (`/datenschutz/`), **Danke** (`/danke/`). Der Footer verlinkt auf Impressum und Datenschutz. Body-Klassen für Ambient-Fee und Unterseiten-Layout werden in `functions.php` gesetzt.

## Barrierefreiheit

Das Theme übernimmt das Verhalten des statischen Originals (u. a. Custom Cursor, Fokus-Styles). Es wurden keine zusätzlichen Barrierefreiheitsänderungen vorgenommen.
