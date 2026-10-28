<?php
/**
 * Nifty Agency — front controller.
 * En Hostinger lo invoca .htaccess; en local: php -S localhost:8000 index.php
 */
declare(strict_types=1);

// Servidor embebido de PHP: servir archivos estáticos tal cual
if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($f) && !str_ends_with($f, '.php') && !preg_match('~/(app|views|storage|database)/~', $f)) return false;
}

require __DIR__ . '/app/bootstrap.php';
require APP . '/controllers/public.php';
require APP . '/controllers/tools.php';
require APP . '/controllers/admin.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = '/' . trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'), '/');

/* Redirecciones 301 desde las URLs del WordPress anterior */
$legacy = [
    '/performance' => '/servicios#performance',
    '/contenido' => '/servicios#branding-contenido',
    '/seo-y-geo' => '/servicios#seo-geo',
    '/contact-center' => '/servicios#contact-center',
    '/sistema-de-marketing-finanzas-y-administracion-de-clientes-para-peluquerias' => '/la-fabrica#peluquerias',
    '/nosotros' => '/#equipo',
    '/equipo' => '/#equipo',
    '/politica-de-privacidad' => '/privacidad',
    '/legales' => '/privacidad',
    '/ticket-de-servicio-tecnico' => '/soporte',
    '/soporte-nifty' => '/soporte',
];
if (isset($legacy[$path])) redirect($legacy[$path], 301);

// Vista previa sin base de datos: el sitio se ve, pero no se guardan formularios ni se entra al admin
if (PREVIEW_MODE && ($method === 'POST' || str_starts_with($path, '/admin'))) {
    http_response_code(503);
    view('preview_notice', ['isAdmin' => str_starts_with($path, '/admin'), 'meta' => ['title' => 'Sitio en configuración — Nifty Agency', 'robots' => 'noindex']]);
    exit;
}

$routes = [
    ['GET',  '#^/$#', 'page_home'],
    ['GET',  '#^/servicios$#', 'page_services'],
    ['GET',  '#^/la-fabrica$#', 'page_fabrica'],
    ['POST', '#^/la-fabrica/consulta$#', 'fabrica_submit'],
    ['GET',  '#^/casos-de-exito$#', 'page_cases'],
    ['GET',  '#^/casos-de-exito/([a-z0-9-]+)$#', 'page_case'],
    ['GET',  '#^/contacto$#', 'page_contact'],
    ['POST', '#^/contacto$#', 'contact_submit'],
    ['GET',  '#^/soporte$#', 'page_support'],
    ['GET',  '#^/privacidad$#', 'page_privacy'],
    ['GET',  '#^/gracias$#', 'page_thanks'],

    ['GET',  '#^/diagnostico-ia$#', 'page_ai_diag'],
    ['POST', '#^/diagnostico-ia$#', 'ai_diag_submit'],
    ['GET',  '#^/diagnostico-ia/resultado/([a-f0-9]{20,40})$#', 'page_ai_diag_result'],
    ['GET',  '#^/plan-de-pauta$#', 'page_ad_plan'],
    ['POST', '#^/plan-de-pauta$#', 'ad_plan_submit'],
    ['GET',  '#^/plan-de-pauta/resultado/([a-f0-9]{20,40})$#', 'page_ad_plan_result'],

    ['GET',  '#^/media/(\d+)-[a-z0-9.-]+$#', 'serve_media'],
    ['GET',  '#^/robots\.txt$#', 'robots_txt'],
    ['GET',  '#^/llms\.txt$#', 'llms_txt'],
    ['GET',  '#^/sitemap\.xml$#', 'sitemap_xml'],

    ['GET|POST', '#^/admin/setup$#', 'admin_setup'],
    ['GET|POST', '#^/admin/login$#', 'admin_login'],
    ['GET',  '#^/admin/logout$#', 'admin_logout'],
    ['GET',  '#^/admin$#', 'admin_dashboard'],
    ['GET',  '#^/admin/leads$#', 'admin_leads'],
    ['GET|POST', '#^/admin/leads/(\d+)$#', 'admin_lead'],
    ['GET',  '#^/admin/leads/exportar$#', 'admin_leads_export'],
    ['GET',  '#^/admin/casos$#', 'admin_cases'],
    ['GET|POST', '#^/admin/casos/nuevo$#', 'admin_case_edit'],
    ['GET|POST', '#^/admin/casos/(\d+)$#', 'admin_case_edit'],
    ['POST', '#^/admin/casos/(\d+)/eliminar$#', 'admin_case_delete'],
    ['POST', '#^/admin/casos/generar$#', 'admin_case_generate'],
    ['GET|POST', '#^/admin/resenas$#', 'admin_reviews'],
    ['GET|POST', '#^/admin/ajustes$#', 'admin_settings'],
    ['GET|POST', '#^/admin/usuarios$#', 'admin_users'],
];

foreach ($routes as [$m, $re, $fn]) {
    if (!in_array($method, explode('|', $m), true)) continue;
    if (preg_match($re, $path, $mm)) {
        array_shift($mm);
        $fn(...$mm);
        exit;
    }
}
not_found();
