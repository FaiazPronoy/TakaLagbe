<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$palette = ['#1E3A63' => __('Navy'), '#A87A2B' => __('Brass'), '#A9412C' => __('Brick'), '#5A4A86' => __('Plum'),
            '#2C6E4E' => __('Moss'), '#4C7396' => __('Slate'), '#8C5A3C' => __('Walnut'), '#3F6F8F' => __('Teal')];
$nid = str_repeat('•', max(0, strlen($user['nid']) - 4)) . substr($user['nid'], -4);
?>
<main id="main" class="page">
  <div class="wrap">
    <div class="head rise">
      <div class="row" style="gap:var(--s4)">
        <?= avatar($user['firstname'], $user['lastname'], $user['avatar_color'], 'lg') ?>
        <div>
          <h1 class="m0"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></h1>
          <span class="muted small"><span class="mono"><?= e($user['phone']) ?></span> ·
            <?= e(__('member since {when}', ['when' => date('F Y', strtotime($stats['member_since']))])) ?> ·
            <span class="badge <?= $user['status'] === 'active' ? 'ok' : 'bad' ?>"><?= e(__($user['status'])) ?></span></span>
        </div>
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="qr.php"><?= icon('qr') ?><?= __('My QR code') ?></a>
        <form method="post" action="logout.php"><?= csrf_field() ?>
          <button class="btn btn-quiet" type="submit"><?= icon('logout') ?><?= __('Sign out') ?></button></form>
      </div>
    </div>
    <?= error_note($error) ?>

    <div class="grid cols-4 mb-5">
      <div class="stat rise" style="--n:1"><span class="label"><?= __('Transactions') ?></span><span class="v"><?= number_format($stats['transactions']) ?></span></div>
      <div class="stat rise" style="--n:2"><span class="label"><?= __('Sent, all time') ?></span><span class="v"><small>৳</small><?= e(money($stats['sent'], false)) ?></span></div>
      <div class="stat rise" style="--n:3"><span class="label"><?= __('Received') ?></span><span class="v"><small>৳</small><?= e(money($stats['received'], false)) ?></span></div>
      <div class="stat rise" style="--n:4"><span class="label"><?= __('Daily limit') ?></span><span class="v"><small>৳</small><?= e(money($user['daily_limit'], false)) ?></span></div>
    </div>

    <div class="grid cols-2">
      <section class="card reveal" aria-labelledby="detTitle">
        <div class="card-h tone-1"><h2 id="detTitle"><?= icon('user') ?><?= __('You') ?></h2></div>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="details">
          <dl class="grid" style="grid-template-columns:auto 1fr;gap:var(--s2) var(--s4);margin:0 0 var(--s4)">
            <dt class="muted"><?= __('Name') ?></dt><dd class="m0"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></dd>
            <dt class="muted"><?= __('Mobile') ?></dt><dd class="mono m0"><?= e($user['phone']) ?></dd>
            <dt class="muted"><?= __('NID') ?></dt><dd class="mono m0"><?= e($nid) ?></dd>
          </dl>
          <div class="field">
            <label for="email"><?= __('Email') ?></label>
            <input id="email" name="email" type="email" required maxlength="100" autocomplete="email" value="<?= e($user['email']) ?>">
          </div>
          <fieldset>
            <legend class="legend"><?= __('Avatar colour') ?></legend>
            <div class="swatches">
              <?php foreach ($palette as $c => $name): ?>
                <input type="radio" name="avatar_color" value="<?= $c ?>" aria-label="<?= e($name) ?>"
                       style="--c:<?= $c ?>" <?= strcasecmp($user['avatar_color'], $c) === 0 ? 'checked' : '' ?>>
              <?php endforeach; ?>
            </div>
          </fieldset>
          <button class="btn" type="submit"><?= icon('check') ?><?= __('Save') ?></button>
        </form>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="limTitle">
        <div class="card-h tone-5"><h2 id="limTitle"><?= icon('shield') ?><?= __('Daily limit') ?></h2></div>
        <p class="small muted"><?= __('Nothing more leaves the wallet once a day\'s total reaches this. Money moved into savings does not count. Today so far:') ?> <span class="num"><?= e(money(spent_today($phone))) ?></span>.</p>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="limit">
          <div class="field">
            <label for="daily_limit"><?= __('Limit') ?></label>
            <div class="money"><span aria-hidden="true">৳</span>
              <input id="daily_limit" name="daily_limit" type="number" step="100" min="1000" max="100000" required
                     value="<?= e((float) $user['daily_limit']) ?>"></div>
            <?= amount_chips('daily_limit', [5000, 10000, 25000, 50000]) ?>
          </div>
          <?= pin_field('limitPin') ?>
          <button class="btn" type="submit"><?= icon('check') ?><?= __('Save limit') ?></button>
        </form>
      </section>

      <section class="card reveal" aria-labelledby="pwTitle">
        <div class="card-h tone-4"><h2 id="pwTitle"><?= icon('lock') ?><?= __('Password') ?></h2></div>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="password">
          <div class="field"><label for="current_password"><?= __('Current password') ?></label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
          <div class="grid cols-2">
            <div class="field"><label for="new_password"><?= __('New password') ?></label>
              <input id="new_password" name="new_password" type="password" required minlength="6" autocomplete="new-password"></div>
            <div class="field"><label for="confirm_password"><?= __('Type it again') ?></label>
              <input id="confirm_password" name="confirm_password" type="password" required minlength="6" autocomplete="new-password"></div>
          </div>
          <button class="btn" type="submit"><?= icon('lock') ?><?= __('Change password') ?></button>
        </form>

        <hr>
        <h2 style="font-size:var(--step-1)" id="pinTitle"><?= __('Transaction PIN') ?></h2>
        <form method="post" data-once aria-labelledby="pinTitle">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="pin">
          <div class="grid cols-3">
            <?php foreach (['current_pin' => __('Current'), 'new_pin' => __('New'), 'confirm_pin' => __('New again')] as $k => $l): ?>
              <div class="field"><label for="<?= $k ?>"><?= e($l) ?></label>
                <input id="<?= $k ?>" name="<?= $k ?>" type="password" inputmode="numeric" pattern="\d{6}"
                       maxlength="6" required autocomplete="off" class="mono"></div>
            <?php endforeach; ?>
          </div>
          <button class="btn" type="submit"><?= icon('lock') ?><?= __('Change PIN') ?></button>
        </form>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="actTitle">
        <div class="card-h tone-6"><h2 id="actTitle"><?= icon('history') ?><?= __('Recent sign ins') ?></h2></div>
        <?php if (!$logins): ?>
          <p class="muted small"><?= __('Nothing recorded yet.') ?></p>
        <?php else: ?>
          <div class="table">
            <table>
              <thead><tr><th scope="col"><?= __('When') ?></th><th scope="col"><?= __('From') ?></th><th scope="col"><?= __('Result') ?></th></tr></thead>
              <tbody>
              <?php foreach ($logins as $l): ?>
                <tr><td class="small"><?= e(short_date($l['attempted_at'])) ?></td>
                  <td class="small mono"><?= e($l['ip']) ?></td>
                  <td><span class="badge <?= $l['successful'] ? 'ok' : 'bad' ?>"><?= e($l['successful'] ? __('signed in') : __('wrong password')) ?></span></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        <h3 class="mt-5"><?= __('Account activity') ?></h3>
        <ul class="rows">
          <?php foreach ($activity as $a): ?>
            <li class="entry" style="padding:var(--s2) 0">
              <span class="body"><span class="t"><?= e(__(ucfirst(str_replace('_', ' ', $a['action'])))) ?></span>
                <span class="s"><?= e($a['detail']) ?></span></span>
              <span class="small muted nowrap"><?= e(time_ago($a['created_at'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
