<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$ranges = ['30' => __('30 days'), '90' => __('3 months'), '365' => __('12 months')];
$tabs = '';
foreach ($ranges as $k => $lbl) {
    $tabs .= '<a class="chip' . ($range === $k ? ' on' : '') . '" href="?range=' . $k . '"'
           . ($range === $k ? ' aria-current="page"' : '') . '>' . e($lbl) . '</a>';
}
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head(__('Insights'), __('Where did it all go? Here\'s your last {period}.', ['period' => $ranges[$range]]), null,
                  '<nav class="chips m0" aria-label="' . e(__('Period')) . '">' . $tabs . '</nav>', badge: ['chart', 6]) ?>

    <div class="grid cols-4 mb-5">
      <?php foreach ([[__('You spent'), $spendTotal, 'neg'], [__('Per day'), $dailyAvg, ''],
                      [__('Per payment'), $avgSpend, ''], [__('Charges paid'), $feesPaid, '']] as $i => [$k, $v, $c]): ?>
        <div class="stat rise" style="--n:<?= $i + 1 ?>"><span class="label"><?= e($k) ?></span>
          <span class="v <?= $c ?>"><small>৳</small><?= e(money($v, false)) ?></span></div>
      <?php endforeach; ?>
    </div>

    <div class="split">
      <section class="card reveal">
        <div class="card-h"><h2><?= icon('exchange') ?><?= __('In vs out') ?></h2></div>
        <div class="table">
          <table>
            <caption class="sr-only"><?= __('Money in and out for the last six months') ?></caption>
            <thead><tr><th scope="col"><?= __('Month') ?></th><th scope="col" class="num"><?= __('In') ?></th><th scope="col" class="num"><?= __('Out') ?></th>
              <th scope="col"><span class="sr-only"><?= __('Compared') ?></span></th><th scope="col" class="num"><?= __('Net') ?></th></tr></thead>
            <tbody>
            <?php foreach ($trend as $t): $net = $t['incoming'] - $t['outgoing']; ?>
              <tr>
                <td><?= e($t['label']) ?></td>
                <td class="num pos"><?= e(money($t['incoming'], false)) ?></td>
                <td class="num neg"><?= e(money($t['outgoing'], false)) ?></td>
                <td style="width:30%">
                  <div class="flow" aria-hidden="true">
                    <span class="in" style="width:<?= round($t['incoming'] / $trendMax * 100) ?>%"></span>
                    <span class="out" style="width:<?= round($t['outgoing'] / $trendMax * 100) ?>%"></span>
                  </div>
                </td>
                <td class="num <?= $net >= 0 ? 'pos' : 'neg' ?>"><?= $net >= 0 ? '+' : '−' ?><?= e(money(abs($net), false)) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="small muted m0 mt-3"><?= __('The upper bar is money in, the lower bar money out.') ?></p>
      </section>

      <section class="card reveal" style="--n:1">
        <div class="card-h"><h2><?= icon('chart') ?><?= __('Where it went') ?></h2></div>
        <?php if (!$categories): ?>
          <?= empty_state('chart', __('Nothing spent yet'),
                __('Either you are very disciplined or you haven\'t started. Spend something and it shows up here.')) ?>
        <?php else: ?>
          <?= donut_slices($categories, 170, 24, __('Spending by category')) ?>
          <ul class="legend">
            <?php foreach ($categories as $c): ?>
              <li><i style="background:<?= e($c['colour']) ?>"></i><?= e(__($c['label'])) ?>
                <span class="muted small"><?= $spendTotal > 0 ? round($c['value'] / $spendTotal * 100) : 0 ?>%</span>
                <span class="amt"><?= e(money($c['value'], false)) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>

    <div class="grid cols-2 mt-5">
      <section class="card reveal">
        <div class="card-h"><h2><?= icon('users') ?><?= __('Your top people') ?></h2></div>
        <?php if (!$topPeople): ?>
          <p class="muted small m0"><?= __('Nobody got money from you in this stretch.') ?></p>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($topPeople as $p): ?>
              <li class="entry">
                <?= avatar($p['firstname'], $p['lastname'], $p['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($p['firstname'] ? $p['firstname'] . ' ' . $p['lastname'] : $p['who']) ?></span>
                  <span class="s"><?= e($p['n'] == 1 ? __('1 transfer') : __('{n} transfers', ['n' => (int) $p['n']])) ?></span></span>
                <span class="amt"><?= e(money($p['total'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="card reveal" style="--n:1">
        <div class="card-h"><h2><?= icon('star') ?><?= __('Fun facts') ?></h2></div>
        <ul class="rows">
          <li class="entry"><span class="ico"><?= icon('calendar') ?></span>
            <span class="body"><span class="t"><?= __('Busiest day') ?></span><span class="s"><?= __('When your wallet works hardest') ?></span></span>
            <span><?= e(isset($busiestDay['d']) ? __($busiestDay['d']) : __('None yet')) ?></span></li>
          <li class="entry"><span class="ico"><?= icon('arrow-out') ?></span>
            <span class="body"><span class="t"><?= __('Largest single payment') ?></span>
              <span class="s"><?= e($biggest ? __($biggest['transaction_type']) . ', ' . date('j M', strtotime($biggest['transaction_time'])) : __('None yet')) ?></span></span>
            <span class="amt"><?= $biggest ? e(money($biggest['amount'])) : '' ?></span></li>
          <li class="entry"><span class="ico"><?= icon('gift') ?></span>
            <span class="body"><span class="t"><?= __('Cashback earned') ?></span><span class="s"><?= __('Free money from recharges and shops') ?></span></span>
            <span class="amt pos">+ <?= e(money($cashbackEarned)) ?></span></li>
          <li class="entry"><span class="ico"><?= icon('alert') ?></span>
            <span class="body"><span class="t"><?= __('Declined attempts') ?></span><span class="s"><?= __('Times your balance or limit said no') ?></span></span>
            <span class="amt"><?= $failedCount ?></span></li>
        </ul>
        <a class="btn btn-quiet btn-block" style="margin-top:var(--s4)"
           href="statement.php?from=<?= e(date('Y-m-d', strtotime("-$days day"))) ?>&amp;to=<?= date('Y-m-d') ?>">
          <?= icon('file') ?><?= __('Statement for this period') ?></a>
      </section>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
