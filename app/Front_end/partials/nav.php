<?php
/* The customer top bar (the brand and the account tools), the dock at the
   bottom that holds the main places, and the flash message.
   Expects $activeNav to name the current section. */
$activeNav = $activeNav ?? '';
$__user    = current_user();
$__unread  = $__user ? unread_count($__user['phone']) : 0;
$__owed    = $__user
    ? (int) scalar("SELECT COUNT(*) FROM money_requests WHERE payer_phone = ? AND status = 'pending'",
                   [$__user['phone']], 0)
    : 0;
$__here = static fn(string $key): string => $activeNav === $key ? ' aria-current="page"' : '';
?>
<header class="bar">
  <div class="bar-in">
    <a class="brand" href="account.php"><span class="mark">৳</span><?= e(APP_NAME) ?></a>


    <div class="tools">
      <?= lang_button() ?>
      <button class="tool theme-btn" id="themeBtn" type="button" aria-label="<?= e(__('Switch theme')) ?>" aria-pressed="false">
        <span class="i-moon"><?= icon('moon') ?></span><span class="i-sun"><?= icon('sun') ?></span>
      </button>
      <a class="tool" href="notifications.php"<?= $__here('notifications') ?>
         aria-label="<?= e($__unread ? __('Notifications, {n} unread', ['n' => $__unread]) : __('Notifications')) ?>" title="<?= e(__('Notifications')) ?>">
        <?= icon('bell') ?>
        <?php if ($__unread): ?><span class="dot" aria-hidden="true"><?= $__unread > 9 ? '9+' : $__unread ?></span><?php endif; ?>
      </a>
      <?php if ($__user): ?>
        <details class="menu">
          <summary aria-label="<?= e(__('Your account')) ?>">
            <?= avatar($__user['firstname'], $__user['lastname'], $__user['avatar_color']) ?>
            <span class="menu-name hide-m"><?= e($__user['firstname']) ?></span>
          </summary>
          <div class="menu-box">
            <p class="menu-who"><strong><?= e($__user['firstname'] . ' ' . $__user['lastname']) ?></strong>
              <span class="small muted"><?= e($__user['phone']) ?></span></p>
            <a href="profile.php"><?= icon('user') ?><?= __('Profile and security') ?></a>
            <a href="qr.php"><?= icon('qr') ?><?= __('My QR code') ?></a>
            <a href="supports.php"><?= icon('lifebuoy') ?><?= __('Help') ?></a>
            <form method="post" action="logout.php">
              <?= csrf_field() ?>
              <button type="submit"><?= icon('logout') ?><?= __('Sign out') ?></button>
            </form>
          </div>
        </details>
      <?php endif; ?>
    </div>
  </div>
</header>

<nav class="tabs-m" aria-label="<?= e(__('Main')) ?>">
  <a href="account.php"<?= $__here('home') ?>><?= icon('home') ?><?= __('Home') ?></a>
  <a href="history.php"<?= $__here('history') ?>><?= icon('history') ?><?= __('History') ?></a>
  <a href="savings.php" class="wide"<?= $__here('savings') ?>><?= icon('target') ?><?= __('Savings') ?></a>
  <a href="sendmoney.php" class="tab-send"<?= $__here('send') ?>><span><?= icon('send') ?></span><?= __('Send') ?></a>
  <a href="requests.php"<?= $__here('requests') ?>><?= icon('exchange') ?><?= __('Requests') ?><?php if ($__owed): ?><span class="count" aria-label="<?= e(__('{n} waiting', ['n' => $__owed])) ?>"><?= $__owed ?></span><?php endif; ?></a>
  <a href="analytics.php" class="wide"<?= $__here('analytics') ?>><?= icon('chart') ?><?= __('Insights') ?></a>
  <a href="profile.php"<?= $__here('profile') ?>><?= icon('user') ?><?= __('Me') ?></a>
</nav>

<?php if ($__flash = take_flash()): ?>
  <div class="note toast <?= $__flash[0] === 'error' ? 'bad' : 'ok' ?>" id="toast" role="status">
    <?= icon($__flash[0] === 'error' ? 'alert' : 'check') ?>
    <span><?= e($__flash[1]) ?></span>
  </div>
<?php endif; ?>
