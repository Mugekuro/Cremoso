<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === FILTER PARAMETERS ===
$date_range = $_GET['range'] ?? '30';
$filter_branch = $_GET['branch'] ?? '';
$filter_channel = $_GET['channel'] ?? '';
$filter_payment = $_GET['payment'] ?? '';
$report_type = $_GET['report_type'] ?? 'daily';

// Date range configuration
$date_config = match($date_range) {
    '7' => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 'label' => 'Last 7 Days', 'days' => 7],
    '30' => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)', 'label' => 'Last 30 Days', 'days' => 30],
    '90' => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 90 DAY)', 'label' => 'Last 90 Days', 'days' => 90],
    'this_month' => ['start' => 'DATE_FORMAT(CURDATE(), "%Y-%m-01")', 'label' => 'This Month', 'days' => date('d')],
    'last_month' => ['start' => 'DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), "%Y-%m-01")', 'end' => 'LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))', 'label' => 'Last Month', 'days' => date('d', strtotime('last month'))],
    'this_year' => ['start' => 'MAKEDATE(YEAR(CURDATE()), 1)', 'label' => 'This Year', 'days' => 365],
    default => ['start' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)', 'label' => 'Last 30 Days', 'days' => 30]
};

$date_start = $date_config['start'];
$date_end = $date_config['end'] ?? 'CURDATE()';
$date_label = $date_config['label'];

// Build WHERE clause
$whereClauses = ["transaction_date >= {$date_start}", "DATE(transaction_date) <= {$date_end}", "t.status = 'confirmed'"];
$params = [];

if($filter_branch) { $whereClauses[] = "t.branch_id = ?"; $params[] = $filter_branch; }
if($filter_channel) { $whereClauses[] = "t.channel_id = ?"; $params[] = $filter_channel; }
if($filter_payment) { $whereClauses[] = "t.payment_method_id = ?"; $params[] = $filter_payment; }

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);

// Fetch filter options
$branches = $pdo->query("SELECT * FROM branches ORDER BY branch_name")->fetchAll();
$channels = $pdo->query("SELECT * FROM order_channels ORDER BY channel_name")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();

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

// Previous period comparison
$prevMetricsSQL = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue
    FROM transactions t 
    WHERE transaction_date < {$date_start} 
    AND transaction_date >= DATE_SUB({$date_start}, INTERVAL ({$date_config['days']}) DAY)
    AND t.status = 'confirmed'";
$stmt = $pdo->prepare($prevMetricsSQL);
$stmt->execute();
$prevMetrics = $stmt->fetch();

$revenueGrowth = $prevMetrics['total_revenue'] > 0 ? 
    (($totalRevenue - $prevMetrics['total_revenue']) / $prevMetrics['total_revenue']) * 100 : 0;
$ordersGrowth = $prevMetrics['total_orders'] > 0 ? 
    (($totalOrders - $prevMetrics['total_orders']) / $prevMetrics['total_orders']) * 100 : 0;

