<?php
/**
 * OH Haustechnik – Kontaktformular Handler
 * Verarbeitet Anfragen via AJAX (JSON Response)
 */

header('Content-Type: application/json; charset=utf-8');

// Nur POST-Anfragen erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Methode nicht erlaubt.']);
    exit;
}

// ---------------------------------------------------------------
// Eingaben bereinigen
// ---------------------------------------------------------------
function clean(string $value): string {
    return trim(strip_tags(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')));
}

$name    = clean($_POST['name']    ?? '');
$email   = clean($_POST['email']   ?? '');
$telefon = clean($_POST['telefon'] ?? '');
$betreff = clean($_POST['betreff'] ?? '');
$nachricht = clean($_POST['nachricht'] ?? '');
$datenschutz = isset($_POST['datenschutz']) ? true : false;

// ---------------------------------------------------------------
// Validierung
// ---------------------------------------------------------------
$errors = [];

if (empty($name) || mb_strlen($name) < 2) {
    $errors[] = 'Bitte geben Sie Ihren Namen ein.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
}

if (empty($nachricht) || mb_strlen($nachricht) < 10) {
    $errors[] = 'Bitte beschreiben Sie Ihr Anliegen (mindestens 10 Zeichen).';
}

if (!$datenschutz) {
    $errors[] = 'Bitte stimmen Sie der Datenschutzerklärung zu.';
}

// Honeypot-Spam-Schutz
if (!empty($_POST['website'])) {
    echo json_encode(['success' => false, 'message' => 'Anfrage konnte nicht verarbeitet werden.']);
    exit;
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// ---------------------------------------------------------------
// E-Mail senden
// ---------------------------------------------------------------
$to      = 'info@oh-haustechnik.de'; // ← BITTE ANPASSEN
$subject = '=?UTF-8?B?' . base64_encode('Neue Anfrage über Website: ' . ($betreff ?: 'Allgemeine Anfrage')) . '?=';

$body  = "Neue Kontaktanfrage über die Website OH Haustechnik\n";
$body .= "=======================================================\n\n";
$body .= "Name:      {$name}\n";
$body .= "E-Mail:    {$email}\n";
$body .= "Telefon:   " . (!empty($telefon) ? $telefon : 'nicht angegeben') . "\n";
$body .= "Betreff:   " . (!empty($betreff) ? $betreff : 'nicht angegeben') . "\n\n";
$body .= "Nachricht:\n{$nachricht}\n\n";
$body .= "-------------------------------------------------------\n";
$body .= "Gesendet am: " . date('d.m.Y \u\m H:i') . " Uhr\n";
$body .= "IP-Adresse: " . ($_SERVER['REMOTE_ADDR'] ?? 'unbekannt') . "\n";

$headers  = "From: noreply@oh-haustechnik.de\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Lead im Büro-System speichern (für Dashboard & Automatik)
@require_once __DIR__ . '/buero-lib.php';
if (function_exists('oh_add_lead')) {
    oh_add_lead([
        'source'    => 'kontakt',
        'name'      => $name,
        'email'     => $email,
        'telefon'   => $telefon,
        'kategorie' => $betreff,
        'details'   => $nachricht,
    ]);
}

$sent = mail($to, $subject, $body, $headers);

// Bestätigungs-E-Mail an den Absender
if ($sent) {
    $confirm_subject = '=?UTF-8?B?' . base64_encode('Ihre Anfrage bei OH Haustechnik – Bestätigung') . '?=';
    $confirm_body    = "Hallo {$name},\n\n";
    $confirm_body   .= "vielen Dank für Ihre Anfrage.\n\n";
    $confirm_body   .= "Wir haben Ihre Nachricht erhalten und melden uns schnellstmöglich bei Ihnen.\n\n";
    $confirm_body   .= "Ihre Angaben:\n";
    $confirm_body   .= "Betreff: " . (!empty($betreff) ? $betreff : 'Allgemeine Anfrage') . "\n\n";
    $confirm_body   .= "Mit freundlichen Grüßen\n";
    $confirm_body   .= "OH Haustechnik\n";
    $confirm_body   .= "info@oh-haustechnik.de\n";

    $confirm_headers  = "From: info@oh-haustechnik.de\r\n";
    $confirm_headers .= "MIME-Version: 1.0\r\n";
    $confirm_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($email, $confirm_subject, $confirm_body, $confirm_headers);
}

// ---------------------------------------------------------------
// Antwort
// ---------------------------------------------------------------
if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt. Wir melden uns in Kürze.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Die E-Mail konnte leider nicht gesendet werden. Bitte rufen Sie uns direkt an.'
    ]);
}
