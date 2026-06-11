<?php
/**
 * OH Haustechnik – Google Ads Monitor (Cronjob)
 * Zieht täglich die Kampagnen-Zahlen, lässt sie von der KI auswerten und
 * schickt dem Chef eine kurze Zusammenfassung + Empfehlungen per E-Mail.
 *
 * Aufruf z.B. 1x täglich:
 *   https://oh-haustechnik.de/ads-monitor.php?key=oh-cron
 */

require_once __DIR__ . '/includes/buero-lib.php';

$CRON_KEY = oh_config()['cron_key'] ?? 'oh-cron';
if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== $CRON_KEY) { http_response_code(403); exit('Zugriff verweigert.'); }
    header('Content-Type: text/plain; charset=utf-8');
}

$err = null;
$rep = oh_ads_report($err);
if ($rep === null) {
    $msg = '[' . date('Y-m-d H:i') . '] Ads-Monitor Fehler: ' . $err;
    @file_put_contents(OH_DATA_DIR . '/ads.log', $msg . "\n", FILE_APPEND);
    echo $msg . "\n";
    exit;
}

// Bericht als Text für die KI aufbereiten
$s = $rep['summe'];
$txt = "Google-Ads-Zahlen der letzten 7 Tage:\n";
$txt .= "Gesamt: Kosten " . number_format($s['kosten'], 2, ',', '.') . " €, "
      . "Klicks {$s['klicks']}, Anfragen {$s['conv']}, "
      . "Kosten je Anfrage " . ($s['cpl'] !== null ? number_format($s['cpl'], 2, ',', '.') . ' €' : '–') . "\n\nKampagnen:\n";
foreach ($rep['kampagnen'] as $k) {
    $txt .= "- {$k['name']} ({$k['status']}): " . number_format($k['kosten'], 2, ',', '.') . " €, "
          . "{$k['klicks']} Klicks, {$k['conv']} Anfragen, CTR {$k['ctr']}%, CPC " . number_format($k['cpc'], 2, ',', '.') . " €\n";
}

$system = "Du bist der Google-Ads-Experte von OH Haustechnik (Elektriker Nürnberg, Kleinunternehmer). "
        . "Analysiere die Zahlen knackig und praxisnah für den Chef (Du-Form). Sag in maximal 8 Sätzen: "
        . "Was läuft gut? Wo wird Geld verbrannt? Welche 1-3 konkreten Schritte heute? Keine Floskeln, nur Klartext.";
$analyse = oh_ki($system, $txt, 700);
if (!$analyse) $analyse = "(KI-Analyse nicht verfügbar – Anthropic-Schlüssel prüfen.)\n\n" . $txt;

// E-Mail an den Chef
$cfg = oh_config();
$empfaenger = $cfg['gmail_user'] ?? 'oh.haustechnik@gmail.com';
$body = "Dein täglicher Google-Ads-Check\n==============================\n\n" . $analyse . "\n\n---\nZahlen:\n" . $txt;
$res = oh_send_mail($empfaenger, 'Google Ads Tagescheck – OH Haustechnik', $body, $empfaenger);

$log = '[' . date('Y-m-d H:i') . '] Ads-Monitor: Kosten ' . $s['kosten'] . '€, ' . $s['conv'] . ' Anfragen, Mail ' . ($res['ok'] ? 'ok' : 'FEHLER: ' . $res['info']);
@file_put_contents(OH_DATA_DIR . '/ads.log', $log . "\n", FILE_APPEND);
echo $log . "\n";
