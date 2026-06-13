<?php
/**
 * GHOST – Der Kontroll- & Qualitaets-Agent ueber dem ganzen OH-System.
 *
 * Idee (aus dem TikTok-Video "Fable 5 als Chef, nicht als Kaffeekocher"):
 *   Das teuerste, beste Modell (Fable 5) macht NICHT die Kleinarbeit, sondern
 *   ist der CHEF/RICHTER. Guenstige Helfer (Haiku 4.5) sammeln die Fakten und
 *   machen die einfachen Einzelpruefungen. Fable 5 bewertet nur das Ergebnis
 *   kritisch (Anti-Schoenrede) und schreibt das Gesamturteil.
 *
 * Lauf: 1x taeglich (Cron 05:30, vor dem Morgen-Bericht). Genau ein Durchlauf.
 * Ausfallsicherung: Stoppt Fable 5 per Sicherheitsfilter (stop_reason=refusal),
 *   wechselt Ghost FUER DIESEN LAUF automatisch auf Opus 4.8 — Pruefung faellt nie aus.
 *
 * Modell-Hoheit: Ghost bewertet, welches Modul welches Modell braucht, und legt
 *   Vorschlaege in den Freigaben-Bereich — er WECHSELT NIE selbst (fragt den Chef).
 *
 * Test/Notlauf:  https://oh-haustechnik.de/ghost.php?key=oh-cron&force=1
 */

require_once __DIR__ . '/includes/buero-lib.php';

const GHOST_CHEF       = 'fable';   // Standard-Richter
const GHOST_MODEL_FAB  = 'claude-fable-5';
const GHOST_MODEL_OPUS = 'claude-opus-4-8';
const GHOST_MODEL_HELF = 'claude-haiku-4-5';

/**
 * Roher Claude-Aufruf mit Modellwahl. Erkennt den Fable-5-'refusal'-Stopgrund.
 * Rueckgabe: ['text'=>string, 'refused'=>bool, 'error'=>?string, 'model'=>string,
 *             'in'=>int, 'out'=>int]
 */
function ghost_call(string $model, string $system, string $user, int $maxTokens = 1200): array {
    $key = oh_config()['anthropic_key'] ?? '';
    if (!$key) return ['text'=>'', 'refused'=>false, 'error'=>'kein API-Key', 'model'=>$model, 'in'=>0, 'out'=>0];

    $body = [
        'model' => $model,
        'max_tokens' => $maxTokens,
        'system' => $system,
        'messages' => [['role' => 'user', 'content' => $user]],
    ];
    // Fable 5 / Opus 4.8: adaptives Denken, KEIN temperature/budget_tokens.
    // (thinking-Param weglassen ist erlaubt; auf Fable 5 ist Denken ohnehin immer an.)

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 240, // Fable 5 kann lange denken
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01',
        ],
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if (!$resp) return ['text'=>'', 'refused'=>false, 'error'=>($err ?: 'keine Antwort'), 'model'=>$model, 'in'=>0, 'out'=>0];

    $d = json_decode($resp, true);
    if (isset($d['error'])) {
        $emsg = $d['error']['message'] ?? 'API-Fehler';
        if (function_exists('oh_alert_guthaben') &&
            (stripos($emsg,'credit')!==false || stripos($emsg,'billing')!==false || stripos($emsg,'insufficient')!==false)) {
            oh_alert_guthaben($emsg);
        }
        return ['text'=>'', 'refused'=>false, 'error'=>$emsg, 'model'=>$model, 'in'=>0, 'out'=>0];
    }
    $refused = (($d['stop_reason'] ?? '') === 'refusal');
    $out = '';
    foreach (($d['content'] ?? []) as $c) {
        if (($c['type'] ?? '') === 'text') $out .= $c['text'];
    }
    return [
        'text' => trim($out),
        'refused' => $refused,
        'error' => null,
        'model' => $model,
        'in'  => (int)($d['usage']['input_tokens'] ?? 0),
        'out' => (int)($d['usage']['output_tokens'] ?? 0),
    ];
}

/**
 * Der RICHTER: Fable 5, bei Sicherheits-Stopp automatisch Opus 4.8.
 * Die Tagespruefung darf nie ausfallen.
 */
function ghost_richter(string $system, string $user, int $maxTokens = 1400): array {
    $r = ghost_call(GHOST_MODEL_FAB, $system, $user, $maxTokens);
    if ($r['refused'] || $r['error']) {
        // Fallback fuer DIESEN Lauf auf Opus 4.8
        $grund = $r['refused'] ? 'Sicherheitsfilter (refusal)' : ('Fehler: ' . $r['error']);
        $r2 = ghost_call(GHOST_MODEL_OPUS, $system, $user, $maxTokens);
        $r2['fallback'] = $grund;
        return $r2;
    }
    $r['fallback'] = null;
    return $r;
}

