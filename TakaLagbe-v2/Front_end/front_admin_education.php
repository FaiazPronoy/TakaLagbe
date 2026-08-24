<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Institutes and education fees</h1>
    <button class="btn btn-primary btn-sm" onclick="openModal('addInst')">Add institute</button>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <div class="row" style="margin-bottom:1.2rem">
    <div class="col" style="flex:1 1 320px">
      <div class="card">
        <div class="card-title">Institutes</div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>ID</th><th>Name</th><th class="num">Payments</th>
              <th class="num">Collected</th></tr></thead>
            <tbody>
            <?php foreach ($institutes as $i): ?>
              <tr>
                <td class="mono tiny"><?= e($i['institute_id']) ?></td>
                <td><strong><?= e($i['institute_name']) ?></strong></td>
                <td class="num"><?= (int) $i['n'] ?></td>
                <td class="num"><?= e(money($i['total_received_amount'], false)) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col" style="flex:1 1 260px">
      <div class="card">
        <div class="card-title">Collected by semester</div>
        <?php if (!$bySemester): ?>
          <p class="muted tiny mb0">No fees paid yet.</p>
        <?php else: foreach ($bySemester as $s): ?>
          <div class="entry">
            <span class="body">
              <span class="t"><?= e($s['semester']) ?></span>
              <span class="s"><?= (int) $s['n'] ?> payment(s)</span>
            </span>
            <span class="amt"><?= e(money($s['total'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="between" style="margin-bottom:.6rem">
      <div class="card-title mb0">Fee payments</div>
      <form method="get" style="display:flex;gap:.4rem">
        <input name="q" value="<?= e($search) ?>" placeholder="Student ID, name or reference"
               style="width:220px;padding:.4rem .6rem">
        <button class="btn btn-primary btn-sm" type="submit">Search</button>
      </form>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>When</th><th>Institute</th><th>Student</th><th>Semester</th>
          <th>Paid by</th><th>Reference</th><th class="num">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($fees as $f): ?>
          <tr>
            <td class="tiny"><?= e(short_date($f['timestamp'])) ?></td>
            <td><?= e($f['institute_name']) ?></td>
            <td><?= e($f['student_name']) ?>
              <div class="tiny muted mono"><?= e($f['student_id']) ?></div></td>
            <td class="tiny"><?= e($f['semester']) ?></td>
            <td class="tiny mono"><?= e($f['payer_phone']) ?></td>
            <td class="tiny mono"><?= e($f['trx_id']) ?></td>
            <td class="num"><?= e(money($f['amount'], false)) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$fees): ?>
          <tr><td colspan="7" class="center muted" style="padding:2rem">No fee payments found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal" id="addInst">
  <div class="modal-box">
    <h2>Add an institute</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="field"><label for="institute_id">Institute ID</label>
        <input id="institute_id" name="institute_id" required maxlength="20" placeholder="559"></div>
      <div class="field"><label for="institute_name">Name</label>
        <input id="institute_name" name="institute_name" required maxlength="120"></div>
      <div style="display:flex;gap:.5rem">
        <button class="btn btn-ghost btn-block" type="button" onclick="closeModal('addInst')">Cancel</button>
        <button class="btn btn-primary btn-block" type="submit">Add</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/partials/foot.php'; ?>
