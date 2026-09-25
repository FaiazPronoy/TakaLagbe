<?php
/* The customer top bar, the phone tab bar, and the flash message.
   Expects $activeNav to name the current section. */
$activeNav = $activeNav ?? '';
$__user    = current_user();
$__unread  = $__user ? unread_count($__user['phone']) : 0;
$__owed    = $__user
    ? (int) scalar("SELECT COUNT(*) FROM money_requests WHERE payer_phone = ? AND status = 'pending'",
                   [$__user['phone']], 0)
    : 0;
$__links = [
    'account.php'   => ['Home',     'home',      'home'],
    'history.php'   => ['History',  'history',   'history'],
    'analytics.php' => ['Insights', 'analytics', 'chart'],
    'savings.php'   => ['Savings',  'savings',   'target'],
    'requests.php'  => ['Requests', 'requests',  'exchange'],
];
$__here = static fn(string $key): string => $activeNav === $key ? ' aria-current="page"' : '';
?>
<header class="bar">
  <div class="bar-in">
    <a class="brand" href="account.php"><span class="mark">৳</span><?= e(APP_NAME) ?></a>

    <nav class="links" aria-label="Main">
      <?php foreach ($__links as $href => [$label, $key, $ico]): ?>
        <a href="<?= $href ?>"<?= $__here($key) ?>><?= icon($ico) ?><?= e($label) ?>
          <?php if ($key === 'requests' && $__owed): ?><span class="badge warn"><?= $__owed ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="tools">
      <button class="tool theme-btn" id="themeBtn" type="button" aria-label="Switch theme" aria-pressed="false">
        <span class="i-moon"><?= icon('moon') ?></span><span class="i-sun"><?= icon('sun') ?></span>
      </button>
      <a class="tool" href="notifications.php"<?= $__here('notifications') ?>
         aria-label="Notifications<?= $__unread ? ', ' . $__unread . ' unread' : '' ?>">
        <?= icon('bell') ?>
        <?php if ($__unread): ?><span class="dot" aria-hidden="true"><?= $__unread > 9 ? '9+' : $__unread ?></span><?php endif; ?>
      </a>
      <a class="tool" href="profile.php"<?= $__here('profile') ?> aria-label="Profile and security"><?= icon('user') ?></a>
      <form class="hide-m" method="post" action="logout.php">
        <?= csrf_field() ?>
        <button class="tool" type="submit" aria-label="Sign out" title="Sign out"><?= icon('logout') ?></button>
      </form>
    </div>
  </div>
</header>

<nav class="tabs-m" aria-label="Main">
  <?php foreach ($__links as $href => [$label, $key, $ico]): ?>
    <a href="<?= $href ?>"<?= $__here($key) ?>><?= icon($ico) ?><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($__flash = take_flash()): ?>
  <div class="note toast <?= $__flash[0] === 'error' ? 'bad' : 'ok' ?>" id="toast" role="status">
    <?= icon($__flash[0] === 'error' ? 'alert' : 'check') ?>
    <span><?= e($__flash[1]) ?></span>
  </div>
<?php endif; ?>
