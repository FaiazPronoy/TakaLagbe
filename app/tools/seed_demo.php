<?php
/*
 * Fills the database with six months of believable activity so the demo has
 * something to show. Wipes every customer table first.
 *
 *   php tools/seed_demo.php --yes
 *
 * Every balance is worked out by replaying the events in time order, the same
 * way move_money() would have, so the ledger and the account table agree.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!in_array('--yes', $argv, true)) {
    exit("This deletes every user and transaction in '" . (getenv('TKL_DB_NAME') ?: 'takalagbe')
       . "'. Run it again with --yes if that is what you want.\n");
}

require __DIR__ . '/../lib/bills.php';

mt_srand(370);
$today = strtotime('today');

const DEMO_PASSWORD = 'demo1234';
const DEMO_PIN      = '482915';

/* phone => first, last, gender, dob, bank, monthly top up, city */
$people = [
    '01711482093' => ['Nusrat',   'Jahan',     'Female', '1998-03-14', 'BRAC Bank',         [14000, 18000]],
    '01819337406' => ['Tanvir',   'Ahmed',     'Male',   '1996-11-02', 'Dutch Bangla Bank', [11000, 15000]],
    '01552104478' => ['Farhana',  'Akter',     'Female', '2000-07-21', 'City Bank',         [6000, 8500]],
    '01913765240' => ['Rakibul',  'Hasan',     'Male',   '1994-01-09', 'Islami Bank',       [12000, 16000]],
    '01626908315' => ['Sadia',    'Islam',     'Female', '2001-05-30', null,                [5000, 7000]],
    '01748221967' => ['Mehedi',   'Hasan',     'Male',   '1999-09-17', 'Eastern Bank',      [7000, 10000]],
    '01307554812' => ['Tahmina',  'Sultana',   'Female', '1992-12-05', 'Prime Bank',        [9000, 12000]],
    '01855670129' => ['Arif',     'Chowdhury', 'Male',   '1990-04-11', 'Visa Card',         [15000, 19000]],
    '01474319086' => ['Shahriar', 'Kabir',     'Male',   '2002-02-26', null,                [4000, 6000]],
    '01984402751' => ['Maliha',   'Rahman',    'Female', '1997-08-08', 'Mastercard',        [7000, 9500]],
    '01721093358' => ['Imran',    'Hossain',   'Male',   '1995-06-19', 'BRAC Bank',         [6500, 9000]],
    '01670248831' => ['Sumaiya',  'Khatun',    'Female', '2003-10-03', null,                [3500, 5000]],
];
$main  = '01711482093';
$other = '01819337406';
$palette = ['#1E3A63', '#A87A2B', '#A9412C', '#5A4A86', '#C09A4E', '#2C6E4E'];

/* who sends whom money, and what for */
$pairs = [
    [$main,          '01552104478', [300, 900],   ['Rickshaw and lunch', 'Your share of the Uber', 'Chotpoti money', 'Book photocopy']],
    [$main,          $other,        [500, 2500],  ['House rent share, Mirpur 10', 'Internet bill share', 'Groceries split']],
    [$other,         $main,         [400, 1800],  ['Paying you back for Star Kabab', 'Electricity share', 'Movie tickets, Star Cineplex']],
    ['01913765240',  $main,         [1000, 5000], ['Tuition for Raisa, class 8 maths', 'Tuition, this month']],
    [$main,          '01626908315', [500, 1500],  ["Ammu'r medicine", 'For the Eid shopping', 'Bus fare home']],
    ['01748221967',  $other,        [200, 1200],  ['Cricket kit share', 'Biryani on Friday', 'Pathao ride back']],
    ['01307554812',  '01474319086', [1000, 3000], ['Hostel fee', 'Pocket money', 'For books']],
    ['01855670129',  '01670248831', [800, 2500],  ['Pocket money', 'Coaching fee', 'Tiffin money for the week']],
    ['01984402751',  $main,         [300, 1200],  ['Birthday cake share', 'Concert ticket', 'Borrowed for the Sylhet trip']],
    [$other,         '01913765240', [1500, 4000], ['Laptop repair', 'Borrowed money, thanks bhai']],
    ['01552104478',  '01984402751', [150, 600],   ['Fuchka and coffee', 'Class notes printing']],
    ['01721093358',  '01748221967', [500, 2000],  ['Futsal booking', 'Office lunch']],
];

