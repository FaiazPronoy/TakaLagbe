<?php
require_once __DIR__ . '/../lib/view.php';
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$actions = [
    ['sendmoney.php',      "\u{1F4B8}", 'Send Money'],
    ['add_money.php',      "\u{1F3E6}", 'Add Money'],
    ['cash_in.php',        "\u{1F4B5}", 'Cash In'],
    ['cash_out.php',       "\u{1F3E7}", 'Cash Out'],
    ['mobilerecharge.php', "\u{1F4F1}", 'Recharge'],
    ['paybill.php',        "\u{1F4A1}", 'Pay Bill'],
    ['education_fee.php',  "\u{1F393}", 'Education'],
    ['payment.php',        "\u{1F6CD}", 'Payment'],
    ['savings.php',        "\u{1F3AF}", 'Savings'],
    ['requests.php',       "\u{1F91D}", 'Request'],
    ['qr.php',             "\u{1F4F7}", 'My QR'],
    ['favourites.php',     "\u{2B50}",  'Favourites'],
    ['checkbalance.php',   "\u{1F50D}", 'Balance'],
    ['statement.php',      "\u{1F4C4}", 'Statement'],
];
?>
<main class="wrap section">

  <!-- ---------------------------------------------------- balance -->
  <div class="row" style="align-items:stretch">
    <div class="col" style="flex:2 1 420px">
      <div class="card balance">
        <div class="between" style="align-items:flex-start">
          <div>
            <div class="label">Available balance</div>
            <div class="amount" id="balanceAmount" data-real="<?= e(money($bal)) ?>" data-hidden="0">
              <?= e(money($bal)) ?>
            </div>
            <div class="meta" style="margin-top:.35rem">
              <?= e($user['firstname'] . ' ' . $user['lastname']) ?> &middot;
              <span class="mono"><?= e($phone) ?></span>
            </div>
          </div>
          <div style="text-align:right">
            <div class="avatar" style="background:<?= e($user['avatar_color']) ?>;margin-left:auto">
              <?= e(initials($user['firstname'], $user['lastname'])) ?>
            </div>
            <button class="eye" id="eyeBtn" onclick="toggleBalance()" style="margin-top:.6rem">Hide</button>
          </div>
        </div>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,.25);margin:1rem 0">

        <div class="between" style="font-size:.85rem">
          <span>Daily limit used</span>
          <strong><?= e(money($todayOut, false)) ?> / <?= e(money($limit, false)) ?></strong>
        </div>
        <div class="bar" style="background:rgba(255,255,255,.25);margin-top:.4rem">
          <span style="width:<?= $limit > 0 ? min(100, round($todayOut / $limit * 100)) : 0 ?>%;
                       background:#fff"></span>
        </div>

        <div style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
          <a class="btn btn-ghost btn-sm" href="sendmoney.php">Send money</a>
          <a class="btn btn-ghost btn-sm" href="add_money.php">Add money</a>
          <a class="btn btn-ghost btn-sm" href="qr.php">Show my QR</a>
        </div>
      </div>
    </div>

    <div class="col" style="flex:1 1 260px">
      <div class="grid" style="grid-template-columns:1fr 1fr;gap:1rem;height:100%">
        <div class="stat"><div class="k">In this month</div>
          <div class="v pos"><?= e(money($monthIn, false)) ?></div></div>
        <div class="stat"><div class="k">Out this month</div>
          <div class="v neg"><?= e(money($monthOut, false)) ?></div></div>
        <div class="stat"><div class="k">In savings</div>
          <div class="v"><?= e(money($saved, false)) ?></div></div>
        <div class="stat"><div class="k">Cashback earned</div>
          <div class="v"><?= e(money($cashback, false)) ?></div></div>
      </div>
    </div>
  </div>

  <!-- ------------------------------------------------ pending asks -->
  <?php if ($requests): ?>
    <div class="card" style="margin-top:1rem;border-left:4px solid var(--warn)">
      <div class="card-title">Waiting for you</div>
      <?php foreach ($requests as $r):
        $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['requester_phone']; ?>
        <div class="entry">
          <span class="ico">&#129309;</span>
          <span class="body">
            <span class="t"><?= e($who) ?> asked for <?= e(money($r['amount'])) ?></span>
            <span class="s"><?= e($r['note'] ?: 'No note') ?> &middot; <?= e(time_ago($r['created_at'])) ?></span>
          </span>
          <a class="btn btn-primary btn-sm" href="requests.php#r<?= (int) $r['id'] ?>">Review</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ------------------------------------------------ quick actions -->
  <h2 style="margin:1.6rem 0 .8rem">What would you like to do?</h2>
  <div class="tiles">
    <?php foreach ($actions as [$href, $icon, $name]): ?>
      <a class="tile" href="<?= $href ?>">
        <span class="ico"><?= $icon ?></span>
        <span class="name"><?= e($name) ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ----------------------------------------------- favourites row -->
  <?php if ($favourites): ?>
    <div class="card" style="margin-top:1.4rem">
      <div class="between">
        <div class="card-title mb0">Send again</div>
        <a class="tiny" href="favourites.php">Manage</a>
      </div>
      <div style="display:flex;gap:1.1rem;overflow-x:auto;padding:.4rem 0">
        <?php foreach ($favourites as $f): ?>
          <a href="sendmoney.php?to=<?= e($f['saved_phone']) ?>"
             style="text-align:center;min-width:74px;color:inherit">
            <span class="avatar" style="margin:0 auto;background:<?= e($f['avatar_color'] ?: '#1E3A63') ?>">
              <?= e(initials($f['firstname'], $f['lastname'])) ?>
            </span>
            <span class="tiny" style="display:block;margin-top:.35rem;font-weight:700">
              <?= e($f['label'] ?: ($f['firstname'] ?: $f['saved_phone'])) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ------------------------------------- activity and week chart -->
  <div class="row" style="margin-top:1.4rem">
    <div class="col" style="flex:2 1 420px">
      <div class="card">
        <div class="between">
          <div class="card-title mb0">Recent activity</div>
          <a class="tiny" href="history.php">See all</a>
        </div>
        <?php if (!$recent): ?>
          <div class="empty">
            <div class="big">&#128179;</div>
            <p class="mb0">No transactions yet.<br>Add money to get started.</p>
          </div>
        <?php else: ?>
          <?php foreach ($recent as $t) { echo trx_entry($t, $phone); } ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="col" style="flex:1 1 260px">
      <div class="card">
        <div class="card-title">Last 7 days spending</div>
        <div class="bars">
          <?php foreach ($chart as $c): ?>
            <div class="b" title="<?= e($c['label'] . ': ' . money($c['value'])) ?>">
              <i style="height:<?= max(3, round($c['value'] / $chartMax * 100)) ?>%"></i>
              <small><?= e($c['label']) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="between tiny muted" style="margin-top:.7rem">
          <span>Highest day</span>
          <strong><?= e(money($chartMax)) ?></strong>
        </div>
        <a class="btn btn-ghost btn-sm btn-block" style="margin-top:.8rem" href="analytics.php">
          Full insights
        </a>
      </div>
    </div>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
