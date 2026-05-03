<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/menu_helpers.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$branch_id = $_SESSION['branch_id'];

// Handle order confirmation/cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $action = $_POST['action'];
    
    if ($action === 'confirm') {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ? AND branch_id = ?");
        $stmt->execute([$transaction_id, $branch_id]);
        $message = "Order confirmed successfully!";
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled' WHERE transaction_id = ? AND branch_id = ?");
        $stmt->execute([$transaction_id, $branch_id]);
        $message = "Order cancelled successfully!";
    }
    
    header("Location: pending_orders.php?msg=" . urlencode($message));
    exit();
}

// Get pending orders
$pendingOrders = $pdo->prepare("SELECT t.*, c.customer_name, pm.method_name, oc.channel_name, u.fullname as staff_name
                                FROM transactions t
                                JOIN customers c ON t.customer_id = c.customer_id
                                JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                                JOIN order_channels oc ON t.channel_id = oc.channel_id
                                JOIN users u ON t.user_id = u.user_id
                                WHERE t.status = 'pending' AND t.branch_id = ?
                                ORDER BY t.transaction_date ASC");
$pendingOrders->execute([$branch_id]);
$pending = $pendingOrders->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/topnav_staff.php';
include __DIR__ . '/../includes/sidebar_staff.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-clock"></i> Pending Orders</h1>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="data-table">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 20px 0;">
            <h3 style="color: var(--text-dark); margin: 0;">Orders Awaiting Payment Confirmation</h3>
            <div style="color: var(--text-muted); font-size: 14px;">
                <i class="fas fa-info-circle"></i> 
                <?= count($pending) ?> pending order<?= count($pending) !== 1 ? 's' : '' ?>
            </div>
        </div>
        
        <?php if(count($pending) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Channel</th>
                    <th>Payment Method</th>
                    <th>Total</th>
                    <th>Created</th>
                    <th>Staff</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pending as $order): ?>
                <?php
                // Get order items from new schema
                $items = $pdo->prepare("SELECT ti.* FROM transaction_items ti WHERE ti.transaction_id = ?");
                $items->execute([$order['transaction_id']]);
                $orderItems = $items->fetchAll();
                ?>
                <tr>
                    <td><strong style="color: var(--primary);"><?= $order['order_number'] ?></strong></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td>
                        <span class="status-badge" style="background: var(--info-pale); color: var(--info);">
                            <?= htmlspecialchars($order['channel_name']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($order['method_name']) ?></td>
                    <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                    <td>
                        <?= date('M j, Y', strtotime($order['transaction_date'])) ?><br>
                        <small style="color: var(--text-muted);"><?= date('h:i A', strtotime($order['transaction_date'])) ?></small>
                    </td>
                    <td><?= htmlspecialchars($order['staff_name']) ?></td>
                    <td>
                        <div style="font-size: 13px;">
                            <?php foreach($orderItems as $item): ?>
                            <?php
                            $displayName = $item['item_name'];
                            $customizations = getTransactionItemCustomizations($item['transaction_item_id']);
                            ?>
                            <div style="margin-bottom: 8px;">
                                <strong>• <?= htmlspecialchars($displayName) ?> x<?= $item['quantity'] ?></strong>
                                <?php 
                                $hasCustomizations = false;
                                foreach($customizations as $type => $items) {
                                    if (!empty($items)) {
                                        $hasCustomizations = true;
                                        break;
                                    }
                                }
                                if ($hasCustomizations): ?>
                                <div style="margin-left: 16px; font-size: 12px; color: var(--text-muted);">
                                    <?php foreach($customizations as $type => $customs): ?>
                                        <?php if (!empty($customs)): ?>
                                        <div>+ <?= ucfirst($type) ?>: <?= implode(', ', array_column($customs, substr($type, 0, -1) . '_name')) ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <form method="POST" id="form-confirm-<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="button" class="btn-success" style="font-size: 12px; padding: 6px 12px;"
                                        onclick="openModal('confirm', <?= $order['transaction_id'] ?>, '<?= addslashes($order['order_number']) ?>', '<?= addslashes(htmlspecialchars($order['customer_name'])) ?>')">
                                    <i class="fas fa-check"></i> Confirm
                                </button>
                            </form>
                            <form method="POST" id="form-cancel-<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="button" class="btn-danger" style="font-size: 12px; padding: 6px 12px;"
                                        onclick="openModal('cancel', <?= $order['transaction_id'] ?>, '<?= addslashes($order['order_number']) ?>', '<?= addslashes(htmlspecialchars($order['customer_name'])) ?>')">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-clock" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.4;"></i>
            <h4 style="margin-bottom: 8px; color: var(--text-dark);">No Pending Orders</h4>
            <p>All orders have been processed. New orders will appear here for payment confirmation.</p>
            <a href="new_order.php" class="btn-primary" style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Create New Order
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if(count($pending) > 0): ?>
    <div style="background: var(--warning-pale); border-radius: 14px; padding: 16px; margin-top: 20px; border-left: 4px solid var(--warning);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-info-circle" style="color: var(--warning); font-size: 18px;"></i>
            <div>
                <strong style="color: var(--text-dark);">Payment Confirmation Required</strong>
                <p style="margin: 4px 0 0; color: var(--text-body); font-size: 14px;">
                    These orders are waiting for payment confirmation. Once the customer completes payment, click "Confirm" to move the order to completed status. 
                    If payment is not received, click "Cancel" to remove the order and free up the system for other customers.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.btn-success {
    background: var(--success);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-success:hover {
    background: var(--success-dark);
    transform: translateY(-1px);
    color: white;
}

.btn-danger {
    background: var(--error);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-danger:hover {
    background: var(--error-dark);
    transform: translateY(-1px);
    color: white;
}

.btn-secondary {
    background: #f0f0f0;
    color: var(--text-dark);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #e0e0e0;
    color: var(--text-dark);
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active { 
    display: flex !important; 
    visibility: visible !important;
}
.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    text-align: center;
    position: relative;
    z-index: 10000;
}
.modal-icon { font-size: 48px; margin-bottom: 16px; }
.modal-title { font-size: 20px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
.modal-body { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.6; }
.modal-order-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13px;
    color: var(--text-body);
    text-align: left;
}
.modal-actions { display: flex; gap: 12px; justify-content: center; }
.modal-actions button { min-width: 110px; }
</style>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="actionModal">
    <div class="modal-box">
        <div class="modal-icon" id="modalIcon"></div>
        <div class="modal-title" id="modalTitle"></div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-order-info" id="modalOrderInfo"></div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal()">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button type="button" id="modalConfirmBtn" style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; color: white; display: inline-flex; align-items: center; gap: 6px;">
            </button>
        </div>
    </div>
</div>

<script>
let pendingFormId = null;

function openModal(type, id, orderNum, customerName) {
    console.log('openModal called:', type, id, orderNum, customerName);
    
    const modal = document.getElementById('actionModal');
    const icon = document.getElementById('modalIcon');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');
    const info = document.getElementById('modalOrderInfo');
    const btn = document.getElementById('modalConfirmBtn');

    console.log('Modal elements found:', {
        modal: !!modal,
        icon: !!icon,
        title: !!title,
        body: !!body,
        info: !!info,
        btn: !!btn
    });

    if (!modal) {
        console.error('Modal element not found!');
        return;
    }

    info.innerHTML = '<strong>Order:</strong> ' + orderNum + '<br><strong>Customer:</strong> ' + customerName;

    if (type === 'confirm') {
        pendingFormId = 'form-confirm-' + id;
        icon.innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i>';
        title.textContent = 'Confirm Payment';
        body.textContent = 'Has the customer completed their payment? Confirming will mark this order as completed.';
        btn.style.background = 'var(--success)';
        btn.innerHTML = '<i class="fas fa-check"></i> Yes, Confirm';
    } else {
        pendingFormId = 'form-cancel-' + id;
        icon.innerHTML = '<i class="fas fa-times-circle" style="color: var(--error);"></i>';
        title.textContent = 'Cancel Order';
        body.textContent = 'Are you sure you want to cancel this order? The order will be removed and this action cannot be undone.';
        btn.style.background = 'var(--error)';
        btn.innerHTML = '<i class="fas fa-times"></i> Yes, Cancel Order';
    }

    btn.onclick = submitModal;
    console.log('Adding active class to modal');
    modal.classList.add('active');
    console.log('Modal classes after adding active:', modal.className);
}

function closeModal() {
    document.getElementById('actionModal').classList.remove('active');
    pendingFormId = null;
}

function submitModal() {
    if (pendingFormId) {
        const form = document.getElementById(pendingFormId);
        if (form) {
            form.submit();
        } else {
            console.error('Form not found:', pendingFormId);
        }
    }
    closeModal();
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up modal event listeners');
    
    const modal = document.getElementById('actionModal');
    if (modal) {
        console.log('Modal found, adding click listener');
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    } else {
        console.error('Modal not found during DOM ready!');
    }
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        console.log('Escape key pressed, closing modal');
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>