<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

const BANKS = ['BRAC Bank', 'City Bank', 'Dutch Bangla Bank', 'Eastern Bank',
               'Islami Bank', 'Prime Bank', 'Visa Card', 'Mastercard'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('add_money');
    $bank   = post('bank');
    $amount = post('amount');
    $pin    = post('pin');

    if (!in_array($bank, BANKS, true)) {
        $error = __('Please choose a bank or card.');
    } elseif (!valid_amount($amount)) {
        $error = __('Enter a valid amount.');
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = __('The smallest top up is {amount}.', ['amount' => money(MIN_AMOUNT)]);
    } elseif ((float) $amount > ADD_MONEY_MAX) {
        $error = __('You cannot add more than {amount} at a time.', ['amount' => money(ADD_MONEY_MAX)]);
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
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
                'skip_limits' => true,
            ]);
            flash('ok', __('{amount} added from {bank}.', ['amount' => money($amount), 'bank' => $bank]));
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));
        } catch (MoneyError $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = __('Add money');
$activeNav = 'home';
include('views/add_money.php');
