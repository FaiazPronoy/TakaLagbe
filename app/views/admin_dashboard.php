<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Overview', 'Signed in as ' . $_SESSION['admin_user'] . '. Figures are live from the ledger.', null, '', 'Back office') ?>

    <div class="grid cols-4 mb-5">
      <?php
      $tiles = [
          ['Customers', number_format($stats['users']), $stats['frozen'] . ' frozen', ''],
          ['Money held', '৳' . money($stats['float'], false), 'across every wallet', ''],
          ['Transactions', number_format($stats['trxCount']), $stats['failed'] . ' declined', ''],
          ['Volume moved', '৳' . money($stats['volume'], false), '৳' . money($stats['today'], false) . ' today', ''],
          ['Charges collected', '৳' . money($stats['fees'], false), 'cash out and bills', 'pos'],
          ['Cashback paid', '৳' . money($stats['cashback'], false), 'recharges and shops', 'neg'],
          ['Net revenue', '৳' . money($stats['fees'] - $stats['cashback'], false), 'charges less cashback',
              $stats['fees'] - $stats['cashback'] >= 0 ? 'pos' : 'neg'],
          ['Open tickets', (string) $stats['tickets'], 'waiting for a reply', ''],
      ];
      foreach ($tiles as $i => [$k, $v, $sub, $cls]): ?>
        <div class="stat rise" style="--n:<?= $i % 4 + 1 ?>"><span class="label"><?= e($k) ?></span>
          <span class="v <?= $cls ?>"><?= e($v) ?></span><span class="sub"><?= e($sub) ?></span></div>
      <?php endforeach; ?>
    </div>

    <div class="split">
      <section class="card reveal" aria-labelledby="volTitle">
        <div class="card-h"><h2 id="volTitle">Volume, last 14 days</h2></div>
        <div class="bars" style="height:180px" role="img" aria-label="Daily volume over the last fourteen days">
          <?php foreach ($chart as $i => $c): ?>
            <div class="b<?= $i === 13 ? ' today' : '' ?>" title="<?= e($c['label'] . ': ' . money($c['value'])) ?>">
              <i style="height:<?= round($c['value'] / $chartMax * 100) ?>%;--n:<?= $i ?>"></i>
              <small><?= e($c['label']) ?></small></div>
          <?php endforeach; ?>
        </div>
        <p class="between small m0 mt-3"><span class="muted">Busiest day</span>
          <span class="num"><?= e(money($chartMax > 1 ? $chartMax : 0)) ?></span></p>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="mixTitle">
        <div class="card-h"><h2 id="mixTitle">By product</h2></div>
        <?php if (!$byType): ?>
          <p class="muted small">No transactions yet.</p>
        <?php else: ?>
          <?= donut_slices($byType, 150, 22, 'Volume by product') ?>
          <ul class="legend">
            <?php foreach ($byType as $t): ?>
              <li><i style="background:<?= e($t['colour']) ?>"></i><?= e($t['label']) ?>
                <span class="muted small">(<?= $t['count'] ?>)</span>
                <span class="amt small"><?= e(money($t['value'], false)) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>

    <div class="split mt-5">
      <section class="card reveal" aria-labelledby="latestTitle">
        <div class="card-h"><h2 id="latestTitle">Latest transactions</h2><a href="admin_audit.php?tab=failed">Declined only</a></div>
        <div class="table">
          <table>
            <thead><tr><th scope="col">Reference</th><th scope="col">Type</th><th scope="col">From → to</th>
              <th scope="col" class="num">Amount</th><th scope="col">When</th></tr></thead>
            <tbody>
            <?php foreach ($latest as $t): ?>
              <tr>
                <td class="mono small"><?= e($t['trx_id']) ?></td>
                <td class="small"><?= e($t['transaction_type']) ?>
                  <?php if ($t['status'] !== 'success'): ?><br><span class="badge bad">declined</span><?php endif; ?></td>
                <td class="small"><?= e($t['transaction_from']) ?> → <?= e($t['transaction_to']) ?></td>
                <td class="num"><?= e(money($t['amount'], false)) ?></td>
                <td class="small muted nowrap"><?= e(time_ago($t['transaction_time'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$latest): ?><tr><td colspan="5" class="muted center">Nothing has moved yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <div class="stack">
        <section class="card reveal" style="--n:1" aria-labelledby="topTitle">
          <div class="card-h"><h2 id="topTitle">Most active</h2></div>
          <ul class="rows">
            <?php foreach ($topUsers as $u): ?>
              <li><a class="entry" href="admin_users.php?q=<?= e($u['phone']) ?>">
                <?= avatar($u['firstname'], $u['lastname'], $u['avatar_color']) ?>
                <span class="body"><span class="t"><?= e($u['firstname'] . ' ' . $u['lastname']) ?></span>
                  <span class="s"><?= (int) $u['n'] ?> transactions</span></span>
                <span class="amt small"><?= e(money($u['balance'])) ?></span></a></li>
            <?php endforeach; ?>
          </ul>
          <?php if (!$topUsers): ?><p class="muted small">No customers yet.</p><?php endif; ?>
        </section>

        <section class="card reveal" style="--n:2" aria-labelledby="auditTitle">
          <div class="card-h"><h2 id="auditTitle">Just now</h2><a href="admin_audit.php">Audit log</a></div>
          <ul class="rows">
            <?php foreach ($recentAudit as $a): ?>
              <li class="entry" style="padding:var(--s2) 0">
                <span class="body"><span class="t"><?= e(ucfirst(str_replace('_', ' ', $a['action']))) ?></span>
                  <span class="s"><?= e($a['actor']) ?> · <?= e($a['detail']) ?></span></span>
                <span class="small muted nowrap"><?= e(time_ago($a['created_at'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      </div>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
