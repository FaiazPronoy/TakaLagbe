<?php
require_once __DIR__ . '/config/helpers.php';

$phone = require_login();
$user  = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $message = trim(post('message'));

    if (mb_strlen($message) < 10) {
        $error = __('Please describe the problem in at least 10 characters.');
    } elseif (mb_strlen($message) > 2000) {
        $error = __('Please keep the message under 2000 characters.');
    } else {
        $open = (int) scalar('SELECT COUNT(*) FROM support_messages
                               WHERE user_phone = ? AND issue_solved = 0', [$phone], 0);
        if ($open >= 5) {
            $error = __('You already have 5 open tickets. Please wait for a reply.');
        } else {
            q('INSERT INTO support_messages (user_name, user_email, user_phone, message)
               VALUES (?,?,?,?)',
              [$user['firstname'] . ' ' . $user['lastname'], $user['email'], $phone, $message]);
            notify($phone, 'Support ticket received',
                   'We have your message and will get back to you.', 'bell');
            audit($phone, 'support_ticket', mb_substr($message, 0, 80));
            flash('ok', __('Your message has been sent to support.'));
            redirect('support.php');
        }
    }
}

$tickets = all('SELECT * FROM support_messages WHERE user_phone = ?
                ORDER BY timestamp DESC LIMIT 20', [$phone]);

$faq = [
    [__('Why was my transfer declined?'),
     __('The three usual reasons are not enough balance, the daily limit, or a wrong PIN. Every declined attempt is written to your history with the reason.')],
    [__('What are the charges?'),
     __('Sending money between users is free. Cash out costs {pct} percent. Bills carry a flat {fee} convenience charge. Adding money, cash in and education fees are free.',
        ['pct' => FEE_CASH_OUT_PCT, 'fee' => money(FEE_BILL_FLAT)])],
    [__('How do I get cashback?'),
     __('Mobile recharge gives {pct} percent back, and merchant payments give whatever that merchant offers. Cashback lands in your balance in the same transaction.',
        ['pct' => CASHBACK_RECHARGE_PCT])],
    [__('I forgot my PIN. What now?'),
     __('Open Profile and change the PIN using your current one. If you cannot remember it at all, raise a ticket here and support will verify your identity.')],
    [__('Why did my account freeze?'),
     __('Five wrong PIN attempts in a row freeze an account so nobody can guess their way in. Support can unfreeze it once you are verified.')],
    [__('Is my money safe if the server crashes mid transfer?'),
     __('Yes. Every transfer runs inside one database transaction. If anything fails the whole thing is rolled back, so money is never taken from one side without reaching the other.')],
];

$pageTitle = __('Support');
$activeNav = '';
include('views/support.php');
