<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Add money', 'Pull money in from your bank or card. Free, always.', ['account.php', 'Home'], badge: ['wallet', 2]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('add_money') ?>

      <div class="field">
        <label for="bank">From where?</label>
        <select id="bank" name="bank" required>
          <option value="">Pick your bank or card</option>
          <?php foreach (BANKS as $b): ?>
            <option value="<?= e($b) ?>" <?= post('bank') === $b ? 'selected' : '' ?>><?= e($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount">How much?</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" max="<?= ADD_MONEY_MAX ?>" value="<?= e(post('amount')) ?>">
        </div>
        <?= amount_chips('amount', [1000, 2000, 5000, 10000]) ?>
        <span class="hint">Up to <?= e(money(ADD_MONEY_MAX)) ?> per top up. Your balance is <?= e(money($bal)) ?>.</span>
      </div>

      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('wallet') ?>Top up my wallet</button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
