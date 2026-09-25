<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

$agents = all('SELECT agent_id, phone_number, cash_in_amount FROM agent_details ORDER BY agent_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('cash_in');
    $agentId = post('agent_id');
    $amount  = post('cash_in_amount');
    $pin     = post('pin');

    $agent = one('SELECT * FROM agent_details WHERE agent_id = ?', [$agentId]);

    if (!$agent) {
        $error = 'That agent number was not found.';
    } elseif (!valid_amount($amount)) {
        $error = 'Enter a valid amount.';
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = 'The smallest cash in is ' . money(MIN_AMOUNT) . '.';
    } elseif ((float) $amount > ADD_MONEY_MAX) {
        $error = 'You cannot cash in more than ' . money(ADD_MONEY_MAX) . ' at a time.';
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = 'Wrong transaction PIN.' . ($left > 0 ? " $left attempt(s) left." : '');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            scalar('SELECT cash_in_amount FROM agent_details WHERE agent_id = ? FOR UPDATE', [$agentId]);
            $trx = move_money([
                'from'        => null,
                'from_ref'    => $agentId,
                'to'          => $phone,
                'amount'      => (float) $amount,
                'type'        => 'Cash In',
                'category'    => 'Cash',
                'note'        => 'Cash in at agent ' . $agentId,
                'skip_limits' => true,
            ]);
            q('UPDATE agent_details SET cash_in_amount = cash_in_amount + ? WHERE agent_id = ?',
              [(float) $amount, $agentId]);
            $pdo->commit();

            flash('ok', money($amount) . ' added to your wallet.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = 'Cash in could not be completed. Please try again.';
        }
    }
}

$pageTitle = 'Cash in';
$activeNav = 'home';
include('Front_end/front_cashin.php');
