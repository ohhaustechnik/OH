<?php
session_start();
$PASSWORT = 'oh';

// API-Key serverseitig - HIER DEINEN KEY EINTRAGEN nach dem Hochladen
$API_KEY = getenv('CLAUDE_KEY') ?: '';

// Login-Logik
if (isset($_POST['login_pw'])) {
    if ($_POST['login_pw'] === $PASSWORT) {
        $_SESSION['eingeloggt'] = true;
    } else {
        $login_fehler = true;
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// API-Proxy fuer Kalkulation
if (isset($_POST['kalk_request']) && !empty($_SESSION['eingeloggt'])) {
    header('Content-Type: application/json');
    $userKey = $_POST['api_key'] ?? $API_KEY;
    $body = $_POST['kalk_request'];
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $userKey,
        'anthropic-version: 2023-06-01'
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        echo json_encode(['error' => ['message' => curl_error($ch)]]);
    } else {
        echo $response;
    }
    curl_close($ch);
    exit;
}

$eingeloggt = !empty($_SESSION['eingeloggt']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="OH Büro">
<meta name="robots" content="noindex, nofollow">
<title>OH Haustechnik · Büro</title>
<style>
:root{--blau:#2e5c8a;--blau-d:#1c3d6b;--blau-dd:#142d50;--gruen:#2e8b57;--grau:#6b7280;--linie:#e5e7eb;--bg:#0f1d33;--karte:#fff;--text:#1a2330;--gold:#c8973f;}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
body{font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding-bottom:40px;}
.wrap{max-width:520px;margin:0 auto;}
header{background:linear-gradient(160deg,var(--blau-dd),var(--blau));color:#fff;padding:22px 18px 16px;padding-top:calc(22px + env(safe-area-inset-top));position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 14px rgba(0,0,0,.3);}
.logo{font-size:24px;font-weight:300;letter-spacing:7px;}
.logo-sub{font-size:9px;letter-spacing:3px;opacity:.85;margin-top:3px;}
.hbtns{display:flex;gap:8px;}
.icobtn{background:rgba(255,255,255,.15);border:none;color:#fff;font-size:17px;width:40px;height:40px;border-radius:20px;cursor:pointer;}
.card{background:var(--karte);border-radius:18px;padding:20px 17px;margin:14px;box-shadow:0 2px 10px rgba(0,0,0,.15);}
h2{font-size:16px;font-weight:700;color:var(--blau-d);margin-bottom:9px;}
.intro{font-size:13px;color:var(--grau);margin-bottom:13px;line-height:1.55;}
label{display:block;font-size:13px;font-weight:600;color:var(--grau);margin:15px 0 6px;}
textarea,input,select{width:100%;padding:14px;border:1.5px solid var(--linie);border-radius:13px;font-size:16px;font-family:inherit;background:#fff;color:var(--text);outline:none;}
textarea{min-height:115px;resize:vertical;}
textarea:focus,input:focus,select:focus{border-color:var(--blau);}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.chip{padding:10px 17px;border-radius:21px;border:1.5px solid var(--linie);background:#fff;color:var(--grau);font-size:14px;cursor:pointer;font-family:inherit;}
.chip.on{background:var(--blau);color:#fff;border-color:var(--blau);font-weight:600;}
.btn-green{width:100%;padding:17px;background:var(--gruen);color:#fff;border:none;border-radius:14px;font-size:16px;font-weight:700;cursor:pointer;margin-top:18px;font-family:inherit;}
.btn-blue{width:100%;padding:15px;background:var(--blau);color:#fff;border:none;border-radius:13px;font-size:15px;font-weight:600;cursor:pointer;margin-top:10px;font-family:inherit;}
.btn-outline{width:100%;padding:15px;background:#fff;color:var(--blau);border:1.5px solid var(--blau);border-radius:13px;font-size:15px;font-weight:600;cursor:pointer;margin-top:10px;font-family:inherit;}
.price-box{background:linear-gradient(135deg,var(--blau),var(--blau-dd));color:#fff;border-radius:18px;padding:24px 18px;margin:14px;text-align:center;}
.price-lbl{font-size:11px;opacity:.85;text-transform:uppercase;letter-spacing:1.5px;}
.price-num{font-size:40px;font-weight:800;margin:5px 0;}
.price-sub{font-size:13px;opacity:.92;margin-top:7px;border-top:1px solid rgba(255,255,255,.2);padding-top:9px;line-height:1.5;}
.kt{width:100%;border-collapse:collapse;font-size:14px;}
.kt td{padding:9px 0;border-bottom:1px solid var(--linie);vertical-align:top;}
.kt td:last-child{text-align:right;font-weight:600;white-space:nowrap;padding-left:10px;}
.kt .summe td{border-bottom:none;border-top:2px solid var(--blau);font-weight:800;color:var(--blau-d);padding-top:12px;font-size:16px;}
.ml{list-style:none;}
.ml li{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--linie);font-size:14px;gap:10px;}
.ml li:last-child{border-bottom:none;}
.ml li span:last-child{color:var(--blau);font-weight:600;white-space:nowrap;}
.at{background:#f9fafb;border:1.5px solid var(--linie);border-radius:13px;padding:15px;font-size:13px;white-space:pre-wrap;line-height:1.65;font-family:monospace;}
.lern-card{background:#f0faf4;border:1.5px solid #bbf0cc;border-radius:18px;padding:17px;margin:14px;}
.fehler{background:#fef2f2;color:#b91c1c;padding:13px;border-radius:12px;font-size:13px;margin:8px 0 0;}
.denkweg{background:#eef3fa;border-radius:13px;padding:13px 15px;font-size:13px;color:#374151;line-height:1.6;margin:0 14px 14px;}
.warnung{font-size:12px;color:#92400e;background:#fffbeb;padding:11px;border-radius:9px;margin-top:11px;}
.badge{display:inline-block;font-size:11px;background:var(--gold);color:#fff;padding:4px 11px;border-radius:11px;font-weight:600;margin-bottom:11px;letter-spacing:.5px;}
.kopiert{color:var(--gruen);font-size:13px;font-weight:600;text-align:center;margin-top:8px;min-height:18px;}
.spinner{width:50px;height:50px;border:3px solid var(--linie);border-top-color:var(--blau);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 20px;}
@keyframes spin{to{transform:rotate(360deg);}}
.lern-item{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--linie);font-size:13px;gap:10px;}
.del{color:#b91c1c;cursor:pointer;font-size:11px;white-space:nowrap;}
/* Login */
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}
.login-card{background:#fff;border-radius:22px;padding:36px 28px;max-width:380px;width:100%;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,.4);}
.login-logo{font-size:38px;font-weight:300;letter-spacing:10px;color:var(--blau-d);}
.login-sub{font-size:10px;letter-spacing:4px;color:var(--grau);margin:6px 0 28px;}
.login-card input{text-align:center;font-size:20px;letter-spacing:4px;margin-bottom:16px;}
/* Büro Kacheln */
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:14px;}
.tile{background:#fff;border-radius:16px;padding:18px 14px;text-align:center;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.12);position:relative;}
.tile-ico{font-size:30px;margin-bottom:8px;}
.tile-name{font-size:13px;font-weight:700;color:var(--blau-d);}
.tile-soon{font-size:9px;color:#fff;background:var(--gold);padding:2px 7px;border-radius:8px;position:absolute;top:8px;right:8px;font-weight:600;}
.tile.aktiv{background:linear-gradient(135deg,var(--blau),var(--blau-dd));}
.tile.aktiv .tile-name{color:#fff;}
.section-title{color:#fff;font-size:13px;font-weight:600;letter-spacing:1px;margin:18px 14px 2px;opacity:.7;text-transform:uppercase;}
.zurueck{color:#9db8dd;background:none;border:none;font-size:14px;padding:14px;cursor:pointer;font-family:inherit;}
</style>
</head>
<body>

<?php if (!$eingeloggt): ?>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">OH</div>
    <div class="login-sub">HAUSTECHNIK · BÜRO</div>
    <form method="POST">
      <input type="password" name="login_pw" placeholder="Passwort" autofocus inputmode="text">
      <button type="submit" class="btn-green" style="margin-top:0">Anmelden</button>
    </form>
    <?php if (!empty($login_fehler)): ?>
      <div class="fehler" style="margin-top:14px">Falsches Passwort.</div>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<div class="wrap">
<header>
  <div><div class="logo">OH</div><div class="logo-sub">HAUSTECHNIK · BÜRO</div></div>
  <div class="hbtns">
    <button class="icobtn" onclick="toggleSettings()" title="Einstellungen">&#9881;</button>
    <a href="?logout=1" class="icobtn" style="display:flex;align-items:center;justify-content:center;text-decoration:none" title="Abmelden">&#10162;</a>
  </div>
</header>

<!-- ÜBERSICHT / BÜRO -->
<div id="s-home">
  <div class="section-title">Werkzeuge</div>
  <div class="tiles">
    <div class="tile aktiv" onclick="zeige('form')">
      <div class="tile-ico">&#129518;</div>
      <div class="tile-name">Kalkulator</div>
    </div>
    <div class="tile" onclick="alert('Kommt in Phase 2: Automatische Lead-Auswertung – kalt/warm Bewertung deiner Anfragen.')">
      <div class="tile-soon">BALD</div>
      <div class="tile-ico">&#128202;</div>
      <div class="tile-name">Leads</div>
    </div>
    <div class="tile" onclick="alert('Kommt in Phase 2: KI schreibt Angebote automatisch raus.')">
      <div class="tile-soon">BALD</div>
      <div class="tile-ico">&#128196;</div>
      <div class="tile-name">Angebote</div>
    </div>
    <div class="tile" onclick="alert('Kommt in Phase 2: KI beantwortet E-Mails automatisch.')">
      <div class="tile-soon">BALD</div>
      <div class="tile-ico">&#9993;</div>
      <div class="tile-name">E-Mails</div>
    </div>
    <div class="tile" onclick="alert('Kommt in Phase 2: KI beantwortet Google-Bewertungen automatisch.')">
      <div class="tile-soon">BALD</div>
      <div class="tile-ico">&#11088;</div>
      <div class="tile-name">Bewertungen</div>
    </div>
    <div class="tile" onclick="alert('Kommt in Phase 2: Automatische Bewertungs-Anfrage nach 5 Tagen + Kundengewinnung.')">
      <div class="tile-soon">BALD</div>
      <div class="tile-ico">&#128226;</div>
      <div class="tile-name">Marketing</div>
    </div>
  </div>
</div>

<!-- SETTINGS -->
<div id="s-settings" style="display:none">
  <div class="card">
    <h2>&#9881; API-Schlüssel</h2>
    <p class="intro">Dein Anthropic-Schlüssel von <b>console.anthropic.com</b>. Wird nur in diesem Gerät gespeichert.</p>
    <label>API-Schlüssel</label>
    <input type="password" id="apiIn" placeholder="sk-ant-...">
    <button class="btn-green" style="margin-top:12px" onclick="saveKey()">Speichern</button>
    <div id="keyMsg" class="kopiert"></div>
  </div>
  <div class="card">
    <h2>&#128218; Gelernte Korrekturen</h2>
    <div id="lernListe"><p style="font-size:13px;color:#9ca3af">Noch keine.</p></div>
  </div>
  <button class="zurueck" onclick="zeige('home')" style="width:100%">&larr; Zurück zur Übersicht</button>
</div>

<!-- KALKULATOR FORM -->
<div id="s-form" style="display:none">
  <div id="noKey" class="card" style="background:#fffbeb;border:1.5px solid #fcd34d;display:none">
    <span style="font-size:13px;color:#92400e">&#9881; Bitte zuerst oben den API-Schlüssel eintragen (Zahnrad-Symbol).</span>
  </div>
  <div class="card">
    <div class="badge">DEIN DIGITALER KALKULATOR</div>
    <h2>Baustelle beschreiben</h2>
    <p class="intro">Beschreib die Baustelle in eigenen Worten. Die KI rechnet Manntage, Material und Preis nach deiner Logik.</p>
    <textarea id="beschr" placeholder="Beispiel: Wohnungssanierung 3 Zimmer, Unterputz, Altbau, neuer Hager Verteiler, Küche mit Herd und Spülmaschine, Demontage nötig, 15 km Fahrt"></textarea>
    <label>Installationsart</label>
    <div class="chips">
      <button class="chip" onclick="setArt('Aufputz',this)">Aufputz</button>
      <button class="chip on" onclick="setArt('Unterputz',this)">Unterputz</button>
      <button class="chip" onclick="setArt('Gemischt',this)">Gemischt</button>
    </div>
    <div class="row" style="margin-top:15px">
      <div><label style="margin-top:0">m² (optional)</label><input type="number" id="qm" placeholder="z.B. 85" inputmode="numeric"></div>
      <div><label style="margin-top:0">Fahrt km (opt.)</label><input type="number" id="km" placeholder="z.B. 15" inputmode="numeric"></div>
    </div>
    <label>Wer stellt das Material?</label>
    <select id="matQ">
      <option value="ich">Ich (im Preis enthalten)</option>
      <option value="bauseits">Bauseits (Kunde stellt Material)</option>
    </select>
    <div id="fehler" class="fehler" style="display:none"></div>
    <button class="btn-green" onclick="kalk()">&#9889; Baustelle kalkulieren</button>
  </div>
  <button class="zurueck" onclick="zeige('home')" style="width:100%">&larr; Zurück zur Übersicht</button>
</div>

<!-- LOADING -->
<div id="s-load" style="display:none;text-align:center;padding:80px 20px">
  <div class="spinner"></div>
  <p style="color:#cdd9ea;font-size:15px">Kalkuliert deine Baustelle …</p>
  <p style="color:#7d93b3;font-size:13px;margin-top:8px">Arbeitszeit · Material · Preis</p>
</div>

<!-- RESULT -->
<div id="s-result" style="display:none">
  <div class="price-box">
    <div class="price-lbl">Zielpreis (netto, 0% USt)</div>
    <div class="price-num" id="r-ziel">–</div>
    <div class="price-sub" id="r-sub"></div>
  </div>
  <div class="denkweg" id="r-denkweg"></div>
  <div class="card">
    <h2>&#129518; Kalkulation</h2>
    <table class="kt"><tbody id="r-kt"></tbody></table>
    <p style="font-size:12px;color:#9ca3af;margin-top:8px">68€×2=136€/Std · 8,5 Std/Tag · Material +10%</p>
  </div>
  <div class="card" id="r-matcard">
    <h2>&#128230; Materialliste</h2>
    <ul class="ml" id="r-mat"></ul>
    <div class="warnung">&#9888;&#65039; Mengen nach Faustregeln geschätzt. Vor Bestellung prüfen.</div>
  </div>
  <div class="card">
    <h2>&#128196; Angebotstext</h2>
    <div class="at" id="r-at"></div>
    <button class="btn-blue" onclick="kopier()">&#128203; Text kopieren</button>
    <div class="kopiert" id="kopMsg"></div>
  </div>
  <div class="lern-card">
    <h2 style="color:#166534">&#127919; Stimmt die Kalkulation?</h2>
    <p class="intro">Korrigiere hier – die App lernt für das nächste Mal.</p>
    <textarea id="korr" placeholder="z.B. 'Zu wenig Tage, Altbau braucht 5 Tage Rohmontage'" style="min-height:80px"></textarea>
    <button class="btn-green" style="margin-top:10px" onclick="lernNeu()">Speichern & neu rechnen</button>
    <div class="kopiert" id="lernMsg"></div>
  </div>
  <button class="btn-outline" style="margin:0 14px;width:calc(100% - 28px)" onclick="neuBau()">↺ Neue Baustelle</button>
  <button class="zurueck" onclick="zeige('home')" style="width:100%">&larr; Zurück zur Übersicht</button>
</div>
</div>

<script>
const W=`KALKULATIONS-LOGIK OH Haustechnik (Kleinunternehmer, 0% USt):
ARBEITSZEIT in MANNTAGEN:
- Stundensatz Sanierung: 68€×2=136€/Std, Arbeitstag=8,5h
- Wohnungssanierung 3Z UP Altbau: Rohmontage 4 Tage(2 Pers)+Fertigmontage 1,5T=8-11 Manntage
- Rohmontage: Demontage(mehrere Std!),anzeichnen,fräsen,schlitzen,stemmen,Leitungen,Verteiler
- Fertigmontage: Schalter/Steckdosen/Lampen,Verteiler anklemmen,messen,prüfen+Puffer
- Aufputz(Zuleitung liegt): ca.2,5Std/Raum(4Steckdosen+Schalter+Lampe)
- Unterputz: ca.4Std/Raum
- Endpreis 3-Zimmer-Sanierung: 8.000-11.000€ inkl.Material
MATERIAL:
- NYM-J 3×1,5: ca.1,5m/m²+10%Verschnitt
- Separate Verbraucher+10m Reserve: Herd→NYM5×2,5|Spülm/Waschm/Trockner→NYM3×2,5|DLE→NYM4×6
- Dosen: 1 Steckdose=1Dose,Doppel=2Dosen
- Verteiler: Hager VU48NC,2×FI40A,12×LSB16,1×LSB163pol,ÜSS Typ2
- Material 3-Zimmer-Sanierung: ca.1.000-1.500€
- Anfahrt>10km: 100-200€ Pauschale
- Material immer +10% Aufschlag (Marge)
VERHANDLUNG: bei wenig Auftragslage 1.000-1.500€ runter möglich (Zielpreis + Minimalpreis)`;

let art='Unterputz',letzterT='',letzterF={};
const gl=id=>document.getElementById(id);
const eur=n=>(n||0).toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2})+' €';
const getKey=()=>localStorage.getItem('oh_key')||'';
const getLern=()=>{try{return JSON.parse(localStorage.getItem('oh_lern')||'[]');}catch(e){return[];}};
const setLernS=a=>localStorage.setItem('oh_lern',JSON.stringify(a));

function zeige(s){
  ['home','settings','form','load','result'].forEach(id=>{const el=gl('s-'+id);if(el)el.style.display='none';});
  gl('s-'+s).style.display='block';
  if(s==='form'){gl('noKey').style.display=getKey()?'none':'block';}
  window.scrollTo({top:0,behavior:'smooth'});
}
function toggleSettings(){
  if(gl('s-settings').style.display==='block'){zeige('home');}
  else{gl('apiIn').value=getKey();renderLL();zeige('settings');}
}
function saveKey(){
  localStorage.setItem('oh_key',gl('apiIn').value.trim());
  gl('keyMsg').textContent='✓ Gespeichert!';
  setTimeout(()=>gl('keyMsg').textContent='',2000);
}
function renderLL(){
  const l=getLern(),el=gl('lernListe');
  el.innerHTML=l.length?l.map((t,i)=>`<div class="lern-item"><span>• ${t}</span><span class="del" onclick="delL(${i})">löschen</span></div>`).join(''):'<p style="font-size:13px;color:#9ca3af">Noch keine.</p>';
}
function delL(i){const l=getLern();l.splice(i,1);setLernS(l);renderLL();}
function setArt(a,b){art=a;document.querySelectorAll('#s-form .chip').forEach(c=>c.classList.remove('on'));b.classList.add('on');}

async function kalk(extra=''){
  const key=getKey(),beschr=gl('beschr').value.trim(),fe=gl('fehler');
  fe.style.display='none';
  if(!key){fe.textContent='Kein API-Schlüssel. Tippe oben auf das Zahnrad.';fe.style.display='block';return;}
  if(beschr.length<8){fe.textContent='Bitte Baustelle beschreiben.';fe.style.display='block';return;}
  const qm=gl('qm').value||'unbekannt',km=gl('km').value||'0',mat=gl('matQ').value;
  letzterF={beschr,art,qm,km,mat};
  const lern=getLern();
  const lT=lern.length?'\nGELERNTE KORREKTUREN:\n- '+lern.join('\n- '):'';
  const eT=extra?`\nZUSATZ: ${extra}`:'';
  const prompt=`${W}${lT}${eT}\n\nBAUSTELLE: "${beschr}"\nArt:${art} m²:${qm} Fahrt:${km}km Material:${mat==='ich'?'durch OH':'bauseits'}\n\nKalkuliere vollständig in Manntagen mit Puffer. Antworte NUR JSON:\n{"manntage":<n>,"arbeitsstunden":<n>,"arbeitskosten":<n>,"fahrtkosten":<n>,"material_netto":<n>,"material_mit_aufschlag":<n>,"zielpreis":<n>,"minimalpreis":<n>,"denkweg":"<2 Sätze>","material_liste":[{"pos":"x","menge":"x"}],"leistungen":["x"]}`;
  zeige('load');
  try{
    const payload=JSON.stringify({model:'claude-sonnet-4-5',max_tokens:2000,messages:[{role:'user',content:prompt}]});
    const fd=new FormData();
    fd.append('kalk_request',payload);
    fd.append('api_key',key);
    const r=await fetch(window.location.pathname,{method:'POST',body:fd});
    const d=await r.json();
    if(d.error){throw new Error(d.error.message||'Fehler');}
    let t=d.content.map(i=>i.type==='text'?i.text:'').join('').trim().replace(/\`\`\`json|\`\`\`/g,'').trim();
    const s=t.indexOf('{'),en=t.lastIndexOf('}');
    if(s>=0&&en>=0)t=t.slice(s,en+1);
    zeigR(JSON.parse(t),mat);
  }catch(e){
    zeige('form');
    let m='Fehler: ';
    const em=(e.message||'')+'';
    if(em.includes('401')||em.includes('authentication'))m+='API-Schlüssel ungültig. Unter Zahnrad prüfen.';
    else if(em.includes('credit')||em.includes('balance'))m+='Guthaben aufladen: console.anthropic.com';
    else m+=em||'Bitte nochmal versuchen.';
    fe.textContent=m;fe.style.display='block';
  }
}
function zeigR(k,mat){
  gl('r-ziel').textContent=eur(k.zielpreis);
  gl('r-sub').innerHTML=`${k.manntage} Manntage · ${k.arbeitsstunden} Std<br>Verhandelbar bis: <b>${eur(k.minimalpreis)}</b>`;
  gl('r-denkweg').textContent=k.denkweg?'💭 '+k.denkweg:'';
  let rows=`<tr><td>Arbeit (${k.arbeitsstunden} Std × 136€)</td><td>${eur(k.arbeitskosten)}</td></tr>`;
  if(k.fahrtkosten>0)rows+=`<tr><td>Anfahrt</td><td>${eur(k.fahrtkosten)}</td></tr>`;
  if(k.material_mit_aufschlag>0)rows+=`<tr><td>Material (+10%)</td><td>${eur(k.material_mit_aufschlag)}</td></tr>`;
  rows+=`<tr class="summe"><td>Zielpreis</td><td>${eur(k.zielpreis)}</td></tr>`;
  gl('r-kt').innerHTML=rows;
  if(mat==='bauseits'||!k.material_liste||!k.material_liste.length){gl('r-matcard').style.display='none';}
  else{gl('r-matcard').style.display='block';gl('r-mat').innerHTML=k.material_liste.map(m=>`<li><span>${m.pos}</span><span>${m.menge||''}</span></li>`).join('');}
  let t='Arbeitsleistung\nLeistungsumfang:\n'+k.leistungen.map(l=>'• '+l).join('\n');
  t+=mat==='bauseits'?'\n\nHinweis:\nMaterial wird bauseits gestellt.':'\n\nHinweis:\nMaterial ist im Angebotspreis enthalten.';
  letzterT=t;gl('r-at').textContent=t;
  gl('korr').value='';gl('lernMsg').textContent='';gl('kopMsg').textContent='';
  zeige('result');
}
function kopier(){
  navigator.clipboard.writeText(letzterT).then(()=>{gl('kopMsg').textContent='✓ Kopiert!';setTimeout(()=>gl('kopMsg').textContent='',2500);}).catch(()=>{
    const r=document.createRange();r.selectNode(gl('r-at'));window.getSelection().removeAllRanges();window.getSelection().addRange(r);try{document.execCommand('copy');}catch(e){}window.getSelection().removeAllRanges();gl('kopMsg').textContent='✓ Kopiert!';
  });
}
function lernNeu(){
  const k=gl('korr').value.trim();if(k.length<4)return;
  const l=getLern();l.push(k);setLernS(l);
  gl('lernMsg').textContent='✓ Gelernt! Rechne neu …';
  setTimeout(()=>{gl('beschr').value=letzterF.beschr||'';gl('qm').value=letzterF.qm||'';gl('km').value=letzterF.km||'';gl('matQ').value=letzterF.mat||'ich';kalk(k);},600);
}
function neuBau(){gl('beschr').value='';gl('qm').value='';gl('km').value='';zeige('form');}
zeige('home');
</script>
</body>
</html>
<?php endif; ?>