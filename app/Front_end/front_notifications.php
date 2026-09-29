<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$icons = ['in' => 'arrow-in', 'out' => 'arrow-out', 'gift' => 'gift', 'lock' => 'lock',
          'request' => 'hand', 'bell' => 'bell'];
$actions = '';
if ($unread) {
    $actions .= '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="read_all">'
              . '<button class="btn btn-quiet" type="submit">' . icon('check') . e(__('Mark all read')) . '</button></form>';
}
$actions .= '<form method="post" data-confirm="' . e(__('Delete every notification you have already read?')) . '">' . csrf_field()
          . '<input type="hidden" name="action" value="clear">'
          . '<button class="btn btn-quiet" type="submit">' . icon('trash') . e(__('Clear read')) . '</button></form>';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head(__('Notifications'), '', null, $actions, badge: ['bell', 3]) ?>

    <nav class="tabs rise" style="--n:1" aria-label="<?= e(__('Filter')) ?>">
      <a href="?show=all"<?= $filter === 'all' ? ' aria-current="page"' : '' ?>><?= __('All') ?></a>
      <a href="?show=unread"<?= $filter === 'unread' ? ' aria-current="page"' : '' ?>><?= __('Unread') ?>
        <?php if ($unread): ?><span class="badge warn"><?= $unread ?></span><?php endif; ?></a>
    </nav>

    <section class="card reveal" aria-label="<?= e(__('Notifications')) ?>">
      <?php if (!$rows): ?>
        <?= $filter === 'unread'
            ? empty_state('check', __('All caught up'), __('Inbox zero energy. New stuff lands here the moment it happens.'), '', 3, 2)
            : empty_state('bell', __('Nothing yet'), __('Money in, bills paid, someone asking for money: you hear about it here first.'), '', 3, 3) ?>
      <?php else: ?>
        <ul class="rows">
          <?php foreach ($rows as $n): ?>
            <li class="entry" style="align-items:flex-start">
              <span class="ico<?= $n['icon'] === 'in' ? ' in' : ($n['icon'] === 'out' ? ' out' : '') ?>"><?= icon($icons[$n['icon']] ?? 'bell') ?></span>
              <span class="body">
                <span class="t" style="white-space:normal"><?= e(__notice($n['title'])) ?>
                  <?php if (!$n['read_status']): ?><span class="badge warn"><?= __('new') ?></span><?php endif; ?></span>
                <span class="s wrap-ok"><?= e(__notice($n['body'])) ?></span>
                <span class="s"><?= e(time_ago($n['created_at'])) ?></span>
              </span>
              <?php if (!$n['read_status']): ?>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="read_one">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <button class="tool" type="submit" aria-label="<?= e(__('Mark as read')) ?>"><?= icon('check') ?></button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
