<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">&#127891; Education fee</h1>
    <a class="btn btn-ghost btn-sm" href="paybill.php">All bills</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" class="card" data-once>
    <?= csrf_field() ?>

    <div class="field">
      <label for="institute_id">Institute</label>
      <select id="institute_id" name="institute_id" required>
        <option value="">Choose an institute</option>
        <?php foreach ($institutes as $i): ?>
          <option value="<?= e($i['institute_id']) ?>"><?= e($i['institute_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row">
      <div class="col field">
        <label for="student_id">Student ID</label>
        <input id="student_id" name="student_id" required maxlength="30" placeholder="21301234">
      </div>
      <div class="col field">
        <label for="student_name">Student name</label>
        <input id="student_name" name="student_name" required maxlength="120" placeholder="Full name">
      </div>
    </div>

    <div class="field">
      <label for="semester">Semester</label>
      <select id="semester" name="semester" required>
        <?php foreach ($semesters as $s): ?>
          <option value="<?= e($s) ?>"><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="hint">One student ID can pay a given semester only once.</div>
    </div>

    <div class="field">
      <label for="amount">Amount</label>
      <div class="money-input">
        <span><?= CURRENCY_SIGN ?></span>
        <input id="amount" name="amount" type="number" step="0.01" required
               min="<?= MIN_AMOUNT ?>" placeholder="0.00"
               data-charge="chargeBox" data-balance="<?= $bal ?>">
      </div>
      <div class="hint">No charge on education payments. Balance <?= e(money($bal)) ?>.</div>
    </div>

    <div id="chargeBox" class="card card-pad-sm" style="background:var(--surface-2);margin-bottom:1rem"></div>

    <div class="field">
      <label>Transaction PIN</label>
      <div class="pin" data-pin="pinValue">
        <?php for ($i = 0; $i < 6; $i++): ?>
          <input type="password" inputmode="numeric" maxlength="1" aria-label="PIN digit <?= $i + 1 ?>">
        <?php endfor; ?>
      </div>
      <input type="hidden" name="pin" id="pinValue" required pattern="\d{6}">
    </div>

    <button class="btn btn-primary btn-block" type="submit">Pay fee</button>
  </form>

  <?php if ($recent): ?>
    <div class="card" style="margin-top:1rem">
      <div class="card-title">Recent education payments</div>
      <?php foreach ($recent as $r): ?>
        <a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>" style="color:inherit">
          <span class="ico">&#127891;</span>
          <span class="body">
            <span class="t"><?= e($r['institute_name']) ?></span>
            <span class="s"><?= e($r['student_name']) ?> &middot; <?= e($r['student_id']) ?>
              &middot; <?= e($r['semester']) ?></span>
          </span>
          <span class="amt"><?= e(money($r['amount'])) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
