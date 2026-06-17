<?php
header('X-Robots-Tag: noindex, nofollow, noarchive');
$data = is_file(__DIR__.'/kabelliste.json') ? json_decode(file_get_contents(__DIR__.'/kabelliste.json'), true) : null;
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$FLOORAA=['-01'=>'01','00'=>'00','01'=>'01','02'=>'02','03'=>'03'];
function owncode($c){ global $FLOORAA; return $c['typ'].'-'.$FLOORAA[$c['etage']].'.'.sprintf('%02d',(int)$c['raum']).'.'.$c['nr']; }
// Anzeige-Code wie auf dem Plan (z.B. S00.02.05) – fuer JEDES Bauteil
function plancode($c){ global $FLOORAA; $code=$c['typ'].($FLOORAA[$c['etage']]??'00').'.'.sprintf('%02d',(int)$c['raum']); if(trim((string)$c['nr'])!=='') $code.='.'.$c['nr']; return $code; }

// Schaltzeichen (vereinfachte SVG, an Plan angelehnt)
function sym($typ){
  $g='stroke="#1f7a33" stroke-width="2.2" fill="none"';
  switch($typ){
    case 'L': return '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" '.$g.'/><line x1="6.3" y1="6.3" x2="17.7" y2="17.7" '.$g.'/><line x1="17.7" y1="6.3" x2="6.3" y2="17.7" '.$g.'/></svg>';
    case 'S': case 'H': return '<svg viewBox="0 0 24 24"><path d="M4 14 A8 8 0 0 1 20 14 Z" '.$g.'/><line x1="12" y1="6" x2="12" y2="14" '.$g.'/></svg>';
    case 'N': case 'TN': return '<svg viewBox="0 0 24 24"><rect x="4" y="7" width="16" height="10" rx="1.5" '.$g.'/><path d="M8 17 v-4 h8 v4" '.$g.'/></svg>';
    case 'K': return '<svg viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2" '.$g.'/><line x1="5" y1="12" x2="19" y2="12" '.$g.'/></svg>';
    case 'HKV': return '<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" '.$g.'/><line x1="9" y1="6" x2="9" y2="18" '.$g.'/><line x1="15" y1="6" x2="15" y2="18" '.$g.'/></svg>';
    case 'FL': case 'W': case 'Mo': return '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" '.$g.'/><path d="M12 5 v14 M5 12 h14" '.$g.'/></svg>';
    case 'Ro': return '<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="14" rx="1" '.$g.'/><line x1="4" y1="9" x2="20" y2="9" '.$g.'/><line x1="4" y1="13" x2="20" y2="13" '.$g.'/></svg>';
    // Tree-Komponenten
    case 'TP': return '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3" stroke="#2452c8" stroke-width="2.2" fill="none"/><text x="12" y="16" text-anchor="middle" font-size="9" fill="#2452c8" font-weight="700">TP</text></svg>';
    default: return '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3" stroke="#2452c8" stroke-width="2.2" fill="none"/><circle cx="12" cy="12" r="3" fill="#2452c8"/></svg>';
  }
}
$TREE=['PR','T','Pa','Pe','TP','TtA','Tta','TN','NFC','Alarm','Wetter'];
$ELABEL=['KG'=>'Kellergeschoss','EG'=>'Erdgeschoss','1. OG'=>'1. Obergeschoss','DG'=>'Dachgeschoss','Außenbereich'=>'Außenbereich'];
$floors=['KG'=>[],'EG'=>[],'1. OG'=>[],'DG'=>[],'Außenbereich'=>[]];
if($data) foreach($data['rooms'] as $r) $floors[$r['floor']][]=$r;
?><!DOCTYPE html><html lang="de"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Installationsplan interaktiv – Weihs</title>
<style>
:root{ --ink:#13142a; --blue:#3f7bf0; --yellow:#FFD400; --line:#e3e6ef; --soft:#f6f7fb; --muted:#6b7088; --green:#1f7a33; }
*{ box-sizing:border-box; }
body{ margin:0; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--ink); background:#eef0f6; }
.bar{ position:sticky; top:0; z-index:60; background:#fff; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:14px; padding:12px 20px; flex-wrap:wrap; box-shadow:0 2px 14px rgba(20,20,40,.05); }
.bar img{ height:32px; background:#fff; border-radius:8px; padding:3px 5px; }
.bar h1{ font-size:15px; margin:0; font-weight:800; }
.bar .sub{ font-size:11.5px; color:var(--muted); }
.bar .spacer{ flex:1; }
.btn{ border:1px solid var(--line); background:var(--soft); color:var(--ink); font-weight:700; font-size:12.5px; border-radius:9px; padding:8px 13px; cursor:pointer; text-decoration:none; }
.hint-bar{ background:#eef3ff; border-bottom:1px solid #d7e0f5; color:#27408b; font-size:12.5px; padding:8px 20px; }

#stage{ position:relative; max-width:1100px; margin:18px auto; padding:0 14px 120px; }
svg#wires{ position:absolute; inset:0; width:100%; height:100%; pointer-events:none; z-index:40; overflow:visible; }
.floor{ background:#fff; border:1px solid var(--line); border-radius:16px; margin:0 0 26px; box-shadow:0 8px 26px rgba(20,20,40,.06); position:relative; z-index:1; }
.floor-h{ display:flex; align-items:center; gap:12px; padding:14px 18px; border-bottom:1px solid var(--line); }
.floor-h .badge{ background:var(--ink); color:#fff; font-weight:800; font-size:12px; padding:4px 11px; border-radius:8px; }
.floor-h .nm{ font-size:18px; font-weight:900; }
.floor-h .ct{ font-size:11.5px; color:var(--muted); margin-left:auto; }
.rooms{ display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:12px; padding:14px; }
.room{ border:1px solid var(--line); border-radius:12px; padding:10px 11px; background:#fcfcfe; }
.room h4{ margin:0 0 8px; font-size:13px; font-weight:800; display:flex; align-items:center; gap:6px; }
.room h4 .rn{ font-size:10px; color:var(--muted); font-weight:700; }
.chips{ display:flex; flex-wrap:wrap; gap:6px; }
.chip{ display:flex; align-items:center; gap:5px; border:1px solid var(--line); border-radius:9px; padding:4px 7px 4px 5px; background:#fff; cursor:pointer; transition:.12s; max-width:100%; }
.chip:hover{ border-color:var(--blue); box-shadow:0 2px 8px rgba(63,123,240,.15); }
.chip svg{ width:20px; height:20px; flex:none; }
.chip .lab{ font-size:10.5px; line-height:1.15; }
.chip .lab b{ display:block; font-size:11px; }
.chip .lab .cd{ color:var(--muted); font-family:ui-monospace,Menlo,monospace; font-size:9.5px; }
.chip.tree{ background:#f2f6ff; }
.chip.sel{ border-color:var(--blue); background:#e8f0ff; box-shadow:0 0 0 2px rgba(63,123,240,.3); }
.chip.target{ border-color:#e0a400; background:#fff7e0; box-shadow:0 0 0 2px rgba(224,164,0,.45); }
.chip.dim{ opacity:.32; }
#vert{ display:inline-flex; align-items:center; gap:6px; }

/* Verteilung Knoten */
.vert-node{ display:inline-flex; align-items:center; gap:7px; border:2px solid var(--ink); border-radius:10px; padding:6px 11px; background:#fff; font-weight:800; font-size:12px; }
.vert-node .d{ width:10px; height:10px; border-radius:2px; background:var(--ink); }

/* Info-Panel */
#info{ position:fixed; left:50%; transform:translateX(-50%); bottom:14px; z-index:80; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 12px 40px rgba(20,20,40,.22); padding:13px 16px; width:min(620px,94vw); display:none; }
#info.on{ display:block; }
#info .it{ display:flex; align-items:center; gap:10px; }
#info .it svg{ width:26px; height:26px; }
#info h3{ margin:0; font-size:15px; font-weight:900; }
#info .meta{ font-size:11.5px; color:var(--muted); }
#info .vn{ display:flex; gap:10px; margin-top:9px; flex-wrap:wrap; }
#info .ep{ flex:1; min-width:200px; border:1px solid var(--line); border-radius:9px; padding:8px 10px; background:var(--soft); }
#info .ep .k{ font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; }
#info .ep.von .k{ color:var(--green); } #info .ep.nach .k{ color:#d83a3a; }
#info .ep .room{ font-weight:800; font-size:13px; }
#info .ep .cd{ font-family:ui-monospace,Menlo,monospace; font-size:11px; color:var(--muted); }
#info .x{ position:absolute; top:8px; right:10px; border:0; background:transparent; font-size:18px; cursor:pointer; color:var(--muted); }
.legend-mini{ font-size:11px; color:var(--muted); padding:0 14px 6px; }
@media print{ .bar,.hint-bar,#info{ display:none; } }
</style></head>
<body>
<div class="bar">
  <img src="../assets/img/logohaustechnikneu.png" alt="OH">
  <div><h1>Installationsplan – interaktiv</h1><div class="sub"><?= $data?h($data['projekt']):'' ?> · Bauteil antippen → Kabelweg wird angezeigt</div></div>
  <div class="spacer"></div>
  <a class="btn" href="index.php">← Zur Kabelliste</a>
</div>
<div class="hint-bar">👆 Tippe auf ein Bauteil (z.&nbsp;B. eine Steckdose oder einen Tree-Knoten) – die Linie zeigt, <b>von wo nach wo</b> das Kabel läuft. Etagen sind von oben dargestellt und untereinander gestapelt.</div>

<?php if(!$data): ?><p style="padding:30px">Keine Daten.</p><?php else: ?>
<div id="stage">
  <svg id="wires"></svg>
<?php foreach($floors as $fl=>$rooms){ if(!$rooms) continue; $fcount=array_sum(array_map(function($x){return count($x['cables']);},$rooms)); ?>
  <div class="floor" data-floor="<?= h($fl) ?>">
    <div class="floor-h"><span class="badge"><?= h($fl) ?></span><span class="nm"><?= h($ELABEL[$fl]) ?></span><span class="ct"><?= count($rooms) ?> Räume · <?= (int)$fcount ?> Kabel</span></div>
    <?php if($fl==='KG'): ?><div class="legend-mini">🏠 Die <b>Verteilung / Technik</b> (Stromkreis-Ziel aller Zuleitungen) sitzt im KG, Raum Technik.</div><?php endif; ?>
    <div class="rooms">
    <?php foreach($rooms as $r){ $rid=$r['etage'].'-'.$r['raum']; ?>
      <div class="room" id="room-<?= h($rid) ?>">
        <h4><?= h($r['name']) ?> <span class="rn">R<?= h($r['raum']) ?></span></h4>
        <div class="chips">
        <?php foreach($r['cables'] as $ci=>$c){
          $istree = in_array($c['typ'],$TREE,true) && strpos($c['desc'],'Tree Bus')!==false;
          $oc = $istree ? owncode($c) : ($c['etage'].'_'.$c['raum'].'_'.$ci);
          // target
          $target='VERTEILUNG';
          if(!empty($c['endp']) && !empty($c['endp']['von']) && $c['endp']['von']['code']!=='—') $target=$c['endp']['von']['code'];
          if(!empty($c['endp']) && $c['endp']['kind']==='Ableitung') $target='VERTEILUNG';
          $short = preg_replace('/^(Zuleitung|Verbindung|Ableitung)\s+/u','',$c['desc']);
          if($short==='') $short=$c['typ'];
        ?>
          <div class="chip <?= $istree?'tree':'' ?>" id="chip-<?= h($oc) ?>"
               data-code="<?= h($oc) ?>" data-target="<?= h($target) ?>" data-room="<?= h($rid) ?>"
               data-typ="<?= h($c['typ']) ?>" data-name="<?= h($short) ?>" data-kabel="<?= h($c['kabeltyp']) ?>"
               data-adern="<?= h($c['anzahl']) ?>"
               data-vonroom="<?= h($c['endp']['von']['room'] ?? '') ?>" data-voncode="<?= h(($c['endp']['von']['code'] ?? '')!=='—'?($c['endp']['von']['code']??''):'') ?>"
               data-nachroom="<?= h($c['endp']['nach']['room'] ?? $r['name']) ?>" data-nachcode="<?= h(($c['endp']['nach']['code'] ?? '')!=='—'?($c['endp']['nach']['code']??''):'') ?>"
               data-disp="<?= h(plancode($c)) ?>" data-treeflag="<?= $istree?1:0 ?>">
            <?= sym($c['typ']) ?>
            <span class="lab"><b><?= h(plancode($c)) ?></b><span class="cd"><?= h($short) ?></span></span>
          </div>
        <?php } ?>
        </div>
      </div>
    <?php } ?>
    </div>
    <?php if($fl==='KG'): ?>
    <div style="padding:0 14px 16px"><span class="vert-node" id="node-VERTEILUNG"><span class="d"></span> Verteilung / Technik (KG)</span></div>
    <?php endif; ?>
  </div>
<?php } ?>
</div>

<div id="info">
  <button class="x" onclick="clearSel()">✕</button>
  <div class="it"><span id="iSym"></span><div><h3 id="iName"></h3><div class="meta" id="iMeta"></div></div></div>
  <div class="vn">
    <div class="ep von"><div class="k">Von</div><div class="room" id="iVonRoom"></div><div class="cd" id="iVonCode"></div></div>
    <div class="ep nach"><div class="k">Nach</div><div class="room" id="iNachRoom"></div><div class="cd" id="iNachCode"></div></div>
  </div>
</div>
<?php endif; ?>

<script>
const wires=document.getElementById('wires');
const stage=document.getElementById('stage');
function clearSel(){
  document.querySelectorAll('.chip.sel,.chip.target,.chip.dim').forEach(c=>c.classList.remove('sel','target','dim'));
  document.querySelectorAll('#node-VERTEILUNG.target').forEach(n=>n.classList.remove('target'));
  wires.innerHTML='';
  document.getElementById('info').classList.remove('on');
}
function centerOf(el){
  const s=stage.getBoundingClientRect(), r=el.getBoundingClientRect();
  return { x:r.left-s.left+r.width/2, y:r.top-s.top+r.height/2, top:r.top-s.top, bottom:r.bottom-s.top };
}
function drawWire(a,b){
  const p1=centerOf(a), p2=centerOf(b);
  const dx=(p2.x-p1.x), midY=(p1.y+p2.y)/2;
  const d=`M ${p1.x} ${p1.y} C ${p1.x+dx*0.2} ${midY}, ${p2.x-dx*0.2} ${midY}, ${p2.x} ${p2.y}`;
  const path=document.createElementNS('http://www.w3.org/2000/svg','path');
  path.setAttribute('d',d); path.setAttribute('fill','none');
  path.setAttribute('stroke','#3f7bf0'); path.setAttribute('stroke-width','3');
  path.setAttribute('stroke-linecap','round'); path.setAttribute('opacity','0.9');
  wires.appendChild(path);
  const len=path.getTotalLength();
  path.style.strokeDasharray=len; path.style.strokeDashoffset=len;
  path.animate([{strokeDashoffset:len},{strokeDashoffset:0}],{duration:650,easing:'ease-in-out',fill:'forwards'});
  // moving dot
  const dot=document.createElementNS('http://www.w3.org/2000/svg','circle');
  dot.setAttribute('r','5'); dot.setAttribute('fill','#FFD400'); dot.setAttribute('stroke','#3f7bf0'); dot.setAttribute('stroke-width','1.5');
  wires.appendChild(dot);
  let t0=null; function step(ts){ if(!t0)t0=ts; let k=Math.min(1,(ts-t0)/900); const pt=path.getPointAtLength(len*k); dot.setAttribute('cx',pt.x); dot.setAttribute('cy',pt.y); if(k<1) requestAnimationFrame(step); }
  requestAnimationFrame(step);
}
function select(chip){
  clearSel();
  chip.classList.add('sel');
  const info=document.getElementById('info');
  const nd=s=>(s||'').replace('-','');
  document.getElementById('iSym').innerHTML=chip.querySelector('svg').outerHTML;
  document.getElementById('iName').textContent=chip.dataset.disp;
  document.getElementById('iMeta').textContent=`${chip.dataset.name} · ${chip.dataset.typ} · ${chip.dataset.kabel||'—'} · ${chip.dataset.adern||''} Adern`;
  const vonRoom=chip.dataset.vonroom, vonCode=chip.dataset.voncode;
  const nachRoom=chip.dataset.nachroom, nachCode=chip.dataset.nachcode;
  if(chip.dataset.treeflag==='1'){
    document.getElementById('iVonRoom').textContent=vonRoom||'Verteilung';
    document.getElementById('iVonCode').textContent=nd(vonCode);
    document.getElementById('iNachRoom').textContent=nachRoom;
    document.getElementById('iNachCode').textContent=nd(nachCode);
  }else{
    document.getElementById('iVonRoom').textContent=nachRoom;
    document.getElementById('iVonCode').textContent=chip.dataset.disp;
    document.getElementById('iNachRoom').textContent='Verteilung / Technik (KG)';
    document.getElementById('iNachCode').textContent='Stromkreis';
  }
  info.classList.add('on');
  // target element
  const tgt=chip.dataset.target;
  let tEl=null;
  if(tgt==='VERTEILUNG') tEl=document.getElementById('node-VERTEILUNG');
  else tEl=document.getElementById('chip-'+cssEsc(tgt));
  if(!tEl){ // fallback: room of von
    if(vonRoom){ /* nothing precise */ }
  }
  document.querySelectorAll('.chip').forEach(c=>{ if(c!==chip && c!==tEl) c.classList.add('dim'); });
  if(tEl){ tEl.classList.add('target'); tEl.classList.remove('dim'); drawWire(chip,tEl); tEl.scrollIntoView({behavior:'smooth',block:'center'}); }
}
function cssEsc(s){ return (window.CSS&&CSS.escape)?CSS.escape(s):s.replace(/[^\w-]/g,'\\$&'); }
document.querySelectorAll('.chip').forEach(c=>c.addEventListener('click',()=>select(c)));
window.addEventListener('resize',clearSel);
</script>
</body></html>
