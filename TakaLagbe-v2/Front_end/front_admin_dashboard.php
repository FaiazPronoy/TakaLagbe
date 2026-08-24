<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Platform overview</h1>
    <span class="tiny muted">Signed in as <?= e($_SESSION['admin_user']) ?></span>
  </div>

  <div class="grid grid-4" style="margin-bottom:1.2rem">
    <div class="stat"><div class="k">Registered users</div>
      <div class="v"><?= number_format($stats['users']) ?></div>
      <div class="tiny muted"><?= $stats['frozen'] ?> frozen</div></div>
    <div class="stat"><div class="k">Money held</div>
      <div class="v"><?= e(money($stats['float'], false)) ?></div>
      <div class="tiny muted">total customer balance</div></div>
    <div class="stat"><div class="k">Transactions</div>
      <div class="v"><?= number_format($stats['trxCount']) ?></div>
      <div class="tiny muted"><?= $stats['failed'] ?> declined</div></div>
    <div class="stat"><div class="k">Volume moved</div>
      <div class="v"><?= e(money($stats['volume'], false)) ?></div>
      <div class="tiny muted"><?= e(money($stats['today'], false)) ?> today</div></div>
    <div class="stat"><div class="k">Charges collected</div>
      <div class="v pos"><?= e(money($stats['fees'], false)) ?></div></div>
    <div class="stat"><div class="k">Cashback paid out</div>
      <div class="v neg"><?= e(money($stats['cashback'], false)) ?></div></div>
    <div class="stat"><div class="k">Net revenue</div>
      <div class="v <?= $stats['fees'] - $stats['cashback'] >= 0 ? 'pos' : 'neg' ?>">
        <?= e(money($stats['fees'] - $stats['cashback'], false)) ?></div></div>
    <div class="stat"><div class="k">Open tickets</div>
      <div class="v"><?= $stats['tickets'] ?></div>
      <div class="tiny muted"><a href="admin_supports.php">Handle them</a></div></div>
  </div>

  <div class="row">
    <div class="col" style="flex:2 1 420px">
      <div class="card">
        <div class="card-title">Volume, last 14 days</div>
        <div class="bars" style="height:170px">
          <?php foreach ($chart as $c): ?>
            <div class="b" title="<?= e($c['label'] . ': ' . money($c['value'])) ?>">
              <i style="height:<?= max(3, round($c['value'] / $chartMax * 100)) ?>%"></i>
              <small><?= e($c['label']) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="between tiny muted" style="margin-top:.6rem">
          <span>Busiest day</span><strong><?= e(money($chartMax)) ?></strong>
        </div>
      </div>
    </div>

    <div class="col" style="flex:1 1 260px">
      <div class="card">
        <div class="card-title">Volume by product</div>
        <?php if (!$byType): ?>
          <div class="empty"><p class="mb0">No transactions yet.</p></div>
        <?php else: ?>
          <?= donut_slices($byType, 150, 24) ?>
          <div class="legend" style="margin-top:.8rem">
            <?php foreach ($byType as $t): ?>
              <div>
                <i style="background:<?= e($t['colour']) ?>"></i>
                <span style="flex:1"><?= e($t['label']) ?>
                  <span class="muted tiny">(<?= $t['count'] ?>)</span></span>
                <strong class="tiny"><?= e(money($t['value'], false)) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top:1.2rem">
    <div class="col" style="flex:2 1 420px">
      <div class="card">
        <div class="between">
          <div class="card-title mb0">Latest transactions</div>
          <span class="tiny muted">newest first</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Reference</th><th>Type</th><th>From</th><th>To</th>
              <th class="num">Amount</th><th>Status</th><th>When</th></tr></thead>
            <tbody>
            <?php foreach ($latest as $t): ?>
              <tr>
                <td class="mono tiny"><?= e($t['trx_id']) ?></td>
                <td class="tiny"><?= trx_icon($t['transaction_type']) ?>
                  <?= e($t['transaction_type']) ?></td>
                <td class="tiny mono"><?= e($t['transaction_from']) ?></td>
                <td class="tiny mono"><?= e($t['transaction_to']) ?></td>
                <td class="num"><?= e(money($t['amount'], false)) ?></td>
                <td><span class="badge badge-<?= $t['status'] === 'success' ? 'ok'
                      : ($t['status'] === 'failed' ? 'fail' : 'pending') ?>">
                  <?= e($t['status']) ?></span></td>
                <td class="tiny muted"><?= e(time_ago($t['transaction_time'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$latest): ?>
              <tr><td colspan="7" class="center muted" style="padding:1.6rem">Nothing yet.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col" style="flex:1 1 260px">
      <div class="card">
        <div class="card-title">Most active users</div>
        <?php foreach ($topUsers as $u): ?>
          <a class="entry" href="admin_user.php?q=<?= e($u['phone']) ?>" style="color:inherit">
            <span class="avatar avatar-sm" style="background:<?= e($u['avatar_color']) ?>">
              <?= e(initials($u['firstname'], $u['lastname'])) ?></span>
            <span class="body">
              <span class="t"><?= e($u['firstname'] . ' ' . $u['lastname']) ?></span>
              <span class="s"><?= (int) $u['n'] ?> transactions</span>
            </span>
            <span class="amt tiny"><?= e(money($u['balance'])) ?></span>
          </a>
        <?php endforeach; ?>
        <?php if (!$topUsers): ?><p class="muted tiny mb0">No users yet.</p><?php endif; ?>
      </div>

      <div class="card" style="margin-top:1rem">
        <div class="card-title">Recent system activity</div>
        <?php foreach ($recentAudit as $a): ?>
          <div class="entry" style="padding:.45rem 0">
            <span class="body">
              <span class="t" style="font-size:.83rem">
                <?= e(ucfirst(str_replace('_', ' ', $a['action']))) ?></span>
              <span class="s"><?= e($a['actor']) ?> &middot; <?= e($a['detail']) ?></span>
            </span>
            <span class="tiny muted"><?= e(time_ago($a['created_at'])) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$recentAudit): ?><p class="muted tiny mb0">Nothing recorded.</p><?php endif; ?>
      </div>
    </div>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
