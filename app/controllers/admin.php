<?php
/* =====================================================================
 * Backend: acceso, CRM de leads, casos de éxito, reseñas, ajustes
 * ===================================================================== */

function admin_view(string $tpl, array $data = []): void {
    $data['me'] = current_user();
    view('admin/' . $tpl, $data, 'admin');
}

/* ----------------------------- Acceso ----------------------------- */
function admin_setup(): void {
    $hasUsers = false;
    try { $hasUsers = (int) val('SELECT COUNT(*) FROM users') > 0; } catch (Throwable $e) {}
    if ($hasUsers) {
        $u = require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); migrate(); flash('Base de datos actualizada.'); redirect('/admin/ajustes'); }
        redirect('/admin/ajustes');
    }
    $err = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $name = post('name'); $email = strtolower(post('email')); $pass = (string) ($_POST['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) $err = 'Completá nombre, email y una contraseña de al menos 10 caracteres.';
        else {
            migrate();
            require_once APP . '/seed.php';
            seed_content();
            $id = insert('users', ['name' => $name, 'email' => $email, 'password_hash' => password_hash($pass, PASSWORD_DEFAULT), 'role' => 'admin', 'created_at' => now()]);
            start_session(); session_regenerate_id(true); $_SESSION['uid'] = $id;
            flash('¡Listo! Base creada, casos y reseñas cargados.');
            redirect('/admin');
        }
    }
    view('admin/setup', ['err' => $err, 'meta' => ['title' => 'Instalación — Nifty', 'robots' => 'noindex']], 'admin_bare');
}

