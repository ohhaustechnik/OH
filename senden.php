<?php
/* OH Haustechnik – Formularversand für die Landingpage (elektriker-nuernberg.html) */
header('Content-Type: application/json; charset=utf-8');

function out($ok, $error = '') { echo json_encode(['ok' => $ok, 'error' => $error]); exit; }
function p($k) { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : ''; }
function clean($s) { return str_replace(["\r", "\n", "\t"], ' ', $s); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Ungültige Anfrage.');

// Spam-Schutz 1: Honeypot (Bots füllen versteckte Felder)
if (p('website') !== '') out(true);           // still verwerfen, aber "ok" zurück
// Spam-Schutz 2: Zeitsperre (Formular in unter 3 s = Bot)
if ((int)p('elapsed') < 3000) out(false, 'Bitte einen Moment und erneut senden.');

$name     = p('name');
$telefon  = p('telefon');
$email    = p('email');
$leistung = p('leistung');
$objekt   = p('objekt');
$vorhaben = p('vorhaben');
$plz      = p('plz');
$ort      = p('ort');

// Pflichtfelder
if ($name === '' || ($telefon === '' && $email === ''))
    out(false, 'Bitte Name und Telefon oder E-Mail angeben.');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
    out(false, 'Die E-Mail-Adresse ist ungültig.');

$to      = 'oh.Haustechnik@gmail.com';
$subject = 'Neue Anfrage: ' . ($leistung !== '' ? $leistung : 'Elektro') . ' – ' . ($ort !== '' ? $ort : $plz);

$body  = "Neue Anfrage über die Website\n";
$body .= "--------------------------------\n";
$body .= "Leistung:  $leistung\n";
$body .= "Objekt:    $objekt\n";
$body .= "Ort:       $plz $ort\n";
$body .= "Vorhaben:  $vorhaben\n\n";
$body .= "Name:      $name\n";
$body .= "Telefon:   $telefon\n";
$body .= "E-Mail:    $email\n";
$body .= "--------------------------------\n";
$body .= "Gesendet:  " . date('d.m.Y H:i') . "\n";

$headers  = 'From: OH Haustechnik <oh.Haustechnik@gmail.com>' . "\r\n";
if ($email !== '') $headers .= 'Reply-To: ' . clean($name) . ' <' . clean($email) . '>' . "\r\n";
$headers .= 'Content-Type: text/plain; charset=utf-8' . "\r\n";
$headers .= 'X-Mailer: OH-Website' . "\r\n";

$subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$sent = @mail($to, $subjectEnc, $body, $headers);

// Fallback: Anfrage immer zusätzlich lokal protokollieren (falls Mail scheitert)
@file_put_contents(
    __DIR__ . '/anfragen.log',
    date('c') . " | $subject | Name: $name | Tel: $telefon | Mail: $email | $plz $ort | $vorhaben\n",
    FILE_APPEND | LOCK_EX
);

// Für den Nutzer als Erfolg werten, sobald protokolliert/gesendet
out(true);
