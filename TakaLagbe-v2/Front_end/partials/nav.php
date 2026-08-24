<?php
/* The top bar. Expects $activeNav to name the current page. */
$activeNav = $activeNav ?? '';
$__links = [
    'account.php'   => ['Home',    'home'],
    'history.php'   => ['History', 'history'],
    'analytics.php' => ['Insights','analytics'],
    'savings.php'   => ['Savings', 'savings'],
    'requests.php'  => ['Requests','requests'],
];
$__pendingReq = $__user
    ? (int) scalar("SELECT COUNT(*) FROM money_requests WHERE payer_phone = ? AND status = 'pending'",
                   [$__user['phone']], 0)
    : 0;
?>
<nav class="nav">
  <div class="nav-inner">
    <a class="brand" href="account.php">
      <span class="brand-mark">T</span><?= e(APP_NAME) ?>
    </a>

    <button class="nav-toggle" onclick="toggleNav()" aria-label="Menu">&#9776;</button>

    <div class="nav-links" id="navLinks">
      <?php foreach ($__links as $href => $l): ?>
        <a href="<?= $href ?>" class="<?= $activeNav === $l[1] ? 'active' : '' ?>"><?= $l[0] ?>
          <?php if ($l[1] === 'requests' && $__pendingReq): ?>
            <span class="badge badge-pending"><?= $__pendingReq ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <a href="notifications.php" class="bell <?= $activeNav === 'notifications' ? 'active' : '' ?>"
         title="Notifications">&#128276;
        <?php if ($__unread): ?><span class="dot"><?= $__unread > 9 ? '9+' : $__unread ?></span><?php endif; ?>
      </a>

      <a href="profile.php" class="<?= $activeNav === 'profile' ? 'active' : '' ?>">Profile</a>

      <button id="themeBtn" onclick="toggleTheme()" title="Switch theme">&#127769;</button>
      <span id="clock" class="tiny muted" style="padding:0 .5rem"></span>

      <a href="logout.php" class="btn btn-ghost btn-sm">Log out</a>
    </div>
  </div>
</nav>

<?php
$__flash = take_flash();
if ($__flash):
    $map = ['ok' => 'alert-ok', 'error' => 'alert-error', 'warn' => 'alert-warn', 'info' => 'alert-info'];
?>
  <div class="alert <?= $map[$__flash[0]] ?? 'alert-info' ?> toast" id="flashToast">
    <span><?= e($__flash[1]) ?></span>
  </div>
<?php endif; ?>
