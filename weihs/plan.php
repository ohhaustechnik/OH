<?php
header('X-Robots-Tag: noindex, nofollow, noarchive');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$dir = __DIR__.'/plaene/img';
$floors = [
  'KG'    => ['Kellergeschoss','plan_KG.jpg'],
  'EG'    => ['Erdgeschoss','plan_EG.jpg'],
  '1OG'   => ['1. Obergeschoss','plan_1OG.jpg'],
  '2OG'   => ['2. OG / Dachgeschoss','plan_2OG.jpg'],
  'Aussen'=> ['Außenbereich','plan_Aussen.jpg'],
];
$avail = [];
foreach($floors as $k=>$v){ if(is_file($dir.'/'.$v[1])) $avail[$k]=$v; }
$first = array_key_first($avail);
?><!DOCTYPE html><html lang="de"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="robots" content="noindex, nofollow">
<title>Installationspläne – Weihs</title>
<style>
:root{ --ink:#13142a; --blue:#3f7bf0; --line:#e3e6ef; --soft:#f6f7fb; --muted:#6b7088; }
*{ box-sizing:border-box; }
html,body{ margin:0; height:100%; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--ink); background:#0f1020; overflow:hidden; }
.bar{ position:fixed; top:0; left:0; right:0; z-index:30; background:#fff; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:12px; padding:9px 16px; flex-wrap:wrap; box-shadow:0 2px 14px rgba(0,0,0,.18); }
.bar img{ height:30px; background:#fff; border-radius:7px; padding:2px 5px; }
.bar h1{ font-size:14px; margin:0; font-weight:800; }
.bar .sub{ font-size:11px; color:var(--muted); }
.bar .spacer{ flex:1; }
.btn{ border:1px solid var(--line); background:var(--soft); color:var(--ink); font-weight:700; font-size:12px; border-radius:8px; padding:7px 11px; cursor:pointer; text-decoration:none; }
.tabs{ position:fixed; top:50px; left:0; right:0; z-index:25; display:flex; gap:6px; padding:8px 12px; background:rgba(255,255,255,.93); backdrop-filter:blur(6px); border-bottom:1px solid var(--line); overflow-x:auto; }
.tab{ flex:none; border:1px solid var(--line); background:#fff; color:var(--ink); font-weight:800; font-size:12.5px; border-radius:999px; padding:7px 14px; cursor:pointer; white-space:nowrap; }
.tab.on{ background:var(--blue); color:#fff; border-color:var(--blue); }
#viewport{ position:fixed; inset:0; top:104px; overflow:hidden; background:#11121f; touch-action:none; cursor:grab; }
#viewport.drag{ cursor:grabbing; }
#canvas{ position:absolute; left:0; top:0; transform-origin:0 0; will-change:transform; }
#canvas img{ display:none; max-width:none; user-select:none; -webkit-user-drag:none; box-shadow:0 0 0 1px #ccc; }
#canvas img.on{ display:block; }
.zoomctl{ position:fixed; right:14px; bottom:18px; z-index:30; display:flex; flex-direction:column; gap:8px; }
.zoomctl button{ width:46px; height:46px; border-radius:12px; border:1px solid var(--line); background:#fff; font-size:20px; font-weight:800; cursor:pointer; box-shadow:0 4px 14px rgba(0,0,0,.25); }
.hint{ position:fixed; left:50%; bottom:18px; transform:translateX(-50%); z-index:28; background:rgba(19,20,42,.86); color:#fff; font-size:12px; padding:8px 14px; border-radius:999px; pointer-events:none; transition:opacity .4s; }
.spin{ position:fixed; inset:104px 0 0 0; display:flex; align-items:center; justify-content:center; color:#9aa; font-size:14px; z-index:5; }
</style></head>
<body>
<div class="bar">
  <img src="../assets/img/logohaustechnikneu.png" alt="OH">
  <div><h1>Installationspläne – interaktiv</h1><div class="sub">Familie Weihs, Nürnberg · zoomen &amp; verschieben</div></div>
  <div class="spacer"></div>
  <a class="btn" href="index.php">← Kabelliste</a>
</div>
<div class="tabs" id="tabs">
<?php foreach($avail as $k=>$v): ?>
  <button class="tab<?= $k===$first?' on':'' ?>" data-floor="<?= h($k) ?>"><?= h($v[0]) ?></button>
<?php endforeach; ?>
</div>

<div id="viewport">
  <div class="spin" id="spin">Plan wird geladen…</div>
  <div id="canvas">
  <?php foreach($avail as $k=>$v): ?>
    <img data-floor="<?= h($k) ?>" data-src="plaene/img/<?= h($v[1]) ?>" alt="<?= h($v[0]) ?>">
  <?php endforeach; ?>
  </div>
</div>

<div class="zoomctl">
  <button onclick="zoomBtn(1.3)">+</button>
  <button onclick="zoomBtn(1/1.3)">−</button>
  <button onclick="fitPlan()" title="einpassen" style="font-size:13px">FIT</button>
</div>
<div class="hint" id="hint">Ziehen zum Verschieben · 2 Finger / Mausrad zum Zoomen</div>

<script>
const vp=document.getElementById('viewport'), canvas=document.getElementById('canvas');
const imgs=[...document.querySelectorAll('#canvas img')];
let cur=null, scale=1, tx=0, ty=0, natW=0, natH=0;
function apply(){ canvas.style.transform=`translate(${tx}px,${ty}px) scale(${scale})`; }
function fitPlan(){
  if(!natW) return;
  const w=vp.clientWidth, h=vp.clientHeight;
  scale=Math.min(w/natW, h/natH)*0.98;
  tx=(w-natW*scale)/2; ty=(h-natH*scale)/2; apply();
}
function showFloor(k){
  const img=imgs.find(i=>i.dataset.floor===k);
  document.getElementById('spin').style.display='flex';
  const load=()=>{ imgs.forEach(i=>i.classList.remove('on')); img.classList.add('on');
    natW=img.naturalWidth; natH=img.naturalHeight; cur=k; document.getElementById('spin').style.display='none'; fitPlan(); };
  if(img.dataset.src && !img.src){ img.onload=load; img.src=img.dataset.src; }
  else if(img.complete){ load(); } else { img.onload=load; }
}
document.querySelectorAll('.tab').forEach(t=>t.addEventListener('click',()=>{
  document.querySelectorAll('.tab').forEach(x=>x.classList.remove('on')); t.classList.add('on'); showFloor(t.dataset.floor);
}));
function zoomAt(cx,cy,f){
  const ns=Math.max(0.05,Math.min(12,scale*f));
  tx = cx-(cx-tx)*(ns/scale); ty = cy-(cy-ty)*(ns/scale); scale=ns; apply();
}
function zoomBtn(f){ zoomAt(vp.clientWidth/2, vp.clientHeight/2, f); }
vp.addEventListener('wheel',e=>{ e.preventDefault(); const r=vp.getBoundingClientRect(); zoomAt(e.clientX-r.left,e.clientY-r.top, e.deltaY<0?1.12:1/1.12); },{passive:false});
// drag + pinch
let drag=false, lx=0, ly=0, pts=new Map(), pinchD=0, pinchC=null;
vp.addEventListener('pointerdown',e=>{ vp.setPointerCapture(e.pointerId); pts.set(e.pointerId,{x:e.clientX,y:e.clientY});
  if(pts.size===1){ drag=true; vp.classList.add('drag'); lx=e.clientX; ly=e.clientY; } });
vp.addEventListener('pointermove',e=>{
  if(!pts.has(e.pointerId)) return; pts.set(e.pointerId,{x:e.clientX,y:e.clientY});
  if(pts.size===2){ drag=false; const p=[...pts.values()]; const d=Math.hypot(p[0].x-p[1].x,p[0].y-p[1].y);
    const r=vp.getBoundingClientRect(); const c={x:(p[0].x+p[1].x)/2-r.left,y:(p[0].y+p[1].y)/2-r.top};
    if(pinchD){ zoomAt(c.x,c.y, d/pinchD); } pinchD=d; pinchC=c; return; }
  if(drag){ tx+=e.clientX-lx; ty+=e.clientY-ly; lx=e.clientX; ly=e.clientY; apply(); }
});
function up(e){ pts.delete(e.pointerId); if(pts.size<2) pinchD=0; if(pts.size===0){ drag=false; vp.classList.remove('drag'); } }
vp.addEventListener('pointerup',up); vp.addEventListener('pointercancel',up);
vp.addEventListener('dblclick',e=>{ const r=vp.getBoundingClientRect(); zoomAt(e.clientX-r.left,e.clientY-r.top,1.6); });
setTimeout(()=>{ document.getElementById('hint').style.opacity='0'; },4200);
window.addEventListener('resize',()=>{ if(cur) fitPlan(); });
showFloor(<?= json_encode($first) ?>);
</script>
</body></html>
