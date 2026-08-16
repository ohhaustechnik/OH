/* OH Haustechnik – geteiltes JS fürs Dark-Design (Sticky Header + Scroll-Reveal).
   Gleiche Logik wie auf index.php / elektriker-nuernberg.html, ausgelagert
   damit die Unterseiten sie mitbenutzen koennen. */
(function () {
  var hd = document.getElementById('hd');
  if (hd) {
    addEventListener('scroll', function () {
      hd.classList.toggle('scrolled', scrollY > 20);
    }, { passive: true });
  }

  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e, i) {
      if (e.isIntersecting) {
        setTimeout(function () { e.target.classList.add('in'); }, i * 60);
        io.unobserve(e.target);
      }
    });
  }, { threshold: .14 });
  document.querySelectorAll('.rv').forEach(function (el) { io.observe(el); });
})();
