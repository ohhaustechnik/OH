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
    if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Neue ' . $lead['stufe'] . '-Anfrage erfasst: ' . ($lead['name'] ?: ($lead['email'] ?: $lead['id'])));
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

/* --------------------------------------------------------------------------
 * GOOGLE ADS – Überwachung der eigenen Kampagnen
 * Benötigt in der Konfiguration: ads_developer_token, ads_client_id,
 * ads_client_secret, ads_refresh_token, ads_customer_id (zu prüfendes Konto),
 * ads_login_customer_id (Verwalterkonto, optional aber empfohlen).
 * ------------------------------------------------------------------------ */
define('OH_ADS_API_VERSION', 'v23'); // bei API-Versionsfehler hier hochzählen (v24, v25 ...)

/** Holt ein frisches Access-Token aus dem Refresh-Token. */
function oh_ads_access_token(): ?string {
    $cfg = oh_config();
    foreach (['ads_client_id', 'ads_client_secret', 'ads_refresh_token'] as $k) {
        if (empty($cfg[$k])) return null;
    }
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id'     => $cfg['ads_client_id'],
            'client_secret' => $cfg['ads_client_secret'],
            'refresh_token' => $cfg['ads_refresh_token'],
            'grant_type'    => 'refresh_token',
        ]),
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $d = json_decode($resp, true);
    return $d['access_token'] ?? null;
}

/** Führt eine GAQL-Abfrage gegen das eigene Ads-Konto aus. */
function oh_ads_search(string $gaql, ?string &$err = null): ?array {
    $cfg = oh_config();
    $token = oh_ads_access_token();
    if (!$token) { $err = 'Login fehlgeschlagen (Refresh-Token/Client prüfen).'; return null; }
    $cid = preg_replace('/\D/', '', $cfg['ads_customer_id'] ?? '');
    if (!$cid) { $err = 'Keine Kundennummer hinterlegt.'; return null; }
    $login = preg_replace('/\D/', '', $cfg['ads_login_customer_id'] ?? '');

    $headers = [
        'Authorization: Bearer ' . $token,
        'developer-token: ' . ($cfg['ads_developer_token'] ?? ''),
        'Content-Type: application/json',
    ];
    if ($login) $headers[] = 'login-customer-id: ' . $login;

    $url = 'https://googleads.googleapis.com/' . OH_ADS_API_VERSION . '/customers/' . $cid . '/googleAds:search';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode(['query' => $gaql]),
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $d = json_decode($resp, true);
    if ($code !== 200) {
        $err = $d['error']['message'] ?? ('HTTP ' . $code . ': ' . substr((string)$resp, 0, 300));
        return null;
    }
    return $d['results'] ?? [];
}

/** Liefert eine Kampagnen-Auswertung der letzten 7 Tage (aufbereitet). */
function oh_ads_report(?string &$err = null): ?array {
    $gaql = "SELECT campaign.name, campaign.status, "
          . "metrics.cost_micros, metrics.clicks, metrics.impressions, "
          . "metrics.conversions, metrics.ctr, metrics.average_cpc "
          . "FROM campaign WHERE segments.date DURING LAST_7_DAYS "
          . "ORDER BY metrics.cost_micros DESC";
    $rows = oh_ads_search($gaql, $err);
    if ($rows === null) return null;

    $kampagnen = [];
    $sum = ['kosten' => 0, 'klicks' => 0, 'impr' => 0, 'conv' => 0];
    foreach ($rows as $r) {
        $m = $r['metrics'] ?? [];
        $kosten = ($m['costMicros'] ?? 0) / 1e6;
        $klicks = (int)($m['clicks'] ?? 0);
        $impr   = (int)($m['impressions'] ?? 0);
        $conv   = (float)($m['conversions'] ?? 0);
        $kampagnen[] = [
            'name'    => $r['campaign']['name'] ?? '–',
            'status'  => $r['campaign']['status'] ?? '',
            'kosten'  => round($kosten, 2),
            'klicks'  => $klicks,
            'impr'    => $impr,
            'conv'    => round($conv, 1),
            'ctr'     => round(((float)($m['ctr'] ?? 0)) * 100, 2),
            'cpc'     => round((($m['averageCpc'] ?? 0) / 1e6), 2),
        ];
        $sum['kosten'] += $kosten; $sum['klicks'] += $klicks;
        $sum['impr'] += $impr; $sum['conv'] += $conv;
    }
    $sum['kosten'] = round($sum['kosten'], 2);
    $sum['conv']   = round($sum['conv'], 1);
    $sum['cpl']    = $sum['conv'] > 0 ? round($sum['kosten'] / $sum['conv'], 2) : null; // Kosten pro Anfrage
    return ['zeitraum' => 'Letzte 7 Tage', 'kampagnen' => $kampagnen, 'summe' => $sum];
}

/** Suchbegriffe der letzten 30 Tage (wonach Leute wirklich gesucht haben). */
function oh_ads_search_terms(?string &$err = null): ?array {
    $gaql = "SELECT search_term_view.search_term, metrics.cost_micros, metrics.clicks, "
          . "metrics.conversions FROM search_term_view WHERE segments.date DURING LAST_30_DAYS "
          . "ORDER BY metrics.cost_micros DESC LIMIT 40";
    $rows = oh_ads_search($gaql, $err);
    if ($rows === null) return null;
    $out = [];
    foreach ($rows as $r) {
        $m = $r['metrics'] ?? [];
        $out[] = [
            'begriff' => $r['searchTermView']['searchTerm'] ?? '–',
            'kosten'  => round(($m['costMicros'] ?? 0) / 1e6, 2),
            'klicks'  => (int)($m['clicks'] ?? 0),
            'conv'    => round((float)($m['conversions'] ?? 0), 1),
        ];
    }
    return $out;
}

/** Keywords der letzten 30 Tage mit Leistung. */
function oh_ads_keywords(?string &$err = null): ?array {
    $gaql = "SELECT ad_group_criterion.keyword.text, ad_group_criterion.keyword.match_type, "
          . "metrics.cost_micros, metrics.clicks, metrics.conversions, metrics.ctr "
          . "FROM keyword_view WHERE segments.date DURING LAST_30_DAYS "
          . "ORDER BY metrics.cost_micros DESC LIMIT 40";
    $rows = oh_ads_search($gaql, $err);
    if ($rows === null) return null;
    $out = [];
    foreach ($rows as $r) {
        $m = $r['metrics'] ?? [];
        $kw = $r['adGroupCriterion']['keyword'] ?? [];
        $out[] = [
            'keyword' => $kw['text'] ?? '–',
            'match'   => $kw['matchType'] ?? '',
            'kosten'  => round(($m['costMicros'] ?? 0) / 1e6, 2),
            'klicks'  => (int)($m['clicks'] ?? 0),
            'conv'    => round((float)($m['conversions'] ?? 0), 1),
            'ctr'     => round(((float)($m['ctr'] ?? 0)) * 100, 2),
        ];
    }
    return $out;
}

