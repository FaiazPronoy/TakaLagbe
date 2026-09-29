<?php
/* Switches between Bangla and English, then goes back to the page it came from. */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/lang.php';

$to = in_array($_GET['to'] ?? '', LANGS, true) ? $_GET['to'] : 'bn';
setcookie('tkl_lang', $to, [
    'expires'  => time() + 365 * 86400,
    'path'     => '/',
    'samesite' => 'Lax',
]);

// only a page on this site: a path, never another host
$back = (string) ($_GET['back'] ?? '');
if (!preg_match('~^/[^/\\\\]~', $back) && $back !== '/') {
    $back = 'index.php';
}
header('Location: ' . $back);
exit;
