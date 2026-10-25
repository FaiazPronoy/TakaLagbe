<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    try {
        if ($action === 'request') {
            $from   = post('from_phone');
            $amount = post('amount');
            $note   = mb_substr(post('note'), 0, 120);

            if (!valid_phone($from)) {
                throw new MoneyError(__('Enter a valid 11 digit number.'));
            }
            if ($from === $phone) {
                throw new MoneyError(__('You cannot request money from yourself.'));
            }
            if (!scalar('SELECT 1 FROM user WHERE phone = ?', [$from])) {
                throw new MoneyError(__('That number is not registered on {app}.', ['app' => APP_NAME]));
            }
            if (!valid_amount($amount) || (float) $amount < MIN_AMOUNT) {
                throw new MoneyError(__('Ask for at least {amount}.', ['amount' => money(MIN_AMOUNT)]));
            }
            $open = (int) scalar("SELECT COUNT(*) FROM money_requests
                                   WHERE requester_phone = ? AND payer_phone = ?
                                     AND status = 'pending'", [$phone, $from], 0);
            if ($open >= 5) {
                throw new MoneyError(__('You already have 5 open requests with that person.'));
            }

            q('INSERT INTO money_requests (requester_phone, payer_phone, amount, note)
               VALUES (?,?,?,?)', [$phone, $from, (float) $amount, $note ?: null]);
            notify($from, 'Money requested',
                   name_of($phone) . ' asked you for ' . money($amount)
                   . ($note ? '. Note: ' . $note : '.'), 'request', (float) $amount);
            audit($phone, 'money_requested', $from . ' ' . $amount);
            flash('ok', __('Request sent to {name}.', ['name' => name_of($from)]));
            redirect('requests.php');
        }

        if ($action === 'split') {
            $total   = post('total');
            $note    = mb_substr(post('split_note'), 0, 120);
            $numbers = array_values(array_unique(array_filter(
                array_map('trim', preg_split('/[\s,;]+/', post('people'))))));

            if (!valid_amount($total) || (float) $total < MIN_AMOUNT) {
                throw new MoneyError(__('Enter the full bill amount.'));
            }
            if (!$numbers) {
                throw new MoneyError(__('Add at least one person to split with.'));
            }
            if (count($numbers) > 10) {
                throw new MoneyError(__('You can split between up to 10 other people.'));
            }
            foreach ($numbers as $n) {
                if (!valid_phone($n)) {
                    throw new MoneyError(__('"{n}" is not a valid number.', ['n' => $n]));
                }
                if ($n === $phone) {
                    throw new MoneyError(__('Your own number is counted automatically.'));
                }
                if (!scalar('SELECT 1 FROM user WHERE phone = ?', [$n])) {
                    throw new MoneyError(__('{phone} is not registered on {app}.', ['phone' => $n, 'app' => APP_NAME]));
                }
            }

            $heads = count($numbers) + 1;
            $share = round((float) $total / $heads, 2);
            if ($share < 1) {
                throw new MoneyError(__('Each share works out below one taka.'));
            }
            $group = 'SP' . strtoupper(bin2hex(random_bytes(4)));

            $pdo = db();
            $pdo->beginTransaction();
            foreach ($numbers as $n) {
                q('INSERT INTO money_requests
                     (requester_phone, payer_phone, amount, note, split_group)
                   VALUES (?,?,?,?,?)',
                  [$phone, $n, $share,
                   ($note ?: 'Split bill') . ' (' . $heads . ' ways)', $group]);
                notify($n, 'Split bill',
                       name_of($phone) . ' split a bill of ' . money($total)
                       . '. Your share is ' . money($share) . '.', 'request', $share);
            }
            $pdo->commit();

            audit($phone, 'bill_split', $group . ' ' . $total . ' between ' . $heads);
            flash('ok', __('Bill split {n} ways. Each person owes {amount}.', ['n' => $heads, 'amount' => money($share)]));
            redirect('requests.php');
        }

        if ($action === 'decline' || $action === 'cancel') {
            $id  = (int) post('request_id');
            $col = $action === 'decline' ? 'payer_phone' : 'requester_phone';
            $req = one("SELECT * FROM money_requests
                         WHERE id = ? AND $col = ? AND status = 'pending'", [$id, $phone]);
            if (!$req) {
                throw new MoneyError(__('That request was not found.'));
            }
            q("UPDATE money_requests SET status = 'declined' WHERE id = ?", [$id]);

            if ($action === 'decline') {
                notify($req['requester_phone'], 'Request declined',
                       name_of($phone) . ' declined your request for '
                       . money($req['amount']) . '.', 'out');
                flash('ok', __('Request declined.'));
            } else {
                flash('ok', __('Request cancelled.'));
            }
            redirect('requests.php');
        }

    } catch (MoneyError $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = __('That did not work. Please try again.');
    }
}

$incoming = all(
    "SELECT r.*, u.firstname, u.lastname, u.avatar_color FROM money_requests r
       LEFT JOIN user u ON u.phone = r.requester_phone
      WHERE r.payer_phone = ? AND r.status = 'pending'
      ORDER BY r.created_at DESC", [$phone]);

$outgoing = all(
    "SELECT r.*, u.firstname, u.lastname, u.avatar_color FROM money_requests r
       LEFT JOIN user u ON u.phone = r.payer_phone
      WHERE r.requester_phone = ? ORDER BY r.created_at DESC LIMIT 25", [$phone]);

$owedToMe = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM money_requests
      WHERE requester_phone = ? AND status = 'pending'", [$phone], 0);
$iOwe = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM money_requests
      WHERE payer_phone = ? AND status = 'pending'", [$phone], 0);

$pageTitle = __('Requests');
$activeNav = 'requests';
include('views/requests.php');
