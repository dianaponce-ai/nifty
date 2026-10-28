<?php
/* =====================================================================
 * Propuestas comerciales + Clientes
 * - Editor por bloques en /admin/propuestas
 * - Página pública /p/{token} con aprobación, forma de pago y firma
 * - Envío al cliente con copia al equipo
 * - Al aprobarse: se crea la ficha del cliente automáticamente
 * ===================================================================== */

function proposal_statuses(): array {
    return [
        'borrador'  => 'Borrador',
        'enviada'   => 'Enviada',
        'vista'     => 'Vista por el cliente',
        'cambios'   => 'Pidió cambios',
        'aprobada'  => 'Aprobada ✓',
        'rechazada' => 'Rechazada',
        'archivada' => 'Archivada',
    ];
}

function block_types(): array {
    return [
        'texto'     => 'Texto (título + párrafos + destacado)',
        'tarjetas'  => 'Tarjetas (título + grilla de ítems)',
        'lista'     => 'Lista con checks',
        'pasos'     => 'Pasos / cronograma',
        'destacado' => 'Bloque destacado oscuro',
    ];
}

function proposal_event(int $pid, string $kind, string $body, ?int $uid = null): void {
    insert('proposal_events', ['proposal_id' => $pid, 'user_id' => $uid, 'kind' => $kind, 'body' => $body, 'created_at' => now()]);
}

function proposal_url(array $p): string { return url('p/' . $p['token']); }

/** Markdown mínimo: **negrita**, *itálica*, listas con "- ", párrafos por línea en blanco. */
function md(?string $s): string {
    $s = trim((string) $s);
    if ($s === '') return '';
    $out = '';
    foreach (preg_split("/\n\s*\n/", $s) as $para) {
        $lines = preg_split("/\r?\n/", trim($para));
        $isList = count(array_filter($lines, fn($l) => preg_match('/^\s*[-•✓]\s+/u', $l))) === count($lines);
        $fmt = function ($t) {
            $t = e($t);
            $t = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $t);
            $t = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/u', '<em>$1</em>', $t);
            return $t;
        };
        if ($isList) {
            $out .= '<ul>' . implode('', array_map(fn($l) => '<li>' . $fmt(preg_replace('/^\s*[-•✓]\s+/u', '', $l)) . '</li>', $lines)) . '</ul>';
        } else {
            $out .= '<p>' . implode('<br>', array_map($fmt, $lines)) . '</p>';
        }
    }
    return $out;
}

/** Totales: [único, mensual] (ítems opcionales no suman). */
function proposal_totals(array $items): array {
    $once = 0.0; $monthly = 0.0;
    foreach ($items as $it) {
        if (!empty($it['optional'])) continue;
        if (($it['kind'] ?? 'unico') === 'mensual') $monthly += (float) ($it['amount'] ?? 0);
        else $once += (float) ($it['amount'] ?? 0);
    }
    return [$once, $monthly];
}

function fmt_money(float $n, string $cur): string {
    $dec = abs($n - round($n)) > 0.001 ? 2 : 0;
    return ($cur === 'USD' ? 'USD ' : '$ ') . number_format($n, $dec, ',', '.');
}

function next_proposal_number(): string {
    $y = date('Y');
    $n = (int) val("SELECT COUNT(*) FROM proposals WHERE number LIKE ?", ["NFT-$y-%"]) + 1;
    return sprintf('NFT-%s-%03d', $y, $n);
}

