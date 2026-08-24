<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Send money</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <?php if ($favourites): ?>
    <div class="card card-pad-sm" style="margin-bottom:1rem">
      <div class="card-title">Favourites</div>
      <div style="display:flex;gap:1rem;overflow-x:auto;padding-bottom:.2rem">
        <?php foreach ($favourites as $f): ?>
          <button type="button" class="chip" style="flex:none;border-radius:12px;padding:.5rem .7rem"
                  onclick="document.getElementById('receiver_phone').value='<?= e($f['saved_phone']) ?>';
                           document.getElementById('receiver_phone').dispatchEvent(new Event('input'));">
            <?= e($f['label'] ?: ($f['firstname'] ?: $f['saved_phone'])) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>
    <input type="hidden" name="request_id" value="<?= (int) ($requestId ?? 0) ?>">

    <div class="field">
      <label for="sender_phone">From</label>
      <input id="sender_phone" name="sender_phone" value="<?= e($phone) ?>" readonly>
      <div class="hint">Balance <?= e(money($bal)) ?></div>
    </div>

    <div class="field">
      <label for="receiver_phone">Send to</label>
      <input id="receiver_phone" name="receiver_phone" type="tel" inputmode="numeric"
             maxlength="11" pattern="01[3-9][0-9]{8}" required
             placeholder="01XXXXXXXXX" value="<?= e($prefill['to']) ?>" autofocus>
      <div class="hint">
        Scanning a code? <a href="qr.php">Open the QR page</a> to read one.
      </div>
    </div>

    <div class="field">
      <label for="amount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="amount" name="amount" type="number" step="0.01"
               min="<?= MIN_AMOUNT ?>" max="<?= SEND_MONEY_MAX ?>" required
               placeholder="0.00" value="<?= e($prefill['amount']) ?>"
               data-charge="chargeBox" data-fee-pct="<?= FEE_SEND_MONEY_PCT ?>"
               data-balance="<?= $bal ?>">
      </div>
      <div class="chips">
        <?php foreach ([100, 500, 1000, 2000, 5000] as $v): ?>
          <button type="button" class="chip" data-chip-target="amount" data-value="<?= $v ?>">
            <?= CURRENCY_SIGN ?> <?= number_format($v) ?>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="hint">
        Between <?= e(money(MIN_AMOUNT)) ?> and <?= e(money(SEND_MONEY_MAX)) ?> per transfer.
        Sending between users is free.
      </div>
    </div>

    <div class="field">
      <label for="note">Note <span class="muted tiny">(optional)</span></label>
      <input id="note" name="note" maxlength="120" placeholder="What is this for?"
             value="<?= e($prefill['note']) ?>">
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
      <div class="hint center">The 6 digit PIN you chose when you signed up.</div>
    </div>

    <label style="display:flex;align-items:center;gap:.5rem;font-weight:500;margin-bottom:1rem">
      <input type="checkbox" name="save_favourite" value="1" style="width:auto">
      Save this number to my favourites
    </label>

    <button class="btn btn-primary btn-block hold" data-hold-ms="900" type="submit">
      <span class="fill"></span>
      <span class="txt">Tap and hold to send</span>
    </button>
    <noscript>
      <button class="btn btn-primary btn-block" type="submit" style="margin-top:.6rem">Send money</button>
    </noscript>
  </form>

  <p class="tiny muted center" style="margin-top:1rem">
    Both accounts are locked while the transfer runs, so the balance can never
    go wrong even if two transfers happen at the same moment.
  </p>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
