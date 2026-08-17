<?php
/* Bewertungs-Seite: QR-Code zum Vorzeigen beim Kunden vor Ort, gleichzeitig
   als Link per WhatsApp/SMS verschickbar. Absichtlich noindex - die Seite ist
   ein Arbeitswerkzeug, kein Inhalt fuer die Google-Suche. */
$REVIEW = 'https://g.page/r/CUrJgck_kjcSEBE/review';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0A0A0A">
<title>Bewerten Sie OH Haustechnik</title>
<link rel="icon" href="/assets/img/favicon.ico">
<style>
@font-face{font-family:'Anton';font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/anton.woff2) format('woff2');}
@font-face{font-family:'Inter';font-style:normal;font-weight:600;font-display:swap;src:url(/assets/fonts/inter600.woff2) format('woff2');}
@font-face{font-family:'Inter';font-style:normal;font-weight:400;font-display:swap;src:url(/assets/fonts/inter400.woff2) format('woff2');}
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0A0A0A;color:#F5F5F5;font-family:'Inter',system-ui,sans-serif;
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center}
.wrap{max-width:520px;width:100%}
h1{font-family:'Anton',Impact,sans-serif;text-transform:uppercase;font-size:clamp(30px,8vw,46px);
  line-height:1.02;margin-bottom:10px}
.sub{color:#9CA3AF;font-size:15px;margin-bottom:18px}
.sterne{color:#FFB400;font-size:26px;letter-spacing:6px;margin-bottom:26px}
.qrbox{background:#fff;border-radius:24px;padding:22px;display:inline-block;margin-bottom:26px;
  box-shadow:0 18px 50px rgba(0,0,0,.55)}
.qrbox img{display:block;width:100%;max-width:320px;height:auto}
.btn{display:block;background:#325AA0;color:#fff;text-decoration:none;font-weight:600;
  padding:19px 26px;border-radius:12px;font-size:17px;margin-bottom:14px}
.btn:active{transform:translateY(1px)}
.hint{color:#6b7280;font-size:13.5px;line-height:1.6}
.zurueck{display:inline-block;margin-top:22px;color:#9CA3AF;font-size:13.5px;
  text-decoration:underline;text-underline-offset:3px}
</style>
</head>
<body>
<div class="wrap">
  <h1>Zufrieden mit<br>unserer Arbeit?</h1>
  <p class="sub">OH Haustechnik · Elektrotechnik Nürnberg</p>
  <div class="sterne">★★★★★</div>

  <div class="qrbox">
    <img src="/assets/img/qr-bewertung.png" alt="QR-Code für die Google-Bewertung"
         width="320" height="320">
  </div>

  <a class="btn" href="<?= htmlspecialchars($REVIEW) ?>" target="_blank" rel="noopener">
    Jetzt auf Google bewerten
  </a>
  <p class="hint">
    Code mit der Handy-Kamera scannen oder auf den Knopf tippen.<br>
    Eine Bewertung dauert unter einer Minute — vielen Dank!
  </p>
  <a class="zurueck" href="/index.php">Zur Website</a>
</div>
</body>
</html>
