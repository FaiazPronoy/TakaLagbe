<?php
require_once __DIR__ . '/config/helpers.php';

http_response_code(404);
$pageTitle = 'Page not found';
$activeNav = '';
include __DIR__ . '/Front_end/front_404.php';