function admin_login(): void {
    $err = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        start_session();
        $_SESSION['login_tries'] = ($_SESSION['login_tries'] ?? 0) + 1;
        if ($_SESSION['login_tries'] > 8) { $err = 'Demasiados intentos. Esperá unos minutos.'; }
        else {
            $u = row('SELECT * FROM users WHERE email = ?', [strtolower(post('email'))]);
            if ($u && password_verify((string) ($_POST['password'] ?? ''), $u['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['uid'] = $u['id']; $_SESSION['login_tries'] = 0;
                q('UPDATE users SET last_login = ? WHERE id = ?', [now(), $u['id']]);
                $next = (string) ($_GET['next'] ?? '/admin');
                redirect(str_starts_with($next, '/') && !str_starts_with($next, '//') ? $next : '/admin');
            }
            $err = 'Email o contraseña incorrectos.';
        }
    }
    view('admin/login', ['err' => $err, 'meta' => ['title' => 'Ingresar — Nifty', 'robots' => 'noindex']], 'admin_bare');
}

function admin_logout(): void { start_session(); $_SESSION = []; session_destroy(); redirect('/admin/login'); }

/* ---------------------------- Dashboard ---------------------------- */
function admin_dashboard(): void {
    require_role();
    $since = date('Y-m-d 00:00:00', strtotime('-29 days'));
    $byType = []; foreach (rows('SELECT type, COUNT(*) n FROM leads GROUP BY type') as $r) $byType[$r['type']] = (int) $r['n'];
    $byStatus = []; foreach (rows('SELECT status, COUNT(*) n FROM leads GROUP BY status') as $r) $byStatus[$r['status']] = (int) $r['n'];
    $daily = array_fill_keys(array_map(fn($i) => date('Y-m-d', strtotime("-$i days")), range(29, 0)), 0);
    foreach (rows('SELECT created_at FROM leads WHERE created_at >= ?', [$since]) as $r) { $d = substr($r['created_at'], 0, 10); if (isset($daily[$d])) $daily[$d]++; }
    $avgIa = val("SELECT AVG(score) FROM leads WHERE type = 'diagnostico_ia'");
    $budgetUsd = 0.0;
    foreach (rows("SELECT result FROM leads WHERE type = 'plan_pauta' AND created_at >= ?", [$since]) as $r) $budgetUsd += (float) (jdec($r['result'])['budget_usd'] ?? 0);
    admin_view('dashboard', [
        'total' => array_sum($byType), 'byType' => $byType, 'byStatus' => $byStatus, 'daily' => $daily,
        'last30' => array_sum($daily), 'avgIa' => $avgIa ? round((float) $avgIa) : null, 'budgetUsd' => $budgetUsd,
        'pending' => (int) ($byStatus['nuevo'] ?? 0),
        'recent' => rows('SELECT * FROM leads ORDER BY created_at DESC LIMIT 10'),
        'cases' => (int) val('SELECT COUNT(*) FROM cases WHERE published = 1'),
        'mailFails' => (int) val('SELECT COUNT(*) FROM mail_log WHERE ok = 0 AND created_at >= ?', [$since]),
    ]);
}

/* ------------------------------ Leads ------------------------------ */
function leads_filter_sql(): array {
    $w = ['1=1']; $p = [];
    $t = (string) ($_GET['tipo'] ?? ''); $s = (string) ($_GET['estado'] ?? ''); $qq = trim((string) ($_GET['q'] ?? ''));
    if (isset(lead_types()[$t])) { $w[] = 'type = ?'; $p[] = $t; }
    if (isset(lead_statuses()[$s])) { $w[] = 'status = ?'; $p[] = $s; }
    if ($qq !== '') { $w[] = '(name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ? OR website LIKE ?)'; array_push($p, ...array_fill(0, 5, '%' . $qq . '%')); }
    return [implode(' AND ', $w), $p];
}

function admin_leads(): void {
    require_role();
    [$w, $p] = leads_filter_sql();
    admin_view('leads', ['leads' => rows("SELECT * FROM leads WHERE $w ORDER BY created_at DESC LIMIT 500", $p),
        'counts' => array_column(rows('SELECT status, COUNT(*) n FROM leads GROUP BY status'), 'n', 'status')]);
}

function admin_leads_export(): void {
    require_role();
    [$w, $p] = leads_filter_sql();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=leads-nifty-' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['ID', 'Fecha', 'Tipo', 'Estado', 'Nombre', 'Empresa', 'Email', 'Teléfono', 'Sitio', 'Rubro', 'Puntaje'], ';');
    foreach (rows("SELECT * FROM leads WHERE $w ORDER BY created_at DESC", $p) as $l) {
        fputcsv($out, [$l['id'], $l['created_at'], lead_types()[$l['type']] ?? $l['type'], lead_statuses()[$l['status']] ?? $l['status'], $l['name'], $l['company'], $l['email'], $l['phone'], $l['website'], $l['rubro'], $l['score']], ';');
    }
    exit;
}

function admin_lead(string $id): void {
    $me = require_role();
    $lead = row('SELECT * FROM leads WHERE id = ?', [(int) $id]);
    if (!$lead) not_found();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $act = post('act');
        if ($act === 'status' && isset(lead_statuses()[post('status')])) {
            $assigned = post('assigned_to') !== '' ? (int) post('assigned_to') : null;
            q('UPDATE leads SET status = ?, assigned_to = ?, updated_at = ? WHERE id = ?', [post('status'), $assigned, now(), $lead['id']]);
            if (post('status') !== $lead['status']) lead_event($lead['id'], 'estado', 'Estado: ' . lead_statuses()[$lead['status']] . ' → ' . lead_statuses()[post('status')], (int) $me['id']);
            flash('Lead actualizado.');
        } elseif ($act === 'note' && post('note') !== '') {
            lead_event($lead['id'], 'nota', post('note'), (int) $me['id']);
            q('UPDATE leads SET updated_at = ? WHERE id = ?', [now(), $lead['id']]);
            flash('Nota agregada.');
        } elseif ($act === 'mail' && post('subject') !== '' && post('body') !== '' && $lead['email']) {
            $inner = nl2br(e(post('body'))) . '<p style="margin-top:22px">' . e($me['name']) . '<br><span style="color:#8a8199">Nifty Agency</span></p>';
            if (post('add_agenda') === '1') $inner .= email_button(setting('agenda_url'), 'Elegir día y horario');
            $cc = post('cc_team') === '1' ? array_values(array_diff(team_emails(), [$me['email']])) : [];
            $ok = send_mail($lead['email'], post('subject'), email_layout(post('subject'), $inner), ['lead_id' => $lead['id'], 'cc' => $cc, 'reply_to' => [$me['email'], $me['name']]]);
            lead_event($lead['id'], 'email', "Asunto: " . post('subject') . "\n\n" . post('body') . ($ok ? '' : "\n\n[ERROR AL ENVIAR]"), (int) $me['id']);
            if ($ok && $lead['status'] === 'nuevo') q("UPDATE leads SET status = 'contactado', updated_at = ? WHERE id = ?", [now(), $lead['id']]);
            flash($ok ? 'Correo enviado.' : 'No se pudo enviar el correo. Revisá la configuración SMTP.', $ok ? 'ok' : 'err');
        }
        redirect('/admin/leads/' . $lead['id']);
    }
    admin_view('lead', ['lead' => $lead, 'payload' => jdec($lead['payload']), 'result' => jdec($lead['result']),
        'events' => rows('SELECT e.*, u.name AS user_name FROM lead_events e LEFT JOIN users u ON u.id = e.user_id WHERE lead_id = ? ORDER BY e.created_at DESC, e.id DESC', [$lead['id']]),
        'team' => rows("SELECT id, name, email FROM users WHERE role IN ('admin','equipo') ORDER BY name"),
        'mails' => rows('SELECT * FROM mail_log WHERE lead_id = ? ORDER BY id DESC', [$lead['id']])]);
}

