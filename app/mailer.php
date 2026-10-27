<?php
/**
 * Envío de correos por SMTP de Google Workspace (PHPMailer) + plantilla Nifty.
 * Si el SMTP no está configurado, los correos se guardan en storage/mail/ (modo prueba).
 */
require_once APP . '/lib/PHPMailer/Exception.php';
require_once APP . '/lib/PHPMailer/PHPMailer.php';
require_once APP . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * @param string|array $to
 * @param array $opt cc, bcc, reply_to [email, name], lead_id
 */
function send_mail($to, string $subject, string $html, array $opt = []): bool {
    $to = (array) $to;
    $ok = false; $err = null;
    $smtpPass = (string) cfg('smtp.pass', '');

    if ($smtpPass === '' || $smtpPass === 'CONTRASEÑA_DE_APLICACION') {
        // Modo prueba: guardar el correo como archivo
        $dir = ROOT . '/storage/mail';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file = $dir . '/' . date('Ymd-His') . '-' . substr(slugify($subject), 0, 50) . '-' . token(4) . '.html';
        $head = '<!-- To: ' . implode(', ', $to) . ' | Cc: ' . implode(', ', (array) ($opt['cc'] ?? [])) . ' | Subject: ' . $subject . ' -->';
        $ok = (bool) file_put_contents($file, $head . "\n" . $html);
        $err = $ok ? 'modo prueba (sin SMTP)' : 'no se pudo escribir storage/mail';
    } else {
        $m = new PHPMailer(true);
        try {
            $m->isSMTP();
            $m->Host       = cfg('smtp.host');
            $m->Port       = (int) cfg('smtp.port', 587);
            $m->SMTPAuth   = true;
            $m->Username   = cfg('smtp.user');
            $m->Password   = $smtpPass;
            $m->SMTPSecure = cfg('smtp.secure', 'tls');
            $m->CharSet    = 'UTF-8';
            $m->setFrom(cfg('smtp.from'), cfg('smtp.from_name', 'Nifty Agency'));
            foreach ($to as $t) $m->addAddress($t);
            foreach ((array) ($opt['cc'] ?? []) as $c) $m->addCC($c);
            foreach ((array) ($opt['bcc'] ?? []) as $b) $m->addBCC($b);
            if (!empty($opt['reply_to'])) $m->addReplyTo($opt['reply_to'][0], $opt['reply_to'][1] ?? '');
            $m->isHTML(true);
            $m->Subject = $subject;
            $m->Body    = $html;
            $m->AltBody = trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/tr|\/h\d)>/i', "\n", $html))));
            $ok = $m->send();
        } catch (Throwable $e) {
            $err = $m->ErrorInfo ?: $e->getMessage();
            error_log('[Nifty mail] ' . $err);
        }
    }

    try {
        insert('mail_log', [
            'lead_id' => $opt['lead_id'] ?? null, 'to_addr' => implode(', ', $to), 'subject' => mb_substr($subject, 0, 250),
            'ok' => $ok ? 1 : 0, 'error' => $err, 'created_at' => now(),
        ]);
    } catch (Throwable $e) {}
    return $ok;
}

/** Logo: usa assets/img/nifty-logo.png si existe; si no, el del WordPress actual. */
function logo_url(): string {
    return is_file(ROOT . '/assets/img/nifty-logo.png')
        ? url('assets/img/nifty-logo.png')
        : 'https://nifty-agency.com/wp-content/uploads/2025/10/Logo-Nifty.png';
}

/** Plantilla de correo con el look de la web (fondo violeta oscuro + naranja). */
function email_layout(string $heading, string $inner, string $eyebrow = 'Nifty Agency'): string {
    $logo = e(logo_url());
    $year = date('Y');
    return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#efeaf6;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#efeaf6;padding:28px 12px"><tr><td align="center">
<table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden">
<tr><td style="background:#0A0118;padding:30px 34px">
  <img src="' . $logo . '" alt="Nifty Agency" height="34" style="height:34px;display:block">
  <div style="color:#ffb36b;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;margin-top:22px">' . e($eyebrow) . '</div>
  <div style="color:#ffffff;font-size:24px;line-height:1.25;font-weight:bold;margin-top:8px">' . e($heading) . '</div>
</td></tr>
<tr><td style="padding:30px 34px;color:#2b2140;font-size:15px;line-height:1.65">' . $inner . '</td></tr>
<tr><td style="padding:20px 34px;background:#f7f4fb;color:#8a8199;font-size:12px;line-height:1.6">
  Nifty Agency · Una empresa de Andina Sur SAS · Buenos Aires, Argentina<br>
  <a href="' . e(url()) . '" style="color:#c2410c;text-decoration:none">nifty-agency.com</a> · &copy; ' . $year . '
</td></tr></table></td></tr></table></body></html>';
}

function email_button(string $href, string $label, string $bg = '#ff7a2f', string $color = '#1a0a00'): string {
    return '<a href="' . e($href) . '" style="display:inline-block;background:' . $bg . ';color:' . $color . ';padding:13px 24px;border-radius:999px;font-weight:bold;text-decoration:none;margin:6px 8px 6px 0;font-size:14px">' . e($label) . '</a>';
}

function email_table(array $rows): string {
    $h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:14px 0 20px;font-size:14px">';
    foreach ($rows as $label => $value) {
        $v = ($value === '' || $value === null) ? '—' : nl2br(e($value));
        $h .= '<tr><td style="padding:9px 12px;border-bottom:1px solid #eee7f5;color:#8a8199;width:38%;vertical-align:top">' . e($label) . '</td><td style="padding:9px 12px;border-bottom:1px solid #eee7f5;color:#2b2140">' . $v . '</td></tr>';
    }
    return $h . '</table>';
}

function email_box(string $html, string $bg = '#fff7ed', string $border = '#ff7a2f'): string {
    return '<div style="background:' . $bg . ';border-left:4px solid ' . $border . ';border-radius:10px;padding:16px 18px;margin:18px 0;font-size:14px;color:#3b2a1a">' . $html . '</div>';
}

/** Aviso al equipo de Nifty de un lead nuevo, con accesos directos. */
function notify_team_lead(int $leadId, string $subject, string $intro, array $tableRows): void {
    $lead = row('SELECT * FROM leads WHERE id = ?', [$leadId]);
    if (!$lead) return;
    $inner = '<p style="margin:0 0 6px">' . $intro . '</p>' . email_table($tableRows);
    $inner .= email_button(url('admin/leads/' . $leadId), 'Abrir en el CRM');
    if ($lead['phone']) {
        $inner .= email_button(wa_link($lead['phone'], 'Hola ' . $lead['name'] . ', te escribimos de Nifty Agency por tu ' . strtolower(lead_types()[$lead['type']] ?? 'consulta') . '. ¿Te parece si coordinamos una reunión breve para revisarlo juntos?'), 'WhatsApp', '#25D366', '#062b14');
    }
    if ($lead['email']) {
        $inner .= email_button('mailto:' . $lead['email'] . '?subject=' . rawurlencode('Nifty Agency — tu ' . (lead_types()[$lead['type']] ?? 'consulta')), 'Responder por mail', '#ede9fe', '#4c1d95');
    }
    $to = team_emails();
    if ($to) send_mail($to, $subject, email_layout('Nuevo lead para atender', $inner, lead_types()[$lead['type']] ?? 'Lead'), [
        'lead_id' => $leadId, 'reply_to' => $lead['email'] ? [$lead['email'], $lead['name']] : null,
    ]);
}
