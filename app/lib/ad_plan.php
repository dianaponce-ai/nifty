<?php
/**
 * Plan de pauta sugerido a partir de un brief.
 * Es un plan de referencia con la metodología Nifty: mix de canales, estructura de campañas,
 * embudo, estimaciones orientativas y hoja de ruta de 90 días.
 */

function plan_options(): array {
    return [
        'rubros' => [
            'ecommerce'  => 'Ecommerce / tienda online',
            'servicios_b2b' => 'Servicios B2B / corporativo',
            'industria'  => 'Industria / distribución mayorista',
            'salud'      => 'Salud / centro médico / estética',
            'educacion'  => 'Educación / cursos',
            'belleza'    => 'Peluquería / belleza / bienestar',
            'gastronomia'=> 'Gastronomía / hotelería / turismo',
            'inmobiliaria'=> 'Inmobiliaria / construcción',
            'tecnologia' => 'Tecnología / software / SaaS',
            'retail'     => 'Comercio con local físico',
            'automotor'  => 'Automotor / movilidad',
            'otro'       => 'Otro',
        ],
        'objetivos' => [
            'ventas'   => 'Vender online',
            'leads'    => 'Conseguir consultas / leads calificados',
            'local'    => 'Más visitas, turnos o reservas',
            'marca'    => 'Que me conozcan (reconocimiento)',
        ],
        'modelos' => ['b2c' => 'Vendo a consumidores (B2C)', 'b2b' => 'Vendo a empresas (B2B)', 'ambos' => 'Ambos'],
        'zonas'   => ['local' => 'Mi ciudad / zona', 'nacional' => 'Todo el país', 'latam' => 'Varios países'],
        'ciclos'  => ['inmediato' => 'Compran en el momento', 'dias' => 'Lo piensan unos días', 'semanas' => 'Semanas', 'meses' => 'Meses (decisión compleja)'],
    ];
}

/** Benchmarks orientativos en USD (Argentina/LatAm): [cpc_min, cpc_max, conv_min, conv_max] */
function plan_benchmarks(string $rubro): array {
    return [
        'ecommerce'    => [0.12, 0.35, 0.010, 0.025],
        'servicios_b2b'=> [0.35, 1.20, 0.025, 0.060],
        'industria'    => [0.25, 0.80, 0.020, 0.050],
        'salud'        => [0.20, 0.60, 0.040, 0.090],
        'educacion'    => [0.15, 0.45, 0.040, 0.080],
        'belleza'      => [0.10, 0.30, 0.050, 0.110],
        'gastronomia'  => [0.10, 0.30, 0.030, 0.070],
        'inmobiliaria' => [0.25, 0.80, 0.015, 0.040],
        'tecnologia'   => [0.40, 1.40, 0.020, 0.050],
        'retail'       => [0.10, 0.30, 0.030, 0.070],
        'automotor'    => [0.20, 0.60, 0.020, 0.050],
        'otro'         => [0.15, 0.60, 0.020, 0.060],
    ][$rubro] ?? [0.15, 0.60, 0.020, 0.060];
}

