<?php
$current_page = basename($_SERVER['PHP_SELF']);

$pendingCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE branch_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['branch_id']]);
    $pendingCount = $stmt->fetchColumn();
} catch (PDOException $e) {}
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/images/logo.jpg" alt="Cremoso">
        <h3>Cremoso Staff</h3>
        <p>Sales & Transaction System</p>
    </div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
        <a href="new_order.php" class="<?= $current_page == 'new_order.php' ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i> <span>New Order</span>
        </a>
        <a href="pending_orders.php" class="<?= $current_page == 'pending_orders.php' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i>
            <span>Pending Orders</span>
            <?php if($pendingCount > 0): ?>
            <span class="notification-badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="daily_log.php" class="<?= $current_page == 'daily_log.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i> <span>Daily Log</span>
        </a>
        <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> <span>Reports</span>
        </a>
    </div>
</div>
