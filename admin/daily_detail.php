<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === PARAMETER HANDLING ===
$target_date = $_GET['date'] ?? date('Y-m-d');
$filter_branch = $_GET['branch'] ?? '';
$filter_channel = $_GET['channel'] ?? '';
$filter_payment = $_GET['payment'] ?? '';

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $target_date)) {
    $target_date = date('Y-m-d');
}

// Build WHERE clause for filtering
$whereClauses = ["DATE(t.transaction_date) = ?"];
$params = [$target_date];

if($filter_branch) { $whereClauses[] = "t.branch_id = ?"; $params[] = $filter_branch; }
if($filter_channel) { $whereClauses[] = "t.channel_id = ?"; $params[] = $filter_channel; }
if($filter_payment) { $whereClauses[] = "t.payment_method_id = ?"; $params[] = $filter_payment; }

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);

// === DAILY METRICS ===
$dailyMetricsSQL = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT customer_id) as unique_customers,
    MIN(transaction_date) as first_order,
    MAX(transaction_date) as last_order
    FROM transactions t {$whereSQL}";
$stmt = $pdo->prepare($dailyMetricsSQL);
$stmt->execute($params);
$dailyMetrics = $stmt->fetch();

// === COMPARISON METRICS ===
// Previous day
$prevDaySQL = "SELECT COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue 
    FROM transactions t WHERE DATE(transaction_date) = DATE_SUB(?, INTERVAL 1 DAY)";
$stmt = $pdo->prepare($prevDaySQL);
$stmt->execute([$target_date]);
$prevDay = $stmt->fetch();

// Same day last week
$lastWeekSQL = "SELECT COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue 
    FROM transactions t WHERE DATE(transaction_date) = DATE_SUB(?, INTERVAL 7 DAY)";
$stmt = $pdo->prepare($lastWeekSQL);
$stmt->execute([$target_date]);
$lastWeek = $stmt->fetch();

// Monthly average (same month)
$monthlyAvgSQL = "SELECT 
    AVG(daily_revenue) as avg_revenue,
    AVG(daily_orders) as avg_orders
    FROM (
        SELECT 
            DATE(transaction_date) as date,
            COUNT(*) as daily_orders,
            COALESCE(SUM(total_amount), 0) as daily_revenue
        FROM transactions t 
        WHERE YEAR(transaction_date) = YEAR(?) 
        AND MONTH(transaction_date) = MONTH(?)
        AND DATE(transaction_date) != ?
        GROUP BY DATE(transaction_date)
    ) daily_stats";
$stmt = $pdo->prepare($monthlyAvgSQL);
$stmt->execute([$target_date, $target_date, $target_date]);
$monthlyAvg = $stmt->fetch();

// Calculate growth percentages
$revenueGrowthDay = $prevDay['revenue'] > 0 ? 
    (($dailyMetrics['total_revenue'] - $prevDay['revenue']) / $prevDay['revenue']) * 100 : 0;
$revenueGrowthWeek = $lastWeek['revenue'] > 0 ? 
    (($dailyMetrics['total_revenue'] - $lastWeek['revenue']) / $lastWeek['revenue']) * 100 : 0;
$revenueGrowthMonth = $monthlyAvg['avg_revenue'] > 0 ? 
    (($dailyMetrics['total_revenue'] - $monthlyAvg['avg_revenue']) / $monthlyAvg['avg_revenue']) * 100 : 0;

$ordersGrowthDay = $prevDay['orders'] > 0 ? 
    (($dailyMetrics['total_orders'] - $prevDay['orders']) / $prevDay['orders']) * 100 : 0;
$ordersGrowthWeek = $lastWeek['orders'] > 0 ? 
    (($dailyMetrics['total_orders'] - $lastWeek['orders']) / $lastWeek['orders']) * 100 : 0;
$ordersGrowthMonth = $monthlyAvg['avg_orders'] > 0 ? 
    (($dailyMetrics['total_orders'] - $monthlyAvg['avg_orders']) / $monthlyAvg['avg_orders']) * 100 : 0;

