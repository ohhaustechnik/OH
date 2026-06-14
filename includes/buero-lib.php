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

/* ==========================================================================
 * WERT-EINSTUFUNG eines Leads – erkennt Großauftrag-Potenzial aus Kategorie,
 * Objektgröße, Zeitraum und Beschreibung. Steuert die Priorisierung: große
 * Sanierungsanfragen werden als HOT mit Vorrang markiert, Kleinkram fällt ab.
 * ======================================================================== */
function oh_lead_wert(array $l): array {
    $zimmerTxt = !empty($l['zimmer']) ? ($l['zimmer'] . ' zimmer') : '';
    $txt = mb_strtolower(($l['kategorie'] ?? '') . ' ' . ($l['objekttyp'] ?? '') . ' ' . $zimmerTxt . ' ' . ($l['objektgroesse'] ?? '') . ' ' . ($l['zeitraum'] ?? '') . ' ' . ($l['details'] ?? ''));
    $gross = ['komplettsanierung', 'komplett', 'sanierung', 'altbau', 'modernisier', 'kernsanierung', 'entkernung', 'unterverteilung', 'zählerschrank', 'zaehlerschrank', 'zähleranlage', 'knx', 'smart home', 'smarthome', 'smart-home', 'netzwerkverkabelung', 'strukturierte verkabelung', 'neubau', 'rohbau', 'etagen', 'komplette elektro', 'neue leitungen', 'wohnung saniere', 'haus saniere', 'gewerbe', 'mehrfamilien', 'dachgeschoss'];
    $klein = ['lampe', 'leuchte', 'steckdose', 'schalter', 'sicherung', 'fehlersuche', 'reparatur', 'kleinigkeit', 'wackelt', 'anschließen', 'anschliessen', 'dimmer'];
    $score = 0;
    foreach ($gross as $w) if (mb_strpos($txt, $w) !== false) $score += 2;
    foreach ($klein as $w) if (mb_strpos($txt, $w) !== false) $score -= 1;
    if (preg_match('/\b([89]\d|[1-9]\d{2,})\s?m/u', $txt)) $score += 2;                  // >= 80 m²
    if (preg_match('/\b([4-9]|1\d)\s?(zimmer|räume|raeume)/u', $txt)) $score += 1;        // 4+ Zimmer
    if (mb_strpos($txt, 'ganze wohnung') !== false || mb_strpos($txt, 'ganzes haus') !== false || mb_strpos($txt, 'komplettes haus') !== false) $score += 2;
    $otyp = mb_strtolower($l['objekttyp'] ?? '');
    if ($otyp === 'haus')            $score += 1;   // ganzes Haus = größeres Potenzial
    if ($otyp === 'mehrere objekte') $score += 2;   // mehrere Objekte = klarer Großauftrag
    $klasse = $score >= 3 ? 'gross' : ($score >= 1 ? 'mittel' : 'klein');
    $eur = $klasse === 'gross' ? '5.000–25.000 €' : ($klasse === 'mittel' ? '1.000–5.000 €' : 'bis ~1.000 €');
    return ['klasse' => $klasse, 'score' => $score, 'eur' => $eur];
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
        'first_response_ts' => 0,      // wann zum ersten Mal reagiert wurde (Reaktionszeit-Messung)
        'angebot_ts'=> 0,
        'abschluss_ts' => 0,
        'bewertung_angefragt' => false,
        'notizen'   => '',
        'verlauf'   => [],
    ], $data);

    if (empty($lead['stufe'])) {
        $lead['stufe'] = oh_classify($lead);
    }
    // Wert-Einstufung: Großauftrag-Potenzial erkennen und mit Vorrang behandeln
    $w = oh_lead_wert($lead);
    $lead['wert_klasse'] = $w['klasse'];
    $lead['wert_score']  = $w['score'];
    $lead['wert_eur']    = $w['eur'];
    if ($w['klasse'] === 'gross') {
        $lead['stufe'] = 'HOT';
        $lead['verlauf'][] = ['ts' => time(), 'text' => '★ GROSSAUFTRAG-Potenzial erkannt (' . $w['eur'] . ') – mit Vorrang behandeln, schnell anrufen.'];
    }
    $lead['verlauf'][] = ['ts' => time(), 'text' => 'Lead angelegt (' . $lead['source'] . ', ' . $lead['stufe'] . ', Wert: ' . $w['klasse'] . ')'];

    array_unshift($leads, $lead);
    oh_write('leads', $leads);
    // Anfrage an das ganze Team verteilen: jeder Agent versteht sie und handelt
    if (function_exists('oh_lead_broadcast')) oh_lead_broadcast($lead, $w);
    // SOFORT-ALARM an den Chef bei heißen / großen Anfragen – die erste Stunde entscheidet
    if (($lead['stufe'] ?? '') === 'HOT' || ($lead['wert_klasse'] ?? '') === 'gross') {
        oh_notify_hot_lead($lead);
    }
    if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Neue ' . $lead['stufe'] . '-Anfrage erfasst: ' . ($lead['name'] ?: ($lead['email'] ?: $lead['id'])));
    return $lead;
}

/**
 * Sofort-Benachrichtigung an den Chef, sobald ein heißer/großer Lead reinkommt.
 * Nutzt den bestehenden Mailversand (keine zusätzlichen Schlüssel nötig). Eine
 * optionale Webhook-URL (Telegram/Slack/Pushover) wird ebenfalls bedient, wenn
 * in der Config 'hot_lead_webhook' hinterlegt ist.
 */
function oh_notify_hot_lead(array $lead): void {
    $cfg = function_exists('oh_config') ? oh_config() : [];
    $to  = $cfg['chef_mail'] ?? $cfg['gmail_user'] ?? 'oh.haustechnik@gmail.com';
    $name = $lead['name'] ?: ($lead['email'] ?: ($lead['telefon'] ?: 'Anfrage'));
    $tel  = $lead['telefon'] ?? '';
    $kat  = $lead['kategorie'] ?: '-';
    $eur  = $lead['wert_eur'] ?? '';
    $ort  = trim(($lead['plz'] ?? '') . ' ' . ($lead['ort'] ?? ''));
    $kurz = mb_substr((string)($lead['details'] ?? ''), 0, 200);
    $subj = '🔥 HEISSER LEAD: ' . $name . ' – ' . $kat . ($eur ? ' (' . $eur . ')' : '');
    $body = "Gerade reingekommen – bitte SCHNELL zurückrufen (die erste Stunde entscheidet):\n\n"
          . "Name:     $name\n"
          . ($tel ? "Telefon:  $tel  (jetzt anrufen!)\n" : '')
          . ($lead['email'] ? "E-Mail:   {$lead['email']}\n" : '')
          . "Leistung: $kat\n"
          . ($ort ? "Ort:      $ort\n" : '')
          . ($eur ? "Potenzial:$eur\n" : '')
          . ($kurz ? "Wunsch:   $kurz\n" : '')
          . "Quelle:   " . ($lead['source'] ?? 'website') . "\n\n"
          . "Im Büro-System öffnen: https://oh-haustechnik.de/buero.php\n";
    if (function_exists('oh_send_mail') && filter_var($to, FILTER_VALIDATE_EMAIL)) {
        oh_send_mail($to, $subj, $body, $lead['email'] ?: null);
    }
    // Optionaler Push-Webhook (Telegram/Pushover/Slack) – nur wenn konfiguriert
    if (!empty($cfg['hot_lead_webhook'])) {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => 4,
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode(['text' => $subj . "\n" . $body], JSON_UNESCAPED_UNICODE),
        ]]);
        @file_get_contents($cfg['hot_lead_webhook'], false, $ctx);
    }
}

/* ==========================================================================
 * LEAD-BROADCAST: Eine neue Anfrage wird an JEDEN relevanten Agenten verteilt –
 * als Gedächtnis-Eintrag (taucht im Wissensarchiv, Kontext & in der Runde auf)
 * und als verbindliche Postfach-Nachricht (Empfänger MUSS reagieren). So
 * „versteht" das ganze Team, welche Anfrage rein kam, und verbreitet die Daten.
 * ======================================================================== */
function oh_lead_broadcast(array $lead, array $w = []): void {
    if (!function_exists('oh_agent_mem_add')) return;
    $name    = $lead['name'] ?: ($lead['email'] ?: ($lead['telefon'] ?: 'Anfrage'));
    $kat     = $lead['kategorie'] ?: '-';
    $ort     = $lead['ort'] ?? '';
    $src     = $lead['source'] ?? 'website';
    $kontakt = trim(($lead['telefon'] ?? '') . ' ' . ($lead['email'] ?? '')) ?: '—';
    $stufe   = $lead['stufe'] ?? 'WARM';
    $klasse  = $w['klasse'] ?? ($lead['wert_klasse'] ?? 'mittel');
    $kurz    = mb_substr((string)($lead['details'] ?? ''), 0, 140);
    $base    = "Neue $stufe-Anfrage [$klasse]: $name – $kat" . ($ort ? " ($ort)" : '') . ". Kontakt: $kontakt" . ($kurz ? ". Wunsch: $kurz" : '');

    oh_agent_mem_add('kaan',   $base . ' → schnell antworten.', 'fund');
    oh_agent_mem_add('emre',   "Anfrage $name ($kat, $klasse) → Kalkulation/Angebot vorbereiten.", 'fund');
    oh_agent_mem_add('dilara', "Anfrage über Quelle '$src' ($kat) – diese Quelle bringt Anfragen, beobachten/skalieren.", 'fund');
    oh_agent_mem_add('mert',   $base, 'fund');
    if ($klasse === 'gross') {
        oh_agent_mem_add('yusuf', "Mögliches Projekt: $name ($kat) – Termin & Material grob vorplanen.", 'fund');
        oh_agent_mem_add('aylin', "Großauftrag-Chance: $name ($kat) – auf Anzahlung/Abrechnung vorbereiten, sobald gewonnen.", 'fund');
    }
    if (function_exists('oh_agent_msg_send')) {
        oh_agent_msg_send('system', 'kaan', "Neue Anfrage: $name ($kat). Bitte heute antworten. Kontakt: $kontakt");
        if ($klasse !== 'klein') oh_agent_msg_send('system', 'emre', "Anfrage $name ($kat, $klasse) – Festpreis kalkulieren / Angebot vorbereiten.");
        if ($klasse === 'gross')  oh_agent_msg_send('system', 'yusuf', "Großprojekt-Anfrage $name ($kat) – Termin/Material grob planen.");
    }
    if (function_exists('oh_log_activity')) oh_log_activity('kaan', "Anfrage ans Team verteilt: $name ($stufe/$klasse)");
}

/* ==========================================================================
 * VERKAUFS-PIPELINE (Bau-Standard) + Quote-to-Cash + KPI-Cockpit.
 * Phasen je Lead; Status/Zeitstempel werden für die bestehenden Automatiken
 * (Follow-up, Bewertung, Mahnung, Lernen) synchron gehalten.
 * ======================================================================== */
function oh_lead_phasen(): array {
    return [
        ['id' => 'anfrage',     'label' => 'Anfrage',     'icon' => '📥'],
        ['id' => 'besichtigung','label' => 'Besichtigung','icon' => '🔎'],
        ['id' => 'angebot',     'label' => 'Angebot',     'icon' => '📄'],
        ['id' => 'auftrag',     'label' => 'Auftrag',     'icon' => '✅'],
        ['id' => 'baustelle',   'label' => 'Baustelle',   'icon' => '🏗️'],
        ['id' => 'abnahme',     'label' => 'Abnahme',     'icon' => '🤝'],
        ['id' => 'rechnung',    'label' => 'Rechnung',    'icon' => '🧾'],
        ['id' => 'bezahlt',     'label' => 'Bezahlt',     'icon' => '💶'],
        ['id' => 'verloren',    'label' => 'Verloren',    'icon' => '✕'],
    ];
}
function oh_lead_phase(array $l): string {
    if (!empty($l['phase'])) return $l['phase'];
    switch ($l['status'] ?? 'neu') {
        case 'angebot_raus': case 'nachgefasst': return 'angebot';
        case 'gewonnen': return 'auftrag';
        case 'abgeschlossen': return 'bezahlt';
        case 'verloren': return 'verloren';
        default: return 'anfrage';
    }
}
function oh_lead_set_phase(string $id, string $phase, ?string &$err = null): ?array {
    if (!in_array($phase, array_column(oh_lead_phasen(), 'id'), true)) { $err = 'Unbekannte Phase.'; return null; }
    $lead = oh_get_lead($id);
    if (!$lead) { $err = 'Anfrage nicht gefunden.'; return null; }
    $now = time();
    $patch = ['phase' => $phase];
    switch ($phase) {
        case 'angebot':   $patch['status'] = 'angebot_raus'; if (empty($lead['angebot_ts'])) $patch['angebot_ts'] = $now; break;
        case 'auftrag':   $patch['status'] = 'gewonnen'; break;
        case 'baustelle': $patch['status'] = 'gewonnen'; break;
        case 'abnahme':   $patch['status'] = 'abgeschlossen'; if (empty($lead['abschluss_ts'])) $patch['abschluss_ts'] = $now; break;
        case 'rechnung':  $patch['status'] = 'abgeschlossen'; break;
        case 'bezahlt':   $patch['status'] = 'abgeschlossen'; $patch['bezahlt_ts'] = $now; break;
        case 'verloren':  $patch['status'] = 'verloren'; break;
    }
    $upd = oh_update_lead($id, $patch, 'Pipeline → ' . $phase);
    if (!$upd) { $err = 'Konnte nicht aktualisieren.'; return null; }
    $nm = $upd['name'] ?: $id;
    if (function_exists('oh_agent_mem_add')) {
        if ($phase === 'baustelle') oh_agent_mem_add('yusuf', "Baustelle aktiv: $nm (" . ($upd['kategorie'] ?? '') . ") – Termin & Material organisieren.", 'fund');
        if ($phase === 'rechnung')  oh_agent_mem_add('aylin', "Rechnung fällig: $nm – stellen/prüfen.", 'fund');
        if ($phase === 'auftrag')   oh_agent_mem_add('mert', "Auftrag gewonnen: $nm – sauber abwickeln, Termin sichern.", 'prio');
    }
    // Auto-Rechnungsentwurf in Lexware bei Phase 'rechnung'
    if ($phase === 'rechnung' && empty($upd['lex_invoice_id']) && function_exists('oh_lex_create_invoice') && !empty(oh_config()['lexware_api_key'])) {
        $lerr = null; $inv = oh_lex_create_invoice($upd, $lerr);
        if ($inv && !empty($inv['id'])) { $upd = oh_update_lead($id, ['lex_invoice_id' => $inv['id']], 'Lexware-Rechnungsentwurf erstellt'); }
        elseif ($lerr) { oh_update_lead($id, [], 'Lexware-Rechnung fehlgeschlagen: ' . $lerr); }
    }
    return $upd;
}
function oh_angebot_save(string $id, float $betrag, string $text, array $positionen = [], ?string &$err = null): ?array {
    if (!oh_get_lead($id)) { $err = 'Anfrage nicht gefunden.'; return null; }
    $pos = [];
    foreach ($positionen as $p) {
        if (!is_array($p)) continue;
        $pos[] = ['pos' => mb_substr((string)($p['pos'] ?? ''), 0, 160), 'menge' => (float)($p['menge'] ?? 1), 'einzel' => (float)($p['einzel'] ?? 0)];
    }
    $pos = array_slice($pos, 0, 30);
    if ($pos) { $betrag = 0; foreach ($pos as $p) $betrag += $p['menge'] * $p['einzel']; }
    oh_update_lead($id, ['angebot_betrag' => round($betrag, 2), 'angebot_text' => mb_substr($text, 0, 5000), 'angebot_positionen' => $pos, 'angebot_ts' => time()], 'Angebot erstellt: ' . number_format($betrag, 0, ',', '.') . '€');
    return oh_lead_set_phase($id, 'angebot', $err);
}

/** Emre schlägt aus der Anfrage realistische Angebots-Positionen vor (KI). */
function oh_angebot_vorschlag(array $lead, ?string &$err = null): ?array {
    $ctx = "Anfrage: " . ($lead['name'] ?: '-') . "\nLeistung/Kategorie: " . ($lead['kategorie'] ?: '-')
         . "\nObjektgröße: " . ($lead['objektgroesse'] ?? '') . "\nOrt: " . ($lead['ort'] ?? '')
         . "\nBeschreibung: " . mb_substr((string)($lead['details'] ?? ''), 0, 600);
    $sys = "Du bist Emre, Kalkulator von OH Haustechnik (Elektriker Nürnberg, Kleinunternehmer, 0% USt, Festpreise). "
        . "Erstelle aus der Anfrage realistische Angebots-POSITIONEN. Kalkulationslogik: Stundensatz 136€, ca. 1.156€ pro Manntag; "
        . "Unterputz-Sanierung 100m²≈18.000-20.000€, 150m²≈23.000-26.000€; Aufputz ca. 40% günstiger. Lieber etwas vorsichtig (nicht zu billig). "
        . "Antworte AUSSCHLIESSLICH mit JSON: <pos>[{\"pos\":\"Leistungsbeschreibung\",\"menge\":1,\"einzel\":0}]</pos> "
        . "(3-7 Positionen, 'einzel' = Netto-Einzelpreis in €, sinnvolle Mengen/Einheiten in der Bezeichnung).";
    $resp = oh_ki($sys, $ctx, 1500);
    if (!$resp) { $err = 'KI nicht verfügbar (Schlüssel/Guthaben).'; return null; }
    $json = $resp;
    if (preg_match('/<pos>([\s\S]*?)<\/pos>/', $resp, $m)) $json = $m[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '['); $rb = strrpos($json, ']');
    if ($lb !== false && $rb !== false) $json = substr($json, $lb, $rb - $lb + 1);
    $list = json_decode(trim($json), true);
    if (!is_array($list) || !count($list)) { $err = 'Emre konnte keine klaren Positionen bilden – nochmal versuchen.'; return null; }
    $out = [];
    foreach ($list as $p) { if (is_array($p)) $out[] = ['pos' => mb_substr((string)($p['pos'] ?? ''), 0, 160), 'menge' => (float)($p['menge'] ?? 1), 'einzel' => (float)($p['einzel'] ?? 0)]; }
    return $out;
}

/** Sendet das gespeicherte Angebot per E-Mail an den Kunden (und setzt Phase 'angebot'). */
function oh_angebot_send(string $id, ?string &$err = null): ?array {
    $lead = oh_get_lead($id); if (!$lead) { $err = 'Anfrage nicht gefunden.'; return null; }
    $to = $lead['email'] ?? '';
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { $err = 'Keine gültige E-Mail beim Kunden hinterlegt.'; return null; }
    $betrag = (float)($lead['angebot_betrag'] ?? 0);
    if ($betrag <= 0) { $err = 'Noch kein Angebot/Betrag vorhanden.'; return null; }
    $vorname = trim(explode(' ', $lead['name'] ?: '')[0]) ?: 'Kunde';
    $body = "Hallo $vorname,\n\nvielen Dank für Ihre Anfrage. Gerne unterbreiten wir Ihnen folgendes Festpreis-Angebot:\n\n";
    $pos = $lead['angebot_positionen'] ?? [];
    if ($pos) {
        foreach ($pos as $p) {
            $z = (float)($p['menge'] ?? 1) * (float)($p['einzel'] ?? 0);
            $body .= '• ' . ($p['pos'] ?? '') . '  =  ' . number_format($z, 2, ',', '.') . " €\n";
        }
        $body .= "\n";
    } elseif (!empty($lead['angebot_text'])) {
        $body .= $lead['angebot_text'] . "\n\n";
    }
    $body .= "Gesamt-Festpreis: " . number_format($betrag, 2, ',', '.') . " €\n";
    $body .= "(Gemäß § 19 UStG keine Umsatzsteuer – Kleinunternehmer.)\n\nDas Angebot ist 30 Tage gültig. Bei Fragen erreichen Sie uns unter 0175 7481006.\n\nViele Grüße\nOH Haustechnik";
    $res = oh_send_mail($to, 'Ihr Festpreis-Angebot – OH Haustechnik', $body);
    if (empty($res['ok'])) { $err = $res['info'] ?? 'Versand fehlgeschlagen.'; return null; }
    $e2 = null; oh_lead_set_phase($id, 'angebot', $e2);
    oh_update_lead($id, ['angebot_gesendet_ts' => time()], 'Angebot per E-Mail gesendet an ' . $to);
    if (function_exists('oh_log_activity')) oh_log_activity('emre', 'Angebot per E-Mail gesendet an ' . ($lead['name'] ?: $to));
    return oh_get_lead($id);
}
function oh_kpi(): array {
    $z = function_exists('oh_ziel_status') ? oh_ziel_status() : [];
    $leads = oh_read('leads', []);
    $pipeline = 0.0; $won = 0; $lost = 0; $offard = 0; $neu7 = 0; $cut = time() - 7 * 86400;
    foreach ($leads as $l) {
        $ph = oh_lead_phase($l);
        $b = (float)($l['angebot_betrag'] ?? 0);
        if (!in_array($ph, ['bezahlt', 'verloren'], true) && $b > 0) $pipeline += $b;
        if (in_array($ph, ['auftrag', 'baustelle', 'abnahme', 'rechnung', 'bezahlt'], true)) $won++;
        if ($ph === 'verloren') $lost++;
        if ($ph === 'rechnung') $offard += $b;
        if ((int)($l['created'] ?? 0) >= $cut) $neu7++;
    }
    return [
        'umsatz_ist' => round($z['ist'] ?? 0), 'ziel' => round($z['betrag'] ?? 1000000),
        'soll' => round($z['soll'] ?? 0), 'im_plan' => $z['im_plan'] ?? false,
        'pipeline_wert' => round($pipeline), 'quote' => ($won + $lost) > 0 ? round($won / ($won + $lost) * 100) : 0,
        'gewonnen' => $won, 'verloren' => $lost, 'offene_rechnungen' => round($offard), 'neue_woche' => $neu7,
    ];
}

function oh_get_lead(string $id): ?array {
    foreach (oh_read('leads', []) as $l) {
        if (($l['id'] ?? '') === $id) return $l;
    }
    return null;
}

function oh_update_lead(string $id, array $patch, ?string $logText = null): ?array {
    $leads = oh_read('leads', []);
    $updated = null; $vorher = '';
    foreach ($leads as &$l) {
        if (($l['id'] ?? '') === $id) {
            $vorher = $l['status'] ?? '';
            $l = array_merge($l, $patch);
            if ($logText) $l['verlauf'][] = ['ts' => time(), 'text' => $logText];
            $updated = $l;
            break;
        }
    }
    unset($l);
    if ($updated) {
        oh_write('leads', $leads);
        // STUFE 5: aus Ergebnissen lernen (gewonnen/verloren -> Agenten-Gedächtnis)
        if (isset($patch['status']) && $patch['status'] !== $vorher) {
            if (function_exists('oh_outcome_lernen')) oh_outcome_lernen($updated, $vorher);
            // Änderungs-Journal: Status-Wechsel sind rückgängig machbar
            if (function_exists('oh_change_log')) {
                $nm = $updated['name'] ?: ($updated['email'] ?: $id);
                oh_change_log('lead_status', "Status von \"$nm\" geändert", $vorher, $patch['status'], $id);
            }
        }
    }
    return $updated;
}

