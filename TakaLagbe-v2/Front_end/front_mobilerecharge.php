<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Mobile recharge</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>

    <div class="field">
      <label for="rechargePhoneNumber">Mobile number</label>
      <input id="rechargePhoneNumber" name="rechargePhoneNumber" type="tel" inputmode="numeric"
             maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX"
             data-operator="opName" autofocus>
      <div class="hint" id="opName"></div>
      <button type="button" class="chip" style="margin-top:.5rem"
              onclick="var i=document.getElementById('rechargePhoneNumber');
                       i.value='<?= e($phone) ?>';i.dispatchEvent(new Event('input'));">
        Use my own number
      </button>
    </div>

    <fieldset>
      <legend>Connection</legend>
      <div class="radio-row">
        <label><input type="radio" name="connection" value="Prepaid" checked> Prepaid</label>
        <label><input type="radio" name="connection" value="Postpaid"> Postpaid</label>
      </div>
    </fieldset>

    <div class="field">
      <label for="rechargeAmount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="rechargeAmount" name="rechargeAmount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" max="<?= RECHARGE_MAX ?>" placeholder="0.00"
               data-charge="chargeBox" data-cashback-pct="<?= CASHBACK_RECHARGE_PCT ?>"
               data-balance="<?= $bal ?>">
      </div>
      <div class="chips">
        <?php foreach ([19, 29, 49, 99, 199, 349] as $v): ?>
          <button type="button" class="chip" data-chip-target="rechargeAmount" data-value="<?= $v ?>">
            <?= CURRENCY_SIGN ?> <?= $v ?>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="hint">
        You get <?= CASHBACK_RECHARGE_PCT ?> percent cashback on every recharge.
        Balance <?= e(money($bal)) ?>.
      </div>
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

    <button class="btn btn-primary btn-block" type="submit">Recharge now</button>
  </form>

  <?php if ($recent): ?>
    <div class="card" style="margin-top:1rem">
      <div class="card-title">Recent recharges</div>
      <?php foreach ($recent as $r): ?>
        <div class="entry">
          <span class="ico">&#128241;</span>
          <span class="body">
            <span class="t"><?= e($r['target_phone']) ?></span>
            <span class="s"><?= e($r['operator']) ?> &middot; <?= e($r['connection']) ?>
              &middot; <?= e(time_ago($r['created_at'])) ?></span>
          </span>
          <span class="amt"><?= e(money($r['amount'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
