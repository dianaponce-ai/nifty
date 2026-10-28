<?php
/**
 * Nifty Agency — núcleo de la aplicación.
 * Config, base de datos, sesión, seguridad y helpers comunes.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);

/** Lee una variable de entorno por cualquiera de las vías que usa el runtime (getenv, $_ENV, $_SERVER). */
function env_var(string $k): string {
    $v = getenv($k);
    if ($v === false || $v === '') $v = $_ENV[$k] ?? $_SERVER[$k] ?? '';
    return trim((string) $v);
}
define('ON_VERCEL', env_var('VERCEL') !== '' || env_var('VERCEL_ENV') !== '');

/**
 * Configuración: config.php (hosting tradicional) o variables de entorno (Vercel).
 * Variables: BASE_URL, DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, SMTP_HOST, SMTP_PORT,
 * SMTP_SECURE, SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME, ANTHROPIC_API_KEY,
 * ANTHROPIC_MODEL, APP_KEY, APP_DEBUG
 */
$cfgFile = ROOT . '/config.php';
if (is_file($cfgFile)) {
    $GLOBALS['CFG'] = require $cfgFile;
} elseif (env_var('DB_HOST') !== '' || env_var('DB_DSN') !== '') {
    $env = fn(string $k, $d = '') => ($v = env_var($k)) !== '' ? $v : $d;
    $GLOBALS['CFG'] = [
        'base_url' => $env('BASE_URL', 'https://' . $env('VERCEL_PROJECT_PRODUCTION_URL', $env('VERCEL_URL', 'localhost'))),
        'db' => [
            'dsn'  => $env('DB_DSN', 'mysql:host=' . $env('DB_HOST') . ';port=' . $env('DB_PORT', '3306') . ';dbname=' . $env('DB_NAME') . ';charset=utf8mb4'),
            'user' => $env('DB_USER'),
            'pass' => $env('DB_PASS'),
        ],
        'smtp' => [
            'host' => $env('SMTP_HOST', 'smtp.gmail.com'), 'port' => (int) $env('SMTP_PORT', '587'), 'secure' => $env('SMTP_SECURE', 'tls'),
            'user' => $env('SMTP_USER'), 'pass' => $env('SMTP_PASS'),
            'from' => $env('SMTP_FROM', $env('SMTP_USER')), 'from_name' => $env('SMTP_FROM_NAME', 'Nifty Agency'),
        ],
        'anthropic' => ['api_key' => $env('ANTHROPIC_API_KEY'), 'model' => $env('ANTHROPIC_MODEL', 'claude-sonnet-5-5')],
        'app_key' => $env('APP_KEY', 'cambiar'),
        'debug' => $env('APP_DEBUG') === '1',
    ];
} else {
    http_response_code(500);
    $seen = array_values(array_filter(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'BASE_URL', 'SMTP_USER', 'VERCEL'], fn($k) => env_var($k) !== ''));
    exit('Falta la configuración: creá config.php (ver config.sample.php) o cargá las variables de entorno. Variables detectadas: ' . ($seen ? implode(', ', $seen) : 'ninguna') . '.');
}

function cfg(string $key, $default = null) {
    $v = $GLOBALS['CFG'];
    foreach (explode('.', $key) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) return $default;
        $v = $v[$k];
    }
    return $v;
}

if (cfg('debug')) { ini_set('display_errors', '1'); error_reporting(E_ALL); }
else { ini_set('display_errors', '0'); }
date_default_timezone_set('America/Argentina/Buenos_Aires');
mb_internal_encoding('UTF-8');

require APP . '/schema.php';
require APP . '/mailer.php';
require APP . '/lib/claude.php';
require APP . '/lib/icons.php';

