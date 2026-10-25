<?php
require_once __DIR__ . '/config/helpers.php';

require_login();

$stats = [
    'users'        => (int) scalar('SELECT COUNT(*) FROM user', [], 0),
    'transactions' => (int) scalar("SELECT COUNT(*) FROM transactions WHERE status = 'success'", [], 0),
    'volume'       => (float) scalar("SELECT COALESCE(SUM(amount),0) FROM transactions
                                       WHERE status = 'success'", [], 0),
    'billers'      => (int) scalar('SELECT COUNT(*) FROM organizations', [], 0),
];

$pageTitle = __('About');
$activeNav = '';
include('views/about.php');
