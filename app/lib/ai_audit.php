<?php
/**
 * Diagnóstico "IA-friendly": qué tan preparado está un sitio para ser leído,
 * entendido y recomendado por motores de IA (ChatGPT, Gemini, Claude, Perplexity, AI Overviews).
 *
 * Score final = 70% análisis técnico del sitio + 30% señales externas (cuestionario).
 */

const AI_BOTS = [
    'GPTBot'          => 'OpenAI (entrenamiento)',
    'OAI-SearchBot'   => 'ChatGPT Search',
    'ChatGPT-User'    => 'ChatGPT navegando',
    'ClaudeBot'       => 'Anthropic Claude',
    'Claude-SearchBot'=> 'Claude búsqueda',
    'PerplexityBot'   => 'Perplexity',
    'Google-Extended' => 'Gemini / Google IA',
    'Applebot-Extended'=> 'Apple Intelligence',
];

/* ------------------------------------------------------------------ */
/* Descarga segura (anti-SSRF)                                         */
/* ------------------------------------------------------------------ */
function audit_normalize_url(string $u): ?string {
    $u = trim($u);
    if ($u === '') return null;
    if (!preg_match('~^https?://~i', $u)) $u = 'https://' . $u;
    $p = parse_url($u);
    if (!$p || empty($p['host'])) return null;
    $host = strtolower($p['host']);
    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host)) return null;
    return strtolower($p['scheme']) . '://' . $host . ($p['path'] ?? '/');
}

function audit_host_is_public(string $host): bool {
    $ips = @gethostbynamel($host) ?: [];
    if (!$ips) return false;
    foreach ($ips as $ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    }
    return true;
}

/** @return array{ok:bool,code:int,body:string,url:string,time:float,headers:string,error:string} */
function audit_fetch(string $url, int $maxBytes = 2500000, int $redirects = 4): array {
    $out = ['ok' => false, 'code' => 0, 'body' => '', 'url' => $url, 'time' => 0.0, 'headers' => '', 'error' => ''];
    if (isset($GLOBALS['AUDIT_FETCH_MOCK'])) return ($GLOBALS['AUDIT_FETCH_MOCK'])($url) + $out; // solo para pruebas
    for ($i = 0; $i <= $redirects; $i++) {
        $p = parse_url($url);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true) || empty($p['host'])) { $out['error'] = 'URL inválida'; return $out; }
        if (isset($p['port']) && !in_array((int) $p['port'], [80, 443], true)) { $out['error'] = 'Puerto no permitido'; return $out; }
        if (!audit_host_is_public($p['host'])) { $out['error'] = 'No se pudo resolver el dominio'; return $out; }

        $ch = curl_init($url);
        $buf = '';
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 14,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; NiftyAIReadiness/1.0; +https://nifty-agency.com/diagnostico-ia)',
            CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml,text/plain;q=0.9,*/*;q=0.8', 'Accept-Language: es-AR,es;q=0.9'],
            CURLOPT_HEADERFUNCTION => function ($ch, $h) use (&$out) { $out['headers'] .= $h; return strlen($h); },
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buf, $maxBytes) { $buf .= $chunk; return strlen($buf) > $maxBytes ? 0 : strlen($chunk); },
        ]);
        $t0 = microtime(true);
        curl_exec($ch);
        $out['time'] += microtime(true) - $t0;
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loc  = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code >= 300 && $code < 400 && $loc) { $url = $loc; $out['headers'] = ''; continue; }
        $out['code'] = $code; $out['url'] = $url; $out['body'] = $buf;
        $out['ok'] = $code >= 200 && $code < 300 && $buf !== '';
        if (!$out['ok'] && !$out['error']) $out['error'] = $err ?: ('HTTP ' . $code);
        return $out;
    }
    $out['error'] = 'Demasiadas redirecciones';
    return $out;
}

/* ------------------------------------------------------------------ */
/* robots.txt                                                          */
/* ------------------------------------------------------------------ */
/** Devuelve [bot => 'permitido'|'bloqueado'|'parcial'] según robots.txt */
function audit_robots_bots(string $robots): array {
    $groups = []; $cur = null; $lastWasUA = false;
    foreach (preg_split('/\r?\n/', $robots) as $line) {
        $line = trim(preg_replace('/#.*/', '', $line));
        if ($line === '' || !str_contains($line, ':')) continue;
        [$k, $v] = array_map('trim', explode(':', $line, 2));
        $k = strtolower($k);
        if ($k === 'user-agent') {
            if (!$lastWasUA) { $groups[] = ['ua' => [], 'dis' => [], 'allow' => []]; $cur = count($groups) - 1; }
            $groups[$cur]['ua'][] = strtolower($v);
            $lastWasUA = true;
        } else {
            $lastWasUA = false;
            if ($cur === null) continue;
            if ($k === 'disallow') $groups[$cur]['dis'][] = $v;
            if ($k === 'allow') $groups[$cur]['allow'][] = $v;
        }
    }
    $res = [];
    foreach (array_keys(AI_BOTS) as $bot) {
        $g = null;
        foreach ($groups as $gr) if (in_array(strtolower($bot), $gr['ua'], true)) { $g = $gr; break; }
        if (!$g) foreach ($groups as $gr) if (in_array('*', $gr['ua'], true)) { $g = $gr; break; }
        if (!$g) { $res[$bot] = 'permitido'; continue; }
        $dis = array_filter($g['dis'], fn($d) => $d !== '');
        if (in_array('/', $dis, true) && !in_array('/', $g['allow'], true)) $res[$bot] = 'bloqueado';
        elseif (count($dis) > 6) $res[$bot] = 'parcial';
        else $res[$bot] = 'permitido';
    }
    return $res;
}