/* -------------------------- Casos de éxito -------------------------- */
function admin_cases(): void {
    require_role();
    admin_view('cases', ['cases' => rows('SELECT * FROM cases ORDER BY sort ASC, id ASC')]);
}

/** "Título | descripción" por línea → [{t,d}] */
function lines_to_pairs(string $txt, string $a = 't', string $b = 'd'): array {
    $out = [];
    foreach (preg_split('/\r?\n/', $txt) as $l) {
        $l = trim($l); if ($l === '') continue;
        $parts = array_map('trim', explode('|', $l, 2));
        $out[] = [$a => $parts[0], $b => $parts[1] ?? ''];
    }
    return $out;
}
function pairs_to_lines(?string $json, string $a = 't', string $b = 'd'): string {
    return implode("\n", array_map(fn($x) => trim(($x[$a] ?? '') . ' | ' . ($x[$b] ?? ''), ' |'), jdec($json)));
}

function upload_image(string $field, ?int $index = null): ?string {
    $f = $_FILES[$field] ?? null;
    if (!$f) return null;
    $name = $index === null ? $f['name'] : ($f['name'][$index] ?? '');
    $tmp = $index === null ? $f['tmp_name'] : ($f['tmp_name'][$index] ?? '');
    $err = $index === null ? $f['error'] : ($f['error'][$index] ?? 4);
    $size = $index === null ? $f['size'] : ($f['size'][$index] ?? 0);
    // Vercel limita el cuerpo de cada request a ~4,5 MB
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($tmp) || $size > 4 * 1024 * 1024) return null;
    $info = @getimagesize($tmp);
    $mime = $info['mime'] ?? (class_exists('finfo') ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : '');
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/avif' => 'avif'][$mime] ?? null;
    if (!$ext) return null;
    // Las imágenes se guardan en la base (tabla media) y se sirven en /media/{id}-{nombre}
    $slug = substr(slugify(pathinfo($name, PATHINFO_FILENAME)), 0, 40) . '.' . $ext;
    $id = insert('media', ['name' => $slug, 'mime' => $mime, 'size' => (int) $size, 'data' => file_get_contents($tmp), 'created_at' => now()]);
    return 'media/' . $id . '-' . $slug;
}

/** Sirve una imagen guardada en la tabla media, con caché larga. */
function serve_media(string $id): void {
    $m = row('SELECT mime, data, created_at FROM media WHERE id = ?', [(int) $id]);
    if (!$m) not_found();
    header('Content-Type: ' . $m['mime']);
    header('Cache-Control: public, max-age=31536000, immutable');
    header('Content-Length: ' . strlen($m['data']));
    echo $m['data'];
}

