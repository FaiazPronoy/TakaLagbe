<?php
require_once __DIR__ . '/config/helpers.php';

$admin = require_admin();
$error = '';

$billtypes = ['gas_bills' => 'Gas', 'water_bills' => 'Water',
              'internet_bills' => 'Internet', 'electricity_bills' => 'Electricity'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    if ($action === 'add_org') {
        $id       = post('organization_id');
        $name     = mb_substr(post('organization_name'), 0, 120);
        $billtype = post('billtype');

        if (!preg_match('/^[0-9A-Za-z]{2,20}$/', $id)) {
            $error = 'The biller ID must be 2 to 20 letters or digits.';
        } elseif ($name === '') {
            $error = 'Enter the biller name.';
        } elseif (!isset($billtypes[$billtype])) {
            $error = 'Choose a bill type.';
        } elseif (scalar('SELECT 1 FROM organizations WHERE organization_id = ?', [$id])) {
            $error = 'That biller ID already exists.';
        } else {
            q('INSERT INTO organizations (organization_id, organization_name, billtype)
               VALUES (?,?,?)', [$id, $name, $billtype]);
            audit('admin:' . $admin, 'biller_added', $id . ' ' . $name);
            flash('ok', $name . ' added.');
            redirect('admin_merchants.php');
        }
    }

    if ($action === 'add_merchant') {
        $id   = post('merchant_id');
        $name = mb_substr(post('merchant_name'), 0, 120);
        $cat  = mb_substr(post('category'), 0, 30) ?: 'Shopping';
        $cb   = post('cashback_pct');

        if (!preg_match('/^[0-9A-Za-z]{2,20}$/', $id)) {
            $error = 'The merchant ID must be 2 to 20 letters or digits.';
        } elseif ($name === '') {
            $error = 'Enter the merchant name.';
        } elseif (!is_numeric($cb) || (float) $cb < 0 || (float) $cb > 20) {
            $error = 'Cashback must be between 0 and 20 percent.';
        } elseif (scalar('SELECT 1 FROM merchants WHERE merchant_id = ?', [$id])) {
            $error = 'That merchant ID already exists.';
        } else {
            q('INSERT INTO merchants (merchant_id, merchant_name, category, cashback_pct)
               VALUES (?,?,?,?)', [$id, $name, $cat, (float) $cb]);
            audit('admin:' . $admin, 'merchant_added', $id . ' ' . $name);
            flash('ok', $name . ' added.');
            redirect('admin_merchants.php');
        }
    }

    if ($action === 'set_cashback') {
        $id = post('merchant_id');
        $cb = post('cashback_pct');
        if (!is_numeric($cb) || (float) $cb < 0 || (float) $cb > 20) {
            $error = 'Cashback must be between 0 and 20 percent.';
        } else {
            q('UPDATE merchants SET cashback_pct = ? WHERE merchant_id = ?', [(float) $cb, $id]);
            audit('admin:' . $admin, 'cashback_changed', $id . ' -> ' . $cb . '%');
            flash('ok', 'Cashback updated.');
            redirect('admin_merchants.php');
        }
    }
}

$organizations = all(
    "SELECT o.*,
            (SELECT COUNT(*) FROM bill_payments b WHERE b.organization_id = o.organization_id) n
       FROM organizations o ORDER BY o.billtype, o.organization_name");

$merchants = all(
    "SELECT m.*,
            (SELECT COUNT(*) FROM transactions t
              WHERE t.transaction_to = m.merchant_name AND t.status = 'success') n
       FROM merchants m ORDER BY m.total_received_amount DESC");

$agents = all('SELECT * FROM agent_details ORDER BY agent_id');

$pageTitle = 'Billers and merchants';
$activeNav = 'merchant';
include('views/admin_merchants.php');
