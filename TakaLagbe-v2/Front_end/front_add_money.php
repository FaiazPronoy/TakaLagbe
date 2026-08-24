<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Add money</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>
    <?= once_field('add_money') ?>

    <div class="field">
      <label for="bank">From</label>
      <select id="bank" name="bank" required>
        <option value="">Choose a bank or card</option>
        <?php foreach (BANKS as $b): ?>
          <option value="<?= e($b) ?>"><?= e($b) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="hint">Current balance <?= e(money($bal)) ?></div>
    </div>

    <div class="field">
      <label for="amount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="amount" name="amount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" max="<?= ADD_MONEY_MAX ?>" placeholder="0.00">
      </div>
      <div class="chips">
        <?php foreach ([500, 1000, 2000, 5000, 10000] as $v): ?>
          <button type="button" class="chip" data-chip-target="amount" data-value="<?= $v ?>">
            <?= CURRENCY_SIGN ?> <?= number_format($v) ?>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="hint">No charge on adding money. Maximum <?= e(money(ADD_MONEY_MAX)) ?> per top up.</div>
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

    <button class="btn btn-primary btn-block" type="submit">Add money</button>
  </form>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
