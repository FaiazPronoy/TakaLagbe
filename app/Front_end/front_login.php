<?php
$pageTitle = __('Sign in');
$frontDoor = true;
include __DIR__ . '/partials/auth_top.php';
$f = take_flash();
?>
    <div class="auth-box rise" style="--n:1">
      <h2><?= __('Open your wallet') ?></h2>
      <p class="muted"><?= __('Your number and password, that\'s all.') ?></p>

      <?= error_note($error) ?>
      <?php if ($f): ?>
        <div class="note <?= $f[0] === 'error' ? 'bad' : 'ok' ?>" role="status">
          <?= icon($f[0] === 'error' ? 'alert' : 'check') ?><span><?= e($f[1]) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" data-once>
        <?= csrf_field() ?>
        <div class="field">
          <label for="Phone"><?= __('Mobile number') ?></label>
          <input type="tel" id="Phone" name="Phone" inputmode="numeric" maxlength="11"
                 pattern="01[3-9][0-9]{8}" placeholder="<?= e(__('01XXXXXXXXX')) ?>" required
                 value="<?= e($phone) ?>" autocomplete="username">
        </div>
        <div class="field">
          <div class="between">
            <label for="Password"><?= __('Password') ?></label>
            <a class="small" href="reset_password.php"><?= __('Forgot it?') ?></a>
          </div>
          <input type="password" id="Password" name="Password" required autocomplete="current-password">
        </div>
        <button class="btn btn-block" type="submit"><?= icon('arrow-in') ?><?= __('Let me in') ?></button>
      </form>

      <p class="center mt-4"><?= __('New here?') ?> <a href="signup.php"><?= __('Grab a wallet, it\'s free') ?></a></p>

      <div class="js-only demo-try">
        <button class="btn btn-quiet btn-block" type="button" id="tryDemo"
                data-phone="01711482093" data-password="demo1234"><?= icon('user') ?><?= __('Try the demo account') ?></button>
        <p class="small muted" id="demoPin" hidden><?= __('Filled in for you. When a payment asks for a PIN, use') ?> <strong>482915</strong>.</p>
      </div>
    </div>
  </main>
</div>
</body>
</html>
