<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Support</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="row">
    <div class="col" style="flex:1 1 320px">
      <div class="card">
        <div class="card-title">Tell us what went wrong</div>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <div class="row">
            <div class="col field"><label>Name</label>
              <input value="<?= e($user['firstname'] . ' ' . $user['lastname']) ?>" readonly></div>
            <div class="col field"><label>Email</label>
              <input value="<?= e($user['email']) ?>" readonly></div>
          </div>
          <div class="field">
            <label for="message">Your message</label>
            <textarea id="message" name="message" required minlength="10" maxlength="2000"
                      placeholder="Include the transaction ID if it is about a payment."></textarea>
            <div class="hint">The more detail you give, the faster this gets sorted.</div>
          </div>
          <button class="btn btn-primary btn-block" type="submit">Send message</button>
        </form>
      </div>

      <div class="card" style="margin-top:1rem">
        <div class="card-title">Your tickets</div>
        <?php if (!$tickets): ?>
          <div class="empty"><p class="mb0">No tickets raised yet.</p></div>
        <?php else: foreach ($tickets as $t): ?>
          <div class="entry">
            <span class="ico"><?= $t['issue_solved'] ? '&#9989;' : '&#128172;' ?></span>
            <span class="body">
              <span class="t" style="white-space:normal"><?= e(mb_substr($t['message'], 0, 90)) ?><?=
                mb_strlen($t['message']) > 90 ? '…' : '' ?></span>
              <span class="s"><?= e(short_date($t['timestamp'])) ?></span>
            </span>
            <span class="badge <?= $t['issue_solved'] ? 'badge-ok' : 'badge-pending' ?>">
              <?= $t['issue_solved'] ? 'solved' : 'open' ?>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="col" style="flex:1 1 320px">
      <div class="card">
        <div class="card-title">Common questions</div>
        <?php foreach ($faq as $i => [$q, $a]): ?>
          <details style="border-bottom:1px solid var(--border);padding:.7rem 0">
            <summary style="cursor:pointer;font-weight:700;font-size:.92rem"><?= e($q) ?></summary>
            <p class="tiny muted" style="margin:.5rem 0 0"><?= e($a) ?></p>
          </details>
        <?php endforeach; ?>
      </div>

      <div class="card" style="margin-top:1rem">
        <div class="card-title">Other ways to reach us</div>
        <div class="entry"><span class="ico">&#9742;</span>
          <span class="body"><span class="t">16247</span>
            <span class="s">Helpline, open every day</span></span></div>
        <div class="entry"><span class="ico">&#9993;</span>
          <span class="body"><span class="t">support@takalagbe.example</span>
            <span class="s">Replies within one working day</span></span></div>
        <div class="entry"><span class="ico">&#127970;</span>
          <span class="body"><span class="t">BRAC University, Merul Badda, Dhaka</span>
            <span class="s">Head office</span></span></div>
      </div>
    </div>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
