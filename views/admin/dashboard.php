<?php
$title = 'Dashboard';
$actions = '<a class="abtn" href="/admin/leads/exportar">Exportar leads</a>';
$max = max(1, max($daily));
$types = lead_types(); $sts = lead_statuses();
$won = (int) ($byStatus['ganado'] ?? 0);
?>
<div class="kpis">
  <div class="kpi-c"><span>Leads últimos 30 días</span><b><?= $last30 ?></b><small><?= $total ?> en total</small></div>
  <div class="kpi-c hot"><span>Sin atender</span><b><?= $pending ?></b><small><a href="/admin/leads?estado=nuevo">Ver nuevos →</a></small></div>
  <div class="kpi-c"><span>Puntaje IA promedio</span><b><?= $avgIa !== null ? $avgIa . '<em>/100</em>' : '—' ?></b><small>de los diagnósticos</small></div>
  <div class="kpi-c"><span>Pauta declarada (30 d)</span><b>USD <?= number_format($budgetUsd, 0, ',', '.') ?></b><small>en planes de pauta</small></div>
  <div class="kpi-c"><span>Ganados</span><b><?= $won ?></b><small><?= $total ? round($won / $total * 100) : 0 ?>% de conversión</small></div>
</div>

<div class="grid-2">
  <section class="card">
    <h2>Leads por día · últimos 30 días</h2>
    <div class="bars">
      <?php foreach ($daily as $d => $n): ?><div class="bar" title="<?= e(date('d/m', strtotime($d))) ?>: <?= $n ?>"><i style="height:<?= round($n / $max * 100) ?>%"></i></div><?php endforeach; ?>
    </div>
    <div class="bars-axis"><span><?= date('d/m', strtotime(array_key_first($daily))) ?></span><span>hoy</span></div>
  </section>
  <section class="card">
    <h2>Por origen</h2>
    <?php foreach ($types as $k => $l): $n = $byType[$k] ?? 0; ?>
      <div class="hbar"><span><?= e($l) ?></span><div><i style="width:<?= $total ? round($n / $total * 100) : 0 ?>%"></i></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
    <h2 style="margin-top:22px">Embudo</h2>
    <?php foreach ($sts as $k => $l): $n = $byStatus[$k] ?? 0; ?>
      <div class="hbar hb-<?= $k ?>"><span><?= e($l) ?></span><div><i style="width:<?= $total ? round($n / $total * 100) : 0 ?>%"></i></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
  </section>
</div>

<section class="card">
  <div class="card-head"><h2>Últimos leads</h2><a href="/admin/leads">Ver todos →</a></div>
  <?php partial('admin/_leads_table', ['leads' => $recent]); ?>
</section>

<div class="grid-3 mini">
  <a class="card link" href="/admin/casos"><b><?= $cases ?></b><span>casos de éxito publicados</span></a>
  <a class="card link" href="/diagnostico-ia" target="_blank"><b>IA</b><span>abrir el Diagnóstico IA-friendly ↗</span></a>
  <a class="card link<?= $mailFails ? ' warn' : '' ?>" href="/admin/ajustes"><b><?= $mailFails ?></b><span>correos con error (30 d)</span></a>
</div>
