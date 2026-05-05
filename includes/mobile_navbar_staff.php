<?php
$current_page = basename($_SERVER['PHP_SELF']);

$pendingCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE branch_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['branch_id']]);
    $pendingCount = $stmt->fetchColumn();
} catch (PDOException $e) {}
?>
<nav class="mobile-navbar">
    <div class="mobile-navbar-scroll">
        <a href="dashboard.php" class="mobile-nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="new_order.php" class="mobile-nav-item <?= $current_page == 'new_order.php' ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i>
            <span>New Order</span>
        </a>
        <a href="pending_orders.php" class="mobile-nav-item <?= $current_page == 'pending_orders.php' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i>
            <span>Pending<?php if($pendingCount > 0): ?> (<?= $pendingCount ?>)<?php endif; ?></span>
        </a>
        <a href="daily_log.php" class="mobile-nav-item <?= $current_page == 'daily_log.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i>
            <span>Daily Log</span>
        </a>
        <a href="reports.php" class="mobile-nav-item <?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Reports</span>
        </a>
        <a href="profile.php" class="mobile-nav-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        <a href="../logout.php" class="mobile-nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>
