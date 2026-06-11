<?php
/**
 * OH Haustechnik – Büro-System Bibliothek
 * Lead-Speicher, HOT/WARM/KALT-Klassifizierung, E-Mail-Versand (Gmail SMTP),
 * Konfiguration & KI-Aufrufe. Wird von buero.php, den Formular-Handlern und
 * buero-cron.php gemeinsam genutzt.
 */

if (!defined('OH_DATA_DIR')) {
    define('OH_DATA_DIR', __DIR__ . '/../daten');
}

/* --------------------------------------------------------------------------
 * Datenverzeichnis sicherstellen (+ Schutz vor direktem Webzugriff)
 * ------------------------------------------------------------------------ */
function oh_ensure_data_dir(): void {
    if (!is_dir(OH_DATA_DIR)) {
        @mkdir(OH_DATA_DIR, 0775, true);
    }
    $ht = OH_DATA_DIR . '/.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht, "Require all denied\nDeny from all\n");
    }
}

/* --------------------------------------------------------------------------
 * Generischer JSON-Store mit Dateisperre
 * ------------------------------------------------------------------------ */
function oh_store_path(string $name): string {
    return OH_DATA_DIR . '/' . $name . '.json';
}

function oh_read(string $name, $default = []) {
    oh_ensure_data_dir();
    $path = oh_store_path($name);
    if (!file_exists($path)) return $default;
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') return $default;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

function oh_write(string $name, $data): bool {
    oh_ensure_data_dir();
    $path = oh_store_path($name);
    $fp = @fopen($path, 'c+');
    if (!$fp) return false;
    $ok = false;
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fflush($fp);
        flock($fp, LOCK_UN);
        $ok = true;
    }
    fclose($fp);
    return $ok;
}

/* --------------------------------------------------------------------------
 * Konfiguration (API-Keys, Gmail-Zugang)  – in daten/config.json
 * ------------------------------------------------------------------------ */
function oh_config(): array {
    $cfg = oh_read('config', []);
    // Umgebungsvariablen als Fallback
    if (empty($cfg['anthropic_key'])) {
        $env = getenv('CLAUDE_KEY');
        if ($env) $cfg['anthropic_key'] = $env;
    }
    return $cfg;
}

function oh_config_set(array $patch): void {
    $cfg = oh_read('config', []);
    foreach ($patch as $k => $v) {
        // Leere Felder überschreiben gespeicherte Werte NICHT (z.B. Passwort leer lassen)
        if ($v === '' || $v === null) continue;
        $cfg[$k] = $v;
    }
    oh_write('config', $cfg);
}

/* --------------------------------------------------------------------------
 * Lead-Verwaltung
 * ------------------------------------------------------------------------ */

/** Stuft eine Anfrage automatisch als HOT / WARM / KALT ein (regelbasiert). */
function oh_classify(array $l): string {
    $txt = mb_strtolower(
        ($l['zeitraum'] ?? '') . ' ' . ($l['details'] ?? '') . ' ' .
        ($l['kategorie'] ?? '') . ' ' . ($l['objektgroesse'] ?? '')
    );
    $hotWords  = ['sofort', 'dringend', 'schnellstmöglich', 'so schnell', 'asap', 'notfall', 'ausfall', 'kein strom', 'diese woche', 'sofortige'];
    $coldWords = ['irgendwann', 'unverbindlich', 'nur eine frage', 'info', 'später', 'nächstes jahr', 'überlege', 'vielleicht', 'grobe schätzung'];

    foreach ($hotWords as $w)  if (mb_strpos($txt, $w) !== false) return 'HOT';
    foreach ($coldWords as $w) if (mb_strpos($txt, $w) !== false) return 'KALT';

    // Große Sanierung + Telefon vorhanden => eher HOT
    $gross = preg_match('/\b(1[0-9]{2,}|[2-9][0-9]{2,})\s?m/u', $txt) || mb_strpos($txt, 'komplett') !== false || mb_strpos($txt, 'sanierung') !== false;
    if ($gross && !empty($l['telefon'])) return 'HOT';
    if ($gross) return 'WARM';

    return !empty($l['telefon']) ? 'WARM' : 'KALT';
}

