<?php
$pageTitle = 'Sign in';
include __DIR__ . '/partials/auth_top.php';
$f = take_flash();
?>
    <div class="auth-box rise" style="--n:1">
      <h2>Welcome back!</h2>
      <p class="muted">Pop in your number and password.</p>

      <?= error_note($error) ?>
      <?php if ($f): ?>
        <div class="note <?= $f[0] === 'error' ? 'bad' : 'ok' ?>" role="status">
          <?= icon($f[0] === 'error' ? 'alert' : 'check') ?><span><?= e($f[1]) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" data-once>
        <?= csrf_field() ?>
        <div class="field">
          <label for="Phone">Mobile number</label>
          <input type="tel" id="Phone" name="Phone" inputmode="numeric" maxlength="11"
                 pattern="01[3-9][0-9]{8}" placeholder="01XXXXXXXXX" required
                 value="<?= e($phone) ?>" autocomplete="username">
        </div>
        <div class="field">
          <div class="between">
            <label for="Password">Password</label>
            <a class="small" href="reset_password.php">Forgot it?</a>
          </div>
          <input type="password" id="Password" name="Password" required autocomplete="current-password">
        </div>
        <button class="btn btn-block" type="submit"><?= icon('arrow-in') ?>Let me in</button>
      </form>

      <p class="center mt-4">New here? <a href="signup.php">Grab a wallet, it's free</a></p>

      <div class="js-only demo-try">
        <button class="btn btn-quiet btn-block" type="button" id="tryDemo"
                data-phone="01711482093" data-password="demo1234"><?= icon('user') ?>Try the demo account</button>
        <p class="small muted" id="demoPin" hidden>Filled in for you. When a payment asks for a PIN, use <strong>482915</strong>.</p>
      </div>
    </div>
  </main>
</div>
</body>
</html>
