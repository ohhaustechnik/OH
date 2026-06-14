# 🎯 OH Haustechnik — Anweisungen & Stand (Kundengewinnung)

> **Diese Datei ist die zentrale Anlaufstelle.** Hier steht: was zu tun ist,
> ein fertiger Prompt für Claude (Code/CMD), die komplette Conversion-Erklärung
> und ganz unten der **aktuelle Stand**, den Claude bei jeder Session aktualisiert.

**Oberste Priorität: KUNDEN GEWINNEN.** Alles andere ist Mittel zum Zweck.

---

## ✅ Was bereits fertig ist (Website-Technik – läuft, nichts mehr zu tun)

- Landingpages für Ads (Sanierung, Altbau, Komplettsanierung, Elektriker, Fürth, Erlangen, Partner)
- Anfrage-Funnel auf allen Seiten (fragt Objekttyp, Zimmer, m²) → Lead-Mail an `oh.haustechnik@gmail.com`
- Hot-Lead-Sofortalarm (Haus / mehrere Objekte = Großauftrag → sofort melden)
- WhatsApp-Direktkontakt
- Festpreis-Kalkulator im Premium-Design (einheitliches Blau, „Sie", vertrauensbildend)
- Conversion-WERT-Tracking im Code (sendet geschätzten Auftragswert) — **wartet nur noch auf dein Ads-Label**

---

## 👉 Was DU (Adnan) tun musst — nur das bringt jetzt Kunden

## 🟦 GOOGLE ADS — Schritt für Schritt (Klick für Klick)

### A) Anzeigen auf die richtige Seite schicken (HEUTE — größter Soforteffekt)
Du zahlst schon für diese Klicks — sie landen auf einer unpassenden Seite und gehen verloren.

**✅ NEU – per Knopf (einfachster Weg):** Büro-Dashboard → **„📈 Google Ads"** →
Karte **„🎯 Keywords auf die richtige Seite schicken"** → Knopf **„🔗 Final-URLs jetzt setzen"**.
Ein Klick setzt alle 4 URLs automatisch über die API. Alte URLs werden gesichert
(rückgängig machbar). KEIN Budget wird verändert.

**Oder von Hand:** Google Ads → linke Spalte **„Keywords"** → auf das Keyword klicken →
Stift/Bearbeiten → Feld **„Finale URL"** → Adresse aus der Tabelle einfügen → **Speichern**.

| Dein Keyword | Neue Finale URL (kopieren) |
|---|---|
| altbau elektrik erneuern | `https://oh-haustechnik.de/altbausanierung-nuernberg.php` |
| altbausanierung elektro | `https://oh-haustechnik.de/altbausanierung-nuernberg.php` |
| elektriker nürnberg | `https://oh-haustechnik.de/elektroinstallation-nuernberg.php` |
| sanierung nbg | `https://oh-haustechnik.de/wohnung-elektro-sanieren-nuernberg.php` |

💡 **Warum wichtig:** „altbau elektrik erneuern" hat in 6 Monaten **257 € gekostet
und 0 Anfragen** gebracht — fast sicher, weil die Klicks auf einer unpassenden Seite
landeten. Mit der dedizierten Altbau-Seite sollte das endlich Anfragen bringen — **ohne
einen Cent mehr Budget.**

### B+C) Conversion-Tracking — ✅ JETZT PER KNOPF im Büro
**Büro-Dashboard → „📈 Google Ads" → Karte „🏆 Conversion-Tracking einrichten" →
Knopf „🏆 Conversion jetzt einrichten".**
Ein Klick legt die Lead-Conversion „OH Website Lead" in deinem Ads-Konto an (oder findet
eine vorhandene), holt automatisch das Label und aktiviert das **Wert-Tracking auf allen
Seiten** — du musst nichts mehr in den Code eintragen.

- Falls der Knopf das Label nicht auslesen kann: im selben Kasten gibt es ein
  **Feld zum manuellen Eintragen** (Label aus Google Ads → „Tag einrichten", Teil nach `AW-…/`).
- **Anruf-Conversion:** Lege in Google Ads zusätzlich eine Aktion für „Anrufe aus Anzeigen"
  an und stelle Lead + Anruf auf **„Primär"** (Scroll/Seitenaufruf auf „Sekundär").

### D) Bewertungen sammeln (kostenlos, stärkster Hebel überhaupt)
Jedem zufriedenen Kunden den Google-Bewertungslink schicken. Ziel: von 21 → **40+**.
Bei 10.000-€-Aufträgen entscheidet das oft, wer den Zuschlag bekommt.

