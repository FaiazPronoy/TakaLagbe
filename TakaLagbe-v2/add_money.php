<?php
/* ---------------------------------------------------------------------
 *  Add money from a linked bank or card.
 *  The money comes from outside the wallet, so there is no account to
 *  debit: move_money() is told `from` is null and the bank name is
 *  recorded in the ledger instead.
 * ------------------------------------------------------------------- */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

const BANKS = ['BRAC Bank', 'City Bank', 'Dutch Bangla Bank', 'Eastern Bank',
               'Islami Bank', 'Prime Bank', 'Visa Card', 'Mastercard'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $bank   = post('bank');
    $amount = post('amount');
    $pin    = post('pin');

    if (!in_array($bank, BANKS, true)) {
        $error = 'Please choose a bank or card.';
    } elseif (!valid_amount($amount)) {
        $error = 'Enter a valid amount.';
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = 'The smallest top up is ' . money(MIN_AMOUNT) . '.';
    } elseif ((float) $amount > ADD_MONEY_MAX) {
        $error = 'You cannot add more than ' . money(ADD_MONEY_MAX) . ' at a time.';
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = 'Wrong transaction PIN.' . ($left > 0 ? " $left attempt(s) left." : '');
    } else {
        try {
            $trx = move_money([
                'from'        => null,
                'from_ref'    => $bank,
                'to'          => $phone,
                'amount'      => (float) $amount,
                'type'        => 'Add Money',
                'category'    => 'Cash',
                'note'        => 'Top up from ' . $bank,
                'skip_limits' => true,     // money coming in is never limited
            ]);
            flash('ok', money($amount) . ' added from ' . $bank . '.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));
        } catch (MoneyError $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Add money';
$activeNav = 'home';
include('Front_end/front_add_money.php');
