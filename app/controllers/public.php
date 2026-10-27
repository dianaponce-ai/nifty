<?php
/* =====================================================================
 * Páginas públicas
 * ===================================================================== */

function create_lead(string $type, array $f, array $payload = [], array $result = [], ?int $score = null): array {
    $tok = token(32);
    $id = insert('leads', [
        'token' => $tok, 'type' => $type, 'status' => 'nuevo',
        'name' => mb_substr($f['name'] ?? '', 0, 160), 'company' => mb_substr($f['company'] ?? '', 0, 200),
        'email' => mb_substr($f['email'] ?? '', 0, 190), 'phone' => mb_substr($f['phone'] ?? '', 0, 60),
        'website' => mb_substr($f['website'] ?? '', 0, 255), 'rubro' => mb_substr($f['rubro'] ?? '', 0, 120),
        'score' => $score, 'payload' => jenc($payload), 'result' => $result ? jenc($result) : null,
        'ip' => client_ip(), 'source' => mb_substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    lead_event($id, 'creado', 'Ingresó desde la web: ' . (lead_types()[$type] ?? $type));
    return [$id, $tok];
}

function video_embed(string $url, string $title = 'Video'): string {
    $url = trim($url);
    if ($url === '') {
        return '<div class="video-ph"><div class="play">▶</div><b>' . e($title) . '</b><small>Cargá la URL del video en Admin → Ajustes</small></div>';
    }
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([\w-]{11})~', $url, $m)) {
        return '<iframe src="https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&modestbranding=1" title="' . e($title) . '" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        return '<iframe src="https://player.vimeo.com/video/' . $m[1] . '?title=0&byline=0&portrait=0" title="' . e($title) . '" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
    }
    return '<video controls playsinline preload="metadata" src="' . e($url) . '"></video>';
}

function published_cases(int $limit = 0): array {
    return rows('SELECT * FROM cases WHERE published = 1 ORDER BY sort ASC, id ASC' . ($limit ? ' LIMIT ' . (int) $limit : ''));
}

function page_home(): void {
    view('home', [
        'cases' => published_cases(6),
        'reviews' => rows('SELECT * FROM reviews WHERE featured = 1 ORDER BY sort ASC, id DESC LIMIT 12'),
        'meta' => [
            'title' => 'Nifty Agency — Agencia de marketing digital, desarrollo y SEO + GEO en Buenos Aires',
            'description' => 'Performance, branding, desarrollo web, software a medida con IA, SEO + GEO y contact center bajo un mismo equipo. Convertimos clics en clientes. Pedí tu diagnóstico gratuito.',
        ],
    ]);
}

function page_services(): void {
    view('services', ['meta' => [
        'title' => 'Servicios — Performance, Branding, Web, Software, SEO + GEO y Contact Center | Nifty Agency',
        'description' => 'Seis servicios integrados para que tu marca se vea, se encuentre y venda: pauta en Google, Meta, LinkedIn y TikTok, contenido, desarrollo web, software a medida, SEO + GEO y contact center.',
    ]]);
}

function page_fabrica(): void {
    view('fabrica', ['meta' => [
        'title' => 'La Fábrica — Software a medida con IA | Nifty Agency',
        'description' => 'Sistemas propios para tu negocio: gestión para peluquerías, plataformas LMS como Eunoia Campus, e-commerce B2B e integraciones con ERP, CRM y WhatsApp.',
    ]]);
}

function fabrica_submit(): void {
    csrf_check();
    $back = '/la-fabrica#consulta';
    if (spam_guard('fabrica')) redirect('/gracias');
    $f = ['name' => post('nombre'), 'company' => post('empresa'), 'email' => post('email'), 'phone' => post('telefono'), 'rubro' => post('producto')];
    if ($f['name'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen(digits($f['phone'])) < 8) { flash('Completá nombre, email y WhatsApp válidos.', 'err'); redirect($back); }
    $productos = ['peluquerias' => 'Sistema para peluquerías', 'lms' => 'Plataforma LMS / escuela', 'medida' => 'Desarrollo a medida'];
    $payload = ['producto' => $productos[post('producto')] ?? post('producto'), 'tamano' => post('tamano'), 'hosting' => post('hosting'), 'mensaje' => post('mensaje'), 'ciudad' => post('ciudad')];
    [$id] = create_lead('fabrica', $f, $payload);

    $rows = ['Nombre' => $f['name'], 'Empresa' => $f['company'], 'Email' => $f['email'], 'WhatsApp' => $f['phone'], 'Interés' => $payload['producto'],
        'Tamaño' => $payload['tamano'], 'Ciudad' => $payload['ciudad'], 'Hosting' => $payload['hosting'], 'Mensaje' => $payload['mensaje']];
    $inner = '<p>¡Hola ' . e($f['name']) . '!</p><p>Recibimos tu consulta por <b>' . e($payload['producto']) . '</b>. En breve te contactamos para mostrarte el sistema funcionando y armar una propuesta a tu medida.</p>'
        . email_table($rows) . email_button(setting('agenda_url'), 'Agendá una demo') ;
    send_mail($f['email'], 'Recibimos tu consulta — La Fábrica de Nifty', email_layout('¡Gracias por tu interés!', $inner, 'La Fábrica'), ['lead_id' => $id, 'reply_to' => [setting('contact_email'), 'Nifty Agency']]);
    notify_team_lead($id, '🏭 Nuevo lead La Fábrica — ' . $payload['producto'] . ' · ' . ($f['company'] ?: $f['name']), 'Entró una consulta desde La Fábrica.', $rows);
    redirect('/gracias?t=fabrica');
}

function page_cases(): void {
    view('cases', ['cases' => published_cases(), 'meta' => [
        'title' => 'Casos de éxito — Nifty Agency',
        'description' => 'Marcas que crecieron con Nifty: Eunoia Campus, Dar Grieco, Thermomix, ACO Workspace, BTM, CMG Hair Studio y más. Qué hicimos y qué resultados logramos.',
    ]]);
}

function page_case(string $slug): void {
    $c = row('SELECT * FROM cases WHERE slug = ?', [$slug]);
    if (!$c || (!$c['published'] && !current_user())) not_found();
    $reviews = rows('SELECT * FROM reviews WHERE case_id = ? ORDER BY sort, id', [$c['id']]);
    $others = rows('SELECT * FROM cases WHERE published = 1 AND id <> ? ORDER BY sort, id LIMIT 3', [$c['id']]);
    view('case', ['c' => $c, 'reviews' => $reviews, 'others' => $others, 'meta' => [
        'title' => $c['client'] . ': ' . $c['title'] . ' — Caso de éxito Nifty Agency',
        'description' => mb_substr(strip_tags((string) ($c['card_summary'] ?: $c['subtitle'])), 0, 160),
        'image' => $c['hero_image'] ? asset_url($c['hero_image']) : null,
        'css' => ['case'],
    ]]);
}

function page_contact(): void {
    view('contact', ['meta' => ['title' => 'Contacto — Nifty Agency', 'description' => 'Contanos sobre tu negocio y te devolvemos un diagnóstico antes de la primera reunión.']]);
}

function contact_submit(): void {
    csrf_check();
    $back = (post('_from') === 'home' ? '/' : '/contacto') . '#contacto';
    if (spam_guard('contacto')) redirect('/gracias');
    $f = ['name' => post('nombre'), 'company' => post('empresa'), 'email' => post('email'), 'phone' => post('whatsapp'), 'website' => post('sitio_web'), 'rubro' => post('rubro')];
    if ($f['name'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen(digits($f['phone'])) < 8) { flash('Completá nombre, email y WhatsApp válidos.', 'err'); redirect($back); }
    $payload = ['presupuesto' => post('presupuesto'), 'objetivo' => post('objetivo'), 'situacion' => post('situacion'), 'mensaje' => post('mensaje')];
    [$id] = create_lead('contacto', $f, $payload);

    $rows = ['Nombre' => $f['name'], 'Empresa' => $f['company'], 'Email' => $f['email'], 'WhatsApp' => $f['phone'], 'Sitio web' => $f['website'], 'Rubro' => $f['rubro'],
        'Presupuesto' => $payload['presupuesto'], 'Objetivo' => $payload['objetivo'], 'Situación' => $payload['situacion'], 'Mensaje' => $payload['mensaje']];
    $inner = '<p>¡Hola ' . e($f['name']) . '!</p><p>Recibimos tus datos. Vamos a revisar tu caso y te contactamos con un primer diagnóstico antes de la reunión.</p>'
        . '<p>Mientras tanto, podés adelantar dos diagnósticos gratuitos:</p>'
        . email_button(url('diagnostico-ia'), '¿Qué tan IA-friendly es tu marca?') . email_button(url('plan-de-pauta'), 'Tu plan de pauta de prueba', '#ede9fe', '#4c1d95')
        . '<p style="margin-top:18px">O reservá directamente un espacio en la agenda:</p>' . email_button(setting('agenda_url'), 'Agendar reunión');
    send_mail($f['email'], 'Recibimos tu consulta — Nifty Agency', email_layout('¡Gracias por escribirnos!', $inner, 'Contacto'), ['lead_id' => $id, 'reply_to' => [setting('contact_email'), 'Nifty Agency']]);
    notify_team_lead($id, '🟠 Nuevo contacto web — ' . ($f['company'] ?: $f['name']), 'Entró una consulta desde el formulario de contacto.', $rows);
    redirect('/gracias?t=contacto');
}

function page_thanks(): void {
    view('thanks', ['t' => $_GET['t'] ?? '', 'meta' => ['title' => '¡Gracias! — Nifty Agency', 'robots' => 'noindex']]);
}
function page_support(): void { view('support', ['meta' => ['title' => 'Soporte técnico — Nifty Agency', 'description' => 'Centro de soporte técnico de Nifty Agency para clientes.']]); }
function page_privacy(): void { view('privacy', ['meta' => ['title' => 'Política de privacidad — Nifty Agency']]); }

function asset_url(string $p): string { return preg_match('~^https?://~', $p) ? $p : url($p); }

/* ---------------------------------------------------------------------
 * Archivos para buscadores y motores de IA
 * --------------------------------------------------------------------- */
function robots_txt(): void {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin\nDisallow: /diagnostico-ia/resultado/\nDisallow: /plan-de-pauta/resultado/\n\n";
    foreach (['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended'] as $b) {
        echo "User-agent: $b\nAllow: /\nDisallow: /admin\n\n";
    }
    echo 'Sitemap: ' . url('sitemap.xml') . "\n";
}

function llms_txt(): void {
    header('Content-Type: text/plain; charset=utf-8');
    $cases = published_cases();
    echo "# Nifty Agency\n\n> Agencia de marketing digital y tecnología de Buenos Aires, Argentina (una empresa de Andina Sur SAS). Más de 10 años integrando performance, branding, desarrollo web, software a medida con IA, SEO + GEO y contact center para empresas B2B y de comercio en Argentina y LATAM.\n\n";
    echo "## Servicios\n- [Performance](" . url('servicios#performance') . "): Google Ads, Meta Ads, LinkedIn Ads y TikTok Ads orientados a ventas y leads.\n- [Branding y contenido](" . url('servicios#branding-contenido') . "): identidad, redes, contenido estratégico y UGC.\n- [Desarrollo web](" . url('servicios#desarrollo-web') . "): sitios corporativos y e-commerce.\n- [Software a medida](" . url('la-fabrica') . "): sistemas propios, CRM, LMS, integraciones con ERP y WhatsApp.\n- [SEO y GEO](" . url('servicios#seo-geo') . "): posicionamiento en Google y en motores de IA generativa.\n- [Contact center](" . url('servicios#contact-center') . "): equipos de venta y atención por WhatsApp.\n\n";
    echo "## Herramientas gratuitas\n- [Diagnóstico IA-friendly](" . url('diagnostico-ia') . "): mide qué tan preparada está una marca para ser recomendada por ChatGPT, Gemini o Perplexity.\n- [Plan de pauta de prueba](" . url('plan-de-pauta') . "): plan sugerido de inversión publicitaria a partir de un brief.\n\n";
    echo "## Casos de éxito\n";
    foreach ($cases as $c) echo '- [' . $c['client'] . '](' . url('casos-de-exito/' . $c['slug']) . '): ' . trim(strip_tags((string) $c['card_summary'])) . "\n";
    echo "\n## Contacto\n- Web: " . url('contacto') . "\n- Email: " . setting('contact_email') . "\n- Ubicación: Buenos Aires, Argentina\n";
}

function sitemap_xml(): void {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = ['', 'servicios', 'la-fabrica', 'casos-de-exito', 'diagnostico-ia', 'plan-de-pauta', 'contacto', 'privacidad'];
    foreach (published_cases() as $c) $urls[] = 'casos-de-exito/' . $c['slug'];
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) echo '<url><loc>' . e(url($u)) . '</loc></url>';
    echo '</urlset>';
}
