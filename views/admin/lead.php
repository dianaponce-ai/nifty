<?php
$types = lead_types(); $sts = lead_statuses();
$title = ($lead['company'] ?: $lead['name'] ?: 'Lead') . ' · ' . ($types[$lead['type']] ?? '');
$resultUrl = match ($lead['type']) { 'diagnostico_ia' => '/diagnostico-ia/resultado/' . $lead['token'], 'plan_pauta' => '/plan-de-pauta/resultado/' . $lead['token'], default => null };
$actions = '<a class="abtn ghost" href="/admin/leads">← Leads</a>' . ($resultUrl ? '<a class="abtn" href="' . e($resultUrl) . '" target="_blank">Ver lo que recibió el cliente ↗</a>' : '');
$labels = ['respuestas' => 'Respuestas', 'presupuesto' => 'Presupuesto', 'objetivo' => 'Objetivo', 'situacion' => 'Situación', 'mensaje' => 'Mensaje', 'producto' => 'Producto / interés',
    'tamano' => 'Tamaño', 'hosting' => 'Hosting', 'ciudad' => 'Ciudad', 'negocio' => 'Negocio', 'rubro' => 'Rubro', 'web' => 'Web', 'modelo' => 'Modelo', 'publico' => 'Público',
    'zona' => 'Zona', 'ciclo' => 'Ciclo de venta', 'moneda' => 'Moneda', 'ticket' => 'Ticket promedio', 'medicion' => 'Medición', 'crm' => 'CRM', 'video' => 'Video',
    'competidores' => 'Competidores', 'fecha' => 'Fecha clave', 'canales_actuales' => 'Canales actuales', 'consent' => 'Acepta contacto'];
