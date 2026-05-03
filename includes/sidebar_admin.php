<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/images/logo.jpg" alt="Cremoso">
        <h3>Cremoso Admin</h3>
        <p>Sales & Transaction System</p>
    </div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> <span>Dashboard</span>
        </a>
        <a href="items.php" class="<?= $current_page == 'items.php' ? 'active' : '' ?>">
            <i class="fas fa-ice-cream"></i> <span>Menu Items</span>
        </a>
        <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> <span>Reports</span>
        </a>
        <a href="analytics.php" class="<?= $current_page == 'analytics.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> <span>Analytics</span>
        </a>
        <a href="transactions.php" class="<?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> <span>Transactions</span>
        </a>
    </div>
</div>