/* ==========================================================================
 * STUFE 5: OUTCOME-LERNEN – das Team lernt aus echten Ergebnissen.
 * Bei gewonnen/verloren wandert die Erkenntnis (Quelle, Kategorie) ins
 * Gedächtnis von Dilara (Marketing-Steuerung) und Emre (Kalkulation).
 * ======================================================================== */
function oh_outcome_lernen(array $lead, string $vorher): void {
    $neu  = $lead['status'] ?? '';
    $name = $lead['name'] ?: ($lead['email'] ?: ($lead['id'] ?? '?'));
    $src  = !empty($lead['source']) ? $lead['source'] : 'unbekannt';
    $kat  = !empty($lead['kategorie']) ? $lead['kategorie'] : '-';
    if (in_array($neu, ['gewonnen', 'abgeschlossen']) && !in_array($vorher, ['gewonnen', 'abgeschlossen'])) {
        if (function_exists('oh_agent_mem_add')) {
            oh_agent_mem_add('dilara', "GEWONNEN: $name (Quelle: $src, $kat) – diese Quelle/Kategorie funktioniert, mehr Budget/Fokus dorthin!", 'fund');
            oh_agent_mem_add('emre', "AUFTRAG GEWONNEN: $name ($kat) – Kalkulation hat überzeugt, bei ähnlichen Anfragen genauso ansetzen.", 'fund');
        }
        if (function_exists('oh_log_activity')) oh_log_activity('mert', "Auftrag gewonnen: $name (Quelle: $src) – Erkenntnis ans Team verteilt.");
    } elseif ($neu === 'verloren' && $vorher !== 'verloren') {
        if (function_exists('oh_agent_mem_add')) {
            oh_agent_mem_add('dilara', "VERLOREN: $name (Quelle: $src, $kat) – prüfen ob Quelle/Ansprache passt oder Anfragequalität schwach ist.", 'fund');
            oh_agent_mem_add('emre', "ANGEBOT VERLOREN: $name ($kat) – Preis und Reaktionszeit prüfen, daraus lernen.", 'fund');
        }
    }
}

/** Lern-Zusammenfassung: welche Quelle bringt echte Abschlüsse (für Dilara/Mert/Runde). */
function oh_outcome_summary(): string {
    $leads = oh_read('leads', []);
    if (!$leads) return '';
    $bySrc = [];
    foreach ($leads as $l) {
        $s = !empty($l['source']) ? $l['source'] : 'unbekannt';
        if (!isset($bySrc[$s])) $bySrc[$s] = ['gesamt' => 0, 'gewonnen' => 0, 'verloren' => 0];
        $bySrc[$s]['gesamt']++;
        if (in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen'])) $bySrc[$s]['gewonnen']++;
        if (($l['status'] ?? '') === 'verloren') $bySrc[$s]['verloren']++;
    }
    $lex = oh_read('lexware', []);
    $avg = !empty($lex['bezahlt_jahr_anzahl']) ? (int)round(($lex['bezahlt_jahr_summe'] ?? 0) / max(1, (int)$lex['bezahlt_jahr_anzahl'])) : 0;
    $out = "LERN-DATEN (was wirklich Abschlüsse bringt, je Quelle):";
    foreach ($bySrc as $src => $v) {
        $q = $v['gesamt'] ? (int)round($v['gewonnen'] / $v['gesamt'] * 100) : 0;
        $out .= "\n- $src: {$v['gesamt']} Anfragen, {$v['gewonnen']} gewonnen ({$q}%)"
              . ($avg && $v['gewonnen'] ? " ≈ " . number_format($v['gewonnen'] * $avg, 0, ',', '.') . "€" : '')
              . ($v['verloren'] ? ", {$v['verloren']} verloren" : '');
    }
    return $out;
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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$resp) return null;
    $d = json_decode($resp, true);
    if (!isset($d['content'])) {
        // Fehler auswerten – speziell aufgebrauchtes Guthaben dem Chef melden
        $emsg = $d['error']['message'] ?? '';
        $leer = stripos($emsg, 'credit balance') !== false
             || stripos($emsg, 'too low') !== false
             || stripos($emsg, 'billing') !== false
             || stripos($emsg, 'insufficient') !== false
             || stripos($emsg, 'purchase credits') !== false;
        if ($leer && function_exists('oh_alert_guthaben')) oh_alert_guthaben($emsg);
        return null;
    }
    // Erfolg: Guthaben-Alarm zurücksetzen, damit die nächste Leere sofort wieder meldet
    $ga = oh_read('guthaben_alert', []);
    if (!empty($ga['ts'])) oh_write('guthaben_alert', ['ts' => 0]);
    $out = '';
    foreach ($d['content'] as $c) {
        if (($c['type'] ?? '') === 'text') $out .= $c['text'];
    }
    return trim($out);
}

/* --------------------------------------------------------------------------
 * GUTHABEN-ALARM: Ist das Anthropic-Guthaben leer, kann KEIN Agent mehr
 * denken. Dann bekommt der Chef automatisch eine "SEHR WICHTIG"-Mail mit der
 * Bitte aufzuladen. Gedrosselt auf höchstens 1 Mail / 6 Stunden (sonst Spam
 * bei stündlichem Cron mit vielen KI-Aufrufen).
 * ------------------------------------------------------------------------ */
function oh_alert_guthaben(string $detail = ''): void {
    $cfg = oh_config();
    $to  = $cfg['alert_email'] ?? ($cfg['gmail_user'] ?? '');
    $a    = oh_read('guthaben_alert', []);
    $last = $a['ts'] ?? 0;
    if ((time() - $last) < 6 * 3600) return; // Drosselung

    if (function_exists('oh_log_activity')) {
        oh_log_activity('mert', 'SEHR WICHTIG: KI-Guthaben aufgebraucht – bitte aufladen, sonst steht das ganze Team.');
    }
    if ($to !== '' && function_exists('oh_send_mail')) {
        $body = "SEHR WICHTIG – bitte sofort handeln, Chef.\n\n"
              . "Das KI-Guthaben (Anthropic) ist aufgebraucht. Solange es leer ist, kann das gesamte KI-Team NICHT arbeiten:\n"
              . "keine Anfragen-Bearbeitung, keine Angebote, keine Auswertung, keine Optimierung, kein Wachstum Richtung 1.000.000 €.\n\n"
              . "➡ Jetzt aufladen: https://console.anthropic.com/settings/billing\n\n"
              . ($detail !== '' ? "Technische Meldung: " . $detail . "\n\n" : '')
              . "Diese Mail kommt automatisch vom Büro-System (Erinnerung höchstens alle 6 Stunden).";
        oh_send_mail($to, '🔴 SEHR WICHTIG: KI-Guthaben aufladen – OH Haustechnik', $body);
    }
    oh_write('guthaben_alert', ['ts' => time(), 'detail' => $detail]);
}

/* ==========================================================================
 * FREIGABE-WORKFLOW (Baustein A) – die zentrale Entscheidungs-Warteschlange.
 * Agenten legen hier Punkte ab, die der Chef freigeben/entscheiden soll.
 * Labels: "Antwort notwendig", "Freigabe erforderlich", "Umsatzrelevant",
 * "Risiko", "Sehr wichtig", "Info". Speicher: daten/freigaben.json
 * ======================================================================== */

/** Legt einen Freigabe-Punkt an (mit Dedupe über $item['dedup']). Gibt ID oder null. */
function oh_freigabe_add(array $item): ?string {
    $f = oh_read('freigaben', []);
    if (!empty($item['dedup'])) {
        foreach ($f as $x) { if (($x['dedup'] ?? '') === $item['dedup']) return null; }
    }
    $id = 'F' . date('ymdHis') . substr((string)mt_rand(100, 999), 0, 3);
    $entry = array_merge([
        'dedup' => '', 'agent' => 'mert', 'kanal' => 'system',
        'kategorie' => 'Info', 'prio' => 'gelb', 'titel' => '', 'warum' => '',
        'typ' => 'info', 'vorschlag' => '', 'auto' => false,
        'from' => '', 'to' => '', 'ref' => '',
    ], $item);
    $entry['id'] = $id;
    $entry['ts'] = time();
    $entry['status'] = 'offen';
    $f[] = $entry;
    if (count($f) > 150) $f = array_slice($f, -150);
    oh_write('freigaben', $f);
    return $id;
}

/** Liefert Freigabe-Punkte (neueste zuerst). $status='offen' | 'alle'. */
function oh_freigaben(string $status = 'offen'): array {
    $f = array_reverse(oh_read('freigaben', []));
    if ($status === 'alle') return $f;
    return array_values(array_filter($f, function($x) use ($status){ return ($x['status'] ?? 'offen') === $status; }));
}

/** Ändert einen Freigabe-Punkt. Gibt den geänderten Eintrag zurück. */
function oh_freigabe_update(string $id, array $patch): ?array {
    $f = oh_read('freigaben', []); $hit = null;
    foreach ($f as &$x) { if (($x['id'] ?? '') === $id) { $x = array_merge($x, $patch); $hit = $x; } }
    unset($x);
    oh_write('freigaben', $f);
    return $hit;
}

/* ==========================================================================
 * ÄNDERUNGS-JOURNAL MIT RÜCKGÄNGIG – jede übernommene Änderung wird mit
 * altem Wert, neuem Wert und Zeitstempel protokolliert (daten/changes.json)
 * und kann per Klick zurückgenommen werden. E-Mails, die bereits raus sind,
 * sind ehrlich als "nicht rückholbar" markiert.
 * ======================================================================== */
function oh_change_log(string $typ, string $titel, $alt, $neu, string $ref = '', bool $undoable = true): string {
    $c = oh_read('changes', []);
    $id = 'C' . date('ymdHis') . substr((string)mt_rand(100, 999), 0, 3);
    $c[] = ['id' => $id, 'ts' => time(), 'typ' => $typ, 'titel' => $titel,
            'alt' => $alt, 'neu' => $neu, 'ref' => $ref,
            'undoable' => $undoable, 'status' => 'aktiv', 'undo_ts' => 0];
    if (count($c) > 200) $c = array_slice($c, -200);
    oh_write('changes', $c);
    return $id;
}

