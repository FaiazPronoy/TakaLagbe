<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Requests</h1>
    <div style="display:flex;gap:.4rem">
      <button class="btn btn-ghost btn-sm" onclick="openModal('splitBill')">Split a bill</button>
      <button class="btn btn-primary btn-sm" onclick="openModal('askMoney')">Ask for money</button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="row" style="margin-bottom:1.2rem">
    <div class="col"><div class="stat"><div class="k">People owe you</div>
      <div class="v pos"><?= e(money($owedToMe, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">You owe</div>
      <div class="v neg"><?= e(money($iOwe, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Waiting on you</div>
      <div class="v"><?= count($incoming) ?></div></div></div>
  </div>

  <div class="row">
    <!-- ------------------------------------------------- incoming -->
    <div class="col">
      <div class="card">
        <div class="card-title">Asked of you</div>
        <?php if (!$incoming): ?>
          <div class="empty"><div class="big">&#9989;</div>
            <p class="mb0">Nobody is waiting on you. Nice.</p></div>
        <?php else: foreach ($incoming as $r):
          $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['requester_phone']; ?>
          <div class="entry" id="r<?= (int) $r['id'] ?>" style="flex-wrap:wrap">
            <span class="avatar avatar-sm" style="background:<?= e($r['avatar_color'] ?: '#1E3A63') ?>">
              <?= e(initials($r['firstname'], $r['lastname'])) ?>
            </span>
            <span class="body">
              <span class="t"><?= e($who) ?></span>
              <span class="s"><?= e($r['note'] ?: 'No note') ?>
                &middot; <?= e(time_ago($r['created_at'])) ?>
                <?php if ($r['split_group']): ?>
                  &middot; <span class="badge">split</span>
                <?php endif; ?>
              </span>
            </span>
            <span class="amt"><?= e(money($r['amount'])) ?></span>
            <span style="display:flex;gap:.4rem;width:100%;margin-top:.4rem">
              <a class="btn btn-primary btn-sm"
                 href="sendmoney.php?request=<?= (int) $r['id'] ?>">Pay now</a>
              <form method="post" data-confirm="Decline this request?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="decline">
                <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit">Decline</button>
              </form>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- ------------------------------------------------- outgoing -->
    <div class="col">
      <div class="card">
        <div class="card-title">You asked for</div>
        <?php if (!$outgoing): ?>
          <div class="empty"><div class="big">&#129309;</div>
            <p class="mb0">You have not asked anyone for money yet.</p></div>
        <?php else: foreach ($outgoing as $r):
          $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['payer_phone'];
          $cls = ['pending' => 'badge-pending', 'paid' => 'badge-ok', 'declined' => 'badge-fail']; ?>
          <div class="entry">
            <span class="avatar avatar-sm" style="background:<?= e($r['avatar_color'] ?: '#1E3A63') ?>">
              <?= e(initials($r['firstname'], $r['lastname'])) ?>
            </span>
            <span class="body">
              <span class="t"><?= e($who) ?></span>
              <span class="s"><?= e($r['note'] ?: 'No note') ?>
                &middot; <?= e(time_ago($r['created_at'])) ?></span>
            </span>
            <span style="text-align:right">
              <span class="amt" style="display:block"><?= e(money($r['amount'])) ?></span>
              <span class="badge <?= $cls[$r['status']] ?>"><?= e($r['status']) ?></span>
            </span>
            <?php if ($r['status'] === 'pending'): ?>
              <form method="post" data-confirm="Cancel this request?" style="margin-left:.4rem">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit">Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</main>

<!-- --------------------------------------------------- ask modal -->
<div class="modal" id="askMoney">
  <div class="modal-box">
    <h2>Ask someone for money</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="request">
      <div class="field">
        <label for="from_phone">Their number</label>
        <input id="from_phone" name="from_phone" type="tel" inputmode="numeric"
               maxlength="11" pattern="01[3-9][0-9]{8}" required placeholder="01XXXXXXXXX">
      </div>
      <div class="field">
        <label for="reqAmount">Amount</label>
        <div class="money-input">
          <span><?= CURRENCY_SIGN ?></span>
          <input id="reqAmount" name="amount" type="number" step="0.01"
                 min="<?= MIN_AMOUNT ?>" required placeholder="0.00">
        </div>
      </div>
      <div class="field">
        <label for="reqNote">What is it for? <span class="muted tiny">(optional)</span></label>
        <input id="reqNote" name="note" maxlength="120" placeholder="Dinner, rickshaw fare, rent">
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('askMoney')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Send request</button>
      </div>
    </form>
  </div>
</div>

<!-- ------------------------------------------------- split modal -->
<div class="modal" id="splitBill">
  <div class="modal-box">
    <h2>Split a bill</h2>
    <p class="muted tiny">Everyone including you shares the total equally.</p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="split">
      <div class="field">
        <label for="total">Total bill</label>
        <div class="money-input">
          <span><?= CURRENCY_SIGN ?></span>
          <input id="total" name="total" type="number" step="0.01" min="<?= MIN_AMOUNT ?>"
                 required placeholder="0.00" oninput="recalc()">
        </div>
      </div>
      <div class="field">
        <label for="people">Split with</label>
        <textarea id="people" name="people" required oninput="recalc()"
                  placeholder="01712345678, 01898765432"></textarea>
        <div class="hint">One number per line, or separated by commas. Up to 10 people.</div>
      </div>
      <div class="alert alert-info" id="splitPreview" style="margin-bottom:1rem">
        <span>Enter a total and some numbers to see each share.</span>
      </div>
      <div class="field">
        <label for="split_note">Note <span class="muted tiny">(optional)</span></label>
        <input id="split_note" name="split_note" maxlength="120" placeholder="Friday dinner at Star">
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('splitBill')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Send requests</button>
      </div>
    </form>
  </div>
</div>

<script>
function recalc() {
  var total = parseFloat(document.getElementById('total').value || '0');
  var list = (document.getElementById('people').value || '')
             .split(/[\s,;]+/).filter(function (x) { return x.length; });
  var box = document.getElementById('splitPreview');
  if (!total || !list.length) {
    box.innerHTML = '<span>Enter a total and some numbers to see each share.</span>';
    return;
  }
  var heads = list.length + 1;
  var share = (total / heads).toFixed(2);
  box.innerHTML = '<span>Split <b>' + heads + '</b> ways. Each person pays <b>৳ ' +
                  share + '</b>, including you.</span>';
}
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
