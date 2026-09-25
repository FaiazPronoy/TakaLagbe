<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$actions = [
    ['sendmoney.php',      'send',     'Send money', 1],
    ['add_money.php',      'wallet',   'Add money',  2],
    ['cash_out.php',       'cash-out', 'Cash out',   8],
    ['cash_in.php',        'cash-in',  'Cash in',    2],
    ['mobilerecharge.php', 'phone',    'Recharge',   6],
    ['paybill.php',        'receipt',  'Pay bills',  5],
    ['payment.php',        'bag',      'Pay a shop', 7],
    ['education_fee.php',  'cap',      'Tuition',    4],
    ['qr.php',             'qr',       'My QR',      3],
    ['statement.php',      'file',     'Statement',  8],
];
$usedPct = $limit > 0 ? min(100, round($todayOut / $limit * 100)) : 0;
$hour    = (int) date('G');
$hello   = match (true) {
    $hour < 5   => 'Still up, ',
    $hour < 12  => 'Shubho shokal, ',
    $hour < 17  => 'Good afternoon, ',
    $hour < 21  => 'Evening, ',
    default     => 'Late night, ',
};
?>
<main id="main" class="page">
  <div class="wrap">

    <div class="head rise">
      <div>
        <span class="eyebrow"><?= e(date('l, j F')) ?></span>
        <h1><?= e($hello . $user['firstname']) ?>!</h1>
        <p class="lede">Taka lagbe? Send it, split it, or stash it for later.</p>
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="add_money.php"><?= icon('wallet') ?>Top up</a>
        <a class="btn" href="sendmoney.php"><?= icon('send') ?>Send money</a>
      </div>
    </div>

    <div class="split">
      <section class="hero rise" style="--n:1" aria-labelledby="balLabel">
        <?= guilloche() ?>
        <div class="between">
          <span class="label" id="balLabel">In your wallet</span>
          <button class="tool js-only" id="eyeBtn" type="button" aria-pressed="false" aria-label="Hide balance"
                  style="color:var(--on-hero)">
            <span class="i-eye" style="display:none"><?= icon('eye') ?></span>
            <span class="i-eye-off"><?= icon('eye-off') ?></span>
          </button>
        </div>
        <p class="figure" id="balanceFigure">
          <span class="real"><span class="cur">৳</span><?= e(money($bal, false)) ?></span>
          <span class="dots" hidden><span class="cur">৳</span>••••••</span>
        </p>
        <p class="who"><?= e($user['firstname'] . ' ' . $user['lastname']) ?> · <span class="mono"><?= e($phone) ?></span></p>

        <div class="mt-5">
          <div class="between small mb-2">
            <span style="color:var(--on-hero-2)">Sent today</span>
            <span class="num"><?= e(money($todayOut, false)) ?> of <?= e(money($limit, false)) ?></span>
          </div>
          <div class="meter" role="progressbar" aria-label="Daily limit used"
               aria-valuenow="<?= $usedPct ?>" aria-valuemin="0" aria-valuemax="100">
            <span style="width:<?= $usedPct ?>%"></span>
          </div>
        </div>
      </section>

      <div class="grid" style="grid-template-columns:1fr 1fr">
        <?php foreach ([['Came in', $monthIn, 'pos', 'arrow-in', 2], ['Went out', $monthOut, 'neg', 'arrow-out', 3],
                        ['Stashed away', $saved, '', 'target', 4], ['Cashback', $cashback, '', 'gift', 5]] as $i => [$k, $v, $cls, $ico, $tone]): ?>
          <div class="stat tone-<?= $tone ?> rise" style="--n:<?= $i + 2 ?>">
            <span class="label"><?= icon($ico) ?><?= e($k) ?></span>
            <span class="v <?= $cls ?>"><small>৳</small><?= e(money($v, false)) ?></span>
            <span class="sub">this month</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($requests): ?>
      <section class="card reveal mt-5 tone-5" style="border-left:4px solid var(--brass)">
        <div class="card-h"><h2><?= icon('hand') ?>Someone's waiting on you</h2><a href="requests.php">See all</a></div>
        <ul class="rows">
          <?php foreach ($requests as $r):
            $who = $r['firstname'] ? $r['firstname'] : $r['requester_phone']; ?>
            <li class="entry">
              <?= avatar($r['firstname'], $r['lastname'], $r['avatar_color'] ?? null) ?>
              <span class="body">
                <span class="t"><?= e($who) ?> wants <span class="num"><?= e(money($r['amount'])) ?></span></span>
                <span class="s"><?= e($r['note'] ?: 'no note, just vibes') ?> · <?= e(time_ago($r['created_at'])) ?></span>
              </span>
              <a class="btn btn-sm" href="sendmoney.php?request=<?= (int) $r['id'] ?>"><?= icon('send') ?>Pay</a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section class="mt-6" aria-labelledby="doTitle">
      <h2 id="doTitle" class="reveal">What are we doing today?</h2>
      <div class="actions">
        <?php foreach ($actions as $i => [$href, $ico, $name, $tone]): ?>
          <a class="action lift reveal tone-<?= $tone ?>" style="--n:<?= $i % 5 ?>" href="<?= $href ?>">
            <span class="disc"><?= icon($ico) ?></span><?= e($name) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($favourites): ?>
      <section class="card reveal mt-6 tone-5">
        <div class="card-h"><h2><?= icon('star') ?>Your people</h2><a href="favourites.php">Edit</a></div>
        <div class="row" style="gap:var(--s5);overflow-x:auto;padding:var(--s1) 0">
          <?php foreach ($favourites as $f): ?>
            <a href="sendmoney.php?to=<?= e($f['saved_phone']) ?>" class="center"
               style="text-decoration:none;color:var(--ink);min-width:72px">
              <?= avatar($f['firstname'], $f['lastname'], $f['avatar_color'], 'mx') ?>
              <span class="small"><?= e($f['label'] ?: $f['firstname']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <div class="split mt-6">
      <section class="card reveal tone-1">
        <div class="card-h"><h2><?= icon('history') ?>What just happened</h2><a href="history.php">Everything</a></div>
        <?php if (!$recent): ?>
          <?= empty_state('wallet', 'Nothing here yet',
                'Fresh wallet, who dis? Top up once and your money moves start showing up here.',
                '<a class="btn" href="add_money.php">' . icon('wallet') . 'Top up</a>', 3, 2) ?>
        <?php else: ?>
          <ul class="rows"><?php foreach ($recent as $t) { echo trx_entry($t, $phone); } ?></ul>
        <?php endif; ?>
      </section>

      <section class="card reveal tone-6" style="--n:1">
        <div class="card-h"><h2><?= icon('chart') ?>Your week</h2><a href="analytics.php">Insights</a></div>
        <p class="small muted">How much left your wallet each day. The gold bar is today.</p>
        <div class="bars" role="img" aria-label="Spending over the last seven days">
          <?php foreach ($chart as $i => $c): ?>
            <div class="b<?= $i === 6 ? ' today' : '' ?>" title="<?= e($c['label'] . ': ' . money($c['value'])) ?>">
              <i style="height:<?= round($c['value'] / $chartMax * 100) ?>%;--n:<?= $i ?>"></i>
              <small><?= e($c['label']) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="between small mt-3">
          <span class="muted">Big spend day</span>
          <span class="num"><?= e(money($chartMax > 1 ? $chartMax : 0)) ?></span>
        </div>
      </section>
    </div>

  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
