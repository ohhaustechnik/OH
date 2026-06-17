<?php
header('X-Robots-Tag: noindex, nofollow, noarchive');
$json = __DIR__ . '/weihs/kabelliste.json';
$data = is_file($json) ? json_decode(file_get_contents($json), true) : null;
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$floors = ['KG'=>[], 'EG'=>[], '1. OG'=>[], 'DG'=>[], 'Außenbereich'=>[]];
if ($data) { foreach ($data['rooms'] as $r) { $floors[$r['floor']][] = $r; } }
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Kabelzugliste nach Raum – <?= h($data['projekt'] ?? 'Weihs') ?></title>
<style>
:root{ --ink:#13142a; --blue:#3f7bf0; --yellow:#FFD400; --line:#e3e6ef; --soft:#f6f7fb; --muted:#6b7088; }
*{ box-sizing:border-box; }
body{ margin:0; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--ink); background:#eef0f6; }
a{ color:inherit; }

/* ---- Bedienleiste (nur Bildschirm) ---- */
.bar{ position:sticky; top:0; z-index:50; background:#fff; border-bottom:1px solid var(--line);
  display:flex; align-items:center; gap:18px; padding:12px 22px; box-shadow:0 2px 14px rgba(20,20,40,.05); flex-wrap:wrap; }
.bar .logo{ height:34px; background:#fff; border-radius:9px; padding:3px 6px; }
.bar h1{ font-size:16px; margin:0; font-weight:800; letter-spacing:.2px; }
.bar .sub{ font-size:12px; color:var(--muted); margin-top:1px; }
.bar .spacer{ flex:1; }
.btn{ border:0; cursor:pointer; font-weight:700; font-size:13px; border-radius:10px; padding:10px 16px;
  background:var(--blue); color:#fff; display:inline-flex; align-items:center; gap:7px; }
.btn.ghost{ background:var(--soft); color:var(--ink); border:1px solid var(--line); }
.nav{ background:#fff; border-bottom:1px solid var(--line); padding:10px 22px; display:flex; gap:8px; flex-wrap:wrap; }
.nav a{ text-decoration:none; font-size:12px; font-weight:700; color:var(--ink); background:var(--soft);
  border:1px solid var(--line); border-radius:999px; padding:6px 12px; }
.nav a:hover{ border-color:var(--blue); color:var(--blue); }
.floorlabel{ width:100%; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--muted); margin:4px 0 0; }

.wrap{ max-width:1000px; margin:22px auto; padding:0 16px; }

/* ---- Raum-Blatt (1 pro A4) ---- */
.sheet{ background:#fff; border:1px solid var(--line); border-radius:14px; padding:26px 28px 30px;
  margin:0 auto 26px; box-shadow:0 8px 30px rgba(20,20,40,.06); }
.head{ display:flex; align-items:flex-end; justify-content:space-between; gap:16px; border-bottom:3px solid var(--ink); padding-bottom:12px; margin-bottom:6px; }
.head .room{ font-size:30px; font-weight:900; letter-spacing:-.5px; line-height:1; }
.head .meta{ text-align:right; font-size:12px; color:var(--muted); line-height:1.5; }
.badge{ display:inline-block; background:var(--ink); color:#fff; font-weight:800; font-size:12px;
  padding:4px 11px; border-radius:8px; letter-spacing:.5px; }
.badge.y{ background:var(--yellow); color:#1a1a1a; }
.proj{ font-size:11px; color:var(--muted); margin:0 0 14px; }

table{ width:100%; border-collapse:collapse; font-size:11.5px; }
thead th{ background:var(--ink); color:#fff; font-size:10px; text-transform:uppercase; letter-spacing:.6px;
  text-align:left; padding:7px 8px; font-weight:700; }
tbody td{ border-bottom:1px solid var(--line); padding:6px 8px; vertical-align:top; }
tbody tr.cstart td{ border-top:2px solid #cfd4e3; }
.cell-bez{ font-weight:800; white-space:nowrap; }
.typ-pill{ display:inline-block; min-width:26px; text-align:center; background:var(--soft); border:1px solid var(--line);
  border-radius:6px; padding:1px 6px; font-weight:800; font-size:11px; }
.bauteil{ font-weight:600; }
.note{ color:var(--muted); font-size:10.5px; font-style:italic; }
.ref{ color:var(--blue); font-weight:700; font-size:10.5px; }
.ref b{ font-weight:900; }
.adern{ text-align:center; font-weight:800; }
.kabel{ white-space:nowrap; font-weight:600; }
.farbe{ white-space:nowrap; }
.belegung{ color:#33384d; }
.dot{ display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:5px; vertical-align:middle; border:1px solid rgba(0,0,0,.18); }
.empty{ color:#b9bdcc; }
.foot{ margin-top:14px; font-size:10px; color:var(--muted); display:flex; justify-content:space-between; border-top:1px solid var(--line); padding-top:8px; }

.warn{ max-width:1000px; margin:40px auto; background:#fff; border:1px solid var(--line); border-radius:14px; padding:30px; }

@media print{
  @page{ size:A4 portrait; margin:12mm; }
  body{ background:#fff; }
  .bar,.nav{ display:none !important; }
  .wrap{ margin:0; padding:0; max-width:none; }
  .sheet{ border:0; border-radius:0; box-shadow:none; padding:0; margin:0; page-break-after:always; }
  .sheet:last-child{ page-break-after:auto; }
  thead{ display:table-header-group; }
  tr{ page-break-inside:avoid; }
}
</style>
</head>
<body>
<?php if(!$data): ?>
  <div class="warn">
    <h2>Keine Daten gefunden</h2>
    <p>Die Datei <code>weihs/kabelliste.json</code> fehlt. Bitte die Kabelliste neu einlesen.</p>
  </div>
<?php else: ?>
<div class="bar">
  <img class="logo" src="assets/img/logohaustechnikneu.png" alt="OH Haustechnik">
  <div>
    <h1>Kabelzugliste nach Raum</h1>
    <div class="sub"><?= h($data['projekt']) ?> &middot; <?= h($data['stand']) ?> &middot; <?= (int)$data['anzahl_kabel'] ?> Kabel &middot; <?= count($data['rooms']) ?> Räume</div>
  </div>
  <div class="spacer"></div>
  <button class="btn" onclick="window.print()">🖨️ Alle Räume drucken</button>
</div>

<div class="nav">
<?php foreach($floors as $fl=>$rooms){ if(!$rooms) continue; ?>
  <div class="floorlabel"><?= h($fl) ?></div>
  <?php foreach($rooms as $r){ ?>
    <a href="#r-<?= h($r['etage'].'-'.$r['raum']) ?>"><?= h($r['name']) ?></a>
  <?php } ?>
<?php } ?>
</div>

<div class="wrap">
<?php foreach($data['rooms'] as $r): ?>
  <section class="sheet" id="r-<?= h($r['etage'].'-'.$r['raum']) ?>">
    <div class="head">
      <div>
        <div class="room"><?= h($r['name']) ?></div>
      </div>
      <div class="meta">
        <span class="badge"><?= h($r['floor']) ?></span>
        <span class="badge y">Raum <?= h($r['raum']) ?></span><br>
        <?= count($r['cables']) ?> Kabel
      </div>
    </div>
    <p class="proj"><?= h($data['projekt']) ?> &middot; Stand <?= h($data['stand']) ?></p>

    <table>
      <thead>
        <tr>
          <th style="width:7%">Bez.</th>
          <th style="width:30%">Angeschlossenes Bauteil</th>
          <th style="width:16%">Kabeltyp</th>
          <th style="width:6%">Adern</th>
          <th style="width:18%">Farbe</th>
          <th style="width:23%">Belegung</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($r['cables'] as $c):
        $rows = $c['rows']; $n = max(1, count($rows));
      ?>
        <?php for($i=0;$i<$n;$i++): $w = $rows[$i] ?? ['farbe'=>'','belegung'=>'','note'=>'']; ?>
        <tr class="<?= $i===0?'cstart':'' ?>">
          <?php if($i===0): ?>
            <td rowspan="<?= $n ?>" class="cell-bez">
              <span class="typ-pill"><?= h($c['typ']) ?></span><br><?= h($c['nr']) ?>
            </td>
            <td rowspan="<?= $n ?>">
              <div class="bauteil"><?= h($c['desc']) !== '' ? h($c['desc']) : '<span class="empty">–</span>' ?></div>
              <?php foreach($c['refs'] as $rf): if(!empty($rf['ziel'])): ?>
                <div class="ref">↳ aus <b><?= h($rf['ziel']) ?></b> <span class="note">(<?= h($rf['src']) ?>)</span></div>
              <?php else: ?>
                <div class="ref note"><?= h($rf['src']) ?></div>
              <?php endif; endforeach; ?>
            </td>
            <td rowspan="<?= $n ?>" class="kabel"><?= h($c['kabeltyp']) !== '' ? h($c['kabeltyp']) : '<span class="empty">–</span>' ?></td>
            <td rowspan="<?= $n ?>" class="adern"><?= h($c['anzahl']) !== '' ? h($c['anzahl']) : '' ?></td>
          <?php endif; ?>
          <td class="farbe">
            <?php $col = oh_color($w['farbe']); if($w['farbe']!==''): ?>
              <?php if($col): ?><span class="dot" style="background:<?= h($col) ?>"></span><?php endif; ?><?= h($w['farbe']) ?>
            <?php endif; ?>
          </td>
          <td class="belegung">
            <?= h($w['belegung']) ?>
            <?php if(!empty($w['note']) && stripos($w['note'],'von ')!==0): ?>
              <div class="note"><?= h($w['note']) ?></div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endfor; ?>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="foot">
      <span>OH Haustechnik &middot; Kabelzugliste nach Raum</span>
      <span><?= h($r['floor']) ?> / <?= h($r['name']) ?></span>
    </div>
  </section>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
function oh_color($f){
  $f = mb_strtolower(trim($f));
  $map = [
    'braun'=>'#7a4a1e','blau'=>'#2b66d8','grün'=>'#2faa48','gelb/ grün'=>'#cfd400','gelb/grün'=>'#cfd400',
    'gelb'=>'#f5c400','grau'=>'#9aa0ad','schwarz'=>'#1a1a1a','weiß'=>'#ffffff','rot'=>'#d83a3a','orange'=>'#f08a26',
    'rosa'=>'#e87fb0','violett'=>'#8a52c9','türkis'=>'#1fb9b0',
  ];
  if(isset($map[$f])) return $map[$f];
  // "weiß (grün)" -> weiß base
  foreach($map as $k=>$v){ if(strpos($f,$k)===0) return $v; }
  return '';
}
?>
</body>
</html>