function admin_case_edit(?string $id = null): void {
    require_role();
    $c = $id ? row('SELECT * FROM cases WHERE id = ?', [(int) $id]) : null;
    if ($id && !$c) not_found();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $client = post('client');
        if ($client === '') { flash('El nombre del cliente es obligatorio.', 'err'); redirect($c ? '/admin/casos/' . $c['id'] : '/admin/casos/nuevo'); }
        $slug = slugify(post('slug') ?: $client);
        $dup = val('SELECT id FROM cases WHERE slug = ? AND id <> ?', [$slug, (int) ($c['id'] ?? 0)]);
        if ($dup) $slug .= '-' . token(3);

        $gallery = jdec($c['gallery'] ?? '[]');
        $remove = (array) ($_POST['remove_gallery'] ?? []);
        $gallery = array_values(array_filter($gallery, fn($g) => !in_array($g['src'], $remove, true)));
        $captions = (array) ($_POST['gallery_caption'] ?? []);
        foreach ($gallery as $i => $g) if (isset($captions[$g['src']])) $gallery[$i]['caption'] = trim((string) $captions[$g['src']]);
        if (!empty($_FILES['gallery_new']['name'][0])) {
            foreach ($_FILES['gallery_new']['name'] as $i => $n) if ($p = upload_image('gallery_new', $i)) $gallery[] = ['src' => $p, 'caption' => ''];
        }

        $data = [
            'slug' => $slug, 'client' => $client, 'industry' => post('industry'), 'location' => post('location'), 'scope' => post('scope'),
            'title' => post('title'), 'subtitle' => post('subtitle'), 'card_summary' => post('card_summary'), 'facts' => post('facts'),
            'challenge_title' => post('challenge_title'), 'challenge_intro' => post('challenge_intro'), 'challenges' => jenc(lines_to_pairs(post('challenges'))),
            'work_title' => post('work_title'), 'work_intro' => post('work_intro'),
            'services_ops' => jenc(lines_to_pairs(post('services_ops'))), 'services_mk' => jenc(lines_to_pairs(post('services_mk'))),
            'stats' => jenc(lines_to_pairs(post('stats'), 'value', 'label')),
            'highlight' => jenc(['kicker' => post('hl_kicker'), 'title' => post('hl_title'), 'text' => post('hl_text'), 'steps' => lines_to_pairs(post('hl_steps'))]),
            'results_title' => post('results_title'), 'results' => jenc(lines_to_pairs(post('results'))),
            'gallery' => jenc($gallery), 'site_url' => post('site_url'),
            'published' => post('published') === '1' ? 1 : 0, 'sort' => (int) post('sort'), 'updated_at' => now(),
        ];
        if ($p = upload_image('hero_image')) $data['hero_image'] = $p;
        elseif (post('hero_image_url') !== '' || ($c && post('hero_image_url') === '')) $data['hero_image'] = post('hero_image_url');
        if ($p = upload_image('highlight_image')) $data['highlight_image'] = $p;
        elseif (post('highlight_image_url') !== '' || ($c && post('highlight_image_url') === '')) $data['highlight_image'] = post('highlight_image_url');

        if ($c) { update('cases', $data, (int) $c['id']); $cid = (int) $c['id']; }
        else { $data['created_at'] = now(); $cid = insert('cases', $data); }

        // Reseñas vinculadas
        q('UPDATE reviews SET case_id = NULL WHERE case_id = ?', [$cid]);
        foreach ((array) ($_POST['review_ids'] ?? []) as $rid) q('UPDATE reviews SET case_id = ? WHERE id = ?', [$cid, (int) $rid]);

        flash('Caso guardado.');
        redirect('/admin/casos/' . $cid);
    }
    admin_view('case_edit', ['c' => $c, 'reviews' => rows('SELECT * FROM reviews ORDER BY author'), 'ai' => claude_enabled()]);
}

function admin_case_delete(string $id): void {
    require_role(['admin']);
    csrf_check();
    q('UPDATE reviews SET case_id = NULL WHERE case_id = ?', [(int) $id]);
    q('DELETE FROM cases WHERE id = ?', [(int) $id]);
    flash('Caso eliminado.');
    redirect('/admin/casos');
}

/** Genera títulos, tarjetas de datos y bloques del caso a partir de los hechos descriptos. */
function admin_case_generate(): void {
    require_role();
    csrf_check();
    header('Content-Type: application/json; charset=utf-8');
    $facts = post('facts'); $client = post('client');
    if (mb_strlen($facts) < 40) { echo jenc(['error' => 'Escribí al menos un par de oraciones con los hechos del caso.']); return; }
    if (!claude_enabled()) { echo jenc(['error' => 'Para generar automáticamente, cargá tu API key de Claude en config.php (anthropic.api_key).']); return; }

    $schema = '{"title":"titular del caso, máx 12 palabras, sin el nombre del cliente al inicio","subtitle":"2 oraciones","card_summary":"1 oración para la tarjeta del listado","scope":"alcance en 2-4 palabras","challenge_title":"","challenge_intro":"","challenges":[{"t":"","d":""}],"work_title":"","work_intro":"","services_ops":[{"t":"servicio de tecnología/operación","d":""}],"services_mk":[{"t":"servicio de marca/demanda","d":""}],"stats":[{"value":"dato corto (ej: +20.000)","label":"qué significa"}],"highlight":{"kicker":"","title":"","text":"","steps":[{"t":"","d":""}]},"results_title":"","results":[{"t":"","d":""}]}';
    $d = claude_json(
        'Sos redactor de casos de éxito de Nifty Agency, agencia de marketing y tecnología de Buenos Aires. Escribís en español rioplatense, profesional y concreto. REGLA: usá solo datos presentes en los hechos; nunca inventes cifras, porcentajes ni resultados. Si no hay suficientes datos numéricos, las tarjetas pueden ser cualitativas (ej: value "360°", label "ecosistema integrado").',
        "Cliente: $client\nRubro: " . post('industry') . "\nHechos:\n$facts\n\nDevolvé un JSON con esta forma (3 desafíos, 2-4 servicios por columna si corresponde, 3-4 tarjetas de datos, 3 resultados, 3 pasos en highlight):\n$schema",
        3000
    );
    echo jenc($d ?: ['error' => 'No se pudo generar. Probá de nuevo en un momento.']);
}

