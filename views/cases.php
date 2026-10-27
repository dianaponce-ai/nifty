<?php $industries = array_values(array_unique(array_filter(array_map(fn($c) => trim(explode('·', $c['industry'])[0]), $cases)))); ?>
<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap">
    <span class="eyebrow">Casos de éxito</span>
    <h1>Marcas que crecieron <em>con Nifty.</em></h1>
    <p class="lead">Escuelas, distribuidores industriales, coworkings, peluquerías, movilidad eléctrica y marcas globales. Qué desafío tenían, qué construimos juntos y qué resultados lograron.</p>
    <?php if (count($industries) > 1): ?>
    <div class="pill-nav filter" role="toolbar" aria-label="Filtrar por rubro">
      <button type="button" class="is-on" data-filter="">Todos</button>
      <?php foreach ($industries as $in): ?><button type="button" data-filter="<?= e($in) ?>"><?= e($in) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<section class="wrap section" style="padding-top:20px">
  <div class="cases-grid" id="casesGrid">
    <?php foreach ($cases as $c): ?><div data-ind="<?= e(trim(explode('·', $c['industry'])[0])) ?>"><?php partial('partials/case_card', ['c' => $c]); ?></div><?php endforeach; ?>
  </div>
  <?php if (!$cases): ?><p class="center muted">Pronto vas a ver acá nuestros casos.</p><?php endif; ?>
</section>
<section class="wrap section">
  <div class="cta-band reveal">
    <span class="eyebrow">Tu marca puede ser el próximo caso</span>
    <h2>Empecemos por un diagnóstico.</h2>
    <p>Gratis y en dos minutos: medí qué tan preparada está tu marca para la IA, o armá un plan de pauta de prueba.</p>
    <div class="hero-ctas center"><a class="btn btn-primary" href="/diagnostico-ia">Diagnóstico IA-friendly</a><a class="btn btn-ghost" href="/plan-de-pauta">Plan de pauta de prueba</a></div>
  </div>
</section>
