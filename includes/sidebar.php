<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>
<div class="sidebar">
    <h4 class="text-center py-3">🍦 Cremoso</h4>
    <hr>
    <?php if ($role == 'admin'): ?>
        <a href="/admin/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="/admin/transactions.php" class="nav-link <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> Transactions
        </a>
        <a href="/admin/reports.php" class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Reports
        </a>
        <a href="/admin/items.php" class="nav-link <?= $current_page == 'items.php' ? 'active' : '' ?>">
            <i class="fas fa-ice-cream"></i> Manage Items
        </a>
    <?php elseif ($role == 'staff'): ?>
        <a href="/staff/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/staff/new_order.php" class="nav-link <?= $current_page == 'new_order.php' ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i> New Order
        </a>
        <a href="/staff/daily_log.php" class="nav-link <?= $current_page == 'daily_log.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i> Daily Log
        </a>
        <a href="/staff/reports.php" class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Reports
        </a>
    <?php endif; ?>
    <hr>
    <a href="/logout.php" class="nav-link text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>