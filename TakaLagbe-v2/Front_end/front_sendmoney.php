<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Send money', 'Zero charge between TakaLagbe users. Up to ' . money(SEND_MONEY_MAX) . ' in one go.',
                  ['account.php', 'Home'], badge: ['send', 1]) ?>

    <?= error_note($error) ?>

    <?php if ($favourites): ?>
      <div class="card tight rise mb-4" style="--n:1">
        <span class="label">Your people, one tap away</span>
        <div class="chips">
          <?php foreach ($favourites as $f): ?>
            <a class="chip" href="sendmoney.php?to=<?= e($f['saved_phone']) ?>"><?= e($f['label'] ?: $f['saved_phone']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" class="card rise" style="--n:2" data-once>
      <?= csrf_field() ?>
      <?= once_field('sendmoney') ?>
      <input type="hidden" name="request_id" value="<?= (int) ($requestId ?? 0) ?>">

      <?php if (!empty($req)): ?>
        <div class="note warn"><?= icon('hand') ?>
          <span>Paying <?= e(name_of($req['requester_phone'])) ?>'s request for
          <?= e(money($req['amount'])) ?>. It is marked as paid once this goes through.</span></div>
      <?php endif; ?>

      <div class="field">
        <label for="receiver_phone">Who gets it?</label>
        <input id="receiver_phone" name="receiver_phone" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required
               placeholder="01XXXXXXXXX" value="<?= e($prefill['to']) ?>">
        <span class="hint">Their TakaLagbe number. Got a QR instead? <a href="qr.php#scan">Scan it</a>.</span>
      </div>

      <div class="field">
        <label for="amount">How much?</label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal"
                 min="<?= MIN_AMOUNT ?>" max="<?= SEND_MONEY_MAX ?>" required
                 value="<?= e($prefill['amount']) ?>"
                 data-charge="sendSum" data-fee-pct="<?= FEE_SEND_MONEY_PCT ?>" data-balance="<?= $bal ?>">
        </div>
        <?= amount_chips('amount', [200, 500, 1000, 2000, 5000]) ?>
        <span class="hint">You've got <?= e(money($bal)) ?> to play with.</span>
      </div>

      <div class="field">
        <label for="note">What's it for? <span class="muted">(optional)</span></label>
        <input id="note" name="note" maxlength="120" value="<?= e($prefill['note']) ?>"
               placeholder="Rent share, Eid salami, that biryani you owe">
      </div>

      <div class="sum" id="sendSum" aria-live="polite"></div>

      <?= pin_field() ?>

      <label class="check mb-4">
        <input type="checkbox" name="save_favourite" value="1"> Add them to my people
      </label>

      <button class="btn btn-block hold" data-hold-ms="800" type="submit" aria-describedby="holdHint">
        <span class="fill"></span><span class="txt"><?= icon('send') ?> Send it</span>
      </button>
      <span class="hint center js-only" id="holdHint" style="justify-content:center">
        Press and hold to send, so a pocket tap can't. Enter works too.</span>
    </form>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
