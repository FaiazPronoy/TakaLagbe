<?php
$pageTitle = 'Open an account';
$artTitle  = 'Your wallet, in about a minute.';
$artText   = 'Your phone number is your account number. Pick a password for signing in and a PIN for paying. That\'s it.';
include __DIR__ . '/partials/auth_top.php';

$err = static fn(string $k): string =>
    isset($errors[$k]) ? '<span class="err" id="' . $k . '-err">' . e($errors[$k]) . '</span>' : '';
$bad = static fn(string $k): string =>
    isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="' . $k . '-err"' : '';
?>
    <div class="auth-box wide rise" style="--n:1">
      <h2>Let's get you set up</h2>
      <p class="muted">You'll need to be 18+, with a Bangladeshi number and NID.</p>

      <?php if ($errors): ?>
        <div class="note bad" role="alert"><?= icon('alert') ?>
          <span>Some details need another look. Each one is marked below.</span></div>
      <?php endif; ?>

      <p class="note warn small"><?= icon('info') ?><span>This is a demo wallet. Please use made up details, not your real NID.</span></p>

      <form method="post" data-once novalidate>
        <?= csrf_field() ?>

        <div class="grid cols-2">
          <div class="field">
            <label for="Firstname">First name</label>
            <input id="Firstname" name="Firstname" required maxlength="50" autocomplete="given-name"
                   value="<?= e($old['Firstname']) ?>"<?= $bad('Firstname') ?>>
            <?= $err('Firstname') ?>
          </div>
          <div class="field">
            <label for="Lastname">Last name</label>
            <input id="Lastname" name="Lastname" required maxlength="50" autocomplete="family-name"
                   value="<?= e($old['Lastname']) ?>">
          </div>

          <div class="field">
            <label for="Phone">Mobile number</label>
            <input id="Phone" name="Phone" type="tel" inputmode="numeric" maxlength="11" autocomplete="tel"
                   pattern="01[3-9][0-9]{8}" required value="<?= e($old['Phone']) ?>" placeholder="01XXXXXXXXX"<?= $bad('Phone') ?>>
            <?= $err('Phone') ?: '<span class="hint">This becomes your account number.</span>' ?>
          </div>
          <div class="field">
            <label for="Nid">National ID number</label>
            <input id="Nid" name="Nid" inputmode="numeric" required maxlength="18"
                   value="<?= e($old['Nid']) ?>"<?= $bad('Nid') ?>>
            <?= $err('Nid') ?: '<span class="hint">10, 13, 17 or 18 digits.</span>' ?>
          </div>

          <div class="field">
            <label for="Email">Email</label>
            <input id="Email" name="Email" type="email" required maxlength="100" autocomplete="email"
                   value="<?= e($old['Email']) ?>"<?= $bad('Email') ?>>
            <?= $err('Email') ?>
          </div>
          <div class="field">
            <label for="Dob">Date of birth</label>
            <input id="Dob" name="Dob" type="date" required value="<?= e($old['Dob']) ?>"
                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>"<?= $bad('Dob') ?>>
            <?= $err('Dob') ?>
          </div>
        </div>

        <fieldset>
          <legend class="legend">Gender</legend>
          <div class="choices">
            <?php foreach (['Male', 'Female', 'Others'] as $g): ?>
              <label><input type="radio" name="Gender" value="<?= $g ?>"
                     <?= $old['Gender'] === $g ? 'checked' : '' ?>> <?= $g ?></label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="grid cols-2">
          <div class="field">
            <label for="Password">Password</label>
            <input id="Password" name="Password" type="password" required minlength="6"
                   autocomplete="new-password"<?= $bad('Password') ?>>
            <?= $err('Password') ?: '<span class="hint">At least 6 characters.</span>' ?>
          </div>
          <div class="field">
            <label for="ConfirmPassword">Type it again</label>
            <input id="ConfirmPassword" name="ConfirmPassword" type="password" required minlength="6"
                   autocomplete="new-password"<?= $bad('ConfirmPassword') ?>>
            <?= $err('ConfirmPassword') ?>
          </div>

          <div class="field">
            <label for="Pin">Transaction PIN</label>
            <input id="Pin" name="Pin" type="password" inputmode="numeric" required
                   pattern="\d{6}" maxlength="6" autocomplete="off"<?= $bad('Pin') ?>>
            <?= $err('Pin') ?: '<span class="hint">Six digits, asked for whenever money leaves.</span>' ?>
          </div>
          <div class="field">
            <label for="PinConfirm">PIN again</label>
            <input id="PinConfirm" name="PinConfirm" type="password" inputmode="numeric" required
                   pattern="\d{6}" maxlength="6" autocomplete="off"<?= $bad('PinConfirm') ?>>
            <?= $err('PinConfirm') ?>
          </div>
        </div>

        <button class="btn btn-block" type="submit"><?= icon('check') ?>Create my wallet</button>
      </form>

      <p class="center mt-4">Already one of us? <a href="login.php">Sign in</a></p>
    </div>
  </main>
</div>
</body>
</html>
