<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head(__('Cash in'), __('Give the cash to an agent, then confirm here. No charge.'), ['account.php', __('Home')], badge: ['cash-in', 2]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('cash_in') ?>

      <div class="field">
        <label for="agent_id"><?= __('Which agent are you at?') ?></label>
        <select id="agent_id" name="agent_id" required>
          <option value=""><?= __('Pick the agent') ?></option>
          <?php foreach ($agents as $a): ?>
            <option value="<?= e($a['agent_id']) ?>" <?= post('agent_id') === $a['agent_id'] ? 'selected' : '' ?>>
              <?= e($a['agent_id']) ?> · <?= e($a['phone_number']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="cash_in_amount"><?= __('Amount handed over') ?></label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="cash_in_amount" name="cash_in_amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" max="<?= ADD_MONEY_MAX ?>" value="<?= e(post('cash_in_amount')) ?>">
        </div>
        <?= amount_chips('cash_in_amount', [500, 1000, 2000, 5000]) ?>
        <span class="hint"><?= e(__('Your balance is {bal}.', ['bal' => money($bal)])) ?></span>
      </div>

      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('cash-in') ?><?= __('Cash it in') ?></button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
