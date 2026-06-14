/* OH Haustechnik – Micro-Conversion-Tracking
 * Sendet Mikro-Signale (Telefon-Klick, Kalkulator, Funnel-Start, Scroll-Tiefe)
 * an GA4/Google Ads (gtag) UND an den dataLayer (GTM). Sicher, ohne Fehler,
 * auch wenn (noch) kein Tag geladen ist.
 *
 * In Google Ads als Conversions nutzbar über GA4-Key-Events (G-004VQKCXXC)
 * ODER direkt: window.OH_ADS_CONV = { kalkulator_abschluss:'LABEL', ... }
 * VOR diesem Script setzen -> dann wird zusätzlich eine echte Ads-Conversion gefeuert.
 */
(function () {
  var AW = 'AW-17801418796';
  function fire(name, params) {
    params = params || {};
    try {
      if (typeof window.gtag === 'function') {
        window.gtag('event', name, params);            // GA4 / generisches Event
        var map = window.OH_ADS_CONV || {};
        if (map[name]) {                                // optional: direkte Ads-Conversion
          var p = {}; for (var k in params) p[k] = params[k];
          p.send_to = AW + '/' + map[name];
          window.gtag('event', 'conversion', p);
        }
      }
      window.dataLayer = window.dataLayer || [];
      var d = { event: 'oh_' + name }; for (var k2 in params) d[k2] = params[k2];
      window.dataLayer.push(d);                         // GTM
    } catch (e) {}
  }
  window.ohTrack = fire;

  // Auto: Telefon-Klicks (wichtigster Direktkontakt)
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (t && t.closest) {
      var tel = t.closest('a[href^="tel:"]');
      if (tel) fire('phone_click', { value: 1 });
    }
  }, true);

  // Auto: Scroll-Tiefe (einmalig 50 % / 90 %) – Interesse messen
  var hit = {};
  window.addEventListener('scroll', function () {
    var h = document.documentElement;
    var sc = h.scrollTop || document.body.scrollTop || 0;
    var max = (h.scrollHeight - h.clientHeight) || 0;
    if (max <= 0) return;
    var p = sc / max * 100;
    if (p >= 50 && !hit['50']) { hit['50'] = 1; fire('scroll_50', { value: 1 }); }
    if (p >= 90 && !hit['90']) { hit['90'] = 1; fire('scroll_90', { value: 1 }); }
  }, { passive: true });
})();