/* ------------------------------ Reseñas ------------------------------ */
function admin_reviews(): void {
    require_role();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $act = post('act');
        if ($act === 'save') {
            $data = ['author' => post('author'), 'role' => post('role'), 'text' => post('text'), 'rating' => max(1, min(5, (int) post('rating', '5'))),
                'source' => post('source', 'Google'), 'case_id' => post('case_id') !== '' ? (int) post('case_id') : null,
                'featured' => post('featured') === '1' ? 1 : 0, 'sort' => (int) post('sort')];
            if ($data['author'] === '' || $data['text'] === '') flash('Autor y texto son obligatorios.', 'err');
            elseif (post('id')) { update('reviews', $data, (int) post('id')); flash('Reseña actualizada.'); }
            else { $data['created_at'] = now(); insert('reviews', $data); flash('Reseña agregada.'); }
        } elseif ($act === 'delete') {
            q('DELETE FROM reviews WHERE id = ?', [(int) post('id')]); flash('Reseña eliminada.');
        }
        redirect('/admin/resenas');
    }
    $edit = isset($_GET['editar']) ? row('SELECT * FROM reviews WHERE id = ?', [(int) $_GET['editar']]) : null;
    admin_view('reviews', ['reviews' => rows('SELECT r.*, c.client FROM reviews r LEFT JOIN cases c ON c.id = r.case_id ORDER BY r.sort, r.id DESC'),
        'cases' => rows('SELECT id, client FROM cases ORDER BY client'), 'edit' => $edit]);
}

/* ------------------------------ Ajustes ------------------------------ */
function admin_settings(): void {
    require_role(['admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (post('act') === 'test_mail') {
            $me = current_user();
            $ok = send_mail($me['email'], 'Correo de prueba — Nifty', email_layout('Correo de prueba', '<p>Si recibís este correo, el envío por SMTP funciona correctamente. ✓</p>'));
            flash($ok ? 'Correo de prueba enviado a ' . $me['email'] . '.' : 'Falló el envío. Revisá el SMTP en config.php.', $ok ? 'ok' : 'err');
        } else {
            foreach (array_keys(setting_defaults()) as $k) if (isset($_POST[$k])) setting_set($k, trim((string) $_POST[$k]));
            flash('Ajustes guardados.');
        }
        redirect('/admin/ajustes');
    }
    $vals = []; foreach (array_keys(setting_defaults()) as $k) $vals[$k] = setting($k);
    admin_view('settings', ['vals' => $vals, 'smtpOk' => (string) cfg('smtp.pass', '') !== '' && cfg('smtp.pass') !== 'CONTRASEÑA_DE_APLICACION', 'ai' => claude_enabled(),
        'mails' => rows('SELECT * FROM mail_log ORDER BY id DESC LIMIT 15')]);
}

/* ------------------------------ Usuarios ------------------------------ */
function admin_users(): void {
    $me = require_role(['admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $act = post('act');
        if ($act === 'add') {
            $email = strtolower(post('email')); $pass = (string) ($_POST['password'] ?? '');
            $role = in_array(post('role'), ['admin', 'equipo'], true) ? post('role') : 'equipo';
            if (post('name') === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 10) flash('Nombre, email y contraseña (mín. 10 caracteres) son obligatorios.', 'err');
            elseif (val('SELECT id FROM users WHERE email = ?', [$email])) flash('Ese email ya tiene usuario.', 'err');
            else { insert('users', ['name' => post('name'), 'email' => $email, 'password_hash' => password_hash($pass, PASSWORD_DEFAULT), 'role' => $role, 'created_at' => now()]); flash('Usuario creado.'); }
        } elseif ($act === 'password' && strlen((string) ($_POST['password'] ?? '')) >= 10) {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash((string) $_POST['password'], PASSWORD_DEFAULT), (int) post('id')]); flash('Contraseña actualizada.');
        } elseif ($act === 'delete' && (int) post('id') !== (int) $me['id']) {
            q('DELETE FROM users WHERE id = ?', [(int) post('id')]); flash('Usuario eliminado.');
        }
        redirect('/admin/usuarios');
    }
    admin_view('users', ['users' => rows("SELECT * FROM users WHERE role IN ('admin','equipo') ORDER BY name")]);
}