// Get filter options for display
$branches = $pdo->query("SELECT * FROM branches ORDER BY branch_name")->fetchAll();
$channels = $pdo->query("SELECT * FROM order_channels ORDER BY channel_name")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment_methods ORDER BY method_name")->fetchAll();

// Get current filter names for display
$currentBranch = $filter_branch ? $pdo->prepare("SELECT branch_name FROM branches WHERE branch_id = ?")->execute([$filter_branch]) ? $pdo->fetchColumn() : 'All Branches' : 'All Branches';
$currentChannel = $filter_channel ? $pdo->prepare("SELECT channel_name FROM order_channels WHERE channel_id = ?")->execute([$filter_channel]) ? $pdo->fetchColumn() : 'All Channels' : 'All Channels';
$currentPayment = $filter_payment ? $pdo->prepare("SELECT method_name FROM payment_methods WHERE payment_method_id = ?")->execute([$filter_payment]) ? $pdo->fetchColumn() : 'All Methods' : 'All Methods';

// === HOURLY BREAKDOWN ===
$hourlySQL = "SELECT 
    HOUR(transaction_date) as hour,
    COUNT(*) as orders,
    COALESCE(SUM(total_amount), 0) as revenue,
    AVG(total_amount) as avg_order,
    COUNT(DISTINCT customer_id) as customers
    FROM transactions t {$whereSQL}
    GROUP BY HOUR(transaction_date)
    ORDER BY hour";
$stmt = $pdo->prepare($hourlySQL);
$stmt->execute($params);
$hourlyData = $stmt->fetchAll();

// Create full 24-hour array with zeros for missing hours
$hourlyBreakdown = [];
for($h = 0; $h < 24; $h++) {
    $hourlyBreakdown[$h] = ['hour' => $h, 'orders' => 0, 'revenue' => 0, 'avg_order' => 0, 'customers' => 0];
}
foreach($hourlyData as $row) {
    $hourlyBreakdown[$row['hour']] = $row;
}

// Find peak hour
$peakHour = 0;
$peakRevenue = 0;
foreach($hourlyBreakdown as $hour => $data) {
    if($data['revenue'] > $peakRevenue) {
        $peakRevenue = $data['revenue'];
        $peakHour = $hour;
    }
}

// Calculate max revenue for chart scaling
$maxRevenue = max(array_column($hourlyBreakdown, 'revenue'));

// === TOP ITEMS ANALYSIS ===
$topItemsSQL = "SELECT 
    ti.item_name,
    ti.category,
    ti.size,
    SUM(ti.quantity) as qty_sold,
    COUNT(DISTINCT ti.transaction_id) as times_ordered,
    COALESCE(SUM(ti.subtotal), 0) as revenue,
    AVG(ti.base_price) as avg_price
    FROM transaction_items ti
    JOIN transactions t ON ti.transaction_id = t.transaction_id
    {$whereSQL}
    GROUP BY ti.item_name, ti.category, ti.size
    ORDER BY revenue DESC
    LIMIT 5";
$stmt = $pdo->prepare($topItemsSQL);
$stmt->execute($params);
$topItems = $stmt->fetchAll();

// === CUSTOMER ANALYSIS ===
$customerAnalysisSQL = "SELECT 
    COUNT(DISTINCT customer_id) as total_customers,
    COUNT(*) as total_orders,
    AVG(total_amount) as avg_order_value,
    COUNT(*) / COUNT(DISTINCT customer_id) as orders_per_customer
    FROM transactions t {$whereSQL}";
$stmt = $pdo->prepare($customerAnalysisSQL);
$stmt->execute($params);
$customerStats = $stmt->fetch();

// === STAFF PERFORMANCE ===
$staffPerformanceSQL = "SELECT 
    u.fullname as staff_name,
    COUNT(*) as orders_handled,
    COALESCE(SUM(total_amount), 0) as revenue_generated,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT customer_id) as customers_served
    FROM transactions t 
    JOIN users u ON t.user_id = u.user_id
    {$whereSQL}
    GROUP BY t.user_id
    ORDER BY revenue_generated DESC";
