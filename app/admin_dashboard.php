<?php
/* The admin overview: platform numbers, live charts and the newest activity. */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

require_admin();

$stats = [
    'users'    => (int)   scalar('SELECT COUNT(*) FROM user', [], 0),
    'frozen'   => (int)   scalar("SELECT COUNT(*) FROM user WHERE status = 'frozen'", [], 0),
    'float'    => (float) scalar('SELECT COALESCE(SUM(balance),0) FROM account', [], 0),
    'trxCount' => (int)   scalar("SELECT COUNT(*) FROM transactions WHERE status = 'success'", [], 0),
    'volume'   => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                   WHERE status = 'success'", [], 0),
    'fees'     => (float) scalar("SELECT COALESCE(SUM(fee),0) FROM transactions
                                   WHERE status = 'success'", [], 0),
    'cashback' => (float) scalar("SELECT COALESCE(SUM(cashback),0) FROM transactions
                                   WHERE status = 'success'", [], 0),
    'failed'   => (int)   scalar("SELECT COUNT(*) FROM transactions WHERE status = 'failed'", [], 0),
    'today'    => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                   WHERE status = 'success' AND DATE(transaction_time) = CURDATE()", [], 0),
    'tickets'  => (int)   scalar('SELECT COUNT(*) FROM support_messages WHERE issue_solved = 0', [], 0),
];

/* volume by type, for the donut */
$byType = [];
foreach (all("SELECT transaction_type t, SUM(amount) v, COUNT(*) n FROM transactions
               WHERE status = 'success' GROUP BY t ORDER BY v DESC") as $r) {
    $byType[] = ['label' => $r['t'], 'value' => (float) $r['v'], 'count' => (int) $r['n'],
                 'colour' => category_colour(match ($r['t']) {
                     'Send Money' => 'Transfer',
                     'Bill Pay' => 'Bills',
                     'Mobile Recharge' => 'Recharge',
                     'Payment' => 'Shopping',
                     'Education Fee' => 'Education',
                     'Savings', 'Savings Release' => 'Savings',
                     'Cash In', 'Cash Out', 'Add Money' => 'Cash',
                     default => 'Other',
                 })];
}

/* fourteen day volume, for the bar chart */
$rows = all("SELECT DATE(transaction_time) d, SUM(amount) v FROM transactions
              WHERE status = 'success' AND transaction_time >= (CURDATE() - INTERVAL 13 DAY)
              GROUP BY d");
$byDay = [];
foreach ($rows as $r) {
    $byDay[$r['d']] = (float) $r['v'];
}
$chart = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $chart[] = ['label' => date('j/n', strtotime($d)), 'value' => $byDay[$d] ?? 0.0];
}
$chartMax = max(1.0, max(array_column($chart, 'value')));

$topUsers = all(
    "SELECT u.phone, u.firstname, u.lastname, u.avatar_color, a.balance,
            (SELECT COUNT(*) FROM transactions t
              WHERE (t.transaction_from = u.phone OR t.transaction_to = u.phone)
                AND t.status = 'success') n
       FROM user u JOIN account a ON a.phone = u.phone
      ORDER BY n DESC, a.balance DESC LIMIT 6");

$latest  = all("SELECT * FROM transactions ORDER BY id DESC LIMIT 10");
$recentAudit = all('SELECT * FROM audit_log ORDER BY id DESC LIMIT 8');

$pageTitle = 'Dashboard';
$activeNav = 'dash';
include('Front_end/front_admin_dashboard.php');
