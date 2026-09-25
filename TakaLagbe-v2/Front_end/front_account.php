<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$actions = [
    ['sendmoney.php',      'send',     'Send money'],
    ['add_money.php',      'wallet',   'Add money'],
    ['cash_out.php',       'cash-out', 'Cash out'],
    ['cash_in.php',        'cash-in',  'Cash in'],
    ['mobilerecharge.php', 'phone',    'Recharge'],
    ['paybill.php',        'receipt',  'Pay a bill'],
    ['payment.php',        'bag',      'Pay a shop'],
    ['education_fee.php',  'cap',      'Tuition'],
    ['qr.php',             'qr',       'My QR'],
    ['statement.php',      'file',     'Statement'],
];
$usedPct = $limit > 0 ? min(100, round($todayOut / $limit * 100)) : 0;
$hour    = (int) date('G');
$hello   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<main id="main" class="page">
  <div class="wrap">

    <div class="head rise">
      <div>
        <span class="eyebrow"><?= e(date('l, j F')) ?></span>
        <h1><?= e($hello) ?>, <?= e($user['firstname']) ?>.</h1>
      </div>
      <div class="row">
        <a class="btn btn-quiet" href="add_money.php"><?= icon('wallet') ?>Add money</a>
        <a class="btn" href="sendmoney.php"><?= icon('send') ?>Send money</a>
      </div>
    </div>

    <div class="split">
      <section class="hero rise" style="--n:1" aria-labelledby="balLabel">
        <?= guilloche() ?>
        <div class="between">
          <span class="label" id="balLabel">Available balance</span>
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
        <?php foreach ([['In this month', $monthIn, 'pos'], ['Out this month', $monthOut, 'neg'],
                        ['Put aside in savings', $saved, ''], ['Cashback earned', $cashback, '']] as $i => [$k, $v, $cls]): ?>
          <div class="stat rise" style="--n:<?= $i + 2 ?>">
            <span class="label"><?= e($k) ?></span>
            <span class="v <?= $cls ?>"><small>৳</small><?= e(money($v, false)) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($requests): ?>
      <section class="card reveal mt-5" style="border-left:3px solid var(--brass)">
        <div class="card-h"><h2>Waiting on you</h2><a href="requests.php">All requests</a></div>
        <ul class="rows">
          <?php foreach ($requests as $r):
            $who = $r['firstname'] ? $r['firstname'] . ' ' . $r['lastname'] : $r['requester_phone']; ?>
            <li class="entry">
              <span class="ico"><?= icon('hand') ?></span>
              <span class="body">
                <span class="t"><?= e($who) ?> asked for <span class="num"><?= e(money($r['amount'])) ?></span></span>
                <span class="s"><?= e($r['note'] ?: 'No note') ?> · <?= e(time_ago($r['created_at'])) ?></span>
              </span>
              <a class="btn btn-sm" href="sendmoney.php?request=<?= (int) $r['id'] ?>">Pay</a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section class="mt-6" aria-labelledby="doTitle">
      <h2 id="doTitle" class="reveal">What do you need?</h2>
      <div class="actions">
        <?php foreach ($actions as $i => [$href, $ico, $name]): ?>
          <a class="action lift reveal" style="--n:<?= $i % 5 ?>" href="<?= $href ?>">
            <span class="disc"><?= icon($ico) ?></span><?= e($name) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($favourites): ?>
      <section class="card reveal mt-6">
        <div class="card-h"><h2>Send again</h2><a href="favourites.php">Manage</a></div>
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
      <section class="card reveal">
        <div class="card-h"><h2>Recent activity</h2><a href="history.php">Full history</a></div>
        <?php if (!$recent): ?>
          <?= empty_state('wallet', 'Nothing here yet',
                'Your transfers, bills and top ups will be listed here as they happen. Add money to get going.',
                '<a class="btn" href="add_money.php">Add money</a>') ?>
        <?php else: ?>
          <ul class="rows"><?php foreach ($recent as $t) { echo trx_entry($t, $phone); } ?></ul>
        <?php endif; ?>
      </section>

      <section class="card reveal" style="--n:1">
        <div class="card-h"><h2>This week</h2><a href="analytics.php">Insights</a></div>
        <p class="small muted">Money out each day, charges included.</p>
        <div class="bars" role="img" aria-label="Spending over the last seven days">
          <?php foreach ($chart as $i => $c): ?>
            <div class="b<?= $i === 6 ? ' today' : '' ?>" title="<?= e($c['label'] . ': ' . money($c['value'])) ?>">
              <i style="height:<?= round($c['value'] / $chartMax * 100) ?>%;--n:<?= $i ?>"></i>
              <small><?= e($c['label']) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="between small mt-3">
          <span class="muted">Biggest day</span>
          <span class="num"><?= e(money($chartMax > 1 ? $chartMax : 0)) ?></span>
        </div>
      </section>
    </div>

  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
