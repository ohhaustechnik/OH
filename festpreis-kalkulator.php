<?php
$pageTitle = "Festpreis-Kalkulator | OH Haustechnik";
require_once __DIR__ . '/includes/buero-lib.php';
$ohReviews = function_exists('oh_google_reviews') ? oh_google_reviews() : ['rating' => 5.0, 'count' => 21];
$ohRating  = number_format($ohReviews['rating'], 1, ',', '');
$ohCount   = (int)$ohReviews['count'];
include 'includes/header.php';

$sent = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = "oh.haustechnik@gmail.com";
    $subject = "Neue Anfrage - " . ($_POST["service"] ?? "Festpreis-Kalkulator") . " - " . ($_POST["name"] ?? "Kunde");

   $message =
"==============================\n" .
"NEUE FESTPREIS-ANFRAGE\n" .
"==============================\n\n" .

"KUNDE\n" .
"------------------------------\n" .
"Name: " . ($_POST["name"] ?? "") . "\n" .
"Telefon: " . ($_POST["phone"] ?? "") . "\n" .
"E-Mail: " . ($_POST["email"] ?? "") . "\n" .
"Adresse: " . ($_POST["address"] ?? "") . "\n\n" .

"AUFTRAG\n" .
"------------------------------\n" .
"Leistung: " . ($_POST["service"] ?? "") . "\n" .
"Details: " . ($_POST["details"] ?? "") . "\n" .
"Preis: " . ($_POST["price"] ?? "") . "\n\n" .

"TERMIN\n" .
"------------------------------\n" .
"Termin: " . ($_POST["appointment"] ?? "") . "\n\n" .

