<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';
require_once __DIR__ . '/lib/view.php';

$phone = require_login();
$user  = current_user();
$bal   = balance_of($phone);

/* --- headline numbers ------------------------------------------------ */
$todayOut = spent_today($phone);
$limit    = (float) $user['daily_limit'];

$monthIn = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM transactions
      WHERE transaction_to = ? AND status = 'success' AND transaction_type <> 'Savings Release'
        AND transaction_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')", [$phone], 0);

$monthOut = (float) scalar(
    "SELECT COALESCE(SUM(amount + fee),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND transaction_type <> 'Savings'
        AND transaction_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')", [$phone], 0);

/* --- recent activity -------------------------------------------------- */
$recent = all(
    TRX_WITH_NAMES . "
      WHERE (t.transaction_from = ? OR t.transaction_to = ?) AND t.status = 'success'
      ORDER BY t.transaction_time DESC, t.id DESC LIMIT 6", [$phone, $phone]);

/* --- money people are waiting on ------------------------------------- */
$requests = all(
    "SELECT r.*, u.firstname, u.lastname, u.avatar_color FROM money_requests r
       LEFT JOIN user u ON u.phone = r.requester_phone
      WHERE r.payer_phone = ? AND r.status = 'pending'
      ORDER BY r.created_at DESC LIMIT 3", [$phone]);

$pageTitle = __('Home');
$activeNav = 'home';
include('Front_end/front_account.php');
