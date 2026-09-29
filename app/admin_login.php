<?php
/* Admin sign in. Same bcrypt and rate limiting as the customer side. */
require_once __DIR__ . '/config/helpers.php';

if (isset($_SESSION['admin_user'])) {
    redirect('admin_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $username = post('username');
    $password = post('password');

    $failed = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'admin' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$username, LOGIN_LOCK_MINUTES], 0);

    if ($failed >= LOGIN_MAX_ATTEMPTS) {
        $error = 'Too many failed attempts. Try again in ' . LOGIN_LOCK_MINUTES . ' minutes.';
    } else {
        $admin = one('SELECT * FROM admin WHERE username = ?', [$username]);
        $ok    = $admin && password_verify($password, $admin['password_hash']);

        q("INSERT INTO login_attempts (kind, identifier, ip, successful)
           VALUES ('admin',?,?,?)", [$username, client_ip(), $ok ? 1 : 0]);

        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = $admin['username'];
            audit('admin:' . $username, 'admin_login', 'signed in');
            redirect('admin_dashboard.php');
        }
        $error = 'Wrong username or password.';
    }
}

include('Front_end/front_admin_login.php');
