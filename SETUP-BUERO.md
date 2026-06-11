# OH Büro-System – Einrichtung

Das Büro (`buero.php`) ist das JARVIS-Command-Center: Dashboard, Kalkulator,
Leads (HOT/WARM/KALT), Marketing-KI und automatische E-Mails.

## 1. Hochladen (FTP / all-inkl)
Diese Dateien/Ordner müssen auf den Server (gleicher Ort wie `index.php`):
- `buero.php`
- `buero-cron.php`
- `includes/buero-lib.php`
- `daten/` (Ordner – wird sonst automatisch angelegt; enthält `.htaccess`-Schutz)

Der Ordner `daten/` ist per `.htaccess` vor Webzugriff geschützt. Er muss für
PHP **beschreibbar** sein (Rechte 0775). Die Dateien `config.json` (Zugänge）
und `leads.json` (Anfragen) entstehen dort automatisch.

## 2. Im Büro einloggen
`https://deine-domain.de/buero.php` → Passwort **`oh`** → oben rechts ⚙️.

### a) KI-Schlüssel (Pflicht)
Anthropic-Key von console.anthropic.com eintragen → Speichern.
Wird serverseitig gespeichert und auch von der Automatik genutzt.

### b) Gmail-Versand (für Auto-E-Mails)
1. Google-Konto → Sicherheit → **2-Faktor-Authentifizierung** aktivieren
2. Dann **App-Passwörter** → neues erstellen (16 Zeichen)
3. Im Büro unter ⚙️ → Gmail-Adresse + App-Passwort eintragen → Speichern

> Wichtig: Das normale Gmail-Passwort funktioniert NICHT, nur ein App-Passwort.

## 3. Automatik aktivieren (Cronjob bei all-inkl)
all-inkl KAS → **Cronjobs** → neuer Cronjob:
- **Typ:** URL aufrufen
- **URL:** `https://deine-domain.de/buero-cron.php?key=oh-cron`
- **Intervall:** 1× täglich (z. B. 08:00 Uhr)

Der Cron erledigt automatisch:
- **Follow-up-E-Mail** 2 Tage nach Angebot (wenn keine Antwort)
- **Bewertungs-Anfrage** 5 Tage nach Abschluss

Den Cron-Schlüssel kannst Du in `daten/config.json` unter `cron_key` ändern.

## 4. Leads kommen automatisch rein
Jede Anfrage über das Website-Formular und den Funnel landet automatisch im
Dashboard – sortiert nach 🔴 SOFORT / 🟡 BALD / 🟢 KANN WARTEN.

## Noch nicht automatisch (brauchen externe Freischaltung)
- **Google-Bewertungen** erkennen/beantworten → Google Business Profile API
- **Instagram/TikTok** auto-posten → Meta- & TikTok-API

Der Code dafür folgt als nächster Schritt, sobald die API-Zugänge da sind.
Bis dahin schreibt Dir die **Marketing-KI** und der **Bewertungen**-Bereich
alle Texte fertig zum Rauskopieren.
