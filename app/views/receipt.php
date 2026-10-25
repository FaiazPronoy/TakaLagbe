<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$ok = $trx['status'] === 'success';
$verifyPayload = 'TKL-VERIFY|' . $trx['trx_id'] . '|' . number_format((float) $trx['amount'], 2, '.', '')
               . '|' . $trx['transaction_time'];
$side  = static fn(string $v): string => valid_phone($v) ? name_of($v) . ' · ' . $v : $v;
$after = $out ? $trx['sender_balance_after'] : $trx['receiver_balance_after'];
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <div class="between no-print mb-4">
      <a class="back m0" href="history.php"><?= icon('back') ?><?= __('History') ?></a>
      <button class="btn btn-quiet btn-sm js-only" type="button" onclick="window.print()"><?= icon('print') ?><?= __('Print') ?></button>
    </div>

    <article class="receipt rise" aria-labelledby="rcTitle">
      <div class="seal<?= $ok ? '' : ' bad' ?>"><?= icon($ok ? 'check' : 'x') ?></div>
      <h1 id="rcTitle" class="center" style="font-size:var(--step-2);margin-bottom:var(--s1)"><?= e(__($trx['transaction_type'])) ?></h1>
      <p class="center muted small m0"><?= e($ok ? __('Completed') : __('Declined, nothing moved')) ?>
        · <?= e(short_date($trx['transaction_time'])) ?></p>

      <p class="big-amt <?= $ok ? ($out ? 'neg' : 'pos') : 'muted' ?>">
        <?= $out ? '−' : '+' ?> ৳<?= e(money($trx['amount'], false)) ?></p>

      <dl>
        <dt><?= __('Reference') ?></dt>
        <dd class="mono"><?= e($trx['trx_id']) ?>
          <button class="tool js-only" type="button" style="display:inline-grid;width:28px;height:28px;vertical-align:middle"
                  data-copy="<?= e($trx['trx_id']) ?>" aria-label="<?= e(__('Copy reference')) ?>"><?= icon('copy') ?></button></dd>
        <dt><?= __('From') ?></dt><dd><?= e($side($trx['transaction_from'])) ?></dd>
        <dt><?= __('To') ?></dt><dd><?= e($side($trx['transaction_to'])) ?></dd>
        <?php if ((float) $trx['fee'] > 0): ?><dt><?= __('Charge') ?></dt><dd class="num"><?= e(money($trx['fee'])) ?></dd><?php endif; ?>
        <?php if ((float) $trx['cashback'] > 0): ?><dt><?= __('Cashback') ?></dt><dd class="num pos">+ <?= e(money($trx['cashback'])) ?></dd><?php endif; ?>
        <?php if ($after !== null): ?><dt><?= __('Balance after') ?></dt><dd class="num"><?= e(money($after)) ?></dd><?php endif; ?>
        <dt><?= __('Category') ?></dt><dd><?= e(__($trx['category'])) ?></dd>
        <?php if ($trx['note']): ?><dt><?= e($ok ? __('Note') : __('Reason')) ?></dt><dd><?= e(__notice($trx['note'])) ?></dd><?php endif; ?>
      </dl>

      <?php if ($ok): ?>
        <div class="center" style="border-top:1px dashed var(--line-strong);padding-top:var(--s4)">
          <div class="qr qr-sm settle"><?= QrCode::svg($verifyPayload, 3) ?></div>
          <p class="small muted m0 mt-2"><?= __('Scan to check the reference and amount.') ?></p>
        </div>
      <?php endif; ?>
    </article>

    <div class="row no-print mt-5" style="justify-content:center">
      <a class="btn btn-quiet" href="account.php"><?= __('Done') ?></a>
      <?php if ($out && valid_phone($trx['transaction_to'])): ?>
        <a class="btn" href="send_money.php?to=<?= e($trx['transaction_to']) ?>"><?= icon('send') ?><?= __('Send again') ?></a>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
