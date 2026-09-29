<?php
require_once __DIR__ . '/config/helpers.php';

$error = '';
$done  = false;
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $phone   = post('Phone');
    $nid     = post('Nid');
    $dob     = post('Dob');
    $new     = post('NewPassword');
    $confirm = post('ConfirmPassword');

    $failed = (int) scalar(
        "SELECT COUNT(*) FROM login_attempts
          WHERE kind = 'login' AND identifier = ? AND successful = 0
            AND attempted_at > (NOW() - INTERVAL ? MINUTE)",
        [$phone, LOGIN_LOCK_MINUTES], 0
    );

    if ($failed >= LOGIN_MAX_ATTEMPTS) {
        $error = __('Too many attempts. Please try again later.');
    } elseif (strlen($new) < 6) {
        $error = __('The new password must be at least 6 characters.');
    } elseif ($new !== $confirm) {
        $error = __('The two passwords do not match.');
    } else {
        $user = one('SELECT * FROM user WHERE phone = ? AND nid = ? AND dob = ?',
                    [$phone, $nid, $dob]);
        if (!$user) {
            q("INSERT INTO login_attempts (kind, identifier, ip, successful)
               VALUES ('login',?,?,0)", [$phone, client_ip()]);
            $error = __('Those details do not match any account.');
        } else {
            q('UPDATE user SET password_hash = ? WHERE phone = ?',
              [password_hash($new, PASSWORD_BCRYPT), $phone]);
            // only the login lockout clears here; the pin counter is what
            // freezes an account, and a password reset must not unfreeze it
            q("DELETE FROM login_attempts WHERE identifier = ? AND kind = 'login'", [$phone]);
            notify($phone, 'Password changed',
                   'Your password was reset. If this was not you, contact support now.', 'lock');
            audit($phone, 'password_reset', 'password reset from ' . client_ip());
            $done = true;
        }
    }
}

include('Front_end/front_reset_password.php');
