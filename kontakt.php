<?php
$page_title       = 'Kontakt – OH Haustechnik Nürnberg | Anfrage für Elektroinstallation';
$meta_description = 'Kontaktieren Sie OH Haustechnik in Nürnberg ✓ Kostenlose Anfrage ✓ Elektroinstallation & Netzwerkverkabelung ✓ Schnelle Rückmeldung ✓ VDE-konform';
$canonical_url    = 'https://oh-haustechnik.de/kontakt.php';
$show_funnel_inline = true;

include 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <div class="breadcrumb">
                <a href="index.php">Start</a>
                <i class="fas fa-chevron-right"></i>
                <span>Kontakt</span>
            </div>
            <h1>Kontakt aufnehmen</h1>
            <p>
                Beschreiben Sie uns kurz Ihr Vorhaben – wir melden uns zeitnah und 
                besprechen gemeinsam die technischen Anforderungen.
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="kontakt-grid">

            <!-- Kontaktinformationen -->
            <div data-animate>
                <h2 style="font-size: 1.75rem; margin-bottom: 2rem;">Kontaktinformationen</h2>

                <div class="kontakt-info-card">

                    <div class="kontakt-info-item">
                        <div class="kontakt-info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="kontakt-info-title">Telefon</div>
                            <div class="kontakt-info-value">
                                <a href="tel:+491757481006">+49 175 7481006</a>
                            </div>
                            <div class="kontakt-info-note">Mo – Fr: 07:30 – 17:00 Uhr</div>
                        </div>
                    </div>

                    <div class="kontakt-info-item">
                        <div class="kontakt-info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="kontakt-info-title">E-Mail</div>
                            <div class="kontakt-info-value">
                                <a href="mailto:oh.Haustechnik@gmail.com">oh.Haustechnik@gmail.com</a>
                            </div>
                            <div class="kontakt-info-note">Antwort in der Regel innerhalb von 24 Stunden</div>
                        </div>
                    </div>

                    <div class="kontakt-info-item">
                        <div class="kontakt-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="kontakt-info-title">Einsatzgebiet</div>
                            <div class="kontakt-info-value">Raum Nürnberg</div>
                            <div class="kontakt-info-note">Nürnberg · Fürth · Erlangen · umliegende Gemeinden</div>
                        </div>
                    </div>

                </div>

                <!-- Trust Box -->
                <div style="background: var(--blue-light); border-radius: var(--border-radius-lg); padding: 2rem; margin-top: 2rem; border: 1px solid var(--gray-200);">
                    <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--blue-dark);">
                        <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>Was passiert nach Ihrer Anfrage?
                    </h4>
                    <ol style="padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <li style="font-size: 0.9rem; color: var(--text-secondary);">Wir prüfen Ihre Anfrage und melden uns innerhalb von 24 Stunden.</li>
                        <li style="font-size: 0.9rem; color: var(--text-secondary);">Wir besprechen gemeinsam Ihren Bedarf und die technischen Anforderungen.</li>
                        <li style="font-size: 0.9rem; color: var(--text-secondary);">Sie erhalten ein transparentes und vollständiges Angebot.</li>
                    </ol>
                </div>
            </div>

            <!-- Funnel Inline -->
            <div data-animate>
                <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Jetzt Angebot anfordern</h2>
                <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.5rem;">In nur 2 Minuten – kostenlos &amp; unverbindlich.</p>
				<p style="font-size:0.85rem;color:#6b7280;margin-top:-10px;margin-bottom:15px;">
               
				<!-- Inline Funnel -->
                <div class="funnel-inline" id="funnel-inline">

                    <!-- Progress -->
                    <div class="funnel-inline-progress">
                        <div class="funnel-progress-bar-wrap" style="flex:1;">
                            <div class="funnel-progress-bar-fill" id="fi-progress-fill" style="width:0%"></div>
                        </div>
                        <span class="funnel-progress-label" id="fi-progress-label">Schritt 1 von 8</span>
                    </div>

                    <!-- Steps -->
                    <div class="funnel-body" style="padding:1.5rem 0 0;">

                        <!-- SCHRITT 1 -->
                        <div class="funnel-step active" id="fi-step-1">
                            <div class="funnel-step-title">Was können wir für Sie tun?</div>
                            <div class="funnel-step-desc">Wählen Sie die passende Leistungskategorie aus.</div>
                            <div class="funnel-option-grid">
                                <div class="funnel-option">
                                    <input type="radio" name="fi_kategorie" id="fi-kat-elektro" value="elektro">
                                    <label class="funnel-option-label" for="fi-kat-elektro">
                                        <span class="funnel-option-icon"><i class="fas fa-plug"></i></span>
                                        <span class="funnel-option-text">Elektroinstallation &amp; Sanierung<small>Neubau, Altbau, Erweiterung</small></span>
                                    </label>
                                </div>
                                <div class="funnel-option">
                                    <input type="radio" name="fi_kategorie" id="fi-kat-netzwerk" value="netzwerk">
                                    <label class="funnel-option-label" for="fi-kat-netzwerk">
                                        <span class="funnel-option-icon"><i class="fas fa-network-wired"></i></span>
                                        <span class="funnel-option-text">Netzwerkverkabelung<small>LAN, Datendosen, Patchpanel</small></span>
                                    </label>
                                </div>
                                <div class="funnel-option">
                                    <input type="radio" name="fi_kategorie" id="fi-kat-fehler" value="fehler">
                                    <label class="funnel-option-label" for="fi-kat-fehler">
                                        <span class="funnel-option-icon"><i class="fas fa-search"></i></span>
                                        <span class="funnel-option-text">Fehlersuche &amp; Reparatur<small>FI-Schalter, Ausfall, Kurzschluss</small></span>
                                    </label>
                                </div>
                                <div class="funnel-option">
                                    <input type="radio" name="fi_kategorie" id="fi-kat-lampen" value="lampen">
                                    <label class="funnel-option-label" for="fi-kat-lampen">
                                        <span class="funnel-option-icon"><i class="fas fa-lightbulb"></i></span>
                                        <span class="funnel-option-text">Lampen &amp; Leuchtenmontage<small>Deckenlampen, Spots, LED</small></span>
                                    </label>
                                </div>
                            </div>
                            <!-- Sub-Options -->
                            <div class="funnel-suboptions" id="fi-sub-elektro">
                                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was soll gemacht werden?</div>
                                <div class="funnel-checkbox-list">
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Neue Steckdosen / Schalter"> Neue Steckdosen / Schalter</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Unterverteilung / Sicherungskasten"> Unterverteilung / Sicherungskasten</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Leitungen verlegen"> Leitungen verlegen</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Elektro-Komplettsanierung"> Elektro-Komplettsanierung</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Außenbereich / Garten"> Außenbereich / Garten</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="E-Auto Ladestation"> E-Auto Ladestation</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Smart Home / KNX"> Smart Home / KNX</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges"> Sonstiges</label>
                                </div>
                            </div>
                            <div class="funnel-suboptions" id="fi-sub-netzwerk">
                                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was soll gemacht werden?</div>
                                <div class="funnel-checkbox-list">
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Datendosen setzen"> Datendosen setzen</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Patchpanel / Netzwerkschrank"> Patchpanel / Netzwerkschrank</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Strukturierte Verkabelung"> Strukturierte Verkabelung</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="WLAN / Access Points"> WLAN / Access Points</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Serverraum Verkabelung"> Serverraum Verkabelung</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Kabelkanal / Schächte"> Kabelkanal / Schächte</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Messung &amp; Zertifizierung"> Messung &amp; Zertifizierung</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Telefon / ISDN"> Telefon / ISDN</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges"> Sonstiges</label>
                                </div>
                            </div>
                            <div class="funnel-suboptions" id="fi-sub-fehler">
                                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was ist das Problem?</div>
                                <div class="funnel-checkbox-list">
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="FI-Schalter löst aus"> FI-Schalter löst aus</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sicherung fliegt raus"> Sicherung fliegt raus</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Kein Strom in Raum"> Kein Strom in Raum</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Steckdose defekt"> Steckdose defekt</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Licht funktioniert nicht"> Licht funktioniert nicht</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Herd / Ofen ohne Funktion"> Herd / Ofen ohne Funktion</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges Problem"> Sonstiges Problem</label>
                                </div>
                            </div>
                            <div class="funnel-suboptions" id="fi-sub-lampen">
                                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was soll montiert werden?</div>
                                <div class="funnel-checkbox-list">
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Deckenlampen montieren"> Deckenlampen montieren</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Einbauspots / LED-Panel"> Einbauspots / LED-Panel</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Außenleuchten"> Außenleuchten</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Spiegelleuchten Bad"> Spiegelleuchten Bad</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Pendel- / Hängelampen"> Pendel- / Hängelampen</label>
                                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges"> Sonstiges</label>
                                </div>
                                <div class="funnel-count-input-wrap">
                                    <label for="fi-lampen-anzahl">Ungefähre Anzahl der Lampen:</label>
                                    <input type="number" id="fi-lampen-anzahl" min="1" max="999" placeholder="z. B. 5">
                                </div>
                            </div>
                        </div>

                        <!-- SCHRITT 2: Elektro-Typ (nur bei Elektroinstallation) -->
                        <div class="funnel-step" id="fi-step-2">
                            <div class="funnel-step-title">Um welche Art von Elektroarbeit handelt es sich?</div>
                            <div class="funnel-step-desc">Wählen Sie den passenden Bereich für Ihre Elektroinstallation.</div>
                            <div class="funnel-option-grid cols-1">
                                <div class="funnel-option">
                                    <input type="radio" name="fi_elektro_typ" id="fi-etyp-altbau" value="Altbausanierung">
                                    <label class="funnel-option-label" for="fi-etyp-altbau">
                                        <span class="funnel-option-icon"><i class="fas fa-house-damage"></i></span>
                                        <span class="funnel-option-text">Altbausanierung<small>Erneuerung veralteter Elektroinstallationen</small></span>
                                    </label>
                                </div>
                                <div class="funnel-option">
                                    <input type="radio" name="fi_elektro_typ" id="fi-etyp-neubau" value="Neubau">
                                    <label class="funnel-option-label" for="fi-etyp-neubau">
                                        <span class="funnel-option-icon"><i class="fas fa-building"></i></span>
                                        <span class="funnel-option-text">Neubau<small>Komplette Elektroinstallation im Neubau</small></span>
                                    </label>
                                </div>
                                <div class="funnel-option">
                                    <input type="radio" name="fi_elektro_typ" id="fi-etyp-erweiterung" value="Erweiterung">
                                    <label class="funnel-option-label" for="fi-etyp-erweiterung">
                                        <span class="funnel-option-icon"><i class="fas fa-expand-arrows-alt"></i></span>
                                        <span class="funnel-option-text">Erweiterung<small>Zusätzliche Stromkreise, Steckdosen, Leitungen</small></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SCHRITT 3 -->
                        <div class="funnel-step" id="fi-step-3">
                            <div class="funnel-step-title">Wie groß ist das Objekt?</div>
                            <div class="funnel-step-desc">Wählen Sie die ungefähre Wohn- oder Nutzfläche.</div>
                            <div class="funnel-option-grid cols-1">
                                <div class="funnel-option"><input type="radio" name="fi_objektgroesse" id="fi-obj-50" value="Bis 50 m²"><label class="funnel-option-label" for="fi-obj-50"><span class="funnel-option-icon"><i class="fas fa-home"></i></span><span class="funnel-option-text">Bis 50 m²<small>Kleine Wohnung / Studio</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_objektgroesse" id="fi-obj-100" value="50 – 100 m²"><label class="funnel-option-label" for="fi-obj-100"><span class="funnel-option-icon"><i class="fas fa-home"></i></span><span class="funnel-option-text">50 – 100 m²<small>Mittelgroße Wohnung / Haus</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_objektgroesse" id="fi-obj-150" value="100 – 150 m²"><label class="funnel-option-label" for="fi-obj-150"><span class="funnel-option-icon"><i class="fas fa-building"></i></span><span class="funnel-option-text">100 – 150 m²<small>Großes Haus / Gewerbe klein</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_objektgroesse" id="fi-obj-200" value="150 – 200 m²"><label class="funnel-option-label" for="fi-obj-200"><span class="funnel-option-icon"><i class="fas fa-building"></i></span><span class="funnel-option-text">150 – 200 m²<small>Großes Haus / Gewerbe mittel</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_objektgroesse" id="fi-obj-over200" value="Über 200 m²"><label class="funnel-option-label" for="fi-obj-over200"><span class="funnel-option-icon"><i class="fas fa-industry"></i></span><span class="funnel-option-text">Über 200 m²<small>Großes Gewerbe / Neubau</small></span></label></div>
                            </div>
                        </div>

                        <!-- SCHRITT 4 -->
                        <div class="funnel-step" id="fi-step-4">
                            <div class="funnel-step-title">Wann soll die Arbeit erledigt werden?</div>
                            <div class="funnel-step-desc">Ihr gewünschter Ausführungszeitraum.</div>
                            <div class="funnel-option-grid cols-1">
                                <div class="funnel-option"><input type="radio" name="fi_ausfuehrungszeit" id="fi-zeit-dringend" value="So schnell wie möglich (dringend)"><label class="funnel-option-label" for="fi-zeit-dringend"><span class="funnel-option-icon"><i class="fas fa-exclamation-triangle"></i></span><span class="funnel-option-text">So schnell wie möglich<small>Dringende Situation</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_ausfuehrungszeit" id="fi-zeit-2wochen" value="Innerhalb der nächsten 2 Wochen"><label class="funnel-option-label" for="fi-zeit-2wochen"><span class="funnel-option-icon"><i class="fas fa-calendar-week"></i></span><span class="funnel-option-text">Innerhalb der nächsten 2 Wochen<small>Zeitnah, aber nicht sofort</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_ausfuehrungszeit" id="fi-zeit-monat" value="Im nächsten Monat"><label class="funnel-option-label" for="fi-zeit-monat"><span class="funnel-option-icon"><i class="fas fa-calendar-alt"></i></span><span class="funnel-option-text">Im nächsten Monat<small>Genug Zeit zum Planen</small></span></label></div>
                                <div class="funnel-option"><input type="radio" name="fi_ausfuehrungszeit" id="fi-zeit-flexibel" value="Flexibel – kein festes Datum"><label class="funnel-option-label" for="fi-zeit-flexibel"><span class="funnel-option-icon"><i class="fas fa-infinity"></i></span><span class="funnel-option-text">Flexibel – kein festes Datum<small>Wir stimmen uns ab</small></span></label></div>
                            </div>
                        </div>

                        <!-- SCHRITT 5 -->
                        <div class="funnel-step" id="fi-step-5">
                            <div class="funnel-step-title">Wann sind Sie erreichbar?</div>
                            <div class="funnel-step-desc">Und wie sollen wir uns bei Ihnen melden?</div>
                            <div class="funnel-split">
                                <div>
                                    <div class="funnel-split-block-title"><i class="fas fa-clock" style="margin-right:0.35rem;"></i>Bevorzugtes Zeitfenster</div>
                                    <div class="funnel-option-grid cols-1">
                                        <div class="funnel-option"><input type="radio" name="fi_erreichbarkeit" id="fi-err-morgens" value="Morgens (07:30 – 12:00 Uhr)"><label class="funnel-option-label" for="fi-err-morgens"><span class="funnel-option-icon"><i class="fas fa-sun"></i></span><span class="funnel-option-text">Morgens<small>07:30 – 12:00 Uhr</small></span></label></div>
                                        <div class="funnel-option"><input type="radio" name="fi_erreichbarkeit" id="fi-err-mittags" value="Mittags (12:00 – 15:00 Uhr)"><label class="funnel-option-label" for="fi-err-mittags"><span class="funnel-option-icon"><i class="fas fa-cloud-sun"></i></span><span class="funnel-option-text">Mittags<small>12:00 – 15:00 Uhr</small></span></label></div>
                                        <div class="funnel-option"><input type="radio" name="fi_erreichbarkeit" id="fi-err-nachmittags" value="Nachmittags (15:00 – 18:00 Uhr)"><label class="funnel-option-label" for="fi-err-nachmittags"><span class="funnel-option-icon"><i class="fas fa-moon"></i></span><span class="funnel-option-text">Nachmittags<small>15:00 – 18:00 Uhr</small></span></label></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="funnel-split-block-title"><i class="fas fa-comments" style="margin-right:0.35rem;"></i>Kontakt bevorzugt per</div>
                                    <div class="funnel-option-grid cols-1">
                                        <div class="funnel-option"><input type="radio" name="fi_kontaktweg" id="fi-kw-telefon" value="Telefon" checked><label class="funnel-option-label" for="fi-kw-telefon"><span class="funnel-option-icon"><i class="fas fa-phone"></i></span><span class="funnel-option-text">Telefon</span></label></div>
                                        <div class="funnel-option"><input type="radio" name="fi_kontaktweg" id="fi-kw-whatsapp" value="WhatsApp"><label class="funnel-option-label" for="fi-kw-whatsapp"><span class="funnel-option-icon"><i class="fab fa-whatsapp"></i></span><span class="funnel-option-text">WhatsApp</span></label></div>
                                        <div class="funnel-option"><input type="radio" name="fi_kontaktweg" id="fi-kw-email" value="E-Mail"><label class="funnel-option-label" for="fi-kw-email"><span class="funnel-option-icon"><i class="fas fa-envelope"></i></span><span class="funnel-option-text">E-Mail</span></label></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SCHRITT 6 -->
                        <div class="funnel-step" id="fi-step-6">
                            <div class="funnel-step-title">Wo befindet sich das Objekt?</div>
                            <div class="funnel-step-desc">So können wir prüfen, ob Sie in unserem Einsatzgebiet liegen.</div>
                            <div class="funnel-field-group">
                                <div class="funnel-field-row">
                                    <div class="funnel-field">
                                        <label class="funnel-label" for="fi-plz">Postleitzahl <span>*</span></label>
                                        <input type="text" id="fi-plz" class="funnel-input" placeholder="z. B. 90402" maxlength="5" inputmode="numeric">
                                    </div>
                                    <div class="funnel-field">
                                        <label class="funnel-label" for="fi-ort">Ort</label>
                                        <input type="text" id="fi-ort" class="funnel-input" placeholder="z. B. Nürnberg">
                                    </div>
                                </div>
                                <div class="funnel-field">
                                    <label class="funnel-label" for="fi-strasse">Straße &amp; Hausnummer <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
                                    <input type="text" id="fi-strasse" class="funnel-input" placeholder="z. B. Musterstraße 12">
                                </div>
                            </div>
                        </div>

                        <!-- SCHRITT 7 -->
                        <div class="funnel-step" id="fi-step-7">
                            <div class="funnel-step-title">Weitere Details zu Ihrem Vorhaben</div>
                            <div class="funnel-step-desc">Optional: Beschreiben Sie kurz Ihre Situation oder laden Sie Fotos hoch.</div>
                            <div class="funnel-field-group">
                                <div class="funnel-field">
                                    <label class="funnel-label" for="fi-details">Ihre Beschreibung <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
                                    <textarea id="fi-details" class="funnel-textarea" placeholder="z. B. Altbau aus den 70ern, Sicherungskasten veraltet, ca. 4 Zimmer…"></textarea>
                                </div>
                                <div class="funnel-field">
                                    <label class="funnel-label">Fotos hochladen <small style="font-weight:400;color:var(--text-muted)">(optional, max. 5 MB)</small></label>
                                    <label class="funnel-upload-label">
                                        <i class="fas fa-camera"></i>
                                        <span>Fotos hier ablegen oder klicken</span>
                                        <small>JPG, PNG, HEIC – max. 5 MB je Datei</small>
                                        <input type="file" id="fi-photos" multiple accept="image/jpeg,image/png,image/heic,image/webp">
                                    </label>
                                    <div class="funnel-upload-name" id="fi-photo-name"></div>
                                </div>
                            </div>
                        </div>

                        <!-- SCHRITT 8 -->
                        <div class="funnel-step" id="fi-step-8">
                            <div class="funnel-step-title">Fast geschafft! Ihre Kontaktdaten</div>
                            <div class="funnel-step-desc">Damit wir Ihnen Ihr kostenloses Angebot zusenden können.</div>
                            <div class="funnel-field-group">
                                <div class="funnel-field-row">
                                    <div class="funnel-field">
                                        <label class="funnel-label" for="fi-vorname">Vorname <span>*</span></label>
                                        <input type="text" id="fi-vorname" class="funnel-input" placeholder="Max" autocomplete="given-name">
                                    </div>
                                    <div class="funnel-field">
                                        <label class="funnel-label" for="fi-nachname">Nachname <span>*</span></label>
                                        <input type="text" id="fi-nachname" class="funnel-input" placeholder="Mustermann" autocomplete="family-name">
                                    </div>
                                </div>
                                <div class="funnel-field">
                                    <label class="funnel-label" for="fi-email">E-Mail-Adresse <span>*</span></label>
                                    <input type="email" id="fi-email" class="funnel-input" placeholder="max@beispiel.de" autocomplete="email">
                                </div>
                                <div class="funnel-field">
                                    <label class="funnel-label" for="fi-tel">Telefonnummer <span>*</span></label>
                                    <input type="tel" id="fi-tel" class="funnel-input" placeholder="+49 176 12345678" autocomplete="tel">
                                </div>
                                <div class="funnel-dsgvo">
                                    <input type="checkbox" id="fi-dsgvo">
                                    <label for="fi-dsgvo">Ich stimme der <a href="datenschutz.php" target="_blank">Datenschutzerklärung</a> zu und bin damit einverstanden, dass meine Angaben zur Bearbeitung meiner Anfrage verwendet werden. *</label>
                                </div>
                            </div>
                            <div style="display:none;" aria-hidden="true"><input type="text" id="fi-website" tabindex="-1" autocomplete="off"></div>
                        </div>

                        <!-- Error -->
                        <div class="funnel-error-msg" id="fi-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span></span>
                        </div>

                    </div><!-- /funnel-body -->

                    <!-- Nav -->
                    <div class="funnel-footer" style="border-radius:12px;margin-top:1.5rem;">
                        <button class="funnel-btn funnel-btn--prev" id="fi-prev" style="visibility:hidden;">
                            <i class="fas fa-arrow-left"></i> Zurück
                        </button>
                        <div class="funnel-dots">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <div class="funnel-dot <?php echo $i === 1 ? 'active' : ''; ?>" id="fi-dot-<?php echo $i; ?>"></div>
                            <?php endfor; ?>
                        </div>
                        <button class="funnel-btn funnel-btn--next" id="fi-next">Weiter <i class="fas fa-arrow-right"></i></button>
                        <button class="funnel-btn funnel-btn--submit" id="fi-submit" style="display:none;"><i class="fas fa-paper-plane"></i> Angebot anfordern</button>
                    </div>

                </div><!-- /funnel-inline -->
            </div>

        </div>
    </div>
