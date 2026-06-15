<?php /* OH Haustechnik – Studio-Vorschau (NEU, Agentur-Niveau). NICHT die Live-Startseite.
   Runde 1: Hero mit Three.js-Energie-Szene + Leistungen. Live-index.php bleibt unberührt. */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH Haustechnik · Elektrotechnik, die mitdenkt.</title>
<meta name="description" content="OH Haustechnik – Elektroinstallation, Smart Home, Photovoltaik im Raum Nürnberg & Fürth. Präzise, modern, zuverlässig.">
<link rel="preconnect" href="https://api.fontshare.com" crossorigin>
<link href="https://api.fontshare.com/v2/css?f[]=clash-display@600,700,800&f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#1A1A2E; --ink-2:#14152a; --navy:#12132a; --blue:#2E5A8C; --blue-lite:#5b86c4;
  --yellow:#FFD400; --white:#EAEAF2; --dim:#9aa3bd; --line:rgba(255,255,255,.10);
  --ease:cubic-bezier(.22,1,.36,1);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:auto}
body{font-family:'Satoshi',-apple-system,sans-serif;background:var(--ink);color:var(--white);line-height:1.6;
  -webkit-font-smoothing:antialiased;overflow-x:hidden}
body.lenis,html.lenis{height:auto}
.lenis.lenis-smooth{scroll-behavior:auto!important}
h1,h2,h3,.display{font-family:'Clash Display',sans-serif;font-weight:700;line-height:.98;letter-spacing:-.02em}
a{color:inherit;text-decoration:none}
::selection{background:var(--yellow);color:var(--ink)}
:focus-visible{outline:2px solid var(--yellow);outline-offset:3px;border-radius:4px}
.wrap{max-width:1240px;margin:0 auto;padding:0 28px}
.eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:12px;font-weight:700;letter-spacing:3px;
  text-transform:uppercase;color:var(--yellow)}
.eyebrow::before{content:"";width:30px;height:1px;background:var(--yellow)}

/* ---------- Nav ---------- */
.nav{position:fixed;top:0;left:0;right:0;z-index:50;display:flex;align-items:center;justify-content:space-between;
  padding:22px 28px;mix-blend-mode:difference}
