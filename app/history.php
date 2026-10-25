<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

$phone = require_login();

$search    = mb_substr(get('q'), 0, 60);
$type      = get('type');
$direction = get('dir');
$from      = get('from');
$to        = get('to');
$page      = max(1, (int) get('page', '1'));

$where  = ['(t.transaction_from = ? OR t.transaction_to = ?)'];
$params = [$phone, $phone];

if ($search !== '') {
    $where[]  = '(t.trx_id LIKE ? OR t.transaction_from LIKE ? OR t.transaction_to LIKE ?
                  OR t.note LIKE ? OR t.transaction_type LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like, $like, $like, $like]);
}
if ($type !== '') {
    $where[]  = 't.transaction_type = ?';
    $params[] = $type;
}
if ($direction === 'in') {
    $where[]  = 't.transaction_to = ?';
    $params[] = $phone;
} elseif ($direction === 'out') {
    $where[]  = 't.transaction_from = ?';
    $params[] = $phone;
}
$from = valid_date($from) ? $from : '';
$to   = valid_date($to)   ? $to   : '';
if ($from !== '') {
    $where[]  = 'DATE(t.transaction_time) >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $where[]  = 'DATE(t.transaction_time) <= ?';
    $params[] = $to;
}

$clause = implode(' AND ', $where);

$total = (int) scalar("SELECT COUNT(*) FROM transactions t WHERE $clause", $params, 0);
$pages = max(1, (int) ceil($total / ROWS_PER_PAGE));
$page  = min($page, $pages);
$offset = ($page - 1) * ROWS_PER_PAGE;

$sumIn = (float) scalar(
    "SELECT COALESCE(SUM(t.amount),0) FROM transactions t
      WHERE $clause AND t.transaction_to = ? AND t.status = 'success'",
    array_merge($params, [$phone]), 0);

$sumOut = (float) scalar(
    "SELECT COALESCE(SUM(t.amount + t.fee),0) FROM transactions t
      WHERE $clause AND t.transaction_from = ? AND t.status = 'success'",
    array_merge($params, [$phone]), 0);

$rows = all(
    TRX_WITH_NAMES . " WHERE $clause
      ORDER BY t.transaction_time DESC, t.id DESC
      LIMIT " . ROWS_PER_PAGE . " OFFSET " . $offset,
    $params);

$types = array_column(
    all("SELECT DISTINCT transaction_type FROM transactions
          WHERE transaction_from = ? OR transaction_to = ?
          ORDER BY transaction_type", [$phone, $phone]),
    'transaction_type');

$queryFor = static function (array $override) use ($search, $type, $direction, $from, $to): string {
    return http_build_query(array_merge(
        array_filter(['q' => $search, 'type' => $type, 'dir' => $direction,
                      'from' => $from, 'to' => $to], static fn($v) => $v !== ''),
        $override
    ));
};

$pageTitle = __('History');
$activeNav = 'history';
include('views/history.php');
