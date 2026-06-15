<?php /* OH Haustechnik – Studio-Vorschau (NEU). NICHT die Live-Startseite.
   Hero: 3D-Glühbirne (Three.js) die flackert/glimmert und die Seite beleuchtet.
   Weiß/Dunkelblau, Schriften wie Live-Startseite (Montserrat + Inter). */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH Haustechnik · Elektrotechnik, die mitdenkt.</title>
<meta name="description" content="OH Haustechnik – Elektroinstallation, Smart Home, Photovoltaik im Raum Nürnberg & Fürth.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#0a182e; --ink2:#0d2143; --blue:#2E5A8C; --blue-lite:#7ba6e0; --white:#eef3fb; --dim:#9fb2d0;
  --line:rgba(255,255,255,.10); --glow:0.2; --ease:cubic-bezier(.22,1,.36,1);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--ink);color:var(--white);line-height:1.65;-webkit-font-smoothing:antialiased;overflow-x:hidden}
h1,h2,h3,.display{font-family:'Montserrat',sans-serif;font-weight:800;line-height:1.04;letter-spacing:-.01em}
a{color:inherit;text-decoration:none}
::selection{background:var(--blue-lite);color:var(--ink)}
:focus-visible{outline:2px solid var(--blue-lite);outline-offset:3px;border-radius:4px}
.wrap{max-width:1240px;margin:0 auto;padding:0 28px}
.eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--blue-lite)}
.eyebrow::before{content:"";width:30px;height:1px;background:var(--blue-lite)}
.nav{position:fixed;top:0;left:0;right:0;z-index:50;display:flex;align-items:center;justify-content:space-between;padding:22px 28px}
.brand{font-family:'Montserrat';font-weight:800;font-size:20px;letter-spacing:3px;color:#fff}
.brand small{font-family:'Inter';font-size:9px;letter-spacing:2px;font-weight:600;display:block;color:#fff;opacity:.65}
.nav-call{font-weight:600;font-size:14px;color:#fff}

/* ---------- Hero ---------- */
.hero{position:relative;min-height:100svh;display:flex;align-items:center;overflow:hidden}
#scene{position:absolute;inset:0;width:100%;height:100%;display:block;z-index:1}
/* Licht der Glühbirne auf der Seite (flackert mit) */
.page-light{position:absolute;inset:0;z-index:0;pointer-events:none;
  background:radial-gradient(50% 55% at 68% 45%, rgba(180,205,255,calc(var(--glow)*.9)), transparent 60%),
            radial-gradient(40% 40% at 68% 45%, rgba(255,247,222,calc(var(--glow)*.7)), transparent 55%)}
.hero-fallback{position:absolute;inset:0;z-index:1;display:none;
  background:radial-gradient(45% 50% at 66% 42%,rgba(255,247,222,.5),transparent 60%),radial-gradient(60% 60% at 66% 42%,rgba(123,166,224,.25),transparent 65%),var(--ink)}
.hero::after{content:"";position:absolute;inset:0;z-index:2;pointer-events:none;background:linear-gradient(90deg,var(--ink) 0%,rgba(10,24,46,.6) 38%,transparent 72%)}
.hero-in{position:relative;z-index:3;max-width:19ch}
.hero h1{font-size:clamp(42px,8vw,104px);margin:22px 0 24px}
.hero h1 .ln{display:block;overflow:hidden}
.hero h1 .ln span{display:block;transform:translateY(110%)}
.hero h1 .hl{color:var(--blue-lite)}
.hero p{font-size:clamp(16px,1.5vw,20px);color:var(--dim);max-width:46ch;margin-bottom:38px;opacity:0}
.hero .cta-row{display:flex;gap:16px;flex-wrap:wrap;align-items:center;opacity:0}
.btn{position:relative;display:inline-flex;align-items:center;gap:10px;font-family:'Montserrat';font-weight:700;font-size:15px;padding:17px 32px;border-radius:100px;cursor:pointer;border:none;transition:transform .3s var(--ease),box-shadow .3s}
.btn-main{background:#fff;color:var(--ink);box-shadow:0 10px 30px rgba(180,205,255,.18)}
.btn-main:hover{box-shadow:0 14px 40px rgba(180,205,255,.32)}
.btn-ghost{background:transparent;color:var(--white);border:1px solid var(--line)}
.btn-ghost:hover{border-color:var(--blue-lite)}
.scroll-hint{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:3;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:var(--dim);display:flex;flex-direction:column;align-items:center;gap:10px;opacity:0}
.scroll-hint .bar{width:1px;height:46px;background:linear-gradient(var(--blue-lite),transparent);animation:drop 2s var(--ease) infinite}
@keyframes drop{0%{transform:scaleY(0);transform-origin:top}50%{transform:scaleY(1);transform-origin:top}51%{transform-origin:bottom}100%{transform:scaleY(0);transform-origin:bottom}}

/* ---------- Leistungen ---------- */
.sec{padding:clamp(90px,14vh,170px) 0;position:relative;background:linear-gradient(180deg,var(--ink),var(--ink2))}
.sec-head{max-width:60ch;margin-bottom:60px}
.sec-head h2{font-size:clamp(32px,5vw,68px);margin:18px 0 18px}
.sec-head p{color:var(--dim);font-size:18px;max-width:48ch}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.card{position:relative;background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.02));border:1px solid var(--line);border-radius:24px;padding:34px 30px;min-height:300px;display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;transform-style:preserve-3d;transition:border-color .4s,transform .2s var(--ease);backdrop-filter:blur(8px);will-change:transform}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(400px circle at var(--mx,50%) var(--my,0%),rgba(123,166,224,.28),transparent 45%);opacity:0;transition:opacity .4s}
.card:hover{border-color:rgba(123,166,224,.45)}
.card:hover::before{opacity:1}
.card .no{font-family:'Montserrat';font-size:13px;font-weight:700;color:var(--blue-lite);letter-spacing:2px;transform:translateZ(30px)}
.card .ic{width:58px;height:58px;border-radius:16px;display:grid;place-items:center;font-size:26px;margin-top:18px;background:linear-gradient(135deg,var(--blue),#1c3454);border:1px solid var(--line);transform:translateZ(45px)}
.card h3{font-size:24px;margin:20px 0 10px;color:#fff;transform:translateZ(35px)}
.card p{font-size:15px;color:var(--dim);transform:translateZ(20px)}
.card .more{margin-top:18px;font-weight:600;font-size:14px;color:var(--blue-lite);display:inline-flex;gap:8px;transform:translateZ(25px)}
.card.big{grid-column:span 2}
.reveal{opacity:0;transform:translateY(42px)}
.cursor{position:fixed;top:0;left:0;width:9px;height:9px;border-radius:50%;background:#fff;z-index:999;pointer-events:none;transform:translate(-50%,-50%);transition:width .25s,height .25s;mix-blend-mode:difference}
.cursor.big{width:46px;height:46px;background:rgba(255,255,255,.4)}
@media (hover:none),(pointer:coarse){.cursor{display:none}}
@media(max-width:900px){.cards{grid-template-columns:1fr 1fr}.card.big{grid-column:span 2}}
@media(max-width:600px){.cards{grid-template-columns:1fr}.card.big{grid-column:span 1}.nav-call span{display:none}.hero h1{font-size:clamp(38px,12vw,64px)}.hero::after{background:linear-gradient(180deg,rgba(10,24,46,.2),var(--ink) 88%)}}
@media (prefers-reduced-motion:reduce){#scene{display:none}.hero-fallback{display:block}.hero h1 .ln span,.hero p,.hero .cta-row,.scroll-hint{transform:none!important;opacity:1!important}.reveal{opacity:1!important;transform:none!important}.scroll-hint .bar{animation:none}}
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<nav class="nav"><div class="brand">OH<small>HAUSTECHNIK</small></div><a class="nav-call" href="tel:+491757481006"><span>Jetzt anrufen · </span>0175 7481006</a></nav>

<header class="hero">
  <div class="page-light"></div>
  <canvas id="scene"></canvas>
  <div class="hero-fallback"></div>
  <div class="wrap"><div class="hero-in">
    <span class="eyebrow">Elektro &amp; Smart Home · Raum Nürnberg</span>
    <h1><span class="ln"><span>Elektrotechnik,</span></span><span class="ln"><span class="hl">die mitdenkt.</span></span></h1>
    <p>Wir installieren, vernetzen und sanieren mit Präzision — von der Steckdose bis zum vollvernetzten Smart Home. Sauber, termintreu, auf dem neuesten Stand.</p>
    <div class="cta-row"><button class="btn btn-main" data-magnet>Projekt anfragen →</button><a class="btn btn-ghost" href="#leistungen" data-magnet>Leistungen ansehen</a></div>
  </div></div>
  <div class="scroll-hint">Scrollen<div class="bar"></div></div>
</header>

<section class="sec" id="leistungen"><div class="wrap">
  <div class="sec-head"><span class="eyebrow reveal">Was wir können</span>
    <h2 class="reveal">Alles aus einer Hand —<br>vom Anschluss bis zur Automation.</h2>
    <p class="reveal">Fünf Kompetenzfelder, ein Anspruch: technisch sauber, durchdacht und für Jahre gemacht.</p></div>
  <div class="cards">
    <article class="card reveal big" data-tilt><div><div class="no">01</div><div class="ic">⚡</div><h3>Elektroinstallation</h3><p>Neubau, Altbau, Erweiterung. Leitungen, Verteilung, FI-Schutz, Messprotokolle — normgerecht und zukunftssicher.</p></div><span class="more">Mehr erfahren →</span></article>
    <article class="card reveal" data-tilt><div><div class="no">02</div><div class="ic">🏠</div><h3>Smart Home / Loxone</h3><p>Licht, Heizung, Beschattung & Sicherheit — intelligent vernetzt und intuitiv steuerbar.</p></div><span class="more">Mehr erfahren →</span></article>
    <article class="card reveal" data-tilt><div><div class="no">03</div><div class="ic">☀️</div><h3>Photovoltaik</h3><p>Eigenen Strom erzeugen, speichern, laden. Anlage, Speicher und Wallbox aus einer Hand.</p></div><span class="more">Mehr erfahren →</span></article>
    <article class="card reveal" data-tilt><div><div class="no">04</div><div class="ic">🔧</div><h3>Sanierung</h3><p>Komplette Elektro-Erneuerung für Wohnung & Haus — zum klaren Festpreis, sauber umgesetzt.</p></div><span class="more">Mehr erfahren →</span></article>
    <article class="card reveal" data-tilt><div><div class="no">05</div><div class="ic">🔔</div><h3>Sprechanlagen</h3><p>Video-Türsprechanlagen & Zutritt — modern, sicher, app-fähig.</p></div><span class="more">Mehr erfahren →</span></article>
  </div>
</div></section>

<script type="importmap">{ "imports": {
  "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
  "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
}}</script>
<script type="module">
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const canvas = document.getElementById('scene');
if (!reduce && canvas){ try {
  const THREE = await import('three');
  const { RoomEnvironment } = await import('three/addons/environments/RoomEnvironment.js');
  const { EffectComposer } = await import('three/addons/postprocessing/EffectComposer.js');
  const { RenderPass } = await import('three/addons/postprocessing/RenderPass.js');
  const { UnrealBloomPass } = await import('three/addons/postprocessing/UnrealBloomPass.js');
  const { OutputPass } = await import('three/addons/postprocessing/OutputPass.js');
  const mobile = innerWidth < 760;
  const renderer = new THREE.WebGLRenderer({canvas, alpha:true, antialias:true, powerPreference:'high-performance'});
  renderer.setPixelRatio(Math.min(devicePixelRatio, mobile?1.5:2));
  renderer.toneMapping = THREE.ACESFilmicToneMapping; renderer.toneMappingExposure = 1.15;
  const scene = new THREE.Scene();
  const cam = new THREE.PerspectiveCamera(42, 1, .1, 100); cam.position.set(0,0,7.4);
  // Echte Umgebung -> realistische Reflexionen/Brechung auf Glas & Metall
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), .04).texture;
  const bulb = new THREE.Group(); bulb.position.x = mobile?0:1.5; scene.add(bulb);

  // --- Glas-Kolben (echtes Glas: Transmission + IOR + Environment) ---
  const prof = [[0.02,1.72],[0.46,1.66],[0.9,1.36],[1.16,0.86],[1.27,0.26],[1.18,-0.3],[0.8,-0.72],[0.5,-0.96],[0.44,-1.16]]
    .map(p=>new THREE.Vector2(p[0],p[1]));
  const glassMat = new THREE.MeshPhysicalMaterial({color:0xffffff,metalness:0,roughness:.05,transmission:1,ior:1.46,
    thickness:.45,transparent:true,opacity:1,envMapIntensity:1.35,emissive:0xffc24d,emissiveIntensity:0,side:THREE.DoubleSide});
  bulb.add(new THREE.Mesh(new THREE.LatheGeometry(prof, 96), glassMat));

  // --- Glühwendel (echte gewickelte Wolfram-Spirale) ---
  const fp=[]; const turns=20, fl=0.92, fr=0.05;
  for(let i=0;i<=turns*12;i++){const u=i/(turns*12),a=u*turns*Math.PI*2;fp.push(new THREE.Vector3(-fl/2+u*fl,0.33+Math.sin(a)*fr,Math.cos(a)*fr));}
  const filGeo = new THREE.TubeGeometry(new THREE.CatmullRomCurve3(fp),280,0.014,8,false);
  const filMat = new THREE.MeshStandardMaterial({color:0xffb14d,emissive:0xff9320,emissiveIntensity:2.6,roughness:.5,metalness:.5});
  const fil = new THREE.Mesh(filGeo, filMat); bulb.add(fil);
  // Halte-Drähte + Glas-Stiel innen
  const wireMat = new THREE.MeshStandardMaterial({color:0x767d8c,metalness:1,roughness:.4,envMapIntensity:1});
  [-fl/2,fl/2].forEach(x=>{const w=new THREE.Mesh(new THREE.CylinderGeometry(0.013,0.013,0.72,8),wireMat);w.position.set(x,-0.03,0);bulb.add(w);});
  const stem=new THREE.Mesh(new THREE.CylinderGeometry(0.05,0.16,0.62,20),new THREE.MeshPhysicalMaterial({color:0xffffff,transmission:.85,roughness:.18,ior:1.46,transparent:true,opacity:1,envMapIntensity:1}));
  stem.position.y=-0.6; bulb.add(stem);

  // --- Edison-Sockel (Metallgewinde) ---
  const metalMat=new THREE.MeshStandardMaterial({color:0xbfc5d0,metalness:1,roughness:.3,envMapIntensity:1.4});
  const base=new THREE.Mesh(new THREE.CylinderGeometry(0.42,0.48,0.66,48),metalMat); base.position.y=-1.5; bulb.add(base);
  for(let i=0;i<5;i++){const r=new THREE.Mesh(new THREE.TorusGeometry(0.45,0.035,16,48),metalMat);r.rotation.x=Math.PI/2;r.position.y=-1.18-i*0.155;bulb.add(r);}
  const ins=new THREE.Mesh(new THREE.CylinderGeometry(0.24,0.34,0.2,32),new THREE.MeshStandardMaterial({color:0x10141c,roughness:.6})); ins.position.y=-1.95; bulb.add(ins);
  const tip=new THREE.Mesh(new THREE.CylinderGeometry(0.13,0.13,0.09,24),metalMat); tip.position.y=-2.07; bulb.add(tip);

  // weicher Schein hinter der Birne
  function glowTex(){const c=document.createElement('canvas');c.width=c.height=256;const x=c.getContext('2d');const g=x.createRadialGradient(128,128,0,128,128,128);g.addColorStop(0,'rgba(255,240,205,.9)');g.addColorStop(.3,'rgba(200,220,255,.4)');g.addColorStop(1,'rgba(200,220,255,0)');x.fillStyle=g;x.fillRect(0,0,256,256);return new THREE.CanvasTexture(c);}
  const glow=new THREE.Sprite(new THREE.SpriteMaterial({map:glowTex(),transparent:true,blending:THREE.AdditiveBlending,depthWrite:false})); glow.scale.set(6,6,1); glow.position.y=.33; bulb.add(glow);

  const light=new THREE.PointLight(0xfff0c4,2,60); light.position.set(0,.33,0); bulb.add(light);
  scene.add(new THREE.AmbientLight(0x2a4a78,.5));
  const key=new THREE.DirectionalLight(0xbcd4ff,.35); key.position.set(-4,3,5); scene.add(key);

  // --- Bloom (lässt den Glühwendel echt leuchten) ---
  const composer=new EffectComposer(renderer); composer.addPass(new RenderPass(scene,cam));
  const bloom=new UnrealBloomPass(new THREE.Vector2(1,1), mobile?.7:.95, .55, .15); composer.addPass(bloom);
  composer.addPass(new OutputPass());

  let mx=0,my=0; addEventListener('pointermove',e=>{mx=e.clientX/innerWidth-.5;my=e.clientY/innerHeight-.5;});
  function resize(){const w=canvas.clientWidth,h=canvas.clientHeight;renderer.setSize(w,h,false);composer.setSize(w,h);cam.aspect=w/h;cam.updateProjectionMatrix();}
  addEventListener('resize',resize); resize();
  const root=document.documentElement; let run=true,f=1,t=0; document.addEventListener('visibilitychange',()=>run=!document.hidden);
  function tick(){requestAnimationFrame(tick); if(!run)return; t+=.016;
    // Flackern: STÄRKER im Licht, gleiche Geschwindigkeit (Frequenzen unverändert)
    let g=.74+Math.sin(t*7)*.08+Math.sin(t*23)*.06+(Math.random()-.5)*.07;
    if(Math.random()<.013)g*=.05;            // tiefe Aussetzer (fast aus)
    if(Math.random()<.008)g=Math.min(1.6,g*2.0); // helle Blitze
    f+=(g-f)*.5;
    filMat.emissiveIntensity=.4+f*4.2; filMat.color.setRGB(1,.78+f*.18,.45+f*.3);
    glassMat.emissiveIntensity=f*.22; light.intensity=f*4.6; glow.material.opacity=.16+f*.7; bloom.strength=(mobile?.5:.7)+f*.8;
    root.style.setProperty('--glow',(0.1+f*.5).toFixed(3));
    bulb.rotation.y=mx*.5+t*.05; bulb.rotation.x=my*.22; bulb.position.y=(mobile?0:Math.sin(t*.8)*.07);
    composer.render();
  }
  tick();
} catch(e){ document.querySelector('.hero-fallback').style.display='block'; canvas.style.display='none'; document.documentElement.style.setProperty('--glow','.3'); } }
</script>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<script>
(function(){
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(!reduce && window.Lenis){const l=new Lenis({duration:1.1,easing:t=>Math.min(1,1.001-Math.pow(2,-10*t))});function raf(t){l.raf(t);requestAnimationFrame(raf);}requestAnimationFrame(raf);l.on('scroll',()=>window.ScrollTrigger&&ScrollTrigger.update());}
  if(window.gsap){gsap.registerPlugin(ScrollTrigger);
    if(!reduce){const tl=gsap.timeline({delay:.3,defaults:{ease:'expo.out'}});
      tl.to('.hero h1 .ln span',{y:0,duration:1.1,stagger:.12}).to('.hero p',{opacity:1,y:0,duration:.9},'-=.6').to('.hero .cta-row',{opacity:1,y:0,duration:.9},'-=.7').to('.scroll-hint',{opacity:1,duration:.8},'-=.5');
      gsap.utils.toArray('.reveal').forEach(el=>gsap.to(el,{opacity:1,y:0,duration:1,ease:'expo.out',scrollTrigger:{trigger:el,start:'top 86%'}}));
      gsap.to('.card.reveal',{opacity:1,y:0,duration:1,ease:'expo.out',stagger:.09,scrollTrigger:{trigger:'.cards',start:'top 82%'}});
    }}
  if(matchMedia('(hover:hover)').matches){
    document.querySelectorAll('[data-tilt]').forEach(c=>{c.addEventListener('pointermove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width,y=(e.clientY-r.top)/r.height;c.style.transform=`perspective(900px) rotateY(${(x-.5)*9}deg) rotateX(${(.5-y)*9}deg) translateY(-4px)`;c.style.setProperty('--mx',x*100+'%');c.style.setProperty('--my',y*100+'%');});c.addEventListener('pointerleave',()=>c.style.transform='');});
    document.querySelectorAll('[data-magnet]').forEach(b=>{b.addEventListener('pointermove',e=>{const r=b.getBoundingClientRect();b.style.transform=`translate(${(e.clientX-r.left-r.width/2)*.25}px,${(e.clientY-r.top-r.height/2)*.35}px)`;});b.addEventListener('pointerleave',()=>b.style.transform='');});
    const cur=document.getElementById('cursor');let cx=0,cy=0,tx=0,ty=0;addEventListener('pointermove',e=>{tx=e.clientX;ty=e.clientY;});(function lp(){cx+=(tx-cx)*.2;cy+=(ty-cy)*.2;cur.style.left=cx+'px';cur.style.top=cy+'px';requestAnimationFrame(lp);})();
    document.querySelectorAll('a,button,[data-tilt]').forEach(el=>{el.addEventListener('pointerenter',()=>cur.classList.add('big'));el.addEventListener('pointerleave',()=>cur.classList.remove('big'));});
  }
})();
</script>
</body>
</html>
