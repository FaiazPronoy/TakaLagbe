<?php
$pageTitle = __('Open an account');
$artTitle  = __('Your wallet, in about a minute.');
$artText   = __('Your phone number is your account number. Pick a password for signing in and a PIN for paying. That\'s it.');
include __DIR__ . '/partials/auth_top.php';

$err = static fn(string $k): string =>
    isset($errors[$k]) ? '<span class="err" id="' . $k . '-err">' . e($errors[$k]) . '</span>' : '';
$bad = static fn(string $k): string =>
    isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="' . $k . '-err"' : '';
?>
    <div class="auth-box wide rise" style="--n:1">
      <h2><?= __('Let\'s get you set up') ?></h2>
      <p class="muted"><?= __('You\'ll need to be 18+, with a Bangladeshi number and NID.') ?></p>

      <?php if ($errors): ?>
        <div class="note bad" role="alert"><?= icon('alert') ?>
          <span><?= __('Some details need another look. Each one is marked below.') ?></span></div>
      <?php endif; ?>

      <p class="note warn small"><?= icon('info') ?><span><?= __('This is a demo wallet. Please use made up details, not your real NID.') ?></span></p>

      <form method="post" data-once novalidate>
        <?= csrf_field() ?>

        <div class="grid cols-2">
          <div class="field">
            <label for="Firstname"><?= __('First name') ?></label>
            <input id="Firstname" name="Firstname" required maxlength="50" autocomplete="given-name"
                   value="<?= e($old['Firstname']) ?>"<?= $bad('Firstname') ?>>
            <?= $err('Firstname') ?>
          </div>
          <div class="field">
            <label for="Lastname"><?= __('Last name') ?></label>
            <input id="Lastname" name="Lastname" required maxlength="50" autocomplete="family-name"
                   value="<?= e($old['Lastname']) ?>">
          </div>

          <div class="field">
            <label for="Phone"><?= __('Mobile number') ?></label>
            <input id="Phone" name="Phone" type="tel" inputmode="numeric" maxlength="11" autocomplete="tel"
                   pattern="01[3-9][0-9]{8}" required value="<?= e($old['Phone']) ?>" placeholder="<?= e(__('01XXXXXXXXX')) ?>"<?= $bad('Phone') ?>>
            <?= $err('Phone') ?: '<span class="hint">' . e(__('This becomes your account number.')) . '</span>' ?>
          </div>
          <div class="field">
            <label for="Nid"><?= __('National ID number') ?></label>
            <input id="Nid" name="Nid" inputmode="numeric" required maxlength="18"
                   value="<?= e($old['Nid']) ?>"<?= $bad('Nid') ?>>
            <?= $err('Nid') ?: '<span class="hint">' . e(__('10, 13, 17 or 18 digits.')) . '</span>' ?>
          </div>

          <div class="field">
            <label for="Email"><?= __('Email') ?></label>
            <input id="Email" name="Email" type="email" required maxlength="100" autocomplete="email"
                   value="<?= e($old['Email']) ?>"<?= $bad('Email') ?>>
            <?= $err('Email') ?>
          </div>
          <div class="field">
            <label for="Dob"><?= __('Date of birth') ?></label>
            <input id="Dob" name="Dob" type="date" required value="<?= e($old['Dob']) ?>"
                   max="<?= date('Y-m-d', strtotime('-18 years')) ?>"<?= $bad('Dob') ?>>
            <?= $err('Dob') ?>
          </div>
        </div>

        <fieldset>
          <legend class="legend"><?= __('Gender') ?></legend>
          <div class="choices">
            <?php foreach (['Male', 'Female', 'Others'] as $g): ?>
              <label><input type="radio" name="Gender" value="<?= $g ?>"
                     <?= $old['Gender'] === $g ? 'checked' : '' ?>> <?= e(__($g)) ?></label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="grid cols-2">
          <div class="field">
            <label for="Password"><?= __('Password') ?></label>
            <input id="Password" name="Password" type="password" required minlength="6"
                   autocomplete="new-password"<?= $bad('Password') ?>>
            <?= $err('Password') ?: '<span class="hint">' . e(__('At least 6 characters.')) . '</span>' ?>
          </div>
          <div class="field">
            <label for="ConfirmPassword"><?= __('Type it again') ?></label>
            <input id="ConfirmPassword" name="ConfirmPassword" type="password" required minlength="6"
                   autocomplete="new-password"<?= $bad('ConfirmPassword') ?>>
            <?= $err('ConfirmPassword') ?>
          </div>

          <div class="field">
            <label for="Pin"><?= __('Transaction PIN') ?></label>
            <input id="Pin" name="Pin" type="password" inputmode="numeric" required
                   pattern="\d{6}" maxlength="6" autocomplete="off"<?= $bad('Pin') ?>>
            <?= $err('Pin') ?: '<span class="hint">' . e(__('Six digits, asked for whenever money leaves.')) . '</span>' ?>
          </div>
          <div class="field">
            <label for="PinConfirm"><?= __('PIN again') ?></label>
            <input id="PinConfirm" name="PinConfirm" type="password" inputmode="numeric" required
                   pattern="\d{6}" maxlength="6" autocomplete="off"<?= $bad('PinConfirm') ?>>
            <?= $err('PinConfirm') ?>
          </div>
        </div>

        <button class="btn btn-block" type="submit"><?= icon('check') ?><?= __('Create my wallet') ?></button>
      </form>

      <p class="center mt-4"><?= __('Already one of us?') ?> <a href="login.php"><?= __('Sign in') ?></a></p>
    </div>
  </main>
</div>
</body>
</html>