.brand{font-family:'Clash Display';font-weight:800;font-size:20px;letter-spacing:3px;color:#fff}
.brand small{font-family:'Satoshi';font-size:9px;letter-spacing:2px;font-weight:600;display:block;color:#fff;opacity:.7}
.nav-call{font-weight:600;font-size:14px;color:#fff}

/* ---------- Hero ---------- */
.hero{position:relative;min-height:100svh;display:flex;align-items:center;overflow:hidden}
#scene{position:absolute;inset:0;width:100%;height:100%;display:block}
.hero-fallback{position:absolute;inset:0;background:
  radial-gradient(60% 60% at 75% 35%,rgba(46,90,140,.45),transparent 60%),
  radial-gradient(40% 50% at 20% 80%,rgba(255,212,0,.10),transparent 60%),var(--ink);display:none}
.hero::after{content:"";position:absolute;inset:0;pointer-events:none;
  background:linear-gradient(90deg,var(--ink) 0%,rgba(26,26,46,.55) 35%,transparent 70%)}
.hero-in{position:relative;z-index:3;max-width:18ch}
.hero h1{font-size:clamp(44px,8.5vw,118px);font-weight:800;margin:22px 0 24px}
.hero h1 .ln{display:block;overflow:hidden}
.hero h1 .ln span{display:block;transform:translateY(110%)}
.hero h1 .am{color:var(--yellow)}
.hero p{font-size:clamp(16px,1.5vw,20px);color:var(--dim);max-width:46ch;margin-bottom:38px;opacity:0}
.hero .cta-row{display:flex;gap:16px;flex-wrap:wrap;align-items:center;opacity:0}
.btn{position:relative;display:inline-flex;align-items:center;gap:10px;font-family:'Clash Display';font-weight:600;
  font-size:16px;padding:18px 34px;border-radius:100px;cursor:pointer;border:none;transition:transform .3s var(--ease)}
.btn-y{background:var(--yellow);color:var(--ink)}
.btn-y::after{content:"";position:absolute;inset:0;border-radius:100px;box-shadow:0 0 0 0 rgba(255,212,0,.5);animation:ring 3s ease-in-out infinite}
@keyframes ring{0%,100%{box-shadow:0 0 0 0 rgba(255,212,0,.45)}50%{box-shadow:0 0 34px 4px rgba(255,212,0,.0)}}
.btn-ghost{background:transparent;color:var(--white);border:1px solid var(--line)}
.btn-ghost:hover{border-color:var(--yellow)}
.scroll-hint{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:3;font-size:11px;letter-spacing:2px;
  text-transform:uppercase;color:var(--dim);display:flex;flex-direction:column;align-items:center;gap:10px;opacity:0}
.scroll-hint .bar{width:1px;height:46px;background:linear-gradient(var(--yellow),transparent);animation:drop 2s var(--ease) infinite}
@keyframes drop{0%{transform:scaleY(0);transform-origin:top}50%{transform:scaleY(1);transform-origin:top}51%{transform-origin:bottom}100%{transform:scaleY(0);transform-origin:bottom}}

/* ---------- Leistungen ---------- */
.sec{padding:clamp(90px,14vh,170px) 0;position:relative}
.sec-head{max-width:60ch;margin-bottom:64px}
.sec-head h2{font-size:clamp(34px,5.5vw,76px);font-weight:700;margin:18px 0 18px}
.sec-head p{color:var(--dim);font-size:18px;max-width:48ch}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.card{position:relative;background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));
  border:1px solid var(--line);border-radius:24px;padding:34px 30px;min-height:300px;display:flex;flex-direction:column;
  justify-content:space-between;overflow:hidden;transform-style:preserve-3d;transition:border-color .4s,transform .2s var(--ease);
  backdrop-filter:blur(8px);will-change:transform}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(400px circle at var(--mx,50%) var(--my,0%),
  rgba(46,90,140,.30),transparent 45%);opacity:0;transition:opacity .4s}
