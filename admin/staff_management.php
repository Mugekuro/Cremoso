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
<link rel="stylesheet" href="../assets/css/admin.css">
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
                        <form method="POST" style="display: inline-block; margin-right: 8px;" id="confirmForm<?= $staff['user_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="button" class="btn-success" 
                                    onclick="showConfirmModal(<?= $staff['user_id'] ?>)"
                                    style="padding: 8px 16px; font-size: 13px;">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form method="POST" style="display: inline-block;" id="rejectForm<?= $staff['user_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="button" class="btn-danger" 
                                    onclick="showRejectModal(<?= $staff['user_id'] ?>)"
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
                        <form method="POST" style="display: inline-block;" id="revokeForm<?= $staff['user_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $staff['user_id'] ?>">
                            <button type="button" class="btn-warning" 
                                    onclick="showRevokeModal(<?= $staff['user_id'] ?>)"
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



<!-- Confirm Staff Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-content confirm-modal">
        <div class="confirm-modal-icon" style="background: rgba(46, 204, 113, 0.1);">
            <i class="fas fa-check-circle" style="color: var(--success);"></i>
        </div>
        <h3>Confirm Staff Account?</h3>
        <p>This staff member will be granted access to the system and can start processing orders.</p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-success" onclick="confirmStaff()">
                <i class="fas fa-check"></i> Confirm
            </button>
        </div>
    </div>
</div>

<!-- Reject Staff Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal-content confirm-modal">
        <div class="confirm-modal-icon" style="background: rgba(231, 76, 60, 0.1);">
            <i class="fas fa-exclamation-triangle" style="color: var(--error);"></i>
        </div>
        <h3>Reject and Delete Staff Account?</h3>
        <p>This action cannot be undone. The staff account will be permanently removed from the system.</p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="btn-danger" onclick="confirmReject()">
                <i class="fas fa-times"></i> Reject & Delete
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/revoke_modal.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