/**
 * KI-Geschäftsführer: analysiert Ads-Daten und liefert konkrete Empfehlungen.
 * Speichert sie in daten/ads_reco.json und gibt sie zurück.
 */
function oh_ads_recommendations(?string &$err = null): ?array {
    $e2 = $e3 = $e4 = null;
    $rep = oh_ads_report($err);   if ($rep === null) return null;
    $terms  = oh_ads_search_terms($e2);
    $kws    = oh_ads_keywords($e3);
    $markt  = oh_ads_market($e4);

    // Datenkontext kompakt aufbereiten
    $s = $rep['summe'];
    $ctx = "ZAHLEN (7 Tage): Kosten " . $s['kosten'] . "€, Klicks " . $s['klicks']
         . ", Anfragen " . $s['conv'] . ", Kosten pro Anfrage " . ($s['cpl'] ?? '–') . "€\n\nKAMPAGNEN:\n";
    foreach ($rep['kampagnen'] as $k) $ctx .= "- {$k['name']}: {$k['kosten']}€, {$k['klicks']} Klicks, {$k['conv']} Anfragen, CTR {$k['ctr']}%\n";
    if ($markt) {
        $ctx .= "\nMARKT & KONKURRENZ (Anteil an allen Nürnberger Suchen, 30 Tage):\n";
        foreach ($markt as $mk) {
            $ctx .= "- {$mk['name']}: Du bekommst " . ($mk['anteil'] ?? '?') . "% der Suchen"
                  . (isset($mk['verlust_budget']) ? ", verlierst " . $mk['verlust_budget'] . "% wegen zu wenig Budget" : "")
                  . (isset($mk['verlust_rang']) ? " und " . $mk['verlust_rang'] . "% wegen Rang/Gebot" : "") . ".\n";
        }
    }
    if ($kws) { $ctx .= "\nKEYWORDS (30 Tage, teuerste zuerst):\n"; foreach (array_slice($kws, 0, 20) as $k) $ctx .= "- \"{$k['keyword']}\" [{$k['match']}]: {$k['kosten']}€, {$k['klicks']} Kl., {$k['conv']} Anfr.\n"; }
    if ($terms) { $ctx .= "\nSUCHBEGRIFFE (wonach Leute wirklich gesucht haben, 30 Tage):\n"; foreach (array_slice($terms, 0, 25) as $t) $ctx .= "- \"{$t['begriff']}\": {$t['kosten']}€, {$t['klicks']} Kl., {$t['conv']} Anfr.\n"; }

    // Bereits vorgeschlagene / erledigte Maßnahmen sammeln (nicht wiederholen!)
    $hist = [];
    foreach (oh_read('ads_reco', []) as $p) if (!empty($p['titel'])) $hist[] = $p['titel'];
    foreach (array_slice(oh_read('ads_changes', []), 0, 15) as $c) if (!empty($c['titel'])) $hist[] = $c['titel'];
    $hist = array_values(array_filter(array_unique($hist)));
    if ($hist) $ctx .= "\nBEREITS VORGESCHLAGEN ODER ERLEDIGT (NICHT wiederholen – bring NEUE, frische Ideen aus den aktuellen Daten):\n- " . implode("\n- ", array_slice($hist, 0, 20)) . "\n";

    $system = "Du bist der digitale Geschäftsführer von OH Haustechnik (Elektriker Nürnberg, Kleinunternehmer). "
        . "ZIEL: möglichst viele HOCHWERTIGE Anfragen für Altbausanierung, Wohnungsmodernisierung, komplette Elektro-Sanierung (3-4 Zimmer), Zähleranlagen, Unterverteilungen und Smart-Home. "
        . "Qualität vor Menge. Werbekosten senken, profitable Aufträge gewinnen.\n"
        . "Analysiere die Google-Ads-Daten wie ein cleverer Profi und finde die wichtigsten Optimierungen. "
        . "Sprich EINFACH, wie ein guter Mitarbeiter zum Chef – KEINE Fachbegriffe, kurze Sätze, immer mit 'Chef' anreden.\n"
        . "Gib AUSSCHLIESSLICH einen JSON-Block in genau diesem Format zurück (3-6 Empfehlungen, wichtigste zuerst), nichts davor/danach:\n"
        . "<reco>[{\"titel\":\"Chef, ...\",\"was\":\"<was genau ändern>\",\"warum\":\"<warum, einfach>\",\"anfragen\":\"<z.B. 2-4 pro Woche>\",\"wahrscheinlichkeit\":\"<hoch|mittel|niedrig>\",\"dringlichkeit\":\"<rot|gelb|gruen>\",\"typ\":\"<negativ_keyword|keyword|budget|gebot|standort|zeit|anzeige|info>\",\"wert\":\"<z.B. das auszuschließende Suchwort oder das neue Keyword>\",\"schritte\":\"<1-2 ganz einfache Schritte zum Umsetzen>\"}]</reco>\n"
        . "Konzentriere Dich auf: Geld-verbrennende Suchbegriffe als negative Keywords ausschließen (z.B. 'job','gehalt','kostenlos','ausbildung','selber'), starke Sanierungs-Keywords pushen, Budget auf das lenken was Anfragen bringt. "
        . "Nutze die MARKT-Daten: Wenn er viele Suchen wegen zu wenig Budget verliert, empfiehl Budget erhöhen (mit erwartetem Gewinn). Wenn wegen Rang/Gebot, empfiehl Gebot/Anzeige verbessern. Sag konkret, wie viel Markt-Anteil (mehr Anfragen) er dadurch gewinnt. "
        . "WICHTIG: Wiederhole KEINE bereits vorgeschlagenen/erledigten Maßnahmen. Liefere jeden Tag NEUE, frische, andere Empfehlungen aus den aktuellen Zahlen. "
        . "STELLE NIEMALS FRAGEN. Liefere immer fertige, konkrete Vorschläge mit erwarteter Verbesserung (z.B. '+18% mehr Anfragen'). Der Chef soll nur noch Übernehmen oder Ablehnen drücken.";

    $resp = oh_ki($system, $ctx, 1800);
    if (!$resp) { $err = 'KI-Analyse nicht verfügbar (Anthropic-Schlüssel/Guthaben prüfen).'; return null; }

    // JSON robust herausschälen (egal ob <reco>-Tags, ```-Blöcke oder Prosa drumherum)
    $json = $resp;
    if (preg_match('/<reco>([\s\S]*?)<\/reco>/', $resp, $mch)) $json = $mch[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '['); $rb = strrpos($json, ']');
    if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
    $list = json_decode(trim($json), true);
    if (!is_array($list) || !count($list)) { $err = 'Die KI hat gerade keine klaren Empfehlungen geliefert – bitte nochmal „Markt neu prüfen".'; return null; }

    // IDs + Status vergeben und speichern
    $alt = oh_read('ads_reco', []);
    $altStatus = [];
    foreach ($alt as $a) $altStatus[md5(($a['titel'] ?? '') . ($a['wert'] ?? ''))] = $a['status'] ?? 'offen';
    $reco = [];
    foreach ($list as $i => $r) {
        $key = md5(($r['titel'] ?? '') . ($r['wert'] ?? ''));
        $reco[] = array_merge($r, [
            'id'      => 'R' . date('ymd') . $i,
            'status'  => $altStatus[$key] ?? 'offen',   // offen | uebernommen | spaeter
            'created' => time(),
        ]);
    }
    oh_write('ads_reco', $reco);
    oh_write('ads_meta', ['last_analysis' => time()]);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Google Ads geprüft: ' . count($reco) . ' Optimierung(en) gefunden');
    return $reco;
}

