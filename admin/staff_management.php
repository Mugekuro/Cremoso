<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Make sure $pdo is available
global $pdo;

$success = '';
$error = '';

// Handle staff confirmation/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_staff'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET is_confirmed = TRUE WHERE user_id = ? AND role = 'staff'");
        if ($stmt->execute([$userId])) {
            $success = 'Staff account confirmed successfully!';
        } else {
            $error = 'Failed to confirm staff account.';
        }
    } elseif (isset($_POST['reject_staff'])) {
        $userId = (int)$_POST['user_id'];
        // Delete the unconfirmed staff account
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'staff' AND is_confirmed = FALSE");
        if ($stmt->execute([$userId])) {
            $success = 'Staff account rejected and removed successfully!';
        } else {
            $error = 'Failed to reject staff account.';
        }
    } elseif (isset($_POST['revoke_staff'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET is_confirmed = FALSE WHERE user_id = ? AND role = 'staff'");
        if ($stmt->execute([$userId])) {
            $success = 'Staff access revoked successfully!';
        } else {
            $error = 'Failed to revoke staff access.';
        }
    }
}

// Fetch pending staff (unconfirmed)
$pendingStmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.fullname, u.created_at, b.branch_name
    FROM users u
    LEFT JOIN branches b ON u.branch_id = b.branch_id
    WHERE u.role = 'staff' AND u.is_confirmed = FALSE
    ORDER BY u.created_at DESC
");
$pendingStmt->execute();
$pendingStaff = $pendingStmt->fetchAll();

// Fetch confirmed staff
$confirmedStmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.fullname, u.created_at, b.branch_name
    FROM users u
    LEFT JOIN branches b ON u.branch_id = b.branch_id
    WHERE u.role = 'staff' AND u.is_confirmed = TRUE
    ORDER BY u.created_at DESC
");
$confirmedStmt->execute();
$confirmedStaff = $confirmedStmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-check"></i> Staff Management</h1>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 8px;">
            Approve or reject staff account registrations
        </p>
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

    <!-- Pending Approvals -->
    <div class="data-table" style="margin-bottom: 32px;">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface);">
            <h3 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-clock" style="color: var(--warning);"></i>
                Pending Approvals
                <?php if (count($pendingStaff) > 0): ?>
                <span class="badge-warning" style="font-size: 13px; padding: 4px 12px; border-radius: 20px; background: var(--warning); color: white; font-weight: 600;">
                    <?= count($pendingStaff) ?>
                </span>
                <?php endif; ?>
            </h3>
        </div>
        
        <?php if (count($pendingStaff) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    
                    <th>Branch</th>
                    <th>Registration Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingStaff as $staff): ?>
                <tr>
                    <td>
                        <strong style="color: var(--text-dark);">
                            <i class="fas fa-user" style="color: var(--text-muted); margin-right: 6px;"></i>
                            <?= htmlspecialchars($staff['username']) ?>
                        </strong>
                    </td>
                    <td><?= htmlspecialchars($staff['fullname']) ?></td>
                    
                    <td>
                        <span class="badge-info">
                            <?= htmlspecialchars($staff['branch_name']) ?>
                        </span>
                    </td>
                    <td>
                        <i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= date('M j, Y g:i A', strtotime($staff['created_at'])) ?>
                    </td>
                    <td style="text-align: center;">
                        <form method="POST" style="display: inline-block; margin-right: 8px;">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="submit" name="confirm_staff" class="btn-success" 
                                    onclick="return confirm('Confirm this staff account?')"
                                    style="padding: 8px 16px; font-size: 13px;">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="submit" name="reject_staff" class="btn-danger" 
                                    onclick="return confirm('Reject and delete this staff account? This action cannot be undone.')"
                                    style="padding: 8px 16px; font-size: 13px;">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
            <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 16px;"></i>
            <p style="font-size: 16px; margin: 0;">No pending staff approvals</p>
            <p style="font-size: 13px; margin-top: 8px;">All staff accounts have been reviewed</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Confirmed Staff -->
    <div class="data-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface);">
            <h3 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-check" style="color: var(--success);"></i>
                Confirmed Staff
                <span class="badge-success" style="font-size: 13px; padding: 4px 12px; border-radius: 20px; background: var(--success); color: white; font-weight: 600;">
                    <?= count($confirmedStaff) ?>
                </span>
            </h3>
        </div>
        
        <?php if (count($confirmedStaff) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    
                    <th>Branch</th>
                    <th>Registration Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($confirmedStaff as $staff): ?>
                <tr>
                    <td>
                        <strong style="color: var(--text-dark);">
                            <i class="fas fa-user" style="color: var(--text-muted); margin-right: 6px;"></i>
                            <?= htmlspecialchars($staff['username']) ?>
                        </strong>
                    </td>
                    <td><?= htmlspecialchars($staff['fullname']) ?></td>
                    
                    <td>
                        <span class="badge-info">
                            <?= htmlspecialchars($staff['branch_name']) ?>
                        </span>
                    </td>
                    <td>
                        <i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= date('M j, Y g:i A', strtotime($staff['created_at'])) ?>
                    </td>
                    <td style="text-align: center;">
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="submit" name="revoke_staff" class="btn-warning" 
                                    onclick="return confirm('Revoke access for this staff member? They will not be able to log in until re-approved.')"
                                    style="padding: 8px 16px; font-size: 13px;">
                                <i class="fas fa-ban"></i> Revoke Access
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
            <i class="fas fa-users" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
            <p style="font-size: 16px; margin: 0;">No confirmed staff members</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge-warning {
    background: var(--warning);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: var(--success);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-info {
    background: var(--primary-pale);
    color: var(--primary-dark);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.btn-success {
    background: var(--success);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-success:hover {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
}

.btn-danger {
    background: var(--danger);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.btn-warning {
    background: var(--warning);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-warning:hover {
    background: #d68910;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

