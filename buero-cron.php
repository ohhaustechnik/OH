<?php
/**
 * OH Haustechnik – Automatik (Cronjob)
 * Wird zeitgesteuert von all-inkl aufgerufen, z.B. 1× täglich:
 *   /usr/bin/php /pfad/zu/buero-cron.php
 * oder per URL-Aufruf mit Schlüssel:
 *   https://deine-domain.de/buero-cron.php?key=DEIN_CRON_SCHLUESSEL
 *
 * Aufgaben:
 *  - Follow-up-E-Mail 2 Tage nach Angebot (wenn noch keine Antwort/Abschluss)
 *  - Bewertungs-Anfrage 5 Tage nach Abschluss
 *
 * Voraussetzung: Anthropic-Key + Gmail-App-Passwort im Büro (Einstellungen) hinterlegt.
 */

require_once __DIR__ . '/includes/buero-lib.php';
if (is_file(__DIR__ . '/includes/vorlagen.php')) require_once __DIR__ . '/includes/vorlagen.php';
if (is_file(__DIR__ . '/auswertung.php')) require_once __DIR__ . '/auswertung.php';

// Einfacher Schutz bei URL-Aufruf
$CRON_KEY = oh_config()['cron_key'] ?? 'oh-cron';
if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== $CRON_KEY) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$now = time();

// Postfach (ungelesene E-Mails) + Website-Erreichbarkeit aktualisieren
if (function_exists('oh_inbox_scan')) oh_inbox_scan();

$leads = oh_read('leads', []);
$log = [];
$cfg = oh_config();
$absender = $cfg['gmail_user'] ?? 'oh.haustechnik@gmail.com';

foreach ($leads as $l) {
    $email = $l['email'] ?? '';
    if (!$email) continue;
    $name = $l['name'] ?: 'Kunde';
    $vorname = trim(explode(' ', $name)[0]);

    // 1) Follow-up 2 Tage nach Angebot
    if (($l['status'] ?? '') === 'angebot_raus'
        && !empty($l['angebot_ts'])
        && ($now - $l['angebot_ts']) >= 2 * 86400) {

        $body = oh_ki(
            "Du schreibst als OH Haustechnik (Elektriker Nürnberg, persönlich, freundlich, kein Druck) eine kurze Follow-up-E-Mail an einen Kunden, dem vor 2 Tagen ein Angebot geschickt wurde und der noch nicht geantwortet hat. Ziel: freundlich nachfragen, ob Fragen offen sind, Termin anbieten. Max 8 Sätze, mit Anrede 'Hallo {$vorname},' und Grußformel 'Viele Grüße\\nOH Haustechnik'. NUR der E-Mail-Text.",
            "Kunde: {$name}. Leistung: " . ($l['kategorie'] ?? '-') . ". Ort: " . ($l['ort'] ?? '-')
        );
        if ($body) {
            $res = oh_send_mail($email, 'Kurze Nachfrage zu Ihrem Angebot – OH Haustechnik', $body, $absender);
            if (!empty($res['ok'])) {
                oh_update_lead($l['id'], ['status' => 'nachgefasst'], 'Automatik: Follow-up-E-Mail gesendet');
                if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Follow-up-E-Mail automatisch gesendet an ' . $name);
                $log[] = "Follow-up an {$email} ({$name})";
            } else {
                $log[] = "FEHLER Follow-up {$email}: " . $res['info'];
            }
        }
        continue;
    }

    // 2) Bewertungs-Anfrage 5 Tage nach Abschluss
    if (in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen'])
        && empty($l['bewertung_angefragt'])
        && !empty($l['abschluss_ts'])
        && ($now - $l['abschluss_ts']) >= 5 * 86400) {

        $body = oh_ki(
            "Du schreibst als OH Haustechnik eine kurze, herzliche E-Mail, die einen zufriedenen Kunden um eine Google-Bewertung bittet (5 Tage nach Projektabschluss). Persönlich, dankbar, kein Druck. Bitte den Hinweis einbauen, dass eine kurze Bewertung sehr hilft. Anrede 'Hallo {$vorname},', Grußformel 'Viele Grüße\\nOH Haustechnik'. NUR der E-Mail-Text.",
            "Kunde: {$name}. Erledigte Leistung: " . ($l['kategorie'] ?? '-')
        );
        if ($body) {
            $res = oh_send_mail($email, 'Wie zufrieden waren Sie? – OH Haustechnik', $body, $absender);
            if (!empty($res['ok'])) {
                oh_update_lead($l['id'], ['bewertung_angefragt' => true], 'Automatik: Bewertungs-Anfrage gesendet');
                if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Bewertungs-Anfrage automatisch gesendet an ' . $name);
                $log[] = "Bewertungs-Anfrage an {$email} ({$name})";
            } else {
                $log[] = "FEHLER Bewertung {$email}: " . $res['info'];
            }
        }
    }
}

