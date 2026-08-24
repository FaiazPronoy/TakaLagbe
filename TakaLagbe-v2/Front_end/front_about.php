<?php
include __DIR__ . '/partials/head.php';
include __DIR__ . '/partials/nav.php';

$features = [
    ["\u{1F4B8}", 'Send money',      'Instant transfers between users, free of charge.'],
    ["\u{1F3E6}", 'Add money',       'Top up from a linked bank account or card.'],
    ["\u{1F3E7}", 'Cash in and out', 'Agent points, with the agent float kept in step.'],
    ["\u{1F4F1}", 'Mobile recharge', 'Operator detected from the number, with cashback.'],
    ["\u{1F4A1}", 'Bill payments',   'Electricity, gas, water and internet in one place.'],
    ["\u{1F393}", 'Education fees',  'Semester payments to registered institutes.'],
    ["\u{1F6CD}", 'Merchant payment','Pay shops and restaurants, earn their cashback.'],
    ["\u{1F3AF}", 'Savings goals',   'Put money aside so it cannot be spent by accident.'],
    ["\u{1F91D}", 'Request and split','Ask one person, or split a bill many ways.'],
    ["\u{1F4F7}", 'QR codes',        'Drawn by the app itself, no internet needed.'],
    ["\u{1F4CA}", 'Insights',        'Category breakdown, six month trend, habits.'],
    ["\u{1F4C4}", 'Statements',      'Printable statement and a CSV export.'],
];

$security = [
    ['Passwords and PINs are hashed', 'bcrypt through password_hash, so the database never
      holds anything readable, and a stolen dump gives an attacker nothing.'],
    ['Every query is prepared', 'The application uses PDO with emulation switched off, so
      user input is never glued into SQL and injection has nowhere to land.'],
    ['Transfers are atomic', 'The debit, the credit and the ledger row happen inside one
      database transaction. Nothing can leave one account without arriving at the other.'],
    ['Accounts are locked while money moves', 'SELECT ... FOR UPDATE, always in the same
      order, so two transfers at once cannot corrupt a balance or deadlock each other.'],
    ['Forms are CSRF protected', 'Every state changing form carries a one time token that
      is checked with hash_equals before anything is written.'],
    ['Brute force is throttled', 'Five wrong sign ins lock the number for fifteen minutes.
      Five wrong PINs freeze the account outright.'],
    ['Limits and velocity checks', 'A daily limit and a short window transaction counter,
      both evaluated inside the lock so they cannot be raced.'],
    ['Everything is written down', 'An audit log records who did what, from which address,
      and the ledger keeps failed attempts as well as successful ones.'],
];
?>
<main class="wrap section">

  <div class="between" style="margin-bottom:1rem">
    <h1 class="mb0">About <?= e(APP_NAME) ?></h1>
    <a class="btn btn-ghost btn-sm" href="account.php">Back</a>
  </div>

  <div class="card balance" style="margin-bottom:1.2rem">
    <h2 style="color:#fff;margin-bottom:.3rem"><?= e(APP_NAME) ?> version <?= e(APP_VERSION) ?></h2>
    <p style="opacity:.92;margin-bottom:1rem"><?= e(APP_TAGLINE) ?></p>
    <div class="row">
      <div class="col"><div class="label">Users</div>
        <div style="font-size:1.5rem;font-weight:800"><?= number_format($stats['users']) ?></div></div>
      <div class="col"><div class="label">Transactions</div>
        <div style="font-size:1.5rem;font-weight:800"><?= number_format($stats['transactions']) ?></div></div>
      <div class="col"><div class="label">Volume moved</div>
        <div style="font-size:1.5rem;font-weight:800"><?= e(money($stats['volume'], false)) ?></div></div>
      <div class="col"><div class="label">Billers</div>
        <div style="font-size:1.5rem;font-weight:800"><?= number_format($stats['billers']) ?></div></div>
    </div>
  </div>

  <h2>What it does</h2>
  <div class="grid grid-3" style="margin-bottom:1.6rem">
    <?php foreach ($features as [$icon, $name, $desc]): ?>
      <div class="card card-pad-sm">
        <div style="font-size:1.6rem"><?= $icon ?></div>
        <div style="font-weight:800;margin-top:.3rem"><?= e($name) ?></div>
        <div class="tiny muted"><?= e($desc) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <h2>How the money is kept safe</h2>
  <div class="card" style="margin-bottom:1.6rem">
    <?php foreach ($security as $i => [$title, $body]): ?>
      <div class="entry" style="align-items:flex-start">
        <span class="ico" style="background:var(--brand-soft);font-size:.9rem;font-weight:800">
          <?= $i + 1 ?></span>
        <span class="body">
          <span class="t"><?= e($title) ?></span>
          <span class="s" style="white-space:normal"><?= e(preg_replace('/\s+/', ' ', $body)) ?></span>
        </span>
      </div>
    <?php endforeach; ?>
  </div>

  <h2>How it is built</h2>
  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-title">Stack</div>
        <div class="table-wrap">
          <table>
            <tbody>
              <tr><td>Language</td><td><strong>PHP 8, no framework</strong></td></tr>
              <tr><td>Database</td><td><strong>MySQL or MariaDB, InnoDB</strong></td></tr>
              <tr><td>Database access</td><td><strong>PDO, prepared statements only</strong></td></tr>
              <tr><td>Front end</td><td><strong>Hand written CSS and plain JavaScript</strong></td></tr>
              <tr><td>External libraries</td><td><strong>None</strong></td></tr>
              <tr><td>QR codes</td><td><strong>Encoder written for this project</strong></td></tr>
            </tbody>
          </table>
        </div>
        <p class="tiny muted mb0" style="margin-top:.6rem">
          Nothing is loaded from a CDN, so the whole application runs from an
          offline XAMPP folder exactly as it does online.
        </p>
      </div>
    </div>
    <div class="col">
      <div class="card">
        <div class="card-title">Database design</div>
        <div class="table-wrap">
          <table>
            <tbody>
              <tr><td>user, account</td><td>Identity and the wallet, one to one</td></tr>
              <tr><td>transactions</td><td>The single ledger every movement lands in</td></tr>
              <tr><td>bill_payments</td><td>One table for all four utility types</td></tr>
              <tr><td>savings_goals</td><td>Money set aside from the spendable balance</td></tr>
              <tr><td>money_requests</td><td>Requests and split bill groups</td></tr>
              <tr><td>favourites, notifications</td><td>Everyday convenience</td></tr>
              <tr><td>login_attempts, audit_log</td><td>The security trail</td></tr>
            </tbody>
          </table>
        </div>
        <p class="tiny muted mb0" style="margin-top:.6rem">
          Money columns are DECIMAL, never INT or FLOAT, so a fraction of a taka
          is never lost to rounding.
        </p>
      </div>
    </div>
  </div>

  <div class="card center" style="margin-top:1.2rem">
    <p class="mb0 tiny muted">
      Built as the CSE370 Database Systems project at BRAC University.<br>
      The interface, the schema and the money engine were all written for this project.
    </p>
  </div>

</main>
<?php include __DIR__ . '/partials/foot.php'; ?>
