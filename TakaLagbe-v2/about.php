<?php
/* What the project is, and how it is built. */
require_once __DIR__ . '/config/helpers.php';

require_login();

$stats = [
    'users'        => (int) scalar('SELECT COUNT(*) FROM user', [], 0),
    'transactions' => (int) scalar("SELECT COUNT(*) FROM transactions WHERE status = 'success'", [], 0),
    'volume'       => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                       WHERE status = 'success'", [], 0),
    'billers'      => (int) scalar('SELECT COUNT(*) FROM organizations', [], 0),
];

$pageTitle = 'About';
$activeNav = '';
include('Front_end/front_about.php');
