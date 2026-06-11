# OH Haustechnik – Website

Elektroinstallation & Netzwerkverkabelung im Raum Nürnberg

---

## Ordnerstruktur

```
oh-haustechnik/
├── assets/
│   ├── css/
│   │   └── style.css           ← Haupt-Stylesheet (3 Markenfarben: Blau-Dunkelblau-Hellblau)
│   ├── js/
│   │   └── main.js             ← Navigation, Animationen, Kontaktformular AJAX
│   └── img/
│       ├── logo.png            ← Logo einfügen (aus Anhang)
│       ├── favicon.ico         ← Favicon einfügen
│       └── og-image.jpg        ← Social-Media-Vorschaubild (1200×630px)
├── includes/
│   ├── header.php              ← Navigation, Meta-Tags, Schema.org
│   ├── footer.php              ← Footer, Scroll-to-Top, JS-Einbindung
│   └── contact-handler.php     ← AJAX-Kontaktformular-Verarbeitung
├── leistungen/
│   ├── elektroinstallation.php ← Detailseite Elektroinstallation
│   ├── netzwerkverkabelung.php ← Detailseite Netzwerkverkabelung
│   └── schutztechnik.php       ← Detailseite Sicherheit & Schutz
├── index.php                   ← Startseite (komplett)
├── leistungen.php              ← Leistungsübersicht
├── ueber-uns.php               ← Über OH Haustechnik
├── kontakt.php                 ← Kontaktseite mit Formular
├── impressum.php               ← Impressum (§ 5 TMG)
├── datenschutz.php             ← Datenschutzerklärung (DSGVO)
├── sitemap.xml                 ← SEO-Sitemap
├── robots.txt                  ← Crawler-Anweisungen
└── .htaccess                   ← HTTPS, Caching, Sicherheits-Header
```

---

## Design-System

### Farben (3 Markenfarben aus Logo)
| Variable          | Hex-Wert  | Verwendung                        |
|-------------------|-----------|-----------------------------------|
| `--blue-primary`  | `#2E5FA3` | Buttons, Icons, Akzente, CTAs     |
| `--blue-dark`     | `#1C3E6E` | Hero-Hintergrund, Gradient-Starts |
| `--blue-light`    | `#EEF3FB` | Hintergründe, Badges, Highlights  |

### Typografie
- **Headings:** Montserrat (700/800) – Google Fonts
- **Fließtext:** Inter (400/500/600) – Google Fonts

### Mobile-Optimierung
- Vollständig responsive ab 480px → 768px → 1024px → Desktop
- Mobile Navigation als Fullscreen-Overlay
- Touch-optimierte Buttons (min. 44px Höhe)
- Keine horizontale Scroll-Probleme

---

## Inbetriebnahme

### 1. Logo einfügen
Das Logo (`logo.png`) in den Ordner `assets/img/` kopieren.

### 2. Kontaktdaten anpassen
In allen Dateien nach `+49XXXXXXXXX` und `info@oh-haustechnik.de` suchen und 
durch die echten Kontaktdaten ersetzen.

### 3. Impressum vervollständigen
In `impressum.php` die Platzhalter `[...]` durch echte Angaben ersetzen:
- Name des Inhabers
- Adresse
- Handwerkskammer-Eintragungsnummer
- USt-IdNr.

### 4. Kontaktformular konfigurieren
In `includes/contact-handler.php` Zeile mit `$to = 'info@oh-haustechnik.de'` 
auf die echte E-Mail-Adresse anpassen.

### 5. Domain in sitemap.xml + .htaccess
`oh-haustechnik.de` durch die echte Domain ersetzen.

### 6. Canonical URLs
In jeder PHP-Datei ganz oben die `$canonical_url` auf die echte Domain anpassen.

---

## Technische Anforderungen
- PHP 7.4 oder höher
- Apache mit mod_rewrite, mod_deflate, mod_expires
- HTTPS-Zertifikat (Let's Encrypt oder ähnlich)

---

## SEO-Optimierung
- Schema.org LocalBusiness (Electrician) im Header hinterlegt
- Geo-Tags für Nürnberg
- Strukturierte Meta-Tags für jede Seite
- Sitemap für Google Search Console
- Optimiert für Keywords: "Elektroinstallation Nürnberg", "Netzwerkverkabelung Nürnberg", "Elektriker Nürnberg"
