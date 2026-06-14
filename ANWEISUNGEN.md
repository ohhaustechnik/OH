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

### Schritt 1 (HEUTE, 10 Min): Anzeigen auf die richtige Seite schicken
Du zahlst schon für die Klicks — sie landen falsch und gehen verloren.
In Google Ads → Keywords → Keyword anklicken → **„Finale URL"** ändern:

| Keyword | Neue Finale URL |
|---|---|
| altbau elektrik erneuern | `https://oh-haustechnik.de/altbausanierung-nuernberg.php` |
| altbausanierung elektro | `https://oh-haustechnik.de/altbausanierung-nuernberg.php` |
| elektriker nürnberg | `https://oh-haustechnik.de/elektroinstallation-nuernberg.php` |
| sanierung nbg | `https://oh-haustechnik.de/wohnung-elektro-sanieren-nuernberg.php` |

### Schritt 2: Bewertungen sammeln (stärkster kostenloser Hebel)
Jedem zufriedenen Kunden den Google-Bewertungslink schicken. Ziel: von 21 → 40+.
Bei 10.000-€-Aufträgen entscheidet das oft, wer den Zuschlag bekommt.

### Schritt 3: Conversions in Google Ads sauber einstellen (siehe unten)

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

**Dein nächster EINER Schritt:** Schritt 1 — die 4 Ziel-URLs in Google Ads
umstellen (Tabelle oben). Das bringt sofort die schon bezahlten Klicks auf die
richtige Seite = mehr Anfragen, ohne einen Cent mehr Werbebudget.
