<?php
/* =========================================================================
 * OH Haustechnik – Privater Kundenbereich (360°-Baudokumentation)
 * ========================================================================= */
session_start();
header('X-Robots-Tag: noindex, nofollow, noarchive');

/* ---- HIER das Kundenpasswort setzen ---- */
$KUNDEN_PASSWORT = 'weihs2026';
$PROJEKT_TITEL   = 'Familie Weihs · Nürnberg';

if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: kunde.php'); exit; }
$err = false;
if (isset($_POST['pw'])) {
    if (hash_equals($KUNDEN_PASSWORT, (string)$_POST['pw'])) { $_SESSION['kunde_ok'] = true; header('Location: kunde.php'); exit; }
    $err = true;
}
$eingeloggt = !empty($_SESSION['kunde_ok']);
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$data = is_file(__DIR__.'/kabelliste.json') ? json_decode(file_get_contents(__DIR__.'/kabelliste.json'), true) : null;
$FLOORAA=['-01'=>'01','00'=>'00','01'=>'01','02'=>'02','03'=>'03'];
function bcode($c){ global $FLOORAA; $s=$c['typ'].($FLOORAA[$c['etage']]??'00').'.'.sprintf('%02d',(int)$c['raum']); if(trim((string)$c['nr'])!=='') $s.='.'.$c['nr']; return $s; }
function panorama($etage,$raum){
    $base=__DIR__.'/360/'.$etage.'-'.sprintf('%02d',(int)$raum);
    foreach(['jpg','jpeg','png','JPG'] as $ext){ if(is_file($base.'.'.$ext)) return '360/'.$etage.'-'.sprintf('%02d',(int)$raum).'.'.$ext; }
    return null;
}
$floors=['EG'=>[],'1. OG'=>[],'DG'=>[],'KG'=>[],'Außenbereich'=>[]];
if($data) foreach($data['rooms'] as $r){ if(isset($floors[$r['floor']])) $floors[$r['floor']][]=$r; }
$FLABEL=['KG'=>'Kellergeschoss','EG'=>'Erdgeschoss','1. OG'=>'1. Obergeschoss','DG'=>'Dachgeschoss / 2. OG','Außenbereich'=>'Außenbereich'];
?><!DOCTYPE html><html lang="de"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>OH Haustechnik · Ihre Baudokumentation</title>
<?php if($eingeloggt): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pannellum/2.5.6/pannellum.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pannellum/2.5.6/pannellum.js"></script>
<?php endif; ?>
<style>
:root{ --bg:#0b0d1a; --card:#161a33; --line:#252a4a; --txt:#eef0fa; --mut:#9aa0c0; --blue:#5b8cff; --gold:#FFD400; }
*{ box-sizing:border-box; }
body{ margin:0; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; color:var(--txt); background:radial-gradient(1200px 700px at 70% -10%, #1b2148 0%, transparent 60%), var(--bg); min-height:100vh; }
a{ color:inherit; }
.logo{ height:40px; background:#fff; border-radius:10px; padding:5px 8px; }
.login-wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
.login{ width:min(420px,94vw); background:linear-gradient(180deg,#1a1f3d,#141830); border:1px solid var(--line); border-radius:22px; padding:34px 30px; box-shadow:0 30px 80px rgba(0,0,0,.5); text-align:center; }
.login .logo{ height:52px; margin-bottom:18px; }
.login h1{ font-size:21px; margin:0 0 4px; font-weight:900; }
.login .p{ color:var(--mut); font-size:13px; margin:0 0 22px; }
.login input{ width:100%; background:#0e1128; border:1px solid var(--line); color:#fff; font-size:16px; border-radius:12px; padding:14px 16px; text-align:center; letter-spacing:1px; }
.login input:focus{ outline:none; border-color:var(--blue); }
.login button{ width:100%; margin-top:12px; background:linear-gradient(90deg,#5b8cff,#7b5bff); color:#fff; border:0; font-weight:800; font-size:15px; border-radius:12px; padding:14px; cursor:pointer; }
.login .err{ color:#ff7a7a; font-size:13px; margin-top:12px; }
.login .foot{ color:var(--mut); font-size:11px; margin-top:20px; }
.top{ position:sticky; top:0; z-index:30; display:flex; align-items:center; gap:14px; padding:14px 20px; background:rgba(11,13,26,.82); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); }
.top h1{ font-size:15px; margin:0; font-weight:800; }
.top .sub{ font-size:11px; color:var(--mut); }
.top .sp{ flex:1; }
.top .out{ font-size:12px; color:var(--mut); text-decoration:none; border:1px solid var(--line); border-radius:8px; padding:7px 11px; }
.hero{ max-width:1000px; margin:26px auto 6px; padding:0 18px; }
.hero .eyebrow{ color:var(--gold); font-weight:800; font-size:12px; letter-spacing:2px; text-transform:uppercase; }
.hero h2{ font-size:26px; margin:6px 0 6px; font-weight:900; }
.hero p{ color:var(--mut); font-size:13.5px; margin:0; max-width:640px; }
.wrap{ max-width:1000px; margin:18px auto 60px; padding:0 18px; }
.floor{ margin-bottom:14px; border:1px solid var(--line); border-radius:18px; overflow:hidden; background:linear-gradient(180deg,#141833,#111428); }
.floor-h{ width:100%; text-align:left; border:0; background:transparent; color:var(--txt); cursor:pointer; display:flex; align-items:center; gap:14px; padding:18px 20px; }
.floor-h:hover{ background:rgba(91,140,255,.06); }
.floor-h .num{ width:42px; height:42px; border-radius:12px; background:#0e1128; border:1px solid var(--line); display:flex; align-items:center; justify-content:center; font-weight:900; color:var(--blue); font-size:14px; }
.floor-h .t{ font-size:17px; font-weight:800; }
.floor-h .c{ font-size:11.5px; color:var(--mut); }
.floor-h .car{ margin-left:auto; color:var(--mut); transition:transform .2s; }
.floor.open .car{ transform:rotate(90deg); }
.rooms{ display:none; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:12px; padding:6px 16px 18px; }
.floor.open .rooms{ display:grid; }
.room{ border:1px solid var(--line); border-radius:14px; overflow:hidden; cursor:pointer; background:#0e1128; transition:.15s; position:relative; }
.room:hover{ transform:translateY(-2px); border-color:var(--blue); box-shadow:0 12px 30px rgba(0,0,0,.4); }
.room .thumb{ height:118px; background:#1a1f3d center/cover no-repeat; display:flex; align-items:center; justify-content:center; position:relative; }
.room .thumb .ph{ color:var(--mut); font-size:12px; text-align:center; padding:10px; }
.room .badge360{ position:absolute; top:8px; left:8px; background:rgba(91,140,255,.9); color:#fff; font-size:10px; font-weight:800; padding:3px 8px; border-radius:999px; }
.room .soon{ position:absolute; top:8px; left:8px; background:rgba(255,255,255,.14); color:#fff; font-size:10px; font-weight:700; padding:3px 8px; border-radius:999px; }
.room .meta{ padding:11px 12px; }
.room .rn{ font-weight:800; font-size:14px; }
.room .rc{ color:var(--mut); font-size:11px; margin-top:2px; }
.room .play{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
.room .play span{ width:44px; height:44px; border-radius:50%; background:rgba(0,0,0,.45); border:1px solid rgba(255,255,255,.5); display:flex; align-items:center; justify-content:center; font-size:16px; }
.viewer{ position:fixed; inset:0; z-index:80; background:#05060f; display:none; flex-direction:column; }
.viewer.on{ display:flex; }
.viewer .vtop{ display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(11,13,26,.9); border-bottom:1px solid var(--line); }
.viewer .vtop .vn{ font-weight:800; font-size:15px; }
.viewer .vtop .vf{ font-size:11px; color:var(--mut); }
.viewer .vtop .x{ margin-left:auto; background:#fff; color:#111; border:0; border-radius:9px; padding:9px 14px; font-weight:800; cursor:pointer; }
.viewer .stage{ flex:1; position:relative; }
#pano{ position:absolute; inset:0; }
.nopano{ position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--mut); gap:8px; text-align:center; padding:20px; }
.docbar{ background:rgba(11,13,26,.95); border-top:1px solid var(--line); max-height:42vh; overflow:auto; }
.docbar .dh{ display:flex; align-items:center; gap:10px; padding:10px 16px; cursor:pointer; font-weight:800; font-size:13px; }
.docbar .dh .cnt{ color:var(--mut); font-weight:600; font-size:11px; }
.docbar table{ width:100%; border-collapse:collapse; font-size:11.5px; display:none; }
.docbar.open table{ display:table; }
.docbar th{ text-align:left; color:var(--mut); font-weight:700; padding:6px 16px; font-size:10px; text-transform:uppercase; position:sticky; top:0; background:#0e1128; }
.docbar td{ padding:6px 16px; border-top:1px solid var(--line); }
.docbar .code{ font-family:ui-monospace,Menlo,monospace; color:var(--blue); font-weight:700; }
</style></head>
<body>
<?php if(!$eingeloggt): ?>
<div class="login-wrap">
  <form class="login" method="post" autocomplete="off">
    <img class="logo" src="../assets/img/logohaustechnikneu.png" alt="OH Haustechnik">
    <h1>Ihre Baudokumentation</h1>
    <p class="p"><?= h($PROJEKT_TITEL) ?></p>
    <input type="password" name="pw" placeholder="Passwort eingeben" autofocus>
    <button type="submit">Zugang öffnen</button>
    <?php if($err): ?><div class="err">Passwort falsch – bitte erneut versuchen.</div><?php endif; ?>
    <div class="foot">🔒 Privater Bereich · nur für den Eigentümer · OH Haustechnik</div>
  </form>
</div>
<?php else: ?>
<div class="top">
  <img class="logo" src="../assets/img/logohaustechnikneu.png" alt="OH" style="height:30px">
  <div><h1>Ihre Baudokumentation</h1><div class="sub"><?= h($PROJEKT_TITEL) ?></div></div>
  <div class="sp"></div>
  <a class="out" href="?logout=1">Abmelden</a>
</div>
<div class="hero">
  <div class="eyebrow">360° Rundgang · Elektroinstallation</div>
  <h2>Ihr Zuhause – jede Leitung dokumentiert</h2>
  <p>Öffnen Sie eine Etage, wählen Sie einen Raum und drehen Sie sich im 360°-Rundgang um. Zu jedem Raum finden Sie die vollständige Dokumentation der verlegten Leitungen.</p>
</div>
<div class="wrap">
<?php $i=0; foreach($floors as $fl=>$rooms){ if(!$rooms) continue; $i++;
  $withpano=0; foreach($rooms as $r){ if(panorama($r['etage'],$r['raum'])) $withpano++; } ?>
  <div class="floor<?= $i===1?' open':'' ?>">
    <button class="floor-h" onclick="this.parentElement.classList.toggle('open')">
      <span class="num"><?= h($fl) ?></span>
      <span><span class="t"><?= h($FLABEL[$fl]) ?></span><br><span class="c"><?= count($rooms) ?> Räume · <?= $withpano ?>× 360°</span></span>
      <span class="car">▸</span>
    </button>
    <div class="rooms">
    <?php foreach($rooms as $r){ $p=panorama($r['etage'],$r['raum']); $kab=count($r['cables']); ?>
      <div class="room" onclick='openRoom(<?= json_encode(["name"=>$r['name'],"floor"=>$FLABEL[$fl],"pano"=>$p,"cables"=>array_map(function($c){ return ["code"=>bcode($c),"desc"=>preg_replace("/^(Zuleitung|Verbindung|Ableitung)\s+/u","",$c["desc"]),"kabel"=>$c["kabeltyp"]]; }, $r['cables'])], JSON_UNESCAPED_UNICODE|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
        <div class="thumb" <?= $p?'style="background-image:url('.h($p).')"':'' ?>>
          <?php if($p): ?><span class="badge360">360°</span><div class="play"><span>▶</span></div>
          <?php else: ?><span class="soon">360° folgt</span><span class="ph">Rundgang wird<br>noch hochgeladen</span><?php endif; ?>
        </div>
        <div class="meta"><div class="rn"><?= h($r['name']) ?></div><div class="rc"><?= $kab ?> Leitungen dokumentiert</div></div>
      </div>
    <?php } ?>
    </div>
  </div>
<?php } ?>
  <p style="color:var(--mut);font-size:11.5px;text-align:center;margin-top:26px">🔒 Privater Bereich · erstellt von OH Haustechnik · Alle Angaben aus dem geprüften Installationsplan.</p>
</div>
<div class="viewer" id="viewer">
  <div class="vtop"><div><div class="vn" id="vName"></div><div class="vf" id="vFloor"></div></div><button class="x" onclick="closeRoom()">Schließen ✕</button></div>
  <div class="stage"><div id="pano"></div><div class="nopano" id="nopano" style="display:none"><div style="font-size:32px">📷</div><div>360°-Rundgang für diesen Raum folgt in Kürze.</div></div></div>
  <div class="docbar" id="docbar">
    <div class="dh" onclick="document.getElementById('docbar').classList.toggle('open')">🔌 Verlegte Leitungen <span class="cnt" id="vCnt"></span> <span style="margin-left:auto;color:var(--mut)">▾</span></div>
    <table><thead><tr><th style="width:32%">Bezeichnung</th><th>Bauteil</th><th style="width:26%">Kabeltyp</th></tr></thead><tbody id="vRows"></tbody></table>
  </div>
</div>
<script>
let viewer=null;
function openRoom(d){
  document.getElementById('vName').textContent=d.name;
  document.getElementById('vFloor').textContent=d.floor;
  document.getElementById('vCnt').textContent='('+d.cables.length+')';
  document.getElementById('vRows').innerHTML=d.cables.map(c=>`<tr><td class="code">${c.code}</td><td>${c.desc||'–'}</td><td>${c.kabel||'–'}</td></tr>`).join('');
  document.getElementById('viewer').classList.add('on');
  document.body.style.overflow='hidden';
  const pano=document.getElementById('pano'), no=document.getElementById('nopano');
  if(viewer){ try{viewer.destroy();}catch(e){} viewer=null; }
  pano.innerHTML='';
  if(d.pano && window.pannellum){ no.style.display='none'; pano.style.display='block'; viewer=pannellum.viewer('pano',{type:'equirectangular',panorama:d.pano,autoLoad:true,showControls:true,autoRotate:-2,compass:false,hfov:100}); }
  else { pano.style.display='none'; no.style.display='flex'; }
}
function closeRoom(){ document.getElementById('viewer').classList.remove('on'); document.body.style.overflow=''; if(viewer){ try{viewer.destroy();}catch(e){} viewer=null; } }
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeRoom(); });
</script>
<?php endif; ?>
</body></html>
