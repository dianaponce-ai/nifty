<?php $title = 'Usuarios del equipo'; ?>
<div class="grid-lead">
  <section class="card">
    <div class="table-wrap"><table class="atable">
      <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Último ingreso</th><th></th></tr></thead>
      <tbody><?php foreach ($users as $u): ?>
        <tr><td><b><?= e($u['name']) ?></b></td><td><?= e($u['email']) ?></td><td><span class="pill"><?= e($u['role']) ?></span></td>
          <td class="small muted"><?= $u['last_login'] ? e(date('d/m/Y H:i', strtotime($u['last_login']))) : '—' ?></td>
          <td class="nowrap">
            <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="act" value="password"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><input type="password" name="password" placeholder="Nueva contraseña" minlength="10" required><button class="abtn sm">Cambiar</button></form>
            <?php if ((int) $u['id'] !== (int) $me['id']): ?><form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar usuario?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><button class="abtn danger sm">×</button></form><?php endif; ?>
          </td></tr>
      <?php endforeach; ?></tbody></table></div>
  </section>
  <aside><section class="card">
    <h2>Nuevo usuario</h2>
    <form method="post" class="aform"><?= csrf_field() ?><input type="hidden" name="act" value="add">
      <label>Nombre<input name="name" required></label>
      <label>Email<input type="email" name="email" required></label>
      <label>Rol<select name="role"><option value="equipo">Equipo (CRM y contenidos)</option><option value="admin">Administrador</option></select></label>
      <label>Contraseña (mín. 10)<input type="password" name="password" minlength="10" required autocomplete="new-password"></label>
      <button class="abtn primary block">Crear usuario</button>
    </form>
    <p class="muted small">Los usuarios con rol “cliente” se crean automáticamente al aprobar una propuesta (Fase 2).</p>
  </section></aside>
</div>
