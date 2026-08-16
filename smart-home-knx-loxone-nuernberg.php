<?php
$seo = [
  'title'   => 'Smart Home KNX & Loxone Nürnberg | OH Haustechnik',
  'desc'    => 'KNX- und Loxone-Installation vom Elektrofachbetrieb im Raum Nürnberg: Licht, Beschattung, Heizung und Sicherheit vernetzt — herstellerneutral geplant und sauber dokumentiert.',
  'slug'    => 'smart-home-knx-loxone-nuernberg.php',
  'service' => 'Smart-Home-Installation KNX und Loxone',
  'image'   => 'https://oh-haustechnik.de/assets/img/lp/sh.jpg',
  'faq'     => [
    ['Was kostet ein Smart Home mit KNX?', 'Der Preis hängt vom Funktionsumfang ab: Ein einzelner Raum mit Licht- und Beschattungssteuerung liegt deutlich unter einer Vollausstattung mit Heizungsregelung, Zutritt und Visualisierung. Entscheidend ist die Anzahl der Aktoren und Sensoren, nicht die Quadratmeterzahl. Sie erhalten vorab eine nachvollziehbare Kalkulation.'],
    ['KNX oder Loxone — was ist besser?', 'KNX ist ein herstellerübergreifender Standard: Sie sind nicht an einen Anbieter gebunden und können auch in zehn Jahren noch erweitern. Loxone ist ein geschlossenes System, dafür schneller in Betrieb genommen und günstiger im Einstieg. Für Neubau und langfristige Planung empfehlen wir meist KNX, für nachträgliche Projekte mit klarem Budget oft Loxone.'],
    ['Kann man Smart Home im Altbau nachrüsten?', 'Ja. Wenn ohnehin eine Elektrosanierung ansteht, ist der Aufwand gering, weil die Busleitung parallel zur neuen Elektrik verlegt wird. Ohne Sanierung gibt es Funklösungen und Aktoren für den Unterputz-Einbau hinter vorhandenen Schaltern — technisch möglich, aber begrenzter im Funktionsumfang.'],
    ['Funktioniert das Haus noch, wenn das Internet ausfällt?', 'Bei einer korrekt geplanten KNX-Anlage ja: Die Grundfunktionen wie Licht und Beschattung laufen auf dem Bus und brauchen weder Internet noch Cloud. Nur Fernzugriff und Sprachsteuerung setzen eine Verbindung voraus. Das ist ein wesentlicher Planungspunkt, den wir vorab mit Ihnen klären.'],
  ],
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<?php include __DIR__ . '/includes/seo-head.php'; ?>
</head>
<body>

<?php $oh_active = 'leistungen'; include __DIR__ . '/includes/header-dark.php'; ?>

<section class="page-hero">
  <div class="wrap">
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · <a href="/leistungen.php">Leistungen</a> · Smart Home</div>
    <h1 class="rv">Smart Home <span class="yellow">KNX &amp; Loxone</span> in Nürnberg</h1>
    <p class="rv">Licht, Beschattung, Heizung und Sicherheit vernetzt — geplant vom Elektrofachbetrieb, nicht vom Elektronikmarkt. Im Raum Nürnberg, Fürth, Schwabach und Wendelstein.</p>
    <div class="badges rv" style="margin-top:22px">
      <span class="badge">KNX</span>
      <span class="badge">Loxone</span>
      <span class="badge">Herstellerneutral geplant</span>
      <span class="badge">Dokumentiert</span>
    </div>
  </div>
</section>

<section>
  <div class="wrap prose">
    <div class="eyebrow rv">Worum es wirklich geht</div>
    <h2 class="rv">Ein Smart Home ist ein Elektroprojekt, kein Gadget-Kauf.</h2>
    <p class="rv">Die meisten enttäuschenden Smart-Home-Installationen scheitern nicht an der Technik, sondern an der Planung: zu wenige Stromkreise, kein Platz im Verteiler, keine Busleitung an den richtigen Stellen. Nachträglich lässt sich das nur mit Aufwand korrigieren.</p>
    <p class="rv">Deshalb beginnt bei uns jedes Projekt mit der Frage, was die Anlage tatsächlich können soll — und was sie in fünf Jahren zusätzlich können muss. Daraus ergibt sich die Elektroplanung: Leitungsführung, Verteilergröße, Aktorik. Die eigentliche Programmierung ist der letzte Schritt, nicht der erste.</p>

    <h2 class="rv">Was sich sinnvoll automatisieren lässt</h2>
    <ul class="rv">
      <li><strong>Licht:</strong> Szenen statt Einzelschalter, Präsenzsteuerung in Fluren und Nebenräumen, Dimmung nach Tageszeit.</li>
      <li><strong>Beschattung:</strong> Rollläden und Jalousien nach Sonnenstand und Innentemperatur — spart im Sommer spürbar Kühlbedarf.</li>
      <li><strong>Heizung:</strong> Einzelraumregelung mit Fensterkontakten, Absenkung bei Abwesenheit.</li>
      <li><strong>Sicherheit:</strong> Anwesenheitssimulation, Verriegelungsmeldungen, Rauchwarnmelder mit Vernetzung.</li>
      <li><strong>Energie:</strong> Verbrauchserfassung, Zusammenspiel mit <a href="/photovoltaik-nuernberg.php" style="text-decoration:underline">PV-Anlage</a> und Wallbox.</li>
    </ul>

    <h2 class="rv">KNX oder Loxone — die ehrliche Einordnung</h2>
    <p class="rv"><strong>KNX</strong> ist ein offener, herstellerübergreifender Standard. Geräte verschiedener Hersteller arbeiten zusammen, die Anlage bleibt über Jahrzehnte erweiterbar und Sie sind nicht von einem einzelnen Anbieter abhängig. Der Einstieg kostet mehr, die Planung ist aufwendiger — dafür ist die Investition langfristig sicher.</p>
    <p class="rv"><strong>Loxone</strong> ist ein geschlossenes System eines Herstellers. Die Inbetriebnahme geht schneller, der Einstiegspreis ist niedriger, viele Funktionen sind vorkonfiguriert. Der Preis dafür: Sie binden sich an einen Anbieter.</p>
    <p class="rv">Wir verkaufen kein System, sondern empfehlen das passende. Bei Neubau und langfristiger Planung ist das meist KNX, bei nachträglichen Projekten mit klarem Budget oft Loxone.</p>

    <h2 class="rv">Nachrüsten im Bestand</h2>
    <p class="rv">Im Altbau lohnt sich die Kombination: Wenn ohnehin eine <a href="/altbausanierung-nuernberg.php" style="text-decoration:underline">Elektrosanierung</a> ansteht, wird die Busleitung im gleichen Arbeitsgang mitverlegt. Der Mehraufwand ist dann gering, das Ergebnis vollwertig.</p>
    <p class="rv">Steht keine Sanierung an, arbeiten wir mit Unterputz-Aktoren hinter vorhandenen Schalterdosen und Funkkomponenten. Das funktioniert für Licht und Beschattung gut, stößt bei Heizungsregelung und Energiemessung aber an Grenzen. Wir sagen Ihnen vorher, was geht und was nicht.</p>

    <h2 class="rv">Was Sie von uns bekommen</h2>
    <ul class="rv">
      <li>Bedarfsklärung vor der Technikauswahl — keine Vorfestlegung auf ein System</li>
      <li>Elektroplanung mit ausreichend dimensioniertem Verteiler und Reserven</li>
      <li>Normgerechte Ausführung nach VDE, mit <a href="/leistungen/schutztechnik.php" style="text-decoration:underline">passender Schutztechnik</a></li>
      <li>Inbetriebnahme und Einweisung — Sie sollen die Anlage selbst bedienen können</li>
      <li>Vollständige Dokumentation: Verteilerplan, Gruppenadressen, Parametrierung</li>
    </ul>
    <p class="rv">Der letzte Punkt ist wichtiger, als er klingt: Eine undokumentierte KNX-Anlage kann später kaum ein anderer Betrieb sinnvoll erweitern. Sie erhalten die Projektdatei — die Anlage gehört Ihnen, nicht uns.</p>
  </div>
</section>

<section class="alt-bg">
  <div class="wrap prose">
    <h2 class="rv">Häufige Fragen</h2>
    <?php foreach ($seo['faq'] as $f): ?>
      <h3 class="rv" style="font-family:var(--body);text-transform:none;font-size:18px;font-weight:700;margin:24px 0 8px"><?= htmlspecialchars($f[0]) ?></h3>
      <p class="rv"><?= htmlspecialchars($f[1]) ?></p>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 28px"><h2>Smart Home planen lassen.</h2><p>Erzählen Sie uns, was die Anlage können soll — wir sagen Ihnen, was technisch sinnvoll ist.</p></div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-y" href="/index.php#anfrage">Jetzt anfragen</a>
      <a class="btn btn-ghost" href="tel:+491757481006">Direkt anrufen</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer-dark.php'; ?>
</body>
</html>
