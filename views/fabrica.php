<?php
$f = flash();
$pelu_mods = [
    ['calendar', 'Turnero inteligente', 'El corazón del sistema: agenda por profesional y por servicio, reservas online y recordatorios, conectada a todo lo demás.', ['agenda', 'por profesional', 'recordatorios', 'reserva online'], true],
    ['users', 'Fichas de clientes', 'Historial de servicios, preferencias, fórmulas de color, notas y frecuencia de visita de cada clienta.', ['historial', 'fórmulas', 'preferencias'], false],
    ['box', 'Insumos', 'Stock de productos y consumo por servicio: sabés qué se usa, cuánto queda y cuándo reponer.', ['stock', 'consumo', 'reposición'], false],
    ['coins', 'Costos y finanzas', 'Ingresos, gastos, costo real de cada servicio y rentabilidad del salón en tiempo real.', ['caja', 'costos', 'rentabilidad'], false],
    ['receipt', 'Comisiones', 'Cálculo automático por profesional según las reglas de tu salón. Sin planillas ni discusiones.', ['por servicio', 'liquidación'], false],
    ['megaphone', 'Marketing', 'Segmentá clientas, detectá quién hace tiempo no viene y armá acciones para traerla de vuelta.', ['segmentos', 'fidelización', 'reactivación'], false],
];
$lms_mods = [
    ['receipt', 'Inscripciones y cobros', 'La planilla de inscripción crea al alumno en el sistema administrativo y en el campus, con su cohorte asignada.'],
    ['users', 'Cohortes y roles', 'Administración, docentes, alumnos y alumnos UGC, cada uno con su vista y sus permisos.'],
    ['play', 'Aula virtual', 'Clases en video con desbloqueo secuencial, materiales y seguimiento del avance de cada alumno.'],
    ['check', 'Evaluaciones automáticas', 'Tests propios con corrección automática y planilla de calificación por clase.'],
    ['cert', 'Certificados', 'Emisión de certificados por programa al completar los requisitos.'],
    ['calendar', 'Recuperación y workshops', 'Calendario de clases extra con inscripción, profesor asignado y lista de anotados.'],
    ['sparkle', 'Asistente con IA', 'Un asistente conectado a Claude que acompaña a los alumnos dentro del campus.'],
    ['coins', 'Sistema administrativo y contable', 'Pagos, cuotas y estado de cada alumno, integrados con la operación académica.'],
    ['chat', 'CRM y WhatsApp', 'Leads, seguimiento comercial y conversaciones de WhatsApp (API oficial) en el mismo sistema.'],
    ['pen', 'Rol UGC', 'Alumnos que pagan su curso con contenido: plan de entregas, avisos y control de cumplimiento.'],
];
?>
<section class="page-hero fab-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div></div>
  <div class="wrap">
    <span class="eyebrow eyebrow-mono">&lt;/&gt; La Fábrica de Nifty</span>
    <h1>Software propio para tu negocio, <em>hecho con IA.</em></h1>
    <p class="lead">En La Fábrica desarrollamos sistemas a medida: no son suscripciones compartidas, son <strong>tuyos</strong>, instalados donde quieras y adaptados a cómo trabajás. Algunos ya están listos para personalizar e implementar en semanas.</p>
    <nav class="pill-nav" aria-label="Productos de La Fábrica">
      <a href="#peluquerias"><?= icon('scissors', 16) ?>Sistema para peluquerías</a>
      <a href="#lms"><?= icon('cap', 16) ?>LMS / escuelas</a>
      <a href="#a-medida"><?= icon('puzzle', 16) ?>Desarrollos a medida</a>
    </nav>
  </div>
</section>

