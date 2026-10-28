<?php
$meta = ($meta ?? []) + [
    'title' => 'Nifty Agency — Agencia de marketing digital y tecnología',
    'description' => 'Performance, branding, desarrollo web, software a medida, SEO + GEO y contact center bajo un mismo equipo.',
    'robots' => 'index, follow, max-image-preview:large',
    'image' => null,
    'css' => [],
];
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$canonical = url(ltrim($path, '/'));
$ogImage = $meta['image'] ?: 'https://nifty-agency.com/wp-content/uploads/2026/09/Equipo-partner-Nifty-Agency-scaled.jpg';
$active = fn(string $p) => ($p === '/' ? $path === '/' : str_starts_with($path, $p)) ? ' class="is-active"' : '';
$gtm = setting('gtm_id');
?><!DOCTYPE html>
<html lang="es-AR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($meta['title']) ?></title>
<meta name="description" content="<?= e($meta['description']) ?>">
<meta name="robots" content="<?= e($meta['robots']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="es_AR">
<meta property="og:site_name" content="Nifty Agency">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<meta property="og:description" content="<?= e($meta['description']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0A0118">
<link rel="icon" href="https://nifty-agency.com/wp-content/uploads/2025/10/cropped-Favicon-Nifty-32x32.png" sizes="32x32">
<link rel="apple-touch-icon" href="https://nifty-agency.com/wp-content/uploads/2025/10/cropped-Favicon-Nifty-180x180.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
<?php foreach ((array) $meta['css'] as $css): ?><link rel="stylesheet" href="<?= asset('assets/css/' . $css . '.css') ?>"><?php endforeach; ?>
<script type="application/ld+json"><?= jenc([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => ['Organization', 'ProfessionalService'], '@id' => url('#organization'), 'name' => 'Nifty Agency', 'url' => url(), 'logo' => logo_url(),
         'description' => 'Agencia de marketing digital y tecnología: performance, branding, desarrollo web, software a medida con IA, SEO + GEO y contact center.',
         'parentOrganization' => ['@type' => 'Organization', 'name' => 'Andina Sur SAS', 'url' => 'https://www.andinasur.space/'],
         'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Buenos Aires', 'addressCountry' => 'AR'],
         'areaServed' => ['Argentina', 'Latinoamérica'],
         'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => setting('google_rating'), 'reviewCount' => setting('google_reviews')],
         'sameAs' => ['https://www.instagram.com/___nifty____/', 'https://www.linkedin.com/company/niftyagency/', 'https://www.tiktok.com/@___nifty____', 'https://www.facebook.com/niftyagencyhello/']],
        ['@type' => 'WebSite', '@id' => url('#website'), 'url' => url(), 'name' => 'Nifty Agency', 'inLanguage' => 'es-AR', 'publisher' => ['@id' => url('#organization')]],
    ],
]) ?></script>
<?php if ($gtm): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($gtm) ?>');</script>
<?php endif; ?>
</head>
<body>
<?php if ($gtm): ?><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><?php endif; ?>
<a class="skip" href="#main">Saltar al contenido</a>

