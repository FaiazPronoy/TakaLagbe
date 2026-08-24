<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

$agents = all('SELECT agent_id, phone_number, cash_in_amount FROM agent_details ORDER BY agent_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('cash_out');
    $agentId = post('agent_id');
    $amount  = post('cash_out_amount');
    $pin     = post('pin');

    $agent = one('SELECT * FROM agent_details WHERE agent_id = ?', [$agentId]);

    if (!$agent) {
        $error = 'That agent number was not found.';
    } elseif (!valid_amount($amount)) {
        $error = 'Enter a valid amount.';
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = 'The smallest cash out is ' . money(MIN_AMOUNT) . '.';
    } elseif ((float) $amount > CASH_OUT_MAX) {
        $error = 'You cannot cash out more than ' . money(CASH_OUT_MAX) . ' at a time.';
    } elseif ((float) $agent['cash_in_amount'] < (float) $amount) {
        $error = 'This agent does not have enough cash right now. Try another agent.';
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
                'to_ref'   => $agentId,
                'amount'   => (float) $amount,
                'type'     => 'Cash Out',
                'category' => 'Cash',
                'note'     => 'Cash out at agent ' . $agentId,
                'fee_pct'  => FEE_CASH_OUT_PCT,
            ]);
            /* the agent hands over notes, so their float drops */
            q('UPDATE agent_details SET cash_in_amount = cash_in_amount - ? WHERE agent_id = ?',
              [(float) $amount, $agentId]);
            $pdo->commit();

            flash('ok', 'Collect ' . money($amount) . ' from agent ' . $agentId . '.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = 'Cash out could not be completed. Please try again.';
        }
    }
}

$pageTitle = 'Cash out';
$activeNav = 'home';
include('Front_end/front_cashout.php');