$stmt = $pdo->prepare($staffPerformanceSQL);
$stmt->execute($params);
$staffPerformance = $stmt->fetchAll();

// === CSV EXPORT ===
if(isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="daily_detail_' . $target_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header information
    fputcsv($output, ['Cremoso Daily Detail Report', '', '', '', '']);
    fputcsv($output, ['Date: ' . date('F j, Y', strtotime($target_date)), '', '', '', '']);
    fputcsv($output, ['Generated: ' . date('F j, Y g:i A'), '', '', '', '']);
    fputcsv($output, []);
    
    // Daily summary
    fputcsv($output, ['DAILY SUMMARY', '', '', '', '']);
    fputcsv($output, ['Total Revenue', '₱' . number_format($dailyMetrics['total_revenue'], 2), '', '', '']);
    fputcsv($output, ['Total Orders', $dailyMetrics['total_orders'], '', '', '']);
    fputcsv($output, ['Avg Order Value', '₱' . number_format($dailyMetrics['avg_order_value'], 2), '', '', '']);
    fputcsv($output, ['Unique Customers', $dailyMetrics['unique_customers'], '', '', '']);
    fputcsv($output, []);
    
    // Hourly breakdown
    fputcsv($output, ['HOURLY BREAKDOWN', '', '', '', '']);
    fputcsv($output, ['Hour', 'Orders', 'Revenue', 'Avg Order', 'Customers']);
    foreach($hourlyBreakdown as $hour => $data) {
        if($data['orders'] > 0) {
            fputcsv($output, [
                sprintf('%02d:00', $hour),
                $data['orders'],
                $data['revenue'],
                $data['avg_order'],
                $data['customers']
            ]);
        }
    }
    fputcsv($output, []);
    
    // Top items
    fputcsv($output, ['TOP PERFORMING ITEMS', '', '', '', '']);
    fputcsv($output, ['Rank', 'Item', 'Flavor', 'Size', 'Qty Sold', 'Revenue']);
    foreach($topItems as $idx => $item) {
        fputcsv($output, [
            $idx + 1,
            $item['item_name'],
            $item['category'],
            $item['size'],
            $item['qty_sold'],
            $item['revenue']
        ]);
    }
    fputcsv($output, []);
    
    // Staff performance
    fputcsv($output, ['STAFF PERFORMANCE', '', '', '', '']);
    fputcsv($output, ['Staff Name', 'Orders Handled', 'Revenue Generated', 'Customers Served']);
    foreach($staffPerformance as $staff) {
        fputcsv($output, [
            $staff['staff_name'],
            $staff['orders_handled'],
            $staff['revenue_generated'],
            $staff['customers_served']
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin.css">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>
<?php include __DIR__ . '/../includes/mobile_navbar_admin.php'; ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <nav style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">
                <a href="reports.php" style="color: var(--primary); text-decoration: none;">Reports</a>
                <span style="margin: 0 8px;">›</span>
                <span>Daily Detail</span>
            </nav>
            <h1><i class="fas fa-calendar-day"></i> Daily Report Detail</h1>
            <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 14px;">
                <?= date('l, F j, Y', strtotime($target_date)) ?>
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn-secondary" style="padding: 8px 16px; font-size: 13px;">
                <i class="fas fa-download"></i> Export
            </a>
        </div>
    </div>

    <!-- Active Filters -->
    <?php if($filter_branch || $filter_channel || $filter_payment): ?>
    <div style="background: var(--primary-pale); border: 1px solid var(--primary); border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 13px;">
        <i class="fas fa-filter" style="color: var(--primary); margin-right: 8px;"></i>
        <strong>Active Filters:</strong>
        <?php if($filter_branch): ?><span class="filter-tag">Branch: <?= htmlspecialchars($currentBranch) ?></span><?php endif; ?>
        <?php if($filter_channel): ?><span class="filter-tag">Channel: <?= htmlspecialchars($currentChannel) ?></span><?php endif; ?>
        <?php if($filter_payment): ?><span class="filter-tag">Payment: <?= htmlspecialchars($currentPayment) ?></span><?php endif; ?>
        <a href="daily_detail.php?date=<?= $target_date ?>" style="color: var(--primary); margin-left: 12px; text-decoration: none;">
            <i class="fas fa-times"></i> Clear Filters
        </a>
    </div>
    <?php endif; ?>

    <!-- Daily KPI Summary -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($dailyMetrics['total_revenue'], 2) ?></div>
            <div class="stat-label">Total Revenue</div>
            <div style="margin-top: 8px; font-size: 11px; display: grid; gap: 2px;">
                <?php if($prevDay['revenue'] > 0): ?>
                <div style="color: <?= $revenueGrowthDay >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                    <i class="fas fa-arrow-<?= $revenueGrowthDay >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs(number_format($revenueGrowthDay, 1)) ?>% vs yesterday
                </div>
                <?php endif; ?>
                <?php if($lastWeek['revenue'] > 0): ?>
                <div style="color: <?= $revenueGrowthWeek >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                    <i class="fas fa-arrow-<?= $revenueGrowthWeek >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs(number_format($revenueGrowthWeek, 1)) ?>% vs last week
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($dailyMetrics['total_orders']) ?></div>
            <div class="stat-label">Total Orders</div>
            <div style="margin-top: 8px; font-size: 11px; display: grid; gap: 2px;">
                <?php if($prevDay['orders'] > 0): ?>
                <div style="color: <?= $ordersGrowthDay >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                    <i class="fas fa-arrow-<?= $ordersGrowthDay >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs(number_format($ordersGrowthDay, 1)) ?>% vs yesterday
                </div>
                <?php endif; ?>
                <?php if($monthlyAvg['avg_orders'] > 0): ?>
                <div style="color: <?= $ordersGrowthMonth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                    <i class="fas fa-arrow-<?= $ordersGrowthMonth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs(number_format($ordersGrowthMonth, 1)) ?>% vs monthly avg
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calculator"></i></div>
            <div class="stat-value">₱<?= number_format($dailyMetrics['avg_order_value'], 2) ?></div>
            <div class="stat-label">Avg Order Value</div>
            <?php if($monthlyAvg['avg_revenue'] > 0 && $monthlyAvg['avg_orders'] > 0): ?>
            <div style="margin-top: 8px; font-size: 11px;">
                <div style="color: var(--text-muted);">
                    Monthly avg: ₱<?= number_format($monthlyAvg['avg_revenue'] / $monthlyAvg['avg_orders'], 2) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= number_format($dailyMetrics['unique_customers']) ?></div>
            <div class="stat-label">Unique Customers</div>
            <div style="margin-top: 8px; font-size: 11px; color: var(--text-muted);">
                <?= $dailyMetrics['unique_customers'] > 0 ? number_format($dailyMetrics['total_orders'] / $dailyMetrics['unique_customers'], 1) : 0 ?> orders/customer
            </div>
        </div>
    </div>

    <!-- Enhanced Navigation -->
    <div style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; margin-bottom: 20px; padding: 16px; background: var(--surface-2); border-radius: 8px; border: 1px solid var(--border);">
        <div style="display: flex; gap: 8px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['date' => date('Y-m-d', strtotime($target_date . ' -1 day'))])) ?>" class="btn-secondary" style="padding: 8px 12px; font-size: 13px;">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <a href="reports.php?<?= http_build_query(array_filter(['branch' => $filter_branch, 'channel' => $filter_channel, 'payment' => $filter_payment])) ?>" class="btn-secondary" style="padding: 8px 12px; font-size: 13px;">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
        
        <div style="text-align: center;">
            <div style="font-weight: 600; color: var(--text-dark); font-size: 16px;">
                <?= date('M j, Y', strtotime($target_date)) ?>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                <?= date('l', strtotime($target_date)) ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 8px; justify-content: flex-end;">
            <a href="?<?= http_build_query(array_merge($_GET, ['date' => date('Y-m-d')])) ?>" class="btn-secondary" style="padding: 8px 12px; font-size: 13px;">
                <i class="fas fa-calendar-day"></i> Today
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['date' => date('Y-m-d', strtotime($target_date . ' +1 day'))])) ?>" class="btn-secondary" style="padding: 8px 12px; font-size: 13px;">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Comparison Summary -->
    <?php if($prevDay['revenue'] > 0 || $lastWeek['revenue'] > 0 || $monthlyAvg['avg_revenue'] > 0): ?>
    <div style="background: var(--surface-2); border-radius: 8px; border: 1px solid var(--border); padding: 16px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 12px 0; color: var(--text-dark); font-size: 14px;">
            <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 8px;"></i>
            Performance Comparison
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13px;">
            <?php if($prevDay['revenue'] > 0): ?>
            <div style="padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid var(--border);">
                <div style="color: var(--text-muted); margin-bottom: 4px;">vs Yesterday</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Revenue:</span>
                    <span style="color: <?= $revenueGrowthDay >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $revenueGrowthDay >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($revenueGrowthDay, 1)) ?>%
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Orders:</span>
                    <span style="color: <?= $ordersGrowthDay >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $ordersGrowthDay >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($ordersGrowthDay, 1)) ?>%
                    </span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($lastWeek['revenue'] > 0): ?>
            <div style="padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid var(--border);">
                <div style="color: var(--text-muted); margin-bottom: 4px;">vs Last Week</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Revenue:</span>
                    <span style="color: <?= $revenueGrowthWeek >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $revenueGrowthWeek >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($revenueGrowthWeek, 1)) ?>%
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Orders:</span>
                    <span style="color: <?= $ordersGrowthWeek >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $ordersGrowthWeek >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($ordersGrowthWeek, 1)) ?>%
                    </span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monthlyAvg['avg_revenue'] > 0): ?>
            <div style="padding: 8px 12px; background: white; border-radius: 6px; border: 1px solid var(--border);">
                <div style="color: var(--text-muted); margin-bottom: 4px;">vs Monthly Avg</div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Revenue:</span>
                    <span style="color: <?= $revenueGrowthMonth >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $revenueGrowthMonth >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($revenueGrowthMonth, 1)) ?>%
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Orders:</span>
                    <span style="color: <?= $ordersGrowthMonth >= 0 ? '#1B5E20' : '#B71C1C' ?>; font-weight: 600;">
                        <i class="fas fa-arrow-<?= $ordersGrowthMonth >= 0 ? 'up' : 'down' ?>"></i>
                        <?= abs(number_format($ordersGrowthMonth, 1)) ?>%
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hourly Breakdown -->
    <div class="data-table" style="margin-bottom: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-clock" style="color: var(--primary); margin-right: 8px;"></i>
            Hourly Breakdown
            <?php if($peakHour): ?>
            <span style="font-size: 13px; color: var(--text-muted); font-weight: normal; margin-left: 12px;">
                Peak: <?= sprintf('%02d:00', $peakHour) ?> (₱<?= number_format($peakRevenue, 0) ?>)
            </span>
            <?php endif; ?>
        </h3>
        
        <?php if(array_sum(array_column($hourlyBreakdown, 'orders')) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Hour</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                    <th>Avg Order</th>
                    <th>Customers</th>
                    <th>Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($hourlyBreakdown as $hour => $data): ?>
                <?php if($data['orders'] > 0): ?>
                <tr <?= $hour == $peakHour ? 'style="background: var(--primary-pale);"' : '' ?>>
                    <td>
                        <strong><?= sprintf('%02d:00', $hour) ?></strong>
                        <?php if($hour == $peakHour): ?>
                        <span style="color: var(--primary); font-size: 11px; margin-left: 4px;">
                            <i class="fas fa-crown"></i> PEAK
                        </span>
                        <?php endif; ?>
                    </td>
                    <td><?= $data['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($data['revenue'], 2) ?></td>
                    <td>₱<?= number_format($data['avg_order'], 2) ?></td>
                    <td><?= $data['customers'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; background: var(--primary-pale); border-radius: 10px; height: 8px; overflow: hidden; min-width: 80px;">
                                <div style="width: <?= $maxRevenue > 0 ? ($data['revenue'] / $maxRevenue) * 100 : 0 ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 11px; color: var(--text-muted); min-width: 30px;">
                                <?= $maxRevenue > 0 ? number_format(($data['revenue'] / $maxRevenue) * 100, 0) : 0 ?>%
                            </span>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: var(--primary-pale); font-weight: 700;">
                    <td>TOTAL</td>
                    <td><?= array_sum(array_column($hourlyBreakdown, 'orders')) ?></td>
                    <td class="item-price">₱<?= number_format(array_sum(array_column($hourlyBreakdown, 'revenue')), 2) ?></td>
                    <td>-</td>
                    <td><?= array_sum(array_column($hourlyBreakdown, 'customers')) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Business Hours Summary -->
        <div style="padding: 16px 20px; background: var(--surface-2); border-top: 1px solid var(--border); font-size: 13px; color: var(--text-muted);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <strong style="color: var(--text-dark);">Business Hours:</strong>
                    <?php 
                    $activeHours = array_filter($hourlyBreakdown, fn($h) => $h['orders'] > 0);
                    if(count($activeHours) > 0) {
                        $firstHour = min(array_keys($activeHours));
                        $lastHour = max(array_keys($activeHours));
                        echo sprintf('%02d:00 - %02d:59', $firstHour, $lastHour);
                    } else {
                        echo 'No activity';
                    }
                    ?>
                </div>
                <div>
                    <strong style="color: var(--text-dark);">Peak Hour:</strong>
                    <?= sprintf('%02d:00 - %02d:59', $peakHour, $peakHour) ?>
                </div>
                <div>
                    <strong style="color: var(--text-dark);">Active Hours:</strong>
                    <?= count($activeHours) ?> hours
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-clock" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-dark); margin-bottom: 8px;">No Hourly Data</h3>
            <p>No transactions found for this date with the selected filters.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Performing Items -->
    <div class="data-table" style="margin-bottom: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-star" style="color: var(--primary); margin-right: 8px;"></i>
            Top Performing Items
        </h3>
        
        <?php if(count($topItems) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Qty Sold</th>
                    <th>Times Ordered</th>
                    <th>Revenue</th>
                    <th>Share</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalItemRevenue = array_sum(array_column($topItems, 'revenue'));
                foreach($topItems as $idx => $item): 
                    $share = $totalItemRevenue > 0 ? ($item['revenue'] / $totalItemRevenue) * 100 : 0;
                ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: var(--primary); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                                <?= $idx + 1 ?>
                            </span>
                        </div>
                    </td>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td><?= $item['qty_sold'] ?></td>
                    <td><?= $item['times_ordered'] ?></td>
                    <td class="item-price">₱<?= number_format($item['revenue'], 2) ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; background: var(--primary-pale); border-radius: 10px; height: 8px; overflow: hidden; min-width: 60px;">
                                <div style="width: <?= $share ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);"><?= number_format($share, 1) ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-ice-cream" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-dark); margin-bottom: 8px;">No Items Sold</h3>
            <p>No items were sold on this date with the selected filters.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Performance Analysis -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <!-- Customer Analysis -->
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
                <i class="fas fa-users" style="color: var(--primary); margin-right: 8px;"></i>
                Customer Analysis
            </h3>
            <div style="padding: 20px;">
                <div style="display: grid; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--surface-2); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Total Customers</span>
                        <strong style="color: var(--text-dark);"><?= number_format($customerStats['total_customers']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--surface-2); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Orders per Customer</span>
                        <strong style="color: var(--text-dark);"><?= number_format($customerStats['orders_per_customer'], 1) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--surface-2); border-radius: 8px;">
                        <span style="color: var(--text-muted);">Avg Order Value</span>
                        <strong style="color: var(--text-dark);">₱<?= number_format($customerStats['avg_order_value'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Performance -->
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
                <i class="fas fa-user-tie" style="color: var(--primary); margin-right: 8px;"></i>
                Staff Performance
            </h3>
            <?php if(count($staffPerformance) > 0): ?>
            <div style="padding: 20px;">
                <?php foreach($staffPerformance as $staff): ?>
                <div style="margin-bottom: 16px; padding: 12px; background: var(--surface-2); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: var(--text-dark);"><?= htmlspecialchars($staff['staff_name']) ?></strong>
                        <span style="color: var(--primary); font-weight: 600;">₱<?= number_format($staff['revenue_generated'], 0) ?></span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; color: var(--text-muted);">
                        <span><?= $staff['orders_handled'] ?> orders</span>
                        <span><?= $staff['customers_served'] ?> customers</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                <p>No staff data available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Footer with Insights -->
    <?php if($dailyMetrics['total_orders'] > 0): ?>
    <div style="background: var(--surface-2); border-radius: 8px; border: 1px solid var(--border); padding: 20px; margin-top: 24px;">
        <h4 style="margin: 0 0 16px 0; color: var(--text-dark); font-size: 16px;">
            <i class="fas fa-lightbulb" style="color: var(--primary); margin-right: 8px;"></i>
            Daily Insights & Summary
        </h4>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; font-size: 14px;">
            <div>
                <h5 style="color: var(--text-dark); margin: 0 0 8px 0; font-size: 14px;">Performance Highlights</h5>
                <ul style="margin: 0; padding-left: 16px; color: var(--text-muted); line-height: 1.6;">
                    <li>Generated <strong style="color: var(--primary);">₱<?= number_format($dailyMetrics['total_revenue'], 2) ?></strong> from <?= $dailyMetrics['total_orders'] ?> orders</li>
                    <li>Served <strong><?= $dailyMetrics['unique_customers'] ?></strong> unique customers</li>
                    <?php if($peakHour): ?>
                    <li>Peak sales hour: <strong><?= sprintf('%02d:00', $peakHour) ?></strong> (₱<?= number_format($peakRevenue, 0) ?>)</li>
                    <?php endif; ?>
                    <?php if(count($topItems) > 0): ?>
                    <li>Top item: <strong><?= htmlspecialchars($topItems[0]['item_name']) ?></strong> (<?= $topItems[0]['qty_sold'] ?> sold)</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div>
                <h5 style="color: var(--text-dark); margin: 0 0 8px 0; font-size: 14px;">Key Metrics</h5>
                <ul style="margin: 0; padding-left: 16px; color: var(--text-muted); line-height: 1.6;">
                    <li>Average order value: <strong>₱<?= number_format($dailyMetrics['avg_order_value'], 2) ?></strong></li>
                    <li>Orders per customer: <strong><?= number_format($customerStats['orders_per_customer'], 1) ?></strong></li>
                    <?php 
                    $activeHours = array_filter($hourlyBreakdown, fn($h) => $h['orders'] > 0);
                    if(count($activeHours) > 0):
                    ?>
                    <li>Active business hours: <strong><?= count($activeHours) ?> hours</strong></li>
                    <?php endif; ?>
                    <?php if(count($staffPerformance) > 1): ?>
                    <li>Staff members active: <strong><?= count($staffPerformance) ?></strong></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 12px; color: var(--text-muted); text-align: center;">
            <i class="fas fa-info-circle" style="margin-right: 4px;"></i>
            Report generated on <?= date('F j, Y g:i A') ?> | 
            Data for <?= date('l, F j, Y', strtotime($target_date)) ?>
            <?php if($filter_branch || $filter_channel || $filter_payment): ?>
            | Filtered results
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>