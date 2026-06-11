<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>GP Teil 2 EEG – Übungsprüfung Satz 1</title>
<style>
:root{--ink:#15202b;--mut:#5a6b7b;--line:#c9d4de;--accent:#1f6feb;--gold:#b8860b;--soft:#f4f7fa;}
*{box-sizing:border-box}
body{font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:var(--ink);max-width:880px;margin:0 auto;padding:28px;line-height:1.55;font-size:15px}
h1{font-size:25px;margin:0 0 4px}
h2{font-size:20px;border-bottom:3px solid var(--accent);padding-bottom:6px;margin:34px 0 14px;color:var(--accent)}
h3{font-size:16.5px;margin:20px 0 8px;color:var(--ink)}
h4{font-size:15px;margin:14px 0 4px;color:var(--gold)}
.muted{color:var(--mut)}
.deckblatt{border:2px solid var(--ink);border-radius:10px;padding:26px;margin-bottom:8px;text-align:center}
.deckblatt .sub{font-size:16px;color:var(--mut);margin-top:6px}
.badge{display:inline-block;background:var(--accent);color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;margin-top:14px}
table{border-collapse:collapse;width:100%;margin:12px 0;font-size:14px}
th,td{border:1px solid var(--line);padding:7px 10px;text-align:left;vertical-align:top}
th{background:var(--soft)}
td.c,th.c{text-align:center}
.task{margin:14px 0;padding:12px 14px;background:var(--soft);border-left:4px solid var(--accent);border-radius:0 8px 8px 0}
.task .p{float:right;font-weight:700;color:var(--accent);font-size:13px}
.task .nr{font-weight:700}
.calc{font-family:'Consolas',monospace;background:#fff;border:1px solid var(--line);border-radius:6px;padding:8px 11px;margin:6px 0;white-space:pre-wrap;font-size:13.5px}
.sol{border-left:4px solid #2e9e5b}
.sol .nr{color:#2e9e5b}
.note{background:#fff8e6;border:1px solid #e8cf86;border-radius:8px;padding:10px 13px;margin:12px 0;font-size:14px}
.fields{margin:14px 0;font-size:14px}
.fields div{border-bottom:1px solid var(--line);padding:8px 0}
hr{border:none;border-top:1px solid var(--line);margin:26px 0}
.pagebreak{page-break-before:always}
ul{margin:6px 0 6px 18px;padding:0}
li{margin:3px 0}
@media print{body{padding:0;font-size:12pt}.task{background:#fff}h2{margin-top:24px}}
</style>
</head>
<body>

<div class="deckblatt">
  <div class="muted" style="font-size:13px;letter-spacing:1px">ÜBUNGSPRÜFUNG · NICHT VERÖFFENTLICHEN</div>
  <h1>Gesellenprüfung Teil 2</h1>
  <div class="sub">Elektroniker/in für Energie- und Gebäudetechnik · Bayern</div>
  <div class="badge">Übungssatz 1 – „Sanierung Einfamilienhaus"</div>
  <table style="margin-top:22px;text-align:left">
    <tr><th style="width:35%">Prüfungsbereich</th><th class="c">Zeit</th><th class="c">Punkte</th><th class="c">Gewichtung</th></tr>
    <tr><td>A · Kundenauftrag (schriftlich)</td><td class="c">90 min</td><td class="c">100</td><td class="c">25 %</td></tr>
    <tr><td>B · Systementwurf</td><td class="c">60 min</td><td class="c">50</td><td class="c">12,5 %</td></tr>
    <tr><td>C · Funktions- und Systemanalyse</td><td class="c">60 min</td><td class="c">50</td><td class="c">12,5 %</td></tr>
    <tr><td>D · Wirtschafts- und Sozialkunde</td><td class="c">40 min</td><td class="c">30</td><td class="c">10 %</td></tr>
    <tr><th>Gesamt</th><th class="c">250 min</th><th class="c">230</th><th class="c">—</th></tr>
  </table>
  <div class="fields" style="margin-top:18px">
    <div>Name, Vorname: ______________________________________</div>
    <div>Prüflingsnummer: __________________ &nbsp;&nbsp; Datum: __________________</div>
  </div>
  <div class="muted" style="font-size:12.5px;margin-top:10px">Hilfsmittel: Tabellenbuch Elektrotechnik, Formelsammlung, netzunabhängiger Taschenrechner.<br>Bewertungsschlüssel und vollständige Musterlösung ab Seite „Lösungen".</div>
</div>

<!-- ============================ BEREICH A ============================ -->
<h2 class="pagebreak">Bereich A · Kundenauftrag <span class="muted" style="font-size:14px">(100 Punkte · 90 min)</span></h2>
<p><b>Situation:</b> Familie Berger (4 Personen) saniert ihr Einfamilienhaus. Sie sollen den Kunden zu <b>Warmwasserbereitung</b>, <b>Photovoltaik mit Speicher</b> und einer <b>Wallbox</b> beraten. Beantworten Sie die Kundenfragen fachlich und verständlich.</p>

<h3>A1 · Warmwasserbereitung</h3>
<div class="task"><span class="p">4 P</span><span class="nr">1.1</span> Der Kunde fragt, ob ein <b>elektronischer Durchlauferhitzer</b> für das Gäste-Bad sinnvoll ist. Nennen Sie je zwei Vor- und Nachteile gegenüber einem zentralen Warmwasserspeicher.</div>
<div class="task"><span class="p">6 P</span><span class="nr">1.2</span> Der Durchlauferhitzer soll <b>11 Liter/Minute</b> von <b>10 °C auf 40 °C</b> erwärmen. Berechnen Sie die erforderliche elektrische Leistung (c<sub>Wasser</sub> = 4187 J/(kg·K), Verluste vernachlässigt).</div>
<div class="task"><span class="p">5 P</span><span class="nr">1.3</span> Das Gerät wird am 400-V-Drehstromnetz betrieben. Berechnen Sie den Strom je Außenleiter.</div>
<div class="task"><span class="p">5 P</span><span class="nr">1.4</span> Welchen Leitungsquerschnitt (Cu) wählen Sie für die Zuleitung? Begründen Sie kurz.</div>
<div class="task"><span class="p">6 P</span><span class="nr">1.5</span> Pro Person werden täglich rund <b>45 Liter</b> Warmwasser (10 °C → 40 °C) verbraucht. Berechnen Sie die <b>jährlichen Stromkosten</b> für 4 Personen bei einem Strompreis von <b>0,32 €/kWh</b>.</div>

<h3>A2 · Photovoltaik mit Speicher</h3>
<div class="task"><span class="p">5 P</span><span class="nr">2.1</span> Der Jahresstromverbrauch liegt bei <b>4500 kWh</b>. Schätzen Sie mit der Faustformel die sinnvolle Anlagenleistung in kWp ab.</div>
<div class="task"><span class="p">5 P</span><span class="nr">2.2</span> Wie viele Module benötigen Sie, wenn ein Modul <b>430 Wp</b> liefert?</div>
<div class="task"><span class="p">5 P</span><span class="nr">2.3</span> Berechnen Sie den zu erwartenden <b>Jahresertrag</b> (950 Volllaststunden) und die jährliche <b>CO₂-Einsparung</b> (0,40 kg CO₂/kWh).</div>
<div class="task"><span class="p">4 P</span><span class="nr">2.4</span> Warum muss eine PV-Anlage sowohl auf der <b>DC- als auch auf der AC-Seite</b> freischaltbar sein?</div>
<div class="task"><span class="p">5 P</span><span class="nr">2.5</span> Ab welcher PV-Leistung wird üblicherweise eine Blitzschutzanlage gefordert und welche Klasse? Liegt die geplante Anlage darüber oder darunter?</div>

<h3>A3 · Wallbox / Ladeinfrastruktur</h3>
<div class="task"><span class="p">5 P</span><span class="nr">3.1</span> Erklären Sie den Unterschied zwischen <b>statischem</b> und <b>dynamischem Lastmanagement</b>.</div>
<div class="task"><span class="p">6 P</span><span class="nr">3.2</span> Die Wallbox hat <b>11 kW</b> Ladeleistung (400 V, 3~). Berechnen Sie den Ladestrom und nennen Sie eine passende Absicherung.</div>
<div class="task"><span class="p">4 P</span><span class="nr">3.3</span> Welche Schutzeinrichtung ist bei Wallboxen für den DC-Fehlerschutz vorgeschrieben und warum?</div>
<div class="task"><span class="p">5 P</span><span class="nr">3.4</span> Der Kunde möchte die Wallbox im selben Raum wie das Batteriespeichersystem und den Hausanschlusskasten unterbringen. Was entgegnen Sie ihm fachlich?</div>

<!-- ============================ BEREICH B ============================ -->
<h2 class="pagebreak">Bereich B · Systementwurf <span class="muted" style="font-size:14px">(50 Punkte · 60 min)</span></h2>
<p><b>Situation:</b> Im Keller entsteht eine <b>Hobby-Werkstatt</b> (18 m × 9 m). Planen Sie Beleuchtung, Zuleitung und Schutzkonzept.</p>

<h3>B1 · Beleuchtung</h3>
<div class="task"><span class="p">5 P</span><span class="nr">1.1</span> Für die Werkstatt sind <b>300 lx</b> gefordert. Eine LED-Leuchte liefert <b>4800 lm</b>. Berechnen Sie die nötige Leuchtenanzahl (Raum 18 m × 9 m, Nutzlichtgrad η = 0,6, Wartungsfaktor MF = 0,8).</div>
<div class="task"><span class="p">4 P</span><span class="nr">1.2</span> Erklären Sie die Begriffe <b>Lichtstrom</b>, <b>Beleuchtungsstärke</b> und <b>Lichtausbeute</b> mit Einheit.</div>
<div class="task"><span class="p">4 P</span><span class="nr">1.3</span> Nennen Sie zwei Vorteile von LEDs und zwei Qualitätsmerkmale guter Beleuchtung.</div>

<h3>B2 · Leitungsdimensionierung & Spannungsfall</h3>
<div class="task"><span class="p">6 P</span><span class="nr">2.1</span> Ein Drehstromverbraucher (I = 18 A, cos φ = 0,9) wird über <b>55 m</b> mit <b>4 mm² Cu</b> (κ = 56) angeschlossen. Berechnen Sie den Spannungsfall in Volt und in Prozent (400 V).</div>
<div class="task"><span class="p">4 P</span><span class="nr">2.2</span> Welche Grenzwerte gelten für den Spannungsfall? Nennen Sie zwei Maßnahmen, falls der Grenzwert überschritten wird.</div>
<div class="task"><span class="p">3 P</span><span class="nr">2.3</span> Ist der Querschnitt 4 mm² hinsichtlich der Strombelastbarkeit für 18 A geeignet? Begründen Sie.</div>

<h3>B3 · Netzform & Schutzkonzept</h3>
<div class="task"><span class="p">5 P</span><span class="nr">3.1</span> Nennen Sie die drei Netzsysteme und je ein typisches Merkmal/Einsatzgebiet.</div>
<div class="task"><span class="p">5 P</span><span class="nr">3.2</span> Erklären Sie die Bedeutung der Kennbuchstaben <b>T – N – C – S</b>.</div>
<div class="task"><span class="p">6 P</span><span class="nr">3.3</span> In der Unterverteilung sitzt ein <b>RCD Typ A, 40 A, 30 mA</b>. Berechnen Sie den maximal zulässigen Erdungswiderstand (U<sub>L</sub> = 50 V) und begründen Sie die Formel.</div>
<div class="task"><span class="p">4 P</span><span class="nr">3.4</span> Welche Aufgabe hat die <b>Haupterdungsschiene (HES)</b>? Nennen Sie vier Teile, die angeschlossen werden.</div>
<div class="task"><span class="p">3 P</span><span class="nr">3.5</span> Nennen Sie die Aufgaben der Überspannungsableiter <b>Typ 1, 2 und 3</b>.</div>

<!-- ============================ BEREICH C ============================ -->
<h2 class="pagebreak">Bereich C · Funktions- und Systemanalyse <span class="muted" style="font-size:14px">(50 Punkte · 60 min)</span></h2>
<p><b>Situation:</b> Eine vorhandene Anlage mit Drehstrommotor und Schützsteuerung ist <b>zu prüfen</b> und ein Fehler zu analysieren.</p>

<h3>C1 · Messen & Prüfen</h3>
<div class="task"><span class="p">5 P</span><span class="nr">1.1</span> Nennen Sie die Schritte der <b>Erstprüfung</b> (Messungen) in der richtigen Reihenfolge.</div>
<div class="task"><span class="p">4 P</span><span class="nr">1.2</span> Geben Sie die Grenzwerte an: Isolationswiderstand, Schutzleiterwiderstand, RCD-Auslösung bei I<sub>ΔN</sub>.</div>
<div class="task"><span class="p">5 P</span><span class="nr">1.3</span> Mit welcher <b>Messspannung</b> wird der Isolationswiderstand eines 230/400-V-Kreises gemessen, und warum wird mit Gleichspannung gemessen?</div>

<h3>C2 · Motor & Drehstrom</h3>
<div class="task"><span class="p">6 P</span><span class="nr">2.1</span> Auf dem Leistungsschild steht <b>230/400 V</b>. Welche Schaltung wählen Sie am 400-V-Netz? Wie werden die Brücken gesetzt? Begründung.</div>
<div class="task"><span class="p">6 P</span><span class="nr">2.2</span> Berechnen Sie den Bemessungsstrom: <b>P = 4 kW, η = 0,86, cos φ = 0,84, 400 V (3~)</b>. Auf welchen Wert stellen Sie den Motorschutzschalter ein?</div>
<div class="task"><span class="p">4 P</span><span class="nr">2.3</span> Wie ändern Sie die Drehrichtung des Motors?</div>
<div class="task"><span class="p">5 P</span><span class="nr">2.4</span> Der Motor soll am Einphasennetz laufen. Erklären Sie die <b>Steinmetzschaltung</b> und nennen Sie die ungefähre Leistungsänderung.</div>

<h3>C3 · Schütz-, Steuerungs- & Fehlertechnik</h3>
<div class="task"><span class="p">6 P</span><span class="nr">3.1</span> Beschreiben Sie Aufbau und Funktion einer <b>Selbsthalteschaltung</b> (mit Selbsthaltung über Schließer).</div>
<div class="task"><span class="p">5 P</span><span class="nr">3.2</span> Bei einer <b>Wendeschützschaltung</b> sind die beiden Schütze zu verriegeln. Welche Arten der Verriegelung gibt es und warum ist sie nötig?</div>
<div class="task"><span class="p">4 P</span><span class="nr">3.3</span> Worin unterscheiden sich <b>Schütz</b> und <b>Relais</b>?</div>
<div class="task"><span class="p">5 P</span><span class="nr">3.4</span> <b>Fehleranalyse:</b> Ein Drehstrommotor brummt, läuft aber nicht an und zieht hohen Strom. Nennen Sie drei mögliche Ursachen.</div>

<!-- ============================ BEREICH D ============================ -->
<h2 class="pagebreak">Bereich D · Wirtschafts- und Sozialkunde <span class="muted" style="font-size:14px">(30 Punkte · 40 min)</span></h2>
<div class="task"><span class="p">4 P</span><span class="nr">1</span> Nennen Sie die <b>fünf Zweige der Sozialversicherung</b> in Deutschland.</div>
<div class="task"><span class="p">4 P</span><span class="nr">2</span> Was versteht man unter <b>Tarifautonomie</b>? Wer schließt Tarifverträge ab?</div>
<div class="task"><span class="p">4 P</span><span class="nr">3</span> Erklären Sie die Merkmale der <b>sozialen Marktwirtschaft</b> (zwei Punkte).</div>
<div class="task"><span class="p">5 P</span><span class="nr">4</span> Ein Kunde zahlt eine Rechnung trotz Fälligkeit nicht. Beschreiben Sie das <b>außergerichtliche und gerichtliche Mahnverfahren</b> in Stichpunkten.</div>
<div class="task"><span class="p">4 P</span><span class="nr">5</span> Nennen Sie zwei Pflichten des Arbeitgebers und zwei Pflichten des Arbeitnehmers aus dem Arbeitsvertrag.</div>
<div class="task"><span class="p">4 P</span><span class="nr">6</span> Was ist der Unterschied zwischen <b>Brutto- und Nettolohn</b>? Nennen Sie zwei Abzüge.</div>
<div class="task"><span class="p">5 P</span><span class="nr">7</span> Welche Aufgaben hat der <b>Betriebsrat</b> und ab welcher Beschäftigtenzahl kann er gewählt werden?</div>

<!-- ============================ LÖSUNGEN ============================ -->
<h2 class="pagebreak">Lösungen, Musterrechenwege &amp; Begründungen</h2>

<h3>Bereich A · Kundenauftrag</h3>
<div class="task sol"><span class="nr">1.1</span> <b>Vorteile DLE:</b> erwärmt nur bei Bedarf (keine Speicher-/Bereitschaftsverluste), kein Platz für Speicher nötig, immer frisches Wasser (hygienisch, keine Legionellen). <b>Nachteile:</b> hohe Anschlussleistung/Drehstrom nötig, hoher Momentanstrom, bei mehreren Zapfstellen gleichzeitig begrenzt.</div>
<div class="task sol"><span class="nr">1.2</span>
<div class="calc">P = m · c · Δϑ / t
m = 11 kg (11 l/min) · t = 60 s · Δϑ = 40−10 = 30 K
P = (11 · 4187 · 30) / 60
P = 1.381.710 / 60
P ≈ 23.029 W ≈ 23,0 kW</div></div>
<div class="task sol"><span class="nr">1.3</span>
<div class="calc">I = P / (√3 · U)
I = 23.029 / (1,732 · 400)
I = 23.029 / 692,8
I ≈ 33,2 A</div></div>
<div class="task sol"><span class="nr">1.4</span> Bei ≈ 33 A ist <b>6 mm² Cu</b> zu wählen. Begründung: 4 mm² ist je nach Verlegeart nur bis ca. 27–32 A belastbar; 6 mm² (Iz ≈ 40 A) bietet ausreichend Reserve und begrenzt zugleich den Spannungsfall. Absicherung passend (z. B. 3× B/C 35 A nach Herstellerangabe des Geräts).</div>
<div class="task sol"><span class="nr">1.5</span>
<div class="calc">Energie je Person/Tag: Q = m · c · Δϑ
Q = 45 · 4187 · 30 = 5.652.450 J = 1,57 kWh
4 Personen: 4 · 1,57 = 6,28 kWh/Tag
Jahr: 6,28 · 365 ≈ 2.293 kWh/a
Kosten: 2.293 · 0,32 € ≈ 733 € / Jahr</div></div>
<div class="task sol"><span class="nr">2.1</span>
<div class="calc">Faustformel: P ≈ (Verbrauch/1000) · 2
P ≈ (4500/1000) · 2 = 9 kWp</div></div>
<div class="task sol"><span class="nr">2.2</span>
<div class="calc">n = P_gesamt / P_Modul = 9000 / 430 = 20,9 → 21 Module
(21 · 430 = 9030 Wp ≈ 9 kWp)</div></div>
<div class="task sol"><span class="nr">2.3</span>
<div class="calc">E = P · h = 9 kWp · 950 h = 8550 kWh/a
CO₂ = 8550 · 0,40 = 3420 kg/a (≈ 3,4 t)</div></div>
<div class="task sol"><span class="nr">2.4</span> Für Wartung/Reparatur müssen Generator und Wechselrichter <b>spannungsfrei</b> geschaltet werden können. Die DC-Seite steht unter Gleichspannung (Module liefern bei Licht immer), die AC-Seite unter Netzspannung – beide Seiten brauchen einen abschließbaren Lasttrennschalter (Schutz vor Wiedereinschalten).</div>
<div class="task sol"><span class="nr">2.5</span> Ab <b>10 kWp</b> wird (VdS 2010) eine Blitzschutzanlage <b>Klasse III (LPS III)</b> mit Überspannungsschutz und Potentialausgleich empfohlen/gefordert. Die geplante Anlage (9 kWp) liegt knapp <b>darunter</b> – koordinierter Überspannungsschutz ist dennoch sinnvoll.</div>
<div class="task sol"><span class="nr">3.1</span> <b>Statisch:</b> der Wallbox wird eine feste Leistungsreserve dauerhaft zugewiesen – ungenutzte Reserve bleibt liegen. <b>Dynamisch:</b> der aktuelle Gebäudeverbrauch wird gemessen, die Restleistung wird der Wallbox zugeteilt – der Hausanschluss wird optimal ausgenutzt und nicht überlastet.</div>
<div class="task sol"><span class="nr">3.2</span>
<div class="calc">I = P / (√3 · U) = 11.000 / (1,732 · 400)
I = 11.000 / 692,8 ≈ 15,9 A
→ Absicherung 3× B16 A, Leitung mind. 5×2,5 mm² Cu
  (bei Dauerlast/Häufung ggf. 4 mm²)</div></div>
<div class="task sol"><span class="nr">3.3</span> Vorgeschrieben ist die Erkennung glatter <b>DC-Fehlerströme</b>: entweder <b>RCD Typ B</b> oder <b>RCD Typ A</b> mit zusätzlicher <b>DC-Fehlerstromüberwachung (6 mA DC)</b>. Grund: Ein normaler Typ-A-RCD würde durch DC-Anteile aus dem Fahrzeug „erblinden" und nicht mehr sicher auslösen.</div>
<div class="task sol"><span class="nr">3.4</span> Das ist <b>nicht zulässig</b>: Der HAK darf nicht in Räumen mit Batterie-/Speichersystemen montiert werden (mögliche explosionsfähige Gase, erhöhte Brandlast). Hausanschluss und Speicher sind räumlich zu trennen.</div>

<h3>Bereich B · Systementwurf</h3>
<div class="task sol"><span class="nr">1.1</span>
<div class="calc">A = 18 · 9 = 162 m²
n = (E · A) / (Φ · η · MF)
n = (300 · 162) / (4800 · 0,6 · 0,8)
n = 48.600 / 2304 ≈ 21,1 → 22 Leuchten</div></div>
<div class="task sol"><span class="nr">1.2</span> <b>Lichtstrom Φ</b> [lm]: gesamtes abgestrahltes Licht einer Quelle. <b>Beleuchtungsstärke E</b> [lx]: auf eine Fläche treffender Lichtstrom, E = Φ/A. <b>Lichtausbeute η</b> [lm/W]: Lichtstrom je aufgenommener elektrischer Leistung (Wirkungsgrad).</div>
<div class="task sol"><span class="nr">1.3</span> <b>LED-Vorteile:</b> hohe Lichtausbeute/geringer Verbrauch, sehr lange Lebensdauer, schaltfest, kein Quecksilber. <b>Qualitätsmerkmale:</b> ausreichende Beleuchtungsstärke, gute Farbwiedergabe (Ra), begrenzte Blendung/Gleichmäßigkeit, geringes Flimmern.</div>
<div class="task sol"><span class="nr">2.1</span>
<div class="calc">ΔU = √3 · l · I · cosφ / (κ · A)
ΔU = 1,732 · 55 · 18 · 0,9 / (56 · 4)
ΔU = 1543,2 / 224 ≈ 6,89 V
ΔU% = 6,89 / 400 · 100 ≈ 1,72 %  → unter 5 %, in Ordnung</div></div>
<div class="task sol"><span class="nr">2.2</span> Grenzwerte: <b>≤ 3 %</b> für Beleuchtung, <b>≤ 5 %</b> für sonstige Verbraucher/Motoren. Maßnahmen bei Überschreitung: <b>größerer Querschnitt</b> oder <b>kürzere Leitungslänge</b> (alternativ Verbraucher näher an UV / Spannung anheben).</div>
<div class="task sol"><span class="nr">2.3</span> Ja. 4 mm² Cu ist je nach Verlegeart bis ca. 27–34 A belastbar – bei 18 A ist ausreichend Reserve vorhanden. Maßgeblich ist hier ohnehin der (kleine) Spannungsfall, nicht die Strombelastbarkeit.</div>
<div class="task sol"><span class="nr">3.1</span> <b>TN-System</b> (TN-C/-S/-C-S): Körper über PEN/PE mit Betriebserder verbunden, Schutz durch Überstrom/RCD – Standard im Wohnbau. <b>TT-System:</b> eigener Anlagenerder, RCD zwingend – oft ländlich/Freileitung. <b>IT-System:</b> Quelle isoliert/über Impedanz, hohe Versorgungssicherheit – Industrie, OP/Medizin.</div>
<div class="task sol"><span class="nr">3.2</span> 1. Buchstabe (Quelle): <b>T</b> = direkt geerdet, <b>I</b> = isoliert. 2. Buchstabe (Körper): <b>T</b> = eigener Erder, <b>N</b> = mit Betriebserder verbunden. Zusatz: <b>C</b> = N und PE kombiniert (PEN), <b>S</b> = getrennt geführt.</div>
<div class="task sol"><span class="nr">3.3</span>
<div class="calc">R_A ≤ U_L / I_ΔN = 50 V / 0,03 A
R_A ≤ 1666,7 Ω</div>
Begründung: Bei einem Fehler darf die Berührungsspannung 50 V (U<sub>L</sub>) nicht überschreiten. Spätestens beim Bemessungsdifferenzstrom I<sub>ΔN</sub> löst der RCD aus, daher R<sub>A</sub> · I<sub>ΔN</sub> ≤ U<sub>L</sub>.</div>
<div class="task sol"><span class="nr">3.4</span> Die HES ist der zentrale Sammelpunkt (Sternpunkt) des Potentialausgleichs. Angeschlossen werden u. a.: <b>PE/PEN, Fundamenterder, metallene Wasser-/Gas-/Heizungsrohre, Blitzschutzanlage, Antennenerdung</b>.</div>
<div class="task sol"><span class="nr">3.5</span> <b>Typ 1</b> (Blitzstromableiter, 10/350 µs): direkter Blitzteilstrom, am Gebäudeeintritt. <b>Typ 2</b> (Überspannungsableiter, 8/20 µs): Schalt-/Ferneinkopplungen, in der UV. <b>Typ 3</b> (Geräteschutz): Feinschutz nahe empfindlichen Endgeräten.</div>

<h3>Bereich C · Funktions- und Systemanalyse</h3>
<div class="task sol"><span class="nr">1.1</span> Reihenfolge: <b>1. Besichtigen</b> → <b>2. Durchgängigkeit Schutzleiter/Potentialausgleich</b> → <b>3. Isolationswiderstand</b> → <b>4. Schutzleiterwiderstand / Schleifenimpedanz</b> → <b>5. RCD-Prüfung (Auslösestrom/-zeit)</b> → <b>6. Drehfeld/Funktionsprüfung</b>. (Erst messen, dann einschalten.)</div>
<div class="task sol"><span class="nr">1.2</span> Isolationswiderstand <b>&gt; 1 MΩ</b> (Wohnbereich), Schutzleiterwiderstand <b>&lt; 1 Ω</b>, RCD löst spätestens bei I<sub>ΔN</sub> in <b>&lt; 200 ms</b> aus (bei 5·I<sub>ΔN</sub> &lt; 40 ms).</div>
<div class="task sol"><span class="nr">1.3</span> Messspannung <b>500 V DC</b> (für 230/400-V-Kreise). Mit Gleichspannung, weil so nur der reine ohmsche Isolationswiderstand erfasst wird – kapazitive Anteile (Leitungskapazitäten) würden bei Wechselspannung das Ergebnis verfälschen.</div>
<div class="task sol"><span class="nr">2.1</span> Am 400-V-Netz <b>Sternschaltung (Y)</b>, weil jede Wicklung für 230 V ausgelegt ist und im Stern an jeder Wicklung 230 V (= 400 V/√3) anliegen. Brücken: <b>U2–V2–W2</b> verbinden (Sternpunkt), Zuleitung an U1, V1, W1.</div>
<div class="task sol"><span class="nr">2.2</span>
<div class="calc">I = P / (√3 · U · η · cosφ)
I = 4000 / (1,732 · 400 · 0,86 · 0,84)
I = 4000 / 500,5
I ≈ 7,99 A ≈ 8,0 A
→ Motorschutzschalter auf ca. 8 A einstellen (Bemessungsstrom).</div></div>
<div class="task sol"><span class="nr">2.3</span> Zwei Außenleiter vertauschen (z. B. L1 ↔ L2) – das Drehfeld kehrt sich um, der Motor läuft rückwärts.</div>
<div class="task sol"><span class="nr">2.4</span> Am Einphasennetz fehlt das Drehfeld. Ein <b>Betriebskondensator</b> erzeugt eine phasenverschobene Hilfsphase für die dritte Wicklung → es entsteht ein Drehfeld, der Motor läuft an. Die Leistung sinkt dabei auf ca. <b>70 %</b> der Nennleistung.</div>
<div class="task sol"><span class="nr">3.1</span> Mit dem Schließer-Taster <b>S1 (Ein)</b> wird das Schütz <b>K1</b> erregt; ein Schließer-Hilfskontakt von K1 liegt parallel zu S1 und hält K1 nach Loslassen weiter (<b>Selbsthaltung</b>). Der Öffner-Taster <b>S0 (Aus)</b> unterbricht den Strompfad → K1 fällt ab. So bleibt der Verbraucher nach kurzem Tasten eingeschaltet, schaltet aber bei Spannungsausfall sicher ab (kein Selbstanlauf).</div>
<div class="task sol"><span class="nr">3.2</span> <b>Elektrische Verriegelung</b> (Öffner des Gegenschützes im Strompfad) und <b>mechanische Verriegelung</b> (Wippe). Notwendig, damit nie beide Schütze gleichzeitig anziehen – sonst <b>Kurzschluss</b> zwischen zwei Außenleitern (zwei Phasen würden vertauscht gleichzeitig geschaltet).</div>
<div class="task sol"><span class="nr">3.3</span> Ein <b>Schütz</b> schaltet hohe Leistungen/Lastströme (Hauptstromkreis, große Kontakte, Löschkammer); ein <b>Relais</b> schaltet kleine Steuer-/Signalströme. Schütze haben definierte Gebrauchskategorien (AC-3 usw.).</div>
<div class="task sol"><span class="nr">3.4</span> Mögliche Ursachen: <b>eine Phase fehlt</b> (Sicherung/Leitungsbruch → „Einphasenlauf"), <b>festsitzendes Lager / blockierte Mechanik</b>, <b>falsche Schaltung</b> (Dreieck statt Stern) oder bei Steinmetz <b>defekter Betriebskondensator</b>. Folge: hohes Brummen, kein Anlauf, Überstrom.</div>

<h3>Bereich D · Wirtschafts- und Sozialkunde</h3>
<div class="task sol"><span class="nr">1</span> Kranken-, Pflege-, Renten-, Arbeitslosen- und Unfallversicherung.</div>
<div class="task sol"><span class="nr">2</span> <b>Tarifautonomie:</b> das im Grundgesetz verankerte Recht der Tarifparteien, Arbeitsbedingungen ohne staatlichen Eingriff selbst zu regeln. Tarifverträge schließen <b>Gewerkschaften</b> (Arbeitnehmerseite) und <b>Arbeitgeberverbände / einzelne Arbeitgeber</b> ab.</div>
<div class="task sol"><span class="nr">3</span> Kombination aus <b>freier Marktwirtschaft</b> (Wettbewerb, Privateigentum, freie Preisbildung) und <b>sozialem Ausgleich</b> (Sozialversicherung, Arbeitsschutz, Kündigungsschutz, Umverteilung) – „so viel Markt wie möglich, so viel Staat wie nötig".</div>
<div class="task sol"><span class="nr">4</span> <b>Außergerichtlich:</b> Zahlungserinnerung/Mahnung(en), Verzug tritt spätestens 30 Tage nach Fälligkeit/Rechnung ein (Verzugszinsen). <b>Gerichtlich:</b> Antrag auf <b>Mahnbescheid</b> beim Mahngericht → bei Widerspruch streitiges Verfahren, sonst <b>Vollstreckungsbescheid</b> → Zwangsvollstreckung (Gerichtsvollzieher).</div>
<div class="task sol"><span class="nr">5</span> <b>Arbeitgeber:</b> Lohnzahlung, Fürsorge-/Schutzpflicht (Arbeitsschutz), Urlaubsgewährung. <b>Arbeitnehmer:</b> Arbeitspflicht (persönlich), Sorgfalts-/Treuepflicht, Verschwiegenheit.</div>
<div class="task sol"><span class="nr">6</span> <b>Bruttolohn</b> = vereinbarter Lohn vor Abzügen; <b>Nettolohn</b> = ausgezahlter Betrag nach Abzügen. Abzüge: <b>Lohnsteuer/Soli/ggf. Kirchensteuer</b> und <b>Sozialversicherungsbeiträge</b> (AN-Anteil).</div>
<div class="task sol"><span class="nr">7</span> Der Betriebsrat vertritt die Interessen der Beschäftigten (Mitbestimmung z. B. bei Arbeitszeit, Einstellungen, Kündigungen, Arbeitsschutz). Er kann in Betrieben mit <b>mindestens 5</b> ständig wahlberechtigten Arbeitnehmern gewählt werden.</div>

<!-- ============================ BEWERTUNG ============================ -->
<h2 class="pagebreak">Bewertungsschlüssel &amp; Auswertung</h2>
<h4>Notenschlüssel (IHK/HWK-Standard, Punkte in % der Gesamtpunktzahl)</h4>
<table>
<tr><th>Erreichte Leistung</th><th class="c">Note</th><th class="c">Bewertung</th></tr>
<tr><td>92 – 100 %</td><td class="c">1</td><td class="c">sehr gut</td></tr>
<tr><td>81 – &lt; 92 %</td><td class="c">2</td><td class="c">gut</td></tr>
<tr><td>67 – &lt; 81 %</td><td class="c">3</td><td class="c">befriedigend</td></tr>
<tr><td>50 – &lt; 67 %</td><td class="c">4</td><td class="c">ausreichend</td></tr>
<tr><td>30 – &lt; 50 %</td><td class="c">5</td><td class="c">mangelhaft</td></tr>
<tr><td>0 – &lt; 30 %</td><td class="c">6</td><td class="c">ungenügend</td></tr>
</table>
<div class="note"><b>Bestehensregel:</b> Teil 2 ist bestanden, wenn das Gesamtergebnis sowie die Bereiche im Mittel mindestens „ausreichend" sind und kein Prüfungsbereich mit „ungenügend" bewertet wurde. Umrechnung: erreichte Punkte / 230 · 100 %.</div>

<h4>Themenübersicht dieses Satzes</h4>
<table>
<tr><th>Bereich</th><th>Behandelte Themen</th></tr>
<tr><td>A · Kundenauftrag</td><td>Warmwasser/Durchlauferhitzer, Leistungs-/Strom-/Kostenberechnung, Leitungsquerschnitt, PV-Auslegung &amp; Ertrag, Freischaltung, Blitzschutz ab 10 kWp, Wallbox/Lastmanagement, RCD Typ B, HAK-Aufstellung</td></tr>
<tr><td>B · Systementwurf</td><td>Lichtberechnung, Lichtgrößen, LED, Spannungsfall (3~), Strombelastbarkeit, Netzformen TN/TT/IT, Kennbuchstaben, RCD/Erdungswiderstand, HES/Potentialausgleich, SPD Typ 1/2/3</td></tr>
<tr><td>C · Funktions-/Systemanalyse</td><td>Erstprüfung &amp; Grenzwerte, Isolationsmessung, Stern-/Dreieck, Motorbemessungsstrom, Drehrichtung, Steinmetz, Selbsthaltung, Wendeschütz/Verriegelung, Schütz vs. Relais, Fehleranalyse</td></tr>
<tr><td>D · Wirtschaft/Sozialkunde</td><td>Sozialversicherung, Tarifautonomie, soziale Marktwirtschaft, Mahnverfahren, Arbeitsvertragspflichten, Brutto/Netto, Betriebsrat</td></tr>
</table>

<h4>Einschätzung des Schwierigkeitsgrades</h4>
<p><b>Mittel bis anspruchsvoll – realistisches GP-Teil-2-Niveau.</b> Die Rechenaufgaben (1.2/1.3/1.5, 2.1–2.3 B, 2.2 C) entsprechen typischen Prüfungsrechnungen und sind ohne Tabellenbuch in der vorgesehenen Zeit lösbar. Anspruchsvoll sind die <b>Begründungs- und Transferaufgaben</b> (Freischaltung, RCD Typ B, Verriegelung, Fehleranalyse), weil hier Verständnis statt Auswendiglernen gefragt ist. Empfohlene Wiederholung bei Schwächen: <b>Spannungsfall &amp; Querschnitt</b>, <b>Schutzmaßnahmen/RCD</b> und <b>Schützsteuerungen</b>.</p>

<hr>
<p class="muted" style="font-size:12.5px;text-align:center">Eigenständig erstellte Übungsprüfung in Anlehnung an Aufbau, Stil und Themengewichtung der GP Teil 2 (EEG, Bayern). Keine Kopie offizieller Prüfungsaufgaben. Zahlenwerte und Szenarien frei gewählt. © Lernbereich OH Haustechnik – nur intern.</p>

</body>
</html>
