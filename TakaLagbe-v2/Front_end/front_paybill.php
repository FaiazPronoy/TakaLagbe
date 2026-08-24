<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Pay bills</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <div class="row" style="margin-bottom:1.2rem">
    <div class="col"><div class="stat"><div class="k">Bills this month</div>
      <div class="v"><?= e(money($thisMonth, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Bills paid all time</div>
      <div class="v"><?= e(money($totalPaid, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Convenience charge</div>
      <div class="v"><?= e(money(FEE_BILL_FLAT, false)) ?></div></div></div>
  </div>

  <div class="tiles">
    <?php foreach (BILL_TYPES as $key => [$title, $icon, $label]): ?>
      <a class="tile" href="<?= e($key) ?>.php">
        <span class="ico"><?= $icon ?></span>
        <span class="name"><?= e(str_replace(' bill', '', $title)) ?></span>
      </a>
    <?php endforeach; ?>
    <a class="tile" href="education_fee.php">
      <span class="ico">&#127891;</span><span class="name">Education</span>
    </a>
  </div>

  <div class="card" style="margin-top:1.4rem">
    <div class="card-title">Recently paid</div>
    <?php if (!$recent): ?>
      <div class="empty"><div class="big">&#129534;</div>
        <p class="mb0">No bills paid yet.</p></div>
    <?php else: ?>
      <?php foreach ($recent as $r):
        $meta = BILL_TYPES[$r['billtype']] ?? ['Bill', "\u{1F4A1}", 'Number']; ?>
        <a class="entry" href="<?= e($r['billtype']) ?>.php" style="color:inherit">
          <span class="ico"><?= $meta[1] ?></span>
          <span class="body">
            <span class="t"><?= e($r['organization_name']) ?></span>
            <span class="s"><?= e($meta[2]) ?> <?= e($r['consumer_no']) ?>
              &middot; <?= e($r['month']) ?> &middot; <?= e(time_ago($r['payment_date'])) ?></span>
          </span>
          <span class="amt"><?= e(money($r['amount'])) ?></span>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
