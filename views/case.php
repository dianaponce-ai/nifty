<?php
$challenges = jdec($c['challenges']); $ops = jdec($c['services_ops']); $mk = jdec($c['services_mk']);
$stats = jdec($c['stats']); $hl = jdec($c['highlight']); $results = jdec($c['results']); $gallery = jdec($c['gallery']);
$domain = $c['site_url'] ? preg_replace('~^https?://(www\.)?|/$~', '', $c['site_url']) : mb_strtolower(str_replace(' ', '', $c['client'])) . '.com';
$nServ = count($ops) + count($mk);
$star = '<svg viewBox="0 0 24 24" fill="#db2777" aria-hidden="true"><path d="m12 2.8 2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9L12 2.8Z"/></svg>';
?>
<script type="application/ld+json"><?= jenc(['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $c['client'] . ': ' . $c['title'], 'description' => strip_tags((string) $c['subtitle']),
    'about' => ['@type' => 'Organization', 'name' => $c['client']] + ($c['site_url'] ? ['url' => $c['site_url']] : []),
    'author' => ['@id' => url('#organization')], 'publisher' => ['@id' => url('#organization')], 'dateModified' => date('c', strtotime($c['updated_at'])),
    'image' => $c['hero_image'] ? asset_url($c['hero_image']) : null, 'inLanguage' => 'es-AR']) ?></script>

