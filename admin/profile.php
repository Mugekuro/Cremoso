<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

$user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user->execute([$_SESSION['user_id']]);
$userData = $user->fetch();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <div class="user-info">
            <i class="fas fa-user-shield"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <span class="branch-badge">Admin</span>
        </div>
    </div>

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($userData['fullname']) ?></h3>
                <span class="role-badge"><?= ucfirst($userData['role']) ?></span>
            </div>
        </div>

        <form>
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($userData['username']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" value="<?= htmlspecialchars($userData['fullname']) ?>">
            </div>
            <div class="form-group">
                <label>Email (Contact)</label>
                <input type="email" placeholder="admin@cremoso.com" value="admin@cremoso.com">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" placeholder="+63 XXX XXX XXXX" value="+63 912 345 6789">
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?= ucfirst($userData['role']) ?>" readonly>
            </div>
            <button type="button" class="btn-primary" onclick="alert('Profile update feature coming soon!')">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>