/* --------------------------------------------------------------------- */
/* Admin: listado                                                         */
/* --------------------------------------------------------------------- */
function admin_proposals(): void {
    require_role();
    $st = (string) ($_GET['estado'] ?? '');
    $q = trim((string) ($_GET['q'] ?? ''));
    $w = ['1=1']; $p = [];
    if (isset(proposal_statuses()[$st])) { $w[] = 'status = ?'; $p[] = $st; }
    else { $w[] = "status <> 'archivada'"; }
    if ($q !== '') { $w[] = '(LOWER(client_name) LIKE ? OR LOWER(title) LIKE ? OR LOWER(number) LIKE ?)'; array_push($p, ...array_fill(0, 3, '%' . mb_strtolower($q) . '%')); }
    $list = rows('SELECT * FROM proposals WHERE ' . implode(' AND ', $w) . ' ORDER BY updated_at DESC', $p);
    $counts = array_column(rows('SELECT status, COUNT(*) n FROM proposals GROUP BY status'), 'n', 'status');
    admin_view('proposals', ['list' => $list, 'counts' => $counts]);
}

/* --------------------------------------------------------------------- */
/* Admin: crear / editar                                                  */
/* --------------------------------------------------------------------- */
function proposal_from_post(): array {
    // Bloques
    $blocks = [];
    foreach ((array) ($_POST['blocks'] ?? []) as $b) {
        $type = isset(block_types()[$b['type'] ?? '']) ? $b['type'] : 'texto';
        $blk = ['type' => $type, 'kicker' => trim((string) ($b['kicker'] ?? '')), 'title' => trim((string) ($b['title'] ?? '')),
            'text' => trim((string) ($b['text'] ?? '')), 'callout' => trim((string) ($b['callout'] ?? '')),
            'items' => lines_to_pairs((string) ($b['items'] ?? ''))];
        if ($blk['title'] === '' && $blk['text'] === '' && !$blk['items']) continue;
        $blocks[] = $blk;
    }
    // Ítems de inversión: Concepto | detalle | monto | unico/mensual | opcional
    $items = [];
    foreach (preg_split('/\r?\n/', post('items')) as $l) {
        $l = trim($l); if ($l === '') continue;
        $c = array_map('trim', explode('|', $l));
        $amount = (float) str_replace(['$', 'USD', ' ', '.'], ['', '', '', ''], str_replace(',', '.', preg_replace('/\.(?=\d{3}(\D|$))/', '', $c[2] ?? '0')));
        $kind = str_starts_with(mb_strtolower($c[3] ?? ''), 'men') ? 'mensual' : 'unico';
        $items[] = ['concept' => $c[0], 'detail' => $c[1] ?? '', 'amount' => $amount, 'kind' => $kind, 'optional' => isset($c[4]) && stripos($c[4], 'opc') !== false];
    }
    // Opciones de pago
    $opts = [];
    $disc = (float) str_replace(',', '.', post('pay_discount'));
    [$once] = proposal_totals($items);
    if (post('pay_cash') === '1') {
        $opts[] = ['label' => 'Pago de contado' . ($disc > 0 ? ' (' . rtrim(rtrim(number_format($disc, 1, ',', ''), '0'), ',') . '% off)' : ''),
            'detail' => $disc > 0 && $once > 0 ? 'Total con descuento: ' . fmt_money($once * (1 - $disc / 100), post('currency', 'ARS')) . ' en un solo pago.' : 'Pago único por adelantado para iniciar el proyecto.'];
    }
    if (post('pay_7030') === '1') {
        $opts[] = ['label' => '70% al inicio · 30% al finalizar',
            'detail' => $once > 0 ? fmt_money($once * .7, post('currency', 'ARS')) . ' al inicio y ' . fmt_money($once * .3, post('currency', 'ARS')) . ' al finalizar.' : 'Anticipo del 70% para iniciar y saldo del 30% contra entrega.'];
    }
    foreach (lines_to_pairs(post('pay_custom')) as $o) $opts[] = ['label' => $o['t'], 'detail' => $o['d']];

    return [
        'client_name' => post('client_name'), 'client_tagline' => post('client_tagline'),
        'contact_name' => post('contact_name'), 'contact_email' => strtolower(post('contact_email')), 'contact_phone' => post('contact_phone'),
        'website' => post('website'), 'eyebrow' => post('eyebrow'), 'title' => post('title'), 'subtitle' => post('subtitle'),
        'blocks' => jenc($blocks), 'currency' => post('currency') === 'USD' ? 'USD' : 'ARS', 'items' => jenc($items),
        'pay_options' => jenc(['cash' => post('pay_cash') === '1', 'discount' => $disc, 'p7030' => post('pay_7030') === '1', 'custom' => post('pay_custom'), 'list' => $opts]),
        'terms' => post('terms'), 'valid_until' => post('valid_until') ?: null,
        'updated_at' => now(),
    ];
}

