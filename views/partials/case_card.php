<?php $stats = array_slice(jdec($c['stats']), 0, 2); ?>
<a class="case-card" href="/casos-de-exito/<?= e($c['slug']) ?>">
  <div class="case-card-media">
    <?php if ($c['hero_image']): ?>
      <img src="<?= e(asset_url($c['hero_image'])) ?>" alt="<?= e($c['client']) ?>" loading="lazy">
    <?php else: ?>
      <div class="case-card-ph"><span><?= e($c['client']) ?></span></div>
    <?php endif; ?>
    <span class="case-card-tag"><?= e($c['industry']) ?></span>
  </div>
  <div class="case-card-body">
    <p class="case-card-client"><?= e($c['client']) ?></p>
    <h3><?= e($c['title']) ?></h3>
    <?php if ($stats): ?>
    <div class="case-card-stats">
      <?php foreach ($stats as $s): ?><div><b><?= e($s['value']) ?></b><span><?= e($s['label']) ?></span></div><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <span class="case-card-link">Ver caso <?= icon('arrow', 16) ?></span>
  </div>
</a>
