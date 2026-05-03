<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/menu_helpers.php';
if (!isStaff()) { http_response_code(403); exit(); }

header('Content-Type: application/json');

$item_id = $_GET['item_id'] ?? 0;
$flavors = getItemFlavors($item_id);

echo json_encode($flavors);