/* --------------------------------------------------------------------------
 * TEAM-AUTONOMIE: Bei jedem Lauf (z.B. stündlich) denkt das Team selbst –
 * Geschäftsführer Mert setzt die Tagesprioritäten, danach stimmt sich das
 * vernetzte Agenten-Team ab (jeder prüft seinen Bereich, gibt Wichtiges an
 * Kollegen weiter). Beides schreibt ins Gedächtnis der Agenten.
 * Benötigt einen hinterlegten Anthropic-Schlüssel – fehlt er, wird es nur
 * sauber protokolliert (kein Abbruch des Cron-Laufs).
 * ------------------------------------------------------------------------ */
// Dilara: Live-Marktanalyse (echte Klickpreise, Marktanteile, Suchbegriffe aus Google)
if (function_exists('oh_dilara_markt_live') && !empty(oh_config()['ads_refresh_token'])) {
    $derr = null;
    if (oh_dilara_markt_live($derr)) $log[] = 'Dilara: Live-Marktcheck ok';
    elseif ($derr) $log[] = 'Dilara-Markt FEHLER: ' . $derr;
}

// Lexware: Rechnungen & Umsatz abgleichen (nur wenn Schlüssel hinterlegt)
if (function_exists('oh_lex_refresh') && !empty(oh_config()['lexware_api_key'])) {
    $lerr = null;
    $lx = oh_lex_refresh($lerr);
    if ($lx) $log[] = 'Lexware: ' . $lx['offen_anzahl'] . ' offene Rechnungen, Umsatz ' . $lx['bezahlt_jahr_summe'] . '€';
    elseif ($lerr) $log[] = 'Lexware FEHLER: ' . $lerr;
}

// Aylin (Autopilot): freundliche Zahlungserinnerungen bei überfälligen Rechnungen
if (function_exists('oh_aylin_erinnerungen') && !empty(oh_config()['lexware_api_key'])) {
    $merr2 = null;
    $n = oh_aylin_erinnerungen($merr2);
    if ($n) $log[] = "Aylin: $n Zahlungserinnerung(en) automatisch gesendet";
}

// Dilara (Autopilot): tägliche Ads-Analyse + sichere Optimierungen direkt ausführen
if (function_exists('oh_dilara_auto_optimieren')) {
    $derr2 = null;
    $r2 = oh_dilara_auto_optimieren($derr2);
    if (!empty($r2['analyse'])) $log[] = 'Dilara: Ads-Analyse erneuert' . (!empty($r2['ausgefuehrt']) ? ' + ' . $r2['ausgefuehrt'] . ' Geld-Verbrenner automatisch ausgeschlossen' : '');
    elseif ($derr2) $log[] = 'Dilara-Ads FEHLER: ' . $derr2;
}

