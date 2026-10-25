<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
$badge = ['pending' => 'warn', 'paid' => 'ok', 'declined' => 'bad'];
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head(__('Requests'), __('Lent someone money for lunch? Ask for it back. Dinner with the gang? Split it.'), null,
          '<a class="btn btn-quiet" href="#splitBill">' . icon('split') . e(__('Split a bill')) . '</a>'
        . '<a class="btn" href="#askMoney">' . icon('hand') . e(__('Ask for money')) . '</a>', badge: ['hand', 4]) ?>
    <?= error_note($error) ?>

    <div class="grid cols-3 mb-5">
      <div class="stat rise" style="--n:1"><span class="label"><?= icon('arrow-in') ?><?= __('They owe you') ?></span>
        <span class="v pos"><small>৳</small><?= e(money($owedToMe, false)) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label"><?= icon('arrow-out') ?><?= __('You owe') ?></span>
        <span class="v neg"><small>৳</small><?= e(money($iOwe, false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label"><?= icon('bell') ?><?= __('Waiting on you') ?></span>
        <span class="v"><?= count($incoming) ?></span></div>
    </div>

    <div class="grid cols-2">
      <section class="card reveal" aria-labelledby="inTitle">
        <div class="card-h"><h2 id="inTitle"><?= icon('arrow-out') ?><?= __('People asking you') ?></h2></div>
        <?php if (!$incoming): ?>
          <?= empty_state('check', __('All square!'), __('Nobody is chasing you for money. Enjoy it while it lasts.'), '', 3, 2) ?>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($incoming as $r):
              $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['requester_phone']; ?>
              <li class="entry" id="r<?= (int) $r['id'] ?>" style="flex-wrap:wrap">
                <?= avatar($r['firstname'], $r['lastname'], $r['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($who) ?></span>
                  <span class="s"><?= e($r['note'] ?: __('No note')) ?> · <?= e(time_ago($r['created_at'])) ?>
                    <?= $r['split_group'] ? ' · ' . e(__('split')) : '' ?></span></span>
                <span class="amt"><?= e(money($r['amount'])) ?></span>
                <span class="row" style="width:100%;padding-left:52px">
                  <a class="btn btn-sm" href="send_money.php?request=<?= (int) $r['id'] ?>"><?= __('Pay') ?></a>
                  <form method="post" data-confirm="<?= e(__('Decline the request from {name}?', ['name' => $who])) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="decline">
                    <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-quiet btn-sm" type="submit"><?= __('Decline') ?></button>
                  </form>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="outTitle">
        <div class="card-h"><h2 id="outTitle"><?= icon('arrow-in') ?><?= __('You asked them') ?></h2></div>
        <?php if (!$outgoing): ?>
          <?= empty_state('hand', __('Nothing asked yet'),
                __('Paid for someone\'s fuchka? Ask for it back. It sits here until they pay up.'),
                '<a class="btn btn-quiet" href="#askMoney">' . e(__('Ask for money')) . '</a>') ?>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($outgoing as $r):
              $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['payer_phone']; ?>
              <li class="entry">
                <?= avatar($r['firstname'], $r['lastname'], $r['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($who) ?></span>
                  <span class="s"><?= e($r['note'] ?: __('No note')) ?> · <?= e(time_ago($r['created_at'])) ?></span></span>
                <span class="end">
                  <span class="center"><span class="amt" style="display:block"><?= e(money($r['amount'])) ?></span>
                    <span class="badge <?= $badge[$r['status']] ?>"><?= e(__($r['status'])) ?></span></span>
                  <?php if ($r['status'] === 'pending'): ?>
                    <form method="post" data-confirm="<?= e(__('Cancel this request?')) ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                      <button class="tool" type="submit" aria-label="<?= e(__('Cancel the request to {name}', ['name' => $who])) ?>"><?= icon('x') ?></button>
                    </form>
                  <?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<dialog id="askMoney" aria-labelledby="askTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="<?= e(__('Close')) ?>"><?= icon('x') ?></a>
    <h2 id="askTitle"><?= __('Ask someone to pay up') ?></h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="request">
      <div class="field">
        <label for="from_phone"><?= __('Their number') ?></label>
        <input id="from_phone" name="from_phone" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="<?= e(__('01XXXXXXXXX')) ?>">
      </div>
      <div class="field">
        <label for="reqAmount"><?= __('Amount') ?></label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="reqAmount" name="amount" type="number" step="0.01" inputmode="decimal" min="<?= MIN_AMOUNT ?>" required></div>
      </div>
      <div class="field">
        <label for="reqNote"><?= __('What for?') ?> <span class="muted"><?= __('(optional)') ?></span></label>
        <input id="reqNote" name="note" maxlength="120" placeholder="<?= e(__('Lunch, rickshaw fare, that concert ticket')) ?>">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close><?= __('Cancel') ?></a>
        <button class="btn push" type="submit"><?= icon('send') ?><?= __('Send request') ?></button>
      </div>
    </form>
  </div>
</dialog>

<dialog id="splitBill" aria-labelledby="splitTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="<?= e(__('Close')) ?>"><?= icon('x') ?></a>
    <h2 id="splitTitle"><?= __('Split a bill') ?></h2>
    <p class="muted small"><?= __('Everyone pays the same, you included. Each person gets a request. No awkward maths.') ?></p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="split">
      <div class="field">
        <label for="total"><?= __('Total bill') ?></label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="total" name="total" type="number" step="0.01" inputmode="decimal" min="<?= MIN_AMOUNT ?>" required data-split-total></div>
      </div>
      <div class="field">
        <label for="people"><?= __('Who was there?') ?></label>
        <textarea id="people" name="people" required data-split-people aria-describedby="peopleHint"
                  placeholder="01712345678, 01898765432"></textarea>
        <span class="hint" id="peopleHint"><?= __('Their numbers, one per line or separated by commas. Up to 10.') ?></span>
      </div>
      <p class="note js-only" id="splitPreview" aria-live="polite"></p>
      <div class="field">
        <label for="split_note"><?= __('What was it?') ?> <span class="muted"><?= __('(optional)') ?></span></label>
        <input id="split_note" name="split_note" maxlength="120" placeholder="<?= e(__('Friday dinner at Kacchi Bhai')) ?>">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close><?= __('Cancel') ?></a>
        <button class="btn push" type="submit"><?= icon('split') ?><?= __('Split it') ?></button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
