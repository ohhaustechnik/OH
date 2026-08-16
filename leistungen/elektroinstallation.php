<?php
$page_title       = 'Elektroinstallation Nürnberg – Neubau & Modernisierung | OH Haustechnik';
$meta_description = 'Fachgerechte Planung und Umsetzung elektrischer Anlagen im Neubau und Bestand — normgerecht nach VDE, DIN und aktuellen TAB-Vorgaben. Raum Nürnberg.';
$canonical_url    = 'https://oh-haustechnik.de/leistungen/elektroinstallation.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#0A0A0A">
<link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
<meta property="og:title" content="Elektroinstallation Nürnberg – OH Haustechnik">
<meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
<meta property="og:image" content="https://oh-haustechnik.de/assets/img/lp/altbau.jpg">
<meta property="og:locale" content="de_DE">
<link rel="icon" href="/assets/img/favicon.ico">
<link rel="stylesheet" href="/assets/css/site-dark.css">
<title><?= htmlspecialchars($page_title) ?></title>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17801418796"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'AW-17801418796');
  gtag('config', 'G-004VQKCXXC');
</script>
<script defer src="/assets/js/oh-track.js"></script>
</head>
<body>

<?php $oh_active = 'leistungen'; include __DIR__ . '/../includes/header-dark.php'; ?>

<!-- ============ HERO ============ -->
<section class="page-hero">
  <div class="wrap">
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · <a href="/leistungen.php">Leistungen</a> · Elektroinstallation</div>
    <h1 class="rv">Elektro<wbr>installation</h1>
    <p class="rv">Fachgerechte Planung und Umsetzung elektrischer Anlagen — im Neubau und im Bestand. Normgerecht nach VDE, DIN und aktuellen TAB-Vorgaben.</p>
    <div class="badges rv" style="margin-top:22px">
      <span class="badge">VDE-konform</span>
      <span class="badge">DIN-gerecht</span>
      <span class="badge">TAB-Vorgaben</span>
      <span class="badge">Markenkomponenten</span>
      <span class="badge">Dokumentiert</span>
    </div>
  </div>
</section>

<!-- ============ PLANUNG ============ -->
<section>
  <div class="wrap">
    <div class="shead rv">
      <div class="eyebrow">Der erste Schritt</div>
      <h2>Planung &amp; Auslegung.</h2>
      <p>Vor Beginn der Installation erfolgt eine detaillierte technische Analyse und Planung, um eine optimale Auslegung zu gewährleisten.</p>
    </div>
    <div class="grid">
      <div class="card rv"><div class="n">01</div><h3>Leistungsberechnung</h3><p>Lasten- und Leistungsberechnung für eine korrekte Auslegung aller Stromkreise und Schutzorgane.</p></div>
      <div class="card rv"><div class="n">02</div><h3>Stromkreisaufteilung</h3><p>Strukturierte Aufteilung der Stromkreise mit Absicherungskonzept und Reservekapazitäten.</p></div>
      <div class="card rv"><div class="n">03</div><h3>Abstimmung</h3><p>Enge Abstimmung mit Bauleitung oder Architekten für eine reibungslose Integration in den Bauablauf.</p></div>
    </div>
  </div>
</section>

<!-- ============ NEUBAU ============ -->
<section class="alt-bg">
  <div class="wrap prose">
    <div class="eyebrow rv">Neubau</div>
    <h2 class="rv">Umsetzung im Neubau.</h2>
    <p class="rv">Im Neubau beginnt alles von Grund auf. Wir planen und verlegen alle Leitungen nach Installationszonen, installieren Verteiler und bereiten die Anlage für zukünftige Anforderungen vor. Kombinierbar mit unserer <a href="/leistungen/netzwerkverkabelung.php" style="text-decoration:underline">strukturierten Netzwerkverkabelung</a> aus einer Hand.</p>
    <ul class="rv">
      <li>Leitungsverlegung nach Installationszonen</li>
      <li>Installation von Haupt- und Unterverteilungen</li>
      <li>Einbau von FI-/RCD- und LS-Schutztechnik</li>
      <li>Trennung von Lastbereichen</li>
      <li>Vorbereitung für E-Mobilität oder Smart-Home-Systeme</li>
      <li>Strukturierte Beschriftung der Verteiler</li>
    </ul>
    <h3 class="rv" style="font-family:var(--body);text-transform:none;font-size:18px;font-weight:700;margin:24px 0 10px">Neubau-Checkliste</h3>
    <p class="rv">Jede Neuinstallation wird nach festem Standard durchgeführt:</p>
    <div class="badges rv">
      <span class="badge">Installationsplanung nach DIN 18015</span>
      <span class="badge">VDE 0100-konforme Ausführung</span>
      <span class="badge">Verteilerplan &amp; Beschriftung</span>
      <span class="badge">Abnahmedokumentation</span>
    </div>
  </div>
</section>

<!-- ============ ÄLTERE GEBÄUDE HINWEIS ============ -->
<section>
  <div class="wrap prose">
    <h3 class="rv" style="font-size:clamp(24px,3.5vw,32px);margin-bottom:14px">Wichtig für ältere Gebäude</h3>
    <p class="rv">Besonders in älteren Gebäuden steht die Sicherheit im Vordergrund. Veraltete Installationen ohne Fehlerstromschutz entsprechen nicht mehr den aktuellen Normen und können ein erhebliches Sicherheitsrisiko darstellen.</p>
    <p class="rv">Eine zeitgemäße Modernisierung erhöht nicht nur die Sicherheit, sondern auch die Belastbarkeit für heutige und zukünftige elektrische Verbraucher. <a href="/leistungen/schutztechnik.php" style="text-decoration:underline">Mehr zur modernen Schutztechnik.</a></p>
  </div>
</section>

<!-- ============ BESTAND ============ -->
<section class="alt-bg">
  <div class="wrap prose">
    <div class="eyebrow rv">Bestand</div>
    <h2 class="rv">Modernisierung im Bestand.</h2>
    <p class="rv">Veraltete Installationen sicher und normgerecht auf den neuesten Stand bringen.</p>
    <ul class="rv">
      <li>Austausch veralteter Installationen</li>
      <li>Umrüstung auf aktuelle Schutztechnik</li>
      <li>Anpassung an erhöhte Leistungsanforderungen</li>
      <li>Erweiterung zusätzlicher Stromkreise</li>
      <li>Integration von Überspannungsschutz</li>
    </ul>
  </div>
</section>

<!-- ============ CTA ============ -->
<section>
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 28px"><h2>Elektroinstallation anfragen.</h2><p>Schildern Sie uns kurz Ihr Vorhaben — wir beraten Sie kostenlos und unverbindlich.</p></div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-y" href="/index.php#anfrage">Jetzt anfragen</a>
      <a class="btn btn-ghost" href="tel:+491757481006">Direkt anrufen</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer-dark.php'; ?>
</body>
</html>