/* ------------------------------------------------------------------ */
/* Base de datos                                                       */
/* ------------------------------------------------------------------ */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO(cfg('db.dsn'), cfg('db.user'), cfg('db.pass'), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $st = db()->prepare($sql); $st->execute($p); return $st; }
function row(string $sql, array $p = []): ?array { $r = q($sql, $p)->fetch(); return $r ?: null; }
function rows(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function val(string $sql, array $p = []) { return q($sql, $p)->fetchColumn(); }
function insert(string $table, array $data): int {
    $cols = array_keys($data);
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
    q($sql, array_values($data));
    return (int) db()->lastInsertId();
}
function update(string $table, array $data, int $id): void {
    $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
    q("UPDATE $table SET $set WHERE id = ?", [...array_values($data), $id]);
}
function now(): string { return date('Y-m-d H:i:s'); }

/* ------------------------------------------------------------------ */
/* Ajustes editables desde el backend                                  */
/* ------------------------------------------------------------------ */
function setting_defaults(): array {
    return [
        'contact_email'      => 'diana@nifty-agency.com',
        'team_emails'        => 'diana@nifty-agency.com, pedro@nifty-agency.com, francisco@nifty-agency.com, andreina@nifty-agency.com',
        'whatsapp'           => '',
        'agenda_url'         => 'https://calendar.google.com/calendar/appointments/AcZssZ0VP4jTFeCG0SMtny7v5lt4f39TEVh0EmXFCPo=?gv=true',
        'fabrica_video_url'  => '',
        'lms_video_url'      => '',
        'usd_ars'            => '1450',
        'google_rating'      => '5.0',
        'google_reviews'     => '13',
        'google_reviews_url' => 'https://maps.google.com/?cid=1547481330784811439',
        'ga4_id'             => 'G-7JTHYDKX5B',
        'gtm_id'             => 'GTM-WZNJTBKW',
    ];
}
function setting(string $k): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try { foreach (rows('SELECT k, v FROM settings') as $r) $cache[$r['k']] = $r['v']; } catch (Throwable $e) {}
    }
    return (string) ($cache[$k] ?? setting_defaults()[$k] ?? '');
}
function setting_set(string $k, string $v): void {
    if (val('SELECT COUNT(*) FROM settings WHERE k = ?', [$k])) q('UPDATE settings SET v = ? WHERE k = ?', [$v, $k]);
    else q('INSERT INTO settings (k, v) VALUES (?, ?)', [$k, $v]);
}
function team_emails(): array {
    return array_values(array_filter(array_map('trim', explode(',', setting('team_emails'))), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
}

/* ------------------------------------------------------------------ */
/* Sesión, CSRF y autenticación                                        */
/* ------------------------------------------------------------------ */
/** Sesiones guardadas en la tabla `sessions` (necesario en Vercel: no hay disco compartido). */
class DbSessionHandler implements SessionHandlerInterface {
    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }
    public function read($id): string|false { return (string) (val('SELECT data FROM sessions WHERE id = ?', [$id]) ?: ''); }
    public function write($id, $data): bool {
        q('DELETE FROM sessions WHERE id = ?', [$id]);
        q('INSERT INTO sessions (id, data, updated_at) VALUES (?, ?, ?)', [$id, $data, time()]);
        return true;
    }
    public function destroy($id): bool { q('DELETE FROM sessions WHERE id = ?', [$id]); return true; }
    public function gc($max): int|false { return q('DELETE FROM sessions WHERE updated_at < ?', [time() - $max])->rowCount(); }
}

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    if (ON_VERCEL || env_var('SESSION_DB') === '1') {
        try { session_set_save_handler(new DbSessionHandler(), true); } catch (Throwable $e) {}
        ini_set('session.gc_maxlifetime', '86400');
    }
    session_name('nifty_sid');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        'secure' => (($_SERVER['HTTPS'] ?? '') === 'on') || str_starts_with(cfg('base_url', ''), 'https'),
    ]);
    session_start();
}
function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    start_session();
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('La sesión expiró. Volvé atrás, recargá la página e intentá de nuevo.');
    }
}
function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    static $u = false;
    if ($u === false) $u = row('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]);
    return $u ?: null;
}
function require_role(array $roles = ['admin', 'equipo']): array {
    $u = current_user();
    if (!$u) redirect('/admin/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/admin'));
    if (!in_array($u['role'], $roles, true)) { http_response_code(403); exit('No tenés permisos para ver esta sección.'); }
    return $u;
}

