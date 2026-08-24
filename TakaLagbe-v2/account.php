<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$user  = current_user();
$bal   = balance_of($phone);

/* --- headline numbers ------------------------------------------------ */
$todayOut = spent_today($phone);
$limit    = (float) $user['daily_limit'];

$monthIn = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM transactions
      WHERE transaction_to = ? AND status = 'success'
        AND transaction_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')", [$phone], 0);

$monthOut = (float) scalar(
    "SELECT COALESCE(SUM(amount + fee),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success'
        AND transaction_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')", [$phone], 0);

$saved = (float) scalar(
    "SELECT COALESCE(SUM(saved_amount),0) FROM savings_goals
      WHERE phone = ? AND status = 'active'", [$phone], 0);

$cashback = (float) scalar(
    "SELECT COALESCE(SUM(cashback),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success'", [$phone], 0);

/* --- recent activity -------------------------------------------------- */
$recent = all(
    "SELECT * FROM transactions
      WHERE (transaction_from = ? OR transaction_to = ?) AND status = 'success'
      ORDER BY transaction_time DESC, id DESC LIMIT 6", [$phone, $phone]);

/* --- money people are waiting on ------------------------------------- */
$requests = all(
    "SELECT r.*, u.firstname, u.lastname FROM money_requests r
       LEFT JOIN user u ON u.phone = r.requester_phone
      WHERE r.payer_phone = ? AND r.status = 'pending'
      ORDER BY r.created_at DESC LIMIT 3", [$phone]);

/* --- seven day outgoing chart ---------------------------------------- */
$rows = all(
    "SELECT DATE(transaction_time) d, SUM(amount + fee) total
       FROM transactions
      WHERE transaction_from = ? AND status = 'success'
        AND transaction_time >= (CURDATE() - INTERVAL 6 DAY)
      GROUP BY DATE(transaction_time)", [$phone]);
$byDay = [];
foreach ($rows as $r) {
    $byDay[$r['d']] = (float) $r['total'];
}
$chart = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $chart[] = ['label' => date('D', strtotime($d)), 'value' => $byDay[$d] ?? 0.0];
}
$chartMax = max(1.0, max(array_column($chart, 'value')));

/* --- favourites for one tap sending ---------------------------------- */
$favourites = all(
    "SELECT f.saved_phone, f.label, u.firstname, u.lastname, u.avatar_color
       FROM favourites f LEFT JOIN user u ON u.phone = f.saved_phone
      WHERE f.owner_phone = ? ORDER BY f.created_at DESC LIMIT 6", [$phone]);

$pageTitle = 'Home';
$activeNav = 'home';
include('Front_end/front_account.php');
