<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name    = trim($_POST['customer_name']);
    $channel_id       = $_POST['channel_id'];
    $payment_method_id = $_POST['payment_method_id'];
    $cart             = json_decode($_POST['cart_data'], true);
    $branch_id        = $_SESSION['branch_id'];
    $user_id          = $_SESSION['user_id'];

    // Use default customer name if empty
    if(empty($customer_name)) {
        $customer_name = 'Walk-in Customer';
    }

    // Insert customer
    $stmt = $pdo->prepare("INSERT INTO customers (customer_name) VALUES (?)");
    $stmt->execute([$customer_name]);
    $customer_id = $pdo->lastInsertId();

    // Generate order number
    $order_number = 'ORD-' . date('YmdHis') . rand(100, 999);

    // Calculate total
    $total = 0;
    foreach($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }

    try {
        $pdo->beginTransaction();

        // Insert transaction as pending
        $stmt = $pdo->prepare("INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, total_amount, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$order_number, $customer_id, $user_id, $branch_id, $channel_id, $payment_method_id, $total, 'pending']);
        $transaction_id = $pdo->lastInsertId();

        // Insert transaction items
        foreach($cart as $item) {
            $stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, item_id, quantity, unit_price, subtotal) VALUES (?,?,?,?,?)");
            $subtotal = $item['price'] * $item['qty'];
            $stmt->execute([$transaction_id, $item['id'], $item['qty'], $item['price'], $subtotal]);
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
?>