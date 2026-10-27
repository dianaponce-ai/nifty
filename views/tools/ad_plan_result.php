<?php
$cur = $plan['currency']; $est = $plan['estimates'];
$nf = fn($n) => number_format((float) $n, 0, ',', '.');
$colors = ['google' => '#ff7a2f', 'meta' => '#a83fd4', 'linkedin' => '#3b82f6', 'tiktok' => '#ec4899', 'youtube' => '#ef4444', 'remarketing' => '#ffb36b'];
?>
<section class="page-hero tool-hero result-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap result-grid">
    <div>
      <span class="eyebrow">Plan de pauta sugerido · <?= e($plan['code']) ?></span>
      <h1>Plan de pauta para <em><?= e($brief['negocio']) ?>.</em></h1>
      <p class="lead">Objetivo: <b><?= e($plan['objective']) ?></b> · Rubro: <?= e($plan['rubro']) ?> · Inversión: <b><?= e(money($plan['budget'], $cur)) ?>/mes</b></p>
      <?php if (!empty($plan['ai_note'])): ?><div class="ai-note"><span class="ai-ic"><?= icon('sparkle') ?></span><p><?= nl2br(e($plan['ai_note'])) ?></p></div><?php endif; ?>
      <p class="muted small">Te enviamos una copia a <?= e($lead['email']) ?>. Plan vigente hasta el <?= e(date('d/m/Y', strtotime($plan['valid_until']))) ?>.</p>
    </div>
    <div class="mix-card">
      <div class="mix-bar"><?php foreach ($plan['channels'] as $c): ?><i style="width:<?= (int) $c['pct'] ?>%;background:<?= $colors[$c['key']] ?? '#ff7a2f' ?>" title="<?= e($c['name']) ?>"></i><?php endforeach; ?></div>
      <?php foreach ($plan['channels'] as $c): ?>
        <div class="mix-row"><span class="dot" style="background:<?= $colors[$c['key']] ?? '#ff7a2f' ?>"></span><b><?= e($c['name']) ?></b><em><?= (int) $c['pct'] ?>%</em><span><?= e(money($c['amount'], $cur)) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="kpi-row">
    <div class="kpi"><span>Clics estimados / mes</span><b><?= $nf($est['clicks'][0]) ?> – <?= $nf($est['clicks'][1]) ?></b></div>
    <div class="kpi"><span><?= e(ucfirst($est['conv_label'])) ?> estimados</span><b><?= $nf($est['conv'][0]) ?> – <?= $nf($est['conv'][1]) ?></b></div>
    <?php if ($est['cpa']): ?><div class="kpi"><span>Costo por resultado</span><b>USD <?= $nf($est['cpa'][0]) ?> – <?= $nf($est['cpa'][1]) ?></b></div><?php endif; ?>
    <?php if ($est['roas']): ?><div class="kpi"><span>Retorno estimado (ROAS)</span><b><?= number_format($est['roas'][0], 1, ',', '') ?>x – <?= number_format($est['roas'][1], 1, ',', '') ?>x</b></div><?php endif; ?>
  </div>
  <p class="muted small">Estimaciones orientativas basadas en benchmarks del rubro en Argentina y LATAM<?= $cur === 'ARS' ? ' y un tipo de cambio de referencia de $' . $nf($plan['rate']) . ' por USD' : '' ?>. Los resultados reales dependen de la oferta, el sitio, las creatividades y la optimización.</p>
</section>

<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Estructura de campañas</span><h2>Qué haríamos en cada canal</h2></div>
  <div class="channel-grid">
    <?php foreach ($plan['channels'] as $c): ?>
      <div class="channel" style="--c:<?= $colors[$c['key']] ?? '#ff7a2f' ?>">
        <div class="channel-head"><b><?= e($c['name']) ?></b><span><?= (int) $c['pct'] ?>% · <?= e(money($c['amount'], $cur)) ?></span></div>
        <p class="muted"><?= e($c['formats']) ?> — <?= e($c['why']) ?></p>
        <ul><?php foreach ($c['campaigns'] as [$n, $d]): ?><li><b><?= e($n) ?></b><span><?= e($d) ?></span></li><?php endforeach; ?></ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="wrap section">
  <div class="two-col">
    <div>
      <div class="sec-head"><span class="eyebrow">Embudo</span><h2>Cómo se reparte el esfuerzo</h2></div>
      <div class="funnel">
        <?php foreach ($plan['funnel'] as $i => [$n, $p, $d]): ?><div class="fstep" style="--w:<?= 100 - $i * 14 ?>%"><b><?= e($n) ?> · <?= (int) $p ?>%</b><span><?= e($d) ?></span></div><?php endforeach; ?>
      </div>
    </div>
    <div>
      <div class="sec-head"><span class="eyebrow">KPIs</span><h2>Qué vamos a medir</h2></div>
      <ul class="kpi-list"><?php foreach ($plan['kpis'] as $k): ?><li><?= icon('chart', 16) ?><?= e($k) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
</section>

<?php if ($plan['prereq']): ?>
<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Antes de invertir</span><h2>Lo que hay que resolver primero</h2></div>
  <div class="prereq"><?php foreach ($plan['prereq'] as [$t, $d]): ?><div><b><?= e($t) ?></b><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</section>
<?php endif; ?>

<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Hoja de ruta</span><h2>Los primeros 90 días</h2></div>
  <div class="roadmap">
    <?php foreach ($plan['roadmap'] as [$t, $items]): ?><div class="rm"><b><?= e($t) ?></b><ul><?php foreach ($items as $it): ?><li><?= e($it) ?></li><?php endforeach; ?></ul></div><?php endforeach; ?>
  </div>
</section>

<section class="wrap section">
  <div class="cta-band">
    <span class="eyebrow">Plan <?= e($plan['code']) ?></span>
    <h2>Este plan se implementa con la metodología de Nifty.</h2>
    <p>La estructura, las audiencias y la optimización semanal son el trabajo de nuestro equipo de performance. Reservá una reunión y lo ajustamos con los datos reales de tu negocio.</p>
    <div class="hero-ctas center"><a class="btn btn-primary" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener">Quiero implementarlo con Nifty</a><a class="btn btn-ghost" href="/diagnostico-ia">Hacer el diagnóstico IA-friendly</a></div>
  </div>
</section>
