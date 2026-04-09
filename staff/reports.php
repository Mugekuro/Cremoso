<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$branch_id = $_SESSION['branch_id'];
$date_range = $_GET['range'] ?? '30';
$report_type = $_GET['report_type'] ?? 'daily';
$filter_channel = $_GET['channel'] ?? '';
$filter_payment = $_GET['payment'] ?? '';

// Date range configuration
$date_config = match($date_range) {
    '7' => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 'label' => 'Last 7 Days', 'days' => 7],
    '30' => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)', 'label' => 'Last 30 Days', 'days' => 30],
    'this_month' => ['start' => 'DATE_FORMAT(CURDATE(), "%Y-%m-01")', 'label' => 'This Month', 'days' => date('d')],
    'last_month' => ['start' => 'DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), "%Y-%m-01")', 'end' => 'LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))', 'label' => 'Last Month', 'days' => date('d', strtotime('last month'))],
    default => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)', 'label' => 'Last 30 Days', 'days' => 30]
};

$date_start = $date_config['start'];
$date_end = $date_config['end'] ?? 'CURDATE()';
$date_label = $date_config['label'];

// Build WHERE clause (always filter by branch for staff)
$whereClauses = ["transaction_date >= {$date_start}", "DATE(transaction_date) <= {$date_end}", "t.branch_id = ?"];
$params = [$branch_id];

if($filter_channel) { $whereClauses[] = "t.channel_id = ?"; $params[] = $filter_channel; }
if($filter_payment) { $whereClauses[] = "t.payment_method_id = ?"; $params[] = $filter_payment; }

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);

// Fetch filter options
$channels = $pdo->query("SELECT * FROM order_channels ORDER BY channel_name")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();

// Get branch name
$branch = $pdo->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
$branch->execute([$branch_id]);
$branchName = $branch->fetchColumn();

// === KPI METRICS ===
$metricsSQL = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT customer_id) as unique_customers,
    COUNT(DISTINCT DATE(transaction_date)) as active_days
    FROM transactions t {$whereSQL}";
$stmt = $pdo->prepare($metricsSQL);
$stmt->execute($params);
$metrics = $stmt->fetch();

$totalOrders = $metrics['total_orders'];
$totalRevenue = $metrics['total_revenue'];
$avgOrderValue = $metrics['avg_order_value'];
$uniqueCustomers = $metrics['unique_customers'];
$activeDays = $metrics['active_days'];
$dailyAvg = $activeDays > 0 ? $totalRevenue / $activeDays : 0;

