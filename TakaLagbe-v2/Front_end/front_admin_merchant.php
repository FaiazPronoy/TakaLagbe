<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Billers and merchants</h1>
    <div style="display:flex;gap:.4rem">
      <button class="btn btn-ghost btn-sm" onclick="openModal('addOrg')">Add biller</button>
      <button class="btn btn-primary btn-sm" onclick="openModal('addMerchant')">Add merchant</button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="row">
    <div class="col" style="flex:1 1 340px">
      <div class="card">
        <div class="between">
          <div class="card-title mb0">Utility billers</div>
          <input data-filter="orgTable" placeholder="Filter" style="width:150px;padding:.35rem .6rem">
        </div>
        <div class="table-wrap">
          <table id="orgTable">
            <thead><tr><th>ID</th><th>Name</th><th>Type</th>
              <th class="num">Bills</th><th class="num">Collected</th></tr></thead>
            <tbody>
            <?php foreach ($organizations as $o): ?>
              <tr data-row>
                <td class="mono tiny"><?= e($o['organization_id']) ?></td>
                <td><strong><?= e($o['organization_name']) ?></strong></td>
                <td class="tiny"><?= e($billtypes[$o['billtype']] ?? $o['billtype']) ?></td>
                <td class="num"><?= (int) $o['n'] ?></td>
                <td class="num"><?= e(money($o['total_received_amount'], false)) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p id="orgTable-none" class="muted tiny center" style="display:none">Nothing matches.</p>
      </div>
    </div>

    <div class="col" style="flex:1 1 340px">
      <div class="card">
        <div class="card-title">Merchants</div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Merchant</th><th>Category</th><th class="num">Cashback</th>
              <th class="num">Payments</th><th class="num">Collected</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($merchants as $m): ?>
              <tr>
                <td><strong><?= e($m['merchant_name']) ?></strong>
                  <div class="tiny muted mono"><?= e($m['merchant_id']) ?></div></td>
                <td class="tiny"><?= e($m['category']) ?></td>
                <td class="num"><?= rtrim(rtrim(number_format((float) $m['cashback_pct'], 2), '0'), '.') ?>%</td>
                <td class="num"><?= (int) $m['n'] ?></td>
                <td class="num"><?= e(money($m['total_received_amount'], false)) ?></td>
                <td>
                  <button class="btn btn-ghost btn-sm" type="button"
                          onclick="editCb(<?= json_encode($m['merchant_id'], JSON_HEX_QUOT | JSON_HEX_APOS) ?>,
                                          <?= json_encode($m['merchant_name'], JSON_HEX_QUOT | JSON_HEX_APOS) ?>,
                                          <?= (float) $m['cashback_pct'] ?>)">Edit</button>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card" style="margin-top:1rem">
        <div class="card-title">Agent points</div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Agent</th><th>Contact</th><th class="num">Cash float</th></tr></thead>
            <tbody>
            <?php foreach ($agents as $a): ?>
              <tr>
                <td class="mono"><?= e($a['agent_id']) ?></td>
                <td class="mono tiny"><?= e($a['phone_number']) ?></td>
                <td class="num"><?= e(money($a['cash_in_amount'], false)) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal" id="addOrg">
  <div class="modal-box">
    <h2>Add a utility biller</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_org">
      <div class="field"><label for="organization_id">Biller ID</label>
        <input id="organization_id" name="organization_id" required maxlength="20" placeholder="448"></div>
      <div class="field"><label for="organization_name">Name</label>
        <input id="organization_name" name="organization_name" required maxlength="120"></div>
      <div class="field"><label for="billtype">Bill type</label>
        <select id="billtype" name="billtype" required>
          <?php foreach ($billtypes as $k => $v): ?>
            <option value="<?= $k ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select></div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('addOrg')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Add</button>
      </div>
    </form>
  </div>
</div>

<div class="modal" id="addMerchant">
  <div class="modal-box">
    <h2>Add a merchant</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_merchant">
      <div class="field"><label for="merchant_id">Merchant ID</label>
        <input id="merchant_id" name="merchant_id" required maxlength="20" placeholder="M109"></div>
      <div class="field"><label for="merchant_name">Name</label>
        <input id="merchant_name" name="merchant_name" required maxlength="120"></div>
      <div class="field"><label for="category">Category</label>
        <select id="category" name="category">
          <?php foreach (['Shopping','Groceries','Food','Transport','Health','Books'] as $c): ?>
            <option><?= $c ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label for="cashback_pct">Cashback percent</label>
        <input id="cashback_pct" name="cashback_pct" type="number" step="0.01" min="0" max="20" value="0"></div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('addMerchant')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Add</button>
      </div>
    </form>
  </div>
</div>

<div class="modal" id="cbModal">
  <div class="modal-box">
    <h2>Change cashback</h2>
    <p class="muted tiny" id="cbWho"></p>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="set_cashback">
      <input type="hidden" name="merchant_id" id="cbId">
      <div class="field"><label for="cbPct">Cashback percent</label>
        <input id="cbPct" name="cashback_pct" type="number" step="0.01" min="0" max="20" required></div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('cbModal')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function editCb(id, name, pct) {
  document.getElementById('cbId').value = id;
  document.getElementById('cbPct').value = pct;
  document.getElementById('cbWho').textContent = name;
  openModal('cbModal');
}
</script>
<?php include __DIR__ . '/partials/foot.php'; ?>
