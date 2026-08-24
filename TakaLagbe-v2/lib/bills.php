<?php
/* Bill payment lookups. */

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/money.php';

const BILL_TYPES = [
    'electricity_bills' => ['Electricity bill', "\u{26A1}", 'Meter number',    'electricity.jpg'],
    'gas_bills'         => ['Gas bill',         "\u{1F525}", 'Customer number', 'gas.jpg'],
    'water_bills'       => ['Water bill',       "\u{1F4A7}", 'Customer number', 'water.jpg'],
    'internet_bills'    => ['Internet bill',    "\u{1F310}", 'Customer number', 'wifi.jpg'],
];

/** Months a bill can be paid for: this month and the eleven before it. */
function bill_months(): array
{
    $out = [];
    for ($i = 0; $i < 12; $i++) {
        $out[] = date('F Y', strtotime("-$i month"));
    }
    return $out;
}

/**
 * Runs a complete bill page: handles the POST, then includes the view.
 * Called by electricity_bills.php and its three siblings.
 */
function run_bill_page(string $billtype): void
{
    if (!isset(BILL_TYPES[$billtype])) {
        http_response_code(404);
        exit('Unknown bill type.');
    }
    [$title, $icon, $consumerLabel, $billImage] = BILL_TYPES[$billtype];

    $phone = require_login();
    $bal   = balance_of($phone);
    $error = '';

    $organizations = all('SELECT * FROM organizations WHERE billtype = ? ORDER BY organization_name',
                         [$billtype]);
    $months = bill_months();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        $orgId      = post('organization_id');
        $consumerNo = post('consumer_no');
        $amount     = post('amount');
        $month      = post('month');
        $pin        = post('pin');

        $org = one('SELECT * FROM organizations WHERE organization_id = ? AND billtype = ?',
                   [$orgId, $billtype]);

        $already = $org ? scalar(
            'SELECT trx_id FROM bill_payments
              WHERE billtype = ? AND organization_id = ? AND consumer_no = ? AND month = ?',
            [$billtype, $orgId, $consumerNo, $month]) : null;

        if (!$org) {
            $error = 'Please choose a provider.';
        } elseif ($consumerNo === '' || !preg_match('/^[0-9A-Za-z\-]{4,40}$/', $consumerNo)) {
            $error = 'Enter a valid ' . strtolower($consumerLabel) . '.';
        } elseif (!in_array($month, $months, true)) {
            $error = 'Please choose a billing month.';
        } elseif (!valid_amount($amount)) {
            $error = 'Enter a valid amount.';
        } elseif ((float) $amount < MIN_AMOUNT) {
            $error = 'The smallest bill payment is ' . money(MIN_AMOUNT) . '.';
        } elseif ($already) {
            $error = 'This bill was already paid for ' . $month
                   . '. Reference ' . $already . '.';
        } elseif (!verify_pin($phone, $pin)) {
            $left  = pin_tries_left($phone);
            $error = 'Wrong transaction PIN.' . ($left > 0 ? " $left attempt(s) left." : '');
        } else {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $trx = move_money([
                    'from'     => $phone,
                    'to'       => null,
                    'to_ref'   => $org['organization_name'],
                    'amount'   => (float) $amount,
                    'type'     => 'Bill Pay',
                    'category' => 'Bills',
                    'note'     => $title . ' for ' . $month,
                    'fee_flat' => FEE_BILL_FLAT,
                ]);

                q('INSERT INTO bill_payments
                     (payer_phone, billtype, organization_id, organization_name,
                      consumer_no, amount, month, trx_id)
                   VALUES (?,?,?,?,?,?,?,?)',
                  [$phone, $billtype, $orgId, $org['organization_name'],
                   $consumerNo, (float) $amount, $month, $trx['trx_id']]);

                $pdo->commit();
                flash('ok', $title . ' of ' . money($amount) . ' paid to '
                          . $org['organization_name'] . '.');
                redirect('receipt.php?id=' . urlencode($trx['trx_id']));

            } catch (MoneyError $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = $e->getMessage();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = 'The bill could not be paid. Please try again.';
            }
        }
    }

    $recent = all('SELECT * FROM bill_payments WHERE payer_phone = ? AND billtype = ?
                   ORDER BY payment_date DESC LIMIT 5', [$phone, $billtype]);

    $pageTitle = $title;
    $activeNav = 'home';
    include __DIR__ . '/../Front_end/front_bill.php';
}
