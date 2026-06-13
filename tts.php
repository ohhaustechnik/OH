<?php
/**
 * TTS-Endpunkt – ElevenLabs-ready.
 *  POST text=...&voice=...  ->  liefert MP3-Audio, WENN in config ein ElevenLabs-Key liegt.
 *  Ohne Key: 204 (leer) -> der Browser nutzt seine eigene Stimme (Fallback).
 *
 * Der grosse Adnan muss spaeter NUR in daten/config.json eintragen:
 *   "elevenlabs_key": "...", "elevenlabs_voice": "<voice_id>"   (voice optional)
 * Dann sprechen die Agenten automatisch mit der ElevenLabs-Stimme. Kein Code-Deploy noetig.
 *
 * Sicherheit: nur eingeloggt. Audio wird gecacht (Hash) -> gleicher Satz nie zweimal bezahlt.
 */
session_start();
require_once __DIR__ . '/includes/buero-lib.php';

if (empty($_SESSION['eingeloggt'])) { http_response_code(403); exit; }

$text = trim((string)($_POST['text'] ?? ''));
if ($text === '') { http_response_code(204); exit; }
$text = mb_substr($text, 0, 800);

$cfg = oh_config();
$key = $cfg['elevenlabs_key'] ?? '';
if ($key === '') { http_response_code(204); exit; } // kein Key -> Browser-Stimme

$voice = $cfg['elevenlabs_voice'] ?? '21m00Tcm4TlvDq8ikWAM'; // Standard-Stimme (Rachel)
$model = $cfg['elevenlabs_model'] ?? 'eleven_multilingual_v2';

// Cache
$cacheDir = (defined('OH_DATA_DIR') ? OH_DATA_DIR : __DIR__ . '/daten') . '/tts_cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
$hash = md5($voice . '|' . $model . '|' . $text);
$cacheFile = $cacheDir . '/' . $hash . '.mp3';
if (is_file($cacheFile) && filesize($cacheFile) > 0) {
    header('Content-Type: audio/mpeg');
    header('Cache-Control: private, max-age=86400');
    readfile($cacheFile);
    exit;
}

// ElevenLabs aufrufen
$body = json_encode([
    'text' => $text,
    'model_id' => $model,
    'voice_settings' => ['stability' => 0.5, 'similarity_boost' => 0.75],
]);
$ch = curl_init('https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voice));
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: audio/mpeg',
        'xi-api-key: ' . $key,
    ],
]);
$audio = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($audio === false || $code !== 200 || strlen($audio) < 200) {
    http_response_code(204); // Fehler -> Browser-Stimme als Fallback
    exit;
}
@file_put_contents($cacheFile, $audio);
header('Content-Type: audio/mpeg');
header('Cache-Control: private, max-age=86400');
echo $audio;
