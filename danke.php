<?php
$page_title       = 'Anfrage erhalten – OH Haustechnik Nürnberg';
$meta_description = 'Vielen Dank für Ihre Anfrage bei OH Haustechnik. Wir melden uns innerhalb von 24 Stunden.';
$canonical_url    = 'https://oh-haustechnik.de/danke.php';

include 'includes/header.php';
?>

<style>
/* ---------------------------------------------------------------
   DANKE PAGE STYLES
--------------------------------------------------------------- */
.danke-section {
    min-height: calc(100vh - var(--header-height) - 8rem);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 1.5rem;
    background: linear-gradient(160deg, var(--blue-light) 0%, var(--off-white) 60%);
}

.danke-card {
    background: #fff;
    border-radius: 24px;
    padding: 3.5rem 3rem;
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 16px 64px rgba(46,95,163,0.12);
    animation: dankeIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes dankeIn {
    from { opacity: 0; transform: translateY(30px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.danke-icon {
    width: 88px;
    height: 88px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2.2rem;
    color: #fff;
    box-shadow: 0 8px 32px rgba(21,128,61,0.3);
    animation: iconPop 0.5s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes iconPop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

.danke-headline {
    font-family: var(--font-display);
    font-size: 1.9rem;
    font-weight: 800;
    color: var(--blue-dark);
    margin-bottom: 0.75rem;
    line-height: 1.2;
}

.danke-sub {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.65;
    margin-bottom: 2.5rem;
}

.danke-steps {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2.5rem;
    text-align: left;
}

.danke-step-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: var(--blue-light);
    border-radius: 12px;
    border: 1px solid var(--gray-200);
    animation: fadeSlideIn 0.4s ease both;
}

.danke-step-item:nth-child(1) { animation-delay: 0.4s; }
.danke-step-item:nth-child(2) { animation-delay: 0.55s; }
.danke-step-item:nth-child(3) { animation-delay: 0.7s; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateX(-12px); }
    to   { opacity: 1; transform: translateX(0); }
}

.danke-step-num {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, var(--blue-primary), var(--blue-dark));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 700;
    flex-shrink: 0;
}

.danke-step-text {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 500;
    line-height: 1.5;
}

.danke-step-text small {
    display: block;
    font-weight: 400;
    color: var(--text-secondary);
    font-size: 0.82rem;
    margin-top: 0.15rem;
}

.danke-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 480px) {
    .danke-card {
        padding: 2.5rem 1.5rem;
    }
    .danke-headline {
        font-size: 1.5rem;
    }
}
</style>

<section class="danke-section">
    <div class="danke-card">

        <div class="danke-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1 class="danke-headline">Anfrage erhalten!</h1>

        <p class="danke-sub">
            Vielen Dank für Ihre Anfrage bei OH Haustechnik.<br>
            Wir haben alle Angaben erhalten und melden uns <strong>innerhalb von 24 Stunden</strong> bei Ihnen.
        </p>

        <!-- Nächste Schritte -->
        <div class="danke-steps">
            <div class="danke-step-item">
                <div class="danke-step-num">1</div>
                <div class="danke-step-text">
                    Wir prüfen Ihre Anfrage
                    <small>Alle Angaben werden gesichtet und bewertet.</small>
                </div>
            </div>
            <div class="danke-step-item">
                <div class="danke-step-num">2</div>
                <div class="danke-step-text">
                    Wir melden uns innerhalb von 24 Stunden
                    <small>Telefonisch, per WhatsApp oder E-Mail – je nach Ihrer Präferenz.</small>
                </div>
            </div>
            <div class="danke-step-item">
                <div class="danke-step-num">3</div>
                <div class="danke-step-text">
                    Sie erhalten ein transparentes Angebot
                    <small>Vollständig, verständlich und ohne versteckte Kosten.</small>
                </div>
            </div>
        </div>

        <div class="danke-actions">
            <a href="index.php" class="btn btn--primary btn--lg">
                <i class="fas fa-home"></i>
                Zurück zur Startseite
            </a>
            <a href="tel:+491757481006" class="btn btn--outline btn--lg">
                <i class="fas fa-phone"></i>
                Jetzt anrufen
            </a>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