function admin_proposal_edit(?string $id = null): void {
    $me = require_role();
    $pr = $id ? row('SELECT * FROM proposals WHERE id = ?', [(int) $id]) : null;
    if ($id && !$pr) not_found();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $data = proposal_from_post();
        if ($data['client_name'] === '') { flash('El nombre del cliente es obligatorio.', 'err'); redirect($pr ? '/admin/propuestas/' . $pr['id'] : '/admin/propuestas/nueva'); }
        if ($pr) {
            update('proposals', $data, (int) $pr['id']);
            proposal_event((int) $pr['id'], 'edicion', 'Propuesta editada', (int) $me['id']);
            $pid = (int) $pr['id'];
        } else {
            $data += ['token' => token(32), 'number' => next_proposal_number(), 'status' => 'borrador', 'created_by' => (int) $me['id'],
                'lead_id' => post('lead_id') !== '' ? (int) post('lead_id') : null, 'created_at' => now()];
            $pid = insert('proposals', $data);
            proposal_event($pid, 'creada', 'Propuesta creada', (int) $me['id']);
            if ($data['lead_id']) { q("UPDATE leads SET status = 'propuesta', updated_at = ? WHERE id = ?", [now(), $data['lead_id']]); lead_event($data['lead_id'], 'estado', 'Se creó la propuesta ' . $data['number'], (int) $me['id']); }
        }
        flash('Propuesta guardada.');
        redirect(post('_after') === 'preview' ? '/p/' . val('SELECT token FROM proposals WHERE id = ?', [$pid]) : '/admin/propuestas/' . $pid);
    }

    // Nueva desde un lead
    $prefill = null;
    if (!$pr && isset($_GET['lead'])) $prefill = row('SELECT * FROM leads WHERE id = ?', [(int) $_GET['lead']]);

    admin_view('proposal_edit', [
        'pr' => $pr, 'prefill' => $prefill,
        'events' => $pr ? rows('SELECT e.*, u.name AS user_name FROM proposal_events e LEFT JOIN users u ON u.id = e.user_id WHERE proposal_id = ? ORDER BY e.id DESC', [$pr['id']]) : [],
        'mails' => $pr ? rows("SELECT * FROM mail_log WHERE subject LIKE ? ORDER BY id DESC LIMIT 10", ['%' . $pr['number'] . '%']) : [],
    ]);
}