function plan_build(array $b): array {
    $opts = plan_options();
    $rate = max(1, (float) setting('usd_ars'));
    $cur = $b['moneda'] === 'ARS' ? 'ARS' : 'USD';
    $budget = max(0, (float) $b['presupuesto']);
    $budgetUsd = $cur === 'ARS' ? $budget / $rate : $budget;
    $obj = $b['objetivo']; $model = $b['modelo']; $rubro = $b['rubro'];
    $hasVideo = $b['video'] === 'si';
    $b2b = $model === 'b2b' || in_array($rubro, ['servicios_b2b', 'industria'], true);

    // 1) Mix de canales según objetivo y modelo
    $mix = match (true) {
        $obj === 'ventas'              => ['google' => 45, 'meta' => 45, 'tiktok' => $hasVideo ? 10 : 0, 'remarketing' => $hasVideo ? 0 : 10],
        $obj === 'leads' && $b2b       => ['google' => 55, 'linkedin' => 25, 'meta' => 20],
        $obj === 'leads'               => ['meta' => 50, 'google' => 40, 'remarketing' => 10],
        $obj === 'local'               => ['meta' => 50, 'google' => 40, 'remarketing' => 10],
        default                        => ['meta' => 50, 'youtube' => 25, 'tiktok' => $hasVideo ? 25 : 0, 'google' => $hasVideo ? 0 : 25],
    };
    // LinkedIn solo tiene sentido con presupuesto suficiente
    if (isset($mix['linkedin']) && $budgetUsd < 1200) { $mix['google'] += $mix['linkedin'] - 10; $mix['meta'] += 10; unset($mix['linkedin']); }
    // Presupuestos chicos: concentrar en 2 canales
    if ($budgetUsd < 350) {
        arsort($mix); $top = array_slice($mix, 0, 2, true); $tot = array_sum($top);
        $mix = array_map(fn($v) => (int) round($v / $tot * 100), $top);
    }
    $mix = array_filter($mix);
    arsort($mix);

    $channelInfo = [
        'google'      => ['Google Ads', 'Search + Performance Max', 'Capturar a quien ya está buscando lo que vendés.'],
        'meta'        => ['Meta Ads', 'Facebook + Instagram (+ WhatsApp)', 'Generar demanda y conversaciones con creatividades que frenen el scroll.'],
        'linkedin'    => ['LinkedIn Ads', 'Segmentación por cargo e industria', 'Llegar a decisores con contenido de autoridad.'],
        'tiktok'      => ['TikTok Ads', 'Video vertical y UGC', 'Alcance eficiente con contenido nativo de la plataforma.'],
        'youtube'     => ['YouTube Ads', 'Video in-stream y Demand Gen', 'Construir recordación de marca con video.'],
        'remarketing' => ['Remarketing', 'Meta + Google Display', 'Recuperar a quien visitó el sitio y no convirtió.'],
    ];

    $channels = [];
    foreach ($mix as $k => $pct) {
        $amount = $budget * $pct / 100;
        $channels[] = ['key' => $k, 'name' => $channelInfo[$k][0], 'formats' => $channelInfo[$k][1], 'why' => $channelInfo[$k][2],
            'pct' => $pct, 'amount' => $amount, 'campaigns' => plan_campaigns($k, $obj, $b2b, $b)];
    }

    // 2) Estimaciones orientativas
    [$cpcMin, $cpcMax, $crMin, $crMax] = plan_benchmarks($rubro);
    if ($b['zona'] === 'latam') { $cpcMin *= 0.9; $cpcMax *= 1.1; }
    $clicksMin = $budgetUsd > 0 ? $budgetUsd / $cpcMax : 0;
    $clicksMax = $budgetUsd > 0 ? $budgetUsd / $cpcMin : 0;
    $convMin = $clicksMin * $crMin; $convMax = $clicksMax * $crMax;
    $convLabel = match ($obj) { 'ventas' => 'ventas', 'leads' => 'leads', 'local' => 'consultas/turnos', default => 'interacciones de calidad' };
    if ($obj === 'marca') { $convMin = $budgetUsd / 4 * 1000 * 0.6; $convMax = $budgetUsd / 1.8 * 1000; $convLabel = 'personas alcanzadas'; }
    $ticket = (float) ($b['ticket'] ?? 0);
    $ticketUsd = $cur === 'ARS' ? $ticket / $rate : $ticket;

    $est = [
        'clicks' => [(int) round($clicksMin), (int) round($clicksMax)],
        'conv' => [(int) round($convMin), (int) round($convMax)],
        'conv_label' => $convLabel,
        'cpa' => $convMax > 0 && $obj !== 'marca' ? [$budgetUsd / max(1, $convMax), $budgetUsd / max(1, $convMin)] : null,
        'roas' => ($obj === 'ventas' && $ticketUsd > 0 && $budgetUsd > 0) ? [$convMin * $ticketUsd / $budgetUsd, $convMax * $ticketUsd / $budgetUsd] : null,
    ];

    // 3) Embudo
    $funnel = match ($obj) {
        'marca'  => [['Descubrimiento', 60, 'Alcance y video para que te conozcan.'], ['Consideración', 30, 'Interacción y visitas al perfil o sitio.'], ['Conversión', 10, 'Remarketing a quienes ya interactuaron.']],
        'ventas' => [['Descubrimiento', 25, 'Audiencias amplias con señales de compra.'], ['Consideración', 25, 'Catálogo, beneficios y prueba social.'], ['Conversión', 50, 'Búsqueda, Performance Max y remarketing de carrito.']],
        default  => [['Descubrimiento', 30, 'Mensajes que planteen el problema que resolvés.'], ['Consideración', 30, 'Casos, testimonios y diferenciales.'], ['Conversión', 40, 'Búsqueda de alta intención y remarketing a formularios/WhatsApp.']],
    };

    // 4) Requisitos previos detectados en el brief
    $prereq = [];
    if ($b['medicion'] !== 'si') $prereq[] = ['Medición', 'Instalar Google Tag Manager, GA4, Píxel/API de Conversiones de Meta y etiqueta de conversión de Google Ads con eventos de negocio reales.'];
    if ($b['crm'] !== 'si') $prereq[] = ['Seguimiento de leads', 'Centralizar consultas (formularios y WhatsApp) en un CRM para medir cuántas se convierten en ventas.'];
    if (!$b['web'] ) $prereq[] = ['Destino de la pauta', 'Definir una landing page o catálogo optimizado para conversión: sin un buen destino, la pauta se desperdicia.'];
    if (!$hasVideo) $prereq[] = ['Creatividades', 'Producir piezas en video vertical (UGC o testimonios): hoy son el formato con mejor costo por resultado en Meta y TikTok.'];
    if ($budgetUsd > 0 && $budgetUsd < 250) $prereq[] = ['Presupuesto', 'Con menos de USD 250/mes conviene empezar con un solo canal y un objetivo, para que los algoritmos tengan datos suficientes para aprender.'];

    // 5) Hoja de ruta 90 días
    $roadmap = [
        ['Mes 1 · Base y aprendizaje', ['Auditoría de cuentas, medición y destino.', 'Lanzamiento de campañas con 3–5 ángulos creativos por canal.', 'Lectura semanal de datos y descarte de lo que no funciona.']],
        ['Mes 2 · Optimización', ['Concentrar inversión en audiencias y anuncios ganadores.', 'Ajuste de pujas, palabras clave negativas y segmentos.', 'Pruebas A/B de landing y mensajes.']],
        ['Mes 3 · Escala', ['Subir inversión gradualmente donde el costo por resultado se sostiene.', 'Sumar remarketing y audiencias similares.', 'Reporte de resultados y plan del siguiente trimestre.']],
    ];

    $kpis = match ($obj) {
        'ventas' => ['ROAS', 'Costo por compra', 'Tasa de conversión del sitio', 'Ticket promedio'],
        'leads'  => ['Costo por lead', 'Tasa de lead calificado', 'Costo por oportunidad', 'Tiempo de respuesta comercial'],
        'local'  => ['Costo por conversación/turno', 'Turnos confirmados', 'Tasa de asistencia', 'Reseñas nuevas'],
        default  => ['CPM', 'Alcance único', 'Reproducciones de video al 75%', 'Búsquedas de marca'],
    };

    $planCode = 'NFT-' . strtoupper(substr(md5($b['email'] . microtime()), 0, 6));
    return [
        'code' => $planCode, 'currency' => $cur, 'budget' => $budget, 'budget_usd' => $budgetUsd, 'rate' => $rate,
        'objective' => $opts['objetivos'][$obj] ?? $obj, 'rubro' => $opts['rubros'][$rubro] ?? $rubro,
        'channels' => $channels, 'estimates' => $est, 'funnel' => $funnel, 'prereq' => $prereq,
        'roadmap' => $roadmap, 'kpis' => $kpis, 'generated_at' => now(), 'valid_until' => date('Y-m-d', strtotime('+15 days')),
    ];
}