/** Kosten-Schaetzung in EUR (1 USD = 1 EUR konservativ). */
function ghost_kosten(string $model, int $in, int $out): float {
    $preise = [
        'claude-fable-5'   => [10.0, 50.0],
        'claude-opus-4-8'  => [5.0, 25.0],
        'claude-haiku-4-5' => [1.0, 5.0],
    ];
    [$pi, $po] = $preise[$model] ?? [5.0, 25.0];
    return ($in * $pi + $out * $po) / 1_000_000;
}

/**
 * Sammelt die System-Fakten, die der Richter bewerten soll.
 * (Hier guenstig & lokal — keine teure KI noetig, reine Daten.)
 */
function ghost_fakten(): array {
    $f = [];
    // Sperre
    $lock = oh_read('lock', ['locked'=>false]);
    $f['sperre'] = !empty($lock['locked']) ? 'GESCHLOSSEN' : 'offen';
    // Leads & Reaktion
    $leads = oh_read('leads', []);
    $f['leads_gesamt'] = count($leads);
    $offen = array_filter($leads, fn($l)=>($l['status'] ?? '')==='neu');
    $f['leads_offen'] = count($offen);
    // Freigaben offen
    $f['freigaben_offen'] = function_exists('oh_freigaben') ? count(oh_freigaben('offen')) : 0;
    // Lexware / Umsatz
    $f['ziel'] = function_exists('oh_ziel_status') ? oh_ziel_status() : [];
    // Config-Sicherheit: Key im Klartext?
    $cfg = oh_config();
    $f['config_keys'] = array_keys($cfg);
    // Cron-Log frische?
    $logp = defined('OH_DATA_DIR') ? OH_DATA_DIR . '/cron.log' : __DIR__ . '/daten/cron.log';
    $f['cron_letzte'] = is_file($logp) ? date('Y-m-d H:i', filemtime($logp)) : 'fehlt';
    // Aktivitaet
    $akt = oh_read('aktivitaet', []);
    $f['aktivitaet_eintraege'] = count($akt);
    return $f;
}

/**
 * Haupt-Lauf: Helfer (guenstig) -> Richter (Fable 5) -> Bericht + Freigaben.
 */
