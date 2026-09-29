<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head(__('Add money'), __('Pull money in from your bank or card. Free, always.'), ['account.php', __('Home')], badge: ['wallet', 2]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('add_money') ?>

      <div class="field">
        <label for="bank"><?= __('From where?') ?></label>
        <select id="bank" name="bank" required>
          <option value=""><?= __('Pick your bank or card') ?></option>
          <?php foreach (BANKS as $b): ?>
            <option value="<?= e($b) ?>" <?= post('bank') === $b ? 'selected' : '' ?>><?= e($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount"><?= __('How much?') ?></label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" max="<?= ADD_MONEY_MAX ?>" value="<?= e(post('amount')) ?>">
        </div>
        <?= amount_chips('amount', [1000, 2000, 5000, 10000]) ?>
        <span class="hint"><?= e(__('Up to {max} per top up. Your balance is {bal}.', ['max' => money(ADD_MONEY_MAX), 'bal' => money($bal)])) ?></span>
      </div>

      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('wallet') ?><?= __('Top up my wallet') ?></button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
