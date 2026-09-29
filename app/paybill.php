<?php
/* The bill hub: pick a utility, or repeat a bill you have paid before. */
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/bills.php';

$phone = require_login();

$recent = all('SELECT * FROM bill_payments WHERE payer_phone = ?
               ORDER BY payment_date DESC LIMIT 8', [$phone]);

$totalPaid = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM bill_payments WHERE payer_phone = ?", [$phone], 0);

$thisMonth = (float) scalar(
    "SELECT COALESCE(SUM(amount),0) FROM bill_payments
      WHERE payer_phone = ? AND payment_date >= DATE_FORMAT(CURDATE(),'%Y-%m-01')",
    [$phone], 0);

$pageTitle = __('Pay bills');
$activeNav = 'home';
include('Front_end/front_paybill.php');
