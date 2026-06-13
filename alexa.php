<?php
/**
 * OH Haustechnik – Sprachsteuerung / Alexa-Endpunkt
 *
 * Faehigkeiten:
 *  - "Alexa, oeffne das Buero"  -> fragt nach Passwort ('OH'), entsperrt das Buero
 *  - "Alexa, schliesse das Buero" -> ECHTE serverseitige Sperre (token_version++)
 *  - Status/Briefing: liest die Tages-Zusammenfassung vor (Leads, E-Mails, Mert)
 *
 * Sicherheit: Sperr-Logik laeuft serverseitig; Passwort wird hier geprueft,
 * nicht im Skill. Browser-/Notfall-Steuerung nur mit cron_key.
 *
 * Test im Browser:   https://oh-haustechnik.de/alexa.php?key=oh-cron
 * Notfall-Handy:     ...alexa.php?key=oh-cron&aktion=zu   (sperren)
 *                    ...alexa.php?key=oh-cron&aktion=auf&pw=OH  (entsperren)
 * Alexa-Skill:       Endpoint = https://oh-haustechnik.de/alexa.php (POST)
 */

require_once __DIR__ . '/includes/buero-lib.php';

const OH_SPRACH_PASSWORT = 'oh'; // gesprochen: 'OH'

function oh_lock_get(): array {
    $l = oh_read('lock', ['locked' => false, 'token_version' => 1]);
    return is_array($l) ? $l : ['locked' => false, 'token_version' => 1];
}

function oh_lock_set(bool $locked): array {
    $l = oh_lock_get();
    $l['locked'] = $locked;
    if ($locked) { $l['token_version'] = (int)($l['token_version'] ?? 1) + 1; }
    $l['geaendert'] = date('Y-m-d H:i:s');
    oh_write('lock', $l);
    if (function_exists('oh_log_activity')) {
        oh_log_activity('alexa', $locked ? 'Büro per Sprachbefehl GESPERRT.' : 'Büro per Sprachbefehl geöffnet.');
    }
    return $l;
}

function oh_alexa_antwort(string $text, bool $ende = true, string $reprompt = ''): void {
    header('Content-Type: application/json; charset=utf-8');
    $resp = [
        'version' => '1.0',
        'response' => [
            'outputSpeech' => ['type' => 'PlainText', 'text' => $text],
            'card' => ['type' => 'Simple', 'title' => 'OH Büro', 'content' => $text],
            'shouldEndSession' => $ende,
        ],
    ];
    if (!$ende && $reprompt !== '') {
        $resp['response']['reprompt'] = ['outputSpeech' => ['type' => 'PlainText', 'text' => $reprompt]];
    }
    echo json_encode($resp);
    exit;
}

// ---------------------------------------------------------------------------
// Alexa-POST (Skill-Anfragen)
// ---------------------------------------------------------------------------
$raw = file_get_contents('php://input');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $raw) {
    $req = json_decode($raw, true) ?: [];
    $typ = $req['request']['type'] ?? '';
    $intent = $req['request']['intent']['name'] ?? '';
    $slots = $req['request']['intent']['slots'] ?? [];

    if ($typ === 'IntentRequest' && $intent === 'OeffneBueroIntent') {
        $pw = strtolower(trim($slots['passwort']['value'] ?? ''));
        if ($pw === '') {
            oh_alexa_antwort('Wie lautet das Passwort, großer Adnan?', false, 'Bitte nenne das Passwort.');
        }
        if ($pw === OH_SPRACH_PASSWORT) {
            oh_lock_set(false);
            oh_alexa_antwort('Willkommen zurück, großer Adnan. Das Büro ist geöffnet und dein Team ist bereit.');
        }
        oh_alexa_antwort('Das Passwort war leider falsch. Das Büro bleibt geschlossen.');
    }

    if ($typ === 'IntentRequest' && $intent === 'SchliesseBueroIntent') {
        oh_lock_set(true);
        oh_alexa_antwort('Das Büro ist jetzt vollständig gesperrt, großer Adnan. Niemand kommt rein, bis du es wieder öffnest. Gute Nacht.');
    }

    if ($typ === 'IntentRequest' && $intent === 'StatusIntent') {
        oh_alexa_antwort(oh_alexa_summary());
    }

    // LaunchRequest oder unbekannter Intent -> Zusammenfassung (bisheriges Verhalten)
    oh_alexa_antwort(oh_alexa_summary());
}

// ---------------------------------------------------------------------------
// Browser-/Notfall-Steuerung (Handy): nur mit cron_key
// ---------------------------------------------------------------------------
$key = oh_config()['cron_key'] ?? 'oh-cron';
if (($_GET['key'] ?? '') !== $key) { http_response_code(403); exit('Zugriff verweigert.'); }
header('Content-Type: text/plain; charset=utf-8');

$aktion = $_GET['aktion'] ?? '';
if ($aktion === 'zu') {
    oh_lock_set(true);
    exit("Büro GESPERRT. Alle Sitzungen beendet.\n");
}
if ($aktion === 'auf') {
    if (strtolower(trim($_GET['pw'] ?? '')) !== OH_SPRACH_PASSWORT) { exit("Passwort falsch. Büro bleibt zu.\n"); }
    oh_lock_set(false);
    exit("Büro geöffnet. Willkommen zurück, großer Adnan.\n");
}
$l = oh_lock_get();
echo 'Sperre: ' . (!empty($l['locked']) ? 'GESCHLOSSEN' : 'offen') . "\n\n" . oh_alexa_summary() . "\n";
