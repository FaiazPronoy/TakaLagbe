<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Audit and risk</h1>
    <span class="tiny muted"><?= number_format($auditTotal) ?> audit entries</span>
  </div>

  <?php if ($frozen): ?>
    <div class="alert alert-warn" style="margin-bottom:1rem">
      <span><strong><?= count($frozen) ?></strong> account(s) are frozen:
        <?php foreach ($frozen as $i => $f): ?>
          <a href="admin_user.php?q=<?= e($f['phone']) ?>"><?= e($f['phone']) ?></a><?= $i < count($frozen) - 1 ? ', ' : '' ?>
        <?php endforeach; ?>
      </span>
    </div>
  <?php endif; ?>

  <div class="tabs">
    <a href="?tab=audit"  class="<?= $tab === 'audit'  ? 'on' : '' ?>">Audit log</a>
    <a href="?tab=failed" class="<?= $tab === 'failed' ? 'on' : '' ?>">Declined transactions
      <?php if ($failedRows): ?><span class="badge"><?= count($failedRows) ?></span><?php endif; ?></a>
    <a href="?tab=logins" class="<?= $tab === 'logins' ? 'on' : '' ?>">Failed sign ins
      <?php if ($badLogins): ?><span class="badge"><?= count($badLogins) ?></span><?php endif; ?></a>
  </div>

  <?php if ($tab === 'audit'): ?>
    <div class="card">
      <div class="between" style="margin-bottom:.6rem">
        <div class="card-title mb0">Everything that happened</div>
        <form method="get" style="display:flex;gap:.4rem">
          <input type="hidden" name="tab" value="audit">
          <input name="q" value="<?= e($search) ?>" placeholder="Actor, action, detail or IP"
                 style="width:230px;padding:.4rem .6rem">
          <button class="btn btn-primary btn-sm" type="submit">Search</button>
        </form>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Detail</th><th>From</th></tr></thead>
          <tbody>
          <?php foreach ($auditRows as $a): ?>
            <tr>
              <td class="tiny"><?= e(short_date($a['created_at'])) ?></td>
              <td class="mono tiny"><?= e($a['actor']) ?></td>
              <td><span class="badge"><?= e(str_replace('_', ' ', $a['action'])) ?></span></td>
              <td class="tiny"><?= e($a['detail']) ?></td>
              <td class="mono tiny muted"><?= e($a['ip']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$auditRows): ?>
            <tr><td colspan="5" class="center muted" style="padding:2rem">Nothing recorded.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($auditPages > 1): ?>
      <div class="pager">
        <?php
        $start = max(1, $page - 3);
        $end   = min($auditPages, $start + 6);
        for ($p = $start; $p <= $end; $p++): ?>
          <?php if ($p === $page): ?><span class="on"><?= $p ?></span>
          <?php else: ?>
            <a href="?tab=audit&amp;q=<?= e($search) ?>&amp;page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($tab === 'failed'): ?>
    <div class="card">
      <div class="card-title">Transactions the system refused</div>
      <p class="tiny muted">
        Every declined attempt is kept, with the reason, so a customer complaint can
        always be traced back.
      </p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>When</th><th>Type</th><th>From</th><th>To</th>
            <th class="num">Amount</th><th>Reason</th></tr></thead>
          <tbody>
          <?php foreach ($failedRows as $t): ?>
            <tr>
              <td class="tiny"><?= e(short_date($t['transaction_time'])) ?></td>
              <td class="tiny"><?= trx_icon($t['transaction_type']) ?> <?= e($t['transaction_type']) ?></td>
              <td class="mono tiny"><?= e($t['transaction_from']) ?></td>
              <td class="mono tiny"><?= e($t['transaction_to']) ?></td>
              <td class="num"><?= e(money($t['amount'], false)) ?></td>
              <td class="tiny"><?= e($t['note']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$failedRows): ?>
            <tr><td colspan="6" class="center muted" style="padding:2rem">
              Nothing has been declined.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php else: ?>
    <div class="card">
      <div class="card-title">Repeated failed sign ins, last 7 days</div>
      <p class="tiny muted">
        Two or more failures from the same address. Five in fifteen minutes locks the number
        automatically, and five wrong PINs freeze the account.
      </p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Account</th><th>From</th><th class="num">Failures</th>
            <th>Last attempt</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($badLogins as $l): ?>
            <tr>
              <td class="mono tiny"><?= e($l['phone']) ?></td>
              <td class="mono tiny"><?= e($l['ip']) ?></td>
              <td class="num">
                <span class="badge <?= $l['n'] >= LOGIN_MAX_ATTEMPTS ? 'badge-fail' : 'badge-pending' ?>">
                  <?= (int) $l['n'] ?></span>
              </td>
              <td class="tiny"><?= e(short_date($l['last_try'])) ?></td>
              <td>
                <?php if (valid_phone($l['phone'])): ?>
                  <a class="btn btn-ghost btn-sm" href="admin_user.php?q=<?= e($l['phone']) ?>">Open</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$badLogins): ?>
            <tr><td colspan="5" class="center muted" style="padding:2rem">
              No repeated failures. All quiet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
