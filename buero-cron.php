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
                $log[] = "Bewertungs-Anfrage an {$email} ({$name})";
            } else {
                $log[] = "FEHLER Bewertung {$email}: " . $res['info'];
            }
        }
    }
}

$summary = '[' . date('Y-m-d H:i') . '] Cron-Lauf: ' . (count($log) ? implode('; ', $log) : 'nichts zu tun');
@file_put_contents(OH_DATA_DIR . '/cron.log', $summary . "\n", FILE_APPEND);
echo $summary . "\n";
