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

    /* how many bad tries has this number had recently? */
    $failed = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'login' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$phone, LOGIN_LOCK_MINUTES], 0
    );

    if ($failed >= LOGIN_MAX_ATTEMPTS) {
        $error = 'Too many failed attempts. Please try again in '
               . LOGIN_LOCK_MINUTES . ' minutes.';
    } elseif ($phone === '' || $password === '') {
        $error = 'Please enter both your number and your password.';
    } else {
        $user = one('SELECT * FROM user WHERE phone = ?', [$phone]);
        $ok   = $user && password_verify($password, $user['password_hash']);

        q("INSERT INTO login_attempts (kind, identifier, ip, successful)
           VALUES ('login',?,?,?)", [$phone, client_ip(), $ok ? 1 : 0]);

        if (!$ok) {
            $left = LOGIN_MAX_ATTEMPTS - $failed - 1;
            $error = 'Wrong number or password.'
                   . ($left > 0 && $left <= 2 ? " $left attempt(s) left." : '');
        } elseif ($user['status'] === 'frozen') {
            $error = 'This account is frozen. Please contact support.';
        } else {
            login_user($user);
            audit($phone, 'login', 'signed in');
            flash('ok', 'Welcome back, ' . $user['firstname'] . '.');
            redirect('account.php');
        }
    }
}

include('Front_end/front_login.php');
