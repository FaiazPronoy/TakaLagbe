<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$user  = current_user();
$error = $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    /* details */
    if ($action === 'details') {
        $email  = strtolower(post('email'));
        $colour = post('avatar_color');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colour)) {
            $error = 'Pick a colour from the list.';
        } elseif (scalar('SELECT 1 FROM user WHERE email = ? AND phone <> ?', [$email, $phone])) {
            $error = 'That email is already used by another account.';
        } else {
            q('UPDATE user SET email = ?, avatar_color = ? WHERE phone = ?',
              [$email, $colour, $phone]);
            audit($phone, 'profile_updated', 'details changed');
            flash('ok', 'Profile updated.');
            redirect('profile.php');
        }
    }

    /* password */
    if ($action === 'password') {
        $current = post('current_password');
        $new     = post('new_password');
        $confirm = post('confirm_password');

        if (!password_verify($current, $user['password_hash'])) {
            $error = 'Your current password is not right.';
        } elseif (strlen($new) < 6) {
            $error = 'The new password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'The two new passwords do not match.';
        } elseif (password_verify($new, $user['password_hash'])) {
            $error = 'The new password has to be different from the old one.';
        } else {
            q('UPDATE user SET password_hash = ? WHERE phone = ?',
              [password_hash($new, PASSWORD_BCRYPT), $phone]);
            notify($phone, 'Password changed',
                   'Your password was changed just now.', 'lock');
            audit($phone, 'password_changed', 'from ' . client_ip());
            flash('ok', 'Password changed.');
            redirect('profile.php');
        }
    }

    /* PIN */
    if ($action === 'pin') {
        $current = post('current_pin');
        $new     = post('new_pin');
        $confirm = post('confirm_pin');

        if (!password_verify($current, $user['pin_hash'])) {
            $error = 'Your current PIN is not right.';
        } elseif (!valid_pin($new)) {
            $error = 'The new PIN must be exactly 6 digits.';
        } elseif ($new !== $confirm) {
            $error = 'The two new PINs do not match.';
        } elseif (preg_match('/^(\d)\1{5}$/', $new) || in_array($new, ['123456', '654321'], true)) {
            $error = 'Please choose a less obvious PIN.';
        } else {
            q('UPDATE user SET pin_hash = ? WHERE phone = ?',
              [password_hash($new, PASSWORD_BCRYPT), $phone]);
            notify($phone, 'PIN changed', 'Your transaction PIN was changed.', 'lock');
            audit($phone, 'pin_changed', 'from ' . client_ip());
            flash('ok', 'Transaction PIN changed.');
            redirect('profile.php');
        }
    }

    /* daily limit */
    if ($action === 'limit') {
        $limit = post('daily_limit');
        $pin   = post('pin');

        if (!is_numeric($limit) || (float) $limit < 1000 || (float) $limit > 100000) {
            $error = 'Choose a limit between ' . money(1000) . ' and ' . money(100000) . '.';
        } elseif (!verify_pin($phone, $pin)) {
            $error = 'Wrong transaction PIN.';
        } else {
            q('UPDATE user SET daily_limit = ? WHERE phone = ?', [(float) $limit, $phone]);
            audit($phone, 'limit_changed', 'daily limit set to ' . $limit);
            flash('ok', 'Daily limit set to ' . money($limit) . '.');
            redirect('profile.php');
        }
    }
}

$user = one('SELECT * FROM user WHERE phone = ?', [$phone]);   // refresh after any change

$logins = all("SELECT * FROM login_attempts
                WHERE kind = 'login' AND identifier = ?
                ORDER BY attempted_at DESC LIMIT 8", [$phone]);
$activity = all('SELECT * FROM audit_log WHERE actor = ?
                 ORDER BY created_at DESC LIMIT 10', [$phone]);
$stats = [
    'transactions' => (int) scalar("SELECT COUNT(*) FROM transactions
                                     WHERE (transaction_from = ? OR transaction_to = ?)
                                       AND status = 'success'", [$phone, $phone], 0),
    'sent'         => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                       WHERE transaction_from = ? AND status = 'success'", [$phone], 0),
    'received'     => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                       WHERE transaction_to = ? AND status = 'success'", [$phone], 0),
    'member_since' => $user['created_at'],
];

$pageTitle = 'Profile';
$activeNav = 'profile';
include('Front_end/front_profile.php');
