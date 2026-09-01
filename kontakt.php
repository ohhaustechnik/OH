<?php
$page_title       = 'Kontakt – OH Haustechnik Nürnberg | Anfrage für Elektroinstallation';
$meta_description = 'Kontaktieren Sie OH Haustechnik: Telefon, E-Mail oder Anfrageformular. Antwort in der Regel innerhalb von 24 Stunden — kostenlos & unverbindlich.';
$canonical_url    = 'https://oh-haustechnik.de/kontakt.php';
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
<meta property="og:title" content="Kontakt – OH Haustechnik">
<meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
<meta property="og:image" content="https://oh-haustechnik.de/assets/img/lp/poster.jpg">
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
<script>window.OH_ADS_CONV = {"lead_form_submit":"sMAOCPTShb8cEKywsKhC","phone_click":"WVjGCJyxmeMcEKywsKhC"};</script>
<script defer src="/assets/js/oh-track.js"></script>
</head>
<body>

<?php $oh_active = 'kontakt'; include __DIR__ . '/includes/header-dark.php'; ?>

<!-- ============ HERO ============ -->
<section class="page-hero">
  <div class="wrap">
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · Kontakt</div>
    <h1 class="rv">Kontakt <span class="yellow">aufnehmen</span></h1>
    <p class="rv">Beschreiben Sie uns kurz Ihr Vorhaben — wir melden uns zeitnah und besprechen gemeinsam die technischen Anforderungen.</p>
  </div>
</section>

<!-- ============ KONTAKTINFORMATIONEN ============ -->
<section>
  <div class="wrap">
    <div class="shead rv"><div class="eyebrow">Kontaktinformationen</div><h2>So erreichen Sie uns.</h2></div>
    <div class="cgrid">
      <div class="cbox rv">
        <div class="k">Telefon</div>
        <div class="v"><a href="tel:+491757481006">+49 175 7481006</a></div>
        <div class="v small">Mo – Fr: 07:00 – 19:00 Uhr erreichbar</div>
      </div>
      <div class="cbox rv">
        <div class="k">E-Mail</div>
        <div class="v" style="font-size:16px"><a href="mailto:oh.Haustechnik@gmail.com">oh.Haustechnik@gmail.com</a></div>
        <div class="v small">Antwort i.d.R. innerhalb von 24 Stunden</div>
      </div>
      <div class="cbox rv">
        <div class="k">Einsatzgebiet</div>
        <div class="v">Raum Nürnberg</div>
        <div class="v small">Nürnberg · Fürth · Erlangen · umliegende Gemeinden</div>
      </div>
    </div>
  </div>
</section>

<!-- ============ ABLAUF ============ -->
<section class="alt-bg">
  <div class="wrap">
    <div class="shead rv"><div class="eyebrow">Und dann?</div><h2>Was passiert nach Ihrer Anfrage?</h2></div>
    <div class="steps">
      <div class="step rv"><div class="n">01</div><h3>Prüfung</h3><p>Wir prüfen Ihre Anfrage und melden uns innerhalb von 24 Stunden.</p></div>
      <div class="step rv"><div class="n">02</div><h3>Abstimmung</h3><p>Wir besprechen gemeinsam Ihren Bedarf und die technischen Anforderungen.</p></div>
      <div class="step rv"><div class="n">03</div><h3>Angebot</h3><p>Sie erhalten ein transparentes und vollständiges Angebot.</p></div>
    </div>
  </div>
</section>

<!-- ============ FORMULAR ============ -->
<section id="formular">
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 28px"><div class="eyebrow">Jetzt Angebot anfordern</div><h2>In 2 Minuten — kostenlos &amp; unverbindlich.</h2></div>
  </div>
  <div class="wrap" style="display:flex;justify-content:center">
    <form class="formwrap rv" id="kform" autocomplete="on" novalidate style="text-align:left;width:100%">
      <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
      <div class="field">
        <label for="kf-name">Name *</label>
        <input id="kf-name" name="name" type="text" required>
      </div>
      <div class="row2">
        <div class="field">
          <label for="kf-tel">Telefonnummer</label>
          <input id="kf-tel" name="telefon" type="tel">
        </div>
        <div class="field">
          <label for="kf-mail">E-Mail-Adresse</label>
          <input id="kf-mail" name="email" type="email">
        </div>
      </div>
      <div class="row2">
        <div class="field">
          <label for="kf-plz">Postleitzahl</label>
          <input id="kf-plz" name="plz" type="text" inputmode="numeric">
        </div>
        <div class="field">
          <label for="kf-ort">Ort</label>
          <input id="kf-ort" name="ort" type="text">
        </div>
      </div>
      <div class="field">
        <label for="kf-vorhaben">Ihr Anliegen</label>
        <textarea id="kf-vorhaben" name="vorhaben" placeholder="Kurz beschreiben, worum es geht …"></textarea>
      </div>
      <label class="check">
        <input type="checkbox" name="datenschutz" required>
        <span>Ich stimme der <a href="/datenschutz.php" style="text-decoration:underline">Datenschutzerklärung</a> zu und bin damit einverstanden, dass meine Angaben zur Bearbeitung meiner Anfrage verwendet werden. *</span>
      </label>
      <div class="err" data-err="1">Bitte Name sowie Telefon oder E-Mail angeben.</div>
      <p style="margin-top:22px">
        <button class="btn btn-y" id="kf-send" type="submit" style="width:100%">Angebot anfordern →</button>
      </p>
      <p class="formnote">Alternativ erreichen Sie uns direkt unter <a href="tel:+491757481006" style="text-decoration:underline">+49 175 7481006</a>.</p>
      <div class="thanks" id="kf-thanks">
        <div class="big">Danke!</div>
        <p>Ihre Anfrage ist bei uns eingegangen. Wir melden uns in der Regel innerhalb von 24 Stunden.</p>
      </div>
    </form>
  </div>
</section>

<?php include __DIR__ . '/includes/footer-dark.php'; ?>
<script>
(function(){
  var form=document.getElementById('kform');
  var startT=Date.now();
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var name=form.name.value.trim(), tel=form.telefon.value.trim(), email=form.email.value.trim(), ds=form.datenschutz.checked;
    var errEl=form.querySelector('[data-err="1"]');
    if(!name || (!tel && !email) || !ds){ errEl.style.display='block'; return; }
    errEl.style.display='none';
    var fd=new FormData(form);
    fd.append('elapsed', String(Date.now()-startT));
    var btn=document.getElementById('kf-send'); btn.disabled=true; btn.textContent='Senden…';
    fetch('/senden.php',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(d && d.ok){
        form.querySelectorAll('.field,.check,.err,.formnote').forEach(function(el){el.style.display='none';});
        document.getElementById('kf-thanks').style.display='block';
        btn.style.display='none';
        if(window.ohTrack) window.ohTrack('lead_form_submit', {value:1});
      } else {
        errEl.textContent=(d && d.error) || 'Es ist etwas schiefgelaufen. Bitte rufen Sie kurz an: 0175 7481006';
        errEl.style.display='block'; btn.disabled=false; btn.textContent='Angebot anfordern →';
      }
    }).catch(function(){
      document.getElementById('kf-thanks').style.display='block';
      if(window.ohTrack) window.ohTrack('lead_form_submit', {value:1});
    });
  });
})();
</script>
</body>
</html>
