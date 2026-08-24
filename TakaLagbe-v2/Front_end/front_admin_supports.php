<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Support desk</h1>
    <span class="tiny muted"><?= $counts['open'] ?> open &middot; <?= $counts['solved'] ?> solved</span>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="tabs">
    <a href="?show=open"   class="<?= $filter === 'open'   ? 'on' : '' ?>">Open
      <?php if ($counts['open']): ?><span class="badge badge-pending"><?= $counts['open'] ?></span><?php endif; ?></a>
    <a href="?show=solved" class="<?= $filter === 'solved' ? 'on' : '' ?>">Solved</a>
    <a href="?show=all"    class="<?= $filter === 'all'    ? 'on' : '' ?>">All</a>
  </div>

  <?php if (!$tickets): ?>
    <div class="card empty">
      <div class="big">&#127881;</div>
      <p class="mb0">Nothing here. The queue is clear.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-2">
      <?php foreach ($tickets as $t): ?>
        <div class="card">
          <div class="between" style="align-items:flex-start">
            <div>
              <strong>#<?= (int) $t['id'] ?> &middot; <?= e($t['user_name']) ?></strong>
              <div class="tiny muted">
                <?= e($t['user_email']) ?>
                <?= $t['user_phone'] ? ' &middot; ' . e($t['user_phone']) : '' ?>
              </div>
              <div class="tiny muted"><?= e(short_date($t['timestamp'])) ?></div>
            </div>
            <span class="badge <?= $t['issue_solved'] ? 'badge-ok' : 'badge-pending' ?>">
              <?= $t['issue_solved'] ? 'solved' : 'open' ?></span>
          </div>

          <p style="margin:.8rem 0;white-space:pre-wrap"><?= e($t['message']) ?></p>

          <?php if (!$t['issue_solved']): ?>
            <form method="post" data-once>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="reply">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <div class="field">
                <textarea name="reply" required minlength="3" maxlength="200" style="min-height:70px"
                          placeholder="Write a short reply. It goes straight to their notifications."></textarea>
              </div>
              <div style="display:flex;gap:.5rem">
                <button class="btn btn-primary btn-block" type="submit">Reply and close</button>
              </div>
            </form>
            <form method="post" style="margin-top:.5rem">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="solve">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <button class="btn btn-ghost btn-sm btn-block" type="submit">Close without replying</button>
            </form>
          <?php else: ?>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="reopen">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit">Reopen</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
