<?php
/**
 * Gemeinsamer Renderer fuer OH-Kampagnen-Landing-Pages — Premium-Design.
 * Eine Seite = ein Ziel = eine klare Hierarchie (Nivea-Conversion-Formel),
 * aber mit eigener Marken-Identitaet statt generischem KI-Template-Look:
 *   - Display-Schrift Sora, Fliesstext Manrope (distinctive, nicht Inter/Roboto)
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
<link rel="icon" href="assets/img/favicon.ico">
<meta name="theme-color" content="#ffffff">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/funnel.css">
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
    'areaServed' => ['Nürnberg', 'Fürth', 'Erlangen', 'Schwabach', 'Stein', 'Zirndorf'],
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
:root{
  --ink:#13294f;--ink2:#13294f;--ink-soft:#1c3e6e;
  --cream:#f4f8ff;--paper:#ffffff;
  --blue:#2563eb;--blue-d:#1c3e6e;
  --amber:#2563eb;--amber-d:#1c3e6e;
  --txt:#15202f;--txt-dim:#5b6b80;--line:#e2e8f0;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Manrope',-apple-system,BlinkMacSystemFont,sans-serif;color:var(--txt);line-height:1.65;background:var(--paper);-webkit-font-smoothing:antialiased}
h1,h2,h3,.num{font-family:'Sora',sans-serif;line-height:1.08;letter-spacing:-.02em}
.wrap{max-width:1120px;margin:0 auto;padding:0 24px}
.amber{color:var(--amber)}
/* ---------- HERO ---------- */
.hero{background:linear-gradient(180deg,#eef4ff 0%,#ffffff 100%);color:#15202f;position:relative;overflow:hidden;padding:30px 0 64px;border-bottom:1px solid #e2e8f0}
.hero::before{content:"";position:absolute;inset:0;opacity:.7;
  background:
    radial-gradient(60% 50% at 82% 8%,rgba(37,99,235,.10),transparent 60%),
    radial-gradient(45% 40% at 8% 96%,rgba(28,62,110,.06),transparent 60%);}
.hero::after{content:"";position:absolute;inset:0;opacity:.5;pointer-events:none;
  background-image:linear-gradient(rgba(37,99,235,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(37,99,235,.035) 1px,transparent 1px);
  background-size:64px 64px;mask-image:radial-gradient(circle at 70% 0%,#000,transparent 75%)}
.hero .wrap{position:relative;z-index:2}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:8px 0 44px}
.logo{font-family:'Sora';font-weight:800;font-size:22px;letter-spacing:3px;color:#13294f;display:flex;align-items:center;gap:11px}
.logo .dot{width:9px;height:9px;border-radius:50%;background:var(--blue);box-shadow:0 0 12px rgba(37,99,235,.5)}
.logo small{font-family:'Manrope';font-size:10px;letter-spacing:2.5px;font-weight:600;color:#7b8aa0}
.tb-call{color:#33425c;text-decoration:none;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
.tb-call i{color:var(--blue)}
.eyebrow{display:inline-flex;align-items:center;gap:9px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue);margin-bottom:20px}
.eyebrow::before{content:"";width:26px;height:2px;background:var(--blue)}
.hero h1{font-size:clamp(33px,5vw,58px);font-weight:800;max-width:15ch;margin-bottom:20px;color:#13294f}
.hero .sub{font-size:clamp(16px,1.6vw,20px);color:#5b6b80;max-width:54ch;margin-bottom:30px}
.trust{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:34px}
.chip{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#33425c;background:#fff;border:1px solid #dbe4f3;padding:9px 15px;border-radius:11px;box-shadow:0 4px 14px rgba(20,30,50,.05)}
.chip i{color:var(--blue);font-size:12px}
.cta-row{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
.btn-primary{background:linear-gradient(135deg,#2563eb,#1c3e6e);color:#fff;border:none;padding:18px 32px;border-radius:14px;font-family:'Sora';font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 14px 34px rgba(37,99,235,.30);transition:transform .16s,box-shadow .16s;display:inline-flex;align-items:center;gap:10px}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 44px rgba(37,99,235,.42)}
/* Dauerhafter Aufmerksamkeits-Puls auf dem Haupt-CTA (zieht den Blick → mehr Klicks) */
@keyframes lpCtaAttract{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.5)}50%{box-shadow:0 0 26px 6px rgba(37,99,235,.45),0 0 0 8px rgba(37,99,235,.10)}}
.cta-row .btn-primary,.ctablock .btn-primary{animation:lpCtaAttract 2.4s ease-in-out infinite}
.cta-row .btn-primary:hover,.ctablock .btn-primary:hover{animation:none}
@media (prefers-reduced-motion: reduce){.cta-row .btn-primary,.ctablock .btn-primary{animation:none}}
.btn-ghost{color:#13294f;text-decoration:none;font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:9px;padding:16px 24px;border:1.5px solid #cdd9ec;border-radius:14px;transition:border-color .16s,background .16s;background:#fff}
.btn-ghost:hover{border-color:#2563eb;background:#f4f8ff}
.rating{margin-top:28px;display:flex;align-items:center;gap:11px;font-size:14px;color:#5b6b80}
.stars{color:#f5b301;letter-spacing:3px;font-size:15px}
.rating b{color:#13294f}
/* ---------- SECTIONS ---------- */
.sec{padding:74px 0}
.sec-cream{background:var(--cream)}
.sec-ink{background:#eef4ff;color:#15202f;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}
.kicker{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue);text-align:center;margin-bottom:12px}
.sec-ink .kicker{color:var(--blue)}
.sec h2{font-size:clamp(26px,3.2vw,38px);font-weight:800;text-align:center;margin-bottom:14px;color:#13294f}
.sec .lead{text-align:center;color:var(--txt-dim);max-width:60ch;margin:0 auto 50px;font-size:17px}
.sec-ink .lead{color:#5b6b80}
/* steps – editorial */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;counter-reset:s}
.step{position:relative;padding:30px 26px;background:var(--paper);border:1px solid var(--line);border-radius:20px;box-shadow:0 8px 26px rgba(20,30,50,.05)}
.step .num{font-size:46px;font-weight:800;color:var(--amber);line-height:1;margin-bottom:14px;opacity:.95}
.step h3{font-size:19px;margin-bottom:8px;color:var(--txt)}
.step p{font-size:15px;color:var(--txt-dim)}
/* leistungen */
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.lcard{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:30px 26px;box-shadow:0 8px 26px rgba(20,30,50,.05);transition:transform .18s,border-color .18s,box-shadow .18s}
.lcard:hover{transform:translateY(-4px);border-color:rgba(37,99,235,.4);box-shadow:0 16px 34px rgba(20,30,50,.10)}
.lcard .ico{width:54px;height:54px;border-radius:15px;background:linear-gradient(135deg,#eef4ff,#dbe7ff);border:1px solid #d6e2fb;color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:18px}
.lcard h3{font-size:19px;margin-bottom:9px;color:#13294f}
.lcard p{font-size:15px;color:#5b6b80}
/* faq */
.faqs{max-width:780px;margin:0 auto}
.faq{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px 24px;margin-bottom:14px;box-shadow:0 6px 20px rgba(20,30,50,.04)}
.faq h3{font-size:17px;margin-bottom:8px;color:var(--txt);display:flex;gap:11px;align-items:baseline}
.faq h3 i{color:var(--blue);font-size:14px}
.faq p{font-size:15px;color:var(--txt-dim);padding-left:25px}
/* cta block */
.ctablock{background:linear-gradient(135deg,#2563eb,#1c3e6e);color:#fff;text-align:center;padding:80px 0;position:relative;overflow:hidden}
.ctablock::before{content:"";position:absolute;inset:0;opacity:.7;background:radial-gradient(50% 80% at 50% 0%,rgba(255,255,255,.14),transparent 60%)}
.ctablock .wrap{position:relative;z-index:2}
.ctablock h2{font-size:clamp(28px,3.6vw,42px);font-weight:800;margin-bottom:14px}
.ctablock p{color:#dbe7ff;max-width:50ch;margin:0 auto 30px;font-size:17px}
.ctablock .btn-primary{background:#fff;color:#1c3e6e;box-shadow:0 14px 34px rgba(0,0,0,.18)}
.ctablock .btn-primary:hover{box-shadow:0 20px 44px rgba(0,0,0,.26)}
/* footer */
.foot{background:#f4f8ff;color:#5b6b80;text-align:center;padding:34px 0;font-size:13px;border-top:1px solid #e2e8f0}
.foot a{color:#33425c;text-decoration:none;margin:0 9px}
.foot a:hover{color:#2563eb}
/* reveal */
.reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s ease}
.reveal.in{opacity:1;transform:none}
@media(max-width:820px){
  .steps,.cards{grid-template-columns:1fr}
  .sec{padding:52px 0}.hero{padding:24px 0 50px}
  .topbar{padding-bottom:34px}.tb-call span{display:none}
}
/* ── Funnel auf Landingpages: Design-Variablen & LP-Overrides (weiß/blau) ── */
:root{
  /* Fehlende Variablen aus der Hauptseite – Funnel-CSS braucht diese */
  --blue-dark:#1c3e6e;
  --blue-primary:#2563eb;
  --blue-light:#f4f8ff;
  --gray-100:#f1f5fc;
  --gray-200:#e2e8f0;
  --gray-300:#cbd5e1;
  --text-primary:#15202f;
  --text-secondary:#5b6b80;
  --text-muted:#7e8ea7;
  --font-display:'Sora',sans-serif;
  --font-primary:'Manrope',sans-serif;
}
/* Modal-Body: weiß */
.funnel-modal{background:#ffffff}
/* Header: Blau */
.funnel-header{background:linear-gradient(135deg,#2563eb 0%,#1c3e6e 100%)}
/* Fortschrittsbalken: Blau */
.funnel-progress-bar-fill{background:#fff}
/* Schritt-Titel: Navy-Blau */
.funnel-step-title{color:#13294f}
/* Auswahlkarten: Hover + Checked in Blau */
.funnel-option-label:hover{border-color:#2563eb;background:#f4f8ff}
.funnel-option input:checked + .funnel-option-label{border-color:#2563eb;background:#eef4ff;box-shadow:0 0 0 3px rgba(37,99,235,.18)}
.funnel-option-icon{background:linear-gradient(135deg,#2563eb,#1c3e6e)}
.funnel-option input:checked + .funnel-option-label::after{background:#2563eb;color:#fff}
/* Checkboxen */
.funnel-checkbox-item:hover{border-color:#2563eb;background:#f4f8ff}
.funnel-checkbox-item input{accent-color:#2563eb}
/* Inputs */
.funnel-input:focus,.funnel-textarea:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
.funnel-label span{color:#2563eb}
/* Upload-Bereich */
.funnel-upload-label:hover{border-color:#2563eb;background:#f4f8ff;color:#13294f}
.funnel-upload-label i{color:#2563eb}
.funnel-upload-name{color:#2563eb}
/* DSGVO-Box */
.funnel-dsgvo{background:#f4f8ff;border-color:#d6e2fb}
.funnel-dsgvo input{accent-color:#2563eb}
/* Weiter/Senden-Button: Blau */
.funnel-btn--next,.funnel-btn--submit{background:linear-gradient(135deg,#2563eb,#1c3e6e);color:#fff;border:none}
.funnel-btn--next:hover,.funnel-btn--submit:hover{background:linear-gradient(135deg,#1c3e6e,#13294f)}
/* Sub-Options Hintergrund */
.funnel-suboptions{background:#f1f5fc;border-color:#e2e8f0}
.funnel-suboptions-title{color:#13294f}
/* Block-Titel über Abschnitten */
.funnel-split-block-title{font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#5b6b80;margin:.9rem 0 .5rem}
.mobar{display:none}
@media(max-width:820px){
  .mobar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:90;gap:10px;padding:10px 14px calc(10px + env(safe-area-inset-bottom));
    background:rgba(19,41,79,.97);backdrop-filter:blur(10px);border-top:1px solid rgba(255,255,255,.12)}
  .mobar a,.mobar button{flex:1;border:none;cursor:pointer;font-family:'Sora';font-weight:700;font-size:14px;border-radius:12px;padding:13px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
  .mobar .m-call{background:rgba(255,255,255,.14);color:#fff}
  .mobar .m-wa{background:#25D366;color:#06301a}
  .mobar .m-offer{background:#fff;color:#1c3e6e}
  body{padding-bottom:72px}
}
</style>
</head>
<body>
<header class="hero">
  <div class="wrap">
    <div class="topbar">
      <div class="logo"><span class="dot"></span>OH <small>HAUSTECHNIK · FACHBETRIEB</small></div>
      <a class="tb-call" href="tel:+491757481006"><i class="fas fa-phone"></i> <span>0175 7481006</span></a>
    </div>
    <div class="eyebrow">Elektro-Fachbetrieb · Raum Nürnberg</div>
    <h1><?= $g('h1') ?></h1>
    <p class="sub"><?= htmlspecialchars($g('sub')) ?></p>
    <div class="trust">
      <?php foreach ($g('badges', []) as $b): ?><span class="chip"><i class="fas fa-check"></i><?= htmlspecialchars($b) ?></span><?php endforeach; ?>
    </div>
    <div class="cta-row">
      <button class="btn-primary" data-open-funnel><i class="fas fa-bolt"></i> <?= htmlspecialchars($g('cta', 'Kostenloses Angebot anfordern')) ?></button>
      <a class="btn-ghost" href="festpreis-kalkulator.php"><i class="fas fa-calculator"></i> Preis in 2 Min berechnen</a>
      <a class="btn-ghost" href="https://wa.me/491757481006?text=<?= rawurlencode('Hallo OH Haustechnik, ich interessiere mich für: ' . $g('h1')) ?>" target="_blank" rel="noopener" data-wa><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <div class="rating"><span class="stars">★★★★★</span> <b><?= $ohRating ?></b> aus <?= $ohCount ?> Google-Bewertungen · echte Kunden aus der Region</div>
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
        <?php if ($hot): ?><div style="display:inline-block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#1a1206;background:var(--amber);padding:4px 11px;border-radius:999px;margin-bottom:12px">Beliebteste Wahl</div><?php endif; ?>
        <div style="font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--blue-d)"><?= htmlspecialchars($p[0]) ?></div>
        <div style="font-family:'Sora';font-weight:800;font-size:31px;color:var(--txt);margin:7px 0"><?= htmlspecialchars($p[1]) ?></div>
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
  <a class="m-wa" href="https://wa.me/491757481006?text=<?= rawurlencode('Hallo OH Haustechnik, ich interessiere mich für: ' . $g('h1')) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
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
