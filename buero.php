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

// ---------------------------------------------------------------------------
// BUERO-SPERRE (Alexa: "schliesse das Buero") — echte serverseitige Sperre.
// daten/lock.json: {locked:bool, token_version:int}. Fehlt die Datei = offen.
// Notfall-Reset: daten/lock.json per FTP loeschen.
// ---------------------------------------------------------------------------
$__lock = function_exists('oh_read') ? oh_read('lock', ['locked' => false, 'token_version' => 1]) : ['locked' => false, 'token_version' => 1];
if (!is_array($__lock)) { $__lock = ['locked' => false, 'token_version' => 1]; }
if (!empty($__lock['locked'])) {
    $_SESSION = [];
    if (isset($_POST['action'])) { // API-Aufrufe hart blocken
        http_response_code(423);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Buero ist gesperrt.']);
        exit;
    }
    http_response_code(423);
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex, nofollow"><title>OH Büro · Gesperrt</title><style>body{font-family:-apple-system,BlinkMacSystemFont,Roboto,sans-serif;background:#0a1426;color:#e8eefa;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}.box{max-width:420px;text-align:center;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:44px 28px}.s{font-size:46px;margin-bottom:14px}h1{font-size:20px;margin:0 0 10px}p{font-size:14px;color:#7d8db0;line-height:1.6;margin:0}</style></head><body><div class="box"><div class="s">&#128274;</div><h1>Das Büro ist geschlossen</h1><p>Sag <b>„Alexa, öffne das Büro"</b> und nenne das Passwort — dann ist alles wieder für dich da, großer Adnan.</p></div></body></html>';
    exit;
}
// Token-Version: Beim Sperren wird die Version erhoeht -> alle alten Sessions sterben sofort.
if (!empty($_SESSION['eingeloggt'])) {
    if ((int)($_SESSION['lock_token'] ?? 0) !== (int)($__lock['token_version'] ?? 1)) {
        $_SESSION = [];
    }
}

