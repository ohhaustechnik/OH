/* =================================================================
   OH HAUSTECHNIK – MULTI-STEP FUNNEL LOGIC
   ================================================================= */

(function () {
    'use strict';

    /* ---------------------------------------------------------------
       CONFIG
    --------------------------------------------------------------- */
    const TOTAL_STEPS = 8;

    /* ---------------------------------------------------------------
       DOM REFS
    --------------------------------------------------------------- */
    const overlay       = document.getElementById('funnel-overlay');
    const modal         = document.getElementById('funnel-modal');
    const closeBtn      = document.getElementById('funnel-close');
    const progressFill  = document.getElementById('funnel-progress-fill');
    const progressLabel = document.getElementById('funnel-progress-label');
    const dots          = document.querySelectorAll('.funnel-dot');
    const steps         = document.querySelectorAll('.funnel-step');
    const prevBtn       = document.getElementById('funnel-prev');
    const nextBtn       = document.getElementById('funnel-next');
    const submitBtn     = document.getElementById('funnel-submit');
    const errorMsg      = document.getElementById('funnel-error');

    /* all "open funnel" triggers */
    const openTriggers  = document.querySelectorAll('[data-open-funnel], #open-funnel-btn, #open-funnel-btn-mobile');

    if (!overlay || !modal) return; // Funnel not on page

    let currentStep = 1;

    /* ---------------------------------------------------------------
       OPEN / CLOSE
    --------------------------------------------------------------- */
    function openFunnel() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        goToStep(1);
    }

    function closeFunnel() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    openTriggers.forEach(function (btn) {
        btn.addEventListener('click', openFunnel);
    });

    closeBtn && closeBtn.addEventListener('click', closeFunnel);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeFunnel();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeFunnel();
    });

    /* ---------------------------------------------------------------
       NAVIGATION
    --------------------------------------------------------------- */
    function goToStep(n) {
        currentStep = n;

        // Show/hide steps
        steps.forEach(function (step) {
            step.classList.remove('active');
        });
        const activeStep = document.getElementById('funnel-step-' + n);
        if (activeStep) activeStep.classList.add('active');

        // Progress bar
        const pct = Math.round(((n - 1) / TOTAL_STEPS) * 100);
        progressFill.style.width = pct + '%';
        progressLabel.textContent = 'Schritt ' + n + ' von ' + TOTAL_STEPS;

        // Dots
        dots.forEach(function (dot, i) {
            dot.classList.remove('active', 'done');
            if (i + 1 < n)  dot.classList.add('done');
            if (i + 1 === n) dot.classList.add('active');
        });

        // Prev/Next/Submit visibility
        prevBtn.style.visibility = n === 1 ? 'hidden' : 'visible';
        nextBtn.style.display    = n === TOTAL_STEPS ? 'none' : 'inline-flex';
        submitBtn.style.display  = n === TOTAL_STEPS ? 'inline-flex' : 'none';

        // Clear error
        hideError();

        // Scroll modal to top
        modal.scrollTop = 0;
    }

    prevBtn && prevBtn.addEventListener('click', function () {
        if (currentStep <= 1) return;
        // Skip step 2 going back if not elektro
        if (currentStep === 3) {
            const kat = document.querySelector('input[name="kategorie"]:checked');
            if (!kat || kat.value !== 'elektro') {
                goToStep(1);
                return;
            }
        }
        goToStep(currentStep - 1);
    });

    nextBtn && nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep)) return;
        // Skip step 2 going forward if not elektro
        if (currentStep === 1) {
            const kat = document.querySelector('input[name="kategorie"]:checked');
            if (!kat || kat.value !== 'elektro') {
                goToStep(3);
                return;
            }
        }
        goToStep(currentStep + 1);
    });

    /* ---------------------------------------------------------------
       STEP 1 – CATEGORY → show sub-options
    --------------------------------------------------------------- */
    const categoryRadios = document.querySelectorAll('input[name="kategorie"]');
    const allSuboptions  = document.querySelectorAll('.funnel-suboptions');

    categoryRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            allSuboptions.forEach(function (sub) {
                sub.classList.remove('visible');
            });
            const target = document.getElementById('sub-' + this.value);
            if (target) target.classList.add('visible');
        });
    });

    /* ---------------------------------------------------------------
       FILE UPLOAD – show filename
    --------------------------------------------------------------- */
    const photoInput = document.getElementById('funnel-photos');
    const photoName  = document.getElementById('funnel-photo-name');

    if (photoInput && photoName) {
        photoInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const names = Array.from(this.files).map(function (f) { return f.name; });
                photoName.textContent = names.join(', ');
            } else {
                photoName.textContent = '';
            }
        });
    }

    /* ---------------------------------------------------------------
       VALIDATION
    --------------------------------------------------------------- */
    function validateStep(step) {
        hideError();

        switch (step) {
            case 1: {
                const cat = document.querySelector('input[name="kategorie"]:checked');
                if (!cat) {
                    showError('Bitte wählen Sie eine Leistungskategorie aus.');
                    return false;
                }
                return true;
            }
            case 2: {
                // Only required if elektro was selected
                const kat = document.querySelector('input[name="kategorie"]:checked');
                if (kat && kat.value === 'elektro') {
                    const typ = document.querySelector('input[name="elektro_typ"]:checked');
                    if (!typ) {
                        showError('Bitte wählen Sie die Art der Elektroarbeit aus.');
                        return false;
                    }
                }
                return true;
            }
            case 3: {
                const size = document.querySelector('input[name="objektgroesse"]:checked');
                if (!size) {
                    showError('Bitte wählen Sie die Objektgröße aus.');
                    return false;
                }
                return true;
            }
            case 4: {
                const time = document.querySelector('input[name="ausfuehrungszeit"]:checked');
                if (!time) {
                    showError('Bitte wählen Sie einen Ausführungszeitraum aus.');
                    return false;
                }
                return true;
            }
            case 5: {
                const reach = document.querySelector('input[name="erreichbarkeit"]:checked');
                if (!reach) {
                    showError('Bitte wählen Sie Ihre bevorzugte Erreichbarkeit aus.');
                    return false;
                }
                return true;
            }
            case 6: {
                const plz = document.getElementById('funnel-plz');
                if (!plz || !plz.value.trim() || !/^\d{4,5}$/.test(plz.value.trim())) {
                    showError('Bitte geben Sie eine gültige Postleitzahl ein.');
                    return false;
                }
                return true;
            }
            case 7:
                return true; // optional step
            case 8: {
                const vorname = document.getElementById('funnel-vorname');
                const nachname = document.getElementById('funnel-nachname');
                const email   = document.getElementById('funnel-email');
                const tel     = document.getElementById('funnel-tel');
                const dsgvo   = document.getElementById('funnel-dsgvo');

                if (!vorname || !vorname.value.trim()) {
                    showError('Bitte geben Sie Ihren Vornamen ein.');
                    return false;
                }
                if (!nachname || !nachname.value.trim()) {
                    showError('Bitte geben Sie Ihren Nachnamen ein.');
                    return false;
                }
                if (!email || !email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                    showError('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
                    return false;
                }
                if (!tel || !tel.value.trim()) {
                    showError('Bitte geben Sie Ihre Telefonnummer ein.');
                    return false;
                }
                if (!dsgvo || !dsgvo.checked) {
                    showError('Bitte stimmen Sie der Datenschutzerklärung zu, um fortzufahren.');
                    return false;
                }
                return true;
            }
        }
        return true;
    }

    function showError(msg) {
        if (errorMsg) {
            errorMsg.querySelector('span').textContent = msg;
            errorMsg.classList.add('visible');
        }
    }

    function hideError() {
        if (errorMsg) errorMsg.classList.remove('visible');
    }

    /* ---------------------------------------------------------------
       SUBMIT
    --------------------------------------------------------------- */
    submitBtn && submitBtn.addEventListener('click', function () {
        if (!validateStep(8)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet…';

        const formData = buildFormData();

        fetch('includes/funnel-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                closeFunnel();
                window.location.href = 'danke.php';
            } else {
                showError(data.message || 'Fehler beim Senden. Bitte versuchen Sie es erneut.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Angebot anfordern';
            }
        })
        .catch(function () {
            showError('Verbindungsfehler. Bitte versuchen Sie es erneut oder rufen Sie uns an.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Angebot anfordern';
        });
    });

    /* ---------------------------------------------------------------
       BUILD FORM DATA
    --------------------------------------------------------------- */
    function buildFormData() {
        const fd = new FormData();

        // Step 1 – Kategorie
        const kat = document.querySelector('input[name="kategorie"]:checked');
        fd.append('kategorie', kat ? kat.value : '');

        // Step 2 – Elektro-Typ (nur bei elektro)
        const elektroTyp = document.querySelector('input[name="elektro_typ"]:checked');
        fd.append('elektro_typ', elektroTyp ? elektroTyp.value : '');

        // Sub-options (checkboxes for selected category)
        if (kat) {
            const subChecked = document.querySelectorAll('#sub-' + kat.value + ' input[type="checkbox"]:checked');
            const subs = Array.from(subChecked).map(function (cb) { return cb.value; });
            fd.append('suboptionen', subs.join(', '));
        }

        // Lampen count
        const lampenCount = document.getElementById('lampen-anzahl');
        if (lampenCount && lampenCount.value) {
            fd.append('lampen_anzahl', lampenCount.value);
        }

        // Step 2
        const size = document.querySelector('input[name="objektgroesse"]:checked');
        fd.append('objektgroesse', size ? size.value : '');

        // Step 3
        const time = document.querySelector('input[name="ausfuehrungszeit"]:checked');
        fd.append('ausfuehrungszeit', time ? time.value : '');

        // Step 4
        const reach = document.querySelector('input[name="erreichbarkeit"]:checked');
        fd.append('erreichbarkeit', reach ? reach.value : '');
        const kontaktweg = document.querySelector('input[name="kontaktweg"]:checked');
        fd.append('kontaktweg', kontaktweg ? kontaktweg.value : '');

        // Step 5
        fd.append('plz',     document.getElementById('funnel-plz')?.value?.trim()  || '');
        fd.append('ort',     document.getElementById('funnel-ort')?.value?.trim()  || '');
        fd.append('strasse', document.getElementById('funnel-strasse')?.value?.trim() || '');

        // Step 6
        fd.append('details', document.getElementById('funnel-details')?.value?.trim() || '');

        const photos = document.getElementById('funnel-photos');
        if (photos && photos.files.length > 0) {
            Array.from(photos.files).forEach(function (file, i) {
                fd.append('fotos[]', file, file.name);
            });
        }

        // Step 7
        fd.append('vorname',  document.getElementById('funnel-vorname')?.value?.trim()  || '');
        fd.append('nachname', document.getElementById('funnel-nachname')?.value?.trim() || '');
        fd.append('email',    document.getElementById('funnel-email')?.value?.trim()    || '');
        fd.append('telefon',  document.getElementById('funnel-tel')?.value?.trim()      || '');
        fd.append('datenschutz', document.getElementById('funnel-dsgvo')?.checked ? '1' : '0');

        return fd;
    }

})();

