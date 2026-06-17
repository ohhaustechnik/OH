<?php
header('X-Robots-Tag: noindex, nofollow, noarchive');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
// Originale Installationsplaene (maßstabsgetreu) direkt einbinden
function findpdf($pat){ $g=glob(__DIR__.'/plaene/'.$pat); return $g? 'plaene/'.rawurlencode(basename($g[0])) : null; }
$floors = [
  'KG'    => ['Kellergeschoss',      findpdf('*Installationsplan KG*')],
  'EG'    => ['Erdgeschoss',         findpdf('*Installationsplan EG*')],
  '1OG'   => ['1. Obergeschoss',     findpdf('*Installationsplan 1.OG*')],
  '2OG'   => ['2. OG / Dachgeschoss',findpdf('*Installationsplan 2.OG*')],
  'Aussen'=> ['Außenbereich',        findpdf('*Installationsplan Außenbereich*')],
];
$avail=[]; foreach($floors as $k=>$v){ if($v[1]) $avail[$k]=$v; }
$first = array_key_first($avail);
?><!DOCTYPE html><html lang="de"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Installationspläne (Original) – Weihs</title>
<style>
:root{ --ink:#13142a; --blue:#3f7bf0; --line:#e3e6ef; --soft:#f6f7fb; --muted:#6b7088; }
*{ box-sizing:border-box; }
html,body{ margin:0; height:100%; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--ink); background:#33353f; overflow:hidden; }
.bar{ position:fixed; top:0; left:0; right:0; z-index:30; background:#fff; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:12px; padding:9px 16px; flex-wrap:wrap; box-shadow:0 2px 14px rgba(0,0,0,.18); }
.bar img{ height:30px; background:#fff; border-radius:7px; padding:2px 5px; }
.bar h1{ font-size:14px; margin:0; font-weight:800; }
.bar .sub{ font-size:11px; color:var(--muted); }
.bar .spacer{ flex:1; }
.btn{ border:1px solid var(--line); background:var(--soft); color:var(--ink); font-weight:700; font-size:12px; border-radius:8px; padding:7px 11px; cursor:pointer; text-decoration:none; }
.tabs{ position:fixed; top:50px; left:0; right:0; z-index:25; display:flex; gap:6px; padding:8px 12px; background:rgba(255,255,255,.96); border-bottom:1px solid var(--line); overflow-x:auto; align-items:center; }
.tab{ flex:none; border:1px solid var(--line); background:#fff; color:var(--ink); font-weight:800; font-size:12.5px; border-radius:999px; padding:7px 14px; cursor:pointer; white-space:nowrap; }
.tab.on{ background:var(--blue); color:#fff; border-color:var(--blue); }
.tabs .open{ margin-left:auto; flex:none; }
#frameWrap{ position:fixed; inset:104px 0 0 0; background:#52525a; }
#pdf{ width:100%; height:100%; border:0; }
</style></head>
<body>
<div class="bar">
  <img src="../assets/img/logohaustechnikneu.png" alt="OH">
  <div><h1>Installationspläne – Original (maßstabsgetreu)</h1><div class="sub">Familie Weihs, Nürnberg · Original-PDF, zoombar</div></div>
  <div class="spacer"></div>
  <a class="btn" href="index.php">← Kabelliste</a>
</div>
<div class="tabs" id="tabs">
<?php foreach($avail as $k=>$v): ?>
  <button class="tab<?= $k===$first?' on':'' ?>" data-floor="<?= h($k) ?>" data-src="<?= h($v[1]) ?>"><?= h($v[0]) ?></button>
<?php endforeach; ?>
  <a class="btn open" id="openTab" href="<?= h($avail[$first][1] ?? '#') ?>" target="_blank" rel="noopener">↗ In neuem Tab öffnen</a>
</div>

<div id="frameWrap">
  <iframe id="pdf" src="<?= h($avail[$first][1] ?? '') ?>#zoom=page-fit" title="Installationsplan"></iframe>
</div>

<script>
const frame=document.getElementById('pdf'), openTab=document.getElementById('openTab');
document.querySelectorAll('.tab').forEach(t=>t.addEventListener('click',()=>{
  document.querySelectorAll('.tab').forEach(x=>x.classList.remove('on')); t.classList.add('on');
  frame.src=t.dataset.src+'#zoom=page-fit';
  openTab.href=t.dataset.src;
}));
</script>
</body></html>
