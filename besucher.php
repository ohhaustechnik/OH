<?php
/**
 * Besucher-Zaehler (DSGVO-freundlich: KEINE personenbezogenen Daten, nur Zaehler).
 * Landing-Pages binden ein unsichtbares Pixel ein: <img src="besucher.php?q=lp-altbau">
 * Speichert daten/besucher.json: { "YYYY-MM-DD": { "quelle": anzahl } }
 */
$DATA = __DIR__ . '/daten'; if (!is_dir($DATA)) $DATA = __DIR__;
$F = $DATA . '/besucher.json';
$q = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['q'] ?? 'website'));
if ($q === '') $q = 'website';
$tag = date('Y-m-d');

$d = @json_decode(@file_get_contents($F), true); if (!is_array($d)) $d = [];
if (!isset($d[$tag])) $d[$tag] = [];
$d[$tag][$q] = (int)($d[$tag][$q] ?? 0) + 1;
// nur die letzten 120 Tage behalten
if (count($d) > 120) { ksort($d); $d = array_slice($d, -120, null, true); }
@file_put_contents($F, json_encode($d, JSON_UNESCAPED_UNICODE), LOCK_EX);

// 1x1 transparentes GIF zurueck
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
