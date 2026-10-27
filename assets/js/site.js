/* Nifty Agency — interacciones del sitio */
(function () {
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Menú móvil y submenú
  var burger = document.querySelector('.burger'), nav = document.getElementById('mainNav');
  if (burger && nav) burger.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    document.body.classList.toggle('nav-open', open);
    burger.setAttribute('aria-expanded', open);
  });
  document.querySelectorAll('.sub-toggle').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.stopPropagation();
      var li = b.parentElement, open = li.classList.toggle('open');
      b.setAttribute('aria-expanded', open);
    });
  });
  document.addEventListener('click', function () { document.querySelectorAll('.has-sub.open').forEach(function (l) { l.classList.remove('open'); }); });
  if (nav) nav.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { nav.classList.remove('open'); document.body.classList.remove('nav-open'); }); });

  // Aparición al hacer scroll
  var els = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (en) { en.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }); }, { threshold: .1 });
    els.forEach(function (el) { io.observe(el); });
  } else els.forEach(function (el) { el.classList.add('in'); });

  // Texto que se escribe solo
  document.querySelectorAll('[data-typer]').forEach(function (el) {
    var phrases; try { phrases = JSON.parse(el.getAttribute('data-typer')); } catch (e) { return; }
    if (reduce || !phrases.length) return;
    var speed = +el.getAttribute('data-speed') || 55, hold = +el.getAttribute('data-hold') || 1700, i = 0, j = 0, del = false;
    (function tick() {
      var p = phrases[i]; el.textContent = p.slice(0, j);
      if (!del) { j++; if (j > p.length) { del = true; return setTimeout(tick, hold); } }
      else { j--; if (j < 0) { del = false; i = (i + 1) % phrases.length; j = 0; } }
      setTimeout(tick, del ? 22 : speed);
    })();
  });

  // Contadores
  setTimeout(function () {
    document.querySelectorAll('.count').forEach(function (el) {
      var to = parseFloat(el.getAttribute('data-to')), dec = parseInt(el.getAttribute('data-dec') || '0', 10);
      if (reduce) { el.textContent = to.toFixed(dec); return; }
      var t0 = null;
      (function step(ts) { if (!t0) t0 = ts; var p = Math.min((ts - t0) / 1600, 1); el.textContent = (to * (1 - Math.pow(1 - p, 3))).toFixed(dec); if (p < 1) requestAnimationFrame(step); })(performance.now());
    });
  }, 400);

  // Filtro de casos por rubro
  document.querySelectorAll('.pill-nav.filter button').forEach(function (b) {
    b.addEventListener('click', function () {
      var f = b.getAttribute('data-filter');
      document.querySelectorAll('.pill-nav.filter button').forEach(function (x) { x.classList.toggle('is-on', x === b); });
      document.querySelectorAll('#casesGrid > div').forEach(function (c) { c.hidden = !!f && c.getAttribute('data-ind') !== f; });
    });
  });

  // Visor de imágenes (casos)
  var lb = document.getElementById('nceLb');
  if (lb) {
    var img = lb.querySelector('img'), last = null;
    document.querySelectorAll('.nce .zoom').forEach(function (b) {
      b.addEventListener('click', function () { var im = b.querySelector('img'); last = b; img.src = im.src; img.alt = im.alt; lb.classList.add('open'); lb.tabIndex = -1; lb.focus(); });
    });
    var close = function () { lb.classList.remove('open'); if (last) last.focus(); };
    lb.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && lb.classList.contains('open')) close(); });
  }

  // La Fábrica: preseleccionar producto en el formulario
  document.querySelectorAll('[data-producto]').forEach(function (a) {
    a.addEventListener('click', function () { var s = document.getElementById('fb-prod'); if (s) s.value = a.getAttribute('data-producto'); });
  });

  // Pantalla de "procesando" en las herramientas
  document.querySelectorAll('form[data-loading]').forEach(function (f) {
    f.addEventListener('submit', function () {
      if (!f.checkValidity()) return;
      var steps = ['Revisando robots.txt…', 'Leyendo tu sitio como un bot de IA…', 'Buscando datos estructurados…', 'Calculando tu puntaje…'];
      if (f.action.indexOf('plan-de-pauta') > -1) steps = ['Analizando tu brief…', 'Distribuyendo el presupuesto…', 'Armando la estructura de campañas…', 'Preparando tu hoja de ruta…'];
      var ov = document.createElement('div'); ov.className = 'loading-overlay';
      ov.innerHTML = '<div class="spinner"></div><p>' + f.getAttribute('data-loading') + '</p><div class="loading-steps"></div>';
      document.body.appendChild(ov);
      var k = 0, st = ov.querySelector('.loading-steps');
      st.textContent = steps[0];
      setInterval(function () { k = (k + 1) % steps.length; st.textContent = steps[k]; }, 2200);
      f.querySelectorAll('button[type=submit]').forEach(function (b) { b.disabled = true; });
    });
  });
})();
