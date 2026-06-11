<?php
session_start();
$PASSWORT = 'oh';

// Bibliothek laden (defensiv: fehlt sie, läuft das Büro trotzdem im Basismodus)
$__lib = __DIR__ . '/includes/buero-lib.php';
if (is_file($__lib)) { require_once $__lib; }
if (!function_exists('oh_config')) {
    function oh_config() { return []; }
}

// API-Key: serverseitige Konfiguration (daten/config.json) oder Umgebungsvariable
$cfg0 = oh_config();
$API_KEY = isset($cfg0['anthropic_key']) ? $cfg0['anthropic_key'] : (getenv('CLAUDE_KEY') ?: '');

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

// AJAX-Login (vor der Session-Schranke)
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json; charset=utf-8');
    $in = json_decode($_POST['data'] ?? '{}', true) ?: [];
    if (($in['pw'] ?? '') === $PASSWORT) {
        $_SESSION['eingeloggt'] = true;
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ---------------------------------------------------------------------------
// Büro-API: Dashboard, Leads, E-Mail, Konfiguration (nur eingeloggt)
// ---------------------------------------------------------------------------
if (isset($_POST['action']) && !empty($_SESSION['eingeloggt'])) {
    header('Content-Type: application/json; charset=utf-8');
    $a = $_POST['action'];
    $in = json_decode($_POST['data'] ?? '{}', true) ?: [];

    if ($a === 'dashboard') {
        echo json_encode([
            'tasks' => oh_dashboard_tasks(),
            'leads' => oh_read('leads', []),
            'stats' => [
                'leads'  => count(oh_read('leads', [])),
                'hot'    => count(array_filter(oh_read('leads', []), function($l){ return ($l['stufe'] ?? '') === 'HOT' && ($l['status'] ?? '') === 'neu'; })),
            ],
        ]);
    } elseif ($a === 'lead_add') {
        echo json_encode(['lead' => oh_add_lead($in)]);
    } elseif ($a === 'lead_update') {
        echo json_encode(['lead' => oh_update_lead($in['id'] ?? '', $in['patch'] ?? [], $in['log'] ?? null)]);
    } elseif ($a === 'lead_delete') {
        oh_delete_lead($in['id'] ?? '');
        echo json_encode(['ok' => true]);
    } elseif ($a === 'send_mail') {
        $res = oh_send_mail($in['to'] ?? '', $in['subject'] ?? '', $in['body'] ?? '', $in['replyTo'] ?? null);
        if (!empty($in['lead_id']) && !empty($res['ok'])) {
            $patch = ['status' => $in['set_status'] ?? 'angebot_raus'];
            if (($in['set_status'] ?? '') === 'angebot_raus') $patch['angebot_ts'] = time();
            if (!empty($in['bewertung'])) $patch['bewertung_angefragt'] = true;
            oh_update_lead($in['lead_id'], $patch, 'E-Mail gesendet: ' . ($in['subject'] ?? ''));
        }
        echo json_encode($res);
    } elseif ($a === 'config_get') {
        $c = oh_config();
        echo json_encode([
            'gmail_user'     => $c['gmail_user'] ?? '',
            'has_gmail_pass' => !empty($c['gmail_pass']),
            'has_anthropic'  => !empty($c['anthropic_key']),
        ]);
    } elseif ($a === 'config_set') {
        oh_config_set([
            'anthropic_key' => $in['anthropic_key'] ?? '',
            'gmail_user'    => $in['gmail_user'] ?? '',
            'gmail_pass'    => $in['gmail_pass'] ?? '',
        ]);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['error' => 'unbekannte Aktion']);
    }
    exit;
}

// Generischer API-Proxy fuer alle KI-Module (Kalkulation, Marketing, Leads, Chat)
if (isset($_POST['ki_request']) && !empty($_SESSION['eingeloggt'])) {
    header('Content-Type: application/json');
    $userKey = $_POST['api_key'] ?: $API_KEY;
    if (!$userKey) { echo json_encode(['error' => ['message' => 'Kein API-Schlüssel hinterlegt.']]); exit; }
    $body = $_POST['ki_request'];
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
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
<meta name="apple-mobile-web-app-title" content="OH System">
<meta name="theme-color" content="#04070d">
<meta name="robots" content="noindex, nofollow">
<title>OH · System</title>
<style>
:root{
  --bg:#04070d; --bg2:#070d18;
  --cyan:#39d6ff; --cyan-d:#1693c4; --cyan-soft:rgba(57,214,255,.12);
  --gold:#e7b14b; --green:#34e09a; --red:#ff5d6c;
  --txt:#dfeaf6; --txt-dim:#7e93ad; --line:rgba(57,214,255,.18);
  --glass:rgba(12,22,38,.55); --glass-2:rgba(16,28,48,.72);
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
html,body{height:100%;}
body{
  font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','Segoe UI',Roboto,sans-serif;
  background:var(--bg); color:var(--txt); min-height:100vh; overflow-x:hidden;
  position:relative;
}
/* --- HUD HINTERGRUND --- */
.bg-fx{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.bg-fx .glow{position:absolute;width:120vmax;height:120vmax;left:50%;top:-30%;transform:translateX(-50%);
  background:radial-gradient(circle at center, rgba(25,120,170,.35), rgba(8,18,34,.0) 60%);}
.bg-fx .glow2{position:absolute;width:80vmax;height:80vmax;right:-20%;bottom:-30%;
  background:radial-gradient(circle at center, rgba(40,90,140,.22), rgba(8,18,34,0) 60%);}
.bg-fx .grid{position:absolute;inset:0;
  background-image:linear-gradient(rgba(57,214,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(57,214,255,.05) 1px,transparent 1px);
  background-size:46px 46px;mask-image:radial-gradient(circle at 50% 30%,#000 30%,transparent 80%);}
.bg-fx .scan{position:absolute;inset:0;background:linear-gradient(rgba(57,214,255,.04),rgba(57,214,255,0) 3px);
  background-size:100% 4px;animation:scan 8s linear infinite;opacity:.5;}
@keyframes scan{to{background-position:0 400px;}}
.corner{position:fixed;width:26px;height:26px;border:2px solid var(--cyan);opacity:.5;z-index:5;pointer-events:none;}
.corner.tl{top:14px;left:14px;border-right:0;border-bottom:0;}
.corner.tr{top:14px;right:14px;border-left:0;border-bottom:0;}
.corner.bl{bottom:14px;left:14px;border-right:0;border-top:0;}
.corner.br{bottom:14px;right:14px;border-left:0;border-top:0;}

.wrap{max-width:560px;margin:0 auto;position:relative;z-index:2;padding-bottom:40px;}

/* --- HEADER --- */
header{padding:18px 18px 12px;padding-top:calc(18px + env(safe-area-inset-top));
  display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20;
  background:linear-gradient(180deg,rgba(4,7,13,.92),rgba(4,7,13,.4) 70%,transparent);backdrop-filter:blur(6px);}
.brand{display:flex;align-items:center;gap:11px;}
.brand .mark{font-size:23px;font-weight:300;letter-spacing:6px;color:#fff;
  text-shadow:0 0 14px rgba(57,214,255,.55);}
.brand .sub{font-size:8.5px;letter-spacing:3px;color:var(--cyan);opacity:.8;margin-top:2px;font-family:'SF Mono',ui-monospace,monospace;}
.hbtns{display:flex;gap:8px;}
.icobtn{background:var(--glass);border:1px solid var(--line);color:var(--cyan);font-size:16px;width:40px;height:40px;
  border-radius:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;backdrop-filter:blur(8px);}
.icobtn:active{transform:scale(.93);}
.statusbar{display:flex;gap:14px;align-items:center;padding:0 20px 6px;font-family:'SF Mono',ui-monospace,monospace;
  font-size:10px;color:var(--txt-dim);letter-spacing:1px;}
.dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);display:inline-block;margin-right:5px;animation:pulse 2s infinite;}
@keyframes pulse{50%{opacity:.4;}}

/* --- BOOT / WILLKOMMEN OVERLAY --- */
#boot{position:fixed;inset:0;z-index:100;background:radial-gradient(circle at 50% 35%,#081426,#03060c 70%);
  display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px;
  transition:opacity .7s ease;}
#boot .ring{width:128px;height:128px;border-radius:50%;position:relative;margin-bottom:30px;}
#boot .ring:before,#boot .ring:after{content:'';position:absolute;inset:0;border-radius:50%;border:2px solid transparent;}
#boot .ring:before{border-top-color:var(--cyan);border-right-color:var(--cyan);animation:spin 1.4s linear infinite;
  box-shadow:0 0 22px rgba(57,214,255,.5);}
#boot .ring:after{inset:16px;border-bottom-color:rgba(57,214,255,.5);border-left-color:rgba(57,214,255,.5);animation:spin 2s linear infinite reverse;}
@keyframes spin{to{transform:rotate(360deg);}}
#boot .core{position:absolute;inset:42px;border-radius:50%;background:radial-gradient(circle,#fff,var(--cyan) 70%);
  box-shadow:0 0 30px var(--cyan);animation:pulse 1.6s infinite;}
#boot .lines{font-family:'SF Mono',ui-monospace,monospace;font-size:12px;color:var(--cyan);text-align:left;
  min-height:90px;letter-spacing:1px;line-height:2;text-shadow:0 0 8px rgba(57,214,255,.4);}