/* merchant id => amount range */
$shops = [
    'M101' => [1400, 6800], 'M102' => [650, 4200], 'M103' => [1100, 2600], 'M104' => [480, 1850],
    'M105' => [350, 5500],  'M106' => [180, 650],  'M107' => [220, 1400],  'M108' => [380, 1200],
];

/* recurring bills: payer => [billtype, organization id, consumer no, amount range] */
$bills = [
    $main          => [['electricity_bills', '445', 'DESCO-40218867', [1100, 2600]],
                       ['gas_bills',         '111', 'TGL-7730152',    [1080, 1080]],
                       ['internet_bills',    '333', 'BNET-22091',     [1050, 1050]]],
    $other         => [['electricity_bills', '444', 'DPDC-11873542', [900, 2100]],
                       ['water_bills',       '222', 'WASA-5601389',  [450, 900]]],
    '01913765240'  => [['electricity_bills', '446', 'PBS-3309817',   [700, 1800]],
                       ['internet_bills',    '334', 'XP-008812',     [1200, 1200]]],
    '01307554812'  => [['gas_bills',         '114', 'BGL-4471206',   [975, 975]],
                       ['water_bills',       '223', 'CWASA-218804',  [380, 720]]],
    '01855670129'  => [['electricity_bills', '445', 'DESCO-40771190', [2400, 4800]],
                       ['internet_bills',    '335', 'SAM-771045',     [1500, 1500]]],
    '01984402751'  => [['electricity_bills', '447', 'BPDB-6620419',   [650, 1400]]],
];

$merchantRows = [];
foreach (all('SELECT merchant_id, merchant_name, category, cashback_pct FROM merchants') as $m) {
    $merchantRows[$m['merchant_id']] = $m;
}
$orgNames = array_column(all('SELECT organization_id, organization_name FROM organizations'),
                         'organization_name', 'organization_id');

$rand  = static fn(array $r): float => $r[0] === $r[1] ? (float) $r[0]
    : round(mt_rand($r[0], $r[1]) / 10) * 10;
$when  = static fn(int $daysAgo, int $h = -1, int $m = -1): int =>
    $today - $daysAgo * 86400 + ($h < 0 ? mt_rand(8, 22) : $h) * 3600 + ($m < 0 ? mt_rand(0, 59) : $m) * 60;
$pick  = static fn(array $a) => $a[array_rand($a)];

$events = [];
$add = static function (array $e) use (&$events): void { $events[] = $e; };

/* ---- income ------------------------------------------------------------ */
foreach ($people as $phone => [$first, , , , $bank, $range]) {
    for ($mo = 6; $mo >= 0; $mo--) {
        $day = $mo * 30 + mt_rand(-2, 2);
        if ($day < 0) continue;
        $t = $when(max(0, $day), mt_rand(9, 13));
        if ($bank) {
            $add(['t' => $t, 'from' => null, 'from_ref' => $bank, 'to' => $phone, 'amount' => $rand($range),
                  'type' => 'Add Money', 'category' => 'Cash', 'note' => 'Top up from ' . $bank]);
        } else {
            $agent = $pick(['AG1001', 'AG1002', 'AG1003']);
            $add(['t' => $t, 'from' => null, 'from_ref' => $agent, 'to' => $phone, 'amount' => $rand($range),
                  'type' => 'Cash In', 'category' => 'Cash', 'note' => 'Cash in at agent ' . $agent,
                  'agent' => [$agent, +1]]);
        }
    }
}

