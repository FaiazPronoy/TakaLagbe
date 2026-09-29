<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Pay a bill', 'Current, gas, water, wifi, tuition. Sorted in a minute, receipt included.',
                  ['account.php', 'Home'], badge: ['receipt', 5]) ?>

    <div class="grid cols-3 mb-5">
      <div class="stat rise" style="--n:1"><span class="label"><?= icon('calendar') ?>This month</span>
        <span class="v"><small>৳</small><?= e(money($thisMonth, false)) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label"><?= icon('receipt') ?>All time</span>
        <span class="v"><small>৳</small><?= e(money($totalPaid, false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label"><?= icon('info') ?>Charge per bill</span>
        <span class="v"><small>৳</small><?= e(money(FEE_BILL_FLAT, false)) ?></span></div>
    </div>

    <div class="actions">
      <?php $i = 0; foreach (BILL_TYPES as $key => [$title, $ico]): ?>
        <a class="action lift reveal" style="--n:<?= $i++ ?>" href="<?= e($key) ?>.php">
          <span class="disc"><?= icon($ico) ?></span><?= e(str_replace(' bill', '', $title)) ?></a>
      <?php endforeach; ?>
      <a class="action lift reveal" style="--n:4" href="education_fee.php">
        <span class="disc"><?= icon('cap') ?></span>Tuition fee</a>
    </div>

    <section class="card reveal mt-6">
      <div class="card-h"><h2><?= icon('history') ?>Paid lately</h2></div>
      <?php if (!$recent): ?>
        <?= empty_state('receipt', 'No bills paid yet',
              'Pick one above. Pay it once and it lives here, so next month is a few taps.') ?>
      <?php else: ?>
        <ul class="rows">
          <?php foreach ($recent as $r):
            $meta = BILL_TYPES[$r['billtype']] ?? ['Bill', 'receipt', 'Number']; ?>
            <li><a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>">
              <span class="ico"><?= icon($meta[1]) ?></span>
              <span class="body"><span class="t"><?= e($r['organization_name']) ?></span>
                <span class="s"><?= e($r['consumer_no']) ?> · <?= e($r['month']) ?> · paid <?= e(time_ago($r['payment_date'])) ?></span></span>
              <span class="amt"><?= e(money($r['amount'])) ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
