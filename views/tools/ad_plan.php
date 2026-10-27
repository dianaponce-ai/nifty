<?php $f = flash(); $radio = function (string $name, array $opts, string $def = '') {
    $h = '<div class="opts">';
    foreach ($opts as $v => $l) $h .= '<label class="opt"><input type="radio" name="' . e($name) . '" value="' . e($v) . '"' . ($v === $def ? ' checked' : '') . ' required><span>' . e($l) . '</span></label>';
    return $h . '</div>';
}; ?>
<section class="page-hero tool-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap tool-hero-grid">
    <div>
      <span class="eyebrow"><span class="live-dot"></span>Herramienta gratuita</span>
      <h1>Tu plan de pauta <em>de prueba.</em></h1>
      <p class="lead">Contanos sobre tu negocio como si fuera un brief y te devolvemos un plan de pauta sugerido con la metodología de Nifty: en qué canales invertir, cuánto en cada uno, cómo estructurar las campañas y qué esperar en los primeros 90 días.</p>
      <ul class="check-list">
        <li><?= icon('check', 16) ?> Distribución del presupuesto por canal</li>
        <li><?= icon('check', 16) ?> Estructura de campañas y embudo</li>
        <li><?= icon('check', 16) ?> Estimación orientativa de clics y resultados</li>
        <li><?= icon('check', 16) ?> Hoja de ruta de 90 días y KPIs a seguir</li>
      </ul>
    </div>
    <div class="plan-preview" aria-hidden="true">
      <div class="pp-row"><span>Google Ads</span><i style="width:45%"></i><b>45%</b></div>
      <div class="pp-row"><span>Meta Ads</span><i style="width:40%"></i><b>40%</b></div>
      <div class="pp-row"><span>Remarketing</span><i style="width:15%"></i><b>15%</b></div>
      <div class="pp-foot"><span>Plan NFT-••••</span><span>90 días</span></div>
    </div>
  </div>
</section>

<section class="wrap section" id="brief">
  <form method="post" action="/plan-de-pauta" class="tool-form reveal" data-loading="Armando tu plan de pauta…">
    <?= csrf_field() ?><?= antispam_fields() ?>
    <?php if ($f): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>

    <div class="tool-step">
      <div class="tool-step-num">1</div>
      <div class="tool-step-body">
        <h2>Tu negocio</h2>
        <div class="field-row">
          <div class="field"><label for="p-neg">Nombre del negocio *</label><input id="p-neg" name="negocio" required autocomplete="organization"></div>
          <div class="field"><label for="p-web">Sitio web o Instagram</label><input id="p-web" name="web" placeholder="www.tunegocio.com"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="p-rubro">Rubro *</label><select id="p-rubro" name="rubro" required><option value="">Elegí</option><?php foreach ($opts['rubros'] as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label>¿A quién le vendés? *</label><?= $radio('modelo', $opts['modelos'], 'b2c') ?></div>
        </div>
        <div class="field"><label for="p-prod">¿Qué vendés? Producto o servicio principal</label><input id="p-prod" name="producto" placeholder="Ej: rodamientos industriales, cursos de marketing, tratamientos faciales…"></div>
        <div class="field"><label for="p-pub">¿Quién es tu cliente ideal?</label><textarea id="p-pub" name="publico" rows="2" placeholder="Edad, cargo, tipo de empresa, zona, qué problema le resolvés…"></textarea></div>
      </div>
    </div>

    <div class="tool-step">
      <div class="tool-step-num">2</div>
      <div class="tool-step-body">
        <h2>Objetivo e inversión</h2>
        <div class="field"><label>¿Qué querés lograr con la pauta? *</label><?= $radio('objetivo', $opts['objetivos'], 'leads') ?></div>
        <div class="field-row">
          <div class="field"><label for="p-bud">Presupuesto mensual para pauta *</label>
            <div class="money"><select name="moneda" aria-label="Moneda"><option value="ARS">ARS $</option><option value="USD">USD</option></select><input id="p-bud" name="presupuesto" inputmode="numeric" placeholder="300.000" required></div></div>
          <div class="field"><label for="p-ticket">Ticket promedio de venta</label>
            <div class="money"><span class="money-note">misma moneda</span><input id="p-ticket" name="ticket" inputmode="numeric" placeholder="Opcional"></div></div>
        </div>
        <div class="field-row">
          <div class="field"><label>¿Dónde están tus clientes?</label><?= $radio('zona', $opts['zonas'], 'nacional') ?></div>
          <div class="field"><label>¿Cuánto tardan en decidir?</label><?= $radio('ciclo', $opts['ciclos'], 'dias') ?></div>
        </div>
      </div>
    </div>

    <div class="tool-step">
      <div class="tool-step-num">3</div>
      <div class="tool-step-body">
        <h2>Punto de partida</h2>
        <div class="field-row three">
          <div class="field"><label>¿Tenés medición (GA4, píxel)?</label><?= $radio('medicion', ['si' => 'Sí', 'no' => 'No / no sé'], 'no') ?></div>
          <div class="field"><label>¿Usás un CRM?</label><?= $radio('crm', ['si' => 'Sí', 'no' => 'No'], 'no') ?></div>
          <div class="field"><label>¿Tenés contenido en video?</label><?= $radio('video', ['si' => 'Sí', 'no' => 'No'], 'no') ?></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="p-ch">¿Ya invertís en algún canal?</label><input id="p-ch" name="canales_actuales" placeholder="Ej: Instagram promocionado, Google Ads…"></div>
          <div class="field"><label for="p-fecha">¿Hay alguna fecha clave?</label><input id="p-fecha" name="fecha" placeholder="Ej: lanzamiento en marzo, Hot Sale…"></div>
        </div>
        <div class="field"><label for="p-comp">Competidores principales</label><input id="p-comp" name="competidores" placeholder="Nombres o sitios web"></div>
      </div>
    </div>

    <div class="tool-step">
      <div class="tool-step-num">4</div>
      <div class="tool-step-body">
        <h2>¿A dónde te enviamos el plan?</h2>
        <div class="field-row">
          <div class="field"><label for="p-name">Nombre *</label><input id="p-name" name="nombre" required autocomplete="name"></div>
          <div class="field"><label for="p-email">Email *</label><input id="p-email" type="email" name="email" required autocomplete="email"></div>
        </div>
        <div class="field"><label for="p-wa">WhatsApp (opcional)</label><input id="p-wa" type="tel" name="whatsapp" placeholder="+54 9 11 ..." autocomplete="tel"></div>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Generar mi plan de pauta <?= icon('arrow', 18) ?></button>
        <p class="form-note">El plan es orientativo y se implementa con la metodología de optimización de Nifty. <a href="/privacidad">Privacidad</a>.</p>
      </div>
    </div>
  </form>
</section>
