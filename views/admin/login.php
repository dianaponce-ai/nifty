<h1>Ingresar</h1>
<p class="muted">Backend de Nifty Agency</p>
<?php if ($err): ?><div class="adm-flash err"><?= e($err) ?></div><?php endif; ?>
<form method="post" class="aform">
  <?= csrf_field() ?>
  <label>Email<input type="email" name="email" required autofocus autocomplete="username"></label>
  <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
  <button class="abtn primary block">Ingresar</button>
</form>
<p class="muted small center"><a href="/">← Volver al sitio</a></p>