function plan_campaigns(string $ch, string $obj, bool $b2b, array $b): array {
    $neg = $b['negocio'] ?: 'tu negocio';
    return match ($ch) {
        'google' => array_values(array_filter([
            ['Search · Marca', 'Proteger búsquedas de “' . $neg . '” frente a la competencia.'],
            ['Search · Intención de compra', $b2b ? 'Palabras clave por línea de producto/servicio + “proveedor”, “mayorista”, “empresa”.' : 'Palabras clave de servicio/producto + zona + “precio”, “turno”, “comprar”.'],
            $obj === 'ventas' ? ['Performance Max · Catálogo', 'Feed de productos con señales de audiencia de clientes actuales.'] : ['Performance Max · Conversiones', 'Recursos de imagen, video y texto orientados a formulario o WhatsApp.'],
        ])),
        'meta' => [
            ['Prospección', $obj === 'local' ? 'Campaña de mensajes a WhatsApp con segmentación geográfica.' : 'Advantage+ con 3–5 ángulos creativos (problema, beneficio, prueba social).'],
            [$obj === 'ventas' ? 'Catálogo dinámico' : 'Formularios / WhatsApp', $obj === 'ventas' ? 'Anuncios de catálogo para quienes vieron productos.' : 'Formularios instantáneos con preguntas de calificación.'],
            ['Remarketing', 'Visitantes del sitio y quienes interactuaron con Instagram en los últimos 30 días.'],
        ],
        'linkedin' => [
            ['Autoridad', 'Contenido patrocinado: casos, datos y guías para decisores.'],
            ['Generación de leads', 'Lead Gen Forms segmentados por cargo, industria y tamaño de empresa.'],
        ],
        'tiktok' => [
            ['Alcance con UGC', 'Videos de creadores mostrando el producto o servicio en uso.'],
            ['Conversión', 'Optimización a evento de compra o lead con Spark Ads.'],
        ],
        'youtube' => [
            ['Video in-stream', 'Piezas de 15–30 s con el mensaje de marca en los primeros 5 s.'],
            ['Demand Gen', 'Video + imagen en YouTube, Discover y Gmail.'],
        ],
        'remarketing' => [
            ['Recuperación', 'Mensajes con objeciones resueltas y prueba social a visitantes recientes.'],
        ],
        default => [],
    };
}
