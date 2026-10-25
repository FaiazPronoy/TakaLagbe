<?php
require_once __DIR__ . '/config/helpers.php';

$admin = require_admin();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id     = (int) post('id');
    $action = post('action');
    $ticket = one('SELECT * FROM support_messages WHERE id = ?', [$id]);

    if (!$ticket) {
        $error = 'That ticket was not found.';
    } elseif ($action === 'solve' || $action === 'reopen') {
        $solved = $action === 'solve' ? 1 : 0;
        q('UPDATE support_messages SET issue_solved = ? WHERE id = ?', [$solved, $id]);
        if ($solved && $ticket['user_phone']) {
            notify($ticket['user_phone'], 'Support ticket closed',
                   'Your ticket has been marked as solved. Reply if it is still an issue.', 'bell');
        }
        audit('admin:' . $admin, 'ticket_' . $action, '#' . $id);
        flash('ok', 'Ticket #' . $id . ' marked as ' . ($solved ? 'solved' : 'open') . '.');
        redirect('admin_support.php');

    } elseif ($action === 'reply') {
        $reply = trim(post('reply'));
        if (mb_strlen($reply) < 3) {
            $error = 'Write a reply first.';
        } else {
            if ($ticket['user_phone']) {
                notify($ticket['user_phone'], 'Reply from support',
                       mb_substr($reply, 0, 200), 'bell');
            }
            q('UPDATE support_messages SET issue_solved = 1 WHERE id = ?', [$id]);
            audit('admin:' . $admin, 'ticket_replied', '#' . $id . ': ' . mb_substr($reply, 0, 80));
            flash('ok', 'Reply sent and ticket closed.');
            redirect('admin_support.php');
        }
    }
}

$filter = get('show') === 'solved' ? 'solved' : (get('show') === 'all' ? 'all' : 'open');
$where  = match ($filter) {
    'solved' => 'WHERE issue_solved = 1',
    'all'    => '',
    default  => 'WHERE issue_solved = 0',
};

$tickets = all("SELECT * FROM support_messages $where ORDER BY timestamp DESC LIMIT 60");
$counts  = [
    'open'   => (int) scalar('SELECT COUNT(*) FROM support_messages WHERE issue_solved = 0', [], 0),
    'solved' => (int) scalar('SELECT COUNT(*) FROM support_messages WHERE issue_solved = 1', [], 0),
];

$pageTitle = 'Support desk';
$activeNav = 'support';
include('views/admin_support.php');
