<?php
/**
 * OH – Firmen-Anschreiben-Vorlagen (von Kaan automatisch genutzt).
 * Professionelle Geschaeftsbriefe mit Platzhaltern. Editierbar in daten/vorlagen.json,
 * sonst greifen die Standard-Vorlagen unten.
 *
 * Nutzung:  $m = oh_vorlage('angebot', ['name'=>'...', 'leistung'=>'...', ...]);
 *           -> ['betreff'=>..., 'text'=>...]
 *
 * Typen: angebot · nachfass · bestaetigung · firmen_akquise
 */

function oh_vorlagen_standard(): array {
    $gruss = "Mit freundlichen Grüßen\nOH Haustechnik\nElektroinstallation & Netzwerkverkabelung · Raum Nürnberg\nTel. 0175 7481006 · oh.haustechnik@gmail.com";
    return [
        'angebot' => [
            'betreff' => 'Ihr Angebot von OH Haustechnik – {leistung}',
            'text' =>
"Sehr geehrte/r {anrede} {name},\n\n".
"vielen Dank für Ihre Anfrage zu {leistung}. Gerne unterbreiten wir Ihnen unser Angebot.\n\n".
"Leistung: {leistung}\n".
"Objekt: {objekt}\n".
"Festpreis: {preis}\n\n".
"Im Preis enthalten sind Material, Anfahrt und fachgerechte Ausführung durch unseren Meisterbetrieb. ".
"Gerne stimmen wir einen Termin mit Ihnen ab – wir sind flexibel und arbeiten sauber und termintreu.\n\n".
"Bei Fragen erreichen Sie uns jederzeit unter 0175 7481006.\n\n{gruss}",
        ],
        'nachfass' => [
            'betreff' => 'Kurze Nachfrage zu Ihrem Angebot – OH Haustechnik',
            'text' =>
"Sehr geehrte/r {anrede} {name},\n\n".
"vor einigen Tagen haben wir Ihnen unser Angebot zu {leistung} zugesendet. ".
"Wir wollten kurz nachfragen, ob noch Fragen offen sind oder wir Sie bei der Entscheidung unterstützen können.\n\n".
"Gerne vereinbaren wir auch einen unverbindlichen Termin vor Ort. Melden Sie sich einfach – telefonisch unter 0175 7481006 oder per Antwort auf diese E-Mail.\n\n{gruss}",
        ],
        'bestaetigung' => [
            'betreff' => 'Auftragsbestätigung – OH Haustechnik',
            'text' =>
"Sehr geehrte/r {anrede} {name},\n\n".
"vielen Dank für Ihren Auftrag. Hiermit bestätigen wir die Beauftragung folgender Leistung:\n\n".
"Leistung: {leistung}\n".
"Objekt: {objekt}\n".
"Vereinbarter Preis: {preis}\n".
"Termin: {termin}\n\n".
"Wir freuen uns auf die Zusammenarbeit und führen die Arbeiten sauber und termintreu aus. ".
"Sollte sich etwas ändern, melden wir uns rechtzeitig bei Ihnen.\n\n{gruss}",
        ],
        // B2B-Akquise – LEGAL: nur als Entwurf/Freigabe, Versand ueber zulaessigen Kanal (Tel./Brief)
        'firmen_akquise' => [
            'betreff' => 'Zuverlässiger Elektro-Partner für {firma} im Raum Nürnberg',
            'text' =>
"Sehr geehrte Damen und Herren,\n\n".
"als Elektro-Meisterbetrieb aus dem Raum Nürnberg unterstützen wir Unternehmen wie {firma} ".
"bei Elektroinstallation, Sanierung, Zähleranlagen und Netzwerkverkabelung – zuverlässig, termintreu und zu fairen Festpreisen.\n\n".
"Gerade für {branche} sind ein fester, erreichbarer Ansprechpartner und schnelle Reaktionszeiten entscheidend. ".
"Genau das bieten wir: kurze Wege, ein eingespieltes Team und saubere Ausführung.\n\n".
"Wenn Sie einen verlässlichen Elektro-Partner suchen, freue ich mich über ein kurzes Gespräch. ".
"Sie erreichen mich unter 0175 7481006.\n\n{gruss}",
        ],
    ];
}

function oh_vorlage(string $typ, array $d = []): array {
    // Editierte Vorlagen aus daten/vorlagen.json bevorzugen
    $custom = function_exists('oh_read') ? oh_read('vorlagen', []) : [];
    $alle = array_merge(oh_vorlagen_standard(), is_array($custom) ? $custom : []);
    $v = $alle[$typ] ?? oh_vorlagen_standard()['angebot'];

    $gruss = "Mit freundlichen Grüßen\nOH Haustechnik\nElektroinstallation & Netzwerkverkabelung · Raum Nürnberg\nTel. 0175 7481006 · oh.haustechnik@gmail.com";
    // Anrede automatisch aus Geschlecht/Name ableiten, falls nicht gesetzt
    if (empty($d['anrede'])) {
        $d['anrede'] = 'Herr/Frau';
    }
    $d += ['name'=>'', 'leistung'=>'', 'objekt'=>'', 'preis'=>'auf Anfrage', 'termin'=>'nach Absprache', 'firma'=>'Ihr Unternehmen', 'branche'=>'Ihre Branche', 'gruss'=>$gruss];

    $ersetze = function($s) use ($d) {
        foreach ($d as $k => $val) { $s = str_replace('{' . $k . '}', (string)$val, $s); }
        return preg_replace('/\{[a-z_]+\}/', '', $s); // unbenutzte Platzhalter entfernen
    };
    return ['betreff' => $ersetze($v['betreff']), 'text' => $ersetze($v['text'])];
}
