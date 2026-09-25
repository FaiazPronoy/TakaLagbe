<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$features = [
    ['send',     'Send money',       'Free between users, locked and settled in one transaction.'],
    ['wallet',   'Add money',        'From a linked bank account or card.'],
    ['cash-out', 'Cash in and out',  'At agent points, with the agent\'s cash float kept in step.'],
    ['phone',    'Mobile recharge',  'Operator read from the number, with cashback.'],
    ['receipt',  'Bills',            'Electricity, gas, water and internet, once per month per bill.'],
    ['cap',      'Tuition',          'Semester fees to registered institutes.'],
    ['bag',      'Shops',            'Each merchant sets its own cashback.'],
    ['target',   'Savings goals',    'Money set aside so it cannot be spent by accident.'],
    ['split',    'Requests, splits', 'Ask one person, or split a bill many ways.'],
    ['qr',       'QR codes',         'Encoded by the app itself, byte by byte.'],
    ['chart',    'Insights',         'Categories, six month trend, habits.'],
    ['file',     'Statements',       'Printable, or a CSV that Excel reads correctly.'],
];
$security = [
    ['Passwords and PINs are hashed', 'bcrypt through password_hash. The database never holds either in a readable form.'],
    ['Every query is prepared', 'PDO with emulation off, so input is never glued into SQL.'],
    ['Transfers are atomic', 'The debit, the credit and the ledger row commit together or not at all.'],
    ['Accounts are locked while money moves', 'SELECT … FOR UPDATE, always in the same order, so two transfers cannot race or deadlock.'],
    ['Forms cannot be forged or replayed', 'A CSRF token on every form, and a one time token on anything that moves money.'],
    ['Guessing is throttled', 'Five wrong passwords lock a number for fifteen minutes. Five wrong PINs freeze the account.'],
    ['Everything is written down', 'An audit log of who did what, and a ledger that keeps declined attempts too.'],
];
?>
<main id="main" class="page">
  <div class="wrap">
    <?= page_head('About ' . APP_NAME, APP_TAGLINE . ' A mobile wallet built for CSE370, Database Systems, at BRAC University.',
                  ['account.php', 'Home'], '', 'Version ' . APP_VERSION) ?>

    <section class="hero rise mb-6" style="--n:1" aria-label="In numbers">
      <?= guilloche() ?>
      <div class="grid cols-4">
        <?php foreach ([['Users', number_format($stats['users'])], ['Transactions', number_format($stats['transactions'])],
                        ['Moved', '৳' . money($stats['volume'], false)], ['Billers', number_format($stats['billers'])]] as [$k, $v]): ?>
          <div><span class="label"><?= $k ?></span><p class="figure m0 mt-1" style="font-size:var(--step-2)"><?= e($v) ?></p></div>
        <?php endforeach; ?>
      </div>
    </section>

    <h2 class="reveal">What it does</h2>
    <div class="grid cols-3 mb-6">
      <?php foreach ($features as $i => [$ico, $name, $desc]): ?>
        <div class="card tight lift reveal" style="--n:<?= $i % 3 ?>">
          <div class="row" style="flex-wrap:nowrap;align-items:flex-start">
            <span class="ico" style="color:var(--brand)"><?= icon($ico) ?></span>
            <div><strong><?= e($name) ?></strong><p class="small muted m0"><?= e($desc) ?></p></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="split">
      <section class="card reveal" aria-labelledby="safeTitle">
        <div class="card-h"><h2 id="safeTitle">How the money is kept straight</h2></div>
        <ol class="rows" style="list-style:none">
          <?php foreach ($security as $i => [$t, $b]): ?>
            <li class="entry" style="align-items:flex-start">
              <span class="ico mono" style="font-size:.85rem"><?= $i + 1 ?></span>
              <span class="body"><span class="t" style="white-space:normal"><?= e($t) ?></span>
                <span class="s wrap-ok"><?= e($b) ?></span></span>
            </li>
          <?php endforeach; ?>
        </ol>
      </section>

      <section class="card reveal" style="--n:1" aria-labelledby="stackTitle">
        <div class="card-h"><h2 id="stackTitle">How it is built</h2></div>
        <dl class="grid m0" style="grid-template-columns:auto 1fr;gap:var(--s2) var(--s4)">
          <dt class="muted">Language</dt><dd class="m0">PHP 8, no framework</dd>
          <dt class="muted">Database</dt><dd class="m0">MySQL / MariaDB, InnoDB, 18 tables</dd>
          <dt class="muted">Front end</dt><dd class="m0">Hand written CSS and plain JavaScript</dd>
          <dt class="muted">Libraries</dt><dd class="m0">None</dd>
          <dt class="muted">QR codes</dt><dd class="m0">An encoder written for this project</dd>
        </dl>
        <p class="small muted m0 mt-4">Money columns are DECIMAL, never FLOAT, so a fraction of a
          taka is never lost to rounding. Nothing here moves real money.</p>
      </section>
    </div>
  </div>
</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
