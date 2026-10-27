<?php
$svcs = [
    ['performance', 'target', 'Performance', 'Pauta que se mide en ventas, no en alcance.',
     'Gestionamos Google Ads, Meta Ads, LinkedIn Ads y TikTok Ads con un único objetivo: que cada peso invertido se pueda rastrear hasta una venta o una oportunidad comercial.',
     ['Auditoría de cuentas, medición y destino antes de invertir', 'Estrategia de canales y presupuesto por etapa del embudo', 'Campañas de búsqueda, Performance Max, Advantage+ y Lead Gen', 'Creatividades y pruebas A/B continuas', 'Conversiones offline y CRM conectados a la pauta', 'Reporte semanal y tablero en tiempo real'],
     ['Ecommerce', 'Generación de leads B2B', 'Turnos y reservas', 'Lanzamientos']],
    ['branding-contenido', 'brand', 'Branding y Contenido', 'Marcas que se reconocen y se eligen.',
     'Construimos la identidad, el tono y el sistema de contenido que hace que tu marca sea reconocible — y que tus anuncios funcionen mejor porque hay una marca detrás.',
     ['Identidad visual y manual de marca', 'Tono de comunicación y mensajes clave', 'Estrategia de redes y calendario de contenido', 'Producción foto y video', 'Programas de UGC con creadores', 'Contenido para pauta (hooks, formatos, variantes)'],
     ['Marcas nuevas', 'Rebranding', 'Escuelas y servicios', 'Belleza y retail']],
    ['desarrollo-web', 'browser', 'Desarrollo Web', 'Sitios que convierten visitas en clientes.',
     'Diseñamos y desarrollamos sitios rápidos, medibles y pensados para vender. Con código limpio y contenido en HTML, para que los lean bien Google y los motores de IA.',
     ['Sitios corporativos y landings de conversión', 'E-commerce B2C y B2B (tiendas tipo tabla, precios por cliente)', 'Formularios conectados a CRM y WhatsApp', 'Medición completa: GTM, GA4, píxeles y conversiones', 'Datos estructurados, llms.txt y SEO técnico', 'Hosting de alto rendimiento y mantenimiento'],
     ['Empresas B2B', 'Tiendas online', 'Profesionales', 'Instituciones']],
    ['software-a-medida', 'code', 'Software a medida', 'Software que acelera tu operación.',
     'En La Fábrica construimos sistemas propios con IA: tuyos, instalados donde quieras y adaptados a cómo trabaja tu negocio. Sin pagar licencias por usuario para siempre.',
     ['Sistemas de gestión (turnos, clientes, stock, comisiones, finanzas)', 'Plataformas LMS para escuelas y capacitaciones', 'CRM y portales de clientes', 'Integraciones con ERP (Netegia, Odoo), pagos y facturación', 'WhatsApp API oficial y bots', 'Asistentes de IA conectados a tus datos'],
     ['Peluquerías', 'Escuelas', 'Distribuidores', 'Servicios']],
    ['seo-geo', 'search', 'SEO y GEO', 'Visibilidad donde tus clientes buscan hoy.',
     'El SEO te pone en Google; el GEO (Generative Engine Optimization) hace que ChatGPT, Gemini, Claude, Perplexity y los resúmenes con IA de Google te mencionen y te recomienden.',
     ['Auditoría técnica y de visibilidad en IA', 'Acceso de bots de IA, robots.txt y llms.txt', 'Datos estructurados (Organization, Service, FAQ, Product)', 'Contenido que responde las preguntas reales de tus clientes', 'Perfil de Google Business y reseñas', 'Menciones en medios y directorios del rubro'],
     ['Servicios profesionales', 'Salud', 'Industria', 'Turismo']],
    ['contact-center', 'phone', 'Contact Center', 'Equipos de venta que cierran, no solo atienden.',
     'Un equipo propio alineado a tu estrategia, que atiende y vende por WhatsApp y teléfono con procesos, métricas y tecnología — sin los costos de contratar personal interno.',
     ['Atención y venta por WhatsApp Business / API', 'Bots de primera respuesta y derivación', 'Etiquetas, pipelines y tiempos de respuesta', 'Capacitación de equipos internos', 'Integración con CRM (Odoo, Clientify y propios)', 'Reporte de conversaciones, ventas y calidad'],
     ['Ecommerce', 'Movilidad', 'Electrodomésticos', 'Servicio técnico']],
];
$faqs = [
    ['¿Trabajan con empresas de cualquier tamaño?', 'Trabajamos con pymes, empresas medianas y marcas en expansión, en Argentina y LATAM. Lo importante es que haya un objetivo comercial claro y voluntad de medir.'],
    ['¿Qué es el GEO y en qué se diferencia del SEO?', 'El SEO optimiza tu sitio para los resultados de Google. El GEO lo prepara para que los motores de IA generativa (ChatGPT, Gemini, Perplexity, Claude y los AI Overviews) entiendan tu marca y la mencionen en sus respuestas. Hoy hacen falta los dos.'],
    ['¿Puedo contratar un solo servicio?', 'Sí. Muchos clientes empiezan por pauta o por un desarrollo y suman servicios después. La ventaja de hacerlo con un mismo equipo es que todo queda conectado y medido.'],
    ['¿Cómo veo los resultados?', 'Con reportes periódicos, reuniones de seguimiento y tableros en tiempo real. Cada cliente tiene además su ficha con el estado de su ecosistema digital.'],
];
?>
<script type="application/ld+json"><?= jenc(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)]) ?></script>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><div class="hero-gridlines"></div><div class="orb o1"></div><div class="orb o2"></div></div>
  <div class="wrap">
    <span class="eyebrow">Servicios</span>
    <h1>Seis servicios, <em>un mismo equipo.</em></h1>
    <p class="lead">Marca, demanda, tecnología y ventas conectadas. Elegí por dónde empezar: todo lo que hacemos está pensado para sumarse a lo demás.</p>
    <nav class="pill-nav" aria-label="Servicios">
      <?php foreach ($svcs as $s): ?><a href="#<?= $s[0] ?>"><?= icon($s[1], 16) ?><?= e($s[2]) ?></a><?php endforeach; ?>
    </nav>
  </div>
