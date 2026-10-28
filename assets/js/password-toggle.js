/* Nifty Backend — botón "ojito" para mostrar u ocultar contraseñas */
(function () {
  var eye = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  var eyeOff = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-2.9 3.8M6.6 6.6C3.8 8.4 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="M2 2l20 20"/></svg>';

  function add(input) {
    if (input.dataset.pwToggle) return;
    input.dataset.pwToggle = '1';
    var wrap = document.createElement('span');
    wrap.className = 'pw-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-toggle';
    btn.innerHTML = eye;
    btn.setAttribute('aria-label', 'Mostrar contraseña');
    btn.title = 'Mostrar contraseña';
    btn.addEventListener('click', function (ev) {
      ev.preventDefault();
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? eyeOff : eye;
      var label = show ? 'Ocultar contraseña' : 'Mostrar contraseña';
      btn.setAttribute('aria-label', label);
      btn.title = label;
      input.focus();
    });
    wrap.appendChild(btn);

    // Al enviar el formulario se vuelve a ocultar, para que el navegador no la guarde como texto
    if (input.form) input.form.addEventListener('submit', function () { input.type = 'password'; });
  }

  function init() {
    document.querySelectorAll('input[type="password"]').forEach(add);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
