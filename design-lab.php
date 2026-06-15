<?php /* OH Büro – Design-Labor (Vorschau-Spielwiese, NICHT live im Büro).
   Hier legt Claude Design-Vorschläge rein, die du erst visuell prüfst,
   bevor wir etwas ins echte Büro übernehmen. Aktuell leer – warte auf deine Richtung. */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH Büro · Design-Labor (Vorschau)</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#0b1220;--card:#142033;--line:rgba(255,255,255,.08);--blue2:#3f7bf0;--gold:#FFD400;--cyan:#22d3ee;--txt:#e8eefb;--dim:#9aa7b8;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Manrope',sans-serif;color:var(--txt);background:var(--ink);min-height:100vh;overflow-x:hidden;line-height:1.6}
h1{font-family:'Sora',sans-serif;letter-spacing:-.02em}
.bg{position:fixed;inset:0;z-index:-1;overflow:hidden}
.bg::before,.bg::after{content:"";position:absolute;width:60vw;height:60vw;border-radius:50%;filter:blur(90px);opacity:.4;animation:float 18s ease-in-out infinite}
.bg::before{background:radial-gradient(circle,var(--blue2),transparent 70%);top:-15vw;left:-10vw}
.bg::after{background:radial-gradient(circle,var(--gold),transparent 70%);bottom:-20vw;right:-10vw;opacity:.22;animation-delay:-9s}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(6vw,5vw) scale(1.15)}}
.wrap{max-width:760px;margin:0 auto;padding:60px 20px;text-align:center}
.tag{display:inline-block;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);background:rgba(255,212,0,.1);padding:6px 14px;border-radius:999px;margin-bottom:18px}
h1{font-size:clamp(28px,6vw,44px);background:linear-gradient(90deg,#fff,var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:14px}
.sub{color:var(--dim);max-width:48ch;margin:0 auto 36px}
.empty{background:var(--card);border:1px dashed rgba(255,255,255,.18);border-radius:20px;padding:46px 26px}
.empty .ic{font-size:42px;margin-bottom:14px}
.empty h2{font-family:'Sora';font-size:20px;margin-bottom:8px}
.empty p{color:var(--dim);font-size:14.5px;max-width:42ch;margin:0 auto}
.empty code{background:rgba(255,255,255,.06);padding:2px 8px;border-radius:6px;color:var(--cyan)}
.foot{color:var(--dim);font-size:13px;margin-top:34px}
</style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
  <span class="tag">Design-Labor · Vorschau</span>
  <h1>OH Büro – Design-Ideen</h1>
  <p class="sub">Hier lege ich dir Design-Vorschläge rein, die du erst anschaust, bevor wir etwas ins echte Büro übernehmen.</p>
  <div class="empty">
    <div class="ic">🎨</div>
    <h2>Aktuell keine Vorschläge</h2>
    <p>Sag mir die <b>Richtung</b>, die dir vorschwebt – z. B. „cleaner &amp; minimalistisch", „dunkel-edel", „verspielt mit 3D" oder ein <b>Vorbild</b> (eine Seite, die dir gefällt). Dann lege ich hier frische Vorschläge rein, die du visuell prüfst.</p>
  </div>
  <div class="foot">Nichts hier verändert dein laufendes Büro. Gefällt dir ein Vorschlag → ich übernehme ihn. Gefällt er nicht → sofort raus.</div>
</div>
</body>
</html>