/* ------------------------------------------------------------------ */
/* Análisis técnico                                                    */
/* ------------------------------------------------------------------ */
function audit_site(string $url): array {
    $home = audit_fetch($url);
    $checks = [];
    $add = function (string $id, string $label, int $weight, float $ratio, string $detail, string $reco, string $prio = 'media') use (&$checks) {
        $checks[] = ['id' => $id, 'label' => $label, 'weight' => $weight, 'points' => round($weight * max(0, min(1, $ratio)), 1),
            'status' => $ratio >= 0.99 ? 'ok' : ($ratio > 0 ? 'parcial' : 'falta'), 'detail' => $detail, 'reco' => $reco, 'prio' => $prio];
    };

    if (!$home['ok']) {
        return ['reachable' => false, 'error' => $home['error'], 'final_url' => $home['url'], 'checks' => [], 'score' => 0, 'bots' => []];
    }

    $finalUrl = $home['url'];
    $p = parse_url($finalUrl);
    $origin = $p['scheme'] . '://' . $p['host'];
    $html = $home['body'];

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
    $xp = new DOMXPath($doc);
    $first = fn(string $q) => ($n = $xp->query($q)) && $n->length ? trim($n->item(0)->nodeValue) : '';

    // 1. HTTPS
    $https = $p['scheme'] === 'https';
    $add('https', 'Sitio seguro (HTTPS)', 5, $https ? 1 : 0, $https ? 'El sitio carga con HTTPS.' : 'El sitio no fuerza HTTPS.',
        'Activar certificado SSL y redirigir todo a HTTPS: los motores de IA y Google priorizan fuentes seguras.', 'alta');

    // 2. Velocidad de respuesta
    $t = $home['time'];
    $add('speed', 'Respuesta del servidor', 5, $t < 1.2 ? 1 : ($t < 3 ? 0.5 : 0), 'El HTML tardó ' . number_format($t, 1, ',', '.') . ' s en llegar.',
        'Optimizar servidor y caché: los rastreadores de IA abandonan páginas lentas y priorizan las que responden rápido.');

    // Texto visible sin JS
    foreach (['//script', '//style', '//noscript', '//svg', '//template'] as $q) foreach (iterator_to_array($xp->query($q)) as $n) $n->parentNode?->removeChild($n);
    $bodyText = $xp->query('//body')->length ? $xp->query('//body')->item(0)->textContent : '';
    $words = str_word_count(preg_replace('/\s+/u', ' ', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $bodyText) ?: $bodyText));
    $isSpa = (bool) preg_match('/<div[^>]+id=["\'](root|__next|app|__nuxt)["\'][^>]*>\s*<\/div>/i', $html);
    // 3. Contenido legible sin JavaScript
    $ratio = $words >= 400 ? 1 : ($words >= 150 ? 0.5 : 0);
    $add('content', 'Contenido legible sin JavaScript', 15, $ratio,
        "Encontramos ~$words palabras de texto en el HTML" . ($isSpa ? ' y el sitio parece depender de JavaScript para mostrar el contenido.' : '.'),
        'La mayoría de los rastreadores de IA no ejecuta JavaScript: el contenido clave (qué hacés, para quién, dónde, precios, preguntas frecuentes) tiene que estar en el HTML que entrega el servidor.', 'alta');

    // 4. robots.txt y bots de IA
    $rob = audit_fetch($origin . '/robots.txt', 300000, 2);
    $robotsTxt = $rob['ok'] ? $rob['body'] : '';
    $bots = $robotsTxt !== '' ? audit_robots_bots($robotsTxt) : array_fill_keys(array_keys(AI_BOTS), 'permitido');
    $blocked = array_keys(array_filter($bots, fn($s) => $s === 'bloqueado'));
    $add('ai_bots', 'Acceso de los bots de IA', 15, 1 - count($blocked) / count($bots),
        $blocked ? 'robots.txt bloquea a: ' . implode(', ', $blocked) . '.' : 'Ningún bot de IA está bloqueado en robots.txt.',
        'Revisar robots.txt para permitir los rastreadores de búsqueda de IA (OAI-SearchBot, ChatGPT-User, PerplexityBot, Claude-SearchBot). Si bloqueás el entrenamiento, hacelo de forma deliberada y sin cerrar la búsqueda.', 'alta');

    // 5. llms.txt
    $llms = audit_fetch($origin . '/llms.txt', 300000, 2);
    $hasLlms = $llms['ok'] && !preg_match('/<html/i', $llms['body']);
    $add('llms', 'Archivo llms.txt', 5, $hasLlms ? 1 : 0, $hasLlms ? 'Tiene /llms.txt publicado.' : 'No encontramos /llms.txt.',
        'Publicar un /llms.txt: un resumen en texto plano de quién sos, tus servicios y tus páginas clave, pensado para modelos de lenguaje.', 'baja');

    // 6. Datos estructurados
    $types = [];
    preg_match_all('#<script[^>]+application/ld\+json[^>]*>(.*?)</script>#is', $html, $mm);
    foreach ($mm[1] as $json) {
        $d = json_decode(trim($json), true);
        $walk = function ($x) use (&$walk, &$types) {
            if (!is_array($x)) return;
            if (isset($x['@type'])) foreach ((array) $x['@type'] as $t) $types[] = (string) $t;
            foreach ($x as $v) if (is_array($v)) $walk($v);
        };
        $walk($d);
    }
    $types = array_values(array_unique($types));
    // Organization y todos los subtipos de LocalBusiness (AccountingService, Dentist, HairSalon, Store…)
    $hasOrg = (bool) array_filter($types, fn($t) => preg_match('/(Organization|Business|Service|Store|Corporation|Clinic|Physician|Dentist|School|Restaurant|Hotel|Salon|Agency|Office|Center)$/', $t) && $t !== 'Service');
    $hasRich = (bool) array_intersect($types, ['FAQPage', 'Product', 'Service', 'Offer', 'Review', 'AggregateRating', 'Course', 'HowTo']);
    $add('schema', 'Datos estructurados (Schema.org)', 12, ($hasOrg ? 0.6 : 0) + ($hasRich ? 0.4 : 0),
        $types ? 'Tipos detectados: ' . implode(', ', array_slice($types, 0, 8)) . '.' : 'No detectamos datos estructurados JSON-LD.',
        'Agregar JSON-LD de Organization/LocalBusiness (nombre, logo, contacto, redes) y de Service, Product o FAQPage: es la forma más directa de que la IA entienda qué ofrecés.', 'alta');

    // 7. Title + meta description
    $title = $first('//title');
    $desc = '';
    foreach ($xp->query('//meta[@name]') as $m) if (strtolower($m->getAttribute('name')) === 'description') $desc = trim($m->getAttribute('content'));
    $tOk = mb_strlen($title) >= 15 && mb_strlen($title) <= 70; $dOk = mb_strlen($desc) >= 70;
    $add('meta', 'Título y descripción', 8, ($tOk ? 0.5 : ($title ? 0.25 : 0)) + ($dOk ? 0.5 : ($desc ? 0.25 : 0)),
        'Título: ' . ($title ? '“' . mb_substr($title, 0, 80) . '”' : 'no encontrado') . '. Descripción: ' . ($desc ? mb_strlen($desc) . ' caracteres.' : 'no encontrada.'),
        'Escribir un título claro (servicio + diferencial + marca) y una meta descripción que responda qué hacés y para quién: es el resumen que leen buscadores e IA.');

    // 8. Jerarquía de encabezados
    $h1 = $xp->query('//h1')->length; $h2 = $xp->query('//h2')->length;
    $add('headings', 'Jerarquía de encabezados', 8, ($h1 === 1 ? 0.6 : ($h1 > 1 ? 0.3 : 0)) + ($h2 >= 2 ? 0.4 : 0),
        "La página tiene $h1 H1 y $h2 H2.",
        'Usar un único H1 con la propuesta principal y H2 que funcionen como preguntas/respuestas: los modelos extraen respuestas a partir de esa estructura.');

    // 9. Open Graph
    $og = 0;
    foreach ($xp->query('//meta[@property]') as $m) if (in_array(strtolower($m->getAttribute('property')), ['og:title', 'og:description', 'og:image'], true)) $og++;
    $add('og', 'Etiquetas para compartir (Open Graph)', 4, $og / 3, "Detectamos $og de 3 etiquetas Open Graph principales.",
        'Completar og:title, og:description y og:image para que la marca se vea bien cuando se comparte y cuando la citan asistentes.', 'baja');

    // 10. Sitemap
    $sitemapUrls = [];
    if (preg_match_all('/^\s*sitemap:\s*(\S+)/im', $robotsTxt, $sm)) $sitemapUrls = $sm[1];
    $hasSitemap = (bool) $sitemapUrls;
    if (!$hasSitemap) foreach (['/sitemap.xml', '/sitemap_index.xml', '/wp-sitemap.xml'] as $s) {
        $r = audit_fetch($origin . $s, 200000, 2);
        if ($r['ok'] && str_contains($r['body'], '<urlset') || ($r['ok'] && str_contains($r['body'], '<sitemapindex'))) { $hasSitemap = true; break; }
    }
    $add('sitemap', 'Mapa del sitio (sitemap.xml)', 6, $hasSitemap ? 1 : 0, $hasSitemap ? 'Tiene sitemap disponible.' : 'No encontramos sitemap.xml.',
        'Publicar un sitemap.xml y declararlo en robots.txt para que todas las páginas importantes se descubran e indexen.');

    // 11. Canonical + idioma
    $canon = $xp->query('//link[@rel="canonical"]')->length > 0;
    $lang = $xp->query('//html[@lang]')->length > 0;
    $add('canon', 'Canonical e idioma declarado', 4, ($canon ? 0.5 : 0) + ($lang ? 0.5 : 0),
        ($canon ? 'Tiene canonical' : 'Sin canonical') . ' · ' . ($lang ? 'idioma declarado.' : 'sin atributo de idioma.'),
        'Declarar la URL canónica y el idioma (lang="es-AR") ayuda a que la IA atribuya bien el contenido y responda en el mercado correcto.', 'baja');

    // 12. Imágenes con alt
    $imgs = $xp->query('//img'); $withAlt = 0;
    foreach ($imgs as $im) if (trim($im->getAttribute('alt')) !== '') $withAlt++;
    $altRatio = $imgs->length ? $withAlt / $imgs->length : 1;
    $add('alt', 'Imágenes descriptas (texto alternativo)', 4, $altRatio >= 0.8 ? 1 : $altRatio,
        $imgs->length ? "$withAlt de {$imgs->length} imágenes tienen texto alternativo." : 'No hay imágenes en el HTML inicial.',
        'Describir las imágenes con texto alternativo: los modelos multimodales y los buscadores las entienden por ese texto.', 'baja');

    // 13. Señales de confianza (E-E-A-T)
    $hasPhone = (bool) preg_match('/(\+?\d[\d\s().-]{7,}\d)|tel:/i', $html);
    $hasMail  = (bool) preg_match('/mailto:|[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $html);
    $hasAbout = (bool) preg_match('~href=["\'][^"\']*(nosotros|about|quienes|equipo|empresa)~i', $html);
    $add('trust', 'Señales de confianza en el sitio', 5, ($hasPhone ? 0.34 : 0) + ($hasMail ? 0.33 : 0) + ($hasAbout ? 0.33 : 0),
        implode(' · ', [($hasPhone ? '✓' : '✗') . ' teléfono', ($hasMail ? '✓' : '✗') . ' email', ($hasAbout ? '✓' : '✗') . ' página “nosotros”']),
        'Mostrar datos de contacto, quiénes están detrás y casos reales: la IA recomienda marcas que puede verificar como confiables.');

    // 14. Indexable
    $noindex = false;
    foreach ($xp->query('//meta[@name]') as $m) if (strtolower($m->getAttribute('name')) === 'robots' && str_contains(strtolower($m->getAttribute('content')), 'noindex')) $noindex = true;
    if (preg_match('/x-robots-tag:.*noindex/i', $home['headers'])) $noindex = true;
    $add('index', 'Página indexable', 4, $noindex ? 0 : 1, $noindex ? 'La página tiene noindex: no debería aparecer en buscadores ni en respuestas de IA.' : 'La página es indexable.',
        'Quitar la directiva noindex de las páginas que querés que te recomienden.', 'alta');

    $score = (int) round(array_sum(array_column($checks, 'points')));
    return [
        'reachable' => true, 'final_url' => $finalUrl, 'checks' => $checks, 'score' => $score, 'bots' => $bots,
        'title' => $title, 'words' => $words, 'schema_types' => $types, 'has_robots' => $robotsTxt !== '',
    ];
}

