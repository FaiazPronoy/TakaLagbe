<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$options = ['BDT' => ['Bangladeshi Taka', 1.0]] + RATES;
$chosen  = ['from' => $from, 'to' => $to];
?>
<main id="main" class="page">
  <div class="wrap narrow">
    <?= page_head('Currency converter', 'Rough rates for a quick idea. Planning that Kolkata or KL trip?',
                  ['account.php', 'Home'], badge: ['globe', 2]) ?>

    <form class="card rise" style="--n:1" method="get">
      <div class="field">
        <label for="amount">Amount</label>
        <input id="amount" name="amount" type="number" step="0.01" min="0" inputmode="decimal" value="<?= e($amount) ?>" required class="mono">
      </div>
      <div class="grid cols-2">
        <?php foreach (['from' => 'From', 'to' => 'To'] as $k => $l): ?>
          <div class="field">
            <label for="<?= $k ?>"><?= $l ?></label>
            <select id="<?= $k ?>" name="<?= $k ?>">
              <?php foreach ($options as $code => $o): ?>
                <option value="<?= $code ?>" <?= $chosen[$k] === $code ? 'selected' : '' ?>><?= $code ?> · <?= e($o[0]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="row">
        <button class="btn" type="submit">Convert</button>
        <a class="btn btn-quiet" href="?amount=<?= e($amount) ?>&amp;from=<?= e($to) ?>&amp;to=<?= e($from) ?>"><?= icon('exchange') ?>Swap</a>
      </div>
    </form>

    <section class="card center reveal mt-4" aria-live="polite">
      <span class="label"><?= e(number_format($amount, 2)) ?> <?= e($from) ?> is about</span>
      <p class="big-amt" style="margin:var(--s2) 0"><?= e(number_format($result, 2)) ?> <?= e($to) ?></p>
      <span class="small muted mono">1 <?= e($from) ?> = <?= e(number_format($single, 4)) ?> <?= e($to) ?></span>
    </section>

    <section class="card reveal mt-4">
      <div class="card-h"><h2>One unit in taka</h2></div>
      <div class="table">
        <table>
          <thead><tr><th scope="col">Currency</th><th scope="col" class="num">BDT</th></tr></thead>
          <tbody>
          <?php foreach (RATES as $code => $r): ?>
            <tr><td><strong><?= $code ?></strong> <span class="muted small"><?= e($r[0]) ?></span></td>
              <td class="num"><?= e(number_format($r[1], 2)) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="small muted m0 mt-3">Rates are a guide only and can differ from what your bank charges.</p>
    </section>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
