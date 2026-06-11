<?php
$page_title       = 'Datenschutzerklärung – OH Haustechnik Nürnberg';
$meta_description = 'Datenschutzerklärung von OH Haustechnik gemäß DSGVO.';
$canonical_url    = 'https://oh-haustechnik.de/datenschutz.php';

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <div class="breadcrumb">
                <a href="index.php">Start</a>
                <i class="fas fa-chevron-right"></i>
                <span>Datenschutz</span>
            </div>
            <h1>Datenschutzerklärung</h1>
            <p>Informationen zur Verarbeitung Ihrer personenbezogenen Daten gemäß DSGVO.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">

        <?php
        $sections_ds = [
            [
                'title' => '1. Datenschutz auf einen Blick',
                'content' => '
                    <h4 style="margin-bottom: 0.75rem;">Allgemeine Hinweise</h4>
                    <p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Datenerfassung auf dieser Website</h4>
                    <p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong><br>
                    Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten können Sie dem Impressum dieser Website entnehmen.</p>
                    
                    <p><strong>Wie erfassen wir Ihre Daten?</strong><br>
                    Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. Hierbei kann es sich z.B. um Daten handeln, die Sie in ein Kontaktformular eingeben. Andere Daten werden automatisch oder nach Ihrer Einwilligung beim Besuch der Website durch unsere IT-Systeme erfasst (z.B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs).</p>
                    
                    <p><strong>Wofür nutzen wir Ihre Daten?</strong><br>
                    Ein Teil der Daten wird erhoben, um eine fehlerfreie Bereitstellung der Website zu gewährleisten. Andere Daten können zur Analyse Ihres Nutzerverhaltens verwendet werden. Daten, die Sie über das Kontaktformular übermitteln, werden ausschließlich zur Bearbeitung Ihrer Anfrage verwendet.</p>
                    
                    <p><strong>Welche Rechte haben Sie bezüglich Ihrer Daten?</strong><br>
                    Sie haben jederzeit das Recht, unentgeltlich Auskunft über Herkunft, Empfänger und Zweck Ihrer gespeicherten personenbezogenen Daten zu erhalten. Sie haben außerdem ein Recht, die Berichtigung oder Löschung dieser Daten zu verlangen. Hierzu sowie zu weiteren Fragen zum Thema Datenschutz können Sie sich jederzeit an uns wenden.'
            ],
            [
                'title' => '2. Hosting',
                'content' => '<p>Diese Website wird bei einem externen Dienstleister gehostet (Hoster). Die personenbezogenen Daten, die auf dieser Website erfasst werden, werden auf den Servern des Hosters gespeichert. Hierbei kann es sich v.a. um IP-Adressen, Kontaktanfragen, Meta- und Kommunikationsdaten, Vertragsdaten, Kontaktdaten, Namen, Websitezugriffe und sonstige Daten, die über eine Website generiert werden, handeln.</p>
                <p>Der Einsatz des Hosters erfolgt zum Zwecke der Vertragserfüllung gegenüber unseren potenziellen und bestehenden Kunden (Art. 6 Abs. 1 lit. b DSGVO) und im Interesse einer sicheren, schnellen und effizienten Bereitstellung unseres Online-Angebots durch einen professionellen Anbieter (Art. 6 Abs. 1 lit. f DSGVO).</p>'
            ],
            [
                'title' => '3. Allgemeine Hinweise und Pflichtinformationen',
                'content' => '
                    <h4 style="margin-bottom: 0.75rem;">Datenschutz</h4>
                    <p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Hinweis zur verantwortlichen Stelle</h4>
                    <p>Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:<br>
                    OH Haustechnik<br>
                    [Adresse]<br>
                    Nürnberg<br>
                    Telefon: +49 175 7481006<br>
                    E-Mail: info@oh-haustechnik.de</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Speicherdauer</h4>
                    <p>Soweit innerhalb dieser Datenschutzerklärung keine speziellere Speicherdauer genannt wurde, verbleiben Ihre personenbezogenen Daten bei uns, bis der Zweck für die Datenverarbeitung entfällt. Wenn Sie ein berechtigtes Löschersuchen geltend machen oder eine Einwilligung zur Datenverarbeitung widerrufen, werden Ihre Daten gelöscht, sofern wir keine anderen rechtlich zulässigen Gründe für die Speicherung Ihrer personenbezogenen Daten haben.</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Widerruf Ihrer Einwilligung zur Datenverarbeitung</h4>
                    <p>Viele Datenverarbeitungsvorgänge sind nur mit Ihrer ausdrücklichen Einwilligung möglich. Sie können eine bereits erteilte Einwilligung jederzeit widerrufen. Die Rechtmäßigkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unberührt.</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Recht auf Datenübertragbarkeit</h4>
                    <p>Sie haben das Recht, Daten, die wir auf Grundlage Ihrer Einwilligung oder in Erfüllung eines Vertrags automatisiert verarbeiten, an sich oder an einen Dritten in einem gängigen, maschinenlesbaren Format aushändigen zu lassen.</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">SSL- bzw. TLS-Verschlüsselung</h4>
                    <p>Diese Seite nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher Inhalte eine SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie daran, dass die Adresszeile des Browsers von "http://" auf "https://" wechselt.'
            ],
            [
                'title' => '4. Datenerfassung auf dieser Website',
                'content' => '
                    <h4 style="margin-bottom: 0.75rem;">Cookies</h4>
                    <p>Unsere Internetseiten verwenden so genannte "Cookies". Cookies sind kleine Datenpakete und richten auf Ihrem Endgerät keinen Schaden an. Sie werden entweder vorübergehend für die Dauer einer Sitzung (Session-Cookies) oder dauerhaft (permanente Cookies) auf Ihrem Endgerät gespeichert. Session-Cookies werden nach Ende Ihres Besuchs automatisch gelöscht. Permanente Cookies bleiben auf Ihrem Endgerät gespeichert, bis Sie diese selbst löschen oder eine automatische Löschung durch Ihren Webbrowser erfolgt.</p>
                    <p>Wir setzen ausschließlich technisch notwendige Cookies ein (z.B. zur Speicherung Ihrer Cookie-Einwilligung).</p>
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Kontaktformular</h4>
                    <p>Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem Anfrageformular inklusive der von Ihnen dort angegebenen Kontaktdaten zwecks Bearbeitung der Anfrage und für den Fall von Anschlussfragen bei uns gespeichert. Diese Daten geben wir nicht ohne Ihre Einwilligung weiter.</p>
                    <p>Die Verarbeitung dieser Daten erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO, sofern Ihre Anfrage mit der Erfüllung eines Vertrags zusammenhängt oder zur Durchführung vorvertraglicher Maßnahmen erforderlich ist. In allen übrigen Fällen beruht die Verarbeitung auf unserem berechtigten Interesse an der effektiven Bearbeitung der an uns gerichteten Anfragen (Art. 6 Abs. 1 lit. f DSGVO) oder auf Ihrer Einwilligung (Art. 6 Abs. 1 lit. a DSGVO) sofern diese abgefragt wurde; die Einwilligung ist jederzeit widerrufbar.</p>
                    <p>Die von Ihnen im Kontaktformular eingegebenen Daten verbleiben bei uns, bis Sie uns zur Löschung auffordern, Ihre Einwilligung zur Speicherung widerrufen oder der Zweck für die Datenspeicherung entfällt (z.B. nach abgeschlossener Bearbeitung Ihrer Anfrage). Zwingende gesetzliche Bestimmungen – insbesondere Aufbewahrungsfristen – bleiben unberührt.
                    
                    <h4 style="margin: 1.5rem 0 0.75rem;">Anfrage per E-Mail oder Telefon</h4>
                    <p>Wenn Sie uns per E-Mail oder Telefon kontaktieren, wird Ihre Anfrage inklusive aller daraus hervorgehenden personenbezogenen Daten (Name, Anfrage) zum Zwecke der Bearbeitung Ihres Anliegens bei uns gespeichert und verarbeitet. Diese Daten geben wir nicht ohne Ihre Einwilligung weiter.</p>'
            ],
            [
                'title' => '5. Ihre Rechte',
                'content' => '
                    <p>Im Rahmen der geltenden gesetzlichen Bestimmungen haben Sie jederzeit das Recht auf:</p>
                    <ul style="padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0; color: var(--text-secondary);">
                        <li><strong>Auskunft</strong> über Ihre gespeicherten personenbezogenen Daten (Art. 15 DSGVO)</li>
                        <li><strong>Berichtigung</strong> unrichtiger Daten (Art. 16 DSGVO)</li>
                        <li><strong>Löschung</strong> Ihrer Daten (Art. 17 DSGVO)</li>
                        <li><strong>Einschränkung</strong> der Datenverarbeitung (Art. 18 DSGVO)</li>
                        <li><strong>Widerspruch</strong> gegen die Verarbeitung (Art. 21 DSGVO)</li>
                        <li><strong>Datenübertragbarkeit</strong> (Art. 20 DSGVO)</li>
                    </ul>
                    <p>Außerdem haben Sie das Recht, sich bei einer Datenschutz-Aufsichtsbehörde über die Verarbeitung Ihrer personenbezogenen Daten durch uns zu beschweren.</p>
                    <p>Zur Geltendmachung Ihrer Rechte wenden Sie sich bitte an: <a href="mailto:info@oh-haustechnik.de" style="color: var(--blue-primary);">info@oh-haustechnik.de</a></p>'
            ]
        ];
        ?>

        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <?php foreach ($sections_ds as $sec): ?>
            <div style="background: var(--gray-100); border-radius: var(--border-radius-lg); padding: 2.5rem; border: 1px solid var(--gray-200);" data-animate>
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; padding-bottom: 0.875rem; border-bottom: 2px solid var(--blue-primary); color: var(--text-primary);">
                    <?php echo $sec['title']; ?>
                </h2>
                <div style="color: var(--text-secondary); line-height: 1.8; font-size: 0.95rem;">
                    <?php echo $sec['content']; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;" data-animate>
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Stand: <?php echo date('F Y'); ?> – Bei Fragen zum Datenschutz wenden Sie sich bitte an 
                <a href="mailto:info@oh-haustechnik.de" style="color: var(--blue-primary);">info@oh-haustechnik.de</a>
            </p>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
