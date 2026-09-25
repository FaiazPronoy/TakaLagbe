<?php
$pageTitle = 'Reset password';
$artTitle  = 'Locked out? Prove it is you.';
$artText   = 'Your number, NID and date of birth together are enough to set a new password. Your PIN stays as it was.';
include __DIR__ . '/partials/auth_top.php';
?>
    <div class="auth-box rise" style="--n:1">
      <a class="back" href="login.php"><?= icon('back') ?>Back to sign in</a>
      <h2>Reset your password</h2>

      <?php if ($done): ?>
        <div class="note ok" role="status"><?= icon('check') ?>
          <span>Done. Your password has been changed, and a note has gone to your notifications.</span></div>
        <a class="btn btn-block" href="login.php">Sign in</a>
      <?php else: ?>
        <p class="muted">Enter the details you opened the account with.</p>
        <?= error_note($error) ?>

        <form method="post" data-once>
          <?= csrf_field() ?>
          <div class="field">
            <label for="Phone">Mobile number</label>
            <input id="Phone" name="Phone" type="tel" inputmode="numeric" maxlength="11" autocomplete="username"
                   pattern="01[3-9][0-9]{8}" required value="<?= e($phone) ?>">
          </div>
          <div class="grid cols-2">
            <div class="field">
              <label for="Nid">National ID number</label>
              <input id="Nid" name="Nid" inputmode="numeric" required maxlength="18">
            </div>
            <div class="field">
              <label for="Dob">Date of birth</label>
              <input id="Dob" name="Dob" type="date" required>
            </div>
          </div>
          <div class="field">
            <label for="NewPassword">New password</label>
            <input id="NewPassword" name="NewPassword" type="password" required minlength="6" autocomplete="new-password">
          </div>
          <div class="field">
            <label for="ConfirmPassword">Type it again</label>
            <input id="ConfirmPassword" name="ConfirmPassword" type="password" required minlength="6"
                   autocomplete="new-password">
          </div>
          <button class="btn btn-block" type="submit">Set new password</button>
        </form>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>
