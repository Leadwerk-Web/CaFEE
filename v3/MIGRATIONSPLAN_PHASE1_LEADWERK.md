# WordPress-Migrationsplan CaFEE Brückenmühle → leadwerk_theme / leadwerk_importer

**Projekt:** Statisches HTML/CSS/JS → WordPress (Block-Theme + Importer-Plugin)  
**Stand:** Phase 1 – Analyse, Modellierung, Architektur, Migrationsplan  
**Quelle:** `Version 1/` (index.html, styles.css, script.js, images/)

---

## 1. Executive Summary

**Was ist das Projekt?**  
Eine einsprachige (deutsch), einseitige Marketing-Website für das Café „CaFEE Brückenmühle“. Eine Single-Page-Site mit Anker-Navigation (Hero, Story, Speisekarte, Erlebnis, Gästestimmen, Team, Reservierung), interaktivem Speisekarten-Buch, Video-Sektionen (Imagefilm, Interview-Slider), Kontaktformular und Footer.

**Zielarchitektur:**  
- **Block-Theme** `leadwerk_theme` (theme.json, Templates, Template Parts, Patterns, ACF-JSON, minimale PHP-Stellen nur wo nötig).  
- **Importer-Plugin** `leadwerk_importer` für Datenbankbefüllung (Pages, Attachments, ACF), Medienimport, Manifest/Mapping, Dry-Run, Re-Import, Logging.

**Hauptrisiken:**  
- Einzelne HTML-Datei: Alle Sektionen müssen sauber in eine Startseite + ggf. Unterseiten/Anker oder eine Page mit Flexible Content aufgeteilt werden.  
- Viele Medien (Bilder, Videos, PDF): Deduplizierung und stabile Zuordnung zu Attachments/ACF.  
- Custom Cursor / Fairy Dust: Entscheidung theme-fest vs. optional; Barrierefreiheit (cursor: none, prefers-reduced-motion bereits berücksichtigt).

**Chancen:**  
- Klare Sektionsstruktur → gut auf ACF Flexible Content / Repeater abbildbar.  
- Einsprachig → kein WPML-Zwang; später erweiterbar.  
- Kein Blog erkennbar → Fokus auf Pages + ggf. eine „News“-Seite nur wenn gewünscht.

---

## 2. Projektinventar

### 2.1 HTML-Dateien
| Datei        | Zeilen | Inhalt |
|-------------|--------|--------|
| `index.html` | 904   | Single-Page: Navigation, Hero, Story, Menu Preview, Experience, Interviews, Team, Reservation, Footer, 2× Video-Lightbox |

### 2.2 CSS-Dateien
| Datei       | Zeilen (ca.) | Inhalt |
|------------|--------------|--------|
| `styles.css` | ~3020 | Design Tokens (CSS Variables), Reset, Custom Cursor, Fairy Dust, Navigation, Hero, Story, Menu Book, Experience, Interviews, Team, Reservation, Footer, Formulare, Utilities, Print |

### 2.3 JavaScript-Dateien
| Datei     | Zeilen (ca.) | Inhalt |
|----------|--------------|--------|
| `script.js` | ~733 | Custom Cursor, Fairy Dust, Navigation (inkl. Mobile Toggle, Smooth Scroll), Menu Book (Öffnen/Blättern/Touch), Parallax, Scroll-Animationen, Lazy Loading, Touch Swipe, Active Nav, Video-Lightbox (Imagefilm), Interview-Slider + Interview-Lightbox, Performance (reduced motion, low-end), init() |

### 2.4 Medien / Bilder / Icons / Fonts / Downloads

