<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$icons = ['in' => "\u{1F4B0}", 'out' => "\u{1F4B8}", 'gift' => "\u{1F381}",
          'lock' => "\u{1F512}", 'request' => "\u{1F91D}", 'bell' => "\u{1F514}"];
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Notifications</h1>
    <div style="display:flex;gap:.4rem">
      <?php if ($unread): ?>
        <form method="post"><?= csrf_field() ?>
          <input type="hidden" name="action" value="read_all">
          <button class="btn btn-ghost btn-sm" type="submit">Mark all read</button>
        </form>
      <?php endif; ?>
      <form method="post" data-confirm="Delete every notification you have already read?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="clear">
        <button class="btn btn-ghost btn-sm" type="submit">Clear read</button>
      </form>
    </div>
  </div>

  <div class="tabs">
    <a href="?show=all"    class="<?= $filter === 'all'    ? 'on' : '' ?>">All</a>
    <a href="?show=unread" class="<?= $filter === 'unread' ? 'on' : '' ?>">
      Unread <?php if ($unread): ?><span class="badge badge-pending"><?= $unread ?></span><?php endif; ?>
    </a>
  </div>

  <div class="card">
    <?php if (!$rows): ?>
      <div class="empty">
        <div class="big">&#128276;</div>
        <p class="mb0"><?= $filter === 'unread' ? 'Nothing unread.' : 'No notifications yet.' ?></p>
      </div>
    <?php else: foreach ($rows as $n): ?>
      <div class="entry" style="<?= $n['read_status'] ? '' : 'background:var(--brand-soft);
           margin:0 -1.25rem;padding-left:1.25rem;padding-right:1.25rem' ?>">
        <span class="ico"><?= $icons[$n['icon']] ?? $icons['bell'] ?></span>
        <span class="body">
          <span class="t"><?= e($n['title']) ?>
            <?php if (!$n['read_status']): ?>
              <span class="badge badge-pending">new</span>
            <?php endif; ?>
          </span>
          <span class="s" style="white-space:normal"><?= e($n['body']) ?></span>
          <span class="tiny muted"><?= e(time_ago($n['created_at'])) ?></span>
        </span>
        <?php if (!$n['read_status']): ?>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="read_one">
            <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit" title="Mark as read">&#10003;</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