function admin_proposal_action(string $id): void {
    $me = require_role();
    csrf_check();
    $pr = row('SELECT * FROM proposals WHERE id = ?', [(int) $id]);
    if (!$pr) not_found();
    $act = post('act');

    if ($act === 'send') {
        if (!filter_var($pr['contact_email'], FILTER_VALIDATE_EMAIL)) { flash('Cargá un email válido del cliente antes de enviar.', 'err'); redirect('/admin/propuestas/' . $pr['id']); }
        $msg = post('message');
        $greet = $pr['contact_name'] ? 'Hola ' . explode(' ', $pr['contact_name'])[0] . ',' : 'Hola,';
        $inner = '<p>' . e($greet) . '</p>'
            . ($msg !== '' ? md($msg) : '<p>Te compartimos la propuesta que preparamos para <b>' . e($pr['client_name']) . '</b>. Podés revisarla con calma, elegir la forma de pago y aprobarla con tu firma directamente desde el enlace.</p>')
            . email_box('<b>' . e($pr['title'] ?: 'Propuesta comercial') . '</b><br>' . e($pr['number']) . ($pr['valid_until'] ? ' · válida hasta el ' . date('d/m/Y', strtotime($pr['valid_until'])) : ''), '#f5f3ff', '#7c3aed')
            . email_button(proposal_url($pr), 'Ver la propuesta')
            . '<p style="margin-top:20px">Cualquier duda, respondé este correo y lo vemos juntos.</p><p>' . e($me['name']) . '<br><span style="color:#8a8199">Nifty Agency</span></p>';
        $cc = array_values(array_diff(team_emails(), [$pr['contact_email']]));
        $ok = send_mail($pr['contact_email'], 'Propuesta ' . $pr['number'] . ' — ' . $pr['client_name'] . ' | Nifty Agency',
            email_layout($pr['title'] ?: 'Tu propuesta está lista', $inner, 'Propuesta comercial'),
            ['cc' => $cc, 'reply_to' => [$me['email'], $me['name']]]);
        if ($ok) {
            q("UPDATE proposals SET status = CASE WHEN status IN ('borrador','cambios') THEN 'enviada' ELSE status END, sent_at = ?, updated_at = ? WHERE id = ?", [now(), now(), $pr['id']]);
            proposal_event((int) $pr['id'], 'enviada', 'Enviada a ' . $pr['contact_email'] . ($cc ? ' (cc: ' . implode(', ', $cc) . ')' : ''), (int) $me['id']);
        }
        flash($ok ? 'Propuesta enviada al cliente con copia al equipo.' : 'No se pudo enviar el correo. Revisá el SMTP.', $ok ? 'ok' : 'err');
    } elseif ($act === 'status' && isset(proposal_statuses()[post('status')])) {
        q('UPDATE proposals SET status = ?, updated_at = ? WHERE id = ?', [post('status'), now(), $pr['id']]);
        proposal_event((int) $pr['id'], 'estado', 'Estado cambiado a ' . proposal_statuses()[post('status')], (int) $me['id']);
        if (post('status') === 'aprobada' && !$pr['client_id']) {
            $cid = client_from_proposal(array_merge($pr, ['status' => 'aprobada']));
            flash('Propuesta marcada como aprobada. Se creó la ficha del cliente.');
            redirect('/admin/clientes/' . $cid);
        }
        flash('Estado actualizado.');
    } elseif ($act === 'duplicate') {
        $copy = $pr;
        unset($copy['id']);
        $copy = array_merge($copy, ['token' => token(32), 'number' => next_proposal_number(), 'status' => 'borrador', 'client_id' => null,
            'sent_at' => null, 'viewed_at' => null, 'views' => 0, 'decided_at' => null, 'approver_name' => '', 'approver_doc' => '', 'pay_choice' => '',
            'signature' => null, 'decision_note' => null, 'decision_ip' => '', 'legacy_url' => '', 'created_by' => (int) $me['id'],
            'title' => $pr['title'], 'created_at' => now(), 'updated_at' => now()]);
        $nid = insert('proposals', $copy);
        proposal_event($nid, 'creada', 'Duplicada desde ' . $pr['number'], (int) $me['id']);
        flash('Propuesta duplicada. Editá los datos del nuevo cliente.');
        redirect('/admin/propuestas/' . $nid);
    } elseif ($act === 'delete' && $me['role'] === 'admin') {
        q('DELETE FROM proposal_events WHERE proposal_id = ?', [$pr['id']]);
        q('DELETE FROM proposals WHERE id = ?', [$pr['id']]);
        flash('Propuesta eliminada.');
        redirect('/admin/propuestas');
    }
    redirect('/admin/propuestas/' . $pr['id']);
}

