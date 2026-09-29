<?php
/* QR payload build and parse. */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/qr.php';

$phone  = require_login();
$user   = current_user();
$amount = get('amount');

$payload = 'TKL|' . $phone . '|' . $user['firstname'] . ' ' . $user['lastname']
         . '|' . (valid_amount($amount) ? number_format((float) $amount, 2, '.', '') : '');

$pageTitle = __('My QR code');
$activeNav = 'home';
include('Front_end/front_qr.php');
