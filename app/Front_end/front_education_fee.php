<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head(__('Tuition fee'), __('Semester fees, no charge, no queue at the accounts office.'),
                  ['paybill.php', __('All bills')], badge: ['cap', 4]) ?>
    <?= error_note($error) ?>

    <form method="post" class="card rise" style="--n:1" data-once>
      <?= csrf_field() ?>
      <?= once_field('education_fee') ?>

      <div class="field">
        <label for="institute_id"><?= __('Institute') ?></label>
        <select id="institute_id" name="institute_id" required>
          <option value=""><?= __('Choose an institute') ?></option>
          <?php foreach ($institutes as $i): ?>
            <option value="<?= e($i['institute_id']) ?>"
              <?= post('institute_id') === $i['institute_id'] ? 'selected' : '' ?>><?= e($i['institute_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid cols-2">
        <div class="field">
          <label for="student_id"><?= __('Student ID') ?></label>
          <input id="student_id" name="student_id" required maxlength="30" autocomplete="off"
                 value="<?= e(post('student_id')) ?>">
        </div>
        <div class="field">
          <label for="student_name"><?= __('Student name') ?></label>
          <input id="student_name" name="student_name" required maxlength="120" value="<?= e(post('student_name')) ?>">
        </div>
      </div>

      <div class="field">
        <label for="semester"><?= __('Semester') ?></label>
        <select id="semester" name="semester" required>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= e($s) ?>" <?= post('semester') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount"><?= __('Amount') ?></label>
        <div class="money">
          <span aria-hidden="true">৳</span>
          <input id="amount" name="amount" type="number" step="0.01" inputmode="decimal" required
                 min="<?= MIN_AMOUNT ?>" value="<?= e(post('amount')) ?>" data-charge="feeSum" data-balance="<?= $bal ?>">
        </div>
        <span class="hint"><?= e(__('You have {bal}.', ['bal' => money($bal)])) ?></span>
      </div>

      <div class="sum" id="feeSum" aria-live="polite"></div>
      <?= pin_field() ?>
      <button class="btn btn-block" type="submit"><?= icon('cap') ?><?= __('Pay the fee') ?></button>
    </form>

    <?php if ($recent): ?>
      <section class="card reveal mt-5">
        <div class="card-h"><h2><?= icon('history') ?><?= __('Fees you\'ve paid') ?></h2></div>
        <ul class="rows">
          <?php foreach ($recent as $r): ?>
            <li><a class="entry" href="receipt.php?id=<?= e($r['trx_id']) ?>">
              <span class="ico"><?= icon('cap') ?></span>
              <span class="body"><span class="t"><?= e($r['institute_name']) ?></span>
                <span class="s"><?= e($r['student_name']) ?> · <?= e($r['student_id']) ?> · <?= e($r['semester']) ?></span></span>
              <span class="amt"><?= e(money($r['amount'])) ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
