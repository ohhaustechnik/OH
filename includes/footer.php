<!-- Footer -->
<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">

            <!-- Spalte 1: Brand -->
            <div>
                <div class="footer-logo">
                    <img src="<?php echo $base_path; ?>assets/img/logohaustechnikneu.png" alt="OH Haustechnik Logo">
                    <div class="footer-logo-text">
                        <span class="footer-logo-name">OH Haustechnik</span>
                        <span class="footer-logo-sub">Elektrotechnik · Nürnberg</span>
                    </div>
                </div>
                <p class="footer-desc">
                    Fachgerechte Elektroinstallation für Wohn- und Gewerbeobjekte im Raum Nürnberg.
                    Zuverlässige Umsetzung – persönlich, sauber und transparent.
                </p>
                <ul class="footer-contact-list">
                    <li>
                        <i class="fas fa-phone"></i>
                        <a href="tel:+491757481006">+49 175 7481006</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:oh.Haustechnik@gmail.com">oh.Haustechnik@gmail.com</a>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Nürnberg, Bayern</span>
                    </li>
                </ul>
            </div>

            <!-- Spalte 2: Leistungen -->
            <div class="footer-col">
                <h4>Leistungen</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>leistungen/elektroinstallation.php"><i class="fas fa-chevron-right"></i> Elektroinstallation</a></li>
                    <li><a href="<?php echo $base_path; ?>leistungen/netzwerkverkabelung.php"><i class="fas fa-chevron-right"></i> Netzwerkverkabelung</a></li>
                    <li><a href="<?php echo $base_path; ?>leistungen/schutztechnik.php"><i class="fas fa-chevron-right"></i> Sicherheit & Schutz</a></li>
                    <li><a href="<?php echo $base_path; ?>leistungen.php"><i class="fas fa-chevron-right"></i> Alle Leistungen</a></li>
                </ul>
            </div>

            <!-- Spalte 3: Unternehmen -->
            <div class="footer-col">
                <h4>Unternehmen</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>ueber-uns.php"><i class="fas fa-chevron-right"></i> Über uns</a></li>
                    <li><a href="<?php echo $base_path; ?>kontakt.php"><i class="fas fa-chevron-right"></i> Kontakt</a></li>
                    <li><a href="<?php echo $base_path; ?>impressum.php"><i class="fas fa-chevron-right"></i> Impressum</a></li>
                    <li><a href="<?php echo $base_path; ?>datenschutz.php"><i class="fas fa-chevron-right"></i> Datenschutz</a></li>
                </ul>
            </div>

            <!-- Spalte 4: Einsatzgebiet -->
            <div class="footer-col">
                <h4>Einsatzgebiet</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $base_path; ?>kontakt.php"><i class="fas fa-map-pin"></i> Nürnberg</a></li>
                    <li><a href="<?php echo $base_path; ?>kontakt.php"><i class="fas fa-map-pin"></i> Fürth</a></li>
                    <li><a href="<?php echo $base_path; ?>kontakt.php"><i class="fas fa-map-pin"></i> Erlangen</a></li>
                    <li><a href="<?php echo $base_path; ?>kontakt.php"><i class="fas fa-map-pin"></i> Umliegende Gemeinden</a></li>
                </ul>

            </div>

        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> OH Haustechnik. Alle Rechte vorbehalten.</span>
            <div class="footer-bottom-links">
                <a href="<?php echo $base_path; ?>impressum.php">Impressum</a>
                <a href="<?php echo $base_path; ?>datenschutz.php">Datenschutz</a>
                <a href="<?php echo $base_path; ?>kontakt.php">Kontakt</a>
            </div>
            <div style="margin-top: 0.75rem; font-size: 0.78rem; color: rgba(255,255,255,0.4);">
                Website erstellt von <a href="https://analytics-rocket.de/" target="_blank" rel="noopener" style="color: rgba(255,255,255,0.55); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.25);">Analyticsrocket</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button id="scroll-top" aria-label="Nach oben scrollen">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- JavaScript -->
<script src="<?php echo $base_path; ?>assets/js/main.js"></script>
<script src="<?php echo $base_path; ?>assets/js/funnel.js"></script>

</body>
</html>