function ghost_run(bool $force = false): array {
    // Genau 1x pro Tag (ausser force)
    $meta = oh_read('ghost_meta', ['letzter_lauf' => '']);
    $heute = date('Y-m-d');
    if (!$force && ($meta['letzter_lauf'] ?? '') === $heute) {
        return ['status' => 'schon_gelaufen', 'datum' => $heute];
    }

    $kosten = 0.0;
    $fakten = ghost_fakten();

    // --- Schritt 1: Guenstiger Helfer (Haiku) macht die technische Einzelpruefung ---
    $helfSys = "Du bist ein technischer Pruef-Helfer fuer ein PHP-Handwerker-Buerosystem. "
        . "Pruefe die uebergebenen System-Fakten nuechtern auf Auffaelligkeiten: Sicherheit, "
        . "Datenfrische, offene Freigaben, Lead-Reaktion, Kostenrisiken. Antworte in max 8 kurzen Stichpunkten, "
        . "nur Fakten und konkrete Auffaelligkeiten, keine Floskeln.";
    $helfUser = "System-Fakten (JSON):\n" . json_encode($fakten, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $h = ghost_call(GHOST_MODEL_HELF, $helfSys, $helfUser, 700);
    $kosten += ghost_kosten(GHOST_MODEL_HELF, $h['in'], $h['out']);
    $helferBefund = $h['text'] ?: '(Helfer ohne Befund)';

    // --- Schritt 2: Der RICHTER (Fable 5 / Fallback Opus 4.8) bewertet kritisch ---
    $regeln = oh_read('ghost_regeln', []);
    $regelTxt = $regeln ? implode("\n", array_map(fn($r)=>'- '.$r, array_slice($regeln,-15))) : '(noch keine Dauerregeln)';

    $richterSys = "Du bist GHOST, der oberste Kontroll- und Qualitaets-Agent des KI-Buerosystems "
        . "der Firma OH-Haustechnik (Elektro, Raum Nuernberg). Du sprichst den Chef immer mit "
        . "'grosser Adnan' an. Ziel der Firma: 1 Mio EUR Umsatz in 5 Monaten. "
        . "Deine Rolle ist die des CHEFS, nicht des Kaffeekochers: Du bewertest die Vorarbeit des "
        . "Helfers kritisch (Anti-Schoenrede: pruefe Annahmen, suche Gegenargumente, rede nichts schoen), "
        . "findest Schwachstellen, Sicherheitsluecken, Umsatz-Lecks und Benutzerfreundlichkeits-Probleme. "
        . "Halte dich KURZ und konkret. Format strikt:\n"
        . "URTEIL: <eine Zeile: Gesamtzustand des Systems heute>\n"
        . "BEFUNDE:\n- <max 5 konkrete Punkte, je mit Schweregrad [hoch/mittel/niedrig]>\n"
        . "FREIGABE: <eine Zeile: was sollte der grosse Adnan heute entscheiden/freigeben, oder 'nichts'>\n"
        . "REGEL: <eine neue Dauer-Pruefregel aus dem groessten Fund, oder 'keine'>";
    $richterUser = "Heutige Helfer-Pruefung:\n$helferBefund\n\n"
        . "Bekannte Dauer-Pruefregeln:\n$regelTxt\n\n"
        . "Roh-Fakten:\n" . json_encode($fakten, JSON_UNESCAPED_UNICODE);
    $j = ghost_richter($richterSys, $richterUser, 1200);
    $kosten += ghost_kosten($j['model'], $j['in'], $j['out']);
    $urteil = $j['text'] ?: '(kein Urteil erzeugt)';

    // --- Schritt 3: Auswerten, Regel lernen, Freigabe anlegen ---
    $neueRegel = '';
    if (preg_match('/REGEL:\s*(.+)/u', $urteil, $m)) {
        $rg = trim($m[1]);
        if ($rg && stripos($rg, 'keine') !== 0) $neueRegel = $rg;
    }
    if ($neueRegel) {
        $regeln[] = $neueRegel;
        oh_write('ghost_regeln', array_slice($regeln, -50));
    }
    $freigabeText = '';
    if (preg_match('/FREIGABE:\s*(.+)/u', $urteil, $m)) {
        $ft = trim($m[1]);
        if ($ft && stripos($ft, 'nichts') !== 0) $freigabeText = $ft;
    }
    if ($freigabeText && function_exists('oh_freigabe_add')) {
        oh_freigabe_add([
            'typ' => 'ghost',
            'titel' => 'Ghost-Empfehlung',
            'text' => $freigabeText,
            'agent' => 'Ghost',
        ]);
    }

    // --- Bericht speichern ---
    $bericht = [
        'datum' => $heute,
        'zeit' => date('Y-m-d H:i:s'),
        'urteil' => $urteil,
        'helfer' => $helferBefund,
        'richter_modell' => $j['model'],
        'fallback' => $j['fallback'] ?? null,
        'kosten_eur' => round($kosten, 4),
        'fakten' => $fakten,
        'neue_regel' => $neueRegel,
    ];
    oh_write('ghost_report', $bericht);

    // Event in den Bus (Aktivitaet + Postfach an Mert fuer den Morgen-Bericht)
    if (function_exists('oh_log_activity')) {
        $mdl = $j['model'] === GHOST_MODEL_FAB ? 'Fable 5' : 'Opus 4.8';
        oh_log_activity('ghost', "Tagespruefung fertig ($mdl)" . ($j['fallback'] ? " [Fallback: {$j['fallback']}]" : '') . '.');
    }
    if (function_exists('oh_agent_msg_send')) {
        $kurz = preg_match('/URTEIL:\s*(.+)/u', $urteil, $mm) ? trim($mm[1]) : 'Pruefung abgeschlossen.';
        oh_agent_msg_send('ghost', 'mert', "Ghost-Tagesurteil fuer den Morgen-Bericht: $kurz");
    }

    $meta['letzter_lauf'] = $heute;
    $meta['letzte_kosten'] = round($kosten, 4);
    oh_write('ghost_meta', $meta);

    return ['status' => 'ok', 'modell' => $j['model'], 'fallback' => $j['fallback'] ?? null, 'kosten' => round($kosten,4), 'urteil' => $urteil];
}

// ---------------------------------------------------------------------------
// HTTP-Einstieg: nur mit cron_key (Cron oder Notlauf).
// Wird ghost.php aus buero-cron.php eingebunden (OH_GHOST_INCLUDE gesetzt),
// laeuft dieser Block NICHT — dann ruft der Cron ghost_run() selbst auf.
// ---------------------------------------------------------------------------
if (!defined('OH_GHOST_INCLUDE') && php_sapi_name() !== 'cli') {
    $key = oh_config()['cron_key'] ?? 'oh-cron';
    if (($_GET['key'] ?? '') !== $key) { http_response_code(403); exit('Zugriff verweigert.'); }
    header('Content-Type: text/plain; charset=utf-8');
    $force = isset($_GET['force']) && $_GET['force'];
    $r = ghost_run($force);
    echo "GHOST-Lauf:\n" . json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}
