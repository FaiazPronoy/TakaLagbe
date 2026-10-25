<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/money.php';

const BILL_TYPES = [
    'electricity_bills' => ['Electricity bill', 'bolt',    'Meter number'],
    'gas_bills'         => ['Gas bill',         'flame',   'Customer number'],
    'water_bills'       => ['Water bill',       'droplet', 'Customer number'],
    'internet_bills'    => ['Internet bill',    'wifi',    'Customer number'],
];

function bill_months(): array
{
    $out = [];
    for ($i = 0; $i < 12; $i++) {
        $out[] = date('F Y', strtotime("-$i month"));
    }
    return $out;
}

function run_bill_page(string $billtype): void
{
    if (!isset(BILL_TYPES[$billtype])) {
        http_response_code(404);
        exit('Unknown bill type.');
    }
    [$title, $icon, $consumerLabel] = BILL_TYPES[$billtype];

    $phone = require_login();
    $bal   = balance_of($phone);
    $error = '';

    $organizations = all('SELECT * FROM organizations WHERE billtype = ? ORDER BY organization_name',
                         [$billtype]);
    $months = bill_months();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
        require_fresh('bill');
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
            $error = __('Please choose a provider.');
        } elseif ($consumerNo === '' || !preg_match('/^[0-9A-Za-z\-]{4,40}$/', $consumerNo)) {
            $error = __('Enter a valid {what}.', ['what' => mb_strtolower(__($consumerLabel))]);
        } elseif (!in_array($month, $months, true)) {
            $error = __('Please choose a billing month.');
        } elseif (!valid_amount($amount)) {
            $error = __('Enter a valid amount.');
        } elseif ((float) $amount < MIN_AMOUNT) {
            $error = __('The smallest bill payment is {amount}.', ['amount' => money(MIN_AMOUNT)]);
        } elseif ($already) {
            $error = __('This bill was already paid for {month}. Reference {ref}.', ['month' => $month, 'ref' => $already]);
        } elseif (!verify_pin($phone, $pin)) {
            $left  = pin_tries_left($phone);
            $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
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
                flash('ok', __('{bill} of {amount} paid to {org}.',
                               ['bill' => __($title), 'amount' => money($amount), 'org' => $org['organization_name']]));
                redirect('receipt.php?id=' . urlencode($trx['trx_id']));

            } catch (MoneyError $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                record_refusal($e);
                $error = $e->getMessage();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = ($e->errorInfo[1] ?? 0) === 1062
                    ? 'This bill was already paid for ' . $month . '.'
                    : 'The bill could not be paid. Please try again.';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = __('The bill could not be paid. Please try again.');
            }
        }
    }

    $recent = all('SELECT * FROM bill_payments WHERE payer_phone = ? AND billtype = ?
                   ORDER BY payment_date DESC LIMIT 5', [$phone, $billtype]);

    $pageTitle = __($title);
    $activeNav = 'home';
    include __DIR__ . '/../views/bill.php';
}
