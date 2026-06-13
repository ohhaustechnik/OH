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
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($g('title')) ?></title>
<meta name="description" content="<?= htmlspecialchars($g('meta')) ?>">
<link rel="canonical" href="https://oh-haustechnik.de/<?= htmlspecialchars($g('slug')) ?>">
<link rel="icon" href="assets/img/favicon.ico">
<meta name="theme-color" content="#0c1526">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/funnel.css">
<style>
:root{
  --ink:#0c1526;--ink2:#0a1120;--ink-soft:#16223a;
  --cream:#f7f4ee;--paper:#fffdf9;
  --blue:#3f7bf0;--blue-d:#2a5fc7;
  --amber:#e8a24a;--amber-d:#cf8a32;
  --txt:#15202f;--txt-dim:#5b6b80;--line:#e8e1d5;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Manrope',-apple-system,BlinkMacSystemFont,sans-serif;color:var(--txt);line-height:1.65;background:var(--paper);-webkit-font-smoothing:antialiased}
h1,h2,h3,.num{font-family:'Sora',sans-serif;line-height:1.08;letter-spacing:-.02em}
.wrap{max-width:1120px;margin:0 auto;padding:0 24px}
.amber{color:var(--amber)}
/* ---------- HERO ---------- */
.hero{background:var(--ink);color:#eef3fb;position:relative;overflow:hidden;padding:30px 0 64px}
.hero::before{content:"";position:absolute;inset:0;opacity:.5;
  background:
    radial-gradient(60% 50% at 82% 8%,rgba(63,123,240,.20),transparent 60%),
    radial-gradient(45% 40% at 8% 96%,rgba(232,162,74,.13),transparent 60%);}
.hero::after{content:"";position:absolute;inset:0;opacity:.5;pointer-events:none;
  background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
  background-size:64px 64px;mask-image:radial-gradient(circle at 70% 0%,#000,transparent 75%)}
.hero .wrap{position:relative;z-index:2}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:8px 0 44px}
.logo{font-family:'Sora';font-weight:800;font-size:22px;letter-spacing:3px;color:#fff;display:flex;align-items:center;gap:11px}
.logo .dot{width:9px;height:9px;border-radius:50%;background:var(--amber);box-shadow:0 0 12px rgba(232,162,74,.7)}
.logo small{font-family:'Manrope';font-size:10px;letter-spacing:2.5px;font-weight:600;color:#8fa3c4}
.tb-call{color:#cdd9ec;text-decoration:none;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
.tb-call i{color:var(--amber)}
.eyebrow{display:inline-flex;align-items:center;gap:9px;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--amber);margin-bottom:20px}
.eyebrow::before{content:"";width:26px;height:2px;background:var(--amber)}
.hero h1{font-size:clamp(33px,5vw,58px);font-weight:800;max-width:15ch;margin-bottom:20px;color:#fff}
.hero .sub{font-size:clamp(16px,1.6vw,20px);color:#b6c4da;max-width:54ch;margin-bottom:30px}
.trust{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:34px}
.chip{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#dde6f3;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);padding:9px 15px;border-radius:11px}
.chip i{color:var(--amber);font-size:12px}
.cta-row{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
.btn-primary{background:linear-gradient(135deg,var(--amber),var(--amber-d));color:#1a1206;border:none;padding:18px 32px;border-radius:14px;font-family:'Sora';font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 14px 34px rgba(232,162,74,.32);transition:transform .16s,box-shadow .16s;display:inline-flex;align-items:center;gap:10px}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 44px rgba(232,162,74,.42)}
.btn-ghost{color:#fff;text-decoration:none;font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:9px;padding:16px 24px;border:1.5px solid rgba(255,255,255,.22);border-radius:14px;transition:border-color .16s,background .16s}
.btn-ghost:hover{border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.04)}
.rating{margin-top:28px;display:flex;align-items:center;gap:11px;font-size:14px;color:#aebbd0}
.stars{color:var(--amber);letter-spacing:3px;font-size:15px}
.rating b{color:#fff}
/* ---------- SECTIONS ---------- */
.sec{padding:74px 0}
.sec-cream{background:var(--cream)}
.sec-ink{background:var(--ink2);color:#eef3fb}
.kicker{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue-d);text-align:center;margin-bottom:12px}
.sec-ink .kicker{color:var(--amber)}
.sec h2{font-size:clamp(26px,3.2vw,38px);font-weight:800;text-align:center;margin-bottom:14px;color:inherit}
.sec .lead{text-align:center;color:var(--txt-dim);max-width:60ch;margin:0 auto 50px;font-size:17px}
.sec-ink .lead{color:#a9b8cf}
/* steps – editorial */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;counter-reset:s}
.step{position:relative;padding:30px 26px;background:var(--paper);border:1px solid var(--line);border-radius:20px;box-shadow:0 8px 26px rgba(20,30,50,.05)}
.step .num{font-size:46px;font-weight:800;color:var(--amber);line-height:1;margin-bottom:14px;opacity:.95}
.step h3{font-size:19px;margin-bottom:8px;color:var(--txt)}
.step p{font-size:15px;color:var(--txt-dim)}
/* leistungen */
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.lcard{background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:30px 26px;transition:transform .18s,border-color .18s,background .18s}
.lcard:hover{transform:translateY(-4px);border-color:rgba(232,162,74,.4);background:rgba(255,255,255,.055)}
.lcard .ico{width:54px;height:54px;border-radius:15px;background:linear-gradient(135deg,rgba(63,123,240,.22),rgba(232,162,74,.18));border:1px solid rgba(255,255,255,.12);color:var(--amber);display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:18px}
.lcard h3{font-size:19px;margin-bottom:9px;color:#fff}
.lcard p{font-size:15px;color:#a9b8cf}
/* faq */
.faqs{max-width:780px;margin:0 auto}
.faq{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px 24px;margin-bottom:14px;box-shadow:0 6px 20px rgba(20,30,50,.04)}
.faq h3{font-size:17px;margin-bottom:8px;color:var(--txt);display:flex;gap:11px;align-items:baseline}
.faq h3 i{color:var(--blue);font-size:14px}
.faq p{font-size:15px;color:var(--txt-dim);padding-left:25px}
/* cta block */
.ctablock{background:var(--ink);color:#fff;text-align:center;padding:80px 0;position:relative;overflow:hidden}
.ctablock::before{content:"";position:absolute;inset:0;opacity:.6;background:radial-gradient(50% 80% at 50% 0%,rgba(232,162,74,.18),transparent 60%)}
.ctablock .wrap{position:relative;z-index:2}
.ctablock h2{font-size:clamp(28px,3.6vw,42px);font-weight:800;margin-bottom:14px}
.ctablock p{color:#b6c4da;max-width:50ch;margin:0 auto 30px;font-size:17px}
/* footer */
.foot{background:var(--ink2);color:#7e8ea7;text-align:center;padding:34px 0;font-size:13px;border-top:1px solid rgba(255,255,255,.06)}
.foot a{color:#aebbd0;text-decoration:none;margin:0 9px}
.foot a:hover{color:#fff}
/* reveal */
.reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease,transform .6s ease}
.reveal.in{opacity:1;transform:none}
@media(max-width:820px){
  .steps,.cards{grid-template-columns:1fr}
  .sec{padding:52px 0}.hero{padding:24px 0 50px}
  .topbar{padding-bottom:34px}.tb-call span{display:none}
}
</style>
</head>
<body>
<header class="hero">
  <div class="wrap">
    <div class="topbar">
      <div class="logo"><span class="dot"></span>OH <small>HAUSTECHNIK · MEISTERBETRIEB</small></div>
      <a class="tb-call" href="tel:+491757481006"><i class="fas fa-phone"></i> <span>0175 7481006</span></a>
    </div>
    <div class="eyebrow">Elektro-Meisterbetrieb · Raum Nürnberg</div>
    <h1><?= $g('h1') ?></h1>
    <p class="sub"><?= htmlspecialchars($g('sub')) ?></p>
    <div class="trust">
      <?php foreach ($g('badges', []) as $b): ?><span class="chip"><i class="fas fa-check"></i><?= htmlspecialchars($b) ?></span><?php endforeach; ?>
    </div>
    <div class="cta-row">
      <button class="btn-primary" data-open-funnel><i class="fas fa-bolt"></i> <?= htmlspecialchars($g('cta', 'Kostenloses Angebot anfordern')) ?></button>
      <a class="btn-ghost" href="tel:+491757481006"><i class="fas fa-phone"></i> Jetzt anrufen</a>
    </div>
    <div class="rating"><span class="stars">★★★★★</span> <b>5,0</b> aus 15 Google-Bewertungen · echte Kunden aus der Region</div>
  </div>
</header>

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
    <p class="lead">Sauber, termintreu und zum fairen Festpreis – vom Meisterbetrieb.</p>
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

<img src="besucher.php?q=<?= htmlspecialchars($quelle) ?>" alt="" width="1" height="1" style="position:absolute;left:-9999px" aria-hidden="true">
<?php include __DIR__ . '/funnel-modal.php'; ?>
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
