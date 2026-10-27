<?php $types = lead_types(); $sts = lead_statuses(); ?>
<div class="table-wrap">
<table class="atable">
  <thead><tr><th>Fecha</th><th>Origen</th><th>Contacto</th><th>Empresa / sitio</th><th>Dato clave</th><th>Estado</th></tr></thead>
  <tbody>
  <?php if (!$leads): ?><tr><td colspan="6" class="muted center">Todavía no hay leads.</td></tr><?php endif; ?>
  <?php foreach ($leads as $l): $res = jdec($l['result']);
    $key = match ($l['type']) {
      'diagnostico_ia' => $l['score'] !== null ? '<span class="score" style="--c:' . e($res['color'] ?? '#999') . '">' . (int) $l['score'] . '/100</span>' : '',
      'plan_pauta' => isset($res['budget']) ? e(money((float) $res['budget'], $res['currency'] ?? 'USD')) . '/mes' : '',
      default => e(mb_strimwidth((string) (jdec($l['payload'])['objetivo'] ?? jdec($l['payload'])['producto'] ?? ''), 0, 40, '…')),
    }; ?>
    <tr class="<?= $l['status'] === 'nuevo' ? 'is-new' : '' ?>" onclick="location.href='/admin/leads/<?= (int) $l['id'] ?>'">
      <td class="nowrap"><?= e(date('d/m H:i', strtotime($l['created_at']))) ?></td>
      <td><span class="tag t-<?= e($l['type']) ?>"><?= e($types[$l['type']] ?? $l['type']) ?></span></td>
      <td><a href="/admin/leads/<?= (int) $l['id'] ?>"><b><?= e($l['name'] ?: '—') ?></b></a><br><span class="muted small"><?= e($l['email']) ?></span></td>
      <td><?= e($l['company'] ?: '—') ?><br><span class="muted small"><?= e(preg_replace('~^https?://~', '', $l['website'])) ?></span></td>
      <td><?= $key ?></td>
      <td><span class="pill st-<?= e($l['status']) ?>"><?= e($sts[$l['status']] ?? $l['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
