<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Customers', number_format($total) . ' matching. Freeze an account, reopen it, or change a daily limit.', null, '', 'Back office') ?>
    <?= error_note($error) ?>

    <form class="card rise mb-4" style="--n:1" method="get" role="search" aria-label="Find customers">
      <div class="row" style="align-items:flex-end">
        <div class="field m0" style="flex:2 1 240px"><label for="q">Search</label>
          <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Number, name, email or NID"></div>
        <div class="field m0" style="flex:1 1 140px"><label for="status">Status</label>
          <select id="status" name="status">
            <option value="">Any</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="frozen" <?= $status === 'frozen' ? 'selected' : '' ?>>Frozen</option>
          </select></div>
        <button class="btn" type="submit"><?= icon('search') ?>Search</button>
        <?php if ($search !== '' || $status !== ''): ?><a class="btn btn-quiet" href="admin_user.php">Clear</a><?php endif; ?>
      </div>
    </form>

    <section class="card reveal" aria-label="Customers">
      <?php if (!$users): ?>
        <?= empty_state('users', 'Nobody matches', 'Search by any part of a number, name, email or NID.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <thead><tr><th scope="col">Customer</th><th scope="col">Contact</th><th scope="col" class="num">Balance</th>
              <th scope="col" class="num">Trx</th><th scope="col" class="num">Daily limit</th><th scope="col">Status</th>
              <th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u):
              $active = $u['status'] === 'active'; ?>
              <tr>
                <td><div class="row" style="flex-wrap:nowrap">
                  <?= avatar($u['firstname'], $u['lastname'], $u['avatar_color']) ?>
                  <span><strong><?= e($u['firstname'] . ' ' . $u['lastname']) ?></strong><br>
                    <span class="mono small muted"><?= e($u['phone']) ?></span></span></div></td>
                <td class="small"><?= e($u['email']) ?><br>
                  <span class="muted">NID ••••<?= e(substr($u['nid'], -4)) ?> · joined <?= e(date('j M Y', strtotime($u['created_at']))) ?></span></td>
                <td class="num"><?= e(money($u['balance'] ?? 0, false)) ?></td>
                <td class="num"><?= (int) $u['n'] ?></td>
                <td class="num"><?= e(money($u['daily_limit'], false)) ?></td>
                <td><span class="badge <?= $active ? 'ok' : 'bad' ?>"><?= e($u['status']) ?></span></td>
                <td>
                  <div class="row" style="flex-wrap:nowrap;align-items:flex-start">
                    <form method="post" data-confirm="<?= $active ? 'Freeze this account? They will be signed out and cannot sign in.' : 'Reopen this account?' ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="<?= $active ? 'freeze' : 'unfreeze' ?>">
                      <input type="hidden" name="phone" value="<?= e($u['phone']) ?>">
                      <button class="btn btn-sm <?= $active ? 'btn-danger' : '' ?>" type="submit"><?= $active ? 'Freeze' : 'Reopen' ?></button>
                    </form>
                    <details class="inline">
                      <summary class="btn btn-quiet btn-sm">Limit</summary>
                      <form method="post" class="panel" style="min-width:220px">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="limit">
                        <input type="hidden" name="phone" value="<?= e($u['phone']) ?>">
                        <label for="lim<?= e($u['phone']) ?>">New daily limit</label>
                        <div class="row" style="flex-wrap:nowrap">
                          <input id="lim<?= e($u['phone']) ?>" name="daily_limit" type="number" step="100" min="1000" max="500000"
                                 required value="<?= e((float) $u['daily_limit']) ?>" class="mono">
                          <button class="btn btn-sm" type="submit">Save</button>
                        </div>
                      </form>
                    </details>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Pages">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === $page): ?><span aria-current="page"><?= $p ?></span>
          <?php else: ?><a href="?<?= e(http_build_query(['q' => $search, 'status' => $status, 'page' => $p])) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
