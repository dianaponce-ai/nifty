<?php $from = $from ?? 'contacto'; $f = flash(); ?>
<section id="contacto" class="wrap section">
  <div class="contact-card reveal">
    <div class="contact-left">
      <span class="eyebrow">Hablemos</span>
      <h2>Contanos sobre tu negocio</h2>
      <p>Contanos en qué rubro estás y qué querés lograr, y te devolvemos un diagnóstico antes de la primera reunión.</p>
      <ul class="feat-list">
        <li><span class="ck">✓</span> Diagnóstico gratuito de tu situación actual</li>
        <li><span class="ck">✓</span> Reunión sin costo, sin compromiso</li>
        <li><span class="ck">✓</span> Reporting semanal de performance si avanzamos juntos</li>
      </ul>
      <a class="btn btn-ghost" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener"><?= icon('calendar', 18) ?> Prefiero agendar directo</a>
    </div>
    <div class="contact-right">
      <?php if ($f): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
      <form method="post" action="/contacto" class="form">
        <?= csrf_field() ?><?= antispam_fields() ?>
        <input type="hidden" name="_from" value="<?= e($from) ?>">
        <div class="field-row">
          <div class="field"><label for="c-name">Nombre *</label><input id="c-name" name="nombre" type="text" placeholder="Tu nombre" required autocomplete="name"></div>
          <div class="field"><label for="c-company">Empresa *</label><input id="c-company" name="empresa" type="text" placeholder="Nombre de tu empresa" required autocomplete="organization"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="c-email">Email *</label><input id="c-email" name="email" type="email" placeholder="vos@empresa.com" required autocomplete="email"></div>
          <div class="field"><label for="c-wa">WhatsApp *</label><input id="c-wa" name="whatsapp" type="tel" placeholder="+54 9 11 ...." required autocomplete="tel"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="c-rubro">Rubro *</label>
            <select id="c-rubro" name="rubro" required>
              <option value="">Elegí una opción</option>
              <?php foreach (['Servicios B2B / corporativo', 'Industria / distribución', 'Salud / centro médico', 'Coworking / espacios de trabajo', 'Ecommerce', 'Educación', 'Belleza / peluquería', 'Tecnología / software', 'Movilidad urbana', 'Otro'] as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
            </select></div>
          <div class="field"><label for="c-budget">Presupuesto mensual estimado *</label>
            <select id="c-budget" name="presupuesto" required>
              <option value="">Elegí una opción</option>
              <?php foreach (['Menos de USD 500', 'USD 500 – 1.500', 'USD 1.500 – 3.000', 'USD 3.000 – 6.000', 'Más de USD 6.000', 'Todavía no lo tengo definido'] as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="c-obj">Objetivo principal *</label>
            <select id="c-obj" name="objetivo" required>
              <option value="">Elegí una opción</option>
              <?php foreach (['Generar más reuniones comerciales calificadas', 'Bajar el costo por lead / mejorar eficiencia', 'Vender más online', 'Aparecer en Google y en las respuestas de la IA', 'Desarrollar un sistema o software a medida', 'Ordenar y profesionalizar mi marketing', 'Otro'] as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
            </select></div>
          <div class="field"><label for="c-sit">Situación actual *</label>
            <select id="c-sit" name="situacion" required>
              <option value="">Elegí una opción</option>
              <?php foreach (['Ya invierto en pauta pero no logro los resultados esperados', 'Nunca invertí en pauta y quiero empezar', 'Tengo equipo interno y busco un partner estratégico', 'Trabajo con otra agencia y estoy evaluando un cambio'] as $o): ?><option><?= e($o) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <div class="field"><label for="c-web">Sitio web (opcional)</label><input id="c-web" name="sitio_web" type="text" placeholder="www.tuempresa.com"></div>
        <div class="field"><label for="c-msg">Contanos brevemente tu objetivo</label><textarea id="c-msg" name="mensaje" rows="3" placeholder="Ej: queremos generar más reuniones calificadas..."></textarea></div>
        <button class="btn btn-primary btn-block" type="submit">Quiero mi diagnóstico gratuito</button>
        <p class="form-note">Al enviar aceptás nuestra <a href="/privacidad">política de privacidad</a>.</p>
      </form>
    </div>
  </div>
</section>
