<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Insights</h1>
    <div class="chips">
      <?php foreach (['30' => 'Last 30 days', '90' => 'Last 3 months', '365' => 'Last year'] as $k => $lbl): ?>
        <a class="chip <?= $range === $k ? 'on' : '' ?>" href="?range=<?= $k ?>"
           style="text-decoration:none"><?= $lbl ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- --------------------------------------------------- headline -->
  <div class="row" style="margin-bottom:1.2rem">
    <div class="col"><div class="stat"><div class="k">Total spent</div>
      <div class="v neg"><?= e(money($spendTotal, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Average per day</div>
      <div class="v"><?= e(money($dailyAvg, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Average per transaction</div>
      <div class="v"><?= e(money($avgSpend, false)) ?></div></div></div>
    <div class="col"><div class="stat"><div class="k">Charges paid</div>
      <div class="v"><?= e(money($feesPaid, false)) ?></div></div></div>
  </div>

  <div class="row">
    <!-- ------------------------------------------ category donut -->
    <div class="col" style="flex:1 1 300px">
      <div class="card">
        <div class="card-title">Where the money went</div>
        <?php if (!$categories): ?>
          <div class="empty"><div class="big">&#128202;</div>
            <p class="mb0">Nothing spent in this period.</p></div>
        <?php else: ?>
          <?= donut_slices($categories) ?>
          <div class="legend" style="margin-top:1rem">
            <?php foreach ($categories as $c): ?>
              <div>
                <i style="background:<?= e($c['colour']) ?>"></i>
                <span style="flex:1"><?= e($c['label']) ?>
                  <span class="muted tiny">(<?= $c['count'] ?>)</span></span>
                <strong><?= e(money($c['value'], false)) ?></strong>
                <span class="muted tiny" style="min-width:44px;text-align:right">
                  <?= $spendTotal > 0 ? round($c['value'] / $spendTotal * 100) : 0 ?>%
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- --------------------------------------------- month trend -->
    <div class="col" style="flex:1.4 1 380px">
      <div class="card">
        <div class="card-title">Six month trend</div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Month</th><th class="num">In</th><th class="num">Out</th><th>Balance of flow</th></tr></thead>
            <tbody>
            <?php foreach ($trend as $t):
              $net = $t['incoming'] - $t['outgoing']; ?>
              <tr>
                <td><strong><?= e($t['label']) ?></strong></td>
                <td class="num pos"><?= e(money($t['incoming'], false)) ?></td>
                <td class="num neg"><?= e(money($t['outgoing'], false)) ?></td>
                <td style="min-width:150px">
                  <div style="display:flex;gap:3px;align-items:center">
                    <div class="bar" style="flex:1;height:7px">
                      <span style="width:<?= round($t['incoming'] / $trendMax * 100) ?>%;
                                   background:var(--accent)"></span>
                    </div>
                    <div class="bar" style="flex:1;height:7px">
                      <span style="width:<?= round($t['outgoing'] / $trendMax * 100) ?>%;
                                   background:var(--danger)"></span>
                    </div>
                  </div>
                  <span class="tiny <?= $net >= 0 ? 'pos' : 'neg' ?>">
                    <?= $net >= 0 ? '+' : '' ?><?= e(money($net, false)) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="tiny muted mb0" style="margin-top:.6rem">
          Green is money coming in, red is money going out.
        </p>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top:1.2rem">
    <!-- ------------------------------------------- top receivers -->
    <div class="col">
      <div class="card">
        <div class="card-title">You send to these people most</div>
        <?php if (!$topPeople): ?>
          <div class="empty"><p class="mb0">No transfers in this period.</p></div>
        <?php else: foreach ($topPeople as $p): ?>
          <div class="entry">
            <span class="ico">&#128100;</span>
            <span class="body">
              <span class="t"><?= e(name_of($p['who'])) ?></span>
              <span class="s"><?= e($p['who']) ?> &middot; <?= (int) $p['n'] ?> transfer(s)</span>
            </span>
            <span class="amt"><?= e(money($p['total'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- --------------------------------------------- habit facts -->
    <div class="col">
      <div class="card">
        <div class="card-title">Good to know</div>
        <div class="entry">
          <span class="ico">&#128197;</span>
          <span class="body"><span class="t">Busiest day</span>
            <span class="s">When you spend most often</span></span>
          <span class="amt"><?= e($busiestDay['d'] ?? 'n/a') ?></span>
        </div>
        <div class="entry">
          <span class="ico">&#128176;</span>
          <span class="body"><span class="t">Largest single payment</span>
            <span class="s"><?= e($biggest ? $biggest['transaction_type'] . ' on '
                . date('d M Y', strtotime($biggest['transaction_time'])) : 'Nothing yet') ?></span></span>
          <span class="amt"><?= e($biggest ? money($biggest['amount']) : '-') ?></span>
        </div>
        <div class="entry">
          <span class="ico">&#127873;</span>
          <span class="body"><span class="t">Cashback earned</span>
            <span class="s">Money given back to you</span></span>
          <span class="amt pos">+ <?= e(money($cashbackEarned)) ?></span>
        </div>
        <div class="entry">
          <span class="ico">&#9888;</span>
          <span class="body"><span class="t">Declined attempts</span>
            <span class="s">Blocked by balance, limit or PIN checks</span></span>
          <span class="amt"><?= $failedCount ?></span>
        </div>
        <a class="btn btn-ghost btn-block btn-sm" style="margin-top:.8rem"
           href="statement.php?from=<?= e(date('Y-m-d', strtotime("-$days day"))) ?>&amp;to=<?= date('Y-m-d') ?>">
          Open the statement for this period
        </a>
      </div>
    </div>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