/** Zeitpunkt der letzten Marktanalyse. */
function oh_ads_last_analysis(): int {
    $m = oh_read('ads_meta', []);
    return (int)($m['last_analysis'] ?? 0);
}

/** Dokumentiert eine übernommene Änderung. */
function oh_ads_log_change(array $entry): void {
    $log = oh_read('ads_changes', []);
    array_unshift($log, array_merge(['ts' => time()], $entry));
    oh_write('ads_changes', $log);
}

/**
 * MARKT & KONKURRENZ: Anteil an den Suchen (Impression Share) und warum
 * Anzeigen verloren gehen (zu wenig Budget vs. zu schlechter Rang/Gebot).
 */
function oh_ads_market(?string &$err = null): ?array {
    $gaql = "SELECT campaign.name, metrics.search_impression_share, "
          . "metrics.search_budget_lost_impression_share, metrics.search_rank_lost_impression_share, "
          . "metrics.search_top_impression_share, metrics.search_absolute_top_impression_share "
          . "FROM campaign WHERE segments.date DURING LAST_30_DAYS";
    $rows = oh_ads_search($gaql, $err);
    if ($rows === null) return null;
    $out = [];
    foreach ($rows as $r) {
        $m = $r['metrics'] ?? [];
        $pct = function($v) { return $v === null ? null : round($v * 100); };
        $out[] = [
            'name'        => $r['campaign']['name'] ?? '–',
            'anteil'      => $pct($m['searchImpressionShare'] ?? null),               // % der Suchen, die Du bekommst
            'verlust_budget' => $pct($m['searchBudgetLostImpressionShare'] ?? null),  // % verloren wegen zu wenig Budget
            'verlust_rang'   => $pct($m['searchRankLostImpressionShare'] ?? null),    // % verloren wegen Rang/Gebot
            'oben'        => $pct($m['searchTopImpressionShare'] ?? null),
            'ganz_oben'   => $pct($m['searchAbsoluteTopImpressionShare'] ?? null),
        ];
    }
    return $out;
}

/* --- Änderungen im Konto ausführen (Mutate) --- */

/** Generischer Mutate-Aufruf gegen die Google Ads API. */
function oh_ads_mutate(string $endpoint, array $body, ?string &$err = null) {
    $cfg = oh_config();
    $token = oh_ads_access_token();
    if (!$token) { $err = 'Login fehlgeschlagen.'; return null; }
    $cid = preg_replace('/\D/', '', $cfg['ads_customer_id'] ?? '');
    $login = preg_replace('/\D/', '', $cfg['ads_login_customer_id'] ?? '');
    $headers = [
        'Authorization: Bearer ' . $token,
        'developer-token: ' . ($cfg['ads_developer_token'] ?? ''),
        'Content-Type: application/json',
    ];
    if ($login) $headers[] = 'login-customer-id: ' . $login;
    $url = 'https://googleads.googleapis.com/' . OH_ADS_API_VERSION . '/customers/' . $cid . '/' . $endpoint;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 45,
        CURLOPT_HTTPHEADER => $headers, CURLOPT_POSTFIELDS => json_encode($body),
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $d = json_decode($resp, true);
    if ($code !== 200) { $err = $d['error']['message'] ?? ('HTTP ' . $code); return null; }
    return $d;
}

/** Aktive Such-Kampagnen-IDs. */
function oh_ads_enabled_search_campaigns(?string &$err = null): array {
    $gaql = "SELECT campaign.id FROM campaign WHERE campaign.status = 'ENABLED' "
          . "AND campaign.advertising_channel_type = 'SEARCH'";
    $rows = oh_ads_search($gaql, $err);
    $ids = [];
    foreach (($rows ?: []) as $r) { if (!empty($r['campaign']['id'])) $ids[] = $r['campaign']['id']; }
    return $ids;
}

