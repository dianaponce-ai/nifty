<section class="page-hero center">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap">
    <span class="eyebrow eyebrow-mono">// sitio en configuración</span>
    <?php if ($isAdmin): ?>
      <h1>El backend todavía <em>no está conectado.</em></h1>
      <p class="lead">Falta conectar la base de datos. En Vercel → Settings → Environment Variables cargá <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code> y <code>DB_PASS</code> (y el SMTP), hacé Redeploy y entrá a <code>/admin/setup</code>.</p>
    <?php else: ?>
      <h1>Estamos terminando de <em>configurar el sitio.</em></h1>
      <p class="lead">Tu mensaje no se envió todavía. Escribinos directamente y te respondemos enseguida.</p>
      <div class="hero-ctas center">
        <a class="btn btn-primary" href="mailto:<?= e(setting('contact_email')) ?>">Escribir a <?= e(setting('contact_email')) ?></a>
        <a class="btn btn-ghost" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener">Agendar reunión</a>
      </div>
    <?php endif; ?>
  </div>
</section>
