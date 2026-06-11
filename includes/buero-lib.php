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

    $system = "Du bist der digitale Geschäftsführer von OH Haustechnik (Elektriker Nürnberg, Kleinunternehmer). "
        . "ZIEL: möglichst viele HOCHWERTIGE Anfragen für Altbausanierung, Wohnungsmodernisierung, komplette Elektro-Sanierung (3-4 Zimmer), Zähleranlagen, Unterverteilungen und Smart-Home. "
        . "Qualität vor Menge. Werbekosten senken, profitable Aufträge gewinnen.\n"
        . "Analysiere die Google-Ads-Daten wie ein cleverer Profi und finde die wichtigsten Optimierungen. "
        . "Sprich EINFACH, wie ein guter Mitarbeiter zum Chef – KEINE Fachbegriffe, kurze Sätze, immer mit 'Chef' anreden.\n"
        . "Gib AUSSCHLIESSLICH einen JSON-Block in genau diesem Format zurück (3-6 Empfehlungen, wichtigste zuerst), nichts davor/danach:\n"
        . "<reco>[{\"titel\":\"Chef, ...\",\"was\":\"<was genau ändern>\",\"warum\":\"<warum, einfach>\",\"anfragen\":\"<z.B. 2-4 pro Woche>\",\"wahrscheinlichkeit\":\"<hoch|mittel|niedrig>\",\"dringlichkeit\":\"<rot|gelb|gruen>\",\"typ\":\"<negativ_keyword|keyword|budget|gebot|standort|zeit|anzeige|info>\",\"wert\":\"<z.B. das auszuschließende Suchwort oder das neue Keyword>\",\"schritte\":\"<1-2 ganz einfache Schritte zum Umsetzen>\"}]</reco>\n"
        . "Konzentriere Dich auf: Geld-verbrennende Suchbegriffe als negative Keywords ausschließen (z.B. 'job','gehalt','kostenlos','ausbildung','selber'), starke Sanierungs-Keywords pushen, Budget auf das lenken was Anfragen bringt. "
        . "Nutze die MARKT-Daten: Wenn er viele Suchen wegen zu wenig Budget verliert, empfiehl Budget erhöhen (mit erwartetem Gewinn). Wenn wegen Rang/Gebot, empfiehl Gebot/Anzeige verbessern. Sag konkret, wie viel Markt-Anteil (mehr Anfragen) er dadurch gewinnt.";

    $resp = oh_ki($system, $ctx, 1800);
    if (!$resp) { $err = 'KI-Analyse nicht verfügbar (Anthropic-Schlüssel prüfen).'; return null; }
    if (!preg_match('/<reco>([\s\S]*?)<\/reco>/', $resp, $mch)) { $err = 'KI-Antwort unlesbar.'; return null; }
    $list = json_decode(trim($mch[1]), true);
    if (!is_array($list)) { $err = 'KI-Daten ungültig.'; return null; }

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
    return $reco;
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
            return ['executed' => true, 'msg' => "Erledigt, Chef! \"{$wert}\" wird ab sofort ausgeschlossen – das spart Werbegeld."];
        }
        return ['executed' => false, 'msg' => 'Konnte nicht automatisch ausgeführt werden (' . $err . '). Bitte kurz manuell im Ads-Konto eintragen.'];
    }
    return ['executed' => false, 'msg' => 'Notiert, Chef. Diese Änderung bitte kurz selbst umsetzen – die Schritte stehen in der Empfehlung.'];
}