<div class="nce">
  <header class="case-hero">
    <div class="wrap">
      <nav class="crumbs" aria-label="Ruta"><a href="/casos-de-exito">Casos de éxito</a> <span>/</span> <?= e($c['client']) ?></nav>
      <span class="tag"><i></i>Caso de éxito · <?= e($c['industry']) ?></span>
      <h1><?= e($c['client']) ?>: <?= e($c['title']) ?></h1>
      <div class="hero-sub">
        <p><?= e($c['subtitle']) ?></p>
        <div class="hero-meta">
          <div><b>Cliente</b><span><?= e($c['client']) ?></span></div>
          <div><b>Ubicación</b><span><?= e($c['location'] ?: '—') ?></span></div>
          <div><b>Alcance</b><span><?= e($c['scope'] ?: '—') ?></span></div>
        </div>
      </div>
      <div class="browser-stage">
        <div class="browser">
          <div class="browser-bar"><s></s><s></s><s></s><em><?= e($domain) ?></em></div>
          <?php if ($c['hero_image']): ?>
            <button class="zoom" type="button" aria-label="Ampliar imagen de <?= e($c['client']) ?>"><img src="<?= e(asset_url($c['hero_image'])) ?>" alt="<?= e($c['client']) ?> — trabajo realizado por Nifty Agency" loading="eager"></button>
          <?php else: ?>
            <div class="browser-ph"><span class="ph-kicker"><?= e($c['industry']) ?></span><b><?= e($c['client']) ?></b><span class="ph-sub"><?= e($c['scope']) ?></span></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <?php if ($challenges): ?>
  <section class="blk reto">
    <div class="wrap reto-grid">
      <div>
        <p class="kicker">El desafío</p>
        <h2><?= e($c['challenge_title']) ?></h2>
        <?php if ($c['challenge_intro']): ?><p class="lead"><?= e($c['challenge_intro']) ?></p><?php endif; ?>
      </div>
      <ul class="reto-list"><?php foreach ($challenges as $x): ?><li><b><?= e($x['t']) ?></b><?= e($x['d']) ?></li><?php endforeach; ?></ul>
    </div>
  </section>
  <?php else: ?><div class="reto reto-min"></div><?php endif; ?>

  <?php if ($stats): ?>
  <section class="stats-band">
    <div class="wrap stats-grid n<?= min(4, count($stats)) ?>">
      <?php foreach ($stats as $s): ?><div class="stat-card"><b><?= e($s['value']) ?></b><span><?= e($s['label']) ?></span></div><?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($ops || $mk): ?>
  <section class="blk">
    <div class="wrap">
      <div class="eco-head">
        <div><p class="kicker">Lo que hicimos</p><h2><?= e($c['work_title'] ?: ($nServ . ' servicios, un mismo ecosistema.')) ?></h2></div>
        <?php if ($c['work_intro']): ?><p class="lead" style="margin-top:0"><?= e($c['work_intro']) ?></p><?php endif; ?>
      </div>
      <div class="eco<?= (!$ops || !$mk) ? ' single' : '' ?>">
        <?php if ($ops): ?>
        <div class="capa op"><h3>Operación y tecnología</h3><p>La base sobre la que funciona el negocio todos los días.</p>
          <?php foreach ($ops as $x): ?><div class="serv"><span class="serv-ic"><?= icon(icon_for($x['t'] . ' ' . $x['d']), 28) ?></span><div><b><?= e($x['t']) ?></b><span><?= e($x['d']) ?></span></div></div><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($mk): ?>
        <div class="capa mk"><h3>Marca y demanda</h3><p>Lo que hace que la marca se conozca, se elija y se recomiende.</p>
          <?php foreach ($mk as $x): ?><div class="serv"><span class="serv-ic"><?= icon(icon_for($x['t'] . ' ' . $x['d']), 28) ?></span><div><b><?= e($x['t']) ?></b><span><?= e($x['d']) ?></span></div></div><?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <p class="eco-note">Todo lo diseñó, desarrolló y opera el mismo equipo de Nifty: por eso cada pieza alimenta a la siguiente.</p>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($hl['title'])): ?>
  <section class="blk ugc">
    <div class="wrap ugc-grid<?= $c['highlight_image'] ? '' : ' noimg' ?>">
      <div>
        <p class="kicker"><?= e($hl['kicker'] ?? '') ?></p>
        <h2><?= e($hl['title']) ?></h2>
        <?php if (!empty($hl['text'])): ?><p class="lead"><?= e($hl['text']) ?></p><?php endif; ?>
        <?php if (!empty($hl['steps'])): ?><ol class="pasos"><?php foreach ($hl['steps'] as $s): ?><li><span><b><?= e($s['t']) ?>.</b> <?= e($s['d']) ?></span></li><?php endforeach; ?></ol><?php endif; ?>
      </div>
      <?php if ($c['highlight_image']): ?>
      <div class="phone"><button class="zoom" type="button" aria-label="Ampliar imagen"><img src="<?= e(asset_url($c['highlight_image'])) ?>" alt="<?= e($hl['title']) ?>" loading="lazy"></button></div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($gallery): ?>
  <section class="blk gallery-blk">
    <div class="wrap">
      <p class="kicker">Galería</p>
      <div class="gallery n<?= min(3, count($gallery)) ?>">
        <?php foreach ($gallery as $g): ?><figure><button class="zoom" type="button" aria-label="Ampliar imagen"><img src="<?= e(asset_url($g['src'])) ?>" alt="<?= e($g['caption'] ?: $c['client']) ?>" loading="lazy"></button><?php if ($g['caption']): ?><figcaption><?= e($g['caption']) ?></figcaption><?php endif; ?></figure><?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="blk reviews-blk">
    <div class="wrap">
      <?php if ($reviews): ?>
        <p class="kicker">Lo que dice el cliente</p>
        <div class="quotes">
          <?php foreach ($reviews as $r): ?><blockquote><div class="estrellas"><?= str_repeat($star, (int) $r['rating']) ?></div><p>“<?= e($r['text']) ?>”</p><footer><b><?= e($r['author']) ?></b><?= $r['role'] ? ' · ' . e($r['role']) : '' ?></footer></blockquote><?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="trust-row">
          <div class="estrellas"><?= str_repeat($star, 5) ?></div>
          <p><b><?= e(setting('google_rating')) ?> en Google</b> con <?= e(setting('google_reviews')) ?> reseñas de clientes de Nifty Agency. <a href="<?= e(setting('google_reviews_url')) ?>" target="_blank" rel="noopener">Leer reseñas →</a></p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="resultado">
    <div class="wrap">
      <div class="res-box">
        <p class="kicker">El resultado</p>
        <h2><?= e($c['results_title']) ?></h2>
        <?php if ($results): ?><div class="res-cols"><?php foreach ($results as $x): ?><div><b><?= e($x['t']) ?></b><p><?= e($x['d']) ?></p></div><?php endforeach; ?></div><?php endif; ?>
        <div class="cta-row">
          <a class="btn" href="/contacto">Quiero resultados así</a>
          <?php if ($c['site_url']): ?><a class="btn ghost" href="<?= e($c['site_url']) ?>" target="_blank" rel="noopener">Visitar <?= e($c['client']) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if ($others): ?>
  <section class="more-cases">
    <div class="wrap">
      <p class="kicker">Más casos</p>
      <div class="more-grid">
        <?php foreach ($others as $o): ?><a href="/casos-de-exito/<?= e($o['slug']) ?>"><span><?= e($o['industry']) ?></span><b><?= e($o['client']) ?></b><em><?= e($o['title']) ?></em></a><?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</div>
<div class="nce-lb" id="nceLb" role="dialog" aria-modal="true" aria-label="Imagen ampliada"><img alt=""></div>
