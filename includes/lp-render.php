<?php
/**
 * Gemeinsamer Renderer fuer OH-Kampagnen-Landing-Pages — Premium-Design.
 * Eine Seite = ein Ziel = eine klare Hierarchie (Nivea-Conversion-Formel),
 * aber mit eigener Marken-Identitaet statt generischem KI-Template-Look:
 *   - Design wie die Hauptseite: dunkel (#0A0A0A), Akzent #6E9BE0, Anton + Inter
 *   - Tiefes Navy-Ink + warmer Signal-Bernstein + ruhige Cremeflaechen
 *   - Tiefe durch Schichtung/Schatten statt Neon
 * Serverseitig gerendert (SEO), nutzt den bestehenden 7-Schritte-Funnel + Quellen-Tracking.
 */
$LP = $LP ?? [];
$g = fn($k, $d = '') => $LP[$k] ?? $d;
$quelle = $g('quelle', 'lp');
require_once __DIR__ . '/buero-lib.php';
$ohReviews = function_exists('oh_google_reviews') ? oh_google_reviews() : ['rating' => 5.0, 'count' => 21];
$ohRating  = number_format($ohReviews['rating'], 1, ',', '');
$ohCount   = (int)$ohReviews['count'];
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($g('title')) ?></title>
<meta name="description" content="<?= htmlspecialchars($g('meta')) ?>">
<link rel="canonical" href="https://oh-haustechnik.de/<?= htmlspecialchars($g('slug')) ?>">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<meta name="theme-color" content="#0A0A0A">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/funnel.css">
<link rel="stylesheet" href="assets/css/funnel-dark.css">
<!-- Google tag (gtag.js) – Landingpages jetzt ebenfalls getrackt -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17801418796"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'AW-17801418796');
  gtag('config', 'G-004VQKCXXC');
</script>
<?php
/* Strukturierte Daten – jede Landingpage liefert jetzt LocalBusiness + Service(+Offer) + FAQ an Google.
   Das ist die Voraussetzung für Rich-Results und besseres lokales Ranking (Long-Tail). */
$lpGraph = [
  [
    '@type' => ['LocalBusiness', 'Electrician'],
    '@id'   => 'https://oh-haustechnik.de/#business',
    'name'  => 'OH Haustechnik',
    'telephone' => '+49 175 7481006',
    'url'   => 'https://oh-haustechnik.de/' . $g('slug'),
    /* sameAs verbindet die Website mit den gepflegten Profilen. Erst dadurch
       erkennen Such- und KI-Systeme, dass Website, Kartenaeintrag und
       Bewertungen zum selben Betrieb gehoeren. */
    'sameAs' => [
      'https://www.google.com/maps?cid=1312678619063109962',
      'https://www.instagram.com/oh_haustechnik',
      'https://www.tiktok.com/@oh.haustechnik',
      'https://www.my-hammer.de/auftragnehmer/oh-haustechnik',
    ],
    'areaServed' => ['Nürnberg', 'Fürth', 'Erlangen', 'Schwabach', 'Stein', 'Zirndorf',
                     'Oberasbach', 'Feucht', 'Wendelstein', 'Roth', 'Lauf an der Pegnitz',
                     'Altdorf bei Nürnberg', 'Schwaig bei Nürnberg', 'Hersbruck'],
    'priceRange' => $g('preis_range', '€€'),
    'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $ohReviews['rating'], 'reviewCount' => $ohCount],
  ],
];
$lpService = [
  '@type' => 'Service',
  'serviceType' => $g('h1'),
  'provider' => ['@id' => 'https://oh-haustechnik.de/#business'],
  'areaServed' => 'Nürnberg',
  'description' => $g('meta'),
];
if ($g('ab_preis')) {
  $lpService['offers'] = ['@type' => 'Offer', 'priceCurrency' => 'EUR', 'price' => (string)$g('ab_preis'), 'availability' => 'https://schema.org/InStock'];
}
$lpGraph[] = $lpService;
if ($g('faq')) {
  $fq = ['@type' => 'FAQPage', 'mainEntity' => []];
  foreach ($g('faq') as $q) $fq['mainEntity'][] = ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]];
  $lpGraph[] = $fq;
}
?>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $lpGraph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<style>
/* Dunkles Design – identische Token wie die Startseite (#0A0A0A / #6E9BE0 / Anton + Inter),
   damit Anzeige, Landingpage und Hauptseite wie aus einem Guss wirken. */
