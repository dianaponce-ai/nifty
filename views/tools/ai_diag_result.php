<?php
$site = $r['site'] ?? []; $checks = $site['checks'] ?? [];
$host = parse_url($r['url'] ?? '', PHP_URL_HOST);
$dash = 2 * M_PI * 54; $off = $dash * (1 - ($r['score'] ?? 0) / 100);
$stLabel = ['ok' => 'Bien', 'parcial' => 'A mejorar', 'falta' => 'Falta'];
?>
<section class="page-hero tool-hero result-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap result-grid">
    <div>
      <span class="eyebrow">Diagnóstico IA-friendly · <?= e($host) ?></span>
      <h1><?= e($lead['name']) ?>, tu marca está <em>“<?= e($r['level']) ?>”.</em></h1>
      <p class="lead"><?= e($r['level_text']) ?></p>
      <?php if (!empty($r['ai_note'])): ?><div class="ai-note"><span class="ai-ic"><?= icon('sparkle') ?></span><p><?= nl2br(e($r['ai_note'])) ?></p></div><?php endif; ?>
      <p class="muted small">También te lo enviamos a <?= e($lead['email']) ?>.</p>
    </div>
    <div class="gauge-card">
      <svg viewBox="0 0 120 120" class="gauge" role="img" aria-label="Puntaje <?= (int) $r['score'] ?> de 100">
        <circle cx="60" cy="60" r="54" class="g-bg"/>
        <circle cx="60" cy="60" r="54" class="g-fg" style="stroke:<?= e($r['color']) ?>;stroke-dasharray:<?= $dash ?>;stroke-dashoffset:<?= $off ?>"/>
      </svg>
      <div class="gauge-num"><b><?= (int) $r['score'] ?></b><span>/100</span></div>
      <div class="gauge-split">
        <div><b><?= (int) $r['site_score'] ?></b><span>Sitio (70%)</span></div>
        <div><b><?= (int) $r['ext_score'] ?></b><span>Señales externas (30%)</span></div>
      </div>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Plan de acción</span><h2>Lo que recomendamos trabajar, <em>en orden.</em></h2></div>
  <ol class="reco-list">
    <?php foreach ($r['recos'] as $i => $x): ?>
      <li class="reco prio-<?= e($x['prio']) ?>"><span class="reco-n"><?= $i + 1 ?></span><div><b><?= e($x['title']) ?></b><p><?= e($x['text']) ?></p></div><span class="prio">Prioridad <?= e($x['prio']) ?></span></li>
    <?php endforeach; ?>
    <?php if (!$r['recos']): ?><li class="reco"><div><b>¡Excelente base!</b><p>No encontramos puntos críticos. El siguiente paso es medir en qué respuestas de IA aparecés y ganar más menciones.</p></div></li><?php endif; ?>
  </ol>
  <div class="cta-inline">
    <div><b>Cada punto tiene una solución concreta.</b><span>En Nifty trabajamos SEO + GEO para que tu marca sea la que la IA recomienda. Te mostramos por dónde empezar en una reunión de 30 minutos.</span></div>
    <a class="btn btn-primary" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener">Agendar reunión con Nifty</a>
  </div>
</section>

<?php if ($site['reachable'] ?? false): ?>
<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Detalle técnico</span><h2>Así leyó la IA tu sitio</h2></div>
  <div class="checks">
    <?php foreach ($checks as $c): ?>
      <div class="check st-<?= e($c['status']) ?>">
        <span class="check-st"><?= $stLabel[$c['status']] ?></span>
        <b><?= e($c['label']) ?></b>
        <p><?= e($c['detail']) ?></p>
        <span class="check-pts"><?= rtrim(rtrim(number_format($c['points'], 1, ',', ''), '0'), ',') ?>/<?= (int) $c['weight'] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (!empty($site['bots'])): ?>
  <h3 class="h3" style="margin:40px 0 16px">Acceso de los bots de IA</h3>
  <div class="bots">
    <?php foreach ($site['bots'] as $bot => $st): ?><div class="bot b-<?= e($st) ?>"><b><?= e($bot) ?></b><span><?= e(AI_BOTS[$bot] ?? '') ?></span><em><?= e($st) ?></em></div><?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="wrap section">
  <div class="sec-head"><span class="eyebrow">Señales externas</span><h2>Tu reputación fuera del sitio</h2></div>
  <div class="ext-list">
    <?php foreach ($r['external']['items'] as $x): ?><div class="ext"><span class="bar"><i style="width:<?= (int) round($x['value'] * 100) ?>%"></i></span><b><?= e($x['question']) ?></b><span class="muted"><?= e($x['answer']) ?></span></div><?php endforeach; ?>
  </div>
</section>

<section class="wrap section">
  <div class="cta-band">
    <span class="eyebrow">Siguiente paso</span>
    <h2>¿Querés que la IA te recomiende a vos?</h2>
    <p>Revisamos este diagnóstico juntos y te proponemos un plan de SEO + GEO para tu marca. Sin costo y sin compromiso.</p>
    <div class="hero-ctas center"><a class="btn btn-primary" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener">Agendar reunión</a><a class="btn btn-ghost" href="/plan-de-pauta">Armar también mi plan de pauta</a></div>
  </div>
</section>