/* ------------------------------------------------------------------ */
/* Cuestionario de señales externas                                    */
/* ------------------------------------------------------------------ */
function audit_questions(): array {
    return [
        'gbp'      => ['¿Tenés el perfil de Google Business completo y actualizado?', ['si' => 'Sí, completo', 'parcial' => 'Lo tengo pero incompleto', 'no' => 'No tengo / no sé'], ['si' => 1, 'parcial' => 0.5, 'no' => 0],
                       'Completar y mantener activo el perfil de Google Business (categorías, horarios, fotos, servicios y publicaciones): es una de las fuentes que más usan los asistentes para recomendar negocios.'],
        'reviews'  => ['¿Cuántas reseñas tenés en Google?', ['50+' => 'Más de 50', '10-50' => 'Entre 10 y 50', '1-9' => 'Menos de 10', '0' => 'Ninguna'], ['50+' => 1, '10-50' => 0.7, '1-9' => 0.3, '0' => 0],
                       'Armar un sistema para pedir reseñas a cada cliente satisfecho y responderlas todas: el volumen y la calidad de las reseñas pesan en lo que la IA dice de vos.'],
        'chatgpt'  => ['Si le preguntás a ChatGPT por tu rubro en tu zona, ¿te menciona?', ['si' => 'Sí, me menciona', 'aveces' => 'A veces / a la competencia sí', 'no' => 'No / nunca lo probé'], ['si' => 1, 'aveces' => 0.4, 'no' => 0],
                       'Medir la visibilidad en IA de forma periódica (qué preguntas te traen, a quién recomiendan en tu lugar) y trabajar el contenido para esas preguntas.'],
        'content'  => ['¿Publicás contenido útil (blog, guías, preguntas frecuentes) en tu sitio?', ['si' => 'Sí, seguido', 'aveces' => 'Cada tanto', 'no' => 'No'], ['si' => 1, 'aveces' => 0.5, 'no' => 0],
                       'Crear contenido que responda las preguntas reales de tus clientes, con datos concretos y propios: es el material que la IA cita.'],
        'mentions' => ['¿Aparecés en medios, directorios o sitios de referencia de tu industria?', ['si' => 'Sí, en varios', 'pocos' => 'En pocos', 'no' => 'No'], ['si' => 1, 'pocos' => 0.5, 'no' => 0],
                       'Conseguir menciones en medios, directorios y asociaciones del rubro: los modelos confían más en marcas que aparecen citadas por terceros.'],
        'social'   => ['¿Tus redes y tu sitio describen tu negocio de forma coherente (mismo nombre, servicios y datos)?', ['si' => 'Sí', 'parcial' => 'Más o menos', 'no' => 'No'], ['si' => 1, 'parcial' => 0.5, 'no' => 0],
                       'Unificar nombre, descripción, servicios y datos de contacto en todos los canales para que la IA identifique a tu marca como una sola entidad.'],
    ];
}

