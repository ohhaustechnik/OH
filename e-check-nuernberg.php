<?php
$seo = [
  'title'   => 'E-Check Nürnberg | Elektroprüfung für Haus, Wohnung & Vermieter',
  'desc'    => 'E-Check im Raum Nürnberg: Elektroprüfung für Eigentümer, Vermieter und Hauskäufer — mit vollständigem Prüfprotokoll und allen Messwerten.',
  'slug'    => 'e-check-nuernberg.php',
  'service' => 'E-Check Elektroprüfung',
  'image'   => 'https://oh-haustechnik.de/assets/img/lp/uv.jpg',
  'faq'     => [
    ['Was kostet ein E-Check?', 'Der Preis richtet sich nach Größe und Umfang der Anlage — eine Wohnung liegt deutlich unter einem Haus mit mehreren Verteilungen und Außenbereich. Nach kurzer Beschreibung Ihres Objekts nennen wir Ihnen vorab einen verbindlichen Preis, damit Sie nicht mit einer offenen Rechnung dastehen.'],
    ['Wie oft sollte man einen E-Check machen lassen?', 'Für private Eigentümer gibt es keine gesetzliche Pflicht. Üblich sind längere Abstände bei selbstgenutzten Objekten und kürzere bei vermieteten Wohnungen, weil dort die Verkehrssicherungspflicht gegenüber den Mietern greift. Nach einem Kauf, einer Sanierung oder bei sichtbar alter Installation ist eine Prüfung in jedem Fall sinnvoll.'],
    ['Lohnt sich ein E-Check vor dem Hauskauf?', 'Ja, häufig sogar besonders. Die Elektrik ist einer der Posten, die nach dem Kauf richtig teuer werden können — eine Anlage ohne Fehlerstromschutz und mit zu wenigen Stromkreisen bedeutet meist eine komplette Sanierung. Das vorher zu wissen, verändert entweder Ihre Kaufentscheidung oder Ihre Verhandlungsposition.'],
    ['Was passiert, wenn Mängel gefunden werden?', 'Sie erhalten einen klaren Befund mit Einordnung: Was ist ein Sicherheitsmangel, der behoben werden muss, und was ist ein Hinweis ohne akute Gefahr. Wir reparieren nichts ungefragt mit, sondern legen Ihnen Mängel und Kosten vorher vor. Auf Wunsch beheben wir sie im selben Termin.'],
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
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · <a href="/leistungen.php">Leistungen</a> · E-Check</div>
    <h1 class="rv"><span class="yellow">E-Check</span> in Nürnberg</h1>
    <p class="rv">Elektroprüfung für Eigentümer, Vermieter und Hauskäufer im Raum Nürnberg — mit belastbarem Prüfprotokoll statt Aufkleber ohne Substanz.</p>
    <div class="badges rv" style="margin-top:22px">
      <span class="badge">Prüfprotokoll</span>
      <span class="badge">Messwerte dokumentiert</span>
      <span class="badge">Preis vorab</span>
      <span class="badge">VDE-konform</span>
    </div>
  </div>
</section>

<section>
  <div class="wrap prose">
    <div class="eyebrow rv">Für wen sich die Prüfung lohnt</div>
    <h2 class="rv">Wann ein E-Check sinnvoll ist</h2>
    <ul class="rv">
      <li><strong>Vor dem Hauskauf:</strong> Klarheit über den Zustand der Elektrik, bevor Sie unterschreiben — die Elektrik ist einer der teuersten Nachrüstposten überhaupt.</li>
      <li><strong>Als Vermieter:</strong> Nachweis der Verkehrssicherungspflicht gegenüber Ihren Mietern, besonders bei älteren Installationen ohne Fehlerstromschutz.</li>
      <li><strong>Nach einer Erbschaft oder Übernahme:</strong> Objektive Bestandsaufnahme, bevor über Sanierung oder Verkauf entschieden wird.</li>
      <li><strong>Bei Verdacht auf Mängel:</strong> Warme Steckdosen, flackerndes Licht, ein FI, der gelegentlich auslöst — das gehört gemessen, nicht abgewartet.</li>
      <li><strong>Im Altbau generell:</strong> Als Entscheidungsgrundlage vor einer <a href="/altbausanierung-nuernberg.php" style="text-decoration:underline">Elektrosanierung</a>.</li>
    </ul>

    <h2 class="rv">Was geprüft wird</h2>
    <p class="rv">Die Prüfung besteht aus drei Teilen: Besichtigen, Erproben, Messen. Der Sichtteil deckt bereits einen großen Teil der Mängel auf — beschädigte Isolierungen, unzulässige Provisorien, überlastete Mehrfachsteckdosen. Anschließend wird gemessen:</p>
    <ul class="rv">
      <li>Schutzleiterwiderstand und Durchgängigkeit des Schutzleiters</li>
      <li>Isolationswiderstand der Stromkreise</li>
      <li>Wirksamkeit und Auslösezeit der Fehlerstrom-Schutzeinrichtungen (RCD)</li>
      <li>Schleifenimpedanz und Abschaltbedingungen</li>
      <li>Zustand von Verteilung, Klemmstellen und Beschriftung</li>
    </ul>

    <h2 class="rv">Was Sie hinterher in der Hand haben</h2>
    <p class="rv">Ein Prüfprotokoll mit sämtlichen Messwerten und einem eindeutigen Ergebnis je Position. Das ist der Unterschied zwischen einer echten Prüfung und einem Aufkleber: Im Schadensfall zählt die Dokumentation.</p>
    <p class="rv">Gefundene Mängel legen wir Ihnen mit Einordnung vor — akuter Sicherheitsmangel oder Hinweis ohne unmittelbare Gefahr. Sie entscheiden, was behoben wird.</p>

    <h2 class="rv">Besonders relevant im Altbau</h2>
    <p class="rv">In Gebäuden aus der Zeit vor den 1980er-Jahren fehlt der Fehlerstromschutz häufig vollständig. Gleichzeitig ist die elektrische Last durch moderne Haushaltsgeräte, Heimnetzwerke und Ladetechnik deutlich gestiegen. Die Prüfung zeigt objektiv, ob die vorhandene Anlage dem noch gewachsen ist — und liefert die Grundlage für eine <a href="/leistungen/schutztechnik.php" style="text-decoration:underline">Nachrüstung der Schutztechnik</a>.</p>
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
    <div class="shead rv" style="margin:0 auto 28px"><h2>E-Check anfragen.</h2><p>Sagen Sie uns kurz, um welches Objekt es geht — dann nennen wir Ihnen den Preis vorab.</p></div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-y" href="/index.php#anfrage">Jetzt anfragen</a>
      <a class="btn btn-ghost" href="tel:+491757481006">Direkt anrufen</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer-dark.php'; ?>
</body>
</html>