:root{
  --ink:#F5F5F5;--ink2:#F5F5F5;--ink-soft:#C9D3E4;
  --cream:#111111;--paper:#1A1A1A;
  --blue:#6E9BE0;--blue-d:#325AA0;
  --amber:#6E9BE0;--amber-d:#325AA0;
  --txt:#F5F5F5;--txt-dim:#9CA3AF;--line:rgba(255,255,255,.08);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;color:var(--txt);line-height:1.65;background:#0A0A0A;-webkit-font-smoothing:antialiased}
h1,h2,h3,.num{font-family:'Anton',Impact,'Arial Narrow',system-ui,sans-serif;font-weight:400;line-height:1.1;letter-spacing:.5px}
.wrap{max-width:1120px;margin:0 auto;padding:0 24px}
.amber{color:var(--amber)}
/* ---------- HERO ---------- */
.hero{background:linear-gradient(180deg,#111111 0%,#0A0A0A 100%);color:var(--txt);position:relative;overflow:hidden;padding:30px 0 64px;border-bottom:1px solid var(--line)}
.hero::before{content:"";position:absolute;inset:0;opacity:.7;
  background:
    radial-gradient(60% 50% at 82% 8%,rgba(110,155,224,.12),transparent 60%),
    radial-gradient(45% 40% at 8% 96%,rgba(50,90,160,.10),transparent 60%);}
.hero::after{content:"";position:absolute;inset:0;opacity:.5;pointer-events:none;
  background-image:linear-gradient(rgba(110,155,224,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(110,155,224,.05) 1px,transparent 1px);
  background-size:64px 64px;mask-image:radial-gradient(circle at 70% 0%,#000,transparent 75%)}
.hero .wrap{position:relative;z-index:2}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:8px 0 44px}
.logo{display:flex;align-items:center;gap:11px;text-decoration:none}
.logo img{height:50px;width:auto;display:block}
.logo small{font-family:'Inter',sans-serif;font-size:10px;letter-spacing:2.5px;font-weight:600;color:#9CA3AF}
.tb-call{color:#C9D3E4;text-decoration:none;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
.tb-call i{color:var(--blue)}
.eyebrow{display:inline-flex;align-items:center;gap:9px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue);margin-bottom:20px}
.eyebrow::before{content:"";width:26px;height:2px;background:var(--blue)}
.hero h1{font-size:clamp(33px,5vw,58px);max-width:16ch;margin-bottom:20px;color:#F5F5F5}
.hero .sub{font-size:clamp(16px,1.6vw,20px);color:#9CA3AF;max-width:54ch;margin-bottom:30px}
.trust{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:34px}
.chip{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#C9D3E4;background:#161616;border:1px solid rgba(255,255,255,.12);padding:9px 15px;border-radius:11px}
.chip i{color:var(--blue);font-size:12px}
.cta-row{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
.btn-primary{background:linear-gradient(135deg,#6E9BE0,#325AA0);color:#0A0A0A;border:none;padding:18px 32px;border-radius:14px;font-family:'Inter',sans-serif;font-size:16px;font-weight:800;cursor:pointer;box-shadow:0 14px 34px rgba(110,155,224,.28);transition:transform .16s,box-shadow .16s;display:inline-flex;align-items:center;gap:10px}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 44px rgba(110,155,224,.40)}
/* Dauerhafter Aufmerksamkeits-Puls auf dem Haupt-CTA (zieht den Blick → mehr Klicks) */
@keyframes lpCtaAttract{0%,100%{box-shadow:0 0 0 0 rgba(110,155,224,.5)}50%{box-shadow:0 0 26px 6px rgba(110,155,224,.40),0 0 0 8px rgba(110,155,224,.10)}}
.cta-row .btn-primary,.ctablock .btn-primary{animation:lpCtaAttract 2.4s ease-in-out infinite}
.cta-row .btn-primary:hover,.ctablock .btn-primary:hover{animation:none}
@media (prefers-reduced-motion: reduce){.cta-row .btn-primary,.ctablock .btn-primary{animation:none}}
.btn-ghost{color:#F5F5F5;text-decoration:none;font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:9px;padding:16px 24px;border:1.5px solid rgba(255,255,255,.18);border-radius:14px;transition:border-color .16s,background .16s;background:transparent}
.btn-ghost:hover{border-color:#6E9BE0;background:rgba(110,155,224,.08)}
.rating{margin:0 0 18px;display:flex;align-items:center;gap:9px;font-size:14px;color:#9CA3AF;flex-wrap:wrap}
.stars{color:#f5b301;letter-spacing:3px;font-size:15px}
.rating b{color:#F5F5F5}
/* Preis-Orientierung direkt im Hero – nimmt die häufigste Hemmschwelle */
.preisleiste{display:inline-flex;align-items:center;gap:9px;background:#161616;border:1px solid rgba(255,255,255,.12);
 border-left:3px solid var(--blue);border-radius:11px;padding:11px 15px;margin-bottom:22px;
 font-size:14.5px;font-weight:600;color:#E8EDF5}
.cta-neben{display:flex;gap:10px;flex-wrap:wrap}
.ic-wa{vertical-align:-.125em;flex:0 0 auto}
/* ---------- SECTIONS ---------- */
.sec{padding:74px 0}
.sec-cream{background:var(--cream)}
.sec-ink{background:#111111;color:var(--txt);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.kicker{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue);text-align:center;margin-bottom:12px}
.sec-ink .kicker{color:var(--blue)}
.sec h2{font-size:clamp(26px,3.2vw,38px);text-align:center;margin-bottom:14px;color:#F5F5F5}
.sec .lead{text-align:center;color:var(--txt-dim);max-width:60ch;margin:0 auto 50px;font-size:17px}
.sec-ink .lead{color:#9CA3AF}
/* steps – editorial */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;counter-reset:s}
.step{position:relative;padding:30px 26px;background:var(--paper);border:1px solid var(--line);border-radius:20px}
.step .num{font-size:46px;color:var(--amber);line-height:1;margin-bottom:14px;opacity:.95}
.step h3{font-size:19px;margin-bottom:8px;color:var(--txt)}
.step p{font-size:15px;color:var(--txt-dim)}
/* leistungen */
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.lcard{background:var(--paper);border:1px solid var(--line);border-radius:20px;padding:30px 26px;transition:transform .18s,border-color .18s,box-shadow .18s}
.lcard:hover{transform:translateY(-4px);border-color:rgba(110,155,224,.45);box-shadow:0 16px 34px rgba(0,0,0,.35)}
.lcard .ico{width:54px;height:54px;border-radius:15px;background:rgba(110,155,224,.12);border:1px solid rgba(110,155,224,.28);color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:18px}
.lcard h3{font-size:19px;margin-bottom:9px;color:#F5F5F5}
.lcard p{font-size:15px;color:#9CA3AF}
/* faq */
.faqs{max-width:780px;margin:0 auto}
.faq{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px 24px;margin-bottom:14px}
.faq h3{font-size:17px;margin-bottom:8px;color:var(--txt);display:flex;gap:11px;align-items:baseline;font-family:'Inter',sans-serif;font-weight:700}
.faq h3 i{color:var(--blue);font-size:14px}
.faq p{font-size:15px;color:var(--txt-dim);padding-left:25px}
/* cta block */
.ctablock{background:linear-gradient(135deg,#325AA0,#16233c);color:#fff;text-align:center;padding:80px 0;position:relative;overflow:hidden}
.ctablock::before{content:"";position:absolute;inset:0;opacity:.7;background:radial-gradient(50% 80% at 50% 0%,rgba(255,255,255,.10),transparent 60%)}
.ctablock .wrap{position:relative;z-index:2}
.ctablock h2{font-size:clamp(28px,3.6vw,42px);margin-bottom:14px}
.ctablock p{color:#C9D3E4;max-width:50ch;margin:0 auto 30px;font-size:17px}
.ctablock .btn-primary{background:#fff;color:#16233c;box-shadow:0 14px 34px rgba(0,0,0,.25)}
.ctablock .btn-primary:hover{box-shadow:0 20px 44px rgba(0,0,0,.35)}
/* footer */
.foot{background:#0A0A0A;color:#9CA3AF;text-align:center;padding:34px 0;font-size:13px;border-top:1px solid var(--line)}
.foot a{color:#C9D3E4;text-decoration:none;margin:0 9px}
.foot a:hover{color:#6E9BE0}
/* reveal */
.reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s ease}
.reveal.in{opacity:1;transform:none}
@media(max-width:820px){
  .steps,.cards{grid-template-columns:1fr}
  .sec{padding:52px 0}.hero{padding:24px 0 50px}
  .topbar{padding-bottom:34px}.tb-call span{display:none}
}
/* ── Handy: Hero kompakt halten, damit Angebot + Beweis früh sichtbar sind ── */
@media(max-width:600px){
  .hero{padding:16px 0 34px}
  .topbar{padding-bottom:18px}
  .eyebrow{margin-bottom:12px;font-size:11px;letter-spacing:1.6px}
  .hero h1{font-size:clamp(28px,7.6vw,36px);line-height:1.1;margin-bottom:12px;max-width:100%}
  .rating{margin-bottom:14px;font-size:13.5px}
  .rating-lang{display:none}                 /* Zusatz kürzen, Kernaussage bleibt */
  .hero .sub{font-size:16px;line-height:1.5;margin-bottom:16px}
  .preisleiste{margin-bottom:16px;font-size:13.5px;padding:10px 13px;width:100%}
  .trust{gap:7px;margin-bottom:20px}
  .chip{font-size:12px;padding:7px 11px;border-radius:9px;gap:6px}
  .cta-row{display:block}
  .cta-row .btn-primary{width:100%;justify-content:center;padding:17px 20px;font-size:16px}
  .cta-neben{margin-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:9px}
  .cta-neben .btn-ghost{padding:13px 8px;font-size:14px;justify-content:center;gap:7px}
  /* Am Handy zuerst handeln lassen, die Merkmale kommen danach:
     der Anfrage-Knopf rutscht damit über die Sichtkante. */
  .hero .wrap{display:flex;flex-direction:column}
  .hero .topbar{order:1}
  .hero .eyebrow{order:2}
  .hero h1{order:3}
  .hero .rating{order:4}
  .hero .sub{order:5}
  .hero .preisleiste{order:6}
  .hero .cta-row{order:7}
  .hero .trust{order:8;margin:22px 0 0}
  /* Platz für die feste Kontaktleiste am unteren Rand */
  .foot{padding-bottom:88px}
}
/* ── Funnel auf Landingpages: Basis-Variablen fuer funnel.css.
   Die Optik kommt aus funnel-dark.css (gleiche Datei wie die Startseite),
   damit der Funnel ueberall identisch dunkel aussieht. ── */
:root{
  --blue-dark:#325AA0;
  --blue-primary:#6E9BE0;
  --blue-light:#16202e;
  --gray-100:#1c1c1c;
  --gray-200:#2a2a2a;
  --gray-300:#3a3a3a;
  --text-primary:#F5F5F5;
  --text-secondary:#9CA3AF;
  --text-muted:#79879b;
  --font-display:'Anton',Impact,sans-serif;
  --font-primary:'Inter',sans-serif;
}
/* Block-Titel über Abschnitten */
.funnel-split-block-title{font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9CA3AF;margin:.9rem 0 .5rem}
.mobar{display:none}
@media(max-width:820px){
  .mobar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:90;gap:10px;padding:10px 14px calc(10px + env(safe-area-inset-bottom));
    background:rgba(10,10,10,.97);backdrop-filter:blur(10px);border-top:1px solid rgba(255,255,255,.12)}
  .mobar a,.mobar button{flex:1;border:none;cursor:pointer;font-family:'Inter',sans-serif;font-weight:700;font-size:14px;border-radius:12px;padding:13px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
  .mobar .m-call{background:rgba(255,255,255,.14);color:#fff}
  .mobar .m-wa{background:#25D366;color:#06301a}
  .mobar .m-offer{background:#6E9BE0;color:#0A0A0A}
  body{padding-bottom:72px}
}
</style>
</head>
<body>
<header class="hero">
  <div class="wrap">
    <div class="topbar">
      <a class="logo" href="/" aria-label="OH Haustechnik – Startseite"><img src="assets/img/logo-oh-320.png" alt="OH Haustechnik" width="55" height="50" loading="eager"><small>HAUSTECHNIK · FACHBETRIEB</small></a>
      <a class="tb-call" href="tel:+491757481006"><i class="fas fa-phone"></i> <span>0175 7481006</span></a>
    </div>
    <div class="eyebrow">Elektro-Fachbetrieb · Raum Nürnberg</div>
    <h1><?= $g('h1') ?></h1>
    <!-- Bewertung steht bewusst VOR dem Handlungsaufruf: erst Vertrauen, dann Bitte. -->
    <div class="rating"><span class="stars">★★★★★</span> <b><?= $ohRating ?></b> aus <?= $ohCount ?> Google-Bewertungen <span class="rating-lang">· echte Kunden aus der Region</span></div>
    <p class="sub"><?= htmlspecialchars($g('sub')) ?></p>
    <?php if ($g('preis_hinweis')): ?>
    <div class="preisleiste"><?= htmlspecialchars($g('preis_hinweis')) ?></div>
    <?php endif; ?>
    <div class="trust">
      <?php foreach ($g('badges', []) as $b): ?><span class="chip"><i class="fas fa-check"></i><?= htmlspecialchars($b) ?></span><?php endforeach; ?>
    </div>
    <div class="cta-row">
      <button class="btn-primary" data-open-funnel><i class="fas fa-bolt"></i> <?= htmlspecialchars($g('cta', 'Kostenloses Angebot anfordern')) ?></button>
      <div class="cta-neben">
        <a class="btn-ghost" href="festpreis-kalkulator.php"><i class="fas fa-calculator"></i> Preis in 2 Min</a>
        <a class="btn-ghost" href="https://wa.me/491757481006?text=<?= rawurlencode('Hallo OH Haustechnik, ich interessiere mich für: ' . strip_tags($g('h1'))) ?>" target="_blank" rel="noopener" data-wa><svg class="ic-wa" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 110.9L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg> WhatsApp</a>
      </div>
    </div>
  </div>
</header>

<?php if ($g('preise')): ?>
<section class="sec">
  <div class="wrap">
    <div class="kicker">Preis-Orientierung</div>
    <h2><?= htmlspecialchars($g('preis_titel', 'Was kostet das ungefähr?')) ?></h2>
    <p class="lead"><?= htmlspecialchars($g('preis_lead', 'Echte Richtwerte aus der Praxis im Raum Nürnberg – Ihren verbindlichen Festpreis bekommen Sie nach einem kurzen Vor-Ort-Termin.')) ?></p>
    <div class="cards">
      <?php foreach ($g('preise') as $p): $hot = !empty($p[3]); ?>
      <div class="lcard reveal" style="background:var(--paper);border:1px solid <?= $hot ? 'var(--amber)' : 'var(--line)' ?>;box-shadow:0 8px 26px rgba(20,30,50,<?= $hot ? '.10' : '.05' ?>)">
        <?php if ($hot): ?><div style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#0A0A0A;background:var(--amber);padding:4px 11px;border-radius:999px;margin-bottom:12px">Beliebteste Wahl</div><?php endif; ?>
        <div style="font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--blue)"><?= htmlspecialchars($p[0]) ?></div>
        <div style="font-family:'Anton',Impact,sans-serif;font-weight:400;font-size:31px;letter-spacing:.5px;color:var(--txt);margin:7px 0"><?= htmlspecialchars($p[1]) ?></div>
        <p style="font-size:15px;color:var(--txt-dim)"><?= htmlspecialchars($p[2]) ?></p>
        <button class="btn-primary" data-open-funnel style="margin-top:18px;padding:13px 22px;font-size:15px"><i class="fas fa-bolt"></i> Festpreis anfragen</button>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;color:var(--txt-dim);font-size:14px;margin-top:26px">Richtwerte inkl. Material als Orientierung · verbindlich nach Vor-Ort-Termin.</p>
  </div>
</section>
<?php endif; ?>

<section class="sec sec-cream">
  <div class="wrap">
    <div class="kicker">In 3 Schritten</div>
    <h2>So einfach kommen Sie zum Angebot</h2>
    <p class="lead">Zwei Minuten für Ihre Anfrage – den ganzen Rest übernehmen wir.</p>
    <div class="steps">
      <?php $i=1; foreach ($g('schritte', []) as $s): ?>
      <div class="step reveal"><div class="num"><?= sprintf('%02d', $i++) ?></div><h3><?= htmlspecialchars($s[0]) ?></h3><p><?= htmlspecialchars($s[1]) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec-ink">
  <div class="wrap">
    <div class="kicker">Unsere Leistung</div>
    <h2>Das übernehmen wir für Sie</h2>
    <p class="lead">Sauber, termintreu und zum fairen Festpreis – vom Fachbetrieb.</p>
    <div class="cards">
      <?php foreach ($g('leistungen', []) as $l): ?>
      <div class="lcard reveal"><div class="ico"><i class="fas <?= htmlspecialchars($l[0]) ?>"></i></div><h3><?= htmlspecialchars($l[1]) ?></h3><p><?= htmlspecialchars($l[2]) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec sec-cream">
  <div class="wrap">
    <div class="kicker">Gut zu wissen</div>
    <h2>Häufige Fragen</h2>
    <p class="lead">Kurz und ehrlich beantwortet.</p>
    <div class="faqs">
      <?php foreach ($g('faq', []) as $q): ?>
      <div class="faq reveal"><h3><i class="fas fa-bolt"></i><?= htmlspecialchars($q[0]) ?></h3><p><?= htmlspecialchars($q[1]) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="ctablock">
  <div class="wrap">
    <h2>Bereit für Ihr Projekt?</h2>
    <p>Fordern Sie jetzt Ihr kostenloses, unverbindliches Festpreis-Angebot an – Sie hören schnell von uns.</p>
    <button class="btn-primary" data-open-funnel><i class="fas fa-bolt"></i> <?= htmlspecialchars($g('cta', 'Kostenloses Angebot anfordern')) ?></button>
  </div>
</section>

<footer class="foot">
  <div class="wrap">
    OH Haustechnik · Elektroinstallation &amp; Netzwerkverkabelung · Raum Nürnberg<br>
    <a href="impressum.php">Impressum</a> · <a href="datenschutz.php">Datenschutz</a> · <a href="index.php">Zur Hauptseite</a>
  </div>
</footer>

<div class="mobar">
  <a class="m-call" href="tel:+491757481006"><i class="fas fa-phone"></i> Anrufen</a>
  <a class="m-wa" href="https://wa.me/491757481006?text=<?= rawurlencode('Hallo OH Haustechnik, ich interessiere mich für: ' . $g('h1')) ?>" target="_blank" rel="noopener"><svg class="ic-wa" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 110.9L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg> WhatsApp</a>
  <button class="m-offer" data-open-funnel><i class="fas fa-bolt"></i> Angebot</button>
</div>

<img src="besucher.php?q=<?= htmlspecialchars($quelle) ?>" alt="" width="1" height="1" style="position:absolute;left:-9999px" aria-hidden="true">
<?php include __DIR__ . '/funnel-modal.php'; ?>
<?php
/* Conversion-Label aus der Büro-Konfiguration -> echte Ads-Conversion mit Wert (falls gesetzt). */
$__conv = [];
if (!empty($ohCfg = (function_exists('oh_config') ? oh_config() : []))) {
    if (!empty($ohCfg['ads_conversion_label'])) $__conv['lead_form_submit'] = $ohCfg['ads_conversion_label'];
    if (!empty($ohCfg['ads_call_label']))       $__conv['phone_click']      = $ohCfg['ads_call_label'];
}
if ($__conv): ?>
<script>window.OH_ADS_CONV = <?= json_encode($__conv, JSON_UNESCAPED_SLASHES) ?>;</script>
<?php endif; ?>
<script src="assets/js/oh-track.js"></script>
<script src="assets/js/funnel.js"></script>
<script>
/* Quellen-Tracking NACH funnel.js setzen, damit die Landing-Page-Quelle gewinnt.
   gclid (Google-Ads-Klick) wird als Praefix erhalten -> feine Attribution je Kampagne. */
(function(){
  try{
    var ads = new URLSearchParams(location.search).get('gclid') ? 'ads-' : '';
    localStorage.setItem('oh_quelle', ads + <?= json_encode($quelle) ?>);
  }catch(e){}
  /* sanfte Scroll-Reveals */
  var io = new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}});},{threshold:.15});
  document.querySelectorAll('.reveal').forEach(function(el){io.observe(el);});
})();
</script>
</body>
</html>