**👉 Reihenfolge:** Erst **A** (heute), dann **B+C**, dann laufend **D**.

---

## ⚙️ Kann Claude die Google-Ads-Sachen selbst per API einstellen? (ehrlich)

Ja, dein Büro-System hat eine **Google-Ads-API-Verbindung**. Aber sie kann **nicht alles** —
hier die Wahrheit, damit keine Session etwas Falsches verspricht oder dein Geld riskiert:

**Automatisierbar (sicher, kann Claude bauen/auslösen):**
- 📖 Kampagnen-Zahlen, Keywords, Kosten, Conversions **lesen**.
- ➖ **Negative Keywords** hinzufügen, **Budgets** ändern, Keywords anpassen.

**NICHT automatisierbar (heute nicht im Code / zu riskant):**
- ❌ **Conversion-Aktionen anlegen** — geht per Bot praktisch nicht sauber, und du musst
  ohnehin einmal im Konto bestätigen. → **Schritt B/C oben machst du in der Oberfläche (5 Min).**
- ❌ **Final-URLs der Keywords ändern** — diese Funktion ist nicht eingebaut. Sie ließe sich
  bauen, **aber sie greift in dein Live-Konto ein, das echtes Geld ausgibt.** Solche
  Eingriffe macht Claude **nur nach deiner ausdrücklichen Freigabe**, nie blind automatisch.

**Regel für jede Claude-Session (WICHTIG):**
> Niemals automatisch ins Live-Google-Ads-Konto schreiben (URLs, Gebote, Conversions),
> ohne dass Adnan es ausdrücklich in diesem Dokument oder im Chat freigegeben hat.
> Lesen/Analysieren ist ok. Schreiben = nur mit Freigabe.

**✅ URL-Knopf ist gebaut:** Büro-Dashboard → „📈 Google Ads" → Karte „🎯 Keywords auf die
richtige Seite schicken" → „🔗 Final-URLs jetzt setzen". Setzt die 4 URLs per Klick über die
API, sichert die alten URLs (rückgängig machbar), ändert KEIN Budget. Du drückst, du kontrollierst.

---

## 📊 Conversions — komplett erklärt (was eingestellt werden muss)

**Was ist eine Conversion?** Eine messbare Aktion, die = ein Kunde/Lead.
„Besucher kommt auf die Website" ist KEINE Conversion (nur Traffic).

### Die richtige Struktur für einen Elektrikerbetrieb

| Priorität in Ads | Conversion | Warum |
|---|---|---|
| **PRIMÄR** | **Lead-Formular abgesendet** (mit Euro-Wert) | echte Anfrage |
| **PRIMÄR** | **Anruf** (Telefon-Klick + Anrufe aus Anzeigen) | bei Handwerk Gold wert |
| Sekundär (nur beobachten) | Kalkulator-Abschluss, WhatsApp-Klick | Interesse-Signal |
| Ignorieren | Seitenaufruf, Scroll | kein Lead |

→ Google Ads optimiert sonst auf das Falsche. Nur **Lead + Anruf = „Primär"**, Rest = „Sekundär".

### Was in Google Ads konkret einzustellen ist
1. **Tools → Conversions → + Neue Conversion-Aktion**
2. Typ **Website**, Kategorie **„Lead / Kontaktformular absenden"**
3. Bei „Wert": **„Für jede Conversion unterschiedliche Werte verwenden"** ⚠️ (sonst wird der gesendete Euro-Wert ignoriert!)
4. Zählung: **„Eine"**
5. Zweite Aktion für **Anrufe** anlegen (Telefon-Klick / Anrufe aus Anzeigen)
6. Nach dem Speichern zeigt Ads ein Label: `AW-17801418796/XXXXXXXX` → **den Teil nach dem `/` brauche ich.**

### Das eine fehlende Teil: das Conversion-Label
- **Wo:** Google Ads → Tools → Conversions → [deine Lead-Aktion] → „Tag einrichten / Tag selbst hinzufügen" → dort steht `send_to: 'AW-17801418796/XXXX'`.
- **Was tun:** Dieses Label hier unten in „STAND" eintragen ODER an Claude schicken.
- Sobald das Label da ist, trägt Claude EINE Zeile in `assets/js/oh-track.js` ein → echte Ads-Conversions mit Euro-Wert sind scharf.
- (Mehr Details: `CONVERSION-TRACKING.md`)

