<?php $title = 'Casos de éxito'; $actions = '<a class="abtn primary" href="/admin/casos/nuevo">+ Nuevo caso</a>'; ?>
<div class="case-admin-grid">
  <?php foreach ($cases as $c): $stats = array_slice(jdec($c['stats']), 0, 3); ?>
    <article class="card case-adm">
      <div class="case-adm-media"><?php if ($c['hero_image']): ?><img src="<?= e(asset_url($c['hero_image'])) ?>" alt=""><?php else: ?><span><?= e($c['client']) ?></span><?php endif; ?>
        <em class="pill <?= $c['published'] ? 'st-ganado' : 'st-perdido' ?>"><?= $c['published'] ? 'Publicado' : 'Borrador' ?></em></div>
      <div class="case-adm-body">
        <span class="muted small"><?= e($c['industry']) ?> · orden <?= (int) $c['sort'] ?></span>
        <h3><?= e($c['client']) ?></h3>
        <p><?= e($c['title']) ?></p>
        <?php if ($stats): ?><div class="mini-stats"><?php foreach ($stats as $s): ?><span><b><?= e($s['value']) ?></b> <?= e($s['label']) ?></span><?php endforeach; ?></div><?php endif; ?>
        <div class="row-actions"><a class="abtn primary" href="/admin/casos/<?= (int) $c['id'] ?>">Editar</a><a class="abtn ghost" href="/casos-de-exito/<?= e($c['slug']) ?>" target="_blank">Ver ↗</a></div>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$cases): ?><p class="muted">No hay casos todavía.</p><?php endif; ?>
</div>
