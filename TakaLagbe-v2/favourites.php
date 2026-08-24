<?php
/* Saved numbers, so common transfers take one tap. */
require_once __DIR__ . '/config/helpers.php';

$phone = require_login();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = post('action');

    if ($action === 'add') {
        $saved = post('saved_phone');
        $label = mb_substr(post('label'), 0, 40);

        if (!valid_phone($saved)) {
            $error = 'Enter a valid 11 digit number.';
        } elseif ($saved === $phone) {
            $error = 'You do not need to save your own number.';
        } elseif (!scalar('SELECT 1 FROM user WHERE phone = ?', [$saved])) {
            $error = 'That number is not registered on ' . APP_NAME . '.';
        } elseif (scalar('SELECT 1 FROM favourites WHERE owner_phone = ? AND saved_phone = ?',
                         [$phone, $saved])) {
            $error = 'That number is already in your favourites.';
        } else {
            q('INSERT INTO favourites (owner_phone, saved_phone, label) VALUES (?,?,?)',
              [$phone, $saved, $label ?: name_of($saved)]);
            flash('ok', 'Saved to favourites.');
            redirect('favourites.php');
        }
    }

    if ($action === 'remove') {
        q('DELETE FROM favourites WHERE owner_phone = ? AND id = ?',
          [$phone, (int) post('id')]);
        flash('ok', 'Removed from favourites.');
        redirect('favourites.php');
    }

    if ($action === 'rename') {
        q('UPDATE favourites SET label = ? WHERE owner_phone = ? AND id = ?',
          [mb_substr(post('label'), 0, 40), $phone, (int) post('id')]);
        flash('ok', 'Name updated.');
        redirect('favourites.php');
    }
}

$favourites = all(
    "SELECT f.*, u.firstname, u.lastname, u.avatar_color,
            (SELECT COUNT(*) FROM transactions t
              WHERE t.transaction_from = f.owner_phone
                AND t.transaction_to = f.saved_phone
                AND t.status = 'success') AS sends,
            (SELECT COALESCE(SUM(t.amount),0) FROM transactions t
              WHERE t.transaction_from = f.owner_phone
                AND t.transaction_to = f.saved_phone
                AND t.status = 'success') AS total
       FROM favourites f LEFT JOIN user u ON u.phone = f.saved_phone
      WHERE f.owner_phone = ? ORDER BY sends DESC, f.created_at DESC", [$phone]);

/* people you have sent to but have not saved yet */
$suggestions = all(
    "SELECT t.transaction_to AS phone, COUNT(*) n, u.firstname, u.lastname, u.avatar_color
       FROM transactions t JOIN user u ON u.phone = t.transaction_to
      WHERE t.transaction_from = ? AND t.transaction_type = 'Send Money'
        AND t.status = 'success'
        AND t.transaction_to NOT IN (SELECT saved_phone FROM favourites WHERE owner_phone = ?)
      GROUP BY t.transaction_to, u.firstname, u.lastname, u.avatar_color
      ORDER BY n DESC LIMIT 5", [$phone, $phone]);

$pageTitle = 'Favourites';
$activeNav = 'home';
include('Front_end/front_favourites.php');