// === DAILY SALES REPORT ===
if($report_type == 'daily') {
    $dailySales = $pdo->prepare("SELECT 
        DATE(transaction_date) as sale_date,
        COUNT(*) as orders,
        COALESCE(SUM(total_amount), 0) as revenue,
        AVG(total_amount) as avg_order
        FROM transactions t {$whereSQL}
        GROUP BY DATE(transaction_date)
        ORDER BY sale_date DESC");
    $dailySales->execute($params);
    $reportData = $dailySales->fetchAll();
}

// === ITEM PERFORMANCE REPORT ===
elseif($report_type == 'items') {
    $itemReport = $pdo->prepare("SELECT 
        i.item_name,
        f.flavor_name,
        s.size_name,
        SUM(ti.quantity) as qty_sold,
        COALESCE(SUM(ti.subtotal), 0) as revenue,
        AVG(ti.unit_price) as avg_price
        FROM transaction_items ti
        JOIN transactions t ON ti.transaction_id = t.transaction_id
        JOIN items i ON ti.item_id = i.item_id
        JOIN flavors f ON i.flavor_id = f.flavor_id
        JOIN item_sizes s ON i.size_id = s.size_id
        {$whereSQL}
        GROUP BY ti.item_id
        ORDER BY revenue DESC");
    $itemReport->execute($params);
    $reportData = $itemReport->fetchAll();
}

// === CHANNEL PERFORMANCE REPORT ===
elseif($report_type == 'channels') {
    $channelReport = $pdo->prepare("SELECT 
        oc.channel_name,
        COUNT(t.transaction_id) as orders,
        COALESCE(SUM(t.total_amount), 0) as revenue,
        AVG(t.total_amount) as avg_order
        FROM order_channels oc
        LEFT JOIN transactions t ON oc.channel_id = t.channel_id {$whereSQL}
        GROUP BY oc.channel_id
        ORDER BY revenue DESC");
    $channelReport->execute($params);
    $reportData = $channelReport->fetchAll();
}

// === PAYMENT METHOD REPORT ===
elseif($report_type == 'payments') {
    $paymentReport = $pdo->prepare("SELECT 
        pm.method_name,
        COUNT(t.transaction_id) as orders,
        COALESCE(SUM(t.total_amount), 0) as revenue,
        AVG(t.total_amount) as avg_order
        FROM payment_methods pm
        LEFT JOIN transactions t ON pm.payment_method_id = t.payment_method_id {$whereSQL}
        GROUP BY pm.payment_method_id
        ORDER BY revenue DESC");
    $paymentReport->execute($params);
    $reportData = $paymentReport->fetchAll();
}

// === CSV EXPORT ===
if(isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="staff_report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Cremoso Staff Report - ' . $branchName, '', '', '']);
    fputcsv($output, ['Generated: ' . date('F j, Y g:i A'), '', '', '']);
    fputcsv($output, []);
    
    if(in_array($report_type, ['daily'])) {
        fputcsv($output, ['Date', 'Orders', 'Revenue', 'Avg Order']);
        foreach($reportData as $row) {
            fputcsv($output, [$row['sale_date'], $row['orders'], $row['revenue'], $row['avg_order']]);
        }
    } elseif($report_type == 'items') {
        fputcsv($output, ['Item Name', 'Flavor', 'Size', 'Qty Sold', 'Revenue', 'Avg Price']);
        foreach($reportData as $row) {
            fputcsv($output, [$row['item_name'], $row['flavor_name'], $row['size_name'], $row['qty_sold'], $row['revenue'], $row['avg_price']]);
        }
    } else {
        fputcsv($output, ['Name', 'Orders', 'Revenue', 'Avg Order']);
        foreach($reportData as $row) {
            fputcsv($output, [$row['channel_name'] ?? $row['method_name'] ?? '', $row['orders'], $row['revenue'], $row['avg_order']]);
        }
    }
    
    fclose($output);
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_staff.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> Reports</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <span style="font-size: 13px; color: var(--text-muted); background: var(--primary-pale); padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border);">
                <i class="fas fa-store"></i> <?= htmlspecialchars($branchName) ?>
            </span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label><i class="fas fa-chart-pie"></i> Report Type</label>
                <select name="report_type" onchange="this.form.submit()">
                    <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Daily Sales</option>
                    <option value="items" <?= $report_type == 'items' ? 'selected' : '' ?>>Item Performance</option>
                    <option value="channels" <?= $report_type == 'channels' ? 'selected' : '' ?>>Channel Performance</option>
                    <option value="payments" <?= $report_type == 'payments' ? 'selected' : '' ?>>Payment Methods</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> Date Range</label>
                <select name="range" onchange="this.form.submit()">
                    <option value="7" <?= $date_range == '7' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30" <?= $date_range == '30' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="this_month" <?= $date_range == 'this_month' ? 'selected' : '' ?>>This Month</option>
                    <option value="last_month" <?= $date_range == 'last_month' ? 'selected' : '' ?>>Last Month</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-shopping-bag"></i> Channel</label>
                <select name="channel" onchange="this.form.submit()">
                    <option value="">All Channels</option>
                    <?php foreach($channels as $c): ?>
                    <option value="<?= $c['channel_id'] ?>" <?= $filter_channel == $c['channel_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['channel_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-credit-card"></i> Payment</label>
                <select name="payment" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    <?php foreach($payments as $p): ?>
                    <option value="<?= $p['payment_method_id'] ?>" <?= $filter_payment == $p['payment_method_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['method_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-sync-alt"></i> Apply</button>
                <a href="reports.php" class="btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- KPI Summary -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($totalOrders) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calculator"></i></div>
            <div class="stat-value">₱<?= number_format($avgOrderValue, 2) ?></div>
            <div class="stat-label">Avg Order Value</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-value">₱<?= number_format($dailyAvg, 2) ?></div>
            <div class="stat-label">Daily Average</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= number_format($uniqueCustomers) ?></div>
            <div class="stat-label">Unique Customers</div>
        </div>
    </div>

    <!-- Report Data Table -->
    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-<?= match($report_type) {
                'daily' => 'calendar-day',
                'items' => 'ice-cream',
                'channels' => 'shopping-bag',
                'payments' => 'credit-card',
                default => 'table'
            } ?>" style="color: var(--primary); margin-right: 8px;"></i>
            <?= ucfirst(str_replace('_', ' ', $report_type)) ?> Report - <?= $date_label ?>
        </h3>
        
        <?php if(count($reportData) > 0): ?>
        <table>
            <thead>
                <tr>
                    <?php
                    match($report_type) {
                        'daily' => print('<th>Date</th><th>Orders</th><th>Revenue</th><th>Avg Order</th>'),
                        'items' => print('<th>Item</th><th>Flavor</th><th>Size</th><th>Qty Sold</th><th>Revenue</th><th>Avg Price</th>'),
                        'channels' => print('<th>Channel</th><th>Orders</th><th>Revenue</th><th>Avg Order</th>'),
                        'payments' => print('<th>Payment Method</th><th>Orders</th><th>Revenue</th><th>Avg Order</th>'),
                        default => print('<th>No columns defined</th>')
                    };
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalReportRevenue = array_sum(array_column($reportData, 'revenue'));
                
                foreach($reportData as $idx => $row):
                ?>
                <tr>
                    <?php if($report_type == 'daily'): ?>
                    <td><strong><?= date('M d, Y', strtotime($row['sale_date'])) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    
                    <?php elseif($report_type == 'items'): ?>
                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['flavor_name']) ?></td>
                    <td><?= htmlspecialchars($row['size_name']) ?></td>
                    <td><?= $row['qty_sold'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_price'], 2) ?></td>
                    
                    <?php elseif($report_type == 'channels'): ?>
                    <td><strong><?= htmlspecialchars($row['channel_name']) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    
                    <?php elseif($report_type == 'payments'): ?>
                    <td><strong><?= htmlspecialchars($row['method_name']) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if(in_array($report_type, ['daily', 'items'])): ?>
            <tfoot>
                <tr style="background: var(--primary-pale); font-weight: 700;">
                    <?php if($report_type == 'daily'): ?>
                    <td>TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'orders')) ?></td>
                    <td class="item-price">₱<?= number_format($totalReportRevenue, 2) ?></td>
                    <td>-</td>
                    <?php elseif($report_type == 'items'): ?>
                    <td colspan="3">TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'qty_sold')) ?></td>
                    <td class="item-price">₱<?= number_format($totalReportRevenue, 2) ?></td>
                    <td>-</td>
                    <?php endif; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-chart-bar" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-dark); margin-bottom: 8px;">No Data Available</h3>
            <p>No records found for the selected filters and date range.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Report Summary Footer -->
    <?php if(count($reportData) > 0): ?>
    <div style="margin-top: 16px; padding: 16px; background: var(--surface-2); border-radius: 10px; border: 1px solid var(--border); font-size: 13px; color: var(--text-muted);">
        <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i>
        <strong>Report Summary:</strong> 
        <?= count($reportData) ?> record(s) found | 
        Total Revenue: <strong style="color: var(--primary-dark);">₱<?= number_format($totalReportRevenue, 2) ?></strong> |
        <?= $date_label ?> |
        Generated on <?= date('F j, Y g:i A') ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
