<?php
/* OH Haustechnik – Formularversand für Startseite, Landingpage und Kontaktseite.
   Versand über oh_send_mail() (authentifiziertes Gmail-SMTP). Wichtig: NICHT
   per PHP-mail() mit From-Adresse @gmail.com verschicken – der Webserver darf
   nicht im Namen von gmail.com senden, SPF schlägt fehl und Gmail verwirft
   die Nachricht. Genau daran sind die Anfragen vorher gescheitert. */
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

$to      = 'oh.haustechnik@gmail.com';
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
$body .= "Seite:     " . clean($_SERVER['HTTP_REFERER'] ?? '-') . "\n";

$replyTo = ($email !== '') ? $email : null;

// --- Versand über die bewährte SMTP-Funktion, Fallback auf mail() ---
$gesendet = false; $info = '';
$lib = __DIR__ . '/includes/buero-lib.php';
if (is_file($lib)) {
    require_once $lib;
}
if (function_exists('oh_send_mail')) {
    $res      = oh_send_mail($to, $subject, $body, $replyTo);
    $gesendet = !empty($res['ok']);
    $info     = $res['info'] ?? '';
} else {
    // Fallback: eigene Domain als Absender (NICHT @gmail.com – siehe oben)
    $headers  = "From: OH Haustechnik <noreply@oh-haustechnik.de>\r\n";
    if ($replyTo) $headers .= 'Reply-To: ' . clean($replyTo) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $gesendet = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    $info     = $gesendet ? 'via mail()' : 'mail() fehlgeschlagen';
}

// --- Eingangsbestätigung an den Kunden (nur wenn E-Mail angegeben) ---
if ($email !== '' && function_exists('oh_send_mail')) {
    $kBody  = "Hallo $name,\n\n";
    $kBody .= "vielen Dank für Ihre Anfrage bei OH Haustechnik.\n";
    $kBody .= "Wir haben Ihre Angaben erhalten und melden uns zeitnah bei Ihnen.\n\n";
    if ($leistung !== '') $kBody .= "Ihre Anfrage: $leistung\n";
    if ($vorhaben !== '') $kBody .= "Ihr Hinweis:  $vorhaben\n";
    $kBody .= "\nWenn es eilt, erreichen Sie uns direkt unter 0175 7481006.\n\n";
    $kBody .= "Freundliche Grüße\n";
    $kBody .= "OH Haustechnik · Onur-Can Hezer\n";
    $kBody .= "Dianastraße 62, 90441 Nürnberg\n";
    $kBody .= "https://oh-haustechnik.de\n";
    @oh_send_mail($email, 'Ihre Anfrage bei OH Haustechnik', $kBody, $to);
}

// --- Anfrage IMMER lokal protokollieren, auch wenn der Mailversand scheitert ---
@file_put_contents(
    __DIR__ . '/anfragen.log',
    date('c') . " | " . ($gesendet ? 'MAIL-OK' : 'MAIL-FEHLER') . " ($info)"
    . " | $subject | Name: $name | Tel: $telefon | Mail: $email | $plz $ort | $vorhaben\n",
    FILE_APPEND | LOCK_EX
);

// Für den Nutzer als Erfolg werten, sobald protokolliert/gesendet
out(true);
