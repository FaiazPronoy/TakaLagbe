<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

// the four things people come to do, then everything else
$main = [
    ['sendmoney.php', 'send',     'Send money', 1],
    ['add_money.php', 'wallet',   'Add money',  2],
    ['cash_out.php',  'cash-out', 'Cash out',   8],
    ['paybill.php',   'receipt',  'Pay bills',  5],
];
$more = [
    ['mobilerecharge.php', 'phone',   'Recharge',   6],
    ['payment.php',        'bag',     'Pay a shop', 7],
    ['education_fee.php',  'cap',     'Tuition',    4],
    ['cash_in.php',        'cash-in', 'Cash in',    2],
    ['savings.php',        'target',  'Savings',    3],
    ['requests.php',       'split',   'Split a bill', 4],
    ['qr.php',             'qr',      'My QR',      1],
    ['statement.php',      'file',    'Statement',  8],
];
$usedPct = $limit > 0 ? min(100, round($todayOut / $limit * 100)) : 0;
$hour    = (int) date('G');
$hello   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$owed    = array_sum(array_map(static fn($r) => (float) $r['amount'], $requests));
?>
<main id="main" class="page">
  <div class="wrap home">

    <div class="head rise">
      <div>
        <h1><?= e($hello) ?>, <?= e($user['firstname']) ?></h1>
        <p class="lede">টাকা লাগবে? পাঠাও, ভাগ করো, নয়তো জমিয়ে রাখো।</p>
      </div>
    </div>

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

      <div class="hero-foot">
        <div><span class="k">In this month</span><span class="n">+ ৳<?= e(money($monthIn, false)) ?></span></div>
        <div><span class="k">Out this month</span><span class="n">− ৳<?= e(money($monthOut, false)) ?></span></div>
        <div class="grow">
          <span class="k">Sent today · <?= e(money($todayOut, false)) ?> of <?= e(money($limit, false)) ?></span>
          <div class="meter" role="progressbar" aria-label="Daily limit used"
               aria-valuenow="<?= $usedPct ?>" aria-valuemin="0" aria-valuemax="100">
            <span style="width:<?= $usedPct ?>%"></span></div>
        </div>
      </div>
    </section>

    <nav class="big-actions rise" style="--n:2" aria-label="Main actions">
      <?php foreach ($main as [$href, $ico, $name, $tone]): ?>
        <a class="big-action lift tone-<?= $tone ?>" href="<?= $href ?>"><span class="disc"><?= icon($ico) ?></span><?= e($name) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($requests): ?>
      <a class="alert-strip rise" style="--n:3" href="requests.php">
        <?= icon('hand') ?>
        <span><strong><?= count($requests) === 1 ? e($requests[0]['firstname'] ?: $requests[0]['requester_phone']) . ' is'
                        : count($requests) . ' people are' ?></strong> waiting for
          <strong><?= e(money($owed)) ?></strong> from you</span>
        <span class="go">Review <?= icon('arrow-out') ?></span>
      </a>
    <?php endif; ?>

    <div class="home-grid">
      <section class="card reveal" aria-labelledby="moreTitle">
        <div class="card-h"><h2 id="moreTitle">More services</h2></div>
        <div class="services">
          <?php foreach ($more as $i => [$href, $ico, $name, $tone]): ?>
            <a class="service tone-<?= $tone ?>" href="<?= $href ?>"><span class="disc"><?= icon($ico) ?></span><?= e($name) ?></a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="actTitle">
        <div class="card-h"><h2 id="actTitle">Recent activity</h2><a href="history.php">See all</a></div>
        <?php if (!$recent): ?>
          <?= empty_state('wallet', 'No activity yet',
                'Add money to your wallet and your transactions will show up here.',
                '<a class="btn" href="add_money.php">' . icon('wallet') . 'Add money</a>', 3, 2) ?>
        <?php else: ?>
          <ul class="rows"><?php foreach (array_slice($recent, 0, 5) as $t) { echo trx_entry($t, $phone); } ?></ul>
        <?php endif; ?>
      </section>
    </div>

  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
