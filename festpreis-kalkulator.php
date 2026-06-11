<?php
$pageTitle = "Festpreis-Kalkulator | OH Haustechnik";
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

        <div class="premium-services">
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

        <div class="premium-layout">
            <div class="premium-left" id="config-area"></div>

            <div class="premium-right">
                <div class="premium-price-card">
                    <small>Ihr Preis</small>
                    <strong id="side-price">89 €</strong>
                    <span id="side-price-sub">inkl. Montage, Anfahrt in Ihrer Zone und MwSt.</span>
                    <i class="fas fa-lightbulb"></i>
                </div>

                <button class="premium-hint-btn" type="button" onclick="openHint(defaultHint)">
                    <i class="fas fa-info-circle"></i>
                    Wichtige Hinweise anzeigen
                </button>

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
            </div>
        </div>

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
    sidePriceSub.textContent = subtitle || "inkl. Montage, Anfahrt in Ihrer Zone und MwSt.";
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
        });
    });
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
            <span>inkl. Anfahrt in Ihrer Zone und MwSt.</span>
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
        updateForm("Lampenmontage", lampCount + " Standardlampe(n)", priceText, "inkl. Montage, Anfahrt in Ihrer Zone und MwSt.");
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
document.querySelector('.premium-form').addEventListener('submit', function(e){

    let phone = document.getElementById('phone-field').value.trim();
    let email = document.getElementById('email-field').value.trim();

    if(phone === '' && email === ''){
        alert('Bitte Telefonnummer oder E-Mail-Adresse angeben.');
        e.preventDefault();
    }

});
</script>

<?php include 'includes/footer.php'; ?>
