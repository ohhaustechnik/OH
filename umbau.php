<?php
/**
 * OH Umbau- & Kommando-Zentrale (mobil).
 *  - Zeigt Live-Status des Nacht-Umbaus (umbau.json)
 *  - Mit ?key=oh-cron: Chat-Befehlskanal — der grosse Adnan tippt Befehle,
 *    Claude holt sie per FTP ab, setzt sie um und antwortet hier.
 * Dateien (in daten/, web-geschuetzt, nur serverseitig gelesen):
 *  - chat.json  : [{id, rolle:'chef'|'claude', text, ts, erledigt}]
 */
$DATA = __DIR__ . '/daten';
if (!is_dir($DATA)) { $DATA = __DIR__; }
$CHAT = $DATA . '/chat.json';
$KEY  = 'oh-cron';

function chat_read($f){ $d = @json_decode(@file_get_contents($f), true); return is_array($d) ? $d : []; }
function chat_write($f,$d){ @file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), LOCK_EX); }

// ---- API ----
if (isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $key = $_POST['key'] ?? '';
    if ($key !== $KEY) { http_response_code(403); echo json_encode(['err'=>'key']); exit; }
    $a = $_POST['action'];
    if ($a === 'send') {
        $text = trim((string)($_POST['text'] ?? ''));
        if ($text !== '') {
            $c = chat_read($CHAT);
            $c[] = ['id'=>uniqid('m'), 'rolle'=>'chef', 'text'=>mb_substr($text,0,2000), 'ts'=>time(), 'erledigt'=>false];
            chat_write($CHAT, $c);
        }
        echo json_encode(['ok'=>true]); exit;
    }
    if ($a === 'poll') {
        echo json_encode(['chat'=>chat_read($CHAT)], JSON_UNESCAPED_UNICODE); exit;
    }
    echo json_encode(['err'=>'unknown']); exit;
}

