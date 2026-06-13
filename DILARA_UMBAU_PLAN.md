# DILARA UMBAU – AUFGABEN

> Vorbereitungs-Datei für den Umbau des Agenten **Dilara** (Marketing / Google Ads /
> Website-Optimierung im OH-System, oh-haustechnik.de). Damit heute Abend mit
> Claude Code CMD auf dem Laptop weitergearbeitet werden kann.

## WICHTIG VORAB (für Claude Code CMD heute Abend)
- Der **LIVE-Stand auf dem FTP-Server** (oh-haustechnik.de) ist **AKTUELLER** als GitHub,
  weil nicht jedes Mal gepusht wurde.
- **Vor jeder Änderung:** aktuellen Live-Stand vom FTP holen, als Grundlage nehmen,
  **NIEMALS** den neueren Live-Stand mit älterem GitHub-Stand überschreiben.
  Bei Konflikt: **zusammenführen, nicht überschreiben.**
- Vor Live-Änderungen **immer Backup mit Datum** anlegen.

## DILARA SOLL DREI FÄHIGKEITEN BEKOMMEN

**A) Dauerhaftes Gedächtnis**
Merkt sich Gesprächsverlauf, offene Punkte, Status, nächste Schritte; lädt das beim
nächsten Start wieder.

**B) Google Ads Kampagne "OH"**
Dilara hat Ads-Zugriff, findet aber die aktive Kampagne "OH" nicht. Grund finden und
beheben (zuerst prüfen: Customer-ID, Status-Filter, MCC/Unterkonto). Danach Kampagne
"OH" laden und alle Kennzahlen scharf analysieren: Conversions, Klicks, Impressionen,
CTR, Kosten, Keywords, Suchbegriffe → konkrete Optimierungen für mehr Kundenanfragen.

**C) Echte Ausführung**
Wenn ich auf "Übernehmen" drücke, setzt Dilara ihren Website-Vorschlag WIRKLICH auf
der Seite um. Mit Backup + Rückgängig-Funktion im Erledigt-Archiv.

## ARBEITSWEISE
Schritt für Schritt, einer nach dem anderen, nach jedem Schritt stoppen und auf
"weiter" warten. Schritte:

1. Bestandsaufnahme Dilara (Dateien, Gedächtnis, Ads-Anbindung)
2. Kampagne "OH" finden + anbinden
3. Scharfe Ads-Analyse
4. Gedächtnis-System ausbauen
5. Vorschläge mit "Übernehmen"-Button (noch ohne Ausführung)
6. Echte Ausführung (mit Backup + Rückgängig)
7. Grenzen festlegen (was automatisch / was Freigabe)

---

## HINWEISE AUS DEM CLOUD-STAND (Stand 13.06.2026)
Folgende Punkte sind im Cloud-Repo bereits gebaut und helfen beim Umbau – beim
Merge mit dem Live-Stand berücksichtigen, nicht doppelt bauen:

- **Kampagne "OH" finden (Punkt B):** In `includes/buero-lib.php` filtern die
  Ads-Abfragen jetzt auf `campaign.status = 'ENABLED'` (in `oh_ads_report`,
  `oh_ads_keywords`, `oh_ads_search_terms`, `oh_ads_market`). Damit sieht Dilara nur
  noch die aktive Kampagne **"oh"** (Customer-ID 305-400-7990, Login/MCC 246-805-3721,
  API v23). Falls die Live-Version diesen Filter noch nicht hat → übernehmen.
- **Gedächtnis (Punkt A):** Es gibt bereits `oh_wissen_*` (Gedächtnisspeicher) und
  `oh_agent_context('dilara')` (Live-Daten je Agent). Darauf aufbauen für echtes
  Gesprächsgedächtnis pro Agent.
- **Website-Analyse + Übernehmen (Punkt C):** `oh_website_analyze()` liest die echte
  Website und erzeugt Vorschläge; die Ansicht `s-web` + `website_apply` dokumentieren
  bereits. Für die ECHTE Ausführung fehlt noch: sichere Schreib-Bausteine
  (editierbare Überschrift/CTA) + Backup + Rückgängig-Archiv.
- **Selbst-Update:** `update.php` + Button „🔄 Jetzt aktualisieren" holen Dateien von
  GitHub. Achtung: NICHT benutzen, solange GitHub älter als der Live-Stand ist
  (würde Live überschreiben).

--- ENDE ---
