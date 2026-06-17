<?php
header('X-Robots-Tag: noindex, nofollow, noarchive');
$base = __DIR__;                       // weihs/
$data = is_file($base.'/kabelliste.json') ? json_decode(file_get_contents($base.'/kabelliste.json'), true) : null;
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Dateien einsammeln (dynamisch, damit nichts vergessen wird) ---
function shorten($f){
  $n = basename($f);
  $n = preg_replace('/^\d{4}_\d{2}_\d{2}[_ -]*/u', '', $n);
  $n = str_replace(['Weihs, Nürnberg,', 'Weihs, Nürnberg', 'Familie Weihs, Nürnberg,'], '', $n);
  $n = preg_replace('/,?\s*Version\s*[\d.]+/u', '', $n);
  $n = preg_replace('/\.pdf$/i', '', $n);
  return trim($n, " ,-");
}
$plaene = glob($base.'/plaene/*.pdf') ?: [];
$kabel  = glob($base.'/*.pdf') ?: [];
sort($plaene); sort($kabel);
$groups = ['Installationspläne'=>[], 'Texte'=>[], 'Kabelliste (Original)'=>[]];
foreach($plaene as $f){
  $short = shorten($f);
  $g = (stripos($short,'Texte')===0) ? 'Texte' : 'Installationspläne';
  $groups[$g][] = ['path'=>'plaene/'.rawurlencode(basename($f)), 'name'=>$short];
}
foreach($kabel as $f){
  $groups['Kabelliste (Original)'][] = ['path'=>rawurlencode(basename($f)), 'name'=>shorten($f)];
}

$floors = ['KG'=>[], 'EG'=>[], '1. OG'=>[], 'DG'=>[], 'Außenbereich'=>[]];
if ($data) foreach ($data['rooms'] as $r) $floors[$r['floor']][] = $r;

