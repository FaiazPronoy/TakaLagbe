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
        $error = __('Enter a valid 11 digit mobile number.');
    } elseif ($operator === 'Unknown') {
        $error = __('That number does not belong to a known operator.');
    } elseif (!valid_amount($amount)) {
        $error = __('Enter a valid amount.');
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = __('The smallest recharge is {amount}.', ['amount' => money(MIN_AMOUNT)]);
    } elseif ((float) $amount > RECHARGE_MAX) {
        $error = __('A recharge cannot be more than {amount}.', ['amount' => money(RECHARGE_MAX)]);
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $trx = move_money([
                'from'         => $phone,
                'to'           => null,
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

            flash('ok', __('{amount} recharged to {number} on {operator}.', ['amount' => money($amount), 'number' => $target, 'operator' => $operator]));
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = __('The recharge could not be completed. Please try again.');
        }
    }
}

$recent = all('SELECT * FROM mobile_recharge WHERE payer_phone = ?
               ORDER BY created_at DESC LIMIT 5', [$phone]);

$pageTitle = __('Mobile recharge');
$activeNav = 'home';
include('views/mobile_recharge.php');
