<?php
/**
 * OH Haustechnik – Selbst-Update
 * Holt die neuesten Büro-Dateien direkt von GitHub auf den Server.
 * So kannst Du vom Handy aus aktualisieren – ohne FTP/Laptop.
 *
 *   https://oh-haustechnik.de/update.php?key=oh-cron
 */
require_once __DIR__ . '/includes/buero-lib.php';

$CRON_KEY = oh_config()['cron_key'] ?? 'oh-cron';
if (($_GET['key'] ?? '') !== $CRON_KEY) { http_response_code(403); exit('Zugriff verweigert.'); }
header('Content-Type: text/plain; charset=utf-8');

$err = null;
$log = oh_self_update($err);
echo "OH Büro – Update von GitHub\n===========================\n";
echo implode("\n", $log) . "\n";