<header class="site-header" id="top">
  <div class="wrap header-inner">
    <a class="brand" href="/" aria-label="Nifty Agency — inicio"><img src="<?= e(logo_url()) ?>" alt="Nifty Agency" width="132" height="40"></a>
    <nav class="main-nav" id="mainNav" aria-label="Principal">
      <ul>
        <li><a href="/servicios"<?= $active('/servicios') ?>>Servicios</a></li>
        <li><a href="/la-fabrica"<?= $active('/la-fabrica') ?>>La Fábrica</a></li>
        <li><a href="/casos-de-exito"<?= $active('/casos-de-exito') ?>>Casos de éxito</a></li>
        <li class="has-sub">
          <button type="button" class="sub-toggle" aria-expanded="false">Herramientas gratis <span aria-hidden="true">▾</span></button>
          <ul class="sub">
            <li><a href="/diagnostico-ia"><b>Diagnóstico IA-friendly</b><span>¿Te recomienda ChatGPT?</span></a></li>
            <li><a href="/plan-de-pauta"><b>Plan de pauta de prueba</b><span>Tu inversión publicitaria, ordenada</span></a></li>
          </ul>
        </li>
        <li><a href="/#equipo">Nosotros</a></li>
        <li><a href="/contacto"<?= $active('/contacto') ?>>Contacto</a></li>
        <li class="nav-mobile-only"><a href="/soporte">Soporte técnico</a></li>
        <li class="nav-mobile-only"><a href="/admin">Ingresar</a></li>
      </ul>
    </nav>
    <div class="header-cta">
      <a class="link-quiet" href="/admin">Ingresar</a>
      <a class="btn btn-primary btn-sm" href="/diagnostico-ia">Diagnóstico gratis</a>
      <button class="burger" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="mainNav"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <?php if (!in_array($path, ['/', '/contacto'], true)): ?>
    <div class="footer-cta">
      <div>
        <p class="eyebrow-mono">// ¿planeando tu próximo proyecto?</p>
        <h2>Contactanos y pensemos juntos.</h2>
      </div>
      <div class="footer-cta-btns">
        <a class="btn btn-primary" href="/contacto">Hablemos</a>
        <a class="btn btn-ghost" href="<?= e(setting('agenda_url')) ?>" target="_blank" rel="noopener">Agendar reunión</a>
      </div>
    </div>
    <?php endif; ?>
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="<?= e(logo_url()) ?>" alt="Nifty Agency" width="150" height="45" loading="lazy">
        <p>Impulsamos marcas con estrategia, creatividad, tecnología y resultados medibles.</p>
        <p class="muted">Una empresa de <a href="https://www.andinasur.space/" target="_blank" rel="noopener">Andina Sur SAS</a>.</p>
      </div>
      <div>
        <h3>Servicios</h3>
        <ul>
          <li><a href="/servicios#performance">Performance</a></li>
          <li><a href="/servicios#branding-contenido">Branding y contenido</a></li>
          <li><a href="/servicios#desarrollo-web">Desarrollo web</a></li>
          <li><a href="/la-fabrica">Software a medida</a></li>
          <li><a href="/servicios#seo-geo">SEO y GEO</a></li>
          <li><a href="/servicios#contact-center">Contact center</a></li>
        </ul>
      </div>
      <div>
        <h3>Nifty</h3>
        <ul>
          <li><a href="/casos-de-exito">Casos de éxito</a></li>
          <li><a href="/la-fabrica">La Fábrica</a></li>
          <li><a href="/diagnostico-ia">Diagnóstico IA-friendly</a></li>
          <li><a href="/plan-de-pauta">Plan de pauta de prueba</a></li>
          <li><a href="/soporte">Soporte técnico</a></li>
        </ul>
      </div>
      <div>
        <h3>Seguinos</h3>
        <ul class="socials">
          <li><a href="https://www.linkedin.com/company/niftyagency/" target="_blank" rel="noopener">LinkedIn</a></li>
          <li><a href="https://www.instagram.com/___nifty____/" target="_blank" rel="noopener">Instagram</a></li>
          <li><a href="https://www.tiktok.com/@___nifty____" target="_blank" rel="noopener">TikTok</a></li>
          <li><a href="https://www.facebook.com/niftyagencyhello/" target="_blank" rel="noopener">Facebook</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Nifty Agency · Buenos Aires, Argentina</span>
      <span><a href="/privacidad">Política de privacidad</a></span>
    </div>
  </div>
</footer>

<?php if ($wa = setting('whatsapp')): ?>
<a class="wa-float" href="<?= e(wa_link($wa, 'Hola Nifty! Quiero hacerles una consulta.')) ?>" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp">
  <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3C8.8 3 3 8.8 3 16c0 2.3.6 4.5 1.7 6.4L3 29l6.8-1.8c1.9 1 4 1.6 6.2 1.6 7.2 0 13-5.8 13-13S23.2 3 16 3zm0 23.6c-2 0-3.9-.5-5.6-1.5l-.4-.2-4 1.1 1.1-3.9-.3-.4C5.7 20 5.2 18 5.2 16 5.2 10 10 5.2 16 5.2S26.8 10 26.8 16 22 26.6 16 26.6zm5.9-8c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-.9-1.6-1.9-1.8-2.2-.2-.3 0-.5.1-.7l.5-.6c.2-.2.2-.3.3-.6.1-.2 0-.4 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.4 3.7 5.9 5.2 2.9 1.1 3.5.9 4.1.8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.1-.3-.2-.6-.3z"/></svg>
</a>
<?php endif; ?>

<script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
