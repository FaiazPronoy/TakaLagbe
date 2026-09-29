<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

$merchants = all('SELECT * FROM merchants ORDER BY merchant_name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('payment');
    $merchantId = post('merchant_id');
    $amount     = post('amount');
    $reference  = mb_substr(post('reference'), 0, 60);
    $pin        = post('pin');

    $merchant = one('SELECT * FROM merchants WHERE merchant_id = ?', [$merchantId]);

    if (!$merchant) {
        $error = __('Please choose a merchant.');
    } elseif (!valid_amount($amount)) {
        $error = __('Enter a valid amount.');
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = __('The smallest payment is {amount}.', ['amount' => money(MIN_AMOUNT)]);
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
                'to_ref'       => $merchant['merchant_name'],
                'amount'       => (float) $amount,
                'type'         => 'Payment',
                'category'     => array_key_exists($merchant['category'], TRX_CATEGORIES)
                                    ? $merchant['category'] : 'Shopping',
                'note'         => $reference ?: ('Payment to ' . $merchant['merchant_name']),
                'cashback_pct' => (float) $merchant['cashback_pct'],
            ]);
            q('UPDATE merchants SET total_received_amount = total_received_amount + ?
                WHERE merchant_id = ?', [(float) $amount, $merchantId]);
            $pdo->commit();

            flash('ok', money($amount) . ' paid to ' . $merchant['merchant_name'] . '.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = __('The payment could not be completed. Please try again.');
        }
    }
}

$pageTitle = __('Make a payment');
$activeNav = 'home';
include('Front_end/front_payment.php');
