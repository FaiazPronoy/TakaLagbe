<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head(__('Savings'), __('Put money in a jar and it stays out of reach, so the laptop fund survives Eid shopping.'), null,
                  '<a class="btn" href="#newGoal">' . icon('plus') . e(__('New jar')) . '</a>', badge: ['target', 3]) ?>
    <?= error_note($error) ?>

    <div class="grid cols-3 mb-5">
      <div class="stat rise" style="--n:1"><span class="label"><?= icon('wallet') ?><?= __('Spendable') ?></span>
        <span class="v"><small>৳</small><?= e(money($bal, false)) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label"><?= icon('target') ?><?= __('Saved') ?></span>
        <span class="v pos"><small>৳</small><?= e(money($totalSaved, false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label"><?= icon('star') ?><?= __('Jars') ?></span>
        <span class="v"><?= count($goals) ?> <small><?= __('of 8') ?></small></span></div>
    </div>

    <?php if (!$goals): ?>
      <div class="card reveal">
        <?= empty_state('target', __('No jars yet'),
              __('New laptop? Cox\'s Bazar trip? Name it, set a target, and drop in a little whenever you can.'),
              '<a class="btn" href="#newGoal">' . icon('plus') . e(__('Start my first jar')) . '</a>', 3, 3) ?>
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
                    <?= e(__('By {date}', ['date' => date('j M Y', strtotime($g['deadline']))])) ?> ·
                    <?= e($days < 0 ? __('past the date') : ($days === 1 ? __('1 day left') : __('{n} days left', ['n' => $days]))) ?>
                  <?php else: ?><?= __('No deadline') ?><?php endif; ?>
                </span>
              </div>
              <span class="badge <?= $pct >= 100 ? 'ok' : '' ?>"><?= $pct ?>%</span>
            </div>

            <p style="margin:var(--s4) 0 var(--s2)">
              <span class="num" style="font-size:1.35rem"><?= e(money($g['saved_amount'])) ?></span>
              <span class="muted small"><?= e(__('of {amount}', ['amount' => money($g['target_amount'])])) ?></span>
            </p>
            <div class="meter" role="progressbar" aria-label="<?= e(__('{goal} progress', ['goal' => $g['title']])) ?>"
                 aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $pct ?>%"></span></div>
            <p class="small muted" style="margin:var(--s2) 0 var(--s3)">
              <?= e($left > 0 ? __('{amount} to go.', ['amount' => money($left)]) : __('You did it! Close the jar to spend it, or keep going.')) ?></p>

            <details class="inline">
              <summary class="btn btn-quiet btn-sm"><?= icon('exchange') ?><?= __('Add or take out') ?></summary>
              <form method="post" class="panel" data-once>
                <?= csrf_field() ?>
                <?= once_field('savings_move') ?>
                <input type="hidden" name="goal_id" value="<?= (int) $g['id'] ?>">
                <fieldset>
                  <legend class="sr-only"><?= __('Direction') ?></legend>
                  <div class="choices">
                    <label><input type="radio" name="action" value="deposit" checked> <?= __('Drop some in') ?></label>
                    <label><input type="radio" name="action" value="withdraw"> <?= __('Take some out') ?></label>
                  </div>
                </fieldset>
                <div class="field">
                  <label for="amt<?= (int) $g['id'] ?>"><?= __('Amount') ?></label>
                  <div class="money"><span aria-hidden="true">৳</span>
                    <input id="amt<?= (int) $g['id'] ?>" name="amount" type="number" step="0.01" inputmode="decimal"
                           min="<?= MIN_AMOUNT ?>" required></div>
                  <?= amount_chips('amt' . (int) $g['id'], [500, 1000, 2000]) ?>
                </div>
                <?= pin_field('pin' . (int) $g['id']) ?>
                <button class="btn btn-block" type="submit"><?= __('Confirm') ?></button>
              </form>
            </details>

            <form method="post" style="display:inline"
                  data-confirm="<?= e(__('Close "{goal}"? Everything in it goes back to your balance.', ['goal' => $g['title']])) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="close">
              <input type="hidden" name="goal_id" value="<?= (int) $g['id'] ?>">
              <button class="btn-link small" type="submit"><?= __('Close this jar') ?></button>
            </form>
          </section>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($archived): ?>
      <section class="card reveal mt-5">
        <div class="card-h"><h2><?= icon('check') ?><?= __('Done and dusted') ?></h2></div>
        <ul class="rows">
          <?php foreach ($archived as $g): ?>
            <li class="entry">
              <span class="ico<?= $g['status'] === 'completed' ? ' in' : '' ?>"><?= icon($g['status'] === 'completed' ? 'check' : 'x') ?></span>
              <span class="body"><span class="t"><?= e($g['title']) ?></span>
                <span class="s"><?= e(__('Target {amount}', ['amount' => money($g['target_amount'])])) ?> · <?= e($g['status'] === 'completed' ? __('reached') : __('closed early')) ?></span></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  </div>
</main>

<dialog id="newGoal" aria-labelledby="newGoalTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="<?= e(__('Close')) ?>"><?= icon('x') ?></a>
    <h2 id="newGoalTitle"><?= __('A new savings jar') ?></h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="field">
        <label for="title"><?= __('What\'s it for?') ?></label>
        <input id="title" name="title" required maxlength="60" placeholder="<?= e(__('New laptop, Sajek trip, emergency fund')) ?>">
      </div>
      <div class="field">
        <label for="target_amount"><?= __('How much do you need?') ?></label>
        <div class="money"><span aria-hidden="true">৳</span>
          <input id="target_amount" name="target_amount" type="number" step="0.01" inputmode="decimal"
                 min="<?= MIN_AMOUNT ?>" required></div>
      </div>
      <div class="field">
        <label for="deadline"><?= __('By when?') ?> <span class="muted"><?= __('(optional)') ?></span></label>
        <input id="deadline" name="deadline" type="date" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="#" data-close><?= __('Cancel') ?></a>
        <button class="btn push" type="submit"><?= icon('plus') ?><?= __('Make the jar') ?></button>
      </div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