/* ---- people paying people --------------------------------------------- */
foreach ($pairs as [$from, $to, $range, $notes]) {
    $n = in_array($main, [$from, $to], true) ? mt_rand(7, 11) : mt_rand(4, 7);
    for ($i = 0; $i < $n; $i++) {
        $add(['t' => $when(mt_rand(0, 175)), 'from' => $from, 'to' => $to, 'amount' => $rand($range),
              'type' => 'Send Money', 'category' => 'Transfer', 'note' => $pick($notes)]);
    }
}

/* ---- shops -------------------------------------------------------------- */
foreach ($people as $phone => $_) {
    $n = $phone === $main ? 34 : mt_rand(10, 20);
    for ($i = 0; $i < $n; $i++) {
        $id = array_rand($shops);
        $m  = $merchantRows[$id];
        $add(['t' => $when(mt_rand(0, 178)), 'from' => $phone, 'to' => null, 'to_ref' => $m['merchant_name'],
              'amount' => $rand($shops[$id]), 'type' => 'Payment', 'category' => $m['category'],
              'note' => 'Payment to ' . $m['merchant_name'], 'cashback_pct' => (float) $m['cashback_pct'],
              'merchant' => $id]);
    }
}

/* ---- phone top ups ------------------------------------------------------ */
foreach ($people as $phone => $_) {
    $n = $phone === $main ? 14 : mt_rand(5, 9);
    for ($i = 0; $i < $n; $i++) {
        $target = mt_rand(0, 4) ? $phone : $pick(array_keys($people));
        $add(['t' => $when(mt_rand(0, 178)), 'from' => $phone, 'to' => null,
              'to_ref' => operator_of($target), 'amount' => (float) $pick([29, 49, 99, 149, 199, 299, 399, 499]),
              'type' => 'Mobile Recharge', 'category' => 'Recharge',
              'note' => 'Prepaid top up for ' . $target, 'cashback_pct' => CASHBACK_RECHARGE_PCT,
              'recharge' => $target]);
    }
}

/* ---- bills, paid a few days into the next month ------------------------- */
foreach ($bills as $phone => $list) {
    foreach ($list as [$type, $org, $consumer, $range]) {
        for ($mo = 5; $mo >= 1; $mo--) {
            $billMonth = date('F Y', strtotime("first day of -$mo month", $today));
            $paidOn    = strtotime("first day of -" . ($mo - 1) . " month", $today) + mt_rand(3, 12) * 86400;
            if ($paidOn > $today) continue;
            $add(['t' => $paidOn + mt_rand(10, 21) * 3600, 'from' => $phone, 'to' => null,
                  'to_ref' => $orgNames[$org], 'amount' => $rand($range), 'type' => 'Bill Pay',
                  'category' => 'Bills', 'note' => BILL_TYPES[$type][0] . ' for ' . $billMonth,
                  'fee_flat' => FEE_BILL_FLAT, 'bill' => [$type, $org, $consumer, $billMonth]]);
        }
    }
}

/* ---- cash out now and then --------------------------------------------- */
foreach ([$main => 4, $other => 3, '01913765240' => 3, '01626908315' => 2, '01474319086' => 2] as $phone => $n) {
    for ($i = 0; $i < $n; $i++) {
        $agent = $pick(['AG1001', 'AG1002', 'AG1003']);
        $add(['t' => $when(mt_rand(1, 170)), 'from' => $phone, 'to' => null, 'to_ref' => $agent,
              'amount' => (float) $pick([1000, 1500, 2000, 3000, 5000]), 'type' => 'Cash Out',
              'category' => 'Cash', 'note' => 'Cash out at agent ' . $agent, 'fee_pct' => FEE_CASH_OUT_PCT,
              'agent' => [$agent, -1]]);
    }
}

/* ---- tuition ----------------------------------------------------------- */
$year = (int) date('Y', $today);
$add(['t' => strtotime("$year-06-08 11:20"), 'from' => $main, 'to' => null, 'to_ref' => 'BRAC University',
      'amount' => 21500.0, 'type' => 'Education Fee', 'category' => 'Education', 'note' => "Summer $year fee for 22101487",
      'fee' => ['555', '22101487', 'Nusrat Jahan', "Summer $year"]]);