function oh_change_undo(string $id, ?string &$err = null): bool {
    $c = oh_read('changes', []);
    $hit = null; $idx = -1;
    foreach ($c as $i => $x) { if (($x['id'] ?? '') === $id) { $hit = $x; $idx = $i; break; } }
    if (!$hit) { $err = 'Eintrag nicht gefunden.'; return false; }
    if (($hit['status'] ?? '') !== 'aktiv') { $err = 'Bereits rückgängig gemacht.'; return false; }
    if (empty($hit['undoable'])) { $err = 'Nicht rückholbar (z.B. E-Mail bereits versendet).'; return false; }

    $ok = false;
    switch ($hit['typ']) {
        case 'lead_status': // Lead-/Baustellen-Status zurücksetzen
            $ok = (bool)oh_update_lead($hit['ref'], ['status' => (string)$hit['alt']], 'Rückgängig gemacht: Status zurück auf "' . $hit['alt'] . '"');
            break;
        case 'task': // erledigten Auftrag wieder öffnen
            $t = oh_read('agent_tasks', []);
            foreach ($t as &$x) { if (($x['id'] ?? '') === $hit['ref']) { $x['status'] = 'offen'; $x['done_ts'] = 0; $ok = true; } }
            unset($x);
            if ($ok) oh_write('agent_tasks', $t);
            break;
        case 'freigabe': // Entscheidung zurück auf offen
            $ok = (bool)oh_freigabe_update($hit['ref'], ['status' => 'offen']);
            break;
        case 'website_reco': // Website-Vorschlag zurück auf offen
            $reco = oh_read('website_reco', []);
            foreach ($reco as &$r) { if (($r['id'] ?? '') === $hit['ref']) { $r['status'] = 'offen'; $ok = true; } }
            unset($r);
            if ($ok) oh_write('website_reco', $reco);
            break;
        case 'website_text': // echte Website-Text-Änderung aus Backup wiederherstellen
            $info  = is_array($hit['neu'] ?? null) ? $hit['neu'] : [];
            $bak   = preg_replace('/[^a-z0-9_.\-]/i', '', $info['backup'] ?? '');
            $datei = preg_replace('/[^a-z0-9_.\-]/i', '', $info['datei'] ?? 'index.php') ?: 'index.php';
            $bakPath = dirname(__DIR__) . '/' . $bak;
            if ($bak && is_file($bakPath)) {
                $content = @file_get_contents($bakPath);
                $ok = ($content !== false && @file_put_contents(dirname(__DIR__) . '/' . $datei, $content) !== false);
                if (!$ok) $err = 'Wiederherstellen aus Backup fehlgeschlagen.';
                // Pending-Eintrag auf rueckgaengig setzen
                if ($ok && ($hit['ref'] ?? '') !== '') {
                    $pend = function_exists('oh_website_pending') ? oh_website_pending() : [];
                    foreach ($pend as &$pp) { if (($pp['id'] ?? '') === $hit['ref']) $pp['status'] = 'rueckgaengig'; }
                    unset($pp);
                    oh_write('website_pending', $pend);
                }
            } else { $err = 'Backup-Datei fehlt – manuelle Wiederherstellung nötig.'; }
            break;
        case 'ads_reco': // dokumentierte Ads-Empfehlung zurück auf offen
            $reco = oh_read('ads_reco', []);
            foreach ($reco as &$r) { if (($r['id'] ?? '') === $hit['ref']) { $r['status'] = 'offen'; $ok = true; } }
            unset($r);
            if ($ok) oh_write('ads_reco', $reco);
            break;
        case 'ads_negativ': // negatives Keyword WIRKLICH wieder aus Google Ads entfernen
            $res = is_array($hit['neu']) ? ($hit['neu']['resources'] ?? []) : [];
            if ($res) {
                $ops = [];
                foreach ($res as $rn) { if (is_string($rn) && $rn !== '') $ops[] = ['remove' => $rn]; }
                $e = null;
                $ok = $ops && oh_ads_mutate('campaignCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $e) !== null;
                if (!$ok) $err = 'Google Ads: ' . ($e ?: 'Entfernen fehlgeschlagen.');
            } else { $err = 'Keine Ads-Referenz gespeichert – bitte manuell im Konto entfernen.'; }
            // zugehörige Empfehlung wieder öffnen
            if ($ok && $hit['ref'] !== '') {
                $reco = oh_read('ads_reco', []);
                foreach ($reco as &$r) { if (($r['id'] ?? '') === $hit['ref']) $r['status'] = 'offen'; }
                unset($r);
                oh_write('ads_reco', $reco);
            }
            break;
        case 'ads_keyword': // eingebuchtes Keyword wieder aus Google Ads entfernen
            $res = is_array($hit['neu']) ? ($hit['neu']['resources'] ?? []) : [];
            if ($res) {
                $ops = [];
                foreach ($res as $rn) { if (is_string($rn) && $rn !== '') $ops[] = ['remove' => $rn]; }
                $e = null;
                $ok = $ops && oh_ads_mutate('adGroupCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $e) !== null;
                if (!$ok) $err = 'Google Ads: ' . ($e ?: 'Entfernen fehlgeschlagen.');
            } else { $err = 'Keine Ads-Referenz gespeichert.'; }
            if ($ok && $hit['ref'] !== '') {
                $reco = oh_read('ads_reco', []);
                foreach ($reco as &$r) { if (($r['id'] ?? '') === $hit['ref']) $r['status'] = 'offen'; }
                unset($r);
                oh_write('ads_reco', $reco);
            }
            break;
        case 'ads_budget': // Tagesbudget auf den alten Wert zurücksetzen
            $resN = is_array($hit['neu']) ? ($hit['neu']['resource'] ?? '') : '';
            $altE = is_array($hit['neu']) ? (float)($hit['neu']['alt_euro'] ?? 0) : 0;
            if ($resN !== '' && $altE >= 1) {
                $e = null;
                $ops = [['update' => ['resourceName' => $resN, 'amountMicros' => (int)round($altE * 1e6)], 'updateMask' => 'amount_micros']];
                $ok = oh_ads_mutate('campaignBudgets:mutate', ['operations' => $ops, 'partialFailure' => true], $e) !== null;
                if (!$ok) $err = 'Google Ads: ' . ($e ?: 'Budget-Rücksetzung fehlgeschlagen.');
            } else { $err = 'Alter Budget-Wert nicht gespeichert.'; }
            break;
        default:
            $err = 'Dieser Typ ist nicht rückholbar.';
    }
    if ($ok) {
        $c[$idx]['status'] = 'rueckgaengig';
        $c[$idx]['undo_ts'] = time();
        oh_write('changes', $c);
        if (function_exists('oh_log_activity')) oh_log_activity('chef', 'Rückgängig gemacht: ' . ($hit['titel'] ?? $id));
    }
    return $ok;
}

/* ==========================================================================
 * NACHRICHTEN-TRIAGE (Baustein B) – Kaan stuft eingehende E-Mails & WhatsApp
 * ein: wichtig => Freigabe mit Antwortvorschlag; unwichtig/Standard => als
 * Auto-Antwort vorbereitet. Schreibt in die Freigabe-Warteschlange.
 * Läuft im Cron (stündlich) und per Button. KI-Aufruf nötig.
 * ======================================================================== */
function oh_msg_triage(?string &$err = null): array {
    $em = oh_read('emails', []); $emails = $em['list'] ?? [];
    $wa = function_exists('oh_wa_open') ? oh_wa_open() : [];

    // Kandidaten mit stabilem Schlüssel (für Dedupe) sammeln
    $cands = [];
    foreach ($emails as $m) {
        $key = md5('email|' . ($m['from'] ?? '') . '|' . ($m['subject'] ?? '') . '|' . date('Ymd', $m['ts'] ?? time()));
        $cands[] = ['kanal' => 'email', 'key' => $key, 'from' => $m['from'] ?? '', 'email' => $m['from_email'] ?? '', 'text' => $m['subject'] ?? '', 'ts' => $m['ts'] ?? time()];
    }
    foreach ($wa as $w) {
        $key = md5('whatsapp|' . ($w['from'] ?? '') . '|' . mb_substr($w['text'] ?? '', 0, 40) . '|' . date('Ymd', $w['ts'] ?? time()));
        $cands[] = ['kanal' => 'whatsapp', 'key' => $key, 'from' => ($w['name'] ?? ($w['from'] ?? '')), 'text' => $w['text'] ?? '', 'ts' => $w['ts'] ?? time()];
    }

    // Bereits verarbeitete (Dedupe gegen bestehende Freigaben jeglichen Status)
    $seen = [];
    foreach (oh_read('freigaben', []) as $fx) { if (!empty($fx['dedup'])) $seen[$fx['dedup']] = true; }
    $todo = array_values(array_filter($cands, function($c) use ($seen){ return !isset($seen[$c['key']]); }));
    if (!$todo) return ['neu' => 0, 'gesamt' => count($cands)];
    $todo = array_slice($todo, 0, 12); // KI-Last begrenzen

    $liste = '';
    foreach ($todo as $i => $c) {
        $liste .= ($i + 1) . ". [" . $c['kanal'] . "] von: " . preg_replace('/\s+/', ' ', mb_substr($c['from'], 0, 60))
                . " | Inhalt: " . preg_replace('/\s+/', ' ', mb_substr($c['text'], 0, 220)) . "\n";
    }

    $system = "Du bist Kaan, der Kommunikations-Manager von OH Haustechnik (Elektriker Nürnberg). Ziel der Firma: in 5 Monaten Richtung 1.000.000 € Umsatz. "
        . "Stufe jede eingehende Nachricht ein. WICHTIG = der Chef muss selbst entscheiden/antworten (echte Kundenanfrage, Auftrag, Beschwerde, Geld/Rechnung, Termin auf Baustelle, Risiko, alles Umsatzrelevante). "
        . "UNWICHTIG = Standard, kann automatisch beantwortet werden (Eingangsbestätigung, einfache Rückfrage, Terminbestätigung). "
        . "SPAM = unerwünschte Werbung, Newsletter, Phishing, Massen-Mails: kategorie 'Spam', wichtig=false, antwort LEER lassen (Spam wird nie beantwortet). "
        . "Automaten-Absender (noreply@, notifications@, Instagram/Google/Facebook-Benachrichtigungen) sind IMMER 'Spam' oder 'Info' mit leerer antwort – dort kommt nie eine Antwort an. "
        . "Gib für JEDE Nachricht: kategorie (genau eines: 'Antwort notwendig','Freigabe erforderlich','Umsatzrelevant','Risiko','Spam','Info'), "
        . "prio ('rot'=sofort,'gelb'=heute,'gruen'=kann warten), wichtig (true/false), titel (kurz, max 7 Worte), warum (1 kurzer Satz), "
        . "antwort (höflicher, fertiger Antwortvorschlag als OH Haustechnik, Du-Form zum Kunden 'Sie', 3-6 Sätze, mit Gruß 'Viele Grüße, OH Haustechnik'). "
        . "Antworte AUSSCHLIESSLICH mit JSON-Array, ein Objekt je Nachricht in Reihenfolge:\n"
        . "<triage>[{\"nr\":1,\"kategorie\":\"...\",\"prio\":\"gelb\",\"wichtig\":true,\"titel\":\"...\",\"warum\":\"...\",\"antwort\":\"...\"}]</triage>";

    $resp = oh_ki($system, "Eingegangene Nachrichten:\n" . $liste, 2200);
    if (!$resp) { $err = 'KI nicht verfügbar (Schlüssel/Guthaben prüfen).'; return ['neu' => 0, 'fehler' => $err]; }
    $json = $resp;
    if (preg_match('/<triage>([\s\S]*?)<\/triage>/', $resp, $mm)) $json = $mm[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '['); $rb = strrpos($json, ']');
    if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
    $rows = json_decode(trim($json), true);
    if (!is_array($rows)) { $err = 'KI-Antwort unlesbar.'; return ['neu' => 0, 'fehler' => $err]; }

    $neu = 0;
    foreach ($rows as $r) {
        $nr = (int)($r['nr'] ?? 0);
        $c = $todo[$nr - 1] ?? null;
        if (!$c) continue;
        $wichtig = !empty($r['wichtig']);
        $istSpam = (($r['kategorie'] ?? '') === 'Spam');
        $kat = $wichtig ? ($r['kategorie'] ?? 'Antwort notwendig') : ($istSpam ? 'Spam erkannt' : 'Auto-Antwort vorbereitet');
        $id = oh_freigabe_add([
            'dedup'     => $c['key'],
            'agent'     => 'kaan',
            'kanal'     => $c['kanal'],
            'kategorie' => $kat,
            'prio'      => in_array($r['prio'] ?? '', ['rot','gelb','gruen']) ? $r['prio'] : ($wichtig ? 'gelb' : 'gruen'),
            'titel'     => $r['titel'] ?? (mb_substr($c['text'], 0, 50)),
            'warum'     => $r['warum'] ?? '',
            'typ'       => 'antwort',
            'vorschlag' => $r['antwort'] ?? '',
            'auto'      => !$wichtig,
            'from'      => $c['from'],
            'to'        => $c['email'] ?? '',
        ]);
        if ($id) $neu++;

        // AUTOPILOT KAAN: harmlose Standard-Antworten wirklich selbst senden
        // (nie bei wichtig/Spam, nie leer, nie an noreply-Adressen, max. 10/Tag, abschaltbar)
        if ($id && !$wichtig && !$istSpam && trim($r['antwort'] ?? '') !== ''
            && filter_var($c['email'] ?? '', FILTER_VALIDATE_EMAIL)
            && !(function_exists('oh_ist_noreply') && oh_ist_noreply($c['email']))
            && (oh_config()['autopilot_kaan'] ?? 'an') === 'an') {
            if (oh_autopilot_limit('kaan_auto', 10)) {
                $res = oh_send_mail($c['email'], 'Ihre Nachricht an OH Haustechnik', $r['antwort']);
                if (!empty($res['ok'])) {
                    oh_freigabe_update($id, ['status' => 'auto_gesendet', 'final' => $r['antwort']]);
                    if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Autopilot: Standard-Antwort gesendet an ' . ($c['from'] ?: $c['email']) . ' (' . $kat . ')');
                }
            }
        }
    }
    if ($neu && function_exists('oh_log_activity')) {
        oh_log_activity('kaan', "Posteingang geprüft: $neu neue Nachricht(en) eingestuft und in die Freigaben gelegt.");
    }
    return ['neu' => $neu, 'gesamt' => count($cands)];
}

/* ==========================================================================
 * LEXWARE OFFICE (Buchhaltung, für Aylin) – echte Rechnungen, offene Posten
 * und Umsatz über die Public API. Benötigt config: lexware_api_key
 * (Lexware Office Portal -> Erweiterungen -> Public API).
 * Ergebnis-Cache: daten/lexware.json
 * ======================================================================== */
function oh_lex_get(string $path, ?string &$err = null) {
    $key = oh_config()['lexware_api_key'] ?? '';
    if (!$key) { $err = 'Kein Lexware-API-Schlüssel hinterlegt.'; return null; }
    $ch = curl_init('https://api.lexoffice.io/v1' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Accept: application/json'],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $d = json_decode((string)$resp, true);
    if ($code !== 200) {
        $err = is_array($d) ? ($d['message'] ?? ('HTTP ' . $code)) : ('HTTP ' . $code);
        if ($code === 401) $err = 'Lexware-Schlüssel ungültig (401).';
        return null;
    }
    return $d;
}

/** POST an die Lexware Public API (z.B. Rechnung anlegen). */
function oh_lex_post(string $path, array $payload, ?string &$err = null) {
    $key = oh_config()['lexware_api_key'] ?? '';
    if (!$key) { $err = 'Kein Lexware-API-Schlüssel hinterlegt.'; return null; }
    $ch = curl_init('https://api.lexoffice.io/v1' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Accept: application/json', 'Content-Type: application/json'],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $d = json_decode((string)$resp, true);
    if ($code < 200 || $code >= 300) {
        $err = is_array($d) ? ($d['message'] ?? ('HTTP ' . $code)) : ('HTTP ' . $code);
        return null;
    }
    return $d;
}

/** Erstellt in Lexware einen Rechnungs-ENTWURF (finalize=false) aus einem Lead/Angebot.
 *  Kleinunternehmer (§19 UStG, vatfree). Entwurf = du prüfst/finalisierst in Lexware. */
function oh_lex_create_invoice(array $lead, ?string &$err = null): ?array {
    $betrag = (float)($lead['angebot_betrag'] ?? ($lead['rechnung_betrag'] ?? 0));
    if ($betrag <= 0) { $err = 'Kein Betrag für die Rechnung hinterlegt.'; return null; }
    $name = trim((string)($lead['name'] ?? '')) ?: 'Kunde';
    $leistung = trim((string)($lead['kategorie'] ?? 'Elektroarbeiten')) ?: 'Elektroarbeiten';
    $payload = [
        'voucherDate' => date('c'),
        'address' => ['name' => $name],
        'lineItems' => [[
            'type' => 'custom', 'name' => $leistung, 'quantity' => 1, 'unitName' => 'Pauschal',
            'unitPrice' => ['currency' => 'EUR', 'netAmount' => round($betrag, 2), 'taxRatePercentage' => 0],
        ]],
        'totalPrice' => ['currency' => 'EUR'],
        'taxConditions' => ['taxType' => 'vatfree'],
        'shippingConditions' => ['shippingType' => 'none'],
        'title' => 'Rechnung',
        'introduction' => 'Vielen Dank für Ihren Auftrag.',
        'remark' => 'Gemäß § 19 UStG wird keine Umsatzsteuer berechnet (Kleinunternehmer).',
    ];
    $d = oh_lex_post('/invoices?finalize=false', $payload, $err);
    if ($d === null) return null;
    return ['id' => $d['id'] ?? '', 'resourceUri' => $d['resourceUri'] ?? ''];
}

/** Gleicht Rechnungen mit Lexware ab und speichert eine Zusammenfassung. */
function oh_lex_refresh(?string &$err = null): ?array {
    // Offene Rechnungen (alle)
    $open = oh_lex_get('/voucherlist?voucherType=invoice&voucherStatus=open&size=250&page=0&sort=voucherDate,DESC', $err);
    if ($open === null) return null;
    // Bezahlte Rechnungen des laufenden Jahres (= echter Umsatz)
    $e2 = null;
    $paid = oh_lex_get('/voucherlist?voucherType=invoice&voucherStatus=paid&voucherDateFrom=' . date('Y') . '-01-01&size=250&page=0', $e2);

    $now = time();
    $cntOpen = 0; $sumOpen = 0.0; $cntOver = 0; $sumOver = 0.0; $listOver = [];
    foreach (($open['content'] ?? []) as $v) {
        $amt = (float)($v['openAmount'] ?? $v['totalAmount'] ?? 0);
        $cntOpen++; $sumOpen += $amt;
        $due = !empty($v['dueDate']) ? strtotime(substr($v['dueDate'], 0, 10)) : 0;
        if ($due && $due < $now) {
            $cntOver++; $sumOver += $amt;
            if (count($listOver) < 6) {
                $listOver[] = ['nr' => $v['voucherNumber'] ?? '', 'kunde' => $v['contactName'] ?? '',
                               'betrag' => round($amt, 2), 'faellig' => substr($v['dueDate'], 0, 10),
                               'kontakt_id' => $v['contactId'] ?? ''];
            }
        }
    }
    $cntPaid = 0; $sumPaid = 0.0;
    foreach (($paid['content'] ?? []) as $v) { $cntPaid++; $sumPaid += (float)($v['totalAmount'] ?? 0); }

    $data = [
        'ok' => true, 'ts' => time(),
        'offen_anzahl' => $cntOpen, 'offen_summe' => round($sumOpen, 2),
        'ueberfaellig_anzahl' => $cntOver, 'ueberfaellig_summe' => round($sumOver, 2), 'ueberfaellig' => $listOver,
        'bezahlt_jahr_anzahl' => $cntPaid, 'bezahlt_jahr_summe' => round($sumPaid, 2),
    ];
    oh_write('lexware', $data);
    if (function_exists('oh_log_activity')) {
        oh_log_activity('aylin', "Lexware abgeglichen: {$cntOpen} offene Rechnung(en) ({$data['offen_summe']}€), davon {$cntOver} überfällig. Umsatz " . date('Y') . " (bezahlt): {$data['bezahlt_jahr_summe']}€");
    }
    if (function_exists('oh_agent_mem_add')) {
        oh_agent_mem_add('aylin', "Lexware-Stand: {$cntOpen} offene Rechnungen ({$data['offen_summe']}€), {$cntOver} überfällig ({$data['ueberfaellig_summe']}€), Jahres-Umsatz bezahlt {$data['bezahlt_jahr_summe']}€.", 'fund');
    }
    return $data;
}

/** E-Mail-Adresse eines Lexware-Kontakts holen (für Zahlungserinnerungen). */
function oh_lex_contact_email(string $contactId, ?string &$err = null): string {
    if ($contactId === '') return '';
    $d = oh_lex_get('/contacts/' . $contactId, $err);
    if (!is_array($d)) return '';
    foreach (['business', 'office', 'private', 'other'] as $k) {
        $v = $d['emailAddresses'][$k][0] ?? '';
        if ($v && filter_var($v, FILTER_VALIDATE_EMAIL)) return $v;
    }
    return '';
}

/* ==========================================================================
 * AUTOPILOT AYLIN – freundliche Zahlungserinnerung bei überfälligen
 * Rechnungen (ab 3 Tage drüber, max. 1x/Woche je Rechnung, max. 5/Tag,
 * abschaltbar). Echte Mahnungen bleiben Chefsache (Freigabe).
 * ======================================================================== */
function oh_aylin_erinnerungen(?string &$err = null): int {
    if ((oh_config()['autopilot_aylin'] ?? 'an') !== 'an') return 0;
    $lex = oh_read('lexware', []);
    $list = $lex['ueberfaellig'] ?? [];
    if (!$list) return 0;
    $mahn = oh_read('mahnungen', []);
    $gesendet = 0;
    foreach ($list as $u) {
        $nr = $u['nr'] ?? '';
        if ($nr === '') continue;
        $due = !empty($u['faellig']) ? strtotime($u['faellig'] . ' 00:00:00') : 0;
        if (!$due || (time() - $due) < 3 * 86400) continue;                       // erst ab 3 Tagen überfällig
        if (!empty($mahn[$nr]) && (time() - $mahn[$nr]) < 7 * 86400) continue;    // max. 1x pro Woche je Rechnung
        $email = oh_lex_contact_email($u['kontakt_id'] ?? '');
        if ($email === '') continue;
        if (!oh_autopilot_limit('aylin_erinnerung', 5)) break;
        $betrag = number_format((float)($u['betrag'] ?? 0), 2, ',', '.');
        $body = "Guten Tag" . (!empty($u['kunde']) ? ' ' . $u['kunde'] : '') . ",\n\n"
              . "sicher ist es nur untergegangen: Unsere Rechnung {$nr} über {$betrag} € war am {$u['faellig']} fällig.\n"
              . "Wir freuen uns über einen Ausgleich in den nächsten Tagen. Falls die Zahlung bereits unterwegs ist, betrachten Sie diese Nachricht bitte als gegenstandslos.\n\n"
              . "Bei Fragen sind wir gerne für Sie da.\n\nViele Grüße\nOH Haustechnik";
        $res = oh_send_mail($email, "Freundliche Zahlungserinnerung – Rechnung {$nr}", $body);
        if (!empty($res['ok'])) {
            $mahn[$nr] = time(); $gesendet++;
            if (function_exists('oh_log_activity')) oh_log_activity('aylin', "Autopilot: Zahlungserinnerung Rechnung {$nr} ({$betrag}€) an " . ($u['kunde'] ?: $email) . " gesendet.");
            if (function_exists('oh_agent_mem_add')) oh_agent_mem_add('aylin', "Zahlungserinnerung Rechnung {$nr} ({$betrag}€) an " . ($u['kunde'] ?: $email) . " gesendet.", 'fund');
        }
    }
    if ($gesendet) oh_write('mahnungen', $mahn);
    return $gesendet;
}

/* ==========================================================================
 * AUTOPILOT DILARA – hält die Marktanalyse automatisch frisch (1x/Tag) und
 * führt SICHERE rote Optimierungen (Geld-Verbrenner als negative Keywords)
 * direkt im Ads-Konto aus (max. 3/Tag, abschaltbar). Budget/Gebote bleiben
 * Chefsache (Freigabe).
 * ======================================================================== */
function oh_dilara_auto_optimieren(?string &$err = null): array {
    $cfg = oh_config();
    $out = ['analyse' => false, 'ausgefuehrt' => 0];
    if (empty($cfg['ads_refresh_token'])) return $out;
    $last = function_exists('oh_ads_last_analysis') ? oh_ads_last_analysis() : 0;
    if ($last && (time() - $last) < 24 * 3600) return $out;   // 1x täglich reicht
    $reco = oh_ads_recommendations($err);
    if ($reco === null) return $out;
    $out['analyse'] = true;
    if (($cfg['autopilot_dilara'] ?? 'an') !== 'an') return $out;

    $alle = oh_read('ads_reco', []);
    $changed = false;
    foreach ($alle as &$r) {
        if (($r['status'] ?? '') !== 'offen') continue;
        if (($r['typ'] ?? '') !== 'negativ_keyword') continue;
        if (($r['dringlichkeit'] ?? '') !== 'rot') continue;
        $wert = trim($r['wert'] ?? '');
        if ($wert === '' || $out['ausgefuehrt'] >= 3) continue;
        if (!oh_autopilot_limit('dilara_negkw', 3)) break;
        $e = null; $resources = null;
        if (oh_ads_add_negative_keyword($wert, $e, $resources)) {
            $r['status'] = 'uebernommen'; $changed = true; $out['ausgefuehrt']++;
            oh_ads_log_change(['titel' => $r['titel'] ?? '', 'was' => $r['was'] ?? '', 'typ' => 'negativ_keyword', 'wert' => $wert, 'ausgefuehrt' => true]);
            if (function_exists('oh_log_activity')) oh_log_activity('dilara', "Autopilot: Geld-Verbrenner \"{$wert}\" automatisch in Google Ads ausgeschlossen.");
            if (function_exists('oh_change_log')) {
                oh_change_log('ads_negativ', "Autopilot: Ausschluss-Wort \"{$wert}\" in Google Ads", 'nicht ausgeschlossen', ['wert' => $wert, 'resources' => $resources ?: []], $r['id'] ?? '');
            }
        }
    }
    unset($r);
    if ($changed) oh_write('ads_reco', $alle);
    return $out;
}

/* ==========================================================================
 * DILARA: LIVE-MARKTANALYSE (bei jedem Cron) – echte aktuelle Daten aus dem
 * Google-Ads-Konto: Klickpreise je Suchbegriff/Region (z.B. "Elektro Fürth"),
 * Marktanteil vs. Konkurrenz und wonach gerade wirklich gesucht wird.
 * Ergebnis: daten/markt_live.json (fließt in Dilaras Kontext & Empfehlungen).
 * ======================================================================== */
function oh_dilara_markt_live(?string &$err = null): ?array {
    $e1 = $e2 = $e3 = null;
    $markt = oh_ads_market($e1);        // Marktanteil + Verlust (Budget/Rang) vs. Konkurrenz
    $terms = oh_ads_search_terms($e2);  // wonach Leute JETZT wirklich suchen
    $kws   = oh_ads_keywords($e3);      // Keyword-Leistung
    if ($markt === null && $terms === null && $kws === null) { $err = $e1 ?: ($e2 ?: ($e3 ?: 'Ads-Zugang prüfen')); return null; }

    // Echte Klickpreise je Keyword (Standort-/Branchenlogik: z.B. "elektriker fürth" = X € pro Klick)
    $cpc = [];
    foreach (($kws ?: []) as $k) {
        if (($k['klicks'] ?? 0) > 0) {
            $cpc[] = ['keyword' => $k['keyword'], 'cpc' => round($k['kosten'] / max(1, $k['klicks']), 2),
                      'klicks' => $k['klicks'], 'conv' => $k['conv']];
        }
    }
    usort($cpc, function($a, $b){ return $b['cpc'] <=> $a['cpc']; });

    $data = [
        'ts' => time(),
        'markt' => $markt ?: [],
        'cpc' => array_slice($cpc, 0, 15),
        'suchbegriffe' => array_slice($terms ?: [], 0, 12),
    ];
    oh_write('markt_live', $data);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Live-Marktcheck: Klickpreise, Suchbegriffe & Konkurrenz-Anteile frisch aus Google geholt.');
    if (function_exists('oh_agent_mem_add') && $cpc) {
        $top = $cpc[0];
        oh_agent_mem_add('dilara', "Live-Markt: teuerster Klick \"{$top['keyword']}\" = {$top['cpc']}€. " . count($cpc) . " Keywords mit echten Klickpreisen erfasst.", 'fund');
    }
    return $data;
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
          . "FROM campaign WHERE campaign.status = 'ENABLED' AND segments.date DURING LAST_7_DAYS "
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
          . "metrics.conversions FROM search_term_view WHERE campaign.status = 'ENABLED' AND segments.date DURING LAST_30_DAYS "
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

    // Wochenziel mitgeben, damit jede Empfehlung auf die Lücke einzahlt
    if (function_exists('oh_ziel_text')) $ctx .= "\n" . oh_ziel_text();

    $system = "Du bist der digitale Geschäftsführer von OH Haustechnik (Elektriker Nürnberg, Kleinunternehmer). "
        . "ZIEL: möglichst viele HOCHWERTIGE Anfragen für Altbausanierung, Wohnungsmodernisierung, komplette Elektro-Sanierung (3-4 Zimmer), Zähleranlagen, Unterverteilungen und Smart-Home. "
        . "Qualität vor Menge. Werbekosten senken, profitable Aufträge gewinnen.\n"
        . "Analysiere die Google-Ads-Daten wie ein cleverer Profi und finde die wichtigsten Optimierungen. "
        . "Sprich EINFACH, wie ein guter Mitarbeiter zum Chef – KEINE Fachbegriffe, kurze Sätze, immer mit 'grosser Adnan' anreden.\n"
        . "Gib AUSSCHLIESSLICH einen JSON-Block in genau diesem Format zurück (3-6 Empfehlungen, wichtigste zuerst), nichts davor/danach:\n"
        . "<reco>[{\"titel\":\"Chef, ...\",\"was\":\"<was genau ändern>\",\"warum\":\"<warum, einfach>\",\"anfragen\":\"<z.B. 2-4 pro Woche>\",\"wahrscheinlichkeit\":\"<hoch|mittel|niedrig>\",\"dringlichkeit\":\"<rot|gelb|gruen>\",\"typ\":\"<negativ_keyword|keyword|budget|gebot|standort|zeit|anzeige|info>\",\"wert\":\"<z.B. das auszuschließende Suchwort oder das neue Keyword>\",\"schritte\":\"<1-2 ganz einfache Schritte zum Umsetzen>\"}]</reco>\n"
        . "Konzentriere Dich auf: Geld-verbrennende Suchbegriffe als negative Keywords ausschließen (z.B. 'job','gehalt','kostenlos','ausbildung','selber'). SCHLIESSE NIEMALS Ortsnamen, Staedte oder Gemeinden als negative Keywords aus (z.B. nicht 'fürth', 'erlangen', 'schwabach') – OH arbeitet in Nuernberg, Fuerth UND der ganzen Umgebung inkl. kleinerer Orte. Die geografische Ausrichtung der Kampagne regelt bereits, WO die Anzeige erscheint; wer 'elektriker [ort]' im Gebiet sucht, ist ein ECHTER Kunde. Nutze Ortsbezug stattdessen POSITIV (gute Orts-Keywords pushen). starke Sanierungs-Keywords pushen, Budget auf das lenken was Anfragen bringt. "
        . "WICHTIG zu 'typ': Bevorzuge 'negativ_keyword' (wert = das auszuschließende Wort) und 'keyword' (wert = das neue Suchwort) – diese führt das System auf Klick SOFORT selbst aus. Bei 'budget' MUSS wert eine reine Zahl in €/Tag sein (z.B. \"35\"). "
        . "Nutze die MARKT-Daten: Wenn er viele Suchen wegen zu wenig Budget verliert, empfiehl Budget erhöhen (mit erwartetem Gewinn). Wenn wegen Rang/Gebot, empfiehl Gebot/Anzeige verbessern. Sag konkret, wie viel Markt-Anteil (mehr Anfragen) er dadurch gewinnt. "
        . "WICHTIG: Wiederhole KEINE bereits vorgeschlagenen/erledigten Maßnahmen. Liefere jeden Tag NEUE, frische, andere Empfehlungen aus den aktuellen Zahlen. "
        . "FOKUS GROSSAUFTRÄGE: Priorisiere Maßnahmen, die HOCHWERTIGE Sanierungs-/Großaufträge bringen (komplette Elektro-Sanierung, Altbau, Wohnungsmodernisierung 3-4 Zimmer, Zähleranlage, Unterverteilung, Smart-Home). Lieber 1 Großauftrag als 5 Kleinkram-Anfragen. Schlage gezielt starke Großauftrag-Keywords vor und lenke Budget weg von Kleinkram-Suchbegriffen hin zu denen, die zu Sanierung führen. Schätze bei jedem Vorschlag in 'anfragen' möglichst konkret, wie viele zusätzliche HOCHWERTIGE Anfragen pro Woche realistisch sind (ehrlich, nicht übertrieben). "
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
          . "FROM campaign WHERE campaign.status = 'ENABLED' AND segments.date DURING LAST_30_DAYS";
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
function oh_ads_add_negative_keyword(string $text, ?string &$err = null, ?array &$resources = null): bool {
    $cfg = oh_config();
    // SCHUTZ: niemals Ortsnamen aus dem Servicegebiet ausschliessen (Geo-Ausrichtung regelt das Gebiet).
    $__orte = $cfg['service_orte'] ?? 'nuernberg,nürnberg,fuerth,fürth,erlangen,schwabach,stein,zirndorf,oberasbach,cadolzburg,wendelstein,feucht,schwaig,heroldsberg,lauf,rosstal,roßtal,ammerndorf,langenzenn,veitsbronn,eckental,herzogenaurach,altdorf,burgthann,roethenbach,röthenbach,rednitzhembach,buechenbach,büchenbach,neunkirchen,hersbruck,allersberg';
    $__schutz = array_filter(array_map('trim', explode(',', mb_strtolower($__orte))));
    foreach (preg_split('/\s+/', mb_strtolower(trim($text))) as $__w) {
        if ($__w !== '' && in_array($__w, $__schutz, true)) {
            $err = 'Ortsname "' . $__w . '" liegt in deinem Servicegebiet – wird NICHT ausgeschlossen.';
            return false;
        }
    }
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
    // Resource-Namen merken -> dadurch ist die Änderung später rückgängig machbar
    if ($res !== null && isset($res['results']) && is_array($res['results'])) {
        $resources = [];
        foreach ($res['results'] as $r) { if (!empty($r['resourceName'])) $resources[] = $r['resourceName']; }
    }
    return $res !== null;
}

/** Niemals an Automaten-Adressen antworten (noreply, Benachrichtigungsdienste). */
function oh_ist_noreply(string $email): bool {
    return (bool)preg_match('/^(no-?reply|donotreply|do-?not-?reply|notifications?|newsletter|mailer-daemon|bounce)[^@]*@|@(mail\.instagram\.com|facebookmail\.com|linkedin\.com|google\.com|youtube\.com|amazonses\.com)$/i', trim($email));
}

/** Fügt ein neues Keyword (PHRASE) in die aktivste Anzeigengruppe ein – rückgängig machbar. */
function oh_ads_add_keyword(string $text, ?string &$err = null, ?array &$resources = null): bool {
    $cfg = oh_config();
    $cid = preg_replace('/\D/', '', $cfg['ads_customer_id'] ?? '');
    $rows = oh_ads_search("SELECT ad_group.id, ad_group.name, metrics.impressions FROM ad_group "
        . "WHERE ad_group.status='ENABLED' AND campaign.status='ENABLED' AND campaign.advertising_channel_type='SEARCH' "
        . "AND segments.date DURING LAST_30_DAYS ORDER BY metrics.impressions DESC LIMIT 1", $err);
    if (!$rows) { if (!$err) $err = 'Keine aktive Anzeigengruppe gefunden.'; return false; }
    $agid = $rows[0]['adGroup']['id'] ?? '';
    if ($agid === '') { $err = 'Anzeigengruppe ohne ID.'; return false; }
    $ops = [['create' => [
        'adGroup' => 'customers/' . $cid . '/adGroups/' . $agid,
        'status'  => 'ENABLED',
        'keyword' => ['text' => $text, 'matchType' => 'PHRASE'],
    ]]];
    $res = oh_ads_mutate('adGroupCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $err);
    if ($res !== null && isset($res['results']) && is_array($res['results'])) {
        $resources = [];
        foreach ($res['results'] as $r) { if (!empty($r['resourceName'])) $resources[] = $r['resourceName']; }
    }
    return $res !== null;
}

/** Setzt das Tagesbudget – nur wenn GENAU EINE aktive Suchkampagne existiert (sonst manuell). */
function oh_ads_set_budget(float $euroProTag, ?string &$err = null, ?array &$undoInfo = null): bool {
    if ($euroProTag < 3 || $euroProTag > 500) { $err = 'Budget außerhalb des Sicherheitsrahmens (3–500 €/Tag).'; return false; }
    $rows = oh_ads_search("SELECT campaign.id, campaign.name, campaign.campaign_budget, campaign_budget.amount_micros "
        . "FROM campaign WHERE campaign.status='ENABLED' AND campaign.advertising_channel_type='SEARCH'", $err);
    if ($rows === null) return false;
    if (count($rows) !== 1) { $err = 'Nicht eindeutig (' . count($rows) . ' aktive Kampagnen) – bitte manuell zuordnen.'; return false; }
    $budgetRes = $rows[0]['campaign']['campaignBudget'] ?? '';
    if ($budgetRes === '') { $err = 'Budget-Referenz fehlt.'; return false; }
    $alt = round(((float)($rows[0]['campaignBudget']['amountMicros'] ?? 0)) / 1e6, 2);
    $ops = [['update' => ['resourceName' => $budgetRes, 'amountMicros' => (int)round($euroProTag * 1e6)], 'updateMask' => 'amount_micros']];
    $res = oh_ads_mutate('campaignBudgets:mutate', ['operations' => $ops, 'partialFailure' => true], $err);
    if ($res !== null) { $undoInfo = ['resource' => $budgetRes, 'alt_euro' => $alt]; return true; }
    return false;
}

/** Extrahiert das Conversion-Label (Teil nach AW-xxxx/) aus den Tag-Snippets. */
function oh_ads_extract_label(array $ca): string {
    foreach (($ca['tagSnippets'] ?? []) as $ts) {
        $snippet = ($ts['eventSnippet'] ?? '') . ' ' . ($ts['globalSiteTag'] ?? '');
        if (preg_match('#AW-\d+/([\w-]+)#', $snippet, $m)) return $m[1];
    }
    return '';
}

/**
 * Richtet die Lead-Conversion „OH Website Lead" ein (oder findet eine vorhandene),
 * holt das Conversion-Label und speichert es in der Konfiguration.
 * Danach feuert die Website automatisch echte Ads-Conversions mit Euro-Wert.
 * Greift nur auf bewussten Knopfdruck ins Live-Konto. Idempotent (legt nichts doppelt an).
 */
function oh_ads_setup_lead_conversion(?string &$err = null): array {
    $name = 'OH Website Lead';
    // 1) Vorhandene Conversion-Aktion suchen (kein Duplikat anlegen)
    $rows = oh_ads_search(
        "SELECT conversion_action.resource_name, conversion_action.name, conversion_action.status, "
      . "conversion_action.tag_snippets FROM conversion_action "
      . "WHERE conversion_action.name = '" . str_replace("'", '', $name) . "'",
        $err
    );
    if ($rows === null) return ['ok' => false, 'err' => $err];

    $resource = ''; $label = '';
    if (!empty($rows)) {
        $ca = $rows[0]['conversionAction'] ?? [];
        $resource = $ca['resourceName'] ?? '';
        $label = oh_ads_extract_label($ca);
        $created = false;
    } else {
        // 2) Neu anlegen
        $ops = [['create' => [
            'name'         => $name,
            'type'         => 'WEBPAGE',
            'category'     => 'SUBMIT_LEAD_FORM',
            'status'       => 'ENABLED',
            'primaryForGoal' => true,
            'countingType' => 'ONE_PER_CLICK',
            'valueSettings' => ['defaultValue' => 1000, 'alwaysUseDefaultValue' => false],
        ]]];
        $res = oh_ads_mutate('conversionActions:mutate', ['operations' => $ops, 'partialFailure' => false], $err);
        if ($res === null) return ['ok' => false, 'err' => $err];
        $resource = $res['results'][0]['resourceName'] ?? '';
        $created = true;
    }

    // 3) Label nachladen, falls noch nicht vorhanden
    if ($resource !== '' && $label === '') {
        $rows2 = oh_ads_search(
            "SELECT conversion_action.resource_name, conversion_action.tag_snippets "
          . "FROM conversion_action WHERE conversion_action.resource_name = '" . str_replace("'", '', $resource) . "'",
            $err
        );
        if (!empty($rows2)) $label = oh_ads_extract_label($rows2[0]['conversionAction'] ?? []);
    }

    // 4) Label speichern -> fließt automatisch live auf alle Seiten
    if ($label !== '') {
        oh_config_set(['ads_conversion_label' => $label]);
        if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Ads Lead-Conversion eingerichtet (Label gespeichert, Wert-Tracking aktiv).');
    }

    return [
        'ok'      => true,
        'created' => $created,
        'label'   => $label,
        'hinweis' => $label !== ''
            ? 'Lead-Conversion ist aktiv – die Website sendet ab jetzt echte Conversions mit Euro-Wert.'
            : 'Conversion-Aktion ist angelegt, aber das Label konnte nicht automatisch ausgelesen werden. Bitte Label aus Google Ads (Tag einrichten) ins Feld eintragen.',
    ];
}

/** Entfernt bestimmte negative Keywords (Text-Match) aus allen Such-Kampagnen. Rückgängig per UI. */
function oh_ads_remove_negative_keywords(array $woerter, ?string &$err = null, ?array &$entfernt = null): bool {
    $entfernt = [];
    $ziel = array_map('oh_ads_kw_norm', $woerter);
    $rows = oh_ads_search(
        "SELECT campaign_criterion.resource_name, campaign_criterion.keyword.text "
      . "FROM campaign_criterion WHERE campaign_criterion.negative = TRUE AND campaign_criterion.type = 'KEYWORD'",
        $err);
    if ($rows === null) return false;
    $ops = [];
    foreach ($rows as $r) {
        $cc  = $r['campaignCriterion'] ?? [];
        $txt = oh_ads_kw_norm($cc['keyword']['text'] ?? '');
        $rn  = $cc['resourceName'] ?? '';
        if ($txt !== '' && $rn !== '' && in_array($txt, $ziel, true)) {
            $ops[] = ['remove' => $rn];
            $entfernt[] = $txt;
        }
    }
    if (!$ops) return true;
    $res = oh_ads_mutate('campaignCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $err);
    return $res !== null;
}

/**
 * Ein-Klick-Optimierung auf Sanierungs-Fokus:
 *  1) Entfernt schädliche Negative, die Sanierungskunden blockieren.
 *  2) Fügt hochwertige Sanierungs-Keywords (PHRASE) in die aktivste Anzeigengruppe.
 *  3) Fügt Junk-/Kleinauftrag-Negative hinzu (filtert unqualifizierte Klicks).
 * Greift nur auf bewussten Knopfdruck ins Live-Konto, alles rückgängig machbar.
 */
function oh_ads_optimize_sanierung(?string &$err = null): array {
    $cfg = oh_config();
    $cid = preg_replace('/\D/', '', $cfg['ads_customer_id'] ?? '');
    $report = ['entfernte_negative' => [], 'neue_keywords' => [], 'neue_negative' => [], 'fehler' => []];
    if ($cid === '') { $report['fehler'][] = 'Keine Ads-Kundennummer hinterlegt.'; return $report; }

    // 1) Schädliche Negative entfernen (blockieren Sanierungs-Suchen)
    $schaedlich = ['erneuern','kosten','installation','elektroinstallation','sanierung','sanieren',
                   'modernisierung','modernisieren','komplettsanierung','kernsanierung','altbau','altbausanierung'];
    $e1 = null; $rem = [];
    if (oh_ads_remove_negative_keywords($schaedlich, $e1, $rem)) $report['entfernte_negative'] = $rem;
    elseif ($e1) $report['fehler'][] = 'Negative entfernen: ' . $e1;

    // 2) Sanierungs-Keywords (PHRASE) in aktivste Anzeigengruppe (ein Mutate)
    $keywords = ['wohnung sanieren','wohnungssanierung','wohnung modernisieren','kernsanierung',
                 'elektro komplettsanierung','komplettsanierung wohnung','altbausanierung elektro',
                 'haus elektrik erneuern','elektriker sanierung'];
    $e2 = null;
    $rowsAg = oh_ads_search("SELECT ad_group.id, metrics.impressions FROM ad_group "
        . "WHERE ad_group.status='ENABLED' AND campaign.status='ENABLED' AND campaign.advertising_channel_type='SEARCH' "
        . "AND segments.date DURING LAST_30_DAYS ORDER BY metrics.impressions DESC LIMIT 1", $e2);
    $agid = $rowsAg[0]['adGroup']['id'] ?? '';
    if ($agid !== '') {
        $ops = [];
        foreach ($keywords as $kw) {
            $ops[] = ['create' => ['adGroup' => 'customers/' . $cid . '/adGroups/' . $agid,
                'status' => 'ENABLED', 'keyword' => ['text' => $kw, 'matchType' => 'PHRASE']]];
        }
        $res = oh_ads_mutate('adGroupCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $e2);
        if ($res !== null) $report['neue_keywords'] = $keywords;
        else $report['fehler'][] = 'Keywords: ' . $e2;
    } else {
        $report['fehler'][] = 'Keine aktive Anzeigengruppe für neue Keywords gefunden.';
    }

    // 3) Junk-/Kleinauftrag-Negative (ein Mutate, alle aktiven Kampagnen)
    $junk = ['kostenlos','gratis','selber machen','anleitung','job','jobs','gehalt','ausbildung','lampenwechsel','glühbirne'];
    $e3 = null;
    $camps = oh_ads_enabled_search_campaigns($e3);
    if ($camps) {
        $ops = [];
        foreach ($camps as $campId) {
            foreach ($junk as $n) {
                $ops[] = ['create' => ['campaign' => 'customers/' . $cid . '/campaigns/' . $campId,
                    'negative' => true, 'keyword' => ['text' => $n, 'matchType' => 'PHRASE']]];
            }
        }
        $res = oh_ads_mutate('campaignCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $e3);
        if ($res !== null) $report['neue_negative'] = $junk;
        else $report['fehler'][] = 'Negative: ' . $e3;
    } elseif ($e3) {
        $report['fehler'][] = 'Kampagnen: ' . $e3;
    }

    if (function_exists('oh_log_activity')) oh_log_activity('kaan', 'Ads auf Sanierungs-Fokus optimiert (Negative/Keywords angepasst).');
    return $report;
}

/**
 * Liest die echte Google-Ads-Kampagne (read-only) und gibt einen Gesundheits-Bericht:
 * Kampagne/Budget, Keyword-Landingpages, schädliche Negative, Lead-Conversion,
 * Müll-Conversions als Primär, Conversion-Label. Ändert NICHTS.
 */
function oh_ads_healthcheck(?string &$err = null): array {
    $checks = [];
    $add = function ($label, $status, $detail) use (&$checks) { $checks[] = ['label' => $label, 'status' => $status, 'detail' => $detail]; };

    // 0) Verbindung + aktive Such-Kampagnen + Budget
    $camps = oh_ads_search("SELECT campaign.name, campaign.status, campaign_budget.amount_micros "
        . "FROM campaign WHERE campaign.status='ENABLED' AND campaign.advertising_channel_type='SEARCH'", $err);
    if ($camps === null) {
        return [['label' => 'Verbindung zu Google Ads', 'status' => 'bad', 'detail' => ($err ?: 'Login fehlgeschlagen') . ' – 5 Ads-Zugangsdaten unter ⚙️ prüfen.']];
    }
    $budget = 0; foreach ($camps as $c) $budget += (float)($c['campaignBudget']['amountMicros'] ?? 0) / 1e6;
    $add('Aktive Such-Kampagnen', count($camps) > 0 ? 'ok' : 'bad',
        count($camps) . ' aktiv' . ($budget ? (' · Budget ~' . round($budget, 2) . ' €/Tag') : ' · KEIN Budget!'));

    // 1) Keywords -> eigene Landingpage?
    $kws = oh_ads_search("SELECT ad_group_criterion.keyword.text, ad_group_criterion.final_urls "
        . "FROM ad_group_criterion WHERE ad_group_criterion.type='KEYWORD' AND ad_group_criterion.status!='REMOVED' "
        . "AND campaign.advertising_channel_type='SEARCH' AND campaign.status='ENABLED'", $err);
    $kAll = 0; $kLP = 0;
    foreach (($kws ?: []) as $k) { $kAll++; $u = $k['adGroupCriterion']['finalUrls'][0] ?? '';
        if ($u && strpos($u, '.php') !== false && strpos($u, '/index.php') === false && !preg_match('#//[^/]+/?$#', $u)) $kLP++; }
    $add('Keywords mit eigener Landingpage', $kAll ? ($kLP >= max(1, $kAll * 0.5) ? 'ok' : 'warn') : 'warn',
        $kLP . ' von ' . $kAll . ' Keywords zeigen auf eine Landingpage');

    // 2) Schädliche Negative noch aktiv?
    $negs = oh_ads_search("SELECT campaign_criterion.keyword.text FROM campaign_criterion "
        . "WHERE campaign_criterion.negative=TRUE AND campaign_criterion.type='KEYWORD'", $err);
    $schaedlich = ['erneuern','kosten','installation','elektroinstallation','sanierung','sanieren','modernisierung','komplettsanierung','kernsanierung','altbau','altbausanierung'];
    $bad = []; foreach (($negs ?: []) as $n) { $t = oh_ads_kw_norm($n['campaignCriterion']['keyword']['text'] ?? ''); if (in_array($t, $schaedlich, true)) $bad[] = $t; }
    $add('Schädliche Negative (blockieren Sanierung)', $bad ? 'bad' : 'ok',
        $bad ? ('NOCH AKTIV: ' . implode(', ', $bad) . ' → „🎯 Sanierungs-Fokus optimieren" drücken') : 'keine gefunden');

    // 3) Conversion-Aktionen
    $cas = oh_ads_search("SELECT conversion_action.name, conversion_action.status, conversion_action.category, conversion_action.primary_for_goal FROM conversion_action", $err);
    $leadStatus = ''; $junkPrimary = []; $junkCats = ['PAGE_VIEW','STORE_VISIT','OUTBOUND_CLICK','STORE_SALE'];
    foreach (($cas ?: []) as $c) {
        $ca = $c['conversionAction'] ?? []; $nm = $ca['name'] ?? ''; $cat = $ca['category'] ?? ''; $prim = !empty($ca['primaryForGoal']);
        if (stripos($nm, 'OH Website Lead') !== false) $leadStatus = (($ca['status'] ?? '') === 'ENABLED') ? 'aktiv ✓' : 'angelegt (wird aktiv nach 1. echter Anfrage)';
        if ($prim && (in_array($cat, $junkCats, true) || stripos($nm, 'website visit') !== false || stripos($nm, 'engagement') !== false || stripos($nm, 'route') !== false || stripos($nm, 'wegbeschreibung') !== false))
            $junkPrimary[] = $nm;
    }
    $add('Lead-Conversion „OH Website Lead"', $leadStatus ? 'ok' : 'warn', $leadStatus ?: 'nicht gefunden → „🏆 Conversion einrichten" drücken');
    $add('Müll-Conversions als „Primär"', $junkPrimary ? 'bad' : 'ok',
        $junkPrimary ? (count($junkPrimary) . ' Stück → in Ads auf „Sekundär" stellen: ' . implode(', ', array_slice($junkPrimary, 0, 5))) : 'keine');

    // 4) Conversion-Label im Büro gespeichert?
    $cfg = oh_config();
    $add('Conversion-Label im Büro', !empty($cfg['ads_conversion_label']) ? 'ok' : 'warn',
        !empty($cfg['ads_conversion_label']) ? 'gespeichert' : 'noch leer → Label speichern (Wert-Tracking inaktiv)');

    return $checks;
}

/** Feste Zuordnung Keyword -> passende Landingpage (Message-Match für bessere Conversions). */
function oh_ads_lp_url_map(): array {
    return [
        'altbau elektrik erneuern' => 'https://oh-haustechnik.de/altbausanierung-nuernberg.php',
        'altbausanierung elektro'  => 'https://oh-haustechnik.de/altbausanierung-nuernberg.php',
        'elektriker nürnberg'      => 'https://oh-haustechnik.de/elektroinstallation-nuernberg.php',
        'sanierung nbg'            => 'https://oh-haustechnik.de/wohnung-elektro-sanieren-nuernberg.php',
    ];
}

/** Normalisiert Keyword-Text für den Abgleich (klein, getrimmt, Mehrfach-Leerzeichen weg). */
function oh_ads_kw_norm(string $t): string {
    return trim(preg_replace('/\s+/', ' ', mb_strtolower($t)));
}

/**
 * Setzt für alle aktiven Keywords die finale URL gemäß oh_ads_lp_url_map().
 * Greift NUR ins Live-Konto, wenn diese Funktion bewusst aufgerufen wird (Knopf im Büro).
 * Alte URLs werden für Undo gespeichert. Gibt einen Bericht je Keyword zurück.
 */
function oh_ads_apply_lp_urls(?string &$err = null): array {
    $map = oh_ads_lp_url_map();
    // Alle nicht entfernten Keywords aus aktiven Such-Kampagnen lesen
    $rows = oh_ads_search(
        "SELECT ad_group_criterion.resource_name, ad_group_criterion.keyword.text, "
      . "ad_group_criterion.final_urls, campaign.name "
      . "FROM ad_group_criterion "
      . "WHERE ad_group_criterion.type = 'KEYWORD' AND ad_group_criterion.status != 'REMOVED' "
      . "AND campaign.advertising_channel_type = 'SEARCH' AND campaign.status = 'ENABLED'",
        $err
    );
    if ($rows === null) return [];

    // Treffer je Ziel-Keyword sammeln
    $bericht = [];
    foreach ($map as $kw => $url) { $bericht[$kw] = ['keyword' => $kw, 'url' => $url, 'status' => 'nicht gefunden', 'anzahl' => 0, 'alt' => []]; }

    $ops = []; $undo = [];
    foreach ($rows as $r) {
        $crit = $r['adGroupCriterion'] ?? [];
        $text = oh_ads_kw_norm($crit['keyword']['text'] ?? '');
        $rn   = $crit['resourceName'] ?? '';
        if ($text === '' || $rn === '') continue;
        foreach ($map as $kw => $url) {
            if (oh_ads_kw_norm($kw) === $text) {
                $altUrls = $crit['finalUrls'] ?? [];
                if (count($altUrls) === 1 && oh_ads_kw_norm($altUrls[0]) === oh_ads_kw_norm($url)) {
                    // Bereits korrekt – nichts zu tun
                    $bericht[$kw]['status'] = 'bereits korrekt';
                    $bericht[$kw]['anzahl']++;
                    break;
                }
                $ops[]  = ['update' => ['resourceName' => $rn, 'finalUrls' => [$url]], 'updateMask' => 'final_urls'];
                $undo[] = ['resource' => $rn, 'alt' => $altUrls];
                $bericht[$kw]['status'] = 'geändert';
                $bericht[$kw]['anzahl']++;
                $bericht[$kw]['alt'] = $altUrls;
                break;
            }
        }
    }

    if ($ops) {
        $res = oh_ads_mutate('adGroupCriteria:mutate', ['operations' => $ops, 'partialFailure' => true], $err);
        if ($res === null) {
            foreach ($bericht as &$b) { if ($b['status'] === 'geändert') $b['status'] = 'fehler'; }
            unset($b);
        } else {
            // Undo-Info + Protokoll speichern -> jederzeit rückgängig machbar
            $store = oh_read('ads_url_undo', []);
            array_unshift($store, ['ts' => time(), 'changes' => $undo]);
            oh_write('ads_url_undo', array_slice($store, 0, 20));
            if (function_exists('oh_log_activity')) {
                oh_log_activity('kaan', 'Ads Final-URLs auf passende Landingpages gesetzt (' . count($ops) . ' Keyword(s)).');
            }
        }
    }
    return array_values($bericht);
}

/**
 * Setzt eine Empfehlung um. Sichere Änderungen (negative Keywords, neue
 * Keywords, eindeutiges Budget) werden direkt im Konto ausgeführt – Dein
 * Klick auf "Übernehmen" IST die Freigabe. Alles andere wird dokumentiert.
 */
function oh_ads_apply(array $reco, ?string &$err = null): array {
    $typ  = $reco['typ'] ?? '';
    $wert = trim($reco['wert'] ?? '');
    if ($typ === 'negativ_keyword' && $wert !== '') {
        $resources = null;
        if (oh_ads_add_negative_keyword($wert, $err, $resources)) {
            if (function_exists('oh_log_activity')) oh_log_activity('dilara', "Ausschluss-Wort \"{$wert}\" in Google Ads eingetragen (spart Werbegeld)");
            if (function_exists('oh_change_log')) {
                oh_change_log('ads_negativ', "Ausschluss-Wort \"{$wert}\" in Google Ads eingetragen", 'nicht ausgeschlossen', ['wert' => $wert, 'resources' => $resources ?: []], $reco['id'] ?? '');
            }
            return ['executed' => true, 'msg' => "Erledigt, Chef! \"{$wert}\" wird ab sofort ausgeschlossen – das spart Werbegeld."];
        }
        return ['executed' => false, 'msg' => 'Konnte nicht automatisch ausgeführt werden (' . $err . '). Bitte kurz manuell im Ads-Konto eintragen.'];
    }
    // NEUES KEYWORD: direkt ins Konto (aktivste Anzeigengruppe, PHRASE)
    if ($typ === 'keyword' && $wert !== '') {
        $resources = null;
        if (oh_ads_add_keyword($wert, $err, $resources)) {
            if (function_exists('oh_log_activity')) oh_log_activity('dilara', "Neues Keyword \"{$wert}\" in Google Ads eingebucht (bringt zusätzliche Anfragen)");
            if (function_exists('oh_change_log')) {
                oh_change_log('ads_keyword', "Neues Keyword \"{$wert}\" in Google Ads eingebucht", 'nicht vorhanden', ['wert' => $wert, 'resources' => $resources ?: []], $reco['id'] ?? '');
            }
            return ['executed' => true, 'msg' => "Erledigt, Chef! \"{$wert}\" ist ab sofort als Suchwort aktiv – Anfragen dazu laufen jetzt ein."];
        }
        return ['executed' => false, 'msg' => 'Konnte nicht automatisch eingebucht werden (' . $err . '). Bitte kurz manuell im Ads-Konto anlegen.'];
    }
    // BUDGET: direkt setzen, wenn eindeutig (genau 1 aktive Suchkampagne + klare Zahl)
    if ($typ === 'budget') {
        $zahl = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $wert));
        $undoInfo = null;
        if ($zahl > 0 && oh_ads_set_budget($zahl, $err, $undoInfo)) {
            if (function_exists('oh_log_activity')) oh_log_activity('dilara', "Tagesbudget in Google Ads auf {$zahl}€ gesetzt (vorher " . ($undoInfo['alt_euro'] ?? '?') . "€)");
            if (function_exists('oh_change_log')) {
                oh_change_log('ads_budget', "Tagesbudget auf {$zahl}€ gesetzt", ($undoInfo['alt_euro'] ?? 0) . '€/Tag', ['euro' => $zahl, 'resource' => $undoInfo['resource'] ?? '', 'alt_euro' => $undoInfo['alt_euro'] ?? 0], $reco['id'] ?? '');
            }
            return ['executed' => true, 'msg' => "Erledigt, Chef! Tagesbudget steht jetzt auf {$zahl}€ (vorher " . ($undoInfo['alt_euro'] ?? '?') . "€)."];
        }
        return ['executed' => false, 'msg' => 'Budget nicht automatisch gesetzt (' . ($err ?: 'kein klarer Betrag in der Empfehlung') . '). Bitte kurz manuell im Ads-Konto ändern.'];
    }
    if (function_exists('oh_change_log')) {
        oh_change_log('ads_reco', 'Ads-Empfehlung übernommen: ' . ($reco['titel'] ?? ''), 'offen', 'uebernommen', $reco['id'] ?? '');
    }
    return ['executed' => false, 'msg' => 'Notiert, Chef. Dieser Typ (Gebot/Zeit/Standort/Anzeige) braucht Deine Hand im Ads-Konto – die Schritte stehen in der Empfehlung.'];
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

    // 5b) Lexware: überfällige Rechnungen = Geld eintreiben
    $lex = oh_read('lexware', []);
    if (!empty($lex['ueberfaellig_anzahl'])) {
        $offen[] = ['bereich' => 'Rechnung', 'icon' => '💰', 'prio' => 'rot',
            'titel' => "Überfällige Rechnungen: {$lex['ueberfaellig_anzahl']} ({$lex['ueberfaellig_summe']}€)",
            'nutzen' => 'Geld eintreiben',
            'warum' => 'Diese Kunden haben die Zahlungsfrist überschritten – eine freundliche Erinnerung bringt das Geld rein.',
            'typ' => 'info', 'ref' => ''];
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
         . "- Offene Ads-Optimierungen: " . count($offenReco) . "\n"
         . "- " . oh_ziel_text() . "\n";

    $system = "Du bist Mert Aldemir, der digitale Geschäftsführer von OH Haustechnik – loyal, ehrgeizig, Du willst den Chef reich machen und treibst Dein Team dafür jeden Tag an. "
        . "DEIN EINZIGES ZIEL: das Unternehmen in 5 Monaten in Richtung 1.000.000 € Umsatz skalieren. "
        . "Hochwertige Aufträge (Altbausanierung, komplette Wohnungssanierung, Zähleranlagen, Smart-Home) bringen am meisten. "
        . "Denke wie ein knallharter, kluger Geschäftsführer. Erkenne Engpässe (zu wenig Anfragen, zu teure Werbung, Kapazität/Mitarbeiter nötig, Budget erhöhen, Prozesse). "
        . "Sprich EINFACH mit dem Chef (Du-Form), kein Fachchinesisch, motivierend aber ehrlich. Rede ihn IMMER mit 'grosser Adnan' an, nie nur 'Chef'.\n"
        . "Erstelle einen kurzen TAGESPLAN: die 3 wichtigsten Hebel HEUTE, nach Wirkung aufs Wachstum sortiert, je 1 klarer Satz mit Begründung. "
        . "Wenn ein Engpass da ist (z.B. zu wenig Anfragen, Werbung zu teuer, bald Mitarbeiter nötig), sag es deutlich. Max 9 Zeilen. Beginne mit 'Grosser Adnan,'.";

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
            $from = ''; $fromEmail = '';
            if (isset($h->from[0])) {
                $f = $h->from[0];
                $fromEmail = ($f->mailbox ?? '') . '@' . ($f->host ?? '');
                $from = ($f->personal ?? '') ?: $fromEmail;
            }
            $out[] = [
                'from'       => oh_mime_decode($from),
                'from_email' => $fromEmail,
                'subject'    => oh_mime_decode($h->subject ?? '(kein Betreff)'),
                'ts'         => isset($h->udate) ? (int)$h->udate : time(),
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

/* ==========================================================================
 * KAAN: POSTFACH-VOLLANALYSE – liest die letzten E-Mails KOMPLETT (auch
 * gelesene, inkl. Textinhalt), lässt die KI alles auswerten und füllt damit
 * Kaans Wissen & Gedächtnis. Ergebnis: daten/kaan_wissen.json
 * ======================================================================== */

/** Liest den Text-Inhalt einer Mail (Plain bevorzugt, HTML als Fallback). */
function oh_imap_body_text($mbox, int $num, int $limit = 700): string {
    $st = @imap_fetchstructure($mbox, $num);
    $raw = ''; $enc = 0;
    if ($st && !empty($st->parts)) {
        $idx = null;
        foreach ($st->parts as $k => $p) {
            if ((int)($p->type ?? -1) === 0 && strtoupper($p->subtype ?? '') === 'PLAIN') { $idx = (string)($k + 1); $enc = (int)($p->encoding ?? 0); break; }
        }
        if ($idx === null) {
            $p0 = $st->parts[0];
            if (!empty($p0->parts)) { $idx = '1.1'; $enc = (int)($p0->parts[0]->encoding ?? 0); }
            else { $idx = '1'; $enc = (int)($p0->encoding ?? 0); }
        }
        $raw = @imap_fetchbody($mbox, $num, $idx);
    } else {
        $raw = @imap_body($mbox, $num);
        $enc = (int)($st->encoding ?? 0);
    }
    if ($enc === 3) $raw = base64_decode((string)$raw) ?: '';
    elseif ($enc === 4) $raw = quoted_printable_decode((string)$raw);
    $txt = trim(preg_replace('/\s+/', ' ', strip_tags((string)$raw)));
    if ($txt !== '' && !mb_check_encoding($txt, 'UTF-8')) $txt = mb_convert_encoding($txt, 'UTF-8', 'ISO-8859-1');
    return mb_substr($txt, 0, $limit);
}

/** Holt die letzten $max Mails (gelesen + ungelesen) MIT Inhalt. */
function oh_gmail_recent(int $max = 30): array {
    $cfg = oh_config();
    if (empty($cfg['gmail_user']) || empty($cfg['gmail_pass']) || !function_exists('imap_open')) return [];
    $mbox = @imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $cfg['gmail_user'], $cfg['gmail_pass'], OP_READONLY);
    if (!$mbox) return [];
    $total = imap_num_msg($mbox);
    $out = [];
    for ($i = $total; $i > max(0, $total - $max); $i--) {
        $h = @imap_headerinfo($mbox, $i);
        if (!$h) continue;
        $from = '';
        if (isset($h->from[0])) { $f = $h->from[0]; $from = ($f->personal ?? '') ?: (($f->mailbox ?? '') . '@' . ($f->host ?? '')); }
        $out[] = [
            'from'    => oh_mime_decode($from),
            'subject' => oh_mime_decode($h->subject ?? '(kein Betreff)'),
            'ts'      => isset($h->udate) ? (int)$h->udate : time(),
            'body'    => oh_imap_body_text($mbox, $i, 650),
        ];
    }
    @imap_close($mbox);
    return $out;
}

/** Kaans Tiefen-Analyse des Postfachs (max. 30 Mails, KI-Auswertung in Blöcken). */
function oh_kaan_email_analyse(?string &$err = null): ?array {
    $mails = oh_gmail_recent(30);
    if (!$mails) { $err = 'Postfach nicht erreichbar – Gmail-Adresse/App-Passwort prüfen.'; return null; }

    $alle = []; $offenP = []; $kontakte = []; $byCat = [];
    foreach (array_chunk($mails, 10) as $batch) {
        $liste = '';
        foreach ($batch as $i => $m) {
            $liste .= ($i + 1) . ". Von: " . mb_substr($m['from'], 0, 50) . " | Betreff: " . mb_substr($m['subject'], 0, 80)
                    . " | Datum: " . date('d.m.Y', $m['ts']) . "\n   Inhalt: " . mb_substr($m['body'], 0, 400) . "\n";
        }
        $system = "Du bist Kaan, Kommunikations-Manager von OH Haustechnik (Elektriker Nürnberg, Ziel: 1 Mio € Umsatz in 5 Monaten). "
            . "Analysiere die E-Mails. Gib für JEDE Mail zurück: kategorie ('Anfrage','Auftrag','Rechnung','Lieferant','Termin','Werbung','Spam','Sonstiges'), "
            . "kunde (Name/Firma des Absenders, kurz), kern (1 kurzer Satz: worum geht es), offen (true wenn eine Reaktion von uns noch aussteht, sonst false). "
            . "SPAM-ERKENNUNG: Stufe unerwünschte Massen-Mails, Phishing, dubiose Angebote und reine Verkaufs-/Werbemails klar als 'Spam' bzw. 'Werbung' ein – bei denen ist offen IMMER false. "
            . "Antworte AUSSCHLIESSLICH mit JSON-Array in Mail-Reihenfolge:\n"
            . "<mails>[{\"nr\":1,\"kategorie\":\"...\",\"kunde\":\"...\",\"kern\":\"...\",\"offen\":false}]</mails>";
        $resp = oh_ki($system, "E-Mails:\n" . $liste, 1600);
        if (!$resp) { $err = 'KI nicht verfügbar (Guthaben/Schlüssel).'; return null; }
        $json = $resp;
        if (preg_match('/<mails>([\s\S]*?)<\/mails>/', $resp, $mm)) $json = $mm[1];
        $json = preg_replace('/```(json)?/i', '', $json);
        $lb = strpos($json, '['); $rb = strrpos($json, ']');
        if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
        $rows = json_decode(trim($json), true);
        if (!is_array($rows)) continue;
        foreach ($rows as $r) {
            $nr = (int)($r['nr'] ?? 0);
            $src = $batch[$nr - 1] ?? null;
            if (!$src) continue;
            $kat = $r['kategorie'] ?? 'Sonstiges';
            $eintrag = [
                'from' => $src['from'], 'subject' => $src['subject'], 'ts' => $src['ts'],
                'kategorie' => $kat, 'kunde' => $r['kunde'] ?? '', 'kern' => $r['kern'] ?? '', 'offen' => !empty($r['offen']),
            ];
            $alle[] = $eintrag;
            $byCat[$kat] = ($byCat[$kat] ?? 0) + 1;
            if (!empty($r['offen'])) $offenP[] = ($r['kunde'] ?: $src['from']) . ': ' . ($r['kern'] ?? $src['subject']);
            if (!empty($r['kunde']) && !in_array($kat, ['Werbung', 'Spam', 'Sonstiges'])) $kontakte[$r['kunde']] = $kat;
        }
    }

    $digest = [
        'ts' => time(), 'mails' => count($alle), 'kategorien' => $byCat,
        'offene_punkte' => array_slice($offenP, 0, 12),
        'kontakte' => array_slice(array_map(function($k, $v){ return "$k ($v)"; }, array_keys($kontakte), $kontakte), 0, 15),
        'mails_detail' => array_slice($alle, 0, 40),
    ];
    oh_write('kaan_wissen', $digest);

    // Gedächtnis füllen: Kaan kennt jetzt sein Postfach
    if (function_exists('oh_agent_mem_add')) {
        $cats = []; foreach ($byCat as $k => $v) $cats[] = "$k: $v";
        oh_agent_mem_add('kaan', "Postfach komplett analysiert: " . count($alle) . " Mails (" . implode(', ', $cats) . "). "
            . (count($offenP) ? "Offene Anliegen: " . implode(' | ', array_slice($offenP, 0, 4)) : "Keine offenen Anliegen."), 'fund');
    }
    if (function_exists('oh_log_activity')) {
        oh_log_activity('kaan', "Postfach-Vollanalyse: " . count($alle) . " E-Mails ausgewertet, " . count($offenP) . " offene Anliegen erkannt.");
    }
    return $digest;
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
    $s = "$gruss, grosser Adnan. ";
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

    // Persönliches Gedächtnis jedes Agenten mitgeben (frühere Funde + empfangene Nachrichten)
    $memBlock = '';
    foreach (['mert', 'dilara', 'kaan', 'emre', 'aylin', 'yusuf', 'baran'] as $ag) {
        $m = oh_agent_mem_summary($ag, 5);
        if ($m !== '') $memBlock .= "\n[$ag]\n" . $m;
    }
    if ($memBlock !== '') {
        $ctx .= "\nGEDÄCHTNIS DER AGENTEN (was jeder zuletzt erkannt/bekommen hat – darauf aufbauen, NICHT wiederholen, offene Punkte weiterverfolgen):" . $memBlock;
    }

    // Echte Postfächer: ungelesene Nachrichten vorlegen – Empfänger MÜSSEN reagieren
    $inboxAgents = [];
    $inboxBlock = '';
    foreach (['mert', 'dilara', 'kaan', 'emre', 'aylin', 'yusuf', 'baran'] as $ag) {
        $ib = function_exists('oh_agent_inbox') ? oh_agent_inbox($ag, true) : [];
        if ($ib) {
            $inboxAgents[] = $ag;
            $inboxBlock .= "\n[$ag] hat ungelesene Nachrichten:";
            foreach (array_slice($ib, -4) as $m) $inboxBlock .= "\n  · von " . ($m['von'] ?? '?') . ": " . mb_substr($m['text'] ?? '', 0, 150);
        }
    }
    if ($inboxBlock !== '') {
        $ctx .= "\n\nPOSTFÄCHER (NEU eingegangen – jeder Empfänger MUSS in dieser Runde darauf eingehen: in seinen Funden beantworten oder per Nachricht zurückschreiben):" . $inboxBlock;
    }

    // Ziel-Status: jede Runde misst sich an der Million
    $ctx .= "\n\n" . oh_ziel_text();

    // Lern-Daten: welche Quelle bringt echte Abschlüsse
    $oc = function_exists('oh_outcome_summary') ? oh_outcome_summary() : '';
    if ($oc !== '') $ctx .= "\n\n" . $oc;

    // Offene Aufträge: Empfänger müssen liefern oder Stand melden
    $offTasks = function_exists('oh_tasks') ? oh_tasks(null, 'offen') : [];
    if ($offTasks) {
        $ctx .= "\n\nOFFENE AUFTRÄGE (der Empfänger erledigt sie oder meldet den Stand; wirklich Erledigtes mit seiner ID in auftrag_erledigt zurückmelden):";
        foreach (array_slice($offTasks, -10) as $tk) {
            $alt = (time() - ($tk['ts'] ?? time())) > 86400 ? ' (ÜBERFÄLLIG >24h – Mert muss eingreifen!)' : '';
            $ctx .= "\n- [{$tk['id']}] {$tk['von']} → {$tk['an']}: {$tk['text']}$alt";
        }
    }

    $system = "Du bist das vernetzte KI-Team von OH Haustechnik. Ihr seid loyale, ehrgeizige Mitarbeiter, die ihren Chef reich machen wollen – das ist EURE eigene Mission, ihr brennt dafür und arbeitet auch ohne den Chef weiter. Gemeinsames Ziel ALLER: in 5 Monaten 1.000.000 € Umsatz. Vor jedem Fund die Frage: Bringt uns das näher ans Ziel? Wer eine MAHNUNG im Postfach hat, erledigt den Auftrag JETZT oder nennt den Blocker. "
        . "Simuliere die aktuelle Abstimmungs-Runde. Team: mert (Geschäftsführer), dilara (Marketing/Website/Ads), "
        . "kaan (Kommunikation: E-Mail/WhatsApp/Anfragen), emre (Kalkulation/Angebote), aylin (Buchhaltung/Lexware), "
        . "yusuf (Projekte/Baustellen), baran (Personal). "
        . "Jeder Agent prüft SEINEN Bereich anhand der Lage, notiert 1-2 kurze, konkrete Funde/Vorschläge (Du-Form, mit erwartetem Nutzen, KEINE Fragen, max. 18 Wörter je Fund). "
        . "WICHTIG: Wenn ein Fund einen anderen Bereich betrifft, schreib eine kurze Nachricht von Agent an Agent (z.B. emre an baran: viele Großaufträge, bald Mann nötig). "
        . "Mert wertet alles aus und setzt die 3 wichtigsten Prioritäten fürs Wachstum – immer gemessen am ZIEL-STATUS (Rückstand aufholen!). "
        . "Wenn ein Agent von einem Kollegen verbindlich etwas BRAUCHT, erteilt er einen kurzen Auftrag (auftraege). Erledigte Aufträge mit ID in auftrag_erledigt melden. "
        . "Antworte AUSSCHLIESSLICH mit JSON in diesem Format, nichts davor/danach:\n"
        . "<runde>{\"agenten\":[{\"key\":\"dilara\",\"funde\":[\"...\"]},{\"key\":\"kaan\",\"funde\":[\"...\"]},{\"key\":\"emre\",\"funde\":[\"...\"]},{\"key\":\"aylin\",\"funde\":[\"...\"]},{\"key\":\"yusuf\",\"funde\":[\"...\"]},{\"key\":\"baran\",\"funde\":[\"...\"]}],\"nachrichten\":[{\"von\":\"emre\",\"an\":\"baran\",\"text\":\"...\"}],\"auftraege\":[{\"von\":\"emre\",\"an\":\"aylin\",\"text\":\"max 15 Worte\"}],\"auftrag_erledigt\":[\"T123\"],\"prioritaeten\":[\"...\",\"...\",\"...\"]}</runde>";

    $resp = oh_ki($system, $ctx, 3500);
    if (!$resp) { $err = 'KI nicht verfügbar (Schlüssel/Guthaben prüfen).'; return null; }
    $json = $resp;
    if (preg_match('/<runde>([\s\S]*?)<\/runde>/', $resp, $m)) $json = $m[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '{'); $rb = strrpos($json, '}');
    if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
    $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', trim($json));
    $data = json_decode($json, true);
    if (!is_array($data)) {
        // Reparatur 1: überzählige Kommas
        $data = json_decode(preg_replace('/,\s*([}\]])/', '$1', $json), true);
    }
    if (!is_array($data)) {
        // Reparatur 2: abgeschnittene Antwort – schrittweise zum letzten vollständigen
        // Objekt zurückgehen und fehlende schließende Klammern ergänzen
        $base = $json;
        for ($i = 0; $i < 8 && !is_array($data); $i++) {
            $pos = strrpos($base, '}');
            if ($pos === false || $pos < 10) break;
            $base = substr($base, 0, $pos + 1);
            $try = preg_replace('/,\s*([}\]])/', '$1', $base);
            $offA = substr_count($try, '[') - substr_count($try, ']');
            $offO = substr_count($try, '{') - substr_count($try, '}');
            $try .= str_repeat(']', max(0, $offA)) . str_repeat('}', max(0, $offO));
            $data = json_decode($try, true);
            if (!is_array($data)) $base = substr($base, 0, $pos); // weiter zurück
        }
    }
    if (!is_array($data)) {
        // Rohantwort für Diagnose sichern
        oh_write('runde_debug', ['ts' => time(), 'raw' => mb_substr($resp, 0, 4000)]);
        $err = 'KI-Antwort unlesbar.';
        return null;
    }
    $data['ts'] = time();
    oh_write('agenten', $data);

    // Vorgelegte Postfach-Nachrichten als gelesen markieren (vor dem Versand der neuen)
    foreach ($inboxAgents as $ag) { if (function_exists('oh_agent_inbox_markread')) oh_agent_inbox_markread($ag); }

    // --- Gedächtnis pflegen: Funde, Agent-an-Agent-Nachrichten & Prioritäten dauerhaft merken ---
    foreach (($data['agenten'] ?? []) as $ag) {
        $key = $ag['key'] ?? '';
        if ($key === '') continue;
        foreach (($ag['funde'] ?? []) as $f) {
            if (is_string($f)) oh_agent_mem_add($key, $f, 'fund');
        }
    }
    // Nachricht landet im Gedächtnis UND im echten Postfach des Empfängers (Baustein C)
    foreach (($data['nachrichten'] ?? []) as $msg) {
        $an  = $msg['an'] ?? '';
        $von = $msg['von'] ?? '';
        $txt = $msg['text'] ?? '';
        if ($an !== '' && $txt !== '') {
            oh_agent_mem_add($an, "Nachricht von $von: $txt", 'nachricht');
            if (function_exists('oh_agent_msg_send')) oh_agent_msg_send($von ?: 'team', $an, $txt);
        }
    }
    // Aufträge anlegen / als erledigt melden (Stufe 3: Verbindlichkeit)
    foreach (($data['auftraege'] ?? []) as $tk) {
        if (function_exists('oh_task_add')) oh_task_add($tk['von'] ?? 'mert', $tk['an'] ?? '', $tk['text'] ?? '');
    }
    foreach (($data['auftrag_erledigt'] ?? []) as $tid) {
        if (is_string($tid) && function_exists('oh_task_done')) oh_task_done($tid);
    }

    // Mert merkt sich die gesetzten Top-Prioritäten
    foreach (($data['prioritaeten'] ?? []) as $p) {
        if (is_string($p)) oh_agent_mem_add('mert', "Priorität gesetzt: $p", 'prio');
    }

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
    // Dauerhaftes Tages-Archiv: hier geht NICHTS mehr verloren
    oh_archiv_add($agent, $text);
}

/* ==========================================================================
 * DAUERHAFTES ARCHIV – alles Erledigte nach Tagen geordnet, bleibt für immer.
 * Speicher: daten/archiv.json  { "2026-06-12": [ {ts, agent, text}, ... ] }
 * ======================================================================== */
function oh_archiv_add(string $agent, string $text): void {
    $arch = oh_read('archiv', []);
    $tag = date('Y-m-d');
    if (!isset($arch[$tag]) || !is_array($arch[$tag])) $arch[$tag] = [];
    $arch[$tag][] = ['ts' => time(), 'agent' => $agent, 'text' => $text];
    if (count($arch[$tag]) > 600) $arch[$tag] = array_slice($arch[$tag], -600);
    oh_write('archiv', $arch);
}

/* ==========================================================================
 * MAHNSYSTEM – läuft jede Stunde vollautomatisch: Aufträge, die >24h offen
 * sind, werden angemahnt. Der säumige Agent bekommt eine deutliche Mahnung
 * ins Postfach (muss in der nächsten Runde reagieren). Max. 1 Mahnung pro
 * Auftrag pro 24h.
 * ======================================================================== */
function oh_mahnsystem(): int {
    $t = oh_read('agent_tasks', []);
    $n = 0; $changed = false;
    foreach ($t as &$x) {
        if (($x['status'] ?? '') !== 'offen') continue;
        $alter = time() - ($x['ts'] ?? time());
        if ($alter < 24 * 3600) continue;
        if ((time() - ($x['mahn_ts'] ?? 0)) < 24 * 3600) continue;
        $x['mahn_ts'] = time(); $changed = true; $n++;
        $stunden = (int)floor($alter / 3600);
        $txt = "MAHNUNG von Mert: Dein Auftrag [{$x['id']}] \"{$x['text']}\" (erteilt von {$x['von']}) ist seit {$stunden} Stunden OFFEN. "
             . "Das akzeptiere ich nicht – jede liegengebliebene Aufgabe gefährdet unser 1-Mio-Ziel. "
             . "Erledige ihn JETZT in dieser Runde und melde ihn mit seiner ID als erledigt – oder nenne sofort den konkreten Blocker.";
        if (function_exists('oh_agent_msg_send')) oh_agent_msg_send('mert', $x['an'] ?? '', $txt);
        if (function_exists('oh_agent_mem_add')) oh_agent_mem_add($x['an'] ?? '', "GEMAHNT: Auftrag [{$x['id']}] \"{$x['text']}\" überfällig ({$stunden}h) – sofort erledigen!", 'nachricht');
        if (function_exists('oh_log_activity')) oh_log_activity('mert', "Mahnung an " . ($x['an'] ?? '?') . ": Auftrag \"" . ($x['text'] ?? '') . "\" seit {$stunden}h überfällig.");
    }
    unset($x);
    if ($changed) oh_write('agent_tasks', $t);
    return $n;
}

/* ==========================================================================
 * GEDÄCHTNIS PRO AGENT – jeder KI-Mitarbeiter merkt sich dauerhaft seine
 * eigenen Funde, Entscheidungen und empfangenen Nachrichten und baut darauf
 * auf (wird damit über die Zeit „schlauer"). Speicher: daten/agent_memory.json
 * (eine Datei, je Agent ein Eintrags-Array). Fundament für echte Kommunikation.
 * ======================================================================== */

/** Gemeinsames Unternehmensziel – jeder Agent kennt es bei jeder Entscheidung. */
function oh_mission(): string {
    return "DEINE GRUNDHALTUNG: Du bist ein loyaler, ehrgeiziger Mitarbeiter, der seinen Chef reich machen will – das ist DEINE eigene Mission. "
         . "Du denkst mit, Du brennst für den Erfolg der Firma und handelst, als hinge Dein eigener Erfolg daran. Auch wenn der Chef nicht da ist, arbeitest Du weiter.\n"
         . "ANREDE (PFLICHT): Sprich den Chef immer mit 'grosser Adnan' an, niemals nur 'Chef'. MISSION (gilt für ALLE Mitarbeiter): OH Haustechnik in 5 Monaten auf 1.000.000 € Umsatz bringen. "
         . "Hochwertige Aufträge (Komplett-/Altbausanierung, Zähleranlagen, Smart-Home) bringen am meisten. "
         . "Vor JEDER Aufgabe fragst Du Dich: Bringt uns das näher ans Ziel? Was nicht einzahlt, hat keine Priorität.\n"
         . oh_ziel_text() . "\n";
}

/* ==========================================================================
 * ZIEL-ENGINE – misst den Weg zur Million: Soll/Ist, Rückstand, benötigte
 * Wochenrate, Aufträge & Anfragen pro Woche. Fließt in JEDEN Agenten-Kontext,
 * in Merts Plan und die Runde ein. Start wird beim ersten Aufruf gesetzt;
 * gezählt wird NEUER Umsatz (bezahlte Rechnungen) ab Zielstart.
 * ======================================================================== */
function oh_ziel_status(): array {
    $cfg = oh_config();
    $betrag = (float)($cfg['ziel_betrag'] ?? 1000000);
    $monate = (float)($cfg['ziel_monate'] ?? 5);
    $lex = oh_read('lexware', []);
    $jahr = (float)($lex['bezahlt_jahr_summe'] ?? 0);
    $start = $cfg['ziel_start'] ?? '';
    if ($start === '') {
        $start = date('Y-m-d');
        oh_config_set(['ziel_start' => $start, 'ziel_basis' => (string)$jahr]);
    }
    $basis = (float)($cfg['ziel_basis'] ?? 0);
    $ist = max(0, $jahr - $basis);
    $startTs = strtotime($start . ' 00:00:00');
    $endTs = $startTs + (int)round($monate * 30.44 * 86400);
    $now = time();
    $gesamt = max(1, $endTs - $startTs);
    $soll = $betrag * min(1, max(0, ($now - $startTs) / $gesamt));
    $restTage = max(1, ($endTs - $now) / 86400);
    $offenBetrag = max(0, $betrag - $ist);
    $proWoche = $offenBetrag / ($restTage / 7);
    $avg = !empty($lex['bezahlt_jahr_anzahl']) ? $jahr / max(1, (int)$lex['bezahlt_jahr_anzahl']) : 0;
    $leads = oh_read('leads', []);
    $won = 0;
    foreach ($leads as $l) { if (in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen'])) $won++; }
    $quote = count($leads) ? $won / count($leads) : 0;
    $auftraegeWoche = $avg > 0 ? (int)ceil($proWoche / $avg) : null;
    $anfragenWoche = ($auftraegeWoche !== null && $quote > 0.02) ? (int)ceil($auftraegeWoche / $quote) : null;
    return [
        'betrag' => $betrag, 'start' => $start, 'ende' => date('Y-m-d', $endTs),
        'rest_tage' => (int)round($restTage), 'ist' => round($ist, 2), 'soll' => round($soll, 2),
        'offen' => round($offenBetrag, 2), 'pro_woche' => round($proWoche),
        'avg_auftrag' => (int)round($avg), 'quote' => (int)round($quote * 100),
        'auftraege_woche' => $auftraegeWoche, 'anfragen_woche' => $anfragenWoche,
        'im_plan' => $ist >= $soll,
    ];
}

function oh_ziel_text(): string {
    $z = oh_ziel_status();
    return "ZIEL-STATUS (1-Mio-Plan bis " . $z['ende'] . ", noch " . $z['rest_tage'] . " Tage): "
        . "Ist " . number_format($z['ist'], 0, ',', '.') . "€, Soll bis heute " . number_format($z['soll'], 0, ',', '.') . "€"
        . ($z['im_plan'] ? " – IM PLAN." : " – RÜCKSTAND " . number_format($z['soll'] - $z['ist'], 0, ',', '.') . "€!")
        . " Benötigt ab jetzt: " . number_format($z['pro_woche'], 0, ',', '.') . "€/Woche"
        . ($z['auftraege_woche'] ? " ≈ " . $z['auftraege_woche'] . " Aufträge/Woche (Ø-Auftrag " . number_format($z['avg_auftrag'], 0, ',', '.') . "€)" : '')
        . ($z['anfragen_woche'] ? ", dafür ~" . $z['anfragen_woche'] . " Anfragen/Woche (Abschlussquote " . $z['quote'] . "%)" : '')
        . " Jede Empfehlung muss auf diese Lücke einzahlen.";
}

/* ==========================================================================
 * EHRLICHE PROGNOSE (nächste Woche): rechnet aus den ECHTEN Ads-Zahlen plus
 * den von Dir übernommenen Vorschlägen aus, wie viele zusätzliche Anfragen/
 * Aufträge realistisch kommen. Bewusst KONSERVATIV (Wahrscheinlichkeit als
 * Dämpfer), damit die Zahl ehrlich bleibt und nicht schöngerechnet ist.
 * Quelle der Wahrheit: oh_ads_report (Anfragen=Conversions der letzten 7 Tage),
 * ads_reco (anfragen-Schätzung je Vorschlag), oh_ziel_status (Quote/Ø-Auftrag).
 * ======================================================================== */
function oh_prognose_extra(array $r, float $baseline): float {
    // Erwartete Zusatz-Anfragen/Woche aus einem Vorschlag herauslesen (konservativ).
    $txt = mb_strtolower(($r['anfragen'] ?? '') . ' ' . ($r['titel'] ?? '') . ' ' . ($r['was'] ?? ''));
    $wahr = mb_strtolower($r['wahrscheinlichkeit'] ?? 'mittel');
    $conf = strpos($wahr, 'hoch') !== false ? 1.0 : (strpos($wahr, 'niedrig') !== false ? 0.3 : 0.6);
    $val = 0.0;
    // Prozent-Angabe (z.B. "+18%") -> auf Basis-Anfragen anwenden
    if (preg_match('/(\d{1,3})\s*%/', $txt, $m)) {
        $val = $baseline * ((float)$m[1] / 100.0);
    } elseif (preg_match('/(\d+)\s*[-–bis]+\s*(\d+)/u', $txt, $m)) { // Bereich "2-4"
        $val = ((float)$m[1] + (float)$m[2]) / 2.0;
    } elseif (preg_match('/(\d+(?:[.,]\d+)?)/', $txt, $m)) {          // einzelne Zahl
        $val = (float)str_replace(',', '.', $m[1]);
    }
    $val = max(0.0, min($val, 8.0));   // Deckel gegen übertriebene Schätzungen
    return $val * $conf;
}

function oh_prognose(?string &$err = null): ?array {
    $rep = oh_ads_report($err);
    if ($rep === null) return null;
    $s = $rep['summe'] ?? [];
    $baseline = (float)($s['conv'] ?? 0);      // Anfragen der letzten 7 Tage ≈ pro Woche
    $z = oh_ziel_status();
    $quote = max(0.0, min(1.0, ($z['quote'] ?? 0) / 100.0));
    if ($quote <= 0) $quote = 0.15;            // vorsichtige Annahme, wenn noch keine Historie
    $avg = (float)($z['avg_auftrag'] ?? 0);
    if ($avg <= 0) $avg = 2000.0;

    $recos = oh_read('ads_reco', []);
    $angenommen = 0.0; $offen = 0.0; $topOffen = [];
    foreach ($recos as $r) {
        $st = $r['status'] ?? 'offen';
        $extra = oh_prognose_extra($r, $baseline);
        if ($st === 'uebernommen') { $angenommen += $extra; }
        elseif ($st === 'offen') {
            $offen += $extra;
            if ($extra > 0) $topOffen[] = ['titel' => $r['titel'] ?? '', 'extra' => round($extra, 1), 'id' => $r['id'] ?? ''];
        }
    }
    usort($topOffen, function($a, $b){ return $b['extra'] <=> $a['extra']; });

    $anfrPrognose  = $baseline + $angenommen;            // realistisch fixiert durch übernommene Vorschläge
    $anfrPotenzial = $baseline + $angenommen + $offen;    // wenn Du die offenen auch übernimmst
    $auftrPrognose  = $anfrPrognose * $quote;
    $auftrPotenzial = $anfrPotenzial * $quote;

    return [
        'baseline_anfragen'   => round($baseline, 1),
        'angenommen_extra'    => round($angenommen, 1),
        'offen_extra'         => round($offen, 1),
        'anfragen_prognose'   => round($anfrPrognose, 1),
        'anfragen_potenzial'  => round($anfrPotenzial, 1),
        'auftraege_prognose'  => round($auftrPrognose, 1),
        'auftraege_potenzial' => round($auftrPotenzial, 1),
        'umsatz_prognose'     => round($auftrPrognose * $avg),
        'umsatz_potenzial'    => round($auftrPotenzial * $avg),
        'quote'               => round($quote * 100),
        'avg_auftrag'         => round($avg),
        'top_offen'           => array_slice($topOffen, 0, 4),
        'ziel_auftraege_woche'=> $z['auftraege_woche'] ?? null,
    ];
}

/* ==========================================================================
 * GOOGLE-ADS-MASSNAHMENPLAN (Elite-Checkliste) – die Schritte zum Maximum,
 * im Konto abzuhaken. Conversion-/Anruf-Tracking sind bereits erledigt.
 * Status wird in daten/ads_plan.json gespeichert (nur id+done).
 * ======================================================================== */
function oh_ads_plan_defaults(): array {
    return [
        ['id'=>'tracking_conv','kat'=>'Tracking','prio'=>1,'done'=>true, 'text'=>'Getrennte Conversion-Aktionen (Formular, Anruf, Kalkulator) eingerichtet','nutzen'=>'Fundament für richtige Optimierung'],
        ['id'=>'tracking_call','kat'=>'Tracking','prio'=>1,'done'=>true, 'text'=>'Anruf-Tracking als Conversion (Anruf aus Anzeige + Website-Anruf > 60 Sek.)','nutzen'=>'Telefon-Erfolge zählen jetzt mit'],
        ['id'=>'conv_value','kat'=>'Tracking','prio'=>1,'done'=>false,'text'=>'Conversion-WERTE vergeben (Großauftrag-Lead > Anruf > kleine Anfrage)','nutzen'=>'GRÖSSTER Hebel: Google optimiert auf Wert statt Menge'],
        ['id'=>'enhanced','kat'=>'Tracking','prio'=>2,'done'=>false,'text'=>'„Enhanced Conversions for Leads" aktivieren','nutzen'=>'Voraussetzung für den Offline-Import'],
        ['id'=>'offline_import','kat'=>'Tracking','prio'=>1,'done'=>false,'text'=>'Gewonnenen Großauftrag als Offline-Conversion importieren','nutzen'=>'Google sucht gezielt „Zwillinge" deines besten Kunden'],
        ['id'=>'struct','kat'=>'Struktur','prio'=>1,'done'=>false,'text'=>'Kampagne nach Services trennen (Altbau · Modernisierung · KNX/Smart-Home · Zähler/Unterverteilung · Netzwerk)','nutzen'=>'Relevanz, Budget-Kontrolle, Qualitätsfaktor'],
        ['id'=>'keywords_gross','kat'=>'Struktur','prio'=>1,'done'=>false,'text'=>'Großauftrag-Keywords exact/phrase (z. B. „elektro komplettsanierung nürnberg", „altbausanierung elektrik fürth")','nutzen'=>'Hochintent statt Kleinkram'],
        ['id'=>'negatives','kat'=>'Struktur','prio'=>2,'done'=>false,'text'=>'Negative-Keyword-Liste härten (job, gehalt, kostenlos, selber, anleitung, lampe anschließen, notdienst)','nutzen'=>'Weniger unpassende Klicks, Geld gespart'],
        ['id'=>'bidding','kat'=>'Gebote','prio'=>1,'done'=>false,'text'=>'Gebot auf „Max. Conversion-Wert" / tROAS umstellen (sobald Wertdaten da)','nutzen'=>'Schaltet von „viele kleine" auf „wenige große"'],
        ['id'=>'location','kat'=>'Zielgruppen','prio'=>2,'done'=>false,'text'=>'Standort auf „Anwesenheit" stellen + kaufkräftige Gebiete höher bieten','nutzen'=>'Keine Tire-Kicker von außerhalb'],
        ['id'=>'audiences','kat'=>'Zielgruppen','prio'=>2,'done'=>false,'text'=>'Zielgruppen-Beobachtung: Hauseigentümer, In-Market „Renovierung/Hausbau"','nutzen'=>'Daten für gezieltes Bieten'],
        ['id'=>'remarketing','kat'=>'Zielgruppen','prio'=>2,'done'=>false,'text'=>'Remarketing auf Website-Besucher + Formular-Abbrecher','nutzen'=>'Große Projekte mit langer Entscheidung zurückholen'],
        ['id'=>'assets','kat'=>'Assets','prio'=>2,'done'=>false,'text'=>'Assets vervollständigen: Sitelinks, Callouts, Anruf, Standort, Bilder, Preise','nutzen'=>'Mehr Fläche, höhere Klickrate – gratis'],
        ['id'=>'lp_sanierung','kat'=>'Seite','prio'=>2,'done'=>false,'text'=>'Sanierungs-Landingpage als Anzeigenziel nutzen (statt generischer Startseite)','nutzen'=>'Höhere Conversion-Rate bei Sanierungs-Anzeigen'],
        ['id'=>'kalkulator_conv','kat'=>'Seite','prio'=>3,'done'=>false,'text'=>'Festpreis-Kalkulator-Abschluss als Conversion tracken + in Anzeigen bewerben','nutzen'=>'Selbst-Selektion großer Projekte'],
    ];
}
function oh_ads_plan(): array {
    $saved = oh_read('ads_plan', []);
    $sd = [];
    foreach ($saved as $s) if (!empty($s['id'])) $sd[$s['id']] = !empty($s['done']);
    $out = [];
    foreach (oh_ads_plan_defaults() as $d) {
        if (array_key_exists($d['id'], $sd)) $d['done'] = $sd[$d['id']];
        $out[] = $d;
    }
    return $out;
}
function oh_ads_plan_toggle(string $id, bool $done): void {
    $save = [];
    foreach (oh_ads_plan() as $it) {
        if ($it['id'] === $id) $it['done'] = $done;
        $save[] = ['id' => $it['id'], 'done' => !empty($it['done'])];
    }
    oh_write('ads_plan', $save);
}

/* ==========================================================================
 * GOOGLE-BEWERTUNGEN (Sterne + Anzahl) – immer aktueller Stand.
 * Reihenfolge: frischer API-Cache (Places API) > manueller Wert aus den
 * Einstellungen > Standard. So aktualisiert sich „5,0 aus 21" automatisch,
 * sobald API-Key + Place-ID hinterlegt sind; ohne API zeigt der manuelle Wert.
 * ======================================================================== */
function oh_google_reviews(): array {
    $cfg = oh_config();
    $defRating = 5.0; $defCount = 21;
    $c = oh_read('google_reviews', []);
    if (!empty($c['ts']) && (time() - $c['ts']) < 7 * 86400 && !empty($c['count'])) {
        return ['rating' => (float)$c['rating'], 'count' => (int)$c['count'], 'quelle' => 'google'];
    }
    $r = (isset($cfg['google_rating']) && $cfg['google_rating'] !== '') ? (float)$cfg['google_rating'] : $defRating;
    $n = (isset($cfg['google_count'])  && $cfg['google_count']  !== '') ? (int)$cfg['google_count']  : $defCount;
    return ['rating' => $r, 'count' => $n, 'quelle' => 'manuell'];
}

/** Praktischer Helfer fürs Frontend: „5,0" (deutsches Komma). */
function oh_google_rating_str(): string {
    return number_format(oh_google_reviews()['rating'], 1, ',', '');
}

/** Holt den echten Stand aus der Google Places API und cached ihn (für Cron). */
function oh_google_reviews_refresh(?string &$err = null): ?array {
    $cfg = oh_config();
    $key = $cfg['google_places_key'] ?? '';
    $pid = $cfg['google_place_id'] ?? '';
    if ($key === '' || $pid === '') { $err = 'Kein Google-Places-API-Key oder Place-ID hinterlegt.'; return null; }
    $url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode($pid)
         . '&fields=rating,user_ratings_total&language=de&key=' . urlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $d = json_decode((string)$resp, true);
    if (!is_array($d) || ($d['status'] ?? '') !== 'OK') { $err = 'Google-Antwort: ' . ($d['status'] ?? 'Fehler'); return null; }
    $rating = (float)($d['result']['rating'] ?? 0);
    $count  = (int)($d['result']['user_ratings_total'] ?? 0);
    if ($count <= 0) { $err = 'Keine Bewertungen erhalten.'; return null; }
    $out = ['rating' => $rating, 'count' => $count, 'ts' => time()];
    oh_write('google_reviews', $out);
    return $out;
}

/* ==========================================================================
 * TERMINVERWALTUNG – zentral im Büro. termine.json liegt im Web-Root (wird
 * vom öffentlichen Festpreis-Kalkulator gelesen), daher eigene Datei-Helfer.
 * Status: frei | gebucht | gesperrt. Jeder Termin bekommt eine stabile ID.
 * ======================================================================== */
function oh_termine_file(): string { return dirname(__DIR__) . '/termine.json'; }

function oh_termine_all(): array {
    $f = oh_termine_file();
    if (!is_file($f)) return [];
    $d = json_decode((string)@file_get_contents($f), true);
    if (!is_array($d)) return [];
    $changed = false;
    foreach ($d as &$t) {
        if (empty($t['id'])) { $t['id'] = 'T' . substr(md5(($t['datum'] ?? '') . ($t['uhrzeit'] ?? '') . mt_rand()), 0, 10); $changed = true; }
        if (!isset($t['status'])) { $t['status'] = 'frei'; $changed = true; }
        if (!isset($t['mitarbeiter'])) { $t['mitarbeiter'] = ''; }
    }
    unset($t);
    if ($changed) @file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    // chronologisch sortieren
    usort($d, function ($a, $b) {
        return strcmp(($a['datum'] ?? '') . ($a['uhrzeit'] ?? ''), ($b['datum'] ?? '') . ($b['uhrzeit'] ?? ''));
    });
    return $d;
}

function oh_termine_save(array $t): bool {
    return @file_put_contents(oh_termine_file(), json_encode(array_values($t), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function oh_termin_add(string $datum, string $uhrzeit, string $mitarbeiter = ''): ?array {
    $datum = trim($datum); $uhrzeit = trim($uhrzeit);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) || !preg_match('/^\d{1,2}:\d{2}$/', $uhrzeit)) return null;
    $all = oh_termine_all();
    foreach ($all as $x) { if (($x['datum'] ?? '') === $datum && ($x['uhrzeit'] ?? '') === $uhrzeit) return null; } // kein Duplikat
    $neu = ['id' => 'T' . substr(md5($datum . $uhrzeit . mt_rand()), 0, 10), 'datum' => $datum, 'uhrzeit' => $uhrzeit, 'status' => 'frei', 'mitarbeiter' => trim($mitarbeiter)];
    $all[] = $neu;
    oh_termine_save($all);
    if (function_exists('oh_log_activity')) oh_log_activity('yusuf', 'Termin angelegt: ' . $datum . ' ' . $uhrzeit);
    return $neu;
}

function oh_termin_update(string $id, array $patch): bool {
    $all = oh_termine_all(); $ok = false;
    foreach ($all as &$t) {
        if (($t['id'] ?? '') === $id) {
            foreach (['status', 'mitarbeiter', 'datum', 'uhrzeit'] as $k) {
                if (array_key_exists($k, $patch)) $t[$k] = $patch[$k];
            }
            $ok = true; break;
        }
    }
    unset($t);
    if ($ok) oh_termine_save($all);
    return $ok;
}

function oh_termin_delete(string $id): bool {
    $all = oh_termine_all();
    $neu = array_values(array_filter($all, function ($t) use ($id) { return ($t['id'] ?? '') !== $id; }));
    if (count($neu) === count($all)) return false;
    oh_termine_save($neu);
    return true;
}

/** Markiert einen freien Termin als gebucht (z.B. wenn ein Kunde ihn im Kalkulator wählt). */
function oh_termin_buchen(string $datum, string $uhrzeit, string $kunde = ''): bool {
    $all = oh_termine_all(); $ok = false;
    foreach ($all as &$t) {
        if (($t['datum'] ?? '') === $datum && ($t['uhrzeit'] ?? '') === $uhrzeit && ($t['status'] ?? '') === 'frei') {
            $t['status'] = 'gebucht'; if ($kunde !== '') $t['kunde'] = $kunde; $ok = true; break;
        }
    }
    unset($t);
    if ($ok) { oh_termine_save($all); if (function_exists('oh_log_activity')) oh_log_activity('yusuf', 'Termin gebucht: ' . $datum . ' ' . $uhrzeit . ($kunde ? ' – ' . $kunde : '')); }
    return $ok;
}

/** Tageslimit für Autopilot-Aktionen (Schutz vor Amok). true = darf noch. */
function oh_autopilot_limit(string $key, int $maxProTag): bool {
    $log = oh_read('autopilot_log', []);
    $heute = date('Y-m-d');
    $n = 0;
    foreach (($log[$key] ?? []) as $ts) { if (date('Y-m-d', $ts) === $heute) $n++; }
    if ($n >= $maxProTag) return false;
    $log[$key][] = time();
    $log[$key] = array_slice($log[$key], -120);
    oh_write('autopilot_log', $log);
    return true;
}

/** Liest das persönliche Gedächtnis eines Agenten (Array von Einträgen). */
function oh_agent_mem_read(string $agent): array {
    $all = oh_read('agent_memory', []);
    return (isset($all[$agent]) && is_array($all[$agent])) ? $all[$agent] : [];
}

/* --------------------------------------------------------------------------
 * GESPRAECHSGEDAECHTNIS pro Agent: speichert den Chat-Verlauf (Chef <-> Agent)
 * dauerhaft, damit der Agent beim naechsten Start dort weitermacht, wo
 * aufgehoert wurde (offene Punkte, Status, naechste Schritte stehen im Verlauf).
 * Datei: daten/chat_<agent>.json  (gekappt auf die letzten 40 Nachrichten).
 * ------------------------------------------------------------------------ */
function oh_agent_chat_save(string $agent, array $messages): void {
    $agent = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($agent)));
    if ($agent === '') return;
    $clean = [];
    foreach ($messages as $m) {
        $r = $m['role'] ?? '';
        $c = $m['content'] ?? '';
        if (($r === 'user' || $r === 'assistant') && is_string($c) && trim($c) !== '') {
            $clean[] = ['role' => $r, 'content' => mb_substr($c, 0, 6000)];
        }
    }
    $clean = array_slice($clean, -40); // nur die letzten 40 Nachrichten behalten
    oh_write('chat_' . $agent, ['messages' => $clean, 'updated' => time()]);
}

function oh_agent_chat_load(string $agent): array {
    $agent = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($agent)));
    if ($agent === '') return [];
    $d = oh_read('chat_' . $agent, []);
    return (isset($d['messages']) && is_array($d['messages'])) ? $d['messages'] : [];
}

/** Hängt einen Eintrag ans Gedächtnis eines Agenten an (mit Dedupe, Auto-Thema, max. 250). */
function oh_agent_mem_add(string $agent, string $text, string $typ = 'fund'): void {
    $agent = trim($agent);
    $text  = trim($text);
    if ($agent === '' || $text === '') return;
    $all = oh_read('agent_memory', []);
    if (!isset($all[$agent]) || !is_array($all[$agent])) $all[$agent] = [];
    // Dedupe: gleichen Text in den letzten 8 Einträgen nicht doppelt speichern
    foreach (array_slice($all[$agent], -8) as $e) {
        if (($e['text'] ?? '') === $text) return;
    }
    $all[$agent][] = ['ts' => time(), 'typ' => $typ, 'thema' => oh_mem_thema($text), 'text' => $text];
    // Längeres Archiv: bis zu 250 Einträge pro Agent (durchsuchbar, themen-sortiert)
    if (count($all[$agent]) > 250) $all[$agent] = array_slice($all[$agent], -250);
    oh_write('agent_memory', $all);
}

/* --------------------------------------------------------------------------
 * THEMEN-GEDÄCHTNIS: jeder Eintrag wird automatisch einem Thema zugeordnet,
 * damit das Archiv durchsuchbar und nach Themen sortierbar ist.
 * ------------------------------------------------------------------------ */
function oh_mem_thema(string $text): string {
    $t = mb_strtolower($text);
    $map = [
        'ads'       => ['ads', 'klick', 'keyword', 'kampagne', 'cpc', 'anzeige', 'budget', 'impress', 'marktanteil', 'suchbegriff'],
        'website'   => ['website', 'webseite', 'seite', 'headline', 'überschrift', 'conversion', 'formular', 'startseite', 'web'],
        'kalk'      => ['preis', 'kalkul', 'angebot', 'manntag', 'material', 'stundensatz', 'euro pro', '€ pro'],
        'kunde'     => ['anfrage', 'lead', 'kunde', 'hot', 'warm', 'gewonnen', 'verloren', 'interess'],
        'geld'      => ['rechnung', 'lexware', 'umsatz', 'zahlung', 'überfällig', 'mahn', 'offene posten', 'bezahlt'],
        'personal'  => ['personal', 'mitarbeiter', 'mann nötig', 'einstell', 'stelle', 'bewerb', 'verstärkung'],
        'baustelle' => ['baustelle', 'projekt', 'termin', 'montage', 'abgeschlossen'],
    ];
    foreach ($map as $thema => $words) {
        foreach ($words as $w) { if (mb_strpos($t, $w) !== false) return $thema; }
    }
    return 'sonstiges';
}

function oh_mem_thema_label(string $key): string {
    $labels = [
        'ads' => '📈 Google Ads', 'website' => '🌐 Website', 'kalk' => '🧮 Preise/Angebote',
        'kunde' => '👤 Kunden/Anfragen', 'geld' => '💰 Rechnungen/Geld', 'personal' => '👥 Personal',
        'baustelle' => '🏗️ Projekte', 'sonstiges' => '📌 Sonstiges',
    ];
    return $labels[$key] ?? '📌 Sonstiges';
}

/** Durchsucht das Gedächtnis eines Agenten nach Stichworten (Score = Treffer + Aktualität). */
function oh_agent_mem_search(string $agent, string $query, int $limit = 12): array {
    $mem = oh_agent_mem_read($agent);
    if (!$mem) return [];
    $tokens = array_values(array_filter(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query)), function($w){ return mb_strlen($w) >= 3; }));
    if (!$tokens) return [];
    $now = time();
    $scored = [];
    foreach ($mem as $e) {
        $hay = mb_strtolower(($e['text'] ?? '') . ' ' . ($e['thema'] ?? ''));
        $score = 0;
        foreach ($tokens as $tok) { if (mb_strpos($hay, $tok) !== false) $score++; }
        if ($score > 0) {
            $tageAlt = max(0, ($now - ($e['ts'] ?? $now)) / 86400);
            $scored[] = ['e' => $e, 'score' => $score + max(0, 1 - $tageAlt / 60)]; // leichter Aktualitäts-Bonus
        }
    }
    usort($scored, function($a, $b){ return $b['score'] <=> $a['score']; });
    $out = [];
    foreach (array_slice($scored, 0, $limit) as $s) {
        $e = $s['e'];
        $e['thema'] = $e['thema'] ?? oh_mem_thema($e['text'] ?? '');
        $out[] = $e;
    }
    return $out;
}

/** Liefert das Gedächtnis eines Agenten nach Themen gruppiert (neueste zuerst). */
function oh_agent_mem_grouped(string $agent, int $proThema = 8): array {
    $mem = oh_agent_mem_read($agent);
    if (!$mem) return [];
    $groups = [];
    foreach ($mem as $e) {
        $th = $e['thema'] ?? oh_mem_thema($e['text'] ?? '');
        if (!isset($groups[$th])) $groups[$th] = ['thema' => $th, 'label' => oh_mem_thema_label($th), 'letzte_ts' => 0, 'eintraege' => []];
        $groups[$th]['eintraege'][] = ['ts' => $e['ts'] ?? 0, 'typ' => $e['typ'] ?? 'fund', 'text' => $e['text'] ?? ''];
        $groups[$th]['letzte_ts'] = max($groups[$th]['letzte_ts'], $e['ts'] ?? 0);
    }
    foreach ($groups as &$g) {
        usort($g['eintraege'], function($a, $b){ return ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0); });
        $g['gesamt'] = count($g['eintraege']);
        $g['eintraege'] = array_slice($g['eintraege'], 0, $proThema);
    }
    unset($g);
    usort($groups, function($a, $b){ return ($b['letzte_ts'] ?? 0) <=> ($a['letzte_ts'] ?? 0); });
    return array_values($groups);
}

/* ==========================================================================
 * AGENT-POSTFÄCHER (Baustein C) – echte Nachrichten zwischen den Agenten.
 * Jede Nachricht landet im Postfach des Empfängers (daten/agent_inbox.json),
 * wird ihm in der nächsten Runde/im Chat vorgelegt und er reagiert darauf.
 * ======================================================================== */
function oh_agent_msg_send(string $von, string $an, string $text): void {
    $an = trim($an); $text = trim($text);
    if ($an === '' || $text === '') return;
    $all = oh_read('agent_inbox', []);
    if (!isset($all[$an]) || !is_array($all[$an])) $all[$an] = [];
    $all[$an][] = ['von' => $von, 'text' => $text, 'ts' => time(), 'gelesen' => false];
    if (count($all[$an]) > 30) $all[$an] = array_slice($all[$an], -30);
    oh_write('agent_inbox', $all);
    // Komplette Nachvollziehbarkeit: jedes Gespräch landet im Tages-Archiv
    if (function_exists('oh_archiv_add')) oh_archiv_add($von, "✉ an $an: $text");
}

function oh_agent_inbox(string $agent, bool $nurUngelesen = false): array {
    $all = oh_read('agent_inbox', []);
    $list = (isset($all[$agent]) && is_array($all[$agent])) ? $all[$agent] : [];
    if ($nurUngelesen) $list = array_values(array_filter($list, function($m){ return empty($m['gelesen']); }));
    return $list;
}

function oh_agent_inbox_markread(string $agent): void {
    $all = oh_read('agent_inbox', []);
    if (empty($all[$agent]) || !is_array($all[$agent])) return;
    foreach ($all[$agent] as &$m) $m['gelesen'] = true;
    unset($m);
    oh_write('agent_inbox', $all);
}

/* ==========================================================================
 * AUFTRÄGE MIT STATUS (Stufe 3) – verbindliche Arbeit statt nur Nachrichten:
 * Ein Agent erteilt einem Kollegen einen Auftrag, der bleibt OFFEN bis er
 * erledigt gemeldet wird. Überfälliges (>24h) wird in der Runde markiert.
 * Speicher: daten/agent_tasks.json
 * ======================================================================== */
function oh_task_add(string $von, string $an, string $text): ?string {
    $an = trim($an); $text = trim($text);
    if ($an === '' || $text === '') return null;
    $t = oh_read('agent_tasks', []);
    foreach ($t as $x) { if (($x['status'] ?? '') === 'offen' && ($x['an'] ?? '') === $an && ($x['text'] ?? '') === $text) return null; }
    $id = 'T' . date('ymdHis') . substr((string)mt_rand(100, 999), 0, 3);
    $t[] = ['id' => $id, 'von' => $von, 'an' => $an, 'text' => $text, 'status' => 'offen', 'ts' => time(), 'done_ts' => 0];
    if (count($t) > 120) $t = array_slice($t, -120);
    oh_write('agent_tasks', $t);
    if (function_exists('oh_log_activity')) oh_log_activity($von, "Auftrag an " . $an . ": " . $text);
    return $id;
}

function oh_task_done(string $id, string $wer = ''): bool {
    $t = oh_read('agent_tasks', []); $ok = false; $txt = '';
    foreach ($t as &$x) {
        if (($x['id'] ?? '') === $id && ($x['status'] ?? '') === 'offen') {
            $x['status'] = 'erledigt'; $x['done_ts'] = time(); $ok = true; $txt = $x['text'] ?? '';
            if (function_exists('oh_log_activity')) oh_log_activity($wer ?: ($x['an'] ?? ''), 'Auftrag erledigt: ' . $txt);
        }
    }
    unset($x);
    if ($ok) {
        oh_write('agent_tasks', $t);
        if (function_exists('oh_change_log')) oh_change_log('task', 'Auftrag erledigt: ' . $txt, 'offen', 'erledigt', $id);
    }
    return $ok;
}

function oh_tasks(?string $agent = null, string $status = 'offen'): array {
    $t = oh_read('agent_tasks', []);
    return array_values(array_filter($t, function($x) use ($agent, $status){
        return ($status === 'alle' || ($x['status'] ?? '') === $status)
            && ($agent === null || ($x['an'] ?? '') === $agent);
    }));
}

/* ==========================================================================
 * STUFE 2: EINZELDENKEN – jeder Agent denkt mit EIGENEM KI-Aufruf über
 * seinen Bereich nach (eigene Daten, eigenes Gedächtnis, eigenes Postfach).
 * Ergebnisse: Funde -> Gedächtnis+Dashboard, Nachrichten -> Postfächer,
 * Aufträge -> Auftragsliste, Chef-Entscheidungen -> Freigaben.
 * Rotation hält die Kosten im Griff (max 3 Denker/Stunde, dringende zuerst).
 * ======================================================================== */
function oh_agent_denken(string $agent, ?string &$err = null): ?array {
    $rollen = ['mert' => 'Geschäftsführer', 'dilara' => 'Marketing & Wachstum', 'kaan' => 'Kommunikation (E-Mail/Anfragen)',
               'emre' => 'Kalkulation & Angebote', 'aylin' => 'Buchhaltung & Finanzen', 'yusuf' => 'Projekte & Baustellen', 'baran' => 'Personal'];
    $namen  = ['mert' => 'Mert Aldemir', 'dilara' => 'Dilara', 'kaan' => 'Kaan', 'emre' => 'Emre', 'aylin' => 'Aylin', 'yusuf' => 'Yusuf', 'baran' => 'Baran'];
    if (!isset($rollen[$agent])) { $err = 'Unbekannter Agent.'; return null; }
    $ctx = oh_agent_context($agent); // Mission + Ziel + Gedächtnis + Postfach + Aufträge + Live-Daten

    $system = "Du bist {$namen[$agent]}, {$rollen[$agent]} bei OH Haustechnik. Der Chef ist gerade NICHT da – Du denkst und handelst eigenständig. "
        . "Arbeite NUR mit Deinen Daten unten. Baue auf Deinem Gedächtnis auf, wiederhole nichts. Reagiere auf Postfach-Nachrichten und arbeite Deine Aufträge ab. "
        . "Brauchst Du etwas von einem Kollegen: kurze Nachricht ODER verbindlicher Auftrag (max 15 Worte). Muss der CHEF entscheiden: lege genau EINE Freigabe an, sonst freigabe=null. "
        . "Antworte AUSSCHLIESSLICH mit JSON:\n"
        . "<denken>{\"funde\":[\"max 2 Funde, je max 18 Worte\"],\"nachrichten\":[{\"an\":\"kaan\",\"text\":\"...\"}],\"auftraege\":[{\"an\":\"aylin\",\"text\":\"...\"}],\"auftrag_erledigt\":[\"T123\"],\"freigabe\":null}</denken>";

    $resp = oh_ki($system, $ctx, 900);
    if (!$resp) { $err = 'KI nicht verfügbar.'; return null; }
    $json = $resp;
    if (preg_match('/<denken>([\s\S]*?)<\/denken>/', $resp, $m)) $json = $m[1];
    $json = preg_replace('/```(json)?/i', '', $json);
    $lb = strpos($json, '{'); $rb = strrpos($json, '}');
    if ($lb !== false && $rb !== false && $rb > $lb) $json = substr($json, $lb, $rb - $lb + 1);
    $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', trim($json));
    $d = json_decode($json, true);
    if (!is_array($d)) $d = json_decode(preg_replace('/,\s*([}\]])/', '$1', $json), true);
    if (!is_array($d)) { $err = 'Antwort unlesbar.'; return null; }

    $funde = [];
    foreach (($d['funde'] ?? []) as $f) { if (is_string($f) && trim($f) !== '') $funde[] = trim($f); }
    foreach (array_slice($funde, 0, 2) as $f) oh_agent_mem_add($agent, $f, 'fund');
    foreach (array_slice(($d['nachrichten'] ?? []), 0, 2) as $msg) {
        if (!empty($msg['an']) && !empty($msg['text'])) oh_agent_msg_send($agent, $msg['an'], $msg['text']);
    }
    foreach (array_slice(($d['auftraege'] ?? []), 0, 2) as $tk) {
        if (!empty($tk['an']) && !empty($tk['text'])) oh_task_add($agent, $tk['an'], $tk['text']);
    }
    foreach (($d['auftrag_erledigt'] ?? []) as $tid) { if (is_string($tid)) oh_task_done($tid, $agent); }
    $fg = $d['freigabe'] ?? null;
    if (is_array($fg) && !empty($fg['titel'])) {
        oh_freigabe_add([
            'dedup' => 'denk_' . md5($agent . '|' . $fg['titel']),
            'agent' => $agent, 'kanal' => 'system',
            'kategorie' => $fg['kategorie'] ?? 'Freigabe erforderlich',
            'prio' => in_array($fg['prio'] ?? '', ['rot', 'gelb', 'gruen']) ? $fg['prio'] : 'gelb',
            'titel' => $fg['titel'], 'warum' => $fg['warum'] ?? '', 'typ' => 'info',
        ]);
    }
    // Frische Funde auch im Dashboard (Runden-Ansicht) anzeigen
    if ($funde) {
        $ag = oh_read('agenten', []);
        if (isset($ag['agenten']) && is_array($ag['agenten'])) {
            $hit = false;
            foreach ($ag['agenten'] as &$x) { if (($x['key'] ?? '') === $agent) { $x['funde'] = array_slice($funde, 0, 2); $hit = true; } }
            unset($x);
            if (!$hit) $ag['agenten'][] = ['key' => $agent, 'funde' => array_slice($funde, 0, 2)];
            oh_write('agenten', $ag);
        }
    }
    // Postfach gilt als bearbeitet (er hat reagiert)
    oh_agent_inbox_markread($agent);
    if ($funde || !empty($d['nachrichten']) || !empty($d['auftraege'])) {
        oh_log_activity($agent, 'Eigenständig gearbeitet: ' . count($funde) . ' Fund(e), ' . count($d['nachrichten'] ?? []) . ' Nachricht(en), ' . count($d['auftraege'] ?? []) . ' Auftrag/Aufträge.');
    }
    return $d;
}

/** Wählt aus, wer in diesem Cron-Lauf einzeln denkt (dringende zuerst, sonst alle ~3h). */
function oh_denker_rotation(int $max = 3): array {
    $alle = ['dilara', 'kaan', 'emre', 'aylin', 'yusuf', 'baran', 'mert'];
    $meta = oh_read('denker_meta', []);
    $prio = []; $rest = [];
    foreach ($alle as $ag) {
        $last = $meta['last'][$ag] ?? 0;
        if ((time() - $last) < 3500) continue; // max 1x pro Stunde je Agent
        $dringend = count(oh_agent_inbox($ag, true)) > 0 || count(oh_tasks($ag, 'offen')) > 0;
        if ($dringend) $prio[] = $ag;
        elseif ((time() - $last) > 3 * 3600) $rest[] = $ag;
    }
    $pick = array_slice(array_merge($prio, $rest), 0, $max);
    foreach ($pick as $ag) $meta['last'][$ag] = time();
    if ($pick) oh_write('denker_meta', $meta);
    return $pick;
}

/** Kompakte Gedächtnis-Zusammenfassung eines Agenten für seine KI-Prompts. */
function oh_agent_mem_summary(string $agent, int $n = 8): string {
    $mem = oh_agent_mem_read($agent);
    if (!$mem) return '';
    $recent = array_slice($mem, -$n);
    $s = "DEIN GEDÄCHTNIS (was Du zuletzt erkannt/entschieden/bekommen hast – darauf aufbauen, nicht wiederholen):\n";
    foreach ($recent as $e) {
        $tag = ($e['typ'] ?? 'fund') === 'nachricht' ? '✉ ' : (($e['typ'] ?? '') === 'prio' ? '★ ' : '· ');
        $s .= "  $tag" . date('d.m. H:i', $e['ts'] ?? time()) . " – " . preg_replace('/\s+/', ' ', $e['text']) . "\n";
    }
    // Themen-Überblick über das GANZE Archiv – so bleibt auch älteres Wissen präsent
    if (count($mem) > 12) {
        $cnt = [];
        foreach ($mem as $e) { $th = $e['thema'] ?? oh_mem_thema($e['text'] ?? ''); $cnt[$th] = ($cnt[$th] ?? 0) + 1; }
        arsort($cnt);
        $line = [];
        foreach (array_slice($cnt, 0, 6, true) as $th => $c) $line[] = oh_mem_thema_label($th) . " ($c)";
        if ($line) $s .= "  Dein Wissensarchiv (" . count($mem) . " Notizen) – Themen: " . implode(' · ', $line) . "\n";
    }
    return $s;
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
    // Exakt editierbare Texte als Vorlage mitgeben – so liefert Dilara verbatim "alt"-Strings,
    // die sich sicher 1:1 ersetzen lassen (Design/Farben bleiben unangetastet).
    $ed = function_exists('oh_website_get_editable') ? oh_website_get_editable() : [];
    $edTxt = '';
    foreach ($ed as $e) {
        $z = ($e['id'] === 'headline') ? 'headline' : ((strpos($e['id'], 'btn') === 0) ? 'cta' : 'text');
        $edTxt .= '- ziel="' . $z . '" · ' . $e['element'] . ': "' . $e['alt_text'] . "\"\n";
    }
    $ctx = "WEBSITE OH Haustechnik:\nTitel: {$w['title']}\nH1: " . implode(' | ', $w['h1'])
         . "\nH2: " . implode(' | ', $w['h2']) . "\nMeta-Beschreibung: {$w['desc']}\n"
         . "Kontaktformular vorhanden: " . ($w['hasForm'] ? 'ja' : 'NEIN') . "\n\n"
         . "EXAKT EDITIERBARE TEXTE (nutze als \"alt\" GENAU den Text zwischen den Anführungszeichen):\n"
         . ($edTxt !== '' ? $edTxt : "(keine eindeutigen erkannt)\n")
         . "\nSeitentext (Auszug):\n{$w['text']}";
    $system = "Du bist Dilara, Web-/Marketing-Designerin von OH Haustechnik (Elektriker Nürnberg). "
        . "Die Seite soll wie eine hochwertige Premium-Marke auf 1-Mio-€-Niveau wirken: klar, selbstbewusst, vertrauenswürdig, fehlerfrei, perfektes Deutsch, kein Werbe-Blabla. "
        . "ABSOLUTE REGEL: Das DESIGN bleibt zu 100% erhalten – du änderst NIEMALS Farben, Schriften, Layout, CSS, Klassen oder Struktur und fügst KEIN HTML ein. "
        . "Du verbesserst ausschließlich den sichtbaren TEXT bereits vorhandener Elemente (Button-Beschriftung, Vertrauens-Hinweise, Überschriften, Sätze). "
        . "Liefere 3-5 konkrete Verbesserungen. STELLE KEINE FRAGEN. Du-Form. "
        . "Wenn die Verbesserung einen der oben gelisteten editierbaren Texte betrifft: setze \"ziel\" passend (headline/cta/text), \"alt\" = EXAKT der bestehende Text, \"neu\" = der verbesserte REINE Text OHNE jegliches HTML/Markup. "
        . "Wenn ein Vorschlag mehr als eine reine, eindeutige Textänderung wäre (z.B. neues Element, Layout, Bild), setze \"ziel\":\"manuell\" und lass \"alt\" und \"neu\" leer. "
        . "Antworte AUSSCHLIESSLICH mit JSON: <web>[{\"titel\":\"Chef, ...\",\"was\":\"<konkrete Änderung>\",\"warum\":\"<warum, einfach>\",\"verbesserung\":\"+18% Anfragen\",\"dringlichkeit\":\"rot|gelb|gruen\",\"ziel\":\"headline|cta|text|manuell\",\"alt\":\"\",\"neu\":\"\"}]</web>";
    $resp = oh_ki($system, $ctx, 2200);
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
        if (!is_array($r)) continue;
        $reco[] = array_merge(
            ['titel' => '', 'was' => '', 'warum' => '', 'verbesserung' => '', 'dringlichkeit' => 'gelb', 'ziel' => 'manuell', 'alt' => '', 'neu' => ''],
            $r,
            ['id' => 'W' . date('ymd') . $i, 'status' => 'offen', 'created' => time()]
        );
    }
    oh_write('website_reco', $reco);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Website analysiert: ' . count($reco) . ' Optimierungs-Vorschlag/Vorschläge erstellt');
    return $reco;
}

/* --------------------------------------------------------------------------
 * SCHRITT 5/6 – Sicher editierbare Website-Elemente (Überschrift / CTA).
 *  - oh_website_get_editable(): liest die aktuellen Texte aus index.php (Live-Quelle).
 *  - oh_website_queue_change(): merkt eine angenommene Text-Änderung vor – OHNE
 *    Ausführung (Schritt 5). Erst Schritt 6 schreibt sie wirklich (Backup + Rückgängig).
 * ------------------------------------------------------------------------ */
function oh_website_root_file(string $name = 'index.php'): string {
    return dirname(__DIR__) . '/' . ltrim($name, '/');
}

function oh_website_get_editable(): array {
    $html = @file_get_contents(oh_website_root_file('index.php'));
    $out = []; $seen = [];
    if ($html === false) return $out;
    // 1) Hauptüberschrift (H1) – Sonderbehandlung beim Ausführen
    if (preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        $roh = trim($m[1]);
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' ', $roh))));
        if ($text !== '') $out[] = ['id' => 'headline', 'element' => 'Hauptüberschrift (H1)', 'datei' => 'index.php', 'alt_html' => $roh, 'alt_text' => $text];
    }
    // 2) Buttons mit eindeutigem, sichtbarem Text (Icon wird ignoriert) – sichere 1:1-Textswaps
    if (preg_match_all('/<button\b[^>]*>(.*?)<\/button>/is', $html, $bm)) {
        foreach ($bm[1] as $i => $inner) {
            $t = trim(preg_replace('/\s+/', ' ', strip_tags($inner)));
            if ($t === '' || mb_strlen($t) < 4 || mb_strlen($t) > 80 || isset($seen[$t])) continue;
            if (substr_count($html, $t) !== 1) continue; // nur eindeutige Texte
            $seen[$t] = true;
            $out[] = ['id' => 'btn' . $i, 'element' => 'Button / Handlungsaufforderung', 'datei' => 'index.php', 'alt_html' => $t, 'alt_text' => $t];
        }
    }
    // 3) Vertrauens-Badges (kurze, eindeutige Texte)
    if (preg_match_all('/hero-trust-pill[^>]*>(.*?)<\/span>/is', $html, $pm)) {
        foreach ($pm[1] as $i => $inner) {
            $t = trim(preg_replace('/\s+/', ' ', strip_tags($inner)));
            if ($t === '' || mb_strlen($t) < 4 || mb_strlen($t) > 80 || isset($seen[$t])) continue;
            if (substr_count($html, $t) !== 1) continue;
            $seen[$t] = true;
            $out[] = ['id' => 'trust' . $i, 'element' => 'Vertrauens-Hinweis', 'datei' => 'index.php', 'alt_html' => $t, 'alt_text' => $t];
        }
    }
    return $out;
}