/** Fügt ein negatives Keyword zu allen aktiven Such-Kampagnen hinzu (spart Geld, kostet nie mehr). */
function oh_ads_add_negative_keyword(string $text, ?string &$err = null): bool {
    $cfg = oh_config();
    $cid = preg_replace('/\D/', '', $cfg['ads_customer_id'] ?? '');
    $camps = oh_ads_enabled_search_campaigns($err);
    if (!$camps) { if (!$err) $err = 'Keine aktive Such-Kampagne gefunden.'; return false; }
    $ops = [];
    foreach ($camps as $campId) {
        $ops[] = ['create' => [
            'campaign' => 'customers/' . $cid . '/campaigns/' . $campId,
            'negative' => true,
            'keyword'  => ['text' => $text, 'matchType' => 'BROAD'],
        ]];
    }
    $res = oh_ads_mutate('campaignCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $err);
    return $res !== null;
}

/**
 * Setzt eine Empfehlung um. Sichere Änderungen (negative Keywords) werden direkt
 * im Konto ausgeführt; alles andere wird dokumentiert (manuell umzusetzen).
 */
function oh_ads_apply(array $reco, ?string &$err = null): array {
    $typ  = $reco['typ'] ?? '';
    $wert = trim($reco['wert'] ?? '');
    if ($typ === 'negativ_keyword' && $wert !== '') {
        if (oh_ads_add_negative_keyword($wert, $err)) {
            if (function_exists('oh_log_activity')) oh_log_activity('dilara', "Ausschluss-Wort \"{$wert}\" in Google Ads eingetragen (spart Werbegeld)");
            return ['executed' => true, 'msg' => "Erledigt, Chef! \"{$wert}\" wird ab sofort ausgeschlossen – das spart Werbegeld."];
        }
        return ['executed' => false, 'msg' => 'Konnte nicht automatisch ausgeführt werden (' . $err . '). Bitte kurz manuell im Ads-Konto eintragen.'];
    }
    return ['executed' => false, 'msg' => 'Notiert, Chef. Diese Änderung bitte kurz selbst umsetzen – die Schritte stehen in der Empfehlung.'];
}


/* ==========================================================================
 * FIRMENWEITES AUFGABEN-DASHBOARD ("Sollte erledigt" / "Bereits erledigt")
 * Sammelt offene Aufgaben aus allen Bereichen: Google Ads, Anfragen/Leads,
 * Angebote, Bewertungen, Markt-Aktualität. (E-Mail/WhatsApp/Website folgen,
 * sobald angebunden.)
 * ======================================================================== */
function oh_company_tasks(): array {
    $now = time();
    $offen = []; $erledigt = [];

    // 1) Google-Ads-Empfehlungen
    foreach (oh_read('ads_reco', []) as $r) {
        $st = $r['status'] ?? 'offen';
        if ($st === 'offen') {
            $offen[] = [
                'bereich' => 'Google Ads', 'icon' => '📈',
                'prio'    => $r['dringlichkeit'] ?? 'gelb',
                'titel'   => $r['titel'] ?? 'Optimierung',
                'nutzen'  => 'ca. ' . ($r['anfragen'] ?? '?') . ' mehr Anfragen',
                'warum'   => $r['warum'] ?? '',
                'typ'     => 'reco', 'ref' => $r['id'] ?? '',
            ];
        } elseif ($st === 'uebernommen') {
            $erledigt[] = ['bereich' => 'Google Ads', 'icon' => '📈', 'titel' => $r['titel'] ?? '', 'ts' => $r['created'] ?? $now];
        }
    }

    // 2) Anfragen / Leads / Angebote / Bewertungen
    foreach (oh_read('leads', []) as $l) {
        $status = $l['status'] ?? 'neu';
        $name = $l['name'] ?: ($l['email'] ?: $l['id']);
        if ($status === 'neu' && ($l['stufe'] ?? '') === 'HOT') {
            $offen[] = ['bereich' => 'Anfrage', 'icon' => '🔥', 'prio' => 'rot', 'titel' => "Heißer Lead: $name", 'nutzen' => 'möglicher Auftrag', 'warum' => 'Will wahrscheinlich beauftragen – sofort reagieren, sonst geht er zur Konkurrenz.', 'typ' => 'lead', 'ref' => $l['id']];
        } elseif ($status === 'angebot_raus' && !empty($l['angebot_ts']) && ($now - $l['angebot_ts']) >= 2 * 86400) {
            $offen[] = ['bereich' => 'Angebot', 'icon' => '📄', 'prio' => 'rot', 'titel' => "Nachfassen: $name", 'nutzen' => 'Abschluss sichern', 'warum' => 'Angebot ist 2+ Tage raus – jetzt nachfassen bringt oft den Auftrag.', 'typ' => 'followup', 'ref' => $l['id']];
        } elseif (in_array($status, ['gewonnen', 'abgeschlossen']) && empty($l['bewertung_angefragt']) && !empty($l['abschluss_ts']) && ($now - $l['abschluss_ts']) >= 5 * 86400) {
            $offen[] = ['bereich' => 'Bewertung', 'icon' => '⭐', 'prio' => 'gelb', 'titel' => "Bewertung anfragen: $name", 'nutzen' => 'bessere Google-Sichtbarkeit', 'warum' => 'Zufriedener Kunde – jetzt um Bewertung bitten bringt neue Anfragen.', 'typ' => 'bewertung', 'ref' => $l['id']];
        } elseif ($status === 'neu' && ($l['stufe'] ?? '') === 'WARM') {
            $offen[] = ['bereich' => 'Anfrage', 'icon' => '📋', 'prio' => 'gelb', 'titel' => "Anfrage prüfen: $name", 'nutzen' => 'möglicher Auftrag', 'warum' => 'Interesse ist da – heute antworten erhöht die Abschlusschance.', 'typ' => 'lead', 'ref' => $l['id']];
        } elseif ($status === 'neu' && ($l['stufe'] ?? '') === 'KALT') {
            $offen[] = ['bereich' => 'Anfrage', 'icon' => '💬', 'prio' => 'gruen', 'titel' => "Info-Anfrage: $name", 'nutzen' => 'Kontakt warmhalten', 'warum' => 'Eher unverbindlich – bei Gelegenheit beantworten.', 'typ' => 'lead', 'ref' => $l['id']];
        }
        if (in_array($status, ['gewonnen', 'abgeschlossen'])) {
            $erledigt[] = ['bereich' => 'Auftrag', 'icon' => '✅', 'titel' => "Auftrag gewonnen: $name", 'ts' => $l['abschluss_ts'] ?: $now];
        }
    }

    // 3) Offene E-Mails (Gmail)
    $em = oh_read('emails', []);
    foreach (($em['list'] ?? []) as $m) {
        $offen[] = ['bereich' => 'E-Mail', 'icon' => '✉️', 'prio' => 'gelb',
            'titel' => 'E-Mail: ' . ($m['subject'] ?: '(kein Betreff)'),
            'nutzen' => 'von ' . ($m['from'] ?: '?'),
            'warum' => 'Ungelesene E-Mail – kann eine neue Anfrage sein. Kurz beantworten.',
            'typ' => 'email', 'ref' => ''];
    }

    // 4) Offene WhatsApp-Nachrichten
    foreach (oh_wa_open() as $m) {
        $offen[] = ['bereich' => 'WhatsApp', 'icon' => '💬', 'prio' => 'rot',
            'titel' => 'WhatsApp: ' . ($m['name'] ?: ($m['from'] ?? '')),
            'nutzen' => 'schnell antworten = mehr Abschlüsse',
            'warum' => $m['text'] ?? '',
            'typ' => 'whatsapp', 'ref' => $m['id'] ?? ''];
    }

    // 5) Website-Status
    $ws = oh_read('web_status', []);
    if (isset($ws['ok']) && !$ws['ok']) {
        foreach (($ws['probleme'] ?? []) as $p) {
            $offen[] = ['bereich' => 'Website', 'icon' => '🌐', 'prio' => 'rot',
                'titel' => 'Website-Problem', 'nutzen' => 'Anfragen sichern',
                'warum' => $p . ' – bitte prüfen, sonst gehen Anfragen verloren.',
                'typ' => 'website', 'ref' => ''];
        }
    } elseif (isset($ws['ok']) && $ws['ok']) {
        $erledigt[] = ['bereich' => 'Website', 'icon' => '🌐', 'titel' => 'Website & Kontaktformular laufen', 'ts' => $ws['ts'] ?? $now];
    }

    // 6) Markt-Aktualität (Warnung wenn Analyse veraltet)
    $warnung = null;
    if (!empty(oh_config()['ads_refresh_token'])) {
        $last = oh_ads_last_analysis();
        $age = $last ? ($now - $last) : PHP_INT_MAX;
        if ($age > 24 * 3600) {
            $tage = $last ? max(1, (int)floor($age / 86400)) : 0;
            $prio = ($tage >= 3 || !$last) ? 'rot' : 'gelb';
            $hinweis = $last
                ? "Letzte Marktanalyse vor $tage Tag(en)."
                : 'Es wurde noch keine Marktanalyse durchgeführt.';
            $offen[] = ['bereich' => 'Markt', 'icon' => '🔍', 'prio' => $prio, 'titel' => 'Marktanalyse aktualisieren', 'nutzen' => 'aktuelle Chancen & Konkurrenz', 'warum' => "$hinweis Eine neue Analyse berücksichtigt aktuelle Marktveränderungen, Konkurrenz, Suchtrends und neue Chancen – für die bestmögliche Kampagnenleistung.", 'typ' => 'markt', 'ref' => ''];
            $warnung = ['prio' => $prio, 'tage' => $tage, 'text' => "Die aktuelle Marktanalyse könnte veraltet sein. Es wird empfohlen, eine neue Marktanalyse durchzuführen, um aktuelle Marktveränderungen, Konkurrenzaktivitäten, Suchtrends und neue Chancen zu berücksichtigen."];
        }
    }

    $ord = ['rot' => 0, 'gelb' => 1, 'gruen' => 2];
    usort($offen, function($a, $b) use ($ord) { return ($ord[$a['prio']] ?? 1) - ($ord[$b['prio']] ?? 1); });
    usort($erledigt, function($a, $b) { return ($b['ts'] ?? 0) - ($a['ts'] ?? 0); });

    return [
        'offen'    => $offen,
        'erledigt' => array_slice($erledigt, 0, 15),
        'warnung'  => $warnung,
        'anzahl'   => ['offen' => count($offen), 'rot' => count(array_filter($offen, function($t){ return $t['prio'] === 'rot'; }))],
    ];
}

/* ==========================================================================
 * MERT ALDEMIR – 24/7 KI-Geschäftsführer. Einziges Ziel: in 5 Monaten
 * Richtung 1.000.000 € Umsatz. Erstellt täglich einen Prioritätenplan.
 * ======================================================================== */
function oh_mert_briefing(?string &$err = null): ?string {
    $leads = oh_read('leads', []);
    $reco  = oh_read('ads_reco', []);
    $offenReco = array_filter($reco, function($r){ return ($r['status'] ?? '') === 'offen'; });
    $neu = $hot = $angebot = $gewonnen = 0;
    foreach ($leads as $l) {
        $s = $l['status'] ?? 'neu';
        if ($s === 'neu') $neu++;
        if (($l['stufe'] ?? '') === 'HOT' && $s === 'neu') $hot++;
        if ($s === 'angebot_raus') $angebot++;
        if (in_array($s, ['gewonnen', 'abgeschlossen'])) $gewonnen++;
    }
    $e = null; $rep = oh_ads_report($e);
    $cpl = $rep['summe']['cpl'] ?? null; $kosten = $rep['summe']['kosten'] ?? null; $adAnfr = $rep['summe']['conv'] ?? null;

    $ctx = "STAND OH Haustechnik (Elektriker Nürnberg, aktuell Ein-Mann-Betrieb):\n"
         . "- Offene Anfragen: $neu (davon $hot heiß)\n"
         . "- Angebote draußen, warten auf Antwort: $angebot\n"
         . "- Gewonnene Aufträge gespeichert: $gewonnen\n"
         . "- Google Ads (7 Tage): Kosten " . ($kosten ?? '?') . "€, Anfragen " . ($adAnfr ?? '?') . ", Kosten pro Anfrage " . ($cpl ?? '?') . "€\n"
         . "- Offene Ads-Optimierungen: " . count($offenReco) . "\n";

    $system = "Du bist Mert Aldemir, der digitale Geschäftsführer von OH Haustechnik. "
        . "DEIN EINZIGES ZIEL: das Unternehmen in 5 Monaten in Richtung 1.000.000 € Umsatz skalieren. "
        . "Hochwertige Aufträge (Altbausanierung, komplette Wohnungssanierung, Zähleranlagen, Smart-Home) bringen am meisten. "
        . "Denke wie ein knallharter, kluger Geschäftsführer. Erkenne Engpässe (zu wenig Anfragen, zu teure Werbung, Kapazität/Mitarbeiter nötig, Budget erhöhen, Prozesse). "
        . "Sprich EINFACH mit dem Chef (Du-Form), kein Fachchinesisch, motivierend aber ehrlich.\n"
        . "Erstelle einen kurzen TAGESPLAN: die 3 wichtigsten Hebel HEUTE, nach Wirkung aufs Wachstum sortiert, je 1 klarer Satz mit Begründung. "
        . "Wenn ein Engpass da ist (z.B. zu wenig Anfragen, Werbung zu teuer, bald Mitarbeiter nötig), sag es deutlich. Max 9 Zeilen. Beginne mit 'Chef,'.";

    $out = oh_ki($system, $ctx, 700);
    if ($out) { oh_write('mert_plan', ['text' => $out, 'ts' => time()]); if (function_exists('oh_log_activity')) oh_log_activity('mert', 'Tagesplan & Prioritäten aktualisiert'); }
    else { $err = 'KI nicht verfügbar (Anthropic-Schlüssel/Guthaben prüfen).'; }
    return $out;
}

/* ==========================================================================
 * ANBINDUNGEN: Gmail (offene E-Mails), Website-Check, WhatsApp
 * ======================================================================== */

/** MIME-Header dekodieren (Umlaute in Betreff/Absender). */
function oh_mime_decode($s): string {
    if (!function_exists('imap_mime_header_decode')) return (string)$s;
    $r = ''; foreach (imap_mime_header_decode($s) ?: [] as $p) { $r .= $p->text; }
    return $r !== '' ? $r : (string)$s;
}

/** Ungelesene E-Mails aus Gmail (IMAP, mit App-Passwort). */
function oh_gmail_unread(int $max = 10): array {
    $cfg = oh_config();
    if (empty($cfg['gmail_user']) || empty($cfg['gmail_pass']) || !function_exists('imap_open')) return [];
    $mbox = @imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $cfg['gmail_user'], $cfg['gmail_pass'], OP_READONLY);
    if (!$mbox) return [];
    $ids = @imap_search($mbox, 'UNSEEN');
    $out = [];
    if ($ids) {
        rsort($ids);
        foreach (array_slice($ids, 0, $max) as $id) {
            $h = @imap_headerinfo($mbox, $id);
            if (!$h) continue;
            $from = '';
            if (isset($h->from[0])) {
                $f = $h->from[0];
                $from = ($f->personal ?? '') ?: (($f->mailbox ?? '') . '@' . ($f->host ?? ''));
            }
            $out[] = [
                'from'    => oh_mime_decode($from),
                'subject' => oh_mime_decode($h->subject ?? '(kein Betreff)'),
                'ts'      => isset($h->udate) ? (int)$h->udate : time(),
            ];
        }
    }
    @imap_close($mbox);
    return $out;
}