function oh_color($f){
  $f = mb_strtolower(trim($f));
  $map = ['braun'=>'#7a4a1e','blau'=>'#2b66d8','grün'=>'#2faa48','gelb/ grün'=>'#cfd400','gelb/grün'=>'#cfd400',
    'gelb'=>'#f5c400','grau'=>'#9aa0ad','schwarz'=>'#1a1a1a','weiß'=>'#ffffff','rot'=>'#d83a3a','orange'=>'#f08a26',
    'rosa'=>'#e87fb0','violett'=>'#8a52c9','türkis'=>'#1fb9b0'];
  if(isset($map[$f])) return $map[$f];
  foreach($map as $k=>$v){ if(strpos($f,$k)===0) return $v; }
  return '';
}
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Projekt Weihs, Nürnberg – Pläne &amp; Kabelzugliste</title>
<style>
:root{ --ink:#13142a; --blue:#3f7bf0; --yellow:#FFD400; --line:#e3e6ef; --soft:#f6f7fb; --muted:#6b7088; }
*{ box-sizing:border-box; }
body{ margin:0; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--ink); background:#eef0f6; }
a{ color:inherit; }

.bar{ position:sticky; top:0; z-index:50; background:#fff; border-bottom:1px solid var(--line);
  display:flex; align-items:center; gap:16px; padding:12px 22px; box-shadow:0 2px 14px rgba(20,20,40,.05); flex-wrap:wrap; }
.bar .logo{ height:34px; background:#fff; border-radius:9px; padding:3px 6px; }
.bar h1{ font-size:16px; margin:0; font-weight:800; }
.bar .sub{ font-size:12px; color:var(--muted); margin-top:1px; }
.bar .spacer{ flex:1; }
.btn{ border:0; cursor:pointer; font-weight:700; font-size:13px; border-radius:10px; padding:10px 16px; text-decoration:none;
  background:var(--blue); color:#fff; display:inline-flex; align-items:center; gap:7px; }
.btn.ghost{ background:var(--soft); color:var(--ink); border:1px solid var(--line); }

.nav{ background:#fff; border-bottom:1px solid var(--line); padding:10px 22px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.nav a{ text-decoration:none; font-size:12px; font-weight:700; background:var(--soft); border:1px solid var(--line); border-radius:999px; padding:6px 12px; }
.nav a:hover{ border-color:var(--blue); color:var(--blue); }
.nav .fl{ width:100%; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--muted); margin:6px 0 0; }

.wrap{ max-width:1000px; margin:22px auto; padding:0 16px; }
.section-title{ font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:1.2px; color:var(--muted); margin:6px 0 14px; display:flex; align-items:center; gap:10px; }
.section-title::after{ content:''; flex:1; height:1px; background:var(--line); }

/* Dateien */
.filegroup{ margin-bottom:18px; }
.filegroup h3{ font-size:13px; margin:0 0 9px; font-weight:800; }
.cards{ display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:12px; }
.card{ background:#fff; border:1px solid var(--line); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:10px;
  box-shadow:0 4px 14px rgba(20,20,40,.04); }
.card .ic{ width:38px; height:38px; border-radius:9px; background:#fdecec; color:#d83a3a; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; }
.card .nm{ font-weight:700; font-size:13.5px; line-height:1.3; }
.card .acts{ display:flex; gap:8px; margin-top:auto; }
.card .acts a{ flex:1; text-align:center; font-size:12px; font-weight:700; text-decoration:none; border-radius:8px; padding:7px 0; }
.a-view{ background:var(--blue); color:#fff; }
.a-dl{ background:var(--soft); border:1px solid var(--line); color:var(--ink); }

/* Raum-Blatt */
.sheet{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:26px 28px 30px; margin:0 auto 26px; box-shadow:0 8px 30px rgba(20,20,40,.06); }
.head{ display:flex; align-items:flex-end; justify-content:space-between; gap:16px; border-bottom:3px solid var(--ink); padding-bottom:12px; }
.head .room{ font-size:30px; font-weight:900; letter-spacing:-.5px; line-height:1; }
.head .meta{ text-align:right; font-size:12px; color:var(--muted); line-height:1.5; }
.badge{ display:inline-block; background:var(--ink); color:#fff; font-weight:800; font-size:12px; padding:4px 11px; border-radius:8px; }
.badge.y{ background:var(--yellow); color:#1a1a1a; }
.proj{ font-size:11px; color:var(--muted); margin:8px 0 14px; }
table{ width:100%; border-collapse:collapse; font-size:11.5px; }
thead th{ background:var(--ink); color:#fff; font-size:10px; text-transform:uppercase; letter-spacing:.6px; text-align:left; padding:7px 8px; font-weight:700; }
tbody td{ border-bottom:1px solid var(--line); padding:6px 8px; vertical-align:top; }
tbody tr.cstart td{ border-top:2px solid #cfd4e3; }
.cell-bez{ font-weight:800; white-space:nowrap; }
.typ-pill{ display:inline-block; min-width:26px; text-align:center; background:var(--soft); border:1px solid var(--line); border-radius:6px; padding:1px 6px; font-weight:800; font-size:11px; }
.bauteil{ font-weight:600; }
.note{ color:var(--muted); font-size:10.5px; font-style:italic; }
.ref{ color:var(--blue); font-weight:700; font-size:10.5px; }
.adern{ text-align:center; font-weight:800; }
.kabel{ white-space:nowrap; font-weight:600; }
.farbe{ white-space:nowrap; }
.belegung{ color:#33384d; }
.dot{ display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:5px; vertical-align:middle; border:1px solid rgba(0,0,0,.18); }
.empty{ color:#b9bdcc; }
.foot{ margin-top:14px; font-size:10px; color:var(--muted); display:flex; justify-content:space-between; border-top:1px solid var(--line); padding-top:8px; }

/* Viewer */
.modal{ position:fixed; inset:0; background:rgba(10,12,25,.72); display:none; z-index:100; padding:24px; }
.modal.on{ display:flex; flex-direction:column; }
.modal .top{ display:flex; align-items:center; gap:14px; color:#fff; padding:4px 4px 12px; }
.modal .top .t{ font-weight:700; font-size:14px; flex:1; }
.modal .x{ background:#fff; border:0; border-radius:9px; padding:8px 14px; font-weight:800; cursor:pointer; }
.modal iframe{ flex:1; width:100%; border:0; border-radius:12px; background:#fff; }

@media print{
  @page{ size:A4 portrait; margin:12mm; }
  body{ background:#fff; }
  .bar,.nav,#dateien,.modal{ display:none !important; }
  .wrap{ margin:0; padding:0; max-width:none; }
  .sheet{ border:0; border-radius:0; box-shadow:none; padding:0; margin:0; page-break-after:always; }
  .sheet:last-child{ page-break-after:auto; }
  thead{ display:table-header-group; }
  tr{ page-break-inside:avoid; }
}
</style>
</head>
<body>
<div class="bar">
  <img class="logo" src="../assets/img/logohaustechnikneu.png" alt="OH Haustechnik">
  <div>
    <h1>Projekt Familie Weihs, Nürnberg</h1>
    <div class="sub">Pläne, Texte &amp; Kabelzugliste nach Raum<?php if($data): ?> &middot; <?= (int)$data['anzahl_kabel'] ?> Kabel &middot; <?= count($data['rooms']) ?> Räume<?php endif; ?> &middot; Stand 15.05.2026 (V1.4)</div>
  </div>
  <div class="spacer"></div>
  <a class="btn ghost" href="#dateien">📂 Dateien</a>
  <button class="btn" onclick="window.print()">🖨️ Kabelliste drucken</button>
</div>

<div class="nav">
  <div class="fl">Springe zu Raum</div>
<?php foreach($floors as $fl=>$rooms){ if(!$rooms) continue; foreach($rooms as $r){ ?>
  <a href="#r-<?= h($r['etage'].'-'.$r['raum']) ?>"><?= h($r['name']) ?></a>
<?php } } ?>
</div>

<div class="wrap">

  <!-- ========== ALLE DATEIEN ========== -->
  <div id="dateien">
    <div class="section-title">📂 Alle Dateien &amp; Pläne</div>
  <?php foreach($groups as $gname=>$files){ if(!$files) continue; ?>
    <div class="filegroup">
      <h3><?= h($gname) ?> <span style="color:var(--muted);font-weight:600">(<?= count($files) ?>)</span></h3>
      <div class="cards">
      <?php foreach($files as $f){ ?>
        <div class="card">
          <div class="ic">PDF</div>
          <div class="nm"><?= h($f['name']) ?></div>
          <div class="acts">
            <a class="a-view" href="#" onclick="openPdf('<?= h($f['path']) ?>','<?= h($f['name']) ?>');return false;">Ansehen</a>
            <a class="a-dl" href="<?= h($f['path']) ?>" target="_blank" rel="noopener" download>↓</a>
          </div>
        </div>
      <?php } ?>
      </div>
    </div>
  <?php } ?>
  </div>

  <!-- ========== KABELZUGLISTE NACH RAUM ========== -->
  <div class="section-title" style="margin-top:34px">🔌 Kabelzugliste nach Raum <span style="color:var(--blue)">– ein Blatt pro Raum zum Aushängen</span></div>
<?php if(!$data): ?>
  <p>Die Datei <code>weihs/kabelliste.json</code> fehlt – Kabelliste kann nicht angezeigt werden.</p>
<?php else: foreach($data['rooms'] as $r): ?>
  <section class="sheet" id="r-<?= h($r['etage'].'-'.$r['raum']) ?>">
    <div class="head">
      <div class="room"><?= h($r['name']) ?></div>
      <div class="meta">
        <span class="badge"><?= h($r['floor']) ?></span>
        <span class="badge y">Raum <?= h($r['raum']) ?></span><br>
        <?= count($r['cables']) ?> Kabel
      </div>
    </div>
    <p class="proj"><?= h($data['projekt']) ?> &middot; Stand <?= h($data['stand']) ?></p>
    <table>
      <thead><tr>
        <th style="width:7%">Bez.</th><th style="width:30%">Angeschlossenes Bauteil</th>
        <th style="width:16%">Kabeltyp</th><th style="width:6%">Adern</th>
        <th style="width:18%">Farbe</th><th style="width:23%">Belegung</th>
      </tr></thead>
      <tbody>
      <?php foreach($r['cables'] as $c): $rows=$c['rows']; $n=max(1,count($rows)); ?>
        <?php for($i=0;$i<$n;$i++): $w=$rows[$i]??['farbe'=>'','belegung'=>'','note'=>'']; ?>
        <tr class="<?= $i===0?'cstart':'' ?>">
          <?php if($i===0): ?>
            <td rowspan="<?= $n ?>" class="cell-bez"><span class="typ-pill"><?= h($c['typ']) ?></span><br><?= h($c['nr']) ?></td>
            <td rowspan="<?= $n ?>">
              <div class="bauteil"><?= h($c['desc'])!==''? h($c['desc']) : '<span class="empty">–</span>' ?></div>
              <?php foreach($c['refs'] as $rf): if(!empty($rf['ziel'])): ?>
                <div class="ref">↳ aus <b><?= h($rf['ziel']) ?></b> <span class="note">(<?= h($rf['src']) ?>)</span></div>
              <?php else: ?><div class="ref note"><?= h($rf['src']) ?></div><?php endif; endforeach; ?>
            </td>
            <td rowspan="<?= $n ?>" class="kabel"><?= h($c['kabeltyp'])!==''? h($c['kabeltyp']) : '<span class="empty">–</span>' ?></td>
            <td rowspan="<?= $n ?>" class="adern"><?= h($c['anzahl']) ?></td>
          <?php endif; ?>
          <td class="farbe"><?php $col=oh_color($w['farbe']); if($w['farbe']!==''){ if($col) echo '<span class="dot" style="background:'.h($col).'"></span>'; echo h($w['farbe']); } ?></td>
          <td class="belegung"><?= h($w['belegung']) ?>
            <?php if(!empty($w['note']) && stripos($w['note'],'von ')!==0): ?><div class="note"><?= h($w['note']) ?></div><?php endif; ?>
          </td>
        </tr>
        <?php endfor; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="foot"><span>OH Haustechnik &middot; Kabelzugliste nach Raum</span><span><?= h($r['floor']) ?> / <?= h($r['name']) ?></span></div>
  </section>
<?php endforeach; endif; ?>
</div>

<div class="modal" id="pdfModal">
  <div class="top"><span class="t" id="pdfTitle"></span>
    <a class="x" id="pdfOpen" target="_blank" rel="noopener" style="text-decoration:none">In neuem Tab</a>
    <button class="x" onclick="closePdf()">Schließen ✕</button>
  </div>
  <iframe id="pdfFrame" src="about:blank"></iframe>
</div>

<script>
function openPdf(path,name){
  document.getElementById('pdfTitle').textContent=name;
  document.getElementById('pdfFrame').src=path;
  document.getElementById('pdfOpen').href=path;
  document.getElementById('pdfModal').classList.add('on');
  document.body.style.overflow='hidden';
}
function closePdf(){
  document.getElementById('pdfModal').classList.remove('on');
  document.getElementById('pdfFrame').src='about:blank';
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closePdf(); });
</script>
</body>
</html>
