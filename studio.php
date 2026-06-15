<?php /* OH Büro – Cockpit-Vorschau (NEU, 3D-Dashboard). Beispieldaten, NICHT das Live-Büro. */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH Büro · Cockpit (Vorschau)</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{--ink:#1A1A2E;--ink2:#12132a;--blue:#2E5A8C;--blue-l:#5b86c4;--yellow:#FFD400;--white:#eef1fa;--dim:#9aa3bd;
  --glass:rgba(255,255,255,.045);--line:rgba(255,255,255,.09);--ok:#1aa86a;--run:#e8902a;--grey:#7b8aa0;--red:#ff5b6e;
  --ease:cubic-bezier(.22,1,.36,1)}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--ink);color:var(--white);line-height:1.5;-webkit-font-smoothing:antialiased;overflow-x:hidden;min-height:100vh}
h1,h2,h3,.num{font-family:'Montserrat',sans-serif;font-weight:800;letter-spacing:-.01em}
/* Ambient-Hintergrund (GPU-günstig, dezent) */
.ambient{position:fixed;inset:0;z-index:-1;background:var(--ink);overflow:hidden}
.ambient::before,.ambient::after{content:"";position:absolute;width:55vw;height:55vw;border-radius:50%;filter:blur(110px);opacity:.4;animation:drift 26s ease-in-out infinite}
.ambient::before{background:radial-gradient(circle,rgba(46,90,140,.6),transparent 70%);top:-12vw;right:-8vw}
.ambient::after{background:radial-gradient(circle,rgba(255,212,0,.14),transparent 70%);bottom:-16vw;left:-6vw;animation-delay:-13s}
@keyframes drift{0%,100%{transform:translate(0,0)}50%{transform:translate(-5vw,4vw)}}
.shell{max-width:1280px;margin:0 auto;padding:26px 24px 70px}
/* Topbar */
.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:26px;opacity:0}
.top .brand{font-family:'Montserrat';font-weight:800;font-size:19px;letter-spacing:2px}
.top .brand b{color:var(--yellow)}
.top .meta{display:flex;align-items:center;gap:14px;font-size:13px;color:var(--dim)}
.tag{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--yellow);background:rgba(255,212,0,.1);padding:5px 11px;border-radius:999px}
/* Karten */
.card{background:var(--glass);border:1px solid var(--line);border-radius:20px;backdrop-filter:blur(14px);
  box-shadow:0 18px 50px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.06);position:relative;overflow:hidden}
