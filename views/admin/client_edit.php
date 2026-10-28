<?php
$isNew = !$c;
$c = $c ?: ['id' => 0, 'name' => '', 'legal_name' => '', 'tax_id' => '', 'industry' => '', 'website' => '', 'contact_name' => '', 'contact_email' => '', 'contact_phone' => '',
    'status' => 'activo', 'fee_amount' => null, 'fee_currency' => 'ARS', 'fee_day' => null, 'services' => '', 'notes' => ''];
$title = $isNew ? 'Nuevo cliente' : $c['name'];
$actions = '<a class="abtn ghost" href="/admin/clientes">← Clientes</a>';
$f = $eco['fields'] ?? [];
$tab = $_GET['tab'] ?? 'ficha';
?>
<?php if (!$isNew): ?>
<nav class="tabs">
  <a href="?tab=ficha" class="<?= $tab === 'ficha' ? 'on' : '' ?>">Ficha y ecosistema</a>
  <a href="?tab=pauta" class="<?= $tab === 'pauta' ? 'on' : '' ?>">Paid media</a>
</nav>
<?php endif; ?>

<?php if ($tab === 'pauta' && !$isNew): ?>
  <section class="card">
    <h2>Paid media</h2>
    <p class="muted">Acá va el resumen de la estrategia de pauta, el presupuesto mes a mes por canal y la conexión con el reporte de Master Metrics. Se habilita en la próxima fase, cuando tengamos la documentación de la API de Master Metrics.</p>
    <?php if (!empty($f['reporte'])): ?><a class="abtn primary" href="<?= e($f['reporte']) ?>" target="_blank" rel="noopener">Abrir reporte actual ↗</a><?php endif; ?>
  </section>
<?php else: ?>
<form method="post" class="aform">
  <?= csrf_field() ?>
  <div class="grid-lead">
    <div>
      <section class="card">
        <h2>Datos del cliente</h2>
        <div class="field-row"><label>Nombre / marca *<input name="name" value="<?= e($c['name']) ?>" required></label><label>Rubro<input name="industry" value="<?= e($c['industry']) ?>"></label></div>
        <div class="field-row"><label>Razón social<input name="legal_name" value="<?= e($c['legal_name']) ?>"></label><label>CUIT<input name="tax_id" value="<?= e($c['tax_id']) ?>"></label></div>
        <div class="field-row"><label>Contacto principal<input name="contact_name" value="<?= e($c['contact_name']) ?>"></label><label>Email<input type="email" name="contact_email" value="<?= e($c['contact_email']) ?>"></label></div>
        <div class="field-row"><label>Teléfono / WhatsApp<input name="contact_phone" value="<?= e($c['contact_phone']) ?>"></label><label>Sitio web<input name="website" value="<?= e($c['website']) ?>"></label></div>
      </section>

      <section class="card">
        <h2>Ecosistema digital</h2>
        <p class="muted small">IDs y accesos de las herramientas del cliente. Más adelante el cliente va a poder ver esta información con su propio usuario.</p>
        <?php foreach (ecosystem_fields() as $group => $fields): ?>
          <h3><?= e($group) ?></h3>
          <div class="eco-grid"><?php foreach ($fields as $k => $l): ?><label><?= e($l) ?><input name="eco[<?= e($k) ?>]" value="<?= e($f[$k] ?? '') ?>"></label><?php endforeach; ?></div>
        <?php endforeach; ?>
        <label>Usuarios clave <span class="hint">uno por línea: <code>nombre | rol | email</code></span><textarea name="eco_people" rows="3"><?= e($eco['people'] ?? '') ?></textarea></label>
        <label>Accesos y notas técnicas <span class="hint">no guardes contraseñas acá: usá un gestor de contraseñas</span><textarea name="eco_access" rows="3"><?= e($eco['access'] ?? '') ?></textarea></label>
      </section>
    </div>
    <aside>
      <section class="card sticky">
        <h2>Cuenta</h2>
        <label>Estado<select name="status"><?php foreach (client_statuses() as $k => $l): ?><option value="<?= $k ?>"<?= $c['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <div class="field-row"><label>Fee mensual<input name="fee_amount" value="<?= $c['fee_amount'] !== null ? e(number_format((float) $c['fee_amount'], 0, ',', '.')) : '' ?>" inputmode="decimal"></label>
          <label>Moneda<select name="fee_currency"><option<?= $c['fee_currency'] === 'ARS' ? ' selected' : '' ?>>ARS</option><option<?= $c['fee_currency'] === 'USD' ? ' selected' : '' ?>>USD</option></select></label></div>
        <label>Día de facturación<input type="number" min="1" max="28" name="fee_day" value="<?= e((string) $c['fee_day']) ?>"></label>
        <label>Servicios contratados<textarea name="services" rows="4"><?= e((string) $c['services']) ?></textarea></label>
        <label>Notas<textarea name="notes" rows="3"><?= e((string) $c['notes']) ?></textarea></label>
        <button class="abtn primary block">Guardar ficha</button>
      </section>
      <?php if ($proposals): ?>
      <section class="card">
        <h2>Propuestas</h2>
        <ul class="mail-log"><?php foreach ($proposals as $p): ?><li><a href="/admin/propuestas/<?= (int) $p['id'] ?>"><b><?= e($p['number']) ?></b></a> · <span class="pill ps-<?= e($p['status']) ?>"><?= e(proposal_statuses()[$p['status']] ?? '') ?></span><span><?= e(mb_strimwidth($p['title'], 0, 60, '…')) ?></span></li><?php endforeach; ?></ul>
      </section>
      <?php endif; ?>
    </aside>
  </div>
</form>
<?php endif; ?>
