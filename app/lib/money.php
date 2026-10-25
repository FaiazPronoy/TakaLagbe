<?php

require_once __DIR__ . '/../config/helpers.php';

class MoneyError extends RuntimeException
{
    public ?array $attempt = null;
}

function new_trx_id(): string
{
    return 'TKL' . strtoupper(bin2hex(random_bytes(4))) . random_int(0, 9);
}

function spent_today(string $phone): float
{
    return (float) scalar(
        "SELECT COALESCE(SUM(amount + fee), 0) FROM transactions
         WHERE transaction_from = ? AND status = 'success' AND transaction_type <> 'Savings'
           AND DATE(transaction_time) = CURDATE()",
        [$phone], 0
    );
}

function recent_trx_count(string $phone): int
{
    return (int) scalar(
        "SELECT COUNT(*) FROM transactions
         WHERE transaction_from = ? AND status = 'success' AND transaction_type <> 'Savings'
           AND transaction_time > (NOW() - INTERVAL ? MINUTE)",
        [$phone, VELOCITY_WINDOW_MIN], 0
    );
}

function move_money(array $o): array
{
    $from        = $o['from']         ?? null;
    $to          = $o['to']           ?? null;
    $fromRef     = $o['from_ref']     ?? ($from ?? 'SYSTEM');
    $toRef       = $o['to_ref']       ?? ($to ?? 'SYSTEM');
    $amount      = round((float) ($o['amount'] ?? 0), 2);
    $type        = $o['type']         ?? 'Transfer';
    $category    = $o['category']     ?? 'Other';
    $note        = $o['note']         ?? null;
    $feePct      = (float) ($o['fee_pct']  ?? 0);
    $feeFlat     = (float) ($o['fee_flat'] ?? 0);
    $cashbackPct = (float) ($o['cashback_pct'] ?? 0);
    $skipLimits  = (bool)  ($o['skip_limits'] ?? false);

    if ($amount <= 0) {
        throw new MoneyError(__('Please enter a valid amount.'));
    }
    if ($from !== null && $to !== null && $from === $to) {
        throw new MoneyError(__('You cannot send money to your own account.'));
    }

    $fee      = round($amount * $feePct / 100 + $feeFlat, 2);
    $cashback = round($amount * $cashbackPct / 100, 2);
    $debit    = round($amount + $fee, 2);

    $pdo = db();
    $ownTransaction = !$pdo->inTransaction();
    if ($ownTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $lock = array_values(array_unique(array_filter([$from, $to])));
        sort($lock);
        $balances = [];
        foreach ($lock as $phone) {
            $row = one('SELECT phone, balance FROM account WHERE phone = ? FOR UPDATE', [$phone]);
            if ($row === null) {
                throw new MoneyError(__('Account {phone} was not found.', ['phone' => mask_phone($phone)]));
            }
            $balances[$phone] = (float) $row['balance'];
        }

        if ($from !== null) {
            if ($balances[$from] < $debit) {
                throw new MoneyError(
                    __('Insufficient balance. You need {need} and you have {have}.',
                       ['need' => money($debit), 'have' => money($balances[$from])])
                );
            }
            if (!$skipLimits) {
                $limit = (float) scalar(
                    'SELECT daily_limit FROM user WHERE phone = ?', [$from], DAILY_LIMIT_DEFAULT
                );
                if (spent_today($from) + $debit > $limit) {
                    throw new MoneyError(
                        __('This would cross your daily limit of {limit}. You have already moved {moved} today.',
                           ['limit' => money($limit), 'moved' => money(spent_today($from))])
                    );
                }
                if (recent_trx_count($from) >= VELOCITY_MAX_TRX) {
                    throw new MoneyError(
                        __('Too many transactions in a short time. Please wait a few minutes.')
                    );
                }
            }
        }

        $senderAfter = $receiverAfter = null;

        if ($from !== null) {
            q('UPDATE account SET balance = balance - ? WHERE phone = ?', [$debit, $from]);
            $senderAfter = round($balances[$from] - $debit, 2);
        }
        if ($to !== null) {
            q('UPDATE account SET balance = balance + ? WHERE phone = ?', [$amount, $to]);
            $receiverAfter = round($balances[$to] + $amount, 2);
        }
        if ($cashback > 0 && $from !== null) {
            q('UPDATE account SET balance = balance + ? WHERE phone = ?', [$cashback, $from]);
            $senderAfter = round($senderAfter + $cashback, 2);
        }

        $trxId = new_trx_id();
        q('INSERT INTO transactions
             (trx_id, transaction_from, transaction_to, transaction_type,
              category, amount, fee, cashback,
              sender_balance_after, receiver_balance_after, note, status)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
          [$trxId, mb_substr($fromRef, 0, 120), mb_substr($toRef, 0, 120),
           $type, $category, $amount, $fee, $cashback,
           $senderAfter, $receiverAfter, $note === null ? null : mb_substr($note, 0, 120),
           'success']);

        if ($ownTransaction) {
            $pdo->commit();
        }

        $row = [
            'trx_id'   => $trxId,
            'from'     => $fromRef,
            'to'       => $toRef,
            'type'     => $type,
            'category' => $category,
            'amount'   => $amount,
            'fee'      => $fee,
            'cashback' => $cashback,
            'sender_balance_after'   => $senderAfter,
            'receiver_balance_after' => $receiverAfter,
        ];

        after_transfer($row, $from, $to, $note);
        return $row;

    } catch (Throwable $err) {
        if ($err instanceof MoneyError) {
            $err->attempt = [$fromRef, $toRef, $type, $category, $amount, $fee];
        }
        if ($ownTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
            if ($err instanceof MoneyError) {
                record_refusal($err);
            }
        }
        throw $err;
    }
}

function record_refusal(MoneyError $e): void
{
    if ($e->attempt !== null) {
        log_refusal(...array_merge($e->attempt, [$e->getMessage()]));
        $e->attempt = null;
    }
}

function log_refusal(string $from, string $to, string $type, string $category,
                     float $amount, float $fee, string $reason): void
{
    try {
        q('INSERT INTO transactions
             (trx_id, transaction_from, transaction_to, transaction_type,
              category, amount, fee, note, status)
           VALUES (?,?,?,?,?,?,?,?,?)',
          ['F' . strtoupper(bin2hex(random_bytes(5))),
           mb_substr($from, 0, 120), mb_substr($to, 0, 120),
           $type, $category, $amount, $fee, mb_substr($reason, 0, 120), 'failed']);
    } catch (Throwable $ignore) {
    }
}

function after_transfer(array $row, ?string $from, ?string $to, ?string $note): void
{
    $amount = money($row['amount']);

    if ($to !== null) {
        $who   = $from !== null ? name_of($from) : $row['from'];
        $title = $from !== null ? 'Money received' : $row['type'];
        notify($to, $title,
            "You received $amount from $who." . ($note ? " Note: $note" : ''),
            'in', $row['amount']);
    }
    if ($from !== null) {
        $who = $to !== null ? name_of($to) : $row['to'];
        notify($from, $row['type'],
            "$amount sent to $who. Reference " . $row['trx_id'] . '.',
            'out', $row['amount']);

        if ($row['cashback'] > 0) {
            notify($from, 'Cashback earned',
                money($row['cashback']) . ' cashback was added to your balance.',
                'gift', $row['cashback']);
        }
    }
    audit($from ?? $row['from'], strtolower(str_replace(' ', '_', $row['type'])),
          $row['trx_id'] . ' ' . $amount . ' to ' . $row['to']);
}
