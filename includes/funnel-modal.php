<?php /* OH Haustechnik – Multi-Step Funnel Modal (Partial) */ ?>
<!-- ============================================================
     MULTI-STEP FUNNEL MODAL
============================================================ -->
<div class="funnel-overlay" id="funnel-overlay" role="dialog" aria-modal="true" aria-labelledby="funnel-title">
<div class="funnel-modal" id="funnel-modal">

    <!-- HEADER -->
    <div class="funnel-header">
        <div class="funnel-header-top">
            <div>
                <div class="funnel-header-title" id="funnel-title">
                    <i class="fas fa-bolt" style="margin-right:0.4rem;"></i>
                    Kostenloses Angebot anfordern
                </div>
                <div class="funnel-header-sub">Nur 2 Minuten · Keine Kosten · Schnelle Rückmeldung</div>
            
				<div class="funnel-privacy-note">
🔒 Ihre Daten bleiben vertraulich
</div>
			
			</div>
            <button class="funnel-close" id="funnel-close" aria-label="Schließen">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Progress Bar -->
        <div class="funnel-progress">
            <div class="funnel-progress-bar-wrap">
                <div class="funnel-progress-bar-fill" id="funnel-progress-fill" style="width:0%"></div>
            </div>
            <span class="funnel-progress-label" id="funnel-progress-label">Schritt 1 von 8</span>
        </div>
    </div>

    <!-- BODY -->
    <div class="funnel-body">

        <!-- ── SCHRITT 1: Kategorie ── -->
        <div class="funnel-step active" id="funnel-step-1">
            <div class="funnel-step-title">Was können wir für Sie tun?</div>
            <div class="funnel-step-desc">Wählen Sie die passende Leistungskategorie aus.</div>

            <div class="funnel-option-grid">
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-elektro" value="elektro">
                    <label class="funnel-option-label" for="kat-elektro">
                        <span class="funnel-option-icon"><i class="fas fa-plug"></i></span>
                        <span class="funnel-option-text">
                            Elektroinstallation &amp; Sanierung
                            <small>Neubau, Altbau, Erweiterung</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-netzwerk" value="netzwerk">
                    <label class="funnel-option-label" for="kat-netzwerk">
                        <span class="funnel-option-icon"><i class="fas fa-network-wired"></i></span>
                        <span class="funnel-option-text">
                            Netzwerkverkabelung
                            <small>LAN, Datendosen, Patchpanel</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-fehler" value="fehler">
                    <label class="funnel-option-label" for="kat-fehler">
                        <span class="funnel-option-icon"><i class="fas fa-search"></i></span>
                        <span class="funnel-option-text">
                            Fehlersuche &amp; Reparatur
                            <small>FI-Schalter, Ausfall, Kurzschluss</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-lampen" value="lampen">
                    <label class="funnel-option-label" for="kat-lampen">
                        <span class="funnel-option-icon"><i class="fas fa-lightbulb"></i></span>
                        <span class="funnel-option-text">
                            Lampen &amp; Leuchtenmontage
                            <small>Deckenlampen, Spots, LED</small>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Sub-Options: Elektro -->
            <div class="funnel-suboptions" id="sub-elektro">
                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was soll gemacht werden?</div>
                <div class="funnel-checkbox-list">
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Neue Steckdosen / Schalter"> Neue Steckdosen / Schalter</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Unterverteilung / Sicherungskasten"> Unterverteilung / Sicherungskasten</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Leitungen verlegen"> Leitungen verlegen</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Elektro-Komplettsanierung"> Elektro-Komplettsanierung</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Außenbereich / Garten"> Außenbereich / Garten</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="E-Auto Ladestation"> E-Auto Ladestation</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Smart Home / KNX"> Smart Home / KNX</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges Elektro"> Sonstiges</label>
                </div>
            </div>

            <!-- Sub-Options: Netzwerk -->
            <div class="funnel-suboptions" id="sub-netzwerk">
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
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges Netzwerk"> Sonstiges</label>
                </div>
            </div>

            <!-- Sub-Options: Fehlersuche -->
            <div class="funnel-suboptions" id="sub-fehler">
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

            <!-- Sub-Options: Lampen -->
            <div class="funnel-suboptions" id="sub-lampen">
                <div class="funnel-suboptions-title"><i class="fas fa-list" style="margin-right:0.35rem;"></i>Was soll montiert werden?</div>
                <div class="funnel-checkbox-list">
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Deckenlampen montieren"> Deckenlampen montieren</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Einbauspots / LED-Panel"> Einbauspots / LED-Panel</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Außenleuchten"> Außenleuchten</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Spiegelleuchten Bad"> Spiegelleuchten Bad</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Pendel- / Hängelampen"> Pendel- / Hängelampen</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sonstiges Lampen"> Sonstiges</label>
                </div>
                <div class="funnel-count-input-wrap">
                    <label for="lampen-anzahl">Ungefähre Anzahl der Lampen:</label>
                    <input type="number" id="lampen-anzahl" name="lampen_anzahl" min="1" max="999" placeholder="z. B. 5">
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 2: Elektro-Typ (nur bei Elektroinstallation) ── -->
        <div class="funnel-step" id="funnel-step-2">
            <div class="funnel-step-title">Um welche Art von Elektroarbeit handelt es sich?</div>
            <div class="funnel-step-desc">Wählen Sie den passenden Bereich für Ihre Elektroinstallation.</div>
            <div class="funnel-option-grid cols-1">
                <div class="funnel-option">
                    <input type="radio" name="elektro_typ" id="etyp-altbau" value="Altbausanierung">
                    <label class="funnel-option-label" for="etyp-altbau">
                        <span class="funnel-option-icon"><i class="fas fa-house-damage"></i></span>
                        <span class="funnel-option-text">
                            Altbausanierung
                            <small>Erneuerung veralteter Elektroinstallationen</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="elektro_typ" id="etyp-neubau" value="Neubau">
                    <label class="funnel-option-label" for="etyp-neubau">
                        <span class="funnel-option-icon"><i class="fas fa-building"></i></span>
                        <span class="funnel-option-text">
                            Neubau
                            <small>Komplette Elektroinstallation im Neubau</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="elektro_typ" id="etyp-erweiterung" value="Erweiterung">
                    <label class="funnel-option-label" for="etyp-erweiterung">
                        <span class="funnel-option-icon"><i class="fas fa-expand-arrows-alt"></i></span>
                        <span class="funnel-option-text">
                            Erweiterung
                            <small>Zusätzliche Stromkreise, Steckdosen, Leitungen</small>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 3: Objektgröße ── -->
        <div class="funnel-step" id="funnel-step-3">
            <div class="funnel-step-title">Wie groß ist das Objekt?</div>
            <div class="funnel-step-desc">Wählen Sie die ungefähre Wohn- oder Nutzfläche.</div>
            <div class="funnel-option-grid cols-1">
                <div class="funnel-option">
                    <input type="radio" name="objektgroesse" id="obj-50" value="Bis 50 m²">
                    <label class="funnel-option-label" for="obj-50">
                        <span class="funnel-option-icon"><i class="fas fa-home"></i></span>
                        <span class="funnel-option-text">Bis 50 m² <small>Kleine Wohnung / Studio</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objektgroesse" id="obj-100" value="50 – 100 m²">
                    <label class="funnel-option-label" for="obj-100">
                        <span class="funnel-option-icon"><i class="fas fa-home"></i></span>
                        <span class="funnel-option-text">50 – 100 m² <small>Mittelgroße Wohnung / Haus</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objektgroesse" id="obj-150" value="100 – 150 m²">
                    <label class="funnel-option-label" for="obj-150">
                        <span class="funnel-option-icon"><i class="fas fa-building"></i></span>
                        <span class="funnel-option-text">100 – 150 m² <small>Großes Haus / Gewerbe klein</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objektgroesse" id="obj-200" value="150 – 200 m²">
                    <label class="funnel-option-label" for="obj-200">
                        <span class="funnel-option-icon"><i class="fas fa-building"></i></span>
                        <span class="funnel-option-text">150 – 200 m² <small>Großes Haus / Gewerbe mittel</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objektgroesse" id="obj-over200" value="Über 200 m²">
                    <label class="funnel-option-label" for="obj-over200">
                        <span class="funnel-option-icon"><i class="fas fa-industry"></i></span>
                        <span class="funnel-option-text">Über 200 m² <small>Großes Gewerbe / Neubau</small></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 4: Ausführungszeit ── -->
        <div class="funnel-step" id="funnel-step-4">
            <div class="funnel-step-title">Wann soll die Arbeit erledigt werden?</div>
            <div class="funnel-step-desc">Ihr gewünschter Ausführungszeitraum.</div>
            <div class="funnel-option-grid cols-1">
                <div class="funnel-option">
                    <input type="radio" name="ausfuehrungszeit" id="zeit-dringend" value="So schnell wie möglich (dringend)">
                    <label class="funnel-option-label" for="zeit-dringend">
                        <span class="funnel-option-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="funnel-option-text">So schnell wie möglich <small>Dringende Situation</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="ausfuehrungszeit" id="zeit-2wochen" value="Innerhalb der nächsten 2 Wochen">
                    <label class="funnel-option-label" for="zeit-2wochen">
                        <span class="funnel-option-icon"><i class="fas fa-calendar-week"></i></span>
                        <span class="funnel-option-text">Innerhalb der nächsten 2 Wochen <small>Zeitnah, aber nicht sofort</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="ausfuehrungszeit" id="zeit-monat" value="Im nächsten Monat">
                    <label class="funnel-option-label" for="zeit-monat">
                        <span class="funnel-option-icon"><i class="fas fa-calendar-alt"></i></span>
                        <span class="funnel-option-text">Im nächsten Monat <small>Genug Zeit zum Planen</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="ausfuehrungszeit" id="zeit-flexibel" value="Flexibel – kein festes Datum">
                    <label class="funnel-option-label" for="zeit-flexibel">
                        <span class="funnel-option-icon"><i class="fas fa-infinity"></i></span>
                        <span class="funnel-option-text">Flexibel – kein festes Datum <small>Wir stimmen uns ab</small></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 5: Erreichbarkeit ── -->
        <div class="funnel-step" id="funnel-step-5">
            <div class="funnel-step-title">Wann sind Sie erreichbar?</div>
            <div class="funnel-step-desc">Und wie sollen wir uns bei Ihnen melden?</div>
            <div class="funnel-split">
                <div>
                    <div class="funnel-split-block-title"><i class="fas fa-clock" style="margin-right:0.35rem;"></i>Bevorzugtes Zeitfenster</div>
                    <div class="funnel-option-grid cols-1">
                        <div class="funnel-option">
                            <input type="radio" name="erreichbarkeit" id="err-morgens" value="Morgens (07:30 – 12:00 Uhr)">
                            <label class="funnel-option-label" for="err-morgens">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fas fa-sun"></i></span>
                                <span class="funnel-option-text">Morgens <small>07:30 – 12:00 Uhr</small></span>
                            </label>
                        </div>
                        <div class="funnel-option">
                            <input type="radio" name="erreichbarkeit" id="err-mittags" value="Mittags (12:00 – 15:00 Uhr)">
                            <label class="funnel-option-label" for="err-mittags">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fas fa-cloud-sun"></i></span>
                                <span class="funnel-option-text">Mittags <small>12:00 – 15:00 Uhr</small></span>
                            </label>
                        </div>
                        <div class="funnel-option">
                            <input type="radio" name="erreichbarkeit" id="err-nachmittags" value="Nachmittags (15:00 – 18:00 Uhr)">
                            <label class="funnel-option-label" for="err-nachmittags">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fas fa-moon"></i></span>
                                <span class="funnel-option-text">Nachmittags <small>15:00 – 18:00 Uhr</small></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="funnel-split-block-title"><i class="fas fa-comments" style="margin-right:0.35rem;"></i>Kontakt bevorzugt per</div>
                    <div class="funnel-option-grid cols-1">
                        <div class="funnel-option">
                            <input type="radio" name="kontaktweg" id="kw-telefon" value="Telefon" checked>
                            <label class="funnel-option-label" for="kw-telefon">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fas fa-phone"></i></span>
                                <span class="funnel-option-text">Telefon</span>
                            </label>
                        </div>
                        <div class="funnel-option">
                            <input type="radio" name="kontaktweg" id="kw-whatsapp" value="WhatsApp">
                            <label class="funnel-option-label" for="kw-whatsapp">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fab fa-whatsapp"></i></span>
                                <span class="funnel-option-text">WhatsApp</span>
                            </label>
                        </div>
                        <div class="funnel-option">
                            <input type="radio" name="kontaktweg" id="kw-email" value="E-Mail">
                            <label class="funnel-option-label" for="kw-email">
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><i class="fas fa-envelope"></i></span>
                                <span class="funnel-option-text">E-Mail</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 6: Standort ── -->
        <div class="funnel-step" id="funnel-step-6">
            <div class="funnel-step-title">Wo befindet sich das Objekt?</div>
            <div class="funnel-step-desc">So können wir prüfen, ob Sie in unserem Einsatzgebiet liegen.</div>
            <div class="funnel-field-group">
                <div class="funnel-field-row">
                    <div class="funnel-field">
                        <label class="funnel-label" for="funnel-plz">Postleitzahl <span>*</span></label>
                        <input type="text" id="funnel-plz" name="plz" class="funnel-input"
                               placeholder="z. B. 90402" maxlength="5" inputmode="numeric">
                    </div>
                    <div class="funnel-field">
                        <label class="funnel-label" for="funnel-ort">Ort</label>
                        <input type="text" id="funnel-ort" name="ort" class="funnel-input"
                               placeholder="z. B. Nürnberg">
                    </div>
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="funnel-strasse">Straße &amp; Hausnummer <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
                    <input type="text" id="funnel-strasse" name="strasse" class="funnel-input"
                           placeholder="z. B. Musterstraße 12">
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 7: Details ── -->
        <div class="funnel-step" id="funnel-step-7">
            <div class="funnel-step-title">Weitere Details zu Ihrem Vorhaben</div>
            <div class="funnel-step-desc">Optional: Beschreiben Sie kurz Ihre Situation oder laden Sie Fotos hoch.</div>
            <div class="funnel-field-group">
                <div class="funnel-field">
                    <label class="funnel-label" for="funnel-details">Ihre Beschreibung <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
                    <textarea id="funnel-details" name="details" class="funnel-textarea"
                              placeholder="z. B. Altbau aus den 70ern, Sicherungskasten veraltet, ca. 4 Zimmer…"></textarea>
                </div>
                <div class="funnel-field">
                    <label class="funnel-label">Fotos hochladen <small style="font-weight:400;color:var(--text-muted)">(optional, max. 5 MB)</small></label>
                    <label class="funnel-upload-label">
                        <i class="fas fa-camera"></i>
                        <span>Fotos hier ablegen oder klicken</span>
                        <small>JPG, PNG, HEIC – max. 5 MB je Datei</small>
                        <input type="file" id="funnel-photos" name="fotos[]" multiple
                               accept="image/jpeg,image/png,image/heic,image/webp">
                    </label>
                    <div class="funnel-upload-name" id="funnel-photo-name"></div>
                </div>
            </div>
        </div>

        <!-- ── SCHRITT 8: Kontaktdaten ── -->
        <div class="funnel-step" id="funnel-step-8">
            <div class="funnel-step-title">Fast geschafft! Ihre Kontaktdaten</div>
            <div class="funnel-step-desc">Damit wir Ihnen Ihr kostenloses Angebot zusenden können.</div>
            <div class="funnel-field-group">
                <div class="funnel-field-row">
                    <div class="funnel-field">
                        <label class="funnel-label" for="funnel-vorname">Vorname <span>*</span></label>
                        <input type="text" id="funnel-vorname" name="vorname" class="funnel-input"
                               placeholder="Max" autocomplete="given-name">
                    </div>
                    <div class="funnel-field">
                        <label class="funnel-label" for="funnel-nachname">Nachname <span>*</span></label>
                        <input type="text" id="funnel-nachname" name="nachname" class="funnel-input"
                               placeholder="Mustermann" autocomplete="family-name">
                    </div>
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="funnel-email">E-Mail-Adresse <span>*</span></label>
                    <input type="email" id="funnel-email" name="email" class="funnel-input"
                           placeholder="max@beispiel.de" autocomplete="email">
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="funnel-tel">Telefonnummer <span>*</span></label>
                    <input type="tel" id="funnel-tel" name="telefon" class="funnel-input"
                           placeholder="+49 176 12345678" autocomplete="tel">
                </div>
                <div class="funnel-dsgvo">
                    <input type="checkbox" id="funnel-dsgvo" name="datenschutz">
                    <label for="funnel-dsgvo">
                        Ich stimme der <a href="datenschutz.php" target="_blank">Datenschutzerklärung</a> zu und bin damit einverstanden,
                        dass meine Angaben zur Bearbeitung meiner Anfrage verwendet werden. *
                    </label>
                </div>
            </div>
            <!-- Honeypot -->
            <div style="display:none;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>
        </div>

        <!-- Error message -->
        <div class="funnel-error-msg" id="funnel-error">
            <i class="fas fa-exclamation-circle"></i>
            <span></span>
        </div>

    </div><!-- /funnel-body -->

    <!-- FOOTER NAV -->
    <div class="funnel-footer">
        <button class="funnel-btn funnel-btn--prev" id="funnel-prev" style="visibility:hidden;">
            <i class="fas fa-arrow-left"></i> Zurück
        </button>

        <div class="funnel-dots">
            <?php for ($i = 1; $i <= 8; $i++): ?>
            <div class="funnel-dot <?php echo $i === 1 ? 'active' : ''; ?>" data-step="<?php echo $i; ?>"></div>
            <?php endfor; ?>
        </div>

        <button class="funnel-btn funnel-btn--next" id="funnel-next">
            Weiter <i class="fas fa-arrow-right"></i>
        </button>
        <button class="funnel-btn funnel-btn--submit" id="funnel-submit" style="display:none;">
            <i class="fas fa-paper-plane"></i> Angebot anfordern
        </button>
    </div>

</div><!-- /funnel-modal -->
</div><!-- /funnel-overlay -->