/** Prüft, ob Website + wichtige Seiten erreichbar sind. */
function oh_website_check(): array {
    $base = rtrim(oh_config()['site_url'] ?? 'https://oh-haustechnik.de', '/');
    $pages = ['/' => 'Startseite', '/kontakt.php' => 'Kontaktseite', '/festpreis-kalkulator.php' => 'Festpreis-Kalkulator'];
    $probleme = [];
    foreach ($pages as $p => $name) {
        $ch = curl_init($base . $p);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 400) $probleme[] = "$name nicht erreichbar (Status $code)";
    }
    $st = ['ok' => empty($probleme), 'probleme' => $probleme, 'ts' => time()];
    oh_write('web_status', $st);
    return $st;
}

/** Sammelt alle Anbindungen ein (für Cron & Button). */
function oh_inbox_scan(): void {
    $unread = oh_gmail_unread(15);
    oh_write('emails', ['list' => $unread, 'ts' => time()]);
    oh_wissen_add_emails($unread);
    oh_website_check();
}

/** Offene (unbeantwortete) WhatsApp-Nachrichten. */
function oh_wa_open(): array {
    return array_values(array_filter(oh_read('whatsapp', []), function($m){ return ($m['status'] ?? 'offen') === 'offen'; }));
}

/* ==========================================================================
 * WISSENSSPEICHER – dauerhaftes Gedächtnis (E-Mails, Kommunikation, Kontext)
 * ======================================================================== */