#boot .greet{font-size:26px;font-weight:300;letter-spacing:2px;color:#fff;margin-top:26px;opacity:0;transition:opacity .8s;
  text-shadow:0 0 20px rgba(57,214,255,.5);}
#boot .greet b{font-weight:600;color:var(--cyan);}
#boot .greet small{display:block;font-size:12px;color:var(--txt-dim);letter-spacing:2px;margin-top:10px;font-family:'SF Mono',monospace;}

/* --- KARTEN / GLAS --- */
.section-title{font-family:'SF Mono',ui-monospace,monospace;font-size:11px;font-weight:600;letter-spacing:2px;
  color:var(--cyan);margin:20px 18px 4px;opacity:.8;text-transform:uppercase;}
.card{background:var(--glass);border:1px solid var(--line);border-radius:18px;padding:18px 16px;margin:12px 14px;
  backdrop-filter:blur(14px);box-shadow:0 8px 30px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.04);}
h2{font-size:15px;font-weight:700;color:#fff;margin-bottom:8px;display:flex;align-items:center;gap:8px;}
.intro{font-size:13px;color:var(--txt-dim);margin-bottom:12px;line-height:1.6;}

/* --- DASHBOARD --- */
.dash-head{margin:8px 14px 0;}
.dash-hi{font-size:20px;font-weight:300;letter-spacing:1px;color:#fff;}
.dash-hi b{font-weight:700;color:var(--cyan);}
.dash-stats{display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;}
.stat{flex:1;min-width:90px;background:var(--glass);border:1px solid var(--line);border-radius:13px;padding:11px 13px;backdrop-filter:blur(10px);}
.stat .n{font-size:22px;font-weight:800;color:#fff;}
.stat .l{font-size:10px;color:var(--txt-dim);letter-spacing:1px;text-transform:uppercase;margin-top:2px;}
.stat.hot .n{color:var(--red);}
.prio-group{margin:8px 14px 4px;}
.prio-lbl{display:flex;align-items:center;gap:8px;font-family:'SF Mono',ui-monospace,monospace;font-size:11px;font-weight:600;
  letter-spacing:1px;color:var(--txt-dim);margin:12px 0 7px;text-transform:uppercase;}
.prio-dot{width:9px;height:9px;border-radius:50%;}
.prio-dot.rot{background:var(--red);box-shadow:0 0 9px var(--red);}
.prio-dot.gelb{background:var(--gold);box-shadow:0 0 9px var(--gold);}
.prio-dot.gruen{background:var(--green);box-shadow:0 0 9px var(--green);}
.prio-list{display:flex;flex-direction:column;gap:8px;}
.task{display:flex;align-items:center;gap:11px;background:var(--glass);border:1px solid var(--line);border-radius:13px;
  padding:13px 14px;backdrop-filter:blur(10px);cursor:pointer;transition:transform .12s,border-color .2s;}
.task:active{transform:scale(.98);}
.task.rot{border-left:3px solid var(--red);}
.task.gelb{border-left:3px solid var(--gold);}
.task.gruen{border-left:3px solid var(--green);}
.task .tx{flex:1;min-width:0;}
.task .tt{font-size:14px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.task .ta{font-size:11.5px;color:var(--cyan);margin-top:2px;}
.task .go{color:var(--cyan);font-size:18px;flex-shrink:0;}
.prio-empty{font-size:12px;color:var(--txt-dim);padding:4px 2px;opacity:.7;}

/* --- KACHELN --- */
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 14px;}
.tile{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:18px 14px;text-align:left;
  cursor:pointer;position:relative;backdrop-filter:blur(12px);transition:transform .15s, border-color .2s, box-shadow .2s;overflow:hidden;}
.tile:active{transform:scale(.97);}
.tile:before{content:'';position:absolute;top:-40%;right:-40%;width:120px;height:120px;border-radius:50%;
  background:radial-gradient(circle,var(--cyan-soft),transparent 70%);}
.tile.aktiv{border-color:var(--cyan);box-shadow:0 0 22px rgba(57,214,255,.25);}
.tile-ico{font-size:26px;margin-bottom:10px;filter:drop-shadow(0 0 6px rgba(57,214,255,.4));}
.tile-name{font-size:14px;font-weight:700;color:#fff;}
.tile-desc{font-size:10.5px;color:var(--txt-dim);margin-top:3px;line-height:1.4;}
.tile-tag{font-size:8px;color:var(--bg);background:var(--cyan);padding:3px 7px;border-radius:7px;position:absolute;
  top:10px;right:10px;font-weight:700;letter-spacing:.5px;}
.tile-tag.soon{background:var(--gold);}

/* --- CHAT --- */
.chat-wrap{margin:0 14px;}
.chat-head{display:flex;align-items:center;gap:11px;margin:8px 0 12px;}
.chat-head .av{width:42px;height:42px;border-radius:13px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 0 18px rgba(57,214,255,.4);}
.chat-head .nm{font-weight:700;font-size:15px;color:#fff;}
.chat-head .st{font-size:10px;color:var(--green);font-family:'SF Mono',monospace;letter-spacing:1px;}
.chat-log{display:flex;flex-direction:column;gap:12px;padding:6px 0 4px;min-height:120px;}
.msg{max-width:88%;padding:12px 14px;border-radius:16px;font-size:14px;line-height:1.6;white-space:pre-wrap;word-wrap:break-word;}
.msg.ai{align-self:flex-start;background:var(--glass-2);border:1px solid var(--line);border-bottom-left-radius:5px;backdrop-filter:blur(10px);}
.msg.me{align-self:flex-end;background:linear-gradient(140deg,var(--cyan-d),#0e6a92);color:#fff;border-bottom-right-radius:5px;}
.msg.ai b{color:var(--cyan);}
.typing{display:inline-flex;gap:4px;align-items:center;}
.typing span{width:7px;height:7px;border-radius:50%;background:var(--cyan);animation:blink 1.2s infinite;}
.typing span:nth-child(2){animation-delay:.2s;}.typing span:nth-child(3){animation-delay:.4s;}
@keyframes blink{0%,60%,100%{opacity:.25;}30%{opacity:1;}}

/* Kalkulations-Ergebnis-Karte im Chat */
.calc-card{align-self:stretch;max-width:100%;background:linear-gradient(150deg,rgba(14,40,62,.85),rgba(8,20,36,.85));
  border:1px solid var(--cyan);border-radius:16px;padding:16px;box-shadow:0 0 26px rgba(57,214,255,.2);backdrop-filter:blur(10px);}
.calc-card .lbl{font-size:10px;letter-spacing:1.5px;color:var(--cyan);text-transform:uppercase;font-family:'SF Mono',monospace;}
.calc-card .big{font-size:34px;font-weight:800;color:#fff;margin:3px 0 2px;text-shadow:0 0 16px rgba(57,214,255,.4);}
.calc-card .meta{font-size:12px;color:var(--txt-dim);border-top:1px solid var(--line);padding-top:9px;margin-top:9px;line-height:1.6;}
.calc-card table{width:100%;border-collapse:collapse;font-size:13px;margin-top:10px;}
.calc-card td{padding:7px 0;border-bottom:1px solid var(--line);color:var(--txt);}
.calc-card td:last-child{text-align:right;font-weight:600;white-space:nowrap;color:#fff;}
.calc-card .copybtn{margin-top:12px;width:100%;padding:11px;background:var(--cyan-soft);border:1px solid var(--cyan);
  color:var(--cyan);border-radius:11px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}

/* Eingabezeile */
.composer{position:sticky;bottom:0;background:linear-gradient(0deg,var(--bg) 60%,transparent);padding:10px 14px calc(14px + env(safe-area-inset-bottom));margin-top:8px;}
.composer-in{display:flex;gap:9px;align-items:flex-end;background:var(--glass-2);border:1px solid var(--line);
  border-radius:18px;padding:7px 7px 7px 15px;backdrop-filter:blur(14px);}
.composer textarea{flex:1;background:transparent;border:none;color:var(--txt);font-size:15px;font-family:inherit;
  resize:none;outline:none;max-height:130px;line-height:1.4;padding:8px 0;}
.send{width:42px;height:42px;border-radius:14px;border:none;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));
  color:var(--bg);font-size:19px;cursor:pointer;flex-shrink:0;box-shadow:0 0 16px rgba(57,214,255,.45);display:flex;align-items:center;justify-content:center;}
.send:active{transform:scale(.92);}
.send:disabled{opacity:.4;box-shadow:none;}
.quick{display:flex;gap:8px;overflow-x:auto;padding:0 14px 4px;margin-bottom:2px;-webkit-overflow-scrolling:touch;}
.quick::-webkit-scrollbar{display:none;}
.qchip{flex-shrink:0;padding:9px 14px;border-radius:14px;border:1px solid var(--line);background:var(--glass);
  color:var(--cyan);font-size:12.5px;cursor:pointer;font-family:inherit;white-space:nowrap;backdrop-filter:blur(8px);}
.qchip:active{transform:scale(.95);}

/* --- BUTTONS / FORM --- */
.btn{width:100%;padding:14px;border:none;border-radius:13px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;}
.btn-cyan{background:linear-gradient(140deg,var(--cyan),var(--cyan-d));color:var(--bg);box-shadow:0 0 18px rgba(57,214,255,.4);}
.btn-ghost{background:var(--glass);color:var(--cyan);border:1px solid var(--line);}
input[type=password],input[type=text]{width:100%;padding:14px;border:1px solid var(--line);border-radius:13px;font-size:16px;
  font-family:inherit;background:rgba(8,16,30,.7);color:var(--txt);outline:none;}
input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-soft);}
label{display:block;font-size:12px;font-weight:600;color:var(--txt-dim);margin:14px 0 6px;letter-spacing:.5px;}
.zurueck{color:var(--txt-dim);background:none;border:none;font-size:13px;padding:16px;cursor:pointer;font-family:inherit;width:100%;letter-spacing:.5px;}
.msg-ok{color:var(--green);font-size:13px;font-weight:600;text-align:center;margin-top:10px;min-height:18px;}
.fehler{background:rgba(255,93,108,.12);color:#ff97a1;border:1px solid rgba(255,93,108,.4);padding:12px;border-radius:11px;font-size:13px;margin:10px 0 0;}
.lern-item{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);font-size:13px;gap:10px;color:var(--txt);}
.del{color:var(--red);cursor:pointer;font-size:11px;white-space:nowrap;}

/* --- LOGIN --- */
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;position:relative;z-index:2;}
.login-card{background:var(--glass-2);border:1px solid var(--line);border-radius:24px;padding:40px 30px;max-width:380px;width:100%;
  text-align:center;backdrop-filter:blur(18px);box-shadow:0 20px 60px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.05);}
.login-logo{font-size:40px;font-weight:300;letter-spacing:12px;color:#fff;text-shadow:0 0 22px rgba(57,214,255,.6);}
.login-sub{font-size:9px;letter-spacing:5px;color:var(--cyan);margin:8px 0 30px;font-family:'SF Mono',monospace;}
.login-card input{text-align:center;font-size:20px;letter-spacing:6px;margin-bottom:16px;}
</style>
</head>
<body>
<div class="bg-fx"><div class="glow"></div><div class="glow2"></div><div class="grid"></div><div class="scan"></div></div>
<div class="corner tl"></div><div class="corner tr"></div><div class="corner bl"></div><div class="corner br"></div>

<!-- Hintergrund-Song (eigene Datei nach assets/audio/ hochladen) – läuft max. 1 Minute -->
<audio id="bgm" preload="auto">
  <source src="assets/audio/oh-intro.mp3" type="audio/mpeg">
  <source src="assets/audio/oh-intro.m4a" type="audio/mp4">
  <source src="assets/audio/oh-intro.ogg" type="audio/ogg">
</audio>

<!-- LOGIN -->
<div class="login-wrap" id="loginWrap"<?= $eingeloggt ? ' style="display:none"' : '' ?>>
  <div class="login-card">
    <div class="login-logo">OH</div>
    <div class="login-sub">SYSTEM · ZUGANG</div>
    <form id="loginForm" method="POST">
      <input type="password" name="login_pw" id="loginPw" placeholder="• • • •" autofocus inputmode="text">
      <button type="submit" class="btn btn-cyan">Authentifizieren</button>
    </form>
    <div class="fehler" id="loginErr" style="margin-top:16px;display:<?= !empty($login_fehler) ? 'block' : 'none' ?>">Zugang verweigert.</div>
  </div>
</div>

<!-- BOOT / WILLKOMMEN -->
<div id="boot" style="display:none">
  <div class="ring"><div class="core"></div></div>
  <div class="lines" id="bootLines"></div>
  <div class="greet" id="greet"></div>
</div>

<div class="wrap" style="visibility:hidden" id="app">
<header>
  <div class="brand"><div><div class="mark">OH</div><div class="sub">SYSTEM ONLINE</div></div></div>
  <div class="hbtns">
    <button class="icobtn" id="muteBtn" onclick="toggleMute()" title="Ton an/aus">&#128266;</button>
    <button class="icobtn" onclick="toggleSettings()" title="Einstellungen">&#9881;</button>
    <a href="?logout=1" class="icobtn" title="Abmelden">&#10162;</a>
  </div>
</header>
<div class="statusbar">
  <span><span class="dot"></span>KI AKTIV</span>
  <span id="clock">--:--:--</span>
  <span style="margin-left:auto" id="datum"></span>
</div>

<!-- HOME / DASHBOARD -->
<div id="s-home">
  <div class="dash-head">
    <div class="dash-hi" id="dashHi">Kommandozentrale</div>
    <div class="dash-stats" id="dashStats"></div>
  </div>

  <div class="section-title">// Offene Aufgaben</div>
  <div id="dashTasks">
    <div class="prio-group" data-p="rot"><div class="prio-lbl"><span class="prio-dot rot"></span>SOFORT · heute erledigen</div><div class="prio-list" id="taskRot"></div></div>
    <div class="prio-group" data-p="gelb"><div class="prio-lbl"><span class="prio-dot gelb"></span>BALD · innerhalb 24 h</div><div class="prio-list" id="taskGelb"></div></div>
    <div class="prio-group" data-p="gruen"><div class="prio-lbl"><span class="prio-dot gruen"></span>KANN WARTEN</div><div class="prio-list" id="taskGruen"></div></div>
  </div>

  <div class="section-title">// Werkzeuge</div>
  <div class="tiles">
    <div class="tile aktiv" onclick="openChat('kalk')">
      <div class="tile-ico">&#129518;</div>
      <div class="tile-name">Kalkulator</div>
      <div class="tile-desc">Baustelle beschreiben &rarr; KI rechnet live</div>
    </div>
    <div class="tile" onclick="openChat('marketing')">
      <div class="tile-tag">NEU</div>
      <div class="tile-ico">&#128640;</div>
      <div class="tile-name">Marketing-KI</div>
      <div class="tile-desc">Posts, Anzeigen &amp; Kampagnen</div>
    </div>
    <div class="tile" onclick="openChat('leads')">
      <div class="tile-tag">NEU</div>
      <div class="tile-ico">&#128202;</div>
      <div class="tile-name">Leads</div>
      <div class="tile-desc">Anfragen bewerten &amp; beantworten</div>
    </div>
    <div class="tile" onclick="openChat('angebot')">
      <div class="tile-tag">NEU</div>
      <div class="tile-ico">&#128196;</div>
      <div class="tile-name">Angebote</div>
      <div class="tile-desc">Angebotstexte automatisch</div>
    </div>
    <div class="tile" onclick="openChat('bewertung')">
      <div class="tile-tag">NEU</div>
      <div class="tile-ico">&#11088;</div>
      <div class="tile-name">Bewertungen</div>
      <div class="tile-desc">Google-Antworten formulieren</div>
    </div>
    <div class="tile" onclick="openChat('berater')">
      <div class="tile-ico">&#129504;</div>
      <div class="tile-name">Berater</div>
      <div class="tile-desc">Dein KI-Sparringspartner</div>
    </div>
  </div>
</div>

<!-- SETTINGS -->
<div id="s-settings" style="display:none">
  <div class="card">
    <h2>&#9881; KI-Schlüssel (Server)</h2>
    <p class="intro">Dein Anthropic-Schlüssel von <b>console.anthropic.com</b>. Wird sicher auf dem Server gespeichert und auch für die Automatik (Cron) genutzt.</p>
    <label>Anthropic API-Schlüssel</label>
    <input type="password" id="apiIn" placeholder="sk-ant-... (leer lassen = unverändert)">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveKey()">Speichern</button>
    <div id="keyMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#9993; Gmail-Versand</h2>
    <p class="intro">Für automatische E-Mails (Angebote, Follow-ups, Bewertungs-Anfragen) über <b>oh.haustechnik@gmail.com</b>. Du brauchst ein <b>App-Passwort</b> (Google-Konto → Sicherheit → 2-Faktor → App-Passwörter), NICHT Dein normales Passwort.</p>
    <label>Gmail-Adresse</label>
    <input type="text" id="gmailUser" placeholder="oh.haustechnik@gmail.com">
    <label>App-Passwort (16 Zeichen)</label>
    <input type="password" id="gmailPass" placeholder="•••• •••• •••• ••••  (leer = unverändert)">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveGmail()">Speichern</button>
    <div id="gmailMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#128218; Gelernte Korrekturen (Kalkulator)</h2>
    <p class="intro">Das System lernt aus Deinen Korrekturen für genauere Preise.</p>
    <div id="lernListe"></div>
  </div>
  <button class="zurueck" onclick="goHome()">&larr; Zurück zur Kommandozentrale</button>
</div>

<!-- CHAT (universal) -->
<div id="s-chat" style="display:none">
  <div class="chat-wrap">
    <div class="chat-head">
      <div class="av" id="chatIco">&#129518;</div>
      <div><div class="nm" id="chatName">Kalkulator</div><div class="st">&#9679; ONLINE · bereit</div></div>
    </div>
    <div class="chat-log" id="chatLog"></div>
  </div>
  <div class="quick" id="quickRow"></div>
  <div class="composer">
    <div class="composer-in">
      <textarea id="chatIn" rows="1" placeholder="Schreib einfach drauf los…"></textarea>
      <button class="send" id="sendBtn" onclick="send()">&#10148;</button>
    </div>
    <button class="zurueck" onclick="goHome()">&larr; Kommandozentrale</button>
  </div>
</div>

</div><!-- /app -->

<script>
/* ============ KONFIG ============ */
const MODEL='claude-sonnet-4-5';
const gl=id=>document.getElementById(id);
const getKey=()=>localStorage.getItem('oh_key')||'';
const getLern=()=>{try{return JSON.parse(localStorage.getItem('oh_lern')||'[]');}catch(e){return[];}};
const setLernS=a=>localStorage.setItem('oh_lern',JSON.stringify(a));
const eur=n=>(+n||0).toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2})+' €';

/* ============ KALKULATIONS-WISSEN ============ */
const KALK_WISSEN=`KALKULATIONS-LOGIK OH Haustechnik (Kleinunternehmer, 0% USt):
GRUNDLAGE: Stundensatz 68€×2=136€/Std, Arbeitstag=8,5h, also ca. 1.156€ Arbeitskosten pro Manntag.

PRÄZISE FAUSTFORMELN (UNTERPUTZ-Sanierung, Endpreis inkl. Material):
- 100 m² Unterputz = 8-9 Manntage  = 18.000-20.000 €
- 150 m² Unterputz = 11-12 Manntage = 23.000-26.000 €
- 200 m² Unterputz = 14-16 Manntage = 28.000-33.000 €
- Dazwischen/darüber sinnvoll interpolieren, nie übertreiben.
- AUFPUTZ: immer ca. 40 % GÜNSTIGER als Unterputz (weniger Schlitzen/Stemmen). Also Unterputz-Preis × 0,6.
- GEMISCHT: zwischen Unterputz und Aufputz schätzen.

ABLAUF: Rohmontage (Demontage, anzeichnen, fräsen, schlitzen, stemmen, Leitungen, Verteiler) + Fertigmontage (Schalter/Steckdosen/Lampen, Verteiler anklemmen, messen, prüfen) + Puffer.
Altbau/Demontage kostet extra Zeit – im Zweifel oberes Ende der Spanne.

MATERIAL (realistisch, NICHT übertreiben):
- NYM-J 3×1,5: ca. 1,5 m/m² +10% Verschnitt
- Separate Verbraucher +10m Reserve: Herd→NYM5×2,5 | Spülm/Waschm/Trockner→NYM3×2,5 | DLE→NYM4×6
- Dosen: 1 Steckdose=1 Dose, Doppel=2 Dosen
- Verteiler: Hager VU48NC, 2×FI 40A, 12×LSB16, 1×LSB16 3pol, ÜSS Typ2
- Materialanteil grob: 100 m² ≈ 1.500-2.200 €, 150 m² ≈ 2.200-3.000 €, 200 m² ≈ 3.000-4.000 €
- Material immer +10% Aufschlag (Marge), aber ehrlich kalkulieren
- Anfahrt >10km: 100-200€ Pauschale

VERHANDLUNG: bei wenig Auftragslage 1.000-1.500€ runter möglich (Zielpreis + Minimalpreis).
ZIEL: perfekte, sofort versendbare Angebote – konkret, sauber, ohne Übertreibung.`;

const FIRMA=`FIRMA: OH Haustechnik, Inhaber arbeitet als Elektriker/Haustechniker im Raum Nürnberg.
Leistungen: Elektroinstallation, Netzwerkverkabelung, Schutz-/Sicherheitstechnik. Kleinunternehmer (0% USt).
Stil: bodenständig, ehrlich, handwerklich, regional. Kunde steht im Mittelpunkt, kein Marketing-Blabla.`;

/* ============ MODI ============ */
const MODI={
  kalk:{ name:'Kalkulator', ico:'\u{1F9EE}',
    quick:['Wohnung 3 Zimmer Altbau, Unterputz sanieren','Neubau EFH komplett','Nur Verteiler tauschen','Smart-Home nachrüsten'],
    system(){
      const lern=getLern(); const lT=lern.length?`\n\nGELERNTE KORREKTUREN (unbedingt beachten):\n- ${lern.join('\n- ')}`:'';
      return `Du bist der digitale Kalkulator von OH Haustechnik. Du sprichst locker, direkt und auf Augenhöhe mit dem Chef (Du-Form, kurz).
${FIRMA}
${KALK_WISSEN}${lT}

ARBEITSWEISE:
- Der Chef beschreibt eine Baustelle in eigenen Worten – oft unvollständig. Stell höchstens 1-2 kurze Rückfragen, wenn etwas Wichtiges fehlt (z.B. Aufputz/Unterputz, Material durch uns oder bauseits). Wenn genug Info da ist, RECHNE einfach.
- Wenn Du eine vollständige Kalkulation hast, gib eine kurze Erklärung in normalem Text UND danach EINEN Block in genau diesem Format (nichts dahinter):
<calc>{"zielpreis":<n>,"minimalpreis":<n>,"manntage":<n>,"arbeitsstunden":<n>,"arbeitskosten":<n>,"fahrtkosten":<n>,"material_mit_aufschlag":<n>,"denkweg":"<1 Satz>","material_liste":[{"pos":"x","menge":"x"}],"angebotstext":"<fertiger Angebotstext>"}</calc>
- Zahlen sind reine Zahlen ohne €. Bei bauseits material_liste leer lassen.
- Sei der proaktive Sparringspartner: weise auf Risiken hin (Altbau, Demontage), schlag Verhandlungsspielraum vor.`;
    }},
  marketing:{ name:'Marketing-KI', ico:'\u{1F680}',
    quick:['Instagram-Post für ein fertiges Bad-Projekt','Google-Anzeige Elektriker Nürnberg','5 Reel-Ideen für diese Woche','Aktion für Winter-Flaute'],
    system(){return `Du bist die Marketing-KI von OH Haustechnik – ein cleverer, regionaler Online-Marketing-Experte für Handwerker.
${FIRMA}
AUFGABE: Hilf dem Chef, mehr Anfragen zu bekommen. Du schreibst sofort einsatzbereite Inhalte: Instagram-/Facebook-Posts (mit Hashtags & Emojis, regional Nürnberg), Google-Anzeigentexte, Reel-/Story-Ideen, Flyer-Texte, Aktionen.
STIL: handwerklich-bodenständig, kein leeres Buzzword-Marketing, vertrauenswürdig, lokal. Immer konkret und fertig zum Rauskopieren. Frag kurz nach, wenn ein Detail (Projektfoto, Leistung, Zielgruppe) fehlt, sonst leg direkt los. Du-Form mit dem Chef.`;}},
  leads:{ name:'Leads', ico:'\u{1F4CA}',
    quick:['Anfrage bewerten (Text einfügen)','Erstantwort schreiben','Nachfass-Nachricht nach 3 Tagen','Lead ist abgesprungen – zurückholen'],
    system(){return `Du bist der Lead-Manager von OH Haustechnik. Du hilfst dem Chef, eingehende Kundenanfragen zu bearbeiten.
${FIRMA}
AUFGABE: (1) Anfragen bewerten – heiß/warm/kalt + kurze Begründung + Priorität. (2) Professionelle, freundliche Antworten formulieren (WhatsApp/E-Mail, Du oder Sie je nach Anfrage). (3) Nachfass-Nachrichten texten, die nicht aufdringlich wirken. (4) Vorschlagen, welche Infos noch fehlen, um ein Angebot zu machen.
STIL: schnell, klar, verkaufsstark aber ehrlich. Gib Antworten fertig zum Rauskopieren. Du-Form mit dem Chef.`;}},
  angebot:{ name:'Angebote', ico:'\u{1F4C4}',
    quick:['Angebot aus Stichpunkten','Angebot freundlicher machen','Nachtrag formulieren','Angebot kürzen'],
    system(){return `Du bist der Angebots-Assistent von OH Haustechnik. Aus Stichpunkten oder einer Kalkulation machst Du saubere, professionelle Angebotstexte.
${FIRMA}
Wichtig: Kleinunternehmer = 0% USt, kein USt-Ausweis. Struktur: Anrede, Leistungsbeschreibung, Hinweis zu Material (im Preis enthalten oder bauseits), Preis netto, Gültigkeit, freundlicher Abschluss. Liefere den Text fertig zum Rauskopieren. Du-Form mit dem Chef bei Rückfragen.`;}},
  bewertung:{ name:'Bewertungen', ico:'⭐',
    quick:['Antwort auf 5-Sterne-Bewertung','Antwort auf schlechte Bewertung','Kunden um Bewertung bitten','Mehrere Antworten generieren'],
    system(){return `Du bist der Reputations-Assistent von OH Haustechnik. Du schreibst Antworten auf Google-Bewertungen.
${FIRMA}
Bei guten Bewertungen: herzlich, persönlich, danke. Bei schlechten: professionell, deeskalierend, lösungsorientiert, niemals streiten. Immer regional & menschlich. Liefere Antworten fertig zum Rauskopieren. Du-Form mit dem Chef bei Rückfragen.`;}},
  berater:{ name:'Berater', ico:'\u{1F9E0}',
    quick:['Wie gewinne ich mehr Aufträge?','Soll ich Preise erhöhen?','Tagesplanung für heute','Idee gegen Sommerloch'],
    system(){return `Du bist der persönliche Business-Berater & Sparringspartner des Chefs von OH Haustechnik – wie ein cleverer Mitgründer, der Handwerk, Zahlen und Marketing versteht.
${FIRMA}
Du denkst mit, gibst ehrliche, umsetzbare Tipps zu Aufträgen, Preisen, Zeit, Marketing, Wachstum. Kurz, konkret, motivierend. Du-Form, auf Augenhöhe.`;}}
};

/* ============ STATE ============ */
let mode='kalk';
let history={}; // pro modus: [{role,content}]
let leadsCache=[];
let lastTasks=null;
let serverCfg={has_anthropic:false,has_gmail_pass:false,gmail_user:''};

/* ============ SERVER-API ============ */
async function api(action,data){
  const fd=new FormData();fd.append('action',action);fd.append('data',JSON.stringify(data||{}));
  const r=await fetch(window.location.pathname,{method:'POST',body:fd});
  return r.json();
}

/* ============ DASHBOARD ============ */
async function loadDashboard(){
  try{
    const d=await api('dashboard');
    leadsCache=d.leads||[];
    renderStats(d.stats||{});
    renderTasks(d.tasks||{rot:[],gelb:[],gruen:[]});
  }catch(e){/* offline – Dashboard bleibt leer */}
  try{serverCfg=await api('config_get');}catch(e){}
}
function renderStats(s){
  gl('dashStats').innerHTML=
    `<div class="stat"><div class="n">${s.leads||0}</div><div class="l">Leads gesamt</div></div>`+
    `<div class="stat hot"><div class="n">${s.hot||0}</div><div class="l">🔥 Heiß &amp; offen</div></div>`;
}
function renderTasks(t){
  lastTasks=t;
  const map={rot:'taskRot',gelb:'taskGelb',gruen:'taskGruen'};
  let total=0;
  Object.keys(map).forEach(p=>{
    const list=t[p]||[]; total+=list.length;
    gl(map[p]).innerHTML=list.length?list.map(x=>
      `<div class="task ${p}" onclick="openTask('${x.id}','${x.typ}')">
         <div class="tx"><div class="tt">${esc(x.titel)}</div><div class="ta">${esc(x.aktion)} →</div></div>
         <div class="go">›</div>
       </div>`).join(''):'<div class="prio-empty">Nichts offen.</div>';
  });
  if(total===0){gl('taskGruen').innerHTML='<div class="prio-empty">Alles erledigt, Chef. 🎯 Neue Anfragen erscheinen hier automatisch.</div>';}
}
function leadById(id){return leadsCache.find(l=>l.id===id);}
function leadInfo(l){
  if(!l)return '';
  return `Lead-Infos:\n- Name: ${l.name||'?'}\n- E-Mail: ${l.email||'?'}\n- Telefon: ${l.telefon||'?'}\n- Leistung: ${l.kategorie||'?'}\n- Größe: ${l.objektgroesse||'?'}\n- Zeitraum: ${l.zeitraum||'?'}\n- Ort: ${(l.plz||'')+' '+(l.ort||'')}\n- Details: ${l.details||'-'}`;
}
function openTask(id,typ){
  const l=leadById(id);
  if(typ==='bewertung'){
    openChat('bewertung','Schreib eine freundliche Bewertungs-Anfrage per E-Mail an diesen abgeschlossenen Kunden:\n\n'+leadInfo(l));
  }else if(typ==='followup'){
    openChat('leads','Schreib eine freundliche Follow-up-Nachricht (das Angebot ist 2 Tage raus, noch keine Antwort) an:\n\n'+leadInfo(l));
  }else{
    openChat('kalk',(l?l.details||l.kategorie:'')+'\n\n['+leadInfo(l)+']');
  }
}

/* ============ INTRO-SOUND (JARVIS-Boot, WebAudio) ============ */
let introPlayed=false, ohCtx=null;
function playIntro(){
  if(introPlayed)return;
  try{
    const AC=window.AudioContext||window.webkitAudioContext; if(!AC)return;
    if(!ohCtx)ohCtx=new AC();
    const ctx=ohCtx;
    // Browser blockiert Ton ohne Berührung -> erst abspielen, wenn Context wirklich läuft
    if(ctx.state==='suspended'){ ctx.resume().then(()=>renderIntro(ctx)).catch(()=>{}); return; }
    renderIntro(ctx);
  }catch(e){}
}
function renderIntro(ctx){
  if(introPlayed||ctx.state!=='running')return;
  introPlayed=true;
  try{
    const now=ctx.currentTime;
    const master=ctx.createGain();master.gain.value=0.0001;master.connect(ctx.destination);
    master.gain.exponentialRampToValueAtTime(0.18,now+0.15);
    master.gain.exponentialRampToValueAtTime(0.0001,now+4.8);
    // Aufsteigender Power-Sweep
    const o1=ctx.createOscillator();o1.type='sawtooth';
    o1.frequency.setValueAtTime(70,now);o1.frequency.exponentialRampToValueAtTime(420,now+2.2);
    const f=ctx.createBiquadFilter();f.type='lowpass';f.frequency.setValueAtTime(300,now);
    f.frequency.exponentialRampToValueAtTime(3500,now+2.4);
    o1.connect(f);f.connect(master);o1.start(now);o1.stop(now+2.6);
    // Hologramm-Pad
    [220,277,330].forEach((fr,i)=>{const o=ctx.createOscillator();o.type='sine';o.frequency.value=fr;
      const g=ctx.createGain();g.gain.value=0;g.gain.linearRampToValueAtTime(0.06,now+1+i*0.15);
      g.gain.linearRampToValueAtTime(0,now+4.6);o.connect(g);g.connect(master);o.start(now+1);o.stop(now+4.6);});
    // 3 Tech-Beeps
    [0.2,0.55,0.9].forEach((tt,i)=>{const o=ctx.createOscillator();o.type='square';
      o.frequency.value=880+i*220;const g=ctx.createGain();g.gain.value=0;
      g.gain.linearRampToValueAtTime(0.05,now+tt);g.gain.exponentialRampToValueAtTime(0.0001,now+tt+0.12);
      o.connect(g);g.connect(master);o.start(now+tt);o.stop(now+tt+0.14);});
  }catch(e){}
}

/* ============ BOOT / WILLKOMMEN ============ */
let currentTitle='Chef';
function boot(){
  const titel=['großer Meister','große Herrschaft','Chef','Kommandant','Boss'];
  const t=titel[Math.floor(Math.random()*titel.length)];
  currentTitle=t;
  const seq=['> Initialisiere OH-System…','> KI-Kerne geladen ✓','> Module: Kalkulator · Marketing · Leads ✓','> Verbindung gesichert ✓','> Alle Systeme bereit ✓'];
  const lines=gl('bootLines'); let i=0;
  const iv=setInterval(()=>{
    if(i<seq.length){lines.innerHTML+=seq[i]+'<br>';i++;}
    else{
      clearInterval(iv);
      const g=gl('greet');
      g.innerHTML=`Willkommen, <b>${t}</b>.<small>OH HAUSTECHNIK · SYSTEM ZU DEINEN DIENSTEN</small>`;
      g.style.opacity='1';
      setTimeout(async ()=>{
        gl('boot').style.opacity='0';
        gl('app').style.visibility='visible';
        await loadDashboard();
        speakDashboard();
        setTimeout(()=>gl('boot').remove(),700);
      },1700);
    }
  },330);
}

/* ============ UHR ============ */
function clock(){
  const d=new Date();
  const p=n=>String(n).padStart(2,'0');
  gl('clock').textContent=`${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
  gl('datum').textContent=d.toLocaleDateString('de-DE',{weekday:'short',day:'2-digit',month:'2-digit'});
}

/* ============ NAVIGATION ============ */
function showSection(s){
  ['home','settings','chat'].forEach(id=>{const el=gl('s-'+id);if(el)el.style.display='none';});
  gl('s-'+s).style.display='block';
  window.scrollTo({top:0,behavior:'smooth'});
}
function goHome(){showSection('home');}
async function toggleSettings(){
  if(gl('s-settings').style.display==='block'){goHome();}
  else{
    gl('apiIn').value='';gl('gmailPass').value='';
    try{const c=await api('config_get');serverCfg=c;gl('gmailUser').value=c.gmail_user||'';}catch(e){}
    renderLL();showSection('settings');
  }
}
async function saveKey(){
  const v=gl('apiIn').value.trim();
  if(!v){gl('keyMsg').textContent='Bitte Schlüssel eingeben.';return;}
  await api('config_set',{anthropic_key:v});
  serverCfg.has_anthropic=true;gl('apiIn').value='';
  gl('keyMsg').textContent='✓ Gespeichert (Server)';
  setTimeout(()=>gl('keyMsg').textContent='',2500);
}
async function saveGmail(){
  const u=gl('gmailUser').value.trim(),p=gl('gmailPass').value.trim();
  await api('config_set',{gmail_user:u,gmail_pass:p});
  if(u)serverCfg.gmail_user=u; if(p)serverCfg.has_gmail_pass=true; gl('gmailPass').value='';
  gl('gmailMsg').textContent='✓ Gmail gespeichert';
  setTimeout(()=>gl('gmailMsg').textContent='',2500);
}
function renderLL(){
  const l=getLern(),el=gl('lernListe');
  el.innerHTML=l.length?l.map((t,i)=>`<div class="lern-item"><span>• ${esc(t)}</span><span class="del" onclick="delL(${i})">löschen</span></div>`).join(''):'<p style="font-size:13px;color:var(--txt-dim)">Noch keine.</p>';
}
function delL(i){const l=getLern();l.splice(i,1);setLernS(l);renderLL();}

/* ============ CHAT ÖFFNEN ============ */
function openChat(m,prefill){
  mode=m; const cfg=MODI[m];
  gl('chatName').textContent=cfg.name;
  gl('chatIco').innerHTML=cfg.ico;
  gl('quickRow').innerHTML=cfg.quick.map(q=>`<button class="qchip" onclick="quick(this)">${esc(q)}</button>`).join('');
  if(!history[m]){history[m]=[];}
  renderLog();
  if(history[m].length===0){
    const greet={
      kalk:'Servus Chef! 🧮 Beschreib mir einfach die Baustelle – egal wie, in eigenen Worten. Ich rechne Dir Manntage, Material und Preis aus. Frag mich auch gern, wie wir die Kalkulation noch besser hinbekommen.',
      marketing:'Bereit, Chef! 🚀 Sag mir, was Du bewerben willst – ein fertiges Projekt, eine Leistung oder eine Aktion – und ich schreib Dir Posts, Anzeigen und Ideen, die in Nürnberg ziehen.',
      leads:'Leg los, Chef! 📊 Füg eine Kundenanfrage ein – ich bewerte sie (heiß/warm/kalt) und schreib Dir gleich die passende Antwort.',
      angebot:'Bereit! 📄 Gib mir Stichpunkte oder eine Kalkulation und ich mach ein sauberes Angebot draus.',
      bewertung:'Bereit! ⭐ Kopier mir die Google-Bewertung rein und ich formulier Dir die perfekte Antwort.',
      berater:'Ich bin da, Chef. 🧠 Erzähl, was ansteht – Aufträge, Preise, Zeit, Wachstum. Ich denk mit.'
    }[m];
    pushMsg('ai',greet);
  }
  showSection('chat');
  if(prefill){gl('chatIn').value=prefill;autoGrow();}
  setTimeout(()=>gl('chatIn').focus(),300);
}
function quick(b){gl('chatIn').value=b.textContent;gl('chatIn').focus();autoGrow();}

/* ============ RENDERING ============ */
function esc(s){return (s||'').replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));}
function fmt(s){return esc(s).replace(/\*\*(.+?)\*\*/g,'<b>$1</b>').replace(/\n/g,'<br>');}
function pushMsg(role,text,raw){history[mode].push({role:role==='ai'?'assistant':'user',content:raw||text,_render:text});renderLog();}
function renderLog(){
  const log=gl('chatLog'); log.innerHTML='';
  history[mode].forEach(m=>{
    const txt=m._render!==undefined?m._render:m.content;
    if(txt){
      const d=document.createElement('div');
      d.className='msg '+(m.role==='assistant'?'ai':'me');
      d.innerHTML=fmt(txt);
      log.appendChild(d);
    }
    if(m._calc){log.appendChild(calcCard(m._calc));}
  });
  window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'});
}
function calcCard(k){
  const div=document.createElement('div'); div.className='calc-card';
  let rows=`<tr><td>Arbeit (${k.arbeitsstunden||'?'} Std × 136€)</td><td>${eur(k.arbeitskosten)}</td></tr>`;
  if(+k.fahrtkosten>0)rows+=`<tr><td>Anfahrt</td><td>${eur(k.fahrtkosten)}</td></tr>`;
  if(+k.material_mit_aufschlag>0)rows+=`<tr><td>Material (+10%)</td><td>${eur(k.material_mit_aufschlag)}</td></tr>`;
  let mat='';
  if(k.material_liste&&k.material_liste.length){
    mat='<div class="meta"><b style="color:var(--cyan)">📦 Material:</b><br>'+k.material_liste.map(m=>`${esc(m.pos)} — ${esc(m.menge||'')}`).join('<br>')+'</div>';
  }
  div.innerHTML=`
    <div class="lbl">Zielpreis · netto · 0% USt</div>
    <div class="big">${eur(k.zielpreis)}</div>
    <div class="meta">${k.manntage||'?'} Manntage · verhandelbar bis <b style="color:#fff">${eur(k.minimalpreis)}</b>${k.denkweg?'<br>💭 '+esc(k.denkweg):''}</div>
    <table>${rows}</table>${mat}
    ${k.angebotstext?`<button class="copybtn" onclick='copyTxt(this,${JSON.stringify(k.angebotstext)})'>📋 Angebotstext kopieren</button>`:''}`;
  return div;
}
function copyTxt(btn,t){navigator.clipboard.writeText(t).then(()=>{const o=btn.textContent;btn.textContent='✓ Kopiert!';setTimeout(()=>btn.textContent=o,2000);});}

/* ============ SENDEN ============ */
function autoGrow(){const t=gl('chatIn');t.style.height='auto';t.style.height=Math.min(t.scrollHeight,130)+'px';}
gl('chatIn').addEventListener('input',autoGrow);
gl('chatIn').addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey&&!isMobile()){e.preventDefault();send();}});
function isMobile(){return /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);}

async function send(){
  const inp=gl('chatIn'); const text=inp.value.trim();
  if(!text)return;
  if(!serverCfg.has_anthropic && !getKey()){pushMsg('ai','⚙️ Kein API-Schlüssel hinterlegt. Tipp oben rechts auf das Zahnrad und trag Deinen Anthropic-Schlüssel ein.');return;}
  pushMsg('me',text); inp.value=''; autoGrow();
  gl('sendBtn').disabled=true;
  // Typing-Indikator
  const log=gl('chatLog');const tp=document.createElement('div');tp.className='msg ai';tp.innerHTML='<span class="typing"><span></span><span></span><span></span></span>';log.appendChild(tp);
  window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'});
  try{
    const msgs=history[mode].map(m=>({role:m.role,content:m.content}));
    const payload=JSON.stringify({model:MODEL,max_tokens:2500,system:MODI[mode].system(),messages:msgs});
    const fd=new FormData();fd.append('ki_request',payload);fd.append('api_key',getKey());
    const r=await fetch(window.location.pathname,{method:'POST',body:fd});
    const d=await r.json();
    tp.remove();
    if(d.error){throw new Error(d.error.message||'Fehler');}
    let txt=d.content.map(i=>i.type==='text'?i.text:'').join('').trim();
    // Kalkulations-Block extrahieren
    const cm=txt.match(/<calc>([\s\S]*?)<\/calc>/);
    if(cm){
      const vor=txt.slice(0,cm.index).trim();
      let k=null; try{k=JSON.parse(cm[1]);}catch(e){}
      if(k){history[mode].push({role:'assistant',content:txt,_render:vor,_calc:k});}
      else{history[mode].push({role:'assistant',content:txt,_render:txt});}
      renderLog();
    }else{
      pushMsg('ai',txt);
    }
  }catch(e){
    tp.remove();
    let m=(e.message||'')+'';
    let out='⚠️ ';
    if(m.includes('401')||m.includes('authentication'))out+='API-Schlüssel ungültig. Unter dem Zahnrad prüfen.';
    else if(m.includes('credit')||m.includes('balance'))out+='Guthaben aufladen unter console.anthropic.com';
    else out+='Fehler: '+(m||'Bitte nochmal versuchen.');
    pushMsg('ai',out);
  }
  gl('sendBtn').disabled=false;
}

/* ============ AUDIO (eigener Song) + STIMME ============ */
let audioUnlocked=false, isMuted=false, stopTimer=null, fadeTimer=null;
const SONG_DAUER=60; // Sekunden – Song läuft max. 1 Minute
function unlockAudio(){
  const b=gl('bgm'); if(!b)return;
  b.muted=isMuted; b.volume=isMuted?0:0.22;
  const p=b.play(); if(p&&p.catch)p.catch(()=>{});
  audioUnlocked=true;
  scheduleStop();
  // Sprachausgabe „aufwecken“ (iOS verlangt eine Geste)
  try{ if('speechSynthesis' in window){speechSynthesis.cancel();speechSynthesis.speak(new SpeechSynthesisUtterance(' '));} }catch(e){}
}
// Song nach 1 Minute sanft ausblenden und stoppen
function scheduleStop(){
  clearTimeout(stopTimer); clearTimeout(fadeTimer);
  const b=gl('bgm'); if(!b)return;
  fadeTimer=setTimeout(()=>{
    let v=b.volume;
    const fade=setInterval(()=>{
      v-=0.03;
      if(v<=0||isMuted){clearInterval(fade);b.pause();b.currentTime=0;b.volume=isMuted?0:0.22;}
      else b.volume=v;
    },120);
  },(SONG_DAUER-3)*1000); // letzte 3 Sek ausblenden
  stopTimer=setTimeout(()=>{const bb=gl('bgm');if(bb){bb.pause();bb.currentTime=0;}},SONG_DAUER*1000);
}
function toggleMute(){
  isMuted=!isMuted; const b=gl('bgm');
  if(b){b.muted=isMuted; b.volume=isMuted?0:0.22; if(!isMuted&&b.paused)b.play().catch(()=>{});}
  if(isMuted)try{speechSynthesis.cancel();}catch(e){}
  gl('muteBtn').innerHTML=isMuted?'&#128263;':'&#128266;';
}
function duck(on){ const b=gl('bgm'); if(b&&!isMuted) b.volume= on?0.07:0.22; }
function speak(txt){
  try{
    if(isMuted||!('speechSynthesis' in window))return;
    speechSynthesis.cancel();
    const u=new SpeechSynthesisUtterance(txt);
    u.lang='de-DE'; u.rate=1; u.pitch=1;
    const vs=speechSynthesis.getVoices().filter(v=>/de(-|_)/i.test(v.lang));
    if(vs.length)u.voice=vs[0];
    duck(true); u.onend=()=>duck(false); u.onerror=()=>duck(false);
    speechSynthesis.speak(u);
  }catch(e){}
}
function cleanSpeech(s){return (s||'').replace(/[\u{1F000}-\u{1FAFF}\u{2600}-\u{27BF}\u{2190}-\u{21FF}\u{2B00}-\u{2BFF}]/gu,'').replace(/\s+/g,' ').trim();}
function speakDashboard(){
  const t=lastTasks||{rot:[],gelb:[],gruen:[]};
  let s='Willkommen zurück, '+(currentTitle||'Chef')+'. ';
  const r=(t.rot||[]).length, g=(t.gelb||[]).length;
  if(r>0){ s+=r+(r===1?' dringende Aufgabe':' dringende Aufgaben')+' sofort. ';
    (t.rot||[]).slice(0,3).forEach(x=>{s+=cleanSpeech(x.titel)+'. ';}); }
  else { s+='Keine dringenden Aufgaben. '; }
  if(g>0) s+=g+(g===1?' weitere Aufgabe':' weitere Aufgaben')+' bald. ';
  s+='Ich bin bereit, wenn Du es bist.';
  speak(s);
}

/* ============ LOGIN (per AJAX, damit der Song bei der Geste startet) ============ */
const LOGGED_IN=<?= $eingeloggt ? 'true' : 'false' ?>;
function startSession(){
  gl('loginWrap').style.display='none';
  gl('boot').style.display='flex';
  boot();
}
gl('loginForm').addEventListener('submit',async function(e){
  e.preventDefault();
  unlockAudio(); // genau hier (Geste) startet Dein Song
  const pw=gl('loginPw').value;
  try{
    const r=await api('login',{pw});
    if(r&&r.ok){ gl('loginErr').style.display='none'; startSession(); }
    else { gl('loginErr').style.display='block'; const b=gl('bgm'); if(b)b.pause(); }
  }catch(err){ gl('loginErr').style.display='block'; }
});

/* ============ START ============ */
clock();setInterval(clock,1000);
if(LOGGED_IN){ startSession(); }   // schon eingeloggt -> direkt rein (Ton ab 1. Tipp)
// Schon eingeloggt (Seite neu geladen): Song beim ersten Antippen nachholen
['touchstart','click'].forEach(ev=>document.addEventListener(ev,function once(){if(LOGGED_IN&&!audioUnlocked)unlockAudio();document.removeEventListener(ev,once);},{once:true}));
</script>
</body>
</html>
