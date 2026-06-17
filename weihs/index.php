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

// Typ-Kuerzel -> ausgeschriebene Bezeichnung. Eindeutige aus den Beschreibungen
// abgeleitet (sicher). Reine Tree-Bus-Knoten ohne Klartext => 'unsicher'=>true.
$TYP = [
  'L'    =>['Beleuchtung', false],
  'S'    =>['Steckdose', false],
  'K'    =>['Fenster-/Türkontakt', false],
  'N'    =>['Netzwerk', false],
  'HKV'  =>['Heizkreisverteiler', false],
  'FL'   =>['Feuchtraumlüfter', false],
  'W'    =>['Wassermelder', false],
  'H'    =>['Handtuchheizkörper (Steckdose)', false],
  'Mo'   =>['Motor / Antrieb', false],
  'Gong' =>['Türgong', false],
  'B(a)' =>['Bewegungsmelder außen', false],
  'Ro'   =>['Rollladen / Beschattung', false],
  'NFC'  =>['NFC-Zutrittsleser', false],
  'Alarm'=>['Alarmsirene', false],
  'Wetter'=>['Wetterstation', false],
  'So'   =>['Funk-Komponente (kein Kabel)', false],
  // Tree-Bus-Knoten – exakte Gerätebezeichnung noch ungeklärt (keine Legende im PDF)
  'PR'   =>['Tree-Bus-Komponente', true],
  'T'    =>['Tree-Bus-Komponente', true],
  'Pa'   =>['Tree-Bus-Komponente', true],
  'Pe'   =>['Tree-Bus-Komponente', true],
  'TP'   =>['Touch Pure (Tree)', false],
  'TtA'  =>['Tree-to-Air-Bridge (Tree)', false],
  'Tta'  =>['Tree-to-Air-Bridge (Tree)', false],
  'TN'   =>['Tree-Bus-Komponente', true],
];
function typname($t, $TYP){ return $TYP[$t] ?? [$t, false]; }
// Bauteil-Bezeichnung (Code wie auf dem Plan, z.B. S00.02.05) fuer JEDES Bauteil
function bauteilcode($c){
  $aa=['-01'=>'01','00'=>'00','01'=>'01','02'=>'02','03'=>'03'];
  $code=$c['typ'].($aa[$c['etage']] ?? '00').'.'.sprintf('%02d',(int)$c['raum']);
  if(trim((string)$c['nr'])!=='') $code.='.'.$c['nr'];
  return $code;
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
.cell-bez{ font-weight:800; }
.typ-full{ font-weight:800; font-size:12px; line-height:1.2; }
.typ-full.uncertain{ color:#9a6b00; }
.typ-full span[title]{ cursor:help; }
.bez-sub{ margin-top:4px; }
.code-pill{ display:inline-block; font-family:ui-monospace,Menlo,Consolas,monospace; font-weight:800; font-size:11px; background:#13142a; color:#fff; border-radius:5px; padding:2px 7px; letter-spacing:.3px; }
.typ-pill{ display:inline-block; min-width:22px; text-align:center; background:var(--soft); border:1px solid var(--line); border-radius:6px; padding:1px 5px; font-weight:800; font-size:10.5px; color:var(--ink); }
/* Legende */
.legend{ background:#fff; border:1px solid var(--line); border-radius:12px; padding:16px 18px; margin-bottom:22px; }
.legend h3{ margin:0 0 10px; font-size:13px; font-weight:800; }
.legend .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:6px 18px; font-size:12px; }
.legend .row{ display:flex; gap:8px; align-items:baseline; }
.legend .k{ font-weight:800; min-width:34px; }
.legend .u{ color:#9a6b00; }
.legend .hint{ margin-top:10px; font-size:11.5px; color:#9a6b00; background:#fff8e6; border:1px solid #ffe6a3; border-radius:8px; padding:8px 10px; }
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

/* Accordion */
.acc-tools{ margin-bottom:12px; }
.floors{ display:flex; flex-direction:column; gap:12px; }
.floor{ background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; box-shadow:0 6px 22px rgba(20,20,40,.05); }
.floor-head{ width:100%; border:0; background:#fff; cursor:pointer; display:flex; align-items:center; gap:12px; padding:16px 18px; text-align:left; }
.floor-head:hover{ background:var(--soft); }
.fh-name{ font-size:20px; font-weight:900; }
.fh-meta{ font-size:12px; color:var(--muted); font-weight:600; }
.floor .acc-body{ display:none; padding:0 14px 14px; }
.floor.open > .acc-body{ display:block; }

.room{ border:1px solid var(--line); border-radius:11px; overflow:hidden; margin-top:10px; }
.room-head{ display:flex; align-items:center; gap:10px; padding:12px 14px; cursor:pointer; background:var(--soft); }
.room-head:hover{ background:#eef1f8; }
.rh-name{ font-weight:800; font-size:15px; }
.rh-badge{ font-size:11px; color:var(--muted); font-weight:600; }
.rh-print{ border:1px solid var(--line); background:#fff; border-radius:8px; cursor:pointer; font-size:15px; padding:4px 9px; line-height:1; }
.rh-print:hover{ border-color:var(--blue); }
.room .acc-body{ display:none; padding:0; background:#fff; }
.room.open > .acc-body{ display:block; }
.sheet-inner{ padding:16px 16px 18px; }
.proj-line{ font-size:11px; color:var(--muted); margin-bottom:12px; }
.room.alldone .room-head{ background:#eafaef; }
.room.alldone .rh-name::after{ content:' ✓'; color:#2faa48; }

.caret{ display:inline-block; transition:transform .15s; color:var(--muted); font-size:13px; }
.floor.open > .floor-head .caret, .room.open > .room-head .caret{ transform:rotate(90deg); }
.prog{ margin-left:auto; font-size:12px; font-weight:800; color:var(--blue); background:#eef3ff; border-radius:999px; padding:3px 10px; min-width:46px; text-align:center; }
.room-head .prog{ margin-left:auto; }
.room-head .rh-print{ margin-left:8px; }

/* Checkbox-Spalte + erledigt */
.col-chk{ width:34px; text-align:center; }
.col-ad{ width:5%; }
.chk{ width:18px; height:18px; cursor:pointer; accent-color:var(--blue); }
tr.done td{ color:#aab; text-decoration:line-through; text-decoration-color:#cfd4e3; }
tr.done .typ-pill, tr.done .dot{ opacity:.4; }
.bridge{ display:inline-block; margin-top:5px; font-size:10.5px; font-weight:700; color:#7a4a00; background:#fff3d6; border:1px solid #f0d693; border-radius:7px; padding:3px 8px; }
.rf-l{ color:var(--muted); font-weight:600; }
.rf-arrow{ color:var(--blue); font-weight:900; }
/* Von -> Nach Endpunkte */
.vn{ margin-top:7px; border:1px solid #d7e0f5; border-radius:8px; background:#f5f8ff; padding:7px 9px; }
.vn-kind{ font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.7px; color:var(--blue); margin-bottom:4px; }
.vn-row{ display:flex; align-items:baseline; gap:7px; font-size:11.5px; padding:1px 0; }
.vn-k{ font-weight:800; min-width:38px; color:#2faa48; }
.vn-k.vn-k2{ color:#d83a3a; }
.vn-room{ font-weight:700; }
.vn-code{ margin-left:auto; font-family:ui-monospace,Menlo,Consolas,monospace; font-weight:700; font-size:11px; background:#fff; border:1px solid var(--line); border-radius:5px; padding:1px 6px; white-space:nowrap; }

/* Viewer */
.modal{ position:fixed; inset:0; background:rgba(10,12,25,.72); display:none; z-index:100; padding:24px; }
.modal.on{ display:flex; flex-direction:column; }
.modal .top{ display:flex; align-items:center; gap:14px; color:#fff; padding:4px 4px 12px; }
.modal .top .t{ font-weight:700; font-size:14px; flex:1; }
.modal .x{ background:#fff; border:0; border-radius:9px; padding:8px 14px; font-weight:800; cursor:pointer; }
.modal iframe{ flex:1; width:100%; border:0; border-radius:12px; background:#fff; }

@media print{
  @page{ size:A4 portrait; margin:11mm; }
  body{ background:#fff; }
  .bar,.nav,#dateien,.modal,.legend,.acc-tools,.floor-head,.caret,.rh-print,.prog,.section-title{ display:none !important; }
  .wrap{ margin:0; padding:0; max-width:none; }
  .floors,.floor,.floor .acc-body,.room .acc-body{ display:block !important; }
  .floor{ border:0; border-radius:0; box-shadow:none; }
  .floor .acc-body{ padding:0; }
  .room{ border:0; border-radius:0; margin:0; page-break-after:always; page-break-inside:auto; }
  .room:last-child{ page-break-after:auto; }
  .room-head{ background:#fff !important; padding:0 0 6px; border-bottom:3px solid var(--ink); margin-bottom:6px; }
  .rh-name{ font-size:24px; font-weight:900; }
  .rh-badge{ font-size:13px; }
  .sheet-inner{ padding:0; }
  thead{ display:table-header-group; }
  tr{ page-break-inside:avoid; }
  .col-chk{ width:30px; }
  .chk{ -webkit-appearance:none; appearance:none; width:14px; height:14px; border:1.5px solid #333; border-radius:3px; }
  .chk:checked{ background:#333; }
  /* Nur einen Raum drucken */
  body.print-one .room{ display:none !important; }
  body.print-one .room.print-target{ display:block !important; page-break-after:auto; }
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
  <a class="btn" href="plan.php" style="background:var(--blue);color:#fff;text-decoration:none">🗺️ Interaktiver Plan</a>
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

  <div class="legend">
    <h3>Legende – Bezeichnungen</h3>
    <div class="grid">
    <?php foreach($TYP as $k=>$v): ?>
      <div class="row"><span class="k"><?= h($k) ?></span><span class="<?= $v[1]?'u':'' ?>"><?= h($v[0]) ?><?= $v[1]?' ⚠':'' ?></span></div>
    <?php endforeach; ?>
    </div>
    <div class="hint">⚠ = Tree-Bus-Komponente: das CAT7-Kabel des Loxone-Tree-Bus. Aus der Plan-Legende bestätigt: <b>TP = Touch Pure</b>, <b>TtA = Tree-to-Air-Bridge</b>. Noch zu bestätigen (PR, T, Pa, Pe, TN) – starke Hinweise vom Plan (z.&nbsp;B. Pe = Präsenzmelder, T = Touch, TN = Nano Relay), final bitte vom Planer.</div>
  </div>

<?php if(!$data): ?>
  <p>Die Datei <code>weihs/kabelliste.json</code> fehlt – Kabelliste kann nicht angezeigt werden.</p>
<?php else: ?>
<div class="acc-tools">
  <button type="button" class="btn ghost" id="expandAll" onclick="toggleAll()">⊕ Alle aufklappen</button>
</div>
<div class="floors">
<?php foreach($floors as $flname=>$rooms): if(!$rooms) continue;
  $fcables = array_sum(array_map(function($x){ return count($x['cables']); }, $rooms));
  $fcode = $rooms[0]['etage'];
?>
  <section class="floor" data-floor="<?= h($fcode) ?>">
    <button type="button" class="floor-head" onclick="toggleAcc(this)">
      <span class="caret">▸</span>
      <span class="fh-name"><?= h($flname) ?></span>
      <span class="fh-meta"><?= count($rooms) ?> Räume · <?= (int)$fcables ?> Kabel</span>
      <span class="prog" data-floor-prog="<?= h($fcode) ?>"></span>
    </button>
    <div class="acc-body">
    <?php foreach($rooms as $r): $rid=$r['etage'].'-'.$r['raum']; ?>
      <section class="room" id="r-<?= h($rid) ?>" data-room="<?= h($rid) ?>">
        <div class="room-head" onclick="toggleAcc(this)">
          <span class="caret">▸</span>
          <span class="rh-name"><?= h($r['name']) ?></span>
          <span class="rh-badge">Raum <?= h($r['raum']) ?> · <?= count($r['cables']) ?> Kabel</span>
          <span class="prog" data-room-prog="<?= h($rid) ?>"></span>
          <button type="button" class="rh-print" title="Nur diesen Raum drucken" onclick="printRoom(event,'<?= h($rid) ?>')">🖨️</button>
        </div>
        <div class="acc-body room-body">
          <div class="sheet-inner">
            <div class="proj-line"><?= h($data['projekt']) ?> · Stand <?= h($data['stand']) ?> · <b><?= h($r['floor']) ?> – <?= h($r['name']) ?></b></div>
            <table>
              <thead><tr>
                <th class="col-chk">✓</th>
                <th style="width:16%">Bezeichnung</th><th style="width:23%">Angeschlossenes Bauteil</th>
                <th style="width:14%">Kabeltyp</th><th class="col-ad">Adern</th>
                <th style="width:16%">Farbe</th><th style="width:21%">Belegung</th>
              </tr></thead>
              <tbody>
              <?php foreach($r['cables'] as $ci=>$c): $rows=$c['rows']; $n=max(1,count($rows)); $cid=$r['etage'].'_'.$r['raum'].'_'.$ci; ?>
                <?php for($i=0;$i<$n;$i++): $w=$rows[$i]??['farbe'=>'','belegung'=>'','note'=>'']; ?>
                <tr data-cb="<?= h($cid) ?>" class="<?= $i===0?'cstart':'' ?>">
                  <?php if($i===0): ?>
                    <td rowspan="<?= $n ?>" class="col-chk"><input type="checkbox" class="chk" data-cid="<?= h($cid) ?>"></td>
                    <td rowspan="<?= $n ?>" class="cell-bez"><?php $tn=typname($c['typ'],$TYP); ?>
                      <div class="typ-full<?= $tn[1]?' uncertain':'' ?>"><?= h($tn[0]) ?><?= $tn[1]?' <span title="Genaue Tree-Bus-Gerätebezeichnung noch zu klären">⚠</span>':'' ?></div>
                      <div class="bez-sub"><span class="code-pill"><?= h(bauteilcode($c)) ?></span></div>
                    </td>
                    <td rowspan="<?= $n ?>">
                      <div class="bauteil"><?= h($c['desc'])!==''? h($c['desc']) : '<span class="empty">–</span>' ?></div>
                      <?php $bnotes=trim(implode(' ',array_map(function($x){return $x['note'];},$c['rows']))); if(preg_match('/miteinander|verbinden/i',$bnotes)): ?>
                        <div class="bridge">⛓ durchgeschleift – <b>1 Zuleitung</b>, <?= stripos($c['desc'],'leucht')!==false?'Leuchten':'Steckdosen' ?> gebrückt<?php if(preg_match('/(\d)-adrig/',$bnotes,$mm)): ?> · <?= h($mm[1]) ?>-adrig<?php endif; ?></div>
                      <?php endif; ?>
                      <?php if(!empty($c['endp'])): $e=$c['endp']; ?>
                        <div class="vn">
                          <div class="vn-kind"><?= h($e['kind']) ?></div>
                          <div class="vn-row"><span class="vn-k">Von</span>
                            <span class="vn-room"><?= $e['von']? h($e['von']['room']) : '—' ?></span>
                            <?php if($e['von'] && $e['von']['code']!=='—'): ?><span class="vn-code"><?= h($e['von']['code']) ?></span><?php endif; ?>
                          </div>
                          <div class="vn-row"><span class="vn-k vn-k2">Nach</span>
                            <span class="vn-room"><?= $e['nach']? h($e['nach']['room']) : '—' ?></span>
                            <?php if($e['nach'] && $e['nach']['code']!=='—'): ?><span class="vn-code"><?= h($e['nach']['code']) ?></span><?php endif; ?>
                          </div>
                        </div>
                      <?php endif; ?>
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
            <div class="foot"><span>OH Haustechnik · Kabelzugliste nach Raum</span><span><?= h($r['floor']) ?> / <?= h($r['name']) ?></span></div>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
</div>
<?php endif; ?>

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

// ---- Aufklappen ----
function toggleAcc(el){ el.closest('.floor,.room').classList.toggle('open'); }
function toggleAll(){
  const btn=document.getElementById('expandAll');
  const open = btn.dataset.open!=='1';
  document.querySelectorAll('.floor,.room').forEach(s=>s.classList.toggle('open',open));
  btn.dataset.open = open?'1':'0';
  btn.textContent = open ? '⊖ Alle zuklappen' : '⊕ Alle aufklappen';
}
function openTo(id){
  const room=document.getElementById(id); if(!room) return;
  const fl=room.closest('.floor'); if(fl) fl.classList.add('open');
  room.classList.add('open');
  room.scrollIntoView({behavior:'smooth', block:'start'});
}
document.querySelectorAll('.nav a[href^="#r-"]').forEach(a=>{
  a.addEventListener('click',e=>{ e.preventDefault(); openTo(a.getAttribute('href').slice(1)); history.replaceState(null,'',a.getAttribute('href')); });
});

// ---- Abhaken (im Browser/App gespeichert) ----
const CHK_KEY='weihs_kabel_checked_v1';
let checked = new Set();
try{ checked = new Set(JSON.parse(localStorage.getItem(CHK_KEY)||'[]')); }catch(e){}
function rowsFor(cid){ return document.querySelectorAll('[data-cb="'+CSS.escape(cid)+'"]'); }
function applyChecks(){
  document.querySelectorAll('.chk').forEach(cb=>{
    const on=checked.has(cb.dataset.cid);
    cb.checked=on;
    rowsFor(cb.dataset.cid).forEach(tr=>tr.classList.toggle('done',on));
  });
  updateProgress();
}
function updateProgress(){
  document.querySelectorAll('.room').forEach(room=>{
    const cbs=room.querySelectorAll('.chk'); const done=[...cbs].filter(c=>c.checked).length;
    const el=room.querySelector('[data-room-prog]'); if(el) el.textContent=done+'/'+cbs.length;
    room.classList.toggle('alldone', cbs.length>0 && done===cbs.length);
  });
  document.querySelectorAll('.floor').forEach(fl=>{
    const cbs=fl.querySelectorAll('.chk'); const done=[...cbs].filter(c=>c.checked).length;
    const el=fl.querySelector('[data-floor-prog]'); if(el) el.textContent=done+'/'+cbs.length;
  });
}
document.addEventListener('change',e=>{
  if(!e.target.classList.contains('chk')) return;
  const cb=e.target;
  if(cb.checked) checked.add(cb.dataset.cid); else checked.delete(cb.dataset.cid);
  localStorage.setItem(CHK_KEY, JSON.stringify([...checked]));
  rowsFor(cb.dataset.cid).forEach(tr=>tr.classList.toggle('done',cb.checked));
  updateProgress();
});

// ---- Druck pro Raum ----
function printRoom(ev,id){
  ev.stopPropagation();
  document.querySelectorAll('.room.print-target').forEach(r=>r.classList.remove('print-target'));
  const room=document.getElementById(id); if(!room) return;
  room.classList.add('print-target');
  document.body.classList.add('print-one');
  window.print();
}
window.addEventListener('afterprint',()=>{
  document.body.classList.remove('print-one');
  document.querySelectorAll('.room.print-target').forEach(r=>r.classList.remove('print-target'));
});

applyChecks();
if(location.hash.startsWith('#r-')) openTo(location.hash.slice(1));
</script>
</body>
</html>
