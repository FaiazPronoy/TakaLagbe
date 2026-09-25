<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Audit and risk', number_format($auditTotal) . ' audit entries. Declined payments keep their reason; repeated bad sign ins are grouped by address.',
                  null, '', 'Back office') ?>

    <?php if ($frozen): ?>
      <div class="note warn rise" style="--n:1"><?= icon('lock') ?>
        <span><strong><?= count($frozen) ?></strong> frozen:
          <?php foreach ($frozen as $i => $f): ?><a href="admin_user.php?q=<?= e($f['phone']) ?>"><?= e($f['firstname'] . ' ' . $f['lastname']) ?></a><?= $i < count($frozen) - 1 ? ', ' : '' ?><?php endforeach; ?>
        </span></div>
    <?php endif; ?>

    <nav class="tabs rise" style="--n:1" aria-label="Views">
      <a href="?tab=audit"<?= $tab === 'audit' ? ' aria-current="page"' : '' ?>>Audit log</a>
      <a href="?tab=failed"<?= $tab === 'failed' ? ' aria-current="page"' : '' ?>>Declined <span class="badge"><?= count($failedRows) ?></span></a>
      <a href="?tab=logins"<?= $tab === 'logins' ? ' aria-current="page"' : '' ?>>Failed sign ins <span class="badge"><?= count($badLogins) ?></span></a>
    </nav>

    <section class="card reveal">
    <?php if ($tab === 'audit'): ?>
      <div class="card-h"><h2>Everything that happened</h2>
        <form method="get" class="row" role="search" aria-label="Search the audit log">
          <input type="hidden" name="tab" value="audit">
          <label class="sr-only" for="q">Search</label>
          <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Who, what or IP" style="max-width:220px;padding:.45rem .7rem">
          <button class="btn btn-sm" type="submit">Search</button>
        </form>
      </div>
      <?php if (!$auditRows): ?>
        <?= empty_state('list', 'Nothing recorded', $search !== '' ? 'No entry matches that search.' : 'Sign ins, payments and admin actions are written here.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <thead><tr><th scope="col">When</th><th scope="col">Who</th><th scope="col">What</th><th scope="col">Detail</th><th scope="col">From</th></tr></thead>
            <tbody>
            <?php foreach ($auditRows as $a): ?>
              <tr><td class="small nowrap"><?= e(short_date($a['created_at'])) ?></td>
                <td class="mono small"><?= e($a['actor']) ?></td>
                <td><span class="badge"><?= e(str_replace('_', ' ', $a['action'])) ?></span></td>
                <td class="small"><?= e($a['detail']) ?></td>
                <td class="mono small muted"><?= e($a['ip']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'failed'): ?>
      <div class="card-h"><h2>Payments the system refused</h2></div>
      <?php if (!$failedRows): ?>
        <?= empty_state('check', 'Nothing declined', 'Refused transfers, with the reason, are kept here so a complaint can be traced.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <thead><tr><th scope="col">When</th><th scope="col">What</th><th scope="col">From → to</th>
              <th scope="col" class="num">Amount</th><th scope="col">Reason</th></tr></thead>
            <tbody>
            <?php foreach ($failedRows as $t): ?>
              <tr><td class="small nowrap"><?= e(short_date($t['transaction_time'])) ?></td>
                <td class="small"><?= e($t['transaction_type']) ?></td>
                <td class="small"><?= e($t['transaction_from']) ?> → <?= e($t['transaction_to']) ?></td>
                <td class="num"><?= e(money($t['amount'], false)) ?></td>
                <td class="small"><?= e($t['note']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="card-h"><h2>Repeated failed sign ins, last 7 days</h2></div>
      <p class="small muted">Two or more from the same address. Five in fifteen minutes locks the number by itself.</p>
      <?php if (!$badLogins): ?>
        <?= empty_state('shield', 'All quiet', 'Nobody has been guessing passwords this week.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <thead><tr><th scope="col">Account</th><th scope="col">From</th><th scope="col" class="num">Failures</th>
              <th scope="col">Last try</th><th scope="col"><span class="sr-only">Open</span></th></tr></thead>
            <tbody>
            <?php foreach ($badLogins as $l): ?>
              <tr><td class="mono small"><?= e($l['phone']) ?></td><td class="mono small"><?= e($l['ip']) ?></td>
                <td class="num"><span class="badge <?= $l['n'] >= LOGIN_MAX_ATTEMPTS ? 'bad' : 'warn' ?>"><?= (int) $l['n'] ?></span></td>
                <td class="small nowrap"><?= e(short_date($l['last_try'])) ?></td>
                <td><?php if (valid_phone($l['phone'])): ?><a href="admin_user.php?q=<?= e($l['phone']) ?>">Open</a><?php endif; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    </section>

    <?php if ($tab === 'audit' && $auditPages > 1): ?>
      <nav class="pager" aria-label="Pages">
        <?php
        $start = max(1, min($page - 3, $auditPages - 6));
        for ($p = $start; $p <= min($auditPages, $start + 6); $p++): ?>
          <?php if ($p === $page): ?><span aria-current="page"><?= $p ?></span>
          <?php else: ?><a href="?<?= e(http_build_query(['tab' => 'audit', 'q' => $search, 'page' => $p])) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