/** Fügt einen neuen Lead hinzu und gibt ihn (mit ID) zurück. */
function oh_add_lead(array $data): array {
    $leads = oh_read('leads', []);
    $lead = array_merge([
        'id'        => 'L' . date('ymdHis') . substr((string)mt_rand(100, 999), 0, 3),
        'created'   => time(),
        'source'    => 'manuell',
        'name'      => '',
        'email'     => '',
        'telefon'   => '',
        'kategorie' => '',
        'objektgroesse' => '',
        'zeitraum'  => '',
        'plz'       => '',
        'ort'       => '',
        'details'   => '',
        'status'    => 'neu',          // neu | angebot_raus | nachgefasst | gewonnen | abgeschlossen | verloren
        'angebot_ts'=> 0,
        'abschluss_ts' => 0,
        'bewertung_angefragt' => false,
        'notizen'   => '',
        'verlauf'   => [],
    ], $data);

    if (empty($lead['stufe'])) {
        $lead['stufe'] = oh_classify($lead);
    }
    $lead['verlauf'][] = ['ts' => time(), 'text' => 'Lead angelegt (' . $lead['source'] . ', ' . $lead['stufe'] . ')'];

    array_unshift($leads, $lead);
    oh_write('leads', $leads);
    return $lead;
}

function oh_get_lead(string $id): ?array {
    foreach (oh_read('leads', []) as $l) {
        if (($l['id'] ?? '') === $id) return $l;
    }
    return null;
}

function oh_update_lead(string $id, array $patch, ?string $logText = null): ?array {
    $leads = oh_read('leads', []);
    $updated = null;
    foreach ($leads as &$l) {
        if (($l['id'] ?? '') === $id) {
            $l = array_merge($l, $patch);
            if ($logText) $l['verlauf'][] = ['ts' => time(), 'text' => $logText];
            $updated = $l;
            break;
        }
    }
    unset($l);
    if ($updated) oh_write('leads', $leads);
    return $updated;
}

function oh_delete_lead(string $id): void {
    $leads = array_values(array_filter(oh_read('leads', []), function($l) use ($id) { return ($l['id'] ?? '') !== $id; }));
    oh_write('leads', $leads);
}

/* --------------------------------------------------------------------------
 * Dashboard: offene Aufgaben nach Priorität
 * ------------------------------------------------------------------------ */
function oh_dashboard_tasks(): array {
    $now = time();
    $tasks = ['rot' => [], 'gelb' => [], 'gruen' => []];
    foreach (oh_read('leads', []) as $l) {
        $status = $l['status'] ?? 'neu';
        if (in_array($status, ['verloren', 'abgeschlossen'])) {
            // abgeschlossen kann noch Bewertungsanfrage brauchen
        }
        $name = $l['name'] ?: ($l['email'] ?: $l['id']);

        // Neuer HOT-Lead ohne Angebot -> SOFORT
        if ($status === 'neu' && ($l['stufe'] ?? '') === 'HOT') {
            $tasks['rot'][] = ['id' => $l['id'], 'titel' => "🔥 Heißer Lead: $name", 'aktion' => 'Kalkulieren & Angebot raus', 'typ' => 'lead'];
            continue;
        }
        // Angebot raus, 2+ Tage her, noch nicht nachgefasst -> Nachfassen
        if ($status === 'angebot_raus' && $l['angebot_ts'] && ($now - $l['angebot_ts']) >= 2 * 86400) {
            $tasks['rot'][] = ['id' => $l['id'], 'titel' => "⏰ Nachfassen: $name", 'aktion' => 'Follow-up E-Mail senden', 'typ' => 'followup'];
            continue;
        }
        // Abgeschlossen, 5+ Tage, keine Bewertung angefragt -> Bewertung
        if (in_array($status, ['gewonnen', 'abgeschlossen']) && empty($l['bewertung_angefragt']) && $l['abschluss_ts'] && ($now - $l['abschluss_ts']) >= 5 * 86400) {
            $tasks['gelb'][] = ['id' => $l['id'], 'titel' => "⭐ Bewertung anfragen: $name", 'aktion' => 'Bewertungs-E-Mail senden', 'typ' => 'bewertung'];
            continue;
        }
        // Neuer WARM-Lead -> BALD
        if ($status === 'neu' && ($l['stufe'] ?? '') === 'WARM') {
            $tasks['gelb'][] = ['id' => $l['id'], 'titel' => "📋 Anfrage prüfen: $name", 'aktion' => 'Kalkulieren & antworten', 'typ' => 'lead'];
            continue;
        }
        // Neuer KALT-Lead -> KANN WARTEN
        if ($status === 'neu' && ($l['stufe'] ?? '') === 'KALT') {
            $tasks['gruen'][] = ['id' => $l['id'], 'titel' => "💬 Info-Anfrage: $name", 'aktion' => 'Bei Gelegenheit antworten', 'typ' => 'lead'];
            continue;
        }
    }
    return $tasks;
}