**Bilder (referenziert im HTML):**
- `images/Logo CaFEE vektorisiert.svg` – Logo (Nav, Footer, Cover)
- `images/Fee CaFEE_favicon_ohne Dampf.svg` – Favicon, Speisekarten-Cover
- `images/placeholder_atmosphere.svg` – (im Ordner, nicht in HTML referenziert – prüfen)
- `images/icon-kaffeetasse.svg`, `images/icon-herz.svg` – (im Ordner; HTML nutzt `Herz Icon.png`, `Kaffee Icon.png`)
- `images/Herz Icon.png` – Experience-Karte
- `images/Kaffee Icon.png` – Experience-Karte
- `images/Capuccino.png` – Speisekarte Buchseite
- `images/Frühstück.png` – Experience Galerie groß
- `images/Avocado Ei.png`, `images/Tresen.png`, `images/Kaffebohnen.png`, `images/Cafe blurred.png` – Experience Galerie
- `images/Team/Fee.jpg`, `images/Team/Fabian.jpg`, `images/Team/Vanessa.jpg` – Team

**Videos:**
- `images/Imagefilm.mp4` – Story-Sektion + Video-Lightbox
- `images/Interviews/Bernd Frank_1-1_01.mp4`
- `images/Interviews/Kirsten Böckner-Egner_1-1_01.mp4`
- `images/Interviews/Timo Kunz_1-1_01.mp4`

**Downloads:**
- `Speisekarte.pdf` – Download-Link im Menü-Bereich (Root, nicht in images/)

**Fonts:**
- Google Fonts (CDN): Montserrat (300,400,500,600), Josefin Sans (300,400,500,600,700)
- Brushwell (onlinewebfonts CDN) – Script/Display

**Hinweis:** Einige referenzierte Dateien (z. B. `Herz Icon.png`, `Kaffee Icon.png`, `Imagefilm.mp4`, `Capuccino.png`, PDF) sind im aktuellen Glob nicht aufgeführt – können im Projektordner dennoch vorhanden sein; Importer muss alle referenzierten Pfade aus dem HTML auslesen und nur vorhandene importieren.

### 2.5 Erkannte Layout-Bestandteile
- **Header/Nav:** fixiert, Logo + Menü (Home, Unsere Geschichte, Speisekarte, Erlebnis, Team, Reservieren), Mobile Hamburger, Scroll-Zustand „scrolled“
- **Hero:** Vollbild-Hintergrund, Badge, H1 zweizeilig, Subtitle, 2 CTAs, Scroll-Indikator
- **Story:** Zweispaltig (Video links, Text rechts), Drop-Cap, CTA-Link
- **Menu Preview:** Section-Header, interaktives „Buch“ (Cover + 3 Doppelseiten), Menüpunkte mit Name/Dots/Preis/Beschreibung, Zitat + Bild auf einer Seite, Buch-Navigation, PDF-Download
- **Experience:** Section-Header, 4 Karten (Icon, Titel, Text), Instagram-ähnliche Galerie (1 groß + 4 klein) + Instagram-Button
- **Interviews:** Section-Header, Slider mit 3 Slides (Video + Zitat + Name/Detail), Dots + Prev/Next
- **Team:** Section-Header, Karten (Bild, Name, Rolle) – 3 Mitglieder + 1 leere Karte
- **Reservation:** Hintergrund, Badge, Titel, Fließtext, 3 Infoblöcke (Adresse, Öffnungszeiten, Telefon), 2 Buttons (Anrufen, E-Mail), Kontaktformular (Name, E-Mail, Nachricht, Submit)
- **Footer:** Logo, Kurztext, 3 Spalten (Navigation, Kontakt, Öffnungszeiten), Bottom (Copyright, Social Instagram/Facebook), Dekoration

### 2.6 Erkannte Templatestruktur
- Eine logische „Seite“ = Startseite mit allen Sektionen als Anker-Blöcke.  
- Keine getrennten HTML-Seiten; Unterseiten (Impressum, Datenschutz) fehlen im statischen Projekt und werden als Platzhalter/Leerseiten im WordPress-Ziel angelegt oder dokumentiert.