</section>

<?php foreach ($svcs as $i => [$id, $ic, $tag, $h, $p, $items, $fit]): ?>
<section id="<?= $id ?>" class="wrap section svc-detail reveal<?= $i % 2 ? ' alt' : '' ?>">
  <div class="svc-detail-head">
    <div class="svc-icon lg"><?= icon($ic, 26) ?></div>
    <span class="svc-tag"><?= e($tag) ?></span>
    <h2><?= e($h) ?></h2>
    <p class="lead"><?= e($p) ?></p>
    <div class="fit"><span>Ideal para:</span><?php foreach ($fit as $f): ?><b><?= e($f) ?></b><?php endforeach; ?></div>
    <div class="hero-ctas">
      <a class="btn btn-primary" href="/contacto">Quiero <?= e(mb_strtolower($tag)) ?></a>
      <?php if ($id === 'seo-geo'): ?><a class="btn btn-ghost" href="/diagnostico-ia">Hacer el diagnóstico IA</a><?php endif; ?>
      <?php if ($id === 'performance'): ?><a class="btn btn-ghost" href="/plan-de-pauta">Armar mi plan de pauta</a><?php endif; ?>
      <?php if ($id === 'software-a-medida'): ?><a class="btn btn-ghost" href="/la-fabrica">Ver La Fábrica</a><?php endif; ?>
    </div>
  </div>
  <ul class="check-grid">
    <?php foreach ($items as $it): ?><li><span><?= icon('check', 16) ?></span><?= e($it) ?></li><?php endforeach; ?>
  </ul>
</section>
<?php endforeach; ?>

<section class="wrap section">
  <div class="sec-head reveal"><span class="eyebrow">Preguntas frecuentes</span><h2>Lo que más nos preguntan</h2></div>
  <div class="faq reveal">
    <?php foreach ($faqs as [$q, $a]): ?><details><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details><?php endforeach; ?>
  </div>
</section>
