<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

$phone = require_login();
$user  = current_user();

$from = valid_date(get('from')) ? get('from') : date('Y-m-01');
$to   = valid_date(get('to'))   ? get('to')   : date('Y-m-d');
if ($from > $to) {
    [$from, $to] = [$to, $from];
}
$type = get('type');

$where  = "(t.transaction_from = ? OR t.transaction_to = ?) AND t.status = 'success'
           AND DATE(t.transaction_time) BETWEEN ? AND ?";
$params = [$phone, $phone, $from, $to];
if ($type !== '') {
    $where   .= ' AND t.transaction_type = ?';
    $params[] = $type;
}

$rows = all(TRX_WITH_NAMES . " WHERE $where
             ORDER BY t.transaction_time ASC, t.id ASC", $params);

$balanceAt = static function (string $moment) use ($phone): float {
    $r = one("SELECT sender_balance_after, receiver_balance_after, transaction_from
                FROM transactions
               WHERE (transaction_from = ? OR transaction_to = ?)
                 AND transaction_time < ? AND status = 'success'
               ORDER BY transaction_time DESC, id DESC LIMIT 1",
             [$phone, $phone, $moment]);
    if (!$r) {
        return 0.0;
    }
    return (float) ($r['transaction_from'] === $phone ? $r['sender_balance_after']
                                                      : $r['receiver_balance_after']);
};

$opening = $balanceAt($from . ' 00:00:00');
$closing = $balanceAt(date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00');

if (get('format') === 'csv') {
    $name = 'takalagbe-statement-' . $from . '-to-' . $to . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');

    $fh = fopen('php://output', 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, ['Statement for', $user['firstname'] . ' ' . $user['lastname'] . ' (' . $phone . ')']);
    fputcsv($fh, ['Period', $from . ' to ' . $to]);
    fputcsv($fh, ['Opening balance', number_format($opening, 2, '.', '')]);
    fputcsv($fh, []);
    fputcsv($fh, ['Date', 'Transaction ID', 'Type', 'Category', 'Counterparty',
                  'Note', 'Money in', 'Money out', 'Charge', 'Cashback',
                  'Balance after', 'Status']);

    foreach ($rows as $t) {
        $out = trx_is_out($t, $phone);
        fputcsv($fh, [
            $t['transaction_time'],
            $t['trx_id'],
            $t['transaction_type'],
            $t['category'],
            $out ? $t['transaction_to'] : $t['transaction_from'],
            $t['note'],
            $out ? '' : number_format((float) $t['amount'], 2, '.', ''),
            $out ? number_format((float) $t['amount'], 2, '.', '') : '',
            number_format((float) $t['fee'], 2, '.', ''),
            number_format((float) $t['cashback'], 2, '.', ''),
            $out ? $t['sender_balance_after'] : $t['receiver_balance_after'],
            $t['status'],
        ]);
    }
    fputcsv($fh, []);
    fputcsv($fh, ['Closing balance', number_format($closing, 2, '.', '')]);
    fclose($fh);
    exit;
}

$totalIn = $totalOut = $totalFee = $totalBack = 0.0;
foreach ($rows as $t) {
    if ($t['status'] !== 'success') {
        continue;
    }
    if (trx_is_out($t, $phone)) {
        $totalOut  += (float) $t['amount'];
        $totalFee  += (float) $t['fee'];
        $totalBack += (float) $t['cashback'];
    } else {
        $totalIn += (float) $t['amount'];
    }
}

$pageTitle = __('Statement');
$activeNav = 'history';
include('views/statement.php');
