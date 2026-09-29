<?php
/* User management: search, inspect, freeze, unfreeze and adjust limits. */
require_once __DIR__ . '/config/helpers.php';

$admin = require_admin();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');
    $phone  = post('phone');
    $user   = one('SELECT * FROM user WHERE phone = ?', [$phone]);

    if (!$user) {
        $error = 'That user was not found.';
    } elseif ($action === 'freeze' || $action === 'unfreeze') {
        $status = $action === 'freeze' ? 'frozen' : 'active';
        q('UPDATE user SET status = ? WHERE phone = ?', [$status, $phone]);
        if ($action === 'unfreeze') {
            q("DELETE FROM login_attempts WHERE identifier = ?", [$phone]);
        }
        notify($phone, $action === 'freeze' ? 'Account frozen' : 'Account reopened',
               $action === 'freeze'
                 ? 'Your account has been frozen by support. Contact us to reopen it.'
                 : 'Your account is active again. You can sign in as usual.',
               'lock');
        audit('admin:' . $admin, 'user_' . $action, $phone);
        flash('ok', 'User ' . $phone . ' is now ' . $status . '.');
        redirect('admin_user.php?q=' . urlencode($phone));

    } elseif ($action === 'limit') {
        $limit = post('daily_limit');
        if (!is_numeric($limit) || (float) $limit < 1000 || (float) $limit > 500000) {
            $error = 'Choose a limit between 1,000 and 500,000.';
        } else {
            q('UPDATE user SET daily_limit = ? WHERE phone = ?', [(float) $limit, $phone]);
            audit('admin:' . $admin, 'user_limit_changed', $phone . ' -> ' . $limit);
            flash('ok', 'Daily limit updated for ' . $phone . '.');
            redirect('admin_user.php?q=' . urlencode($phone));
        }
    }
}

$search = mb_substr(get('q'), 0, 60);
$status = in_array(get('status'), ['active', 'frozen'], true) ? get('status') : '';
$page   = max(1, (int) get('page', '1'));

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(u.phone LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?
                 OR u.email LIKE ? OR u.nid LIKE ?)';
    $like    = '%' . $search . '%';
    $params  = array_merge($params, array_fill(0, 5, $like));
}
if ($status !== '') {
    $where[]  = 'u.status = ?';
    $params[] = $status;
}
$clause = implode(' AND ', $where);

$total  = (int) scalar("SELECT COUNT(*) FROM user u WHERE $clause", $params, 0);
$pages  = max(1, (int) ceil($total / 15));
$page   = min($page, $pages);
$offset = ($page - 1) * 15;

$users = all(
    "SELECT u.*, a.balance,
            (SELECT COUNT(*) FROM transactions t
              WHERE (t.transaction_from = u.phone OR t.transaction_to = u.phone)
                AND t.status = 'success') n
       FROM user u LEFT JOIN account a ON a.phone = u.phone
      WHERE $clause ORDER BY u.created_at DESC LIMIT 15 OFFSET $offset", $params);

$pageTitle = 'Users';
$activeNav = 'users';
include('Front_end/front_admin_user.php');
