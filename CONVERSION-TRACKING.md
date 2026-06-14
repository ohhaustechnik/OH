# Conversion-Wert-Tracking (Google Ads)

## Was automatisch passiert (schon gebaut ✅)

Wenn ein Kunde den Funnel absendet, wird jetzt ein **geschätzter Auftragswert**
an Google Ads / GA4 gesendet – statt eines festen Werts. So lernt Google Ads,
welche Anzeigen **echte Großaufträge** bringen und nicht nur billige Klicks.

Der Wert wird automatisch aus der Lead-Einstufung berechnet:

| Lead-Art (Beispiel)                  | Einstufung | gesendeter Wert |
|--------------------------------------|------------|-----------------|
| Haus, Komplettsanierung, mehrere Objekte | groß    | **8.000 €**     |
| mittlere Wohnung, Erweiterung        | mittel     | **2.500 €**     |
| einzelne Lampe, kleine Reparatur     | klein      | **400 €**       |

(Diese Zahlen kannst du jederzeit anpassen – sie stehen in
`includes/funnel-handler.php`, Zeile mit `$wertMap`.)

---

## Was DU einmalig in Google Ads machen musst (5 Min)

Damit der Wert wirklich als **Conversion** in Ads ankommt, brauchst du ein
„Conversion-Label". Das gibt es nur in deinem Google-Ads-Konto:

1. Google Ads → **Tools → Conversions → + Neue Conversion-Aktion**
2. Typ **„Website"**, Kategorie **„Lead / Kontaktanfrage senden"**
3. Bei „Wert" → **„Für jede Conversion unterschiedliche Werte verwenden"** wählen
   (wichtig! sonst wird der gesendete Euro-Wert ignoriert)
4. Zählung: **„Eine"**
5. Nach dem Speichern zeigt Ads dir ein Snippet mit `send_to: 'AW-17801418796/XXXXXXXX'`.
   Du brauchst nur den Teil **nach dem Schrägstrich** – das ist dein Label, z. B. `AbCdEfG123`.

### Label eintragen

In der Datei **`assets/js/oh-track.js`** ganz oben (direkt nach der ersten Zeile
`(function () {`) diese Zeile einfügen und DEIN_LABEL ersetzen:

```js
window.OH_ADS_CONV = { lead_form_submit: 'DEIN_LABEL' };
```

Fertig. Ab dann feuert jede Funnel-Anfrage eine echte Ads-Conversion **mit dem
passenden Euro-Wert**.

> Sag mir einfach dein Label, dann trage ich die Zeile selbst ein und pushe es.

---

## Optional: Kalkulator-Abschluss auch als Conversion

Der Festpreis-Kalkulator feuert bereits `kalkulator_abschluss`. Wenn du dafür
auch eine Ads-Conversion willst, einfach erweitern:

```js
window.OH_ADS_CONV = {
  lead_form_submit:    'LABEL_FUER_FORMULAR',
  kalkulator_abschluss:'LABEL_FUER_KALKULATOR'
};
```
