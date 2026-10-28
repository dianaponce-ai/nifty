<?php
$isNew = !$pr;
$pf = $prefill ?? null;
$pr = $pr ?: [
    'id' => 0, 'token' => '', 'number' => '(se asigna al guardar)', 'status' => 'borrador',
    'client_name' => $pf['company'] ?? ($pf['name'] ?? ''), 'client_tagline' => $pf['rubro'] ?? '', 'contact_name' => $pf['name'] ?? '',
    'contact_email' => $pf['email'] ?? '', 'contact_phone' => $pf['phone'] ?? '', 'website' => $pf['website'] ?? '',
    'eyebrow' => 'Propuesta comercial', 'title' => '', 'subtitle' => '', 'blocks' => jenc([
        ['type' => 'texto', 'kicker' => 'El punto de partida', 'title' => 'El desafío', 'text' => '', 'callout' => '', 'items' => []],
        ['type' => 'tarjetas', 'kicker' => 'Lo que proponemos', 'title' => 'La solución', 'text' => '', 'callout' => '', 'items' => []],
        ['type' => 'pasos', 'kicker' => 'Cómo trabajamos', 'title' => 'Plan de trabajo', 'text' => '', 'callout' => '', 'items' => []],
    ]),
    'currency' => 'ARS', 'items' => '[]', 'pay_options' => jenc(['cash' => true, 'discount' => 10, 'p7030' => true, 'custom' => '', 'list' => []]),
    'terms' => "- Los valores no incluyen IVA.\n- La propuesta no incluye la inversión en pauta publicitaria, que se abona directamente a cada plataforma.\n- Hosting y dominio a cargo del cliente salvo que se indique lo contrario.",
    'valid_until' => date('Y-m-d', strtotime('+15 days')), 'views' => 0, 'lead_id' => $pf['id'] ?? null, 'client_id' => null, 'legacy_url' => '',
];
$blocks = jdec($pr['blocks']); $items = jdec($pr['items']); $po = jdec($pr['pay_options']);
$itemLines = implode("\n", array_map(fn($i) => implode(' | ', [$i['concept'], $i['detail'] ?? '', rtrim(rtrim(number_format((float) $i['amount'], 2, ',', '.'), '0'), ','), ($i['kind'] ?? 'unico') === 'mensual' ? 'mensual' : 'único'] + (!empty($i['optional']) ? [4 => 'opcional'] : [])), $items));
$title = $isNew ? 'Nueva propuesta' : $pr['number'] . ' · ' . $pr['client_name'];
$actions = '<a class="abtn ghost" href="/admin/propuestas">← Propuestas</a>' . (!$isNew ? '<a class="abtn" href="/p/' . e($pr['token']) . '" target="_blank">Ver como cliente ↗</a>' : '');
[$once, $monthly] = proposal_totals($items);
$blockTpl = function ($i, $b) { ob_start(); ?>
  <div class="blk-card" data-i="<?= e($i) ?>">
    <div class="blk-head">
      <select name="blocks[<?= e($i) ?>][type]"><?php foreach (block_types() as $k => $l): ?><option value="<?= $k ?>"<?= ($b['type'] ?? '') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <span class="blk-tools"><button type="button" class="abtn sm ghost" data-mv="-1" title="Subir">↑</button><button type="button" class="abtn sm ghost" data-mv="1" title="Bajar">↓</button><button type="button" class="abtn sm danger" data-del title="Quitar">×</button></span>
    </div>
    <div class="field-row"><label>Etiqueta<input name="blocks[<?= e($i) ?>][kicker]" value="<?= e($b['kicker'] ?? '') ?>" placeholder="Ej: El desafío"></label><label>Título<input name="blocks[<?= e($i) ?>][title]" value="<?= e($b['title'] ?? '') ?>"></label></div>
    <label>Texto <span class="hint">**negrita**, líneas con “- ” para listas, línea en blanco = párrafo</span><textarea name="blocks[<?= e($i) ?>][text]" rows="3"><?= e($b['text'] ?? '') ?></textarea></label>
    <label>Ítems <span class="hint">uno por línea: <code>título | descripción</code></span><textarea name="blocks[<?= e($i) ?>][items]" rows="3"><?= e(implode("\n", array_map(fn($x) => trim($x['t'] . ($x['d'] !== '' ? ' | ' . $x['d'] : '')), $b['items'] ?? []))) ?></textarea></label>
    <label>Destacado <span class="hint">opcional, recuadro al final del bloque</span><textarea name="blocks[<?= e($i) ?>][callout]" rows="2"><?= e($b['callout'] ?? '') ?></textarea></label>
  </div>
<?php return ob_get_clean(); };
?>
<form method="post" class="aform" id="propForm" action="<?= $isNew ? '/admin/propuestas/nueva' : '/admin/propuestas/' . (int) $pr['id'] ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="lead_id" value="<?= e((string) ($pr['lead_id'] ?? '')) ?>">
  <input type="hidden" name="_after" value="" id="propAfter">
  <div class="grid-lead">
    <div>
      <section class="card">
        <h2>1 · Cliente</h2>
        <div class="field-row">
          <label>Cliente / marca *<input name="client_name" value="<?= e($pr['client_name']) ?>" required></label>
          <label>Bajada del cliente <span class="hint">ej. “Cirujano plástico & medicina estética”</span><input name="client_tagline" value="<?= e($pr['client_tagline']) ?>"></label>
        </div>
        <div class="field-row">
          <label>Contacto<input name="contact_name" value="<?= e($pr['contact_name']) ?>"></label>
          <label>Email del cliente<input type="email" name="contact_email" value="<?= e($pr['contact_email']) ?>"></label>
        </div>
        <div class="field-row">
          <label>Teléfono<input name="contact_phone" value="<?= e($pr['contact_phone']) ?>"></label>
          <label>Sitio web<input name="website" value="<?= e($pr['website']) ?>"></label>
        </div>
      </section>

      <section class="card">
        <h2>2 · Portada</h2>
        <div class="field-row">
          <label>Etiqueta<input name="eyebrow" value="<?= e($pr['eyebrow']) ?>" placeholder="Propuesta comercial"></label>
          <label>Válida hasta<input type="date" name="valid_until" value="<?= e((string) $pr['valid_until']) ?>"></label>
        </div>
        <label>Título<input name="title" value="<?= e($pr['title']) ?>" placeholder="Ej: Propuesta de Ecosistema Digital Premium"></label>
        <label>Bajada<textarea name="subtitle" rows="2"><?= e($pr['subtitle']) ?></textarea></label>
      </section>

      <section class="card">
        <div class="card-head"><h2>3 · Contenido</h2><button type="button" class="abtn sm" id="addBlock">+ Agregar bloque</button></div>
        <div id="blocks"><?php foreach ($blocks as $i => $b) echo $blockTpl($i, $b); ?></div>
        <template id="blockTpl"><?= $blockTpl('__I__', ['type' => 'texto']) ?></template>
      </section>

      <section class="card">
        <h2>4 · Inversión</h2>
        <div class="field-row"><label>Moneda<select name="currency"><option value="ARS"<?= $pr['currency'] === 'ARS' ? ' selected' : '' ?>>Pesos (ARS)</option><option value="USD"<?= $pr['currency'] === 'USD' ? ' selected' : '' ?>>Dólares (USD)</option></select></label><span></span></div>
        <label>Ítems <span class="hint">uno por línea: <code>concepto | detalle | monto | único o mensual | opcional</code></span>
          <textarea name="items" rows="6" placeholder="Diseño y desarrollo del sitio web | Sitio de 6 páginas con SEO + GEO | 1.700.000 | único&#10;Gestión de pauta en Google y Meta | Optimización semanal y reporte mensual | 450.000 | mensual&#10;Producción de contenido UGC | 4 videos por mes | 300.000 | mensual | opcional"><?= e($itemLines) ?></textarea></label>
        <p class="muted small">Total actual: <b><?= e(fmt_money($once, $pr['currency'])) ?></b> único<?= $monthly ? ' · <b>' . e(fmt_money($monthly, $pr['currency'])) . '</b> mensual' : '' ?> (se recalcula al guardar).</p>
        <h3>Formas de pago</h3>
        <div class="checks-row">
          <label class="chk"><input type="checkbox" name="pay_cash" value="1"<?= !empty($po['cash']) ? ' checked' : '' ?>> Pago de contado con descuento</label>
          <label style="max-width:160px">Descuento %<input name="pay_discount" value="<?= e((string) ($po['discount'] ?? 10)) ?>" inputmode="decimal"></label>
          <label class="chk"><input type="checkbox" name="pay_7030" value="1"<?= !empty($po['p7030']) ? ' checked' : '' ?>> 70% al inicio / 30% al finalizar</label>
        </div>
        <label>Otras opciones <span class="hint">una por línea: <code>nombre | detalle</code></span><textarea name="pay_custom" rows="2" placeholder="Pago en 3 cuotas | 3 pagos mensuales sin interés"><?= e($po['custom'] ?? '') ?></textarea></label>
        <label>Condiciones<textarea name="terms" rows="4"><?= e($pr['terms']) ?></textarea></label>
      </section>
    </div>

    <aside>
      <section class="card sticky">
        <h2><?= e($pr['number']) ?></h2>
        <p><span class="pill ps-<?= e($pr['status']) ?>"><?= e(proposal_statuses()[$pr['status']] ?? $pr['status']) ?></span><?= $pr['views'] ? ' <span class="muted small">' . (int) $pr['views'] . ' vistas</span>' : '' ?></p>
        <button class="abtn primary block">Guardar</button>
        <button class="abtn block" style="margin-top:8px" onclick="document.getElementById('propAfter').value='preview'">Guardar y ver</button>
        <?php if (!$isNew && $pr['client_id']): ?><a class="abtn ghost block" style="margin-top:8px" href="/admin/clientes/<?= (int) $pr['client_id'] ?>">Ver ficha del cliente</a><?php endif; ?>
        <?php if (!empty($pr['legacy_url'])): ?><p class="muted small" style="margin-top:12px">Importada de WordPress: <a href="<?= e($pr['legacy_url']) ?>" target="_blank">original ↗</a></p><?php endif; ?>
      </section>
