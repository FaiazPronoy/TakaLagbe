<?php $err = static fn(string $k): string =>
    isset($errors[$k]) ? '<div class="err">' . e($errors[$k]) . '</div>' : ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create an account &middot; <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="Assets/css/app.css">
<script>(function(){try{var t=localStorage.getItem('tkl-theme');
 if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}
 document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<div class="auth">
  <div class="auth-card auth-wide">

    <div class="auth-logo">
      <div class="brand-mark">T</div>
      <h1>Create your account</h1>
      <p>It takes a minute. You must be 18 or older.</p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error" style="margin-bottom:1rem">
        <span>Please fix the highlighted fields below.</span>
      </div>
    <?php endif; ?>

    <form method="post" data-once>
      <?= csrf_field() ?>

      <div class="row">
        <div class="col field">
          <label for="Firstname">First name</label>
          <input id="Firstname" name="Firstname" required maxlength="50"
                 value="<?= e($old['Firstname']) ?>" placeholder="Faiaz">
          <?= $err('Firstname') ?>
        </div>
        <div class="col field">
          <label for="Lastname">Last name</label>
          <input id="Lastname" name="Lastname" required maxlength="50"
                 value="<?= e($old['Lastname']) ?>" placeholder="Abrar">
          <?= $err('Lastname') ?>
        </div>
      </div>

      <div class="row">
        <div class="col field">
          <label for="Phone">Mobile number</label>
          <input id="Phone" name="Phone" type="tel" inputmode="numeric" maxlength="11"
                 pattern="01[3-9][0-9]{8}" required value="<?= e($old['Phone']) ?>"
                 placeholder="01XXXXXXXXX">
          <div class="hint">This becomes your account number.</div>
          <?= $err('Phone') ?>
        </div>
        <div class="col field">
          <label for="Nid">National ID</label>
          <input id="Nid" name="Nid" inputmode="numeric" required maxlength="18"
                 value="<?= e($old['Nid']) ?>" placeholder="10, 13, 17 or 18 digits">
          <?= $err('Nid') ?>
        </div>
      </div>

      <div class="row">
        <div class="col field">
          <label for="Email">Email</label>
          <input id="Email" name="Email" type="email" required maxlength="100"
                 value="<?= e($old['Email']) ?>" placeholder="you@example.com">
          <?= $err('Email') ?>
        </div>
        <div class="col field">
          <label for="Dob">Date of birth</label>
          <input id="Dob" name="Dob" type="date" required value="<?= e($old['Dob']) ?>"
                 max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
          <?= $err('Dob') ?>
        </div>
      </div>

      <fieldset>
        <legend>Gender</legend>
        <div class="radio-row">
          <?php foreach (['Male', 'Female', 'Others'] as $g): ?>
            <label><input type="radio" name="Gender" value="<?= $g ?>"
                   <?= $old['Gender'] === $g ? 'checked' : '' ?>> <?= $g ?></label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <div class="row">
        <div class="col field">
          <label for="Password">Password</label>
          <input id="Password" name="Password" type="password" required minlength="6"
                 data-strength="pwMeter" autocomplete="new-password"
                 placeholder="At least 6 characters">
          <div class="meter" id="pwMeter"><span></span></div>
          <?= $err('Password') ?>
        </div>
        <div class="col field">
          <label for="ConfirmPassword">Confirm password</label>
          <input id="ConfirmPassword" name="ConfirmPassword" type="password" required
                 minlength="6" autocomplete="new-password">
          <?= $err('ConfirmPassword') ?>
        </div>
      </div>

      <div class="row">
        <div class="col field">
          <label for="Pin">Transaction PIN</label>
          <input id="Pin" name="Pin" type="password" inputmode="numeric" required
                 pattern="\d{6}" maxlength="6" placeholder="6 digits">
          <div class="hint">Asked for every time money leaves your account.</div>
          <?= $err('Pin') ?>
        </div>
        <div class="col field">
          <label for="PinConfirm">Confirm PIN</label>
          <input id="PinConfirm" name="PinConfirm" type="password" inputmode="numeric"
                 required pattern="\d{6}" maxlength="6">
          <?= $err('PinConfirm') ?>
        </div>
      </div>

      <button class="btn btn-primary btn-block" type="submit">Create account</button>
    </form>

    <p class="center tiny muted" style="margin-top:1rem">
      Already have an account? <a href="login.php">Sign in</a>
    </p>
  </div>
</div>
<script src="Assets/js/app.js"></script>
</body>
</html>