<!-- ======================= PELUQUERÍAS ======================= -->
<section id="peluquerias" class="fab-product">
  <div class="wrap">
    <div class="fab-product-head reveal">
      <span class="eyebrow">Sistema para peluquerías</span>
      <h2>Tu peluquería con <em>su propio sistema</em> de gestión.</h2>
      <p class="lead">Marketing, finanzas y gestión de clientes en una sola plataforma. 100% desarrollado, listo para personalizar e instalar en tu hosting o en el nuestro.</p>
    </div>
  </div>

  <!-- Video grande -->
  <div class="theater reveal">
    <div class="wrap-wide">
      <div class="vframe vframe-xl">
        <div class="vbar"><i></i><i></i><i></i><span>~/tu-salon/demo-del-sistema</span><b class="vbar-live"><span class="live-dot"></span>Demo en vivo</b></div>
        <div class="vbox"><?= video_embed(setting('fabrica_video_url'), 'Demo del sistema para peluquerías') ?></div>
        <div class="vfoot">
          <?php foreach (['turnero', 'clientes', 'insumos', 'comisiones', 'finanzas', 'marketing'] as $ch): ?><span class="chip-sm ok">✓ <?= $ch ?></span><?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="wrap">
    <div class="hero-stats center reveal">
      <div class="stat"><b>100%</b><span>desarrollado y listo</span></div>
      <div class="stat"><b>1 : 1</b><span>un sistema por salón</span></div>
      <div class="stat"><b>0</b><span>datos compartidos con otros salones</span></div>
      <div class="stat"><b>4</b><span>pasos para tenerlo funcionando</span></div>
    </div>

    <div class="sec-head center reveal" style="margin-top:72px"><h3 class="h3">Todo tu salón, <em>conectado.</em></h3><p>Lo que pasa en el turnero impacta en insumos, finanzas y comisiones automáticamente.</p></div>
    <div class="mod-grid reveal">
      <?php foreach ($pelu_mods as [$ic, $t, $d, $tags, $star]): ?>
        <div class="mod<?= $star ? ' star' : '' ?>"><div class="ct"><span class="mod-ic"><?= icon($ic) ?></span><?= e($t) ?></div><p><?= e($d) ?></p><div class="tagrow"><?php foreach ($tags as $tg): ?><span class="tag"><?= e($tg) ?></span><?php endforeach; ?></div></div>
      <?php endforeach; ?>
    </div>

    <div class="hybrid-grid reveal">
      <div class="hybrid-card bad"><h3>Sistema de gestión genérico</h3><p>Pensado para todos, igual para todos.</p>
        <ul><li>Tus datos en una plataforma compartida</li><li>Funciones fijas, iguales para cualquier salón</li><li>Te adaptás vos a cómo funciona el sistema</li><li>Pagás licencia mes a mes, para siempre</li></ul></div>
      <div class="hybrid-card good"><h3>Tu sistema Nifty</h3><p>Un desarrollo propio para tu peluquería.</p>
        <ul><li>Instalado en tu hosting, con tus datos</li><li>Funciones especiales según lo que necesites</li><li>Se adapta a tus servicios y reglas de trabajo</li><li>Turnero, insumos, finanzas y comisiones conectados</li></ul></div>
    </div>

    <div class="steps reveal">
      <div class="step"><div class="n">01</div><h4>Relevamiento</h4><p>Conocemos tu salón: servicios, equipo, forma de cobrar y comisiones.</p></div>
      <div class="step"><div class="n">02</div><h4>Personalización</h4><p>Adaptamos el sistema y sumamos las funciones especiales que necesites.</p></div>
      <div class="step"><div class="n">03</div><h4>Instalación</h4><p>Lo instalamos en tu hosting actual o en el que te sugerimos.</p></div>
      <div class="step"><div class="n">04</div><h4>Capacitación</h4><p>Acompañamos a tu equipo para que lo use desde el primer día.</p></div>
    </div>
    <p class="center reveal" style="margin-top:28px"><a class="btn btn-primary" href="#consulta" data-producto="peluquerias">Quiero ver el sistema funcionando</a> &nbsp; <a class="btn btn-ghost" href="/casos-de-exito/cmg-hair-studio">Ver caso CMG Hair Studio</a></p>
  </div>
</section>

<!-- ======================= LMS ======================= -->
<section id="lms" class="fab-product fab-product-alt">
  <div class="wrap">
    <div class="fab-split reveal">
      <div>
        <span class="eyebrow">LMS · Plataforma para escuelas</span>
        <h2>El sistema completo de <em>Eunoia Campus</em>, para tu escuela.</h2>
        <p class="lead">Es la plataforma con la que opera Eunoia Campus todos los días: desde que alguien se inscribe hasta que recibe su certificado. La desarrollamos nosotros y podemos implementarla para tu institución, academia o programa de capacitación in-company.</p>
        <div class="flow">
          <?php foreach (['Inscripción', 'Sistema administrativo', 'Campus y cohorte', 'Aula y evaluaciones', 'Certificado'] as $i => $s): ?><span><?= $i + 1 ?>. <?= e($s) ?></span><?php endforeach; ?>
        </div>
        <div class="hero-ctas"><a class="btn btn-primary" href="#consulta" data-producto="lms">Quiero una demo del LMS</a><a class="btn btn-ghost" href="/casos-de-exito/eunoia-campus">Ver caso Eunoia Campus</a></div>
      </div>
      <div class="vframe">
        <div class="vbar"><i></i><i></i><i></i><span>eunoiacampus.com/campus</span></div>
        <div class="vbox">
          <?php if (setting('lms_video_url')): ?><?= video_embed(setting('lms_video_url'), 'Demo del LMS') ?>
          <?php else: ?><img src="https://nifty-agency.com/wp-content/uploads/2026/10/Screenshot-2026-10-07-at-8.51.45-PM-scaled.png" alt="Sitio y campus de Eunoia Campus desarrollado por Nifty" loading="lazy" class="vbox-img"><?php endif; ?>
        </div>
      </div>
    </div>

    <div class="lms-grid reveal">
      <?php foreach ($lms_mods as [$ic, $t, $d]): ?><div class="lms-mod"><span class="mod-ic"><?= icon($ic) ?></span><b><?= e($t) ?></b><p><?= e($d) ?></p></div><?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ======================= A MEDIDA ======================= -->
