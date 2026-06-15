<?php /* OH Büro – Design-Labor (Vorschau-Spielwiese, NICHT live im Büro).
   Hier schlägt Claude Design-Ideen mit Animationen/3D vor. Du schaust sie an,
   sagst was dir gefällt, dann übernehmen wir es gezielt ins echte Büro. */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH Büro · Design-Labor (Vorschau)</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#0b1220;--ink2:#0f1a2e;--card:#142033;--line:rgba(255,255,255,.08);
  --blue:#2E5A8C;--blue2:#3f7bf0;--gold:#FFD400;--cyan:#22d3ee;--green:#1aa86a;--txt:#e8eefb;--dim:#9aa7b8;}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Manrope',sans-serif;color:var(--txt);background:var(--ink);overflow-x:hidden;line-height:1.6}
h1,h2,h3{font-family:'Sora',sans-serif;letter-spacing:-.02em}
/* Animierter Mesh-Hintergrund */
.bg{position:fixed;inset:0;z-index:-1;background:var(--ink);overflow:hidden}
.bg::before,.bg::after{content:"";position:absolute;width:60vw;height:60vw;border-radius:50%;filter:blur(90px);opacity:.45;animation:float 18s ease-in-out infinite}
.bg::before{background:radial-gradient(circle,var(--blue2),transparent 70%);top:-15vw;left:-10vw}
.bg::after{background:radial-gradient(circle,var(--gold),transparent 70%);bottom:-20vw;right:-10vw;animation-delay:-9s;opacity:.25}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(6vw,5vw) scale(1.15)}}
.wrap{max-width:980px;margin:0 auto;padding:28px 20px 80px}
.lab-head{text-align:center;padding:30px 0 10px}
.lab-head .tag{display:inline-block;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);background:rgba(255,212,0,.1);padding:6px 14px;border-radius:999px;margin-bottom:14px}
.lab-head h1{font-size:clamp(28px,6vw,46px);background:linear-gradient(90deg,#fff,var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.lab-head p{color:var(--dim);max-width:50ch;margin:12px auto 0}
.note{background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.25);border-radius:14px;padding:16px 18px;margin:24px 0;font-size:14.5px}
.note b{color:var(--cyan)}
.sec{margin:46px 0}
.sec-t{font-size:13px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--cyan);margin-bottom:4px}
.sec h2{font-size:clamp(20px,4vw,28px);margin-bottom:6px}
.sec .desc{color:var(--dim);font-size:14.5px;margin-bottom:22px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}

/* Vorschlag 1: 3D-Tilt Glas-Karten */
.tilt{background:linear-gradient(145deg,rgba(255,255,255,.07),rgba(255,255,255,.02));border:1px solid var(--line);border-radius:20px;padding:26px;backdrop-filter:blur(12px);transition:transform .12s ease,box-shadow .2s;transform-style:preserve-3d;position:relative;overflow:hidden;cursor:pointer}
.tilt::after{content:"";position:absolute;inset:0;background:radial-gradient(400px circle at var(--mx,50%) var(--my,0%),rgba(255,255,255,.14),transparent 40%);opacity:0;transition:opacity .2s}
.tilt:hover::after{opacity:1}
.tilt .ic{width:54px;height:54px;border-radius:15px;display:grid;place-items:center;font-size:24px;background:linear-gradient(135deg,var(--blue2),var(--blue));margin-bottom:16px;box-shadow:0 8px 20px rgba(63,123,240,.4);transform:translateZ(40px)}
.tilt h3{font-size:19px;margin-bottom:6px;transform:translateZ(25px)}
.tilt p{color:var(--dim);font-size:14px;transform:translateZ(15px)}

/* Vorschlag 2: Rotierender 3D-Würfel */
.cube-stage{display:flex;justify-content:center;perspective:900px;padding:30px 0}
.cube{width:140px;height:140px;position:relative;transform-style:preserve-3d;animation:spin 14s linear infinite}
@keyframes spin{from{transform:rotateX(-20deg) rotateY(0)}to{transform:rotateX(-20deg) rotateY(360deg)}}
.cube .f{position:absolute;width:140px;height:140px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.18);border-radius:14px;font-family:'Sora';font-weight:800;font-size:26px;color:#fff;background:linear-gradient(135deg,rgba(46,90,140,.85),rgba(15,26,46,.9));box-shadow:inset 0 0 30px rgba(34,211,238,.15)}
.cube .f small{display:block;font-size:11px;font-weight:600;color:var(--cyan);letter-spacing:1px}
.cube .f1{transform:translateZ(70px)} .cube .f2{transform:rotateY(180deg) translateZ(70px)}
.cube .f3{transform:rotateY(90deg) translateZ(70px)} .cube .f4{transform:rotateY(-90deg) translateZ(70px)}
.cube .f5{transform:rotateX(90deg) translateZ(70px)} .cube .f6{transform:rotateX(-90deg) translateZ(70px)}

/* Vorschlag 3: Animierte Zähler + Glow */
.kpi{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:22px;text-align:center;position:relative;overflow:hidden}
.kpi::before{content:"";position:absolute;top:-40%;left:-40%;width:180%;height:80%;background:linear-gradient(90deg,transparent,rgba(34,211,238,.12),transparent);transform:rotate(8deg);animation:sheen 3.5s ease-in-out infinite}
@keyframes sheen{0%{transform:translateX(-60%) rotate(8deg)}60%,100%{transform:translateX(60%) rotate(8deg)}}
.kpi .num{font-family:'Sora';font-weight:800;font-size:34px;color:var(--gold);text-shadow:0 0 22px rgba(255,212,0,.35)}
.kpi .lbl{color:var(--dim);font-size:12px;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}

