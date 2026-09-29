<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
$csv = 'statement.php?format=csv&from=' . urlencode($from) . '&to=' . urlencode($to) . '&type=' . urlencode($type);
?>
<main id="main" class="page">
  <div class="wrap">
    <div class="no-print">
      <?= page_head('Statement', 'Your transactions for any period. Print it or save it as a PDF.',
            ['history.php', 'History'],
            '<a class="btn btn-quiet" href="' . e($csv) . '">' . icon('download') . 'CSV</a>'
          . '<button class="btn js-only" type="button" onclick="window.print()">' . icon('print') . 'Print or save as PDF</button>', badge: ['file', 8]) ?>

      <form class="card rise mb-4" style="--n:1" method="get">
        <div class="row" style="align-items:flex-end">
          <div class="field m0" style="flex:1 1 150px"><label for="from">From</label>
            <input id="from" name="from" type="date" value="<?= e($from) ?>"></div>
          <div class="field m0" style="flex:1 1 150px"><label for="to">To</label>
            <input id="to" name="to" type="date" value="<?= e($to) ?>" max="<?= date('Y-m-d') ?>"></div>
          <button class="btn" type="submit">Show</button>
        </div>
      </form>
    </div>

    <article class="card reveal">
      <header class="between mb-4" style="align-items:flex-start;border-bottom:1px solid var(--line-strong);padding-bottom:var(--s4)">
        <div>
          <span class="eyebrow"><?= e(APP_NAME) ?></span>
          <h2 class="m0">Account statement</h2>
          <p class="small muted m0 mt-1"><?= e(date('j M Y', strtotime($from))) ?> to <?= e(date('j M Y', strtotime($to))) ?></p>
        </div>
        <p class="small m0" style="text-align:right">
          <strong><?= e($user['firstname'] . ' ' . $user['lastname']) ?></strong><br>
          <span class="mono"><?= e($phone) ?></span><br><?= e($user['email']) ?>
        </p>
      </header>

      <div class="grid cols-4 mb-4">
        <div><span class="label">Opening</span><span class="num" style="display:block;font-size:1.15rem"><?= e(money($opening)) ?></span></div>
        <div><span class="label">Money in</span><span class="num pos" style="display:block;font-size:1.15rem"><?= e(money($totalIn)) ?></span></div>
        <div><span class="label">Money out</span><span class="num neg" style="display:block;font-size:1.15rem"><?= e(money($totalOut + $totalFee)) ?></span></div>
        <div><span class="label">Closing</span><span class="num" style="display:block;font-size:1.15rem"><?= e(money($closing)) ?></span></div>
      </div>

      <?php if (!$rows): ?>
        <?= empty_state('file', 'Nothing in these dates', 'Try earlier dates, or come back after some money has moved.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <caption class="sr-only">Statement lines, oldest first</caption>
            <thead><tr>
              <th scope="col">Date</th><th scope="col">Details</th>
              <th scope="col" class="num">In</th><th scope="col" class="num">Out</th>
              <th scope="col" class="num">Charge</th><th scope="col" class="num">Balance</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $t):
              $out = trx_is_out($t, $phone);
              $after = $out ? $t['sender_balance_after'] : $t['receiver_balance_after']; ?>
              <tr>
                <td class="small nowrap"><?= e(date('j M Y', strtotime($t['transaction_time']))) ?><br>
                  <span class="muted"><?= e(date('g:i a', strtotime($t['transaction_time']))) ?></span></td>
                <td><?= e($t['transaction_type']) ?> · <span class="muted"><?= $out ? 'to' : 'from' ?></span>
                  <?= e(trx_counterparty($t, $phone)) ?><br>
                  <span class="mono small muted"><?= e($t['trx_id']) ?></span>
                  <?php if ($t['note']): ?><span class="small muted"> · <?= e($t['note']) ?></span><?php endif; ?></td>
                <td class="num pos"><?= $out ? '' : e(money($t['amount'], false)) ?></td>
                <td class="num neg"><?= $out ? e(money($t['amount'], false)) : '' ?></td>
                <td class="num"><?= (float) $t['fee'] > 0 ? e(money($t['fee'], false)) : '' ?></td>
                <td class="num"><?= $after !== null ? e(money($after, false)) : '' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr>
              <td colspan="2">Totals for the period</td>
              <td class="num pos"><?= e(money($totalIn, false)) ?></td>
              <td class="num neg"><?= e(money($totalOut, false)) ?></td>
              <td class="num"><?= e(money($totalFee, false)) ?></td>
              <td class="num"><?= e(money($closing, false)) ?></td>
            </tr></tfoot>
          </table>
        </div>
      <?php endif; ?>

      <?php if ($totalBack > 0): ?>
        <p class="small mt-4">Cashback in this period: <span class="num pos"><?= e(money($totalBack)) ?></span></p>
      <?php endif; ?>
      <p class="small muted" style="margin:var(--s5) 0 0;border-top:1px dashed var(--line);padding-top:var(--s3)">
        Generated <?= date('j M Y \a\t g:i a') ?> from the transaction ledger. Declined attempts are left out;
        they are listed in your history.</p>
    </article>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
