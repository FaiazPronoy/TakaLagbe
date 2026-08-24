<?php
/* ---------------------------------------------------------------------
 *  My QR code.
 *  The code is drawn by lib/qr.php, written from scratch, so nothing is
 *  fetched from the internet and the page works offline.
 *  Scanning is done in the browser with the built in BarcodeDetector
 *  where it exists, and there is a manual fallback everywhere else.
 * ------------------------------------------------------------------- */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/qr.php';

$phone  = require_login();
$user   = current_user();
$amount = get('amount');

$payload = 'TKL|' . $phone . '|' . $user['firstname'] . ' ' . $user['lastname']
         . '|' . (valid_amount($amount) ? number_format((float) $amount, 2, '.', '') : '');

$pageTitle = 'My QR code';
$activeNav = 'home';
include('Front_end/front_qr.php');
