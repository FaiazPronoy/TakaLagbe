<?php
/* JSON list of billers. A small read only API over the same data the bill
   pages render server side, handy for testing and for a future mobile client. */
require_once __DIR__ . '/config/helpers.php';

require_login();
header('Content-Type: application/json; charset=utf-8');

$billtype = get('billtype');
$rows = $billtype !== ''
    ? all('SELECT organization_id, organization_name, billtype FROM organizations
            WHERE billtype = ? ORDER BY organization_name', [$billtype])
    : all('SELECT organization_id, organization_name, billtype FROM organizations
            ORDER BY billtype, organization_name');

echo json_encode(['organizations' => $rows], JSON_UNESCAPED_UNICODE);
