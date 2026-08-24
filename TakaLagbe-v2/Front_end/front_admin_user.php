<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Users</h1>
    <span class="tiny muted"><?= number_format($total) ?> matching</span>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form class="card" method="get" style="margin-bottom:1rem">
    <div class="row" style="align-items:flex-end">
      <div class="col field mb0" style="flex:2 1 240px">
        <label for="q">Search</label>
        <input id="q" name="q" value="<?= e($search) ?>" placeholder="Number, name, email or NID">
      </div>
      <div class="col field mb0">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="">All</option>
          <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="frozen" <?= $status === 'frozen' ? 'selected' : '' ?>>Frozen</option>
        </select>
      </div>
      <div class="col" style="flex:0 1 auto;display:flex;gap:.5rem">
        <button class="btn btn-primary" type="submit">Search</button>
        <a class="btn btn-ghost" href="admin_user.php">Clear</a>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>User</th><th>Contact</th><th class="num">Balance</th>
            <th class="num">Trx</th><th class="num">Daily limit</th>
            <th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;gap:.6rem;align-items:center">
                <span class="avatar avatar-sm" style="background:<?= e($u['avatar_color']) ?>">
                  <?= e(initials($u['firstname'], $u['lastname'])) ?></span>
                <div>
                  <strong><?= e($u['firstname'] . ' ' . $u['lastname']) ?></strong>
                  <div class="tiny muted mono"><?= e($u['phone']) ?></div>
                </div>
              </div>
            </td>
            <td class="tiny">
              <?= e($u['email']) ?><br>
              <span class="muted">NID <?= e(str_repeat('*', max(0, strlen($u['nid']) - 4))
                    . substr($u['nid'], -4)) ?></span><br>
              <span class="muted">Joined <?= e(date('d M Y', strtotime($u['created_at']))) ?></span>
            </td>
            <td class="num"><?= e(money($u['balance'] ?? 0, false)) ?></td>
            <td class="num"><?= (int) $u['n'] ?></td>
            <td class="num"><?= e(money($u['daily_limit'], false)) ?></td>
            <td><span class="badge <?= $u['status'] === 'active' ? 'badge-ok' : 'badge-fail' ?>">
              <?= e($u['status']) ?></span></td>
            <td>
              <div style="display:flex;gap:.3rem;flex-wrap:wrap">
                <form method="post"
                      data-confirm="<?= $u['status'] === 'active'
                        ? 'Freeze this account? The user will not be able to sign in.'
                        : 'Reopen this account?' ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action"
                         value="<?= $u['status'] === 'active' ? 'freeze' : 'unfreeze' ?>">
                  <input type="hidden" name="phone" value="<?= e($u['phone']) ?>">
                  <button class="btn <?= $u['status'] === 'active' ? 'btn-danger' : 'btn-success' ?> btn-sm"
                          type="submit"><?= $u['status'] === 'active' ? 'Freeze' : 'Unfreeze' ?></button>
                </form>
                <button class="btn btn-ghost btn-sm" type="button"
                        onclick="editLimit(<?= json_encode($u['phone']) ?>, <?= (float) $u['daily_limit'] ?>)">
                  Limit
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
          <tr><td colspan="7" class="center muted" style="padding:2rem">No users match that search.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <?php if ($p === $page): ?><span class="on"><?= $p ?></span>
        <?php else: ?>
          <a href="?q=<?= e($search) ?>&amp;status=<?= e($status) ?>&amp;page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</main>

<div class="modal" id="limitModal">
  <div class="modal-box">
    <h2>Change daily limit</h2>
    <p class="muted tiny" id="limitWho"></p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="limit">
      <input type="hidden" name="phone" id="limitPhone">
      <div class="field">
        <label for="daily_limit">New limit</label>
        <div class="money-input">
          <span><?= CURRENCY_SIGN ?></span>
          <input id="daily_limit" name="daily_limit" type="number" step="100"
                 min="1000" max="500000" required>
        </div>
      </div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('limitModal')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function editLimit(phone, current) {
  document.getElementById('limitPhone').value = phone;
  document.getElementById('daily_limit').value = current;
  document.getElementById('limitWho').textContent = phone;
  openModal('limitModal');
}
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