// Login-Logik
if (isset($_POST['login_pw'])) {
    if ($_POST['login_pw'] === $PASSWORT) {
        $_SESSION['eingeloggt'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['lock_token'] = (int)($__lock['token_version'] ?? 1);
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
        $_SESSION['lock_token'] = (int)($__lock['token_version'] ?? 1);
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
            'freigaben' => function_exists('oh_freigaben') ? oh_freigaben('offen') : [],
            'lexware'  => oh_read('lexware', []),
            'ziel'     => function_exists('oh_ziel_status') ? oh_ziel_status() : null,
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
    } elseif ($a === 'chat_load') {
        // Gespraechsgedaechtnis: gespeicherten Chat-Verlauf eines Agenten laden
        $ag = preg_replace('/[^a-z0-9_]/', '', strtolower($in['agent'] ?? ''));
        echo json_encode(['messages' => ($ag && function_exists('oh_agent_chat_load')) ? oh_agent_chat_load($ag) : []]);
    } elseif ($a === 'chat_save') {
        // Gespraechsgedaechtnis: Chat-Verlauf eines Agenten speichern
        $ag = preg_replace('/[^a-z0-9_]/', '', strtolower($in['agent'] ?? ''));
        $msgs = $in['messages'] ?? [];
        if ($ag && is_array($msgs) && function_exists('oh_agent_chat_save')) oh_agent_chat_save($ag, $msgs);
        echo json_encode(['ok' => true]);
    } elseif ($a === 'agent_memory') {
        // Durchsuchbares, themen-sortiertes Wissensarchiv eines Agenten
        $ag = preg_replace('/[^a-z0-9_]/', '', strtolower($in['agent'] ?? ''));
        $q  = trim((string)($in['q'] ?? ''));
        if ($ag === '' || !function_exists('oh_agent_mem_read')) { echo json_encode(['ok' => false]); exit; }
        $gesamt = count(oh_agent_mem_read($ag));
        if ($q !== '' && function_exists('oh_agent_mem_search')) {
            echo json_encode(['ok' => true, 'modus' => 'suche', 'gesamt' => $gesamt, 'treffer' => oh_agent_mem_search($ag, $q, 25)]);
        } else {
            echo json_encode(['ok' => true, 'modus' => 'themen', 'gesamt' => $gesamt, 'themen' => function_exists('oh_agent_mem_grouped') ? oh_agent_mem_grouped($ag) : []]);
        }
    } elseif ($a === 'website_reco') {
        echo json_encode(['ok' => true, 'reco' => oh_read('website_reco', [])]);
    } elseif ($a === 'website_analyze') {
        $werr = null;
        $r = oh_website_analyze($werr);
        echo json_encode($r !== null ? ['ok' => true, 'reco' => $r] : ['ok' => false, 'error' => $werr]);
    } elseif ($a === 'website_editable') {
        // Schritt 5: aktuelle editierbare Elemente (Überschrift/CTA) + vorgemerkte Änderungen
        echo json_encode([
            'ok' => true,
            'elemente' => function_exists('oh_website_get_editable') ? oh_website_get_editable() : [],
            'pending'  => function_exists('oh_website_pending') ? oh_website_pending() : [],
        ]);
    } elseif ($a === 'website_queue') {
        // Schritt 5: Text-Änderung VORMERKEN (keine Ausführung)
        $r = function_exists('oh_website_queue_change')
            ? oh_website_queue_change((string)($in['element'] ?? 'Text'), (string)($in['alt'] ?? ''), (string)($in['neu'] ?? ''), (string)($in['datei'] ?? 'index.php'))
            : null;
        echo json_encode($r ? ['ok' => true, 'eintrag' => $r] : ['ok' => false, 'error' => 'Ungültige Änderung (alt/neu fehlt oder identisch).']);
    } elseif ($a === 'website_execute') {
        // Schritt 6: vorgemerkte Änderung WIRKLICH ausführen (mit Backup + Rückgängig)
        $eerr = null;
        $r = function_exists('oh_website_execute_change') ? oh_website_execute_change((string)($in['id'] ?? ''), $eerr) : null;
        echo json_encode($r ? ['ok' => true] + $r : ['ok' => false, 'error' => $eerr ?: 'Ausführung fehlgeschlagen.']);
    } elseif ($a === 'website_apply' || $a === 'website_later' || $a === 'website_dismiss') {
        $id = $in['id'] ?? '';
        $reco = oh_read('website_reco', []);
        $hit = null;
        foreach ($reco as $rr0) { if (($rr0['id'] ?? '') === $id) { $hit = $rr0; break; } }
        $executed = false; $undo = false; $msg = '';
        if ($a === 'website_apply') {
            if ($hit && function_exists('oh_website_apply_reco')) {
                $werr = null;
                $r = oh_website_apply_reco($hit, $werr);   // sichere Text-Änderung WIRKLICH ausführen (Backup+Undo)
                $executed = !empty($r['executed']);
                $undo = $executed;
                $msg = $r['msg'] ?? '';
            } else { $msg = 'Notiert, Chef!'; }
            $newStatus = 'uebernommen';
        } elseif ($a === 'website_dismiss') { $newStatus = 'abgelehnt'; }
        else { $newStatus = 'spaeter'; }
        foreach ($reco as &$rr) { if (($rr['id'] ?? '') === $id) { $rr['status'] = $newStatus; } }
        unset($rr);
        oh_write('website_reco', $reco);
        if ($a === 'website_apply' && $hit) oh_log_activity('dilara', ($executed ? 'Website LIVE geändert: ' : 'Website-Optimierung vorgemerkt: ') . ($hit['titel'] ?? ''));
        // Bei echter Ausführung hat oh_website_execute_change bereits einen rückgängig-machbaren 'website_text'-Eintrag erzeugt – kein Doppel-Log.
        if ($hit && function_exists('oh_change_log') && !$executed) oh_change_log('website_reco', 'Website-Vorschlag: ' . ($hit['titel'] ?? ''), 'offen', $newStatus, $id);
        echo json_encode(['ok' => true, 'executed' => $executed, 'undo' => $undo, 'msg' => $a === 'website_apply' ? $msg : '']);
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
            'has_lexware'    => !empty($c['lexware_api_key']),
            'autopilot_kaan'   => $c['autopilot_kaan'] ?? 'an',
            'autopilot_aylin'  => $c['autopilot_aylin'] ?? 'an',
            'autopilot_dilara' => $c['autopilot_dilara'] ?? 'an',
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
            'lexware_api_key' => $in['lexware_api_key'] ?? '',
            'autopilot_kaan'   => $in['autopilot_kaan'] ?? '',
            'autopilot_aylin'  => $in['autopilot_aylin'] ?? '',
            'autopilot_dilara' => $in['autopilot_dilara'] ?? '',
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
    } elseif ($a === 'prognose') {
        $err = null;
        $p = function_exists('oh_prognose') ? oh_prognose($err) : null;
        echo json_encode($p !== null ? ['ok' => true, 'prognose' => $p] : ['ok' => false, 'error' => $err]);
    } elseif ($a === 'adsplan_get') {
        $items = function_exists('oh_ads_plan') ? oh_ads_plan() : [];
        $done = 0; foreach ($items as $i) if (!empty($i['done'])) $done++;
        echo json_encode(['ok' => true, 'items' => $items, 'done' => $done, 'gesamt' => count($items)]);
    } elseif ($a === 'adsplan_toggle') {
        if (function_exists('oh_ads_plan_toggle')) oh_ads_plan_toggle((string)($in['id'] ?? ''), !empty($in['done']));
        echo json_encode(['ok' => true]);
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
    } elseif ($a === 'agent_inbox') {
        $list = function_exists('oh_agent_inbox') ? oh_agent_inbox($in['agent'] ?? '') : [];
        $tasks = function_exists('oh_tasks') ? array_slice(oh_tasks($in['agent'] ?? '', 'offen'), -8) : [];
        echo json_encode(['ok' => true, 'list' => array_slice($list, -10), 'tasks' => $tasks]);
    } elseif ($a === 'task_done') {
        $ok = function_exists('oh_task_done') ? oh_task_done($in['id'] ?? '', 'chef') : false;
        echo json_encode(['ok' => $ok]);
    } elseif ($a === 'changes') {
        $c = array_reverse(oh_read('changes', []));
        echo json_encode(['ok' => true, 'changes' => array_slice($c, 0, 40)]);
    } elseif ($a === 'change_undo') {
        $uerr = null;
        $ok = function_exists('oh_change_undo') ? oh_change_undo($in['id'] ?? '', $uerr) : false;
        echo json_encode(['ok' => $ok, 'error' => $uerr]);
    } elseif ($a === 'archiv') {
        $arch = oh_read('archiv', []);
        $tag = $in['tag'] ?? '';
        if ($tag !== '' && isset($arch[$tag]) && is_array($arch[$tag])) {
            echo json_encode(['ok' => true, 'tag' => $tag, 'eintraege' => array_values($arch[$tag]),
                'heute' => date('Y-m-d'), 'gestern' => date('Y-m-d', time() - 86400)]);
        } else {
            $tage = [];
            foreach ($arch as $d => $list) { if (is_array($list)) $tage[] = ['tag' => $d, 'anzahl' => count($list)]; }
            usort($tage, function($x, $y){ return strcmp($y['tag'], $x['tag']); });
            echo json_encode(['ok' => true, 'tage' => array_slice($tage, 0, 90),
                'heute' => date('Y-m-d'), 'gestern' => date('Y-m-d', time() - 86400)]);
        }
    } elseif ($a === 'baustelle_done') {
        $id = $in['id'] ?? '';
        $lead = oh_update_lead($id, ['status' => 'abgeschlossen', 'abschluss_ts' => time()], 'Yusuf: Baustelle als abgeschlossen markiert');
        if ($lead) {
            if (function_exists('oh_log_activity')) oh_log_activity('yusuf', 'Baustelle abgeschlossen: ' . ($lead['name'] ?: $id) . ' – Aylin übernimmt die Abrechnung.');
            if (function_exists('oh_agent_mem_add')) {
                oh_agent_mem_add('yusuf', 'Baustelle abgeschlossen: ' . ($lead['name'] ?: $id) . ' (' . ($lead['kategorie'] ?? '') . ')', 'fund');
            }
            if (function_exists('oh_agent_msg_send')) {
                oh_agent_msg_send('yusuf', 'aylin', 'Baustelle "' . ($lead['name'] ?: $id) . '" ist abgeschlossen – bitte Schlussrechnung in Lexware prüfen/stellen.');
                oh_agent_msg_send('yusuf', 'dilara', 'Projekt "' . ($lead['name'] ?: $id) . '" fertig – in 5 Tagen geht automatisch die Bewertungs-Anfrage raus.');
            }
        }
        echo json_encode(['ok' => (bool)$lead]);
    } elseif ($a === 'lex_refresh') {
        $lerr = null;
        $lx = function_exists('oh_lex_refresh') ? oh_lex_refresh($lerr) : null;
        echo json_encode($lx !== null ? ['ok' => true, 'lexware' => $lx] : ['ok' => false, 'error' => $lerr ?: 'nicht verfügbar']);
    } elseif ($a === 'kaan_analyse') {
        $kerr = null;
        $ka = function_exists('oh_kaan_email_analyse') ? oh_kaan_email_analyse($kerr) : null;
        echo json_encode($ka !== null ? ['ok' => true, 'mails' => $ka['mails'] ?? 0, 'offen' => count($ka['offene_punkte'] ?? [])] : ['ok' => false, 'error' => $kerr ?: 'nicht verfügbar']);
    } elseif ($a === 'freigaben') {
        echo json_encode(['ok' => true, 'freigaben' => function_exists('oh_freigaben') ? oh_freigaben('offen') : []]);
    } elseif ($a === 'triage_now') {
        $terr = null;
        $tr = function_exists('oh_msg_triage') ? oh_msg_triage($terr) : ['neu' => 0, 'fehler' => 'nicht verfügbar'];
        echo json_encode([
            'ok' => empty($tr['fehler']),
            'neu' => $tr['neu'] ?? 0,
            'error' => $tr['fehler'] ?? null,
            'freigaben' => function_exists('oh_freigaben') ? oh_freigaben('offen') : [],
        ]);
    } elseif ($a === 'freigabe_decide') {
        $id  = $in['id'] ?? '';
        $dec = $in['decision'] ?? '';
        $txt = array_key_exists('text', $in) ? (string)$in['text'] : null;
        $hit = null;
        foreach (oh_read('freigaben', []) as $x) { if (($x['id'] ?? '') === $id) { $hit = $x; break; } }
        if (!$hit) { echo json_encode(['ok' => false, 'error' => 'nicht gefunden']); exit; }

        if ($dec === 'ablehnen') {
            oh_freigabe_update($id, ['status' => 'abgelehnt']);
            if (function_exists('oh_log_activity')) oh_log_activity($hit['agent'] ?? 'kaan', 'Freigabe abgelehnt: ' . ($hit['titel'] ?? ''));
            if (function_exists('oh_change_log')) oh_change_log('freigabe', 'Abgelehnt: ' . ($hit['titel'] ?? ''), 'offen', 'abgelehnt', $id);
            echo json_encode(['ok' => true, 'sent' => false]);
        } elseif ($dec === 'spaeter') {
            oh_freigabe_update($id, ['status' => 'spaeter']);
            if (function_exists('oh_change_log')) oh_change_log('freigabe', 'Verschoben: ' . ($hit['titel'] ?? ''), 'offen', 'spaeter', $id);
            echo json_encode(['ok' => true, 'sent' => false]);
        } else { // uebernehmen
            $reply = trim($txt !== null ? $txt : ($hit['vorschlag'] ?? ''));
            $sent = false; $info = ''; $note = '';
            // SCHUTZ: nie leere Mails, nie an noreply-/Automaten-Adressen
            $istNoreply = function_exists('oh_ist_noreply') && oh_ist_noreply($hit['to'] ?? '');
            if (($hit['typ'] ?? '') === 'antwort' && ($hit['kanal'] ?? '') === 'email'
                && filter_var($hit['to'] ?? '', FILTER_VALIDATE_EMAIL)) {
                if ($reply === '') {
                    $note = 'kein Antworttext vorhanden – es wurde NICHTS versendet';
                } elseif ($istNoreply) {
                    $note = 'Absender ist eine No-Reply-Adresse – Antworten kommen dort nie an, nichts versendet';
                } else {
                    $res = oh_send_mail($hit['to'], 'Ihre Nachricht an OH Haustechnik', $reply);
                    $sent = !empty($res['ok']); $info = $res['info'] ?? '';
                }
            }
            oh_freigabe_update($id, ['status' => $sent ? 'gesendet' : 'uebernommen', 'final' => $reply]);
            if (function_exists('oh_log_activity')) oh_log_activity($hit['agent'] ?? 'kaan', ($sent ? 'Antwort gesendet' : 'Freigabe übernommen') . ': ' . ($hit['titel'] ?? ''));
            // Gesendete Mails sind NICHT rückholbar – ehrlich markieren
            if (function_exists('oh_change_log')) oh_change_log('freigabe', ($sent ? 'Antwort GESENDET: ' : 'Übernommen: ') . ($hit['titel'] ?? ''), 'offen', $sent ? 'gesendet' : 'uebernommen', $id, !$sent);
            echo json_encode(['ok' => true, 'sent' => $sent, 'text' => $reply, 'info' => $info, 'note' => $note]);
        }
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  /* Buero neu – dunkles Glas-Design (Tokens aus der React-App) */
  --bg:#070a12; --bg2:#04070d;
  --card:#0e131d; --card2:#131a27;
  --glass:rgba(19,26,39,.72); --glass-2:rgba(14,19,29,.86);
  --line:#1e2940;
  --cyan:#5b91f5; --cyan-d:#1693c4; --cyan-soft:rgba(57,214,255,.12);
  --txt:#dfeaf6; --txt-dim:#7e93ad;
  --gold:#e7b14b; --green:#34e09a; --red:#ff5d6c;
  --shadow:0 10px 34px rgba(0,0,0,.45);
  --glow:0 0 26px rgba(57,214,255,.18);
  --sbw:248px;
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
html,body{height:100%;}
body{
  font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','Segoe UI',Inter,Roboto,sans-serif;
  background:var(--bg); color:var(--txt); min-height:100vh; overflow-x:hidden; position:relative;
  -webkit-font-smoothing:antialiased;
}
::-webkit-scrollbar{width:9px;height:9px;}
::-webkit-scrollbar-thumb{background:#1e2940;border-radius:8px;}
::-webkit-scrollbar-track{background:transparent;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

/* --- HINTERGRUND + LOGO-WASSERZEICHEN --- */
.bg-fx{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.bg-fx .glow{position:absolute;width:120vmax;height:120vmax;left:50%;top:-40%;transform:translateX(-50%);
  background:radial-gradient(circle at center, rgba(57,214,255,.10), rgba(8,18,34,0) 60%);}
.bg-fx .glow2{position:absolute;width:80vmax;height:80vmax;right:-25%;bottom:-35%;
  background:radial-gradient(circle at center, rgba(22,147,196,.10), rgba(8,18,34,0) 60%);}
.bg-fx .grid{position:absolute;inset:0;
  background-image:linear-gradient(rgba(57,214,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(57,214,255,.04) 1px,transparent 1px);
  background-size:52px 52px;mask-image:radial-gradient(circle at 60% 10%,#000 25%,transparent 80%);}
.bg-fx .scan{display:none;}
.bg-fx:after{content:'OH';position:absolute;right:-1vw;bottom:-7vh;font-size:38vw;line-height:.8;font-weight:800;
  letter-spacing:-.04em;color:rgba(57,214,255,.030);}
.corner{display:none;}

/* ====================== SIDEBAR (Buero-neu Navigation) ====================== */
.sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sbw);z-index:40;display:flex;flex-direction:column;
  padding:18px 14px;background:rgba(10,15,25,.92);backdrop-filter:blur(16px);border-right:1px solid var(--line);
  transition:transform .28s cubic-bezier(.4,0,.2,1);}
.sb-brand{display:flex;align-items:center;gap:9px;padding:4px 8px 16px;}
.sb-brand .mk{font-size:24px;font-weight:300;letter-spacing:6px;color:#fff;text-shadow:0 0 14px rgba(57,214,255,.5);}
.sb-brand .sub{font-size:8px;letter-spacing:3px;color:var(--cyan);margin-top:5px;font-weight:600;}
.sb-nav{flex:1;overflow-y:auto;}
.sb-group{margin-bottom:16px;}
.sb-glabel{font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:var(--txt-dim);margin:0 0 4px 8px;font-weight:700;}
.sb-item{width:100%;display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:12px;font-size:14px;
  color:var(--txt);background:none;border:none;cursor:pointer;font-family:inherit;margin-bottom:3px;text-align:left;
  text-decoration:none;transition:background .15s,color .15s;}
.sb-item .ic{font-size:16px;width:20px;text-align:center;}
.sb-item:hover{background:rgba(255,255,255,.05);}
.sb-item.active{background:var(--cyan-soft);color:var(--cyan);font-weight:700;}
.sb-foot{border-top:1px solid var(--line);padding-top:10px;margin-top:6px;}
.sb-foot .sb-item.logout{color:var(--red);}
.sb-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:35;opacity:0;visibility:hidden;transition:opacity .25s;}
body.sb-open .sb-backdrop{opacity:1;visibility:visible;}
.hamburger{display:none;background:var(--glass);border:1px solid var(--line);color:var(--cyan);font-size:18px;width:40px;height:40px;
  border-radius:11px;cursor:pointer;align-items:center;justify-content:center;}

.wrap{max-width:100%;margin:0;position:relative;z-index:2;padding-bottom:46px;}

/* --- HEADER --- */
header{padding:13px 18px;padding-top:calc(13px + env(safe-area-inset-top));
  display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:20;
  background:linear-gradient(180deg,rgba(7,10,18,.94),rgba(7,10,18,.55) 70%,transparent);backdrop-filter:blur(8px);}
.brand{display:flex;align-items:center;gap:11px;}
.brand .mark{font-size:21px;font-weight:300;letter-spacing:5px;color:#fff;text-shadow:0 0 14px rgba(57,214,255,.5);}
.brand .sub{font-size:8px;letter-spacing:3px;color:var(--cyan);opacity:.85;margin-top:2px;font-weight:600;}
.hbtns{display:flex;gap:8px;margin-left:auto;}
.icobtn{background:var(--glass);border:1px solid var(--line);color:var(--cyan);font-size:16px;width:40px;height:40px;
  border-radius:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:background .15s;}
.icobtn:hover{background:var(--cyan-soft);}
.icobtn:active{transform:scale(.93);}
.statusbar{display:flex;gap:14px;align-items:center;padding:0 20px 8px;font-family:'SF Mono',ui-monospace,monospace;
  font-size:10px;color:var(--txt-dim);letter-spacing:1px;}
.dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);display:inline-block;margin-right:5px;animation:pulse 2.2s infinite;}
@keyframes pulse{50%{opacity:.4;}}

/* --- BOOT / WILLKOMMEN --- */
#boot{position:fixed;inset:0;z-index:100;background:radial-gradient(circle at 50% 35%,#0a1426,#03060c 70%);
  display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:30px;transition:opacity .7s ease;}
#boot .ring{width:128px;height:128px;border-radius:50%;position:relative;margin-bottom:30px;}
#boot .ring:before,#boot .ring:after{content:'';position:absolute;inset:0;border-radius:50%;border:2px solid transparent;}
#boot .ring:before{border-top-color:var(--cyan);border-right-color:var(--cyan);animation:spin 1.4s linear infinite;box-shadow:0 0 22px rgba(57,214,255,.5);}
#boot .ring:after{inset:16px;border-bottom-color:rgba(57,214,255,.5);border-left-color:rgba(57,214,255,.5);animation:spin 2s linear infinite reverse;}
@keyframes spin{to{transform:rotate(360deg);}}
#boot .core{position:absolute;inset:42px;border-radius:50%;background:radial-gradient(circle,#fff,var(--cyan) 70%);box-shadow:0 0 30px var(--cyan);animation:pulse 1.6s infinite;}
#boot .lines{font-family:'SF Mono',ui-monospace,monospace;font-size:12px;color:var(--cyan);text-align:left;min-height:90px;letter-spacing:1px;line-height:2;text-shadow:0 0 8px rgba(57,214,255,.4);}
#boot .greet{font-size:26px;font-weight:300;letter-spacing:2px;color:#fff;margin-top:26px;opacity:0;transition:opacity .8s;text-shadow:0 0 20px rgba(57,214,255,.5);}
#boot .greet b{font-weight:600;color:var(--cyan);}
#boot .greet small{display:block;font-size:12px;color:var(--txt-dim);letter-spacing:2px;margin-top:10px;font-family:'SF Mono',monospace;}

/* --- SEKTIONEN / KARTEN --- */
.section-title{font-family:'SF Mono',ui-monospace,monospace;font-size:11px;font-weight:600;letter-spacing:2px;color:var(--cyan);margin:22px 16px 4px;opacity:.85;text-transform:uppercase;}
.scan-btn{cursor:pointer;color:var(--cyan);border:1px solid var(--line);background:var(--glass);border-radius:10px;padding:7px 13px;font-size:12px;font-family:'SF Mono',monospace;transition:background .15s;}
.scan-btn:hover{background:var(--cyan-soft);}
.scan-btn:active{transform:scale(.96);}
.dash-bar{display:flex;justify-content:flex-end;margin:12px 16px 0;}

/* KPI-Karten + Umsatz-Fortschritt (Buero-neu Dashboard) */
.kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:12px 16px 0;}
.kpi{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:15px;backdrop-filter:blur(12px);
  box-shadow:var(--shadow);animation:fadeUp .4s ease both;}
.kpi .top{display:flex;align-items:center;justify-content:space-between;}
.kpi .ic{font-size:18px;opacity:.9;}
.kpi .badge{width:8px;height:8px;border-radius:50%;background:var(--txt-dim);}
.kpi.ok .badge{background:var(--green);box-shadow:0 0 8px var(--green);}
.kpi.warn .badge{background:var(--gold);box-shadow:0 0 8px var(--gold);}
.kpi.bad .badge{background:var(--red);box-shadow:0 0 8px var(--red);}
.kpi .n{font-size:28px;font-weight:800;color:#fff;margin-top:8px;letter-spacing:-.5px;}
.kpi .l{font-size:11px;color:var(--txt-dim);text-transform:uppercase;letter-spacing:.6px;margin-top:2px;font-weight:600;}
.kpi .s{font-size:11px;color:var(--txt-dim);margin-top:4px;}
.kpi.bad .s{color:var(--red);} .kpi.warn .s{color:var(--gold);} .kpi.ok .s{color:var(--green);}
.umsatz{margin:14px 16px 0;background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:16px;box-shadow:var(--shadow);animation:fadeUp .4s ease both;}
.umsatz-h{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:10px;}
.umsatz-h .t{font-size:13px;color:var(--txt-dim);font-weight:600;}
.umsatz-h .v{font-size:15px;font-weight:800;color:#fff;}
.umsatz-h .v b{color:var(--cyan);}
.bar{height:10px;border-radius:6px;background:#0a0f1a;overflow:hidden;border:1px solid var(--line);}
.bar > i{display:block;height:100%;border-radius:6px;background:linear-gradient(90deg,var(--cyan-d),var(--cyan));box-shadow:0 0 14px rgba(57,214,255,.5);transition:width .8s cubic-bezier(.4,0,.2,1);}

/* Tagesfokus (Glas + Glow) */
.fokus{margin:14px 16px 0;background:var(--glass);border:1px solid var(--cyan);border-radius:18px;padding:16px;backdrop-filter:blur(14px);box-shadow:var(--glow);animation:fadeUp .4s ease both;}
.fokus-h{font-size:14px;font-weight:800;color:#fff;margin-bottom:10px;}
.fokus-i{display:flex;align-items:center;gap:12px;padding:9px 0;border-top:1px solid var(--line);cursor:pointer;}
.fokus-i:first-of-type{border-top:none;}
.fokus-n{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#031018;flex-shrink:0;background:var(--cyan);}
.fokus-n.rot{background:var(--red);}.fokus-n.gelb{background:var(--gold);}.fokus-n.gruen{background:var(--green);}
.fokus-i .tt{font-size:13.5px;font-weight:600;color:#fff;}
.fokus-i .ta{font-size:11px;color:var(--txt-dim);margin-top:1px;}

/* Akkordeon */
.acc{margin:12px 16px 0;background:var(--glass);border:1px solid var(--line);border-radius:14px;overflow:hidden;backdrop-filter:blur(10px);}
.acc-h{display:flex;align-items:center;gap:10px;padding:14px 15px;cursor:pointer;}
.acc-c{color:var(--cyan);font-size:11px;transition:transform .2s;display:inline-block;}
.acc-t{flex:1;font-size:13.5px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.acc-cnt{font-size:11px;color:#031018;background:var(--cyan);border-radius:7px;padding:1px 7px;font-weight:700;}
.acc-b{padding:0 12px 12px;}
.pill{font-size:9.5px;font-weight:700;letter-spacing:.3px;padding:2px 8px;border-radius:7px;text-transform:uppercase;}
.pill.sm{font-size:8.5px;padding:1px 6px;}
.pill.rot{background:rgba(255,93,108,.2);color:#ff8b96;}
.pill.gelb{background:rgba(231,177,75,.2);color:#f0cd8a;}
.pill.gruen{background:rgba(52,224,154,.2);color:#7ef0bd;}
.task-btns{display:flex;gap:7px;margin-top:9px;flex-wrap:wrap;}
.tb{background:var(--glass);border:1px solid var(--line);color:var(--txt);border-radius:9px;padding:8px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:border-color .15s,background .15s;}
.tb:hover{border-color:var(--cyan);}
.tb.ok{background:var(--cyan-soft);border-color:var(--cyan);color:var(--cyan);}
.tb.no{color:var(--red);border-color:rgba(255,93,108,.4);}
.ar-prio{font-size:13px;color:var(--txt);margin-bottom:10px;} .ar-prio b{color:var(--cyan);} .ar-prio ol{margin:6px 0 0 18px;line-height:1.7;}
.ar-feed{margin:10px 0;} .ar-feed b{color:var(--cyan);font-size:12.5px;}
.ar-msg{font-size:12.5px;color:var(--txt);background:rgba(57,214,255,.06);border-left:2px solid var(--cyan);border-radius:8px;padding:8px 10px;margin-top:7px;line-height:1.5;}
.ar-from{color:var(--cyan);font-weight:700;display:block;font-size:11px;margin-bottom:2px;}
.ar-funde{margin-top:10px;} .ar-ag{margin-top:8px;font-size:12.5px;} .ar-ag b{color:#fff;} .ar-ag ul{margin:3px 0 0 16px;color:var(--txt-dim);line-height:1.5;}
.agent-funde{background:var(--glass-2);border:1px solid var(--line);border-radius:13px;padding:13px 15px;margin:8px 16px;}
.agent-funde b{color:var(--cyan);font-size:13px;} .agent-funde ul{margin:6px 0 0 16px;color:var(--txt);line-height:1.6;font-size:13px;}
.akt-feed{display:flex;flex-direction:column;gap:2px;}
.akt-row{display:flex;gap:11px;align-items:flex-start;padding:9px 2px;border-bottom:1px solid var(--line);}
.akt-row:last-child{border-bottom:none;}
.akt-ico{font-size:17px;flex-shrink:0;margin-top:1px;}
.akt-t{font-size:13px;color:var(--txt);line-height:1.45;} .akt-t b{color:var(--cyan);}
.akt-z{font-size:10.5px;color:var(--txt-dim);margin-top:2px;}
.card{background:var(--glass);border:1px solid var(--line);border-radius:18px;padding:18px 16px;margin:14px 16px;backdrop-filter:blur(14px);box-shadow:var(--shadow);animation:fadeUp .4s ease both;}
h2{font-size:15px;font-weight:700;color:#fff;margin-bottom:8px;display:flex;align-items:center;gap:8px;}
.intro{font-size:13px;color:var(--txt-dim);margin-bottom:12px;line-height:1.6;}

/* --- DASHBOARD-Kopf --- */
.dash-head{margin:14px 16px 0;}
.dash-hi{font-size:23px;font-weight:800;letter-spacing:-.3px;color:#fff;}
.dash-hi b{font-weight:800;color:var(--cyan);}
.dash-stats{display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;}
.stat{flex:1;min-width:90px;background:var(--glass);border:1px solid var(--line);border-radius:13px;padding:11px 13px;backdrop-filter:blur(10px);}
.stat .n{font-size:22px;font-weight:800;color:#fff;}
.stat .l{font-size:10px;color:var(--txt-dim);letter-spacing:1px;text-transform:uppercase;margin-top:2px;}
.stat.hot .n{color:var(--red);}
.prio-group{margin:8px 16px 4px;}
.prio-lbl{display:flex;align-items:center;gap:8px;font-family:'SF Mono',ui-monospace,monospace;font-size:11px;font-weight:600;letter-spacing:1px;color:var(--txt-dim);margin:12px 0 7px;text-transform:uppercase;}
.prio-dot{width:9px;height:9px;border-radius:50%;}
.prio-dot.rot{background:var(--red);box-shadow:0 0 9px var(--red);}
.prio-dot.gelb{background:var(--gold);box-shadow:0 0 9px var(--gold);}
.prio-dot.gruen{background:var(--green);box-shadow:0 0 9px var(--green);}
.prio-list{display:flex;flex-direction:column;gap:8px;}
.task{display:flex;align-items:flex-start;gap:11px;background:var(--glass);border:1px solid var(--line);border-radius:13px;padding:13px 14px;backdrop-filter:blur(10px);cursor:pointer;transition:transform .12s,border-color .2s;}
.task:hover{border-color:var(--cyan);}
.task:active{transform:scale(.98);}
.task.rot{border-left:3px solid var(--red);}
.task.gelb{border-left:3px solid var(--gold);}
.task.gruen{border-left:3px solid var(--green);}
.task .tx{flex:1;min-width:0;}
.task .tt{font-size:14px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.task .ta{font-size:11.5px;color:var(--cyan);margin-top:2px;}
.task .go{color:var(--cyan);font-size:18px;flex-shrink:0;}
.prio-empty{font-size:12px;color:var(--txt-dim);padding:4px 2px;opacity:.7;}
.ki-alert{margin:12px 16px 0;padding:13px 15px;border-radius:13px;font-size:13px;line-height:1.5;background:linear-gradient(135deg,rgba(255,93,108,.95),rgba(200,40,55,.95));color:#fff;font-weight:600;box-shadow:0 0 22px rgba(255,93,108,.4);border:1px solid rgba(255,255,255,.18);}
.ki-alert b{color:#fff;}
.ki-alert.warn{background:linear-gradient(135deg,rgba(231,177,75,.95),rgba(190,130,30,.95));box-shadow:0 0 20px rgba(231,177,75,.35);}
.mert-card{margin:14px 16px 0;background:var(--glass);border:1px solid var(--cyan);border-radius:18px;padding:16px;backdrop-filter:blur(14px);box-shadow:var(--glow);animation:fadeUp .4s ease both;}
.mert-head{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
.mert-av{width:42px;height:42px;border-radius:13px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-size:21px;box-shadow:0 0 16px rgba(57,214,255,.4);}
.mert-nm{font-weight:800;font-size:15px;color:#fff;}
.mert-rl{font-size:10.5px;color:var(--cyan);letter-spacing:.3px;}
.mert-txt{font-size:14px;line-height:1.6;color:var(--txt);white-space:pre-wrap;}
.mert-txt b{color:var(--cyan);}
.mert-refresh{margin-top:12px;width:100%;padding:11px;background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:11px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}
.task-ico{font-size:20px;flex-shrink:0;margin-top:1px;}
.task-why{font-size:12.5px;color:var(--txt-dim);margin-top:8px;line-height:1.5;}
.task-go{display:block;margin-top:8px;background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:9px;padding:8px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
.task.done{opacity:.55;}
.task.done .tt{text-decoration:line-through;}
.prog-card{border-color:var(--cyan);box-shadow:var(--glow);}
.prog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:4px 0 12px;}
.prog-stat{background:var(--glass-2);border:1px solid var(--line);border-radius:12px;padding:12px 10px;text-align:center;}
.prog-stat .n{font-size:21px;font-weight:800;color:#fff;letter-spacing:-.3px;}
.prog-stat .l{font-size:10px;color:var(--txt-dim);letter-spacing:.3px;margin-top:4px;line-height:1.3;}
.prog-stat.hi{background:var(--cyan-soft);border-color:var(--cyan);}
.prog-stat.hi .n{color:var(--cyan);}
.prog-line{font-size:12.5px;color:var(--txt);line-height:1.5;margin-top:8px;}
.prog-line b{color:#fff;}
.prog-pot{font-size:13px;color:var(--txt);line-height:1.55;margin-top:12px;padding:11px 13px;background:rgba(52,224,154,.08);border:1px solid var(--green);border-radius:12px;}
.prog-pot b{color:var(--green);}
.prog-top{margin-top:10px;}
.prog-top-h{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--txt-dim);font-weight:700;margin-bottom:6px;}
.prog-top-i{font-size:12.5px;color:var(--txt);padding:6px 0;border-bottom:1px solid var(--line);}
.prog-top-i:last-child{border-bottom:none;}
.prog-top-i span{display:inline-block;min-width:62px;color:var(--green);font-weight:800;}
.prog-foot{font-size:10.5px;color:var(--txt-dim);margin-top:12px;font-style:italic;line-height:1.4;}
.ap-kat{font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--cyan);margin:16px 0 7px;}
.ap-item{display:flex;gap:11px;align-items:flex-start;padding:11px 12px;margin-bottom:8px;background:var(--glass-2);border:1px solid var(--line);border-radius:12px;cursor:pointer;transition:border-color .15s,opacity .15s;}
.ap-item:hover{border-color:var(--cyan);}
.ap-item input{width:20px;height:20px;flex-shrink:0;margin-top:1px;accent-color:var(--green);cursor:pointer;}
.ap-txt{display:flex;flex-direction:column;gap:3px;}
.ap-txt b{font-size:13.5px;font-weight:600;color:#fff;line-height:1.4;}
.ap-nutzen{font-size:11.5px;color:var(--txt-dim);line-height:1.4;}
.ap-item.done{opacity:.6;}
.ap-item.done .ap-txt b{text-decoration:line-through;color:var(--txt-dim);}
.ads-sum{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px;}
.ads-stat{background:var(--glass-2);border:1px solid var(--line);border-radius:12px;padding:12px;}
.ads-stat .n{font-size:19px;font-weight:800;color:#fff;}
.ads-stat .l{font-size:10px;color:var(--txt-dim);letter-spacing:.5px;text-transform:uppercase;margin-top:2px;}
.ads-tbl{width:100%;border-collapse:collapse;font-size:13px;}
.ads-tbl th{text-align:left;color:var(--cyan);font-size:11px;text-transform:uppercase;letter-spacing:.5px;padding:6px 4px;border-bottom:1px solid var(--line);}
.ads-tbl td{padding:8px 4px;border-bottom:1px solid var(--line);color:var(--txt);}
.ads-tbl td:nth-child(n+2){text-align:right;white-space:nowrap;}
.spinner-mini{font-size:12px;color:var(--txt-dim);}
.backbtn{flex-shrink:0;}
.chat-reco-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0 14px 8px;padding:10px 12px;background:rgba(57,214,255,.08);border:1px solid var(--cyan);border-radius:12px;}
.chat-reco-bar .crb-txt{font-size:12.5px;color:var(--txt);flex:1;min-width:140px;}
.chat-reco-bar .crb-txt b{color:#fff;}
.chat-reco-bar .crb-done{background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:9px;padding:8px 12px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;}
.chat-reco-bar .crb-done:disabled{opacity:.6;cursor:default;}
.mem-btn{flex-shrink:0;background:var(--glass);border:1px solid var(--line);color:var(--cyan);font-size:18px;width:40px;height:40px;border-radius:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.mem-btn:hover{background:var(--cyan-soft);}
.mem-overlay{position:fixed;inset:0;z-index:60;background:rgba(3,6,12,.72);backdrop-filter:blur(6px);display:flex;align-items:flex-end;justify-content:center;padding:0;}
.mem-box{width:100%;max-width:640px;max-height:82vh;background:var(--glass-2);border:1px solid var(--line);border-radius:18px 18px 0 0;display:flex;flex-direction:column;box-shadow:0 -10px 40px rgba(0,0,0,.5);animation:fadeUp .25s ease both;}
@media(min-width:680px){.mem-overlay{align-items:center;}.mem-box{border-radius:18px;}}
.mem-head{display:flex;align-items:center;justify-content:space-between;padding:15px 16px;border-bottom:1px solid var(--line);font-size:15px;font-weight:800;color:#fff;}
.mem-x{background:none;border:none;color:var(--txt-dim);font-size:26px;line-height:1;cursor:pointer;padding:0 4px;}
.mem-search{margin:12px 16px 6px;padding:11px 13px;background:var(--card);border:1px solid var(--line);border-radius:11px;color:var(--txt);font-size:14px;font-family:inherit;}
.mem-search:focus{outline:none;border-color:var(--cyan);}
.mem-body{overflow-y:auto;padding:6px 16px 18px;}
.mem-thema{margin-top:14px;}
.mem-thema-h{font-size:12px;font-weight:800;color:var(--cyan);letter-spacing:.3px;margin-bottom:6px;display:flex;align-items:center;gap:7px;}
.mem-thema-h .cnt{font-size:10px;color:#031018;background:var(--cyan);border-radius:7px;padding:1px 7px;font-weight:700;}
.mem-item{display:flex;gap:9px;padding:8px 0;border-bottom:1px solid var(--line);font-size:13px;color:var(--txt);line-height:1.45;}
.mem-item:last-child{border-bottom:none;}
.mem-item .mz{font-size:10.5px;color:var(--txt-dim);white-space:nowrap;flex-shrink:0;margin-top:1px;}
.mem-empty{font-size:13px;color:var(--txt-dim);padding:18px 2px;text-align:center;}
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
.reco-result{margin-top:10px;padding:10px 12px;border-radius:10px;font-size:12.5px;line-height:1.5;background:rgba(231,177,75,.12);border:1px solid rgba(231,177,75,.4);color:#f0cd8a;}
.reco-result.done{background:rgba(52,224,154,.12);border-color:rgba(52,224,154,.4);color:#7ef0bd;}
.briefing{margin:12px 16px 0;background:var(--glass-2);border:1px solid var(--line);border-radius:14px;padding:14px 16px;backdrop-filter:blur(12px);}
.briefing h3{font-size:13px;color:var(--cyan);margin-bottom:8px;font-family:'SF Mono',monospace;letter-spacing:1px;}
.briefing .bl{font-size:13px;color:var(--txt);padding:5px 0;display:flex;gap:9px;line-height:1.4;}
.briefing .bl b{color:#fff;}

/* --- FREIGABEN / ENTSCHEIDUNGEN --- */
.fg-titel{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:20px 16px 4px;font-size:14px;font-weight:800;color:#fff;}
.fg-count{display:inline-flex;min-width:22px;height:22px;align-items:center;justify-content:center;padding:0 7px;background:var(--red);color:#fff;border-radius:11px;font-size:12px;font-weight:800;margin-left:4px;}
.fg-check{font-size:11.5px;font-weight:600;color:var(--cyan);border:1px solid var(--line);background:var(--glass);border-radius:9px;padding:6px 11px;cursor:pointer;}
.fg-check:active{transform:scale(.96);}
.fg-card{background:var(--glass);border:1px solid var(--line);border-radius:15px;padding:15px 16px;margin:9px 16px 0;backdrop-filter:blur(12px);box-shadow:var(--shadow);border-left:4px solid var(--cyan);animation:fadeUp .4s ease both;}
.fg-card.rot{border-left-color:var(--red);}
.fg-card.gelb{border-left-color:var(--gold);}
.fg-card.gruen{border-left-color:var(--green);}
.fg-head{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;}
.fg-cat{font-size:9.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;padding:3px 9px;border-radius:7px;background:var(--cyan);color:#031018;}
.fg-card.rot .fg-cat{background:var(--red);color:#fff;}
.fg-card.gelb .fg-cat{background:var(--gold);color:#031018;}
.fg-card.gruen .fg-cat{background:var(--green);color:#031018;}
.fg-kanal{font-size:11px;color:var(--txt-dim);font-weight:600;margin-left:auto;}
.fg-tit{font-size:14.5px;font-weight:700;color:#fff;line-height:1.35;}
.fg-why{font-size:12.5px;color:var(--txt-dim);margin-top:4px;line-height:1.5;}
.fg-reply{margin-top:10px;background:var(--glass-2);border:1px solid var(--line);border-radius:11px;padding:11px 12px;font-size:13px;color:var(--txt);line-height:1.55;white-space:pre-wrap;}
.fg-reply .fg-lbl{display:block;font-size:9.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--cyan);margin-bottom:5px;}
.fg-edit{width:100%;margin-top:10px;border:1px solid var(--cyan);border-radius:11px;padding:11px 12px;font-size:13px;font-family:inherit;color:var(--txt);background:#0a0f1a;line-height:1.55;resize:vertical;min-height:90px;outline:none;}
.fg-btns{display:flex;gap:8px;margin-top:11px;flex-wrap:wrap;}
.fg-btns .tb{flex:1;min-width:120px;text-align:center;}
.fg-done{margin-top:10px;font-size:12.5px;font-weight:700;color:var(--green);}

/* --- AGENTEN-TEAM --- */
.team{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 16px;}
.agent-card{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:16px 13px;text-align:center;cursor:pointer;backdrop-filter:blur(12px);transition:transform .15s,border-color .2s,box-shadow .2s;}
.agent-card:hover{border-color:var(--cyan);box-shadow:var(--glow);transform:translateY(-2px);}
.agent-card:active{transform:scale(.97);}
.agent-card.chef{grid-column:1 / -1;border-color:var(--cyan);box-shadow:var(--glow);display:flex;align-items:center;gap:14px;text-align:left;}
.agent-card.chef .agent-nm{font-size:17px;}
.agent-av{width:54px;height:54px;border-radius:50%;margin:0 auto 9px;display:flex;align-items:center;justify-content:center;background:linear-gradient(140deg,var(--cyan-d),#0e2c48);box-shadow:0 0 16px rgba(57,214,255,.3);overflow:hidden;flex-shrink:0;}
.agent-card.chef .agent-av{margin:0;}
.agent-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;}
.agent-av.big{width:74px;height:74px;}
.agent-emoji{font-size:25px;align-items:center;justify-content:center;width:100%;height:100%;}
.agent-av.big .agent-emoji{font-size:34px;}
.agent-nm{font-weight:700;font-size:14px;color:#fff;}
.agent-nm.big{font-size:22px;}
.agent-rl{font-size:10.5px;color:var(--txt-dim);margin-top:3px;line-height:1.35;}
.agent-rl.big{font-size:12px;margin-bottom:12px;}
.agent-hero{display:flex;align-items:center;gap:16px;background:var(--glass-2);border:1px solid var(--cyan);border-radius:20px;padding:18px;margin:8px 16px 0;backdrop-filter:blur(14px);box-shadow:var(--glow);}
.agent-talk{margin-top:4px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));color:#031018;border:none;border-radius:11px;padding:9px 14px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;}
.agent-area{display:flex;align-items:center;justify-content:space-between;background:var(--glass);border:1px solid var(--line);border-radius:13px;padding:15px 16px;margin:8px 16px;cursor:pointer;font-size:14px;font-weight:600;color:#fff;backdrop-filter:blur(10px);transition:border-color .2s;}
.agent-area:hover{border-color:var(--cyan);}
.agent-area:active{transform:scale(.98);}
.agent-area .go{color:var(--cyan);font-size:18px;}

/* --- KACHELN --- */
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:12px 16px;}
.tile{background:var(--glass);border:1px solid var(--line);border-radius:16px;padding:18px 14px;text-align:left;cursor:pointer;position:relative;backdrop-filter:blur(12px);transition:transform .15s, border-color .2s, box-shadow .2s;overflow:hidden;}
.tile:hover{border-color:var(--cyan);box-shadow:var(--glow);transform:translateY(-2px);}
.tile:active{transform:scale(.97);}
.tile:before{content:'';position:absolute;top:-40%;right:-40%;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,var(--cyan-soft),transparent 70%);}
.tile.aktiv{border-color:var(--cyan);box-shadow:var(--glow);}
.tile-ico{font-size:26px;margin-bottom:10px;filter:drop-shadow(0 0 6px rgba(57,214,255,.4));}
.tile-name{font-size:14px;font-weight:700;color:#fff;}
.tile-desc{font-size:10.5px;color:var(--txt-dim);margin-top:3px;line-height:1.4;}
.tile-tag{font-size:8px;color:#031018;background:var(--cyan);padding:3px 7px;border-radius:7px;position:absolute;top:10px;right:10px;font-weight:700;letter-spacing:.5px;}
.tile-tag.soon{background:var(--gold);}

/* --- CHAT --- */
.chat-wrap{margin:0 16px;}
.chat-head{display:flex;align-items:center;gap:11px;margin:8px 0 12px;}
.chat-head .av{width:42px;height:42px;border-radius:13px;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 0 18px rgba(57,214,255,.4);}
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
.calc-card{align-self:stretch;max-width:100%;background:var(--glass-2);border:1px solid var(--cyan);border-radius:16px;padding:16px;box-shadow:var(--glow);backdrop-filter:blur(10px);}
.calc-card .lbl{font-size:10px;letter-spacing:1.5px;color:var(--cyan);text-transform:uppercase;font-family:'SF Mono',monospace;}
.calc-card .big{font-size:34px;font-weight:800;color:#fff;margin:3px 0 2px;text-shadow:0 0 16px rgba(57,214,255,.4);}
.calc-card .meta{font-size:12px;color:var(--txt-dim);border-top:1px solid var(--line);padding-top:9px;margin-top:9px;line-height:1.6;}
.calc-card table{width:100%;border-collapse:collapse;font-size:13px;margin-top:10px;}
.calc-card td{padding:7px 0;border-bottom:1px solid var(--line);color:var(--txt);}
.calc-card td:last-child{text-align:right;font-weight:600;white-space:nowrap;color:#fff;}
.calc-card .copybtn{margin-top:12px;width:100%;padding:11px;background:var(--cyan-soft);border:1px solid var(--cyan);color:var(--cyan);border-radius:11px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;}
.composer{position:sticky;bottom:0;background:linear-gradient(0deg,var(--bg) 60%,transparent);padding:10px 16px calc(14px + env(safe-area-inset-bottom));margin-top:8px;}
.composer-in{display:flex;gap:9px;align-items:flex-end;background:var(--glass-2);border:1px solid var(--line);border-radius:18px;padding:7px 7px 7px 15px;backdrop-filter:blur(14px);}
.composer textarea{flex:1;background:transparent;border:none;color:var(--txt);font-size:16px;font-family:inherit;resize:none;outline:none;max-height:130px;line-height:1.4;padding:8px 0;}

/* --- FIXE CHAT-ANSICHT: Layout bleibt stabil (iPhone-Tastatur), nur Nachrichten scrollen --- */
body.chat-mode{overflow:hidden;}
body.chat-mode #s-chat{position:fixed;left:0;right:0;top:0;bottom:0;height:100vh;height:100dvh;
  display:flex;flex-direction:column;z-index:34;background:var(--bg);padding-top:calc(8px + env(safe-area-inset-top));}
body.chat-mode #s-chat .chat-wrap{flex:1;display:flex;flex-direction:column;min-height:0;}
body.chat-mode #s-chat .chat-log{flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;min-height:0;padding-bottom:8px;}
body.chat-mode #s-chat .composer{position:static;background:var(--bg);margin-top:0;}
@media(min-width:900px){body.chat-mode #s-chat{left:var(--sbw);}}
.send{width:42px;height:42px;border-radius:14px;border:none;background:linear-gradient(140deg,var(--cyan),var(--cyan-d));color:#031018;font-size:19px;cursor:pointer;flex-shrink:0;box-shadow:0 0 16px rgba(57,214,255,.45);display:flex;align-items:center;justify-content:center;}
.send:active{transform:scale(.92);}
.send:disabled{opacity:.4;box-shadow:none;}
.quick{display:flex;gap:8px;overflow-x:auto;padding:0 16px 4px;margin-bottom:2px;-webkit-overflow-scrolling:touch;}
.quick::-webkit-scrollbar{display:none;}
.qchip{flex-shrink:0;padding:9px 14px;border-radius:14px;border:1px solid var(--line);background:var(--glass);color:var(--cyan);font-size:12.5px;cursor:pointer;font-family:inherit;white-space:nowrap;backdrop-filter:blur(8px);}
.qchip:active{transform:scale(.95);}

/* --- BUTTONS / FORM --- */
.btn{width:100%;padding:14px;border:none;border-radius:13px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:filter .15s,transform .1s;}
.btn-cyan{background:linear-gradient(140deg,var(--cyan),var(--cyan-d));color:#031018;box-shadow:0 0 18px rgba(57,214,255,.4);}
.btn-cyan:hover{filter:brightness(1.06);}
.btn-cyan:active{transform:scale(.98);}
.btn-ghost{background:var(--glass);color:var(--cyan);border:1px solid var(--line);}
.btn-ghost:hover{border-color:var(--cyan);}
input[type=password],input[type=text]{width:100%;padding:14px;border:1px solid var(--line);border-radius:13px;font-size:16px;font-family:inherit;background:#0a0f1a;color:var(--txt);outline:none;transition:border-color .15s,box-shadow .15s;}
input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-soft);}
label{display:block;font-size:12px;font-weight:600;color:var(--txt-dim);margin:14px 0 6px;letter-spacing:.5px;}
.zurueck{color:var(--txt-dim);background:none;border:none;font-size:13px;padding:16px;cursor:pointer;font-family:inherit;width:100%;letter-spacing:.5px;}
.zurueck:hover{color:var(--cyan);}
.msg-ok{color:var(--green);font-size:13px;font-weight:600;text-align:center;margin-top:10px;min-height:18px;}
.fehler{background:rgba(255,93,108,.12);color:#ff97a1;border:1px solid rgba(255,93,108,.4);padding:12px;border-radius:11px;font-size:13px;margin:10px 0 0;}
.lern-item{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);font-size:13px;gap:10px;color:var(--txt);}
.del{color:var(--red);cursor:pointer;font-size:11px;white-space:nowrap;}

/* --- LOGIN --- */
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;position:relative;z-index:2;background:radial-gradient(circle at 50% 30%,#0a1426,#03060c 75%);}
.login-card{background:var(--glass-2);border:1px solid var(--line);border-radius:24px;padding:40px 30px;max-width:380px;width:100%;text-align:center;backdrop-filter:blur(18px);box-shadow:0 20px 60px rgba(0,0,0,.6),var(--glow);}
.login-logo{font-size:40px;font-weight:300;letter-spacing:12px;color:#fff;text-shadow:0 0 22px rgba(57,214,255,.6);}
.login-sub{font-size:9px;letter-spacing:5px;color:var(--cyan);margin:8px 0 30px;font-family:'SF Mono',monospace;}
.login-card input{text-align:center;font-size:20px;letter-spacing:6px;margin-bottom:16px;}

/* ====================== RESPONSIVE ====================== */
@media(min-width:900px){
  .sidebar{transform:none;}
  .wrap{margin-left:var(--sbw);}
  .hamburger{display:none;}
  .sb-backdrop{display:none;}
  .kpis{grid-template-columns:repeat(4,1fr);}
  .team{grid-template-columns:repeat(3,1fr);}
  .tiles{grid-template-columns:repeat(4,1fr);}
  .ads-sum{grid-template-columns:repeat(4,1fr);}
  .card,.acc,.fokus,.mert-card,.ki-alert,.umsatz,.briefing,.fg-card,.dash-head,.kpis,.section-title,.dash-bar,.fg-titel,.team,.tiles,.prio-group,.agent-funde,.agent-hero,.agent-area,.chat-wrap,.quick,.composer{max-width:1080px;}
  .dash-head,.kpis,.umsatz{margin-left:24px;}
}
@media(min-width:1500px){
  .team{grid-template-columns:repeat(4,1fr);}
}
@media(max-width:899px){
  .sidebar{transform:translateX(-100%);}
  body.sb-open .sidebar{transform:translateX(0);}
  .hamburger{display:flex;}
  .wrap{margin-left:0;}
  .kpis{grid-template-columns:repeat(2,1fr);}
}

/* ============================================================
   PREMIUM-REDESIGN-OVERRIDE — Kommandozentrale (13.06.2026)
   Weg vom generischen Sci-Fi-Cyan-Look (Neon, Grid, Scanlines)
   hin zu einer refined, premium Engineering-Identitaet:
   tiefes Navy, ruhiger Blau-Akzent, warmer Bernstein fuer Energie,
   Display-Schrift Sora + Fliesstext Manrope, Tiefe statt Neon.
   ============================================================ */
:root{
  --bg:#0b1322; --bg2:#070d18;
  --card:#121b2e; --card2:#18233a;
  --glass:rgba(18,27,46,.74); --glass-2:rgba(13,20,33,.88);
  --line:#222f49;
  --cyan:#5b91f5; --cyan-d:#3a6fd6; --cyan-soft:rgba(91,145,245,.13);
  --txt:#e9eef8; --txt-dim:#8595b3;
  --gold:#e8a24a; --green:#37d89a; --red:#ff6b78;
  --shadow:0 12px 34px rgba(0,0,0,.5);
  --glow:0 0 0 1px rgba(91,145,245,.16);
}
body{font-family:'Manrope',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
.brand .mark,.sb-brand .mk,.login-logo,.kpi .n,.umsatz-h .v,.fokus-h,.section-title,#boot .greet{font-family:'Sora','Manrope',sans-serif;}
/* Sci-Fi-Hintergrund entschaerfen */
.bg-fx .grid{display:none;}
.bg-fx .scan{display:none;}
.bg-fx .glow{background:radial-gradient(circle at center,rgba(91,145,245,.085),transparent 62%);}
.bg-fx .glow2{background:radial-gradient(circle at center,rgba(232,162,74,.06),transparent 62%);}
.bg-fx:after{content:'';}
/* Neon-Textschatten raus */
.brand .mark,.login-logo{text-shadow:none;color:#fff;font-weight:700;}
.sb-brand .mk{text-shadow:none;color:#fff;font-weight:800;letter-spacing:5px;}
#boot .greet,#boot .greet b{text-shadow:none;}
#boot .greet{font-weight:400;}
#boot .lines{text-shadow:none;color:var(--cyan);font-family:'Manrope',ui-monospace,monospace;}
#boot .ring:before{box-shadow:none;}
#boot .core{box-shadow:0 0 16px rgba(91,145,245,.42);}
/* Labels: weg vom Mono-Techno-Look */
.section-title{font-family:'Sora',sans-serif;color:var(--txt-dim);opacity:1;letter-spacing:1.4px;font-weight:700;text-transform:uppercase;font-size:11px;}
.statusbar{font-family:'Manrope',sans-serif;letter-spacing:.5px;}
.scan-btn{font-family:'Manrope',sans-serif;}
/* KPI + Ziel-Balken: premium, Fortschritt blau->bernstein (Energie Richtung Ziel) */
.kpi .n{letter-spacing:-.5px;}
.bar > i{background:linear-gradient(90deg,var(--cyan-d),var(--cyan) 55%,var(--gold));box-shadow:none;}
.umsatz-h .v b{color:var(--gold);}
.fokus{box-shadow:var(--shadow);}
.fokus-n{color:#06101f;}
/* Login refined */
.login-sub{font-family:'Sora',sans-serif;color:var(--cyan);}
.login-logo{font-weight:700;}
/* sanfte Hover-Mikrointeraktion auf Karten */
.kpi,.acc,.fokus,.umsatz{transition:transform .18s ease,border-color .18s ease;}
.kpi:hover,.fokus:hover{transform:translateY(-2px);}
/* ---------- PREMIUM LOGIN ---------- */
.login-wrap{position:relative;overflow:hidden;background:var(--bg2);}
.login-bgfx{position:absolute;inset:0;z-index:0;pointer-events:none;
  background:
    radial-gradient(50% 45% at 78% 8%,rgba(91,145,245,.18),transparent 60%),
    radial-gradient(45% 45% at 12% 96%,rgba(232,162,74,.12),transparent 60%);}
.login-bgfx::after{content:"";position:absolute;inset:0;opacity:.5;
  background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
  background-size:60px 60px;mask-image:radial-gradient(circle at 70% 12%,#000,transparent 72%);}
.login-card{position:relative;z-index:2;background:linear-gradient(165deg,#141d30,#0e1525);
  border:1px solid #233049;border-radius:26px;padding:34px 32px 26px;max-width:392px;width:100%;text-align:center;
  backdrop-filter:blur(18px);box-shadow:0 30px 80px rgba(0,0,0,.62),0 1px 0 rgba(255,255,255,.04) inset;}
.login-card::before{content:"";position:absolute;top:0;left:34px;right:34px;height:2px;border-radius:2px;
  background:linear-gradient(90deg,transparent,var(--amber),transparent);opacity:.8;}
.login-status{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:600;letter-spacing:.5px;
  color:var(--txt-dim);background:rgba(55,216,154,.08);border:1px solid rgba(55,216,154,.2);
  padding:5px 12px;border-radius:20px;margin-bottom:22px;}
.login-status .ls-dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 7px var(--green);animation:pulse 2.2s infinite;}
.login-logo{font-family:'Sora',sans-serif;font-size:44px;font-weight:800;letter-spacing:8px;color:#fff;
  text-shadow:none;display:flex;align-items:center;justify-content:center;gap:4px;}
.login-logo .ll-dot{width:11px;height:11px;border-radius:50%;background:var(--amber);box-shadow:0 0 14px rgba(232,162,74,.7);margin-right:8px;}
.login-name{font-family:'Manrope',sans-serif;font-size:10px;letter-spacing:6px;font-weight:700;color:var(--txt-dim);margin-top:6px;}
.login-sub{font-family:'Sora',sans-serif;font-size:12px;letter-spacing:1px;color:var(--cyan);margin:14px 0 26px;font-weight:600;}
.login-label{display:block;text-align:left;font-size:12px;font-weight:600;color:var(--txt-dim);margin:0 0 7px 4px;letter-spacing:.4px;}
.login-card input{text-align:center;font-size:22px;letter-spacing:8px;margin-bottom:18px;background:#0a1120;
  border:1.5px solid #243250;border-radius:14px;padding:15px;color:#fff;width:100%;transition:border-color .16s,box-shadow .16s;}
.login-card input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(91,145,245,.16);outline:none;}
.btn-login{width:100%;padding:16px;border:none;border-radius:14px;cursor:pointer;font-family:'Sora',sans-serif;
  font-size:15px;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 12px 30px rgba(58,111,214,.34);
  transition:transform .16s,box-shadow .16s;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 18px 40px rgba(58,111,214,.46);}
.btn-login:active{transform:translateY(0);}
.btn-login .login-arrow{font-style:normal;transition:transform .18s;color:#dbe7ff;}
.btn-login:hover .login-arrow{transform:translateX(4px);}
.login-foot{font-size:11px;letter-spacing:.4px;color:#566581;margin-top:22px;}
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
  <div class="login-bgfx"></div>
  <div class="login-card">
    <div class="login-status"><span class="ls-dot"></span> System bereit</div>
    <div class="login-logo"><span class="ll-dot"></span>OH</div>
    <div class="login-name">HAUSTECHNIK</div>
    <div class="login-sub">Kommandozentrale · Zugang</div>
    <form id="loginForm" method="POST">
      <label class="login-label" for="loginPw">Passwort</label>
      <input type="password" name="login_pw" id="loginPw" placeholder="••••" autofocus inputmode="text">
      <button type="submit" class="btn btn-login"><span>Büro öffnen</span> <i class="login-arrow">→</i></button>
    </form>
    <div class="fehler" id="loginErr" style="margin-top:16px;display:<?= !empty($login_fehler) ? 'block' : 'none' ?>">Passwort falsch – Zugang verweigert.</div>
    <div class="login-foot">Reserviert für den grossen Adnan</div>
  </div>
</div>

<!-- BOOT / WILLKOMMEN -->
<div id="boot" style="display:none">
  <div class="ring"><div class="core"></div></div>
  <div class="lines" id="bootLines"></div>
  <div class="greet" id="greet"></div>
</div>

<div class="wrap" style="visibility:hidden" id="app">

<!-- SIDEBAR (Buero-neu Navigation) -->
<div class="sb-backdrop" id="sbBackdrop" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sb-brand"><div class="mk">OH</div><div class="sub">BÜRO · ONLINE</div></div>
  <nav class="sb-nav">
    <div class="sb-group">
      <div class="sb-glabel">Übersicht</div>
      <button class="sb-item active" data-nav="dashboard" onclick="nav('dashboard')"><span class="ic">📊</span>Dashboard</button>
    </div>
    <div class="sb-group">
      <div class="sb-glabel">Business</div>
      <button class="sb-item" data-nav="ads" onclick="nav('ads')"><span class="ic">📈</span>Google Ads</button>
      <button class="sb-item" data-nav="lex" onclick="nav('lex')"><span class="ic">💰</span>Finanzen</button>
      <button class="sb-item" data-nav="team" onclick="nav('team')"><span class="ic">👥</span>Team</button>
      <button class="sb-item" data-nav="activity" onclick="nav('activity')"><span class="ic">📋</span>Aktivität</button>
    </div>
    <div class="sb-group">
      <div class="sb-glabel">Kunden</div>
      <button class="sb-item" data-nav="leads" onclick="nav('leads')"><span class="ic">📥</span>Anfragen</button>
      <button class="sb-item" data-nav="web" onclick="nav('web')"><span class="ic">🌐</span>Website</button>
      <button class="sb-item" data-nav="kalk" onclick="nav('kalk')"><span class="ic">🧮</span>Kalkulator</button>
    </div>
  </nav>
  <div class="sb-foot">
    <button class="sb-item" data-nav="settings" onclick="nav('settings')"><span class="ic">⚙️</span>Einstellungen</button>
    <a class="sb-item logout" href="?logout=1"><span class="ic">⎋</span>Abmelden</a>
  </div>
</aside>

<header>
  <button class="icobtn backbtn" id="backBtn" onclick="goBack()" title="Zurück" style="display:none">&#8592;</button>
  <button class="hamburger" onclick="toggleSidebar()" title="Menü">&#9776;</button>
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

  <div class="dash-bar"><span class="scan-btn" onclick="kaanAnalyse(this)">🧠 Kaan: Postfach analysieren</span><span class="scan-btn" style="margin-left:8px" onclick="scanNow(this)">↻ Aktualisieren</span></div>

  <!-- Tagesfokus (aufklappbar) -->
  <div class="fokus" id="fokus" style="display:none">
    <div class="fokus-h" style="cursor:pointer;margin-bottom:4px" onclick="toggleBox('fokusList','fokusArrow')"><span class="acc-c" id="fokusArrow" style="transform:rotate(90deg)">▶</span> 🎯 Zu erledigen · Deine wichtigsten Aufgaben heute</div>
    <div id="fokusList"></div>
  </div>

  <!-- OFFENE AUFGABEN: Freigaben/Entscheidungen + Aufgaben-Gruppen -->
  <div id="aufgabenAnker"></div>
  <div id="freigabenWrap" style="display:none">
    <div class="fg-titel">
      <span style="cursor:pointer" onclick="toggleBox('freigabenBox','fgArrow')"><span class="acc-c" id="fgArrow" style="transform:rotate(90deg)">▶</span> 📋 Offene Aufgaben · Freigaben &amp; Entscheidungen <span class="fg-count" id="fgCount">0</span></span>
      <span class="fg-check" id="fgCheckBtn" onclick="triageNow(this)">🔄 Nachrichten prüfen</span>
    </div>
    <div id="freigabenBox"></div>
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
  <div id="agentInbox"></div>
  <div id="agentBau"></div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
</div>

<!-- GOOGLE ADS -->
<div id="s-ads" style="display:none">
  <div class="card prog-card">
    <h2>&#128302; Prognose · nächste Woche</h2>
    <p class="intro">Ehrlich aus Deinen echten Ads-Zahlen + den Vorschlägen, die Du übernimmst. Keine geschönten Zahlen – so siehst Du, was wirklich kommt und welcher Vorschlag am meisten bringt.</p>
    <div id="progBody"><div class="prio-empty">Lade Prognose …</div></div>
  </div>
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
  <div class="card" style="border-color:var(--gold)">
    <h2>&#128203; Google-Ads-Maßnahmenplan</h2>
    <p class="intro">Der Weg zum Maximum – Schritt für Schritt im Google-Ads-Konto abhaken. Tracking ist bereits erledigt; der Rest hebt Qualität, Großaufträge und Gewinn.</p>
    <button class="btn btn-cyan" onclick="openAdsPlan()">📋 Maßnahmenplan öffnen</button>
  </div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
</div>

<!-- GOOGLE-ADS-MASSNAHMENPLAN -->
<div id="s-adsplan" style="display:none">
  <div class="card">
    <h2>&#128203; Google-Ads-Maßnahmenplan</h2>
    <p class="intro">Elite-Checkliste auf dem Weg zum Maximum. Was Du im Konto erledigt hast, hier abhaken – der Fortschritt wird gespeichert.</p>
    <div class="prog-grid" style="grid-template-columns:1fr;margin-bottom:6px">
      <div class="umsatz" style="margin:0"><div class="umsatz-h"><span class="t">Fortschritt</span><span class="v"><b id="apDone">0</b> / <span id="apTotal">0</span></span></div><div class="bar"><i id="apBar" style="width:0%"></i></div></div>
    </div>
    <div id="adsplanBody"><div class="prio-empty">Lade …</div></div>
  </div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
</div>

<!-- DILARA · WEBSITE -->
<div id="s-web" style="display:none">
  <div class="card">
    <h2>&#127760; Dilara · Website-Optimierung</h2>
    <p class="intro">Dilara liest Deine echte Website und schlägt konkrete Verbesserungen für mehr Anfragen vor.</p>
    <div id="webBody"><div class="prio-empty">Lade …</div></div>
    <button class="btn btn-cyan" style="margin-top:12px" id="webBtn" onclick="webAnalyze()">🔍 Website jetzt analysieren</button>
  </div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
</div>

<!-- ARCHIV: Erledigtes nach Datum, dauerhaft -->
<div id="s-archiv" style="display:none">
  <div class="card">
    <h2>🗂️ Erledigt · Archiv</h2>
    <p class="intro">Alles, was Dein Team erledigt und besprochen hat – nach Tagen geordnet, <b>dauerhaft gespeichert</b>. Tag antippen für alle Details (wer, was, mit wem, was gesagt wurde). Übernommene Änderungen kannst Du hier <b>rückgängig machen</b>.</p>
    <div id="changesBody"></div>
    <div id="archivBody"><div class="prio-empty">Lade …</div></div>
  </div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
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
    <h2>&#129302; Autopilot</h2>
    <p class="intro">Kleine, ungefährliche Aufgaben erledigen die KI-Mitarbeiter <b>selbst</b> (mit Tageslimit &amp; Protokoll). Alles Wichtige landet weiterhin als Freigabe bei Dir.</p>
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer"><input type="checkbox" id="apKaan" style="width:auto"> 💬 Kaan: Standard-Antworten automatisch senden (max. 10/Tag)</label>
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer"><input type="checkbox" id="apAylin" style="width:auto"> 💰 Aylin: freundliche Zahlungserinnerungen automatisch (max. 5/Tag)</label>
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer"><input type="checkbox" id="apDilara" style="width:auto"> 🚀 Dilara: rote Geld-Verbrenner-Keywords automatisch ausschließen (max. 3/Tag)</label>
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveAutopilot()">Speichern</button>
    <div id="apMsg" class="msg-ok"></div>
  </div>
  <div class="card">
    <h2>&#128188; Lexware (Buchhaltung)</h2>
    <p class="intro">Für Aylin: echte Rechnungen, offene Posten &amp; Umsatz. Den API-Schlüssel bekommst Du im <b>Lexware Office Portal</b> (app.lexware.de → Erweiterungen → Public API).</p>
    <label>API-Schlüssel</label>
    <input type="password" id="lexKey" placeholder="••• (leer = unverändert)">
    <button class="btn btn-cyan" style="margin-top:12px" onclick="saveLex()">Speichern &amp; testen</button>
    <div id="lexMsg" class="msg-ok"></div>
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
    <h2>&#128218; Gelernte Korrekturen (Kalkulator)</h2>
    <p class="intro">Das System lernt aus Deinen Korrekturen für genauere Preise.</p>
    <div id="lernListe"></div>
  </div>
  <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
</div>

<!-- CHAT (universal) -->
<div id="s-chat" style="display:none">
  <div class="chat-wrap">
    <div class="chat-head">
      <div class="av" id="chatIco">&#129518;</div>
      <div style="flex:1"><div class="nm" id="chatName">Kalkulator</div><div class="st">&#9679; ONLINE · bereit</div></div>
      <button class="mem-btn" id="memBtn" onclick="openMem()" title="Wissensarchiv durchsuchen" style="display:none">&#129504;</button>
    </div>
    <div id="chatRecoBar" class="chat-reco-bar" style="display:none"></div>
    <div class="chat-log" id="chatLog"></div>
  </div>
  <div id="memOverlay" class="mem-overlay" style="display:none">
    <div class="mem-box">
      <div class="mem-head">
        <span id="memTitle">🧠 Wissensarchiv</span>
        <button class="mem-x" onclick="closeMem()" title="Schließen">&times;</button>
      </div>
      <input type="text" id="memSearch" class="mem-search" placeholder="Im Gedächtnis suchen… (z.B. Preis, Kunde, Ads)" oninput="memSearchDeb()">
      <div id="memBody" class="mem-body"></div>
    </div>
  </div>
  <div class="quick" id="quickRow"></div>
  <div class="composer">
    <div class="composer-in">
      <textarea id="chatIn" rows="1" placeholder="Schreib einfach drauf los…"></textarea>
      <button class="send" id="sendBtn" onclick="send()">&#10148;</button>
    </div>
    <button class="zurueck" onclick="goBack()">&larr; Zurück</button>
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
function renderKpis(d){
  const stats=d.stats||{}, offen=d.offen||[], erled=d.erledigt||[];
  const rot=offen.filter(t=>t.prio==='rot').length;
  const won=(d.leads||[]).filter(l=>['gewonnen','abgeschlossen'].includes(l.status)).length;
  const lex=d.lexware||{};
  const z=d.ziel||null;
  const umsatz=z?Math.round(z.ist):((lex.bezahlt_jahr_summe>0)?Math.round(lex.bezahlt_jahr_summe):won*2000);
  const goal=z?z.betrag:1000000, pct=Math.max(2,Math.min(100,Math.round(umsatz/goal*100)));
  const fmtE=n=>(Math.round(n)).toLocaleString('de-DE')+' €';
  const zielZeile=z?`<div class="s" style="margin-top:8px">${z.im_plan?'✅ <b>Im Plan!</b>':'⚠️ <b>Rückstand: '+fmtE(z.soll-z.ist)+'</b>'} · Soll bis heute: ${fmtE(z.soll)} · noch ${z.rest_tage} Tage · benötigt: <b>${fmtE(z.pro_woche)}/Woche</b>${z.auftraege_woche?' ≈ '+z.auftraege_woche+' Aufträge'+(z.anfragen_woche?' ('+z.anfragen_woche+' Anfragen)':''):''}</div>`:'';
  const hi=gl('dashHi'); if(hi) hi.innerHTML='Guten Tag, <b>Chef</b>.<div style="font-size:13px;color:var(--txt-dim);font-weight:400;margin-top:5px">Dein Unternehmen auf einen Blick · Ziel: 1 Mio € in 5 Monaten</div>';
  const kpi=(ic,n,l,s,cls,click)=>`<div class="kpi ${cls||''}"${click?` onclick="${click}" style="cursor:pointer"`:''}><div class="top"><span class="ic">${ic}</span><span class="badge"></span></div><div class="n">${n}</div><div class="l">${l}</div>${s?`<div class="s">${s}</div>`:''}</div>`;
  const c=gl('dashStats'); if(!c)return; c.className='kpis';
  c.innerHTML=
    `<div class="umsatz" style="grid-column:1/-1;margin:0 0 2px"><div class="umsatz-h"><span class="t">📈 Umsatz Richtung Ziel${z?' (seit '+z.start+')':''}</span><span class="v"><b>${umsatz.toLocaleString('de-DE')} €</b> / 1 Mio €</span></div><div class="bar"><i style="width:${pct}%"></i></div>${zielZeile}</div>`+
    kpi('📋',offen.length,'Offene Aufgaben',rot?rot+' kritisch':'alles im Griff',rot?'bad':'ok',"kpiAufgaben()")+
    kpi('🔥',stats.hot||0,'Heiße Anfragen',(stats.hot||0)>0?'jetzt reagieren':'',(stats.hot||0)>0?'warn':'',"openChat('leads')")+
    kpi('📥',stats.leads||0,'Leads gesamt','','',"openChat('leads')")+
    kpi('✅',erled.length,'Erledigt','','ok',"openArchiv()");
}
/* Aufklappbare Bereiche: Zustand bleibt gespeichert, Pfeil zeigt ihn an */
function toggleBox(id,arrowId){
  const b=gl(id),a=gl(arrowId);if(!b)return;
  const zu=b.style.display!=='none';
  b.style.display=zu?'none':'';
  if(a)a.style.transform=zu?'':'rotate(90deg)';
  try{localStorage.setItem('oh_zu_'+id,zu?'1':'');}catch(e){}
}
function applyBoxState(id,arrowId){
  let zu='';try{zu=localStorage.getItem('oh_zu_'+id)||'';}catch(e){}
  const b=gl(id),a=gl(arrowId);if(!b)return;
  b.style.display=zu?'none':'';
  if(a)a.style.transform=zu?'':'rotate(90deg)';
}
function kpiAufgaben(){
  const el=gl('freigabenWrap')&&gl('freigabenWrap').style.display!=='none'?gl('freigabenWrap'):gl('dashAcc');
  if(el)el.scrollIntoView({behavior:'smooth',block:'start'});
}

/* ============ ARCHIV: Erledigtes nach Datum, dauerhaft ============ */
let ARCH_HEUTE='',ARCH_GESTERN='';
function archLabel(tag){
  if(tag===ARCH_HEUTE)return'Heute';
  if(tag===ARCH_GESTERN)return'Gestern';
  const p=tag.split('-');return p[2]+'.'+p[1]+'.'+p[0];
}
async function openArchiv(){
  showSection('archiv');
  loadChanges();
  gl('archivBody').innerHTML='<div class="prio-empty">Lade …</div>';
  try{
    const r=await api('archiv');
    if(!(r&&r.ok)){gl('archivBody').innerHTML='<div class="prio-empty">Noch nichts archiviert.</div>';return;}
    ARCH_HEUTE=r.heute||'';ARCH_GESTERN=r.gestern||'';
    const tage=r.tage||[];
    if(!tage.length){gl('archivBody').innerHTML='<div class="prio-empty">Noch nichts archiviert – ab jetzt wird hier alles dauerhaft gesammelt.</div>';return;}
    gl('archivBody').innerHTML=tage.map(t=>`<div class="agent-area" style="margin:8px 0" onclick="archivTag('${t.tag}')"><span>📅 ${archLabel(t.tag)} <span class="acc-cnt">${t.anzahl}</span></span><span class="go">›</span></div>`).join('');
  }catch(e){gl('archivBody').innerHTML='<div class="prio-empty">Fehler beim Laden.</div>';}
}
/* Übernommene Änderungen: alt -> neu, mit Rückgängig-Button */
async function loadChanges(){
  const el=gl('changesBody'); if(!el)return;
  el.innerHTML='';
  try{
    const r=await api('changes');
    const list=(r&&r.changes)||[];
    if(!list.length)return;
    el.innerHTML='<div class="prio-lbl" style="margin:4px 0 8px">Übernommene Änderungen · antippen zum Zurücknehmen</div>'+
      list.map(c=>{
        const wann=new Date((c.ts||0)*1000).toLocaleString('de-DE',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});
        const av=typeof c.alt==='string'?c.alt:JSON.stringify(c.alt);
        const nv=typeof c.neu==='string'?c.neu:(c.neu&&c.neu.wert?('"'+c.neu.wert+'" ausgeschlossen'):JSON.stringify(c.neu));
        let aktion='';
        if(c.status==='rueckgaengig')aktion='<span class="pill gruen">↩ rückgängig gemacht</span>';
        else if(!c.undoable)aktion='<span class="pill gelb">nicht rückholbar (bereits versendet)</span>';
        else aktion=`<button class="tb no" onclick="changeUndo('${c.id}',this)">↩ Rückgängig machen</button>`;
        return `<div class="ar-msg" style="margin:7px 0;border-left-color:${c.status==='rueckgaengig'?'var(--txt-dim)':'var(--cyan)'}">`+
          `<span class="ar-from">${wann} Uhr</span>${esc(c.titel||'')}`+
          `<br><span style="color:var(--txt-dim);font-size:12px">${esc(av||'–')} → ${esc(nv||'–')}</span>`+
          `<div style="margin-top:7px">${aktion}</div></div>`;
      }).join('');
  }catch(e){}
}
async function changeUndo(id,btn){
  btn.disabled=true;btn.textContent='… wird zurückgenommen';
  try{
    const r=await api('change_undo',{id});
    if(r&&r.ok){
      btn.outerHTML='<span class="pill gruen">↩ rückgängig gemacht</span>';
      if(typeof speak==='function')speak('Erledigt Chef, die Änderung ist zurückgenommen.');
      loadDashboard();
    }else{
      btn.textContent='⚠️ '+((r&&r.error)||'Fehler');
      setTimeout(()=>{btn.disabled=false;btn.textContent='↩ Rückgängig machen';},3500);
    }
  }catch(e){btn.disabled=false;btn.textContent='↩ Rückgängig machen';}
}
async function archivTag(tag){
  gl('archivBody').innerHTML='<div class="prio-empty">Lade …</div>';
  try{
    const r=await api('archiv',{tag});
    ARCH_HEUTE=(r&&r.heute)||ARCH_HEUTE;ARCH_GESTERN=(r&&r.gestern)||ARCH_GESTERN;
    const list=(r&&r.eintraege)||[];
    gl('archivBody').innerHTML=
      `<div class="agent-area" style="margin:0 0 10px" onclick="openArchiv()"><span>← Alle Tage</span></div>`+
      `<div class="prio-lbl" style="margin:6px 0">${archLabel(tag)} · ${list.length} Einträge</div>`+
      (list.length
        ?list.slice().reverse().map(e=>{
          const nm=AGENTS[e.agent]?AGENTS[e.agent].name:(e.agent==='chef'?'Chef':e.agent);
          const ico=(e.text||'').indexOf('✉')===0?'✉️':'✅';
          const uhr=new Date((e.ts||0)*1000).toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'});
          return `<div class="akt-row"><span class="akt-ico">${ico}</span><div><div class="akt-t"><b>${esc(nm)}</b> ${esc(e.text||'')}</div><div class="akt-z">${uhr} Uhr</div></div></div>`;
        }).join('')
        :'<div class="prio-empty">Keine Einträge an diesem Tag.</div>');
  }catch(e){gl('archivBody').innerHTML='<div class="prio-empty">Fehler.</div>';}
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
  renderKpis(d); renderKiAlert(d.ki_alert||{alert:false}); renderWarn(d.warnung);
  const offen=lastOffen; offen.forEach((t,i)=>t._i=i);
  // Tagesfokus = Top 3 (serverseitig nach Priorität sortiert)
  renderFokus(offen.slice(0,3));
  renderFreigaben(d.freigaben||[]);
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
  applyBoxState('fokusList','fokusArrow');
}

/* ============ FREIGABEN / ENTSCHEIDUNGEN (Baustein A) ============ */
let fgData={};
function renderFreigaben(list){
  fgData={};
  const wrap=gl('freigabenWrap'),box=gl('freigabenBox');
  if(!wrap||!box)return;
  if(!list||!list.length){wrap.style.display='none';box.innerHTML='';return;}
  wrap.style.display='block';
  const c=gl('fgCount');if(c)c.textContent=list.length;
  applyBoxState('freigabenBox','fgArrow');
  box.innerHTML=list.map(f=>{
    fgData[f.id]=f;
    const kanal=f.kanal==='whatsapp'?'📱 WhatsApp':(f.kanal==='email'?'📧 E-Mail':'⚙️ System');
    const von=f.from?(' · '+esc(f.from)):'';
    const hasReply=(f.typ==='antwort'&&f.vorschlag);
    const reply=hasReply
      ?`<div class="fg-reply" id="fgview_${f.id}"><span class="fg-lbl">Antwortvorschlag</span>${esc(f.vorschlag)}</div>
        <textarea class="fg-edit" id="fgedit_${f.id}" style="display:none">${esc(f.vorschlag)}</textarea>`:'';
    const editBtn=hasReply?`<button class="tb" onclick="fgEdit('${f.id}')">✏️ Antwort bearbeiten</button>`:'';
    return `<div class="fg-card ${f.prio||'gelb'}" id="fgcard_${f.id}">
      <div class="fg-head"><span class="fg-cat">${esc(f.kategorie||'Info')}</span><span class="fg-kanal">${kanal}${von}</span></div>
      <div class="fg-tit">${esc(f.titel||'')}</div>
      ${f.warum?`<div class="fg-why">${esc(f.warum)}</div>`:''}
      ${reply}
      <div class="fg-btns" id="fgbtns_${f.id}">
        <button class="tb ok" onclick="fgDecide('${f.id}','uebernehmen')">✅ Übernehmen</button>
        ${editBtn}
        <button class="tb no" onclick="fgDecide('${f.id}','ablehnen')">✕ Nicht übernehmen</button>
      </div>
    </div>`;
  }).join('');
}
function fgEdit(id){const v=gl('fgview_'+id),t=gl('fgedit_'+id);if(!t)return;
  if(t.style.display==='none'){if(v)v.style.display='none';t.style.display='block';t.focus();}
  else{t.style.display='none';if(v)v.style.display='block';}}
async function fgDecide(id,decision){
  const t=gl('fgedit_'+id);
  const text=(t&&t.style.display!=='none')?t.value:(fgData[id]?(fgData[id].vorschlag||''):'');
  const btns=gl('fgbtns_'+id);if(btns)btns.innerHTML='<span class="fg-done">… wird verarbeitet</span>';
  try{
    const r=await api('freigabe_decide',{id,decision,text});
    if(r&&r.ok){
      let label=decision==='ablehnen'?'✕ Nicht übernommen':(r.sent?'✓ Antwort gesendet':(r.note?'✓ Übernommen – '+r.note:'✓ Übernommen – Antwort kopiert'));
      if(btns)btns.innerHTML='<span class="fg-done">'+label+'</span>';
      if(decision==='uebernehmen'&&!r.sent&&text){try{await navigator.clipboard.writeText(text);}catch(e){}}
      setTimeout(loadFreigaben,1300);
    }else{if(btns)btns.innerHTML='<span class="fg-done" style="color:var(--red)">Fehler – bitte erneut</span>';}
  }catch(e){if(btns)btns.innerHTML='<span class="fg-done" style="color:var(--red)">Fehler – bitte erneut</span>';}
}
async function loadFreigaben(){
  try{const r=await api('freigaben');if(r&&r.ok){if(lastDash)lastDash.freigaben=r.freigaben;renderFreigaben(r.freigaben||[]);}}catch(e){}
}
async function triageNow(btn){
  const o=btn?btn.textContent:'';if(btn)btn.textContent='… prüfe';
  try{const r=await api('triage_now');if(r){if(lastDash)lastDash.freigaben=r.freigaben;renderFreigaben(r.freigaben||[]);
    if(typeof speak==='function'&&r.neu)speak(r.neu+' neue Nachricht'+(r.neu>1?'en':'')+' geprüft, Chef.');}}catch(e){}
  if(btn)btn.textContent=o||'🔄 Nachrichten prüfen';
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
    areas:[['Offene Rechnungen',()=>openChat('aylin','Welche Rechnungen sind offen?')],['Mahnungen',()=>openChat('aylin','Bereite eine freundliche Mahnung vor.')],['Auswertung',()=>openChat('aylin','Mach mir eine Gewinn- und Kosten-Übersicht.')],['Lexware-Übergabe',()=>openChat('aylin','Was soll an Lexware übergeben werden?')]]},
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
  // Postfach + offene Aufträge: echte Kommunikation (Baustein C / Stufe 3)
  gl('agentInbox').innerHTML='';
  api('agent_inbox',{agent:key}).then(r=>{
    let h='';
    if(r&&r.tasks&&r.tasks.length){
      h+='<div class="section-title">// Offene Aufträge</div>'+
        r.tasks.map(t=>`<div class="agent-area" style="cursor:default"><span>📌 von ${esc(AGENTS[t.von]?AGENTS[t.von].name:t.von)}: ${esc(t.text)}</span><button class="tb ok" onclick="taskDone('${t.id}',this)">✓ Erledigt</button></div>`).join('');
    }
    if(r&&r.list&&r.list.length){
      h+='<div class="section-title">// Postfach · Nachrichten von Kollegen</div>'+
        r.list.slice(-6).reverse().map(m=>`<div class="ar-msg" style="margin:8px 16px${m.gelesen?'':';border-left-color:var(--gold)'}"><span class="ar-from">von ${esc(AGENTS[m.von]?AGENTS[m.von].name:m.von)} · ${zeitHer(m.ts)}${m.gelesen?'':' · NEU'}</span>${esc(m.text)}</div>`).join('');
    }
    gl('agentInbox').innerHTML=h;
  }).catch(()=>{});
  // Yusuf: alle Baustellen mit "Abgeschlossen"-Klick
  gl('agentBau').innerHTML='';
  if(key==='yusuf'){
    const gew=(leadsCache||[]).filter(l=>l.status==='gewonnen');
    gl('agentBau').innerHTML='<div class="section-title">// Laufende Baustellen</div>'+(gew.length
      ? gew.map(l=>`<div class="agent-area" style="cursor:default"><span>🏗️ ${esc(l.name||l.email||l.id)}${l.kategorie?' · '+esc(l.kategorie):''}${l.ort?' · '+esc(l.ort):''}</span><button class="tb ok" onclick="baustelleDone('${l.id}',this)">✅ Abgeschlossen</button></div>`).join('')
      : '<div class="prio-empty" style="margin:8px 16px">Keine laufenden Baustellen (gewonnene Aufträge erscheinen hier).</div>');
  }
  showSection('agent');
}
function agentArea(key,i){AGENTS[key].areas[i][1]();}
async function taskDone(id,btn){
  btn.disabled=true;btn.textContent='…';
  try{const r=await api('task_done',{id});
    if(r&&r.ok){btn.textContent='✓';setTimeout(()=>{if(curAgent)openAgent(curAgent);},900);}
    else{btn.textContent='Fehler';btn.disabled=false;}
  }catch(e){btn.textContent='Fehler';btn.disabled=false;}
}
async function baustelleDone(id,btn){
  btn.disabled=true;btn.textContent='…';
  try{
    const r=await api('baustelle_done',{id});
    if(r&&r.ok){btn.textContent='✓ abgeschlossen';if(typeof speak==='function')speak('Erledigt Chef, Baustelle ist abgeschlossen.');setTimeout(()=>{loadDashboard();openAgent('yusuf');},1400);}
    else{btn.textContent='Fehler';btn.disabled=false;}
  }catch(e){btn.textContent='Fehler';btn.disabled=false;}
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
let curSection='home', navHist=[];
let chatReco=null; // aktuell im Chat besprochene Empfehlung (id + kind 'ads'/'web')
function showSection(s,track){
  if(track!==false&&curSection&&curSection!==s){navHist.push(curSection);if(navHist.length>12)navHist.shift();}
  ['home','settings','chat','ads','adsplan','agent','web','archiv'].forEach(id=>{const el=gl('s-'+id);if(el)el.style.display='none';});
  gl('s-'+s).style.display=(s==='chat')?'flex':'block';
  document.body.classList.toggle('chat-mode',s==='chat');
  curSection=s;
  if(s!=='chat'){const mo=gl('memOverlay'); if(mo)mo.style.display='none';}
  const bb=gl('backBtn'); if(bb)bb.style.display=(s==='home')?'none':'flex';
  if(s!=='chat')window.scrollTo({top:0,behavior:'smooth'});
  refreshSection(s);
}
/* Beim Betreten/Zurueckkehren einer Ansicht IMMER frische Server-Daten laden,
   damit erledigte Empfehlungen nicht aus altem Cache als offen erscheinen. */
function refreshSection(s){
  if(s==='ads'){if(typeof loadProg==='function')loadProg();if(typeof loadReco==='function')loadReco();if(typeof loadAds==='function')loadAds();}
  else if(s==='adsplan'){if(typeof loadAdsPlan==='function')loadAdsPlan();}
  else if(s==='web'){if(typeof loadWeb==='function')loadWeb();}
  else if(s==='home'){if(typeof loadDashboard==='function')loadDashboard();}
}
/* Ein Schritt zurück (Tab/Kontext bleibt erhalten) – statt immer zum Dashboard */
function goBack(){
  const p=navHist.pop();
  if(!p||p===curSection){goHome();return;}
  showSection(p,false);
}
function goHome(){navHist=[];showSection('home',false);}

/* ===== SIDEBAR-NAVIGATION (Buero-neu) – verdrahtet bestehende Funktionen ===== */
function setActiveNav(id){document.querySelectorAll('.sb-item[data-nav]').forEach(b=>b.classList.toggle('active',b.getAttribute('data-nav')===id));}
function openSidebar(){document.body.classList.add('sb-open');}
function closeSidebar(){document.body.classList.remove('sb-open');}
function toggleSidebar(){document.body.classList.toggle('sb-open');}
function openSettings(){if(gl('s-settings').style.display!=='block'){toggleSettings();}}
function nav(id){
  closeSidebar(); setActiveNav(id);
  switch(id){
    case 'dashboard': goHome(); break;
    case 'ads': openAds(); break;
    case 'web': openWeb(); break;
    case 'leads': openChat('leads'); break;
    case 'kalk': openChat('emre'); break;
    case 'lex': openAgent('aylin'); break;
    case 'team': goHome(); setTimeout(()=>{const t=gl('teamGrid'); if(t)t.scrollIntoView({behavior:'smooth',block:'start'});},150); break;
    case 'activity': openArchiv(); break;
    case 'settings': openSettings(); break;
    default: goHome();
  }
}
async function toggleSettings(){
  if(gl('s-settings').style.display==='block'){goHome();}
  else{
    gl('apiIn').value='';gl('gmailPass').value='';
    ['adsDev','adsCid','adsSecret','adsRefresh','waToken'].forEach(id=>gl(id).value='');
    try{const c=await api('config_get');serverCfg=c;
      gl('gmailUser').value=c.gmail_user||'';
      gl('adsCustomer').value=c.ads_customer_id||'';
      gl('adsLogin').value=c.ads_login_customer_id||'';
      gl('waPhone').value=c.wa_phone_id||'';
      gl('waVerify').value=c.wa_verify_token||'oh-wa';
      gl('siteUrl').value=c.site_url||'';
      gl('apKaan').checked=(c.autopilot_kaan||'an')==='an';
      gl('apAylin').checked=(c.autopilot_aylin||'an')==='an';
      gl('apDilara').checked=(c.autopilot_dilara||'an')==='an';
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
async function saveAutopilot(){
  await api('config_set',{
    autopilot_kaan:gl('apKaan').checked?'an':'aus',
    autopilot_aylin:gl('apAylin').checked?'an':'aus',
    autopilot_dilara:gl('apDilara').checked?'an':'aus'
  });
  gl('apMsg').textContent='✓ Autopilot gespeichert';
  setTimeout(()=>gl('apMsg').textContent='',2500);
}
async function saveLex(){
  const k=gl('lexKey').value.trim();
  if(k)await api('config_set',{lexware_api_key:k});
  gl('lexKey').value='';
  gl('lexMsg').textContent='… teste Verbindung';
  try{
    const r=await api('lex_refresh');
    if(r&&r.ok){const lx=r.lexware||{};gl('lexMsg').textContent='✓ Verbunden: '+(lx.offen_anzahl||0)+' offene Rechnungen, Umsatz '+(lx.bezahlt_jahr_summe||0)+' €';loadDashboard();}
    else{gl('lexMsg').textContent='⚠️ '+(r&&r.error?r.error:'Verbindung fehlgeschlagen');}
  }catch(e){gl('lexMsg').textContent='⚠️ Fehler beim Testen';}
  setTimeout(()=>gl('lexMsg').textContent='',8000);
}
async function kaanAnalyse(btn){
  const o=btn?btn.textContent:'';if(btn)btn.textContent='… Kaan liest das Postfach';
  try{
    const r=await api('kaan_analyse');
    if(r&&r.ok){if(btn)btn.textContent='✓ '+(r.mails||0)+' Mails analysiert, '+(r.offen||0)+' offene Anliegen';if(typeof speak==='function')speak('Chef, ich habe das komplette Postfach analysiert.');setTimeout(()=>{if(btn)btn.textContent=o;loadDashboard();},3500);}
    else{if(btn)btn.textContent='⚠️ '+(r&&r.error?r.error:'Fehler');setTimeout(()=>{if(btn)btn.textContent=o;},4000);}
  }catch(e){if(btn)btn.textContent=o;}
}
function renderLL(){
  const l=getLern(),el=gl('lernListe');
  el.innerHTML=l.length?l.map((t,i)=>`<div class="lern-item"><span>• ${esc(t)}</span><span class="del" onclick="delL(${i})">löschen</span></div>`).join(''):'<p style="font-size:13px;color:var(--txt-dim)">Noch keine.</p>';
}
function delL(i){const l=getLern();l.splice(i,1);setLernS(l);renderLL();}

/* ============ CHAT ÖFFNEN ============ */
function openChat(m,prefill,reco){
  mode=m; const cfg=MODI[m];
  chatReco=(reco&&reco.id)?reco:null; renderChatReco();
  const _mb=gl('memBtn'); if(_mb)_mb.style.display=(typeof AGENTS!=='undefined'&&AGENTS[m])?'flex':'none';
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
  // Gespraechsgedaechtnis: gespeicherten Verlauf vom Server laden + anzeigen (nur echte Agenten)
  if(typeof AGENTS!=='undefined' && AGENTS[m]){
    api('chat_load',{agent:m}).then(d=>{
      if(d && Array.isArray(d.messages) && d.messages.length){
        history[m]=d.messages.map(x=>({role:x.role, content:x.content, _render:(x.role==='assistant'?x.content:undefined)}));
        renderLog();
      }
    }).catch(()=>{});
  }
  showSection('chat');
  if(prefill){gl('chatIn').value=prefill;autoGrow();}
  setTimeout(()=>gl('chatIn').focus(),300);
}
function quick(b){gl('chatIn').value=b.textContent;gl('chatIn').focus();autoGrow();}

/* ============ GOOGLE ADS ============ */
let lastAdsReport=null;
function openAds(){showSection('ads');}

/* --- Ehrliche Prognose nächste Woche --- */
async function loadProg(){
  const el=gl('progBody'); if(!el)return;
  el.innerHTML='<div class="prio-empty">Lade Prognose …</div>';
  let d={};try{d=await api('prognose');}catch(e){}
  if(!d||!d.ok){el.innerHTML='<div class="prio-empty">Prognose erst möglich, wenn die Ads-Zahlen geladen sind (⚙️ Zugangsdaten prüfen).</div>';return;}
  renderProg(d.prognose||{});
}
function renderProg(p){
  const el=gl('progBody'); if(!el)return;
  const nf=n=>(Math.round((+n||0)*10)/10).toLocaleString('de-DE');
  const eu=n=>(Math.round(+n||0)).toLocaleString('de-DE')+' €';
  let html=`<div class="prog-grid">
    <div class="prog-stat"><div class="n">${nf(p.anfragen_prognose)}</div><div class="l">Anfragen/Woche<br>(realistisch)</div></div>
    <div class="prog-stat"><div class="n">${nf(p.auftraege_prognose)}</div><div class="l">→ Aufträge<br>(Quote ${p.quote||0}%)</div></div>
    <div class="prog-stat hi"><div class="n">${eu(p.umsatz_prognose)}</div><div class="l">erwarteter Umsatz<br>(Ø ${eu(p.avg_auftrag)})</div></div>
  </div>`;
  html+=`<div class="prog-line">Basis aus Ads (letzte 7 Tage): <b>${nf(p.baseline_anfragen)}</b> Anfragen · durch übernommene Vorschläge bereits <b>+${nf(p.angenommen_extra)}</b>.</div>`;
  if((p.offen_extra||0)>0){
    html+=`<div class="prog-pot">📈 <b>Wenn Du die offenen Vorschläge auch übernimmst:</b> bis zu <b>${nf(p.anfragen_potenzial)}</b> Anfragen → <b>${nf(p.auftraege_potenzial)}</b> Aufträge ≈ <b>${eu(p.umsatz_potenzial)}</b>.</div>`;
    if(p.top_offen&&p.top_offen.length){
      html+='<div class="prog-top"><div class="prog-top-h">Diese Vorschläge heben die Zahl am meisten:</div>';
      p.top_offen.forEach(t=>{html+=`<div class="prog-top-i"><span>＋${nf(t.extra)}/Wo</span> ${esc(t.titel)}</div>`;});
      html+='</div>';
    }
  } else {
    html+='<div class="prog-line" style="opacity:.8">Übernimm Dilaras Vorschläge unten, dann steigt die Prognose sichtbar.</div>';
  }
  html+='<div class="prog-foot">Ehrliche Schätzung – konservativ gerechnet (Erfolgs-Wahrscheinlichkeit eingepreist). Keine geschönten Zahlen.</div>';
  el.innerHTML=html;
}

/* --- Google-Ads-Maßnahmenplan (Checkliste) --- */
function openAdsPlan(){showSection('adsplan');}
async function loadAdsPlan(){
  const el=gl('adsplanBody'); if(!el)return;
  el.innerHTML='<div class="prio-empty">Lade …</div>';
  let d={};try{d=await api('adsplan_get');}catch(e){}
  if(!d||!d.ok){el.innerHTML='<div class="prio-empty">Konnte den Plan nicht laden.</div>';return;}
  const pct=d.gesamt?Math.round(d.done/d.gesamt*100):0;
  gl('apDone').textContent=d.done; gl('apTotal').textContent=d.gesamt;
  const bar=gl('apBar'); if(bar)bar.style.width=pct+'%';
  const KAT=['Tracking','Struktur','Gebote','Zielgruppen','Assets','Seite'];
  const prioT={1:'🔴 sofort',2:'🟡 wichtig',3:'🟢 später'};
  const items=d.items||[];
  let html='';
  KAT.forEach(kat=>{
    const grp=items.filter(i=>i.kat===kat); if(!grp.length)return;
    html+=`<div class="ap-kat">${esc(kat)}</div>`;
    grp.sort((a,b)=>(a.prio||9)-(b.prio||9)).forEach(i=>{
      html+=`<label class="ap-item${i.done?' done':''}">
        <input type="checkbox" ${i.done?'checked':''} onchange="toggleAdsPlan('${i.id}',this.checked)">
        <span class="ap-txt"><b>${esc(i.text)}</b><span class="ap-nutzen">${esc(prioT[i.prio]||'')} · ${esc(i.nutzen||'')}</span></span>
      </label>`;
    });
  });
  el.innerHTML=html;
}
async function toggleAdsPlan(id,done){
  try{await api('adsplan_toggle',{id,done});}catch(e){}
  // Fortschritt sofort aktualisieren (ohne komplettes Neuladen-Flackern)
  loadAdsPlan();
}

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
      <button class="task-go" style="width:100%;text-align:center;margin-top:8px" onclick="dilaraChat(this)" data-id="${esc(r.id||'')}" data-kind="ads" data-titel="${esc(r.titel||'')}" data-was="${esc(r.was||'')}">💬 Mit Dilara besprechen</button>
    </div>`;
  }).join('');
}
function dilaraChat(btn){
  const t=btn.getAttribute('data-titel')||'',w=btn.getAttribute('data-was')||'';
  const id=btn.getAttribute('data-id')||'',kind=btn.getAttribute('data-kind')||'ads';
  openChat('dilara','Zu Deiner Empfehlung "'+t+'" ('+w+'): Erkläre mir das genauer – lohnt sich das wirklich, was bringt es konkret und was muss ich tun?',{id:id,kind:kind,titel:t});
}
/* Banner im Chat: zeigt die gerade besprochene Empfehlung + „erledigt"-Knopf.
   Markieren ueber den Chat nutzt DENSELBEN Endpunkt wie der Listen-Button
   (eine zentrale Quelle der Wahrheit) – danach laedt die Liste beim Zurueck frisch. */
function renderChatReco(){
  const bar=gl('chatRecoBar'); if(!bar)return;
  if(chatReco&&chatReco.id){
    bar.style.display='flex';
    bar.innerHTML='<span class="crb-txt">📌 Empfehlung: <b>'+esc(chatReco.titel||'')+'</b></span>'
      +'<button class="crb-done" onclick="chatRecoDone(this)">✅ Als erledigt markieren</button>';
  }else{bar.style.display='none';bar.innerHTML='';}
}
async function chatRecoDone(btn){
  if(!chatReco||!chatReco.id)return;
  btn.disabled=true; btn.textContent='⏳ …';
  const action=chatReco.kind==='web'?'website_apply':'ads_apply';
  const titel=chatReco.titel||'';
  let d={}; try{d=await api(action,{id:chatReco.id});}catch(e){}
  const bar=gl('chatRecoBar'); if(bar){bar.innerHTML='<span class="crb-txt">'+(d.executed?'✅ Live geändert':'✅ Erledigt')+' – ist jetzt aus Deiner Aufgabenliste raus.</span>';}
  pushMsg('ai',(d.executed?'✅ ':'📝 ')+(d.msg||('Erledigt, Chef! Ich hab die Empfehlung „'+titel+'" als übernommen markiert.')));
  chatReco=null;
  if(typeof loadDashboard==='function')loadDashboard();
}

/* ===== WISSENSARCHIV (durchsuchbar, themen-sortiert) ===== */
let memTimer=null;
function openMem(){
  if(typeof AGENTS==='undefined'||!AGENTS[mode])return;
  gl('memTitle').innerHTML='🧠 '+esc(MODI[mode].name)+' · Wissensarchiv';
  gl('memSearch').value='';
  gl('memOverlay').style.display='flex';
  loadMem('');
  setTimeout(()=>{const s=gl('memSearch');if(s)s.focus();},200);
}
function closeMem(){const o=gl('memOverlay');if(o)o.style.display='none';}
function memSearchDeb(){clearTimeout(memTimer);memTimer=setTimeout(()=>loadMem(gl('memSearch').value.trim()),250);}
async function loadMem(q){
  gl('memBody').innerHTML='<div class="mem-empty">Lade …</div>';
  let d={};try{d=await api('agent_memory',{agent:mode,q});}catch(e){}
  if(!d||!d.ok){gl('memBody').innerHTML='<div class="mem-empty">Noch kein Gedächtnis vorhanden.</div>';return;}
  const mz=ts=>{try{return new Date(ts*1000).toLocaleString('de-DE',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});}catch(e){return'';}};
  const item=e=>`<div class="mem-item"><span class="mz">${mz(e.ts)}</span><span>${esc(e.text)}</span></div>`;
  if(d.modus==='suche'){
    const t=d.treffer||[];
    if(!t.length){gl('memBody').innerHTML='<div class="mem-empty">Nichts gefunden für „'+esc(q)+'".</div>';return;}
    gl('memBody').innerHTML='<div class="mem-thema"><div class="mem-thema-h">🔎 Treffer <span class="cnt">'+t.length+'</span></div>'+t.map(item).join('')+'</div>';
  }else{
    const th=d.themen||[];
    if(!th.length){gl('memBody').innerHTML='<div class="mem-empty">Noch kein Gedächtnis vorhanden – sobald '+esc(MODI[mode].name)+' arbeitet, sammelt sich hier Wissen.</div>';return;}
    gl('memBody').innerHTML=th.map(g=>`<div class="mem-thema"><div class="mem-thema-h">${esc(g.label)} <span class="cnt">${g.gesamt}</span></div>`+(g.eintraege||[]).map(item).join('')+'</div>').join('');
  }
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
  lastAdsReport=null; const _kb=gl('adsKiBtn'); if(_kb)_kb.disabled=true;
  gl('adsBody').innerHTML='<div class="prio-empty">Lade Kampagnen-Daten …</div>';
  try{
    const d=await api('ads_report');
    if(!d.ok){
      gl('adsBody').innerHTML=`<div class="fehler">⚠️ ${esc(d.error||'Fehler')}<br><br>Tipp: Sind alle 5 Ads-Zugangsdaten unter ⚙️ eingetragen?</div>`;
      return;
    }
    lastAdsReport=d.report; if(_kb)_kb.disabled=false;
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
function openWeb(){showSection('web');}
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
      </div>
      <button class="task-go" style="width:100%;text-align:center;margin-top:8px" onclick="dilaraChat(this)" data-id="${esc(r.id||'')}" data-kind="web" data-titel="${esc(r.titel||'')}" data-was="${esc(r.was||'')}">💬 Mit Dilara besprechen</button></div>`;}).join('');
}
async function webApply(id,btn){
  btn.disabled=true;btn.textContent='⏳ …';
  let d={};try{d=await api('website_apply',{id});}catch(e){}
  const card=btn.closest('.reco');
  if(card){const m=document.createElement('div');m.className='reco-result'+(d.executed?' done':'');m.textContent=(d.executed?'✅ ':'📝 ')+(d.msg||'Übernommen');card.appendChild(m);}
  if(d.msg&&typeof speak==='function')speak(cleanSpeech(d.msg));
  setTimeout(()=>{loadWeb();loadDashboard();},2400);
}
async function webAct(action,id,btn){btn.disabled=true;await api(action,{id});setTimeout(loadWeb,400);}

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
function chatScroll(){const l=gl('chatLog');if(l)l.scrollTop=l.scrollHeight;}
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
  chatScroll();
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
  chatScroll();
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
    // Gespraechsgedaechtnis: Verlauf speichern (nur echte Agenten)
    if(typeof AGENTS!=='undefined' && AGENTS[mode]){
      api('chat_save',{agent:mode, messages:history[mode].map(x=>({role:x.role, content:x.content}))}).catch(()=>{});
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

<?php
// Begruessung: Anzahl offener Freigaben fuer den "wartet auf dich"-Hinweis
$__fg_offen = (!empty($_SESSION['eingeloggt']) && function_exists('oh_freigaben')) ? count(oh_freigaben('offen')) : 0;
?>
<!-- WILLKOMMEN ZURUECK – einmalige Nacht-Einfuehrung (wegklickbar) -->
<div id="nightWelcome" data-fg="<?= (int)$__fg_offen ?>" style="display:none">
  <div class="nw-card">
    <div class="nw-badge">ÜBER NACHT AUSGEBAUT</div>
    <h2 class="nw-title">Willkommen zurück, grosser Adnan</h2>
    <p class="nw-text">
      Über Nacht ist einiges dazugekommen. Du hast jetzt <b>4 eigene Landing-Pages</b> für Google Ads
      (Elektro, Altbau, Netzwerk, Wallbox), ein <b>Firmen-Anschreiben-System</b>, automatisches
      <b>Lead-Tracking</b> (Anfragen pro 100 Besucher, Anfrage→Auftrag-Quote) und ein
      <b>Sprach-Interface</b>: im Agenten-Chat einfach das 🎤 antippen und reden statt tippen.
      Ein echtes <b>Sicherheitsleck</b> habe ich geschlossen (eine öffentlich ladbare Backup-Datei
      mit allen Passwörtern). Den ganzen Tag konntest du mich über die Umbau-Seite direkt steuern.
    </p>
    <div class="nw-points">
      <div class="nw-p"><span>📊</span> <b>Google-Ads-Analyse</b> liegt bereit: was läuft, was Budget verbrennt, wo du hinschieben solltest – im Morgen-Bericht.</div>
      <div class="nw-p"><span>📋</span> <b>Freigaben</b> im Dashboard – dort wartet alles, was deine Entscheidung braucht (Budget, Ads-Änderungen).</div>
      <div class="nw-p"><span>👻</span> <b>Ghost</b> hat die Nacht geprüft und meldet sich künftig jeden Morgen von selbst.</div>
    </div>
    <div id="nwFreigabeHint" class="nw-hint" style="display:none"></div>
    <button class="nw-btn" onclick="nwClose()">Los geht's, mein Team wartet</button>
  </div>
</div>
<style>
#nightWelcome{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(4,7,13,.82);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);animation:nwFade .5s ease}
@keyframes nwFade{from{opacity:0}to{opacity:1}}
.nw-card{max-width:560px;width:100%;background:linear-gradient(160deg,#0e131d,#0a0f18);border:1px solid #1e2940;border-radius:24px;padding:30px 26px 26px;box-shadow:0 24px 70px rgba(0,0,0,.6),0 0 40px rgba(57,214,255,.10);animation:nwUp .55s cubic-bezier(.2,.8,.2,1)}
@keyframes nwUp{from{transform:translateY(18px);opacity:0}to{transform:translateY(0);opacity:1}}
.nw-badge{display:inline-block;font-size:10px;letter-spacing:2.5px;font-weight:700;color:#5b91f5;background:rgba(57,214,255,.10);border:1px solid rgba(57,214,255,.25);padding:5px 11px;border-radius:20px;margin-bottom:14px}
.nw-title{font-size:23px;font-weight:800;color:#eaf3fb;margin:0 0 12px;letter-spacing:.3px}
.nw-text{font-size:14px;line-height:1.7;color:#b9c8da;margin:0 0 18px}
.nw-text b{color:#eaf3fb}
.nw-points{display:flex;flex-direction:column;gap:9px;margin-bottom:16px}
.nw-p{font-size:13px;line-height:1.5;color:#9fb1c6;display:flex;gap:10px;align-items:flex-start;background:rgba(255,255,255,.03);border:1px solid #18222f;border-radius:12px;padding:11px 13px}
.nw-p span{font-size:16px;flex-shrink:0}
.nw-p b{color:#dfeaf6}
.nw-hint{font-size:13px;font-weight:600;color:#e7b14b;background:rgba(231,177,75,.10);border:1px solid rgba(231,177,75,.28);border-radius:12px;padding:12px 14px;margin-bottom:16px;line-height:1.5}
.nw-btn{width:100%;padding:15px;background:linear-gradient(135deg,#1693c4,#5b91f5);color:#04121a;border:none;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;letter-spacing:.3px;transition:transform .15s,box-shadow .15s}
.nw-btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(57,214,255,.30)}
@media(max-width:600px){.nw-card{padding:26px 20px 22px}.nw-title{font-size:20px}}
</style>
<script>
(function(){
  var KEY='oh_welcome_20260613b';
  function nwShow(){
    if(localStorage.getItem(KEY))return;
    var el=document.getElementById('nightWelcome'); if(!el)return;
    var fg=parseInt(el.getAttribute('data-fg')||'0',10);
    if(fg>0){var h=document.getElementById('nwFreigabeHint'); if(h){h.style.display='block';
      h.innerHTML='⏳ Das wartet heute auf deine Freigabe: <b>'+fg+'</b> '+(fg===1?'Punkt':'Punkte')+' im Freigaben-Bereich.';}}
    el.style.display='flex';
  }
  window.nwClose=function(){var el=document.getElementById('nightWelcome'); if(el)el.style.display='none'; try{localStorage.setItem(KEY,'1');}catch(e){}};
  // Zeige nach dem Login-/Boot-Vorgang
  function tryShow(){ if(typeof LOGGED_IN!=='undefined' && LOGGED_IN){ setTimeout(nwShow,1400); } }
  if(document.readyState==='complete'||document.readyState==='interactive'){ tryShow(); }
  else { document.addEventListener('DOMContentLoaded', tryShow); }
  // Falls Login per AJAX passiert (kein Reload): nach Klick auf Authentifizieren ebenfalls versuchen
  document.addEventListener('submit',function(e){ if(e.target&&e.target.id==='loginForm'){ setTimeout(nwShow,2600); } });
})();
</script>

<!-- SPRACH-INTERFACE: Mikrofon-Eingabe + ElevenLabs-ready Sprachausgabe -->
<script>
(function(){
  // (1) speak() ElevenLabs-ready machen: erst Server-Stimme (tts.php), sonst Browser-Stimme
  var _browserSpeak = window.speak;
  window.speak = function(txt){
    try{
      if(typeof isMuted!=='undefined' && isMuted) return;
      var clean = (typeof cleanSpeech==='function') ? cleanSpeech(txt) : (txt||'');
      if(!clean) return;
      var fd = new FormData(); fd.append('text', clean);
      fetch('tts.php',{method:'POST',body:fd}).then(function(r){
        var ct = r.headers.get('Content-Type')||'';
        if(r.status===200 && ct.indexOf('audio')>=0){
          return r.blob().then(function(b){
            try{ if(typeof duck==='function') duck(true);
              var a=new Audio(URL.createObjectURL(b));
              a.onended=function(){ if(typeof duck==='function') duck(false); };
              a.onerror=function(){ if(typeof duck==='function') duck(false); if(_browserSpeak)_browserSpeak(txt); };
              a.play();
            }catch(e){ if(_browserSpeak)_browserSpeak(txt); }
          });
        } else { if(_browserSpeak)_browserSpeak(txt); }
      }).catch(function(){ if(_browserSpeak)_browserSpeak(txt); });
    }catch(e){ if(_browserSpeak)_browserSpeak(txt); }
  };

  // (2) Mikrofon-Button: einfach reinreden statt tippen (Deutsch)
  var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  function addMic(){
    var inp = document.getElementById('chatIn'); if(!inp) return;
    var composer = inp.parentElement; if(!composer || document.getElementById('micBtn')) return;
    var mic = document.createElement('button');
    mic.id='micBtn'; mic.type='button'; mic.title='Sprechen statt tippen';
    mic.innerHTML='🎤';
    mic.style.cssText='background:rgba(91,145,245,.14);border:1px solid #243250;color:#5b91f5;font-size:18px;width:44px;height:44px;border-radius:12px;cursor:pointer;flex-shrink:0;margin-right:6px;';
    var sendBtn=document.getElementById('sendBtn');
    composer.insertBefore(mic, sendBtn);
    if(!SR){ mic.disabled=true; mic.style.opacity=.4; mic.title='Spracherkennung in diesem Browser nicht verfügbar'; return; }
    var rec=new SR(); rec.lang='de-DE'; rec.interimResults=true; rec.continuous=false;
    var listening=false;
    mic.onclick=function(){ if(listening){ rec.stop(); return; } try{ if('speechSynthesis' in window) speechSynthesis.cancel(); }catch(e){} try{ rec.start(); }catch(e){} };
    rec.onstart=function(){ listening=true; mic.style.background='rgba(255,107,120,.18)'; mic.style.color='#ff6b78'; };
    rec.onend=function(){ listening=false; mic.style.background='rgba(91,145,245,.14)'; mic.style.color='#5b91f5'; var v=inp.value.trim(); if(v && typeof send==='function') send(); };
    rec.onresult=function(e){ var t=''; for(var i=0;i<e.results.length;i++) t+=e.results[i][0].transcript; inp.value=t; if(typeof autoGrow==='function') autoGrow(); };
    rec.onerror=function(){ listening=false; mic.style.background='rgba(91,145,245,.14)'; mic.style.color='#5b91f5'; };
  }
  if(document.readyState!=='loading') addMic(); else document.addEventListener('DOMContentLoaded', addMic);
  setTimeout(addMic, 2500);
})();
</script>
</body>
</html>

