<?php
/* =====================================================================
 * Herramientas: Diagnóstico IA-friendly y Plan de pauta de prueba
 * Cada envío crea un lead en el CRM + correo al cliente + aviso al equipo.
 * ===================================================================== */
require_once APP . '/lib/ai_audit.php';
require_once APP . '/lib/ad_plan.php';

/* ---------------------- Diagnóstico IA-friendly ---------------------- */
function page_ai_diag(): void {
    view('tools/ai_diag', ['meta' => [
        'title' => 'Diagnóstico IA-friendly gratis: ¿te recomienda ChatGPT? | Nifty Agency',
        'description' => 'Analizamos tu sitio como lo leen ChatGPT, Gemini, Claude y Perplexity: acceso de bots de IA, contenido, datos estructurados y reputación. Recibí tu puntaje y recomendaciones por email.',
    ]]);
}

function ai_diag_submit(): void {
    csrf_check();
    @set_time_limit(90);
    $spam = spam_guard('diagnostico_ia', 5);
    if ($spam === 'limit') { flash('Ya hiciste varios diagnósticos en la última hora. Probá de nuevo más tarde.', 'err'); redirect('/diagnostico-ia#form'); }
    if ($spam) redirect('/gracias');

    $f = ['name' => post('nombre'), 'company' => post('empresa'), 'email' => post('email'), 'phone' => post('whatsapp'), 'website' => post('sitio'), 'rubro' => post('rubro')];
    $url = audit_normalize_url($f['website']);
    if ($f['name'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || !$url) {
        flash('Revisá tu nombre, email y la dirección de tu sitio web.', 'err'); redirect('/diagnostico-ia#form');
    }
    $f['website'] = $url;
    $answers = [];
    foreach (array_keys(audit_questions()) as $k) $answers[$k] = post('q_' . $k, 'no');

    $result = audit_run($url, $answers);
    $result['ai_note'] = ai_diag_note($result, $f);
    [$id, $tok] = create_lead('diagnostico_ia', $f, ['respuestas' => $answers, 'consent' => post('consent') === '1'], $result, $result['score']);

    // Correo al cliente
    $link = url('diagnostico-ia/resultado/' . $tok);
    $top = array_slice($result['recos'], 0, 5);
    $list = '';
    foreach ($top as $r) $list .= '<li style="margin:0 0 10px"><b>' . e($r['title']) . '.</b> ' . e($r['text']) . '</li>';
    $inner = '<p>¡Hola ' . e($f['name']) . '!</p><p>Este es el diagnóstico de <b>' . e(parse_url($url, PHP_URL_HOST)) . '</b>: qué tan preparada está tu marca para que la lean y la recomienden los motores de IA (ChatGPT, Gemini, Claude, Perplexity y los resúmenes de Google).</p>'
        . '<div style="text-align:center;margin:22px 0;padding:22px;border-radius:14px;background:#0A0118">'
        . '<div style="font-size:52px;font-weight:bold;color:' . $result['color'] . ';line-height:1">' . $result['score'] . '<span style="font-size:20px;color:#bba9d6">/100</span></div>'
        . '<div style="color:#fff;font-weight:bold;font-size:18px;margin-top:6px">' . e($result['level']) . '</div>'
        . '<div style="color:#cbbde0;font-size:13px;margin-top:6px">Sitio ' . $result['site_score'] . '/100 · Señales externas ' . $result['ext_score'] . '/100</div></div>'
        . '<p>' . e($result['level_text']) . '</p>'
        . ($result['ai_note'] ? email_box(nl2br(e($result['ai_note'])), '#f5f3ff', '#7c3aed') : '')
        . '<p style="margin-top:20px"><b>Lo primero que recomendamos trabajar:</b></p><ol style="padding-left:20px">' . $list . '</ol>'
        . email_box('<b>¿Y ahora qué?</b> Cada uno de estos puntos tiene una solución técnica y de contenido concreta. En Nifty trabajamos <b>SEO + GEO</b> (posicionamiento en buscadores y en motores de IA) para que tu marca sea la que la IA recomienda. Coordinemos una reunión de 30 minutos y te mostramos por dónde empezar en tu caso.')
        . email_button(setting('agenda_url'), 'Agendar una reunión con Nifty') . email_button($link, 'Ver el diagnóstico completo', '#ede9fe', '#4c1d95');
    send_mail($f['email'], 'Tu diagnóstico IA-friendly: ' . $result['score'] . '/100 — Nifty Agency', email_layout('Tu marca frente a la IA: ' . $result['level'], $inner, 'Diagnóstico IA-friendly'),
        ['lead_id' => $id, 'reply_to' => [setting('contact_email'), 'Nifty Agency']]);

    // Aviso al equipo
    notify_team_lead($id, '🤖 Diagnóstico IA ' . $result['score'] . '/100 — ' . ($f['company'] ?: parse_url($url, PHP_URL_HOST)),
        'Un prospecto hizo el Diagnóstico IA-friendly. Buen momento para escribirle y ofrecer una reunión de SEO + GEO.',
        ['Nombre' => $f['name'], 'Empresa' => $f['company'], 'Email' => $f['email'], 'WhatsApp' => $f['phone'], 'Sitio' => $url, 'Rubro' => $f['rubro'],
         'Puntaje' => $result['score'] . '/100 — ' . $result['level'], 'Sitio / Externas' => $result['site_score'] . ' / ' . $result['ext_score'],
         'Prioridades' => implode("\n", array_map(fn($r) => '• ' . $r['title'], array_slice($result['recos'], 0, 4)))]);

    redirect('/diagnostico-ia/resultado/' . $tok);
}

function ai_diag_note(array $r, array $f): ?string {
    if (!claude_enabled()) return null;
    $fails = array_map(fn($x) => $x['title'], array_slice($r['recos'], 0, 6));
    return claude_ask(
        'Sos estratega de SEO + GEO en Nifty Agency (Buenos Aires). Escribís en español rioplatense, claro, cálido y profesional, sin tecnicismos innecesarios.',
        "Escribí 3 oraciones (máx. 70 palabras) para {$f['name']} de " . ($f['company'] ?: 'su empresa') . " (rubro: {$f['rubro']}) que expliquen qué significa su puntaje {$r['score']}/100 de preparación para IA y cuál es la oportunidad más importante. Puntos débiles detectados: " . implode('; ', $fails) . '. No prometas resultados ni inventes datos.',
        300
    );
}

function page_ai_diag_result(string $tok): void {
    $lead = row("SELECT * FROM leads WHERE token = ? AND type = 'diagnostico_ia'", [$tok]);
    if (!$lead) not_found();
    view('tools/ai_diag_result', ['lead' => $lead, 'r' => jdec($lead['result']), 'meta' => ['title' => 'Tu diagnóstico IA-friendly — Nifty Agency', 'robots' => 'noindex']]);
}

/* ---------------------- Plan de pauta de prueba ---------------------- */
function page_ad_plan(): void {
    view('tools/ad_plan', ['opts' => plan_options(), 'meta' => [
        'title' => 'Plan de pauta publicitaria gratis para tu negocio | Nifty Agency',
        'description' => 'Completá un brief de tu negocio y recibí por email un plan de pauta sugerido: mix de canales, estructura de campañas, estimaciones y hoja de ruta de 90 días.',
    ]]);
}

function ad_plan_submit(): void {
    csrf_check();
    $spam = spam_guard('plan_pauta', 5);
    if ($spam === 'limit') { flash('Ya generaste varios planes en la última hora. Probá de nuevo más tarde.', 'err'); redirect('/plan-de-pauta#brief'); }
    if ($spam) redirect('/gracias');

    $opts = plan_options();
    $b = [
        'negocio' => post('negocio'), 'rubro' => array_key_exists(post('rubro'), $opts['rubros']) ? post('rubro') : 'otro',
        'web' => post('web'), 'modelo' => post('modelo', 'b2c'), 'objetivo' => array_key_exists(post('objetivo'), $opts['objetivos']) ? post('objetivo') : 'leads',
        'producto' => post('producto'), 'publico' => post('publico'), 'zona' => post('zona', 'nacional'), 'ciclo' => post('ciclo', 'dias'),
        'moneda' => post('moneda') === 'USD' ? 'USD' : 'ARS', 'presupuesto' => (float) str_replace(['.', ','], ['', '.'], post('presupuesto', '0')),
        'ticket' => (float) str_replace(['.', ','], ['', '.'], post('ticket', '0')),
        'medicion' => post('medicion', 'no'), 'crm' => post('crm', 'no'), 'video' => post('video', 'no'),
        'competidores' => post('competidores'), 'fecha' => post('fecha'), 'canales_actuales' => post('canales_actuales'),
        'email' => post('email'),
    ];
    $f = ['name' => post('nombre'), 'company' => $b['negocio'], 'email' => $b['email'], 'phone' => post('whatsapp'), 'website' => $b['web'], 'rubro' => $opts['rubros'][$b['rubro']]];
    if ($f['name'] === '' || $b['negocio'] === '' || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || $b['presupuesto'] <= 0) {
        flash('Completá tu nombre, el nombre del negocio, el presupuesto mensual y un email válido.', 'err'); redirect('/plan-de-pauta#brief');
    }

    $plan = plan_build($b);
    $plan['ai_note'] = ad_plan_note($plan, $b);
    [$id, $tok] = create_lead('plan_pauta', $f, $b, $plan, null);

    $link = url('plan-de-pauta/resultado/' . $tok);
    $cur = $plan['currency'];
    $mixRows = '';
    foreach ($plan['channels'] as $c) {
        $mixRows .= '<tr><td style="padding:10px 12px;border-bottom:1px solid #eee7f5"><b>' . e($c['name']) . '</b><br><span style="color:#8a8199;font-size:13px">' . e($c['formats']) . '</span></td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #eee7f5;text-align:right;white-space:nowrap"><b>' . $c['pct'] . '%</b><br><span style="color:#8a8199;font-size:13px">' . e(money($c['amount'], $cur)) . '</span></td></tr>';
    }
    $est = $plan['estimates'];
    $inner = '<p>¡Hola ' . e($f['name']) . '!</p><p>Este es el <b>plan de pauta sugerido para ' . e($b['negocio']) . '</b>, armado con la metodología de Nifty a partir de tu brief. Objetivo: <b>' . e($plan['objective']) . '</b> · Inversión mensual: <b>' . e(money($b['presupuesto'], $cur)) . '</b>.</p>'
        . ($plan['ai_note'] ? email_box(nl2br(e($plan['ai_note'])), '#f5f3ff', '#7c3aed') : '')
        . '<p style="margin:20px 0 6px"><b>Distribución sugerida</b></p><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px">' . $mixRows . '</table>'
        . '<p style="margin:20px 0 6px"><b>Estimación orientativa del primer mes</b></p>'
        . email_table(['Clics' => number_format($est['clicks'][0], 0, ',', '.') . ' – ' . number_format($est['clicks'][1], 0, ',', '.'),
            ucfirst($est['conv_label']) => number_format($est['conv'][0], 0, ',', '.') . ' – ' . number_format($est['conv'][1], 0, ',', '.')])
        . email_box('<b>Este plan es una hoja de ruta, no una receta.</b> La estructura de campañas, las audiencias y las creatividades se ajustan con datos reales de tu cuenta, y la implementación la hace el equipo de Nifty con su metodología propia de optimización semanal. Código de plan: <b>' . e($plan['code']) . '</b> (vigente hasta el ' . date('d/m/Y', strtotime($plan['valid_until'])) . ').')
        . email_button(setting('agenda_url'), 'Quiero implementarlo con Nifty') . email_button($link, 'Ver el plan completo', '#ede9fe', '#4c1d95');
    send_mail($f['email'], 'Tu plan de pauta sugerido — ' . $b['negocio'] . ' (' . $plan['code'] . ')', email_layout('Plan de pauta para ' . $b['negocio'], $inner, 'Plan de pauta de prueba'),
        ['lead_id' => $id, 'reply_to' => [setting('contact_email'), 'Nifty Agency']]);

    $opt = plan_options();
    notify_team_lead($id, '📈 Plan de pauta — ' . $b['negocio'] . ' · ' . money($b['presupuesto'], $cur) . '/mes',
        'Un prospecto generó un plan de pauta de prueba. Tiene presupuesto definido: ideal para llamarlo y proponer una reunión.',
        ['Nombre' => $f['name'], 'Negocio' => $b['negocio'], 'Email' => $f['email'], 'WhatsApp' => $f['phone'], 'Sitio' => $b['web'], 'Rubro' => $f['rubro'],
         'Objetivo' => $plan['objective'], 'Modelo' => $opt['modelos'][$b['modelo']] ?? $b['modelo'], 'Presupuesto' => money($b['presupuesto'], $cur) . ' / mes',
         'Ticket promedio' => $b['ticket'] ? money($b['ticket'], $cur) : '', 'Zona' => $opt['zonas'][$b['zona']] ?? '', 'Público' => $b['publico'], 'Plan' => $plan['code']]);

    redirect('/plan-de-pauta/resultado/' . $tok);
}

function ad_plan_note(array $plan, array $b): ?string {
    if (!claude_enabled()) return null;
    $mix = implode(', ', array_map(fn($c) => $c['name'] . ' ' . $c['pct'] . '%', $plan['channels']));
    return claude_ask(
        'Sos estratega de paid media senior en Nifty Agency (Buenos Aires). Español rioplatense, concreto y profesional.',
        "Negocio: {$b['negocio']} · Rubro: {$plan['rubro']} · Producto/servicio: {$b['producto']} · Público: {$b['publico']} · Objetivo: {$plan['objective']} · Presupuesto: " . money($b['presupuesto'], $plan['currency']) . "/mes · Mix sugerido: $mix · Competidores: {$b['competidores']} · Fecha clave: {$b['fecha']}.\n"
        . 'Escribí una lectura estratégica de 3 a 4 oraciones (máx. 90 palabras): el ángulo creativo principal que probarías y el principal riesgo a cuidar. No prometas resultados ni inventes cifras.',
        350
    );
}

function page_ad_plan_result(string $tok): void {
    $lead = row("SELECT * FROM leads WHERE token = ? AND type = 'plan_pauta'", [$tok]);
    if (!$lead) not_found();
    view('tools/ad_plan_result', ['lead' => $lead, 'plan' => jdec($lead['result']), 'brief' => jdec($lead['payload']), 'opts' => plan_options(),
        'meta' => ['title' => 'Tu plan de pauta sugerido — Nifty Agency', 'robots' => 'noindex']]);
}
