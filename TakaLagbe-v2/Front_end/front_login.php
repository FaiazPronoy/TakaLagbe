<?php
$pageTitle = 'Sign in';
include __DIR__ . '/partials/auth_top.php';
$f = take_flash();
?>
    <div class="auth-box rise" style="--n:1">
      <h2>Sign in</h2>
      <p class="muted">Use the mobile number your account was opened with.</p>

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
        <button class="btn btn-block" type="submit">Sign in</button>
      </form>

      <p class="center mt-4">New here? <a href="signup.php">Open an account</a></p>

      <div class="demo">
        <strong>Trying the demo?</strong> Sign in as <code>01711482093</code> with
        <code>demo1234</code>. The transaction PIN is <code>482915</code>.
        The back office is at <a href="admin_login.php">admin sign in</a>.
      </div>
    </div>
  </main>
</div>
</body>
</html>
