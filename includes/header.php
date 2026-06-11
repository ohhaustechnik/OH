<?php
// Base Path für Unterordner
$base_path = '';
if (
    strpos($_SERVER['PHP_SELF'], '/leistungen/') !== false
) {
    $base_path = '../';
}

// Standard Meta-Werte
$page_title       = isset($page_title)       ? $page_title       : 'OH Haustechnik – Elektroinstallation & Netzwerkverkabelung Nürnberg';
$meta_description = isset($meta_description) ? $meta_description : 'Normgerechte Elektroinstallation und strukturierte Netzwerkverkabelung im Raum Nürnberg. Fachgerechte Umsetzung nach VDE, DIN und TAB. Jetzt Kontakt aufnehmen!';
$meta_keywords    = isset($meta_keywords)    ? $meta_keywords    : 'Elektroinstallation Nürnberg, Netzwerkverkabelung Nürnberg, Elektriker Nürnberg, Elektrotechnik Fürth Erlangen, VDE-konform, Schutztechnik';
$canonical_url    = isset($canonical_url)    ? $canonical_url    : 'https://oh-haustechnik.de/';
$og_image         = isset($og_image)         ? $og_image         : 'https://oh-haustechnik.de/assets/img/og-image.jpg';

// Aktive Seite
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO Meta -->
    <meta name="description"  content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords"     content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="robots"       content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="author"       content="OH Haustechnik">
    <meta name="geo.region"   content="DE-BY">
    <meta name="geo.placename" content="Nürnberg">
    <link rel="canonical"     href="<?php echo htmlspecialchars($canonical_url); ?>">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:image"       content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:locale"      content="de_DE">
    <meta property="og:site_name"   content="OH Haustechnik">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="twitter:image"       content="<?php echo htmlspecialchars($og_image); ?>">

    <!-- Schema.org LocalBusiness -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Electrician",
        "name": "OH Haustechnik",
        "url": "https://oh-haustechnik.de",
        "telephone": "+491757481006",
        "email": "oh.Haustechnik@gmail.com",
        "description": "Fachgerechte Elektroinstallation und Netzwerkverkabelung im Raum Nürnberg.",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Nürnberg",
            "addressRegion": "Bayern",
            "addressCountry": "DE"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 49.4521,
            "longitude": 11.0767
        },
        "areaServed": [
            {"@type": "City", "name": "Nürnberg"},
            {"@type": "City", "name": "Fürth"},
            {"@type": "City", "name": "Erlangen"}
        ],
        "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
            "opens": "07:30",
            "closes": "17:00"
        },
        "priceRange": "€€"
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/funnel.css">

    <!-- Favicon Placeholder -->
    <link rel="icon" type="image/x-icon" href="<?php echo $base_path; ?>assets/img/favicon.ico">
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17801418796">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-17801418796');
  gtag('config', 'G-004VQKCXXC');
</script>
</head>
<body>

<!-- Scroll Progress Bar -->
<div id="scroll-progress"></div>

