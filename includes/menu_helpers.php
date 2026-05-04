<?php
// Menu helper functions for simplified schema

function getActiveCategories() {
    global $pdo;
    return $pdo->query("SELECT DISTINCT category as category_id, category as category_name FROM items WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
}

function getMenuItemsByCategory($category) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT item_id as menu_item_id, item_name, 0 as has_sizes, 0 as has_flavors FROM items WHERE category = ? AND is_active = 1 ORDER BY display_order ASC");
    $stmt->execute([$category]);
    return $stmt->fetchAll();
}

function getItemSizePrices($item_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT item_id as size_price_id, size as size_name, price FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    return $stmt->fetchAll();
}

function getItemFlavors($item_id) {
    global $pdo;
    return [];
}

function getActiveToppings() {
    global $pdo;
    return $pdo->query("SELECT addon_id as topping_id, addon_name as topping_name, price FROM addons WHERE addon_type = 'topping' AND is_active = 1 ORDER BY addon_name ASC")->fetchAll();
}

function getActiveSauces() {
    global $pdo;
    return $pdo->query("SELECT addon_id as sauce_id, addon_name as sauce_name, price FROM addons WHERE addon_type = 'sauce' AND is_active = 1 ORDER BY addon_name ASC")->fetchAll();
}

function getActiveFruits() {
    global $pdo;
    return $pdo->query("SELECT addon_id as fruit_id, addon_name as fruit_name, price FROM addons WHERE addon_type = 'fruit' AND is_active = 1 ORDER BY addon_name ASC")->fetchAll();
}

function getActiveExtras() {
    global $pdo;
    return [];
}

function getTransactionItemCustomizations($transaction_item_id) {
    global $pdo;
    
    $customizations = [
        'toppings' => [],
        'sauces' => [],
        'fruits' => [],
        'extras' => []
    ];
    
    $stmt = $pdo->prepare("SELECT addons_detail FROM transaction_items WHERE transaction_item_id = ?");
    $stmt->execute([$transaction_item_id]);
    $result = $stmt->fetch();
    
    if ($result && $result['addons_detail'] && $result['addons_detail'] !== 'N/A') {
        // Parse the readable format: "type: topping, name: Mallows, price: 10; type: sauce, name: Chocolate, price: 20"
        $addonItems = explode('; ', $result['addons_detail']);
        
        foreach ($addonItems as $addonItem) {
            $parts = explode(', ', $addonItem);
            $addon = [];
            
            foreach ($parts as $part) {
                list($key, $value) = explode(': ', $part, 2);
                $addon[$key] = $value;
            }
            
            if (isset($addon['type']) && isset($addon['name']) && isset($addon['price'])) {
                $type = $addon['type'] . 's';
                if (isset($customizations[$type])) {
                    $key = $addon['type'] . '_name';
                    $customizations[$type][] = [
                        $key => $addon['name'],
                        'price' => floatval($addon['price'])
                    ];
                }
            }
        }
    }
    
    return $customizations;
}
