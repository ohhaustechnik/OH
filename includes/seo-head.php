<?php
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
  'areaServed' => array_map(fn($o) => ['@type' => 'City', 'name' => $o],
                            ['Nürnberg','Fürth','Erlangen','Schwabach','Wendelstein']),
  'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => '5.0', 'reviewCount' => '27'],
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
<link rel="icon" href="/assets/img/favicon.ico">
<link rel="stylesheet" href="/assets/css/site-dark.css">
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
<?php
/* Ads-Conversion-Labels. Ohne diese Zuordnung sendet oh-track.js nur GA4-Events
   und KEINE Google-Ads-Conversion - Telefon-Klicks blieben dadurch unsichtbar.
   phone_click ist bei einem Handwerksbetrieb der wichtigste Kanal. */
$ohConv = ['lead_form_submit' => 'sMAOCPTShb8cEKywsKhC',
           'phone_click'      => 'WVjGCJyxmeMcEKywsKhC'];
?>
<script>window.OH_ADS_CONV = <?= json_encode($ohConv, JSON_UNESCAPED_SLASHES) ?>;</script>
<script defer src="/assets/js/oh-track.js"></script>