function audit_external(array $answers): array {
    $items = []; $sum = 0;
    foreach (audit_questions() as $k => [$q, $opts, $vals, $reco]) {
        $a = $answers[$k] ?? 'no';
        $v = $vals[$a] ?? 0;
        $sum += $v;
        $items[] = ['id' => $k, 'question' => $q, 'answer' => $opts[$a] ?? '—', 'value' => $v, 'reco' => $reco];
    }
    return ['score' => (int) round($sum / count(audit_questions()) * 100), 'items' => $items];
}

function audit_level(int $score): array {
    if ($score >= 80) return ['IA-friendly', 'Tu marca está bien preparada para que la IA la lea y la recomiende. El desafío ahora es medir y ganar más menciones.', '#16a34a'];
    if ($score >= 60) return ['En camino', 'Tenés una buena base, pero hay ajustes concretos que hoy le hacen perder recomendaciones frente a la competencia.', '#ca8a04'];
    if ($score >= 40) return ['Visibilidad baja', 'La IA puede encontrarte, pero le cuesta entender qué hacés y por qué recomendarte.', '#ea580c'];
    return ['Invisible para la IA', 'Hoy es muy difícil que un asistente de IA te recomiende. La buena noticia: casi todo lo que falta se puede resolver.', '#dc2626'];
}

