# GRENZEN – Was automatisch läuft, was Freigabe braucht

> Einzige Wahrheitsquelle: `oh_grenzen()` in `includes/buero-lib.php`.
> Vom Code geprüft (Cron/Autopilot rufen keine gesperrten Funktionen auf).

## ✅ AUTOMATISCH (ohne Freigabe)
- Analysen & Lesen: Ads-Report, Marktdaten, Website-Analyse, Lexware-Abgleich
- Vorschläge/Empfehlungen erstellen (Dilara, Mert, Kaan)
- Agenten-Gedächtnis, -Denken, -Chat (kein Außenkontakt)
- E-Mail/WhatsApp klassifizieren (Spam wird ignoriert, nie beantwortet)
- Klare Geld-VERBRENNER als negative Keywords ausschließen
  (Autopilot: max 3/Tag, nur „rot", **NIE Ortsnamen**) – spart Geld, gibt keins aus
- Follow-up- & Bewertungs-Mail an BESTEHENDE Leads (haben schon angefragt)

## 🔒 NUR MIT FREIGABE (Chef drückt „Übernehmen")
- Geld ausgeben: Budget ändern/erhöhen, neue Keywords einbuchen
- Live-Website ändern (Überschrift/CTA setzen) – der „Übernehmen → live"-Klick IST die Freigabe
- E-Mails an NEUE Firmen/Kunden (B2B-Akquise) – §7 UWG
- Kundendaten löschen
- Massen-Aktionen an echte Kunden

## Durchsetzung (geprüft am 13.06.2026)
- Cron (`buero-cron.php`) ruft KEINE Geld-/Schreib-/Akquise-Funktion auf.
- Autopilot (`oh_dilara_auto_optimieren`) führt nur `negativ_keyword` aus (rot, max 3/Tag),
  über `oh_ads_add_negative_keyword` (mit Ortsnamen-Schutz).
- `oh_ads_apply` (Budget/Keywords) und `oh_website_execute_change` (Live-Seite) laufen
  ausschließlich auf Chef-Klick, nie automatisch.
- Jede Live-Änderung: Backup zuerst + Rückgängig im Erledigt-Archiv.
