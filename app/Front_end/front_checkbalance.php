<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head(__('Check balance'), __('PIN first, so whoever borrowed your phone sees nothing.'), ['account.php', __('Home')], badge: ['eye', 1]) ?>
    <?= error_note($error) ?>

    <?php if ($balance !== null): ?>
      <section class="hero rise" aria-label="<?= e(__('Your balance')) ?>">
        <?= guilloche() ?>
        <span class="label"><?= __('Available balance') ?></span>
        <p class="figure"><span class="cur">৳</span><?= e(money($balance, false)) ?></p>
        <p class="who"><?= e(__('Checked {when}', ['when' => date('j M Y, g:i a')])) ?></p>
        <?php if ($inSavings > 0): ?>
          <p class="who"><?= e(__('Another {amount} is set aside in savings goals.', ['amount' => money($inSavings)])) ?></p>
        <?php endif; ?>
      </section>
      <div class="row mt-4">
        <a class="btn btn-quiet" href="account.php"><?= __('Home') ?></a>
        <a class="btn" href="sendmoney.php"><?= icon('send') ?><?= __('Send money') ?></a>
      </div>
    <?php else: ?>
      <form method="post" class="card rise" style="--n:1" data-once>
        <?= csrf_field() ?>
        <?= pin_field() ?>
        <button class="btn btn-block" type="submit"><?= icon('eye') ?><?= __('Show me') ?></button>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
