<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in &middot; <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="Assets/css/app.css">
<script>(function(){try{var t=localStorage.getItem('tkl-theme');
 if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}
 document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<div class="auth">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="brand-mark">T</div>
      <h1><?= e(APP_NAME) ?></h1>
      <p><?= e(APP_TAGLINE) ?></p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
    <?php endif; ?>
    <?php $f = take_flash(); if ($f): ?>
      <div class="alert alert-<?= $f[0] === 'error' ? 'error' : ($f[0] === 'warn' ? 'warn' : 'ok') ?>"
           style="margin-bottom:1rem"><span><?= e($f[1]) ?></span></div>
    <?php endif; ?>

    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="field">
        <label for="Phone">Mobile number</label>
        <input type="tel" id="Phone" name="Phone" inputmode="numeric" maxlength="11"
               pattern="01[3-9][0-9]{8}" placeholder="01XXXXXXXXX" required
               value="<?= e($phone) ?>" autocomplete="username" autofocus>
      </div>
      <div class="field">
        <label for="Password">Password</label>
        <input type="password" id="Password" name="Password" required
               autocomplete="current-password" placeholder="Your password">
      </div>
      <button class="btn btn-primary btn-block" type="submit">Sign in</button>
    </form>

    <div class="between" style="margin-top:1rem;font-size:.88rem">
      <a href="reset_password.php">Forgot password?</a>
      <a href="signup.php">Create an account</a>
    </div>

    <hr class="dashed">
    <p class="tiny muted center mb0">
      Your password is stored as a bcrypt hash, never as plain text.<br>
      After <?= LOGIN_MAX_ATTEMPTS ?> wrong tries a number is locked for
      <?= LOGIN_LOCK_MINUTES ?> minutes.
    </p>
  </div>
</div>
<script src="Assets/js/app.js"></script>
</body>
</html>
