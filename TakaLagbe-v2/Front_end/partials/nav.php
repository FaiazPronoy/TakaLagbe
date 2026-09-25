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
    'requests.php'  => ['Requests', 'requests',  'exchange'],
    'savings.php'   => ['Savings',  'savings',   'target'],
    'analytics.php' => ['Insights', 'analytics', 'chart'],
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
         aria-label="Notifications<?= $__unread ? ', ' . $__unread . ' unread' : '' ?>" title="Notifications">
        <?= icon('bell') ?>
        <?php if ($__unread): ?><span class="dot" aria-hidden="true"><?= $__unread > 9 ? '9+' : $__unread ?></span><?php endif; ?>
      </a>
      <?php if ($__user): ?>
        <details class="menu">
          <summary aria-label="Your account">
            <?= avatar($__user['firstname'], $__user['lastname'], $__user['avatar_color']) ?>
            <span class="menu-name hide-m"><?= e($__user['firstname']) ?></span>
          </summary>
          <div class="menu-box">
            <p class="menu-who"><strong><?= e($__user['firstname'] . ' ' . $__user['lastname']) ?></strong>
              <span class="small muted"><?= e($__user['phone']) ?></span></p>
            <a href="profile.php"><?= icon('user') ?>Profile and security</a>
            <a href="qr.php"><?= icon('qr') ?>My QR code</a>
            <a href="supports.php"><?= icon('lifebuoy') ?>Help</a>
            <form method="post" action="logout.php">
              <?= csrf_field() ?>
              <button type="submit"><?= icon('logout') ?>Sign out</button>
            </form>
          </div>
        </details>
      <?php endif; ?>
    </div>
  </div>
</header>

<nav class="tabs-m" aria-label="Main">
  <a href="account.php"<?= $__here('home') ?>><?= icon('home') ?>Home</a>
  <a href="history.php"<?= $__here('history') ?>><?= icon('history') ?>History</a>
  <a href="sendmoney.php" class="tab-send"<?= $__here('send') ?>><span><?= icon('send') ?></span>Send</a>
  <a href="requests.php"<?= $__here('requests') ?>><?= icon('exchange') ?>Requests</a>
  <a href="profile.php"<?= $__here('profile') ?>><?= icon('user') ?>Me</a>
</nav>

<?php if ($__flash = take_flash()): ?>
  <div class="note toast <?= $__flash[0] === 'error' ? 'bad' : 'ok' ?>" id="toast" role="status">
    <?= icon($__flash[0] === 'error' ? 'alert' : 'check') ?>
    <span><?= e($__flash[1]) ?></span>
  </div>
<?php endif; ?>
