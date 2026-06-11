<?php
/**
 * OH Haustechnik – Sprachsteuerung / Alexa-Endpunkt
 * Liefert die tägliche Zusammenfassung (Leads, E-Mails, WhatsApp, Mert).
 *
 * Test im Browser:  https://oh-haustechnik.de/alexa.php?key=oh-cron
 * Alexa-Skill:      Endpoint = https://oh-haustechnik.de/alexa.php (POST)
 */

require_once __DIR__ . '/includes/buero-lib.php';

$summary = oh_alexa_summary();

// Alexa schickt JSON per POST -> Alexa-konforme Antwort
$raw = file_get_contents('php://input');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $raw) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'version' => '1.0',
        'response' => [
            'outputSpeech' => ['type' => 'PlainText', 'text' => $summary],
            'card' => ['type' => 'Simple', 'title' => 'OH Büro', 'content' => $summary],
            'shouldEndSession' => true,
        ],
    ]);
    exit;
}

// Browser-Test (mit Schlüssel) -> Klartext
$key = oh_config()['cron_key'] ?? 'oh-cron';
if (($_GET['key'] ?? '') !== $key) { http_response_code(403); exit('Zugriff verweigert.'); }
header('Content-Type: text/plain; charset=utf-8');
echo $summary . "\n";
