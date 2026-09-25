<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Savings', 'Money in a goal leaves your spendable balance, so it cannot be spent by accident.', null,
                  '<a class="btn" href="#newGoal">' . icon('plus') . 'New goal</a>') ?>
    <?= error_note($error) ?>

    <div class="grid cols-3 mb-5">
      <div class="stat rise" style="--n:1"><span class="label">Spendable</span>
        <span class="v"><small>৳</small><?= e(money($bal, false)) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label">Set aside</span>
        <span class="v pos"><small>৳</small><?= e(money($totalSaved, false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label">Open goals</span>
        <span class="v"><?= count($goals) ?> <small>of 8</small></span></div>
    </div>

    <?php if (!$goals): ?>
      <div class="card reveal">
        <?= empty_state('target', 'No goals yet',
              'Name something you are saving for, like a laptop or Eid, set a target, and move a little in whenever you can.',
              '<a class="btn" href="#newGoal">Create your first goal</a>') ?>
      </div>
    <?php else: ?>
      <div class="grid cols-2">
        <?php foreach ($goals as $i => $g):
          $pct  = (float) $g['target_amount'] > 0
                ? min(100, round((float) $g['saved_amount'] / (float) $g['target_amount'] * 100)) : 0;
          $left = max(0, (float) $g['target_amount'] - (float) $g['saved_amount']);
          $days = $g['deadline'] ? (int) floor((strtotime($g['deadline']) - strtotime('today')) / 86400) : null; ?>
          <section class="card lift reveal" style="--n:<?= $i % 2 ?>" aria-labelledby="g<?= (int) $g['id'] ?>">
            <div class="between" style="align-items:flex-start">
              <div>
                <h2 id="g<?= (int) $g['id'] ?>" style="font-size:var(--step-1);margin:0"><?= e($g['title']) ?></h2>
                <span class="small muted">
                  <?php if ($g['deadline']): ?>
                    By <?= e(date('j M Y', strtotime($g['deadline']))) ?> ·
                    <?= $days >= 0 ? $days . ' day' . ($days === 1 ? '' : 's') . ' left' : 'past the date' ?>
                  <?php else: ?>No deadline<?php endif; ?>
                </span>
              </div>
              <span class="badge <?= $pct >= 100 ? 'ok' : '' ?>"><?= $pct ?>%</span>
            </div>

            <p style="margin:var(--s4) 0 var(--s2)">
              <span class="num" style="font-size:1.35rem"><?= e(money($g['saved_amount'])) ?></span>
              <span class="muted small">of <?= e(money($g['target_amount'])) ?></span>
            </p>
            <div class="meter" role="progressbar" aria-label="<?= e($g['title']) ?> progress"
                 aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $pct ?>%"></span></div>
            <p class="small muted" style="margin:var(--s2) 0 var(--s3)">
              <?= $left > 0 ? e(money($left)) . ' to go.' : 'Target reached. Close it to move the money back, or keep saving.' ?></p>

            <details class="inline">
              <summary class="btn btn-quiet btn-sm">Move money</summary>
              <form method="post" class="panel" data-once>
                <?= csrf_field() ?>
                <?= once_field('savings_move') ?>
                <input type="hidden" name="goal_id" value="<?= (int) $g['id'] ?>">
                <fieldset>
                  <legend class="sr-only">Direction</legend>
                  <div class="choices">
                    <label><input type="radio" name="action" value="deposit" checked> Put money in</label>
                    <label><input type="radio" name="action" value="withdraw"> Take some out</label>
                  </div>
                </fieldset>
                <div class="field">
                  <label for="amt<?= (int) $g['id'] ?>">Amount</label>
                  <div class="money"><span aria-hidden="true">৳</span>
                    <input id="amt<?= (int) $g['id'] ?>" name="amount" type="number" step="0.01" inputmode="decimal"
                           min="<?= MIN_AMOUNT ?>" required></div>
                  <?= amount_chips('amt' . (int) $g['id'], [500, 1000, 2000]) ?>
                </div>
                <?= pin_field('pin' . (int) $g['id']) ?>
                <button class="btn btn-block" type="submit">Confirm</button>
              </form>
            </details>

            <form method="post" style="display:inline"
                  data-confirm="Close &quot;<?= e($g['title']) ?>&quot;? Everything in it goes back to your balance.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="close">
              <input type="hidden" name="goal_id" value="<?= (int) $g['id'] ?>">
              <button class="btn-link small" type="submit">Close this goal</button>
            </form>
          </section>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($archived): ?>
      <section class="card reveal mt-5">
        <div class="card-h"><h2>Finished</h2></div>
        <ul class="rows">
          <?php foreach ($archived as $g): ?>
            <li class="entry">
              <span class="ico<?= $g['status'] === 'completed' ? ' in' : '' ?>"><?= icon($g['status'] === 'completed' ? 'check' : 'x') ?></span>
              <span class="body"><span class="t"><?= e($g['title']) ?></span>
                <span class="s">Target <?= e(money($g['target_amount'])) ?> · <?= $g['status'] === 'completed' ? 'reached' : 'closed early' ?></span></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  </div>
</main>

<dialog id="newGoal" aria-labelledby="newGoalTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="newGoalTitle">A new goal</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="field">
        <label for="title">What are you saving for?</label>
        <input id="title" name="title" required maxlength="60" placeholder="New laptop, Eid, emergency fund">
      </div>
      <div class="field">
        <label for="target_amount">Target</label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="target_amount" name="target_amount" type="number" step="0.01" inputmode="decimal"
                 min="<?= MIN_AMOUNT ?>" required></div>
      </div>
      <div class="field">
        <label for="deadline">By when? <span class="muted">(optional)</span></label>
        <input id="deadline" name="deadline" type="date" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close>Cancel</a>
        <button class="btn push" type="submit">Create goal</button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
