<h1>Instalación inicial</h1>
<p class="muted">Se crean las tablas, se cargan los casos de éxito y las reseñas, y se crea el primer usuario administrador.</p>
<?php if ($err): ?><div class="adm-flash err"><?= e($err) ?></div><?php endif; ?>
<form method="post" class="aform">
  <?= csrf_field() ?>
  <label>Nombre<input name="name" required value="Diana Ponce"></label>
  <label>Email<input type="email" name="email" required value="diana@nifty-agency.com"></label>
  <label>Contraseña (mín. 10 caracteres)<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
  <button class="abtn primary block">Instalar y crear administrador</button>
</form>
