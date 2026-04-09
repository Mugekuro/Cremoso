<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Fetch current user data
$stmt = $pdo->prepare("SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.branch_id WHERE u.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);

    if (empty($fullname) || empty($username)) {
        $error = 'All fields are required.';
    } else {
        // Check if username is already taken by another user
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
        $checkStmt->execute([$username, $_SESSION['user_id']]);
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'Username is already taken.';
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ? WHERE user_id = ?");
            if ($updateStmt->execute([$fullname, $username, $_SESSION['user_id']])) {
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;

                // Refresh user data
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();

                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif ($current_password !== $user['password']) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if ($updateStmt->execute([$new_password, $_SESSION['user_id']])) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <div class="user-info">
            <i class="fas fa-calendar-day"></i>
            <span><?= date('l, F j, Y') ?></span>
            <span class="branch-badge">Admin</span>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 8px;">
        <!-- Profile Information -->
        <div class="profile-card" style="max-width: 100%;">
            <h3 style="color: var(--text-dark); margin-bottom: 24px; font-weight: 700;">
                <i class="fas fa-id-card" style="color: var(--primary); margin-right: 8px;"></i>
                Profile Information
            </h3>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username"><i class="fas fa-at" style="color: var(--text-muted); margin-right: 6px;"></i>Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="fullname"><i class="fas fa-user" style="color: var(--text-muted); margin-right: 6px;"></i>Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-shield-alt" style="color: var(--text-muted); margin-right: 6px;"></i>Role</label>
                    <input type="text" value="<?= ucfirst($user['role']) ?>" disabled style="background: var(--primary-pale); cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-store" style="color: var(--text-muted); margin-right: 6px;"></i>Assigned Branch</label>
                    <input type="text" value="<?= $user['branch_name'] ? htmlspecialchars($user['branch_name']) : 'All Branches (Admin)' ?>" disabled style="background: var(--primary-pale); cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-clock" style="color: var(--text-muted); margin-right: 6px;"></i>Account Created</label>
                    <input type="text" value="<?= date('F j, Y g:i A', strtotime($user['created_at'])) ?>" disabled style="background: var(--primary-pale); cursor: not-allowed;">
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card" style="max-width: 100%;">
            <h3 style="color: var(--text-dark); margin-bottom: 24px; font-weight: 700;">
                <i class="fas fa-key" style="color: var(--primary); margin-right: 8px;"></i>
                Change Password
            </h3>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password"><i class="fas fa-lock" style="color: var(--text-muted); margin-right: 6px;"></i>Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password"><i class="fas fa-lock" style="color: var(--text-muted); margin-right: 6px;"></i>New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock" style="color: var(--text-muted); margin-right: 6px;"></i>Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" name="change_password" class="btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>

            <!-- Account Security Info -->
            <div style="margin-top: 32px; padding: 20px; background: var(--primary-pale); border-radius: 14px; border: 1px solid var(--border);">
                <h4 style="color: var(--text-dark); margin-bottom: 12px; font-size: 15px; font-weight: 700;">
                    <i class="fas fa-shield-alt" style="color: var(--primary-dark); margin-right: 8px;"></i>
                    Security Tips
                </h4>
                <ul style="color: var(--text-body); font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
                    <li>Use at least 6 characters for your password</li>
                    <li>Include a mix of letters, numbers, and symbols</li>
                    <li>Avoid using common words or personal information</li>
                    <li>Change your password regularly</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="data-table" style="margin-top: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-chart-pie" style="color: var(--primary); margin-right: 8px;"></i>
            Account Activity Summary
        </h3>
        <div style="padding: 24px;">
            <?php
            $totalTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
            $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
            $todayTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = {$_SESSION['user_id']} AND DATE(transaction_date) = CURDATE()")->fetchColumn();
            $monthTransactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = {$_SESSION['user_id']} AND MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())")->fetchColumn();
            ?>
            <div class="stats-grid" style="margin-bottom: 0;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-value"><?= number_format($totalTransactions) ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
                    <div class="stat-label">Total Revenue Handled</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-value"><?= number_format($todayTransactions) ?></div>
                    <div class="stat-label">Today's Transactions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="stat-value"><?= number_format($monthTransactions) ?></div>
                    <div class="stat-label">This Month's Transactions</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