// Kaan: Postfach-Vollanalyse höchstens 1x pro Tag (KI-Kosten begrenzen)
if (function_exists('oh_kaan_email_analyse')) {
    $kw = oh_read('kaan_wissen', []);
    if ((time() - ($kw['ts'] ?? 0)) > 20 * 3600) {
        $kerr = null;
        $ka = oh_kaan_email_analyse($kerr);
        if ($ka) $log[] = 'Kaan: Postfach-Vollanalyse (' . ($ka['mails'] ?? 0) . ' Mails)';
        elseif ($kerr) $log[] = 'Kaan-Analyse FEHLER: ' . $kerr;
    }
}

// Nachrichten-Triage: eingehende E-Mails & WhatsApp einstufen und in die Freigaben legen
if (function_exists('oh_msg_triage')) {
    $terr = null;
    $tr = oh_msg_triage($terr);
    if (!empty($tr['neu'])) $log[] = 'Triage: ' . $tr['neu'] . ' neue Nachricht(en) eingestuft';
    elseif ($terr) $log[] = 'Triage FEHLER: ' . $terr;
}

if (function_exists('oh_mert_briefing')) {
    $merr = null;
    if (oh_mert_briefing($merr)) $log[] = 'Mert: Tagesplan aktualisiert';
    elseif ($merr) $log[] = 'Mert FEHLER: ' . $merr;
}
// STUFE 2: Einzeldenken – jeder Agent denkt eigenständig mit eigenem KI-Aufruf
// (Rotation: max 3 pro Lauf, dringende zuerst, sonst jeder ~alle 3 Stunden)
if (function_exists('oh_denker_rotation')) {
    $denker = oh_denker_rotation(3);
    $dok = [];
    foreach ($denker as $dAg) { $de = null; if (oh_agent_denken($dAg, $de) !== null) $dok[] = $dAg; }
    if ($dok) $log[] = 'Einzeldenken: ' . implode(', ', $dok);
}

// Mahnsystem: überfällige Aufträge automatisch anmahnen (Druck, ohne Chef)
if (function_exists('oh_mahnsystem')) {
    $mahn = oh_mahnsystem();
    if ($mahn) $log[] = "Mahnsystem: $mahn Mahnung(en) verschickt";
}

if (function_exists('oh_agenten_runde')) {
    $aerr = null;
    $runde = oh_agenten_runde($aerr);
    if ($runde) $log[] = 'Agenten-Runde ok (' . count($runde['nachrichten'] ?? []) . ' Nachrichten)';
    elseif ($aerr) $log[] = 'Agenten-Runde FEHLER: ' . $aerr;
}

// ---------------------------------------------------------------------------
// GHOST: Kontroll-Agent prueft das System 1x taeglich (ab 05 Uhr, vor dem
// Morgen-Bericht). Die einmal-pro-Tag-Sperre steckt in ghost_run() selbst.
// ---------------------------------------------------------------------------
if ((int)date('H') >= 5 && is_file(__DIR__ . '/ghost.php')) {
    define('OH_GHOST_INCLUDE', 1);
    require __DIR__ . '/ghost.php';
    if (function_exists('ghost_run')) {
        $g = ghost_run(false);
        if (($g['status'] ?? '') === 'ok') {
            $log[] = 'Ghost: Tagespruefung (' . ($g['modell'] ?? '?') . ($g['fallback'] ? ' [Fallback]' : '') . ', ' . ($g['kosten'] ?? 0) . '€)';
        }
    }
}

// Wochen-Auswertung taeglich frisch berechnen (Anfragen/100 Besucher, Anfrage->Auftrag-Quote)
if (function_exists('oh_lead_auswertung')) { oh_write('auswertung', oh_lead_auswertung(7)); }

$summary = '[' . date('Y-m-d H:i') . '] Cron-Lauf: ' . (count($log) ? implode('; ', $log) : 'nichts zu tun');
@file_put_contents(OH_DATA_DIR . '/cron.log', $summary . "\n", FILE_APPEND);
echo $summary . "\n";
