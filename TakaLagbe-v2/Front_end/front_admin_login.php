<?php
$pageTitle = 'Admin sign in';
$artTitle  = 'The back office.';
$artText   = 'Users, billers, merchants, agent floats, the support desk and the audit trail.';
include __DIR__ . '/partials/auth_top.php';
$f = take_flash();
?>
    <div class="auth-box rise" style="--n:1">
      <span class="eyebrow">Admin</span>
      <h2>Sign in to the console</h2>

      <?= error_note($error) ?>
      <?php if ($f): ?>
        <div class="note ok" role="status"><?= icon('check') ?><span><?= e($f[1]) ?></span></div>
      <?php endif; ?>

      <form method="post" data-once>
        <?= csrf_field() ?>
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" required autocomplete="username" maxlength="40">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-block" type="submit">Sign in</button>
      </form>

      <p class="center mt-4"><a href="login.php">Back to TakaLagbe</a></p>
    </div>
  </main>
</div>
</body>
</html>