"==============================\n" .
"OH Haustechnik Website\n" .
"==============================\n";
    $headers = "From: website@oh-haustechnik.de\r\n";
    $headers .= "Reply-To: " . ($_POST["email"] ?? "website@oh-haustechnik.de") . "\r\n";

    mail($to, $subject, $message, $headers);

    // Anfrage als Lead ins Büro übernehmen (Dashboard, HOT-/Großauftrag-Erkennung)
    if (function_exists('oh_add_lead')) {
        oh_add_lead([
            'source'    => 'kalkulator',
            'name'      => $_POST['name'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'telefon'   => $_POST['phone'] ?? '',
            'kategorie' => $_POST['service'] ?? 'Festpreis-Kalkulator',
            'ort'       => $_POST['zipcode'] ?? '',
            'details'   => trim(($_POST['details'] ?? '') . ' | Preis: ' . ($_POST['price'] ?? '') . ' | Wunschtermin: ' . ($_POST['appointment'] ?? '') . ' | Adresse: ' . ($_POST['address'] ?? '')),
        ]);
    }

	$appointment = $_POST["appointment"] ?? "";

if ($appointment !== "") {
    $file = __DIR__ . "/termine.json";

    if (file_exists($file)) {
        $termine = json_decode(file_get_contents($file), true);

        if (is_array($termine)) {
            foreach ($termine as &$termin) {
                $datetime = strtotime($termin["datum"] . " " . $termin["uhrzeit"]);

                $tage = [
                    "Monday" => "Montag",
                    "Tuesday" => "Dienstag",
                    "Wednesday" => "Mittwoch",
                    "Thursday" => "Donnerstag",
                    "Friday" => "Freitag",
                    "Saturday" => "Samstag",
                    "Sunday" => "Sonntag"
                ];

                $tag = $tage[date("l", $datetime)] ?? date("l", $datetime);
                $uhrzeit = date("H:i", $datetime) . " Uhr";
                $terminText = $tag . " " . $uhrzeit;

                if ($terminText === $appointment) {
                    $termin["status"] = "gebucht";
                }
            }

            unset($termin);

            file_put_contents(
                $file,
                json_encode($termine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }
}
	
	$sent = true;
}
?>

<section class="premium-calc">
    <div class="premium-calc-bg"></div>

    <div class="container premium-calc-container">

        <?php if ($sent): ?>
            <div class="premium-success">
                ✅ Ihre Anfrage wurde gesendet. Wir melden uns schnellstmöglich zurück.
            </div>
        <?php endif; ?>

        <div class="premium-calc-hero">
            <div>
                <h1>Festpreis-<span>Kalkulator</span></h1>
                <p>Leistung auswählen, Preis berechnen und freien Termin sichern.</p>
            </div>

            <div class="premium-trust-card">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Transparente Preise</strong>
                    <small>Keine versteckten Kosten</small>
                </div>
            </div>
        </div>

        <style>
        /* ===== KALKULATOR – PREMIUM-VEREDELUNG (einheitliches Tiefblau) ===== */
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap');
        .premium-calc{font-family:'Manrope',-apple-system,BlinkMacSystemFont,sans-serif}
        .premium-calc h1,.premium-calc h2,.premium-calc h3,.premium-calc .wiz-h,
        .premium-calc .premium-price-card strong,.premium-calc .premium-inline-price strong,
        .premium-calc .wiz-recap-card strong,.premium-calc .wiz-st .wiz-n{font-family:'Sora',sans-serif;letter-spacing:-.01em}
        .premium-rep-bar{display:flex;flex-wrap:wrap;align-items:center;gap:8px;font-size:14px;color:#2e3b4e;margin:4px 0 14px}
        .premium-rep-bar .prep-stars{color:#f5b301;letter-spacing:2px}
        .premium-rep-bar b{color:#15202f}
        .premium-rep-bar .prep-sep{color:#c3ccd8}
        /* Vertrauensbildender Hinweis (ersetzt die alte "Testphase"-Box) */
        .premium-assurance{display:flex;gap:12px;align-items:flex-start;background:linear-gradient(135deg,#f2f7ff,#eaf1ff);border:1px solid #d7e4fb;border-radius:14px;padding:14px 16px;font-size:14px;color:#33425c;line-height:1.55;margin:0 0 20px}
        .premium-assurance i{color:#2563eb;font-size:1.2rem;margin-top:1px;flex-shrink:0}
        .premium-assurance b{color:#0f2547}
        .premium-service[data-type="grossprojekt"]{border-color:#2563eb}
        .premium-service[data-type="grossprojekt"] span{color:#1e3a8a;font-weight:800}
        .gp-grid{display:grid;gap:12px;margin-top:8px}
        .gp-grid label{font-size:13px;font-weight:700;color:#33425c}
        .gp-grid select,.gp-grid input{width:100%;padding:13px 14px;border:1.5px solid #dbe4f3;border-radius:12px;font-size:14px;font-family:inherit;color:#0f2547;transition:.15s}
        .gp-grid select:focus,.gp-grid input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
        /* ---- PREMIUM-WIZARD ---- */
        .wiz-progress{max-width:780px;margin:0 auto 30px}
        .wiz-track{height:7px;background:#e3ebf8;border-radius:7px;overflow:hidden;margin-bottom:14px}
        .wiz-track>i{display:block;height:100%;width:0;background:linear-gradient(90deg,#2563eb,#1e3a8a);border-radius:7px;transition:width .5s cubic-bezier(.4,0,.2,1)}
        .wiz-steps{display:flex;justify-content:space-between;gap:8px}
        .wiz-st{flex:1;background:none;border:none;cursor:pointer;font:inherit;display:flex;align-items:center;justify-content:center;gap:9px;font-size:14px;font-weight:700;color:#9aa7b8;padding:4px}
        .wiz-st .wiz-n{width:30px;height:30px;border-radius:50%;background:#e8eefb;color:#7b8aa0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;transition:.3s;flex-shrink:0}
        .wiz-st.active{color:#0f2547}
        .wiz-st.active .wiz-n{background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.42);transform:scale(1.08)}
        .wiz-st.done .wiz-n{background:#1aa86a;color:#fff}
        .wiz-panel{display:none}
        .wiz-panel.active{display:block;animation:wizIn .45s cubic-bezier(.2,.7,.3,1) both}
        @keyframes wizIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
        .wiz-h{font-size:clamp(22px,3vw,30px);font-weight:800;color:#0f2547;margin:0 0 8px;text-align:center}
        .wiz-sub{text-align:center;color:#5b6b80;margin:0 auto 26px;max-width:50ch;font-size:15.5px;line-height:1.6}
        .wiz-grid{display:grid;grid-template-columns:1fr 330px;gap:24px;align-items:start}
        .wiz-side{position:sticky;top:90px;display:flex;flex-direction:column;gap:14px}
        .wiz-recap{margin-bottom:20px}
        .wiz-recap-card{display:flex;justify-content:space-between;align-items:center;gap:14px;background:linear-gradient(135deg,#f2f7ff,#e9f1ff);border:1px solid #d6e2fb;border-radius:16px;padding:18px 20px}
        .wiz-recap-card small{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#7b8aa0;margin-bottom:3px}
        .wiz-recap-card strong{display:block;font-size:16px;color:#0f2547}
        .wiz-recap-card span{font-size:13px;color:#5b6b80}
        .wiz-recap-price{text-align:right;white-space:nowrap}
        .wiz-recap-price strong{color:#1e3a8a;font-size:22px}
        .wiz-nav{display:flex;justify-content:space-between;gap:12px;margin-top:28px;max-width:780px;margin-left:auto;margin-right:auto}
        .wiz-back,.wiz-next{border:none;cursor:pointer;font:inherit;font-weight:700;font-size:15px;border-radius:14px;padding:16px 30px;display:inline-flex;align-items:center;gap:10px;transition:transform .15s,box-shadow .15s,background .15s}
        .wiz-back{background:#eef2f9;color:#42536b}
        .wiz-back:hover{background:#e2e9f4}
        .wiz-next{background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;box-shadow:0 12px 28px rgba(37,99,235,.34);margin-left:auto}
        .wiz-next:hover{transform:translateY(-2px);box-shadow:0 18px 38px rgba(37,99,235,.44)}
        /* ---- Premium-Veredelung der Kernelemente ---- */
        .premium-service{transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
        .premium-service:hover{transform:translateY(-4px);box-shadow:0 16px 34px rgba(19,36,63,.13)}
        .premium-service.active{background:linear-gradient(135deg,#f2f7ff,#e9f1ff);border-color:#2563eb;box-shadow:0 14px 32px rgba(37,99,235,.18)}
        .premium-price-card{background:linear-gradient(135deg,#2563eb 0%,#1e3a8a 55%,#13294f 100%)}
        .premium-price-card::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 80% at 88% 0%,rgba(255,255,255,.18),transparent 55%);pointer-events:none}
        .premium-submit{background:linear-gradient(135deg,#2563eb,#1e3a8a)}
        @media(max-width:820px){
          .wiz-grid{grid-template-columns:1fr}
          .wiz-side{position:static}
          .wiz-st{flex-direction:column;gap:6px;font-size:11px;text-align:center}
          .wiz-st .wiz-n{width:34px;height:34px}
          .wiz-back,.wiz-next{padding:15px 20px;font-size:14px}
        }
        </style>

        <div class="premium-rep-bar">
            <span class="prep-stars">★★★★★</span> <b><?= $ohRating ?></b> aus <?= $ohCount ?> Google-Bewertungen
            <span class="prep-sep">·</span> <i class="fas fa-bolt" style="color:#2563eb"></i> Schnelle Rückmeldung
            <span class="prep-sep">·</span> <i class="fas fa-check-circle" style="color:#2563eb"></i> Saubere Ausführung
        </div>
        <div class="premium-assurance">
            <i class="fas fa-user-check"></i>
            <span><b>Persönlich geprüft.</b> Jedes Angebot wird von uns persönlich kontrolliert. Ihr Festpreis gilt verbindlich nach kurzer Bestätigung – transparent und ohne versteckte Kosten.</span>
        </div>

        <div class="wiz-progress">
            <div class="wiz-track"><i id="wizBar"></i></div>
            <div class="wiz-steps">
                <button type="button" class="wiz-st active" data-s="1" onclick="wizGo(1)"><span class="wiz-n">1</span> Leistung</button>
                <button type="button" class="wiz-st" data-s="2" onclick="wizGo(2)"><span class="wiz-n">2</span> Konfigurieren</button>
                <button type="button" class="wiz-st" data-s="3" onclick="wizGo(3)"><span class="wiz-n">3</span> Kontakt &amp; Termin</button>
            </div>
        </div>

        <div class="wiz-panel active" id="wiz1">
        <h2 class="wiz-h">Was können wir für Sie tun?</h2>
        <p class="wiz-sub">Wählen Sie Ihre Leistung – Sie bekommen sofort einen transparenten Festpreis. Bei großen Projekten erstellen wir Ihnen ein persönliches Angebot.</p>
        <div class="premium-services">
            <button class="premium-service" type="button" data-type="grossprojekt">
                <i class="fas fa-house-chimney"></i>
                <strong>Großes Projekt / Sanierung</strong>
                <span>persönliches Angebot</span>
            </button>
            <button class="premium-service active" type="button" data-type="lampe">
                <i class="fas fa-lightbulb"></i>
                <strong>Lampenmontage</strong>
                <span>ab 89 €</span>
            </button>

            <button class="premium-service" type="button" data-type="steckdose">
                <i class="fas fa-plug"></i>
                <strong>Steckdose austauschen</strong>
                <span>ab 69 €</span>
            </button>

            <button class="premium-service" type="button" data-type="fi">
                <i class="fas fa-shield-alt"></i>
                <strong>FI-Schalter nachrüsten</strong>
                <span>ab 119 €</span>
            </button>

            <button class="premium-service" type="button" data-type="fehler">
                <i class="fas fa-search"></i>
                <strong>Fehlersuche</strong>
                <span>ab 70 €/h</span>
            </button>
        </div>
        </div><!-- /wiz1 -->

        <div class="wiz-panel" id="wiz2">
          <div class="wiz-grid">
            <div class="premium-left" id="config-area"></div>
            <aside class="wiz-side">
                <div class="premium-price-card">
                    <small>Ihr Preis</small>
                    <strong id="side-price">89 €</strong>
                    <span id="side-price-sub">inkl. Montage, Anfahrt in Ihrer Zone.</span>
                    <i class="fas fa-lightbulb"></i>
                </div>
                <button class="premium-hint-btn" type="button" onclick="openHint(defaultHint)">
                    <i class="fas fa-info-circle"></i>
                    Wichtige Hinweise anzeigen
                </button>
            </aside>
          </div>
          <div class="wiz-nav">
            <button type="button" class="wiz-back" onclick="wizGo(1)"><i class="fas fa-arrow-left"></i> Zurück</button>
            <button type="button" class="wiz-next" onclick="wizGo(3)">Weiter <i class="fas fa-arrow-right"></i></button>
          </div>
        </div><!-- /wiz2 -->

        <div class="wiz-panel" id="wiz3">
          <div class="wiz-recap" id="wizRecap"></div>
            <div class="premium-appointments">
    <h3><i class="far fa-calendar-alt"></i> Freien Termin auswählen</h3>

    <?php
    $termineFile = __DIR__ . "/termine.json";
    $termine = [];

    if (file_exists($termineFile)) {
        $termine = json_decode(file_get_contents($termineFile), true);
        if (!is_array($termine)) {
            $termine = [];
        }
    }

    $now = time();
    $shown = 0;

    foreach ($termine as $termin) {
        $datetime = strtotime($termin["datum"] . " " . $termin["uhrzeit"]);

        if ($termin["status"] === "frei" && $datetime > $now) {
            $shown++;

            $tage = [
                "Monday" => "Montag",
                "Tuesday" => "Dienstag",
                "Wednesday" => "Mittwoch",
                "Thursday" => "Donnerstag",
                "Friday" => "Freitag",
                "Saturday" => "Samstag",
                "Sunday" => "Sonntag"
            ];

            $tag = $tage[date("l", $datetime)] ?? date("l", $datetime);
            $uhrzeit = date("H:i", $datetime) . " Uhr";
            $terminText = $tag . " " . $uhrzeit;
            ?>

            <button type="button"
                    class="premium-date <?= $shown === 1 ? 'active' : '' ?>"
                    data-appointment="<?= htmlspecialchars($terminText) ?>">
                <i class="far fa-calendar-alt"></i>
                <span><?= htmlspecialchars($tag) ?><br><small><?= htmlspecialchars($uhrzeit) ?></small></span>
            </button>

            <?php
        }
    }

    if ($shown === 0) {
        echo '<p class="premium-no-date">Aktuell sind keine Online-Termine verfügbar. Bitte Anfrage senden, wir melden uns zeitnah.</p>';
    }
    ?>
</div>

                <form class="premium-form" method="post">
                    <h3><i class="far fa-user"></i> Ihre Kontaktdaten</h3>

                    <input type="hidden" name="service" id="form-service" value="Lampenmontage">
                    <input type="hidden" name="details" id="form-details" value="1 Standardlampe">
                    <input type="hidden" name="price" id="form-price" value="89 €">
                    <input type="hidden" name="appointment" id="form-appointment" value="">

                    <input type="text" name="name" placeholder="Vor- und Nachname*" required>

<input type="tel" name="phone" id="phone-field" placeholder="Telefonnummer">

<input type="email" name="email" id="email-field" placeholder="E-Mail-Adresse">

<input type="text" name="address" placeholder="Straße + Hausnummer*" required>

<input type="text" name="zipcode" placeholder="PLZ + Ort*" required>

                    <p class="premium-privacy">
                        <i class="fas fa-lock"></i>
                        Ihre Daten bleiben vertraulich und werden nicht an Dritte weitergegeben.
                    </p>

                    <button class="premium-submit" type="submit">
                        <i class="fas fa-paper-plane"></i>
                        Unverbindlich anfragen
                    </button>

                    <small class="premium-small-note">
                        <i class="fas fa-shield-alt"></i>
                        Unverbindlich & kostenlos
                    </small>
                </form>
          <div class="wiz-nav">
            <button type="button" class="wiz-back" onclick="wizGo(2)"><i class="fas fa-arrow-left"></i> Zurück</button>
          </div>
        </div><!-- /wiz3 -->

        <div class="premium-benefits">
            <div><i class="fas fa-tags"></i><strong>Festpreise</strong><span>Transparente Preise von Anfang an</span></div>
            <div><i class="fas fa-shield-alt"></i><strong>Sicherheit</strong><span>Fachgerechte Ausführung</span></div>
            <div><i class="far fa-clock"></i><strong>Termingarantie</strong><span>Pünktlich & zuverlässig</span></div>
            <div><i class="far fa-star"></i><strong>Zufriedenheit</strong><span>Kundenorientierter Service</span></div>
        </div>
    </div>
</section>

<div id="hint-modal" class="premium-hint-modal">
    <div class="premium-hint-box">
        <button type="button" onclick="closeHint()" class="premium-hint-close">×</button>
        <h3>Wichtige Hinweise</h3>
        <p id="hint-text"></p>
    </div>
</div>

<script>
const configArea = document.getElementById("config-area");
const sidePrice = document.getElementById("side-price");
const sidePriceSub = document.getElementById("side-price-sub");

let defaultHint = "Standardmontagen gelten für normale Bedingungen. Sonderfälle wie hohe Decken, Außenbereich, schwere Designlampen oder nicht geeignete Elektroinstallationen werden individuell kalkuliert.";

function updateForm(service, details, price, subtitle){
    document.getElementById("form-service").value = service;
    document.getElementById("form-details").value = details;
    document.getElementById("form-price").value = price;
    sidePrice.textContent = price;
    sidePriceSub.textContent = subtitle || "inkl. Montage, Anfahrt in Ihrer Zone.";
}

function serviceClickEvents(){
    document.querySelectorAll(".premium-service").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".premium-service").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            if(btn.dataset.type === "lampe") showLampe();
            if(btn.dataset.type === "steckdose") showSteckdose();
            if(btn.dataset.type === "fi") showFI();
            if(btn.dataset.type === "fehler") showFehler();
            if(btn.dataset.type === "grossprojekt") showGrossprojekt();
            wizGo(2);
        });
    });
}

/* ---- PREMIUM-WIZARD: Schritt-Steuerung ---- */
let wizStep=1;
function wizGo(n){
    n=Math.max(1,Math.min(3,n));
    if(n>=2 && !document.querySelector(".premium-service.active")) return; // erst Leistung wählen
    wizStep=n;
    document.querySelectorAll(".wiz-panel").forEach(p=>p.classList.remove("active"));
    const panel=document.getElementById("wiz"+n); if(panel)panel.classList.add("active");
    document.querySelectorAll(".wiz-st").forEach(s=>{const sv=+s.dataset.s;s.classList.toggle("active",sv===n);s.classList.toggle("done",sv<n);});
    const bar=document.getElementById("wizBar"); if(bar)bar.style.width=((n-1)/2*100)+"%";
    if(n===3)wizRecap();
    const top=document.querySelector(".wiz-progress"); if(top)top.scrollIntoView({behavior:"smooth",block:"start"});
}
function wizRecap(){
    const g=id=>document.getElementById(id);
    const s=(g("form-service")||{}).value||"",d=(g("form-details")||{}).value||"",p=(g("form-price")||{}).value||"";
    const r=document.getElementById("wizRecap"); if(!r)return;
    r.innerHTML='<div class="wiz-recap-card"><div><small>Ihre Auswahl</small><strong>'+s+'</strong><span>'+d+'</span></div><div class="wiz-recap-price"><small>Preis</small><strong>'+p+'</strong></div></div>';
}

/* Großprojekt-/Sanierungs-Pfad: kein Fixpreis, sondern Qualifizierung ->
   persönliches Festpreis-Angebot. Fängt Großaufträge ein statt sie zu verlieren. */
function showGrossprojekt(){
    defaultHint = "Bei größeren Projekten erstellen wir ein individuelles Festpreis-Angebot. Je genauer Ihre Angaben, desto schneller und passender unser Angebot.";
    configArea.innerHTML = `
        <div class="premium-config-head">
            <i class="fas fa-house-chimney"></i>
            <div>
                <h2>Großes Projekt / Sanierung</h2>
                <p>Kurz beschreiben – wir machen ein persönliches Festpreis-Angebot</p>
            </div>
        </div>
        <div class="premium-step gp-grid">
            <label>Um welches Projekt geht es?</label>
            <select id="gp-art">
                <option>Komplettsanierung Wohnung</option>
                <option>Komplettsanierung Haus</option>
                <option>Wohnungsmodernisierung</option>
                <option>Zählerschrank / Unterverteilung erneuern</option>
                <option>Smart Home / KNX</option>
                <option>Netzwerkverkabelung</option>
                <option>Etwas anderes (Großprojekt)</option>
            </select>
            <label>Größe (Wohnfläche oder Zimmerzahl)</label>
            <input type="text" id="gp-groesse" placeholder="z. B. 95 m² / 4 Zimmer">
            <label>Wann soll es losgehen?</label>
            <select id="gp-zeit">
                <option>So schnell wie möglich</option>
                <option>In den nächsten 4 Wochen</option>
                <option>In 1–3 Monaten</option>
                <option>Zeitpunkt noch offen</option>
            </select>
            <label>Kurz beschreiben (optional)</label>
            <input type="text" id="gp-text" placeholder="z. B. Altbau, alle Leitungen neu, Smart-Home-ready">
        </div>
        <p class="premium-info-line"><i class="fas fa-info-circle"></i> Sie bekommen ein persönliches Festpreis-Angebot – kein Sofortpreis, weil jedes Großprojekt anders ist.</p>
    `;
    function gpUpdate(){
        const art=document.getElementById("gp-art").value;
        const gr=(document.getElementById("gp-groesse").value||"").trim();
        const zeit=document.getElementById("gp-zeit").value;
        const txt=(document.getElementById("gp-text").value||"").trim();
        const details=art+(gr?(" · "+gr):"")+" · "+zeit+(txt?(" · "+txt):"");
        updateForm("Großprojekt: "+art, details, "Persönliches Angebot", "Wir melden uns mit Ihrem individuellen Festpreis.");
    }
    ["gp-art","gp-groesse","gp-zeit","gp-text"].forEach(id=>{
        const el=document.getElementById(id);
        if(el){ el.addEventListener("input",gpUpdate); el.addEventListener("change",gpUpdate); }
    });
    gpUpdate();
}

function appointmentEvents(){
    document.querySelectorAll(".premium-date").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".premium-date").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            document.getElementById("form-appointment").value = btn.dataset.appointment;
        });
    });
}

function showLampe(){
    defaultHint = "Der Standardpreis gilt für normale Deckenlampen in Wohnräumen. Treppenhäuser, hohe Decken, Außenbereiche, Kronleuchter oder schwere Designlampen werden individuell kalkuliert.";

    configArea.innerHTML = `
        <div class="premium-config-head">
            <i class="fas fa-lightbulb"></i>
            <div>
                <h2>Lampenmontage</h2>
                <p>Konfigurieren Sie Ihre Anfrage</p>
            </div>
        </div>

        <div class="premium-step">
            <h3>1. Wo soll die Lampe montiert werden?</h3>
            <div class="premium-choice-grid">
                <button class="premium-choice active" type="button" data-location="standard">
                    <i class="fas fa-home"></i>
                    Wohnzimmer<br>Schlafzimmer<br>Küche<br>Kinderzimmer
                </button>
                <button class="premium-choice" type="button" data-location="special">
                    <i class="fas fa-building"></i>
                    Treppenhaus<br>hohe Decke
                </button>
                <button class="premium-choice" type="button" data-location="special">
                    <i class="fas fa-tree"></i>
                    Außenbereich
                </button>
            </div>
        </div>

        <div class="premium-step">
            <h3>2. Welche Lampe soll montiert werden?</h3>
            <div class="premium-choice-grid two">
                <button class="premium-choice active" type="button" data-lamptype="standard">
                    <i class="fas fa-lightbulb"></i>
                    Normale Deckenlampe
                </button>
                <button class="premium-choice" type="button" data-lamptype="special">
                    <i class="fas fa-gem"></i>
                    Große Designlampe<br>oder Kronleuchter
                </button>
            </div>
        </div>

        <div class="premium-step">
            <h3>3. Anzahl Lampen</h3>
            <div class="premium-counter">
                <button type="button" id="lamp-minus">−</button>
                <strong id="lamp-number">1</strong>
                <button type="button" id="lamp-plus">+</button>
                <input type="range" id="lamp-range" min="1" max="10" value="1">
            </div>
        </div>

        <div class="premium-step">
            <h3>4. Anfahrtszone</h3>
            <select id="travel-zone" class="premium-select">
                <option value="0">Nürnberg / Fürth / Erlangen inklusive</option>
                <option value="19">Bis 50 km Entfernung (+19 €)</option>
                <option value="39">50–75 km Entfernung (+39 €)</option>
                <option value="special">Über 75 km Entfernung</option>
            </select>
        </div>

        <div class="premium-inline-price">
            <div>
                <small>Ihr Preis inkl. Montage</small>
                <strong id="main-price">89 €</strong>
            </div>
            <span>inkl. Anfahrt in Ihrer Zone.</span>
        </div>

        <p class="premium-info-line"><i class="fas fa-info-circle"></i> Standardmontage. Sonderfälle werden individuell kalkuliert.</p>
    `;

    let lampCount = 1;
    let location = "standard";
    let lampType = "standard";

    function calc(){
        const zone = document.getElementById("travel-zone").value;
        let priceText;

        if(location === "special" || lampType === "special" || zone === "special"){
            priceText = "Individuell";
            document.getElementById("main-price").textContent = priceText;
            updateForm("Lampenmontage", "Sonderfall", "Individuelle Kalkulation", "Sonderfälle werden persönlich kalkuliert.");
            return;
        }

        const price = 89 + ((lampCount - 1) * 35) + parseInt(zone);
        priceText = price + " €";
        document.getElementById("main-price").textContent = priceText;
        updateForm("Lampenmontage", lampCount + " Standardlampe(n)", priceText, "inkl. Montage, Anfahrt in Ihrer Zone.");
    }

    document.querySelectorAll("[data-location]").forEach(btn => {
        btn.onclick = () => {
            document.querySelectorAll("[data-location]").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            location = btn.dataset.location;
            calc();
        };
    });

    document.querySelectorAll("[data-lamptype]").forEach(btn => {
        btn.onclick = () => {
            document.querySelectorAll("[data-lamptype]").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            lampType = btn.dataset.lamptype;
            calc();
        };
    });

    document.getElementById("lamp-plus").onclick = () => {
        if(lampCount < 10) lampCount++;
        document.getElementById("lamp-number").textContent = lampCount;
        document.getElementById("lamp-range").value = lampCount;
        calc();
    };

    document.getElementById("lamp-minus").onclick = () => {
        if(lampCount > 1) lampCount--;
        document.getElementById("lamp-number").textContent = lampCount;
        document.getElementById("lamp-range").value = lampCount;
        calc();
    };

    document.getElementById("lamp-range").oninput = (e) => {
        lampCount = parseInt(e.target.value);
        document.getElementById("lamp-number").textContent = lampCount;
        calc();
    };

    document.getElementById("travel-zone").onchange = calc;
    calc();
}

function showSteckdose(){
    defaultHint = "Steckdosen können nur ausgetauscht werden, wenn die vorhandene Elektroinstallation technisch geeignet ist. Sollte vor Ort festgestellt werden, dass Schutzleiter, FI-Schutz oder Absicherung nicht geeignet sind, kann zusätzlicher Aufwand entstehen.";

    configArea.innerHTML = `
        <div class="premium-config-head">
            <i class="fas fa-plug"></i>
            <div>
                <h2>Steckdose austauschen</h2>
                <p>Bestehende Steckdose ersetzen lassen</p>
            </div>
        </div>

        <div class="premium-step">
            <h3>Anzahl Steckdosen</h3>
            <div class="premium-counter">
                <button type="button" id="socket-minus">−</button>
                <strong id="socket-number">1</strong>
                <button type="button" id="socket-plus">+</button>
                <input type="range" id="socket-range" min="1" max="10" value="1">
            </div>
        </div>

        <div class="premium-step">
            <h3>Anfahrtszone</h3>
            <select id="socket-zone" class="premium-select">
                <option value="0">Nürnberg / Fürth / Erlangen inklusive</option>
                <option value="19">Bis 50 km Entfernung (+19 €)</option>
                <option value="39">50–75 km Entfernung (+39 €)</option>
                <option value="special">Über 75 km Entfernung</option>
            </select>
        </div>

        <div class="premium-inline-price">
            <div>
                <small>Ihr Preis</small>
                <strong id="socket-price">ab 69 €</strong>
            </div>
            <span>Material inklusive</span>
        </div>
    `;

    let count = 1;

    function calc(){
        const zone = document.getElementById("socket-zone").value;

        if(zone === "special"){
            document.getElementById("socket-price").textContent = "Individuell";
            updateForm("Steckdose austauschen", "Über 75 km", "Individuelle Kalkulation", "Sonderfälle werden persönlich kalkuliert.");
            return;
        }

        const price = (69 * count) + parseInt(zone);
        const priceText = "ab " + price + " €";
        document.getElementById("socket-price").textContent = priceText;
        updateForm("Steckdose austauschen", count + " Steckdose(n)", priceText, "Material inklusive");
    }

    document.getElementById("socket-plus").onclick = () => {
        if(count < 10) count++;
        document.getElementById("socket-number").textContent = count;
        document.getElementById("socket-range").value = count;
        calc();
    };

    document.getElementById("socket-minus").onclick = () => {
        if(count > 1) count--;
        document.getElementById("socket-number").textContent = count;
        document.getElementById("socket-range").value = count;
        calc();
    };

    document.getElementById("socket-range").oninput = (e) => {
        count = parseInt(e.target.value);
        document.getElementById("socket-number").textContent = count;
        calc();
    };

    document.getElementById("socket-zone").onchange = calc;
    calc();
}

function showFI(){
    defaultHint = "Vor der Nachrüstung prüfen wir Ihre bestehende Elektroinstallation. Sollten dabei Mängel festgestellt werden, z. B. ältere zweiadrige Leitungen oder fehlerhafte Verdrahtungen, kann zusätzlicher Aufwand für Fehlersuche und Instandsetzung entstehen. Fehlersuche bei Bedarf ab 70 € pro Stunde.";

    configArea.innerHTML = `
        <div class="premium-config-head">
            <i class="fas fa-shield-alt"></i>
            <div>
                <h2>FI-Schalter nachrüsten</h2>
                <p>Nachrüstung inkl. Prüfung</p>
            </div>
        </div>

        <div class="premium-inline-price">
            <div>
                <small>Preisrahmen</small>
                <strong>ab 119 €</strong>
            </div>
            <span>inkl. FI-Schalter, Montage und Funktionsprüfung</span>
        </div>

        <p class="premium-info-line"><i class="fas fa-info-circle"></i> Bei Mängeln kann zusätzlicher Aufwand entstehen.</p>
    `;

    updateForm("FI-Schalter nachrüsten", "FI-Schalter Nachrüstung", "ab 119 €", "inkl. FI-Schalter, Montage und Funktionsprüfung");
}

function showFehler(){
    defaultHint = "Bei Fehlersuchen kann der genaue Aufwand erst vor Ort eingeschätzt werden. Die Abrechnung erfolgt transparent nach Zeitaufwand. Material und Anfahrt werden zusätzlich berechnet.";

    configArea.innerHTML = `
        <div class="premium-config-head">
            <i class="fas fa-search"></i>
            <div>
                <h2>Fehlersuche Elektroanlage</h2>
                <p>Abrechnung nach tatsächlichem Aufwand</p>
            </div>
        </div>

        <div class="premium-inline-price">
            <div>
                <small>Stundenlohn</small>
                <strong>ab 70 €/h</strong>
            </div>
            <span>Material und Anfahrt zusätzlich</span>
        </div>

        <p class="premium-info-line"><i class="fas fa-info-circle"></i> Der genaue Aufwand wird vor Ort transparent besprochen.</p>
    `;

    updateForm("Fehlersuche Elektroanlage", "Fehlersuche nach Stundenlohn", "ab 70 €/h", "Material und Anfahrt zusätzlich");
}

function openHint(text){
    document.getElementById("hint-text").textContent = text;
    document.getElementById("hint-modal").classList.add("active");
}

function closeHint(){
    document.getElementById("hint-modal").classList.remove("active");
}

serviceClickEvents();
appointmentEvents();
showLampe();
document.querySelectorAll(".premium-service").forEach(b=>b.classList.remove("active")); // Start: noch nichts gewählt
wizGo(1);
document.querySelector('.premium-form').addEventListener('submit', function(e){

    let phone = document.getElementById('phone-field').value.trim();
    let email = document.getElementById('email-field').value.trim();

    if(phone === '' && email === ''){
        alert('Bitte Telefonnummer oder E-Mail-Adresse angeben.');
        e.preventDefault();
    } else {
        // Micro-Conversion: Kalkulator abgeschlossen (berechneter Preis als Wert)
        if(window.ohTrack){
            var pr=(document.getElementById('form-price')||{}).value||'';
            ohTrack('kalkulator_abschluss',{value: parseInt((pr+'').replace(/[^0-9]/g,''))||0});
        }
    }

});

/* Micro-Conversion: Kalkulator gestartet (erste Interaktion mit der Konfiguration) */
(function(){
    var ca=document.getElementById('config-area'); var started=false;
    if(ca) ca.addEventListener('click', function(){
        if(!started){ started=true; if(window.ohTrack) ohTrack('kalkulator_start',{value:1}); }
    }, true);
})();
</script>

<?php include 'includes/footer.php'; ?>