/* =================================================================
   INLINE FUNNEL (kontakt.php)
   ================================================================= */
(function () {
    'use strict';

    const TOTAL = 8;
    const wrap  = document.getElementById('funnel-inline');
    if (!wrap) return;

    const steps        = wrap.querySelectorAll('.funnel-step');
    const progressFill = document.getElementById('fi-progress-fill');
    const progressLbl  = document.getElementById('fi-progress-label');
    const prevBtn      = document.getElementById('fi-prev');
    const nextBtn      = document.getElementById('fi-next');
    const submitBtn    = document.getElementById('fi-submit');
    const errorMsg     = document.getElementById('fi-error');

    let current = 1;

    /* --- Navigation --- */
    function goTo(n) {
        current = n;
        steps.forEach(function (s) { s.classList.remove('active'); });
        const active = document.getElementById('fi-step-' + n);
        if (active) active.classList.add('active');

        const pct = Math.round(((n - 1) / TOTAL) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (progressLbl)  progressLbl.textContent  = 'Schritt ' + n + ' von ' + TOTAL;

        // dots
        for (let i = 1; i <= TOTAL; i++) {
            const dot = document.getElementById('fi-dot-' + i);
            if (!dot) continue;
            dot.classList.remove('active', 'done');
            if (i < n)  dot.classList.add('done');
            if (i === n) dot.classList.add('active');
        }

        if (prevBtn) prevBtn.style.visibility = n === 1 ? 'hidden' : 'visible';
        if (nextBtn) nextBtn.style.display    = n === TOTAL ? 'none' : 'inline-flex';
        if (submitBtn) submitBtn.style.display = n === TOTAL ? 'inline-flex' : 'none';
        hideError();

        // Scroll to top of inline funnel
        wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    prevBtn  && prevBtn.addEventListener('click',  function () {
        if (current <= 1) return;
        // Skip step 2 going back if not elektro
        if (current === 3) {
            const kat = wrap.querySelector('input[name="fi_kategorie"]:checked');
            if (!kat || kat.value !== 'elektro') { goTo(1); return; }
        }
        goTo(current - 1);
    });
    nextBtn  && nextBtn.addEventListener('click',  function () {
        if (!validate(current)) return;
        // Skip step 2 going forward if not elektro
        if (current === 1) {
            const kat = wrap.querySelector('input[name="fi_kategorie"]:checked');
            if (!kat || kat.value !== 'elektro') { goTo(3); return; }
        }
        goTo(current + 1);
    });

    /* --- Category → Sub-options --- */
    wrap.querySelectorAll('input[name="fi_kategorie"]').forEach(function (r) {
        r.addEventListener('change', function () {
            wrap.querySelectorAll('.funnel-suboptions').forEach(function (s) { s.classList.remove('visible'); });
            const sub = document.getElementById('fi-sub-' + this.value);
            if (sub) sub.classList.add('visible');
        });
    });

    /* --- File upload name --- */
    const fiPhotos = document.getElementById('fi-photos');
    const fiPhotoName = document.getElementById('fi-photo-name');
    if (fiPhotos && fiPhotoName) {
        fiPhotos.addEventListener('change', function () {
            fiPhotoName.textContent = this.files.length
                ? Array.from(this.files).map(function (f) { return f.name; }).join(', ')
                : '';
        });
    }

    /* --- Validation --- */
    function validate(step) {
        hideError();
        switch (step) {
            case 1: if (!wrap.querySelector('input[name="fi_kategorie"]:checked')) { showError('Bitte wählen Sie eine Leistungskategorie aus.'); return false; } return true;
            case 2: {
                const kat = wrap.querySelector('input[name="fi_kategorie"]:checked');
                if (kat && kat.value === 'elektro') {
                    if (!wrap.querySelector('input[name="fi_elektro_typ"]:checked')) { showError('Bitte wählen Sie die Art der Elektroarbeit aus.'); return false; }
                }
                return true;
            }
            case 3: if (!wrap.querySelector('input[name="fi_objektgroesse"]:checked')) { showError('Bitte wählen Sie die Objektgröße aus.'); return false; } return true;
            case 4: if (!wrap.querySelector('input[name="fi_ausfuehrungszeit"]:checked')) { showError('Bitte wählen Sie einen Ausführungszeitraum aus.'); return false; } return true;
            case 5: if (!wrap.querySelector('input[name="fi_erreichbarkeit"]:checked')) { showError('Bitte wählen Sie Ihre bevorzugte Erreichbarkeit aus.'); return false; } return true;
            case 6: {
                const plz = document.getElementById('fi-plz');
                if (!plz || !/^\d{4,5}$/.test(plz.value.trim())) { showError('Bitte geben Sie eine gültige Postleitzahl ein.'); return false; }
                return true;
            }
            case 7: return true;
            case 8: {
                const vn = document.getElementById('fi-vorname');
                const nn = document.getElementById('fi-nachname');
                const em = document.getElementById('fi-email');
                const tl = document.getElementById('fi-tel');
                const ds = document.getElementById('fi-dsgvo');
                if (!vn?.value.trim()) { showError('Bitte geben Sie Ihren Vornamen ein.'); return false; }
                if (!nn?.value.trim()) { showError('Bitte geben Sie Ihren Nachnamen ein.'); return false; }
                if (!em?.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value.trim())) { showError('Bitte geben Sie eine gültige E-Mail-Adresse ein.'); return false; }
                if (!tl?.value.trim()) { showError('Bitte geben Sie Ihre Telefonnummer ein.'); return false; }
                if (!ds?.checked) { showError('Bitte stimmen Sie der Datenschutzerklärung zu.'); return false; }
                return true;
            }
        }
        return true;
    }

    /* --- Submit --- */
    submitBtn && submitBtn.addEventListener('click', function () {
        if (!validate(8)) return;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wird gesendet…';

        const fd = new FormData();
        const kat = wrap.querySelector('input[name="fi_kategorie"]:checked');
        fd.append('kategorie', kat ? kat.value : '');
        // Elektro-Typ (nur bei elektro)
        const elektroTyp = wrap.querySelector('input[name="fi_elektro_typ"]:checked');
        fd.append('elektro_typ', elektroTyp ? elektroTyp.value : '');
        if (kat) {
            const subs = Array.from(wrap.querySelectorAll('#fi-sub-' + kat.value + ' input[type="checkbox"]:checked')).map(function(c){ return c.value; });
            fd.append('suboptionen', subs.join(', '));
        }
        const lampen = document.getElementById('fi-lampen-anzahl');
        if (lampen?.value) fd.append('lampen_anzahl', lampen.value);

        const obj = wrap.querySelector('input[name="fi_objektgroesse"]:checked');
        fd.append('objektgroesse', obj ? obj.value : '');
        const zeit = wrap.querySelector('input[name="fi_ausfuehrungszeit"]:checked');
        fd.append('ausfuehrungszeit', zeit ? zeit.value : '');
        const err = wrap.querySelector('input[name="fi_erreichbarkeit"]:checked');
        fd.append('erreichbarkeit', err ? err.value : '');
        const kw = wrap.querySelector('input[name="fi_kontaktweg"]:checked');
        fd.append('kontaktweg', kw ? kw.value : '');

        fd.append('plz',     document.getElementById('fi-plz')?.value?.trim()     || '');
        fd.append('ort',     document.getElementById('fi-ort')?.value?.trim()     || '');
        fd.append('strasse', document.getElementById('fi-strasse')?.value?.trim() || '');
        fd.append('details', document.getElementById('fi-details')?.value?.trim() || '');

        const photos = document.getElementById('fi-photos');
        if (photos?.files.length) {
            Array.from(photos.files).forEach(function (f) { fd.append('fotos[]', f, f.name); });
        }

        fd.append('vorname',     document.getElementById('fi-vorname')?.value?.trim()  || '');
        fd.append('nachname',    document.getElementById('fi-nachname')?.value?.trim() || '');
        fd.append('email',       document.getElementById('fi-email')?.value?.trim()    || '');
        fd.append('telefon',     document.getElementById('fi-tel')?.value?.trim()      || '');
        fd.append('datenschutz', document.getElementById('fi-dsgvo')?.checked ? '1' : '0');

        fetch('includes/funnel-handler.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = 'danke.php';
                } else {
                    showError(data.message || 'Fehler beim Senden. Bitte versuchen Sie es erneut.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Angebot anfordern';
                }
            })
            .catch(function () {
                showError('Verbindungsfehler. Bitte versuchen Sie es erneut oder rufen Sie uns an.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Angebot anfordern';
            });
    });

    function showError(msg) {
        if (errorMsg) {
            errorMsg.querySelector('span').textContent = msg;
            errorMsg.classList.add('visible');
        }
    }
    function hideError() {
        if (errorMsg) errorMsg.classList.remove('visible');
    }

})();
