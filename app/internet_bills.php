<?php
/* Pays an internet bill. All four bill pages share lib/bills.php. */
require_once __DIR__ . '/lib/bills.php';
run_bill_page('internet_bills');
