<?php
require_once __DIR__ . '/config/helpers.php';

$admin = require_admin();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    if (post('action') === 'add') {
        $id   = post('institute_id');
        $name = mb_substr(post('institute_name'), 0, 120);

        if (!preg_match('/^[0-9A-Za-z]{2,20}$/', $id)) {
            $error = 'The institute ID must be 2 to 20 letters or digits.';
        } elseif ($name === '') {
            $error = 'Enter the institute name.';
        } elseif (scalar('SELECT 1 FROM institutes WHERE institute_id = ?', [$id])) {
            $error = 'That institute ID already exists.';
        } else {
            q('INSERT INTO institutes (institute_id, institute_name) VALUES (?,?)', [$id, $name]);
            audit('admin:' . $admin, 'institute_added', $id . ' ' . $name);
            flash('ok', $name . ' added.');
            redirect('admin_institutes.php');
        }
    }
}

$institutes = all(
    "SELECT i.*,
            (SELECT COUNT(*) FROM education_fee f WHERE f.institute_id = i.institute_id) n
       FROM institutes i ORDER BY i.total_received_amount DESC");

$search  = mb_substr(get('q'), 0, 60);
$params  = [];
$clause  = '1=1';
if ($search !== '') {
    $clause = '(student_id LIKE ? OR student_name LIKE ? OR institute_name LIKE ?
                OR payer_phone LIKE ? OR trx_id LIKE ?)';
    $params = array_fill(0, 5, '%' . $search . '%');
}

$fees = all("SELECT * FROM education_fee WHERE $clause
             ORDER BY timestamp DESC LIMIT 40", $params);

$bySemester = all("SELECT semester, COUNT(*) n, SUM(amount) total
                    FROM education_fee GROUP BY semester ORDER BY semester DESC LIMIT 8");

$pageTitle = 'Institutes';
$activeNav = 'edu';
include('views/admin_institutes.php');
