<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name    = trim($_POST['customer_name']);
    $channel_id       = $_POST['channel_id'];
    $payment_method_id = $_POST['payment_method_id'];
    $account_name     = trim($_POST['account_name'] ?? '');
    $cart             = json_decode($_POST['cart_data'], true);
    $branch_id        = $_SESSION['branch_id'];
    $user_id          = $_SESSION['user_id'];

    if(empty($customer_name)) {
        $customer_name = 'Anonymous Customer';
    }

    $stmt = $pdo->prepare("INSERT INTO customers (customer_name) VALUES (?)");
    $stmt->execute([$customer_name]);
    $customer_id = $pdo->lastInsertId();

    $order_number = 'ORD-' . date('YmdHis') . rand(100, 999);

    $total = 0;
    foreach($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, account_name, transaction_date, total_amount, status) VALUES (?,?,?,?,?,?,?,NOW(),?,?)");
        $stmt->execute([$order_number, $customer_id, $user_id, $branch_id, $channel_id, $payment_method_id, $account_name ?: null, $total, 'pending']);
        $transaction_id = $pdo->lastInsertId();

        foreach($cart as $item) {
            $subtotal = $item['price'] * $item['qty'];
            
            // Get item details from database using menu_item_id
            $stmt = $pdo->prepare("SELECT item_name, category, size FROM items WHERE item_id = ?");
            $stmt->execute([$item['menu_item_id']]);
            $itemData = $stmt->fetch();
            
            // Prepare addons in readable format
            $addonsLines = [];
            $addonsTotal = 0;
            
            foreach(['toppings', 'sauces', 'fruits', 'extras'] as $type) {
                if (!empty($item[$type])) {
                    foreach ($item[$type] as $addon) {
                        $addonsLines[] = "type: " . rtrim($type, 's') . ", name: " . $addon['name'] . ", price: " . $addon['price'];
                        $addonsTotal += $addon['price'] * $item['qty'];
                    }
                }
            }
            
            // Store "NONE" if no add-ons, otherwise store readable format
            $addonsDetail = !empty($addonsLines) ? implode('; ', $addonsLines) : 'NONE';
            
            $stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $transaction_id,
                $item['menu_item_id'],
                $itemData['item_name'],
                $itemData['category'],
                $itemData['size'],
                $item['base_price'],
                $item['qty'],
                $addonsDetail,
                $addonsTotal,
                $subtotal
            ]);
        }

        $pdo->commit();
        header("Location: pending_orders.php?msg=" . urlencode("Order created and placed in pending queue! Order#: $order_number"));
        exit();
    } catch(Exception $e) {
        $pdo->rollBack();
        header("Location: new_order.php?error=" . urlencode("Failed to save order: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: new_order.php");
    exit();
}
