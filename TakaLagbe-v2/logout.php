<?php
require_once __DIR__ . '/config/helpers.php';

// signing out changes state, so it is a POST like every other form; a plain
// link could be fired by any page that embeds it as an image
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(is_logged_in() ? 'account.php' : 'login.php');
}
require_csrf();

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
