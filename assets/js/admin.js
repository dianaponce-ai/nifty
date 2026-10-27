/* Nifty Backend — generador de casos de éxito con IA */
(function () {
  var btn = document.getElementById('genBtn');
  if (!btn) return;
  var form = document.getElementById('caseForm'), status = document.getElementById('genStatus');

  function setField(key, value) {
    var el = form.querySelector('[data-k="' + key + '"]');
    if (!el || value == null) return;
    var pairs = el.getAttribute('data-pairs');
    if (pairs && Array.isArray(value)) {
      var k = pairs.split(',');
      value = value.map(function (x) { return (x[k[0]] || '') + (x[k[1]] ? ' | ' + x[k[1]] : ''); }).join('\n');
    }
    el.value = value;
    el.classList.remove('flash-fill'); void el.offsetWidth; el.classList.add('flash-fill');
  }

  btn.addEventListener('click', function () {
    if (btn.hasAttribute('data-noai')) { status.textContent = 'Cargá la API key de Claude en config.php para usar la generación automática.'; status.className = 'gen-status err'; return; }
    var hasContent = ['title', 'subtitle', 'stats'].some(function (k) { var el = form.querySelector('[data-k="' + k + '"]'); return el && el.value.trim(); });
    if (hasContent && !confirm('Esto reemplaza los textos actuales de los bloques. ¿Seguimos?')) return;
    var fd = new FormData();
    fd.append('_csrf', form.querySelector('[name=_csrf]').value);
    fd.append('facts', form.querySelector('[name=facts]').value);
    fd.append('client', form.querySelector('[name=client]').value);
    fd.append('industry', form.querySelector('[name=industry]').value);
    btn.disabled = true; status.textContent = 'Generando… (puede tardar unos segundos)'; status.className = 'gen-status muted small';
    fetch('/admin/casos/generar', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        btn.disabled = false;
        if (d.error) { status.textContent = d.error; status.className = 'gen-status err'; return; }
        ['title', 'subtitle', 'card_summary', 'scope', 'challenge_title', 'challenge_intro', 'challenges', 'work_title', 'work_intro', 'services_ops', 'services_mk', 'stats', 'results_title', 'results']
          .forEach(function (k) { setField(k, d[k]); });
        if (d.highlight) ['kicker', 'title', 'text', 'steps'].forEach(function (k) { setField('highlight.' + k, d.highlight[k]); });
        status.textContent = '✓ Listo. Revisá los textos, ajustá lo que quieras y guardá.'; status.className = 'gen-status ok';
      })
      .catch(function () { btn.disabled = false; status.textContent = 'No se pudo generar. Probá de nuevo.'; status.className = 'gen-status err'; });
  });
})();
