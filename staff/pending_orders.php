<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$branch_id = $_SESSION['branch_id'];

// Handle order confirmation/cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $action = $_POST['action'];
    
    if ($action === 'confirm') {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'confirmed' WHERE transaction_id = ? AND branch_id = ?");
        $stmt->execute([$transaction_id, $branch_id]);
        $message = "Order confirmed successfully!";
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled' WHERE transaction_id = ? AND branch_id = ?");
        $stmt->execute([$transaction_id, $branch_id]);
        $message = "Order cancelled successfully!";
    }
    
    header("Location: pending_orders.php?msg=" . urlencode($message));
    exit();
}

// Get pending orders
$pendingOrders = $pdo->prepare("SELECT t.*, c.customer_name, pm.method_name, oc.channel_name, u.fullname as staff_name
                                FROM transactions t
                                JOIN customers c ON t.customer_id = c.customer_id
                                JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                                JOIN order_channels oc ON t.channel_id = oc.channel_id
                                JOIN users u ON t.user_id = u.user_id
                                WHERE t.status = 'pending' AND t.branch_id = ?
                                ORDER BY t.transaction_date ASC");
$pendingOrders->execute([$branch_id]);
$pending = $pendingOrders->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_staff.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-clock"></i> Pending Orders</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 20px 0;">
            <h3 style="color: var(--text-dark); margin: 0;">Orders Awaiting Payment Confirmation</h3>
            <div style="color: var(--text-muted); font-size: 14px;">
                <i class="fas fa-info-circle"></i> 
                <?= count($pending) ?> pending order<?= count($pending) !== 1 ? 's' : '' ?>
            </div>
        </div>
        
        <?php if(count($pending) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Channel</th>
                    <th>Payment Method</th>
                    <th>Total</th>
                    <th>Created</th>
                    <th>Staff</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pending as $order): ?>
                <?php
                // Get order items
                $items = $pdo->prepare("SELECT ti.*, i.item_name, f.flavor_name, s.size_name
                                       FROM transaction_items ti
                                       JOIN items i ON ti.item_id = i.item_id
                                       JOIN flavors f ON i.flavor_id = f.flavor_id
                                       JOIN item_sizes s ON i.size_id = s.size_id
                                       WHERE ti.transaction_id = ?");
                $items->execute([$order['transaction_id']]);
                $orderItems = $items->fetchAll();
                ?>
                <tr>
                    <td><strong style="color: var(--primary);"><?= $order['order_number'] ?></strong></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td>
                        <span class="status-badge" style="background: var(--info-pale); color: var(--info);">
                            <?= htmlspecialchars($order['channel_name']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($order['method_name']) ?></td>
                    <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                    <td>
                        <?= date('M j, Y', strtotime($order['transaction_date'])) ?><br>
                        <small style="color: var(--text-muted);"><?= date('h:i A', strtotime($order['transaction_date'])) ?></small>
                    </td>
                    <td><?= htmlspecialchars($order['staff_name']) ?></td>
                    <td>
                        <div style="font-size: 13px;">
                            <?php foreach($orderItems as $item): ?>
                            <div>• <?= htmlspecialchars($item['item_name']) ?> - <?= htmlspecialchars($item['flavor_name']) ?> (<?= htmlspecialchars($item['size_name']) ?>) x<?= $item['quantity'] ?></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="btn-success" style="font-size: 12px; padding: 6px 12px;" 
                                        onclick="return confirm('Confirm this order? Customer has completed payment?')">
                                    <i class="fas fa-check"></i> Confirm
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn-danger" style="font-size: 12px; padding: 6px 12px;"
                                        onclick="return confirm('Cancel this order? This action cannot be undone.')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-clock" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.4;"></i>
            <h4 style="margin-bottom: 8px; color: var(--text-dark);">No Pending Orders</h4>
            <p>All orders have been processed. New orders will appear here for payment confirmation.</p>
            <a href="new_order.php" class="btn-primary" style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Create New Order
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if(count($pending) > 0): ?>
    <div style="background: var(--warning-pale); border-radius: 14px; padding: 16px; margin-top: 20px; border-left: 4px solid var(--warning);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-info-circle" style="color: var(--warning); font-size: 18px;"></i>
            <div>
                <strong style="color: var(--text-dark);">Payment Confirmation Required</strong>
                <p style="margin: 4px 0 0; color: var(--text-body); font-size: 14px;">
                    These orders are waiting for payment confirmation. Once the customer completes payment, click "Confirm" to move the order to completed status. 
                    If payment is not received, click "Cancel" to remove the order and free up the system for other customers.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.btn-success {
    background: var(--success);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: var(--success-dark);
    transform: translateY(-1px);
}

.btn-danger {
    background: var(--error);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-danger:hover {
    background: var(--error-dark);
    transform: translateY(-1px);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>