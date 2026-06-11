<?php
$page_title       = 'Leistungen – Elektroinstallation & Netzwerkverkabelung | OH Haustechnik Nürnberg';
$meta_description = 'Alle Leistungen von OH Haustechnik: Elektroinstallation, Netzwerkverkabelung und Schutztechnik im Raum Nürnberg ✓ VDE & DIN-konform ✓ Neubau & Bestand';
$canonical_url    = 'https://oh-haustechnik.de/leistungen.php';

include 'includes/header.php';
?>

<style>
@media (max-width: 768px) {
    .leistung-section-grid {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
    .leistung-section-grid > div[style*="order"] {
        order: unset !important;
    }
    .leistung-section-grid h2 { font-size: 1.5rem !important; }
}
</style>

<section class="page-hero">
    <div class="container">
        <div class="page-hero-content">
            <div class="breadcrumb">
                <a href="index.php">Start</a>
                <i class="fas fa-chevron-right"></i>
                <span>Leistungen</span>
            </div>
            <h1>Unsere Leistungen</h1>
            <p>
                Normgerechte Elektroinstallation, strukturierte Netzwerkverkabelung und 
                moderne Schutztechnik – alles aus einer Hand im Raum Nürnberg.
            </p>
        </div>
    </div>
</section>

<!-- Trust Bar -->
<div class="trust-bar">
    <div class="trust-bar-inner">
        <div class="trust-item"><i class="fas fa-shield-alt"></i><span>VDE-konform</span></div>
        <div class="trust-item"><i class="fas fa-certificate"></i><span>DIN-gerecht</span></div>
        <div class="trust-item"><i class="fas fa-plug"></i><span>TAB-Vorgaben</span></div>
        <div class="trust-item"><i class="fas fa-star"></i><span>Markenkomponenten</span></div>
        <div class="trust-item"><i class="fas fa-file-alt"></i><span>Dokumentiert</span></div>
    </div>
</div>

<!-- Leistungen Detail -->
<section class="section">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label">Fachgerechte Umsetzung</span>
            <h2 class="section-title">Was wir für Sie <span>leisten</span></h2>
            <p class="section-subtitle">
                Jede Leistung wird mit dem gleichen Anspruch an Qualität, Normkonformität und 
                Dokumentation erbracht.
            </p>
        </div>

        <!-- Elektroinstallation -->
        <div class="leistung-section-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; margin-bottom: 6rem;" data-animate>
            <div>
                <div class="leistung-icon" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Elektroinstallation</h2>
                <p style="margin-bottom: 1.25rem;">
                    Vor Beginn der Installation erfolgt eine detaillierte Lasten- und Leistungsberechnung,
                    Stromkreisaufteilung sowie die Auswahl geeigneter Schutzorgane. Ergänzend dazu bieten wir 
                    <a href="leistungen/netzwerkverkabelung.php" class="text-link">strukturierte Netzwerkverkabelung</a> 
                    und <a href="leistungen/schutztechnik.php" class="text-link">moderne Schutztechnik</a> aus einer Hand.
                </p>
                <h4 style="margin-bottom: 1rem; color: var(--blue-primary);">Umsetzung im Neubau</h4>
                <ul class="leistung-list" style="margin-bottom: 1.5rem;">
                    <li><i class="fas fa-circle"></i> Leitungsverlegung nach Installationszonen</li>
                    <li><i class="fas fa-circle"></i> Installation von Haupt- und Unterverteilungen</li>
                    <li><i class="fas fa-circle"></i> Einbau von FI-/RCD- und LS-Schutztechnik</li>
                    <li><i class="fas fa-circle"></i> Trennung von Lastbereichen</li>
                    <li><i class="fas fa-circle"></i> Vorbereitung für E-Mobilität oder Smart-Home</li>
                    <li><i class="fas fa-circle"></i> Strukturierte Beschriftung der Verteiler</li>
                </ul>
                <h4 style="margin-bottom: 1rem; color: var(--blue-primary);">Modernisierung im Bestand</h4>
                <ul class="leistung-list" style="margin-bottom: 2rem;">
                    <li><i class="fas fa-circle"></i> Austausch veralteter Installationen</li>
                    <li><i class="fas fa-circle"></i> Umrüstung auf aktuelle Schutztechnik</li>
                    <li><i class="fas fa-circle"></i> Erweiterung zusätzlicher Stromkreise</li>
                    <li><i class="fas fa-circle"></i> Integration von Überspannungsschutz</li>
                </ul>
                <a href="leistungen/elektroinstallation.php" class="btn btn--primary">
                    <i class="fas fa-bolt"></i> Details zur Elektroinstallation
                </a>
            </div>
            <div style="background: var(--blue-light); border-radius: var(--border-radius-lg); padding: 3rem; border: 1px solid var(--gray-200);">
                <div style="width: 80px; height: 80px; background: var(--blue-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem;">
                    <i class="fas fa-bolt" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Planung & Auslegung</h3>
                <ul class="anspruch-list">
                    <li><i class="fas fa-check"></i> Lasten- und Leistungsberechnung</li>
                    <li><i class="fas fa-check"></i> Stromkreisaufteilung</li>
                    <li><i class="fas fa-check"></i> Absicherungskonzept</li>
                    <li><i class="fas fa-check"></i> Auswahl geeigneter Schutzorgane</li>
                    <li><i class="fas fa-check"></i> Berücksichtigung von Reservekapazitäten</li>
                </ul>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid var(--gray-200); margin-bottom: 6rem;">

        <!-- Netzwerkverkabelung -->
        <div class="leistung-section-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; margin-bottom: 6rem;" data-animate>
            <div style="background: var(--gray-900); border-radius: var(--border-radius-lg); padding: 3rem; order: -1;">
                <div style="width: 80px; height: 80px; background: var(--blue-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem;">
                    <i class="fas fa-network-wired" style="font-size: 2rem; color: white;"></i>
                </div>
                <h3 style="color: white; margin-bottom: 1rem;">Besonderer Fokus:</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.75rem;">
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                        <i class="fas fa-check-circle" style="color: var(--blue-primary);"></i>
                        Geringe Störanfälligkeit
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                        <i class="fas fa-check-circle" style="color: var(--blue-primary);"></i>
                        Klare Leitungsführung
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                        <i class="fas fa-check-circle" style="color: var(--blue-primary);"></i>
                        Nachvollziehbare Dokumentation
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                        <i class="fas fa-check-circle" style="color: var(--blue-primary);"></i>
                        Erweiterbarkeit der Infrastruktur
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                        <i class="fas fa-check-circle" style="color: var(--blue-primary);"></i>
                        Glasfaser-Vorbereitung
                    </li>
                </ul>
            </div>
            <div>
                <div class="leistung-icon" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Netzwerkverkabelung</h2>
                <p style="margin-bottom: 1.25rem;">
                    Moderne Gebäude erfordern eine leistungsfähige Dateninfrastruktur. Wir planen 
                    strukturierte Netzwerksysteme mit klarer Segmentierung und zentralem Verteilerpunkt.
                    Die Netzwerkverkabelung lässt sich ideal mit unserer <a href="leistungen/elektroinstallation.php" class="text-link">Elektroinstallation</a> kombinieren –
                    <a href="kontakt.php" class="text-link">sprechen Sie uns an</a>.
                </p>
                <ul class="leistung-list" style="margin-bottom: 2rem;">
                    <li><i class="fas fa-circle"></i> Strukturierte Verkabelung nach aktuellen Standards</li>
                    <li><i class="fas fa-circle"></i> Installation von Datendosen</li>
                    <li><i class="fas fa-circle"></i> Patchpanel-Montage</li>
                    <li><i class="fas fa-circle"></i> Serverschrank-Installation</li>
                    <li><i class="fas fa-circle"></i> Saubere Kabelführung mit Dokumentation</li>
                    <li><i class="fas fa-circle"></i> Vorbereitung für Glasfaser</li>
                </ul>
                <a href="leistungen/netzwerkverkabelung.php" class="btn btn--primary">
                    <i class="fas fa-network-wired"></i> Details zur Netzwerkverkabelung
                </a>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid var(--gray-200); margin-bottom: 6rem;">

        <!-- Schutztechnik -->
        <div class="leistung-section-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;" data-animate>
            <div>
                <div class="leistung-icon" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Sicherheit & Schutztechnik</h2>
                <p style="margin-bottom: 1.25rem;">
                    Ein moderner Verteiler erfüllt mehr als nur die Grundfunktion. So wird nicht nur 
                    Funktionalität, sondern auch langfristige Sicherheit gewährleistet – besonders 
                    wichtig bei der <a href="leistungen/elektroinstallation.php" class="text-link">Modernisierung im Bestand</a>.
                </p>
                <ul class="leistung-list" style="margin-bottom: 2rem;">
                    <li><i class="fas fa-circle"></i> Fehlerstromschutzschalter (RCD)</li>
                    <li><i class="fas fa-circle"></i> Leitungsschutzschalter (LS)</li>
                    <li><i class="fas fa-circle"></i> Überspannungsschutz</li>
                    <li><i class="fas fa-circle"></i> Separate Stromkreise für leistungsintensive Verbraucher</li>
                    <li><i class="fas fa-circle"></i> Strukturierte Beschriftung</li>
                </ul>
                <a href="leistungen/schutztechnik.php" class="btn btn--primary">
                    <i class="fas fa-shield-alt"></i> Details zur Schutztechnik
                </a>
            </div>
            <div style="background: var(--blue-light); border-radius: var(--border-radius-lg); padding: 3rem; border: 1px solid var(--gray-200);">
                <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem; border-left: 3px solid var(--blue-primary); padding-left: 1rem;">
                    „Besonders in älteren Gebäuden steht die Sicherheit im Vordergrund."
                </p>
                <div class="norm-badges" style="flex-direction: column;">
                    <div class="norm-badge" style="background: var(--white); border-color: var(--gray-200); color: var(--text-primary); border-radius: var(--border-radius);">
                        <i class="fas fa-shield-alt" style="color: var(--blue-primary);"></i> Fehlerstromschutz (RCD)
                    </div>
                    <div class="norm-badge" style="background: var(--white); border-color: var(--gray-200); color: var(--text-primary); border-radius: var(--border-radius);">
                        <i class="fas fa-plug" style="color: var(--blue-primary);"></i> Leitungsschutz (LS)
                    </div>
                    <div class="norm-badge" style="background: var(--white); border-color: var(--gray-200); color: var(--text-primary); border-radius: var(--border-radius);">
                        <i class="fas fa-bolt" style="color: var(--blue-primary);"></i> Überspannungsschutz
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container" style="position: relative; z-index: 2;">
        <h2>Welche Leistung benötigen Sie?</h2>
        <p>Schildern Sie uns kurz Ihr Vorhaben – wir beraten Sie kostenlos und unverbindlich.</p>
        <div class="cta-actions">
            <a href="kontakt.php" class="btn btn--white btn--lg">
                <i class="fas fa-paper-plane"></i> Jetzt anfragen
            </a>
            <a href="tel:+491757481006" class="btn btn--outline-white btn--lg">
                <i class="fas fa-phone"></i> Direkt anrufen
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
