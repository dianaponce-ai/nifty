<?php
$blocks = jdec($pr['blocks']); $items = jdec($pr['items']); $po = jdec($pr['pay_options']); $opts = $po['list'] ?? [];
$cur = $pr['currency'];
[$once, $monthly] = proposal_totals($items);
$onceItems = array_values(array_filter($items, fn($i) => ($i['kind'] ?? 'unico') !== 'mensual'));
$monItems = array_values(array_filter($items, fn($i) => ($i['kind'] ?? 'unico') === 'mensual'));
$done = $pr['status'] === 'aprobada';
$closed = in_array($pr['status'], ['rechazada', 'archivada'], true);
$expired = $pr['valid_until'] && strtotime($pr['valid_until'] . ' 23:59:59') < time() && !$done;
$f = flash();
$date = date('d/m/Y', strtotime($pr['sent_at'] ?: $pr['created_at']));
$money = fn($i) => (float) ($i['amount'] ?? 0) > 0 ? fmt_money((float) $i['amount'], $cur) : 'Incluido';
?>
<?php if ($isTeam): ?>
<div class="pp-teambar">
  <span><b>Vista del equipo</b> · tus visitas no se cuentan · estado: <?= e(proposal_statuses()[$pr['status']] ?? $pr['status']) ?> · <?= (int) $pr['views'] ?> vistas del cliente</span>
  <a href="/admin/propuestas/<?= (int) $pr['id'] ?>">Editar en el CRM →</a>
</div>
<?php endif; ?>

