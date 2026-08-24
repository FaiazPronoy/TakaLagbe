<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/money.php';

$phone = require_login();
$bal   = balance_of($phone);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    try {
        /* create */
        if ($action === 'create') {
            $title    = mb_substr(post('title'), 0, 60);
            $target   = post('target_amount');
            $deadline = post('deadline') ?: null;

            if ($title === '') {
                throw new MoneyError('Give the goal a name.');
            }
            if (!valid_amount($target) || (float) $target < MIN_AMOUNT) {
                throw new MoneyError('Set a target of at least ' . money(MIN_AMOUNT) . '.');
            }
            if ($deadline !== null && strtotime($deadline) < strtotime('today')) {
                throw new MoneyError('The deadline has to be in the future.');
            }
            $open = (int) scalar("SELECT COUNT(*) FROM savings_goals
                                   WHERE phone = ? AND status = 'active'", [$phone], 0);
            if ($open >= 8) {
                throw new MoneyError('You can keep up to 8 goals open at a time.');
            }

            q('INSERT INTO savings_goals (phone, title, target_amount, deadline)
               VALUES (?,?,?,?)', [$phone, $title, (float) $target, $deadline]);
            audit($phone, 'savings_goal_created', $title);
            flash('ok', 'Goal "' . $title . '" created.');
            redirect('savings.php');
        }

        /* deposit */
        if ($action === 'deposit' || $action === 'withdraw') {
            require_fresh('savings_move');
            $goalId = (int) post('goal_id');
            $amount = post('amount');
            $pin    = post('pin');

            if (!valid_amount($amount) || (float) $amount < MIN_AMOUNT) {
                throw new MoneyError('Enter an amount of at least ' . money(MIN_AMOUNT) . '.');
            }
            if (!verify_pin($phone, $pin)) {
                $left = pin_tries_left($phone);
                throw new MoneyError('Wrong transaction PIN.'
                    . ($left > 0 ? " $left attempt(s) left." : ''));
            }

            $pdo = db();
            $pdo->beginTransaction();
            try {
                /* Read the goal inside the transaction and lock the row. Without
                   the lock, two withdrawals fired at the same moment would both
                   see the full balance and both succeed, which would create money
                   out of nothing. */
                $goal = one("SELECT * FROM savings_goals
                              WHERE id = ? AND phone = ? AND status = 'active'
                              FOR UPDATE", [$goalId, $phone]);
                if (!$goal) {
                    throw new MoneyError('That goal was not found.');
                }

                if ($action === 'deposit') {
                    move_money([
                        'from'        => $phone,
                        'to'          => null,
                        'to_ref'      => 'Savings: ' . $goal['title'],
                        'amount'      => (float) $amount,
                        'type'        => 'Savings',
                        'category'    => 'Savings',
                        'note'        => 'Added to goal ' . $goal['title'],
                        'skip_limits' => true,   // saving is not spending
                    ]);
                    q('UPDATE savings_goals SET saved_amount = saved_amount + ? WHERE id = ?',
                      [(float) $amount, $goalId]);

                    $now = (float) $goal['saved_amount'] + (float) $amount;
                    if ($now >= (float) $goal['target_amount']) {
                        notify($phone, 'Goal reached',
                               'You hit your target for "' . $goal['title'] . '".', 'gift');
                    }
                    $msg = money($amount) . ' moved into "' . $goal['title'] . '".';
                } else {
                    if ((float) $goal['saved_amount'] < (float) $amount) {
                        throw new MoneyError('That goal only holds '
                            . money($goal['saved_amount']) . '.');
                    }
                    move_money([
                        'from'        => null,
                        'from_ref'    => 'Savings: ' . $goal['title'],
                        'to'          => $phone,
                        'amount'      => (float) $amount,
                        'type'        => 'Savings Release',
                        'category'    => 'Savings',
                        'note'        => 'Taken back from goal ' . $goal['title'],
                        'skip_limits' => true,
                    ]);
                    q('UPDATE savings_goals SET saved_amount = saved_amount - ? WHERE id = ?',
                      [(float) $amount, $goalId]);
                    $msg = money($amount) . ' returned to your balance.';
                }
                $pdo->commit();
                flash('ok', $msg);
                redirect('savings.php');

            } catch (Throwable $err) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                throw $err;
            }
        }

        /* close */
        if ($action === 'close') {
            $goalId = (int) post('goal_id');
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $goal = one("SELECT * FROM savings_goals
                              WHERE id = ? AND phone = ? AND status = 'active'
                              FOR UPDATE", [$goalId, $phone]);
                if (!$goal) {
                    throw new MoneyError('That goal was not found.');
                }
                if ((float) $goal['saved_amount'] > 0) {
                    move_money([
                        'from'        => null,
                        'from_ref'    => 'Savings: ' . $goal['title'],
                        'to'          => $phone,
                        'amount'      => (float) $goal['saved_amount'],
                        'type'        => 'Savings Release',
                        'category'    => 'Savings',
                        'note'        => 'Goal closed: ' . $goal['title'],
                        'skip_limits' => true,
                    ]);
                }
                $done = (float) $goal['saved_amount'] >= (float) $goal['target_amount'];
                q('UPDATE savings_goals SET saved_amount = 0, status = ? WHERE id = ?',
                  [$done ? 'completed' : 'cancelled', $goalId]);
                $pdo->commit();
                flash('ok', 'Goal closed and ' . money($goal['saved_amount'])
                          . ' returned to your balance.');
                redirect('savings.php');
            } catch (Throwable $err) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                throw $err;
            }
        }

    } catch (MoneyError $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'That did not work. Please try again.';
    }
}

$goals    = all("SELECT * FROM savings_goals WHERE phone = ? AND status = 'active'
                 ORDER BY created_at DESC", [$phone]);
$archived = all("SELECT * FROM savings_goals WHERE phone = ? AND status <> 'active'
                 ORDER BY created_at DESC LIMIT 6", [$phone]);
$totalSaved = array_sum(array_map(static fn($g) => (float) $g['saved_amount'], $goals));

$pageTitle = 'Savings';
$activeNav = 'savings';
include('Front_end/front_savings.php');
