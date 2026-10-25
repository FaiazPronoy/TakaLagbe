<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';

require_admin();

$tab    = in_array(get('tab'), ['audit', 'failed', 'logins'], true) ? get('tab') : 'audit';
$search = mb_substr(get('q'), 0, 60);
$page   = max(1, (int) get('page', '1'));
$offset = ($page - 1) * 25;

$auditWhere  = '1=1';
$auditParams = [];
if ($search !== '') {
    $auditWhere  = '(actor LIKE ? OR action LIKE ? OR detail LIKE ? OR ip LIKE ?)';
    $auditParams = array_fill(0, 4, '%' . $search . '%');
}

$auditTotal = (int) scalar("SELECT COUNT(*) FROM audit_log WHERE $auditWhere", $auditParams, 0);
$auditRows  = all("SELECT * FROM audit_log WHERE $auditWhere
                   ORDER BY id DESC LIMIT 25 OFFSET $offset", $auditParams);
$auditPages = max(1, (int) ceil($auditTotal / 25));

$failedRows = all("SELECT * FROM transactions WHERE status = 'failed'
                   ORDER BY id DESC LIMIT 40");

$badLogins = all(
    "SELECT identifier AS phone, ip, COUNT(*) n, MAX(attempted_at) last_try
       FROM login_attempts
      WHERE successful = 0 AND attempted_at > (NOW() - INTERVAL 7 DAY)
      GROUP BY identifier, ip HAVING n >= 2
      ORDER BY n DESC, last_try DESC LIMIT 40");

$frozen = all("SELECT phone, firstname, lastname, email FROM user
                WHERE status = 'frozen' ORDER BY phone");

$pageTitle = 'Audit log';
$activeNav = 'audit';
include('views/admin_audit.php');
