<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$branch_id = $_SESSION['branch_id'];
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT t.*, c.customer_name, u.fullname as staff, oc.channel_name, pm.method_name
                       FROM transactions t
                       JOIN customers c ON t.customer_id = c.customer_id
                       JOIN users u ON t.user_id = u.user_id
                       JOIN order_channels oc ON t.channel_id = oc.channel_id
                       JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                       WHERE DATE(t.transaction_date) = ? AND t.branch_id = ?
                       ORDER BY t.transaction_date DESC");
$stmt->execute([$today, $branch_id]);
$transactions = $stmt->fetchAll();

// Calculate daily total (only completed)
$dailyTotal = 0;
foreach($transactions as $t) {
    if(($t['status'] ?? 'completed') === 'completed') {
        $dailyTotal += $t['total_amount'];
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/topnav_staff.php';
include __DIR__ . '/../includes/sidebar_staff.php';
include __DIR__ . '/../includes/mobile_navbar_staff.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-calendar-day"></i> Daily Transaction Log</h1>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= count($transactions) ?></div>
            <div class="stat-label">Total Orders Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($dailyTotal, 2) ?></div>
            <div class="stat-label">Total Sales Today</div>
        </div>
    </div>

    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">Today's Transactions</h3>
        <table style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 14%;">
                <col style="width: 13%;">
                <col style="width: 10%;">
                <col style="width: 11%;">
                <col style="width: 11%;">
                <col style="width: 12%;">
                <col style="width: 10%;">
                <col style="width: 19%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Channel</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $t): ?>
                <tr>
                    <td><strong><?= $t['order_number'] ?></strong></td>
                    <td style="text-align: center;"><?= htmlspecialchars($t['customer_name']) ?></td>
                    <td>₱<?= number_format($t['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($t['method_name']) ?></td>
                    <td><?= htmlspecialchars($t['channel_name']) ?></td>
                    <td><?= date('M d, Y', strtotime($t['transaction_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($t['transaction_date'])) ?></td>
                    <td>
                        <?php 
                        $status = $t['status'] ?? 'completed';
                        if($status === 'completed'): ?>
                            <span class="status-badge status-completed">
                                <i class="fas fa-check-circle"></i> Completed
                            </span>
                        <?php elseif($status === 'pending'): ?>
                            <span class="status-badge status-pending">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        <?php elseif($status === 'cancelled'): ?>
                            <span class="status-badge status-cancelled">
                                <i class="fas fa-times-circle"></i> Cancelled
                            </span>
                        <?php else: ?>
                            <span class="status-badge">
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($transactions) == 0): ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-receipt" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No transactions recorded today
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>