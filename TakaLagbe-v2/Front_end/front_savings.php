<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$pinBoxes = static function (string $id): void { ?>
  <div class="pin" data-pin="<?= e($id) ?>">
    <?php for ($i = 0; $i < 6; $i++): ?>
      <input type="password" inputmode="numeric" maxlength="1" aria-label="PIN digit <?= $i + 1 ?>">
    <?php endfor; ?>
  </div>
  <input type="hidden" name="pin" id="<?= e($id) ?>" required pattern="\d{6}">
<?php };
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Savings goals</h1>
    <button class="btn btn-primary btn-sm" onclick="openModal('newGoal')">New goal</button>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="row" style="margin-bottom:1.2rem">
    <div class="col"><div class="stat"><div class="k">Spendable balance</div>
      <div class="v"><?= e(money($bal, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Put aside in goals</div>
      <div class="v pos"><?= e(money($totalSaved, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Open goals</div>
      <div class="v"><?= count($goals) ?></div></div></div>
  </div>

  <?php if (!$goals): ?>
    <div class="card empty">
      <div class="big">&#127919;</div>
      <h3>No goals yet</h3>
      <p class="muted">Set a target, put a little aside each week, and watch it fill up.</p>
      <button class="btn btn-primary" onclick="openModal('newGoal')">Create your first goal</button>
    </div>
  <?php else: ?>
    <div class="grid grid-2">
      <?php foreach ($goals as $g):
        $pct  = min(100, (float) $g['target_amount'] > 0
              ? round((float) $g['saved_amount'] / (float) $g['target_amount'] * 100) : 0);
        $left = max(0, (float) $g['target_amount'] - (float) $g['saved_amount']);
        $days = $g['deadline'] ? (int) floor((strtotime($g['deadline']) - time()) / 86400) : null;
      ?>
        <div class="card">
          <div class="between" style="align-items:flex-start">
            <div>
              <h3 class="mb0"><?= e($g['title']) ?></h3>
              <div class="tiny muted">
                <?php if ($g['deadline']): ?>
                  Due <?= e(date('d M Y', strtotime($g['deadline']))) ?>
                  <?= $days !== null ? ' &middot; ' . ($days >= 0 ? "$days days left" : 'overdue') : '' ?>
                <?php else: ?>
                  No deadline
                <?php endif; ?>
              </div>
            </div>
            <span class="badge <?= $pct >= 100 ? 'badge-ok' : '' ?>"><?= $pct ?>%</span>
          </div>

          <div style="margin:.9rem 0 .4rem;display:flex;justify-content:space-between;
                      font-weight:800;font-size:1.1rem">
            <span><?= e(money($g['saved_amount'])) ?></span>
            <span class="muted" style="font-weight:600;font-size:.9rem">
              of <?= e(money($g['target_amount'])) ?></span>
          </div>
          <div class="bar"><span style="width:<?= $pct ?>%"></span></div>
          <p class="tiny muted" style="margin-top:.5rem">
            <?= $left > 0 ? e(money($left)) . ' to go' : 'Target reached, well done' ?>
          </p>

          <div style="display:flex;gap:.4rem;flex-wrap:wrap">
            <button class="btn btn-primary btn-sm"
                    onclick="prepGoal(<?= (int) $g['id'] ?>, 'deposit', <?= json_encode($g['title']) ?>)">
              Add money
            </button>
            <button class="btn btn-ghost btn-sm"
                    onclick="prepGoal(<?= (int) $g['id'] ?>, 'withdraw', <?= json_encode($g['title']) ?>)">
              Take out
            </button>
            <form method="post" style="display:inline"
                  data-confirm="Close this goal? Everything saved goes back to your balance.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="close">
              <input type="hidden" name="goal_id" value="<?= (int) $g['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit">Close</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($archived): ?>
    <div class="card" style="margin-top:1.4rem">
      <div class="card-title">Finished goals</div>
      <?php foreach ($archived as $g): ?>
        <div class="entry">
          <span class="ico"><?= $g['status'] === 'completed' ? '&#127942;' : '&#128465;' ?></span>
          <span class="body">
            <span class="t"><?= e($g['title']) ?></span>
            <span class="s">Target <?= e(money($g['target_amount'])) ?>
              &middot; <?= e(ucfirst($g['status'])) ?></span>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<!-- new goal modal -->
<div class="modal" id="newGoal">
  <div class="modal-box">
    <h2>New savings goal</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="field">
        <label for="title">What are you saving for?</label>
        <input id="title" name="title" required maxlength="60" placeholder="New laptop, Eid, emergency fund">
      </div>
      <div class="field">
        <label for="target_amount">Target amount</label>
        <div class="money-input">
          <span><?= CURRENCY_SIGN ?></span>
          <input id="target_amount" name="target_amount" type="number" step="0.01"
                 min="<?= MIN_AMOUNT ?>" required placeholder="0.00">
        </div>
      </div>
      <div class="field">
        <label for="deadline">Deadline <span class="muted tiny">(optional)</span></label>
        <input id="deadline" name="deadline" type="date" min="<?= date('Y-m-d') ?>">
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('newGoal')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Create goal</button>
      </div>
    </form>
  </div>
</div>

<!-- move money modal -->
<div class="modal" id="moveGoal">
  <div class="modal-box">
    <h2 id="moveTitle">Add money</h2>
    <p class="muted tiny" id="moveSub"></p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <?= once_field('savings_move') ?>
      <input type="hidden" name="action" id="moveAction" value="deposit">
      <input type="hidden" name="goal_id" id="moveGoalId">
      <div class="field">
        <label for="moveAmount">Amount</label>
        <div class="money-input">
          <span><?= CURRENCY_SIGN ?></span>
          <input id="moveAmount" name="amount" type="number" step="0.01"
                 min="<?= MIN_AMOUNT ?>" required placeholder="0.00">
        </div>
        <div class="chips">
          <?php foreach ([200, 500, 1000, 2000] as $v): ?>
            <button type="button" class="chip" data-chip-target="moveAmount" data-value="<?= $v ?>">
              <?= CURRENCY_SIGN ?> <?= number_format($v) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field">
        <label>Transaction PIN</label>
        <?php $pinBoxes('goalPin'); ?>
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('moveGoal')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Confirm</button>
      </div>
    </form>
  </div>
</div>

<script>
function prepGoal(id, action, title) {
  document.getElementById('moveGoalId').value = id;
  document.getElementById('moveAction').value = action;
  document.getElementById('moveTitle').textContent =
    action === 'deposit' ? 'Add money to this goal' : 'Take money back out';
  document.getElementById('moveSub').textContent = title;
  openModal('moveGoal');
}
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
