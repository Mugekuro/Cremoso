<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$branch_id = $_SESSION['branch_id'];
$today = date('Y-m-d');

// Today's stats (only confirmed orders - with fallback for missing status column)
try {
    $todayStats = $pdo->prepare("SELECT COUNT(*) as orders, SUM(total_amount) as revenue FROM transactions WHERE DATE(transaction_date) = ? AND branch_id = ? AND status = 'confirmed'");
    $todayStats->execute([$today, $branch_id]);
    $todayData = $todayStats->fetch();
} catch (PDOException $e) {
    // Fallback if status column doesn't exist yet
    $todayStats = $pdo->prepare("SELECT COUNT(*) as orders, SUM(total_amount) as revenue FROM transactions WHERE DATE(transaction_date) = ? AND branch_id = ?");
    $todayStats->execute([$today, $branch_id]);
    $todayData = $todayStats->fetch();
}

// Recent orders (only confirmed ones - with fallback)
try {
    $recentOrders = $pdo->prepare("SELECT t.*, c.customer_name, pm.method_name
                                   FROM transactions t
                                   JOIN customers c ON t.customer_id = c.customer_id
                                   JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                                   WHERE t.branch_id = ? AND t.status = 'confirmed'
                                   ORDER BY t.transaction_date DESC LIMIT 5");
    $recentOrders->execute([$branch_id]);
    $recent = $recentOrders->fetchAll();
} catch (PDOException $e) {
    // Fallback if status column doesn't exist yet
    $recentOrders = $pdo->prepare("SELECT t.*, c.customer_name, pm.method_name
                                   FROM transactions t
                                   JOIN customers c ON t.customer_id = c.customer_id
                                   JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                                   WHERE t.branch_id = ?
                                   ORDER BY t.transaction_date DESC LIMIT 5");
    $recentOrders->execute([$branch_id]);
    $recent = $recentOrders->fetchAll();
}

// Total orders this month (only confirmed - with fallback)
try {
    $monthOrders = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND branch_id = ? AND status = 'confirmed'");
    $monthOrders->execute([$branch_id]);
    $monthlyOrders = $monthOrders->fetchColumn();
} catch (PDOException $e) {
    $monthOrders = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND branch_id = ?");
    $monthOrders->execute([$branch_id]);
    $monthlyOrders = $monthOrders->fetchColumn();
}

// Monthly revenue (only confirmed - with fallback)
try {
    $monthRevenue = $pdo->prepare("SELECT SUM(total_amount) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND branch_id = ? AND status = 'confirmed'");
    $monthRevenue->execute([$branch_id]);
    $monthlyRevenue = $monthRevenue->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $monthRevenue = $pdo->prepare("SELECT SUM(total_amount) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND branch_id = ?");
    $monthRevenue->execute([$branch_id]);
    $monthlyRevenue = $monthRevenue->fetchColumn() ?: 0;
}

// Get branch name
$branch = $pdo->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
$branch->execute([$branch_id]);
$branchName = $branch->fetchColumn();

// Get pending orders count (with fallback)
try {
    $pendingCount = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE branch_id = ? AND status = 'pending'");
    $pendingCount->execute([$branch_id]);
    $pendingOrders = $pendingCount->fetchColumn();
} catch (PDOException $e) {
    // If status column doesn't exist, show 0 pending orders
    $pendingOrders = 0;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_staff.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-home"></i> Staff Dashboard</h1>
        <div class="user-info">
            <i class="fas fa-store"></i>
            <span><?= htmlspecialchars($branchName) ?></span>
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <span class="branch-badge">Staff</span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value"><?= $todayData['orders'] ?? 0 ?></div>
            <div class="stat-label">Today's Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($todayData['revenue'] ?? 0, 2) ?></div>
            <div class="stat-label">Today's Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value"><?= $monthlyOrders ?></div>
            <div class="stat-label">Orders This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">
                <a href="pending_orders.php" style="color: <?= $pendingOrders > 0 ? 'var(--warning)' : 'var(--primary)' ?>; text-decoration: none;">
                    <?= $pendingOrders ?>
                </a>
            </div>
            <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-plus-circle"></i></div>
            <div class="stat-value">
                <a href="new_order.php" style="color: var(--primary); text-decoration: none;">New Order</a>
            </div>
            <div class="stat-label">Click to create order</div>
        </div>
    </div>

    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent as $r): ?>
                <tr>
                    <td><strong><?= $r['order_number'] ?></strong></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($r['method_name']) ?></td>
                    <td><?= date('h:i A', strtotime($r['transaction_date'])) ?></td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($recent) == 0): ?>
                <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-shopping-cart" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No orders yet today
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>