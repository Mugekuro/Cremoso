<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>
<div class="sidebar">
    <h4 class="text-center py-3">🍦 Cremoso</h4>
    <hr>
    <?php if ($role == 'admin'): ?>
        <a href="/cremoso_system/admin/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="/cremoso_system/admin/transactions.php" class="nav-link <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> Transactions
        </a>
        <a href="/cremoso_system/admin/reports.php" class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Reports
        </a>
        <a href="/cremoso_system/admin/items.php" class="nav-link <?= $current_page == 'items.php' ? 'active' : '' ?>">
            <i class="fas fa-ice-cream"></i> Manage Items
        </a>
    <?php elseif ($role == 'staff'): ?>
        <a href="/cremoso_system/staff/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/cremoso_system/staff/new_order.php" class="nav-link <?= $current_page == 'new_order.php' ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i> New Order
        </a>
        <a href="/cremoso_system/staff/daily_log.php" class="nav-link <?= $current_page == 'daily_log.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i> Daily Log
        </a>
    <?php endif; ?>
    <hr>
    <a href="/cremoso_system/logout.php" class="nav-link text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>