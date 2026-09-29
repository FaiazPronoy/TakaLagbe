<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/lib/view.php';
require_once __DIR__ . '/lib/qr.php';

$phone = require_login();
$id    = get('id');

$trx = one('SELECT * FROM transactions WHERE trx_id = ?', [$id]);

if (!$trx || ($trx['transaction_from'] !== $phone && $trx['transaction_to'] !== $phone)) {
    http_response_code(404);
    $pageTitle = __('Receipt not found');
    $activeNav = '';
    include('Front_end/front_404.php');
    exit;
}

$out = trx_is_out($trx, $phone);

$pageTitle = __('Receipt {ref}', ['ref' => $trx['trx_id']]);
$activeNav = 'history';
include('Front_end/front_receipt.php');
