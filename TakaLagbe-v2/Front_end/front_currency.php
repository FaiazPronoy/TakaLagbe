<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$options = ['BDT' => ['Bangladeshi Taka', 1.0, "\u{1F1E7}\u{1F1E9}"]] + RATES;
?>
<main class="wrap-sm section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">Currency converter</h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <form class="card" method="get">
    <div class="field">
      <label for="amount">Amount</label>
      <input id="amount" name="amount" type="number" step="0.01" min="0"
             value="<?= e($amount) ?>" required>
    </div>

    <div class="row">
      <div class="col field">
        <label for="from">From</label>
        <select id="from" name="from">
          <?php foreach ($options as $code => $o): ?>
            <option value="<?= $code ?>" <?= $from === $code ? 'selected' : '' ?>>
              <?= $o[2] ?> <?= $code ?> &middot; <?= e($o[0]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col field">
        <label for="to">To</label>
        <select id="to" name="to">
          <?php foreach ($options as $code => $o): ?>
            <option value="<?= $code ?>" <?= $to === $code ? 'selected' : '' ?>>
              <?= $o[2] ?> <?= $code ?> &middot; <?= e($o[0]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="display:flex;gap:.5rem">
      <button class="btn btn-primary btn-block" type="submit">Convert</button>
      <a class="btn btn-ghost" href="?amount=<?= e($amount) ?>&amp;from=<?= e($to) ?>&amp;to=<?= e($from) ?>"
         title="Swap">&#8646;</a>
    </div>
  </form>

  <div class="card center" style="margin-top:1rem">
    <div class="muted tiny">
      <?= e(number_format($amount, 2)) ?> <?= e($from) ?> equals
    </div>
    <div style="font-size:2rem;font-weight:800;letter-spacing:-.02em;color:var(--brand-dark)">
      <?= e(number_format($result, 2)) ?> <?= e($to) ?>
    </div>
    <div class="tiny muted" style="margin-top:.4rem">
      1 <?= e($from) ?> = <?= e(number_format($single, 4)) ?> <?= e($to) ?>
    </div>
  </div>

  <div class="card" style="margin-top:1rem">
    <div class="card-title">Rate table, one unit in taka</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Currency</th><th class="num">In BDT</th></tr></thead>
        <tbody>
        <?php foreach (RATES as $code => $r): ?>
          <tr>
            <td><?= $r[2] ?> <strong><?= $code ?></strong>
              <span class="muted tiny">&middot; <?= e($r[0]) ?></span></td>
            <td class="num"><?= e(number_format($r[1], 2)) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="tiny muted mb0" style="margin-top:.6rem">
      These are indicative rates kept inside the application, so the page works
      without an internet connection. Update them in currency.php when they move.
    </p>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