/** Importa propuestas desde un JSON (las propuestas históricas de WordPress). */
function admin_proposals_import(): void {
    $me = require_role();
    csrf_check();
    $f = $_FILES['file'] ?? null;
    $raw = ($f && $f['error'] === UPLOAD_ERR_OK) ? file_get_contents($f['tmp_name']) : post('json');
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) { flash('El archivo no es un JSON válido.', 'err'); redirect('/admin/propuestas'); }
    if (isset($data['client_name'])) $data = [$data];
    $n = 0;
    foreach ($data as $p) {
        if (empty($p['client_name'])) continue;
        if (!empty($p['legacy_url']) && val('SELECT id FROM proposals WHERE legacy_url = ?', [$p['legacy_url']])) continue;
        $created = !empty($p['date']) ? date('Y-m-d H:i:s', strtotime($p['date'])) : now();
        $items = $p['items'] ?? [];
        $pid = insert('proposals', [
            'token' => token(32), 'number' => $p['number'] ?? next_proposal_number(), 'status' => $p['status'] ?? 'enviada',
            'client_name' => $p['client_name'], 'client_tagline' => $p['client_tagline'] ?? '', 'contact_name' => $p['contact_name'] ?? '',
            'contact_email' => $p['contact_email'] ?? '', 'contact_phone' => $p['contact_phone'] ?? '', 'website' => $p['website'] ?? '',
            'eyebrow' => $p['eyebrow'] ?? 'Propuesta comercial', 'title' => $p['title'] ?? '', 'subtitle' => $p['subtitle'] ?? '',
            'blocks' => jenc($p['blocks'] ?? []), 'currency' => $p['currency'] ?? 'ARS', 'items' => jenc($items),
            'pay_options' => jenc(['cash' => false, 'discount' => 0, 'p7030' => false, 'custom' => implode("\n", array_map(fn($o) => $o['label'] . ' | ' . ($o['detail'] ?? ''), $p['pay_options'] ?? [])), 'list' => $p['pay_options'] ?? []]),
            'terms' => $p['terms'] ?? '', 'legacy_url' => $p['legacy_url'] ?? '', 'created_by' => (int) $me['id'],
            'sent_at' => $created, 'created_at' => $created, 'updated_at' => $created,
        ]);
        proposal_event($pid, 'importada', 'Importada desde WordPress' . (!empty($p['legacy_url']) ? ': ' . $p['legacy_url'] : ''), (int) $me['id']);
        $n++;
    }
    flash("Se importaron $n propuestas.");
    redirect('/admin/propuestas');
}

/* --------------------------------------------------------------------- */
/* Público: ver, aprobar, pedir cambios                                   */
/* --------------------------------------------------------------------- */
function page_proposal(string $tok): void {
    $pr = row('SELECT * FROM proposals WHERE token = ?', [$tok]);
    if (!$pr || $pr['status'] === 'archivada' && !current_user()) not_found();
    $isTeam = (bool) current_user();
    if (!$isTeam && !PREVIEW_MODE) {
        $first = !$pr['viewed_at'];
        q('UPDATE proposals SET views = views + 1, viewed_at = COALESCE(viewed_at, ?), status = CASE WHEN status = ? THEN ? ELSE status END WHERE id = ?', [now(), 'enviada', 'vista', $pr['id']]);
        if ($first) {
            proposal_event((int) $pr['id'], 'vista', 'El cliente abrió la propuesta por primera vez');
            $to = team_emails();
            if ($to) send_mail($to, '👀 ' . $pr['client_name'] . ' abrió la propuesta ' . $pr['number'],
                email_layout('Abrieron la propuesta', '<p><b>' . e($pr['client_name']) . '</b> acaba de abrir la propuesta <b>' . e($pr['number']) . '</b>. Buen momento para un seguimiento.</p>' . email_button(url('admin/propuestas/' . $pr['id']), 'Ver en el CRM'), 'Propuesta'));
        }
    }
    view('proposal', ['pr' => $pr, 'isTeam' => $isTeam, 'meta' => [
        'title' => 'Propuesta para ' . $pr['client_name'] . ' — Nifty Agency', 'robots' => 'noindex, nofollow', 'css' => ['proposal'],
    ]]);
}

