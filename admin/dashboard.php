<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === KPI METRICS ===
$todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE DATE(transaction_date) = CURDATE() AND status = 'completed'")->fetchColumn();
$todayOrders = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = CURDATE() AND status = 'completed'")->fetchColumn();

$yesterdaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE DATE(transaction_date) = CURDATE() - INTERVAL 1 DAY AND status = 'completed'")->fetchColumn();
$yesterdayOrders = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = CURDATE() - INTERVAL 1 DAY AND status = 'completed'")->fetchColumn();

$weekSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'completed'")->fetchColumn();
$monthSales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE()) AND status = 'completed'")->fetchColumn();

$salesGrowth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;
$ordersGrowth = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : 0;

// === RECENT TRANSACTIONS (paginated, up to 10) ===
$search = $_GET['search'] ?? '';
$txn_page = isset($_GET['txn_page']) ? max(1, (int)$_GET['txn_page']) : 1;
$txn_perPage = 10;
$txn_offset = ($txn_page - 1) * $txn_perPage;

$whereClause = "WHERE 1=1";
$params = [];
if ($search) {
    $whereClause .= " AND (t.order_number LIKE ? OR c.customer_name LIKE ? OR b.branch_name LIKE ? OR pm.method_name LIKE ?)";
    $searchParam = "%{$search}%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
}

$countSql = "SELECT COUNT(*) FROM transactions t
             JOIN customers c ON t.customer_id = c.customer_id
             JOIN users u ON t.user_id = u.user_id
             JOIN branches b ON t.branch_id = b.branch_id
             JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
             {$whereClause} AND t.status = 'completed'";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalTxnRecords = $countStmt->fetchColumn();
$totalTxnPages = ceil($totalTxnRecords / $txn_perPage);

$sql = "SELECT t.*, c.customer_name, u.fullname as staff, b.branch_name, pm.method_name
        FROM transactions t
        JOIN customers c ON t.customer_id = c.customer_id
        JOIN users u ON t.user_id = u.user_id
        JOIN branches b ON t.branch_id = b.branch_id
        JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
        {$whereClause} AND t.status = 'completed'
        ORDER BY t.transaction_date DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(':limit', $txn_perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $txn_offset, PDO::PARAM_INT);
$stmt->execute();
$recentTxns = $stmt->fetchAll();

