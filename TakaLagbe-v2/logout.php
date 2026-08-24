<?php
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    audit(current_phone(), 'logout', 'signed out');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();
start_session();
flash('ok', 'You have been signed out.');
redirect('login.php');
