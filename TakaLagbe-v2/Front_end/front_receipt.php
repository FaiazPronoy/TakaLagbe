<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$verifyPayload = 'TKL-VERIFY|' . $trx['trx_id'] . '|' . number_format((float) $trx['amount'], 2, '.', '')
               . '|' . $trx['transaction_time'];
?>
<main class="wrap-sm section">

  <div class="between no-print" style="margin-bottom:1rem">
    <h1 class="mb0">Receipt</h1>
    <div style="display:flex;gap:.4rem">
      <a class="btn btn-ghost btn-sm" href="history.php">History</a>
      <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
    </div>
  </div>

  <div class="receipt">
    <div class="tick"><?= $trx['status'] === 'success' ? '&#10003;' : '&#33;' ?></div>
    <h2 class="center mb0"><?= e($trx['transaction_type']) ?></h2>
    <p class="center muted tiny" style="margin-top:.2rem">
      <?= $trx['status'] === 'success' ? 'Completed successfully' : e(ucfirst($trx['status'])) ?>
    </p>

    <div class="center" style="margin:1rem 0">
      <div style="font-size:2rem;font-weight:800;letter-spacing:-.02em"
           class="<?= $out ? 'neg' : 'pos' ?>">
        <?= $out ? '-' : '+' ?> <?= e(money($trx['amount'])) ?>
      </div>
    </div>

    <hr class="dashed">

    <dl>
      <dt>Transaction ID</dt>
      <dd class="mono"><?= e($trx['trx_id']) ?>
        <button class="btn btn-ghost btn-sm no-print" style="margin-left:.3rem"
                onclick="copyText('<?= e($trx['trx_id']) ?>', this)">Copy</button></dd>

      <dt>From</dt>
      <dd><?= e(valid_phone($trx['transaction_from'])
                ? name_of($trx['transaction_from']) . ' (' . $trx['transaction_from'] . ')'
                : $trx['transaction_from']) ?></dd>

      <dt>To</dt>
      <dd><?= e(valid_phone($trx['transaction_to'])
                ? name_of($trx['transaction_to']) . ' (' . $trx['transaction_to'] . ')'
                : $trx['transaction_to']) ?></dd>

      <dt>Amount</dt>
      <dd><?= e(money($trx['amount'])) ?></dd>

      <?php if ((float) $trx['fee'] > 0): ?>
        <dt>Charge</dt><dd><?= e(money($trx['fee'])) ?></dd>
      <?php endif; ?>

      <?php if ((float) $trx['cashback'] > 0): ?>
        <dt>Cashback</dt><dd class="pos">+ <?= e(money($trx['cashback'])) ?></dd>
      <?php endif; ?>

      <?php if ($out && $trx['sender_balance_after'] !== null): ?>
        <dt>Balance after</dt><dd><?= e(money($trx['sender_balance_after'])) ?></dd>
      <?php elseif (!$out && $trx['receiver_balance_after'] !== null): ?>
        <dt>Balance after</dt><dd><?= e(money($trx['receiver_balance_after'])) ?></dd>
      <?php endif; ?>

      <dt>Category</dt><dd><?= e($trx['category']) ?></dd>

      <?php if ($trx['note']): ?>
        <dt>Note</dt><dd><?= e($trx['note']) ?></dd>
      <?php endif; ?>

      <dt>Date and time</dt><dd><?= e(short_date($trx['transaction_time'])) ?></dd>
    </dl>

    <hr class="dashed">

    <div class="center">
      <div class="qr"><?= QrCode::svg($verifyPayload, 3) ?></div>
      <p class="tiny muted" style="margin-top:.5rem">
        Scan to verify this receipt.<br>
        <?= e(APP_NAME) ?> &middot; generated <?= date('d M Y, g:i a') ?>
      </p>
    </div>
  </div>

  <div class="no-print" style="display:flex;gap:.5rem;margin-top:1rem">
    <a class="btn btn-ghost btn-block" href="account.php">Done</a>
    <?php if ($out && valid_phone($trx['transaction_to'])): ?>
      <a class="btn btn-primary btn-block"
         href="sendmoney.php?to=<?= e($trx['transaction_to']) ?>">Send again</a>
    <?php endif; ?>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
