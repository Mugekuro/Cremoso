<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Make sure $pdo is available
global $pdo;

$success = '';
$error = '';

// Handle branch operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_branch'])) {
        $branchName = trim($_POST['branch_name']);
        $location = trim($_POST['location']);
        
        if (empty($branchName) || empty($location)) {
            $error = 'Branch name and location are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO branches (branch_name, location, is_active) VALUES (?, ?, TRUE)");
            if ($stmt->execute([$branchName, $location])) {
                $success = 'Branch added successfully!';
            } else {
                $error = 'Failed to add branch.';
            }
        }
    } elseif (isset($_POST['update_branch'])) {
        $branchId = (int)$_POST['branch_id'];
        $branchName = trim($_POST['branch_name']);
        $location = trim($_POST['location']);
        
        if (empty($branchName) || empty($location)) {
            $error = 'Branch name and location are required.';
        } else {
            $stmt = $pdo->prepare("UPDATE branches SET branch_name = ?, location = ? WHERE branch_id = ?");
            if ($stmt->execute([$branchName, $location, $branchId])) {
                $success = 'Branch updated successfully!';
            } else {
                $error = 'Failed to update branch.';
            }
        }
    } elseif (isset($_POST['toggle_status'])) {
        $branchId = (int)$_POST['branch_id'];
        $newStatus = (int)$_POST['new_status'];
        
        $stmt = $pdo->prepare("UPDATE branches SET is_active = ? WHERE branch_id = ?");
        if ($stmt->execute([$newStatus, $branchId])) {
            $success = $newStatus ? 'Branch activated successfully!' : 'Branch deactivated successfully!';
        } else {
            $error = 'Failed to update branch status.';
        }
    } elseif (isset($_POST['delete_branch'])) {
        $branchId = (int)$_POST['branch_id'];
        
        // Check if branch has associated staff
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE branch_id = ?");
        $checkStmt->execute([$branchId]);
        $staffCount = $checkStmt->fetchColumn();
        
        if ($staffCount > 0) {
            $error = "Cannot delete branch. It has {$staffCount} staff member(s) assigned to it.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM branches WHERE branch_id = ?");
            if ($stmt->execute([$branchId])) {
                $success = 'Branch deleted successfully!';
            } else {
                $error = 'Failed to delete branch.';
            }
        }
    }
}

