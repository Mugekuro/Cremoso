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
<link rel="stylesheet" href="../assets/css/staff.css">
<style>
.pending-orders-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.order-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.order-number {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.meta-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 600;
    letter-spacing: 0.5px;
}

.meta-value {
    font-size: 14px;
    color: var(--text-dark);
    font-weight: 500;
}

.order-items {
    background: #f9fafb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.order-items-title {
    font-size: 12px;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
}

.item-entry {
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.item-entry:last-child {
    border-bottom: none;
}

.item-main {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}

.item-customizations {
    font-size: 12px;
    color: var(--text-muted);
    margin-left: 16px;
    line-height: 1.6;
}

.order-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.order-actions button {
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-confirm {
    background: var(--success);
    color: white;
}

.btn-confirm:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.btn-cancel-order {
    background: var(--error);
    color: white;
}

.btn-cancel-order:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.order-total {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 64px;
    display: block;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state h4 {
    margin-bottom: 8px;
    color: var(--text-dark);
    font-size: 20px;
}

.empty-state p {
    margin-bottom: 24px;
    font-size: 15px;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

.modal-overlay.active {
    display: flex !important;
    visibility: visible !important;
    opacity: 1;
}

.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: scale(0.9);
    transition: transform 0.2s ease;
}

.modal-overlay.active .modal-box {
    transform: scale(1);
}

.modal-icon {
    text-align: center;
    font-size: 64px;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
    text-align: center;
    margin-bottom: 12px;
}

.modal-body {
    font-size: 15px;
    color: var(--text-body);
    text-align: center;
    margin-bottom: 20px;
    line-height: 1.6;
}

.modal-order-info {
    background: #f9fafb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
    font-size: 14px;
    line-height: 1.8;
    color: var(--text-dark);
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.modal-actions button {
    flex: 1;
    padding: 14px 24px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.modal-actions .btn-secondary {
    background: #e5e7eb;
    color: var(--text-dark);
}

.modal-actions .btn-secondary:hover {
    background: #d1d5db;
    transform: translateY(-1px);
}
</style>

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
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 20px 16px;">
            <h3 style="color: var(--text-dark); margin: 0;">Orders Awaiting Payment Confirmation</h3>
            <div style="color: var(--text-muted); font-size: 14px;">
                <i class="fas fa-info-circle"></i> 
                <?= count($pending) ?> pending order<?= count($pending) !== 1 ? 's' : '' ?>
            </div>
        </div>
        
        <?php if(count($pending) > 0): ?>
        <div class="pending-orders-container" style="padding: 0 20px 20px;">
            <?php foreach($pending as $order): ?>
            <?php
            // Get order items from new schema
            $items = $pdo->prepare("SELECT ti.* FROM transaction_items ti WHERE ti.transaction_id = ?");
            $items->execute([$order['transaction_id']]);
            $orderItems = $items->fetchAll();
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number"><?= htmlspecialchars($order['order_number']) ?></div>
                        <div style="font-size: 13px; color: var(--text-muted);">
                            <?= date('M j, Y', strtotime($order['transaction_date'])) ?> at <?= date('h:i A', strtotime($order['transaction_date'])) ?>
                        </div>
                    </div>
                    <div class="order-total">₱<?= number_format($order['total_amount'], 2) ?></div>
                </div>

                <div class="order-meta">
                    <div class="meta-item">
                        <div class="meta-label"><i class="fas fa-user"></i> Customer</div>
                        <div class="meta-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label"><i class="fas fa-shopping-bag"></i> Channel</div>
                        <div class="meta-value">
                            <span class="status-badge" style="background: var(--info-pale); color: var(--info); font-size: 12px; padding: 4px 10px;">
                                <?= htmlspecialchars($order['channel_name']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label"><i class="fas fa-credit-card"></i> Payment</div>
                        <div class="meta-value"><?= htmlspecialchars($order['method_name']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label"><i class="fas fa-user-tie"></i> Staff</div>
                        <div class="meta-value"><?= htmlspecialchars($order['staff_name']) ?></div>
                    </div>
                </div>

                <div class="order-items">
                    <div class="order-items-title"><i class="fas fa-list"></i> Order Items</div>
                    <?php foreach($orderItems as $item): ?>
                    <?php
                    $displayName = $item['item_name'];
                    $customizations = getTransactionItemCustomizations($item['transaction_item_id']);
                    ?>
                    <div class="item-entry">
                        <div class="item-main">
                            <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i>
                            <?= htmlspecialchars($displayName) ?> <span style="color: var(--text-muted);">× <?= $item['quantity'] ?></span>
                        </div>
                        <?php 
                        $hasCustomizations = false;
                        foreach($customizations as $type => $items) {
                            if (!empty($items)) {
                                $hasCustomizations = true;
                                break;
                            }
                        }
                        if ($hasCustomizations): ?>
                        <div class="item-customizations">
                            <?php foreach($customizations as $type => $customs): ?>
                                <?php if (!empty($customs)): ?>
                                <div><i class="fas fa-plus" style="font-size: 8px; margin-right: 4px;"></i> <?= ucfirst($type) ?>: <?= implode(', ', array_column($customs, substr($type, 0, -1) . '_name')) ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-actions">
                    <form method="POST" id="form-cancel-<?= $order['transaction_id'] ?>" style="display: inline;">
                        <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="button" class="btn-cancel-order"
                                onclick="openModal('cancel', <?= $order['transaction_id'] ?>, '<?= addslashes($order['order_number']) ?>', '<?= addslashes(htmlspecialchars($order['customer_name'])) ?>')">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                    </form>
                    <form method="POST" id="form-confirm-<?= $order['transaction_id'] ?>" style="display: inline;">
                        <input type="hidden" name="transaction_id" value="<?= $order['transaction_id'] ?>">
                        <input type="hidden" name="action" value="confirm">
                        <button type="button" class="btn-confirm"
                                onclick="openModal('confirm', <?= $order['transaction_id'] ?>, '<?= addslashes($order['order_number']) ?>', '<?= addslashes(htmlspecialchars($order['customer_name'])) ?>')">
                            <i class="fas fa-check"></i> Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clock"></i>
            <h4>No Pending Orders</h4>
            <p>All orders have been processed. New orders will appear here for payment confirmation.</p>
            <a href="new_order.php" class="btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; padding: 10px 22px; font-size: 14px; font-weight: 600; line-height: 1;">
                <i class="fas fa-plus" style="font-size: 14px; line-height: 1;"></i> Create New Order
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
            <button type="button" id="modalConfirmBtn" style="padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; color: white; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
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
        btn.style.borderRadius = '10px';
        btn.innerHTML = '<i class="fas fa-check"></i> Yes, Confirm';
        btn.onmouseover = function() { this.style.background = '#059669'; this.style.transform = 'translateY(-1px)'; };
        btn.onmouseout = function() { this.style.background = 'var(--success)'; this.style.transform = 'translateY(0)'; };
    } else {
        pendingFormId = 'form-cancel-' + id;
        icon.innerHTML = '<i class="fas fa-times-circle" style="color: var(--error);"></i>';
        title.textContent = 'Cancel Order';
        body.textContent = 'Are you sure you want to cancel this order? The order will be removed and this action cannot be undone.';
        btn.style.background = 'var(--error)';
        btn.style.borderRadius = '10px';
        btn.innerHTML = '<i class="fas fa-times"></i> Yes, Cancel Order';
        btn.onmouseover = function() { this.style.background = '#dc2626'; this.style.transform = 'translateY(-1px)'; };
        btn.onmouseout = function() { this.style.background = 'var(--error)'; this.style.transform = 'translateY(0)'; };
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