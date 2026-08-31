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
                    <input type="radio" name="kategorie" id="kat-smarthome" value="smarthome">
                    <label class="funnel-option-label" for="kat-smarthome">
                        <span class="funnel-option-icon"><i class="fas fa-house-signal"></i></span>
                        <span class="funnel-option-text">
                            Smart Home
                            <small>KNX, Loxone, Licht &amp; Beschattung</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-pv" value="pv">
                    <label class="funnel-option-label" for="kat-pv">
                        <span class="funnel-option-icon"><i class="fas fa-solar-panel"></i></span>
                        <span class="funnel-option-text">
                            Photovoltaik
                            <small>Module, Wechselrichter, Speicher</small>
                        </span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="kategorie" id="kat-wallbox" value="wallbox">
                    <label class="funnel-option-label" for="kat-wallbox">
                        <span class="funnel-option-icon"><i class="fas fa-charging-station"></i></span>
                        <span class="funnel-option-text">
                            Wallbox
                            <small>Ladepunkt f&uuml;rs E-Auto</small>
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

            <!-- Sub-Options: Smart Home -->
            <div class="funnel-suboptions" id="sub-smarthome">
                <div class="funnel-suboptions-title">Welches System schwebt Ihnen vor?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="sh_system" id="sh_system-0" value="KNX">
                        <label class="funnel-option-label" for="sh_system-0">
                            <span class="funnel-option-text">KNX<small>Herstellerübergreifender Standard</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="sh_system" id="sh_system-1" value="Loxone">
                        <label class="funnel-option-label" for="sh_system-1">
                            <span class="funnel-option-text">Loxone<small>Ein Anbieter, schneller eingerichtet</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="sh_system" id="sh_system-2" value="Noch offen">
                        <label class="funnel-option-label" for="sh_system-2">
                            <span class="funnel-option-text">Noch offen<small>Wir beraten Sie dazu</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Neubau oder Nachr&uuml;stung?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="sh_bau" id="sh_bau-0" value="Neubau / Rohbau">
                        <label class="funnel-option-label" for="sh_bau-0">
                            <span class="funnel-option-text">Neubau oder Rohbau<small>Leitungen noch offen</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="sh_bau" id="sh_bau-1" value="Nachrüstung im Bestand">
                        <label class="funnel-option-label" for="sh_bau-1">
                            <span class="funnel-option-text">Nachr&uuml;stung im Bestand<small>Bewohnt, Leitungen liegen</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Was soll gesteuert werden?</div>
                <div class="funnel-checkbox-list">
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Licht"> Licht</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Beschattung / Rollläden"> Beschattung / Rollläden</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Heizung"> Heizung</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Sicherheit / Zutritt"> Sicherheit / Zutritt</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Musik / Multimedia"> Musik / Multimedia</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Energie / Verbrauch"> Energie / Verbrauch</label>
                    <label class="funnel-checkbox-item"><input type="checkbox" value="Noch unklar"> Noch unklar</label>
                </div>
            </div>

            <!-- Sub-Options: Photovoltaik -->
            <div class="funnel-suboptions" id="sub-pv">
                <div class="funnel-suboptions-title">Was f&uuml;r ein Dach haben Sie?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="pv_dach" id="pv_dach-0" value="Schrägdach Ziegel">
                        <label class="funnel-option-label" for="pv_dach-0">
                            <span class="funnel-option-text">Schr&auml;gdach mit Ziegeln<small>Der h&auml;ufigste Fall</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_dach" id="pv_dach-1" value="Schrägdach Blech">
                        <label class="funnel-option-label" for="pv_dach-1">
                            <span class="funnel-option-text">Schr&auml;gdach mit Blech<small>Trapez- oder Wellblech</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_dach" id="pv_dach-2" value="Flachdach">
                        <label class="funnel-option-label" for="pv_dach-2">
                            <span class="funnel-option-text">Flachdach<small>Aufst&auml;nderung n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_dach" id="pv_dach-3" value="Weiß ich nicht">
                        <label class="funnel-option-label" for="pv_dach-3">
                            <span class="funnel-option-text">Wei&szlig; ich nicht<small>Wir schauen es uns an</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Mit Batteriespeicher?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="pv_speicher" id="pv_speicher-0" value="Ja, mit Speicher">
                        <label class="funnel-option-label" for="pv_speicher-0">
                            <span class="funnel-option-text">Ja, mit Speicher<small>Strom auch abends nutzen</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_speicher" id="pv_speicher-1" value="Nein, ohne Speicher">
                        <label class="funnel-option-label" for="pv_speicher-1">
                            <span class="funnel-option-text">Nein, ohne Speicher<small>G&uuml;nstiger im Einstieg</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_speicher" id="pv_speicher-2" value="Bitte beraten">
                        <label class="funnel-option-label" for="pv_speicher-2">
                            <span class="funnel-option-text">Bitte beraten<small>Wir rechnen es durch</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Wie alt ist Ihr Z&auml;hlerschrank?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="pv_zaehler" id="pv_zaehler-0" value="Neu, unter 10 Jahre">
                        <label class="funnel-option-label" for="pv_zaehler-0">
                            <span class="funnel-option-text">Neu, unter 10 Jahre<small>Vermutlich kein Umbau n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_zaehler" id="pv_zaehler-1" value="&Auml;lter als 10 Jahre">
                        <label class="funnel-option-label" for="pv_zaehler-1">
                            <span class="funnel-option-text">&Auml;lter als 10 Jahre<small>Umbau m&ouml;glicherweise n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="pv_zaehler" id="pv_zaehler-2" value="Sehr alt oder unklar">
                        <label class="funnel-option-label" for="pv_zaehler-2">
                            <span class="funnel-option-text">Sehr alt oder unklar<small>Wir schauen es uns an</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="pv-flaeche">Ungef&auml;hre Dachfl&auml;che in m&sup2; (optional)</label>
                    <input type="text" id="pv-flaeche" name="pv_flaeche" class="funnel-input"
                           placeholder="z. B. 60" inputmode="numeric">
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="pv-verbrauch">Stromverbrauch pro Jahr in kWh (optional)</label>
                    <input type="text" id="pv-verbrauch" name="pv_verbrauch" class="funnel-input"
                           placeholder="z. B. 4500 – steht auf Ihrer Abrechnung" inputmode="numeric">
                </div>
            </div>

            <!-- Sub-Options: Wallbox -->
            <div class="funnel-suboptions" id="sub-wallbox">
                <div class="funnel-suboptions-title">Welche Ladeleistung?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="wb_leistung" id="wb_leistung-0" value="11 kW">
                        <label class="funnel-option-label" for="wb_leistung-0">
                            <span class="funnel-option-text">11 kW<small>Nur Anmeldung n&ouml;tig, f&uuml;r die meisten ausreichend</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_leistung" id="wb_leistung-1" value="22 kW">
                        <label class="funnel-option-label" for="wb_leistung-1">
                            <span class="funnel-option-text">22 kW<small>Genehmigung durch den Netzbetreiber n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_leistung" id="wb_leistung-2" value="Bitte beraten">
                        <label class="funnel-option-label" for="wb_leistung-2">
                            <span class="funnel-option-text">Bitte beraten<small>Wir empfehlen das Passende</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Wo soll die Wallbox hin?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="wb_ort" id="wb_ort-0" value="Garage">
                        <label class="funnel-option-label" for="wb_ort-0">
                            <span class="funnel-option-text">Garage</span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_ort" id="wb_ort-1" value="Carport">
                        <label class="funnel-option-label" for="wb_ort-1">
                            <span class="funnel-option-text">Carport</span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_ort" id="wb_ort-2" value="Außenwand / Stellplatz">
                        <label class="funnel-option-label" for="wb_ort-2">
                            <span class="funnel-option-text">Au&szlig;enwand oder Stellplatz</span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_ort" id="wb_ort-3" value="Tiefgarage">
                        <label class="funnel-option-label" for="wb_ort-3">
                            <span class="funnel-option-text">Tiefgarage<small>Oft Abstimmung mit der Verwaltung n&ouml;tig</small></span>
                        </label>
                    </div>
                </div>
                <div class="funnel-suboptions-title" style="margin-top:1rem;">Wie alt ist Ihr Z&auml;hlerschrank?</div>
                <div class="funnel-option-grid">
                    <div class="funnel-option">
                        <input type="radio" name="wb_zaehler" id="wb_zaehler-0" value="Neu, unter 10 Jahre">
                        <label class="funnel-option-label" for="wb_zaehler-0">
                            <span class="funnel-option-text">Neu, unter 10 Jahre<small>Vermutlich kein Umbau n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_zaehler" id="wb_zaehler-1" value="&Auml;lter als 10 Jahre">
                        <label class="funnel-option-label" for="wb_zaehler-1">
                            <span class="funnel-option-text">&Auml;lter als 10 Jahre<small>Umbau m&ouml;glicherweise n&ouml;tig</small></span>
                        </label>
                    </div>
                    <div class="funnel-option">
                        <input type="radio" name="wb_zaehler" id="wb_zaehler-2" value="Sehr alt oder unklar">
                        <label class="funnel-option-label" for="wb_zaehler-2">
                            <span class="funnel-option-text">Sehr alt oder unklar<small>Wir schauen es uns an</small></span>
                        </label>
                    </div>
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

        <!-- ── SCHRITT 3: Objekt (Typ, Zimmer, Fläche) ── -->
        <div class="funnel-step" id="funnel-step-3">
            <div class="funnel-step-title">Angaben zum Objekt</div>
            <div class="funnel-step-desc">Damit wir Ihren Festpreis genau kalkulieren können.</div>

            <!-- Objekttyp -->
            <div class="funnel-split-block-title"><i class="fas fa-building-user" style="margin-right:0.35rem;"></i>Um was für ein Objekt geht es?</div>
            <div class="funnel-option-grid">
                <div class="funnel-option">
                    <input type="radio" name="objekttyp" id="otyp-wohnung" value="Wohnung">
                    <label class="funnel-option-label" for="otyp-wohnung">
                        <span class="funnel-option-icon"><i class="fas fa-door-closed"></i></span>
                        <span class="funnel-option-text">Wohnung <small>Etagen- / Eigentumswohnung</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objekttyp" id="otyp-haus" value="Haus">
                    <label class="funnel-option-label" for="otyp-haus">
                        <span class="funnel-option-icon"><i class="fas fa-house"></i></span>
                        <span class="funnel-option-text">Haus <small>Einfamilien- / Reihenhaus</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objekttyp" id="otyp-gewerbe" value="Gewerbe">
                    <label class="funnel-option-label" for="otyp-gewerbe">
                        <span class="funnel-option-icon"><i class="fas fa-store"></i></span>
                        <span class="funnel-option-text">Gewerbe <small>Büro, Laden, Praxis</small></span>
                    </label>
                </div>
                <div class="funnel-option">
                    <input type="radio" name="objekttyp" id="otyp-mehrere" value="Mehrere Objekte">
                    <label class="funnel-option-label" for="otyp-mehrere">
                        <span class="funnel-option-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="funnel-option-text">Mehrere Objekte <small>z. B. mehrere Wohnungen / Haus + Wohnung</small></span>
                    </label>
                </div>
            </div>

            <!-- Anzahl Zimmer -->
            <div class="funnel-count-input-wrap">
                <label for="zimmer-anzahl">Anzahl Zimmer <small style="font-weight:400;color:var(--text-muted)">(optional, je Objekt)</small></label>
                <input type="number" id="zimmer-anzahl" name="zimmer_anzahl" min="1" max="99" placeholder="z. B. 3">
            </div>

            <!-- Fläche -->
            <div class="funnel-split-block-title" style="margin-top:1.1rem;"><i class="fas fa-ruler-combined" style="margin-right:0.35rem;"></i>Ungefähre Wohn- oder Nutzfläche</div>
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
                                <span class="funnel-option-icon" style="font-size:0.9rem;"><svg class="ic-wa" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 110.9L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg></span>
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
        <!-- ── SCHRITT 7: Kontaktdaten + optionale Details (zusammengelegt) ── -->
        <div class="funnel-step" id="funnel-step-7">
            <div class="funnel-step-title">Fast geschafft! Ihre Kontaktdaten</div>
            <div class="funnel-step-desc">Damit wir Ihnen Ihr kostenloses Angebot zusenden können. Beschreibung und Fotos sind optional.</div>
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
                    <label class="funnel-label" for="funnel-tel">Telefonnummer <span>*</span></label>
                    <input type="tel" id="funnel-tel" name="telefon" class="funnel-input"
                           placeholder="+49 176 12345678" autocomplete="tel">
                </div>
                <div class="funnel-field">
                    <label class="funnel-label" for="funnel-email">E-Mail-Adresse <span>*</span></label>
                    <input type="email" id="funnel-email" name="email" class="funnel-input"
                           placeholder="max@beispiel.de" autocomplete="email">
                </div>
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
            <?php for ($i = 1; $i <= 7; $i++): ?>
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
