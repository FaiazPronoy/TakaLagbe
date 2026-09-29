<?php
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(isset($_SESSION['admin_user']) ? 'admin_dashboard.php' : 'admin_login.php');
}
require_csrf();

if (isset($_SESSION['admin_user'])) {
    audit('admin:' . $_SESSION['admin_user'], 'admin_logout', 'signed out');
    unset($_SESSION['admin_user']);
}
flash('ok', 'Signed out of the admin console.');
redirect('admin_login.php');