function oh_kategorie(string $s): string {
    $s = mb_strtolower($s);
    if (mb_strpos($s, 'rechnung') !== false || mb_strpos($s, 'zahlung') !== false || mb_strpos($s, 'mahnung') !== false) return 'Rechnung';
    if (mb_strpos($s, 'angebot') !== false || mb_strpos($s, 'kostenvoranschlag') !== false) return 'Angebot';
    if (mb_strpos($s, 'bewertung') !== false || mb_strpos($s, 'google') !== false) return 'Bewertung';
    if (mb_strpos($s, 'anfrage') !== false || mb_strpos($s, 'sanierung') !== false || mb_strpos($s, 'elektr') !== false || mb_strpos($s, 'angebot') !== false) return 'Anfrage';
    return 'Sonstiges';
}

/** Speichert E-Mails dauerhaft im Wissensspeicher (mit Dedupe + Kategorie). */
function oh_wissen_add_emails(array $list): void {
    if (!$list) return;
    $w = oh_read('wissen', []);
    $seen = [];
    foreach ($w as $x) { if (!empty($x['hash'])) $seen[$x['hash']] = true; }
    foreach ($list as $m) {
        $hash = md5(($m['from'] ?? '') . '|' . ($m['subject'] ?? '') . '|' . date('Y-m-d', $m['ts'] ?? time()));
        if (isset($seen[$hash])) continue;
        $w[] = [
            'hash' => $hash, 'typ' => 'email',
            'from' => $m['from'] ?? '', 'subject' => $m['subject'] ?? '',
            'ts' => $m['ts'] ?? time(), 'kategorie' => oh_kategorie($m['subject'] ?? ''),
            'status' => 'offen',
        ];
        $seen[$hash] = true;
    }
    if (count($w) > 600) $w = array_slice($w, -600);
    oh_write('wissen', $w);
}

/** Kompakte Gedächtnis-Zusammenfassung für alle Agenten-Prompts. */
function oh_wissen_summary(): string {
    $w = oh_read('wissen', []);
    $leads = oh_read('leads', []);
    $byCat = [];
    foreach ($w as $x) { $k = $x['kategorie'] ?? 'Sonstiges'; $byCat[$k] = ($byCat[$k] ?? 0) + 1; }
    $waiting = 0;
    foreach ($leads as $l) { if (in_array($l['status'] ?? '', ['neu', 'angebot_raus', 'nachgefasst'])) $waiting++; }
    $s = "UNTERNEHMENS-GEDÄCHTNIS (dauerhaft gespeichert, wächst mit jeder Info):\n";
    $s .= "- Erfasste Nachrichten gesamt: " . count($w);
    if ($byCat) { $p = []; foreach ($byCat as $k => $v) $p[] = "$k: $v"; $s .= " (" . implode(', ', $p) . ")"; }
    $s .= "\n- Gespeicherte Kunden/Leads: " . count($leads) . ", davon warten " . $waiting . " auf Rückmeldung.\n";
    $recent = array_slice($w, -10);
    if ($recent) {
        $s .= "- Zuletzt erfasste Nachrichten:\n";
        foreach ($recent as $r) $s .= "  · [" . ($r['kategorie'] ?? '') . "] " . ($r['subject'] ?? '') . " – " . ($r['from'] ?? '') . "\n";
    }
    return $s;
}

/** Tägliche Sprach-/Alexa-Zusammenfassung. */
function oh_alexa_summary(): string {
    $leads = oh_read('leads', []); $em = oh_read('emails', []); $wa = oh_wa_open(); $mert = oh_read('mert_plan', []);
    $hot = $neu = $angebot = 0;
    foreach ($leads as $l) {
        $st = $l['status'] ?? 'neu';
        if ($st === 'neu') $neu++;
        if (($l['stufe'] ?? '') === 'HOT' && $st === 'neu') $hot++;
        if ($st === 'angebot_raus') $angebot++;
    }
    $mails = count($em['list'] ?? []);
    $std = (int)date('G'); $gruss = $std < 11 ? 'Guten Morgen' : ($std < 18 ? 'Hallo' : 'Guten Abend');
    $s = "$gruss Chef. ";
    $s .= "$neu offene Anfragen, davon $hot heiß. ";
    $s .= "$mails ungelesene E-Mails. " . count($wa) . " neue WhatsApp-Nachrichten. ";
    if ($angebot) $s .= "$angebot Angebote warten auf Antwort. ";
    if (!empty($mert['text'])) $s .= "Mert sagt: " . preg_replace('/\s+/', ' ', mb_substr($mert['text'], 0, 280));
    return trim($s);
}

