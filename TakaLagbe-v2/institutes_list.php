<?php
/* JSON list of institutes. A small read only API over the same data the
   education fee page renders server side. */
require_once __DIR__ . '/config/helpers.php';

require_login();
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'institutes' => all('SELECT institute_id, institute_name FROM institutes
                         ORDER BY institute_name'),
], JSON_UNESCAPED_UNICODE);