$firstName = explode(' ', trim($lead['name']))[0] ?: '';
$defaultSubject = match ($lead['type']) {
    'diagnostico_ia' => 'Tu diagnóstico IA-friendly: ¿lo revisamos juntos?',
    'plan_pauta' => 'Tu plan de pauta ' . ($result['code'] ?? '') . ': próximos pasos',
    'fabrica' => 'Demo del sistema — Nifty Agency',
    default => 'Tu consulta en Nifty Agency',
};
$defaultBody = "Hola $firstName,\n\nGracias por " . match ($lead['type']) {
    'diagnostico_ia' => 'hacer el diagnóstico IA-friendly. Vimos tu resultado (' . (int) $lead['score'] . "/100) y hay varias mejoras concretas que podemos implementar rápido.",
    'plan_pauta' => 'armar tu plan de pauta de prueba. Nos gustaría ajustarlo con los datos reales de tu negocio.',
    'fabrica' => 'tu interés en nuestros sistemas. Nos encantaría mostrarte cómo funciona.',
    default => 'escribirnos. Revisamos tu consulta y queremos proponerte una reunión breve.',
} . "\n\n¿Te parece si coordinamos una reunión de 30 minutos esta semana?\n\nSaludos,";
?>
<div class="grid-lead">
  <div>
    <section class="card">
      <div class="lead-head">
        <div>
          <span class="tag t-<?= e($lead['type']) ?>"><?= e($types[$lead['type']] ?? $lead['type']) ?></span>
          <h2><?= e($lead['name']) ?></h2>
          <p class="muted"><?= e($lead['company']) ?><?= $lead['rubro'] ? ' · ' . e($lead['rubro']) : '' ?></p>
        </div>
        <?php if ($lead['score'] !== null): ?><div class="score big" style="--c:<?= e($result['color'] ?? '#999') ?>"><?= (int) $lead['score'] ?><small>/100</small></div><?php endif; ?>
      </div>
      <div class="contact-actions">
        <?php if ($lead['email']): ?><a class="abtn" href="mailto:<?= e($lead['email']) ?>"><?= icon('chat', 16) ?><?= e($lead['email']) ?></a><?php endif; ?>
        <?php if ($lead['phone']): ?><a class="abtn wa" target="_blank" rel="noopener" href="<?= e(wa_link($lead['phone'], "Hola $firstName, te escribimos de Nifty Agency.")) ?>">WhatsApp <?= e($lead['phone']) ?></a><?php endif; ?>
        <?php if ($lead['website']): ?><a class="abtn ghost" target="_blank" rel="noopener" href="<?= e(preg_match('~^https?://~', $lead['website']) ? $lead['website'] : 'https://' . $lead['website']) ?>"><?= icon('globe', 16) ?>Sitio</a><?php endif; ?>
      </div>
      <p class="muted small">Ingresó el <?= e(date('d/m/Y H:i', strtotime($lead['created_at']))) ?><?= $lead['source'] ? ' · desde ' . e(parse_url($lead['source'], PHP_URL_PATH) ?: $lead['source']) : '' ?></p>
    </section>

    <?php if ($lead['type'] === 'diagnostico_ia' && $result): ?>
    <section class="card">
      <h2>Diagnóstico: <?= e($result['level'] ?? '') ?></h2>
      <p class="muted">Sitio <?= (int) ($result['site_score'] ?? 0) ?>/100 · Señales externas <?= (int) ($result['ext_score'] ?? 0) ?>/100</p>
      <?php if (!empty($result['ai_note'])): ?><p class="note"><?= nl2br(e($result['ai_note'])) ?></p><?php endif; ?>
      <h3>Prioridades para la propuesta</h3>
      <ol class="reco-mini"><?php foreach (array_slice($result['recos'] ?? [], 0, 8) as $r): ?><li><span class="pill prio-<?= e($r['prio']) ?>"><?= e($r['prio']) ?></span> <b><?= e($r['title']) ?></b></li><?php endforeach; ?></ol>
      <?php if (!empty($result['site']['bots'])): $blocked = array_keys(array_filter($result['site']['bots'], fn($s) => $s === 'bloqueado')); ?>
        <p class="small"><b>Bots de IA bloqueados:</b> <?= $blocked ? e(implode(', ', $blocked)) : 'ninguno' ?></p>
      <?php endif; ?>
    </section>
    <?php elseif ($lead['type'] === 'plan_pauta' && $result): ?>
    <section class="card">
      <h2>Plan <?= e($result['code'] ?? '') ?> · <?= e(money((float) $result['budget'], $result['currency'])) ?>/mes</h2>
      <p class="muted"><?= e($result['objective'] ?? '') ?> · <?= e($result['rubro'] ?? '') ?></p>
      <?php foreach ($result['channels'] ?? [] as $c): ?><div class="hbar"><span><?= e($c['name']) ?></span><div><i style="width:<?= (int) $c['pct'] ?>%"></i></div><b><?= (int) $c['pct'] ?>%</b></div><?php endforeach; ?>
      <?php if (!empty($result['prereq'])): ?><h3>Pendientes detectados</h3><ul><?php foreach ($result['prereq'] as $p): ?><li><b><?= e($p[0]) ?>:</b> <?= e($p[1]) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="card">
      <h2>Datos que dejó</h2>
      <dl class="kv">
        <?php foreach ($payload as $k => $v): if ($v === '' || $v === null || $k === 'email') continue; ?>
          <dt><?= e($labels[$k] ?? $k) ?></dt>
          <dd><?php if (is_array($v)): ?><?php foreach ($v as $kk => $vv): ?><div><span class="muted"><?= e($kk) ?>:</span> <?= e(is_bool($vv) ? ($vv ? 'sí' : 'no') : $vv) ?></div><?php endforeach; ?><?php else: ?><?= nl2br(e(is_bool($v) ? ($v ? 'sí' : 'no') : $v)) ?><?php endif; ?></dd>
        <?php endforeach; ?>
      </dl>
    </section>

    <?php if ($lead['email']): ?>
    <section class="card">
      <h2>Enviar correo al cliente</h2>
      <form method="post" class="aform">
        <?= csrf_field() ?><input type="hidden" name="act" value="mail">
        <label>Asunto<input name="subject" value="<?= e($defaultSubject) ?>" required></label>
        <label>Mensaje<textarea name="body" rows="8" required><?= e($defaultBody) ?></textarea></label>
        <div class="checks-row">
          <label class="chk"><input type="checkbox" name="add_agenda" value="1" checked> Incluir botón para agendar</label>
          <label class="chk"><input type="checkbox" name="cc_team" value="1" checked> Copia al equipo (<?= e(implode(', ', array_map(fn($m) => explode('@', $m)[0], team_emails()))) ?>)</label>
        </div>
        <button class="abtn primary">Enviar correo</button>
        <p class="muted small">Se envía con el diseño de Nifty y queda registrado en la línea de tiempo. Las respuestas llegan a tu casilla (Reply-To).</p>
      </form>
    </section>
    <?php endif; ?>
  </div>

  <aside>
    <section class="card">
      <h2>Seguimiento</h2>
      <form method="post" class="aform">
        <?= csrf_field() ?><input type="hidden" name="act" value="status">
        <label>Estado<select name="status"><?php foreach ($sts as $k => $l): ?><option value="<?= $k ?>"<?= $lead['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label>Responsable<select name="assigned_to"><option value="">— Sin asignar —</option><?php foreach ($team as $t): ?><option value="<?= (int) $t['id'] ?>"<?= (int) $lead['assigned_to'] === (int) $t['id'] ? ' selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select></label>
        <button class="abtn primary block">Guardar</button>
      </form>
      <a class="abtn ghost block" style="margin-top:10px" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener"><?= icon('calendar', 16) ?>Abrir agenda de reuniones</a>
    </section>

    <section class="card">
      <h2>Notas y actividad</h2>
      <form method="post" class="aform">
        <?= csrf_field() ?><input type="hidden" name="act" value="note">
        <textarea name="note" rows="3" placeholder="Ej: llamé, pidió propuesta para enero…" required></textarea>
        <button class="abtn">Agregar nota</button>
      </form>
      <ul class="timeline">
        <?php foreach ($events as $ev): ?>
          <li class="ev-<?= e($ev['kind']) ?>"><span class="when"><?= e(date('d/m H:i', strtotime($ev['created_at']))) ?><?= $ev['user_name'] ? ' · ' . e($ev['user_name']) : '' ?></span><p><?= nl2br(e($ev['body'])) ?></p></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <?php if ($mails): ?>
    <section class="card">
      <h2>Correos enviados</h2>
      <ul class="mail-log"><?php foreach ($mails as $m): ?><li class="<?= $m['ok'] ? 'ok' : 'err' ?>"><b><?= e($m['subject']) ?></b><span><?= e($m['to_addr']) ?> · <?= e(date('d/m H:i', strtotime($m['created_at']))) ?><?= $m['error'] ? ' · ' . e($m['error']) : '' ?></span></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>
  </aside>
</div>
