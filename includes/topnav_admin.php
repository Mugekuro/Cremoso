<nav class="topnav">
    <div class="topnav-left">
        <button class="topnav-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <span class="topnav-branch"><i class="fas fa-shield-alt"></i> Admin</span>
    </div>
    <div class="topnav-right">
        <div class="topnav-dropdown" id="userDropdown">
            <button class="topnav-user-btn" id="userDropdownBtn">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                <i class="fas fa-chevron-down" style="font-size:11px; opacity:0.7;"></i>
            </button>
            <div class="topnav-dropdown-menu" id="userDropdownMenu">
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="staff_management.php"><i class="fas fa-user-check"></i> Staffs</a>
                <a href="../logout.php" class="logout-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>
<script>
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
document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('userDropdownMenu').classList.toggle('open');
});
document.addEventListener('click', function() {
    document.getElementById('userDropdownMenu').classList.remove('open');
});
</script>