function proposal_decide(string $tok): void {
    csrf_check();
    $pr = row('SELECT * FROM proposals WHERE token = ?', [$tok]);
    if (!$pr) not_found();
    $back = '/p/' . $tok . '#aprobar';
    if (in_array($pr['status'], ['aprobada', 'rechazada', 'archivada'], true)) { flash('Esta propuesta ya fue respondida.', 'err'); redirect($back); }

    if (post('decision') === 'cambios') {
        $note = post('note');
        if ($note === '') { flash('Contanos qué te gustaría cambiar.', 'err'); redirect($back); }
        q('UPDATE proposals SET status = ?, decision_note = ?, updated_at = ? WHERE id = ?', ['cambios', $note, now(), $pr['id']]);
        proposal_event((int) $pr['id'], 'cambios', 'El cliente pidió cambios: ' . $note);
        if ($to = team_emails()) send_mail($to, '✏️ ' . $pr['client_name'] . ' pidió cambios en la propuesta ' . $pr['number'],
            email_layout('Pedido de cambios', '<p><b>' . e($pr['client_name']) . '</b> dejó este comentario sobre la propuesta <b>' . e($pr['number']) . '</b>:</p>' . email_box(nl2br(e($note))) . email_button(url('admin/propuestas/' . $pr['id']), 'Abrir en el CRM'), 'Propuesta'),
            ['reply_to' => $pr['contact_email'] ? [$pr['contact_email'], $pr['contact_name']] : null]);
        flash('¡Gracias! Recibimos tus comentarios y te respondemos a la brevedad.');
        redirect($back);
    }

    // Aprobación
    $name = post('approver_name'); $sig = (string) ($_POST['signature'] ?? '');
    $opts = jdec($pr['pay_options'])['list'] ?? [];
    $choice = post('pay_choice');
    if ($name === '' || post('accept') !== '1') { flash('Completá tu nombre y aceptá los términos para aprobar.', 'err'); redirect($back); }
    if (!preg_match('~^data:image/png;base64,[A-Za-z0-9+/=]+$~', $sig) || strlen($sig) > 400000 || strlen($sig) < 1500) { flash('Firmá en el recuadro para aprobar la propuesta.', 'err'); redirect($back); }
    if ($opts && !in_array($choice, array_column($opts, 'label'), true)) { flash('Elegí una forma de pago.', 'err'); redirect($back); }

    q('UPDATE proposals SET status = ?, decided_at = ?, approver_name = ?, approver_doc = ?, pay_choice = ?, signature = ?, decision_note = ?, decision_ip = ?, updated_at = ? WHERE id = ?',
        ['aprobada', now(), $name, post('approver_doc'), $choice, $sig, post('note'), client_ip(), now(), $pr['id']]);
    $pr = row('SELECT * FROM proposals WHERE id = ?', [$pr['id']]);
    proposal_event((int) $pr['id'], 'aprobada', 'Aprobada por ' . $name . ($choice ? ' · forma de pago: ' . $choice : ''));
    $cid = client_from_proposal($pr);

    [$once, $monthly] = proposal_totals(jdec($pr['items']));
    $summary = email_table(array_filter([
        'Propuesta' => $pr['number'] . ' — ' . ($pr['title'] ?: 'Propuesta comercial'),
        'Aprobada por' => $name . ($pr['approver_doc'] ? ' (' . $pr['approver_doc'] . ')' : ''),
        'Fecha' => date('d/m/Y H:i'),
        'Forma de pago' => $choice,
        'Inversión única' => $once > 0 ? fmt_money($once, $pr['currency']) : null,
        'Fee mensual' => $monthly > 0 ? fmt_money($monthly, $pr['currency']) . ' / mes' : null,
        'Comentarios' => post('note'),
    ], fn($v) => $v !== null && $v !== ''));
    if (filter_var($pr['contact_email'], FILTER_VALIDATE_EMAIL)) {
        send_mail($pr['contact_email'], '✓ Propuesta aprobada — ' . $pr['number'] . ' | Nifty Agency',
            email_layout('¡Bienvenidos a Nifty!', '<p>¡Gracias, ' . e(explode(' ', $name)[0]) . '! Registramos la aprobación de la propuesta. Este es el resumen:</p>' . $summary
                . '<p>En las próximas horas te contactamos para coordinar el inicio y los datos de facturación.</p>' . email_button(proposal_url($pr), 'Ver la propuesta aprobada'), 'Propuesta aprobada'),
            ['cc' => team_emails()]);
    } elseif ($to = team_emails()) {
        send_mail($to, '✅ Propuesta aprobada — ' . $pr['client_name'] . ' (' . $pr['number'] . ')',
            email_layout('¡Propuesta aprobada!', $summary . email_button(url('admin/clientes/' . $cid), 'Abrir ficha del cliente'), 'Propuesta aprobada'));
    }
    if ($to = team_emails()) {
        send_mail($to, '✅ ' . $pr['client_name'] . ' aprobó la propuesta ' . $pr['number'],
            email_layout('¡Nuevo cliente!', '<p><b>' . e($pr['client_name']) . '</b> aprobó la propuesta. Ya se creó su ficha de cliente para cargar el ecosistema y el fee.</p>' . $summary
                . email_button(url('admin/clientes/' . $cid), 'Abrir ficha del cliente') . email_button(url('admin/propuestas/' . $pr['id']), 'Ver propuesta', '#ede9fe', '#4c1d95'), 'Propuesta aprobada'));
    }
    redirect('/p/' . $tok . '?aprobada=1#aprobar');
}

