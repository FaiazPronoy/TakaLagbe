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
        if ($action === 'create') {
            $title    = mb_substr(post('title'), 0, 60);
            $target   = post('target_amount');
            $deadline = post('deadline') ?: null;

            if ($title === '') {
                throw new MoneyError(__('Give the goal a name.'));
            }
            if (!valid_amount($target) || (float) $target < MIN_AMOUNT) {
                throw new MoneyError(__('Set a target of at least {amount}.', ['amount' => money(MIN_AMOUNT)]));
            }
            if ($deadline !== null && strtotime($deadline) < strtotime('today')) {
                throw new MoneyError(__('The deadline has to be in the future.'));
            }
            $open = (int) scalar("SELECT COUNT(*) FROM savings_goals
                                   WHERE phone = ? AND status = 'active'", [$phone], 0);
            if ($open >= 8) {
                throw new MoneyError(__('You can keep up to 8 goals open at a time.'));
            }

            q('INSERT INTO savings_goals (phone, title, target_amount, deadline)
               VALUES (?,?,?,?)', [$phone, $title, (float) $target, $deadline]);
            audit($phone, 'savings_goal_created', $title);
            flash('ok', __('Goal "{goal}" created.', ['goal' => $title]));
            redirect('savings.php');
        }

        if ($action === 'deposit' || $action === 'withdraw') {
            require_fresh('savings_move');
            $goalId = (int) post('goal_id');
            $amount = post('amount');
            $pin    = post('pin');

            if (!valid_amount($amount) || (float) $amount < MIN_AMOUNT) {
                throw new MoneyError(__('Enter an amount of at least {amount}.', ['amount' => money(MIN_AMOUNT)]));
            }
            if (!verify_pin($phone, $pin)) {
                $left = pin_tries_left($phone);
                throw new MoneyError(__('Wrong transaction PIN.')
                    . ($left > 0 ? ' ' . __('{n} attempt(s) left.', ['n' => $left]) : ''));
            }

            $pdo = db();
            $pdo->beginTransaction();
            try {
                $goal = one("SELECT * FROM savings_goals
                              WHERE id = ? AND phone = ? AND status = 'active'
                              FOR UPDATE", [$goalId, $phone]);
                if (!$goal) {
                    throw new MoneyError(__('That goal was not found.'));
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
                        'skip_limits' => true,
                    ]);
                    q('UPDATE savings_goals SET saved_amount = saved_amount + ? WHERE id = ?',
                      [(float) $amount, $goalId]);

                    $now = (float) $goal['saved_amount'] + (float) $amount;
                    if ($now >= (float) $goal['target_amount']) {
                        notify($phone, 'Goal reached',
                               'You hit your target for "' . $goal['title'] . '".', 'gift');
                    }
                    $msg = __('{amount} moved into "{goal}".', ['amount' => money($amount), 'goal' => $goal['title']]);
                } else {
                    if ((float) $goal['saved_amount'] < (float) $amount) {
                        throw new MoneyError(__('That goal only holds {amount}.', ['amount' => money($goal['saved_amount'])]));
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
                    $msg = __('{amount} returned to your balance.', ['amount' => money($amount)]);
                }
                $pdo->commit();
                flash('ok', $msg);
                redirect('savings.php');

            } catch (Throwable $err) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                throw $err;
            }
        }

        if ($action === 'close') {
            $goalId = (int) post('goal_id');
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $goal = one("SELECT * FROM savings_goals
                              WHERE id = ? AND phone = ? AND status = 'active'
                              FOR UPDATE", [$goalId, $phone]);
                if (!$goal) {
                    throw new MoneyError(__('That goal was not found.'));
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
                flash('ok', __('Goal closed and {amount} returned to your balance.', ['amount' => money($goal['saved_amount'])]));
                redirect('savings.php');
            } catch (Throwable $err) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                throw $err;
            }
        }

    } catch (MoneyError $e) {
        record_refusal($e);
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = __('That did not work. Please try again.');
    }
}

$goals    = all("SELECT * FROM savings_goals WHERE phone = ? AND status = 'active'
                 ORDER BY created_at DESC", [$phone]);
$archived = all("SELECT * FROM savings_goals WHERE phone = ? AND status <> 'active'
                 ORDER BY created_at DESC LIMIT 6", [$phone]);
$totalSaved = array_sum(array_map(static fn($g) => (float) $g['saved_amount'], $goals));

$pageTitle = __('Savings');
$activeNav = 'savings';
include('views/savings.php');