/** Arma el resultado completo y la lista priorizada de recomendaciones. */
function audit_run(string $url, array $answers): array {
    $site = audit_site($url);
    $ext = audit_external($answers);
    $siteScore = $site['reachable'] ? $site['score'] : 0;
    $final = $site['reachable'] ? (int) round($siteScore * 0.7 + $ext['score'] * 0.3) : (int) round($ext['score'] * 0.3);

    $recos = [];
    if (!$site['reachable']) {
        $recos[] = ['prio' => 'alta', 'title' => 'No pudimos leer tu sitio', 'text' => 'Nuestro analizador no logró acceder (' . $site['error'] . '). Si a nosotros nos pasa, es probable que a varios rastreadores de IA también: conviene revisar firewall, bloqueos de bots y velocidad del servidor.'];
    }
    foreach ($site['checks'] as $c) if ($c['status'] !== 'ok') $recos[] = ['prio' => $c['prio'], 'title' => $c['label'], 'text' => $c['reco']];
    foreach ($ext['items'] as $i) if ($i['value'] < 1) $recos[] = ['prio' => $i['value'] == 0 ? 'media' : 'baja', 'title' => mb_substr($i['question'], 0, 70), 'text' => $i['reco']];
    $order = ['alta' => 0, 'media' => 1, 'baja' => 2];
    usort($recos, fn($a, $b) => $order[$a['prio']] <=> $order[$b['prio']]);

    [$level, $levelText, $color] = audit_level($final);
    return [
        'url' => $url, 'score' => $final, 'site_score' => $siteScore, 'ext_score' => $ext['score'],
        'level' => $level, 'level_text' => $levelText, 'color' => $color,
        'site' => $site, 'external' => $ext, 'recos' => $recos, 'generated_at' => now(),
    ];
}
