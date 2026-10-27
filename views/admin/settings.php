<?php $title = 'Ajustes';
$fields = [
  'team_emails' => ['Correos del equipo', 'Reciben cada lead nuevo y van en copia de los correos al cliente. Separados por coma.'],
  'contact_email' => ['Correo de contacto público', 'Se muestra en el sitio y es el Reply-To de los correos automáticos.'],
  'whatsapp' => ['WhatsApp de Nifty', 'Formato internacional, solo números (ej: 5491112345678). Si está vacío, no se muestra el botón flotante.'],
  'agenda_url' => ['Link de agenda (Google Calendar)', 'Botón “Agendar reunión” en el sitio y en los correos.'],
  'fabrica_video_url' => ['Video de La Fábrica (peluquerías)', 'YouTube, Vimeo o .mp4.'],
  'lms_video_url' => ['Video del LMS', 'Opcional. Si está vacío se muestra una captura del campus.'],
  'usd_ars' => ['Tipo de cambio de referencia (ARS por USD)', 'Para las estimaciones del plan de pauta.'],
  'google_rating' => ['Puntaje en Google', ''], 'google_reviews' => ['Cantidad de reseñas en Google', ''], 'google_reviews_url' => ['Link a las reseñas de Google', ''],
  'gtm_id' => ['Google Tag Manager', 'ID del contenedor (GTM-XXXX).'], 'ga4_id' => ['GA4 (referencia)', 'Se carga desde GTM.'],
]; ?>
<div class="grid-lead">
  <section class="card">
    <form method="post" class="aform">
      <?= csrf_field() ?>
      <?php foreach ($fields as $k => [$l, $h]): ?><label><?= e($l) ?><input name="<?= $k ?>" value="<?= e($vals[$k]) ?>"><?php if ($h): ?><span class="hint"><?= e($h) ?></span><?php endif; ?></label><?php endforeach; ?>
      <button class="abtn primary">Guardar ajustes</button>
    </form>
  </section>
  <aside>
    <section class="card">
      <h2>Estado</h2>
      <p><span class="pill <?= $smtpOk ? 'st-ganado' : 'st-perdido' ?>"><?= $smtpOk ? 'SMTP configurado' : 'SMTP sin configurar' ?></span></p>
      <p class="small muted"><?= $smtpOk ? 'Los correos salen por Google Workspace.' : 'Los correos se guardan en storage/mail/ hasta que completes el SMTP en config.php.' ?></p>
      <p><span class="pill <?= $ai ? 'st-ganado' : 'st-contactado' ?>"><?= $ai ? 'IA (Claude) activa' : 'IA desactivada' ?></span></p>
      <p class="small muted">Con la API de Claude se generan casos de éxito y se suman lecturas personalizadas a los diagnósticos.</p>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="test_mail"><button class="abtn block">Enviarme un correo de prueba</button></form>
      <form method="post" action="/admin/setup" style="margin-top:8px"><?= csrf_field() ?><button class="abtn ghost block">Actualizar base de datos</button></form>
    </section>
    <section class="card">
      <h2>Últimos correos</h2>
      <ul class="mail-log"><?php foreach ($mails as $m): ?><li class="<?= $m['ok'] ? 'ok' : 'err' ?>"><b><?= e(mb_strimwidth($m['subject'], 0, 60, '…')) ?></b><span><?= e(mb_strimwidth($m['to_addr'], 0, 50, '…')) ?> · <?= e(date('d/m H:i', strtotime($m['created_at']))) ?><?= $m['error'] ? ' · ' . e($m['error']) : '' ?></span></li><?php endforeach; ?></ul>
    </section>
  </aside>
</div>