// === DAILY SALES REPORT ===
if($report_type == 'daily') {
    $dailySales = $pdo->prepare("SELECT 
        DATE(transaction_date) as sale_date,
        COUNT(*) as orders,
        COALESCE(SUM(total_amount), 0) as revenue,
        AVG(total_amount) as avg_order,
        COUNT(DISTINCT customer_id) as customers
        FROM transactions t {$whereSQL}
        GROUP BY DATE(transaction_date)
        ORDER BY sale_date DESC");
    $dailySales->execute($params);
    $reportData = $dailySales->fetchAll();
}

// === WEEKLY SALES REPORT ===
elseif($report_type == 'weekly') {
    $weeklySales = $pdo->prepare("SELECT 
        YEARWEEK(transaction_date, 1) as week_num,
        MIN(DATE(transaction_date)) as week_start,
        MAX(DATE(transaction_date)) as week_end,
        COUNT(*) as orders,
        COALESCE(SUM(total_amount), 0) as revenue,
        AVG(total_amount) as avg_order
        FROM transactions t {$whereSQL}
        GROUP BY YEARWEEK(transaction_date, 1)
        ORDER BY week_start DESC");
    $weeklySales->execute($params);
    $reportData = $weeklySales->fetchAll();
}

// === MONTHLY SALES REPORT ===
elseif($report_type == 'monthly') {
    $monthlySales = $pdo->prepare("SELECT 
        YEAR(transaction_date) as year,
        MONTH(transaction_date) as month,
        MONTHNAME(transaction_date) as month_name,
        COUNT(*) as orders,
        COALESCE(SUM(total_amount), 0) as revenue,
        AVG(total_amount) as avg_order,
        COUNT(DISTINCT customer_id) as customers
        FROM transactions t {$whereSQL}
        GROUP BY YEAR(transaction_date), MONTH(transaction_date)
        ORDER BY year DESC, month DESC");
    $monthlySales->execute($params);
    $reportData = $monthlySales->fetchAll();
}

// === ITEM PERFORMANCE REPORT ===
elseif($report_type == 'items') {
    $itemReport = $pdo->prepare("SELECT 
        i.item_name,
        f.flavor_name,
        s.size_name,
        SUM(ti.quantity) as qty_sold,
        COUNT(DISTINCT ti.transaction_id) as times_ordered,
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

// === BRANCH PERFORMANCE REPORT ===
elseif($report_type == 'branches') {
    $branchReport = $pdo->prepare("SELECT 
        b.branch_name,
        b.location,
        COUNT(t.transaction_id) as orders,
        COALESCE(SUM(t.total_amount), 0) as revenue,
        AVG(t.total_amount) as avg_order,
        COUNT(DISTINCT t.customer_id) as customers,
        COUNT(DISTINCT DATE(t.transaction_date)) as active_days
        FROM branches b
        LEFT JOIN transactions t ON b.branch_id = t.branch_id {$whereSQL}
        GROUP BY b.branch_id
        ORDER BY revenue DESC");
    $branchReport->execute($params);
    $reportData = $branchReport->fetchAll();
}

// === CHANNEL PERFORMANCE REPORT ===
elseif($report_type == 'channels') {
    $channelReport = $pdo->prepare("SELECT 
        oc.channel_name,
        COUNT(t.transaction_id) as orders,
        COALESCE(SUM(t.total_amount), 0) as revenue,
        AVG(t.total_amount) as avg_order,
        COUNT(DISTINCT t.customer_id) as customers
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
        AVG(t.total_amount) as avg_order,
        COUNT(DISTINCT t.customer_id) as customers
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
    header('Content-Disposition: attachment; filename="report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, ['Cremoso Sales Report - ' . $date_label, '', '', '', '']);
    fputcsv($output, ['Generated: ' . date('F j, Y g:i A'), '', '', '', '']);
    fputcsv($output, []);
    
    // Report-specific headers
    if(in_array($report_type, ['daily', 'weekly', 'monthly'])) {
        fputcsv($output, ['Date/Period', 'Orders', 'Revenue', 'Avg Order', 'Customers/Details']);
        foreach($reportData as $row) {
            if($report_type == 'daily') {
                fputcsv($output, [
                    $row['sale_date'],
                    $row['orders'],
                    $row['revenue'],
                    $row['avg_order'],
                    $row['customers']
                ]);
            } elseif($report_type == 'weekly') {
                fputcsv($output, [
                    $row['week_start'] . ' to ' . $row['week_end'],
                    $row['orders'],
                    $row['revenue'],
                    $row['avg_order'],
                    ''
                ]);
            } else {
                fputcsv($output, [
                    $row['month_name'] . ' ' . $row['year'],
                    $row['orders'],
                    $row['revenue'],
                    $row['avg_order'],
                    $row['customers']
                ]);
            }
        }
    } elseif($report_type == 'items') {
        fputcsv($output, ['Item Name', 'Flavor', 'Size', 'Qty Sold', 'Revenue', 'Avg Price', 'Times Ordered']);
        foreach($reportData as $row) {
            fputcsv($output, [
                $row['item_name'],
                $row['flavor_name'],
                $row['size_name'],
                $row['qty_sold'],
                $row['revenue'],
                $row['avg_price'],
                $row['times_ordered']
            ]);
        }
    } else {
        fputcsv($output, ['Name', 'Orders', 'Revenue', 'Avg Order', 'Customers']);
        foreach($reportData as $row) {
            fputcsv($output, [
                $row['branch_name'] ?? $row['channel_name'] ?? $row['method_name'] ?? '',
                $row['orders'],
                $row['revenue'],
                $row['avg_order'],
                $row['customers'] ?? ''
            ]);
        }
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
        <h1><i class="fas fa-file-alt"></i> Reports</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <span style="font-size: 13px; color: var(--text-muted); background: var(--primary-pale); padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border);">
                <i class="fas fa-calendar-alt"></i> <?= $date_label ?>
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
                    <option value="weekly" <?= $report_type == 'weekly' ? 'selected' : '' ?>>Weekly Sales</option>
                    <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Monthly Sales</option>
                    <option value="items" <?= $report_type == 'items' ? 'selected' : '' ?>>Item Performance</option>
                    <option value="branches" <?= $report_type == 'branches' ? 'selected' : '' ?>>Branch Performance</option>
                    <option value="channels" <?= $report_type == 'channels' ? 'selected' : '' ?>>Channel Performance</option>
                    <option value="payments" <?= $report_type == 'payments' ? 'selected' : '' ?>>Payment Methods</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> Date Range</label>
                <select name="range" onchange="this.form.submit()">
                    <option value="7" <?= $date_range == '7' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30" <?= $date_range == '30' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="90" <?= $date_range == '90' ? 'selected' : '' ?>>Last 90 Days</option>
                    <option value="this_month" <?= $date_range == 'this_month' ? 'selected' : '' ?>>This Month</option>
                    <option value="last_month" <?= $date_range == 'last_month' ? 'selected' : '' ?>>Last Month</option>
                    <option value="this_year" <?= $date_range == 'this_year' ? 'selected' : '' ?>>This Year</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-store"></i> Branch</label>
                <select name="branch" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $b): ?>
                    <option value="<?= $b['branch_id'] ?>" <?= $filter_branch == $b['branch_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['branch_name']) ?>
                    </option>
                    <?php endforeach; ?>
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
            <?php if($prevMetrics['total_revenue'] > 0): ?>
            <div style="margin-top: 8px; font-size: 12px; color: <?= $revenueGrowth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                <i class="fas fa-arrow-<?= $revenueGrowth >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($revenueGrowth, 1)) ?>% vs previous period
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($totalOrders) ?></div>
            <div class="stat-label">Total Orders</div>
            <?php if($prevMetrics['total_orders'] > 0): ?>
            <div style="margin-top: 8px; font-size: 12px; color: <?= $ordersGrowth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                <i class="fas fa-arrow-<?= $ordersGrowth >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($ordersGrowth, 1)) ?>% vs previous period
            </div>
            <?php endif; ?>
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
    </div>

    <!-- Report Data Table -->
    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-<?= match($report_type) {
                'daily' => 'calendar-day',
                'weekly' => 'calendar-week',
                'monthly' => 'calendar-alt',
                'items' => 'ice-cream',
                'branches' => 'store',
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
                    // Dynamic table headers based on report type
                    match($report_type) {
                        'daily' => print('<th>Date</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Actions</th>'),
                        'weekly' => print('<th>Week Period</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Actions</th>'),
                        'monthly' => print('<th>Month</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Unique Customers</th><th>Actions</th>'),
                        'items' => print('<th>Item</th><th>Flavor</th><th>Size</th><th>Qty Sold</th><th>Times Ordered</th><th>Revenue</th><th>Avg Price</th>'),
                        'branches' => print('<th>Branch</th><th>Location</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Customers</th><th>Active Days</th><th>Share</th>'),
                        'channels' => print('<th>Channel</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Customers</th><th>Share</th>'),
                        'payments' => print('<th>Payment Method</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Customers</th><th>Share</th>'),
                        default => print('<th>No columns defined</th>')
                    };
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalReportRevenue = array_sum(array_column($reportData, 'revenue'));
                
                foreach($reportData as $idx => $row):
                    $share = $totalReportRevenue > 0 ? ($row['revenue'] / $totalReportRevenue) * 100 : 0;
                ?>
                <tr>
                    <?php if($report_type == 'daily'): ?>
                    <td><strong><?= date('M d, Y', strtotime($row['sale_date'])) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td>
                        <a href="daily_detail.php?<?= http_build_query(array_merge(['date' => $row['sale_date']], array_filter(['branch' => $filter_branch, 'channel' => $filter_channel, 'payment' => $filter_payment]))) ?>" class="btn-link" style="font-size: 12px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                    
                    <?php elseif($report_type == 'weekly'): ?>
                    <td><strong><?= date('M d', strtotime($row['week_start'])) ?> - <?= date('M d, Y', strtotime($row['week_end'])) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td>
                        <a href="transactions.php?date=<?= $row['week_start'] ?>" class="btn-link" style="font-size: 12px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                    
                    <?php elseif($report_type == 'monthly'): ?>
                    <td><strong><?= $row['month_name'] ?> <?= $row['year'] ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td><?= $row['customers'] ?></td>
                    <td>
                        <span class="status-badge status-active"><?= $row['month'] ?>/<?= $row['year'] ?></span>
                    </td>
                    
                    <?php elseif($report_type == 'items'): ?>
                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['flavor_name']) ?></td>
                    <td><?= htmlspecialchars($row['size_name']) ?></td>
                    <td><?= $row['qty_sold'] ?></td>
                    <td><?= $row['times_ordered'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_price'], 2) ?></td>
                    
                    <?php elseif($report_type == 'branches'): ?>
                    <td><strong><?= htmlspecialchars($row['branch_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td><?= $row['customers'] ?></td>
                    <td><?= $row['active_days'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; background: var(--primary-pale); border-radius: 10px; height: 8px; overflow: hidden; min-width: 60px;">
                                <div style="width: <?= $share ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);"><?= number_format($share, 1) ?>%</span>
                        </div>
                    </td>
                    
                    <?php elseif($report_type == 'channels'): ?>
                    <td><strong><?= htmlspecialchars($row['channel_name']) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td><?= $row['customers'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; background: var(--primary-pale); border-radius: 10px; height: 8px; overflow: hidden; min-width: 60px;">
                                <div style="width: <?= $share ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);"><?= number_format($share, 1) ?>%</span>
                        </div>
                    </td>
                    
                    <?php elseif($report_type == 'payments'): ?>
                    <td><strong><?= htmlspecialchars($row['method_name']) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                    <td>₱<?= number_format($row['avg_order'], 2) ?></td>
                    <td><?= $row['customers'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; background: var(--primary-pale); border-radius: 10px; height: 8px; overflow: hidden; min-width: 60px;">
                                <div style="width: <?= $share ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);"><?= number_format($share, 1) ?>%</span>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if(in_array($report_type, ['daily', 'weekly', 'monthly', 'items'])): ?>
            <tfoot>
                <tr style="background: var(--primary-pale); font-weight: 700;">
                    <?php if($report_type == 'daily'): ?>
                    <td>TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'orders')) ?></td>
                    <td class="item-price">₱<?= number_format($totalReportRevenue, 2) ?></td>
                    <td>-</td>
                    <td></td>
                    <?php elseif($report_type == 'weekly'): ?>
                    <td>TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'orders')) ?></td>
                    <td class="item-price">₱<?= number_format($totalReportRevenue, 2) ?></td>
                    <td>-</td>
                    <td></td>
                    <?php elseif($report_type == 'monthly'): ?>
                    <td>TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'orders')) ?></td>
                    <td class="item-price">₱<?= number_format($totalReportRevenue, 2) ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td></td>
                    <?php elseif($report_type == 'items'): ?>
                    <td colspan="3">TOTAL</td>
                    <td><?= array_sum(array_column($reportData, 'qty_sold')) ?></td>
                    <td><?= array_sum(array_column($reportData, 'times_ordered')) ?></td>
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
            <p style="font-size: 12px; margin-top: 8px;">Try adjusting your filters or selecting a different date range.</p>
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
