<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('recharge');
    $target     = post('rechargePhoneNumber');
    $amount     = post('rechargeAmount');
    $connection = post('connection') === 'Postpaid' ? 'Postpaid' : 'Prepaid';
    $pin        = post('pin');
    $operator   = operator_of($target);

    if (!valid_phone($target)) {
        $error = 'Enter a valid 11 digit mobile number.';
    } elseif ($operator === 'Unknown') {
        $error = 'That number does not belong to a known operator.';
    } elseif (!valid_amount($amount)) {
        $error = 'Enter a valid amount.';
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = 'The smallest recharge is ' . money(MIN_AMOUNT) . '.';
    } elseif ((float) $amount > RECHARGE_MAX) {
        $error = 'A recharge cannot be more than ' . money(RECHARGE_MAX) . '.';
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = 'Wrong transaction PIN.' . ($left > 0 ? " $left attempt(s) left." : '');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $trx = move_money([
                'from'         => $phone,
                'to'           => null,
                // The operator is the counterparty, not the number. Writing the
                // number here would drop this row into that person's own history
                // and let them open the payer's receipt.
                'to_ref'       => $operator,
                'amount'       => (float) $amount,
                'type'         => 'Mobile Recharge',
                'category'     => 'Recharge',
                'note'         => $connection . ' top up for ' . $target,
                'cashback_pct' => CASHBACK_RECHARGE_PCT,
            ]);
            q('INSERT INTO mobile_recharge
                 (payer_phone, target_phone, operator, connection, amount, trx_id)
               VALUES (?,?,?,?,?,?)',
              [$phone, $target, $operator, $connection, (float) $amount, $trx['trx_id']]);
            $pdo->commit();

            flash('ok', money($amount) . ' recharged to ' . $target . ' on ' . $operator . '.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = 'The recharge could not be completed. Please try again.';
        }
    }
}

$recent = all('SELECT * FROM mobile_recharge WHERE payer_phone = ?
               ORDER BY created_at DESC LIMIT 5', [$phone]);

$pageTitle = 'Mobile recharge';
$activeNav = 'home';
include('Front_end/front_mobilerecharge.php');