<!-- Cookie Banner -->
<div id="cookie-banner">
    <div class="cookie-inner container">
        <div class="cookie-text">
            <h4><i class="fas fa-shield-alt" style="color: var(--blue-primary); margin-right: 0.5rem;"></i>Cookie-Hinweis</h4>
            <p>
                Wir verwenden technisch notwendige Cookies. Weitere Informationen finden Sie in unserer
                <a href="<?php echo $base_path; ?>datenschutz.php">Datenschutzerklärung</a>.
            </p>
        </div>
        <div class="cookie-actions">
            <button onclick="acceptCookies()" class="btn btn--primary btn--sm">Akzeptieren</button>
            <button onclick="declineCookies()" class="btn btn--outline btn--sm">Nur notwendige</button>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header" role="banner">
    <div class="header-inner">

        <!-- Logo -->
        <a href="<?php echo $base_path; ?>index.php" class="header-logo" aria-label="OH Haustechnik – Startseite">
            <img src="<?php echo $base_path; ?>assets/img/logohaustechnikneu.png" alt="OH Haustechnik Logo" width="52" height="52">
            <div class="logo-text">
                <span class="logo-name">OH Haustechnik</span>
                <span class="logo-sub">Elektrotechnik · Nürnberg</span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="nav-desktop" role="navigation" aria-label="Hauptnavigation">
            <a href="<?php echo $base_path; ?>index.php"
               class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                Start
            </a>

            <div class="nav-item-dropdown">
                <a href="<?php echo $base_path; ?>leistungen.php"
                   class="nav-link <?php echo ($current_page === 'leistungen.php' || strpos($_SERVER['PHP_SELF'], '/leistungen/') !== false) ? 'active' : ''; ?>">
                    Leistungen <i class="fas fa-chevron-down"></i>
                </a>
                <div class="nav-dropdown">
                    <a href="<?php echo $base_path; ?>leistungen/elektroinstallation.php">
                        <i class="fas fa-bolt"></i> Elektroinstallation
                    </a>
                    <a href="<?php echo $base_path; ?>leistungen/netzwerkverkabelung.php">
                        <i class="fas fa-network-wired"></i> Netzwerkverkabelung
                    </a>
                    <a href="<?php echo $base_path; ?>leistungen/schutztechnik.php">
                        <i class="fas fa-shield-alt"></i> Sicherheit & Schutz
                    </a>
                    <a href="<?php echo $base_path; ?>leistungen.php">
                        <i class="fas fa-list"></i> Alle Leistungen
                    </a>
                </div>
            </div>

            <a href="<?php echo $base_path; ?>ueber-uns.php"
               class="nav-link <?php echo $current_page === 'ueber-uns.php' ? 'active' : ''; ?>">
                Über uns
            </a>
            <a href="<?php echo $base_path; ?>kontakt.php"
               class="nav-link <?php echo $current_page === 'kontakt.php' ? 'active' : ''; ?>">
                Kontakt
            </a>
        </nav>

        <!-- Header CTA -->
        <div class="header-cta">
            <a href="tel:+491757481006" class="btn-phone" aria-label="Jetzt anrufen">
                <i class="fas fa-phone"></i>
                <span>Jetzt anrufen</span>
            </a>
            <?php if ($current_page === 'index.php' || $current_page === ''): ?>
            <button type="button" class="btn btn--primary btn--sm" id="open-funnel-btn">
                <i class="fas fa-paper-plane"></i> Anfrage senden
            </button>
            <?php else: ?>
            <a href="<?php echo $base_path; ?>kontakt.php" class="btn btn--primary btn--sm">
                <i class="fas fa-paper-plane"></i> Anfrage senden
            </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle" aria-label="Menü öffnen" aria-expanded="false" aria-controls="mobile-nav">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</header>

<!-- Mobile Navigation -->
<nav class="mobile-nav" id="mobile-nav" role="navigation" aria-label="Mobile Navigation">
    <ul class="mobile-nav-list">
        <li>
            <a href="<?php echo $base_path; ?>index.php"
               class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home" style="color: var(--blue-primary); margin-right: 0.5rem; width: 18px;"></i>
                Start
            </a>
        </li>
        <li>
            <a href="#" class="mobile-sub-trigger">
                <i class="fas fa-bolt" style="color: var(--blue-primary); margin-right: 0.5rem; width: 18px;"></i>
                Leistungen <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 0.75rem;"></i>
            </a>
            <ul class="mobile-submenu">
                <li><a href="<?php echo $base_path; ?>leistungen/elektroinstallation.php">Elektroinstallation</a></li>
                <li><a href="<?php echo $base_path; ?>leistungen/netzwerkverkabelung.php">Netzwerkverkabelung</a></li>
                <li><a href="<?php echo $base_path; ?>leistungen/schutztechnik.php">Sicherheit & Schutz</a></li>
                <li><a href="<?php echo $base_path; ?>leistungen.php">Alle Leistungen</a></li>
            </ul>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>ueber-uns.php"
               class="<?php echo $current_page === 'ueber-uns.php' ? 'active' : ''; ?>">
                <i class="fas fa-users" style="color: var(--blue-primary); margin-right: 0.5rem; width: 18px;"></i>
                Über uns
            </a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>kontakt.php"
               class="<?php echo $current_page === 'kontakt.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope" style="color: var(--blue-primary); margin-right: 0.5rem; width: 18px;"></i>
                Kontakt
            </a>
        </li>
    </ul>
    <div class="mobile-nav-cta">
        <a href="tel:+491757481006" class="btn btn--primary btn--full">
            <i class="fas fa-phone"></i> Jetzt anrufen
        </a>
        <?php if ($current_page === 'index.php' || $current_page === ''): ?>
        <button type="button" class="btn btn--outline btn--full" id="open-funnel-btn-mobile">
            <i class="fas fa-paper-plane"></i> Anfrage senden
        </button>
        <?php else: ?>
        <a href="<?php echo $base_path; ?>kontakt.php" class="btn btn--outline btn--full">
            <i class="fas fa-paper-plane"></i> Anfrage senden
        </a>
        <?php endif; ?>
    </div>
</nav>
