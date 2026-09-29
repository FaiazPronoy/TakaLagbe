<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$user  = current_user();
$bal   = balance_of($phone);

$error   = '';
$prefill = ['to' => get('to'), 'amount' => get('amount'), 'note' => get('note')];

/* an unpaid request can pre-fill the whole form */
$requestId = (int) get('request');
if ($requestId) {
    $req = one("SELECT * FROM money_requests
                 WHERE id = ? AND payer_phone = ? AND status = 'pending'",
               [$requestId, $phone]);
    if ($req) {
        $prefill = ['to' => $req['requester_phone'],
                    'amount' => $req['amount'],
                    'note' => $req['note'] ?: 'Request payment'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    require_fresh('sendmoney');
    $receiver = post('receiver_phone');
    $amount   = post('amount');
    $note     = mb_substr(post('note'), 0, 120);
    $pin      = post('pin');
    $saveFav  = post('save_favourite') === '1';
    $reqId    = (int) post('request_id');

    $prefill = ['to' => $receiver, 'amount' => $amount, 'note' => $note];

    if (!valid_phone($receiver)) {
        $error = __('Enter a valid 11 digit mobile number.');
    } elseif ($receiver === $phone) {
        $error = __('You cannot send money to your own account.');
    } elseif (!valid_amount($amount)) {
        $error = __('Enter a valid amount.');
    } elseif ((float) $amount < MIN_AMOUNT) {
        $error = __('The smallest amount you can send is {amount}.', ['amount' => money(MIN_AMOUNT)]);
    } elseif ((float) $amount > SEND_MONEY_MAX) {
        $error = __('You cannot send more than {amount} in one go.', ['amount' => money(SEND_MONEY_MAX)]);
    } elseif (!scalar('SELECT 1 FROM account WHERE phone = ?', [$receiver])) {
        $error = __('That number is not registered on {app}.', ['app' => APP_NAME]);
    } elseif (scalar("SELECT 1 FROM user WHERE phone = ? AND status = 'frozen'", [$receiver])) {
        $error = __('That account cannot receive money right now.');
    } elseif (!verify_pin($phone, $pin)) {
        $left  = pin_tries_left($phone);
        $error = __('Wrong transaction PIN.') . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : '');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // a request only counts as paid when this is the payment it asked for
            $req = null;
            if ($reqId) {
                $req = one("SELECT * FROM money_requests
                             WHERE id = ? AND payer_phone = ? AND status = 'pending' FOR UPDATE",
                           [$reqId, $phone]);
                if ($req && ($req['requester_phone'] !== $receiver
                             || (float) $req['amount'] !== round((float) $amount, 2))) {
                    throw new MoneyError('That request is for ' . money($req['amount']) . ' to '
                        . name_of($req['requester_phone']) . '. Change the number or amount back to pay it.');
                }
            }

            $trx = move_money([
                'from'     => $phone,
                'to'       => $receiver,
                'amount'   => (float) $amount,
                'type'     => 'Send Money',
                'category' => 'Transfer',
                'note'     => $note ?: null,
                'fee_pct'  => FEE_SEND_MONEY_PCT,
            ]);

            if ($req) {
                q("UPDATE money_requests SET status = 'paid' WHERE id = ?", [$req['id']]);
            }
            if ($saveFav) {
                q('INSERT IGNORE INTO favourites (owner_phone, saved_phone, label)
                   VALUES (?,?,?)',
                  [$phone, $receiver, mb_substr(name_of($receiver), 0, 120)]);
            }
            $pdo->commit();

            flash('ok', money($amount) . ' sent to ' . name_of($receiver) . '.');
            redirect('receipt.php?id=' . urlencode($trx['trx_id']));

        } catch (MoneyError $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            record_refusal($e);
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $error = __('The transfer could not be completed. Nothing has left your account.');
        }
    }
}

$favourites = all(
    "SELECT f.saved_phone, f.label, u.firstname, u.lastname, u.avatar_color
       FROM favourites f LEFT JOIN user u ON u.phone = f.saved_phone
      WHERE f.owner_phone = ? ORDER BY f.created_at DESC LIMIT 8", [$phone]);

$pageTitle = __('Send money');
$activeNav = 'send';
include('Front_end/front_sendmoney.php');
