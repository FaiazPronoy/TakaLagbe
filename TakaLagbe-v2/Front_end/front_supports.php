<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Support', 'Something off? Tell us. Replies land in your notifications.', ['account.php', 'Home'], badge: ['lifebuoy', 6]) ?>
    <?= error_note($error) ?>

    <div class="split">
      <div class="stack">
        <section class="card rise" style="--n:1" aria-labelledby="askTitle">
          <div class="card-h"><h2 id="askTitle"><?= icon('lifebuoy') ?>Write to us</h2></div>
          <form method="post" data-once>
            <?= csrf_field() ?>
            <p class="small muted">From <?= e($user['firstname'] . ' ' . $user['lastname']) ?> · <?= e($user['email']) ?></p>
            <div class="field">
              <label for="message">What happened? Spill it.</label>
              <textarea id="message" name="message" required minlength="10" maxlength="2000"
                        aria-describedby="msgHint"><?= e(post('message')) ?></textarea>
              <span class="hint" id="msgHint">If it is about a payment, the reference from the receipt helps most.</span>
            </div>
            <button class="btn" type="submit"><?= icon('send') ?>Send</button>
          </form>
        </section>

        <section class="card reveal" aria-labelledby="tkTitle">
          <div class="card-h"><h2 id="tkTitle"><?= icon('list') ?>Your tickets</h2></div>
          <?php if (!$tickets): ?>
            <p class="muted small m0">None yet. Anything you send appears here with its status.</p>
          <?php else: ?>
            <ul class="rows">
              <?php foreach ($tickets as $t): ?>
                <li class="entry" style="align-items:flex-start">
                  <span class="ico<?= $t['issue_solved'] ? ' in' : '' ?>"><?= icon($t['issue_solved'] ? 'check' : 'lifebuoy') ?></span>
                  <span class="body"><span class="t" style="white-space:normal"><?= e(mb_strimwidth($t['message'], 0, 110, '…')) ?></span>
                    <span class="s"><?= e(short_date($t['timestamp'])) ?></span></span>
                  <span class="badge <?= $t['issue_solved'] ? 'ok' : 'warn' ?>"><?= $t['issue_solved'] ? 'solved' : 'open' ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>
      </div>

      <section class="card reveal faq" style="--n:1" aria-labelledby="faqTitle">
        <div class="card-h"><h2 id="faqTitle"><?= icon('info') ?>People usually ask</h2></div>
        <?php foreach ($faq as [$q, $a]): ?>
          <details><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
        <?php endforeach; ?>
        <p class="small muted m0 mt-4" style="border-top:1px solid var(--line);padding-top:var(--s3)">
          We usually reply within a day. The answer lands in your notifications.</p>
      </section>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
