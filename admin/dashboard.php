<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === KPI METRICS ===
$todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetchColumn();
$todayOrders = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetchColumn();

$yesterdaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE DATE(transaction_date) = CURDATE() - INTERVAL 1 DAY")->fetchColumn();
$yesterdayOrders = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = CURDATE() - INTERVAL 1 DAY")->fetchColumn();

$weekSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$monthSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE())")->fetchColumn();

// === RECENT TRANSACTIONS (last 8) ===
$salesGrowth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;
$ordersGrowth = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : 0;

$recentTxns = $pdo->query("SELECT t.*, c.customer_name, u.fullname as staff, b.branch_name, pm.method_name
                           FROM transactions t
                           JOIN customers c ON t.customer_id = c.customer_id
                           JOIN users u ON t.user_id = u.user_id
                           JOIN branches b ON t.branch_id = b.branch_id
                           JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
                           ORDER BY t.transaction_date DESC LIMIT 8")->fetchAll();

// === TOP SELLING ITEMS (this month, top 6) ===
$topItems = $pdo->query("SELECT i.item_name, f.flavor_name, SUM(ti.quantity) as qty, SUM(ti.subtotal) as revenue
                         FROM transaction_items ti
                         JOIN items i ON ti.item_id = i.item_id
                         JOIN flavors f ON i.flavor_id = f.flavor_id
                         JOIN transactions t ON ti.transaction_id = t.transaction_id
                         WHERE MONTH(t.transaction_date) = MONTH(CURDATE()) AND YEAR(t.transaction_date) = YEAR(CURDATE())
                         GROUP BY ti.item_id
                         ORDER BY qty DESC LIMIT 6")->fetchAll();

// === BRANCH PERFORMANCE (this month) ===
$branchPerf = $pdo->query("SELECT b.branch_name, b.location, COUNT(t.transaction_id) as orders, COALESCE(SUM(t.total_amount),0) as revenue
                           FROM branches b
                           LEFT JOIN transactions t ON b.branch_id = t.branch_id AND MONTH(t.transaction_date) = MONTH(CURDATE()) AND YEAR(t.transaction_date) = YEAR(CURDATE())
                           GROUP BY b.branch_id
                           ORDER BY revenue DESC")->fetchAll();

// === REVENUE TREND (last 7 days) ===
$weeklyTrend = $pdo->query("SELECT DATE(transaction_date) as day, SUM(total_amount) as total, COUNT(*) as orders
                            FROM transactions
                            WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                            GROUP BY DATE(transaction_date)
                            ORDER BY day")->fetchAll();

// Format trend data for charts
$trendLabels = array_map(fn($r) => date('D', strtotime($r['day'])), $weeklyTrend);
$trendValues = array_map(fn($r) => $r['total'], $weeklyTrend);
$orderTrendValues = array_map(fn($r) => $r['orders'], $weeklyTrend);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div class="user-info">
            <i class="fas fa-calendar-day"></i>
            <span><?= date('l, F j, Y') ?></span>
            <span class="branch-badge">Admin</span>
        </div>
    </div>

    <!-- KPI Row 1: Today -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($todaySales, 2) ?></div>
            <div class="stat-label">Today's Revenue</div>
            <?php if($yesterdaySales > 0): ?>
            <div style="margin-top: 8px; font-size: 12px; color: <?= $salesGrowth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                <i class="fas fa-arrow-<?= $salesGrowth >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($salesGrowth, 1)) ?>% vs yesterday
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= $todayOrders ?></div>
            <div class="stat-label">Today's Orders</div>
            <?php if($yesterdayOrders > 0): ?>
            <div style="margin-top: 8px; font-size: 12px; color: <?= $ordersGrowth >= 0 ? '#1B5E20' : '#B71C1C' ?>;">
                <i class="fas fa-arrow-<?= $ordersGrowth >= 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($ordersGrowth, 1)) ?>% vs yesterday
            </div>
            <?php endif; ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
            <div class="stat-value">₱<?= number_format($weekSales, 2) ?></div>
            <div class="stat-label">Last 7 Days Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value">₱<?= number_format($monthSales, 2) ?></div>
            <div class="stat-label">This Month's Revenue</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Revenue Trend (Last 7 Days)</h3>
            <canvas id="revenueChart" width="300" height="180" style="max-width:100%"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Orders Per Day (Last 7 Days)</h3>
            <canvas id="ordersChart" width="300" height="180" style="max-width:100%"></canvas>
        </div>
    </div>

    <!-- Two-Column: Recent Transactions + Top Items -->
    <div class="dashboard-two-column">
        <?php if (count($recentTxns) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-clock" style="color: var(--primary); margin-right: 8px;"></i>Recent Transactions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentTxns as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t['order_number']) ?></strong></td>
                        <td><?= htmlspecialchars($t['customer_name']) ?></td>
                        <td><?= htmlspecialchars($t['branch_name']) ?></td>
                        <td class="item-price">₱<?= number_format($t['total_amount'], 2) ?></td>
                        <td><span class="status-badge status-active"><?= htmlspecialchars($t['method_name']) ?></span></td>
                        <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, h:i A', strtotime($t['transaction_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if(count($topItems) > 0): ?>
        <div class="data-table">
            <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-fire" style="color: var(--primary); margin-right: 8px;"></i>Top Items (<?= date('F') ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($topItems as $idx => $item): ?>
                    <tr>
                        <td><span class="status-badge status-active"><?= $idx + 1 ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($item['flavor_name']) ?></div>
                        </td>
                        <td><?= $item['qty'] ?></td>
                        <td class="item-price">₱<?= number_format($item['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Branch Performance -->
    <?php if(count($branchPerf) > 0): ?>
    <div class="data-table" style="margin-top: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);"><i class="fas fa-store" style="color: var(--primary); margin-right: 8px;"></i>Branch Performance (<?= date('F Y') ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Location</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                    <th>Share</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalBranchRevenue = array_sum(array_column($branchPerf, 'revenue'));
                foreach($branchPerf as $b):
                    $share = $totalBranchRevenue > 0 ? ($b['revenue'] / $totalBranchRevenue) * 100 : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                    <td><?= htmlspecialchars($b['location']) ?></td>
                    <td><?= $b['orders'] ?></td>
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

</div>

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
const trendLabels = <?= json_encode($trendLabels) ?>;
const trendValues = <?= json_encode($trendValues) ?>;
if (trendLabels.length > 0) {
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: trendValues,
                borderColor: chartColors.primary,
                backgroundColor: 'rgba(45, 168, 155, 0.10)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: chartColors.primaryDark,
                pointRadius: 5,
                pointHoverRadius: 7
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

// Orders per day chart
const orderTrendValues = <?= json_encode($orderTrendValues) ?>;
if (trendLabels.length > 0) {
    new Chart(document.getElementById('ordersChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Orders',
                data: orderTrendValues,
                backgroundColor: chartColors.accent1,
                borderRadius: 8,
                barThickness: 40,
                maxBarThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
