<?php
/* A quick balance check, PIN protected so a borrowed phone shows nothing. */
require_once __DIR__ . '/config/helpers.php';

$phone   = require_login();
$user    = current_user();
$balance = null;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (!verify_pin($phone, post('pin'))) {
        $left  = pin_tries_left($phone);
        $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
    } else {
        $balance = balance_of($phone);
        audit($phone, 'balance_checked', 'balance viewed');
    }
}

$inSavings = (float) scalar("SELECT COALESCE(SUM(saved_amount),0) FROM savings_goals
                              WHERE phone = ? AND status = 'active'", [$phone], 0);

$pageTitle = __('Check balance');
$activeNav = 'home';
include('Front_end/front_checkbalance.php');