### 2.7 Wiederkehrende Komponenten
- Section-Badge + Section-Title (Display + Script) + ggf. Subtitle
- Buttons: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-secondary-light`, `.btn-full`
- Inline-Link mit Pfeil-Icon
- Karten (Experience, Team)
- Play-Button (Video)
- Form-Gruppen (Label sr-only, Input/Textarea, Focus-Styles)

---

## 3. WordPress-Zielarchitektur

- **Theme-Typ:** Block-Theme mit `theme.json`, Templates, Template Parts, Patterns.  
- **PHP:** Nur wo nötig (z. B. ACF-Ausgabe von Flexible Content, Logo/Footer-Daten aus Options wenn gewünscht, keine komplette PHP-Template-Schlacht).
- **Verzeichnisstruktur (Soll):**
  - `leadwerk_theme/`: `style.css`, `theme.json`, `functions.php` (minimal), `index.php`, `templates/` (index, single, page, front-page, 404, …), `parts/` (header, footer, nav, …), `patterns/` (Hero, Story, Menu, Experience, …), `assets/` (css, js, fonts optional), `acf-json/`, `screenshot.png`, ggf. `inc/` für wenige PHP-Helfer.
  - `leadwerk_importer/`: Plugin mit `leadwerk-importer.php`, Unterordner für Klassen (Import, Media, ACF, Logging), Manifest/Mapping (JSON/YAML), CLI optional, Docs.

---

## 4. HTML-zu-WordPress-Mapping

| Quelldatei   | Erkannter Seitentyp | Ziel WordPress | Slug-Vorschlag | Template | Wiederverwendbare Komponenten | ACF-Feldgruppen | SEO-Hinweise | Sprachbezug | Importhinweise |
|-------------|----------------------|----------------|----------------|----------|-------------------------------|------------------|--------------|-------------|----------------|
| index.html  | Startseite (alle Sektionen) | Page (Front Page) | `home` oder Root | front-page / Custom | Hero, Story, Menu, Experience, Interviews, Team, Reservation, Footer | Siehe Abschnitt 5 | Title/Description aus `<meta>`, H1 nutzen | de | Eine Page „Startseite“; Sektionen als ACF Flexible Content oder Template Parts mit ACF-Daten |
| –           | Impressum            | Page           | `impressum`     | Default  | –                             | Optional ACF für Anschrift/Rechtstexte | Platzhalter | de | Nicht im Static; als leere/Platzhalter-Page anlegen |
| –           | Datenschutz          | Page           | `datenschutz`   | Default  | –                             | Optional ACF     | Platzhalter | de | Wie Impressum |

**Hinweis:** Es gibt nur eine HTML-Datei. Alle Inhalte werden der **einen** WordPress-Page (Startseite) zugeordnet; die Sektionen werden in ACF Flexible Content oder festen Blöcken pro Sektion abgebildet. Keine Posts/Blog im Quellmaterial.

---

## 5. ACF-Konzept

**Prinzip:** Pro wiedererkennbarer Sektion eine ACF-Gruppe oder ein Abschnitt innerhalb einer großen Gruppe „Startseite – Sektionen“ (Flexible Content). Empfehlung: **Flexible Content** „home_sections“ mit Layouts für: hero, story, menu_preview, experience, interviews, team, reservation. Zusätzlich globale Optionen für Header/Footer/Kontakt.

### 5.1 Feldgruppe: Startseite (Page) – Sektionen
- **Ort:** Page Template = Startseite / Front Page
- **Flexible Content:** `home_sections`
  - **Layout Hero:** badge_text, title_line_1, title_line_2_accent, subtitle (textarea), button_1_text, button_1_url, button_2_text, button_2_url
  - **Layout Story:** video (file/video), headline_prefix, headline_accent, content (wysiwyg), cta_text, cta_url
  - **Layout Menu Preview:** section_badge, section_title_display, section_title_script, section_subtitle (textarea), menu_book_cover_logo (image), menu_book_cover_title, menu_book_cover_subtitle, menu_items (repeater: category, items (repeater: name, price, description)), menu_quote, menu_quote_image (image), pdf_download (file), …
  - **Layout Experience:** section_badge, section_title_display, section_title_script, experience_cards (repeater: icon image/svg, title, text), gallery (gallery), instagram_url
  - **Layout Interviews:** section_badge, section_title_display, section_title_script, slides (repeater: video file, quote, author_name, author_detail)
  - **Layout Team:** section_badge, section_title_display, section_title_script, section_subtitle, team_members (repeater: image, name, role)
  - **Layout Reservation:** section_badge, title_line_1, title_line_2_accent, intro_text, address_block (group: street, city, …), opening_hours (textarea oder repeater), phone, email, form_alias (text für WPForms-Zuordnung), button_phone_label, button_email_label
- **Pflicht:** Keine Pflicht für alle; sinnvolle Defaults wo möglich.

### 5.2 Feldgruppe: Optionen (Global)
- **Logo** (image), **Footer-Logo** (image), **Footer-Text** (textarea), **Adresse** (group), **Telefon**, **E-Mail**, **Öffnungszeiten** (textarea oder repeater), **Social Links** (repeater: platform, url), **Copyright-Text**, **Favicon** (image) – wo nicht über Customizer/Theme gesetzt.

### 5.3 Speisekarte (Struktur für ACF)
- Repeater „menu_categories“ oder pro Buchseite: category_title, items (repeater: name, price, description, featured true/false).  
- Zusätzlich: cover_logo, cover_title, cover_subtitle, quote_text, quote_image, pdf_file.

### 5.4 Bilder in ACF
- Alle Bildfelder: Typ **Image** (Array oder ID); Importer befüllt mit Attachment-ID.  
- Galerien: **Gallery**-Feld; Importer befüllt mit Array von Attachment-IDs.

---

## 6. SEO- / Schema- / Meta-Plan

- **Meta Title:** Aus `<title>` übernehmen: „CaFEE Brückenmühle – Wo Magie auf Kaffee trifft“ → Yoast Title für Startseite.
- **Meta Description:** Aus `<meta name="description">` übernehmen (ca. 155 Zeichen).
- **Keywords:** Optional als Yoast-Focus oder verwerfen (Keywords-Meta wenig relevant).
- **Canonical:** Standard WordPress/Yoast; keine Besonderheit.
- **OG/Twitter:** Title, Description, Image (z. B. Hero-Hintergrund oder Logo); OG-Image aus Mediathek.
- **Schema:** Organization/LocalBusiness (Adresse, Öffnungszeiten, Telefon), ggf. FAQPage wenn FAQ-Sektion hinzukommt.
- **H1:** Eine H1 pro Seite; auf Startseite = Hero-Titel.
- **Alt-Texte:** Aus HTML übernehmen wo vorhanden; sonst Dateiname/kontextbasiert dokumentieren.
- **Favicon / App Icons:** Aus Favicon-SVG; Theme/Customizer.
- **Sprache:** lang="de"; kein hreflang nötig bei einsprachig.

**SEO-Mapping (Beispiel Startseite):**
- yoast_title: „CaFEE Brückenmühle – Wo Magie auf Kaffee trifft“
- yoast_meta_description: (bestehende description)
- og_image: Attachment-ID eines repräsentativen Bildes (z. B. Hero oder Logo)

---

## 7. WPForms-Plan

**Erkanntes Formular:** Ein Kontaktformular (Reservierung/Nachricht): Name (text), E-Mail (email), Nachricht (textarea), Submit „Nachricht senden“. Kein action/backend im Static – Submit führt nirgends hin.

**Zielabbildung:**
- Ein WPForms-Formular anlegen (oder Platzhalter): Felder Name, E-Mail, Nachricht; Absender-Benachrichtigung, optional Bestätigungsmail.
- **Formular-Alias im Manifest:** z. B. `reservation_contact`. Im Theme: Ausgabe des Formulars per Shortcode `[wpforms id="XXX"]`; ID wird nicht hart verdrahtet, sondern über ACF-Optionsfeld „Contact Form Shortcode“ oder „WPForms Form Alias“ + Mapping-Tabelle (Alias → Form-ID) dokumentiert. Nach Import: Nutzer trägt in Optionen die echte WPForms-Form-ID ein oder wählt Alias zugewiesene Form.
- **Styling:** Theme-CSS so belassen, dass WPForms-Shortcode in `.contact-form-wrapper` optisch angepasst bleibt (Klassen/Kinder-Selektoren dokumentieren).
- **Datenschutz/Spam:** Hinweis in Doku: Honeypot, DSGVO-Checkbox, Spam-Schutz in WPForms konfigurieren; keine produktiven IDs im Theme/Importer.

---

## 8. WPML-Plan oder Begründung warum nicht nötig

**Entscheidung:** WPML wird **nicht** eingeplant.  
**Begründung:** Das statische Projekt ist einsprachig (deutsch, `lang="de"`). Keine Sprachumschalter, keine parallelen Sprachversionen.  
**Vorgehen:** Keine WPML-spezifischen Schritte in Phase 2. Falls später Mehrsprachigkeit gewünscht wird, Theme und Inhalte sind standardkonform und nachrüstbar.

---

## 9. QM / Accessibility / Responsive Audit

| Problem | Stelle | Priorität | Lösungsvorschlag |
|--------|--------|-----------|------------------|
| `cursor: none` (Custom Cursor) | body, a, button | Mittel | Bereits nur bei `(hover: hover)`; auf Touch-Geräten aus. Für strikte Barrierefreiheit: Option im Theme deaktivierbar machen oder Hinweis in Doku. |
| Form `outline: none` | .form-group input:focus, textarea:focus | Hoch | Nicht `outline: none` ohne Ersatz; `outline: none` + sichtbaren focus-visible-Ring (z. B. box-shadow) beibehalten und Kontrast prüfen. |
| Focus-Visible für Links/Buttons | Global | Mittel | Prüfen ob alle interaktiven Elemente mit `:focus-visible` sichtbar sind; ggf. in theme.json / CSS ergänzen. |
| Alt leer bei dekorativen Icons | Herz Icon.png, Kaffee Icon.png | Niedrig | Bereits `alt=""` und `aria-hidden="true"` – OK. |
| Touch Targets (Mobile) | Nav-Toggle, Buttons, Dots | Mittel | Prüfen min 44×44px; ggf. vergrößern. |
| Semantik | section, nav, footer | Niedrig | Bereits section/id; in WP Landmarks (header, main, footer, nav) beibehalten. |
| Video autoplay | Story, Interviews | Niedrig | muted + playsinline; reduced-motion beachten (bereits im JS). |
| Horizontales Scrollen | Menu Book auf kleinen Screens | Mittel | Breakpoints prüfen; overflow verhindern. |
| Kontraste | Text auf Hero/Reservation (dunkel) | Mittel | Prüfen WCAG 2.1 AA. |

---

## 10. Importstrategie

- **Inhaltsimport:** Eine Page „Startseite“ anlegen/aktualisieren; Inhalt aus index.html parsen (Sektionen nacheinander), in ACF Flexible Content + Optionen schreiben. Titel/Slug/Status/Excerpt aus Manifest.
- **Medienimport:** Alle referenzierten Bild-/Video-/PDF-Pfade aus HTML und CSS (url()) sammeln; je Datei einmal als Attachment anlegen; Deduplizierung über Dateihash oder normierten Quellpfad; Alt-Texte aus HTML übernehmen.
- **ACF-Zuordnung:** Nach Erstellung der Attachments und der Page: ACF-Felder per Field Key befüllen (Flexible Content, Repeater, Image, File, Gallery).
- **Re-Import:** Idempotenz über source_key / import_key (z. B. Quellpfad oder Hash); bei erneutem Lauf: vorhandene Page/Attachments erkennen, aktualisieren statt duplizieren.
- **Variablen/Platzhalter:** Adresse, Telefon, E-Mail, Social-URLs, Öffnungszeiten aus Static in ACF schreiben; Platzhalter in Doku auflisten für spätere Anpassung.
- **Risiken:** Fehlende Dateien im Quellordner (z. B. Imagefilm.mp4, PDF) → Importer loggt „missing file“, setzt Feld leer oder überspringt; keine Abbruch-Kaskade.

---

## 11. Importer-Architektur

- **Form:** WordPress-Plugin `leadwerk_importer`.
- **Aufbau:** Haupt-Plugin-Datei, Autoloader für Klassen, Admin-Seite oder WP-CLI für Start des Imports.  
- **Verantwortlichkeiten:**  
  - Manifest einlesen (JSON/YAML),  
  - Quelldatei (index.html) und Asset-Pfade parsen,  
  - Attachments anlegen (Medienimport),  
  - Page anlegen/aktualisieren,  
  - ACF-Werte schreiben (über update_field mit Field Key),  
  - Logging (pro Datensatz: created/updated/skipped/error),  
  - Dry-Run (keine DB-Schreibvorgänge, nur Log).
- **Plugin-Struktur:**  
  `leadwerk_importer.php` (Header, Bootstrap), `includes/` (class-importer.php, class-media-importer.php, class-acf-mapper.php, class-logger.php), `manifest/` (mapping.json oder manifest.json), `docs/` (Anleitung).
- **WP-CLI:** Optional `wp leadwerk import [--dry-run] [--apply]`.
- **Auslöser:** Manuell über Admin-UI oder WP-CLI; kein automatischer Cron.
- **Re-Import:** Gleicher Lauf mit gleichem source_key aktualisiert; Medienabgleich über Hash/Pfad.

---

## 12. Datenmodell für ACF-Inhalte in der Datenbank

- **Wo liegen welche Inhalte:**  
  - **Hero:** ACF Flexible Content → Layout hero (alle Texte, Buttons).  
  - **Story:** ACF → Layout story (Video-Attachment, WYSIWYG, CTA).  
  - **Menu:** ACF → Layout menu_preview (Repeater für Kategorien/Items, Bild, Zitat, PDF-File).  
  - **Experience:** ACF → Layout experience (Repeater Karten, Galerie Attachment-IDs, Instagram-URL).  
  - **Interviews:** ACF → Layout interviews (Repeater: Video, Zitat, Name, Detail).  
  - **Team:** ACF → Layout team (Repeater: Bild-ID, Name, Rolle).  
  - **Reservation:** ACF → Layout reservation (Texte, Adresse, Öffnungszeiten, Telefon, E-Mail, Form-Alias).  
- **Post Content:** Kann leer bleiben oder kurzer Intro-Text; Hauptinhalt in ACF.  
- **Featured Image:** Optional Startseite = ein Hero-Hintergrund oder Logo.  
- **Theme-fest:** Custom Cursor, Fairy Dust, reine Layout-CSS/JS (keine redaktionellen Texte).

---

## 13. Medienimport- und Attachment-Strategie

- **Erkennung:** Alle `src="..."` und `href="..."` für Bilder/Videos/PDF aus index.html extrahieren; relative Pfade auf Projektroot normieren.  
- **Deduplizierung:** Pro physikalischer Datei ein Attachment; Erkennung über Dateihash (md5/sha1) oder normalisierten Pfad; bei Mehrfachverwendung dieselbe Attachment-ID in ACF setzen.  
- **Zuordnung:** Bildfelder (Image, Gallery) = Attachment-ID(s); File-Felder = Attachment-ID.  
- **Featured Image:** Optional für Startseite ein repräsentatives Bild.  
- **Metadaten:** title/caption/alt aus HTML (alt, title-Attribut) übernehmen; sonst Dateiname als Fallback dokumentieren.  
- **Content-Bilder:** Nicht als rohe URLs im post_content; alle in Mediathek, Referenz über ACF.  
- **Pfad-Ersetzung:** Nach Import keine statischen Pfade mehr; Theme rendert URLs über wp_get_attachment_image_url() etc.

---

## 14. Manifest- / Mapping-Dateiformat

**Format:** JSON (oder YAML).  

**Struktur (Beispiel):**
```json
{
  "version": "1",
  "source_root": "./",
  "pages": [
    {
      "source_file": "index.html",
      "target_type": "page",
      "post_status": "publish",
      "is_front_page": true,
      "slug": "home",
      "title": "CaFEE Brückenmühle",
      "template": "",
      "source_key": "cafee-home-v1",
      "seo": {
        "title": "CaFEE Brückenmühle – Wo Magie auf Kaffee trifft",
        "meta_description": "..."
      },
      "acf_mapping": "home_sections",
      "form_aliases": { "reservation": "reservation_contact" }
    }
  ],
  "options": {
    "acf_group": "global_options"
  }
}
```

**Konventionen:** source_key eindeutig pro Seite/Post; Medien werden implizit über Referenzen in den Inhalten importiert; Deduplizierung über Dateipfad/Hash in Importer-Logik.

---

## 15. Liste aller Felder, die nach Import im Backend editierbar sein sollen

- Seitentitel, Slug, Status (Startseite).  
- Hero: Badge, Titelzeilen, Subtitle, Button-Texte und -URLs.  
- Story: Video, Überschriften, Fließtext, CTA-Text/URL.  
- Speisekarte: Badge, Titel, Untertitel, Cover-Logo/Titel/Untertitel, alle Kategorien und Menüpunkte (Name, Preis, Beschreibung), Zitat, Zitat-Bild, PDF-Datei.  
- Experience: Badge, Titel, Karten (Icon, Titel, Text), Galerie-Bilder, Instagram-URL.  
- Interviews: Badge, Titel, pro Slide: Video, Zitat, Name, Detail.  
- Team: Badge, Titel, Subtitle, pro Person: Bild, Name, Rolle.  
- Reservation: Badge, Titel, Intro, Adresse, Öffnungszeiten, Telefon, E-Mail, Form-Alias, Button-Labels.  
- Global/Optionen: Logo, Footer-Text, Adresse, Telefon, E-Mail, Öffnungszeiten, Social Links, Copyright, Favicon.  
- Yoast: Meta Title, Meta Description (pro Seite); OG-Image wo sinnvoll.

---

## 16. Klare Trennung

| Bereich | Inhalt |
|--------|--------|
| **Theme** | Darstellung, theme.json, Templates, Template Parts, Patterns, CSS/JS für Layout und Interaktion (Cursor, Fairy Dust, Menü-Buch, Slider, Lightbox), ACF-JSON (Felddefinitionen), screenshot.png, Favicon-Konzept, Platzhalter für WPForms-Shortcode-Ausgabe. |
| **Importer** | Datenbankbefüllung, Erstellung/Update von Page und Attachments, ACF-Werte schreiben, Manifest-Auswertung, Dry-Run, Re-Import, Logging. |
| **Datenbank** | Pages (Inhalt in ACF), Attachments (Medien), ACF option (global), Post-Meta (Yoast etc.). |
| **Restschritte Backend** | WPForms-Formular anlegen und Form-ID in Optionen/Alias zuweisen; Yoast Reindex nach Import; Impressum/Datenschutz-Inhalte einpflegen; ggf. Kontaktdaten/Platzhalter final prüfen. |

---

## 17. Definition of Done

- Theme `leadwerk_theme.zip` installierbar und aktivierbar; Block-Theme-Struktur mit theme.json, Templates, Parts, Patterns.  
- screenshot.png im Theme vorhanden (auf Basis des von dir gelieferten Bildes, sofern geliefert).  
- Importer-Plugin `leadwerk_importer.zip` installierbar; Import ausführbar (Dry-Run + Apply).  
- Nach Import: Eine veröffentlichte Startseite; alle referenzierten Medien als Attachments in der Mediathek.  
- ACF-Felder sichtbar und editierbar; Texte/Bilder in DB, nicht nur im Theme referenziert.  
- Re-Import erzeugt keine ungewollten Dubletten; Medien-Deduplizierung dokumentiert und funktionsfähig.  
- WPForms-, Yoast- und WPML-Restschritte in Doku beschrieben.  
- Dokumentation (Import, Re-Import, Dry-Run, ACF, WPForms, Yoast, Grenzen, Checkliste Produktivbetrieb) vollständig.

---

## 18. Offene Entscheidungen (erledigt)

1. **Screenshot.png:** Umgesetzt: Das gelieferte Leadwerk-Logo-Bild (orangefarbenes Sechseck + Schriftzug „leadwerk“ auf dunkelblauem Hintergrund) wurde als `screenshot.png` ins Theme übernommen. Siehe Theme-README.  
2. **Impressum/Datenschutz:** Umgesetzt: Inhalte von juventa-pflege.de (Impressum + Datenschutz) werden vom Importer in die Pages „Impressum“ und „Datenschutz“ eingespielt (Manifest + HTML-Dateien im Plugin).  
3. **Custom Cursor / Fairy Dust:** Wie im Original belassen (Theme-fest).  
4. **Speisekarte PDF:** Als vorhanden bestätigt; Importer/Theme können die Datei bei Angabe des Quellordners importieren.

---

## 19. Exakter Umsetzungsplan (Phase 2)

1. **Theme-Grundgerüst:** Ordner `leadwerk_theme` anlegen, style.css, theme.json, index.php, functions.php (minimal), screenshot.png (Platzhalter oder geliefertes Bild).  
2. **Design Tokens:** theme.json mit Farben, Typo, Spacing aus styles.css abgeleitet.  
3. **Templates:** index.php, front-page.php, page.php, single.php, 404.php; header/footer als Template Parts.  
4. **Patterns:** Pro Sektion (Hero, Story, Menu, Experience, Interviews, Team, Reservation) ein Pattern; Ausgabe dynamischer Daten über ACF in PHP-Template Part oder Block.  
5. **ACF-JSON:** Feldgruppen anlegen (Startseite Flexible Content, Optionen), in acf-json exportieren.  
6. **Assets:** CSS/JS aus styles.css und script.js übernehmen; in assets/ ordnen; enqueue in functions.php.  
7. **Favicon/Logo:** Konzept in theme.json/Doku; Favicon aus Projekt.  
8. **Importer-Plugin:** Plugin-Grundgerüst, Manifest-Struktur, Klasse Medienimport (Attachments, Deduplizierung), Klasse Page-Import, Klasse ACF-Befüllung, Dry-Run, Logging, Admin-UI oder WP-CLI.  
9. **Manifest:** mapping.json für Startseite + Optionen befüllen.  
10. **Dokumentation:** README für Theme und Importer, Import-Anleitung, Re-Import, Dry-Run, WPForms/Yoast/WPML, Checkliste, screenshot-Hinweis.  
11. **Zips:** leadwerk_theme.zip und leadwerk_importer.zip erstellen.  
12. **Abnahme:** Definition of Done durchgehen.

---

## 20. Finale Soll-Ordnerstruktur leadwerk_theme.zip

```
leadwerk_theme/
├── style.css
├── theme.json
├── index.php
├── functions.php
├── screenshot.png
├── readme.txt (optional)
├── templates/
│   ├── index.html
│   ├── front-page.html
│   ├── page.html
│   ├── single.html
│   ├── 404.html
│   └── ...
├── parts/
│   ├── header.html
│   ├── footer.html
│   ├── navigation.html
│   └── ...
├── patterns/
│   ├── hero.html
│   ├── story.html
│   ├── menu-preview.html
│   ├── experience.html
│   ├── interviews.html
│   ├── team.html
│   └── reservation.html
├── assets/
│   ├── css/
│   │   └── (aus styles.css abgeleitet/gebündelt)
│   └── js/
│       └── (aus script.js abgeleitet/gebündelt)
├── acf-json/
│   ├── group_xxx_startseite.json
│   └── group_xxx_options.json
└── inc/ (optional, minimale PHP-Helfer)
```

---

## 21. Finale Soll-Ordnerstruktur leadwerk_importer.zip

```
leadwerk_importer/
├── leadwerk-importer.php
├── readme.txt
├── includes/
│   ├── class-leadwerk-importer.php
│   ├── class-media-importer.php
│   ├── class-acf-mapper.php
│   └── class-logger.php
├── manifest/
│   └── mapping.json
├── admin/ (optional, UI für Import-Start)
│   └── ...
└── docs/
    └── IMPORT.md
```

---

**Ende Phase-1-Bericht.**  
Umsetzung (Phase 2) erst nach deiner Freigabe („ausführen“ oder sinngleiche Bestätigung).
