<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
$badge = ['pending' => 'warn', 'paid' => 'ok', 'declined' => 'bad'];
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Requests', 'Lent someone money for lunch? Ask for it back. Dinner with the gang? Split it.', null,
          '<a class="btn btn-quiet" href="#splitBill">' . icon('split') . 'Split a bill</a>'
        . '<a class="btn" href="#askMoney">' . icon('hand') . 'Ask for money</a>', badge: ['hand', 4]) ?>
    <?= error_note($error) ?>

    <div class="grid cols-3 mb-5">
      <div class="stat rise" style="--n:1"><span class="label"><?= icon('arrow-in') ?>They owe you</span>
        <span class="v pos"><small>৳</small><?= e(money($owedToMe, false)) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label"><?= icon('arrow-out') ?>You owe</span>
        <span class="v neg"><small>৳</small><?= e(money($iOwe, false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label"><?= icon('bell') ?>Waiting on you</span>
        <span class="v"><?= count($incoming) ?></span></div>
    </div>

    <div class="grid cols-2">
      <section class="card reveal" aria-labelledby="inTitle">
        <div class="card-h"><h2 id="inTitle"><?= icon('arrow-out') ?>People asking you</h2></div>
        <?php if (!$incoming): ?>
          <?= empty_state('check', 'All square!', 'Nobody is chasing you for money. Enjoy it while it lasts.', '', 3, 2) ?>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($incoming as $r):
              $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['requester_phone']; ?>
              <li class="entry" id="r<?= (int) $r['id'] ?>" style="flex-wrap:wrap">
                <?= avatar($r['firstname'], $r['lastname'], $r['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($who) ?></span>
                  <span class="s"><?= e($r['note'] ?: 'No note') ?> · <?= e(time_ago($r['created_at'])) ?>
                    <?= $r['split_group'] ? ' · split' : '' ?></span></span>
                <span class="amt"><?= e(money($r['amount'])) ?></span>
                <span class="row" style="width:100%;padding-left:52px">
                  <a class="btn btn-sm" href="sendmoney.php?request=<?= (int) $r['id'] ?>">Pay</a>
                  <form method="post" data-confirm="Decline <?= e($who) ?>'s request?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="decline">
                    <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-quiet btn-sm" type="submit">Decline</button>
                  </form>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="outTitle">
        <div class="card-h"><h2 id="outTitle"><?= icon('arrow-in') ?>You asked them</h2></div>
        <?php if (!$outgoing): ?>
          <?= empty_state('hand', 'Nothing asked yet',
                'Paid for someone\'s fuchka? Ask for it back. It sits here until they pay up.',
                '<a class="btn btn-quiet" href="#askMoney">Ask for money</a>') ?>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($outgoing as $r):
              $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['payer_phone']; ?>
              <li class="entry">
                <?= avatar($r['firstname'], $r['lastname'], $r['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($who) ?></span>
                  <span class="s"><?= e($r['note'] ?: 'No note') ?> · <?= e(time_ago($r['created_at'])) ?></span></span>
                <span class="end">
                  <span class="center"><span class="amt" style="display:block"><?= e(money($r['amount'])) ?></span>
                    <span class="badge <?= $badge[$r['status']] ?>"><?= e($r['status']) ?></span></span>
                  <?php if ($r['status'] === 'pending'): ?>
                    <form method="post" data-confirm="Cancel this request?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                      <button class="tool" type="submit" aria-label="Cancel the request to <?= e($who) ?>"><?= icon('x') ?></button>
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
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="askTitle">Ask someone to pay up</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="request">
      <div class="field">
        <label for="from_phone">Their number</label>
        <input id="from_phone" name="from_phone" type="tel" inputmode="numeric" autocomplete="off"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX">
      </div>
      <div class="field">
        <label for="reqAmount">Amount</label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="reqAmount" name="amount" type="number" step="0.01" inputmode="decimal" min="<?= MIN_AMOUNT ?>" required></div>
      </div>
      <div class="field">
        <label for="reqNote">What for? <span class="muted">(optional)</span></label>
        <input id="reqNote" name="note" maxlength="120" placeholder="Lunch, rickshaw fare, that concert ticket">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close>Cancel</a>
        <button class="btn push" type="submit"><?= icon('send') ?>Send request</button>
      </div>
    </form>
  </div>
</dialog>

<dialog id="splitBill" aria-labelledby="splitTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="splitTitle">Split a bill</h2>
    <p class="muted small">Everyone pays the same, you included. Each person gets a request. No awkward maths.</p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="split">
      <div class="field">
        <label for="total">Total bill</label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="total" name="total" type="number" step="0.01" inputmode="decimal" min="<?= MIN_AMOUNT ?>" required data-split-total></div>
      </div>
      <div class="field">
        <label for="people">Who was there?</label>
        <textarea id="people" name="people" required data-split-people aria-describedby="peopleHint"
                  placeholder="01712345678, 01898765432"></textarea>
        <span class="hint" id="peopleHint">Their numbers, one per line or separated by commas. Up to 10.</span>
      </div>
      <p class="note js-only" id="splitPreview" aria-live="polite"></p>
      <div class="field">
        <label for="split_note">What was it? <span class="muted">(optional)</span></label>
        <input id="split_note" name="split_note" maxlength="120" placeholder="Friday dinner at Kacchi Bhai">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close>Cancel</a>
        <button class="btn push" type="submit"><?= icon('split') ?>Split it</button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
