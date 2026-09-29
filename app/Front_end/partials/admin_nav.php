<?php
/* Layout for the admin side. Expects $pageTitle and $activeNav. */
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';
$bodyClass = 'admin';
$__openTickets = (int) scalar('SELECT COUNT(*) FROM support_messages WHERE issue_solved = 0', [], 0);
$__links = [
    'admin_dashboard.php' => ['Overview',   'dash'],
    'admin_user.php'      => ['Users',      'users'],
    'admin_merchant.php'  => ['Billers',    'merchant'],
    'admin_education.php' => ['Institutes', 'edu'],
    'admin_supports.php'  => ['Support',    'support'],
    'admin_issue.php'     => ['Audit',      'audit'],
];
include __DIR__ . '/head.php';
?>
<header class="bar">
  <div class="bar-in">
    <a class="brand" href="admin_dashboard.php"><span class="mark">৳</span><?= e(APP_NAME) ?><small>Admin</small></a>

    <nav class="links" aria-label="Admin">
      <?php foreach ($__links as $href => [$label, $key]): ?>
        <a href="<?= $href ?>"<?= $activeNav === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?>
          <?php if ($key === 'support' && $__openTickets): ?>
            <span class="badge warn"><?= $__openTickets ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="tools">
      <button class="tool theme-btn" id="themeBtn" type="button" aria-label="Switch theme" aria-pressed="false">
        <span class="i-moon"><?= icon('moon') ?></span><span class="i-sun"><?= icon('sun') ?></span>
      </button>
      <form method="post" action="adminlogout.php">
        <?= csrf_field() ?>
        <button class="tool" type="submit" aria-label="Sign out" title="Sign out"><?= icon('logout') ?></button>
      </form>
    </div>
  </div>
</header>

<?php if ($__flash = take_flash()): ?>
  <div class="note toast <?= $__flash[0] === 'error' ? 'bad' : 'ok' ?>" id="toast" role="status">
    <?= icon($__flash[0] === 'error' ? 'alert' : 'check') ?>
    <span><?= e($__flash[1]) ?></span>
  </div>
<?php endif; ?>
