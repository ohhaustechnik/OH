/* =================================================================
   OH HAUSTECHNIK – MAIN JAVASCRIPT
   ================================================================= */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------------------------------------------------------------
       1. MOBILE NAVIGATION
    --------------------------------------------------------------- */
    const mobileToggle = document.querySelector('.mobile-toggle');
    const mobileNav    = document.querySelector('.mobile-nav');
    const body         = document.body;

    if (mobileToggle && mobileNav) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = mobileNav.classList.toggle('open');
            mobileToggle.classList.toggle('open', isOpen);
            body.style.overflow = isOpen ? 'hidden' : '';
            mobileToggle.setAttribute('aria-expanded', isOpen);
        });

        // Close on overlay click
        mobileNav.addEventListener('click', function (e) {
            if (e.target === mobileNav) {
                closeMobileNav();
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMobileNav();
        });
    }

    function closeMobileNav() {
        if (!mobileNav) return;
        mobileNav.classList.remove('open');
        mobileToggle && mobileToggle.classList.remove('open');
        mobileToggle && mobileToggle.setAttribute('aria-expanded', 'false');
        body.style.overflow = '';
    }

    // Mobile Submenu Toggle
    const mobileSubTriggers = document.querySelectorAll('.mobile-sub-trigger');
    mobileSubTriggers.forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('open');
                const icon = this.querySelector('i');
                if (icon) icon.style.transform = submenu.classList.contains('open') ? 'rotate(180deg)' : '';
            }
        });
    });

    /* ---------------------------------------------------------------
       2. HEADER SCROLL EFFECT
    --------------------------------------------------------------- */
    const header = document.querySelector('.header');

    if (header) {
        function handleScroll() {
            header.classList.toggle('scrolled', window.scrollY > 30);
        }
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ---------------------------------------------------------------
       3. SCROLL PROGRESS BAR
    --------------------------------------------------------------- */
    const progressBar = document.getElementById('scroll-progress');

    if (progressBar) {
        window.addEventListener('scroll', function () {
            const scrollTotal = document.documentElement.scrollHeight - window.innerHeight;
            const progress = scrollTotal > 0 ? (window.scrollY / scrollTotal) * 100 : 0;
            progressBar.style.width = progress + '%';
        }, { passive: true });
    }

    /* ---------------------------------------------------------------
       4. SCROLL TO TOP BUTTON
    --------------------------------------------------------------- */
    const scrollTopBtn = document.getElementById('scroll-top');

    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            scrollTopBtn.classList.toggle('visible', window.scrollY > 500);
        }, { passive: true });

        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------------------------------------------------------------
       5. COOKIE BANNER
    --------------------------------------------------------------- */
    const cookieBanner = document.getElementById('cookie-banner');
    const consent = localStorage.getItem('oh_cookie_consent');

    if (cookieBanner && !consent) {
        setTimeout(function () {
            cookieBanner.style.display = 'block';
        }, 1500);
    }

    window.acceptCookies = function () {
        localStorage.setItem('oh_cookie_consent', 'accepted');
        hideCookieBanner();
    };

    window.declineCookies = function () {
        localStorage.setItem('oh_cookie_consent', 'declined');
        hideCookieBanner();
    };

    function hideCookieBanner() {
        if (cookieBanner) {
            cookieBanner.style.animation = 'slideDown 0.3s ease-out forwards';
            setTimeout(function () { cookieBanner.style.display = 'none'; }, 300);
        }
    }

    /* ---------------------------------------------------------------
       6. INTERSECTION OBSERVER – FADE-IN ANIMATIONS
    --------------------------------------------------------------- */
    const animateEls = document.querySelectorAll('[data-animate]');

    if (animateEls.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        animateEls.forEach(function (el, i) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'opacity 0.6s ease ' + (i * 0.07) + 's, transform 0.6s ease ' + (i * 0.07) + 's';
            observer.observe(el);
        });

        document.addEventListener('animationend', function (e) {}, { passive: true });
    }

    // Apply .animated class = make visible
    const style = document.createElement('style');
    style.textContent = '[data-animate].animated { opacity: 1 !important; transform: translateY(0) !important; }';
    document.head.appendChild(style);

    /* ---------------------------------------------------------------
       7. COUNTER ANIMATION (Stats)
    --------------------------------------------------------------- */
    const counters = document.querySelectorAll('[data-count]');

    if (counters.length > 0 && 'IntersectionObserver' in window) {
        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function (counter) {
            counterObserver.observe(counter);
        });
    }

    function animateCounter(el) {
        const target  = parseInt(el.getAttribute('data-count'), 10);
        const suffix  = el.getAttribute('data-suffix') || '';
        const duration = 1800;
        const start   = performance.now();

        function update(now) {
            const elapsed  = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
            el.textContent = Math.floor(eased * target) + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }

    /* ---------------------------------------------------------------
       8. KONTAKTFORMULAR AJAX (falls vorhanden)
    --------------------------------------------------------------- */
    const contactForm = document.getElementById('contact-form');

    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = contactForm.querySelector('[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            const msgBox = document.getElementById('form-message');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet…';
            }

            const formData = new FormData(contactForm);

            fetch(contactForm.getAttribute('action') || 'includes/contact-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (msgBox) {
                    msgBox.innerHTML = data.success
                        ? '<div class="alert alert--success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Nachricht erfolgreich gesendet!') + '</div>'
                        : '<div class="alert alert--error"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Fehler beim Senden. Bitte versuchen Sie es erneut.') + '</div>';
                    msgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (data.success) contactForm.reset();
            })
            .catch(function () {
                if (msgBox) {
                    msgBox.innerHTML = '<div class="alert alert--error"><i class="fas fa-exclamation-circle"></i> Verbindungsfehler. Bitte versuchen Sie es erneut oder rufen Sie uns an.</div>';
                }
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    }

    /* ---------------------------------------------------------------
       9. ACTIVE NAV LINK
    --------------------------------------------------------------- */
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-link, .mobile-nav-list a').forEach(function (link) {
        const href = link.getAttribute('href') || '';
        if (href === currentPath || (currentPath === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });

    /* ---------------------------------------------------------------
       10. SMOOTH ANCHOR SCROLLING
    --------------------------------------------------------------- */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const offset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height'), 10) || 80;
                const top = target.getBoundingClientRect().top + window.scrollY - offset - 16;
                window.scrollTo({ top: top, behavior: 'smooth' });
                closeMobileNav();
            }
        });
    });

    /* ---------------------------------------------------------------
       11. PARALLAX CTA SECTION
    --------------------------------------------------------------- */
    const parallaxBg = document.getElementById('parallaxCtaBg');

    if (parallaxBg) {
        function updateParallax() {
            const section = parallaxBg.closest('.parallax-cta');
            if (!section) return;
            const rect = section.getBoundingClientRect();
            const windowH = window.innerHeight;

            // Nur wenn Section sichtbar ist
            if (rect.bottom < 0 || rect.top > windowH) return;

            // Verschiebung berechnen: wie weit die Section durch den Viewport gescrollt hat
            const progress = (windowH - rect.top) / (windowH + rect.height);
            // Maximale Verschiebung: 30% der Bildhöhe (da Bild 150% hoch ist)
            const shift = (progress - 0.5) * 60; // -30 bis +30 px
            parallaxBg.style.transform = 'translateY(' + shift + 'px)';
        }

        window.addEventListener('scroll', updateParallax, { passive: true });
        updateParallax();
    }

    /* ---------------------------------------------------------------
       12. 🎬 COUNTER ANIMATION – Zahlen zählen beim Scrollen hoch
    --------------------------------------------------------------- */
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count')) || 100;
        const duration = 2000; // 2 Sekunden
        const increment = target / (duration / 16); // 60fps
        let current = 0;

        element.classList.add('counting');
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
                setTimeout(() => element.classList.remove('counting'), 300);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }

    // Intersection Observer für Counter
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.counted) {
                entry.target.dataset.counted = 'true';
                animateCounter(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => {
        counterObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       13. 🎬 STAGGER ANIMATION – Elemente erscheinen nacheinander
    --------------------------------------------------------------- */
    const staggerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.staggered) {
                entry.target.dataset.staggered = 'true';
                const items = entry.target.querySelectorAll('.stagger-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(25px)';
                        requestAnimationFrame(() => {
                            item.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        });
                    }, index * 100);
                });
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.stagger-container').forEach(el => {
        staggerObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       14. 🎬 CARD SCAN EFFECT – Scan-Linie beim Sichtbarwerden
    --------------------------------------------------------------- */
    const scanObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.scanned) {
                entry.target.dataset.scanned = 'true';
                // Trigger Scan
                const scanLine = entry.target.querySelector('::before');
                if (scanLine) {
                    entry.target.style.animation = 'none';
                    requestAnimationFrame(() => {
                        entry.target.classList.add('scanning');
                    });
                }
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.card-scan').forEach(el => {
        scanObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       15. 🎬 VOLTAGE BAR ANIMATION – Fortschrittsbalken
    --------------------------------------------------------------- */
    const voltageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                entry.target.dataset.animated = 'true';
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.voltage-bar').forEach(el => {
        voltageObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       16. 🎬 POWER ON EFFECT – Sections beim Scrollen
    --------------------------------------------------------------- */
    const powerOnObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.powered) {
                entry.target.dataset.powered = 'true';
                entry.target.classList.add('power-on');
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('[data-power-on]').forEach(el => {
        powerOnObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       17. 🎬 BUTTON EFFECTS – Enhanced Hover Interactions
    --------------------------------------------------------------- */
    document.querySelectorAll('.btn-glow-pulse').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.animation = 'glowPulse 1.5s ease infinite';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.animation = '';
        });
    });

    /* ---------------------------------------------------------------
       18. 🎬 UNIVERSAL SECTION ANIMATIONS – Jede Sektion einzigartig!
    --------------------------------------------------------------- */
    const sectionAnimations = {
        'section-fade-up': { class: 'section-fade-up', threshold: 0.2 },
        'section-clip-wipe': { class: 'section-clip-wipe', threshold: 0.2 },
        'section-faq': { class: 'section-faq', threshold: 0.15 },
        'section-glitch': { class: 'section-glitch', threshold: 0.3 },
        'section-slide-blur': { class: 'section-slide-blur', threshold: 0.2 },
        'section-elastic-zoom': { class: 'section-elastic-zoom', threshold: 0.2 },
        'section-swing-in': { class: 'section-swing-in', threshold: 0.2 },
        'section-fade-scale': { class: 'section-fade-scale', threshold: 0.2 },
        'section-split-reveal': { class: 'section-split-reveal', threshold: 0.3 },
        'section-roll-in': { class: 'section-roll-in', threshold: 0.2 },
        'section-light-speed': { class: 'section-light-speed', threshold: 0.2 },
        'anim-rotate-in': { class: 'anim-rotate-in', threshold: 0.3 }
    };

    // Universal Animation Observer
    const universalAnimObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                entry.target.dataset.animated = 'true';
                entry.target.classList.add('animated');
                
                // Für Stagger-Effekte innerhalb der Section
                const staggerItems = entry.target.querySelectorAll('.stagger-anim-item');
                staggerItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animated');
                    }, index * 100);
                });
            }
        });
    }, { threshold: 0.15 });

    // Alle Sections mit Animationsklassen beobachten
    Object.keys(sectionAnimations).forEach(className => {
        document.querySelectorAll('.' + className).forEach(el => {
            universalAnimObserver.observe(el);
        });
    });

    // Auch alle Elemente mit animate-on-scroll Klasse
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        universalAnimObserver.observe(el);
    });

    /* ---------------------------------------------------------------
       19. 🎬 SPECIAL EFFECTS TRIGGERS
    --------------------------------------------------------------- */

    // FAQ Akkordeon
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.faq-item');
            const isOpen = item.classList.contains('open');

            // Alle schließen
            document.querySelectorAll('.faq-item.open').forEach(openItem => {
                openItem.classList.remove('open');
                openItem.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            });

            // Aktuelles öffnen/schließen
            if (!isOpen) {
                item.classList.add('open');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Rubber Band on Click für Badges
    document.querySelectorAll('.trigger-rubber-band').forEach(el => {
        el.addEventListener('click', function() {
            this.classList.remove('anim-rubber-band');
            void this.offsetWidth; // Trigger reflow
            this.classList.add('anim-rubber-band');
        });
    });

    // Tada Effect für wichtige Karten
    document.querySelectorAll('.trigger-tada').forEach(el => {
        el.addEventListener('mouseenter', function() {
            if (!this.classList.contains('anim-tada')) {
                this.classList.add('anim-tada');
                setTimeout(() => {
                    this.classList.remove('anim-tada');
                }, 1000);
            }
        });
    });

    // Jello für playful Elemente
    document.querySelectorAll('.trigger-jello').forEach(el => {
        el.addEventListener('mouseenter', function() {
            if (!this.classList.contains('anim-jello')) {
                this.classList.add('anim-jello');
                setTimeout(() => {
                    this.classList.remove('anim-jello');
                }, 900);
            }
        });
    });

    // Wobble für Icons
    document.querySelectorAll('.trigger-wobble').forEach(el => {
        el.addEventListener('mouseenter', function() {
            if (!this.classList.contains('anim-wobble')) {
                this.classList.add('anim-wobble');
                setTimeout(() => {
                    this.classList.remove('anim-wobble');
                }, 1000);
            }
        });
    });

    // Heartbeat für CTAs
    document.querySelectorAll('.trigger-heartbeat').forEach(el => {
        const interval = setInterval(() => {
            el.classList.add('anim-heartbeat');
            setTimeout(() => {
                el.classList.remove('anim-heartbeat');
            }, 1300);
        }, 5000); // Alle 5 Sekunden
        
        // Stop on hover
        el.addEventListener('mouseenter', () => clearInterval(interval));
    });

});

