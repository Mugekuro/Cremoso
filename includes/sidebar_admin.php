<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="/cremoso_system/assets/images/logo.jpg" alt="Cremoso">
        <h3>Cremoso Admin</h3>
        <p>Sales & Transaction System</p>
    </div>
    <div class="sidebar-nav">
        <a href="/cremoso_system/admin/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
        </a>
        <a href="/cremoso_system/admin/profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> <span>Profile</span>
        </a>
        <a href="/cremoso_system/admin/items.php" class="<?= $current_page == 'items.php' ? 'active' : '' ?>">
            <i class="fas fa-ice-cream"></i> <span>Menu Items</span>
        </a>
        <a href="/cremoso_system/admin/reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> <span>Reports</span>
        </a>
        <a href="/cremoso_system/admin/analytics.php" class="<?= $current_page == 'analytics.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> <span>Analytics</span>
        </a>
        <a href="/cremoso_system/admin/transactions.php" class="<?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> <span>Transactions</span>
        </a>
        <hr style="margin: 16px 24px; border-color: rgba(255,255,255,0.1);">
        <a href="/cremoso_system/logout.php">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>