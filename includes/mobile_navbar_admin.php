<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="mobile-navbar">
    <div class="mobile-navbar-scroll">
        <a href="dashboard.php" class="mobile-nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="items.php" class="mobile-nav-item <?= $current_page == 'items.php' || $current_page == 'add_item.php' || $current_page == 'edit_item.php' || $current_page == 'menu_management.php' ? 'active' : '' ?>">
            <i class="fas fa-ice-cream"></i>
            <span>Menu</span>
        </a>
        <a href="reports.php" class="mobile-nav-item <?= $current_page == 'reports.php' || $current_page == 'daily_detail.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Reports</span>
        </a>
        <a href="analytics.php" class="mobile-nav-item <?= $current_page == 'analytics.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Analytics</span>
        </a>
        <a href="transactions.php" class="mobile-nav-item <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i>
            <span>Transactions</span>
        </a>
        <a href="staff_management.php" class="mobile-nav-item <?= $current_page == 'staff_management.php' ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i>
            <span>Staff</span>
        </a>
        <a href="branch_management.php" class="mobile-nav-item <?= $current_page == 'branch_management.php' ? 'active' : '' ?>">
            <i class="fas fa-code-branch"></i>
            <span>Branches</span>
        </a>
        <a href="profile.php" class="mobile-nav-item <?= $current_page == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</nav>
