<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Make a payment</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>

    <div class="field">
      <label for="merchant_id">Merchant</label>
      <select id="merchant_id" name="merchant_id" required
              onchange="var o=this.options[this.selectedIndex];
                        var a=document.getElementById('amount');
                        a.dataset.cashbackPct=o.dataset.cb||0;
                        a.dispatchEvent(new Event('input'));">
        <option value="">Choose where you are paying</option>
        <?php foreach ($merchants as $m): ?>
          <option value="<?= e($m['merchant_id']) ?>" data-cb="<?= e($m['cashback_pct']) ?>">
            <?= e($m['merchant_name']) ?> &middot; <?= e($m['category']) ?>
            <?php if ((float) $m['cashback_pct'] > 0): ?>
              (<?= rtrim(rtrim(number_format((float) $m['cashback_pct'], 2), '0'), '.') ?>% back)
            <?php endif; ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="hint">Balance <?= e(money($bal)) ?></div>
    </div>

    <div class="field">
      <label for="amount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="amount" name="amount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" placeholder="0.00"
               data-charge="chargeBox" data-cashback-pct="0" data-balance="<?= $bal ?>">
      </div>
    </div>

    <div class="field">
      <label for="reference">Bill or order reference <span class="muted tiny">(optional)</span></label>
      <input id="reference" name="reference" maxlength="60" placeholder="Invoice number, table number, order id">
    </div>

    <div id="chargeBox" class="card card-pad-sm" style="background:var(--surface-2);margin-bottom:1rem"></div>

    <div class="field">
      <label>Transaction PIN</label>
      <div class="pin" data-pin="pinValue">
        <?php for ($i = 0; $i < 6; $i++): ?>
          <input type="password" inputmode="numeric" maxlength="1" aria-label="PIN digit <?= $i + 1 ?>">
        <?php endfor; ?>
      </div>
      <input type="hidden" name="pin" id="pinValue" required pattern="\d{6}">
    </div>

    <button class="btn btn-primary btn-block" type="submit">Pay now</button>
  </form>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
