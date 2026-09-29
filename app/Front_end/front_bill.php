<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head($title, 'Flat ' . money(FEE_BILL_FLAT) . ' charge. We will stop you if you try to pay the same month twice.',
                  ['paybill.php', 'All bills'], '', 'Utility bill', badge: [$icon, 5]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('bill') ?>

      <div class="field">
        <label for="organization_id">Who sends you the bill?</label>
        <select id="organization_id" name="organization_id" required>
          <option value="">Pick your provider</option>
          <?php foreach ($organizations as $o): ?>
            <option value="<?= e($o['organization_id']) ?>"
              <?= post('organization_id') === $o['organization_id'] ? 'selected' : '' ?>><?= e($o['organization_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid cols-2">
        <div class="field">
          <label for="consumer_no"><?= e($consumerLabel) ?></label>
          <input id="consumer_no" name="consumer_no" required maxlength="40" autocomplete="off"
                 value="<?= e(post('consumer_no')) ?>" aria-describedby="consumerHint">
          <span class="hint" id="consumerHint">It's printed on the bill, top corner usually.</span>
        </div>
        <div class="field">
          <label for="month">For which month?</label>
          <select id="month" name="month" required>
            <?php foreach ($months as $i => $m): ?>
              <option value="<?= e($m) ?>" <?= (post('month') ?: $months[1]) === $m ? 'selected' : '' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="field">
        <label for="amount">Bill amount</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" value="<?= e(post('amount')) ?>"
                 data-charge="billSum" data-fee-flat="<?= FEE_BILL_FLAT ?>" data-balance="<?= $bal ?>">
        </div>
        <span class="hint">You have <?= e(money($bal)) ?>.</span>
      </div>

      <div class="sum" id="billSum" aria-live="polite"></div>
      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('check') ?>Pay this bill</button>
    </form>

    <section class="card reveal mt-5">
      <div class="card-h"><h2><?= icon('history') ?>Paid before</h2></div>
      <?php if (!$recent): ?>
        <p class="muted small m0">Nothing yet. Once you pay a <?= e(strtolower($title)) ?>,
          it is listed here with the month it covered.</p>
      <?php else: ?>
        <ul class="rows">
          <?php foreach ($recent as $r): ?>
            <li><a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>">
              <span class="ico"><?= icon($icon) ?></span>
              <span class="body"><span class="t"><?= e($r['organization_name']) ?></span>
                <span class="s"><?= e($r['consumer_no']) ?> · <?= e($r['month']) ?></span></span>
              <span class="amt"><?= e(money($r['amount'])) ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