</form>

      <?php if (!$isNew): ?>
      <section class="card">
        <h2>Enviar al cliente</h2>
        <form method="post" action="/admin/propuestas/<?= (int) $pr['id'] ?>/accion" class="aform">
          <?= csrf_field() ?><input type="hidden" name="act" value="send">
          <p class="muted small">Para: <b><?= e($pr['contact_email'] ?: 'falta el email') ?></b><br>Copia: <?= e(implode(', ', team_emails())) ?></p>
          <label>Mensaje (opcional)<textarea name="message" rows="4" placeholder="Si lo dejás vacío se usa el texto estándar."></textarea></label>
          <button class="abtn primary block"<?= $pr['contact_email'] ? '' : ' disabled' ?>>Enviar propuesta</button>
        </form>
      </section>

      <section class="card">
        <h2>Estado</h2>
        <form method="post" action="/admin/propuestas/<?= (int) $pr['id'] ?>/accion" class="aform">
          <?= csrf_field() ?><input type="hidden" name="act" value="status">
          <label><select name="status"><?php foreach (proposal_statuses() as $k => $l): ?><option value="<?= $k ?>"<?= $pr['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
          <button class="abtn block">Cambiar estado</button>
          <p class="muted small">Marcarla como aprobada crea la ficha del cliente.</p>
        </form>
        <?php if ($pr['status'] === 'aprobada'): ?>
          <p class="small"><b>Aprobó:</b> <?= e($pr['approver_name']) ?><?= $pr['approver_doc'] ? ' (' . e($pr['approver_doc']) . ')' : '' ?><br><b>Pago:</b> <?= e($pr['pay_choice'] ?: '—') ?><br><b>Fecha:</b> <?= $pr['decided_at'] ? e(date('d/m/Y H:i', strtotime($pr['decided_at']))) : '—' ?></p>
          <?php if ($pr['signature']): ?><img src="<?= e($pr['signature']) ?>" alt="Firma" style="width:100%;border:1px solid #eee;border-radius:8px"><?php endif; ?>
        <?php elseif ($pr['decision_note']): ?><p class="note small"><b>Pedido del cliente:</b><br><?= nl2br(e($pr['decision_note'])) ?></p><?php endif; ?>
        <div class="row-actions" style="margin-top:12px">
          <form method="post" action="/admin/propuestas/<?= (int) $pr['id'] ?>/accion"><?= csrf_field() ?><input type="hidden" name="act" value="duplicate"><button class="abtn sm">Duplicar</button></form>
          <?php if (($me['role'] ?? '') === 'admin'): ?><form method="post" action="/admin/propuestas/<?= (int) $pr['id'] ?>/accion" onsubmit="return confirm('¿Eliminar la propuesta? No se puede deshacer.')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><button class="abtn sm danger">Eliminar</button></form><?php endif; ?>
        </div>
      </section>

      <section class="card">
        <h2>Actividad</h2>
        <ul class="timeline"><?php foreach ($events as $ev): ?><li class="ev-<?= e($ev['kind']) ?>"><span class="when"><?= e(date('d/m H:i', strtotime($ev['created_at']))) ?><?= $ev['user_name'] ? ' · ' . e($ev['user_name']) : '' ?></span><p><?= e($ev['body']) ?></p></li><?php endforeach; ?></ul>
      </section>
      <?php endif; ?>
    </aside>
  </div>

<script>
(function(){
  var box=document.getElementById('blocks'), tpl=document.getElementById('blockTpl').innerHTML, n=box.children.length+100;
  document.getElementById('addBlock').onclick=function(){ box.insertAdjacentHTML('beforeend', tpl.replace(/__I__/g, n++)); };
  box.addEventListener('click', function(e){
    var card=e.target.closest('.blk-card'); if(!card) return;
    if(e.target.hasAttribute('data-del')){ if(confirm('¿Quitar este bloque?')) card.remove(); }
    var mv=e.target.getAttribute('data-mv');
    if(mv==='-1' && card.previousElementSibling) box.insertBefore(card, card.previousElementSibling);
    if(mv==='1' && card.nextElementSibling) box.insertBefore(card.nextElementSibling, card);
  });
})();
</script>
