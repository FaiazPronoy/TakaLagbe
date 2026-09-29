<?php
/* The notification centre. */
require_once __DIR__ . '/config/helpers.php';

$phone = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    if ($action === 'read_all') {
        q('UPDATE notifications SET read_status = 1 WHERE user_phone = ?', [$phone]);
        flash('ok', __('All notifications marked as read.'));
    } elseif ($action === 'clear') {
        q('DELETE FROM notifications WHERE user_phone = ? AND read_status = 1', [$phone]);
        flash('ok', __('Read notifications cleared.'));
    } elseif ($action === 'read_one') {
        q('UPDATE notifications SET read_status = 1 WHERE user_phone = ? AND id = ?',
          [$phone, (int) post('id')]);
    }
    redirect('notifications.php');
}

$filter = get('show') === 'unread' ? 'unread' : 'all';
$where  = $filter === 'unread' ? ' AND read_status = 0' : '';

$rows   = all("SELECT * FROM notifications WHERE user_phone = ?$where
               ORDER BY created_at DESC, id DESC LIMIT 100", [$phone]);
$unread = unread_count($phone);

$pageTitle = __('Notifications');
$activeNav = 'notifications';
include('Front_end/front_notifications.php');
