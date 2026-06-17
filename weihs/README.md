# Weihs – Baustelle Familie Weihs, Nürnberg

Ablage für das Kabelzuglisten-Projekt.

## Struktur
- `weihs/` … Kabelliste (PDF / Excel / CSV), z. B. `Kabelliste_Familie_Weihs_Version_1.4.pdf`
- `weihs/plaene/` … Baupläne pro Etage (PDF oder Bild), z. B. `EG.pdf`, `OG.pdf`, `KG.pdf`

## Ziel
Daraus baut die Seite `kabelliste.php` (druckfertig):
- pro Raum ein A4-Blatt mit allen Kabeln (Zuleitungen + Verbindungen)
- Felder: Raum · Etage · Bezeichnung · Typ · angeschlossenes Bauteil · Adern · Farbe · Belegung
- PR-Codes (z. B. PR-01.07.01) werden in Raumnamen aufgelöst
- Baupläne mit Raum-Beschriftung (Positionen vom Chef gesetzt → keine Fehler)

WICHTIG: Sehr genaues Projekt – Baustellen-Doku darf KEINE Fehler enthalten.
Jeder erste Raum wird gegen das Original geprüft, bevor der Rest erzeugt wird.