</section>

<!-- Weitere Kontaktwege -->
<section class="section section--gray">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label">Direktkontakt</span>
            <h2 class="section-title">So erreichen <span>Sie uns</span></h2>
        </div>
        <div class="grid-3">
            <div class="leistung-card" data-animate style="text-align: center;">
                <div class="leistung-icon" style="margin: 0 auto 1.5rem;"><i class="fas fa-phone"></i></div>
                <h3>Telefon</h3>
                <p>Der direkteste Weg für dringende Anfragen oder wenn Sie lieber persönlich sprechen möchten.</p>
                <a href="tel:+491757481006" class="btn btn--primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-phone"></i> +49 175 7481006
                </a>
            </div>
            <div class="leistung-card" data-animate style="text-align: center;">
                <div class="leistung-icon" style="margin: 0 auto 1.5rem;"><i class="fas fa-envelope"></i></div>
                <h3>E-Mail</h3>
                <p>Für detaillierte Anfragen mit Plänen, Fotos oder Dokumenten. Antwort innerhalb von 24 Stunden.</p>
                <a href="mailto:oh.Haustechnik@gmail.com" class="btn btn--primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-envelope"></i> E-Mail schreiben
                </a>
            </div>
            <div class="leistung-card" data-animate style="text-align: center;">
                <div class="leistung-icon" style="margin: 0 auto 1.5rem;"><i class="fas fa-file-alt"></i></div>
                <h3>Anfrage-Formular</h3>
                <p>Nutzen Sie unser Kontaktformular und beschreiben Sie Ihr Vorhaben direkt – schnell und unkompliziert.</p>
                <a href="#funnel-inline" class="btn btn--primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-paper-plane"></i> Zum Formular
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
