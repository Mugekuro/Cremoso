<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === DATE RANGE FILTERS ===
$date_range = $_GET['range'] ?? '30';
$date_filter = match($date_range) {
    '7' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = \'confirmed\'',
    '30' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status = \'confirmed\'',
    '90' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND status = \'confirmed\'',
    '365' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND status = \'confirmed\'',
    default => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status = \'confirmed\''
};

$date_label = match($date_range) {
    '7' => 'Last 7 Days',
    '30' => 'Last 30 Days',
    '90' => 'Last 90 Days',
    '365' => 'Last 12 Months',
    default => 'Last 30 Days'
};

// === KPI METRICS ===
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions {$date_filter}")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM transactions {$date_filter}")->fetchColumn();
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
$totalItems = $pdo->query("SELECT COALESCE(SUM(ti.quantity),0) FROM transaction_items ti JOIN transactions t ON ti.transaction_id = t.transaction_id {$date_filter}")->fetchColumn();

// Previous period comparison
$prev_start = match($date_range) {
    '7' => 'DATE_SUB(CURDATE(), INTERVAL 14 DAY)',
    '30' => 'DATE_SUB(CURDATE(), INTERVAL 60 DAY)',
    '90' => 'DATE_SUB(CURDATE(), INTERVAL 180 DAY)',
    '365' => 'DATE_SUB(CURDATE(), INTERVAL 2 YEAR)',
    default => 'DATE_SUB(CURDATE(), INTERVAL 60 DAY)'
};

$prev_end = match($date_range) {
    '7' => 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
    '30' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)',
    '90' => 'DATE_SUB(CURDATE(), INTERVAL 90 DAY)',
    '365' => 'DATE_SUB(CURDATE(), INTERVAL 1 YEAR)',
    default => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
};

$prevFilter = "WHERE transaction_date >= {$prev_start} AND transaction_date < {$prev_end} AND status = 'confirmed'";
$prevRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions {$prevFilter}")->fetchColumn();
$prevOrders = $pdo->query("SELECT COUNT(*) FROM transactions {$prevFilter}")->fetchColumn();

$revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
$ordersGrowth = $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

