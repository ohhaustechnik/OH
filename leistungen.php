<?php
$page_title       = 'Leistungen – Elektroinstallation, Netzwerk & Schutztechnik | OH Haustechnik';
$meta_description = 'Normgerechte Elektroinstallation, strukturierte Netzwerkverkabelung und moderne Schutztechnik — alles aus einer Hand im Raum Nürnberg, Fürth & Erlangen.';
$canonical_url    = 'https://oh-haustechnik.de/leistungen.php';
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
<meta property="og:title" content="Leistungen – OH Haustechnik">
<meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
<meta property="og:image" content="https://oh-haustechnik.de/assets/img/lp/uv.jpg">
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

<?php $oh_active = 'leistungen'; include __DIR__ . '/includes/header-dark.php'; ?>

<!-- ============ HERO ============ -->
<section class="page-hero">
  <div class="wrap">
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · Leistungen</div>
    <h1 class="rv">Unsere <span class="yellow">Leistungen</span></h1>
    <p class="rv">Normgerechte Elektroinstallation, strukturierte Netzwerkverkabelung und moderne Schutztechnik — alles aus einer Hand im Raum Nürnberg.</p>
    <div class="badges rv" style="margin-top:22px">
      <span class="badge">VDE-konform</span>
      <span class="badge">DIN-gerecht</span>
      <span class="badge">TAB-Vorgaben</span>
      <span class="badge">Markenkomponenten</span>
      <span class="badge">Dokumentiert</span>
    </div>
  </div>
</section>

<!-- ============ ELEKTROINSTALLATION ============ -->
<section id="elektroinstallation">
  <div class="wrap prose">
    <div class="eyebrow rv">Was wir für Sie leisten</div>
    <h2 class="rv">Elektroinstallation</h2>
    <p class="rv">Vor Beginn der Installation erfolgt eine detaillierte Lasten- und Leistungsberechnung, Stromkreisaufteilung sowie die Auswahl geeigneter Schutzorgane. Ergänzend dazu bieten wir strukturierte Netzwerkverkabelung und moderne Schutztechnik aus einer Hand.</p>
    <h3 class="rv" style="font-family:var(--body);text-transform:none;font-size:18px;font-weight:700;margin:24px 0 10px">Umsetzung im Neubau</h3>
    <ul class="rv">
      <li>Leitungsverlegung nach Installationszonen</li>
      <li>Installation von Haupt- und Unterverteilungen</li>
      <li>Einbau von FI-/RCD- und LS-Schutztechnik</li>
      <li>Trennung von Lastbereichen</li>
      <li>Vorbereitung für E-Mobilität oder Smart-Home</li>
      <li>Strukturierte Beschriftung der Verteiler</li>
    </ul>
    <h3 class="rv" style="font-family:var(--body);text-transform:none;font-size:18px;font-weight:700;margin:24px 0 10px">Modernisierung im Bestand</h3>
    <ul class="rv">
      <li>Austausch veralteter Installationen</li>
      <li>Umrüstung auf aktuelle Schutztechnik</li>
      <li>Erweiterung zusätzlicher Stromkreise</li>
      <li>Integration von Überspannungsschutz</li>
    </ul>
    <p class="rv"><a class="btn btn-ghost" href="/leistungen/elektroinstallation.php">Details zur Elektroinstallation →</a></p>
  </div>
</section>

<!-- ============ NETZWERKVERKABELUNG ============ -->
<section id="netzwerkverkabelung" class="alt-bg">
  <div class="wrap prose">
    <div class="eyebrow rv">Was wir für Sie leisten</div>
    <h2 class="rv">Netzwerkverkabelung</h2>
    <p class="rv">Moderne Gebäude erfordern eine leistungsfähige Dateninfrastruktur. Wir planen strukturierte Netzwerksysteme mit klarer Segmentierung und zentralem Verteilerpunkt — ideal kombinierbar mit unserer Elektroinstallation.</p>
    <ul class="rv">
      <li>Strukturierte Verkabelung nach aktuellen Standards</li>
      <li>Installation von Datendosen</li>
      <li>Patchpanel-Montage</li>
      <li>Serverschrank-Installation</li>
      <li>Saubere Kabelführung mit Dokumentation</li>
      <li>Vorbereitung für Glasfaser</li>
    </ul>
    <p class="rv"><a class="btn btn-ghost" href="/leistungen/netzwerkverkabelung.php">Details zur Netzwerkverkabelung →</a></p>
  </div>
</section>

<!-- ============ SCHUTZTECHNIK ============ -->
<section id="schutztechnik">
  <div class="wrap prose">
    <div class="eyebrow rv">Was wir für Sie leisten</div>
    <h2 class="rv">Sicherheit &amp; Schutztechnik</h2>
    <p class="rv">Ein moderner Verteiler erfüllt mehr als nur die Grundfunktion. So wird nicht nur Funktionalität, sondern auch langfristige Sicherheit gewährleistet — besonders wichtig bei der Modernisierung im Bestand.</p>
    <ul class="rv">
      <li>Fehlerstromschutzschalter (RCD)</li>
      <li>Leitungsschutzschalter (LS)</li>
      <li>Überspannungsschutz</li>
      <li>Separate Stromkreise für leistungsintensive Verbraucher</li>
      <li>Strukturierte Beschriftung</li>
    </ul>
    <p class="rv" style="font-style:italic;color:#d7d9de">„Besonders in älteren Gebäuden steht die Sicherheit im Vordergrund."</p>
    <p class="rv"><a class="btn btn-ghost" href="/leistungen/schutztechnik.php">Details zur Schutztechnik →</a></p>
  </div>
</section>

<!-- ============ CTA ============ -->
<section class="alt-bg">
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 28px"><h2>Welche Leistung benötigen Sie?</h2><p>Schildern Sie uns kurz Ihr Vorhaben — wir beraten Sie kostenlos und unverbindlich.</p></div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-y" href="/index.php#anfrage">Jetzt anfragen</a>
      <a class="btn btn-ghost" href="tel:+491757481006">Direkt anrufen</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer-dark.php'; ?>
</body>
</html>
