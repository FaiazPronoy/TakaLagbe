<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0"><?= $icon ?> <?= e($title) ?></h1>
    <a class="btn btn-ghost btn-sm" href="paybill.php">All bills</a>
  </div>

  <?php if ($billImage !== '' && is_file(__DIR__ . '/../Assets/' . $billImage)): ?>
    <img src="Assets/<?= e($billImage) ?>" alt=""
         style="width:100%;height:120px;object-fit:cover;border-radius:var(--radius);
                margin-bottom:1rem;box-shadow:var(--shadow)">
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>

    <div class="field">
      <label for="organization_id">Provider</label>
      <select id="organization_id" name="organization_id" required>
        <option value="">Choose your provider</option>
        <?php foreach ($organizations as $o): ?>
          <option value="<?= e($o['organization_id']) ?>"><?= e($o['organization_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="consumer_no"><?= e($consumerLabel) ?></label>
      <input id="consumer_no" name="consumer_no" required maxlength="40"
             placeholder="As printed on your bill">
    </div>

    <div class="field">
      <label for="month">Billing month</label>
      <select id="month" name="month" required>
        <?php foreach ($months as $m): ?>
          <option value="<?= e($m) ?>"><?= e($m) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="hint">The same bill cannot be paid twice for one month.</div>
    </div>

    <div class="field">
      <label for="amount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="amount" name="amount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" placeholder="0.00"
               data-charge="chargeBox" data-fee-flat="<?= FEE_BILL_FLAT ?>"
               data-balance="<?= $bal ?>">
      </div>
      <div class="hint">
        A flat convenience charge of <?= e(money(FEE_BILL_FLAT)) ?> applies.
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

    <button class="btn btn-primary btn-block" type="submit">Pay bill</button>
  </form>

  <?php if ($recent): ?>
    <div class="card" style="margin-top:1rem">
      <div class="card-title">Your recent <?= e(strtolower($title)) ?>s</div>
      <?php foreach ($recent as $r): ?>
        <a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>" style="color:inherit">
          <span class="ico"><?= $icon ?></span>
          <span class="body">
            <span class="t"><?= e($r['organization_name']) ?></span>
            <span class="s"><?= e($consumerLabel) ?> <?= e($r['consumer_no']) ?>
              &middot; <?= e($r['month']) ?></span>
          </span>
          <span class="amt"><?= e(money($r['amount'])) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