// === REVENUE BY DAY (for chart) ===
$dailyRevenue = $pdo->query("SELECT DATE(transaction_date) as day, SUM(total_amount) as total, COUNT(*) as orders
                             FROM transactions {$date_filter}
                             GROUP BY DATE(transaction_date)
                             ORDER BY day")->fetchAll();

$revenueLabels = array_map(fn($r) => date('M d', strtotime($r['day'])), $dailyRevenue);
$revenueData = array_map(fn($r) => $r['total'], $dailyRevenue);
$ordersData = array_map(fn($r) => $r['orders'], $dailyRevenue);

// === PAYMENT METHOD BREAKDOWN ===
$paymentBreakdown = $pdo->query("SELECT pm.method_name, COUNT(t.transaction_id) as count, COALESCE(SUM(t.total_amount),0) as total
                                 FROM payment_methods pm
                                 LEFT JOIN transactions t ON pm.payment_method_id = t.payment_method_id {$date_filter}
                                 GROUP BY pm.payment_method_id
                                 ORDER BY total DESC")->fetchAll();

$paymentTotal = array_sum(array_column($paymentBreakdown, 'total'));
$paymentLabels = array_column($paymentBreakdown, 'method_name');
$paymentValues = array_map(fn($r) => $r['total'], $paymentBreakdown);
$paymentCounts = array_column($paymentBreakdown, 'count');

// === ORDER CHANNEL BREAKDOWN ===
$channelBreakdown = $pdo->query("SELECT oc.channel_name, COUNT(t.transaction_id) as count, COALESCE(SUM(t.total_amount),0) as total
                                 FROM order_channels oc
                                 LEFT JOIN transactions t ON oc.channel_id = t.channel_id {$date_filter}
                                 GROUP BY oc.channel_id
                                 ORDER BY total DESC")->fetchAll();

$channelTotal = array_sum(array_column($channelBreakdown, 'total'));
$channelLabels = array_column($channelBreakdown, 'channel_name');
$channelValues = array_map(fn($r) => $r['total'], $channelBreakdown);

// === TOP SELLING ITEMS ===
$topItems = $pdo->query("SELECT ti.item_name, ti.category, ti.size, SUM(ti.quantity) as qty, COALESCE(SUM(ti.subtotal),0) as revenue
                         FROM transaction_items ti
                         JOIN transactions t ON ti.transaction_id = t.transaction_id
                         {$date_filter}
                         GROUP BY ti.item_name, ti.category, ti.size
                         ORDER BY revenue DESC
                         LIMIT 10")->fetchAll();

// === BOTTOM SELLING ITEMS ===
$bottomItems = $pdo->query("SELECT ti.item_name, ti.category, ti.size, SUM(ti.quantity) as qty, COALESCE(SUM(ti.subtotal),0) as revenue
                            FROM transaction_items ti
                            JOIN transactions t ON ti.transaction_id = t.transaction_id
                            {$date_filter}
                            GROUP BY ti.item_name, ti.category, ti.size
                            ORDER BY revenue ASC
                            LIMIT 5")->fetchAll();

// === BRANCH PERFORMANCE ===
$branchPerf = $pdo->query("SELECT b.branch_name, b.location, COUNT(t.transaction_id) as orders, COALESCE(SUM(t.total_amount),0) as revenue, AVG(t.total_amount) as avg_order
                           FROM branches b
                           LEFT JOIN transactions t ON b.branch_id = t.branch_id {$date_filter}
                           GROUP BY b.branch_id
                           ORDER BY revenue DESC")->fetchAll();

$totalBranchRevenue = array_sum(array_column($branchPerf, 'revenue'));

// === HOURLY SALES PATTERN ===
$hourlySales = $pdo->query("SELECT HOUR(transaction_date) as hour, SUM(total_amount) as total, COUNT(*) as orders
                            FROM transactions {$date_filter}
                            GROUP BY HOUR(transaction_date)
                            ORDER BY hour")->fetchAll();

$hourLabels = array_map(fn($r) => date('g A', mktime($r['hour'], 0, 0)), $hourlySales);
$hourValues = array_map(fn($r) => $r['total'], $hourlySales);

// === TOP CUSTOMERS ===
$topCustomers = $pdo->query("SELECT c.customer_name, COUNT(t.transaction_id) as orders, COALESCE(SUM(t.total_amount),0) as total
                             FROM customers c
                             JOIN transactions t ON c.customer_id = t.customer_id
                             {$date_filter}
                             GROUP BY c.customer_id
                             ORDER BY total DESC
                             LIMIT 10")->fetchAll();

// === DAILY AVERAGE ===
$num_days = match($date_range) {
    '7' => 7,
    '30' => 30,
    '90' => 90,
    '365' => 365,
    default => 30
};
$dailyAvg = $num_days > 0 ? $totalRevenue / $num_days : 0;
$dailyAvgOrders = $num_days > 0 ? $totalOrders / $num_days : 0;
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Analytics</h1>
        <div style="display: flex; align-items: center; gap: 12px;">
            <form method="GET" style="display: flex; align-items: center; gap: 8px;">
                <label for="range" style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Period:</label>
                <select id="range" name="range" onchange="this.form.submit()" style="padding: 8px 16px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-family: inherit; background: var(--surface); color: var(--text-dark); cursor: pointer;">
                    <option value="7" <?= $date_range == '7' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30" <?= $date_range == '30' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="90" <?= $date_range == '90' ? 'selected' : '' ?>>Last 90 Days</option>
                    <option value="365" <?= $date_range == '365' ? 'selected' : '' ?>>Last 12 Months</option>
                </select>
            </form>
            <span style="font-size: 13px; color: var(--text-muted); background: var(--primary-pale); padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border);">
                <i class="fas fa-calendar-alt"></i> <?= $date_label ?>
            </span>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
            <?php if($prevRevenue > 0): ?>
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
            <?php if($prevOrders > 0): ?>
            <div style="margin-top: 8px; font-size: 12px; color: <?= $ordersGrowth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                <i class="fas fa-arrow-<?= $ordersGrowth >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($ordersGrowth, 1)) ?>% vs previous period
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calculator"></i></div>
            <div class="stat-value">₱<?= number_format($avgOrderValue, 2) ?></div>
            <div class="stat-label">Average Order Value</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-value">₱<?= number_format($dailyAvg, 2) ?></div>
            <div class="stat-label">Daily Average Revenue</div>
            <div style="margin-top: 8px; font-size: 12px; color: var(--text-muted);">
                <?= number_format($dailyAvgOrders, 1) ?> orders/day
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-value"><?= number_format($totalItems) ?></div>
            <div class="stat-label">Items Sold</div>
        </div>
    </div>

    <!-- Charts Row: Full-width Daily Revenue + 2-column Pie Charts -->
    <!-- Daily Revenue (Full Width) -->
    <div class="chart-card" style="margin-bottom: 16px;">
        <h3 style="font-size: 13px; margin-bottom: 10px;"><i class="fas fa-chart-line"></i> Daily Revenue</h3>
        <canvas id="revenueChart" width="800" height="200" style="max-width:100%"></canvas>
    </div>

    <!-- Pie Charts Row -->
    <div class="analytics-pie-charts">
        <!-- Payment Method Breakdown -->
        <div class="chart-card" style="padding: 14px 14px;">
            <h3 style="font-size: 12px; margin-bottom: 8px;"><i class="fas fa-credit-card"></i> Payment Methods</h3>
            <canvas id="paymentChart" width="200" height="100" style="max-width:100%"></canvas>
            <div style="margin-top: 10px;">
                <?php foreach($paymentBreakdown as $idx => $p): 
                    $pct = $paymentTotal > 0 ? ($p['total'] / $paymentTotal) * 100 : 0;
                ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?= getPaymentColor($idx) ?>;"></span>
                        <span style="font-size: 11px; color: var(--text-body);"><?= htmlspecialchars($p['method_name']) ?></span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-dark);">₱<?= number_format($p['total'], 0) ?></div>
                        <div style="font-size: 10px; color: var(--text-muted);"><?= number_format($pct, 0) ?>%</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Channel Breakdown -->
        <div class="chart-card" style="padding: 14px 14px;">
            <h3 style="font-size: 12px; margin-bottom: 8px;"><i class="fas fa-store"></i> Order Channels</h3>
            <canvas id="channelChart" width="200" height="100" style="max-width:100%"></canvas>
            <div style="margin-top: 10px;">
                <?php foreach($channelBreakdown as $idx => $c): 
                    $pct = $channelTotal > 0 ? ($c['total'] / $channelTotal) * 100 : 0;
                ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?= getChannelColor($idx) ?>;"></span>
                        <span style="font-size: 11px; color: var(--text-body);"><?= htmlspecialchars($c['channel_name']) ?></span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-dark);">₱<?= number_format($c['total'], 0) ?></div>
                        <div style="font-size: 10px; color: var(--text-muted);"><?= number_format($pct, 0) ?>%</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Two-Column: Top Items + Hourly Sales -->
    <div class="analytics-two-column">
        <!-- Top Selling Items -->
        <?php if(count($topItems) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-fire" style="color: var(--primary); margin-right: 8px;"></i>Top 10 Selling Items (<?= $date_label ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Size</th>
                        <th>Qty Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topItems as $idx => $item): ?>
                    <tr>
                        <td><span class="status-badge status-active"><?= $idx + 1 ?></span></td>
                        <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= htmlspecialchars($item['size']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td class="item-price">₱<?= number_format($item['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Hourly Sales Pattern -->
        <?php if(count($hourlySales) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-clock" style="color: var(--primary); margin-right: 8px;"></i>Hourly Sales Pattern</h3>
            <canvas id="hourlyChart" width="300" height="180" style="max-width:100%; margin: 12px;"></canvas>
            <div style="padding: 0 12px 16px;">
                <?php
                $peakHour = array_reduce($hourlySales, fn($carry, $item) => $item['total'] > ($carry['total'] ?? 0) ? $item : $carry, []);
                ?>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                    <i class="fas fa-star" style="color: var(--accent2);"></i>
                    Peak Hour: <strong><?= date('g A', mktime($peakHour['hour'], 0, 0)) ?></strong> (₱<?= number_format($peakHour['total'], 2) ?>)
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bottom Selling Items -->
    <?php if(count($bottomItems) > 0): ?>
    <div class="data-table" style="margin-top: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-arrow-down" style="color: #EF4444; margin-right: 8px;"></i>Bottom 5 Selling Items (<?= $date_label ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Qty Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bottomItems as $idx => $item): ?>
                <tr>
                    <td><span class="status-badge status-inactive"><?= $idx + 1 ?></span></td>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td class="item-price">₱<?= number_format($item['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Branch Performance + Top Customers -->
    <div class="analytics-two-column">
        <!-- Branch Performance -->
        <?php if(count($branchPerf) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-store" style="color: var(--primary); margin-right: 8px;"></i>Branch Performance</h3>
            <table>
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Location</th>
                        <th>Orders</th>
                        <th>Avg Order</th>
                        <th>Revenue</th>
                        <th>Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($branchPerf as $b): 
                        $share = $totalBranchRevenue > 0 ? ($b['revenue'] / $totalBranchRevenue) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                        <td style="font-size: 12px;"><?= htmlspecialchars($b['location']) ?></td>
                        <td><?= $b['orders'] ?></td>
                        <td class="item-price">₱<?= number_format($b['avg_order'], 2) ?></td>
                        <td class="item-price">₱<?= number_format($b['revenue'], 2) ?></td>
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
        </div>
        <?php endif; ?>

        <!-- Top Customers -->
        <?php if(count($topCustomers) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-users" style="color: var(--primary); margin-right: 8px;"></i>Top 10 Customers</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topCustomers as $idx => $cust): ?>
                    <tr>
                        <td><span class="status-badge status-active"><?= $idx + 1 ?></span></td>
                        <td><strong><?= htmlspecialchars($cust['customer_name']) ?></strong></td>
                        <td><?= $cust['orders'] ?></td>
                        <td class="item-price">₱<?= number_format($cust['total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php
// Helper functions for chart colors
function getPaymentColor($idx) {
    $colors = ['#2DA89B', '#6366F1', '#F59E0B', '#EF4444', '#10B981'];
    return $colors[$idx % count($colors)];
}

function getChannelColor($idx) {
    $colors = ['#6366F1', '#F59E0B', '#EF4444', '#10B981', '#2DA89B'];
    return $colors[$idx % count($colors)];
}
?>

<script>
const chartColors = {
    primary: '#2DA89B',
    primaryDark: '#1E8078',
    primaryLight: '#4ABFB3',
    primaryPale: '#E0F5F3',
    accent1: '#6366F1',
    accent2: '#F59E0B',
    accent3: '#EF4444',
    accent4: '#10B981',
};

// Revenue trend chart
const revenueLabels = <?= json_encode($revenueLabels) ?>;
const revenueData = <?= json_encode($revenueData) ?>;
if (revenueLabels.length > 0) {
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: revenueData,
                borderColor: chartColors.primary,
                backgroundColor: 'rgba(45, 168, 155, 0.10)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: chartColors.primaryDark,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString(), font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { ticks: { font: { size: 9 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });
}

// Payment method doughnut chart
const paymentLabels = <?= json_encode($paymentLabels) ?>;
const paymentValues = <?= json_encode($paymentValues) ?>;
if (paymentLabels.length > 0) {
    new Chart(document.getElementById('paymentChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: paymentLabels,
            datasets: [{
                data: paymentValues,
                backgroundColor: [chartColors.primary, chartColors.accent1, chartColors.accent2, chartColors.accent3, chartColors.accent4],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '55%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₱' + ctx.parsed.toLocaleString()
                    }
                }
            }
        }
    });
}

// Channel doughnut chart
const channelLabels = <?= json_encode($channelLabels) ?>;
const channelValues = <?= json_encode($channelValues) ?>;
if (channelLabels.length > 0) {
    new Chart(document.getElementById('channelChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: channelLabels,
            datasets: [{
                data: channelValues,
                backgroundColor: [chartColors.accent1, chartColors.accent2, chartColors.accent3, chartColors.accent4, chartColors.primary],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '55%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₱' + ctx.parsed.toLocaleString()
                    }
                }
            }
        }
    });
}

// Hourly sales bar chart
const hourLabels = <?= json_encode($hourLabels) ?>;
const hourValues = <?= json_encode($hourValues) ?>;
if (hourLabels.length > 0) {
    new Chart(document.getElementById('hourlyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: hourLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: hourValues,
                backgroundColor: chartColors.accent1,
                borderRadius: 6,
                barThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
            }
        }
    });
}
</script>

<style>
/* Analytics page specific responsive adjustments */
.analytics-charts-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.analytics-charts-row .chart-card {
    padding: 14px 14px;
    min-height: 0;
}

.analytics-charts-row .chart-card h3 {
    font-size: 12px;
    margin-bottom: 8px;
}

.analytics-charts-row .chart-card canvas {
    max-height: 160px;
}

@media (max-width: 1024px) {
    .analytics-charts-row {
        grid-template-columns: 1fr !important;
    }
    
    .analytics-charts-row .chart-card canvas {
        max-height: 200px;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
