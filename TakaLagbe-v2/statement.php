<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

$phone = require_login();
$user  = current_user();

$from = get('from') ?: date('Y-m-01');
$to   = get('to')   ?: date('Y-m-d');
$type = get('type');

$where  = "(transaction_from = ? OR transaction_to = ?)
           AND DATE(transaction_time) BETWEEN ? AND ?";
$params = [$phone, $phone, $from, $to];
if ($type !== '') {
    $where   .= ' AND transaction_type = ?';
    $params[] = $type;
}

$rows = all("SELECT * FROM transactions WHERE $where
             ORDER BY transaction_time ASC, id ASC", $params);

$openingRow = one(
    "SELECT sender_balance_after, receiver_balance_after, transaction_from
       FROM transactions
      WHERE (transaction_from = ? OR transaction_to = ?)
        AND transaction_time < ? AND status = 'success'
      ORDER BY transaction_time DESC, id DESC LIMIT 1",
    [$phone, $phone, $from . ' 00:00:00']);

$opening = 0.0;
if ($openingRow) {
    $opening = (float) ($openingRow['transaction_from'] === $phone
        ? $openingRow['sender_balance_after']
        : $openingRow['receiver_balance_after']);
}

$closing = balance_of($phone);

/* CSV */
if (get('format') === 'csv') {
    // the dates come from the form, so only use them if they look like dates
    $safe = static fn(string $d): string =>
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : date('Y-m-d');
    $name = 'takalagbe-statement-' . $safe($from) . '-to-' . $safe($to) . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');

    $fh = fopen('php://output', 'w');
    fwrite($fh, "\xEF\xBB\xBF");                    // BOM so Excel reads Bangla
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

$pageTitle = 'Statement';
$activeNav = 'history';
include('Front_end/front_statement.php');
