<?php /* OH Büro – Cockpit-Vorschau (NEU, fliessender Analytics-Look). Beispieldaten, NICHT Live-Büro. */ ?>
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
:root{--ink:#13142a;--ink2:#0e0f22;--blue:#3f7bf0;--cyan:#22d3ee;--violet:#8b5cf6;--pink:#ec4899;--yellow:#FFD400;
  --white:#eef1fa;--dim:#9aa3bd;--glass:rgba(255,255,255,.05);--line:rgba(255,255,255,.09);
  --ok:#1aa86a;--run:#e8902a;--grey:#7b8aa0;--red:#ff5b6e;--ease:cubic-bezier(.22,1,.36,1)}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--ink);color:var(--white);line-height:1.5;-webkit-font-smoothing:antialiased;overflow-x:hidden;min-height:100vh}
h1,h2,h3,.num{font-family:'Montserrat',sans-serif;font-weight:800;letter-spacing:-.01em}
.ambient{position:fixed;inset:0;z-index:-1;background:radial-gradient(120% 80% at 50% -10%,#1b1d3e,var(--ink) 60%);overflow:hidden}
.ambient::before,.ambient::after{content:"";position:absolute;width:55vw;height:55vw;border-radius:50%;filter:blur(120px);opacity:.5;animation:drift 28s ease-in-out infinite}
.ambient::before{background:radial-gradient(circle,rgba(139,92,246,.45),transparent 70%);top:-14vw;right:-6vw}
.ambient::after{background:radial-gradient(circle,rgba(34,211,238,.28),transparent 70%);bottom:-18vw;left:-8vw;animation-delay:-14s}
@keyframes drift{0%,100%{transform:translate(0,0)}50%{transform:translate(-5vw,4vw)}}
.shell{max-width:1180px;margin:0 auto;padding:24px 22px 70px}
.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;opacity:0}
.top .brand{font-family:'Montserrat';font-weight:800;font-size:18px;letter-spacing:2px}
.top .brand b{color:var(--yellow)}
.top .meta{display:flex;align-items:center;gap:12px;font-size:12.5px;color:var(--dim)}
.tag{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--yellow);background:rgba(255,212,0,.1);padding:5px 11px;border-radius:999px}
.card{background:var(--glass);border:1px solid var(--line);border-radius:22px;backdrop-filter:blur(16px);box-shadow:0 22px 60px rgba(0,0,0,.4),inset 0 1px 0 rgba(255,255,255,.07);position:relative;overflow:hidden}
/* KPI-Reihen */
.kpis{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.kpi{padding:20px 22px;opacity:0;transform:translateY(22px);display:flex;justify-content:space-between;align-items:flex-start;gap:14px}
.kpi .k-l{font-size:12.5px;color:var(--dim);text-transform:uppercase;letter-spacing:.5px}
.kpi .k-v{font-size:clamp(26px,4.6vw,38px);margin-top:8px;line-height:1.05}
.kpi.acc .k-v{color:var(--yellow)}
.kpi .badge{font-size:12px;font-weight:700;padding:4px 9px;border-radius:8px;background:rgba(26,168,106,.16);color:var(--ok);white-space:nowrap}
.kpi .badge.flat{background:rgba(123,138,160,.16);color:var(--grey)}
.kpi .spark{margin-top:14px}
.kpi .ava{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;font-size:22px;background:linear-gradient(135deg,var(--blue),var(--violet));flex-shrink:0}
/* Chart */
.chart-card{padding:20px 8px 10px 8px;margin-bottom:16px;opacity:0;transform:translateY(22px)}
.chart-card .c-h{display:flex;align-items:center;justify-content:space-between;padding:0 14px 6px}
.chart-card h3{font-size:14px;font-weight:700;letter-spacing:.3px}
.legend{display:flex;gap:16px;font-size:12px;color:var(--dim)}
.legend i{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;vertical-align:middle}
#chart{width:100%;height:300px;display:block;touch-action:none}
.x-ax{display:flex;justify-content:space-between;padding:4px 16px 0;font-size:11px;color:var(--dim)}
.hovline{position:absolute;top:14px;bottom:34px;width:1px;background:rgba(255,255,255,.25);opacity:0;pointer-events:none}
.tip{position:absolute;pointer-events:none;background:#0b0c1c;border:1px solid var(--line);border-radius:12px;padding:10px 13px;font-size:12.5px;opacity:0;transform:translate(-50%,-118%);transition:opacity .12s;white-space:nowrap;z-index:6;box-shadow:0 12px 30px rgba(0,0,0,.5)}
.tip .row{display:flex;align-items:center;gap:7px;margin-top:3px}
.tip .row i{width:8px;height:8px;border-radius:50%}
.tip .t-d{font-weight:700;color:var(--white);margin-bottom:2px}
/* Baustellen */
.bs-card{padding-bottom:8px;opacity:0;transform:translateY(22px)}
.bs-card .c-h{padding:18px 20px 0}.bs-card h3{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--dim)}
.bs-row{display:grid;grid-template-columns:repeat(4,1fr);gap:13px;padding:14px 20px 22px}
.bs{background:rgba(255,255,255,.03);border:1px solid var(--line);border-left:4px solid var(--grey);border-radius:14px;padding:15px;transition:transform .2s var(--ease),border-color .3s;transform-style:preserve-3d;cursor:pointer}
.bs.run{border-left-color:var(--run)}.bs.done{border-left-color:var(--ok)}.bs.offen{border-left-color:var(--grey)}
.bs h4{font-size:14.5px;margin-bottom:4px}.bs .m{font-size:12px;color:var(--dim)}
.bs .next{font-size:12px;color:var(--cyan);margin-top:8px}.bs .dl{font-size:12px;color:var(--dim);margin-top:8px}.bs .dl.rot{color:var(--red);font-weight:700}
.pill{display:inline-block;font-size:10px;font-weight:700;text-transform:uppercase;padding:3px 8px;border-radius:999px;margin-top:10px}
.pill.run{background:rgba(232,144,42,.18);color:var(--run)}.pill.done{background:rgba(26,168,106,.18);color:var(--ok)}.pill.offen{background:rgba(123,138,160,.18);color:var(--grey)}
@media(max-width:760px){.bs-row{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.kpis{grid-template-columns:1fr}.bs-row{grid-template-columns:1fr}}
/* Sidebar (aufklappbare Navigation – wie im Büro) */
.sb{position:fixed;left:0;top:0;bottom:0;width:240px;z-index:40;background:rgba(13,14,32,.88);backdrop-filter:blur(18px);
  border-right:1px solid var(--line);display:flex;flex-direction:column;padding:22px 14px;transition:transform .35s var(--ease)}
.sb-brand{padding:4px 12px 20px}
.brand-logo{background:#fff;border-radius:11px;padding:5px 8px;display:inline-flex;align-items:center;box-shadow:0 4px 14px rgba(0,0,0,.3)}
.brand-logo img{height:30px;width:auto;display:block}
.brand-logo.sm{border-radius:8px;padding:3px 5px}.brand-logo.sm img{height:20px}
.sb nav{display:flex;flex-direction:column;gap:4px;flex:1}
.sb-item{display:flex;align-items:center;gap:12px;width:100%;text-align:left;background:none;border:none;color:var(--dim);font:inherit;font-size:14.5px;font-weight:500;padding:12px 14px;border-radius:12px;cursor:pointer;transition:background .2s,color .2s}
.sb-item .ic{font-size:17px;width:22px;text-align:center}
.sb-item:hover{background:rgba(255,255,255,.05);color:var(--white)}
.sb-item.active{background:linear-gradient(135deg,rgba(63,123,240,.28),rgba(139,92,246,.2));color:#fff}
.sb-item.active .ic{filter:drop-shadow(0 0 6px rgba(99,102,241,.7))}
.sb-foot{border-top:1px solid var(--line);padding-top:10px}
.sb-toggle{display:none}
.sb-back{display:none;position:fixed;inset:0;z-index:39;background:rgba(0,0,0,.5);opacity:0;transition:opacity .3s}
.sb-back.show{opacity:1}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(18px);background:#0b0c1c;border:1px solid var(--line);padding:12px 18px;border-radius:12px;font-size:13.5px;opacity:0;transition:.3s;z-index:80;box-shadow:0 14px 40px rgba(0,0,0,.5)}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
@media(min-width:981px){.shell{margin-left:260px}}
@media(max-width:980px){
  .sb{transform:translateX(-100%);width:264px}
  .sb.open{transform:translateX(0);box-shadow:0 0 70px rgba(0,0,0,.7)}
  .sb-back{display:block}
  .sb-toggle{display:grid;place-items:center;position:fixed;top:15px;left:15px;z-index:45;width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.08);border:1px solid var(--line);color:#fff;font-size:19px;cursor:pointer;backdrop-filter:blur(10px)}
  .top{padding-left:54px}
}
@media (prefers-reduced-motion:reduce){.ambient::before,.ambient::after{animation:none}.top,.kpi,.chart-card,.bs-card{opacity:1!important;transform:none!important}}
</style>
</head>
<body>
<div class="ambient"></div>
<button class="sb-toggle" id="sbToggle" aria-label="Menü">☰</button>
<div class="sb-back" id="sbBack"></div>
<aside class="sb" id="sb">
  <div class="sb-brand"><span class="brand-logo"><img src="assets/img/logohaustechnikneu.png" alt="OH Haustechnik"></span></div>
  <nav>
    <button class="sb-item active" data-view="cockpit"><span class="ic">📊</span>Cockpit</button>
    <button class="sb-item" data-view="baustellen"><span class="ic">🏗️</span>Baustellen</button>
    <button class="sb-item" data-view="angebote"><span class="ic">📄</span>Angebote</button>
    <button class="sb-item" data-view="material"><span class="ic">📦</span>Material</button>
    <button class="sb-item" data-view="auswertung"><span class="ic">📈</span>Auswertung</button>
  </nav>
  <div class="sb-foot"><button class="sb-item" data-view="einstellungen"><span class="ic">⚙️</span>Einstellungen</button></div>
</aside>
<div class="shell">
  <div class="top"><div class="brand"><span class="brand-logo sm"><img src="assets/img/logohaustechnikneu.png" alt="OH Haustechnik"></span> Cockpit</div><div class="meta"><span id="today"></span><span class="tag">Vorschau · Beispieldaten</span></div></div>

  <div class="kpis">
    <div class="card kpi"><div><div class="k-l">Umsatz · Monat</div><div class="k-v num" data-to="38500" data-suf=" €">0</div></div><div class="badge">▲ 12 %</div></div>
    <div class="card kpi"><div><div class="k-l">Auslastung</div><div class="k-v num" data-to="78" data-suf=" %">0</div></div><div class="ava">⚡</div></div>
  </div>

  <div class="card chart-card">
    <div class="c-h"><h3>Umsatz &amp; Anfragen · Verlauf</h3>
      <div class="legend"><span><i style="background:linear-gradient(90deg,#6366f1,#ec4899)"></i>Umsatz</span><span><i style="background:#22d3ee"></i>Anfragen</span></div></div>
    <svg id="chart" viewBox="0 0 800 300" preserveAspectRatio="none">
      <defs>
        <linearGradient id="areaA" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#a855f7" stop-opacity=".45"/><stop offset="1" stop-color="#a855f7" stop-opacity="0"/></linearGradient>
        <linearGradient id="lineA" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#6366f1"/><stop offset="1" stop-color="#ec4899"/></linearGradient>
        <linearGradient id="areaB" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#22d3ee" stop-opacity=".22"/><stop offset="1" stop-color="#22d3ee" stop-opacity="0"/></linearGradient>
        <filter id="glow"><feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
      </defs>
      <path id="fillB" fill="url(#areaB)" opacity="0"></path>
      <path id="fillA" fill="url(#areaA)" opacity="0"></path>
      <path id="strokeB" fill="none" stroke="#22d3ee" stroke-width="2.2" stroke-opacity=".8" stroke-linecap="round"></path>
      <path id="strokeA" fill="none" stroke="url(#lineA)" stroke-width="3.4" stroke-linecap="round" filter="url(#glow)"></path>
      <circle id="dotA" r="5" fill="#ec4899" opacity="0"></circle>
    </svg>
    <div class="x-ax" id="xax"></div>
    <div class="hovline" id="hov"></div>
    <div class="tip" id="tip"></div>
  </div>

  <div class="kpis">
    <div class="card kpi"><div><div class="k-l">Offener Auftragswert</div><div class="k-v num acc" data-to="128500" data-suf=" €">0</div></div><div class="badge flat">5 Baustellen</div></div>
    <div class="card kpi"><div><div class="k-l">Nächste Deadline</div><div class="k-v num" data-to="3" data-suf=" Tage">0</div></div><div class="badge" style="background:rgba(255,91,110,.16);color:var(--red)">Müllerstr. 5</div></div>
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

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script>
(function(){
  const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.getElementById('today').textContent=new Date().toLocaleDateString('de-DE',{weekday:'long',day:'2-digit',month:'long'});
  // ---- Daten ----
  const labels=['Nov','Dez','Jan','Feb','Mär','Apr','Mai','Jun'];
  const A=[18,22,19,28,24,31,35,38.5];   // Umsatz (Tsd. €)
  const B=[6,9,7,11,10,14,16,19];        // Anfragen
  const W=800,H=300,padX=24,padT=18,padB=34;
  function pts(arr){const mx=Math.max(...arr),mn=Math.min(...arr);const rng=(mx-mn)||1;
    return arr.map((v,i)=>({x:padX+i*(W-2*padX)/(arr.length-1), y:padT+(1-(v-mn)/rng*.82-.06)*(H-padT-padB)}));}
  const pA=pts(A), pB=pts(B);
  function smooth(p){let d=`M ${p[0].x},${p[0].y}`;for(let i=0;i<p.length-1;i++){const p0=p[i-1]||p[i],p1=p[i],p2=p[i+1],p3=p[i+2]||p2;
    d+=` C ${p1.x+(p2.x-p0.x)/6},${p1.y+(p2.y-p0.y)/6} ${p2.x-(p3.x-p1.x)/6},${p2.y-(p3.y-p1.y)/6} ${p2.x},${p2.y}`;}return d;}
  const dA=smooth(pA), dB=smooth(pB);
  const g=id=>document.getElementById(id);
  g('strokeA').setAttribute('d',dA); g('strokeB').setAttribute('d',dB);
  g('fillA').setAttribute('d',dA+` L ${pA[pA.length-1].x},${H-padB} L ${pA[0].x},${H-padB} Z`);
  g('fillB').setAttribute('d',dB+` L ${pB[pB.length-1].x},${H-padB} L ${pB[0].x},${H-padB} Z`);
  g('xax').innerHTML=labels.map(l=>`<span>${l}</span>`).join('');
  // Linien-Zeichen-Animation
  [['strokeA',1100],['strokeB',1100]].forEach(([id,dur])=>{const p=g(id);const L=p.getTotalLength();p.style.strokeDasharray=L;p.style.strokeDashoffset=reduce?0:L;});
  function drawLines(){['strokeA','strokeB'].forEach((id,k)=>{const p=g(id);const L=p.getTotalLength();const t0=performance.now()+k*150;
    function st(t){const pr=Math.min(1,Math.max(0,(t-t0)/1100));p.style.strokeDashoffset=L*(1-(1-Math.pow(1-pr,3)));if(pr<1)requestAnimationFrame(st);}requestAnimationFrame(st);});
    g('fillA').style.transition=g('fillB').style.transition='opacity 1s ease .4s';g('fillA').style.opacity=1;g('fillB').style.opacity=1;}
  // Hover (vertikale Linie + Tooltip)
  const svg=g('chart'),hov=g('hov'),tip=g('tip'),dot=g('dotA'),card=svg.closest('.chart-card');
  svg.addEventListener('pointermove',e=>{const r=svg.getBoundingClientRect();const rel=(e.clientX-r.left)/r.width;
    let i=Math.round(rel*(A.length-1));i=Math.max(0,Math.min(A.length-1,i));
    const px=(pA[i].x/W)*r.width, pyA=(pA[i].y/H)*r.height;
    hov.style.left=px+'px';hov.style.opacity=1;
    dot.setAttribute('cx',pA[i].x);dot.setAttribute('cy',pA[i].y);dot.setAttribute('opacity','1');
    tip.style.left=px+'px';tip.style.top=pyA+'px';tip.style.opacity=1;
    tip.innerHTML=`<div class="t-d">${labels[i]}</div><div class="row"><i style="background:#ec4899"></i>Umsatz: <b>${A[i]}.000 €</b></div><div class="row"><i style="background:#22d3ee"></i>Anfragen: <b>${B[i]}</b></div>`;});
  svg.addEventListener('pointerleave',()=>{hov.style.opacity=0;tip.style.opacity=0;dot.setAttribute('opacity','0');});
  // ---- Counter ----
  function countTo(el,to,dur,suf){const t0=performance.now();function st(t){const pr=Math.min(1,(t-t0)/dur);const e=1-Math.pow(1-pr,3);
    el.textContent=Math.round(to*e).toLocaleString('de-DE')+(suf||'');if(pr<1)requestAnimationFrame(st);}requestAnimationFrame(st);}
  function counters(){document.querySelectorAll('.num[data-to]').forEach(el=>countTo(el,+el.dataset.to,1300,el.dataset.suf||''));}
  // ---- Ladesequenz ----
  if(window.gsap && !reduce){
    gsap.timeline({defaults:{ease:'expo.out'}})
      .to('.top',{opacity:1,duration:.6})
      .to('.kpis:first-of-type .kpi',{opacity:1,y:0,duration:.8,stagger:.08,onStart:counters},'-=.2')
      .to('.chart-card',{opacity:1,y:0,duration:.8,onStart:drawLines},'-=.3')
      .to('.kpis:last-of-type .kpi',{opacity:1,y:0,duration:.8,stagger:.08},'-=.5')
      .to('.bs-card',{opacity:1,y:0,duration:.8},'-=.5');
  } else { document.querySelectorAll('.top,.kpi,.chart-card,.bs-card').forEach(el=>el.style.opacity=1); counters(); drawLines(); }
  // ---- Sidebar ----
  function toast(m){const t=document.createElement('div');t.className='toast';t.textContent=m;document.body.appendChild(t);
    requestAnimationFrame(()=>t.classList.add('show'));setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),320);},2200);}
  const sb=g('sb'),sbBack=g('sbBack');
  const openSb=()=>{sb.classList.add('open');sbBack.classList.add('show');};
  const closeSb=()=>{sb.classList.remove('open');sbBack.classList.remove('show');};
  g('sbToggle').addEventListener('click',openSb); sbBack.addEventListener('click',closeSb);
  document.querySelectorAll('.sb-item[data-view]').forEach(it=>{it.addEventListener('click',()=>{
    document.querySelectorAll('.sb-item').forEach(x=>x.classList.remove('active'));it.classList.add('active');
    if(it.dataset.view!=='cockpit') toast(it.textContent.trim()+' — kommt als Nächstes in der Vorschau.');
    closeSb();
  });});
  // ---- 3D-Tilt ----
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