/* ==========================================================================
 * AGENTEN-RUNDE – das vernetzte KI-Team stimmt sich ab (24/7 via Cron).
 * Jeder Agent prüft seinen Bereich, gibt Wichtiges an Kollegen weiter,
 * Mert setzt die Top-Prioritäten. Ergebnis in daten/agenten.json.
 * ======================================================================== */
function oh_agenten_runde(?string &$err = null): ?array {
    $leads = oh_read('leads', []);
    $neu = $hot = $angebot = $gewonnen = 0;
    foreach ($leads as $l) {
        $st = $l['status'] ?? 'neu';
        if ($st === 'neu') $neu++;
        if (($l['stufe'] ?? '') === 'HOT' && $st === 'neu') $hot++;
        if ($st === 'angebot_raus') $angebot++;
        if (in_array($st, ['gewonnen', 'abgeschlossen'])) $gewonnen++;
    }
    $e = null; $rep = oh_ads_report($e);
    $kosten = $rep['summe']['kosten'] ?? '?'; $cpl = $rep['summe']['cpl'] ?? '?'; $adAnfr = $rep['summe']['conv'] ?? '?';
    $recoOffen = count(array_filter(oh_read('ads_reco', []), function($r){ return ($r['status'] ?? '') === 'offen'; }));
    $em = oh_read('emails', []); $mails = count($em['list'] ?? []);
    $wa = count(oh_wa_open());
    $ws = oh_read('web_status', []); $web = isset($ws['ok']) ? ($ws['ok'] ? 'erreichbar' : 'PROBLEM') : 'unbekannt';

    $ctx = "AKTUELLE LAGE OH Haustechnik:\n"
         . "- Offene Anfragen: $neu (heiß: $hot), Angebote draußen: $angebot, gewonnen: $gewonnen\n"
         . "- Google Ads 7 Tage: Kosten {$kosten}€, Anfragen {$adAnfr}, Kosten/Anfrage {$cpl}€, offene Optimierungen: $recoOffen\n"
         . "- Ungelesene E-Mails: $mails, neue WhatsApp: $wa, Website: $web\n"
         . "- Mitarbeiter: aktuell Ein-Mann-Betrieb.\n";

    $system = "Du bist das vernetzte KI-Team von OH Haustechnik. Gemeinsames Ziel ALLER: in 5 Monaten 1.000.000 € Umsatz. "
        . "Simuliere die aktuelle Abstimmungs-Runde. Team: mert (Geschäftsführer), dilara (Marketing/Website/Ads), "
        . "kaan (Kommunikation: E-Mail/WhatsApp/Anfragen), emre (Kalkulation/Angebote), aylin (Buchhaltung/Lexware), "
        . "yusuf (Projekte/Baustellen), baran (Personal). "
        . "Jeder Agent prüft SEINEN Bereich anhand der Lage, notiert 1-2 kurze, konkrete Funde/Vorschläge (Du-Form, mit erwartetem Nutzen, KEINE Fragen). "
        . "WICHTIG: Wenn ein Fund einen anderen Bereich betrifft, schreib eine kurze Nachricht von Agent an Agent (z.B. emre an baran: viele Großaufträge, bald Mann nötig). "
        . "Mert wertet alles aus und setzt die 3 wichtigsten Prioritäten fürs Wachstum. "
        . "Antworte AUSSCHLIESSLICH mit JSON in diesem Format, nichts davor/danach:\n"
        . "<runde>{\"agenten\":[{\"key\":\"dilara\",\"funde\":[\"...\"]},{\"key\":\"kaan\",\"funde\":[\"...\"]},{\"key\":\"emre\",\"funde\":[\"...\"]},{\"key\":\"aylin\",\"funde\":[\"...\"]},{\"key\":\"yusuf\",\"funde\":[\"...\"]},{\"key\":\"baran\",\"funde\":[\"...\"]}],\"nachrichten\":[{\"von\":\"emre\",\"an\":\"baran\",\"text\":\"...\"}],\"prioritaeten\":[\"...\",\"...\",\"...\"]}</runde>";

    $resp = oh_ki($system, $ctx, 2000);
    if (!$resp) { $err = 'KI nicht verfügbar (Schlüssel/Guthaben prüfen).'; return null; }
    $json = $resp;
    if (preg_match('/<runde>([\s\S]*?)<\/runde>/', $resp, $m)) $json = $m[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '{'); $rb = strrpos($json, '}');
    if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
    $data = json_decode(trim($json), true);
    if (!is_array($data)) { $err = 'KI-Antwort unlesbar.'; return null; }
    $data['ts'] = time();
    oh_write('agenten', $data);
    if (function_exists('oh_log_activity')) oh_log_activity('mert', 'Agenten-Runde durchgeführt – Team hat sich abgestimmt (' . count($data['nachrichten'] ?? []) . ' Nachrichten)');
    return $data;
}

/* ==========================================================================
 * AKTIVITÄTS-PROTOKOLL – wer (welcher Agent) hat was erledigt
 * ======================================================================== */
function oh_log_activity(string $agent, string $text): void {
    $a = oh_read('aktivitaet', []);
    array_unshift($a, ['ts' => time(), 'agent' => $agent, 'text' => $text]);
    if (count($a) > 150) $a = array_slice($a, 0, 150);
    oh_write('aktivitaet', $a);
}

/* ==========================================================================
 * DILARA: Website wirklich lesen & analysieren
 * ======================================================================== */
function oh_fetch_website(): array {
    $base = rtrim(oh_config()['site_url'] ?? 'https://oh-haustechnik.de', '/');
    $ch = curl_init($base . '/');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_USERAGENT => 'Mozilla/5.0 OHBot']);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$html || $code >= 400) return ['ok' => false];
    preg_match('/<title>(.*?)<\/title>/is', $html, $t);
    preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1);
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $html, $h2);
    preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is', $html, $md);
    $text = trim(preg_replace('/\s+/', ' ', strip_tags(preg_replace('#<(script|style)[^>]*>.*?</\1>#is', ' ', $html))));
    return [
        'ok' => true,
        'title' => trim(strip_tags($t[1] ?? '')),
        'h1' => array_map(function($x){ return trim(strip_tags($x)); }, $h1[1] ?? []),
        'h2' => array_slice(array_map(function($x){ return trim(strip_tags($x)); }, $h2[1] ?? []), 0, 8),
        'desc' => trim($md[1] ?? ''),
        'hasForm' => stripos($html, '<form') !== false,
        'text' => mb_substr($text, 0, 4000),
    ];
}

