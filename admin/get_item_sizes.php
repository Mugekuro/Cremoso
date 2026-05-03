<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/menu_helpers.php';
redirectIfNotAdmin();

header('Content-Type: application/json');

$item_id = $_GET['item_id'] ?? 0;
$sizes = getItemSizePrices($item_id);

echo json_encode($sizes);
