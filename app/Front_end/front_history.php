<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
$filtered = $search !== '' || $type !== '' || $direction !== '' || $from !== '' || $to !== '';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head(__('History'), __('Every taka in and out. Even the ones that bounced, and why.'), null,
          '<a class="btn btn-quiet" href="statement.php?' . e($queryFor([])) . '">' . icon('file') . e(__('Statement')) . '</a>'
        . '<a class="btn btn-quiet" href="statement.php?format=csv&amp;' . e($queryFor([])) . '">' . icon('download') . 'CSV</a>', badge: ['history', 1]) ?>

    <form class="card rise mb-4" style="--n:1" method="get" role="search" aria-label="<?= e(__('Filter transactions')) ?>">
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,160px),1fr));align-items:end">
        <div class="field m0 span-2">
          <label for="q"><?= __('Search') ?></label>
          <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="<?= e(__('Name, number, note, reference')) ?>">
        </div>
        <div class="field m0">
          <label for="type"><?= __('Type') ?></label>
          <select id="type" name="type">
            <option value=""><?= __('Any type') ?></option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(__($t)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field m0">
          <label for="dir"><?= __('Direction') ?></label>
          <select id="dir" name="dir">
            <option value=""><?= __('In and out') ?></option>
            <option value="in"  <?= $direction === 'in'  ? 'selected' : '' ?>><?= __('Money in') ?></option>
            <option value="out" <?= $direction === 'out' ? 'selected' : '' ?>><?= __('Money out') ?></option>
          </select>
        </div>
        <div class="field m0">
          <label for="from"><?= __('From') ?></label>
          <input id="from" name="from" type="date" value="<?= e($from) ?>">
        </div>
        <div class="field m0">
          <label for="to"><?= __('To') ?></label>
          <input id="to" name="to" type="date" value="<?= e($to) ?>">
        </div>
        <div class="row">
          <button class="btn" type="submit"><?= icon('search') ?><?= __('Filter') ?></button>
          <?php if ($filtered): ?><a class="btn btn-quiet" href="history.php"><?= __('Clear') ?></a><?php endif; ?>
        </div>
      </div>
    </form>

    <div class="grid cols-4 mb-4">
      <div class="stat reveal"><span class="label"><?= icon('list') ?><?= __('Found') ?></span><span class="v"><?= number_format($total) ?></span></div>
      <div class="stat reveal" style="--n:1"><span class="label"><?= __('Money in') ?></span>
        <span class="v pos"><small>৳</small><?= e(money($sumIn, false)) ?></span></div>
      <div class="stat reveal" style="--n:2"><span class="label"><?= __('Money out') ?></span>
        <span class="v neg"><small>৳</small><?= e(money($sumOut, false)) ?></span></div>
      <div class="stat reveal" style="--n:3"><span class="label"><?= __('Net') ?></span>
        <span class="v"><small>৳</small><?= ($sumIn - $sumOut < 0 ? '−' : '') . e(money(abs($sumIn - $sumOut), false)) ?></span></div>
    </div>

    <section class="card reveal" aria-label="<?= e(__('Transactions')) ?>">
      <?php if (!$rows): ?>
        <?= $filtered
            ? empty_state('search', __('Nothing matches that'), __('Loosen the filters a bit, or clear them and see everything.'),
                          '<a class="btn btn-quiet" href="history.php">' . e(__('Clear filters')) . '</a>')
            : empty_state('history', __('Quiet in here'), __('Send, pay or top up once and it shows up here.'),
                          '<a class="btn" href="add_money.php">' . e(__('Add money')) . '</a>') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <caption class="sr-only"><?= __('Transactions, newest first') ?></caption>
            <thead>
              <tr><th scope="col"><?= __('What') ?></th><th scope="col"><?= __('With') ?></th><th scope="col"><?= __('When') ?></th>
                  <th scope="col" class="num"><?= __('Amount') ?></th><th scope="col"><?= __('Status') ?></th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $t):
              $out   = trx_is_out($t, $phone);
              $delta = $out ? (float) $t['amount'] + (float) $t['fee'] : (float) $t['amount']; ?>
              <tr>
                <td>
                  <a class="row" style="gap:var(--s2);flex-wrap:nowrap" href="receipt.php?id=<?= e(urlencode($t['trx_id'])) ?>">
                    <span class="muted"><?= trx_icon($t['transaction_type']) ?></span>
                    <span><?= e(__($t['transaction_type'])) ?><br>
                      <span class="mono small muted"><?= e($t['trx_id']) ?></span></span>
                  </a>
                </td>
                <td><?= e(trx_counterparty($t, $phone)) ?>
                  <?php if ($t['note']): ?><br><span class="small muted"><?= e(__notice($t['note'])) ?></span><?php endif; ?></td>
                <td class="small nowrap"><?= e(short_date($t['transaction_time'])) ?></td>
                <td class="num <?= $t['status'] !== 'success' ? 'muted' : ($out ? 'neg' : 'pos') ?>">
                  <?= $out ? '−' : '+' ?> <?= e(money($delta, false)) ?>
                  <?php if ((float) $t['fee'] > 0): ?><br><span class="small muted"><?= e(__('incl. {fee} charge', ['fee' => money($t['fee'], false)])) ?></span><?php endif; ?>
                </td>
                <td><span class="badge <?= $t['status'] === 'success' ? 'ok' : 'bad' ?>">
                  <?= e($t['status'] === 'success' ? __('done') : __('declined')) ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="<?= e(__('Pages')) ?>">
        <?php if ($page > 1): ?>
          <a href="?<?= e($queryFor(['page' => $page - 1])) ?>" rel="prev"><?= __('Previous') ?></a>
        <?php else: ?><span class="off"><?= __('Previous') ?></span><?php endif; ?>
        <?php
        $start = max(1, min($page - 2, $pages - 4));
        for ($p = $start; $p <= min($pages, $start + 4); $p++): ?>
          <?php if ($p === $page): ?><span aria-current="page"><?= $p ?></span>
          <?php else: ?><a href="?<?= e($queryFor(['page' => $p])) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $pages): ?>
          <a href="?<?= e($queryFor(['page' => $page + 1])) ?>" rel="next"><?= __('Next') ?></a>
        <?php else: ?><span class="off"><?= __('Next') ?></span><?php endif; ?>
      </nav>
      <p class="center small muted"><?= e(__('Page {page} of {pages}', ['page' => $page, 'pages' => $pages])) ?></p>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
