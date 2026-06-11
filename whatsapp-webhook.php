<?php
/**
 * OH Haustechnik – WhatsApp Business (Meta Cloud API) Webhook
 * Empfängt eingehende WhatsApp-Nachrichten und legt sie als Aufgabe + Lead an.
 *
 * Einrichtung in der Meta-Developer-Konsole:
 *  - Callback-URL: https://oh-haustechnik.de/whatsapp-webhook.php
 *  - Verify-Token: der Wert aus config (wa_verify_token), Standard 'oh-wa'
 */

require_once __DIR__ . '/includes/buero-lib.php';
$cfg = oh_config();

// 1) Verifizierung (Meta ruft per GET auf)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verify = $cfg['wa_verify_token'] ?? 'oh-wa';
    if (($_GET['hub_mode'] ?? '') === 'subscribe'
        && ($_GET['hub_verify_token'] ?? '') === $verify
        && isset($_GET['hub_challenge'])) {
        echo $_GET['hub_challenge'];
        exit;
    }
    http_response_code(403);
    echo 'Verify-Token falsch.';
    exit;
}

// 2) Eingehende Nachricht (POST)
$payload = json_decode(file_get_contents('php://input'), true);
http_response_code(200); // Meta sofort bestätigen

if (!is_array($payload)) { echo 'OK'; exit; }

$store = oh_read('whatsapp', []);
$neu = false;

foreach ($payload['entry'] ?? [] as $entry) {
    foreach ($entry['changes'] ?? [] as $change) {
        $value = $change['value'] ?? [];
        // Name des Absenders aus dem Kontakt
        $namen = [];
        foreach ($value['contacts'] ?? [] as $c) {
            $namen[$c['wa_id'] ?? ''] = $c['profile']['name'] ?? '';
        }
        foreach ($value['messages'] ?? [] as $msg) {
            if (($msg['type'] ?? '') !== 'text') continue;
            $from = $msg['from'] ?? '';
            $text = $msg['text']['body'] ?? '';
            $name = $namen[$from] ?? '';
            $id = $msg['id'] ?? ('wa' . time() . mt_rand(100, 999));
            // Doppelte vermeiden
            foreach ($store as $s) { if (($s['id'] ?? '') === $id) continue 2; }
            array_unshift($store, [
                'id' => $id, 'from' => $from, 'name' => $name,
                'text' => $text, 'ts' => time(), 'status' => 'offen',
            ]);
            $neu = true;
            // Auch als Lead erfassen
            if (function_exists('oh_add_lead')) {
                oh_add_lead(['source' => 'whatsapp', 'name' => $name ?: $from, 'telefon' => $from, 'details' => $text]);
            }
        }
    }
}

if ($neu) oh_write('whatsapp', $store);
echo 'OK';