$add(['t' => $when(9, 12, 5), 'from' => '01855670129', 'to' => null, 'to_ref' => 'Notre Dame College',
      'amount' => 9800.0, 'type' => 'Education Fee', 'category' => 'Education', 'note' => "Fall $year fee for NDC-24-1177",
      'fee' => ['557', 'NDC-24-1177', 'Sumaiya Khatun', "Fall $year"]]);
$add(['t' => $when(14, 16, 40), 'from' => '01307554812', 'to' => null, 'to_ref' => 'BUET',
      'amount' => 7350.0, 'type' => 'Education Fee', 'category' => 'Education', 'note' => "Fall $year fee for 2106118",
      'fee' => ['556', '2106118', 'Shahriar Kabir', "Fall $year"]]);

usort($events, static fn($a, $b) => $a['t'] <=> $b['t']);

/* ======================================================================= */

$pdo = db();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['transactions', 'notifications', 'login_attempts', 'audit_log', 'favourites', 'savings_goals',
          'money_requests', 'bill_payments', 'education_fee', 'mobile_recharge', 'support_messages',
          'account', 'user'] as $table) {
    $pdo->exec("TRUNCATE `$table`");
}
$pdo->exec('UPDATE organizations SET total_received_amount = 0');
$pdo->exec('UPDATE institutes SET total_received_amount = 0');
$pdo->exec('UPDATE merchants SET total_received_amount = 0');
$pdo->exec('UPDATE agent_details SET cash_in_amount = 50000');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$pdo->beginTransaction();

$pwHash  = password_hash(DEMO_PASSWORD, PASSWORD_BCRYPT);
$pinHash = password_hash(DEMO_PIN, PASSWORD_BCRYPT);
$balance = [];
$i = 0;
foreach ($people as $phone => [$first, $last, $gender, $dob]) {
    $joined = date('Y-m-d H:i:s', $when(190 + $i * 3, 10));
    q('INSERT INTO user (phone, firstname, lastname, nid, email, password_hash, pin_hash, dob, gender,
                         avatar_color, status, daily_limit, created_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
      [$phone, $first, $last, (string) (1990000000 + mt_rand(1000000, 9999999)) . mt_rand(100, 999),
       strtolower($first . '.' . $last) . mt_rand(10, 99) . '@gmail.com', $pwHash, $pinHash, $dob, $gender,
       $palette[$i % count($palette)], $phone === '01721093358' ? 'frozen' : 'active',
       $phone === $main ? 50000 : DAILY_LIMIT_DEFAULT, $joined]);
    q('INSERT INTO account (phone, balance) VALUES (?, 0)', [$phone]);
    $balance[$phone] = 0.0;
    $i++;
}

$agentFloat = ['AG1001' => 50000.0, 'AG1002' => 50000.0, 'AG1003' => 50000.0];
$merchantTotal = [];
$ledger = [];                       // phone => list of rows, for notifications later
$ts = static fn(int $t): string => date('Y-m-d H:i:s', $t);

$record = static function (array $e, string $status, ?float $sAfter, ?float $rAfter,
                           float $fee, float $cb, string $note) use ($ts): string {
    $id = $status === 'success' ? new_trx_id() : 'F' . strtoupper(bin2hex(random_bytes(5)));
    q('INSERT INTO transactions (trx_id, transaction_from, transaction_to, transaction_type, category,
                                 amount, fee, cashback, sender_balance_after, receiver_balance_after,
                                 note, status, transaction_time)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
      [$id, $e['from'] ?? $e['from_ref'], $e['to'] ?? $e['to_ref'], $e['type'], $e['category'],
       $e['amount'], $fee, $cb, $sAfter, $rAfter, mb_substr($note, 0, 120), $status, $ts($e['t'])]);
    return $id;
};

