<?php /* OH Büro – Design-Labor (Vorschau). Zeigt aktuell den neuen 3D-Glühbirnen-Hero. */ ?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>OH · Design-Labor (3D-Glühbirne)</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{--ink:#0a182e;--blue-lite:#7ba6e0;--white:#eef3fb;--dim:#9fb2d0;--glow:.2}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--ink);color:var(--white);min-height:100svh;overflow-x:hidden}
h1{font-family:'Montserrat',sans-serif;font-weight:800;letter-spacing:-.01em}
.hero{position:relative;min-height:100svh;display:flex;align-items:center;justify-content:center;text-align:center;overflow:hidden}
#scene{position:absolute;inset:0;width:100%;height:100%;z-index:1}
.page-light{position:absolute;inset:0;z-index:0;pointer-events:none;background:radial-gradient(50% 55% at 50% 45%,rgba(180,205,255,calc(var(--glow)*.9)),transparent 60%),radial-gradient(40% 40% at 50% 45%,rgba(255,247,222,calc(var(--glow)*.7)),transparent 55%)}
.hero-fallback{position:absolute;inset:0;z-index:1;display:none;background:radial-gradient(45% 50% at 50% 42%,rgba(255,247,222,.5),transparent 60%),var(--ink)}
.hero-in{position:relative;z-index:3;padding:24px}
.tag{display:inline-block;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--blue-lite);background:rgba(123,166,224,.12);padding:6px 14px;border-radius:999px;margin-bottom:18px}
.hero h1{font-size:clamp(34px,7vw,72px);margin-bottom:14px}
.hero h1 .hl{color:var(--blue-lite)}
.hero p{color:var(--dim);max-width:44ch;margin:0 auto}
.note{position:fixed;left:0;right:0;bottom:0;z-index:5;text-align:center;font-size:13px;color:var(--dim);padding:14px;background:linear-gradient(transparent,rgba(10,24,46,.9))}
@media (prefers-reduced-motion:reduce){#scene{display:none}.hero-fallback{display:block}}
</style>
</head>
<body>
<header class="hero">
  <div class="page-light"></div>
  <canvas id="scene"></canvas>
  <div class="hero-fallback"></div>
  <div class="hero-in">
    <span class="tag">Design-Labor · Vorschau</span>
    <h1>Elektrotechnik,<br><span class="hl">die mitdenkt.</span></h1>
    <p>3D-Glühbirne, die flackert &amp; glimmert und die Seite beleuchtet. Gefällt dir der Look?</p>
  </div>
  <div class="note">Nur Vorschau. Gefällt's → übernehme ich's. Gefällt's nicht → sofort raus.</div>
</header>
<script type="importmap">{ "imports": { "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js" } }</script>
<script type="module">
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const canvas = document.getElementById('scene');
if (!reduce && canvas){ try {
  const THREE = await import('three');
  const renderer = new THREE.WebGLRenderer({canvas, alpha:true, antialias:true, powerPreference:'high-performance'});
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  const scene = new THREE.Scene();
  const cam = new THREE.PerspectiveCamera(45, 1, .1, 100); cam.position.set(0,0,7);
  const bulb = new THREE.Group(); scene.add(bulb);
  const prof = [[0.02,1.7],[0.5,1.62],[0.92,1.32],[1.18,0.85],[1.27,0.25],[1.18,-0.3],[0.82,-0.7],[0.52,-0.95],[0.44,-1.15]].map(p=>new THREE.Vector2(p[0],p[1]));
  const glassMat = new THREE.MeshPhysicalMaterial({color:0x9fc0ff,roughness:.08,metalness:0,transparent:true,opacity:.16,transmission:.5,thickness:.6,emissive:0xfff4d6,emissiveIntensity:0,clearcoat:1,side:THREE.DoubleSide});
  bulb.add(new THREE.Mesh(new THREE.LatheGeometry(prof,80), glassMat));
  const fil = new THREE.Mesh(new THREE.TorusKnotGeometry(0.24,0.035,90,14,2,3), new THREE.MeshBasicMaterial({color:0xfff0c4})); fil.position.y=.35; bulb.add(fil);
  const core = new THREE.Mesh(new THREE.SphereGeometry(0.5,32,32), new THREE.MeshBasicMaterial({color:0xfff6e0,transparent:true,opacity:.5,blending:THREE.AdditiveBlending})); core.position.y=.35; bulb.add(core);
  const base = new THREE.Mesh(new THREE.CylinderGeometry(0.44,0.5,0.8,40), new THREE.MeshStandardMaterial({color:0x9aa6bd,metalness:.95,roughness:.35})); base.position.y=-1.55; bulb.add(base);
  for(let i=0;i<4;i++){const r=new THREE.Mesh(new THREE.TorusGeometry(0.46,0.04,12,40),new THREE.MeshStandardMaterial({color:0x808ca6,metalness:1,roughness:.4}));r.rotation.x=Math.PI/2;r.position.y=-1.25-i*0.18;bulb.add(r);}
  function glowTex(){const c=document.createElement('canvas');c.width=c.height=256;const x=c.getContext('2d');const g=x.createRadialGradient(128,128,0,128,128,128);g.addColorStop(0,'rgba(255,245,220,1)');g.addColorStop(.25,'rgba(190,215,255,.55)');g.addColorStop(1,'rgba(190,215,255,0)');x.fillStyle=g;x.fillRect(0,0,256,256);return new THREE.CanvasTexture(c);}
  const glow = new THREE.Sprite(new THREE.SpriteMaterial({map:glowTex(),transparent:true,blending:THREE.AdditiveBlending,depthWrite:false})); glow.scale.set(7,7,1); glow.position.y=.35; bulb.add(glow);
  const light = new THREE.PointLight(0xfff2cc,2,60); light.position.set(0,.4,0); bulb.add(light);
  scene.add(new THREE.AmbientLight(0x24406e,.9));
  const key=new THREE.DirectionalLight(0xbcd4ff,.5); key.position.set(-3,2,4); scene.add(key);
  let mx=0,my=0; addEventListener('pointermove',e=>{mx=e.clientX/innerWidth-.5;my=e.clientY/innerHeight-.5;});
  function resize(){const w=canvas.clientWidth,h=canvas.clientHeight;renderer.setSize(w,h,false);cam.aspect=w/h;cam.updateProjectionMatrix();} addEventListener('resize',resize); resize();
  const root=document.documentElement; let run=true,f=1,t=0; document.addEventListener('visibilitychange',()=>run=!document.hidden);
  function tick(){requestAnimationFrame(tick); if(!run)return; t+=.016;
    let g=.8+Math.sin(t*7)*.05+Math.sin(t*23)*.03+(Math.random()-.5)*.04;
    if(Math.random()<.010)g*=.15; if(Math.random()<.006)g=Math.min(1.25,g*1.6); f+=(g-f)*.5;
    fil.material.color.setRGB(1,.85+f*.12,.55+f*.25); core.material.opacity=.25+f*.55; glassMat.emissiveIntensity=f*.7;
    light.intensity=f*2.4; glow.material.opacity=.25+f*.6; root.style.setProperty('--glow',(0.08+f*.32).toFixed(3));
    bulb.rotation.y=mx*.5+t*.05; bulb.rotation.x=my*.25; bulb.position.y=Math.sin(t*.8)*.08; renderer.render(scene,cam);
  } tick();
} catch(e){ document.querySelector('.hero-fallback').style.display='block'; canvas.style.display='none'; document.documentElement.style.setProperty('--glow','.28'); } }
</script>
</body>
</html>