.card:hover{border-color:rgba(255,212,0,.4)}
.card:hover::before{opacity:1}
.card .no{font-family:'Clash Display';font-size:13px;font-weight:600;color:var(--yellow);letter-spacing:2px;transform:translateZ(30px)}
.card .ic{width:58px;height:58px;border-radius:16px;display:grid;place-items:center;font-size:26px;margin:18px 0 0;
  background:linear-gradient(135deg,var(--blue),#1c3454);border:1px solid var(--line);transform:translateZ(45px)}
.card h3{font-size:25px;margin:20px 0 10px;color:#fff;transform:translateZ(35px)}
.card p{font-size:15px;color:var(--dim);transform:translateZ(20px)}
.card .more{margin-top:18px;font-weight:600;font-size:14px;color:var(--yellow);display:inline-flex;gap:8px;transform:translateZ(25px)}
.card.big{grid-column:span 2}

/* reveal */
.reveal{opacity:0;transform:translateY(42px)}

/* ---------- Cursor ---------- */
.cursor{position:fixed;top:0;left:0;width:9px;height:9px;border-radius:50%;background:var(--yellow);z-index:999;
  pointer-events:none;transform:translate(-50%,-50%);transition:width .25s,height .25s,background .25s;mix-blend-mode:difference}
.cursor.big{width:46px;height:46px;background:rgba(255,212,0,.25)}
@media (hover:none),(pointer:coarse){.cursor{display:none}}

@media(max-width:900px){
  .cards{grid-template-columns:1fr 1fr}.card.big{grid-column:span 2}
  .nav{mix-blend-mode:normal}
}
@media(max-width:600px){
  .cards{grid-template-columns:1fr}.card.big{grid-column:span 1}
  .nav-call span{display:none}
  .hero h1{font-size:clamp(40px,13vw,70px)}
}
@media (prefers-reduced-motion:reduce){
  #scene{display:none}.hero-fallback{display:block}
  .hero h1 .ln span,.hero p,.hero .cta-row,.scroll-hint{transform:none!important;opacity:1!important}
  .reveal{opacity:1!important;transform:none!important}
  .btn-y::after,.scroll-hint .bar{animation:none}
}
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>

<nav class="nav">
  <div class="brand">OH<small>HAUSTECHNIK</small></div>
  <a class="nav-call" href="tel:+491757481006"><span>Jetzt anrufen · </span>0175 7481006</a>
</nav>

<header class="hero">
  <canvas id="scene"></canvas>
  <div class="hero-fallback"></div>
  <div class="wrap">
    <div class="hero-in">
      <span class="eyebrow">Elektro &amp; Smart Home · Raum Nürnberg</span>
      <h1>
        <span class="ln"><span>Elektrotechnik,</span></span>
        <span class="ln"><span class="am">die mitdenkt.</span></span>
      </h1>
      <p>Wir installieren, vernetzen und sanieren mit Präzision — von der Steckdose bis zum vollvernetzten Smart Home. Sauber, termintreu, auf dem neuesten Stand.</p>
      <div class="cta-row">
        <button class="btn btn-y" data-magnet>Projekt anfragen →</button>
        <a class="btn btn-ghost" href="#leistungen" data-magnet>Leistungen ansehen</a>
      </div>
    </div>
  </div>
  <div class="scroll-hint">Scrollen<div class="bar"></div></div>
</header>

<section class="sec" id="leistungen">
  <div class="wrap">
    <div class="sec-head">
      <span class="eyebrow reveal">Was wir können</span>
      <h2 class="reveal">Alles aus einer Hand —<br>vom Anschluss bis zur Automation.</h2>
      <p class="reveal">Fünf Kompetenzfelder, ein Anspruch: technisch sauber, durchdacht und für Jahre gemacht.</p>
    </div>
    <div class="cards">
      <article class="card reveal big" data-tilt>
        <div><div class="no">01</div><div class="ic">⚡</div><h3>Elektroinstallation</h3>
          <p>Neubau, Altbau, Erweiterung. Leitungen, Verteilung, FI-Schutz, Mess­protokolle — normgerecht und zukunftssicher.</p></div>
        <span class="more">Mehr erfahren →</span>
      </article>
      <article class="card reveal" data-tilt>
        <div><div class="no">02</div><div class="ic">🏠</div><h3>Smart Home / Loxone</h3>
          <p>Licht, Heizung, Beschattung & Sicherheit — intelligent vernetzt und intuitiv steuerbar.</p></div>
        <span class="more">Mehr erfahren →</span>
      </article>
      <article class="card reveal" data-tilt>
        <div><div class="no">03</div><div class="ic">☀️</div><h3>Photovoltaik</h3>
          <p>Eigenen Strom erzeugen, speichern, laden. Anlage, Speicher und Wallbox aus einer Hand.</p></div>
        <span class="more">Mehr erfahren →</span>
      </article>
      <article class="card reveal" data-tilt>
        <div><div class="no">04</div><div class="ic">🔧</div><h3>Sanierung</h3>
          <p>Komplette Elektro-Erneuerung für Wohnung & Haus — zum klaren Festpreis, sauber umgesetzt.</p></div>
        <span class="more">Mehr erfahren →</span>
      </article>
      <article class="card reveal" data-tilt>
        <div><div class="no">05</div><div class="ic">🔔</div><h3>Sprechanlagen</h3>
          <p>Video-Türsprechanlagen & Zutritt — modern, sicher, app-fähig.</p></div>
        <span class="more">Mehr erfahren →</span>
      </article>
    </div>
  </div>
</section>

<!-- 3D Hero – Three.js Energie-Netzwerk -->
<script type="importmap">
{ "imports": { "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js" } }
</script>
<script type="module">
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const canvas = document.getElementById('scene');
if (!reduce && canvas) {
  try {
    const THREE = await import('three');
    const isMobile = innerWidth < 760;
    const renderer = new THREE.WebGLRenderer({canvas, alpha:true, antialias:!isMobile, powerPreference:'high-performance'});
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    const scene = new THREE.Scene();
    const cam = new THREE.PerspectiveCamera(60, 1, .1, 100); cam.position.z = 26;
    const group = new THREE.Group(); scene.add(group);

    // Knoten (Energie-Netz)
    const N = isMobile ? 70 : 150;
    const nodes = [];
    for (let i=0;i<N;i++){
      nodes.push(new THREE.Vector3((Math.random()-.5)*46,(Math.random()-.5)*30,(Math.random()-.5)*22));
    }
    // Punkte
    const pPos = new Float32Array(N*3);
    nodes.forEach((v,i)=>{pPos[i*3]=v.x;pPos[i*3+1]=v.y;pPos[i*3+2]=v.z;});
    const pGeo = new THREE.BufferGeometry(); pGeo.setAttribute('position', new THREE.BufferAttribute(pPos,3));
    const pMat = new THREE.PointsMaterial({color:0x5b86c4,size:.5,transparent:true,opacity:.9,blending:THREE.AdditiveBlending,depthWrite:false});
    group.add(new THREE.Points(pGeo,pMat));

    // Verbindungen (Leiterbahnen)
    const segs=[]; const pairs=[];
    for (let i=0;i<N;i++) for (let j=i+1;j<N;j++){
      const d = nodes[i].distanceTo(nodes[j]);
      if (d < 7){ segs.push(nodes[i],nodes[j]); pairs.push([i,j]); }
    }
    const lGeo = new THREE.BufferGeometry().setFromPoints(segs);
    const lMat = new THREE.LineBasicMaterial({color:0x2E5A8C,transparent:true,opacity:.22,blending:THREE.AdditiveBlending,depthWrite:false});
    group.add(new THREE.LineSegments(lGeo,lMat));

    // Gelbe Energie-Impulse, die durch die Bahnen wandern
    const PN = isMobile?5:9;
    const pulseGeo = new THREE.BufferGeometry(); const pulsePos = new Float32Array(PN*3);
    pulseGeo.setAttribute('position', new THREE.BufferAttribute(pulsePos,3));
    const pulseMat = new THREE.PointsMaterial({color:0xFFD400,size:1.5,transparent:true,opacity:1,blending:THREE.AdditiveBlending,depthWrite:false});
    group.add(new THREE.Points(pulseGeo,pulseMat));
    const pulses = Array.from({length:PN},()=>({p:pairs[Math.floor(Math.random()*pairs.length)],t:Math.random(),s:.0025+Math.random()*.004}));

    let mx=0,my=0; addEventListener('pointermove',e=>{mx=(e.clientX/innerWidth-.5);my=(e.clientY/innerHeight-.5);});
    function resize(){const w=canvas.clientWidth,h=canvas.clientHeight;renderer.setSize(w,h,false);cam.aspect=w/h;cam.updateProjectionMatrix();}
    addEventListener('resize',resize); resize();

    let run=true; document.addEventListener('visibilitychange',()=>{run=!document.hidden;});
    function tick(){
      requestAnimationFrame(tick); if(!run) return;
      group.rotation.y += .0009; group.rotation.x += .0004;
      group.rotation.y += (mx*.4 - group.rotation.y%.0001);
      cam.position.x += (mx*5 - cam.position.x)*.04; cam.position.y += (-my*4 - cam.position.y)*.04; cam.lookAt(0,0,0);
      pulses.forEach((pu,i)=>{ pu.t+=pu.s; if(pu.t>=1){pu.t=0;pu.p=pairs[Math.floor(Math.random()*pairs.length)];}
        const a=nodes[pu.p[0]],b=nodes[pu.p[1]];
        pulsePos[i*3]=a.x+(b.x-a.x)*pu.t; pulsePos[i*3+1]=a.y+(b.y-a.y)*pu.t; pulsePos[i*3+2]=a.z+(b.z-a.z)*pu.t; });
      pulseGeo.attributes.position.needsUpdate=true;
      renderer.render(scene,cam);
    }
    tick();
  } catch(e){ document.querySelector('.hero-fallback').style.display='block'; canvas.style.display='none'; }
}
</script>

<!-- GSAP + ScrollTrigger + Lenis + Interaktionen -->
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<script>
(function(){
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  // Smooth Scroll
  if (!reduce && window.Lenis){
    const lenis = new Lenis({duration:1.1,easing:t=>Math.min(1,1.001-Math.pow(2,-10*t))});
    function raf(t){lenis.raf(t);requestAnimationFrame(raf);} requestAnimationFrame(raf);
    if(window.gsap&&gsap.ticker){lenis.on('scroll',()=>window.ScrollTrigger&&ScrollTrigger.update());}
  }
  if (window.gsap){
    gsap.registerPlugin(ScrollTrigger);
    if(!reduce){
      // Hero-Einstieg
      const tl = gsap.timeline({delay:.25,defaults:{ease:'expo.out'}});
      tl.to('.hero h1 .ln span',{y:0,duration:1.1,stagger:.12})
        .to('.hero p',{opacity:1,y:0,duration:.9},'-=.6')
        .to('.hero .cta-row',{opacity:1,y:0,duration:.9},'-=.7')
        .to('.scroll-hint',{opacity:1,duration:.8},'-=.5');
      // Scroll-Reveals
      gsap.utils.toArray('.reveal').forEach((el,i)=>{
        gsap.to(el,{opacity:1,y:0,duration:1,ease:'expo.out',
          scrollTrigger:{trigger:el,start:'top 86%'}});
      });
      // Karten gestaffelt
      gsap.to('.card.reveal',{opacity:1,y:0,duration:1,ease:'expo.out',stagger:.09,
        scrollTrigger:{trigger:'.cards',start:'top 82%'}});
    }
  }
  // 3D-Tilt auf Karten (Desktop)
  if (matchMedia('(hover:hover)').matches){
    document.querySelectorAll('[data-tilt]').forEach(c=>{
      c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;
        c.style.transform=`perspective(900px) rotateY(${(x-.5)*9}deg) rotateX(${(.5-y)*9}deg) translateY(-4px)`;
        c.style.setProperty('--mx',x*100+'%');c.style.setProperty('--my',y*100+'%');});
      c.addEventListener('pointerleave',()=>{c.style.transform='';});
    });
    // Magnetische Buttons
    document.querySelectorAll('[data-magnet]').forEach(b=>{
      b.addEventListener('pointermove',e=>{const r=b.getBoundingClientRect();
        b.style.transform=`translate(${(e.clientX-r.left-r.width/2)*.25}px,${(e.clientY-r.top-r.height/2)*.35}px)`;});
      b.addEventListener('pointerleave',()=>{b.style.transform='';});
    });
    // Eigener Cursor
    const cur=document.getElementById('cursor'); let cx=0,cy=0,tx=0,ty=0;
    addEventListener('pointermove',e=>{tx=e.clientX;ty=e.clientY;});
    (function loop(){cx+=(tx-cx)*.2;cy+=(ty-cy)*.2;cur.style.left=cx+'px';cur.style.top=cy+'px';requestAnimationFrame(loop);})();
    document.querySelectorAll('a,button,[data-tilt]').forEach(el=>{
      el.addEventListener('pointerenter',()=>cur.classList.add('big'));
      el.addEventListener('pointerleave',()=>cur.classList.remove('big'));
    });
  }
})();
</script>
</body>
</html>