/* ------------------------------------------------------------------ */
/* Helpers                                                             */
/* ------------------------------------------------------------------ */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(cfg('base_url', ''), '/') . '/' . ltrim($path, '/'); }
function redirect(string $to, int $code = 302): never { header('Location: ' . $to, true, $code); exit; }
function post(string $k, string $default = ''): string { return trim((string) ($_POST[$k] ?? $default)); }
function flash(?string $msg = null, string $type = 'ok') {
    start_session();
    if ($msg !== null) { $_SESSION['flash'] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function slugify(string $s): string {
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    $s = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $s));
    return trim($s, '-') ?: 'item-' . time();
}
function jdec($s): array { $d = json_decode((string) $s, true); return is_array($d) ? $d : []; }
function jenc($d): string { return json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
function token(int $len = 20): string { return substr(bin2hex(random_bytes($len)), 0, $len); }
function digits(string $s): string { return preg_replace('/\D+/', '', $s); }
function client_ip(): string { return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64); }
function money(float $n, string $cur = 'USD'): string {
    return ($cur === 'ARS' ? '$ ' : 'USD ') . number_format($n, 0, ',', '.');
}
function wa_link(string $phone, string $text = ''): string {
    return 'https://wa.me/' . digits($phone) . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/** Render de una vista dentro del layout público o del admin. */
function view(string $tpl, array $data = [], string $layout = 'public'): void {
    extract($data, EXTR_SKIP);
    ob_start();
    require ROOT . '/views/' . $tpl . '.php';
    $content = ob_get_clean();
    require ROOT . '/views/layouts/' . $layout . '.php';
}
function partial(string $tpl, array $data = []): void { extract($data, EXTR_SKIP); require ROOT . '/views/' . $tpl . '.php'; }

function not_found(): never {
    http_response_code(404);
    view('404', ['meta' => ['title' => 'Página no encontrada — Nifty Agency', 'robots' => 'noindex']]);
    exit;
}

/** Anti-spam: honeypot + tiempo mínimo + límite por IP y tipo. */
function spam_guard(string $type, int $maxPerHour = 6): ?string {
    if (post('website_hp') !== '') return 'spam';
    $ts = (int) post('_ts');
    if ($ts && (time() - $ts) < 3) return 'spam';
    $n = (int) val('SELECT COUNT(*) FROM leads WHERE ip = ? AND type = ? AND created_at > ?', [client_ip(), $type, date('Y-m-d H:i:s', time() - 3600)]);
    return $n >= $maxPerHour ? 'limit' : null;
}
function antispam_fields(): string {
    return '<div class="hp" aria-hidden="true"><label>Web<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label></div>'
        . '<input type="hidden" name="_ts" value="' . time() . '">';
}

/** Registra un evento en la línea de tiempo del lead. */
function lead_event(int $leadId, string $kind, string $body, ?int $userId = null): void {
    insert('lead_events', ['lead_id' => $leadId, 'user_id' => $userId, 'kind' => $kind, 'body' => $body, 'created_at' => now()]);
}

function lead_types(): array {
    return [
        'diagnostico_ia' => 'Diagnóstico IA-friendly',
        'plan_pauta'     => 'Plan de pauta',
        'contacto'       => 'Contacto web',
        'fabrica'        => 'La Fábrica',
    ];
}
function lead_statuses(): array {
    return [
        'nuevo'      => 'Nuevo',
        'contactado' => 'Contactado',
        'reunion'    => 'Reunión agendada',
        'propuesta'  => 'Propuesta enviada',
        'ganado'     => 'Ganado ✓',
        'perdido'    => 'Perdido',
    ];
}
