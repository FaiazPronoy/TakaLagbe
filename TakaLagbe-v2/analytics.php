<?php
/* ---------------------------------------------------------------------
 *  Spending insights, entirely from SQL: category totals, a six month
 *  trend, the people you send to most and a few habit numbers.
 * ------------------------------------------------------------------- */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

$phone = require_login();

$range  = in_array(get('range'), ['30', '90', '365'], true) ? get('range') : '30';
$days   = (int) $range;
$since  = date('Y-m-d', strtotime("-$days day"));

/* ---- spend by category --------------------------------------------- */
$catRows = all(
    "SELECT category, SUM(amount + fee) total, COUNT(*) n
       FROM transactions
      WHERE transaction_from = ? AND status = 'success'
        AND DATE(transaction_time) >= ?
      GROUP BY category ORDER BY total DESC", [$phone, $since]);

$categories = [];
foreach ($catRows as $r) {
    $categories[] = ['label' => $r['category'], 'value' => (float) $r['total'],
                     'count' => (int) $r['n'], 'colour' => category_colour($r['category'])];
}
$spendTotal = array_sum(array_column($categories, 'value'));

/* ---- six month trend ------------------------------------------------ */
$trendRows = all(
    "SELECT DATE_FORMAT(transaction_time, '%Y-%m') ym,
            SUM(CASE WHEN transaction_from = ? THEN amount + fee ELSE 0 END) outgoing,
            SUM(CASE WHEN transaction_to   = ? THEN amount ELSE 0 END) incoming
       FROM transactions
      WHERE (transaction_from = ? OR transaction_to = ?) AND status = 'success'
        AND transaction_time >= (CURDATE() - INTERVAL 5 MONTH)
      GROUP BY ym ORDER BY ym", [$phone, $phone, $phone, $phone]);

$byMonth = [];
foreach ($trendRows as $r) {
    $byMonth[$r['ym']] = $r;
}
$trend = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i month"));
    $trend[] = [
        'label'    => date('M', strtotime($ym . '-01')),
        'outgoing' => (float) ($byMonth[$ym]['outgoing'] ?? 0),
        'incoming' => (float) ($byMonth[$ym]['incoming'] ?? 0),
    ];
}
$trendMax = max(1.0, max(array_merge(array_column($trend, 'outgoing'),
                                     array_column($trend, 'incoming'))));

/* ---- who you send to most ------------------------------------------ */
$topPeople = all(
    "SELECT transaction_to who, SUM(amount) total, COUNT(*) n
       FROM transactions
      WHERE transaction_from = ? AND transaction_type = 'Send Money'
        AND status = 'success' AND DATE(transaction_time) >= ?
      GROUP BY transaction_to ORDER BY total DESC LIMIT 5", [$phone, $since]);

/* ---- habit numbers -------------------------------------------------- */
$busiestDay = one(
    "SELECT DAYNAME(transaction_time) d, COUNT(*) n
       FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND DATE(transaction_time) >= ?
      GROUP BY d ORDER BY n DESC LIMIT 1", [$phone, $since]);

$avgSpend = (float) scalar(
    "SELECT COALESCE(AVG(amount + fee),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND DATE(transaction_time) >= ?",
    [$phone, $since], 0);

$biggest = one(
    "SELECT * FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND DATE(transaction_time) >= ?
      ORDER BY amount DESC LIMIT 1", [$phone, $since]);

$feesPaid = (float) scalar(
    "SELECT COALESCE(SUM(fee),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND DATE(transaction_time) >= ?",
    [$phone, $since], 0);

$cashbackEarned = (float) scalar(
    "SELECT COALESCE(SUM(cashback),0) FROM transactions
      WHERE transaction_from = ? AND status = 'success' AND DATE(transaction_time) >= ?",
    [$phone, $since], 0);

$failedCount = (int) scalar(
    "SELECT COUNT(*) FROM transactions
      WHERE transaction_from = ? AND status = 'failed' AND DATE(transaction_time) >= ?",
    [$phone, $since], 0);

$dailyAvg = $days > 0 ? $spendTotal / $days : 0;

$pageTitle = 'Insights';
$activeNav = 'analytics';
include('Front_end/front_analytics.php');
