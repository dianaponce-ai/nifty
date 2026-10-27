<?php
$title = 'Leads';
$qs = $_GET;
$actions = '<a class="abtn" href="/admin/leads/exportar?' . e(http_build_query($qs)) . '">Exportar CSV</a>';
$cur = fn($k) => (string) ($_GET[$k] ?? '');
?>
<form class="filters card" method="get">
  <input type="search" name="q" value="<?= e($cur('q')) ?>" placeholder="Buscar nombre, empresa, email, sitio…">
  <select name="tipo"><option value="">Todos los orígenes</option><?php foreach (lead_types() as $k => $l): ?><option value="<?= $k ?>"<?= $cur('tipo') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
  <select name="estado"><option value="">Todos los estados</option><?php foreach (lead_statuses() as $k => $l): ?><option value="<?= $k ?>"<?= $cur('estado') === $k ? ' selected' : '' ?>><?= e($l) ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach; ?></select>
  <button class="abtn primary">Filtrar</button>
  <?php if ($qs): ?><a class="abtn ghost" href="/admin/leads">Limpiar</a><?php endif; ?>
</form>
<section class="card"><?php partial('admin/_leads_table', ['leads' => $leads]); ?></section>