$hasKey = (($_GET['key'] ?? '') === $KEY);
$d = json_decode(@file_get_contents(__DIR__ . '/umbau.json'), true);
if (!$d) { $d = ['aktualisiert'=>'—','phase'=>'—','nachricht'=>'—','fortschritt'=>0,'schritte'=>[]]; }
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0a0f1c">
<title>OH · Kommando</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
body{font-family:'Manrope',-apple-system,sans-serif;background:#0a0f1c;color:#e9eef8;min-height:100vh;padding:16px 14px calc(20px + env(safe-area-inset-bottom));}
.wrap{max-width:560px;margin:0 auto}
h1{font-family:'Sora';font-size:18px;font-weight:800;letter-spacing:.3px;display:flex;align-items:center;gap:9px;margin-bottom:3px}
.dot{width:8px;height:8px;border-radius:50%;background:#37d89a;box-shadow:0 0 8px #37d89a;animation:pulse 2s infinite}
@keyframes pulse{50%{opacity:.4}}
.sub{font-size:12px;color:#8595b3;margin-bottom:16px}
.glas{background:rgba(18,27,46,.7);border:1px solid #222f49;border-radius:16px;padding:15px;margin-bottom:13px;backdrop-filter:blur(8px)}
.phase{font-family:'Sora';font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#5b91f5;margin-bottom:6px}
.msg{font-size:14px;line-height:1.55}
.bar-bg{background:rgba(255,255,255,.07);border-radius:99px;height:11px;margin:13px 0 5px;overflow:hidden}
.bar{background:linear-gradient(90deg,#3a6fd6,#5b91f5 55%,#e8a24a);height:100%;border-radius:99px;transition:width .6s;width:<?php echo (int)$d['fortschritt']; ?>%}
.pct{font-size:12px;color:#8595b3;text-align:right}
.s{display:flex;align-items:center;gap:11px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.06);font-size:13.5px}
.s:last-child{border-bottom:none}
.ic{width:22px;height:22px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0}
.fertig .ic{background:#1f6f43}.fertig{color:#9ad8b4}
.laeuft .ic{background:#3a6fd6;animation:pulse 1.4s infinite}.laeuft{color:#bcd7ff;font-weight:600}
.offen .ic{background:rgba(255,255,255,.08)}.offen{color:#7d8db0}
/* Chat */
.chat-h{font-family:'Sora';font-size:13px;font-weight:700;color:#e8edf7;margin:18px 4px 9px;display:flex;align-items:center;gap:8px}
.chat-h .amber{color:#e8a24a}
#chat{display:flex;flex-direction:column;gap:9px;margin-bottom:12px}
.b{max-width:86%;padding:11px 14px;border-radius:15px;font-size:14px;line-height:1.5;white-space:pre-wrap;word-break:break-word}
.b.chef{align-self:flex-end;background:linear-gradient(135deg,#3a6fd6,#2a5fc7);color:#fff;border-bottom-right-radius:5px}
.b.claude{align-self:flex-start;background:rgba(255,255,255,.05);border:1px solid #222f49;color:#dfeaf6;border-bottom-left-radius:5px}
.b .meta{font-size:10px;opacity:.6;margin-top:4px}
.b.warten{align-self:flex-end;opacity:.6;font-style:italic}
.composer{position:sticky;bottom:0;background:linear-gradient(0deg,#0a0f1c 70%,transparent);padding:10px 0 4px;display:flex;gap:8px}
textarea{flex:1;background:#0e1525;border:1.5px solid #243250;border-radius:13px;padding:13px;color:#fff;font-size:16px;font-family:inherit;resize:none;outline:none;min-height:50px;max-height:130px}
textarea:focus{border-color:#5b91f5}
.send{background:linear-gradient(135deg,#e8a24a,#cf8a32);color:#1a1206;border:none;border-radius:13px;width:54px;font-size:20px;font-weight:800;cursor:pointer;flex-shrink:0}
.send:active{transform:scale(.95)}
.hint{font-size:12px;color:#8595b3;text-align:center;margin-top:16px;line-height:1.6}
.locked{background:rgba(232,162,74,.08);border:1px solid rgba(232,162,74,.25);border-radius:13px;padding:14px;font-size:13px;color:#e8c79a;text-align:center;margin-top:14px}
</style>
</head>
<body>
<div class="wrap">
  <h1><span class="dot"></span>OH · Nacht-Umbau</h1>
  <div class="sub">Für den grossen Adnan · <span id="stand"><?php echo htmlspecialchars($d['aktualisiert']); ?></span></div>

  <div class="glas">
    <div class="phase" id="phase"><?php echo htmlspecialchars($d['phase']); ?></div>
    <div class="msg" id="nachricht"><?php echo htmlspecialchars($d['nachricht']); ?></div>
    <div class="bar-bg"><div class="bar" id="bar"></div></div>
    <div class="pct"><span id="pct"><?php echo (int)$d['fortschritt']; ?></span> %</div>
  </div>

  <div class="glas" id="schritteBox">
    <?php foreach ($d['schritte'] as $s):
      $k = ($s['status']??'')==='fertig'?'fertig':((($s['status']??'')==='läuft'||($s['status']??'')==='laeuft')?'laeuft':'offen');
      $i = $k==='fertig'?'✓':($k==='laeuft'?'●':'·'); ?>
      <div class="s <?php echo $k; ?>"><div class="ic"><?php echo $i; ?></div><div><?php echo htmlspecialchars($s['name']); ?></div></div>
    <?php endforeach; ?>
  </div>

<?php if ($hasKey): ?>
  <div class="chat-h">💬 Befehle an <span class="amber">Claude</span> — direkt umgesetzt</div>
  <div id="chat"></div>
  <div class="composer">
    <textarea id="cmd" placeholder="Befehl tippen… (z. B. „ändere die Hero-Überschrift auf …")" rows="1"></textarea>
    <button class="send" onclick="sende()">➤</button>
  </div>
  <div class="hint">Ich hole deine Befehle alle paar Sekunden ab, setze sie um und antworte hier. Du musst nicht warten — schreib einfach.</div>
<?php else: ?>
  <div class="locked">🔒 Nur-Lese-Ansicht. Für den Befehlskanal die Seite mit <b>?key=oh-cron</b> öffnen.</div>
<?php endif; ?>
</div>

<script>
const KEY = <?php echo json_encode($hasKey ? $KEY : ''); ?>;
async function api(action, extra){
  const fd = new FormData(); fd.append('action',action); fd.append('key',KEY);
  for(const k in (extra||{})) fd.append(k, extra[k]);
  const r = await fetch('umbau.php',{method:'POST',body:fd});
  return r.json();
}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function zeit(ts){const d=new Date(ts*1000);return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0');}
function renderChat(list){
  const box=document.getElementById('chat'); if(!box)return;
  box.innerHTML = (list||[]).map(m=>{
    const cls = m.rolle==='chef'?'chef':'claude';
    const wer = m.rolle==='chef'?'Du':'Claude';
    return `<div class="b ${cls}">${esc(m.text)}<div class="meta">${wer} · ${zeit(m.ts)}${m.rolle==='chef'&&!m.erledigt?' · wird bearbeitet…':''}</div></div>`;
  }).join('');
  box.scrollIntoView(false);
  window.scrollTo(0,document.body.scrollHeight);
}
async function sende(){
  const t=document.getElementById('cmd'); const v=t.value.trim(); if(!v)return;
  t.value=''; t.style.height='auto';
  await api('send',{text:v});
  await tick();
}
async function tick(){
  try{
    if(KEY){ const d=await api('poll'); renderChat(d.chat); }
    // Status aktualisieren
    const r = await fetch('umbau.json?_='+Date.now()); const s = await r.json();
    document.getElementById('stand').textContent=s.aktualisiert||'';
    document.getElementById('phase').textContent=s.phase||'';
    document.getElementById('nachricht').textContent=s.nachricht||'';
    document.getElementById('pct').textContent=s.fortschritt||0;
    document.getElementById('bar').style.width=(s.fortschritt||0)+'%';
    if(s.schritte){
      document.getElementById('schritteBox').innerHTML = s.schritte.map(x=>{
        const k = x.status==='fertig'?'fertig':((x.status==='läuft'||x.status==='laeuft')?'laeuft':'offen');
        const i = k==='fertig'?'✓':(k==='laeuft'?'●':'·');
        return `<div class="s ${k}"><div class="ic">${i}</div><div>${esc(x.name)}</div></div>`;
      }).join('');
    }
  }catch(e){}
}
const cmd=document.getElementById('cmd');
if(cmd){
  cmd.addEventListener('input',()=>{cmd.style.height='auto';cmd.style.height=Math.min(130,cmd.scrollHeight)+'px';});
  cmd.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sende();}});
}
tick(); setInterval(tick, 5000);
</script>
</body>
</html>
