<?php
/**
 * Wochen-Auswertung: Anfragen pro 100 Besucher (je Quelle) + Anfrage->Auftrag-Quote.
 * Funktion oh_lead_auswertung() — von Cron (wöchentlich) genutzt, Ergebnis in daten/auswertung.json.
 * Direktaufruf (Bericht ansehen): auswertung.php?key=oh-cron
 */
if (!function_exists('oh_read')) require_once __DIR__ . '/includes/buero-lib.php';

function oh_lead_auswertung(int $tage = 7): array {
    $leads = oh_read('leads', []);
    $bes   = oh_read('besucher', []);
    $grenze = strtotime("-$tage days");

    // Besucher je Quelle (letzte $tage Tage)
    $besQuelle = []; $besGesamt = 0;
    foreach ($bes as $tag => $quellen) {
        if (strtotime($tag) < $grenze) continue;
        foreach ((array)$quellen as $q => $n) { $besQuelle[$q] = ($besQuelle[$q] ?? 0) + (int)$n; $besGesamt += (int)$n; }
    }

    // Leads je Quelle + Status (letzte $tage Tage)
    $leadQuelle = []; $leadGesamt = 0; $gewonnen = 0; $echte = 0;
    foreach ($leads as $l) {
        if ((int)($l['created'] ?? 0) < $grenze) continue;
        if (stripos(($l['name'] ?? '') . ($l['email'] ?? ''), 'test') !== false) continue; // Test-Leads raus
        $q = $l['source'] ?? ($l['quelle'] ?? 'website');
        $leadQuelle[$q] = ($leadQuelle[$q] ?? 0) + 1; $leadGesamt++; $echte++;
        if (in_array($l['status'] ?? '', ['gewonnen', 'abgeschlossen'])) $gewonnen++;
    }

    // Pro-Quelle: Anfragen pro 100 Besucher
    $proQuelle = [];
    foreach (array_unique(array_merge(array_keys($besQuelle), array_keys($leadQuelle))) as $q) {
        $b = $besQuelle[$q] ?? 0; $a = $leadQuelle[$q] ?? 0;
        $proQuelle[$q] = [
            'besucher' => $b, 'anfragen' => $a,
            'pro_100'  => $b > 0 ? round($a / $b * 100, 1) : null,
        ];
    }
    uasort($proQuelle, fn($x, $y) => ($y['anfragen'] <=> $x['anfragen']));

    return [
        'zeitraum_tage'   => $tage,
        'erstellt'        => date('Y-m-d H:i'),
        'besucher_gesamt' => $besGesamt,
        'anfragen_gesamt' => $leadGesamt,
        'anfragen_pro_100'=> $besGesamt > 0 ? round($leadGesamt / $besGesamt * 100, 1) : null,
        'gewonnen'        => $gewonnen,
        'anfrage_auftrag_quote' => $echte > 0 ? round($gewonnen / $echte * 100, 1) : null,
        'je_quelle'       => $proQuelle,
        'hinweis'         => $besGesamt === 0 ? 'Noch keine Besucher gezählt – Zähl-Pixel läuft ab jetzt auf den Landing-Pages.' : '',
    ];
}

// Direktaufruf
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'auswertung.php' && PHP_SAPI !== 'cli') {
    if (($_GET['key'] ?? '') !== (oh_config()['cron_key'] ?? 'oh-cron')) { http_response_code(403); exit('Zugriff verweigert.'); }
    $r = oh_lead_auswertung((int)($_GET['tage'] ?? 7));
    oh_write('auswertung', $r);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($r, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