.card .c-h{display:flex;align-items:center;justify-content:space-between;padding:18px 20px 0}
.card .c-h h3{font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--dim)}
/* KPI */
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
.kpi{padding:20px;opacity:0;transform:translateY(22px)}
.kpi .ic{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;font-size:18px;margin-bottom:14px;
  background:linear-gradient(135deg,var(--blue),#1c3454);border:1px solid var(--line)}
.kpi .k-l{font-size:12.5px;color:var(--dim);text-transform:uppercase;letter-spacing:.5px}
.kpi .k-v{font-size:30px;margin-top:4px;line-height:1.1}
.kpi.accent .k-v{color:var(--yellow)}
.kpi .k-s{font-size:12px;color:var(--dim);margin-top:6px}
.kpi .k-s.warn{color:var(--red);font-weight:600}
/* Hauptgrid */
.grid{display:grid;grid-template-columns:1.7fr 1fr;gap:16px;margin-bottom:18px}
.chart-card{min-height:340px;opacity:0;transform:translateY(22px)}
#chart{width:100%;height:300px;display:block}
.chart-fallback{display:none;padding:0 20px 20px;gap:10px;align-items:flex-end;height:280px}
.chart-fallback .b{flex:1;background:linear-gradient(var(--blue-l),var(--blue));border-radius:7px 7px 0 0;height:0;transition:height 1s var(--ease)}
.chart-fallback .b.cur{background:linear-gradient(var(--yellow),#c9a800)}
.tip{position:absolute;pointer-events:none;background:#0c0d18;border:1px solid var(--line);border-radius:10px;padding:8px 12px;
  font-size:13px;opacity:0;transform:translate(-50%,-120%);transition:opacity .15s;white-space:nowrap;z-index:5}
.tip b{color:var(--yellow)}
/* Gauge */
.gauge-card{display:flex;flex-direction:column;opacity:0;transform:translateY(22px)}
.gauge-wrap{flex:1;display:grid;place-items:center;padding:10px}
.gauge{position:relative;width:210px;height:210px}
.gauge svg{transform:rotate(-90deg)}
.gauge .g-center{position:absolute;inset:0;display:grid;place-items:center;text-align:center}
.gauge .g-center .num{font-size:46px}
.gauge .g-center small{display:block;font-size:12px;color:var(--dim);text-transform:uppercase;letter-spacing:1px}
/* Baustellen */
.bs-card{padding-bottom:8px;opacity:0;transform:translateY(22px)}
.bs-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:14px 20px 22px}
.bs{background:rgba(255,255,255,.03);border:1px solid var(--line);border-left:4px solid var(--grey);border-radius:14px;padding:16px;
  transition:transform .2s var(--ease),border-color .3s;transform-style:preserve-3d;cursor:pointer}
.bs.run{border-left-color:var(--run)} .bs.done{border-left-color:var(--ok)} .bs.offen{border-left-color:var(--grey)}
.bs h4{font-size:15px;margin-bottom:4px}
.bs .m{font-size:12px;color:var(--dim)}
.bs .next{font-size:12.5px;color:var(--blue-l);margin-top:8px}
.bs .dl{font-size:12px;color:var(--dim);margin-top:8px}
.bs .dl.rot{color:var(--red);font-weight:700}
.pill{display:inline-block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:3px 8px;border-radius:999px;margin-top:10px}
.pill.run{background:rgba(232,144,42,.18);color:var(--run)} .pill.done{background:rgba(26,168,106,.18);color:var(--ok)} .pill.offen{background:rgba(123,138,160,.18);color:var(--grey)}
@media(max-width:980px){.kpis{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:1fr}.bs-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.bs-row{grid-template-columns:1fr}.kpi .k-v{font-size:26px}}
@media (prefers-reduced-motion:reduce){.ambient::before,.ambient::after{animation:none}
  .top,.kpi,.chart-card,.gauge-card,.bs-card{opacity:1!important;transform:none!important}}
</style>
</head>
<body>
<div class="ambient"></div>
<div class="shell">
  <div class="top">
    <div class="brand">OH <b>BÜRO</b> · Cockpit</div>
    <div class="meta"><span id="today"></span><span class="tag">Vorschau · Beispieldaten</span></div>
  </div>

  <div class="kpis">
    <div class="card kpi"><div class="ic">🏗️</div><div class="k-l">Offene Baustellen</div><div class="k-v num" data-to="5">0</div><div class="k-s">2 laufen aktuell</div></div>
    <div class="card kpi"><div class="ic">💶</div><div class="k-l">Umsatz · Monat</div><div class="k-v num" data-to="38500" data-suf=" €">0</div><div class="k-s">+12 % zum Vormonat</div></div>
    <div class="card kpi accent"><div class="ic">📊</div><div class="k-l">Offener Auftragswert</div><div class="k-v num" data-to="128500" data-suf=" €">0</div><div class="k-s">über alle Baustellen</div></div>
    <div class="card kpi"><div class="ic">⏱️</div><div class="k-l">Nächste Deadline</div><div class="k-v num" data-to="3" data-suf=" Tage">0</div><div class="k-s warn">Müllerstr. 5 · Dosen setzen</div></div>
  </div>

  <div class="grid">
    <div class="card chart-card">
      <div class="c-h"><h3>Umsatz-Verlauf · 8 Monate</h3><span class="tag" style="color:var(--blue-l);background:rgba(91,134,196,.12)">in € (Tsd.)</span></div>
      <canvas id="chart"></canvas>
      <div class="chart-fallback" id="chartFb"></div>
      <div class="tip" id="tip"></div>
    </div>
    <div class="card gauge-card">
      <div class="c-h"><h3>Auslastung</h3></div>
      <div class="gauge-wrap"><div class="gauge">
        <svg width="210" height="210" viewBox="0 0 210 210">
          <circle cx="105" cy="105" r="88" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="16"/>
          <circle id="gArc" cx="105" cy="105" r="88" fill="none" stroke="url(#gg)" stroke-width="16" stroke-linecap="round"/>
          <defs><linearGradient id="gg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#5b86c4"/><stop offset="1" stop-color="#FFD400"/></linearGradient></defs>
        </svg>
        <div class="g-center"><div><div class="num"><span id="gPct">0</span>%</div><small>diese Woche</small></div></div>
      </div></div>
    </div>
  </div>

  <div class="card bs-card">
    <div class="c-h"><h3>Baustellen-Status</h3></div>
    <div class="bs-row">
      <div class="bs run" data-tilt><h4>Müllerstr. 5</h4><div class="m">Fam. Huber · 8.500 €</div><div class="next">➡️ Dosen setzen</div><div class="dl rot">📅 in 3 Tagen</div><span class="pill run">Läuft</span></div>
      <div class="bs run" data-tilt><h4>Smart Home Erlangen</h4><div class="m">Bauträger Nord · 21.000 €</div><div class="next">➡️ Loxone konfigurieren</div><div class="dl">📅 in 20 Tagen</div><span class="pill run">Läuft</span></div>
      <div class="bs offen" data-tilt><h4>Altbau Fürth</h4><div class="m">Privat · 9.900 €</div><div class="next">➡️ Aufmaß planen</div><div class="dl">📅 in 12 Tagen</div><span class="pill offen">Offen</span></div>
      <div class="bs done" data-tilt><h4>Sanierung Stein</h4><div class="m">Fam. Wagner · 7.200 €</div><div class="next">✓ abgeschlossen</div><div class="dl">📅 erledigt</div><span class="pill done">Fertig</span></div>
    </div>
  </div>
</div>

<script type="importmap">{ "imports": { "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js" } }</script>
<script type="module">
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const data = [18,22,19,28,24,31,35,38.5];
const labels = ['Nov','Dez','Jan','Feb','Mär','Apr','Mai','Jun'];
const canvas = document.getElementById('chart');
let use3D = !reduce && canvas;
if (use3D){ try {
  const THREE = await import('three');
  const renderer = new THREE.WebGLRenderer({canvas, alpha:true, antialias:true, powerPreference:'high-performance'});
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  const scene = new THREE.Scene();
  const cam = new THREE.PerspectiveCamera(38, 2, .1, 100); cam.position.set(0,4.6,12); cam.lookAt(0,1.4,0);
  scene.add(new THREE.AmbientLight(0x9fb4e0,.85));
  const dir = new THREE.DirectionalLight(0xffffff,.7); dir.position.set(4,9,7); scene.add(dir);
  const max = Math.max(...data); const n = data.length; const gap = 1.5; const w = .9;
  const x0 = -(n-1)*gap/2;
  const bars=[]; const tops=[];
  data.forEach((v,i)=>{
    const h = (v/max)*5 + .15;
    const geo = new THREE.BoxGeometry(w,1,w); geo.translate(0,.5,0); // Anker unten
    const isCur = i===n-1;
    const mat = new THREE.MeshStandardMaterial({color:isCur?0xFFD400:0x3f6fb0,metalness:.3,roughness:.35,
      emissive:isCur?0x3a2e00:0x0c1830,emissiveIntensity:.6});
    const m = new THREE.Mesh(geo,mat); m.position.set(x0+i*gap,0,0); m.scale.y=0; m.userData={i,v,h};
    scene.add(m); bars.push(m); tops.push(new THREE.Vector3(x0+i*gap,h,0));
  });
  // Trendlinie über den Balken
  const lineGeo = new THREE.BufferGeometry().setFromPoints(tops.map(p=>p.clone()));
  const line = new THREE.Line(lineGeo, new THREE.LineBasicMaterial({color:0xFFD400,transparent:true,opacity:.0}));
  scene.add(line);
  // Bodengitter (Tiefe)
  const grid = new THREE.GridHelper(22,22,0x2a3a5e,0x1c2742); grid.position.y=0; grid.material.transparent=true; grid.material.opacity=.25; scene.add(grid);

  const tip = document.getElementById('tip'); const ray=new THREE.Raycaster(); const mouse=new THREE.Vector2(-9,-9);
  canvas.addEventListener('pointermove',e=>{const r=canvas.getBoundingClientRect();mouse.x=((e.clientX-r.left)/r.width)*2-1;mouse.y=-((e.clientY-r.top)/r.height)*2+1;
    tip.dataset.cx=e.clientX-r.left; tip.dataset.cy=e.clientY-r.top;});
  canvas.addEventListener('pointerleave',()=>{tip.style.opacity=0;});
  function resize(){const r=canvas.getBoundingClientRect();renderer.setSize(r.width,r.height,false);cam.aspect=r.width/r.height;cam.updateProjectionMatrix();}
  addEventListener('resize',resize); resize();

  let start=null, run=true; document.addEventListener('visibilitychange',()=>run=!document.hidden);
  const easeOut=x=>1-Math.pow(1-x,3);
  function frame(ts){ requestAnimationFrame(frame); if(!run) return; if(start===null)start=ts; const el=(ts-start)/1000;
    let allDone=true;
    bars.forEach((b,i)=>{ const p=Math.max(0,Math.min(1,(el-.2-i*.08)/.9)); if(p<1)allDone=false; b.scale.y=easeOut(p)*b.userData.h;
      tops[i].y=b.scale.y; });
    line.geometry.setFromPoints(tops); line.material.opacity = Math.min(.9, Math.max(0,(el-1)/.6));
    scene.rotation.y = Math.sin(el*.25)*.05; // dezentes Wogen
    // Hover
    ray.setFromCamera(mouse,cam); const hit=ray.intersectObjects(bars)[0];
    bars.forEach(b=>b.material.emissiveIntensity=.6);
    if(hit){ hit.object.material.emissiveIntensity=1.4; tip.innerHTML=labels[hit.object.userData.i]+': <b>'+hit.object.userData.v+'.000 €</b>';
      tip.style.left=tip.dataset.cx+'px'; tip.style.top=tip.dataset.cy+'px'; tip.style.opacity=1; canvas.style.cursor='pointer'; }
    else { tip.style.opacity=0; canvas.style.cursor='default'; }
    renderer.render(scene,cam);
  }
  requestAnimationFrame(frame);
} catch(e){ use3D=false; } }
// Fallback: flache animierte Balken
if(!use3D){ canvas.style.display='none'; const fb=document.getElementById('chartFb'); fb.style.display='flex';
  const max=Math.max(...data); fb.innerHTML=data.map((v,i)=>`<div class="b${i===data.length-1?' cur':''}" data-h="${(v/max)*100}"></div>`).join('');
  requestAnimationFrame(()=>fb.querySelectorAll('.b').forEach((b,i)=>setTimeout(()=>b.style.height=b.dataset.h+'%',120+i*70)));
}
</script>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script>
(function(){
  const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.getElementById('today').textContent=new Date().toLocaleDateString('de-DE',{weekday:'long',day:'2-digit',month:'long'});
  // Zahlen hochzählen
  function countTo(el,to,dur,suf){const t0=performance.now();function st(t){const p=Math.min(1,(t-t0)/dur);const e=1-Math.pow(1-p,3);
    el.textContent=Math.round(to*e).toLocaleString('de-DE')+(suf||'');if(p<1)requestAnimationFrame(st);}requestAnimationFrame(st);}
  function startCounters(){document.querySelectorAll('.num[data-to]').forEach(el=>countTo(el,+el.dataset.to,1300,el.dataset.suf||''));}
  // Gauge
  const arc=document.getElementById('gArc'); const C=2*Math.PI*88; if(arc){arc.style.strokeDasharray=C;arc.style.strokeDashoffset=C;}
  function fillGauge(pct){const target=C*(1-pct/100);const t0=performance.now();function st(t){const p=Math.min(1,(t-t0)/1400);const e=1-Math.pow(1-p,3);
    if(arc)arc.style.strokeDashoffset=C-(C-target)*e;document.getElementById('gPct').textContent=Math.round(pct*e);if(p<1)requestAnimationFrame(st);}requestAnimationFrame(st);}

  if(window.gsap && !reduce){
    const tl=gsap.timeline({defaults:{ease:'expo.out'}});
    tl.to('.top',{opacity:1,duration:.6})
      .to('.kpi',{opacity:1,y:0,duration:.8,stagger:.08,onStart:startCounters},'-=.2')
      .to('.chart-card',{opacity:1,y:0,duration:.8},'-=.3')
      .to('.gauge-card',{opacity:1,y:0,duration:.8,onStart:()=>fillGauge(78)},'-=.6')
      .to('.bs-card',{opacity:1,y:0,duration:.8},'-=.5');
  } else { startCounters(); fillGauge(78); }

  // 3D-Tilt auf Baustellen-Karten (Desktop)
  if(matchMedia('(hover:hover)').matches){
    document.querySelectorAll('[data-tilt]').forEach(c=>{
      c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;
        c.style.transform=`perspective(800px) rotateY(${(x-.5)*8}deg) rotateX(${(.5-y)*8}deg) translateY(-3px)`;});
      c.addEventListener('pointerleave',()=>c.style.transform='');
    });
  }
})();
</script>
</body>
</html>