function oh_website_analyze(?string &$err = null): ?array {
    $w = oh_fetch_website();
    if (empty($w['ok'])) { $err = 'Website konnte nicht geladen werden (Adresse unter ⚙️ prüfen).'; return null; }
    $ctx = "WEBSITE OH Haustechnik:\nTitel: {$w['title']}\nH1: " . implode(' | ', $w['h1'])
         . "\nH2: " . implode(' | ', $w['h2']) . "\nMeta-Beschreibung: {$w['desc']}\n"
         . "Kontaktformular vorhanden: " . ($w['hasForm'] ? 'ja' : 'NEIN') . "\n\nSeitentext (Auszug):\n{$w['text']}";
    $system = "Du bist Dilara, die Marketing-/Website-Agentin von OH Haustechnik (Elektriker Nürnberg). "
        . "Ziel: mehr hochwertige Anfragen über die Website (Conversion). Analysiere die echte Website und liefere 3-5 KONKRETE Optimierungen "
        . "(Überschrift/Headline, Vertrauenselemente, Kontaktformular, Klarheit des Angebots, Handlungsaufforderung, Mobil). "
        . "STELLE KEINE FRAGEN. Fertige Vorschläge mit erwarteter Verbesserung in Prozent. Du-Form. "
        . "Antworte AUSSCHLIESSLICH mit JSON: <web>[{\"titel\":\"Chef, ...\",\"was\":\"<konkrete Änderung, ggf. mit fertigem neuen Text>\",\"warum\":\"<warum, einfach>\",\"verbesserung\":\"+18% Anfragen\",\"dringlichkeit\":\"rot|gelb|gruen\"}]</web>";
    $resp = oh_ki($system, $ctx, 1800);
    if (!$resp) { $err = 'KI nicht verfügbar (Schlüssel/Guthaben).'; return null; }
    $json = $resp;
    if (preg_match('/<web>([\s\S]*?)<\/web>/', $resp, $m)) $json = $m[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '['); $rb = strrpos($json, ']');
    if ($lb !== false && $rb !== false) $json = substr($json, $lb, $rb - $lb + 1);
    $list = json_decode(trim($json), true);
    if (!is_array($list) || !count($list)) { $err = 'Dilara konnte keine klaren Vorschläge bilden – nochmal versuchen.'; return null; }
    $reco = [];
    foreach ($list as $i => $r) {
        $reco[] = array_merge($r, ['id' => 'W' . date('ymd') . $i, 'status' => 'offen', 'created' => time()]);
    }
    oh_write('website_reco', $reco);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Website analysiert: ' . count($reco) . ' Optimierungs-Vorschlag/Vorschläge erstellt');
    return $reco;
}

/* ==========================================================================
 * AGENTEN-WISSEN: jeder Agent kennt seine echten Daten (wird damit „schlauer")
 * ======================================================================== */
function oh_agent_context(string $agent): string {
    $leads = oh_read('leads', []);
    $offeneLeads = array_filter($leads, function($l){ return ($l['status'] ?? '') === 'neu'; });
    $namen = function($arr, $n = 6) {
        $out = [];
        foreach (array_slice(array_values($arr), 0, $n) as $l) {
            $out[] = ($l['name'] ?: ($l['email'] ?: '?')) . ' [' . ($l['stufe'] ?? '') . '] ' . mb_substr($l['details'] ?? '', 0, 60);
        }
        return $out ? "\n- " . implode("\n- ", $out) : ' keine';
    };
    if ($agent === 'kaan') {
        $em = oh_read('emails', []); $list = $em['list'] ?? [];
        $c = "DEINE AKTUELLEN DATEN (Kommunikation):\nUngelesene E-Mails (" . count($list) . "):";
        foreach (array_slice($list, 0, 8) as $m) $c .= "\n- " . ($m['subject'] ?: '(kein Betreff)') . " – " . ($m['from'] ?: '');
        $wa = oh_wa_open();
        $c .= "\nOffene WhatsApp (" . count($wa) . "):";
        foreach ($wa as $w) $c .= "\n- " . ($w['name'] ?: $w['from']) . ": " . mb_substr($w['text'] ?? '', 0, 80);
        $c .= "\nOffene Anfragen:" . $namen($offeneLeads);
        return $c;
    } elseif ($agent === 'dilara') {
        $e = null; $rep = oh_ads_report($e);
        $wreco = oh_read('website_reco', []);
        $c = "DEINE AKTUELLEN DATEN (Marketing):\n";
        if ($rep) $c .= "Google Ads 7 Tage: Kosten {$rep['summe']['kosten']}€, Anfragen {$rep['summe']['conv']}, Kosten/Anfrage " . ($rep['summe']['cpl'] ?? '?') . "€\n";
        $c .= "Website-Status: " . ((oh_read('web_status', [])['ok'] ?? null) ? 'erreichbar' : 'unbekannt/Problem') . "\n";
        $c .= "Offene Website-Vorschläge: " . count(array_filter($wreco, function($r){ return ($r['status'] ?? '') === 'offen'; }));
        return $c;
    } elseif ($agent === 'emre') {
        return "DEINE AKTUELLEN DATEN (Anfragen, die ein Angebot brauchen):" . $namen($offeneLeads, 8);
    } elseif ($agent === 'aylin') {
        $gew = array_filter($leads, function($l){ return in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen']); });
        return "DEINE AKTUELLEN DATEN (Buchhaltung):\nGewonnene Aufträge (Rechnung/Anzahlung prüfen): " . count($gew);
    } elseif ($agent === 'yusuf') {
        $gew = array_filter($leads, function($l){ return in_array($l['status'] ?? '', ['gewonnen']); });
        return "DEINE AKTUELLEN DATEN (Projekte):\nLaufende/gewonnene Projekte zum Planen: " . count($gew);
    } elseif ($agent === 'baran') {
        return "DEINE AKTUELLEN DATEN (Personal):\nOffene Anfragen gesamt: " . count($offeneLeads) . " – aktuell Ein-Mann-Betrieb. Prüfe, ob die Auslastung Verstärkung nötig macht.";
    } elseif ($agent === 'mert') {
        return oh_wissen_summary();
    }
    return '';
}
