<?php
/* Bewertungen zentral aus daten/config.json – nie mehr fest eintippen. */
require_once __DIR__ . '/buero-lib.php';
$ohRev = function_exists('oh_google_reviews') ? oh_google_reviews() : ['rating'=>5.0,'count'=>31];
?><?php
/**
 * Gemeinsamer <head> für die Leistungs- und Standortseiten im Dark-Design.
 * Erwartet vorher gesetzt: $seo = [
 *   'title','desc','slug','h1' (für serviceType), 'image' (optional),
 *   'service' => 'Name der Leistung' (optional -> Service-Schema),
 *   'area'    => 'Nürnberg' (optional, Standardwert Nürnberg),
 *   'faq'     => [[Frage, Antwort], ...] (optional -> FAQPage-Schema)
 * ];
 * Der Business-Knoten verweist per @id auf den Haupt-Knoten der Startseite,
 * damit Google alle Seiten demselben Betrieb zuordnet (NAP-Konsistenz).
 */
$seo   = $seo ?? [];
$s     = fn($k, $d = '') => $seo[$k] ?? $d;
$url   = 'https://oh-haustechnik.de/' . ltrim($s('slug'), '/');
$image = $s('image', 'https://oh-haustechnik.de/assets/img/lp/poster.jpg');

$graph = [[
  '@type' => ['LocalBusiness', 'Electrician'],
  '@id'   => 'https://oh-haustechnik.de/#business',
  'name'  => 'OH Haustechnik',
  'url'   => 'https://oh-haustechnik.de/',
  'telephone' => '+491757481006',
  'email'     => 'oh.Haustechnik@gmail.com',
  'priceRange'=> '€€',
  'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Dianastraße 62',
                'postalCode' => '90441', 'addressLocality' => 'Nürnberg',
                'addressRegion' => 'Bayern', 'addressCountry' => 'DE'],
  'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 49.4521, 'longitude' => 11.0767],
  'openingHoursSpecification' => [[
    '@type' => 'OpeningHoursSpecification',
    'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
    'opens' => '07:00', 'closes' => '19:00',
  ]],
  /* Verknuepfung mit den gepflegten Profilen. Erst dadurch erkennen Such-
     und KI-Systeme, dass Website, Kartenaeintrag und Bewertungen zum selben
     Betrieb gehoeren. Der Google-Eintrag steht ueber die dauerhafte CID,
     nicht ueber einen geteilten Kurzlink. */
  'sameAs' => [
    'https://www.google.com/maps?cid=1312678619063109962',
    'https://www.instagram.com/oh_haustechnik',
    'https://www.tiktok.com/@oh.haustechnik',
    'https://www.my-hammer.de/auftragnehmer/oh-haustechnik',
  ],
  'areaServed' => array_map(fn($o) => ['@type' => 'City', 'name' => $o],
                            ['Nürnberg','Fürth','Erlangen','Schwabach','Wendelstein',
                             'Zirndorf','Stein','Oberasbach','Feucht','Roth',
                             'Lauf an der Pegnitz','Altdorf bei Nürnberg',
                             'Schwaig bei Nürnberg','Röthenbach an der Pegnitz',
                             'Hersbruck','Herzogenaurach','Cadolzburg','Heroldsberg',
                             'Burgthann','Schwarzenbruck','Rednitzhembach','Allersberg']),
  'aggregateRating' => ['@type' => 'AggregateRating',
                        'ratingValue' => number_format($ohRev['rating'], 1, '.', ''),
                        'reviewCount' => (string)(int)$ohRev['count']],
]];

if ($s('service')) {
  $graph[] = [
    '@type' => 'Service',
    'name'  => $s('service'),
    'serviceType' => $s('service'),
    'provider' => ['@id' => 'https://oh-haustechnik.de/#business'],
    'areaServed' => ['@type' => 'City', 'name' => $s('area', 'Nürnberg')],
    'description' => $s('desc'),
    'url' => $url,
  ];
}
if ($s('faq')) {
  $fq = ['@type' => 'FAQPage', 'mainEntity' => []];
  foreach ($s('faq') as $q) {
    $fq['mainEntity'][] = ['@type' => 'Question', 'name' => $q[0],
                           'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]];
  }
  $graph[] = $fq;
}
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= htmlspecialchars($s('desc')) ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#0A0A0A">
<link rel="canonical" href="<?= htmlspecialchars($url) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($url) ?>">
<meta property="og:title" content="<?= htmlspecialchars($s('title')) ?>">
<meta property="og:description" content="<?= htmlspecialchars($s('desc')) ?>">
<meta property="og:image" content="<?= htmlspecialchars($image) ?>">
<meta property="og:locale" content="de_DE">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="stylesheet" href="/assets/css/site-dark.css">
<!-- Anfrage-Formular (Funnel) auch auf den dunklen Seiten verfügbar -->
<link rel="stylesheet" href="/assets/css/funnel.css">
<link rel="stylesheet" href="/assets/css/funnel-dark.css">
<title><?= htmlspecialchars($s('title')) ?></title>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
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
<?php
/* Ads-Conversion-Labels. Ohne diese Zuordnung sendet oh-track.js nur GA4-Events
   und KEINE Google-Ads-Conversion - Telefon-Klicks blieben dadurch unsichtbar.
   phone_click ist bei einem Handwerksbetrieb der wichtigste Kanal. */
$ohConv = ['lead_form_submit' => 'sMAOCPTShb8cEKywsKhC',
           'phone_click'      => 'WVjGCJyxmeMcEKywsKhC'];
?>
<script>window.OH_ADS_CONV = <?= json_encode($ohConv, JSON_UNESCAPED_SLASHES) ?>;</script>
<script defer src="/assets/js/oh-track.js"></script>
