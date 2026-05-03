<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === FILTER PARAMETERS ===
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
$branch_id = $_GET['branch_id'] ?? ''; // Branch filter

// === GET BRANCHES FOR FILTER ===
$branchesSQL = "SELECT branch_id, branch_name FROM branches ORDER BY branch_name";
$branchesStmt = $pdo->query($branchesSQL);
$branches = $branchesStmt->fetchAll();

// === SUMMARY METRICS ===
$metricsSQL = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue
    FROM transactions 
    WHERE DATE(transaction_date) BETWEEN ? AND ?
    AND status = 'completed'";

$params = [$date_from, $date_to];

if(!empty($branch_id)) {
    $metricsSQL .= " AND branch_id = ?";
    $params[] = $branch_id;
}

$stmt = $pdo->prepare($metricsSQL);
$stmt->execute($params);
$metrics = $stmt->fetch();

$totalOrders = $metrics['total_orders'];
$totalRevenue = $metrics['total_revenue'];

// === TRANSACTIONS LIST ===
$transactionsSQL = "SELECT 
    t.transaction_id,
    t.order_number,
    t.transaction_date,
    t.total_amount,
    t.status,
    b.branch_name,
    oc.channel_name,
    pm.method_name,
    c.customer_name
    FROM transactions t
    LEFT JOIN branches b ON t.branch_id = b.branch_id
    LEFT JOIN order_channels oc ON t.channel_id = oc.channel_id
    LEFT JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
    LEFT JOIN customers c ON t.customer_id = c.customer_id
    WHERE DATE(t.transaction_date) BETWEEN ? AND ?
    AND t.status = 'completed'";

$transParams = [$date_from, $date_to];

if(!empty($branch_id)) {
    $transactionsSQL .= " AND t.branch_id = ?";
    $transParams[] = $branch_id;
}

$transactionsSQL .= " ORDER BY t.transaction_date DESC";

$stmt = $pdo->prepare($transactionsSQL);
$stmt->execute($transParams);
$transactions = $stmt->fetchAll();

// === CSV EXPORT ===
if(isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, ['Cremoso Transactions Report']);
    fputcsv($output, ['Period: ' . date('M d, Y', strtotime($date_from)) . ' to ' . date('M d, Y', strtotime($date_to))]);
    fputcsv($output, ['Generated: ' . date('F j, Y g:i A')]);
    fputcsv($output, []);
    fputcsv($output, ['Total Orders: ' . $totalOrders]);
    fputcsv($output, ['Total Revenue: ₱' . number_format($totalRevenue, 2)]);
    fputcsv($output, []);
    
    // Transaction headers
    fputcsv($output, ['Order #', 'Date', 'Customer', 'Branch', 'Channel', 'Payment Method', 'Amount', 'Status']);
    
    // Transaction data
    foreach($transactions as $row) {
        fputcsv($output, [
            $row['order_number'],
            date('M d, Y h:i A', strtotime($row['transaction_date'])),
            $row['customer_name'],
            $row['branch_name'],
            $row['channel_name'],
            $row['method_name'],
            number_format($row['total_amount'], 2),
            ucfirst($row['status'])
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> Sales Report</h1>
        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn-primary">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-card">
        <form method="GET" class="filter-form" style="display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;">
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-dark); font-size: 14px;">
                    <i class="fas fa-calendar" style="color: var(--primary); margin-right: 6px;"></i> From Date
                </label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" required 
                    style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
            </div>
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-dark); font-size: 14px;">
                    <i class="fas fa-calendar" style="color: var(--primary); margin-right: 6px;"></i> To Date
                </label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" required 
                    style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
            </div>
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-dark); font-size: 14px;">
                    <i class="fas fa-store" style="color: var(--primary); margin-right: 6px;"></i> Branch
                </label>
                <select name="branch_id" 
                    style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; background: white;">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $branch): ?>
                        <option value="<?= $branch['branch_id'] ?>" <?= $branch_id == $branch['branch_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="padding: 10px 24px; white-space: nowrap;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="reports.php" class="btn-secondary" style="padding: 10px 24px; white-space: nowrap;">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($totalOrders) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-list" style="color: var(--primary); margin-right: 8px;"></i>
            Transactions (<?= date('M d, Y', strtotime($date_from)) ?> - <?= date('M d, Y', strtotime($date_to)) ?>)
        </h3>
        
        <?php if(count($transactions) > 0): ?>
        <table style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 15%;">Order #</th>
                    <th style="width: 13%;">Date</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 13%;">Branch</th>
                    <th style="width: 11%;">Channel</th>
                    <th style="width: 13%;">Payment Method</th>
                    <th style="width: 11%;">Amount</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['order_number']) ?></strong></td>
                    <td><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['branch_name']) ?></td>
                    <td><?= htmlspecialchars($row['channel_name']) ?></td>
                    <td><?= htmlspecialchars($row['method_name']) ?></td>
                    <td class="item-price">₱<?= number_format($row['total_amount'], 2) ?></td>
                    <td><span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: var(--primary-pale); font-weight: 700;">
                    <td colspan="6" style="text-align: right; padding-right: 20px;">TOTAL</td>
                    <td class="item-price">₱<?= number_format($totalRevenue, 2) ?></td>
                    <td style="text-align: center;"><?= number_format($totalOrders) ?> orders</td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-dark); margin-bottom: 8px;">No Transactions Found</h3>
            <p>No completed transactions found for the selected date range.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