// Fetch all branches
$branchesStmt = $pdo->prepare("
    SELECT 
        b.branch_id, 
        b.branch_name, 
        b.location, 
        b.is_active, 
        b.created_at,
        COUNT(u.user_id) as staff_count
    FROM branches b
    LEFT JOIN users u ON b.branch_id = u.branch_id AND u.role = 'staff' AND u.is_confirmed = TRUE
    GROUP BY b.branch_id
    ORDER BY b.created_at DESC
");
$branchesStmt->execute();
$branches = $branchesStmt->fetchAll();

// Separate active and inactive branches
$activeBranches = array_filter($branches, fn($b) => $b['is_active']);
$inactiveBranches = array_filter($branches, fn($b) => !$b['is_active']);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin.css">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>
<?php include __DIR__ . '/../includes/mobile_navbar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-code-branch"></i> Branch Management</h1>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 8px;">
            Manage branch locations and their status
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

    <!-- Add New Branch -->
    <div class="data-table" style="margin-bottom: 32px;">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface);">
            <h3 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
                Add New Branch
            </h3>
        </div>
        <div style="padding: 24px;">
            <form method="POST" style="max-width: 600px;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-weight: 500;">
                        <i class="fas fa-store" style="color: var(--text-muted); margin-right: 6px;"></i>
                        Branch Name
                    </label>
                    <input type="text" name="branch_name" required 
                           placeholder="e.g., Cremoso Main Branch"
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-weight: 500;">
                        <i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 6px;"></i>
                        Location
                    </label>
                    <input type="text" name="location" required 
                           placeholder="e.g., Malaybalay City"
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <button type="submit" name="add_branch" class="btn-primary" style="padding: 12px 24px;">
                    <i class="fas fa-plus"></i> Add Branch
                </button>
            </form>
        </div>
    </div>

    <!-- Active Branches -->
    <div class="data-table" style="margin-bottom: 32px;">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface);">
            <h3 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                Active Branches
                <span class="badge-success" style="font-size: 13px; padding: 4px 12px; border-radius: 20px; background: var(--success); color: white; font-weight: 600;">
                    <?= count($activeBranches) ?>
                </span>
            </h3>
        </div>
        
        <?php if (count($activeBranches) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Staff Count</th>
                    <th>Created Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeBranches as $branch): ?>
                <tr>
                    <td>
                        <strong style="color: var(--text-dark);">
                            <i class="fas fa-store" style="color: var(--text-muted); margin-right: 6px;"></i>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </strong>
                    </td>
                    <td>
                        <i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= htmlspecialchars($branch['location']) ?>
                    </td>
                    <td>
                        <span class="badge-info" style="padding: 6px 12px;">
                            <i class="fas fa-users" style="margin-right: 4px;"></i>
                            <?= $branch['staff_count'] ?> staff
                        </span>
                    </td>
                    <td>
                        <i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= date('M j, Y', strtotime($branch['created_at'])) ?>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-primary" 
                                onclick="showEditModal(<?= $branch['branch_id'] ?>, '<?= htmlspecialchars($branch['branch_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($branch['location'], ENT_QUOTES) ?>')"
                                style="padding: 8px 16px; font-size: 13px; margin-right: 8px;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn-warning" 
                                onclick="showToggleModal(<?= $branch['branch_id'] ?>, 0)"
                                style="padding: 8px 16px; font-size: 13px; margin-right: 8px;">
                            <i class="fas fa-ban"></i> Deactivate
                        </button>
                        <button type="button" class="btn-danger" 
                                onclick="showDeleteModal(<?= $branch['branch_id'] ?>, '<?= htmlspecialchars($branch['branch_name'], ENT_QUOTES) ?>', <?= $branch['staff_count'] ?>)"
                                style="padding: 8px 16px; font-size: 13px;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 48px 20px; text-align: center; color: var(--text-muted);">
            <i class="fas fa-store-slash" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
            <p style="font-size: 16px; margin: 0;">No active branches</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Inactive Branches -->
    <?php if (count($inactiveBranches) > 0): ?>
    <div class="data-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border); background: var(--surface);">
            <h3 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-times-circle" style="color: var(--error);"></i>
                Inactive Branches
                <span class="badge-error" style="font-size: 13px; padding: 4px 12px; border-radius: 20px; background: var(--error); color: white; font-weight: 600;">
                    <?= count($inactiveBranches) ?>
                </span>
            </h3>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Staff Count</th>
                    <th>Created Date</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inactiveBranches as $branch): ?>
                <tr style="opacity: 0.7;">
                    <td>
                        <strong style="color: var(--text-dark);">
                            <i class="fas fa-store" style="color: var(--text-muted); margin-right: 6px;"></i>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </strong>
                    </td>
                    <td>
                        <i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= htmlspecialchars($branch['location']) ?>
                    </td>
                    <td>
                        <span class="badge-info" style="padding: 6px 12px;">
                            <i class="fas fa-users" style="margin-right: 4px;"></i>
                            <?= $branch['staff_count'] ?> staff
                        </span>
                    </td>
                    <td>
                        <i class="fas fa-calendar" style="color: var(--text-muted); margin-right: 6px;"></i>
                        <?= date('M j, Y', strtotime($branch['created_at'])) ?>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-success" 
                                onclick="showToggleModal(<?= $branch['branch_id'] ?>, 1)"
                                style="padding: 8px 16px; font-size: 13px; margin-right: 8px;">
                            <i class="fas fa-check"></i> Activate
                        </button>
                        <button type="button" class="btn-danger" 
                                onclick="showDeleteModal(<?= $branch['branch_id'] ?>, '<?= htmlspecialchars($branch['branch_name'], ENT_QUOTES) ?>', <?= $branch['staff_count'] ?>)"
                                style="padding: 8px 16px; font-size: 13px;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Branch Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content" style="max-width: 500px;">
        <div style="padding: 24px; border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: var(--text-dark);">
                <i class="fas fa-edit" style="color: var(--primary);"></i>
                Edit Branch
            </h3>
        </div>
        <form method="POST" id="editForm">
            <div style="padding: 24px;">
                <input type="hidden" name="branch_id" id="edit_branch_id">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-weight: 500;">
                        <i class="fas fa-store" style="color: var(--text-muted); margin-right: 6px;"></i>
                        Branch Name
                    </label>
                    <input type="text" name="branch_name" id="edit_branch_name" required 
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--text-dark); font-weight: 500;">
                        <i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 6px;"></i>
                        Location
                    </label>
                    <input type="text" name="location" id="edit_location" required 
                           style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
            </div>
            <div style="padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_branch" class="btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal-overlay" id="toggleModal">
    <div class="modal-content confirm-modal">
        <div class="confirm-modal-icon" id="toggleIcon">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 id="toggleTitle">Confirm Action</h3>
        <p id="toggleMessage">Are you sure you want to proceed?</p>
        <form method="POST" id="toggleForm">
            <input type="hidden" name="branch_id" id="toggle_branch_id">
            <input type="hidden" name="new_status" id="toggle_new_status">
            <div class="confirm-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeToggleModal()">Cancel</button>
                <button type="submit" name="toggle_status" class="btn-primary" id="toggleConfirmBtn">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Branch Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content confirm-modal">
        <div class="confirm-modal-icon" style="background: rgba(231, 76, 60, 0.1);">
            <i class="fas fa-exclamation-triangle" style="color: var(--error);"></i>
        </div>
        <h3>Delete Branch?</h3>
        <p id="deleteMessage">This action cannot be undone.</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="branch_id" id="delete_branch_id">
            <div class="confirm-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" name="delete_branch" class="btn-danger" id="deleteConfirmBtn">
                    <i class="fas fa-trash"></i> Delete Branch
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Edit Modal Functions
function showEditModal(branchId, branchName, location) {
    document.getElementById('edit_branch_id').value = branchId;
    document.getElementById('edit_branch_name').value = branchName;
    document.getElementById('edit_location').value = location;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Toggle Status Modal Functions
function showToggleModal(branchId, newStatus) {
    const icon = document.getElementById('toggleIcon');
    const title = document.getElementById('toggleTitle');
    const message = document.getElementById('toggleMessage');
    const confirmBtn = document.getElementById('toggleConfirmBtn');
    
    document.getElementById('toggle_branch_id').value = branchId;
    document.getElementById('toggle_new_status').value = newStatus;
    
    if (newStatus === 1) {
        icon.style.background = 'rgba(46, 204, 113, 0.1)';
        icon.innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i>';
        title.textContent = 'Activate Branch?';
        message.textContent = 'This branch will be available for staff assignment and operations.';
        confirmBtn.className = 'btn-success';
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Activate';
    } else {
        icon.style.background = 'rgba(255, 193, 7, 0.1)';
        icon.innerHTML = '<i class="fas fa-ban" style="color: var(--warning);"></i>';
        title.textContent = 'Deactivate Branch?';
        message.textContent = 'This branch will be hidden from staff assignment but existing staff will remain assigned.';
        confirmBtn.className = 'btn-warning';
        confirmBtn.innerHTML = '<i class="fas fa-ban"></i> Deactivate';
    }
    
    document.getElementById('toggleModal').classList.add('active');
}

function closeToggleModal() {
    document.getElementById('toggleModal').classList.remove('active');
}

// Delete Modal Functions
function showDeleteModal(branchId, branchName, staffCount) {
    const message = document.getElementById('deleteMessage');
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    
    document.getElementById('delete_branch_id').value = branchId;
    
    if (staffCount > 0) {
        message.innerHTML = `<strong style="color: var(--error);">Cannot delete "${branchName}"</strong><br>This branch has ${staffCount} staff member(s) assigned to it. Please reassign or remove staff first.`;
        confirmBtn.disabled = true;
        confirmBtn.style.opacity = '0.5';
        confirmBtn.style.cursor = 'not-allowed';
    } else {
        message.innerHTML = `Are you sure you want to delete <strong>"${branchName}"</strong>?<br>This action cannot be undone.`;
        confirmBtn.disabled = false;
        confirmBtn.style.opacity = '1';
        confirmBtn.style.cursor = 'pointer';
    }
    
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Close modals when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
