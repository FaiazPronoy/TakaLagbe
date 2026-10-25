<?php
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    redirect('account.php');
}

$error = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $phone    = post('Phone');
    $password = post('Password');

    $failed = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'login' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$phone, LOGIN_LOCK_MINUTES], 0
    );

    if ($failed >= LOGIN_MAX_ATTEMPTS) {
        $error = __('Too many failed attempts. Please try again in {n} minutes.', ['n' => LOGIN_LOCK_MINUTES]);
    } elseif ($phone === '' || $password === '') {
        $error = __('Please enter both your number and your password.');
    } else {
        $user = one('SELECT * FROM user WHERE phone = ?', [$phone]);
        $ok   = $user && password_verify($password, $user['password_hash']);

        q("INSERT INTO login_attempts (kind, identifier, ip, successful)
           VALUES ('login',?,?,?)", [$phone, client_ip(), $ok ? 1 : 0]);

        if (!$ok) {
            $left = LOGIN_MAX_ATTEMPTS - $failed - 1;
            $error = __('Wrong number or password.')
                   . ($left > 0 && $left <= 2 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
        } elseif ($user['status'] === 'frozen') {
            $error = __('This account is frozen. Please contact support.');
        } else {
            login_user($user);
            audit($phone, 'login', 'signed in');
            flash('ok', __('Your wallet is open, {name}.', ['name' => $user['firstname']]));
            redirect('account.php');
        }
    }
}

include('views/login.php');
