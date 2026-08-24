<?php
require_once __DIR__ . '/config/helpers.php';

if (isset($_SESSION['admin_user'])) {
    audit('admin:' . $_SESSION['admin_user'], 'admin_logout', 'signed out');
    unset($_SESSION['admin_user']);
}
flash('ok', 'Signed out of the admin console.');
redirect('admin_login.php');
