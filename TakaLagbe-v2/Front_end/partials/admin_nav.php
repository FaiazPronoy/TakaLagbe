<?php
/* Layout for the admin side. Expects $pageTitle and $activeNav. */
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';
$__openTickets = (int) scalar('SELECT COUNT(*) FROM support_messages WHERE issue_solved = 0', [], 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?> admin</title>
<link rel="stylesheet" href="Assets/css/app.css">
<script>(function(){try{var t=localStorage.getItem('tkl-theme');
 if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}
 document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<nav class="nav">
  <div class="nav-inner">
    <a class="brand" href="admin_dashboard.php">
      <span class="brand-mark" style="background:linear-gradient(135deg,#495057,#212529)">A</span>
      <?= e(APP_NAME) ?> <span class="badge" style="margin-left:.3rem">admin</span>
    </a>
    <button class="nav-toggle" onclick="toggleNav()" aria-label="Menu">&#9776;</button>
    <div class="nav-links" id="navLinks">
      <a href="admin_dashboard.php" class="<?= $activeNav === 'dash'     ? 'active' : '' ?>">Dashboard</a>
      <a href="admin_user.php"      class="<?= $activeNav === 'users'    ? 'active' : '' ?>">Users</a>
      <a href="admin_merchant.php"  class="<?= $activeNav === 'merchant' ? 'active' : '' ?>">Billers</a>
      <a href="admin_education.php" class="<?= $activeNav === 'edu'      ? 'active' : '' ?>">Institutes</a>
      <a href="admin_supports.php"  class="<?= $activeNav === 'support'  ? 'active' : '' ?>">Support
        <?php if ($__openTickets): ?>
          <span class="badge badge-pending"><?= $__openTickets ?></span>
        <?php endif; ?>
      </a>
      <a href="admin_issue.php"     class="<?= $activeNav === 'audit'    ? 'active' : '' ?>">Audit log</a>
      <button id="themeBtn" onclick="toggleTheme()" title="Switch theme">&#127769;</button>
      <a href="adminlogout.php" class="btn btn-ghost btn-sm">Log out</a>
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
