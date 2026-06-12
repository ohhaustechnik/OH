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
        $_SESSION['login_time'] = time();
    } else {
        $login_fehler = true;
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Sicherheit: kein Dauer-Login. Nach Inaktivität automatisch abmelden,
// damit bei jedem Start das Passwort neu verlangt wird.
$OH_TIMEOUT = 1800; // 30 Minuten
if (!empty($_SESSION['eingeloggt'])) {
    if (empty($_SESSION['login_time']) || (time() - $_SESSION['login_time']) > $OH_TIMEOUT) {
        $_SESSION = [];
    }
}

// AJAX-Login (vor der Session-Schranke)
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json; charset=utf-8');
    $in = json_decode($_POST['data'] ?? '{}', true) ?: [];
    if (($in['pw'] ?? '') === $PASSWORT) {
        $_SESSION['eingeloggt'] = true;
        $_SESSION['login_time'] = time();
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
    session_write_close(); // Session-Sperre früh lösen, damit lange KI-Analysen parallele Abfragen nicht blockieren
    $a = $_POST['action'];
    $in = json_decode($_POST['data'] ?? '{}', true) ?: [];

    if ($a === 'dashboard') {
        $ct = oh_company_tasks();
        echo json_encode([
            'offen'    => $ct['offen'],
            'erledigt' => $ct['erledigt'],
            'warnung'  => $ct['warnung'],
            'anzahl'   => $ct['anzahl'],
            'leads' => oh_read('leads', []),
            'stats' => [
                'leads'  => count(oh_read('leads', [])),
                'hot'    => count(array_filter(oh_read('leads', []), function($l){ return ($l['stufe'] ?? '') === 'HOT' && ($l['status'] ?? '') === 'neu'; })),
            ],
            'ki_alert' => oh_read('ki_status', ['alert' => false]),
            'mert'     => oh_read('mert_plan', []),
            'agenten'  => oh_read('agenten', []),
            'aktivitaet' => array_slice(oh_read('aktivitaet', []), 0, 40),
            'wissen'   => oh_wissen_summary(),
        ]);
    } elseif ($a === 'mert_fresh') {
        $merr = null;
        $plan = oh_mert_briefing($merr);
        echo json_encode($plan !== null ? ['ok' => true, 'mert' => oh_read('mert_plan', [])] : ['ok' => false, 'error' => $merr]);
    } elseif ($a === 'agenten_runde') {
        $aerr = null;
        $r = oh_agenten_runde($aerr);
        echo json_encode($r !== null ? ['ok' => true, 'agenten' => $r] : ['ok' => false, 'error' => $aerr]);
    } elseif ($a === 'agent_context') {
        echo json_encode(['ctx' => oh_agent_context($in['agent'] ?? '')]);
    } elseif ($a === 'lex_invoices') {
        $le = null;
        $inv = oh_lex_open_invoices($le);
        echo json_encode($inv !== null ? ['ok' => true, 'invoices' => $inv] : ['ok' => false, 'error' => $le]);
    } elseif ($a === 'self_update') {
        $ue = null;
        $log = oh_self_update($ue);
        echo json_encode(['ok' => true, 'log' => $log]);
    } elseif ($a === 'website_reco') {
        echo json_encode(['ok' => true, 'reco' => oh_read('website_reco', [])]);
    } elseif ($a === 'website_analyze') {
        $werr = null;
        $r = oh_website_analyze($werr);
        echo json_encode($r !== null ? ['ok' => true, 'reco' => $r] : ['ok' => false, 'error' => $werr]);
    } elseif ($a === 'website_apply' || $a === 'website_later' || $a === 'website_dismiss') {
        $id = $in['id'] ?? '';
        $reco = oh_read('website_reco', []);
        $newStatus = $a === 'website_apply' ? 'uebernommen' : ($a === 'website_dismiss' ? 'abgelehnt' : 'spaeter');
        $hit = null;
        foreach ($reco as &$rr) { if (($rr['id'] ?? '') === $id) { $rr['status'] = $newStatus; $hit = $rr; } }
        unset($rr);
        oh_write('website_reco', $reco);
        if ($a === 'website_apply' && $hit) oh_log_activity('dilara', 'Website-Optimierung vorgemerkt: ' . ($hit['titel'] ?? ''));
        echo json_encode(['ok' => true, 'msg' => $a === 'website_apply' ? 'Notiert, Chef! Dilara hat den Vorschlag vorbereitet. (Automatisches Live-Ändern der Website bauen wir als sicheren Baustein als Nächstes.)' : '']);
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
            'ads_customer_id'=> $c['ads_customer_id'] ?? '',
            'ads_login_customer_id' => $c['ads_login_customer_id'] ?? '',
            'has_ads'        => !empty($c['ads_developer_token']) && !empty($c['ads_refresh_token']),
            'site_url'       => $c['site_url'] ?? '',
            'wa_verify_token'=> $c['wa_verify_token'] ?? 'oh-wa',
            'wa_phone_id'    => $c['wa_phone_id'] ?? '',
            'has_wa'         => !empty($c['wa_token']),
            'has_lexware'    => !empty($c['lexware_key']),
        ]);
    } elseif ($a === 'config_set') {
        oh_config_set([
            'anthropic_key' => $in['anthropic_key'] ?? '',
            'gmail_user'    => $in['gmail_user'] ?? '',
            'gmail_pass'    => $in['gmail_pass'] ?? '',
            'ads_developer_token'    => $in['ads_developer_token'] ?? '',
            'ads_client_id'          => $in['ads_client_id'] ?? '',
            'ads_client_secret'      => $in['ads_client_secret'] ?? '',
            'ads_refresh_token'      => $in['ads_refresh_token'] ?? '',
            'ads_customer_id'        => $in['ads_customer_id'] ?? '',
            'ads_login_customer_id'  => $in['ads_login_customer_id'] ?? '',
            'site_url'        => $in['site_url'] ?? '',
            'wa_token'        => $in['wa_token'] ?? '',
            'wa_verify_token' => $in['wa_verify_token'] ?? '',
            'wa_phone_id'     => $in['wa_phone_id'] ?? '',
            'lexware_key'     => $in['lexware_key'] ?? '',
            'gh_read_token'   => $in['gh_read_token'] ?? '',
        ]);
        echo json_encode(['ok' => true]);
    } elseif ($a === 'scan_now') {
        oh_inbox_scan();
        $ct = oh_company_tasks();
        echo json_encode(['ok' => true, 'offen' => $ct['offen'], 'erledigt' => $ct['erledigt'], 'warnung' => $ct['warnung'], 'anzahl' => $ct['anzahl']]);
    } elseif ($a === 'ads_report') {
        $err = null;
        $rep = oh_ads_report($err);
        echo json_encode($rep !== null ? ['ok' => true, 'report' => $rep] : ['ok' => false, 'error' => $err]);
    } elseif ($a === 'ads_reco') {
        // gespeicherte Empfehlungen (schnell, ohne neue KI-Analyse)
        echo json_encode(['ok' => true, 'reco' => oh_read('ads_reco', []), 'changes' => array_slice(oh_read('ads_changes', []), 0, 10)]);
    } elseif ($a === 'ads_reco_fresh') {
        // neue KI-Analyse anstoßen
        $err = null;
        $reco = oh_ads_recommendations($err);
        echo json_encode($reco !== null ? ['ok' => true, 'reco' => $reco] : ['ok' => false, 'error' => $err]);
    } elseif ($a === 'ads_apply' || $a === 'ads_later' || $a === 'ads_dismiss') {
        $id = $in['id'] ?? '';
        $reco = oh_read('ads_reco', []);
        $hit = null;
        foreach ($reco as $r) { if (($r['id'] ?? '') === $id) { $hit = $r; break; } }
        if (!$hit) { echo json_encode(['ok' => false]); exit; }

        $result = ['ok' => true, 'executed' => false, 'msg' => ''];
        if ($a === 'ads_apply') {
            $aerr = null;
            $r = oh_ads_apply($hit, $aerr);        // sichere Änderung direkt ausführen
            $result['executed'] = $r['executed'];
            $result['msg'] = $r['msg'];
            $newStatus = 'uebernommen';
            oh_ads_log_change([
                'titel' => $hit['titel'] ?? '', 'was' => $hit['was'] ?? '',
                'typ' => $hit['typ'] ?? '', 'wert' => $hit['wert'] ?? '',
                'ausgefuehrt' => $r['executed'],
            ]);
        } elseif ($a === 'ads_dismiss') {
            $newStatus = 'abgelehnt';
        } else {
            $newStatus = 'spaeter';
        }
        foreach ($reco as &$rr) { if (($rr['id'] ?? '') === $id) $rr['status'] = $newStatus; }
        unset($rr);
        oh_write('ads_reco', $reco);
        echo json_encode($result);
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
        // Guthaben-/Fehlerstatus für die Dashboard-Warnung merken
        $rd = json_decode($response, true);
        if (isset($rd['error'])) {
            $em = strtolower($rd['error']['message'] ?? '');
            if (strpos($em, 'credit') !== false || strpos($em, 'balance') !== false
                || strpos($em, 'insufficient') !== false || strpos($em, 'quota') !== false
                || strpos($em, 'billing') !== false) {
                oh_write('ki_status', ['alert' => true, 'msg' => 'KI-Guthaben aufgebraucht – bitte bei console.anthropic.com aufladen', 'ts' => time()]);
            }
        } elseif (isset($rd['content'])) {
            oh_write('ki_status', ['alert' => false]);
        }
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
.scan-btn{cursor:pointer;color:var(--cyan);border:1px solid var(--line);border-radius:9px;padding:6px 12px;font-size:12px;font-family:'SF Mono',monospace;}
.scan-btn:active{transform:scale(.95);}
.dash-bar{display:flex;justify-content:flex-end;margin:10px 14px 0;}
/* Tagesfokus */
.fokus{margin:10px 14px 0;background:linear-gradient(150deg,rgba(20,40,70,.85),rgba(10,20,38,.9));border:1px solid var(--cyan);
  border-radius:18px;padding:15px 16px;box-shadow:0 0 22px rgba(57,214,255,.16);}
.fokus-h{font-size:14px;font-weight:800;color:#fff;margin-bottom:10px;}
.fokus-i{display:flex;align-items:center;gap:12px;padding:9px 0;border-top:1px solid var(--line);cursor:pointer;}
.fokus-i:first-of-type{border-top:none;}
.fokus-n{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#fff;flex-shrink:0;background:var(--cyan-d);}
.fokus-n.rot{background:var(--red);}.fokus-n.gelb{background:var(--gold);}.fokus-n.gruen{background:var(--green);}
.fokus-i .tt{font-size:13.5px;font-weight:600;color:#fff;}
.fokus-i .ta{font-size:11px;color:var(--txt-dim);margin-top:1px;}
/* Akkordeon */
.acc{margin:10px 14px 0;background:var(--glass);border:1px solid var(--line);border-radius:14px;overflow:hidden;backdrop-filter:blur(10px);}
.acc-h{display:flex;align-items:center;gap:10px;padding:14px 15px;cursor:pointer;}
.acc-c{color:var(--cyan);font-size:11px;transition:transform .2s;display:inline-block;}
.acc-t{flex:1;font-size:13.5px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.acc-cnt{font-size:11px;color:var(--bg);background:var(--cyan);border-radius:7px;padding:1px 7px;font-weight:700;}
.acc-b{padding:0 12px 12px;}
.pill{font-size:9.5px;font-weight:700;letter-spacing:.3px;padding:2px 8px;border-radius:7px;text-transform:uppercase;}
.pill.sm{font-size:8.5px;padding:1px 6px;}
.pill.rot{background:rgba(255,93,108,.2);color:#ff8b96;}
.pill.gelb{background:rgba(231,177,75,.2);color:#f0cd8a;}
.pill.gruen{background:rgba(52,224,154,.2);color:#7ef0bd;}
.task-btns{display:flex;gap:7px;margin-top:9px;flex-wrap:wrap;}
.tb{background:var(--glass);border:1px solid var(--line);color:var(--txt);border-radius:9px;padding:8px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
.tb.ok{background:var(--cyan-soft);border-color:var(--cyan);color:var(--cyan);}
.tb.no{color:var(--red);border-color:rgba(255,93,108,.4);}
/* Agenten-Runde */
.ar-prio{font-size:13px;color:var(--txt);margin-bottom:10px;} .ar-prio b{color:var(--cyan);} .ar-prio ol{margin:6px 0 0 18px;line-height:1.7;}
.ar-feed{margin:10px 0;} .ar-feed b{color:var(--cyan);font-size:12.5px;}
.ar-msg{font-size:12.5px;color:var(--txt);background:rgba(57,214,255,.06);border-left:2px solid var(--cyan);border-radius:8px;padding:8px 10px;margin-top:7px;line-height:1.5;}
.ar-from{color:var(--cyan);font-weight:700;display:block;font-size:11px;margin-bottom:2px;}
.ar-funde{margin-top:10px;} .ar-ag{margin-top:8px;font-size:12.5px;} .ar-ag b{color:#fff;} .ar-ag ul{margin:3px 0 0 16px;color:var(--txt-dim);line-height:1.5;}
.agent-funde{background:var(--glass-2);border:1px solid var(--line);border-radius:13px;padding:13px 15px;margin:8px 14px;}
.agent-funde b{color:var(--cyan);font-size:13px;} .agent-funde ul{margin:6px 0 0 16px;color:var(--txt);line-height:1.6;font-size:13px;}
/* Aktivitäts-Protokoll */
.akt-feed{display:flex;flex-direction:column;gap:2px;}
.akt-row{display:flex;gap:11px;align-items:flex-start;padding:9px 2px;border-bottom:1px solid var(--line);}
.akt-row:last-child{border-bottom:none;}
.akt-ico{font-size:17px;flex-shrink:0;margin-top:1px;}
.akt-t{font-size:13px;color:var(--txt);line-height:1.45;} .akt-t b{color:var(--cyan);}
.akt-z{font-size:10.5px;color:var(--txt-dim);margin-top:2px;}
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
/* KI-Guthaben-Warnung */
.ki-alert{margin:10px 14px 0;padding:13px 15px;border-radius:13px;font-size:13px;line-height:1.5;
  background:linear-gradient(135deg,rgba(255,93,108,.95),rgba(200,40,55,.95));color:#fff;font-weight:600;
  box-shadow:0 0 22px rgba(255,93,108,.4);border:1px solid rgba(255,255,255,.2);}
.ki-alert b{color:#fff;}
.ki-alert.warn{background:linear-gradient(135deg,rgba(231,177,75,.95),rgba(190,130,30,.95));box-shadow:0 0 20px rgba(231,177,75,.35);}
.ki-alert.warn.gelb{}
/* Mert Aldemir Tagesplan */
.mert-card{margin:8px 14px 0;background:linear-gradient(150deg,rgba(20,40,70,.85),rgba(10,20,38,.9));border:1px solid var(--cyan);
  border-radius:18px;padding:16px;backdrop-filter:blur(14px);box-shadow:0 0 26px rgba(57,214,255,.18);}
.mert-head{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
.mert-av{width:42px;height:42px;border-radius:13px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-size:21px;box-shadow:0 0 16px rgba(57,214,255,.4);}
.mert-nm{font-weight:800;font-size:15px;color:#fff;}
.mert-rl{font-size:10.5px;color:var(--cyan);letter-spacing:.3px;}
.mert-txt{font-size:14px;line-height:1.6;color:var(--txt);white-space:pre-wrap;}
.mert-txt b{color:var(--cyan);}
.mert-refresh{margin-top:12px;width:100%;padding:11px;background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:11px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}
/* Aufgaben-Detail */
.task{align-items:flex-start;}
.task-ico{font-size:20px;flex-shrink:0;margin-top:1px;}
.task-why{font-size:12.5px;color:var(--txt-dim);margin-top:8px;line-height:1.5;}
.task-go{display:block;margin-top:8px;background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:9px;padding:8px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
.task.done{opacity:.6;}
.task.done .tt{text-decoration:line-through;}
/* Google Ads */
.ads-sum{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px;}
.ads-stat{background:var(--glass-2);border:1px solid var(--line);border-radius:12px;padding:12px;}
.ads-stat .n{font-size:19px;font-weight:800;color:#fff;}
.ads-stat .l{font-size:10px;color:var(--txt-dim);letter-spacing:.5px;text-transform:uppercase;margin-top:2px;}
.ads-tbl{width:100%;border-collapse:collapse;font-size:13px;}
.ads-tbl th{text-align:left;color:var(--cyan);font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:6px 4px;border-bottom:1px solid var(--line);}
.ads-tbl td{padding:8px 4px;border-bottom:1px solid var(--line);color:var(--txt);}
.ads-tbl td:nth-child(n+2){text-align:right;white-space:nowrap;}
.spinner-mini{font-size:12px;color:var(--txt-dim);}
/* KI-Empfehlungen */
.reco{background:var(--glass-2);border:1px solid var(--line);border-radius:14px;padding:14px;margin-bottom:11px;}
.reco.rot{border-left:3px solid var(--red);box-shadow:0 0 16px rgba(255,93,108,.12);}
.reco.gelb{border-left:3px solid var(--gold);}
.reco.gruen{border-left:3px solid var(--green);}
.reco-prio{font-family:'SF Mono',monospace;font-size:10px;letter-spacing:1px;color:var(--txt-dim);margin-bottom:6px;}
.reco-tit{font-size:15px;font-weight:700;color:#fff;margin-bottom:8px;line-height:1.35;}
.reco-line{font-size:13px;color:var(--txt);margin-bottom:5px;line-height:1.5;}
.reco-line b{color:var(--cyan);}
.reco-meta{font-size:12px;color:var(--green);margin:8px 0;font-weight:600;}
.reco-steps{font-size:12px;color:var(--txt-dim);background:rgba(57,214,255,.06);border-radius:9px;padding:8px 10px;margin-bottom:10px;line-height:1.5;}
.reco-btns{display:flex;gap:8px;}
.reco-btns .btn{padding:11px;font-size:13px;width:auto;}
.reco-ok{flex:2;}
.reco-later{flex:1;}
.reco-result{margin-top:10px;padding:10px 12px;border-radius:10px;font-size:12.5px;line-height:1.5;
  background:rgba(231,177,75,.12);border:1px solid rgba(231,177,75,.4);color:#f0cd8a;}
.reco-result.done{background:rgba(52,224,154,.12);border-color:rgba(52,224,154,.4);color:#7ef0bd;}
/* Morgen-Briefing */
.briefing{margin:8px 14px 0;background:var(--glass-2);border:1px solid var(--line);border-radius:14px;padding:14px 16px;backdrop-filter:blur(12px);}
.briefing h3{font-size:13px;color:var(--cyan);margin-bottom:8px;font-family:'SF Mono',monospace;letter-spacing:1px;}
.briefing .bl{font-size:13px;color:var(--txt);padding:5px 0;display:flex;gap:9px;line-height:1.4;}
.briefing .bl b{color:#fff;}

/* --- AGENTEN-TEAM --- */
.team{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 14px;}
.agent-card{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:16px 13px;text-align:center;
  cursor:pointer;backdrop-filter:blur(12px);transition:transform .15s,border-color .2s,box-shadow .2s;}
.agent-card:active{transform:scale(.97);}
.agent-card.chef{grid-column:1 / -1;border-color:var(--cyan);box-shadow:0 0 22px rgba(57,214,255,.2);
  display:flex;align-items:center;gap:14px;text-align:left;}
.agent-card.chef .agent-nm{font-size:17px;}
.agent-av{width:54px;height:54px;border-radius:50%;margin:0 auto 9px;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(140deg,var(--cyan-d),#0e2c48);box-shadow:0 0 16px rgba(57,214,255,.3);overflow:hidden;flex-shrink:0;}
.agent-card.chef .agent-av{margin:0;}
.agent-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.agent-av.big{width:74px;height:74px;}
.agent-emoji{font-size:25px;align-items:center;justify-content:center;width:100%;height:100%;}
.agent-av.big .agent-emoji{font-size:34px;}
.agent-nm{font-weight:700;font-size:14px;color:#fff;}
.agent-nm.big{font-size:22px;}
.agent-rl{font-size:10.5px;color:var(--txt-dim);margin-top:3px;line-height:1.35;}
.agent-rl.big{font-size:12px;margin-bottom:12px;}
.agent-hero{display:flex;align-items:center;gap:16px;background:var(--glass-2);border:1px solid var(--cyan);
  border-radius:20px;padding:18px;margin:8px 14px 0;backdrop-filter:blur(14px);box-shadow:0 0 24px rgba(57,214,255,.18);}
.agent-talk{margin-top:4px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));color:var(--bg);border:none;
  border-radius:11px;padding:9px 14px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;}
.agent-area{display:flex;align-items:center;justify-content:space-between;background:var(--glass);border:1px solid var(--line);
  border-radius:13px;padding:15px 16px;margin:8px 14px;cursor:pointer;font-size:14px;font-weight:600;color:#fff;backdrop-filter:blur(10px);}
.agent-area:active{transform:scale(.98);}
.agent-area .go{color:var(--cyan);font-size:18px;}

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
  <div class="brand" onclick="goHome()" style="cursor:pointer"><div><div class="mark">OH</div><div class="sub">SYSTEM ONLINE</div></div></div>
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
  <div id="kiAlert" class="ki-alert" style="display:none"></div>
  <div id="martWarn" class="ki-alert warn" style="display:none"></div>

  <div class="dash-bar"><span class="scan-btn" onclick="scanNow(this)">↻ Aktualisieren</span></div>

  <!-- Tagesfokus -->
  <div class="fokus" id="fokus" style="display:none">
    <div class="fokus-h">🎯 Deine wichtigsten Aufgaben heute</div>
    <div id="fokusList"></div>
  </div>

  <!-- Mert + Agenten-Bereiche (einklappbar) -->
  <div id="dashAcc"></div>

  <div class="section-title">// Dein digitales Team</div>
  <div class="team" id="teamGrid"></div>
</div>

<!-- AGENT-DETAIL -->
<div id="s-agent" style="display:none">
  <div class="agent-hero" id="agentHero"></div>
  <div class="section-title">// Zuständigkeiten</div>
  <div id="agentAreas"></div>
  <button class="zurueck" onclick="goHome()">&larr; Zurück zum Team</button>
</div>

<!-- GOOGLE ADS -->
<div id="s-ads" style="display:none">
  <div class="card">
    <h2>&#129302; Dein KI-Geschäftsführer</h2>
    <p class="intro">Findet Chancen, mehr hochwertige Anfragen zu gewinnen (Altbau, Sanierung, Smart-Home) und Werbekosten zu senken.</p>
    <div id="recoBody"><div class="prio-empty">Lade Empfehlungen …</div></div>
    <button class="btn btn-cyan" style="margin-top:12px" id="recoFreshBtn" onclick="recoFresh()">🔍 Markt jetzt neu prüfen</button>
  </div>
  <div class="card">
    <h2>&#128200; Deine Ads-Zahlen (7 Tage)</h2>
    <div id="adsBody"><div class="spinner-mini">Lade …</div></div>
    <button class="btn btn-ghost" style="margin-top:12px" onclick="loadAds()">↻ Aktualisieren</button>
  </div>
  <button class="zurueck" onclick="goHome()">&larr; Kommandozentrale</button>
</div>

<!-- AYLIN · LEXWARE -->
<div id="s-lex" style="display:none">
  <div class="card">
    <h2>&#128176; Aylin · Offene Rechnungen (Lexware)</h2>
    <p class="intro">Aylin liest Deine offenen Rechnungen direkt aus Lexware Office.</p>
    <div id="lexBody"><div class="prio-empty">Lade …</div></div>
    <button class="btn btn-ghost" style="margin-top:12px" onclick="loadLex()">↻ Aktualisieren</button>
  </div>
  <button class="zurueck" onclick="goHome()">&larr; Kommandozentrale</button>
</div>

<!-- DILARA · WEBSITE -->
<div id="s-web" style="display:none">
  <div class="card">
    <h2>&#127760; Dilara · Website-Optimierung</h2>
    <p class="intro">Dilara liest Deine echte Website und schlägt konkrete Verbesserungen für mehr Anfragen vor.</p>
    <div id="webBody"><div class="prio-empty">Lade …</div></div>
    <button class="btn btn-cyan" style="margin-top:12px" id="webBtn" onclick="webAnalyze()">🔍 Website jetzt analysieren</button>
  </div>
  <button class="zurueck" onclick="goHome()">&larr; Kommandozentrale</button>
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
    <h2>&#128202; Google Ads</h2>
    <p class="intro">Für die automatische Kampagnen-Überwachung. Die 5 Zugangsdaten aus der Einrichtung hier eintragen (leere Felder bleiben unverändert).</p>
    <label>Developer Token</label>
    <input type="password" id="adsDev" placeholder="••• (leer = unverändert)">
    <label>Client-ID</label>
    <input type="password" id="adsCid" placeholder="••• .apps.googleusercontent.com">
    <label>Client-Secret</label>
    <input type="password" id="adsSecret" placeholder="••• (leer = unverändert)">
    <label>Refresh-Token</label>
    <input type="password" id="adsRefresh" placeholder="1// ••• (leer = unverändert)">
    <label>Kundennummer (Werbekonto)</label>
    <input type="text" id="adsCustomer" placeholder="123-456-7890">
    <label>Verwalterkonto-Nummer (MCC)</label>
    <input type="text" id="adsLogin" placeholder="246-895-3721">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveAds()">Speichern</button>
    <div id="adsMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#128241; WhatsApp Business</h2>
    <p class="intro">Für eingehende WhatsApp-Nachrichten im Dashboard. Braucht die <b>Meta Cloud API</b> (Business-Konto + Nummer). Webhook-URL in Meta: <b>oh-haustechnik.de/whatsapp-webhook.php</b></p>
    <label>Zugriffs-Token (Permanent Token)</label>
    <input type="password" id="waToken" placeholder="••• (leer = unverändert)">
    <label>Telefonnummer-ID</label>
    <input type="text" id="waPhone" placeholder="z.B. 1098765432">
    <label>Verify-Token (selbst ausgedacht, in Meta gleich eintragen)</label>
    <input type="text" id="waVerify" placeholder="oh-wa">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveWa()">Speichern</button>
    <div id="waMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#127760; Website-Adresse</h2>
    <p class="intro">Für den automatischen Website-Check (Erreichbarkeit, Kontaktformular).</p>
    <label>Adresse</label>
    <input type="text" id="siteUrl" placeholder="https://oh-haustechnik.de">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveSite()">Speichern</button>
    <div id="siteMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#128176; Lexware Office (Aylin)</h2>
    <p class="intro">Für Aylins Buchhaltung. Schlüssel von <b>app.lexoffice.de</b> → Einstellungen → Öffentliche API.</p>
    <label>API-Schlüssel</label>
    <input type="password" id="lexKey" placeholder="••• (leer = unverändert)">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveLex()">Speichern</button>
    <div id="lexMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#128260; System-Update (vom Handy)</h2>
    <p class="intro">Holt die neuesten Büro-Dateien direkt von GitHub auf den Server – ohne FTP/Laptop. Bei privatem Repo einmalig einen <b>GitHub-Lese-Token</b> eintragen.</p>
    <label>GitHub Lese-Token (optional, nur für privates Repo)</label>
    <input type="password" id="ghToken" placeholder="github_pat_... (leer = unverändert)">
    <button class="btn btn-ghost" style="margin-top:12px" onclick="saveGh()">Token speichern</button>
    <div id="ghMsg" class="msg-ok"></div>
    <button class="btn btn-cyan" style="margin-top:14px" id="updBtn" onclick="doUpdate()">🔄 Jetzt aktualisieren (von GitHub)</button>
    <div id="updBody" style="font-size:12px;color:var(--txt-dim);margin-top:10px;white-space:pre-wrap"></div>
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
Stil: bodenständig, ehrlich, handwerklich, regional. Kunde steht im Mittelpunkt, kein Marketing-Blabla.
GEMEINSAMES ZIEL DES GANZEN TEAMS: OH Haustechnik in 5 Monaten auf 1.000.000 € Umsatz bringen. Jede Entscheidung an diesem Ziel ausrichten (hochwertige Aufträge: Altbausanierung, Wohnungssanierung, Zähler, Smart-Home).`;

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
Du denkst mit, gibst ehrliche, umsetzbare Tipps zu Aufträgen, Preisen, Zeit, Marketing, Wachstum. Kurz, konkret, motivierend. Du-Form, auf Augenhöhe.`;}},
  mert:{ name:'Mert Aldemir', ico:'\u{1F9E0}',
    quick:['Was ist heute am wichtigsten?','Wann brauche ich einen Mitarbeiter?','Wo verliere ich Geld?','Wie komme ich schneller zur Million?'],
    system(){return `Du bist Mert Aldemir, der digitale Geschäftsführer von OH Haustechnik. DEIN EINZIGES ZIEL: das Unternehmen in 5 Monaten Richtung 1.000.000 € Umsatz skalieren.
${FIRMA}
Du überwachst alle Bereiche und koordinierst Dein Team: Dilara (Marketing), Kaan (Kommunikation), Emre (Kalkulation/Angebote), Aylin (Buchhaltung), Yusuf (Projekte), Baran (Personal). Du erkennst Engpässe und Wachstumschancen, verteilst Aufgaben, setzt Prioritäten. Du denkst wie ein knallharter, kluger Geschäftsführer und bewertest jede Maßnahme danach, ob sie schneller Wachstum, mehr qualifizierte Anfragen oder mehr Umsatz bringt. Sprich einfach mit dem Chef (Du-Form), kein Fachchinesisch, ehrlich und motivierend.`;}},
  dilara:{ name:'Dilara', ico:'\u{1F680}',
    quick:['Was bringt heute mehr Anfragen?','Instagram-Post für ein Projekt','Website-Optimierung','Was macht die Konkurrenz?'],
    system(){return `Du bist Dilara, die Marketing-Agentin von OH Haustechnik. Verantwortung: Google Ads, Website, Bewertungen, Social Media, Conversion-Optimierung, Konkurrenzanalyse, SEO, Suchtrends.
${FIRMA}
Dein Ziel: jeden Tag mehr hochwertige Anfragen und mehr Umsatz (Fokus Altbausanierung, Wohnungssanierung, Smart-Home). Du gibst konkrete, umsetzbare Vorschläge und fertige Inhalte (Posts, Anzeigen, Antworten). Du-Form mit dem Chef, einfach und klar.`;}},
  kaan:{ name:'Kaan', ico:'\u{1F4AC}',
    quick:['Antwort auf diese E-Mail','WhatsApp-Antwort formulieren','Wer wartet auf Rückmeldung?','Rückruf-Liste für heute'],
    system(){return `Du bist Kaan, die Kommunikations-Agentin von OH Haustechnik. Verantwortung: Gmail, WhatsApp Business, Kontaktformulare, Kundenanfragen, Rückruflisten, offene Nachrichten.
${FIRMA}
Du kategorisierst und beantwortest Kundenkommunikation professionell und freundlich. Du erinnerst Dich an alle bisherigen Gespräche (siehe Gedächtnis). Du erstellst fertige Antwortvorschläge zum Rauskopieren/Senden. Du-Form mit dem Chef.`;}},
  emre:{ name:'Emre', ico:'\u{1F9EE}',
    quick:['Angebot aus dieser Anfrage','100 m² Altbau Unterputz kalkulieren','Nachkalkulation','Deckungsbeitrag prüfen'],
    system(){return `Du bist Emre, die Kalkulations- & Angebots-Agentin von OH Haustechnik. Du analysierst Anfragen, kalkulierst Material & Arbeitsstunden und erstellst fertige Angebote.
${FIRMA}
${KALK_WISSEN}
Wenn genug Infos da sind, erstelle einen vollständigen Angebotsvorschlag (Text + Preis), den der Chef nur prüfen und versenden muss. Bei Kalkulation nutze die Faustformeln. Du-Form mit dem Chef.`;}},
  aylin:{ name:'Aylin', ico:'\u{1F4B0}',
    quick:['Welche Rechnungen sind offen?','Mahnung vorbereiten','Gewinn-Übersicht','Was an Lexware übergeben?'],
    system(){return `Du bist Aylin, die Buchhaltungs- & Finanz-Agentin von OH Haustechnik (Kleinunternehmer, 0% USt §19). Verantwortung: Rechnungen, Zahlungseingänge, offene Posten, Mahnungen, Gewinn/Kosten-Auswertung, Übergabe an Lexware Office.
${FIRMA}
Du bereitest alles vor, sodass der Chef nur „Übernehmen" drücken muss. Du rechnest sauber und erklärst einfach. Hinweis: Die direkte Lexware-Anbindung wird noch eingerichtet – bis dahin bereitest Du die Daten klar auf. Du-Form mit dem Chef.`;}},
  yusuf:{ name:'Yusuf', ico:'\u{1F3D7}',
    quick:['Tagesplan Baustellen','Materialliste für ein Projekt','Termin koordinieren','Projektfortschritt'],
    system(){return `Du bist Yusuf, die Projekt- & Baustellen-Agentin von OH Haustechnik. Verantwortung: Baustellenplanung, Materialstatus, Termine, Monteure, Projektfortschritt.
${FIRMA}
Du hilfst, Baustellen und Termine zu organisieren, Material und Abläufe zu planen. Du-Form mit dem Chef, praktisch und konkret.`;}},
  baran:{ name:'Baran', ico:'\u{1F465}',
    quick:['Brauche ich bald einen Mitarbeiter?','Stellenanzeige Geselle','Bewerbung bewerten','Kapazität planen'],
    system(){return `Du bist Baran, die Personal-Agentin von OH Haustechnik. Verantwortung: Mitarbeitersuche, Bewerbungen, Kapazitätsplanung, Personalbedarf.
${FIRMA}
Du erkennst, wann ein Mitarbeiter/Geselle nötig ist (wenn mehr Anfragen als Kapazität), schreibst Stellenanzeigen und hilfst bei Bewerbungen. Du-Form mit dem Chef, ehrlich und vorausschauend.`;}}
};

/* ============ STATE ============ */
let mode='kalk';
let history={}; // pro modus: [{role,content}]
let leadsCache=[];
let lastTasks=null;
let WISSEN='';
let AGENT_CTX='';
let serverCfg={has_anthropic:false,has_gmail_pass:false,gmail_user:''};

/* ============ SERVER-API ============ */
async function api(action,data){
  const fd=new FormData();fd.append('action',action);fd.append('data',JSON.stringify(data||{}));
  const r=await fetch(window.location.pathname,{method:'POST',body:fd});
  return r.json();
}

/* ============ DASHBOARD ============ */
const PDOT={rot:'rot',gelb:'gelb',gruen:'gruen'};
let briefingText='';
async function loadDashboard(){
  try{
    const d=await api('dashboard');
    leadsCache=d.leads||[];
    if(d.wissen)WISSEN=d.wissen;
    renderDashboard(d);
  }catch(e){/* offline */}
  renderTeam();
  try{serverCfg=await api('config_get');}catch(e){}
  // E-Mails & Website im Hintergrund aktualisieren (blockiert das Öffnen nicht)
  api('scan_now').then(d=>{if(d&&d.ok&&lastDash){lastDash.offen=d.offen;lastDash.erledigt=d.erledigt;lastDash.warnung=d.warnung;lastDash.anzahl=d.anzahl;renderDashboard(lastDash);}}).catch(()=>{});
}
async function scanNow(btn){
  if(btn){btn.textContent='… aktualisiere';}
  try{const d=await api('scan_now');if(d&&d.ok&&lastDash){lastDash.offen=d.offen;lastDash.erledigt=d.erledigt;lastDash.warnung=d.warnung;lastDash.anzahl=d.anzahl;renderDashboard(lastDash);}}catch(e){}
  if(btn){btn.textContent='↻ Aktualisieren';}
}
function renderStats(s){
  gl('dashStats').innerHTML=
    `<div class="stat"><div class="n">${s.leads||0}</div><div class="l">Leads gesamt</div></div>`+
    `<div class="stat hot"><div class="n">${s.hot||0}</div><div class="l">🔥 Heiß &amp; offen</div></div>`;
}
function renderKiAlert(a){
  const el=gl('kiAlert');
  if(a&&a.alert){
    el.innerHTML='⚠️ <b>KI-Guthaben leer!</b> '+esc(a.msg||'Bitte aufladen')+' — <a href="https://console.anthropic.com" target="_blank" style="color:#fff;text-decoration:underline">jetzt aufladen</a>';
    el.style.display='block';
    speak('Achtung Chef, das KI-Guthaben ist leer. Bitte aufladen.');
  }else{el.style.display='none';}
}
function renderWarn(w){
  const el=gl('martWarn');
  if(w){el.innerHTML='⚠️ <b>'+(w.prio==='rot'?'Marktanalyse veraltet!':'Marktanalyse aktualisieren')+'</b> '+esc(w.text);el.style.display='block';el.className='ki-alert warn'+(w.prio==='rot'?'':' gelb');}
  else{el.style.display='none';}
}
const PLABEL={rot:'Kritisch',gelb:'Hoch',gruen:'Mittel'};
const AGENT_OF={'Google Ads':'dilara','Markt':'dilara','Website':'dilara','Bewertung':'dilara','SEO':'dilara','Anfrage':'kaan','E-Mail':'kaan','WhatsApp':'kaan','Angebot':'emre','Rechnung':'aylin','Zahlung':'aylin','Projekt':'yusuf','Baustelle':'yusuf','Personal':'baran'};
const GRP={dilara:'Marketing (Dilara)',kaan:'Kommunikation (Kaan)',emre:'Angebote & Kalkulation (Emre)',aylin:'Buchhaltung (Aylin)',yusuf:'Projekte & Baustellen (Yusuf)',baran:'Mitarbeiter (Baran)',mert:'Sonstiges'};
let lastDash=null;
function renderDashboard(d){
  lastDash=d; lastOffen=(d.offen||[]).slice();
  renderStats(d.stats||{}); renderKiAlert(d.ki_alert||{alert:false}); renderWarn(d.warnung);
  const offen=lastOffen; offen.forEach((t,i)=>t._i=i);
  // Tagesfokus = Top 3 (serverseitig nach Priorität sortiert)
  renderFokus(offen.slice(0,3));
  // nach Agent gruppieren
  const groups={};
  offen.forEach(t=>{const ag=AGENT_OF[t.bereich]||'mert';(groups[ag]=groups[ag]||[]).push(t);});
  agentenData=d.agenten||null; aktivData=d.aktivitaet||[];
  let html=accordion('📊 Geschäftsführer-Bericht von Mert Aldemir', mertBody(d.mert), false);
  html+=accordion('🤝 Agenten-Runde · Team-Abstimmung', agentenBody(d.agenten), false);
  html+=accordion(`📋 Was wurde erledigt <span class="acc-cnt">${aktivData.length}</span>`, aktivBody(aktivData), false);
  ['dilara','kaan','emre','aylin','yusuf','baran','mert'].forEach(ag=>{
    const list=groups[ag]; if(!list||!list.length)return;
    const badge=`<span class="pill ${list[0].prio}">${PLABEL[list[0].prio]||''}</span>`;
    const emo=AGENTS[ag]?AGENTS[ag].emoji:'•';
    html+=accordion(`${emo} ${GRP[ag]} <span class="acc-cnt">${list.length}</span> ${badge}`, list.map(taskHtml).join(''), false);
  });
  const er=d.erledigt||[];
  html+=accordion(`✅ Bereits erledigt <span class="acc-cnt">${er.length}</span>`, er.length?er.map(erledigtHtml).join(''):'<div class="prio-empty">Noch nichts.</div>', false);
  gl('dashAcc').innerHTML=html;
  buildBriefing(d);
}
function accordion(titleHtml,bodyHtml,open){
  return `<div class="acc"><div class="acc-h" onclick="accT(this)"><span class="acc-c">▶</span><div class="acc-t">${titleHtml}</div></div><div class="acc-b" style="display:${open?'block':'none'}">${bodyHtml}</div></div>`;
}
function accT(el){const b=el.nextElementSibling,c=el.querySelector('.acc-c');const open=b.style.display==='none';b.style.display=open?'block':'none';c.style.transform=open?'rotate(90deg)':'';}
function taskHtml(t){const i=t._i,isReco=t.typ==='reco';
  return `<div class="task ${t.prio}" onclick="toggleTask(${i})">
    <div class="task-ico">${t.icon||'•'}</div>
    <div class="tx">
      <div class="tt">${esc(t.titel)} <span class="pill sm ${t.prio}">${PLABEL[t.prio]||''}</span></div>
      <div class="ta">📈 ${esc(t.nutzen||'')}</div>
      <div class="task-why" id="why${i}" style="display:none">💡 ${esc(t.warum||'')}
        <div class="task-btns">${isReco
          ?`<button class="tb ok" onclick="event.stopPropagation();recoApply('${t.ref}',this)">✅ Übernehmen</button>
            <button class="tb" onclick="event.stopPropagation();recoLater('${t.ref}',this)">Später</button>
            <button class="tb no" onclick="event.stopPropagation();recoDismiss('${t.ref}',this)">Ablehnen</button>`
          :`<button class="tb ok" onclick="event.stopPropagation();actTask(${i})">Erledigen →</button>`}
        </div>
      </div>
    </div>
    <div class="go">›</div>
  </div>`;
}
function erledigtHtml(x){return `<div class="task done"><div class="task-ico">${x.icon||'✅'}</div><div class="tx"><div class="tt">${esc(x.titel)}</div><div class="ta">${esc(x.bereich||'')}</div></div></div>`;}
function toggleTask(i){const w=gl('why'+i);if(w)w.style.display=w.style.display==='none'?'block':'none';}
function actTask(i){const t=lastOffen[i];if(t)openTaskRef(t.typ,t.ref);}
function renderFokus(list){
  if(!list||!list.length){gl('fokus').style.display='none';return;}
  gl('fokus').style.display='block';
  gl('fokusList').innerHTML=list.map((t,n)=>`<div class="fokus-i" onclick="actTask(${t._i})"><span class="fokus-n ${t.prio}">${n+1}</span><div><div class="tt">${esc(t.titel)}</div><div class="ta">${esc(t.bereich)} · ${PLABEL[t.prio]||''}</div></div></div>`).join('');
}
function mertBody(m){
  const txt=(m&&m.text)?fmt(m.text):'<span style="color:var(--txt-dim)">Noch kein Tagesplan. Tipp „Neuen Tagesplan erstellen".</span>';
  return `<div class="mert-txt" id="mertTxt">${txt}</div><button class="mert-refresh" onclick="event.stopPropagation();mertFresh(this)">↻ Neuen Tagesplan erstellen</button>`;
}
let agentenData=null, aktivData=[];
const AG_NAME={mert:'Mert',dilara:'Dilara',kaan:'Kaan',emre:'Emre',aylin:'Aylin',yusuf:'Yusuf',baran:'Baran',system:'System'};
function zeitHer(ts){const s=Math.floor(Date.now()/1000)-ts;if(s<60)return'gerade eben';if(s<3600)return Math.floor(s/60)+' Min her';if(s<86400)return Math.floor(s/3600)+' Std her';return Math.floor(s/86400)+' Tg her';}
function aktivBody(list){
  if(!list||!list.length)return '<div class="prio-empty">Noch keine Aktivität. Sobald die Agenten arbeiten, erscheint hier, wer was erledigt hat.</div>';
  return '<div class="akt-feed">'+list.map(a=>{
    const em=AGENTS[a.agent]?AGENTS[a.agent].emoji:'•';
    return `<div class="akt-row"><span class="akt-ico">${em}</span><div><div class="akt-t"><b>${AG_NAME[a.agent]||a.agent}</b> ${esc(a.text)}</div><div class="akt-z">${zeitHer(a.ts)}</div></div></div>`;
  }).join('')+'</div>';
}
function agentenBody(a){
  if(!a||(!a.nachrichten&&!a.prioritaeten&&!a.agenten)){
    return `<div class="prio-empty">Noch keine Abstimmung. Das Team trifft sich automatisch (Cron) – oder jetzt starten:</div><button class="mert-refresh" onclick="event.stopPropagation();agentenRunde(this)">🤝 Agenten-Runde starten</button>`;
  }
  let h='';
  if(a.prioritaeten&&a.prioritaeten.length){
    h+='<div class="ar-prio"><b>🎯 Merts Prioritäten:</b><ol>'+a.prioritaeten.map(p=>`<li>${esc(p)}</li>`).join('')+'</ol></div>';
  }
  if(a.nachrichten&&a.nachrichten.length){
    h+='<div class="ar-feed"><b>💬 Team-Nachrichten:</b>'+a.nachrichten.map(n=>
      `<div class="ar-msg"><span class="ar-from">${AG_NAME[n.von]||n.von} → ${AG_NAME[n.an]||n.an}</span> ${esc(n.text)}</div>`).join('')+'</div>';
  }
  if(a.agenten&&a.agenten.length){
    h+='<div class="ar-funde">'+a.agenten.map(ag=>{
      const fu=(ag.funde||[]).map(f=>`<li>${esc(f)}</li>`).join('');
      return fu?`<div class="ar-ag"><b>${AGENTS[ag.key]?AGENTS[ag.key].emoji+' '+AGENTS[ag.key].name:ag.key}</b><ul>${fu}</ul></div>`:'';
    }).join('')+'</div>';
  }
  h+=`<div style="font-size:10.5px;color:var(--txt-dim);margin-top:8px">Stand: ${a.ts?new Date(a.ts*1000).toLocaleString('de-DE'):'–'}</div>`;
  h+=`<button class="mert-refresh" onclick="event.stopPropagation();agentenRunde(this)">↻ Neue Agenten-Runde</button>`;
  return h;
}
async function agentenRunde(btn){
  btn.disabled=true;btn.textContent='🤝 Das Team stimmt sich ab …';
  try{const d=await api('agenten_runde');if(d.ok){if(lastDash){lastDash.agenten=d.agenten;renderDashboard(lastDash);}speak('Das Team hat sich abgestimmt, Chef.');}else{btn.textContent='⚠️ '+(d.error||'Fehler');setTimeout(()=>{btn.disabled=false;btn.textContent='↻ Agenten-Runde';},2500);}}catch(e){btn.disabled=false;}
}
async function mertFresh(btn){
  btn.disabled=true;btn.textContent='🧠 Mert denkt nach …';
  try{const d=await api('mert_fresh');if(d.ok){if(lastDash){lastDash.mert=d.mert;renderDashboard(lastDash);}if(d.mert&&d.mert.text)speak(cleanSpeech(d.mert.text.split('\n')[0]));}}catch(e){}
}
function recoDismiss(id,btn){btn.disabled=true;btn.textContent='✕';api('ads_dismiss',{id}).then(()=>setTimeout(()=>{loadDashboard();if(typeof loadReco==='function'&&gl('s-ads').style.display==='block')loadReco();},500));}
function buildBriefing(d){
  const offen=(d.offen||[]).length, rot=(d.anzahl&&d.anzahl.rot)||0;
  const std=new Date().getHours();
  const gruss=std<11?'Guten Morgen':std<18?'Hallo':'Guten Abend';
  briefingText=`${gruss} Chef. Du hast ${offen} offene Aufgabe${offen===1?'':'n'}${rot?`, ${rot} davon kritisch`:''}. `;
  if(d.mert&&d.mert.text)briefingText+='Mert sagt: '+cleanSpeech(d.mert.text.split('\n').slice(0,2).join(' '));
}
let lastOffen=[];
function leadById(id){return leadsCache.find(l=>l.id===id);}
function leadInfo(l){
  if(!l)return '';
  return `Lead-Infos:\n- Name: ${l.name||'?'}\n- E-Mail: ${l.email||'?'}\n- Telefon: ${l.telefon||'?'}\n- Leistung: ${l.kategorie||'?'}\n- Größe: ${l.objektgroesse||'?'}\n- Zeitraum: ${l.zeitraum||'?'}\n- Ort: ${(l.plz||'')+' '+(l.ort||'')}\n- Details: ${l.details||'-'}`;
}
function openTaskRef(typ,ref){
  if(typ==='reco'||typ==='markt'){openAds();return;}
  const task=(lastOffen||[]).find(t=>t.ref===ref&&t.typ===typ);
  if(typ==='email'){openChat('leads','Hilf mir, diese E-Mail kurz und professionell zu beantworten. Ich füge den Text gleich ein:');return;}
  if(typ==='whatsapp'){openChat('leads','Schreib mir eine freundliche, professionelle WhatsApp-Antwort auf diese Kundennachricht:\n\n"'+((task&&task.warum)||'')+'"');return;}
  if(typ==='website'){openChat('berater','Auf der Website gibt es ein Problem: '+((task&&task.warum)||'')+'\nWas soll ich tun?');return;}
  const l=leadById(ref);
  if(typ==='bewertung')openChat('bewertung','Schreib eine freundliche Bewertungs-Anfrage per E-Mail an diesen abgeschlossenen Kunden:\n\n'+leadInfo(l));
  else if(typ==='followup')openChat('leads','Schreib eine freundliche Follow-up-Nachricht (Angebot ist 2 Tage raus, keine Antwort) an:\n\n'+leadInfo(l));
  else openChat('kalk',(l?l.details||l.kategorie:'')+'\n\n['+leadInfo(l)+']');
}

/* ============ AGENTEN-TEAM ============ */
const AGENTS={
  mert:{name:'Mert Aldemir',rolle:'Geschäftsführer · überwacht & koordiniert alles',emoji:'🧠',chat:'mert',
    areas:[['Tagesplan & Prioritäten',()=>openChat('mert','Was ist heute am wichtigsten für mein Wachstum?')],['Team koordinieren',()=>openChat('mert','Gib jedem Agenten heute eine sinnvolle Aufgabe.')],['Wachstum zur Million',()=>openChat('mert','Wie komme ich schneller Richtung 1 Million Umsatz?')]]},
  dilara:{name:'Dilara',rolle:'Marketing & Wachstum',emoji:'🚀',chat:'dilara',
    areas:[['Google Ads',()=>openAds()],['Website-Optimierung',()=>openWeb()],['Bewertungen',()=>openChat('bewertung')],['Social Media',()=>openChat('dilara','Mach mir Social-Media-Content für diese Woche.')],['SEO & Konkurrenz',()=>openChat('dilara','Was macht die Konkurrenz in Nürnberg und wo kann ich besser werden?')]]},
  kaan:{name:'Kaan',rolle:'Kommunikation · E-Mail, WhatsApp, Anfragen',emoji:'💬',chat:'kaan',
    areas:[['E-Mails',()=>openChat('kaan','Hilf mir, meine offenen E-Mails zu beantworten.')],['WhatsApp',()=>openChat('kaan','Hilf mir bei den WhatsApp-Antworten.')],['Anfragen',()=>openChat('leads')],['Rückrufe',()=>openChat('kaan','Wer wartet auf einen Rückruf?')]]},
  emre:{name:'Emre',rolle:'Kalkulation & Angebote',emoji:'🧮',chat:'emre',
    areas:[['Kalkulator',()=>openChat('emre')],['Angebote',()=>openChat('angebot')],['Nachkalkulation',()=>openChat('emre','Mach mir eine Nachkalkulation für ein Projekt.')]]},
  aylin:{name:'Aylin',rolle:'Buchhaltung & Finanzen',emoji:'💰',chat:'aylin',
    areas:[['Offene Rechnungen (Lexware)',()=>openLex()],['Mahnungen',()=>openChat('aylin','Bereite eine freundliche Mahnung vor.')],['Auswertung',()=>openChat('aylin','Mach mir eine Gewinn- und Kosten-Übersicht.')],['Lexware-Übergabe',()=>openChat('aylin','Was soll an Lexware übergeben werden?')]]},
  yusuf:{name:'Yusuf',rolle:'Projekte & Baustellen',emoji:'🏗️',chat:'yusuf',
    areas:[['Baustellen-Tagesplan',()=>openChat('yusuf','Plane meine Baustellen für heute.')],['Materialliste',()=>openChat('yusuf','Mach mir eine Materialliste für ein Projekt.')],['Termine',()=>openChat('yusuf','Hilf mir, Termine zu koordinieren.')]]},
  baran:{name:'Baran',rolle:'Mitarbeiter & Personal',emoji:'👥',chat:'baran',
    areas:[['Personalbedarf',()=>openChat('baran','Brauche ich bald einen Mitarbeiter? Schau auf meine Auslastung.')],['Stellenanzeige',()=>openChat('baran','Schreib eine Stellenanzeige für einen Elektriker-Gesellen.')],['Bewerbungen',()=>openChat('baran','Hilf mir, eine Bewerbung zu bewerten.')]]},
};
const AGENT_ORDER=['mert','dilara','kaan','emre','aylin','yusuf','baran'];
function agentAvatar(key,big){
  const a=AGENTS[key];const sz=big?'agent-av big':'agent-av';
  return `<span class="${sz}"><img src="assets/agents/${key}.png" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><span class="agent-emoji" style="display:none">${a.emoji}</span></span>`;
}
function renderTeam(){
  gl('teamGrid').innerHTML=AGENT_ORDER.map(key=>{const a=AGENTS[key];
    return `<div class="agent-card${key==='mert'?' chef':''}" onclick="openAgent('${key}')">
      ${agentAvatar(key)}
      <div class="agent-nm">${a.name}</div>
      <div class="agent-rl">${esc(a.rolle)}</div>
    </div>`;}).join('');
}
let curAgent=null;
function openAgent(key){
  curAgent=key;const a=AGENTS[key];
  gl('agentHero').innerHTML=`${agentAvatar(key,true)}
    <div><div class="agent-nm big">${a.name}</div><div class="agent-rl big">${esc(a.rolle)}</div>
    <button class="agent-talk" onclick="openChat('${a.chat}')">💬 Mit ${a.name} sprechen</button></div>`;
  let fundeHtml='';
  if(agentenData&&agentenData.agenten){
    const mine=agentenData.agenten.find(x=>x.key===key);
    if(mine&&mine.funde&&mine.funde.length){
      fundeHtml=`<div class="agent-funde"><b>🔎 ${a.name}s aktuelle Funde:</b><ul>${mine.funde.map(f=>`<li>${esc(f)}</li>`).join('')}</ul></div>`;
    }
  }
  const myAkt=(aktivData||[]).filter(x=>x.agent===key).slice(0,6);
  let aktHtml='';
  if(myAkt.length){
    aktHtml=`<div class="agent-funde"><b>📋 Zuletzt von ${a.name} erledigt:</b><ul>${myAkt.map(x=>`<li>${esc(x.text)} <span style="color:var(--txt-dim)">· ${zeitHer(x.ts)}</span></li>`).join('')}</ul></div>`;
  }
  gl('agentAreas').innerHTML=fundeHtml+aktHtml+a.areas.map((ar,i)=>`<div class="agent-area" onclick="agentArea('${key}',${i})"><span>${esc(ar[0])}</span><span class="go">›</span></div>`).join('');
  showSection('agent');
}
function agentArea(key,i){AGENTS[key].areas[i][1]();}

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
  ['home','settings','chat','ads','agent','web','lex'].forEach(id=>{const el=gl('s-'+id);if(el)el.style.display='none';});
  gl('s-'+s).style.display='block';
  window.scrollTo({top:0,behavior:'smooth'});
}
function goHome(){showSection('home');}
async function toggleSettings(){
  if(gl('s-settings').style.display==='block'){goHome();}
  else{
    gl('apiIn').value='';gl('gmailPass').value='';
    ['adsDev','adsCid','adsSecret','adsRefresh','waToken','lexKey','ghToken'].forEach(id=>gl(id).value='');
    try{const c=await api('config_get');serverCfg=c;
      gl('gmailUser').value=c.gmail_user||'';
      gl('adsCustomer').value=c.ads_customer_id||'';
      gl('adsLogin').value=c.ads_login_customer_id||'';
      gl('waPhone').value=c.wa_phone_id||'';
      gl('waVerify').value=c.wa_verify_token||'oh-wa';
      gl('siteUrl').value=c.site_url||'';
    }catch(e){}
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
async function saveAds(){
  await api('config_set',{
    ads_developer_token:gl('adsDev').value.trim(),
    ads_client_id:gl('adsCid').value.trim(),
    ads_client_secret:gl('adsSecret').value.trim(),
    ads_refresh_token:gl('adsRefresh').value.trim(),
    ads_customer_id:gl('adsCustomer').value.trim(),
    ads_login_customer_id:gl('adsLogin').value.trim()
  });
  ['adsDev','adsCid','adsSecret','adsRefresh'].forEach(id=>gl(id).value='');
  gl('adsMsg').textContent='✓ Google Ads gespeichert';
  setTimeout(()=>gl('adsMsg').textContent='',2500);
}
async function saveWa(){
  await api('config_set',{wa_token:gl('waToken').value.trim(),wa_phone_id:gl('waPhone').value.trim(),wa_verify_token:gl('waVerify').value.trim()});
  gl('waToken').value='';
  gl('waMsg').textContent='✓ WhatsApp gespeichert';
  setTimeout(()=>gl('waMsg').textContent='',2500);
}
async function saveSite(){
  await api('config_set',{site_url:gl('siteUrl').value.trim()});
  gl('siteMsg').textContent='✓ Gespeichert';
  setTimeout(()=>gl('siteMsg').textContent='',2500);
}
async function saveLex(){
  await api('config_set',{lexware_key:gl('lexKey').value.trim()});
  gl('lexKey').value='';
  gl('lexMsg').textContent='✓ Lexware gespeichert';
  setTimeout(()=>gl('lexMsg').textContent='',2500);
}
async function saveGh(){
  await api('config_set',{gh_read_token:gl('ghToken').value.trim()});
  gl('ghToken').value='';
  gl('ghMsg').textContent='✓ GitHub-Token gespeichert';
  setTimeout(()=>gl('ghMsg').textContent='',2500);
}
async function doUpdate(){
  const b=gl('updBtn');b.disabled=true;b.textContent='🔄 Hole Dateien von GitHub …';
  gl('updBody').textContent='';
  try{const d=await api('self_update');gl('updBody').textContent=(d.log||[]).join('\n')+'\n\n✅ Fertig. Seite einmal neu laden.';}
  catch(e){gl('updBody').textContent='⚠️ Fehler beim Update.';}
  b.disabled=false;b.textContent='🔄 Jetzt aktualisieren (von GitHub)';
}
function renderLL(){
  const l=getLern(),el=gl('lernListe');
  el.innerHTML=l.length?l.map((t,i)=>`<div class="lern-item"><span>• ${esc(t)}</span><span class="del" onclick="delL(${i})">löschen</span></div>`).join(''):'<p style="font-size:13px;color:var(--txt-dim)">Noch keine.</p>';
}
function delL(i){const l=getLern();l.splice(i,1);setLernS(l);renderLL();}

/* ============ CHAT ÖFFNEN ============ */
function openChat(m,prefill){
  mode=m; const cfg=MODI[m];
  AGENT_CTX='';
  if(AGENTS[m]){api('agent_context',{agent:m}).then(d=>{AGENT_CTX=d.ctx||'';}).catch(()=>{});}
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
      berater:'Ich bin da, Chef. 🧠 Erzähl, was ansteht – Aufträge, Preise, Zeit, Wachstum. Ich denk mit.',
      mert:'Servus Chef! 🧠 Ich bin Mert, Dein Geschäftsführer. Ich hab die ganze Firma im Blick. Frag mich, was heute am wichtigsten ist.',
      dilara:'Hi Chef! 🚀 Ich bin Dilara, Marketing. Sag mir, was beworben werden soll – ich liefere fertige Vorschläge für mehr Anfragen.',
      kaan:'Hi Chef! 💬 Ich bin Kaan, Kommunikation. Füg mir eine Nachricht/Anfrage rein, ich formulier Dir die perfekte Antwort.',
      emre:'Servus Chef! 🧮 Ich bin Emre, Kalkulation & Angebote. Beschreib die Baustelle, ich mach Dir Preis + fertiges Angebot.',
      aylin:'Hallo Chef! 💰 Ich bin Aylin, Buchhaltung. Ich kümmere mich um Rechnungen, offene Posten und Auswertungen.',
      yusuf:'Servus Chef! 🏗️ Ich bin Yusuf, Projekte & Baustellen. Sag mir, was ansteht – ich plan Dir Termine und Material.',
      baran:'Hi Chef! 👥 Ich bin Baran, Personal. Ich sag Dir, wann Du Verstärkung brauchst und schreib Stellenanzeigen.'
    }[m] || ('Bereit, Chef! Ich bin '+cfg.name+'. Sag mir, was Du brauchst.');
    if(greet)pushMsg('ai',greet);
  }
  showSection('chat');
  if(prefill){gl('chatIn').value=prefill;autoGrow();}
  setTimeout(()=>gl('chatIn').focus(),300);
}
function quick(b){gl('chatIn').value=b.textContent;gl('chatIn').focus();autoGrow();}

/* ============ GOOGLE ADS ============ */
let lastAdsReport=null;
function openAds(){showSection('ads');loadReco();loadAds();}

/* --- KI-Empfehlungen ("Chef, ich hab was gefunden") --- */
const PRIO={rot:{t:'🔴 SOFORT übernehmen',c:'rot'},gelb:{t:'🟡 Diese Woche',c:'gelb'},gruen:{t:'🟢 Optional',c:'gruen'}};
async function loadReco(){
  gl('recoBody').innerHTML='<div class="prio-empty">Lade Empfehlungen …</div>';
  try{const d=await api('ads_reco'); renderReco(d.reco||[]);}catch(e){gl('recoBody').innerHTML='<div class="prio-empty">Noch keine Analyse. Tipp auf „Markt jetzt neu prüfen".</div>';}
}
async function recoFresh(){
  const b=gl('recoFreshBtn'); b.disabled=true; b.textContent='🔍 KI prüft den Markt … (dauert kurz)';
  gl('recoBody').innerHTML='<div class="prio-empty">Der Geschäftsführer schaut sich alles an …</div>';
  try{
    const d=await api('ads_reco_fresh');
    if(!d.ok){gl('recoBody').innerHTML=`<div class="fehler">⚠️ ${esc(d.error||'Fehler')}</div>`;}
    else renderReco(d.reco||[]);
  }catch(e){gl('recoBody').innerHTML='<div class="fehler">⚠️ Verbindung fehlgeschlagen.</div>';}
  b.disabled=false; b.textContent='🔍 Markt jetzt neu prüfen';
}
function ordPrio(p){return p==='rot'?0:p==='gelb'?1:2;}
function renderReco(list){
  const offen=(list||[]).filter(r=>r.status==='offen').sort((a,b)=>ordPrio(a.dringlichkeit)-ordPrio(b.dringlichkeit));
  if(!offen.length){gl('recoBody').innerHTML='<div class="prio-empty">✅ Aktuell keine offenen Empfehlungen, Chef. Tipp „Markt neu prüfen" für eine frische Analyse.</div>';return;}
  gl('recoBody').innerHTML=offen.map(r=>{
    const p=PRIO[r.dringlichkeit]||PRIO.gelb;
    return `<div class="reco ${p.c}">
      <div class="reco-prio">${p.t}</div>
      <div class="reco-tit">${esc(r.titel||'')}</div>
      <div class="reco-line"><b>Was:</b> ${esc(r.was||'')}</div>
      <div class="reco-line"><b>Warum:</b> ${esc(r.warum||'')}</div>
      <div class="reco-meta">📈 ca. ${esc(r.anfragen||'?')} mehr Anfragen · Erfolg: ${esc(r.wahrscheinlichkeit||'?')}</div>
      ${r.schritte?`<div class="reco-steps">🛠️ ${esc(r.schritte)}</div>`:''}
      <div class="reco-btns">
        <button class="btn btn-cyan reco-ok" onclick="recoApply('${r.id}',this)">✅ Übernehmen</button>
        <button class="btn btn-ghost reco-later" onclick="recoLater('${r.id}',this)">Später</button>
      </div>
    </div>`;
  }).join('');
}
async function recoApply(id,btn){
  btn.disabled=true; btn.textContent='⏳ …';
  let d={}; try{d=await api('ads_apply',{id});}catch(e){}
  const card=btn.closest('.reco');
  if(card){
    const m=document.createElement('div');
    m.className='reco-result'+(d.executed?' done':'');
    m.textContent=(d.executed?'✅ ':'📝 ')+(d.msg||'Übernommen');
    card.appendChild(m);
  }else if(d.msg){alert((d.executed?'✅ ':'📝 ')+d.msg);}
  if(d.msg)speak(cleanSpeech(d.msg));
  setTimeout(()=>{if(typeof loadReco==='function'&&gl('s-ads').style.display==='block')loadReco();loadDashboard();},2500);
}
async function recoLater(id,btn){
  btn.disabled=true;
  await api('ads_later',{id});
  setTimeout(()=>{if(typeof loadReco==='function'&&gl('s-ads').style.display==='block')loadReco();loadDashboard();},400);
}
async function loadAds(){
  lastAdsReport=null; gl('adsKiBtn').disabled=true;
  gl('adsBody').innerHTML='<div class="prio-empty">Lade Kampagnen-Daten …</div>';
  try{
    const d=await api('ads_report');
    if(!d.ok){
      gl('adsBody').innerHTML=`<div class="fehler">⚠️ ${esc(d.error||'Fehler')}<br><br>Tipp: Sind alle 5 Ads-Zugangsdaten unter ⚙️ eingetragen?</div>`;
      return;
    }
    lastAdsReport=d.report; gl('adsKiBtn').disabled=false;
    renderAds(d.report);
  }catch(e){gl('adsBody').innerHTML='<div class="fehler">⚠️ Verbindung fehlgeschlagen.</div>';}
}
function renderAds(r){
  const s=r.summe||{};
  let html=`<div class="ads-sum">
    <div class="ads-stat"><div class="n">${eur(s.kosten)}</div><div class="l">Kosten 7 Tage</div></div>
    <div class="ads-stat"><div class="n">${s.klicks||0}</div><div class="l">Klicks</div></div>
    <div class="ads-stat"><div class="n">${(s.conv||0)}</div><div class="l">Anfragen</div></div>
    <div class="ads-stat"><div class="n">${s.cpl!=null?eur(s.cpl):'–'}</div><div class="l">Kosten/Anfrage</div></div>
  </div>`;
  if(!r.kampagnen||!r.kampagnen.length){html+='<div class="prio-empty">Keine aktiven Kampagnen-Daten in den letzten 7 Tagen.</div>';}
  else{
    html+='<table class="ads-tbl"><tr><th>Kampagne</th><th>Kosten</th><th>Klicks</th><th>Anfr.</th></tr>';
    r.kampagnen.forEach(k=>{html+=`<tr><td>${esc(k.name)}</td><td>${eur(k.kosten)}</td><td>${k.klicks}</td><td>${k.conv}</td></tr>`;});
    html+='</table>';
  }
  gl('adsBody').innerHTML=html;
}
/* ============ DILARA · WEBSITE ============ */
function openWeb(){showSection('web');loadWeb();}
async function loadWeb(){
  gl('webBody').innerHTML='<div class="prio-empty">Lade …</div>';
  try{const d=await api('website_reco');renderWeb(d.reco||[]);}catch(e){gl('webBody').innerHTML='<div class="prio-empty">Noch keine Analyse. Tipp „Website jetzt analysieren".</div>';}
}
async function webAnalyze(){
  const b=gl('webBtn');b.disabled=true;b.textContent='🔍 Dilara liest Deine Website …';
  gl('webBody').innerHTML='<div class="prio-empty">Dilara analysiert die Seite …</div>';
  try{const d=await api('website_analyze');if(!d.ok)gl('webBody').innerHTML=`<div class="fehler">⚠️ ${esc(d.error||'Fehler')}</div>`;else{renderWeb(d.reco||[]);loadDashboard();}}catch(e){gl('webBody').innerHTML='<div class="fehler">⚠️ Verbindung fehlgeschlagen.</div>';}
  b.disabled=false;b.textContent='🔍 Website neu analysieren';
}
function renderWeb(list){
  const offen=(list||[]).filter(r=>r.status==='offen').sort((a,b)=>ordPrio(a.dringlichkeit)-ordPrio(b.dringlichkeit));
  if(!offen.length){gl('webBody').innerHTML='<div class="prio-empty">✅ Keine offenen Website-Vorschläge. Tipp „Website analysieren" für eine frische Prüfung.</div>';return;}
  gl('webBody').innerHTML=offen.map(r=>{const p=PRIO[r.dringlichkeit]||PRIO.gelb;
    return `<div class="reco ${p.c}"><div class="reco-prio">${p.t}</div>
      <div class="reco-tit">${esc(r.titel||'')}</div>
      <div class="reco-line"><b>Was:</b> ${esc(r.was||'')}</div>
      <div class="reco-line"><b>Warum:</b> ${esc(r.warum||'')}</div>
      <div class="reco-meta">📈 erwartet: ${esc(r.verbesserung||'?')}</div>
      <div class="reco-btns">
        <button class="btn btn-cyan reco-ok" onclick="webApply('${r.id}',this)">✅ Übernehmen</button>
        <button class="btn btn-ghost reco-later" onclick="webAct('website_later','${r.id}',this)">Später</button>
        <button class="btn btn-ghost reco-later" onclick="webAct('website_dismiss','${r.id}',this)" style="color:var(--red)">Ablehnen</button>
      </div></div>`;}).join('');
}
async function webApply(id,btn){
  btn.disabled=true;btn.textContent='✓';
  let d={};try{d=await api('website_apply',{id});}catch(e){}
  const card=btn.closest('.reco');if(card&&d.msg){const m=document.createElement('div');m.className='reco-result';m.textContent='📝 '+d.msg;card.appendChild(m);}
  setTimeout(()=>{loadWeb();loadDashboard();},2200);
}
async function webAct(action,id,btn){btn.disabled=true;await api(action,{id});setTimeout(loadWeb,400);}

/* ============ AYLIN · LEXWARE ============ */
function openLex(){showSection('lex');loadLex();}
async function loadLex(){
  gl('lexBody').innerHTML='<div class="prio-empty">Lade Rechnungen aus Lexware …</div>';
  try{
    const d=await api('lex_invoices');
    if(!d.ok){gl('lexBody').innerHTML=`<div class="fehler">⚠️ ${esc(d.error||'Fehler')}<br><br>Tipp: Lexware-Schlüssel unter ⚙️ eingetragen?</div>`;return;}
    renderLex(d.invoices||[]);
  }catch(e){gl('lexBody').innerHTML='<div class="fehler">⚠️ Verbindung fehlgeschlagen.</div>';}
}
function renderLex(list){
  if(!list.length){gl('lexBody').innerHTML='<div class="prio-empty">✅ Keine offenen Rechnungen, Chef.</div>';return;}
  let sum=0,ueb=0;list.forEach(i=>{sum+=(+i.offen||0);if(i.ueberfaellig)ueb++;});
  let h=`<div class="ads-sum">
    <div class="ads-stat"><div class="n">${list.length}</div><div class="l">Offene Rechnungen</div></div>
    <div class="ads-stat"><div class="n">${eur(sum)}</div><div class="l">Offen gesamt</div></div>
    <div class="ads-stat hot"><div class="n">${ueb}</div><div class="l">⚠️ Überfällig</div></div></div>`;
  h+='<table class="ads-tbl"><tr><th>Nr.</th><th>Kunde</th><th>Offen</th><th>Fällig</th></tr>';
  list.forEach(i=>{h+=`<tr style="${i.ueberfaellig?'color:#ff97a1':''}"><td>${esc(i.nummer)}</td><td>${esc(i.kunde)}</td><td>${eur(i.offen)}</td><td>${esc(i.faellig||'-')}</td></tr>`;});
  h+='</table>';
  gl('lexBody').innerHTML=h;
}

function adsAnalyse(){
  if(!lastAdsReport)return;
  const r=lastAdsReport;
  let txt='Analysiere meine Google-Ads-Zahlen der letzten 7 Tage und gib mir konkrete, umsetzbare Tipps (was läuft gut, was verbrennt Geld, was soll ich ändern):\n\n';
  txt+=`Gesamt: Kosten ${eur(r.summe.kosten)}, Klicks ${r.summe.klicks}, Anfragen ${r.summe.conv}, Kosten/Anfrage ${r.summe.cpl!=null?eur(r.summe.cpl):'–'}\n\nKampagnen:\n`;
  r.kampagnen.forEach(k=>{txt+=`- ${k.name} (${k.status}): ${eur(k.kosten)}, ${k.klicks} Klicks, ${k.conv} Anfragen, CTR ${k.ctr}%, CPC ${eur(k.cpc)}\n`;});
  openChat('berater',txt);
}

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
    const msgs=history[mode].filter(m=>m.content&&(''+m.content).trim()).map(m=>({role:m.role,content:m.content}));
    const sys=MODI[mode].system()+(WISSEN?('\n\n'+WISSEN):'')+(AGENT_CTX?('\n\nDEINE AKTUELLEN LIVE-DATEN:\n'+AGENT_CTX):'');
    const payload=JSON.stringify({model:MODEL,max_tokens:2500,system:sys,messages:msgs});
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
  if(briefingText){ speak(briefingText+'Ich bin bereit, wenn Du es bist.'); return; }
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