/* slideDown Keyframe für Cookie-Banner */
(function () {
    const s = document.createElement('style');
    s.textContent = '@keyframes slideDown { from { transform: translateY(0); } to { transform: translateY(110%); } }';
    document.head.appendChild(s);
})();

/* =================================================================
   HERO BACKGROUND SLIDER
   ================================================================= */
(function () {
    const slides  = document.querySelectorAll('.hero-slide');
    const dots    = document.querySelectorAll('.hero-dot');
    const btnPrev = document.querySelector('.hero-arrow--prev');
    const btnNext = document.querySelector('.hero-arrow--next');

    if (!slides.length) return;

    let current   = 0;
    let timer     = null;
    const DELAY   = 5000;   // 5 Sekunden Autoplay
    const RESUME  = 6000;   // nach manueller Aktion 6 Sek. warten

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    function startAutoplay() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), DELAY);
    }

    function stopAndResume() {
        clearInterval(timer);
        setTimeout(startAutoplay, RESUME);
    }

    // Pfeile
    if (btnPrev) btnPrev.addEventListener('click', () => { goTo(current - 1); stopAndResume(); });
    if (btnNext) btnNext.addEventListener('click', () => { goTo(current + 1); stopAndResume(); });

    // Punkte
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { goTo(i); stopAndResume(); });
    });

    // Touch / Swipe
    let touchStartX = 0;
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });
        hero.addEventListener('touchend', e => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                goTo(diff > 0 ? current + 1 : current - 1);
                stopAndResume();
            }
        }, { passive: true });
    }

    // Start
    startAutoplay();
})();
