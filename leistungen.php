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
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
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
<!-- ChatGPT Ads Measurement Pixel (Basis-Tracking, misst von sich aus nichts -
     Events werden nur per oaiq("measure", ...) gesendet, siehe danke.php) -->
<script>
  (function (w, d, s, u) {
    if (w.oaiq) return;
    var q = function () {
      q.q.push(arguments);
    };
    q.q = [];
    w.oaiq = q;
    var js = d.createElement(s);
    js.async = true;
    js.src = u;
    var f = d.getElementsByTagName(s)[0];
    f.parentNode.insertBefore(js, f);
  })(window, document, "script", "https://bzrcdn.openai.com/sdk/oaiq.min.js");

  oaiq("init", {
    pixelId: "2CGURaPThcg3RZkoYir97P"
  });
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

<!-- ============ WEITERE LEISTUNGEN ============ -->
<section class="alt-bg">
  <div class="wrap">
    <div class="shead rv">
      <div class="eyebrow">Weitere Leistungen</div>
      <h2>Alles rund um Ihre Elektrik.</h2>
      <p>Jede Leistung mit dem gleichen Anspruch an Qualität, Normkonformität und Dokumentation.</p>
    </div>
    <div class="grid">
      <div class="card rv"><div class="n">01</div><h3>Altbau­sanierung</h3><p>Alte Leitungen raus, moderner FI-Schutz rein — ohne die Bausubstanz zu zerlegen.</p><a class="more" href="/altbausanierung-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">02</div><h3>Smart Home</h3><p>KNX oder Loxone — herstellerneutral geplant, sauber dokumentiert.</p><a class="more" href="/smart-home-knx-loxone-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">03</div><h3>Photovoltaik</h3><p>Modulmontage, Wechselrichter, Speicher und Netzbetreiber-Anmeldung.</p><a class="more" href="/photovoltaik-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">04</div><h3>Zählerschrank &amp; Wallbox</h3><p>Ladepunkt fürs E-Auto — fachgerecht abgesichert und angemeldet.</p><a class="more" href="/zaehlerschrank-wallbox-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">05</div><h3>E-Check</h3><p>Elektroprüfung mit belastbarem Prüfprotokoll statt Aufkleber.</p><a class="more" href="/e-check-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">06</div><h3>Kundendienst</h3><p>FI fliegt, kein Strom im Raum? Systematische Fehlersuche statt Raten.</p><a class="more" href="/kundendienst-fehlersuche-nuernberg.php">Mehr erfahren →</a></div>
    </div>
  </div>
</section>

<!-- ============ EINSATZGEBIET ============ -->
<section>
  <div class="wrap">
    <div class="shead rv">
      <div class="eyebrow">Einsatzgebiet</div>
      <h2>Wo wir arbeiten.</h2>
      <p>Nürnberg und Umgebung — mit eigenen Seiten für die Orte, in denen wir regelmäßig unterwegs sind.</p>
    </div>
    <div class="badges rv">
      <a class="badge" href="/elektroinstallation-nuernberg.php">Nürnberg</a>
      <a class="badge" href="/elektro-sanierung-fuerth.php">Fürth</a>
      <a class="badge" href="/elektro-sanierung-erlangen.php">Erlangen</a>
      <a class="badge" href="/elektriker-schwabach.php">Schwabach</a>
      <a class="badge" href="/elektriker-wendelstein.php">Wendelstein</a>
    </div>
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
