<?php
/**
 * OH Haustechnik – Multi-Step Funnel Handler
 * Verarbeitet alle 7 Schritte des Anfrage-Funnels
 */

header('Content-Type: application/json; charset=utf-8');

// Robustheit: niemals PHP-Warnungen/Notices ins JSON lecken (sonst „Fehlercode" für den Kunden).
@ini_set('display_errors', '0');
ob_start();
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Es ist ein technischer Fehler aufgetreten. Bitte rufen Sie uns kurz an – wir sind sofort für Sie da.']);
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Methode nicht erlaubt.']);
    exit;
}

// ---------------------------------------------------------------
// Honeypot
// ---------------------------------------------------------------
if (!empty($_POST['website'])) {
    echo json_encode(['success' => false, 'message' => 'Anfrage konnte nicht verarbeitet werden.']);
    exit;
}

// ---------------------------------------------------------------
// Hilfsfunktionen
// ---------------------------------------------------------------
function clean(string $value): string {
    return trim(strip_tags(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')));
}

function cleanMultiline(string $value): string {
    return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
}

// ---------------------------------------------------------------
// Eingaben einlesen & bereinigen
// ---------------------------------------------------------------

// Schritt 1
$kategorie   = clean($_POST['kategorie']   ?? '');

// Smart Home, Photovoltaik und Wallbox - Angaben aus Schritt 2
$shSystem    = clean($_POST['sh_system']    ?? '');
$shBau       = clean($_POST['sh_bau']       ?? '');
$pvDach      = clean($_POST['pv_dach']      ?? '');
$pvSpeicher  = clean($_POST['pv_speicher']  ?? '');
$pvZaehler   = clean($_POST['pv_zaehler']   ?? '');
$pvFlaeche   = clean($_POST['pv_flaeche']   ?? '');
$pvVerbrauch = clean($_POST['pv_verbrauch'] ?? '');
$wbLeistung  = clean($_POST['wb_leistung']  ?? '');
$wbOrt       = clean($_POST['wb_ort']       ?? '');
$wbZaehler   = clean($_POST['wb_zaehler']   ?? '');
$suboptionen = clean($_POST['suboptionen'] ?? '');
$lampenAnzahl= clean($_POST['lampen_anzahl'] ?? '');

// Schritt 2
$objekttyp     = clean($_POST['objekttyp'] ?? '');
$zimmerAnzahl  = clean($_POST['zimmer_anzahl'] ?? '');
$objektgroesse = clean($_POST['objektgroesse'] ?? '');

// Schritt 3
$ausfuehrungszeit = clean($_POST['ausfuehrungszeit'] ?? '');

// Schritt 4
$erreichbarkeit = clean($_POST['erreichbarkeit'] ?? '');
$kontaktweg     = clean($_POST['kontaktweg']     ?? '');

// Schritt 5
$plz     = clean($_POST['plz']     ?? '');
$ort     = clean($_POST['ort']     ?? '');
$strasse = clean($_POST['strasse'] ?? '');

// Schritt 6
$details = cleanMultiline($_POST['details'] ?? '');

// Schritt 7
$vorname     = clean($_POST['vorname']     ?? '');
$nachname    = clean($_POST['nachname']    ?? '');
$email       = clean($_POST['email']       ?? '');
$telefon     = clean($_POST['telefon']     ?? '');
$datenschutz = !empty($_POST['datenschutz']) && $_POST['datenschutz'] === '1';

// Marketing-Quelle (google-ads bei Anzeigen-Klick, sonst website/utm-*)
$quelle = clean($_POST['quelle'] ?? '');
if ($quelle === '' || mb_strlen($quelle) > 40) $quelle = 'website';

// ---------------------------------------------------------------
// Validierung
// ---------------------------------------------------------------
$errors = [];

if (empty($kategorie)) {
    $errors[] = 'Kategorie fehlt.';
}
if (empty($objektgroesse)) {
    $errors[] = 'Objektgröße fehlt.';
}
if (empty($ausfuehrungszeit)) {
    $errors[] = 'Ausführungszeit fehlt.';
}
if (empty($erreichbarkeit)) {
    $errors[] = 'Erreichbarkeit fehlt.';
}
if (empty($plz) || !preg_match('/^\d{4,5}$/', $plz)) {
    $errors[] = 'Bitte geben Sie eine gültige Postleitzahl ein.';
}
if (empty($vorname)) {
    $errors[] = 'Vorname fehlt.';
}
if (empty($nachname)) {
    $errors[] = 'Nachname fehlt.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
}
if (empty($telefon)) {
    $errors[] = 'Telefonnummer fehlt.';
}
if (!$datenschutz) {
    $errors[] = 'Bitte stimmen Sie der Datenschutzerklärung zu.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ---------------------------------------------------------------
// Kategorie-Label
// ---------------------------------------------------------------
$kategorieLabels = [
    'elektro'   => 'Elektroinstallation & Sanierung',
    'netzwerk'  => 'Netzwerkverkabelung',
    'fehler'    => 'Fehlersuche & Reparatur',
    'lampen'    => 'Lampen & Leuchtenmontage',
    'smarthome' => 'Smart Home (KNX / Loxone)',
    'pv'        => 'Photovoltaik',
    'wallbox'   => 'Wallbox',
];
$kategorieLabel = $kategorieLabels[$kategorie] ?? $kategorie;

// ---------------------------------------------------------------
// Foto-Anhänge verarbeiten
// ---------------------------------------------------------------
$attachments = [];
$fotoErrors  = [];

if (!empty($_FILES['fotos']['name'][0])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($_FILES['fotos']['size'][$i] > $maxSize) {
            $fotoErrors[] = 'Datei zu groß: ' . basename($_FILES['fotos']['name'][$i]);
            continue;
        }
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);
        if (!in_array($mimeType, $allowedTypes)) {
            $fotoErrors[] = 'Ungültiger Dateityp: ' . basename($_FILES['fotos']['name'][$i]);
            continue;
        }
        $attachments[] = [
            'tmp'   => $tmpName,
            'name'  => basename($_FILES['fotos']['name'][$i]),
            'mime'  => $mimeType,
            'size'  => $_FILES['fotos']['size'][$i],
            'data'  => base64_encode(file_get_contents($tmpName)),
        ];
    }
}

// ---------------------------------------------------------------
// Hauptmail an OH Haustechnik aufbauen
// ---------------------------------------------------------------
$to       = 'oh.Haustechnik@gmail.com';
$subject  = '=?UTF-8?B?' . base64_encode('Neue Angebotsanfrage: ' . $kategorieLabel . ' – ' . $vorname . ' ' . $nachname) . '?=';

$standort = $plz;
if (!empty($ort))     $standort .= ' ' . $ort;
if (!empty($strasse)) $standort .= ', ' . $strasse;

// ---------------------------------------------------------------
// Lead im Büro-System speichern (für Dashboard, HOT/WARM/KALT, Automatik)
// ---------------------------------------------------------------
@require_once __DIR__ . '/buero-lib.php';
$convValue   = 10;        // Fallback-Conversion-Wert
$convKlasse  = '';
if (function_exists('oh_add_lead')) {
    $neuerLead = oh_add_lead([
        'source'        => $quelle,
        'name'          => trim($vorname . ' ' . $nachname),
        'email'         => $email,
        'telefon'       => $telefon,
        'kategorie'     => $kategorieLabel,
        'objekttyp'     => $objekttyp,
        'zimmer'        => $zimmerAnzahl,
        'objektgroesse' => $objektgroesse,
        'zeitraum'      => $ausfuehrungszeit,
        'plz'           => $plz,
        'ort'           => $ort,
        'details'       => trim(($suboptionen ? $suboptionen . '. ' : '') . $details),
    ]);
    // Geschätzten Auftragswert für value-based Bidding in Google Ads ableiten.
    // So lernt Ads, welche Kampagnen echte Großaufträge bringen – nicht nur Klicks.
    $convKlasse = $neuerLead['wert_klasse'] ?? '';
    $wertMap    = ['gross' => 8000, 'mittel' => 2500, 'klein' => 400];
    $convValue  = $wertMap[$convKlasse] ?? 1000;
}

// Boundary für Multipart (nur wenn Anhänge vorhanden)
$boundary = md5(uniqid(time(), true));

$body = "Neue Angebotsanfrage über den Website-Funnel\n";
$body .= "=====================================================\n\n";
$body .= "📋 SCHRITT 1 – LEISTUNGSKATEGORIE\n";
$body .= "-----------------------------------\n";
$body .= "Kategorie:      {$kategorieLabel}\n";
if (!empty($suboptionen)) {
    $body .= "Details:        {$suboptionen}\n";
}
if (!empty($lampenAnzahl) && $kategorie === 'lampen') {
    $body .= "Lampenanzahl:   ca. {$lampenAnzahl} Stück\n";
}
if ($kategorie === 'smarthome') {
    if ($shSystem !== '') $body .= "System:         {$shSystem}\n";
    if ($shBau !== '')    $body .= "Bausituation:   {$shBau}\n";
}
if ($kategorie === 'pv') {
    if ($pvDach !== '')      $body .= "Dachart:        {$pvDach}\n";
    if ($pvFlaeche !== '')   $body .= "Dachfläche:     ca. {$pvFlaeche} m²\n";
    if ($pvSpeicher !== '')  $body .= "Speicher:       {$pvSpeicher}\n";
    if ($pvVerbrauch !== '') $body .= "Verbrauch:      ca. {$pvVerbrauch} kWh/Jahr\n";
    if ($pvZaehler !== '')   $body .= "Zählerschrank:  {$pvZaehler}\n";
}
if ($kategorie === 'wallbox') {
    if ($wbLeistung !== '') $body .= "Ladeleistung:   {$wbLeistung}\n";
    if ($wbOrt !== '')      $body .= "Montageort:     {$wbOrt}\n";
    if ($wbZaehler !== '')  $body .= "Zählerschrank:  {$wbZaehler}\n";
}
$body .= "\n📐 SCHRITT 2 – OBJEKT\n";
$body .= "-----------------------------------\n";
$body .= "Objekttyp:      " . ($objekttyp !== '' ? $objekttyp : '–') . "\n";
$body .= "Anzahl Zimmer:  " . ($zimmerAnzahl !== '' ? $zimmerAnzahl : '–') . "\n";
$body .= "Fläche:         {$objektgroesse}\n";

$body .= "\n📅 SCHRITT 3 – AUSFÜHRUNGSZEIT\n";
$body .= "-----------------------------------\n";
$body .= "Zeitraum:       {$ausfuehrungszeit}\n";

$body .= "\n📞 SCHRITT 4 – ERREICHBARKEIT\n";
$body .= "-----------------------------------\n";
$body .= "Erreichbar:     {$erreichbarkeit}\n";
$body .= "Kontakt via:    {$kontaktweg}\n";

$body .= "\n📍 SCHRITT 5 – STANDORT\n";
$body .= "-----------------------------------\n";
$body .= "PLZ / Ort:      {$standort}\n";

if (!empty($details)) {
    $body .= "\n💬 SCHRITT 6 – WEITERE DETAILS\n";
    $body .= "-----------------------------------\n";
    $body .= $details . "\n";
}

$body .= "\n👤 SCHRITT 7 – KONTAKTDATEN\n";
$body .= "-----------------------------------\n";
$body .= "Name:           {$vorname} {$nachname}\n";
$body .= "E-Mail:         {$email}\n";
$body .= "Telefon:        {$telefon}\n";

if (!empty($attachments)) {
    $body .= "\n📎 FOTOS:        " . count($attachments) . " Datei(en) angehängt\n";
}
if (!empty($fotoErrors)) {
    $body .= "\n⚠️ Foto-Fehler: " . implode(', ', $fotoErrors) . "\n";
}

$body .= "\n=====================================================\n";
$body .= "Gesendet am:    " . date('d.m.Y \u\m H:i') . " Uhr\n";
$body .= "IP-Adresse:     " . ($_SERVER['REMOTE_ADDR'] ?? 'unbekannt') . "\n";

// ---------------------------------------------------------------
// E-Mail senden (mit oder ohne Anhänge)
// ---------------------------------------------------------------
if (!empty($attachments)) {
    // Multipart MIME E-Mail
    $headers  = "From: noreply@oh-haustechnik.de\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $body . "\r\n";

    foreach ($attachments as $att) {
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: {$att['mime']}; name=\"{$att['name']}\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"{$att['name']}\"\r\n\r\n";
        $message .= chunk_split($att['data']) . "\r\n";
    }

    $message .= "--{$boundary}--";
    $sent = mail($to, $subject, $message, $headers);

    // Sicherheitsnetz: Wenn mail() den Anhang nicht rausbekommt, geht die
    // Anfrage wenigstens als Textmail ueber authentifiziertes SMTP raus.
    if (!$sent && function_exists('oh_send_mail')) {
        $hinweis = $body . "\n\n(Hinweis: " . count($attachments)
                 . " Foto(s) konnten nicht angehaengt werden - bitte beim Kunden nachfragen.)\n";
        $res  = oh_send_mail($to, 'Neue Angebotsanfrage (ohne Fotos)', $hinweis, $email ?: null);
        $sent = !empty($res['ok']);
    }

} else {
    // Einfache Plaintext-Mail: bevorzugt ueber authentifiziertes SMTP,
    // weil PHP-mail() je nach Serverkonfiguration still verworfen wird.
    if (function_exists('oh_send_mail')) {
        $res  = oh_send_mail($to, 'Neue Angebotsanfrage: ' . $kategorieLabel
                                  . ' – ' . $vorname . ' ' . $nachname,
                             $body, $email ?: null);
        $sent = !empty($res['ok']);
    } else {
        $headers  = "From: OH Haustechnik <noreply@oh-haustechnik.de>\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $sent = mail($to, $subject, $body, $headers);
    }
}

// ---------------------------------------------------------------
// Auto-Reply an den Kunden
// ---------------------------------------------------------------
if ($sent) {
    $confirmSubject = '=?UTF-8?B?' . base64_encode('Ihre Anfrage bei OH Haustechnik – Bestätigung') . '?=';

    $confirmBody  = "Hallo {$vorname},\n\n";
    $confirmBody .= "vielen Dank für Ihre Anfrage bei OH Haustechnik!\n\n";
    $confirmBody .= "Wir haben Ihre Angaben erhalten und melden uns innerhalb von 24 Stunden bei Ihnen.\n";
    $confirmBody .= "Bevorzugter Kontaktweg: {$kontaktweg}\n\n";
    $confirmBody .= "Ihre Anfrage im Überblick:\n";
    $confirmBody .= "- Leistung:      {$kategorieLabel}\n";
    if (!empty($suboptionen)) {
        $confirmBody .= "- Details:       {$suboptionen}\n";
    }
    if ($objekttyp !== '')    { $confirmBody .= "- Objekt:        {$objekttyp}\n"; }
    if ($zimmerAnzahl !== '') { $confirmBody .= "- Anzahl Zimmer: {$zimmerAnzahl}\n"; }
    $confirmBody .= "- Fläche:        {$objektgroesse}\n";
    $confirmBody .= "- Zeitraum:      {$ausfuehrungszeit}\n";
    $confirmBody .= "- Standort:      {$standort}\n\n";
    $confirmBody .= "Bei Fragen erreichen Sie uns unter:\n";
    $confirmBody .= "📧  oh.Haustechnik@gmail.com\n\n";
    $confirmBody .= "Mit freundlichen Grüßen\n";
    $confirmBody .= "OH Haustechnik\n";
    $confirmBody .= "Elektroinstallation & Netzwerkverkabelung Nürnberg\n";
    $confirmBody .= "https://oh-haustechnik.de\n";

    // Bestaetigung an den Kunden: ueber authentifiziertes SMTP. Frueher ging das
    // per mail() mit From @gmail.com raus - dafuer ist der Webserver nicht
    // berechtigt (SPF), solche Mails werden von Gmail & Co. verworfen.
    if (function_exists('oh_send_mail')) {
        oh_send_mail($email, 'Ihre Anfrage bei OH Haustechnik – Bestätigung',
                     $confirmBody, 'oh.haustechnik@gmail.com');
    } else {
        $confirmHeaders  = "From: OH Haustechnik <noreply@oh-haustechnik.de>\r\n";
        $confirmHeaders .= "Reply-To: oh.haustechnik@gmail.com\r\n";
        $confirmHeaders .= "MIME-Version: 1.0\r\n";
        $confirmHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $confirmHeaders .= "Content-Transfer-Encoding: 8bit\r\n";
        $confirmSubjectEncoded = '=?UTF-8?B?' . base64_encode('Ihre Anfrage bei OH Haustechnik – Bestätigung') . '?=';
        mail($email, $confirmSubjectEncoded, $confirmBody, $confirmHeaders);
    }
}

// ---------------------------------------------------------------
// Antwort
// ---------------------------------------------------------------
if ($sent) {
    echo json_encode([
        'success'     => true,
        'message'     => 'Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt. Wir melden uns innerhalb von 24 Stunden.',
        'value'       => $convValue,        // geschätzter Auftragswert (EUR) für Ads-Conversion
        'wert_klasse' => $convKlasse,       // gross | mittel | klein
        'currency'    => 'EUR'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Die E-Mail konnte leider nicht gesendet werden. Bitte rufen Sie uns direkt an oder schreiben Sie uns an oh.Haustechnik@gmail.com.'
    ]);
}
