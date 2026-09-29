<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

// the four things people come to do, then everything else
$main = [
    ['sendmoney.php', 'send',     __('Send money'), 1],
    ['add_money.php', 'wallet',   __('Add money'),  2],
    ['cash_out.php',  'cash-out', __('Cash out'),   8],
    ['paybill.php',   'receipt',  __('Pay bills'),  5],
];
$more = [
    ['mobilerecharge.php', 'phone',   __('Recharge'),     6],
    ['payment.php',        'bag',     __('Pay a shop'),   7],
    ['education_fee.php',  'cap',     __('Tuition'),      4],
    ['cash_in.php',        'cash-in', __('Cash in'),      2],
    ['savings.php',        'target',  __('Savings'),      3],
    ['requests.php',       'split',   __('Split a bill'), 4],
    ['qr.php',             'qr',      __('My QR'),        1],
    ['statement.php',      'file',    __('Statement'),    8],
];
$usedPct = $limit > 0 ? min(100, round($todayOut / $limit * 100)) : 0;
$hour    = (int) date('G');
$hello   = $hour < 12 ? __('Good morning') : ($hour < 17 ? __('Good afternoon') : __('Good evening'));
$owed    = array_sum(array_map(static fn($r) => (float) $r['amount'], $requests));
?>
<main id="main" class="page home">
  <!-- the wallet itself: a green band across the top, like opening the app -->
  <section class="band" aria-labelledby="hello">
    <?= guilloche(26) ?>
    <div class="wrap band-in">
      <div class="band-copy rise">
        <p class="band-kicker"><?= e($hello) ?></p>
        <h1 id="hello"><?= e($user['firstname']) ?></h1>
        <p class="band-line">টাকা লাগবে? পাঠাও, ভাগ করো, নয়তো জমিয়ে রাখো।</p>
        <div class="band-stats" aria-label="<?= e(__('This month')) ?>">
          <div><span class="k"><?= __('In this month') ?></span><span class="n">+ ৳<?= e(money($monthIn, false)) ?></span></div>
          <div><span class="k"><?= __('Out this month') ?></span><span class="n">− ৳<?= e(money($monthOut, false)) ?></span></div>
        </div>
        <div class="band-limit">
          <span class="k"><?= __('Sent today · {sent} of {limit}', ['sent' => '৳' . e(money($todayOut, false)), 'limit' => '৳' . e(money($limit, false))]) ?></span>
          <div class="meter" role="progressbar" aria-label="<?= e(__('Daily limit used')) ?>"
               aria-valuenow="<?= $usedPct ?>" aria-valuemin="0" aria-valuemax="100">
            <span style="width:<?= $usedPct ?>%"></span></div>
        </div>
      </div>

      <section class="hero bankcard rise" style="--n:1" aria-labelledby="balLabel">
        <?= guilloche() ?>
        <div class="card-top">
          <span class="card-brand"><span class="mark" aria-hidden="true">৳</span><?= e(APP_NAME) ?></span>
          <?= icon('wifi') ?>
        </div>
        <div class="card-chip" aria-hidden="true"></div>
        <div class="between">
          <span class="label" id="balLabel"><?= __('Available balance') ?></span>
          <button class="tool js-only" id="eyeBtn" type="button" aria-pressed="false" aria-label="<?= e(__('Hide balance')) ?>"
                  style="color:var(--on-hero)">
            <span class="i-eye" style="display:none"><?= icon('eye') ?></span>
            <span class="i-eye-off"><?= icon('eye-off') ?></span>
          </button>
        </div>
        <p class="figure" id="balanceFigure">
          <span class="real"><span class="cur">৳</span><?= e(money($bal, false)) ?></span>
          <span class="dots" hidden><span class="cur">৳</span>••••••</span>
        </p>
        <div class="card-bottom">
          <span class="card-no" aria-label="<?= e(__('Wallet number {phone}', ['phone' => $phone])) ?>">•••• •••• <?= e(substr($phone, -4)) ?></span>
          <span><?= e($user['firstname'] . ' ' . $user['lastname']) ?></span>
        </div>
      </section>
    </div>
  </section>

  <div class="wrap">
    <nav class="big-actions rise" style="--n:2" aria-label="<?= e(__('Main actions')) ?>">
      <?php foreach ($main as [$href, $ico, $name, $tone]): ?>
        <a class="big-action tone-<?= $tone ?>" href="<?= $href ?>"><span class="disc"><?= icon($ico) ?></span><?= e($name) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($requests): ?>
      <a class="alert-strip rise" style="--n:3" href="requests.php">
        <?= icon('hand') ?>
        <span><?= count($requests) === 1
            ? __('{name} is waiting for {amount} from you', [
                'name'   => '<strong>' . e($requests[0]['firstname'] ?: $requests[0]['requester_phone']) . '</strong>',
                'amount' => '<strong>' . e(money($owed)) . '</strong>'])
            : __('{n} people are waiting for {amount} from you', [
                'n'      => '<strong>' . count($requests) . '</strong>',
                'amount' => '<strong>' . e(money($owed)) . '</strong>']) ?></span>
        <span class="go"><?= __('Review') ?> <?= icon('arrow-out') ?></span>
      </a>
    <?php endif; ?>

    <div class="home-grid">
      <section class="card reveal" aria-labelledby="moreTitle">
        <div class="card-h"><h2 id="moreTitle"><?= __('More services') ?></h2></div>
        <div class="services">
          <?php foreach ($more as $i => [$href, $ico, $name, $tone]): ?>
            <a class="service tone-<?= $tone ?>" href="<?= $href ?>"><span class="disc"><?= icon($ico) ?></span><?= e($name) ?></a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="actTitle">
        <div class="card-h"><h2 id="actTitle"><?= __('Recent activity') ?></h2><a href="history.php"><?= __('See all') ?></a></div>
        <?php if (!$recent): ?>
          <?= empty_state('wallet', __('No activity yet'),
                __('Add money to your wallet and your transactions will show up here.'),
                '<a class="btn" href="add_money.php">' . icon('wallet') . e(__('Add money')) . '</a>', 3, 2) ?>
        <?php else: ?>
          <ul class="rows"><?php foreach (array_slice($recent, 0, 5) as $t) { echo trx_entry($t, $phone); } ?></ul>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
