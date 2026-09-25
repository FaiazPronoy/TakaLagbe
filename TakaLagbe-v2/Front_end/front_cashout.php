<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Cash out', 'Collect notes from an agent. The charge is ' . FEE_CASH_OUT_PCT . ' percent, the usual market rate.',
                  ['account.php', 'Home']) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('cash_out') ?>

      <div class="field">
        <label for="agent_id">Agent point</label>
        <select id="agent_id" name="agent_id" required>
          <option value="">Choose the agent you are at</option>
          <?php foreach ($agents as $a): ?>
            <option value="<?= e($a['agent_id']) ?>" <?= post('agent_id') === $a['agent_id'] ? 'selected' : '' ?>>
              <?= e($a['agent_id']) ?> · <?= e($a['phone_number']) ?> · holds <?= e(money($a['cash_in_amount'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="cash_out_amount">Amount to collect</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="cash_out_amount" name="cash_out_amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" max="<?= CASH_OUT_MAX ?>" value="<?= e(post('cash_out_amount')) ?>"
                 data-charge="cashSum" data-fee-pct="<?= FEE_CASH_OUT_PCT ?>" data-balance="<?= $bal ?>">
        </div>
        <?= amount_chips('cash_out_amount', [500, 1000, 2000, 5000]) ?>
        <span class="hint">You have <?= e(money($bal)) ?>.</span>
      </div>

      <div class="sum" id="cashSum" aria-live="polite"></div>
      <?= pin_field() ?>
      <button class="btn btn-block" type="submit">Cash out</button>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
