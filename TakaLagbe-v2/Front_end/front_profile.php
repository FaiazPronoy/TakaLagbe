<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$palette = ['#1E3A63', '#A87A2B', '#A9412C', '#5A4A86', '#C09A4E', '#2C6E4E', '#8C5A3C', '#4C7396'];
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Profile and security</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <!-- summary -->
  <div class="card" style="margin-bottom:1.2rem">
    <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
      <span class="avatar" style="width:64px;height:64px;font-size:1.4rem;
            background:<?= e($user['avatar_color']) ?>">
        <?= e(initials($user['firstname'], $user['lastname'])) ?>
      </span>
      <div style="flex:1;min-width:200px">
        <h2 class="mb0"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></h2>
        <div class="muted tiny mono"><?= e($user['phone']) ?> &middot; <?= e($user['email']) ?></div>
        <div class="tiny muted">
          Member since <?= e(date('d M Y', strtotime($stats['member_since']))) ?>
          &middot; <span class="badge <?= $user['status'] === 'active' ? 'badge-ok' : 'badge-fail' ?>">
            <?= e($user['status']) ?></span>
        </div>
      </div>
      <a class="btn btn-ghost btn-sm" href="qr.php">My QR code</a>
    </div>

    <div class="row" style="margin-top:1rem">
      <div class="col"><div class="stat"><div class="k">Transactions</div>
        <div class="v"><?= number_format($stats['transactions']) ?></div></div></div>
      <div class="col"><div class="stat"><div class="k">Total sent</div>
        <div class="v neg"><?= e(money($stats['sent'], false)) ?></div></div></div>
      <div class="col"><div class="stat"><div class="k">Total received</div>
        <div class="v pos"><?= e(money($stats['received'], false)) ?></div></div></div>
      <div class="col"><div class="stat"><div class="k">Daily limit</div>
        <div class="v"><?= e(money($user['daily_limit'], false)) ?></div></div></div>
    </div>
  </div>

  <div class="grid grid-2">

    <!-- details -->
    <div class="card">
      <div class="card-title">Your details</div>
      <form method="post" data-once>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="details">

        <div class="row">
          <div class="col field"><label>First name</label>
            <input value="<?= e($user['firstname']) ?>" readonly></div>
          <div class="col field"><label>Last name</label>
            <input value="<?= e($user['lastname']) ?>" readonly></div>
        </div>
        <div class="row">
          <div class="col field"><label>Mobile</label>
            <input value="<?= e($user['phone']) ?>" readonly></div>
          <div class="col field"><label>NID</label>
            <input value="<?= e(str_repeat('*', max(0, strlen($user['nid']) - 4))
                             . substr($user['nid'], -4)) ?>" readonly></div>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required maxlength="100"
                 value="<?= e($user['email']) ?>">
        </div>
        <div class="field">
          <label>Avatar colour</label>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <?php foreach ($palette as $c): ?>
              <label style="cursor:pointer;margin:0">
                <input type="radio" name="avatar_color" value="<?= $c ?>" class="hide"
                       <?= $user['avatar_color'] === $c ? 'checked' : '' ?>
                       onchange="document.querySelectorAll('[data-swatch]').forEach(function(s){
                                 s.style.outline='none'}); this.nextElementSibling.style.outline=
                                 '3px solid var(--text)'">
                <span data-swatch style="display:block;width:34px;height:34px;border-radius:10px;
                      background:<?= $c ?>;outline:<?= $user['avatar_color'] === $c
                      ? '3px solid var(--text)' : 'none' ?>;outline-offset:2px"></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Save details</button>
      </form>
    </div>

    <!-- password -->
    <div class="card">
      <div class="card-title">Change password</div>
      <form method="post" data-once>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <div class="field">
          <label for="current_password">Current password</label>
          <input id="current_password" name="current_password" type="password" required
                 autocomplete="current-password">
        </div>
        <div class="field">
          <label for="new_password">New password</label>
          <input id="new_password" name="new_password" type="password" required minlength="6"
                 data-strength="pwMeter2" autocomplete="new-password">
          <div class="meter" id="pwMeter2"><span></span></div>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm new password</label>
          <input id="confirm_password" name="confirm_password" type="password" required minlength="6"
                 autocomplete="new-password">
        </div>
        <button class="btn btn-primary btn-block" type="submit">Change password</button>
      </form>

      <hr class="dashed">

      <div class="card-title">Change transaction PIN</div>
      <form method="post" data-once>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="pin">
        <div class="row">
          <div class="col field">
            <label for="current_pin">Current PIN</label>
            <input id="current_pin" name="current_pin" type="password" inputmode="numeric"
                   pattern="\d{6}" maxlength="6" required>
          </div>
          <div class="col field">
            <label for="new_pin">New PIN</label>
            <input id="new_pin" name="new_pin" type="password" inputmode="numeric"
                   pattern="\d{6}" maxlength="6" required>
          </div>
          <div class="col field">
            <label for="confirm_pin">Confirm</label>
            <input id="confirm_pin" name="confirm_pin" type="password" inputmode="numeric"
                   pattern="\d{6}" maxlength="6" required>
          </div>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Change PIN</button>
      </form>
    </div>

    <!-- limit -->
    <div class="card">
      <div class="card-title">Daily spending limit</div>
      <p class="tiny muted">
        Nothing can leave your account once the day's total reaches this number.
        Today you have moved <?= e(money(spent_today($phone))) ?>.
      </p>
      <form method="post" data-once>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="limit">
        <div class="field">
          <label for="daily_limit">Limit</label>
          <div class="money-input">
            <span><?= CURRENCY_SIGN ?></span>
            <input id="daily_limit" name="daily_limit" type="number" step="100"
                   min="1000" max="100000" required value="<?= e($user['daily_limit']) ?>">
          </div>
          <div class="chips">
            <?php foreach ([5000, 10000, 25000, 50000] as $v): ?>
              <button type="button" class="chip" data-chip-target="daily_limit" data-value="<?= $v ?>">
                <?= CURRENCY_SIGN ?> <?= number_format($v) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="field">
          <label>Transaction PIN</label>
          <div class="pin" data-pin="limitPin">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input type="password" inputmode="numeric" maxlength="1"
                     aria-label="PIN digit <?= $i + 1 ?>">
            <?php endfor; ?>
          </div>
          <input type="hidden" name="pin" id="limitPin" required pattern="\d{6}">
        </div>
        <button class="btn btn-primary btn-block" type="submit">Save limit</button>
      </form>
    </div>

    <!-- activity -->
    <div class="card">
      <div class="card-title">Recent sign in attempts</div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>When</th><th>From</th><th>Result</th></tr></thead>
          <tbody>
          <?php foreach ($logins as $l): ?>
            <tr>
              <td class="tiny"><?= e(short_date($l['attempted_at'])) ?></td>
              <td class="tiny mono"><?= e($l['ip']) ?></td>
              <td><span class="badge <?= $l['successful'] ? 'badge-ok' : 'badge-fail' ?>">
                <?= $l['successful'] ? 'success' : 'failed' ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$logins): ?>
            <tr><td colspan="3" class="muted center">Nothing recorded yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <hr class="dashed">

      <div class="card-title">Account activity</div>
      <?php foreach ($activity as $a): ?>
        <div class="entry" style="padding:.5rem 0">
          <span class="body">
            <span class="t" style="font-size:.85rem">
              <?= e(ucfirst(str_replace('_', ' ', $a['action']))) ?></span>
            <span class="s"><?= e($a['detail']) ?></span>
          </span>
          <span class="tiny muted"><?= e(time_ago($a['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$activity): ?>
        <p class="muted tiny mb0">Nothing recorded yet.</p>
      <?php endif; ?>
    </div>

  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
