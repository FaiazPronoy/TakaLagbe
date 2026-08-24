<?php
/* =====================================================================
 *  The money engine. Every taka that moves goes through move_money(),
 *  and nothing else writes to the `account` table.
 *
 *  The debit, the credit and the ledger row all happen in one database
 *  transaction, and the accounts are locked with SELECT ... FOR UPDATE
 *  before any balance is read.
 * =====================================================================*/

require_once __DIR__ . '/../config/helpers.php';

/** Thrown when a transfer is refused. The message is safe to show. */
class MoneyError extends RuntimeException {}

/** TKL + 9 hex characters, e.g. TKL8F2A9C31. Unique in the ledger. */
function new_trx_id(): string
{
    // the column is UNIQUE, so a repeat would be rejected by the database
    return 'TKL' . strtoupper(bin2hex(random_bytes(4))) . random_int(0, 9);
}

/** How much this user has already sent out today. */
function spent_today(string $phone): float
{
    return (float) scalar(
        "SELECT COALESCE(SUM(amount + fee), 0) FROM transactions
         WHERE transaction_from = ? AND status = 'success'
           AND DATE(transaction_time) = CURDATE()",
        [$phone], 0
    );
}

/**
 * Successful outgoing transfers in the last few minutes, used as a fraud brake.
 * Only successes count: a run of "insufficient balance" mistakes should not be
 * able to lock a user out of their own account.
 */
function recent_trx_count(string $phone): int
{
    return (int) scalar(
        "SELECT COUNT(*) FROM transactions
         WHERE transaction_from = ? AND status = 'success'
           AND transaction_time > (NOW() - INTERVAL ? MINUTE)",
        [$phone, VELOCITY_WINDOW_MIN], 0
    );
}

/**
 * Move money.
 *
 * @param array $o keys:
 *   from      string|null  phone to debit, or null when the money comes
 *                          from outside the system (a bank, an agent)
 *   to        string|null  phone to credit, or null when the money leaves
 *                          the system (a biller, a merchant, a mobile top-up)
 *   from_ref  string       what to write in the ledger when `from` is null
 *   to_ref    string       what to write in the ledger when `to` is null
 *   amount    float
 *   type      string       'Send Money', 'Cash Out', 'Bill Pay' ...
 *   category  string       one of TRX_CATEGORIES
 *   note      string|null
 *   fee_pct   float        charged on top of the amount, taken from `from`
 *   fee_flat  float
 *   cashback_pct float     credited back to `from` after the debit
 *   skip_limits bool       true for internal moves such as savings
 *
 * @return array the ledger row that was written
 * @throws MoneyError when the transfer is refused
 */
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
        throw new MoneyError('Please enter a valid amount.');
    }
    if ($from !== null && $to !== null && $from === $to) {
        throw new MoneyError('You cannot send money to your own account.');
    }

    $fee      = round($amount * $feePct / 100 + $feeFlat, 2);
    $cashback = round($amount * $cashbackPct / 100, 2);
    $debit    = round($amount + $fee, 2);

    $pdo = db();
    // a caller may already have a transaction open, for example a bill payment
    // that wants the transfer and the bill row to land together
    $ownTransaction = !$pdo->inTransaction();
    if ($ownTransaction) {
        $pdo->beginTransaction();
    }

    try {
        // lock both accounts, in phone number order so two transfers going
        // opposite ways cannot end up waiting on each other
        $lock = array_values(array_unique(array_filter([$from, $to])));
        sort($lock);
        $balances = [];
        foreach ($lock as $phone) {
            $row = one('SELECT phone, balance FROM account WHERE phone = ? FOR UPDATE', [$phone]);
            if ($row === null) {
                throw new MoneyError('Account ' . mask_phone($phone) . ' was not found.');
            }
            $balances[$phone] = (float) $row['balance'];
        }

        /* --- checks that need the lock to be honest ------------------ */
        if ($from !== null) {
            if ($balances[$from] < $debit) {
                throw new MoneyError(
                    'Insufficient balance. You need ' . money($debit) .
                    ' and you have ' . money($balances[$from]) . '.'
                );
            }
            if (!$skipLimits) {
                $limit = (float) scalar(
                    'SELECT daily_limit FROM user WHERE phone = ?', [$from], DAILY_LIMIT_DEFAULT
                );
                if (spent_today($from) + $debit > $limit) {
                    throw new MoneyError(
                        'This would cross your daily limit of ' . money($limit) .
                        '. You have already moved ' . money(spent_today($from)) . ' today.'
                    );
                }
                if (recent_trx_count($from) >= VELOCITY_MAX_TRX) {
                    throw new MoneyError(
                        'Too many transactions in a short time. Please wait a few minutes.'
                    );
                }
            }
        }

        /* --- apply ---------------------------------------------------- */
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

        /* --- ledger --------------------------------------------------- */
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
        if ($ownTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
            // only safe once the rollback is done, and only when the
            // transaction was ours to roll back
            if ($err instanceof MoneyError) {
                log_refusal($fromRef, $toRef, $type, $category,
                            $amount, $fee, $err->getMessage());
            }
        }
        throw $err;
    }
}

/** Writes a refused transfer to the ledger so it shows up in the history. */
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
        // never let bookkeeping hide the real error
    }
}

/** Notifications and the audit trail, written after a transfer lands. */
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
