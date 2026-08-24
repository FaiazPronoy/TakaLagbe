<?php
/* Raise a support ticket and follow its progress. */
require_once __DIR__ . '/config/helpers.php';

$phone = require_login();
$user  = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $message = trim(post('message'));

    if (mb_strlen($message) < 10) {
        $error = 'Please describe the problem in at least 10 characters.';
    } elseif (mb_strlen($message) > 2000) {
        $error = 'Please keep the message under 2000 characters.';
    } else {
        $open = (int) scalar('SELECT COUNT(*) FROM support_messages
                               WHERE user_phone = ? AND issue_solved = 0', [$phone], 0);
        if ($open >= 5) {
            $error = 'You already have 5 open tickets. Please wait for a reply.';
        } else {
            q('INSERT INTO support_messages (user_name, user_email, user_phone, message)
               VALUES (?,?,?,?)',
              [$user['firstname'] . ' ' . $user['lastname'], $user['email'], $phone, $message]);
            notify($phone, 'Support ticket received',
                   'We have your message and will get back to you.', 'bell');
            audit($phone, 'support_ticket', mb_substr($message, 0, 80));
            flash('ok', 'Your message has been sent to support.');
            redirect('supports.php');
        }
    }
}

$tickets = all('SELECT * FROM support_messages WHERE user_phone = ?
                ORDER BY timestamp DESC LIMIT 20', [$phone]);

$faq = [
    ['Why was my transfer declined?',
     'The three usual reasons are not enough balance, the daily limit, or a wrong PIN. '
     . 'Every declined attempt is written to your history with the reason.'],
    ['What are the charges?',
     'Sending money between users is free. Cash out costs ' . FEE_CASH_OUT_PCT
     . ' percent. Bills carry a flat ' . money(FEE_BILL_FLAT) . ' convenience charge. '
     . 'Adding money, cash in and education fees are free.'],
    ['How do I get cashback?',
     'Mobile recharge gives ' . CASHBACK_RECHARGE_PCT
     . ' percent back, and merchant payments give whatever that merchant offers. '
     . 'Cashback lands in your balance in the same transaction.'],
    ['I forgot my PIN. What now?',
     'Open Profile and change the PIN using your current one. If you cannot remember it '
     . 'at all, raise a ticket here and support will verify your identity.'],
    ['Why did my account freeze?',
     'Five wrong PIN attempts in a row freeze an account so nobody can guess their way in. '
     . 'Support can unfreeze it once you are verified.'],
    ['Is my money safe if the server crashes mid transfer?',
     'Yes. Every transfer runs inside one database transaction. If anything fails the whole '
     . 'thing is rolled back, so money is never taken from one side without reaching the other.'],
];

$pageTitle = 'Support';
$activeNav = '';
include('Front_end/front_supports.php');
