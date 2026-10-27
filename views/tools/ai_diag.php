<?php
$f = flash();
$faqs = [
    ['¿Qué es ser “IA-friendly”?', 'Es que tu sitio y tu marca estén preparados para que los motores de IA generativa (ChatGPT, Gemini, Claude, Perplexity y los resúmenes con IA de Google) puedan leerlos, entenderlos y citarlos como fuente cuando alguien pregunta por tu rubro.'],
    ['¿Qué analiza el diagnóstico?', 'Revisamos tu sitio como lo ve un rastreador de IA: si los bots de IA tienen acceso, si el contenido se lee sin JavaScript, datos estructurados, títulos, sitemap, llms.txt y señales de confianza. Lo combinamos con tus respuestas sobre reseñas, Google Business y menciones externas.'],
    ['¿Es gratis?', 'Sí. Recibís el puntaje y las recomendaciones por email, sin compromiso. Si querés implementarlas, el equipo de Nifty te puede ayudar.'],
];
?>
<script type="application/ld+json"><?= jenc(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $faqs)]) ?></script>

<section class="page-hero tool-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap tool-hero-grid">
    <div>
      <span class="eyebrow"><span class="live-dot"></span>Herramienta gratuita</span>
      <h1>¿Tu marca es <em>IA-friendly?</em></h1>
      <p class="lead">Cada vez más clientes le preguntan a ChatGPT, Gemini o Perplexity a quién contratar. Analizamos tu sitio como lo lee la IA y te decimos qué tan preparada está tu marca para que la recomienden — y qué hacer para mejorar.</p>
      <ul class="check-list">
        <li><?= icon('check', 16) ?> Acceso de 8 bots de IA a tu sitio (GPTBot, ClaudeBot, PerplexityBot…)</li>
        <li><?= icon('check', 16) ?> Contenido legible, datos estructurados, sitemap y llms.txt</li>
        <li><?= icon('check', 16) ?> Señales de reputación: reseñas, Google Business y menciones</li>
        <li><?= icon('check', 16) ?> Puntaje de 0 a 100 + recomendaciones priorizadas por email</li>
      </ul>
    </div>
    <div class="ai-preview" aria-hidden="true">
      <div class="ai-chat">
        <div class="ai-msg user">¿Qué agencia de marketing me recomendás en Buenos Aires?</div>
        <div class="ai-msg bot"><span class="ai-dot"></span>Según varias fuentes, <b>tumarca.com</b> se destaca por… <span class="cur"></span></div>
      </div>
      <div class="ai-gauge"><b>¿?</b><span>/100</span></div>
    </div>
  </div>
</section>

<section class="wrap section" id="form">
  <form method="post" action="/diagnostico-ia" class="tool-form reveal" data-loading="Analizando tu sitio como lo lee la IA… puede tardar unos segundos.">
    <?= csrf_field() ?><?= antispam_fields() ?>
    <?php if ($f): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
    <div class="tool-step">
      <div class="tool-step-num">1</div>
      <div class="tool-step-body">
        <h2>Tu sitio</h2>
        <div class="field"><label for="d-site">Dirección de tu sitio web *</label><input id="d-site" name="sitio" placeholder="www.tuempresa.com" required inputmode="url" autocomplete="url"></div>
        <div class="field-row">
          <div class="field"><label for="d-emp">Empresa / marca</label><input id="d-emp" name="empresa" autocomplete="organization"></div>
          <div class="field"><label for="d-rubro">Rubro</label><input id="d-rubro" name="rubro" placeholder="Ej: estudio contable, clínica, ecommerce de ropa…"></div>
        </div>
      </div>
    </div>

    <div class="tool-step">
      <div class="tool-step-num">2</div>
      <div class="tool-step-body">
        <h2>Tu reputación fuera del sitio</h2>
        <p class="muted">Lo que la IA lee de vos en otros lugares también cuenta. Respondé rápido, con lo que sepas.</p>
        <?php foreach (audit_questions() as $k => [$q, $opts]): ?>
          <fieldset class="q">
            <legend><?= e($q) ?></legend>
            <div class="opts">
              <?php $first = true; foreach ($opts as $v => $label): ?><label class="opt"><input type="radio" name="q_<?= $k ?>" value="<?= e($v) ?>"<?= $first ? ' required' : '' ?>><span><?= e($label) ?></span></label><?php $first = false; endforeach; ?>
            </div>
          </fieldset>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="tool-step">
      <div class="tool-step-num">3</div>
      <div class="tool-step-body">
        <h2>¿A dónde te enviamos el resultado?</h2>
        <div class="field-row">
          <div class="field"><label for="d-name">Nombre *</label><input id="d-name" name="nombre" required autocomplete="name"></div>
          <div class="field"><label for="d-email">Email *</label><input id="d-email" type="email" name="email" required autocomplete="email"></div>
        </div>
        <div class="field"><label for="d-wa">WhatsApp (opcional)</label><input id="d-wa" type="tel" name="whatsapp" placeholder="+54 9 11 ..." autocomplete="tel"></div>
        <label class="consent"><input type="checkbox" name="consent" value="1" checked> Quiero que el equipo de Nifty me contacte para revisar el diagnóstico.</label>
        <button class="btn btn-primary btn-block btn-lg" type="submit">Analizar mi marca <?= icon('arrow', 18) ?></button>
        <p class="form-note">Te mostramos el resultado al instante y te lo enviamos por email. <a href="/privacidad">Privacidad</a>.</p>
      </div>
    </div>
  </form>
</section>

<section class="wrap section">
  <div class="sec-head reveal"><span class="eyebrow">Preguntas frecuentes</span><h2>Sobre el diagnóstico</h2></div>
  <div class="faq reveal"><?php foreach ($faqs as [$q, $a]): ?><details><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details><?php endforeach; ?></div>
</section>