<div class="pp">
  <header class="pp-hero">
    <div class="pp-wrap">
      <div class="pp-chip"><i></i><?= e($pr['eyebrow'] ?: 'Propuesta comercial') ?> · <?= e($pr['number']) ?></div>
      <p class="pp-client"><?= e($pr['client_name']) ?><?php if ($pr['client_tagline']): ?><span><?= e($pr['client_tagline']) ?></span><?php endif; ?></p>
      <h1><?= e($pr['title'] ?: 'Propuesta para ' . $pr['client_name']) ?></h1>
      <?php if ($pr['subtitle']): ?><p class="pp-sub"><?= e($pr['subtitle']) ?></p><?php endif; ?>
      <div class="pp-meta">
        <div><b>Para</b><span><?= e($pr['client_name']) ?><?= $pr['contact_name'] ? ' · ' . e($pr['contact_name']) : '' ?></span></div>
        <div><b>Preparada por</b><span>Nifty Agency</span></div>
        <div><b>Fecha</b><span><?= e($date) ?></span></div>
        <?php if ($pr['valid_until']): ?><div><b>Válida hasta</b><span><?= e(date('d/m/Y', strtotime($pr['valid_until']))) ?></span></div><?php endif; ?>
      </div>
      <div class="pp-hero-actions">
        <?php if (!$done && !$closed): ?><a class="pp-btn" href="#aprobar">Aprobar propuesta</a><?php endif; ?>
        <button class="pp-btn ghost" type="button" onclick="window.print()">Descargar PDF</button>
      </div>
    </div>
  </header>

  <main class="pp-body">
    <div class="pp-wrap">
      <?php foreach ($blocks as $b): $items_b = $b['items'] ?? []; ?>
        <section class="pp-block pp-<?= e($b['type']) ?>">
          <?php if (!empty($b['kicker'])): ?><p class="pp-kicker"><?= e($b['kicker']) ?></p><?php endif; ?>
          <?php if (!empty($b['title'])): ?><h2><?= e($b['title']) ?></h2><?php endif; ?>
          <?php if (!empty($b['text'])): ?><div class="pp-text"><?= md($b['text']) ?></div><?php endif; ?>
          <?php if ($items_b): ?>
            <?php if ($b['type'] === 'lista'): ?>
              <ul class="pp-checks"><?php foreach ($items_b as $it): ?><li><span>✓</span><div><b><?= e($it['t']) ?></b><?php if ($it['d']): ?> <?= e($it['d']) ?><?php endif; ?></div></li><?php endforeach; ?></ul>
            <?php elseif ($b['type'] === 'pasos'): ?>
              <ol class="pp-steps"><?php foreach ($items_b as $i => $it): ?><li><em><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></em><div><b><?= e($it['t']) ?></b><?php if ($it['d']): ?><p><?= e($it['d']) ?></p><?php endif; ?></div></li><?php endforeach; ?></ol>
            <?php else: ?>
              <div class="pp-cards n<?= min(3, count($items_b)) ?>"><?php foreach ($items_b as $it): ?><div class="pp-card"><b><?= e($it['t']) ?></b><?php if ($it['d']): ?><p><?= e($it['d']) ?></p><?php endif; ?></div><?php endforeach; ?></div>
            <?php endif; ?>
          <?php endif; ?>
          <?php if (!empty($b['callout'])): ?><div class="pp-callout"><?= md($b['callout']) ?></div><?php endif; ?>
        </section>
      <?php endforeach; ?>

      <?php if ($items): ?>
      <section class="pp-block pp-invest" id="inversion">
        <p class="pp-kicker">Inversión</p>
        <h2>Inversión del proyecto</h2>
        <?php foreach ([['Inversión única', $onceItems, $once, ''], ['Servicios mensuales', $monItems, $monthly, ' / mes']] as [$lbl, $list, $tot, $suf]): if (!$list) continue; ?>
          <div class="pp-table">
            <div class="pp-table-head"><span><?= e($lbl) ?></span><span>Monto</span></div>
            <?php foreach ($list as $it): ?>
              <div class="pp-row<?= !empty($it['optional']) ? ' opt' : '' ?>">
                <div><b><?= e($it['concept']) ?></b><?php if (!empty($it['optional'])): ?><em>Opcional</em><?php endif; ?><?php if (!empty($it['detail'])): ?><span><?= e($it['detail']) ?></span><?php endif; ?></div>
                <strong><?= e($money($it)) ?><?= (float) ($it['amount'] ?? 0) > 0 ? e($suf) : '' ?></strong>
              </div>
            <?php endforeach; ?>
            <?php if ($tot > 0): ?><div class="pp-total"><span>Total<?= $suf ? ' mensual' : '' ?></span><strong><?= e(fmt_money($tot, $cur)) ?><?= e($suf) ?></strong></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
        <p class="pp-fine">Valores en <?= $cur === 'USD' ? 'dólares estadounidenses' : 'pesos argentinos' ?><?= $cur === 'ARS' ? ', no incluyen IVA salvo que se indique lo contrario' : '' ?>.</p>
      </section>
      <?php endif; ?>

      <?php if ($opts): ?>
      <section class="pp-block">
        <p class="pp-kicker">Formas de pago</p>
        <h2>Elegí cómo preferís abonar</h2>
        <div class="pp-cards n<?= min(3, count($opts)) ?>"><?php foreach ($opts as $o): ?><div class="pp-card pay<?= $done && $pr['pay_choice'] === $o['label'] ? ' chosen' : '' ?>"><b><?= e($o['label']) ?></b><?php if (!empty($o['detail'])): ?><p><?= e($o['detail']) ?></p><?php endif; ?></div><?php endforeach; ?></div>
      </section>
      <?php endif; ?>

      <?php if ($pr['terms']): ?>
      <section class="pp-block pp-terms"><p class="pp-kicker">Condiciones</p><div class="pp-text"><?= md($pr['terms']) ?></div></section>
      <?php endif; ?>

      <!-- Aprobación -->
      <section class="pp-approve" id="aprobar">
        <?php if ($f): ?><div class="pp-alert <?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
        <?php if ($done): ?>
          <div class="pp-done">
            <div class="pp-done-check">✓</div>
            <h2>Propuesta aprobada</h2>
            <p>Aprobada por <b><?= e($pr['approver_name']) ?></b><?= $pr['approver_doc'] ? ' (' . e($pr['approver_doc']) . ')' : '' ?> el <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($pr['decided_at']))) ?><?= $pr['pay_choice'] ? ' · Forma de pago: <b>' . e($pr['pay_choice']) . '</b>' : '' ?>.</p>
            <?php if ($pr['signature']): ?><img class="pp-sig-img" src="<?= e($pr['signature']) ?>" alt="Firma de <?= e($pr['approver_name']) ?>"><?php endif; ?>
            <?php if (isset($_GET['aprobada'])): ?><p class="pp-fine">¡Gracias por confiar en Nifty! Te enviamos el resumen por correo y en breve te contactamos para coordinar el inicio.</p><?php endif; ?>
          </div>
        <?php elseif ($closed): ?>
          <div class="pp-done muted"><h2>Esta propuesta ya no está vigente</h2><p>Escribinos y la actualizamos para vos.</p></div>
        <?php else: ?>
          <h2>Aprobar la propuesta</h2>
          <?php if ($expired): ?><p class="pp-warn">La fecha de validez de esta propuesta ya pasó. Podés aprobarla igual y confirmamos los valores con vos.</p><?php endif; ?>
          <form method="post" action="/p/<?= e($pr['token']) ?>/decidir" class="pp-form" id="ppForm">
            <?= csrf_field() ?>
            <input type="hidden" name="decision" value="aprobar">
            <input type="hidden" name="signature" id="ppSig">
            <?php if ($opts): ?>
              <fieldset><legend>Forma de pago *</legend>
                <div class="pp-opts"><?php foreach ($opts as $i => $o): ?><label><input type="radio" name="pay_choice" value="<?= e($o['label']) ?>" <?= $i === 0 ? 'checked' : '' ?> required><span><b><?= e($o['label']) ?></b><?php if (!empty($o['detail'])): ?><small><?= e($o['detail']) ?></small><?php endif; ?></span></label><?php endforeach; ?></div>
              </fieldset>
            <?php endif; ?>
            <div class="pp-grid">
              <label>Nombre y apellido *<input name="approver_name" required autocomplete="name" value="<?= e($pr['contact_name']) ?>"></label>
              <label>DNI / CUIT<input name="approver_doc" autocomplete="off"></label>
            </div>
            <label>Firma *<span class="pp-hint">Firmá con el mouse o con el dedo</span></label>
            <div class="pp-sigpad"><canvas id="ppCanvas" width="900" height="240" aria-label="Recuadro para firmar"></canvas><button type="button" id="ppClear">Borrar</button></div>
            <label>Comentarios (opcional)<textarea name="note" rows="2"></textarea></label>
            <label class="pp-accept"><input type="checkbox" name="accept" value="1" required> Leí y acepto la propuesta, sus valores y condiciones en nombre de <?= e($pr['client_name']) ?>.</label>
            <button class="pp-btn big" type="submit">Aprobar propuesta</button>
          </form>
          <details class="pp-changes">
            <summary>¿Querés ajustar algo antes de aprobar?</summary>
            <form method="post" action="/p/<?= e($pr['token']) ?>/decidir" class="pp-form">
              <?= csrf_field() ?><input type="hidden" name="decision" value="cambios">
              <label>¿Qué te gustaría cambiar?<textarea name="note" rows="3" required></textarea></label>
              <button class="pp-btn ghost dark" type="submit">Enviar comentarios</button>
            </form>
          </details>
        <?php endif; ?>
      </section>
    </div>
  </main>
