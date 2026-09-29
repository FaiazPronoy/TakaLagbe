<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Support desk', $counts['open'] . ' open, ' . $counts['solved'] . ' solved. A reply goes to the customer\'s notifications.',
                  null, '', 'Back office') ?>
    <?= error_note($error) ?>

    <nav class="tabs rise" style="--n:1" aria-label="Filter tickets">
      <?php foreach (['open' => 'Open', 'solved' => 'Solved', 'all' => 'All'] as $k => $l): ?>
        <a href="?show=<?= $k ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= $l ?>
          <?php if ($k === 'open' && $counts['open']): ?><span class="badge warn"><?= $counts['open'] ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if (!$tickets): ?>
      <div class="card reveal"><?= empty_state('check', 'The queue is clear',
          $filter === 'open' ? 'No customer is waiting on a reply.' : 'Nothing in this list yet.') ?></div>
    <?php else: ?>
      <div class="grid cols-2">
        <?php foreach ($tickets as $i => $t): ?>
          <article class="card reveal" style="--n:<?= $i % 2 ?>" aria-labelledby="t<?= (int) $t['id'] ?>">
            <div class="between" style="align-items:flex-start">
              <div>
                <h2 id="t<?= (int) $t['id'] ?>" style="font-size:var(--step-1);margin:0">#<?= (int) $t['id'] ?> · <?= e($t['user_name']) ?></h2>
                <span class="small muted"><?= e($t['user_email']) ?><?= $t['user_phone'] ? ' · ' . e($t['user_phone']) : '' ?>
                  · <?= e(short_date($t['timestamp'])) ?></span>
              </div>
              <span class="badge <?= $t['issue_solved'] ? 'ok' : 'warn' ?>"><?= $t['issue_solved'] ? 'solved' : 'open' ?></span>
            </div>
            <p style="margin:var(--s4) 0;white-space:pre-wrap"><?= e($t['message']) ?></p>

            <?php if (!$t['issue_solved']): ?>
              <form method="post" data-once>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <div class="field"><label for="reply<?= (int) $t['id'] ?>">Reply</label>
                  <textarea id="reply<?= (int) $t['id'] ?>" name="reply" required minlength="3" maxlength="200" style="min-height:80px"></textarea></div>
                <div class="row">
                  <button class="btn" type="submit">Reply and close</button>
                  <button class="btn btn-quiet" type="submit" name="action" value="solve" formnovalidate>Close without reply</button>
                </div>
              </form>
            <?php else: ?>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="reopen">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <button class="btn btn-quiet btn-sm" type="submit">Reopen</button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