/** Crea (o vincula) la ficha del cliente a partir de una propuesta aprobada. */
function client_from_proposal(array $pr): int {
    if (!empty($pr['client_id'])) return (int) $pr['client_id'];
    $existing = null;
    if ($pr['contact_email']) $existing = val('SELECT id FROM clients WHERE LOWER(contact_email) = ?', [strtolower($pr['contact_email'])]);
    if (!$existing) $existing = val('SELECT id FROM clients WHERE LOWER(name) = ?', [mb_strtolower($pr['client_name'])]);
    [, $monthly] = proposal_totals(jdec($pr['items']));
    $services = implode("\n", array_map(fn($i) => $i['concept'], array_filter(jdec($pr['items']), fn($i) => empty($i['optional']))));
    if ($existing) {
        $cid = (int) $existing;
        if ($monthly > 0) q('UPDATE clients SET fee_amount = ?, fee_currency = ?, updated_at = ? WHERE id = ? AND fee_amount IS NULL', [$monthly, $pr['currency'], now(), $cid]);
    } else {
        $cid = insert('clients', [
            'name' => $pr['client_name'], 'website' => $pr['website'], 'contact_name' => $pr['contact_name'] ?: $pr['approver_name'],
            'contact_email' => $pr['contact_email'], 'contact_phone' => $pr['contact_phone'], 'status' => 'activo',
            'fee_amount' => $monthly > 0 ? $monthly : null, 'fee_currency' => $pr['currency'], 'services' => $services,
            'ecosystem' => jenc(default_ecosystem()), 'proposal_id' => $pr['id'], 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    q('UPDATE proposals SET client_id = ? WHERE id = ?', [$cid, $pr['id']]);
    if (!empty($pr['lead_id'])) { q("UPDATE leads SET status = 'ganado', updated_at = ? WHERE id = ?", [now(), $pr['lead_id']]); lead_event((int) $pr['lead_id'], 'estado', 'Ganado: aprobó la propuesta ' . $pr['number']); }
    return $cid;
}

/* --------------------------------------------------------------------- */
/* Clientes                                                               */
/* --------------------------------------------------------------------- */
function ecosystem_fields(): array {
    return [
        'Medición' => ['ga4' => 'Google Analytics 4 (ID de medición)', 'gtm' => 'Google Tag Manager (contenedor)', 'gsc' => 'Search Console (propiedad)', 'pixel' => 'Píxel de Meta / API de Conversiones', 'reporte' => 'Link al reporte (Master Metrics / Looker)'],
        'Pauta' => ['gads' => 'Google Ads (ID de cuenta)', 'meta_bm' => 'Meta Business Manager (ID)', 'meta_ads' => 'Cuenta publicitaria de Meta', 'linkedin' => 'LinkedIn Campaign Manager', 'tiktok' => 'TikTok Ads'],
        'Sitio y canales' => ['dominio' => 'Dominio y dónde está registrado', 'hosting' => 'Hosting / plataforma', 'gbp' => 'Perfil de Google Business', 'whatsapp' => 'WhatsApp / API', 'crm' => 'CRM', 'redes' => 'Redes sociales'],
    ];
}
function default_ecosystem(): array { return ['fields' => [], 'people' => '', 'access' => '']; }

function client_statuses(): array { return ['activo' => 'Activo', 'pausado' => 'Pausado', 'baja' => 'Baja']; }

function admin_clients(): void {
    require_role();
    $q = trim((string) ($_GET['q'] ?? ''));
    $list = $q !== ''
        ? rows('SELECT * FROM clients WHERE LOWER(name) LIKE ? OR LOWER(contact_email) LIKE ? ORDER BY name', ['%' . mb_strtolower($q) . '%', '%' . mb_strtolower($q) . '%'])
        : rows('SELECT * FROM clients ORDER BY status, name');
    $fees = [];
    foreach ($list as $c) if ($c['status'] === 'activo' && $c['fee_amount']) $fees[$c['fee_currency']] = ($fees[$c['fee_currency']] ?? 0) + (float) $c['fee_amount'];
    admin_view('clients', ['list' => $list, 'fees' => $fees]);
}

function admin_client_edit(?string $id = null): void {
    $me = require_role();
    $c = $id ? row('SELECT * FROM clients WHERE id = ?', [(int) $id]) : null;
    if ($id && !$c) not_found();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (post('name') === '') { flash('El nombre es obligatorio.', 'err'); redirect($c ? '/admin/clientes/' . $c['id'] : '/admin/clientes/nuevo'); }
        $fields = [];
        foreach ((array) ($_POST['eco'] ?? []) as $k => $v) if (trim((string) $v) !== '') $fields[$k] = trim((string) $v);
        $data = [
            'name' => post('name'), 'legal_name' => post('legal_name'), 'tax_id' => post('tax_id'), 'industry' => post('industry'), 'website' => post('website'),
            'contact_name' => post('contact_name'), 'contact_email' => strtolower(post('contact_email')), 'contact_phone' => post('contact_phone'),
            'status' => isset(client_statuses()[post('status')]) ? post('status') : 'activo',
            'fee_amount' => post('fee_amount') !== '' ? (float) str_replace(',', '.', str_replace('.', '', post('fee_amount'))) : null,
            'fee_currency' => post('fee_currency') === 'USD' ? 'USD' : 'ARS', 'fee_day' => post('fee_day') !== '' ? (int) post('fee_day') : null,
            'services' => post('services'), 'notes' => post('notes'),
            'ecosystem' => jenc(['fields' => $fields, 'people' => post('eco_people'), 'access' => post('eco_access')]),
            'updated_at' => now(),
        ];
        if ($c) { update('clients', $data, (int) $c['id']); $cid = (int) $c['id']; }
        else { $data['created_at'] = now(); $cid = insert('clients', $data); }
        flash('Ficha guardada.');
        redirect('/admin/clientes/' . $cid);
    }
    admin_view('client_edit', ['c' => $c, 'eco' => $c ? jdec($c['ecosystem']) + default_ecosystem() : default_ecosystem(),
        'proposals' => $c ? rows('SELECT id, number, title, status, created_at FROM proposals WHERE client_id = ? ORDER BY id DESC', [$c['id']]) : []]);
}
