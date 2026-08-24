<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin sign in &middot; <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="Assets/css/app.css">
<script>(function(){try{var t=localStorage.getItem('tkl-theme');
 if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}
 document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<div class="auth" style="background:linear-gradient(160deg,#343A40,#16222E)">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="brand-mark" style="background:linear-gradient(135deg,#495057,#212529)">A</div>
      <h1>Admin console</h1>
      <p><?= e(APP_NAME) ?> back office</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" required autofocus autocomplete="username">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-dark btn-block" type="submit">Sign in</button>
    </form>

    <p class="center tiny muted" style="margin-top:1rem">
      <a href="login.php">Back to the customer app</a>
    </p>
  </div>
</div>
<script src="Assets/js/app.js"></script>
</body>
</html>