/* Vorschlag 4: Glow-Button + Puls-Badges */
.glowbtn{background:linear-gradient(135deg,var(--blue2),var(--blue));color:#fff;border:none;padding:15px 30px;border-radius:14px;font-family:'Sora';font-weight:700;font-size:15px;cursor:pointer;position:relative;box-shadow:0 0 0 0 rgba(63,123,240,.6);animation:pulse 2.2s infinite;transition:transform .15s}
.glowbtn:hover{transform:translateY(-2px)}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(63,123,240,.55)}70%{box-shadow:0 0 0 16px rgba(63,123,240,0)}100%{box-shadow:0 0 0 0 rgba(63,123,240,0)}}
.badges{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.pb{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid var(--line)}
.pb .dot{width:9px;height:9px;border-radius:50%;animation:blink 1.6s infinite}
.pb.on .dot{background:var(--green);box-shadow:0 0 10px var(--green)}
.pb.off .dot{background:#7b8aa0;animation:none}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.foot{text-align:center;color:var(--dim);font-size:13px;margin-top:50px}
</style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
  <div class="lab-head">
    <span class="tag">Design-Labor · Vorschau</span>
    <h1>OH Büro – Design-Ideen</h1>
    <p>Hier zeige ich dir Animationen & 3D-Elemente. Schau sie an, sag mir welche dir gefallen – dann übernehme ich sie gezielt ins echte Büro.</p>
  </div>

  <div class="note">👀 <b>So läuft's:</b> Diese Seite ist NICHT dein Büro – nur eine sichere Spielwiese. Nichts hier verändert dein laufendes System. Du sagst „Vorschlag 2 gefällt mir", ich baue es sauber ein.</div>

  <div class="sec">
    <div class="sec-t">Vorschlag 1</div>
    <h2>3D-Tilt Glas-Karten</h2>
    <p class="desc">Karten kippen leicht mit der Maus/dem Finger und bekommen einen Lichtschein. Modern & edel.</p>
    <div class="grid" id="tiltGrid">
      <div class="tilt"><div class="ic">📥</div><h3>Anfragen</h3><p>Neue Leads im Blick, mit Tiefe & Glanz.</p></div>
      <div class="tilt"><div class="ic">🏗️</div><h3>Baustellen</h3><p>Status & Deadlines plastisch dargestellt.</p></div>
      <div class="tilt"><div class="ic">📈</div><h3>Google Ads</h3><p>Performance mit Wow-Effekt.</p></div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-t">Vorschlag 2</div>
    <h2>Rotierender 3D-Würfel</h2>
    <p class="desc">Z. B. als Live-Kennzahl-Anzeige im Dashboard (dreht sich langsam, zeigt verschiedene Werte).</p>
    <div class="cube-stage">
      <div class="cube">
        <div class="f f1">5<small>Baustellen</small></div>
        <div class="f f2">2,17€<small>CPC</small></div>
        <div class="f f3">13<small>Klicks heute</small></div>
        <div class="f f4">5,7%<small>CTR</small></div>
        <div class="f f5">🔥<small>Hot Leads</small></div>
        <div class="f f6">OH<small>Haustechnik</small></div>
      </div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-t">Vorschlag 3</div>
    <h2>Animierte Zähler + Glanz-Effekt</h2>
    <p class="desc">Zahlen zählen beim Laden hoch, mit einem durchlaufenden Licht-Schimmer.</p>
    <div class="grid">
      <div class="kpi"><div class="num" data-to="13">0</div><div class="lbl">Klicks heute</div></div>
      <div class="kpi"><div class="num" data-to="5">0</div><div class="lbl">Baustellen</div></div>
      <div class="kpi"><div class="num" data-to="28">0</div><div class="lbl">Impressionen</div></div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-t">Vorschlag 4</div>
    <h2>Glow-Button & Status-Badges</h2>
    <p class="desc">Pulsierender Haupt-Button + Online/Offline-Badges mit Blink-Punkt (wie bei den Agenten).</p>
    <button class="glowbtn">🚀 Aktion ausführen</button>
    <div class="badges">
      <span class="pb on"><span class="dot"></span>Dilara · Online</span>
      <span class="pb on"><span class="dot"></span>Mert · Online</span>
      <span class="pb off"><span class="dot"></span>Kaan · Offline</span>
      <span class="pb off"><span class="dot"></span>Emre · Offline</span>
    </div>
  </div>

  <div class="foot">OH Büro Design-Labor · nur Vorschau · sag Claude, was übernommen werden soll.</div>
</div>

<script>
// 3D-Tilt bei Maus/Touch
document.querySelectorAll('.tilt').forEach(c=>{
  c.addEventListener('pointermove',e=>{
    const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width;const y=(e.clientY-r.top)/r.height;
    c.style.transform=`rotateY(${(x-.5)*16}deg) rotateX(${(.5-y)*16}deg) translateY(-2px)`;
    c.style.setProperty('--mx',(x*100)+'%');c.style.setProperty('--my',(y*100)+'%');
  });
  c.addEventListener('pointerleave',()=>{c.style.transform='';});
});
// Zähler hochzählen
document.querySelectorAll('.num[data-to]').forEach(el=>{
  const to=+el.dataset.to;let n=0;const step=Math.max(1,Math.round(to/30));
  const t=setInterval(()=>{n+=step;if(n>=to){n=to;clearInterval(t);}el.textContent=n;},40);
});
</script>
</body>
</html>