---

## 🤖 Fertiger Prompt für Claude (Code / CMD)

> Kopiere den folgenden Text in eine neue Claude-Session, wenn die Arbeit
> weitergehen soll:

```
Lies die Datei ANWEISUNGEN.md im Repo ohhaustechnik/oh (Branch
claude/gallant-brahmagupta-61u7q5). Sie enthält den kompletten Projektstand.

Aufgaben:
1. Falls unter "STAND" ein Google-Ads-Conversion-Label eingetragen ist, baue es
   in assets/js/oh-track.js ein (window.OH_ADS_CONV = { lead_form_submit: 'LABEL' })
   und committe/pushe.
2. Falls ich neue Wünsche eingetragen habe, setze sie um.
3. Schreibe danach den aktuellen Stand unten in ANWEISUNGEN.md unter "STAND"
   (Datum, was erledigt, was offen, was ich als Nächstes tun muss) und pushe die
   Datei. Halte es kurz und in einfachem Deutsch — keine Technik-Wand.

Wichtig: Priorität ist Kundengewinnung. Erkläre mir immer nur den EINEN nächsten
Schritt, den ich tun muss. Frag nicht nach meinem Passwort/Zugang.
```

---

## 📌 STAND (wird von Claude aktualisiert)

**Letztes Update:** 2026-06-14 (von Claude — Session „alles abarbeiten")

**Erledigt (diese Session):**
- ✅ Alle 3 neuen Commits live auf den Server gebracht (getestet, Test OK):
  `festpreis-kalkulator.php` (Premium-Redesign Blau), `assets/js/funnel.js`
  + `includes/funnel-handler.php` (Conversion-Wert-Tracking).
- ✅ Aufgabe 1 (Ads-Label in `oh-track.js` einbauen): Es ist noch **kein Label**
  eingetragen → nichts einzubauen. `oh-track.js` ist vorbereitet und wartet.
- ✅ Aufgabe 2 (neue Wünsche umsetzen): Keine neuen Wünsche gefunden → nichts zu tun.
- ✅ Aufgabe 3 (Stand schreiben & pushen): erledigt (diese Datei).
- ✅ Auto-Deploy-Task „OH Auto-Deploy" war **deaktiviert** → wieder **aktiviert**.
  Läuft wieder alle 3 Min, neue Pushes gehen automatisch live.
- ✅ GitHub-Stand und Live-Server sind synchron (Commit `5a9e43e`).

**Frühere Bausteine (fertig & live):**
- Alle Website-Bausteine fertig & live (siehe „Was bereits fertig ist").
- Conversion-Wert-Tracking im Code eingebaut (wartet auf Ads-Label).
- Premium-Kalkulator-Redesign abgeschlossen.

**Offen — bei DIR (Adnan):**
1. ⏳ Die 4 Anzeigen-Ziel-URLs in Google Ads umstellen (Schritt 1 oben).
2. ⏳ Conversion-Aktionen in Google Ads anlegen (Lead + Anruf), Wert = variabel.
3. ⏳ Conversion-Label hier eintragen → dann macht Claude den Rest:

   **DEIN LABEL HIER:** `________________`

4. ⏳ Bewertungen sammeln (Ziel 40+).

**Offen — automatisierbar (Claude erledigt auf Wunsch):**
- Label in `oh-track.js` eintragen — **sobald du dein Label oben einträgst,
  macht Claude den Rest automatisch.**
- Optional: Tag-1-Follow-up-Automatik (kein Lead wird kalt). ⚠️ Schickt echte
  E-Mails an deine Leads — **ein „ja" von dir, dann baue ich es.**

**NEU gebaut (diese Session):**
- 🔗 **URL-Knopf** (Google Ads → „Keywords auf die richtige Seite schicken") — setzt die
  4 Final-URLs per Klick über die API, mit Undo-Sicherung, ohne Budget-Änderung.
- 🏆 **Conversion-Knopf** (Google Ads → „Conversion-Tracking einrichten") — legt die
  Lead-Conversion an, holt das Label, aktiviert das Wert-Tracking automatisch. Plus Feld
  zum manuellen Eintragen des Labels als Fallback.

**Dein nächster EINER Schritt:** Beide Knöpfe im Büro drücken (1. URL-Knopf, 2. Conversion-Knopf).
Wenn eine Fehlermeldung wegen Zugangsdaten kommt → die 5 Google-Ads-Daten unter ⚙️ eintragen.