// === BEST SELLER (this month, top 1) ===
$bestSeller = $pdo->query("SELECT ti.item_name, SUM(ti.quantity) as qty
                           FROM transaction_items ti
                           JOIN transactions t ON ti.transaction_id = t.transaction_id
                           WHERE MONTH(t.transaction_date) = MONTH(CURDATE()) AND YEAR(t.transaction_date) = YEAR(CURDATE()) AND t.status = 'completed'
                           GROUP BY ti.item_name
                           ORDER BY qty DESC LIMIT 1")->fetch();

// === TOP BRANCH (this month) ===
$topBranch = $pdo->query("SELECT b.branch_name, COALESCE(SUM(t.total_amount),0) as revenue,
                           COUNT(t.transaction_id) as orders
                           FROM branches b
                           LEFT JOIN transactions t ON b.branch_id = t.branch_id
                               AND MONTH(t.transaction_date) = MONTH(CURDATE())
                               AND YEAR(t.transaction_date) = YEAR(CURDATE())
                               AND t.status = 'completed'
                           GROUP BY b.branch_id
                           ORDER BY revenue DESC LIMIT 1")->fetch();

$totalMonthRevenue = $monthSales;
$topBranchShare = ($topBranch && $totalMonthRevenue > 0) ? ($topBranch['revenue'] / $totalMonthRevenue) * 100 : 0;

// === REVENUE TREND (last 7 days) ===
$weeklyTrend = $pdo->query("SELECT DATE(transaction_date) as day, SUM(total_amount) as total, COUNT(*) as orders
                            FROM transactions
                            WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status = 'completed'
                            GROUP BY DATE(transaction_date)
                            ORDER BY day")->fetchAll();

$trendLabels = array_map(fn($r) => date('D', strtotime($r['day'])), $weeklyTrend);
$trendValues = array_map(fn($r) => $r['total'], $weeklyTrend);
$orderTrendValues = array_map(fn($r) => $r['orders'], $weeklyTrend);

// Check if user just logged in
$showLoginNotification = false;
if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    $showLoginNotification = true;
    unset($_SESSION['just_logged_in']);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    </div>

    <!-- KPI Cards -->
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

    <!-- Revenue & Orders Charts (side by side) -->
    <div class="dashboard-two-column" style="margin-top: 24px;">
        <div class="chart-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 16px;"><i class="fas fa-chart-line"></i> Revenue (Last 7 Days)</h3>
            <canvas id="revenueChart" style="max-width: 100%; max-height: 320px;"></canvas>
        </div>
        <div class="chart-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 16px;"><i class="fas fa-chart-bar"></i> Orders (Last 7 Days)</h3>
            <canvas id="ordersChart" style="max-width: 100%; max-height: 320px;"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <?php if (count($recentTxns) > 0 || $search): ?>
    <div class="data-table dashboard-flat-table" style="margin-top: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark); font-weight: bold;">
            <i class="fas fa-clock" style="color: var(--primary); margin-right: 8px;"></i>Recent Transactions
        </h3>
        <table style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 14%;">
                <col style="width: 12%;">
                <col style="width: 13%;">
                <col style="width: 11%;">
                <col style="width: 10%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 16%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th style="text-align: center;">Customer</th>
                    <th>Branch</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Staff</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentTxns as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['order_number']) ?></strong></td>
                    <td style="text-align: center; padding-left: 16px;"><?= htmlspecialchars($t['customer_name']) ?></td>
                    <td><?= htmlspecialchars($t['branch_name']) ?></td>
                    <td class="item-price">₱<?= number_format($t['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($t['method_name']) ?></td>
                    <td><span class="status-badge <?= $t['status'] == 'completed' ? 'status-completed' : 'status-pending' ?>"><?= ucfirst($t['status']) ?></span></td>
                    <td><?= htmlspecialchars($t['staff']) ?></td>
                    <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, h:i A', strtotime($t['transaction_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($recentTxns) == 0): ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-search" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No transactions found
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalTxnPages > 1): ?>
    <div class="pagination-container" style="margin-top: 40px;">
        <div class="pagination-info">
            Showing <?= count($recentTxns) ?> of <?= number_format($totalTxnRecords) ?> transactions (Page <?= $txn_page ?> of <?= $totalTxnPages ?>)
        </div>
        <div class="pagination-buttons">
            <?php
            $queryString = $search ? 'search=' . urlencode($search) : '';
            $separator = $queryString ? '&' : '';
            ?>

            <?php if ($txn_page > 1): ?>
                <a href="?txn_page=<?= $txn_page - 1 ?><?= $separator ? $separator . $queryString : '' ?>" class="btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="btn-disabled" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            <?php endif; ?>

            <span class="page-indicator"><?= $txn_page ?> / <?= $totalTxnPages ?></span>

            <?php if ($txn_page < $totalTxnPages): ?>
                <a href="?txn_page=<?= $txn_page + 1 ?><?= $separator ? $separator . $queryString : '' ?>" class="btn-primary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="btn-disabled" style="opacity: 0.5; cursor: not-allowed;">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

</div>

<!-- Login Notification -->
<div id="loginModal" class="logout-modal" style="display: none;">
    <div class="logout-modal-content">
        <i class="fas fa-check-circle"></i>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>
    </div>
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

const trendLabels = <?= json_encode($trendLabels) ?>;
const trendValues = <?= json_encode($trendValues) ?>;
const orderTrendValues = <?= json_encode($orderTrendValues) ?>;

// Revenue Chart
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

// Orders Chart
if (trendLabels.length > 0) {
    new Chart(document.getElementById('ordersChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Orders',
                data: orderTrendValues,
                backgroundColor: chartColors.accent2,
                borderRadius: 6,
                barThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { ticks: { font: { size: 9 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });
}
</script>

<script>
    // Show login notification if just logged in
    <?php if ($showLoginNotification): ?>
    window.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('loginModal');
        modal.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(function() {
            modal.style.opacity = '0';
            setTimeout(function() {
                modal.style.display = 'none';
            }, 300);
        }, 3000);
    });
    <?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
