<?php
// Get branch name
$branch = $pdo->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
$branch->execute([$_SESSION['branch_id']]);
$branchName = $branch->fetchColumn();

// Pending count for badge
$pendingCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE branch_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['branch_id']]);
    $pendingCount = $stmt->fetchColumn();
} catch (PDOException $e) {}
?>
<nav class="topnav">
    <div class="topnav-left">
        <button class="topnav-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <span class="topnav-branch"><i class="fas fa-store"></i> <?= htmlspecialchars($branchName) ?></span>
    </div>
    <div class="topnav-right">
        <?php if($pendingCount > 0): ?>
        <a href="pending_orders.php" class="topnav-pending-pill">
            <i class="fas fa-clock"></i> <?= $pendingCount ?> Pending
        </a>
        <?php endif; ?>
        <div class="topnav-dropdown" id="userDropdown">
            <button class="topnav-user-btn" id="userDropdownBtn">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                <i class="fas fa-chevron-down" style="font-size:11px; opacity:0.7;"></i>
            </button>
            <div class="topnav-dropdown-menu" id="userDropdownMenu">
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="../logout.php" class="logout-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', function(e) {
    e.stopPropagation();
    document.querySelector('.sidebar').classList.toggle('open');
});
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (sidebar && window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// User dropdown toggle
document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('userDropdownMenu').classList.toggle('open');
});
document.addEventListener('click', function() {
    document.getElementById('userDropdownMenu').classList.remove('open');
});
</script>
