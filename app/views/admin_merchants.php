<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Billers and merchants', 'Who customers can pay, what each has collected, and what cashback it gives.', null,
          '<a class="btn btn-quiet" href="#addOrg">' . icon('plus') . 'Add biller</a>'
        . '<a class="btn" href="#addMerchant">' . icon('plus') . 'Add merchant</a>', 'Back office') ?>
    <?= error_note($error) ?>

    <div class="grid cols-2">
      <section class="card reveal" aria-labelledby="orgTitle">
        <div class="card-h"><h2 id="orgTitle">Utility billers</h2>
          <label class="sr-only" for="orgFilter">Filter billers</label>
          <input id="orgFilter" class="js-only" data-filter="orgTable" placeholder="Filter" style="max-width:150px;padding:.4rem .6rem">
        </div>
        <div class="table">
          <table id="orgTable">
            <thead><tr><th scope="col">Biller</th><th scope="col">Type</th><th scope="col" class="num">Bills</th>
              <th scope="col" class="num">Collected</th></tr></thead>
            <tbody>
            <?php foreach ($organizations as $o): ?>
              <tr data-row><td><strong><?= e($o['organization_name']) ?></strong><br>
                  <span class="mono small muted"><?= e($o['organization_id']) ?></span></td>
                <td class="small"><?= e($billtypes[$o['billtype']] ?? $o['billtype']) ?></td>
                <td class="num"><?= (int) $o['n'] ?></td>
                <td class="num"><?= e(money($o['total_received_amount'], false)) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p id="orgTable-none" class="muted small center" hidden>No biller matches that.</p>
      </section>

      <div class="stack">
        <section class="card reveal" style="--n:1" aria-labelledby="merTitle">
          <div class="card-h"><h2 id="merTitle">Merchants</h2></div>
          <div class="table">
            <table>
              <thead><tr><th scope="col">Merchant</th><th scope="col" class="num">Cashback</th>
                <th scope="col" class="num">Paid</th><th scope="col"><span class="sr-only">Edit</span></th></tr></thead>
              <tbody>
              <?php foreach ($merchants as $m):
                $pct = rtrim(rtrim(number_format((float) $m['cashback_pct'], 2), '0'), '.'); ?>
                <tr>
                  <td><strong><?= e($m['merchant_name']) ?></strong><br>
                    <span class="small muted"><?= e($m['category']) ?> · <?= (int) $m['n'] ?> payments</span></td>
                  <td class="num"><?= $pct ?>%</td>
                  <td class="num"><?= e(money($m['total_received_amount'], false)) ?></td>
                  <td>
                    <details class="inline">
                      <summary class="btn btn-quiet btn-sm">Edit</summary>
                      <form method="post" class="panel" style="min-width:190px">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_cashback">
                        <input type="hidden" name="merchant_id" value="<?= e($m['merchant_id']) ?>">
                        <label for="cb<?= e($m['merchant_id']) ?>">Cashback %</label>
                        <div class="row" style="flex-wrap:nowrap">
                          <input id="cb<?= e($m['merchant_id']) ?>" name="cashback_pct" type="number" step="0.01" min="0" max="20"
                                 required value="<?= e($pct) ?>" class="mono">
                          <button class="btn btn-sm" type="submit">Save</button>
                        </div>
                      </form>
                    </details>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <section class="card reveal" style="--n:2" aria-labelledby="agTitle">
          <div class="card-h"><h2 id="agTitle">Agent points</h2></div>
          <p class="small muted">Cash out is refused once an agent's float would drop below zero.</p>
          <div class="table">
            <table>
              <thead><tr><th scope="col">Agent</th><th scope="col">Phone</th><th scope="col" class="num">Cash float</th></tr></thead>
              <tbody>
              <?php foreach ($agents as $a): ?>
                <tr><td class="mono"><?= e($a['agent_id']) ?></td><td class="mono small"><?= e($a['phone_number']) ?></td>
                  <td class="num"><?= e(money($a['cash_in_amount'], false)) ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </div>
</main>

<dialog id="addOrg" aria-labelledby="addOrgTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="addOrgTitle">Add a utility biller</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_org">
      <div class="field"><label for="organization_id">Biller ID</label>
        <input id="organization_id" name="organization_id" required maxlength="20" pattern="[0-9A-Za-z]{2,20}" placeholder="448"></div>
      <div class="field"><label for="organization_name">Name</label>
        <input id="organization_name" name="organization_name" required maxlength="120"></div>
      <div class="field"><label for="billtype">Bill type</label>
        <select id="billtype" name="billtype" required>
          <?php foreach ($billtypes as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
        </select></div>
      <div class="row"><a class="btn btn-quiet" href="#" data-close>Cancel</a><button class="btn push" type="submit">Add biller</button></div>
    </form>
  </div>
</dialog>

<dialog id="addMerchant" aria-labelledby="addMerTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="addMerTitle">Add a merchant</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_merchant">
      <div class="field"><label for="merchant_id">Merchant ID</label>
        <input id="merchant_id" name="merchant_id" required maxlength="20" pattern="[0-9A-Za-z]{2,20}" placeholder="M109"></div>
      <div class="field"><label for="merchant_name">Name</label>
        <input id="merchant_name" name="merchant_name" required maxlength="120"></div>
      <div class="grid cols-2">
        <div class="field"><label for="category">Category</label>
          <select id="category" name="category">
            <?php foreach (['Shopping', 'Groceries', 'Food', 'Transport', 'Health', 'Books'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label for="cashback_pct">Cashback %</label>
          <input id="cashback_pct" name="cashback_pct" type="number" step="0.01" min="0" max="20" value="0" class="mono"></div>
      </div>
      <div class="row"><a class="btn btn-quiet" href="#" data-close>Cancel</a><button class="btn push" type="submit">Add merchant</button></div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
