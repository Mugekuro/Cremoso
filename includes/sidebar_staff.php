<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="/cremoso_system/assets/images/logo.jpg" alt="Cremoso">
        <h3>Cremoso Staff</h3>
        <p>Sales & Transaction System</p>
    </div>
    <div class="sidebar-nav">
        <a href="/cremoso_system/staff/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
        <a href="/cremoso_system/staff/profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> <span>Profile</span>
        </a>
        <a href="/cremoso_system/staff/new_order.php" class="<?= $current_page == 'new_order.php' ? 'active' : '' ?>">
            <i class="fas fa-cart-plus"></i> <span>New Order</span>
        </a>
        <a href="/cremoso_system/staff/daily_log.php" class="<?= $current_page == 'daily_log.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i> <span>Daily Log</span>
        </a>
        <hr style="margin: 16px 24px; border-color: rgba(255,255,255,0.1);">
        <a href="/cremoso_system/logout.php">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>