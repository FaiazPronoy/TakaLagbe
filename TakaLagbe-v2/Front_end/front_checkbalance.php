<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Check balance</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <?php if ($balance !== null): ?>
    <div class="card balance center">
      <div class="label">Available balance</div>
      <div class="amount"><?= e(money($balance)) ?></div>
      <div class="meta" style="margin-top:.4rem">
        <?= e($user['firstname'] . ' ' . $user['lastname']) ?> &middot; <?= e($phone) ?>
      </div>
      <div class="meta tiny" style="margin-top:.6rem">
        Checked on <?= date('d M Y \a\t g:i a') ?>
      </div>
    </div>
    <?php if ($inSavings > 0): ?>
      <div class="card" style="margin-top:1rem">
        <div class="between mb0">
          <span class="muted">Also held in savings goals</span>
          <strong><?= e(money($inSavings)) ?></strong>
        </div>
      </div>
    <?php endif; ?>
    <div style="display:flex;gap:.5rem;margin-top:1rem">
      <a class="btn btn-ghost btn-block" href="account.php">Home</a>
      <a class="btn btn-primary btn-block" href="sendmoney.php">Send money</a>
    </div>
  <?php else: ?>
    <form method="post" class="card" data-once>
      <?= csrf_field() ?>
      <p class="muted tiny center">Enter your transaction PIN to see your balance.</p>
      <div class="field">
        <div class="pin" data-pin="pinValue">
          <?php for ($i = 0; $i < 6; $i++): ?>
            <input type="password" inputmode="numeric" maxlength="1"
                   aria-label="PIN digit <?= $i + 1 ?>">
          <?php endfor; ?>
        </div>
        <input type="hidden" name="pin" id="pinValue" required pattern="\d{6}">
      </div>
      <button class="btn btn-primary btn-block" type="submit">Show my balance</button>
    </form>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
