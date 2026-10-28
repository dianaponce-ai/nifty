<?php
$title = 'Propuestas';
$actions = '<button class="abtn ghost" type="button" onclick="document.getElementById(\'imp\').showModal()">Importar</button><a class="abtn primary" href="/admin/propuestas/nueva">+ Nueva propuesta</a>';
$sts = proposal_statuses();
$cur = fn($k) => (string) ($_GET[$k] ?? '');
$pipe = ['ARS' => 0, 'USD' => 0]; $won = ['ARS' => 0, 'USD' => 0];
foreach ($list as $p) { [$o, $m] = proposal_totals(jdec($p['items'])); $v = $o + $m * 6;
  if (in_array($p['status'], ['enviada', 'vista', 'cambios'], true)) $pipe[$p['currency']] += $v;
  if ($p['status'] === 'aprobada') $won[$p['currency']] += $v; }
$fmtSum = fn($a) => implode(' + ', array_filter([$a['ARS'] ? fmt_money($a['ARS'], 'ARS') : '', $a['USD'] ? fmt_money($a['USD'], 'USD') : ''])) ?: '—';
?>
<div class="kpis">
  <div class="kpi-c"><span>En curso</span><b><?= (int) (($counts['enviada'] ?? 0) + ($counts['vista'] ?? 0) + ($counts['cambios'] ?? 0)) ?></b><small>enviadas, vistas o con cambios</small></div>
  <div class="kpi-c hot"><span>Pipeline (único + 6 meses de fee)</span><b style="font-size:20px"><?= e($fmtSum($pipe)) ?></b><small>propuestas abiertas</small></div>
  <div class="kpi-c"><span>Aprobadas</span><b><?= (int) ($counts['aprobada'] ?? 0) ?></b><small><?= e($fmtSum($won)) ?></small></div>
  <div class="kpi-c"><span>Borradores</span><b><?= (int) ($counts['borrador'] ?? 0) ?></b><small>sin enviar</small></div>
</div>

<form class="filters card" method="get">
  <input type="search" name="q" value="<?= e($cur('q')) ?>" placeholder="Buscar cliente, título o número…">
  <select name="estado"><option value="">Todas (sin archivadas)</option><?php foreach ($sts as $k => $l): ?><option value="<?= $k ?>"<?= $cur('estado') === $k ? ' selected' : '' ?>><?= e($l) ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach; ?></select>
  <button class="abtn primary">Filtrar</button>
</form>

<section class="card">
  <div class="table-wrap"><table class="atable">
    <thead><tr><th>Número</th><th>Cliente</th><th>Propuesta</th><th>Inversión</th><th>Estado</th><th>Actualizada</th></tr></thead>
    <tbody>
    <?php if (!$list): ?><tr><td colspan="6" class="muted center">Todavía no hay propuestas. Creá la primera o importá las de WordPress.</td></tr><?php endif; ?>
    <?php foreach ($list as $p): [$o, $m] = proposal_totals(jdec($p['items'])); ?>
      <tr onclick="location.href='/admin/propuestas/<?= (int) $p['id'] ?>'">
        <td class="nowrap"><b><?= e($p['number']) ?></b><?= $p['legacy_url'] ? '<br><span class="muted small">WordPress</span>' : '' ?></td>
        <td><b><?= e($p['client_name']) ?></b><br><span class="muted small"><?= e($p['contact_email']) ?></span></td>
        <td><?= e(mb_strimwidth($p['title'], 0, 70, '…')) ?></td>
        <td class="nowrap"><?= $o ? e(fmt_money($o, $p['currency'])) : '' ?><?= $m ? '<br><span class="muted small">' . e(fmt_money($m, $p['currency'])) . '/mes</span>' : '' ?></td>
        <td><span class="pill ps-<?= e($p['status']) ?>"><?= e($sts[$p['status']] ?? $p['status']) ?></span><?= $p['views'] ? '<br><span class="muted small">' . (int) $p['views'] . ' vistas</span>' : '' ?></td>
        <td class="nowrap small muted"><?= e(date('d/m/Y', strtotime($p['updated_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>

<dialog id="imp" class="adlg">
  <form method="post" action="/admin/propuestas/importar" enctype="multipart/form-data" class="aform">
    <?= csrf_field() ?>
    <h2>Importar propuestas</h2>
    <p class="muted small">Subí el archivo <code>.json</code> con las propuestas históricas de WordPress. Las que ya estén importadas se saltean.</p>
    <label>Archivo JSON<input type="file" name="file" accept=".json,application/json" required></label>
    <div class="row-actions"><button class="abtn primary">Importar</button><button class="abtn ghost" type="button" onclick="this.closest('dialog').close()">Cancelar</button></div>
  </form>
</dialog>
