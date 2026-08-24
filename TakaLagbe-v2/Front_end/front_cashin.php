<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Cash in</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="alert alert-info" style="margin-bottom:1rem">
    <span>Hand the cash to the agent, then confirm here. Cash in is free of charge.</span>
  </div>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>
    <?= once_field('cash_in') ?>

    <div class="field">
      <label for="agent_id">Agent</label>
      <select id="agent_id" name="agent_id" required>
        <option value="">Choose an agent point</option>
        <?php foreach ($agents as $a): ?>
          <option value="<?= e($a['agent_id']) ?>">
            <?= e($a['agent_id']) ?> &middot; <?= e($a['phone_number']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="hint">Your balance <?= e(money($bal)) ?></div>
    </div>

    <div class="field">
      <label for="cash_in_amount">Amount handed over</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="cash_in_amount" name="cash_in_amount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" max="<?= ADD_MONEY_MAX ?>" placeholder="0.00">
      </div>
      <div class="chips">
        <?php foreach ([500, 1000, 2000, 5000] as $v): ?>
          <button type="button" class="chip" data-chip-target="cash_in_amount" data-value="<?= $v ?>">
            <?= CURRENCY_SIGN ?> <?= number_format($v) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="field">
      <label>Transaction PIN</label>
      <div class="pin" data-pin="pinValue">
        <?php for ($i = 0; $i < 6; $i++): ?>
          <input type="password" inputmode="numeric" maxlength="1" aria-label="PIN digit <?= $i + 1 ?>">
        <?php endfor; ?>
      </div>
      <input type="hidden" name="pin" id="pinValue" required pattern="\d{6}">
    </div>

    <button class="btn btn-primary btn-block" type="submit">Confirm cash in</button>
  </form>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
