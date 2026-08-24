<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Transaction history</h1>
    <div style="display:flex;gap:.4rem">
      <a class="btn btn-ghost btn-sm" href="statement.php?<?= e($queryFor([])) ?>">Statement</a>
      <a class="btn btn-primary btn-sm" href="statement.php?format=csv&amp;<?= e($queryFor([])) ?>">
        Download CSV
      </a>
    </div>
  </div>

  <!-- --------------------------------------------------- filters -->
  <form class="card" method="get" style="margin-bottom:1rem">
    <div class="row">
      <div class="col field mb0" style="flex:2 1 240px">
        <label for="q">Search</label>
        <input id="q" name="q" value="<?= e($search) ?>"
               placeholder="Transaction ID, number, note or type">
      </div>
      <div class="col field mb0">
        <label for="type">Type</label>
        <select id="type" name="type">
          <option value="">All types</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col field mb0">
        <label for="dir">Direction</label>
        <select id="dir" name="dir">
          <option value="">Both ways</option>
          <option value="in"  <?= $direction === 'in'  ? 'selected' : '' ?>>Money in</option>
          <option value="out" <?= $direction === 'out' ? 'selected' : '' ?>>Money out</option>
        </select>
      </div>
    </div>
    <div class="row" style="margin-top:.8rem;align-items:flex-end">
      <div class="col field mb0">
        <label for="from">From date</label>
        <input id="from" name="from" type="date" value="<?= e($from) ?>">
      </div>
      <div class="col field mb0">
        <label for="to">To date</label>
        <input id="to" name="to" type="date" value="<?= e($to) ?>">
      </div>
      <div class="col" style="display:flex;gap:.5rem;flex:0 1 auto">
        <button class="btn btn-primary" type="submit">Apply</button>
        <a class="btn btn-ghost" href="history.php">Clear</a>
      </div>
    </div>
  </form>

  <!-- ---------------------------------------------------- summary -->
  <div class="row" style="margin-bottom:1rem">
    <div class="col"><div class="stat"><div class="k">Transactions found</div>
      <div class="v"><?= number_format($total) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Money in</div>
      <div class="v pos"><?= e(money($sumIn, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Money out</div>
      <div class="v neg"><?= e(money($sumOut, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Net</div>
      <div class="v <?= $sumIn - $sumOut >= 0 ? 'pos' : 'neg' ?>">
        <?= e(money($sumIn - $sumOut, false)) ?></div></div></div>
  </div>

  <!-- ------------------------------------------------------ table -->
  <div class="card">
    <?php if (!$rows): ?>
      <div class="empty">
        <div class="big">&#128269;</div>
        <p class="mb0">Nothing matches those filters.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Type</th><th>Details</th><th>Reference</th>
              <th>When</th><th class="num">Amount</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $t):
            $out   = trx_is_out($t, $phone);
            $delta = $out ? ((float) $t['amount'] + (float) $t['fee']) : (float) $t['amount']; ?>
            <tr onclick="location.href='receipt.php?id=<?= e($t['trx_id']) ?>'" style="cursor:pointer">
              <td><?= trx_icon($t['transaction_type']) ?> <?= e($t['transaction_type']) ?></td>
              <td>
                <?= e(trx_counterparty($t, $phone)) ?>
                <?php if ($t['note']): ?>
                  <div class="tiny muted"><?= e($t['note']) ?></div>
                <?php endif; ?>
              </td>
              <td class="mono tiny"><?= e($t['trx_id']) ?></td>
              <td class="tiny"><?= e(short_date($t['transaction_time'])) ?></td>
              <td class="num <?= $out ? 'neg' : 'pos' ?>">
                <?= $out ? '-' : '+' ?> <?= e(money($delta, false)) ?>
                <?php if ((float) $t['fee'] > 0): ?>
                  <div class="tiny muted">incl. <?= e(money($t['fee'], false)) ?> charge</div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?= $t['status'] === 'success' ? 'ok'
                      : ($t['status'] === 'failed' ? 'fail' : 'pending') ?>">
                  <?= e($t['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- ------------------------------------------------- pagination -->
  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php if ($page > 1): ?>
        <a href="?<?= e($queryFor(['page' => $page - 1])) ?>">Previous</a>
      <?php else: ?><span class="off">Previous</span><?php endif; ?>

      <?php
      $start = max(1, $page - 2);
      $end   = min($pages, $start + 4);
      $start = max(1, $end - 4);
      for ($p = $start; $p <= $end; $p++):
        if ($p === $page): ?>
          <span class="on"><?= $p ?></span>
        <?php else: ?>
          <a href="?<?= e($queryFor(['page' => $p])) ?>"><?= $p ?></a>
        <?php endif;
      endfor; ?>

      <?php if ($page < $pages): ?>
        <a href="?<?= e($queryFor(['page' => $page + 1])) ?>">Next</a>
      <?php else: ?><span class="off">Next</span><?php endif; ?>
    </div>
    <p class="center tiny muted">Page <?= $page ?> of <?= $pages ?></p>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