foreach ($events as $e) {
    $fee   = round($e['amount'] * ($e['fee_pct'] ?? 0) / 100 + ($e['fee_flat'] ?? 0), 2);
    $cb    = round($e['amount'] * ($e['cashback_pct'] ?? 0) / 100, 2);
    $debit = $e['amount'] + $fee;
    $from  = $e['from'];
    $to    = $e['to'];

    if ($from !== null && $balance[$from] < $debit) {
        // keep a handful of these, the admin console has a tab for them
        if (mt_rand(0, 5) === 0) {
            $record($e, 'failed', null, null, $fee, 0.0,
                    'Insufficient balance. You need ' . money($debit) . ' and you have ' . money($balance[$from]) . '.');
        }
        continue;
    }
    if (isset($e['agent']) && $e['agent'][1] < 0 && $agentFloat[$e['agent'][0]] < $e['amount']) {
        continue;
    }

    $sAfter = $rAfter = null;
    if ($from !== null) {
        $balance[$from] = round($balance[$from] - $debit + $cb, 2);
        $sAfter = $balance[$from];
    }
    if ($to !== null) {
        $balance[$to] = round($balance[$to] + $e['amount'], 2);
        $rAfter = $balance[$to];
    }
    $trx = $record($e, 'success', $sAfter, $rAfter, $fee, $cb, $e['note']);

    if (isset($e['agent'])) {
        $agentFloat[$e['agent'][0]] += $e['agent'][1] * $e['amount'];
    }
    if (isset($e['merchant'])) {
        $merchantTotal[$e['merchant']] = ($merchantTotal[$e['merchant']] ?? 0) + $e['amount'];
    }
    if (isset($e['recharge'])) {
        q('INSERT INTO mobile_recharge (payer_phone, target_phone, operator, connection, amount, trx_id, created_at)
           VALUES (?,?,?,?,?,?,?)',
          [$from, $e['recharge'], operator_of($e['recharge']), 'Prepaid', $e['amount'], $trx, $ts($e['t'])]);
    }
    if (isset($e['bill'])) {
        [$type, $org, $consumer, $month] = $e['bill'];
        q('INSERT INTO bill_payments (payer_phone, billtype, organization_id, organization_name, consumer_no,
                                      amount, month, trx_id, payment_date)
           VALUES (?,?,?,?,?,?,?,?,?)',
          [$from, $type, $org, $orgNames[$org], $consumer, $e['amount'], $month, $trx, $ts($e['t'])]);
    }
    if (isset($e['fee'])) {
        [$inst, $sid, $sname, $sem] = $e['fee'];
        q('INSERT INTO education_fee (payer_phone, institute_id, institute_name, student_id, student_name,
                                      semester, amount, trx_id, timestamp)
           VALUES (?,?,?,?,?,?,?,?,?)',
          [$from, $inst, $e['to_ref'], $sid, $sname, $sem, $e['amount'], $trx, $ts($e['t'])]);
    }

    foreach (array_filter([$from, $to]) as $p) {
        $ledger[$p][] = $e + ['trx' => $trx, 'fee_v' => $fee, 'cb_v' => $cb];
    }
}

foreach ($agentFloat as $id => $f) {
    q('UPDATE agent_details SET cash_in_amount = ? WHERE agent_id = ?', [$f, $id]);
}
foreach ($merchantTotal as $id => $total) {
    q('UPDATE merchants SET total_received_amount = ? WHERE merchant_id = ?', [$total, $id]);
}

