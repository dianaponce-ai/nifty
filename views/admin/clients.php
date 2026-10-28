<?php
$title = 'Clientes';
$actions = '<a class="abtn primary" href="/admin/clientes/nuevo">+ Nuevo cliente</a>';
?>
<div class="kpis">
  <div class="kpi-c"><span>Clientes activos</span><b><?= count(array_filter($list, fn($c) => $c['status'] === 'activo')) ?></b><small><?= count($list) ?> en total</small></div>
  <div class="kpi-c hot"><span>Fee mensual activo</span><b style="font-size:20px"><?= $fees ? e(implode(' + ', array_map(fn($k, $v) => fmt_money($v, $k), array_keys($fees), $fees))) : '—' ?></b><small>suma de fees de clientes activos</small></div>
</div>
<form class="filters card" method="get"><input type="search" name="q" value="<?= e((string) ($_GET['q'] ?? '')) ?>" placeholder="Buscar cliente o email…"><button class="abtn primary">Buscar</button></form>
<section class="card">
  <div class="table-wrap"><table class="atable">
    <thead><tr><th>Cliente</th><th>Contacto</th><th>Servicios</th><th>Fee mensual</th><th>Estado</th></tr></thead>
    <tbody>
    <?php if (!$list): ?><tr><td colspan="5" class="muted center">Los clientes se crean solos cuando aprueban una propuesta. También podés cargarlos a mano.</td></tr><?php endif; ?>
    <?php foreach ($list as $c): ?>
      <tr onclick="location.href='/admin/clientes/<?= (int) $c['id'] ?>'">
        <td><b><?= e($c['name']) ?></b><br><span class="muted small"><?= e(preg_replace('~^https?://~', '', $c['website'])) ?></span></td>
        <td><?= e($c['contact_name']) ?><br><span class="muted small"><?= e($c['contact_email']) ?></span></td>
        <td class="small"><?= e(mb_strimwidth(str_replace("\n", ' · ', (string) $c['services']), 0, 80, '…')) ?></td>
        <td class="nowrap"><?= $c['fee_amount'] ? e(fmt_money((float) $c['fee_amount'], $c['fee_currency'])) : '—' ?></td>
        <td><span class="pill cs-<?= e($c['status']) ?>"><?= e(client_statuses()[$c['status']] ?? $c['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
