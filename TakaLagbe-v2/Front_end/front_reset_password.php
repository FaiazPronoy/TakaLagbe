<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset password &middot; <?= e(APP_NAME) ?></title>
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
      <h1>Reset your password</h1>
      <p>Confirm the details you signed up with.</p>
    </div>

    <?php if ($done): ?>
      <div class="alert alert-ok" style="margin-bottom:1rem">
        <span>Your password has been changed. You can sign in now.</span>
      </div>
      <a class="btn btn-primary btn-block" href="login.php">Go to sign in</a>
    <?php else: ?>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
      <?php endif; ?>

      <form method="post" data-once>
        <?= csrf_field() ?>
        <div class="field">
          <label for="Phone">Mobile number</label>
          <input id="Phone" name="Phone" type="tel" inputmode="numeric" maxlength="11"
                 pattern="01[3-9][0-9]{8}" required value="<?= e($phone) ?>" autofocus>
        </div>
        <div class="field">
          <label for="Nid">National ID</label>
          <input id="Nid" name="Nid" inputmode="numeric" required maxlength="18">
        </div>
        <div class="field">
          <label for="Dob">Date of birth</label>
          <input id="Dob" name="Dob" type="date" required>
        </div>
        <hr class="dashed">
        <div class="field">
          <label for="NewPassword">New password</label>
          <input id="NewPassword" name="NewPassword" type="password" required minlength="6"
                 data-strength="pwMeter" autocomplete="new-password">
          <div class="meter" id="pwMeter"><span></span></div>
        </div>
        <div class="field">
          <label for="ConfirmPassword">Confirm new password</label>
          <input id="ConfirmPassword" name="ConfirmPassword" type="password" required minlength="6"
                 autocomplete="new-password">
        </div>
        <button class="btn btn-primary btn-block" type="submit">Reset password</button>
      </form>

      <p class="center tiny muted" style="margin-top:1rem">
        <a href="login.php">Back to sign in</a>
      </p>
    <?php endif; ?>
  </div>
</div>
<script src="Assets/js/app.js"></script>
</body>
</html>