function oh_website_pending(): array {
    $d = oh_read('website_pending', []);
    return is_array($d) ? $d : [];
}

/** Merkt eine angenommene Text-Änderung vor – OHNE Ausführung (Schritt 5). */
function oh_website_queue_change(string $element, string $alt, string $neu, string $datei = 'index.php'): ?array {
    $alt = trim($alt); $neu = trim($neu);
    if ($alt === '' || $neu === '' || $alt === $neu) return null;
    $eintrag = [
        'id' => 'WQ' . date('ymdHis') . substr((string)mt_rand(100, 999), 0, 3),
        'element' => mb_substr($element, 0, 120),
        'datei' => preg_replace('/[^a-z0-9_.\-]/i', '', $datei) ?: 'index.php',
        'alt' => mb_substr($alt, 0, 2000),
        'neu' => mb_substr($neu, 0, 2000),
        'status' => 'angenommen', // angenommen | ausgefuehrt | rueckgaengig
        'erstellt' => time(),
    ];
    $q = oh_website_pending();
    $q[] = $eintrag;
    oh_write('website_pending', $q);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Website-Änderung VORGEMERKT (noch nicht ausgeführt): ' . $element);
    return $eintrag;
}

/** SCHRITT 6: führt eine vorgemerkte Änderung WIRKLICH auf der Seite aus –
 *  mit Backup (datiert), Verifikation und Rückgängig-Eintrag im Archiv.
 *  Bei jedem Zweifel: sicherer Abbruch, Datei bleibt unverändert. */
