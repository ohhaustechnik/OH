<?php
$seo = [
  'title'   => 'Photovoltaik Nürnberg | PV-Anlage vom Elektro-Fachbetrieb',
  'desc'    => 'PV-Anlage im Raum Nürnberg — Montage und Elektrik aus einer Hand: Module, Wechselrichter, Speicher, Zählerschrank und Anmeldung beim Netzbetreiber.',
  'slug'    => 'photovoltaik-nuernberg.php',
  'service' => 'Photovoltaik-Installation und Speicheranbindung',
  'image'   => 'https://oh-haustechnik.de/assets/img/lp/uv.jpg',
  'faq'     => [
    ['Was kostet eine Photovoltaikanlage?',
     'Eine schlüsselfertige Anlage beginnt bei rund 12.000 € — das entspricht etwa 8 kWp mit Speicher, inklusive Montage, Wechselrichter, Zählerschrank-Anpassung und Anmeldung. Den Preis bestimmen vor allem die Dachfläche, die Dachart, die Speichergröße und ob der Zählerschrank erneuert werden muss. Seit § 12 Abs. 3 UStG fällt auf Anlagen auf Wohngebäuden keine Umsatzsteuer an, der genannte Betrag ist also der Endpreis.'],
    ['Montiert ihr auch die Module auf dem Dach?', 'Ja. Wir übernehmen die Montage auf dem Dach und den kompletten elektrischen Teil — Unterkonstruktion, Module, Wechselrichter, Speicher, Zählerschrank und die Anmeldung beim Netzbetreiber. Sie brauchen also keinen zweiten Betrieb zu koordinieren und haben einen Ansprechpartner, wenn etwas zu klären ist.'],
    ['Muss der Zählerschrank für PV getauscht werden?', 'Häufig ja. Ältere Zählerschränke erfüllen die heutigen Anforderungen des Netzbetreibers oft nicht mehr — es fehlt Platz für den Zweirichtungszähler, der Überspannungsschutz oder die geforderte Aufteilung. Wir prüfen den Bestand vorab und sagen Ihnen, ob ein Umbau nötig ist, bevor Sie die Anlage bestellen.'],
    ['Wer meldet die Anlage beim Netzbetreiber an?', 'Die Anmeldung und Inbetriebsetzung beim Netzbetreiber darf nur ein eingetragener Elektrofachbetrieb vornehmen. Diesen Teil übernehmen wir für Sie, einschließlich der erforderlichen Unterlagen und Messprotokolle. Die Registrierung im Marktstammdatenregister ist zusätzlich erforderlich.'],
    ['Lohnt sich ein Speicher?', 'Das hängt von Ihrem Verbrauchsprofil ab, nicht von der Anlagengröße allein. Wer tagsüber wenig Strom braucht und abends viel, profitiert stärker als ein Haushalt mit hohem Tagverbrauch. Wir rechnen ehrlich mit Ihnen durch, ob sich die Investition in Ihrem Fall trägt — auch wenn die Antwort manchmal nein lautet.'],
  ],
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<?php include __DIR__ . '/includes/seo-head.php'; ?>
</head>
<body>

<?php $oh_active = 'leistungen'; include __DIR__ . '/includes/header-dark.php'; ?>

<section class="page-hero">
  <div class="wrap">
    <div class="breadcrumb rv"><a href="/index.php">Start</a> · <a href="/leistungen.php">Leistungen</a> · Photovoltaik</div>
    <h1 class="rv"><span class="yellow">Photovoltaik</span> in Nürnberg</h1>
    <p class="rv">Montage und Elektrik aus einer Hand: Module, Wechselrichter, Speicher, Zählerschrank und die Anmeldung beim Netzbetreiber — ein Ansprechpartner von der Planung bis zur Inbetriebsetzung.</p>
    <?php $lp_thema = 'Photovoltaik in Nürnberg'; $lp_cta = 'Kostenloses PV-Angebot anfordern'; $lp_preis = 'Montage und Elektrik aus einer Hand · Festpreis nach kurzem Vor-Ort-Termin'; include __DIR__ . '/includes/lp-cta.php'; ?>
    <div class="badges rv" style="margin-top:22px">
      <span class="badge">Montage &amp; Elektrik</span>
      <span class="badge">Speicher &amp; Wallbox</span>
      <span class="badge">Netzbetreiber-Anmeldung</span>
      <span class="badge">VDE-konform</span>
    </div>
  </div>
</section>

<section>
  <div class="wrap prose">
    <div class="eyebrow rv">Montage und Elektrik</div>
    <h2 class="rv">Alles aus einer Hand.</h2>
    <p class="rv">Eine PV-Anlage besteht aus zwei Teilen: der Montage auf dem Dach und dem elektrischen Anschluss. Beides machen wir selbst. Sie koordinieren also nicht zwei Betriebe, die sich im Zweifel gegenseitig die Schuld zuschieben, sondern haben von der Unterkonstruktion bis zur Inbetriebsetzung einen Ansprechpartner.</p>
    <p class="rv">Das umfasst Unterkonstruktion und Modulmontage ebenso wie Leitungsführung, Wechselrichter, Speicheranbindung, Zählerplatz, Schutzorgane, Messkonzept und die Inbetriebsetzung beim Netzbetreiber. Der elektrische Teil ist dabei der anspruchsvollere — und genau der ist unser Kerngeschäft, nicht ein zugekaufter Nebenposten.</p>

    <h2 class="rv">Der Zählerschrank ist meistens der Knackpunkt</h2>
    <p class="rv">Der häufigste Grund, warum PV-Projekte teurer werden als geplant: Der vorhandene Zählerschrank erfüllt die Anforderungen des Netzbetreibers nicht mehr. In Bestandsgebäuden ist das eher die Regel als die Ausnahme.</p>
    <p class="rv">Typische Gründe sind fehlender Platz für den Zweirichtungszähler, ein nicht mehr zulässiger Aufbau, fehlender Überspannungsschutz oder ein Zählerfeld ohne die geforderte Trennvorrichtung. Wir prüfen das <em>vor</em> der Anlagenbestellung — damit die Kosten von Anfang an auf dem Tisch liegen und nicht mitten im Projekt auftauchen.</p>

    <h2 class="rv">Leistungen im Detail</h2>
    <ul class="rv">
      <li>Montage der Unterkonstruktion und der Module auf dem Dach</li>
      <li>Bestandsaufnahme von Zählerschrank, Unterverteilung und Leitungswegen</li>
      <li>Anpassung oder Neuaufbau des Zählerplatzes nach TAB des Netzbetreibers</li>
      <li>DC- und AC-seitiger Anschluss von Wechselrichter und Batteriespeicher</li>
      <li>Überspannungsschutz und passende <a href="/leistungen/schutztechnik.php" style="text-decoration:underline">Fehlerstrom-Schutztechnik</a></li>
      <li>Anbindung einer <a href="/zaehlerschrank-wallbox-nuernberg.php" style="text-decoration:underline">Wallbox</a> mit Überschussladung</li>
      <li>Einbindung in ein vorhandenes <a href="/smart-home-knx-loxone-nuernberg.php" style="text-decoration:underline">Smart-Home-System</a> zur Verbrauchssteuerung</li>
      <li>Messprotokolle, Anmeldung und Inbetriebsetzung beim Netzbetreiber</li>
    </ul>

    <h2 class="rv">Speicher: rechnen statt hoffen</h2>
    <p class="rv">Ob sich ein Batteriespeicher trägt, entscheidet Ihr Verbrauchsprofil. Ein Haushalt, der tagsüber leer steht und abends kocht, wäscht und lädt, nutzt einen Speicher deutlich besser als ein Homeoffice-Haushalt mit hohem Tagverbrauch — bei dem geht ein großer Teil des Solarstroms ohnehin direkt in den Eigenverbrauch.</p>
    <p class="rv">Wir schauen uns Ihren tatsächlichen Jahresverbrauch an, bevor wir eine Größe empfehlen. Wenn sich der Speicher nicht rechnet, sagen wir das auch.</p>

    <h2 class="rv">Wallbox und PV zusammen denken</h2>
    <p class="rv">Wer ohnehin eine PV-Anlage plant und in absehbarer Zeit ein E-Auto anschafft, sollte beides gemeinsam auslegen. Die Leitung zur Wallbox mitzuverlegen kostet während der PV-Installation wenig; sie später nachzuziehen bedeutet oft erneutes Aufstemmen. Auch die Überschussladung — also nur mit eigenem Solarstrom laden — setzt voraus, dass Wechselrichter, Zähler und Wallbox zusammenpassen.</p>
  </div>
</section>

<section class="alt-bg">
  <div class="wrap prose">
    <h2 class="rv">Häufige Fragen</h2>
    <?php foreach ($seo['faq'] as $f): ?>
      <h3 class="rv" style="font-family:var(--body);text-transform:none;font-size:18px;font-weight:700;margin:24px 0 8px"><?= htmlspecialchars($f[0]) ?></h3>
      <p class="rv"><?= htmlspecialchars($f[1]) ?></p>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 28px"><h2>PV-Anlage anfragen.</h2><p>Schicken Sie uns ein Foto Ihres Dachs und Ihres Zählerschranks — dann können wir die wichtigsten Fragen meist sofort beantworten.</p></div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-y" href="/index.php#anfrage">Jetzt anfragen</a>
      <a class="btn btn-ghost" href="tel:+491757481006">Direkt anrufen</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer-dark.php'; ?>
</body>
</html>