/* ---- savings goals, funded out of the balance they have now ------------- */
$goals = [
    [$main,         'New laptop for thesis',  85000, date('Y-m-d', strtotime('+3 months', $today)), [6000, 5000, 4500, 3000]],
    [$main,         'Emergency fund',         50000, null,                                          [3000, 2500, 2000]],
    [$other,        'Sajek trip in December', 18000, date('Y-12-10', $today),                     [2500, 2000]],
    ['01984402751', 'Eid shopping',           12000, null,                                          [1500]],
];
foreach ($goals as [$phone, $title, $target, $deadline, $deposits]) {
    q('INSERT INTO savings_goals (phone, title, target_amount, deadline, created_at) VALUES (?,?,?,?,?)',
      [$phone, $title, $target, $deadline, $ts($when(count($deposits) * 12 + 5))]);
    $goalId = (int) db()->lastInsertId();
    $saved = 0.0;
    foreach ($deposits as $k => $amount) {
        if ($balance[$phone] - $amount < 1500) break;
        $balance[$phone] -= $amount;
        $saved += $amount;
        $e = ['t' => $when((count($deposits) - $k) * 12, 21), 'from' => $phone, 'to' => null,
              'to_ref' => 'Savings: ' . $title, 'amount' => (float) $amount, 'type' => 'Savings',
              'category' => 'Savings', 'note' => 'Added to goal ' . $title];
        $record($e, 'success', $balance[$phone], null, 0.0, 0.0, $e['note']);
    }
    q('UPDATE savings_goals SET saved_amount = ? WHERE id = ?', [$saved, $goalId]);
}
q("INSERT INTO savings_goals (phone, title, target_amount, saved_amount, status, created_at)
   VALUES (?, ?, 15000, 0, 'completed', ?)", [$main, "Cox's Bazar with the girls", $ts($when(160))]);

foreach ($balance as $phone => $b) {
    q('UPDATE account SET balance = ? WHERE phone = ?', [$b, $phone]);
}

/* ---- requests ----------------------------------------------------------- */
$req = static fn(string $by, string $of, float $amt, string $note, string $status, int $ago, ?string $grp = null)
    => q('INSERT INTO money_requests (requester_phone, payer_phone, amount, note, split_group, status, created_at)
          VALUES (?,?,?,?,?,?,?)', [$by, $of, $amt, $note, $grp, $status, date('Y-m-d H:i:s', $when($ago))]);
$req($other,         $main,         350,  'Star Kabab on Thursday, your share', 'pending', 1);
$req('01984402751',  $main,         1200, 'Coke Studio Bangla tickets',          'pending', 3);
$req($main,          '01552104478', 450,  'Rickshaw to Dhanmondi and back',      'pending', 2);
$req($main,          '01913765240', 800,  'Printing the thesis draft',           'paid',    20);
$req($main,          '01748221967', 300,  'Fuchka at TSC',                      'declined', 34);
foreach (['01552104478', '01984402751', $other] as $p) {
    $req($main, $p, 612.5, 'Friday dinner at Kacchi Bhai (4 ways)', $p === $other ? 'paid' : 'pending', 4, 'SP7C31A0B2');
}

/* ---- favourites --------------------------------------------------------- */
foreach ([[$main, $other, 'Tanvir (flatmate)'], [$main, '01552104478', 'Farhana'],
          [$main, '01626908315', 'Sadia apu'], [$main, '01984402751', 'Maliha'],
          [$other, $main, 'Nusrat'], [$other, '01913765240', 'Rakib bhai']] as [$o, $s, $l]) {
    q('INSERT INTO favourites (owner_phone, saved_phone, label, created_at) VALUES (?,?,?,?)',
      [$o, $s, $l, $ts($when(mt_rand(30, 150)))]);
}

/* ---- notifications for the two demo accounts ---------------------------- */
foreach ([$main, $other] as $phone) {
    $rows = array_slice($ledger[$phone] ?? [], -18);
    foreach ($rows as $k => $e) {
        $in    = ($e['to'] ?? null) === $phone;
        $title = $in ? ($e['from'] !== null ? 'Money received' : $e['type']) : $e['type'];
        $body  = $in
            ? 'You received ' . money($e['amount']) . ' from ' . ($e['from'] !== null ? name_of($e['from']) : $e['from_ref']) . '.'
            : money($e['amount']) . ' sent to ' . ($e['to'] !== null ? name_of($e['to']) : $e['to_ref'])
              . '. Reference ' . $e['trx'] . '.';
        q('INSERT INTO notifications (user_phone, title, body, icon, amount, read_status, created_at)
           VALUES (?,?,?,?,?,?,?)',
          [$phone, $title, $body, $in ? 'in' : 'out', $e['amount'], $k < count($rows) - 4 ? 1 : 0, $ts($e['t'])]);
    }
}
q("INSERT INTO notifications (user_phone, title, body, icon, amount, created_at) VALUES (?,?,?,?,?,?)",
  [$main, 'Money requested', 'Tanvir Ahmed asked you for ' . money(350) . '. Note: Star Kabab on Thursday, your share',
   'request', 350, $ts($when(1, 20))]);

/* ---- support desk ------------------------------------------------------- */
$tickets = [
    [$main,          'I paid my DESCO bill twice by mistake last month, the second one was DESCO-40218867 again. Can it be reversed?', 1, 26],
    ['01626908315',  'Cash out at agent AG1002 said the agent did not have enough cash. The agent says they do. Which one is right?', 1, 17],
    [$other,         'The QR scanner does not open on my phone. It just says the browser cannot open the camera.', 0, 3],
    ['01721093358',  'My account got frozen after I typed the wrong PIN. I am the owner, NID ends in 4471. Please unfreeze.', 0, 1],
];
foreach ($tickets as [$phone, $msg, $solved, $ago]) {
    [$f, $l] = $people[$phone];
    q('INSERT INTO support_messages (user_name, user_email, user_phone, message, issue_solved, timestamp)
       VALUES (?,?,?,?,?,?)',
      [$f . ' ' . $l, scalar('SELECT email FROM user WHERE phone = ?', [$phone]), $phone, $msg, $solved,
       $ts($when($ago))]);
}

/* ---- sign in trail and audit ------------------------------------------- */
$ips = ['103.94.135.22', '114.130.54.17', '27.147.201.9', '103.112.48.201'];
foreach ($people as $phone => $_) {
    for ($k = 0; $k < ($phone === $main ? 6 : 2); $k++) {
        $t = $ts($when(mt_rand(0, 40)));
        q("INSERT INTO login_attempts (kind, identifier, ip, successful, attempted_at) VALUES ('login',?,?,1,?)",
          [$phone, $pick($ips), $t]);
        q('INSERT INTO audit_log (actor, action, detail, ip, created_at) VALUES (?,?,?,?,?)',
          [$phone, 'login', 'signed in', $pick($ips), $t]);
    }
}
foreach (range(1, 4) as $k) {
    q("INSERT INTO login_attempts (kind, identifier, ip, successful, attempted_at) VALUES ('login',?,?,0,?)",
      ['01721093358', '45.125.222.91', $ts($when(2, 3, 10 + $k))]);
}
q("INSERT INTO login_attempts (kind, identifier, ip, successful, attempted_at) VALUES ('login',?,?,0,?)",
  [$main, '114.130.54.17', $ts($when(6, 23, 48))]);
q('INSERT INTO audit_log (actor, action, detail, ip, created_at) VALUES (?,?,?,?,?)',
  ['01721093358', 'account_frozen', 'too many wrong PIN attempts', '45.125.222.91', $ts($when(2, 3, 20))]);

$pdo->commit();

/* ---- check the books balance ------------------------------------------- */
$bad = 0;
foreach ($balance as $phone => $b) {
    $fromLedger = (float) scalar(
        "SELECT COALESCE(SUM(CASE WHEN transaction_to = ? THEN amount ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN transaction_from = ? THEN amount + fee - cashback ELSE 0 END), 0)
           FROM transactions WHERE status = 'success' AND (transaction_to = ? OR transaction_from = ?)",
        [$phone, $phone, $phone, $phone], 0);
    if (abs($fromLedger - (float) scalar('SELECT balance FROM account WHERE phone = ?', [$phone])) > 0.001) {
        echo "  ledger does not match the balance for $phone\n";
        $bad++;
    }
}

printf("%d users, %d transactions (%d declined), %d bills, %d recharges.\n",
       count($people),
       scalar("SELECT COUNT(*) FROM transactions"),
       scalar("SELECT COUNT(*) FROM transactions WHERE status = 'failed'"),
       scalar('SELECT COUNT(*) FROM bill_payments'),
       scalar('SELECT COUNT(*) FROM mobile_recharge'));
printf("Sign in as %s or %s, password %s, PIN %s.\n", $main, $other, DEMO_PASSWORD, DEMO_PIN);
exit($bad ? 1 : 0);
