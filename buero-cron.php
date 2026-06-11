<?php
/**
 * OH Haustechnik – Automatik (Cronjob)
 * Wird zeitgesteuert von all-inkl aufgerufen, z.B. 1× täglich:
 *   /usr/bin/php /pfad/zu/buero-cron.php
 * oder per URL-Aufruf mit Schlüssel:
 *   https://deine-domain.de/buero-cron.php?key=DEIN_CRON_SCHLUESSEL
 *
 * Aufgaben:
 *  - Follow-up-E-Mail 2 Tage nach Angebot (wenn noch keine Antwort/Abschluss)
 *  - Bewertungs-Anfrage 5 Tage nach Abschluss
 *
 * Voraussetzung: Anthropic-Key + Gmail-App-Passwort im Büro (Einstellungen) hinterlegt.
 */

require_once __DIR__ . '/includes/buero-lib.php';

// Einfacher Schutz bei URL-Aufruf
$CRON_KEY = oh_config()['cron_key'] ?? 'oh-cron';
if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== $CRON_KEY) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$now = time();

// Postfach (ungelesene E-Mails) + Website-Erreichbarkeit aktualisieren
if (function_exists('oh_inbox_scan')) oh_inbox_scan();

$leads = oh_read('leads', []);
$log = [];
$cfg = oh_config();
$absender = $cfg['gmail_user'] ?? 'oh.haustechnik@gmail.com';

foreach ($leads as $l) {
    $email = $l['email'] ?? '';
    if (!$email) continue;
    $name = $l['name'] ?: 'Kunde';
    $vorname = trim(explode(' ', $name)[0]);

    // 1) Follow-up 2 Tage nach Angebot
    if (($l['status'] ?? '') === 'angebot_raus'
        && !empty($l['angebot_ts'])
        && ($now - $l['angebot_ts']) >= 2 * 86400) {

        $body = oh_ki(
            "Du schreibst als OH Haustechnik (Elektriker Nürnberg, persönlich, freundlich, kein Druck) eine kurze Follow-up-E-Mail an einen Kunden, dem vor 2 Tagen ein Angebot geschickt wurde und der noch nicht geantwortet hat. Ziel: freundlich nachfragen, ob Fragen offen sind, Termin anbieten. Max 8 Sätze, mit Anrede 'Hallo {$vorname},' und Grußformel 'Viele Grüße\\nOH Haustechnik'. NUR der E-Mail-Text.",
            "Kunde: {$name}. Leistung: " . ($l['kategorie'] ?? '-') . ". Ort: " . ($l['ort'] ?? '-')
        );
        if ($body) {
            $res = oh_send_mail($email, 'Kurze Nachfrage zu Ihrem Angebot – OH Haustechnik', $body, $absender);
            if (!empty($res['ok'])) {
                oh_update_lead($l['id'], ['status' => 'nachgefasst'], 'Automatik: Follow-up-E-Mail gesendet');
                if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Follow-up-E-Mail automatisch gesendet an ' . $name);
                $log[] = "Follow-up an {$email} ({$name})";
            } else {
                $log[] = "FEHLER Follow-up {$email}: " . $res['info'];
            }
        }
        continue;
    }

    // 2) Bewertungs-Anfrage 5 Tage nach Abschluss
    if (in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen'])
        && empty($l['bewertung_angefragt'])
        && !empty($l['abschluss_ts'])
        && ($now - $l['abschluss_ts']) >= 5 * 86400) {

        $body = oh_ki(
            "Du schreibst als OH Haustechnik eine kurze, herzliche E-Mail, die einen zufriedenen Kunden um eine Google-Bewertung bittet (5 Tage nach Projektabschluss). Persönlich, dankbar, kein Druck. Bitte den Hinweis einbauen, dass eine kurze Bewertung sehr hilft. Anrede 'Hallo {$vorname},', Grußformel 'Viele Grüße\\nOH Haustechnik'. NUR der E-Mail-Text.",
            "Kunde: {$name}. Erledigte Leistung: " . ($l['kategorie'] ?? '-')
        );
        if ($body) {
            $res = oh_send_mail($email, 'Wie zufrieden waren Sie? – OH Haustechnik', $body, $absender);
            if (!empty($res['ok'])) {
                oh_update_lead($l['id'], ['bewertung_angefragt' => true], 'Automatik: Bewertungs-Anfrage gesendet');
                if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Bewertungs-Anfrage automatisch gesendet an ' . $name);
                $log[] = "Bewertungs-Anfrage an {$email} ({$name})";
            } else {
                $log[] = "FEHLER Bewertung {$email}: " . $res['info'];
            }
        }
    }
}

/* --------------------------------------------------------------------------
 * TEAM-AUTONOMIE: Bei jedem Lauf (z.B. stündlich) denkt das Team selbst –
 * Geschäftsführer Mert setzt die Tagesprioritäten, danach stimmt sich das
 * vernetzte Agenten-Team ab (jeder prüft seinen Bereich, gibt Wichtiges an
 * Kollegen weiter). Beides schreibt ins Gedächtnis der Agenten.
 * Benötigt einen hinterlegten Anthropic-Schlüssel – fehlt er, wird es nur
 * sauber protokolliert (kein Abbruch des Cron-Laufs).
 * ------------------------------------------------------------------------ */
// Nachrichten-Triage: eingehende E-Mails & WhatsApp einstufen und in die Freigaben legen
if (function_exists('oh_msg_triage')) {
    $terr = null;
    $tr = oh_msg_triage($terr);
    if (!empty($tr['neu'])) $log[] = 'Triage: ' . $tr['neu'] . ' neue Nachricht(en) eingestuft';
    elseif ($terr) $log[] = 'Triage FEHLER: ' . $terr;
}

if (function_exists('oh_mert_briefing')) {
    $merr = null;
    if (oh_mert_briefing($merr)) $log[] = 'Mert: Tagesplan aktualisiert';
    elseif ($merr) $log[] = 'Mert FEHLER: ' . $merr;
}
if (function_exists('oh_agenten_runde')) {
    $aerr = null;
    $runde = oh_agenten_runde($aerr);
    if ($runde) $log[] = 'Agenten-Runde ok (' . count($runde['nachrichten'] ?? []) . ' Nachrichten)';
    elseif ($aerr) $log[] = 'Agenten-Runde FEHLER: ' . $aerr;
}

$summary = '[' . date('Y-m-d H:i') . '] Cron-Lauf: ' . (count($log) ? implode('; ', $log) : 'nichts zu tun');
@file_put_contents(OH_DATA_DIR . '/cron.log', $summary . "\n", FILE_APPEND);
echo $summary . "\n";
