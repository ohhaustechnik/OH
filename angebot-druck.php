<?php
/**
 * Druckbare Profi-Angebotsansicht (Festpreis-Angebot) für einen Lead.
 * Nur eingeloggt (Büro-Session) aufrufbar: angebot-druck.php?id=LEADID
 * "Als PDF speichern" über den Drucken-Dialog des Browsers.
 */
session_start();
if (empty($_SESSION['eingeloggt'])) { http_response_code(403); exit('Kein Zugriff. Bitte im Büro anmelden.'); }
require_once __DIR__ . '/includes/buero-lib.php';

$id = preg_replace('/[^A-Za-z0-9]/', '', $_GET['id'] ?? '');
$lead = function_exists('oh_get_lead') ? oh_get_lead($id) : null;
if (!$lead) { http_response_code(404); exit('Anfrage nicht gefunden.'); }

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$betrag   = (float)($lead['angebot_betrag'] ?? 0);
$betragFmt= number_format($betrag, 2, ',', '.') . ' €';
$text     = trim((string)($lead['angebot_text'] ?? ''));
$leistung = $lead['kategorie'] ?: 'Elektroarbeiten';
$kunde    = $lead['name'] ?: 'Kunde';
$ort      = trim(($lead['plz'] ?? '') . ' ' . ($lead['ort'] ?? ''));
$nr       = 'AG-' . date('Ymd') . '-' . substr($id, -4);
$datum    = date('d.m.Y');
$gueltig  = date('d.m.Y', time() + 30 * 86400);
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Angebot <?= $h($nr) ?> · OH Haustechnik</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#1a2230;background:#eef1f6;padding:24px;line-height:1.55}
.sheet{max-width:780px;margin:0 auto;background:#fff;border-radius:10px;box-shadow:0 6px 30px rgba(0,0,0,.12);padding:46px 50px}
.top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #2a5fc7;padding-bottom:18px;margin-bottom:26px}
.brand{font-size:26px;font-weight:800;letter-spacing:3px;color:#15202f}
.brand small{display:block;font-size:10px;letter-spacing:2px;color:#2a5fc7;font-weight:700;margin-top:3px}
.firm{font-size:12px;color:#5b6b80;text-align:right;line-height:1.7}
.meta{display:flex;justify-content:space-between;margin-bottom:26px;font-size:13px}
.meta .to b{display:block;font-size:11px;color:#8a97a8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
.meta .info{text-align:right;color:#42536b}
h1{font-size:22px;color:#15202f;margin-bottom:6px}
.sub{color:#5b6b80;font-size:13px;margin-bottom:22px}
table{width:100%;border-collapse:collapse;margin-bottom:8px}
th{background:#f4f7fc;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#5b6b80;padding:11px 12px}
td{padding:13px 12px;border-bottom:1px solid #eef1f6;font-size:14px;vertical-align:top}
td.r,th.r{text-align:right;white-space:nowrap}
.sum{display:flex;justify-content:flex-end;margin-top:10px}
.sum .box{min-width:260px}
.sum .row{display:flex;justify-content:space-between;padding:7px 12px;font-size:14px}
.sum .total{background:#2a5fc7;color:#fff;font-weight:800;font-size:17px;border-radius:8px;padding:13px 14px;margin-top:6px}
.note{font-size:12px;color:#5b6b80;margin-top:24px;border-top:1px solid #eef1f6;padding-top:16px}
.foot{margin-top:30px;font-size:11px;color:#8a97a8;text-align:center}
.bar{max-width:780px;margin:0 auto 16px;display:flex;gap:10px;justify-content:flex-end}
.bar button,.bar a{font:inherit;font-weight:700;font-size:14px;border:none;border-radius:9px;padding:11px 20px;cursor:pointer;text-decoration:none}
.bar .print{background:#2a5fc7;color:#fff}
.bar .back{background:#dde3ec;color:#42536b}
@media print{body{background:#fff;padding:0}.sheet{box-shadow:none;border-radius:0;max-width:100%;padding:0}.bar{display:none}}
</style>
</head>
<body>
<div class="bar">
  <a class="back" href="javascript:window.close()">Schließen</a>
  <button class="print" onclick="window.print()">🖨️ Drucken / als PDF speichern</button>
</div>
<div class="sheet">
  <div class="top">
    <div>
      <div class="brand">OH<small>HAUSTECHNIK</small></div>
    </div>
    <div class="firm">
      OH Haustechnik · Elektrotechnik<br>
      Raum Nürnberg · Fürth · Erlangen<br>
      Tel. 0175 7481006<br>
      oh.haustechnik@gmail.com · oh-haustechnik.de
    </div>
  </div>

  <div class="meta">
    <div class="to">
      <b>Angebot an</b>
      <?= $h($kunde) ?><br>
      <?= $ort ? $h($ort) . '<br>' : '' ?>
      <?= $lead['telefon'] ? $h($lead['telefon']) . '<br>' : '' ?>
      <?= $lead['email'] ? $h($lead['email']) : '' ?>
    </div>
    <div class="info">
      <b>Angebot</b> <?= $h($nr) ?><br>
      Datum: <?= $h($datum) ?><br>
      Gültig bis: <?= $h($gueltig) ?>
    </div>
  </div>

  <h1>Festpreis-Angebot</h1>
  <div class="sub">Vielen Dank für Ihre Anfrage. Gerne unterbreiten wir Ihnen folgendes unverbindliches Festpreis-Angebot:</div>

  <table>
    <tr><th>Leistung</th><th class="r">Festpreis</th></tr>
    <?php $pos = $lead['angebot_positionen'] ?? []; if (is_array($pos) && $pos): ?>
      <?php foreach ($pos as $p): $z = (float)($p['menge'] ?? 1) * (float)($p['einzel'] ?? 0); ?>
      <tr>
        <td><b><?= $h($p['pos'] ?? '') ?></b><?php if ((float)($p['menge'] ?? 1) != 1): ?><br><span style="color:#5b6b80;font-size:12px"><?= $h(rtrim(rtrim(number_format((float)($p['menge'] ?? 1), 2, ',', '.'), '0'), ',')) ?> × <?= $h(number_format((float)($p['einzel'] ?? 0), 2, ',', '.')) ?> €</span><?php endif; ?></td>
        <td class="r"><?= $h(number_format($z, 2, ',', '.')) ?> €</td>
      </tr>
      <?php endforeach; ?>
      <?php if ($text): ?><tr><td colspan="2" style="color:#5b6b80;font-size:13px"><?= nl2br($h($text)) ?></td></tr><?php endif; ?>
    <?php else: ?>
      <tr>
        <td><b><?= $h($leistung) ?></b><?= $text ? '<br><span style="color:#5b6b80;font-size:13px">' . nl2br($h($text)) . '</span>' : '' ?></td>
        <td class="r"><?= $h($betragFmt) ?></td>
      </tr>
    <?php endif; ?>
  </table>

  <div class="sum"><div class="box">
    <div class="row"><span>Zwischensumme</span><span><?= $h($betragFmt) ?></span></div>
    <div class="row"><span>USt (Kleinunternehmer §19)</span><span>0,00 €</span></div>
    <div class="total" style="display:flex;justify-content:space-between"><span>Gesamt</span><span><?= $h($betragFmt) ?></span></div>
  </div></div>

  <div class="note">
    Gemäß § 19 UStG wird keine Umsatzsteuer berechnet (Kleinunternehmer). Der genannte Festpreis ist verbindlich für den beschriebenen Leistungsumfang; Sonderleistungen werden vorab abgestimmt. Zahlbar nach Abnahme ohne Abzug. Wir freuen uns auf Ihren Auftrag!
  </div>
  <div class="foot">OH Haustechnik · Elektrotechnik · Raum Nürnberg — Dieses Angebot wurde digital erstellt und ist auch ohne Unterschrift gültig.</div>
</div>
</body>
</html>
