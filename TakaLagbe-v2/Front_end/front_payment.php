<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Pay a shop', 'Pay at the counter or online. Some shops throw in cashback.',
                  ['account.php', 'Home'], badge: ['bag', 7]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('payment') ?>

      <div class="field">
        <label for="merchant_id">Where are you paying?</label>
        <select id="merchant_id" name="merchant_id" required data-cashback-from="amount">
          <option value="">Pick the shop</option>
          <?php foreach ($merchants as $m):
            $cb = rtrim(rtrim(number_format((float) $m['cashback_pct'], 2), '0'), '.'); ?>
            <option value="<?= e($m['merchant_id']) ?>" data-cb="<?= e($m['cashback_pct']) ?>"
                    <?= post('merchant_id') === $m['merchant_id'] ? 'selected' : '' ?>>
              <?= e($m['merchant_name']) ?> · <?= e($m['category']) ?><?= (float) $cb > 0 ? ' · ' . $cb . '% back' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount">Amount</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" value="<?= e(post('amount')) ?>"
                 data-charge="paySum" data-cashback-pct="0" data-balance="<?= $bal ?>">
        </div>
        <span class="hint">You have <?= e(money($bal)) ?>.</span>
      </div>

      <div class="field">
        <label for="reference">Order or table no. <span class="muted">(optional)</span></label>
        <input id="reference" name="reference" maxlength="60" value="<?= e(post('reference')) ?>">
      </div>

      <div class="sum" id="paySum" aria-live="polite"></div>
      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('bag') ?>Pay the shop</button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
