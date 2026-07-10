# CaFEE Brückenmühle → WordPress – Projekt-Dokumentation

## Lieferumfang (Phase 2)

- **leadwerk_theme.zip** – WordPress Block-Theme (CaFEE Brückenmühle)
- **leadwerk_importer.zip** – WordPress-Plugin zum Import von Seiten und Inhalten
- **MIGRATIONSPLAN_PHASE1_LEADWERK.md** – Analyse- und Migrationsplan (Phase 1)
- **DOKUMENTATION.md** – diese Datei

## Theme-Vorschaubild (screenshot.png)

Das Theme enthält ein **screenshot.png** auf Basis des von Ihnen bereitgestellten Bildes: **Leadwerk-Logo** (orangefarbenes Sechseck-Symbol mit Schriftzug „leadwerk“ auf dunkelblauem Hintergrund). Das Bild wurde unverändert bzw. formatgerecht ins Theme übernommen und liegt unter `leadwerk_theme/screenshot.png`.

## Theme (leadwerk_theme)

- **style.css** enthält das vollständige Stylesheet (kein separates main.css nötig); alle Bildpfade zeigen auf **assets/images/**.
- **assets/images/** ist Bestandteil des Themes (Logo, Favicon, Icons); weitere Bilder können ergänzt werden. Theme läuft auf dem Server ohne lokalen Zugriff.
- Block-Theme mit `theme.json`, Templates, Template Parts; ACF PRO erforderlich; Feldgruppen in `acf-json/`.
- Startseite: ACF Flexible Content „home_sections“ (Hero, Story, Speisekarte, Erlebnis, Interviews, Team, Reservierung).
- Logo-Block und Optionen-Seite „Leadwerk Optionen“.
- Ausführliche Beschreibung: siehe `leadwerk_theme/README.md`.

## Importer (leadwerk_importer)

- Unter **Tools → Leadwerk Import**: Dry-Run und Import ausführen.
- **Ohne lokalen Zugriff**: Das Plugin enthält den Ordner **source_assets** mit allen relevanten Medien (Bilder, ggf. Videos, Speisekarte-PDF). Auf dem Webserver wird automatisch aus diesem Ordner importiert, wenn kein eigener Quellpfad gesetzt ist.
- Legt/aktualisiert Seiten an: **Startseite** (Front Page), **Impressum**, **Datenschutz**.
- **Impressum-** und **Datenschutz-Inhalte** stammen von juventa-pflege.de und liegen unter `manifest/impressum-content.html` und `manifest/datenschutz-content.html`.
- Re-Import: erkennt bestehende Seiten über `leadwerk_source_key`, keine Dubletten.
- Ausführliche Anleitung: siehe `leadwerk_importer/docs/IMPORT.md`.

## Barrierefreiheit

Wie gewünscht unverändert zum Original (keine zusätzlichen Barrierefreiheits-Anpassungen).

## Speisekarte PDF

Die Datei `Speisekarte.pdf` ist im Projekt vorhanden; bei konfiguriertem Quellordner kann der Importer Medien (inkl. PDF) importieren. Die ACF-Struktur für die Speisekarte unterstützt ein PDF-Download-Feld.

## Checkliste Produktivbetrieb

1. WordPress + ACF PRO installieren
2. Theme `leadwerk_theme.zip` installieren und aktivieren
3. Plugin `leadwerk_importer.zip` installieren und aktivieren
4. Quellordner (Pfad zu „Version 1“) ggf. in `wp-config.php` setzen: `define( 'LEADWERK_IMPORT_SOURCE_ROOT', '...' );`
5. **Tools → Leadwerk Import** → zuerst Dry-Run, dann Import ausführen
6. Startseite im Backend unter „Seiten“ öffnen und ACF-Felder (home_sections) befüllen (oder erweiterten Import nutzen)
7. **Leadwerk Optionen** (ACF): Logo, Kontakt, Footer, Social Links prüfen
8. Impressum und Datenschutz prüfen und ggf. an Ihr Unternehmen anpassen
9. WPForms-Formular anlegen und Form-ID in Optionen eintragen (Reservierung)
10. Yoast SEO: Reindex bzw. Meta-Titel/Beschreibung prüfen
