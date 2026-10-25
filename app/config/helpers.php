<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../lib/lang.php';

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function guard_request(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if (APP_HOSTS && !in_array($host, APP_HOSTS, true)) {
        http_response_code(400);
        exit('Unknown host.');
    }
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $origin !== '' && $origin !== 'null'
        && strtolower((string) parse_url($origin, PHP_URL_HOST)) !== $host) {
        http_response_code(403);
        exit('That form was sent from another site.');
    }
}
guard_request();

function start_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => is_https(),
        'samesite' => 'Lax',
        'path'     => '/',
    ]);
    session_start();
}
start_session();

function current_phone(): ?string
{
    return $_SESSION['user_phone'] ?? null;
}

function is_logged_in(): bool
{
    return current_phone() !== null;
}

function require_login(): string
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    $u = current_user();
    if ($u === null) {
        session_unset();
        header('Location: login.php');
        exit;
    }
    if ($u['status'] === 'frozen') {
        session_unset();
        session_destroy();
        start_session();
        flash('error', __('Your account has been frozen by support. Contact us to reopen it.'));
        header('Location: login.php');
        exit;
    }
    return $u['phone'];
}

function current_user(): ?array
{
    $phone = current_phone();
    return $phone === null ? null : one('SELECT * FROM user WHERE phone = ?', [$phone]);
}

function balance_of(string $phone): float
{
    return (float) scalar('SELECT balance FROM account WHERE phone = ?', [$phone], 0);
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['user_name']  = $user['firstname'] . ' ' . $user['lastname'];
    $_SESSION['last_seen']  = time();
}

function verify_pin(string $phone, string $pin): bool
{
    $hash = (string) scalar('SELECT pin_hash FROM user WHERE phone = ?', [$phone], '');
    if ($hash !== '' && password_verify($pin, $hash)) {
        q("DELETE FROM login_attempts WHERE kind = 'pin' AND identifier = ?", [$phone]);
        return true;
    }

    q("INSERT INTO login_attempts (kind, identifier, ip, successful) VALUES ('pin',?,?,0)",
      [$phone, client_ip()]);

    $bad = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'pin' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$phone, LOGIN_LOCK_MINUTES], 0
    );
    if ($bad >= LOGIN_MAX_ATTEMPTS) {
        q("UPDATE user SET status = 'frozen' WHERE phone = ?", [$phone]);
        notify($phone, 'Account frozen',
               'Too many wrong PIN attempts. Contact support to unfreeze.', 'lock');
        audit($phone, 'account_frozen', 'too many wrong PIN attempts');
    }
    return false;
}

function pin_tries_left(string $phone): int
{
    $bad = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'pin' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$phone, LOGIN_LOCK_MINUTES], 0
    );
    return max(0, LOGIN_MAX_ATTEMPTS - $bad);
}

function require_admin(): string
{
    if (!isset($_SESSION['admin_user'])) {
        header('Location: admin_login.php');
        exit;
    }
    return $_SESSION['admin_user'];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_ok(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
        http_response_code(400);
        exit('Security check failed. Please go back and try again.');
    }
}

function once_field(string $form): string
{
    $token = bin2hex(random_bytes(16));
    $_SESSION['once'][$form][$token] = time();
    if (count($_SESSION['once'][$form]) > 10) {
        array_shift($_SESSION['once'][$form]);
    }
    return '<input type="hidden" name="once" value="' . $token . '">';
}

function once_ok(string $form): bool
{
    $token = (string) ($_POST['once'] ?? '');
    if ($token === '' || !isset($_SESSION['once'][$form][$token])) {
        return false;
    }
    unset($_SESSION['once'][$form][$token]);
    return true;
}

function require_fresh(string $form): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !once_ok($form)) {
        http_response_code(409);
        exit('That looks like a repeat of something you already sent. Check your history before trying again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [$type, $message];
}

function take_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function money($amount, bool $sign = true): string
{
    $s = number_format((float) $amount, 2);
    return $sign ? CURRENCY_SIGN . ' ' . $s : $s;
}

function short_date($ts): string
{
    return date('d M Y, g:i a', strtotime((string) $ts));
}

function time_ago($ts): string
{
    $diff = time() - strtotime((string) $ts);
    if ($diff < 60)     return __('just now');
    if ($diff < 3600)   return __('{n} min ago', ['n' => floor($diff / 60)]);
    if ($diff < 86400)  return __('{n} hr ago', ['n' => floor($diff / 3600)]);
    if ($diff < 604800) return __('{n} d ago', ['n' => floor($diff / 86400)]);
    return date('d M Y', strtotime((string) $ts));
}

function initials(?string $first, ?string $last): string
{
    return strtoupper(substr((string) $first, 0, 1) . substr((string) $last, 0, 1));
}

function mask_phone(string $phone): string
{
    return strlen($phone) === 11
        ? substr($phone, 0, 5) . '***' . substr($phone, -3)
        : $phone;
}

function operator_of(string $phone): string
{
    return OPERATOR_PREFIX[substr($phone, 0, 3)] ?? 'Unknown';
}

function name_of(string $phone): string
{
    $r = one('SELECT firstname, lastname FROM user WHERE phone = ?', [$phone]);
    return $r ? $r['firstname'] . ' ' . $r['lastname'] : $phone;
}

function notify(string $phone, string $title, string $body,
                string $icon = 'bell', ?float $amount = null): void
{
    try {
        q('INSERT INTO notifications (user_phone, title, body, icon, amount)
           VALUES (?,?,?,?,?)',
          [$phone, mb_substr($title, 0, 80), mb_substr($body, 0, 300),
           mb_substr($icon, 0, 16), $amount]);
    } catch (Throwable $ignore) {
    }
}

function unread_count(string $phone): int
{
    return (int) scalar(
        'SELECT COUNT(*) FROM notifications WHERE user_phone = ? AND read_status = 0',
        [$phone], 0
    );
}

function audit(string $actor, string $action, string $detail = ''): void
{
    try {
        q('INSERT INTO audit_log (actor, action, detail, ip) VALUES (?,?,?,?)',
          [mb_substr($actor, 0, 64), mb_substr($action, 0, 60),
           mb_substr($detail, 0, 255), client_ip()]);
    } catch (Throwable $ignore) {
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'cli';
}

function valid_phone($p): bool
{
    return (bool) preg_match('/^01[3-9]\d{8}$/', (string) $p);
}

function valid_pin($p): bool
{
    return (bool) preg_match('/^\d{6}$/', (string) $p);
}

function valid_date($d): bool
{
    $dt = DateTime::createFromFormat('!Y-m-d', (string) $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

function valid_amount($a): bool
{
    return is_numeric($a) && (float) $a > 0 && round((float) $a, 2) == (float) $a;
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function get(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
