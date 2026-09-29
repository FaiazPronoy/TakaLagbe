<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Mobile recharge', 'Top up any number and get ' . CASHBACK_RECHARGE_PCT . '% back. Small, but it adds up.',
                  ['account.php', 'Home'], badge: ['phone', 6]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('recharge') ?>

      <div class="field">
        <label for="rechargePhoneNumber">Which number?</label>
        <input id="rechargePhoneNumber" name="rechargePhoneNumber" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX"
               value="<?= e(post('rechargePhoneNumber') ?: $phone) ?>" data-operator="opName">
        <span class="hint" id="opName" aria-live="polite"></span>
      </div>

      <fieldset>
        <legend class="legend">Connection</legend>
        <div class="choices">
          <label><input type="radio" name="connection" value="Prepaid" <?= post('connection') !== 'Postpaid' ? 'checked' : '' ?>> Prepaid</label>
          <label><input type="radio" name="connection" value="Postpaid" <?= post('connection') === 'Postpaid' ? 'checked' : '' ?>> Postpaid</label>
        </div>
      </fieldset>

      <div class="field">
        <label for="rechargeAmount">Amount</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="rechargeAmount" name="rechargeAmount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" max="<?= RECHARGE_MAX ?>" value="<?= e(post('rechargeAmount')) ?>"
                 data-charge="rechargeSum" data-cashback-pct="<?= CASHBACK_RECHARGE_PCT ?>" data-balance="<?= $bal ?>">
        </div>
        <?= amount_chips('rechargeAmount', [49, 99, 199, 299, 499]) ?>
      </div>

      <div class="sum" id="rechargeSum" aria-live="polite"></div>
      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('phone') ?>Recharge now</button>
    </form>

    <?php if ($recent): ?>
      <section class="card reveal mt-5">
        <div class="card-h"><h2><?= icon('history') ?>Recharged lately</h2></div>
        <ul class="rows">
          <?php foreach ($recent as $r): ?>
            <li><a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>">
              <span class="ico"><?= icon('phone') ?></span>
              <span class="body"><span class="t mono"><?= e($r['target_phone']) ?></span>
                <span class="s"><?= e($r['operator']) ?> · <?= e($r['connection']) ?> · <?= e(time_ago($r['created_at'])) ?></span></span>
              <span class="amt"><?= e(money($r['amount'])) ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