<section id="a-medida" class="wrap section">
  <div class="sec-head reveal">
    <span class="eyebrow">Desarrollos a medida</span>
    <h2>Si tu operación lo necesita, <em>lo construimos.</em></h2>
    <p>Sistemas de ventas, portales de clientes, integraciones y automatizaciones. Algunos ejemplos de lo que ya está funcionando:</p>
  </div>
  <div class="services reveal">
    <a class="svc" href="/casos-de-exito/dar-grieco"><div class="svc-top"><div class="svc-icon"><?= icon('cart') ?></div><span class="svc-tag">Ventas B2B</span></div><h3>Sistema de ventas integrado con Netegia</h3><p>Más de 20.000 productos, precios por cliente, cuenta corriente y app móvil.</p><span class="svc-link">Ver caso <?= icon('arrow', 15) ?></span></a>
    <a class="svc" href="/casos-de-exito/btm"><div class="svc-top"><div class="svc-icon"><?= icon('chat') ?></div><span class="svc-tag">WhatsApp + CRM</span></div><h3>Integración Odoo + WhatsApp</h3><p>Equipos por sede, pipelines y ruteo automático de leads de pauta.</p><span class="svc-link">Ver caso <?= icon('arrow', 15) ?></span></a>
    <div class="svc"><div class="svc-top"><div class="svc-icon"><?= icon('layers') ?></div><span class="svc-tag">CRM propio</span></div><h3>CRM y portales de clientes</h3><p>Leads, propuestas, clientes y reportes en un mismo lugar, con acceso para cada cliente.</p></div>
  </div>
</section>

<!-- ======================= CONSULTA ======================= -->
<section id="consulta" class="wrap section">
  <div class="contact-card reveal">
    <div class="contact-left">
      <span class="eyebrow">Hablemos de tu sistema</span>
      <h2>Contanos qué querés <em>construir</em></h2>
      <p>Te mostramos el sistema funcionando y te armamos una propuesta de personalización para tu negocio.</p>
      <ul class="feat-list">
        <li><span class="ck">✓</span> Desarrollo propio, no compartido</li>
        <li><span class="ck">✓</span> En tu hosting o en el que te sugerimos</li>
        <li><span class="ck">✓</span> Argentina y toda LATAM</li>
      </ul>
      <?php if ($wa = setting('whatsapp')): ?><a class="btn btn-wa" href="<?= e(wa_link($wa, 'Hola Nifty! Quiero saber más sobre los sistemas de La Fábrica.')) ?>" target="_blank" rel="noopener">Escribinos por WhatsApp</a><?php endif; ?>
    </div>
    <div class="contact-right">
      <?php if ($f): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
      <form method="post" action="/la-fabrica/consulta" class="form">
        <?= csrf_field() ?><?= antispam_fields() ?>
        <div class="field"><label for="fb-prod">¿Qué te interesa? *</label>
          <select id="fb-prod" name="producto" required>
            <option value="peluquerias">Sistema para peluquerías</option>
            <option value="lms">Plataforma LMS / escuela</option>
            <option value="medida">Desarrollo a medida</option>
          </select></div>
        <div class="field-row">
          <div class="field"><label for="fb-name">Nombre y apellido *</label><input id="fb-name" name="nombre" required autocomplete="name"></div>
          <div class="field"><label for="fb-emp">Negocio / institución</label><input id="fb-emp" name="empresa" autocomplete="organization"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="fb-email">Email *</label><input id="fb-email" type="email" name="email" required autocomplete="email"></div>
          <div class="field"><label for="fb-tel">WhatsApp *</label><input id="fb-tel" type="tel" name="telefono" required placeholder="+54 9 11 ..." autocomplete="tel"></div>
        </div>
        <div class="field-row">
          <div class="field"><label for="fb-city">Ciudad / país</label><input id="fb-city" name="ciudad"></div>
          <div class="field"><label for="fb-size">Tamaño</label>
            <select id="fb-size" name="tamano"><option value="">— Elegí —</option><option>1 a 3 personas</option><option>4 a 15 personas</option><option>16 a 50 personas</option><option>Más de 50 / varias sedes</option></select></div>
        </div>
        <div class="field"><label for="fb-host">¿Ya tenés hosting / sitio web?</label>
          <select id="fb-host" name="hosting"><option value="">— Elegí —</option><option>Sí, tengo hosting</option><option>No, necesito que me recomienden</option><option>No estoy seguro</option></select></div>
        <div class="field"><label for="fb-msg">¿Qué te gustaría resolver?</label><textarea id="fb-msg" name="mensaje" rows="3" placeholder="Ej: manejo los turnos por WhatsApp y las comisiones en una planilla…"></textarea></div>
        <button class="btn btn-primary btn-block" type="submit">Quiero ver el sistema →</button>
        <p class="form-note">Te llega una copia por mail. Al enviar aceptás nuestra <a href="/privacidad">política de privacidad</a>.</p>
      </form>
    </div>
  </div>
</section>
