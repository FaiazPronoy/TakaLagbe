<?php include __DIR__ . '/partials/admin_nav.php'; ?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('Institutes', 'Registered institutes and every tuition fee paid through the wallet.', null,
                  '<a class="btn" href="#addInst">' . icon('plus') . 'Add institute</a>', 'Back office') ?>
    <?= error_note($error) ?>

    <div class="split mb-5">
      <section class="card reveal" aria-labelledby="instTitle">
        <div class="card-h"><h2 id="instTitle">Institutes</h2></div>
        <div class="table">
          <table>
            <thead><tr><th scope="col">Institute</th><th scope="col" class="num">Fees</th><th scope="col" class="num">Collected</th></tr></thead>
            <tbody>
            <?php foreach ($institutes as $i): ?>
              <tr><td><strong><?= e($i['institute_name']) ?></strong> <span class="mono small muted"><?= e($i['institute_id']) ?></span></td>
                <td class="num"><?= (int) $i['n'] ?></td>
                <td class="num"><?= e(money($i['total_received_amount'], false)) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="semTitle">
        <div class="card-h"><h2 id="semTitle">By semester</h2></div>
        <?php if (!$bySemester): ?>
          <p class="muted small">No fees paid yet.</p>
        <?php else: ?>
          <ul class="rows">
            <?php foreach ($bySemester as $s): ?>
              <li class="entry"><span class="body"><span class="t"><?= e($s['semester']) ?></span>
                <span class="s"><?= (int) $s['n'] ?> payment<?= $s['n'] == 1 ? '' : 's' ?></span></span>
                <span class="amt"><?= e(money($s['total'])) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>

    <section class="card reveal" aria-labelledby="feeTitle">
      <div class="card-h"><h2 id="feeTitle">Fee payments</h2>
        <form method="get" class="row" role="search" aria-label="Search fees">
          <label class="sr-only" for="q">Search fees</label>
          <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Student, reference, payer" style="max-width:220px;padding:.45rem .7rem">
          <button class="btn btn-sm" type="submit">Search</button>
        </form>
      </div>
      <?php if (!$fees): ?>
        <?= empty_state('cap', $search !== '' ? 'No fee matches that' : 'No fees yet',
              $search !== '' ? 'Try a student ID, a name or a reference.' : 'Tuition paid by customers will be listed here.') ?>
      <?php else: ?>
        <div class="table">
          <table>
            <thead><tr><th scope="col">When</th><th scope="col">Student</th><th scope="col">Institute</th>
              <th scope="col">Paid by</th><th scope="col" class="num">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($fees as $f): ?>
              <tr><td class="small nowrap"><?= e(short_date($f['timestamp'])) ?></td>
                <td><?= e($f['student_name']) ?><br><span class="mono small muted"><?= e($f['student_id']) ?> · <?= e($f['semester']) ?></span></td>
                <td class="small"><?= e($f['institute_name']) ?></td>
                <td class="small"><span class="mono"><?= e($f['payer_phone']) ?></span><br><span class="mono muted"><?= e($f['trx_id']) ?></span></td>
                <td class="num"><?= e(money($f['amount'], false)) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<dialog id="addInst" aria-labelledby="addInstTitle">
  <div class="sheet">
    <a class="tool x" href="#" data-close aria-label="Close"><?= icon('x') ?></a>
    <h2 id="addInstTitle">Add an institute</h2>
    <form method="post" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="field"><label for="institute_id">Institute ID</label>
        <input id="institute_id" name="institute_id" required maxlength="20" pattern="[0-9A-Za-z]{2,20}" placeholder="559"></div>
      <div class="field"><label for="institute_name">Name</label>
        <input id="institute_name" name="institute_name" required maxlength="120"></div>
      <div class="row"><a class="btn btn-quiet" href="#" data-close>Cancel</a><button class="btn push" type="submit">Add institute</button></div>
    </form>
  </div>
</dialog>
<?php include __DIR__ . '/partials/foot.php'; ?>