/* --------------------------------------------------------------------------
 * E-Mail-Versand: Gmail-SMTP wenn konfiguriert, sonst PHP mail()
 * ------------------------------------------------------------------------ */
function oh_send_mail(string $to, string $subject, string $body, ?string $replyTo = null): array {
    $cfg = oh_config();
    $from = $cfg['gmail_user'] ?? 'oh.haustechnik@gmail.com';

    // SMTP, wenn App-Passwort hinterlegt
    if (!empty($cfg['gmail_user']) && !empty($cfg['gmail_pass'])) {
        return oh_smtp_send($cfg['gmail_user'], $cfg['gmail_pass'], $from, $to, $subject, $body, $replyTo);
    }

    // Fallback: PHP mail()
    $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers  = "From: OH Haustechnik <{$from}>\r\n";
    if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $ok = @mail($to, $subjEnc, $body, $headers);
    return ['ok' => $ok, 'info' => $ok ? 'via mail()' : 'mail() fehlgeschlagen'];
}

/** Minimaler SMTP-Client (STARTTLS) für Gmail – ohne externe Library. */
function oh_smtp_send(string $user, string $pass, string $from, string $to, string $subject, string $body, ?string $replyTo = null): array {
    $host = 'smtp.gmail.com'; $port = 587;
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 20);
    if (!$fp) return ['ok' => false, 'info' => "SMTP-Verbindung fehlgeschlagen: $errstr"];

    $read = function() use ($fp) {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function($c) use ($fp, $read) { fwrite($fp, $c . "\r\n"); return $read(); };

    $read();
    $cmd("EHLO oh-haustechnik.de");
    $cmd("STARTTLS");
    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($fp); return ['ok' => false, 'info' => 'STARTTLS fehlgeschlagen'];
    }
    $cmd("EHLO oh-haustechnik.de");
    $r = $cmd("AUTH LOGIN");
    if (strpos($r, '334') === false) { fclose($fp); return ['ok' => false, 'info' => 'AUTH abgelehnt']; }
    $cmd(base64_encode($user));
    $r = $cmd(base64_encode($pass));
    if (strpos($r, '235') === false) { fclose($fp); return ['ok' => false, 'info' => 'Login abgelehnt – App-Passwort prüfen']; }

    $cmd("MAIL FROM:<{$user}>");
    $cmd("RCPT TO:<{$to}>");
    $r = $cmd("DATA");
    if (strpos($r, '354') === false) { fclose($fp); return ['ok' => false, 'info' => 'DATA abgelehnt']; }

    $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers  = "From: OH Haustechnik <{$from}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "Subject: {$subjEnc}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $data = $headers . "\r\n" . chunk_split(base64_encode($body));
    // Punkt-Zeilen escapen
    $data = preg_replace('/^\./m', '..', $data);
    $r = $cmd($data . "\r\n.");
    $ok = strpos($r, '250') !== false;
    $cmd("QUIT");
    fclose($fp);
    return ['ok' => $ok, 'info' => $ok ? 'via Gmail SMTP gesendet' : 'Versand abgelehnt: ' . trim($r)];
}

/* --------------------------------------------------------------------------
 * KI-Aufruf (serverseitig, für Cron & Automatik)
 * ------------------------------------------------------------------------ */
function oh_ki(string $system, string $userMsg, int $maxTokens = 1500): ?string {
    $cfg = oh_config();
    $key = $cfg['anthropic_key'] ?? '';
    if (!$key) return null;
    $payload = json_encode([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => $maxTokens,
        'system' => $system,
        'messages' => [['role' => 'user', 'content' => $userMsg]],
    ]);
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01',
        ],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    if (!$resp) return null;
    $d = json_decode($resp, true);
    if (!isset($d['content'])) return null;
    $out = '';
    foreach ($d['content'] as $c) {
        if (($c['type'] ?? '') === 'text') $out .= $c['text'];
    }
    return trim($out);
}
