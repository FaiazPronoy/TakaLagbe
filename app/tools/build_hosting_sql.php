<?php
/*
 * Writes one SQL file to import on shared hosting, where there is no shell
 * to run seed_demo.php and no permission to CREATE DATABASE.
 *
 *   php tools/seed_demo.php --yes
 *   php tools/build_hosting_sql.php hosting.sql
 *
 * The file is the schema without its CREATE DATABASE and USE lines, then
 * every row of the database you just seeded. Import it with phpMyAdmin into
 * the database the host gave you.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/db.php';

$out = $argv[1] ?? 'hosting.sql';

$schema = file_get_contents(__DIR__ . '/../takalagbe.sql');
$schema = preg_replace('/^CREATE DATABASE.*?;\s*$/ms', '', $schema);
$schema = preg_replace('/^USE `takalagbe`;\s*$/m', '', $schema);
// the dump turns key checks back on at the end; the data below needs them off
$schema = preg_replace('/^SET FOREIGN_KEY_CHECKS = 1;\s*$/m', '', $schema);

// organizations and institutes go last: the triggers add to their totals as
// the payment rows go in, and the saved totals then put them back exactly
$tables = ['user', 'account', 'transactions', 'favourites', 'savings_goals', 'money_requests',
           'notifications', 'login_attempts', 'audit_log', 'admin', 'support_messages',
           'merchants', 'agent_details', 'bill_payments', 'education_fee', 'mobile_recharge',
           'organizations', 'institutes'];

$sql = "-- TakaLagbe! schema and demo data, built " . date('Y-m-d H:i') . " from `" . DB_NAME . "`\n"
     . $schema . "\n-- ============================================================ demo data\n";

foreach ($tables as $t) {
    $rows = all("SELECT * FROM `$t`");
    $sql .= "\nDELETE FROM `$t`;\n";
    foreach (array_chunk($rows, 200) as $chunk) {
        $cols = '`' . implode('`,`', array_keys($chunk[0])) . '`';
        $values = array_map(static fn(array $r): string =>
            '(' . implode(',', array_map(static fn($v) => $v === null ? 'NULL' : db()->quote((string) $v), $r)) . ')',
            $chunk);
        $sql .= "INSERT INTO `$t` ($cols) VALUES\n" . implode(",\n", $values) . ";\n";
    }
}
$sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents($out, $sql);
printf("Wrote %s, %s KB, %d tables.\n", $out, number_format(strlen($sql) / 1024), count($tables));