</div>

<script>
(function(){
  var c=document.getElementById('ppCanvas'); if(!c) return;
  var ctx=c.getContext('2d'), drawing=false, has=false, last=null;
  ctx.lineWidth=3; ctx.lineCap='round'; ctx.lineJoin='round'; ctx.strokeStyle='#1a1033';
  function pos(e){var r=c.getBoundingClientRect(), p=e.touches?e.touches[0]:e; return {x:(p.clientX-r.left)*c.width/r.width, y:(p.clientY-r.top)*c.height/r.height};}
  function start(e){drawing=true; last=pos(e); e.preventDefault();}
  function move(e){if(!drawing) return; var p=pos(e); ctx.beginPath(); ctx.moveTo(last.x,last.y); ctx.lineTo(p.x,p.y); ctx.stroke(); last=p; has=true; e.preventDefault();}
  function end(){drawing=false;}
  c.addEventListener('mousedown',start); c.addEventListener('mousemove',move); window.addEventListener('mouseup',end);
  c.addEventListener('touchstart',start,{passive:false}); c.addEventListener('touchmove',move,{passive:false}); c.addEventListener('touchend',end);
  document.getElementById('ppClear').onclick=function(){ctx.clearRect(0,0,c.width,c.height); has=false;};
  document.getElementById('ppForm').addEventListener('submit',function(e){
    if(!has){e.preventDefault(); alert('Firmá en el recuadro para aprobar la propuesta.'); return;}
    document.getElementById('ppSig').value=c.toDataURL('image/png');
  });
})();
</script>