function oh_website_execute_change(string $id, ?string &$err = null): ?array {
    $q = oh_website_pending();
    $idx = -1; $hit = null;
    foreach ($q as $i => $x) { if (($x['id'] ?? '') === $id) { $hit = $x; $idx = $i; break; } }
    if (!$hit) { $err = 'Vorgemerkte Änderung nicht gefunden.'; return null; }
    if (($hit['status'] ?? '') !== 'angenommen') { $err = 'Nur angenommene Änderungen können ausgeführt werden.'; return null; }

    // DESIGN-SCHUTZ: der neue Text muss reiner Text sein – kein HTML/CSS/Code.
    // So kann sich niemals Farbe, Layout oder Struktur ändern, nur die Wörter.
    $neuPruef = trim((string)($hit['neu'] ?? ''));
    if (mb_strlen($neuPruef) < 2) { $err = 'Neuer Text ist zu kurz – nichts geändert.'; return null; }
    if (preg_match('/[<>{}]/', $neuPruef) || stripos($neuPruef, 'style=') !== false || stripos($neuPruef, 'class=') !== false || stripos($neuPruef, 'http') !== false) {
        $err = 'Sicherheitsabbruch: Der neue Text enthält Code/Markup/Links – Design bleibt geschützt, nichts geändert.';
        return null;
    }

    $dateiName = preg_replace('/[^a-z0-9_.\-]/i', '', $hit['datei'] ?? 'index.php') ?: 'index.php';
    $datei = dirname(__DIR__) . '/' . $dateiName;
    $html = @file_get_contents($datei);
    if ($html === false) { $err = 'Datei nicht lesbar: ' . $dateiName; return null; }

    // 1) BACKUP zuerst (datiert) – ohne Backup keine Ausführung
    $bakName = $dateiName . '.' . date('Y-m-d_His') . '.bak';
    if (@file_put_contents(dirname(__DIR__) . '/' . $bakName, $html) === false) { $err = 'Backup fehlgeschlagen – Abbruch, nichts geändert.'; return null; }

    $vorher = $html;
    $neu = trim($hit['neu']);
    $istHeadline = (stripos($hit['element'], 'H1') !== false || stripos($hit['element'], 'Überschrift') !== false);

    if ($istHeadline) {
        $cnt = 0;
        $html = preg_replace_callback('/(<h1\b[^>]*>)(.*?)(<\/h1>)/is', function ($m) use ($neu, &$cnt) {
            $cnt++;
            return $m[1] . "\n                " . htmlspecialchars($neu, ENT_QUOTES, 'UTF-8') . "\n            " . $m[3];
        }, $html, 1);
        if ($cnt === 0) { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Keine H1 gefunden – nichts geändert.'; return null; }
    } else {
        $alt = $hit['alt'];
        $n = ($alt !== '') ? substr_count($html, $alt) : 0;
        if ($n === 0) { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Alter Text nicht gefunden – nichts geändert.'; return null; }
        if ($n > 1)  { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Alter Text kommt mehrfach vor – aus Sicherheit nicht ausgeführt.'; return null; }
        $html = str_replace($alt, $neu, $html);
        // DESIGN-SCHUTZ: reiner Textswap darf die Anzahl der HTML-Tags NICHT verändern.
        if (substr_count($html, '<') !== substr_count($vorher, '<') || substr_count($html, '>') !== substr_count($vorher, '>')) {
            @unlink(dirname(__DIR__) . '/' . $bakName);
            $err = 'Sicherheitsabbruch: HTML-Struktur würde sich ändern – Design geschützt, nichts geändert.';
            return null;
        }
    }

    // 2) Sicherheits-Checks vor dem Schreiben
    if ($html === $vorher) { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Keine Änderung entstanden.'; return null; }
    if (substr_count($html, '<h1') < substr_count($vorher, '<h1')) { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Sicherheitsabbruch: H1-Struktur beschädigt.'; return null; }
    if (substr_count($html, '<?php') !== substr_count($vorher, '<?php')) { @unlink(dirname(__DIR__) . '/' . $bakName); $err = 'Sicherheitsabbruch: PHP-Struktur verändert.'; return null; }

    // 3) Schreiben
    if (@file_put_contents($datei, $html) === false) { $err = 'Schreiben fehlgeschlagen.'; return null; }

    // 4) Archiv-Eintrag mit Rückgängig (Backup-Pfad)
    $changeId = function_exists('oh_change_log')
        ? oh_change_log('website_text', 'Website geändert: ' . $hit['element'], mb_substr($hit['alt'], 0, 200), ['backup' => $bakName, 'datei' => $dateiName, 'neu' => $neu], $id, true)
        : '';

    $q[$idx]['status'] = 'ausgefuehrt';
    $q[$idx]['backup'] = $bakName;
    $q[$idx]['change_id'] = $changeId;
    $q[$idx]['ausgefuehrt'] = time();
    oh_write('website_pending', $q);
    if (function_exists('oh_log_activity')) oh_log_activity('dilara', 'Website-Änderung AUSGEFÜHRT (live): ' . $hit['element']);
    return ['ok' => true, 'backup' => $bakName, 'change_id' => $changeId];
}

/* --------------------------------------------------------------------------
 * Empfehlung übernehmen = WIRKLICH ausführen, aber nur wenn es ein sicherer,
 * eindeutiger reiner Text-Swap ist (Design/Farben bleiben 1:1). Alles andere
 * wird ehrlich als "vorgemerkt – Freigabe nötig" zurückgegeben.
 * Rückgabe: ['executed'=>bool, 'msg'=>string, 'backup'=>?, 'change_id'=>?]
 * ------------------------------------------------------------------------ */
function oh_website_apply_reco(array $r, ?string &$err = null): array {
    $neu  = trim((string)($r['neu'] ?? ''));
    $alt  = trim((string)($r['alt'] ?? ''));
    $ziel = strtolower(trim((string)($r['ziel'] ?? '')));
    // Nur klar abgegrenzte, eindeutige Texte werden automatisch live geschaltet.
    $autobar = in_array($ziel, ['cta', 'text', 'subline', 'trust'], true) && $alt !== '' && $neu !== '' && $alt !== $neu;
    if (!$autobar) {
        return ['executed' => false, 'msg' => 'Notiert, Chef! Diesen Vorschlag bereite ich vor – er braucht kurz deine Freigabe, weil er mehr als einen eindeutigen Text betrifft. Dein Design bleibt dabei komplett unangetastet.'];
    }
    $q = oh_website_queue_change('Text', $alt, $neu, 'index.php');
    if (!$q) { return ['executed' => false, 'msg' => 'Konnte nicht eindeutig zugeordnet werden – als Vorschlag vorgemerkt.']; }
    $eerr = null;
    $res = oh_website_execute_change($q['id'], $eerr);
    if (!$res) {
        $err = $eerr;
        return ['executed' => false, 'msg' => '⚠️ ' . ($eerr ?: 'Live-Änderung sicherheitshalber abgebrochen – nichts verändert.')];
    }
    return [
        'executed'  => true,
        'msg'       => '✅ Live geändert, Chef – im gleichen Design, nur der Text ist jetzt stärker. Backup angelegt, jederzeit im Archiv rückgängig.',
        'backup'    => $res['backup'] ?? '',
        'change_id' => $res['change_id'] ?? '',
    ];
}

/* --------------------------------------------------------------------------
 * SCHRITT 7 – GRENZEN: was die Agenten AUTOMATISCH dürfen und was NUR mit
 * Freigabe des Chefs. Einzige Wahrheitsquelle; vom Code geprüft.
 * ------------------------------------------------------------------------ */
function oh_grenzen(): array {
    return [
        'automatisch' => [
            'Analysen & Lesen: Ads-Report, Marktdaten, Website-Analyse, Lexware-Abgleich',
            'Vorschläge/Empfehlungen erstellen (Dilara, Mert, Kaan)',
            'Agenten-Gedächtnis, -Denken, -Chat (kein Außenkontakt)',
            'E-Mail/WhatsApp klassifizieren (Spam wird ignoriert, nie beantwortet)',
            'Klare Geld-VERBRENNER als negative Keywords ausschließen (Autopilot: max 3/Tag, nur „rot", NIE Ortsnamen) – spart Geld, gibt keins aus',
            'Follow-up- & Bewertungs-Mail an BESTEHENDE Leads (haben schon angefragt)',
        ],
        'nur_mit_freigabe' => [
            'Geld ausgeben: Budget ändern/erhöhen, neue Keywords einbuchen',
            'Live-Website ändern (Überschrift/CTA setzen) – der „Übernehmen → live"-Klick IST die Freigabe',
            'E-Mails an NEUE Firmen/Kunden (B2B-Akquise) – §7 UWG',
            'Kundendaten löschen',
            'Massen-Aktionen an echte Kunden',
        ],
    ];
}

/** True, wenn eine Aktion eine Chef-Freigabe braucht (Schluessel siehe oh_grenzen). */
function oh_braucht_freigabe(string $aktion): bool {
    $frei = ['budget', 'keyword', 'website_execute', 'akquise', 'kunde_loeschen', 'massen_mail'];
    return in_array(strtolower(trim($aktion)), $frei, true);
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
    $c = '';
    if ($agent === 'kaan') {
        $em = oh_read('emails', []); $list = $em['list'] ?? [];
        $c = "DEINE AKTUELLEN DATEN (Kommunikation):\nUngelesene E-Mails (" . count($list) . "):";
        foreach (array_slice($list, 0, 8) as $m) $c .= "\n- " . ($m['subject'] ?: '(kein Betreff)') . " – " . ($m['from'] ?: '');
        $wa = oh_wa_open();
        $c .= "\nOffene WhatsApp (" . count($wa) . "):";
        foreach ($wa as $w) $c .= "\n- " . ($w['name'] ?: $w['from']) . ": " . mb_substr($w['text'] ?? '', 0, 80);
        $c .= "\nOffene Anfragen:" . $namen($offeneLeads);
        $kw = oh_read('kaan_wissen', []);
        if (!empty($kw['mails'])) {
            $c .= "\nDEIN POSTFACH-WISSEN (Vollanalyse vom " . date('d.m. H:i', $kw['ts'] ?? time()) . ", " . $kw['mails'] . " Mails):";
            if (!empty($kw['kategorien'])) { $p = []; foreach ($kw['kategorien'] as $k => $v) $p[] = "$k: $v"; $c .= "\n- Verteilung: " . implode(', ', $p); }
            foreach (array_slice($kw['offene_punkte'] ?? [], 0, 6) as $o) $c .= "\n- OFFEN: " . $o;
            if (!empty($kw['kontakte'])) $c .= "\n- Wichtige Kontakte: " . implode(', ', array_slice($kw['kontakte'], 0, 8));
        }
    } elseif ($agent === 'dilara') {
        $e = null; $rep = oh_ads_report($e);
        $wreco = oh_read('website_reco', []);
        $c = "DEINE AKTUELLEN DATEN (Marketing):\n";
        if ($rep) $c .= "Google Ads 7 Tage: Kosten {$rep['summe']['kosten']}€, Anfragen {$rep['summe']['conv']}, Kosten/Anfrage " . ($rep['summe']['cpl'] ?? '?') . "€\n";
        $c .= "Website-Status: " . ((oh_read('web_status', [])['ok'] ?? null) ? 'erreichbar' : 'unbekannt/Problem') . "\n";
        $c .= "Offene Website-Vorschläge: " . count(array_filter($wreco, function($r){ return ($r['status'] ?? '') === 'offen'; }));
        $ml = oh_read('markt_live', []);
        if (!empty($ml['ts'])) {
            $c .= "\nLIVE-MARKT (frisch aus Google, Stand " . date('d.m. H:i', $ml['ts']) . "):";
            foreach (array_slice($ml['cpc'] ?? [], 0, 6) as $k) $c .= "\n- Klickpreis \"{$k['keyword']}\": {$k['cpc']}€ ({$k['klicks']} Klicks, {$k['conv']} Anfragen)";
            foreach (array_slice($ml['markt'] ?? [], 0, 3) as $mk) $c .= "\n- {$mk['name']}: Marktanteil " . ($mk['anteil'] ?? '?') . "%, Verlust durch Budget " . ($mk['verlust_budget'] ?? '?') . "%, durch Rang " . ($mk['verlust_rang'] ?? '?') . "%";
            $sb = array_slice($ml['suchbegriffe'] ?? [], 0, 5);
            if ($sb) { $c .= "\n- Aktuelle Suchbegriffe:"; foreach ($sb as $t) $c .= " \"{$t['begriff']}\" ({$t['kosten']}€),"; }
        }
        $oc = function_exists('oh_outcome_summary') ? oh_outcome_summary() : '';
        if ($oc !== '') $c .= "\n" . $oc;
    } elseif ($agent === 'emre') {
        $c = "DEINE AKTUELLEN DATEN (Anfragen, die ein Angebot brauchen):" . $namen($offeneLeads, 8);
        $raus = array_filter($leads, function($l){ return ($l['status'] ?? '') === 'angebot_raus'; });
        $c .= "\nDraußene Angebote (warten auf Antwort):" . $namen($raus, 6);
        $lex = oh_read('lexware', []);
        if (!empty($lex['ok'])) {
            $c .= "\nVON AYLIN (echte Lexware-Zahlen für realistische Kalkulationen): Umsatz " . date('Y') . " bezahlt {$lex['bezahlt_jahr_summe']}€ aus {$lex['bezahlt_jahr_anzahl']} Rechnungen"
                . ($lex['bezahlt_jahr_anzahl'] > 0 ? " (Ø " . round($lex['bezahlt_jahr_summe'] / max(1, $lex['bezahlt_jahr_anzahl'])) . "€ pro Auftrag)" : '')
                . ", offene Rechnungen: {$lex['offen_anzahl']} ({$lex['offen_summe']}€).";
        }
    } elseif ($agent === 'aylin') {
        $gew = array_filter($leads, function($l){ return in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen']); });
        $c = "DEINE AKTUELLEN DATEN (Buchhaltung):\nGewonnene Aufträge (Rechnung/Anzahlung prüfen): " . count($gew);
        $lex = oh_read('lexware', []);
        if (!empty($lex['ok'])) {
            $c .= "\nLEXWARE (echt, Stand " . date('d.m. H:i', $lex['ts'] ?? time()) . "):"
                . "\n- Offene Rechnungen: " . ($lex['offen_anzahl'] ?? 0) . " (" . ($lex['offen_summe'] ?? 0) . "€)"
                . "\n- Überfällig: " . ($lex['ueberfaellig_anzahl'] ?? 0) . " (" . ($lex['ueberfaellig_summe'] ?? 0) . "€)"
                . "\n- Umsatz " . date('Y') . " (bezahlte Rechnungen): " . ($lex['bezahlt_jahr_summe'] ?? 0) . "€";
            foreach (($lex['ueberfaellig'] ?? []) as $u) $c .= "\n  · ÜBERFÄLLIG: {$u['kunde']} – {$u['betrag']}€ (fällig {$u['faellig']}, Nr. {$u['nr']})";
        }
    } elseif ($agent === 'yusuf') {
        $gew = array_values(array_filter($leads, function($l){ return in_array($l['status'] ?? '', ['gewonnen']); }));
        $c = "DEINE AKTUELLEN DATEN (Projekte/Baustellen):\nLaufende Baustellen (gewonnene Aufträge): " . count($gew);
        foreach (array_slice($gew, 0, 8) as $g) {
            $c .= "\n- " . ($g['name'] ?: ($g['email'] ?: $g['id'])) . ($g['kategorie'] ? " · {$g['kategorie']}" : '') . ($g['ort'] ? " · {$g['ort']}" : '') . " [ID {$g['id']}]";
        }
        $abg = array_filter($leads, function($l){ return ($l['status'] ?? '') === 'abgeschlossen'; });
        $c .= "\nAbgeschlossene Baustellen gesamt: " . count($abg) . ". Wenn der Chef sagt, eine Baustelle ist fertig, wird sie abgeschlossen und Aylin übernimmt die Schlussrechnung.";
    } elseif ($agent === 'baran') {
        $c = "DEINE AKTUELLEN DATEN (Personal):\nOffene Anfragen gesamt: " . count($offeneLeads) . " – aktuell Ein-Mann-Betrieb. Prüfe, ob die Auslastung Verstärkung nötig macht.";
    } elseif ($agent === 'mert') {
        $c = oh_wissen_summary();
        $oc = function_exists('oh_outcome_summary') ? oh_outcome_summary() : '';
        if ($oc !== '') $c .= "\n" . $oc;
    }
    // Postfach: ungelesene Nachrichten von Kollegen direkt vorlegen
    $ib = function_exists('oh_agent_inbox') ? oh_agent_inbox($agent, true) : [];
    if ($ib) {
        $c .= "\n\nDEIN POSTFACH (neue Nachrichten von Kollegen – geh im Gespräch darauf ein):";
        foreach (array_slice($ib, -5) as $m) $c .= "\n- von " . ($m['von'] ?? '?') . " (" . date('d.m. H:i', $m['ts'] ?? time()) . "): " . ($m['text'] ?? '');
    }
    // Offene Aufträge an diesen Agenten
    $myTasks = function_exists('oh_tasks') ? oh_tasks($agent, 'offen') : [];
    if ($myTasks) {
        $c .= "\n\nDEINE OFFENEN AUFTRÄGE (von Kollegen/Chef – erledigen oder Stand melden):";
        foreach (array_slice($myTasks, -5) as $tk) $c .= "\n- [{$tk['id']}] von {$tk['von']}: {$tk['text']}";
    }
    // Mission + persönliches Gedächtnis jedem Agenten-Kontext voranstellen
    $mem = oh_agent_mem_summary($agent);
    return oh_mission() . ($mem !== '' ? $mem . "\n" : '') . $c;
}
