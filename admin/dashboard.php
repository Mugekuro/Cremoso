<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

if (!isset($pdo)) {
    die("Database connection not established.");
}

$today = date('Y-m-d');

$totalStaff = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();

$monthlySales = $pdo->prepare("SELECT COUNT(*), SUM(total_amount) FROM transactions WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE())");
$monthlySales->execute();
$monthlyData = $monthlySales->fetch();
$totalTransactions = $monthlyData[0] ?? 0;
$totalRevenue = $monthlyData[1] ?? 0;

$todaySales = $pdo->prepare("SELECT COUNT(*), SUM(total_amount) FROM transactions WHERE DATE(transaction_date) = ?");
$todaySales->execute([$today]);
$todayData = $todaySales->fetch();
$todayOrders = $todayData[0] ?? 0;
$todayRevenue = $todayData[1] ?? 0;

$recent = $pdo->query("SELECT t.*, u.fullname as staff, b.branch_name 
                       FROM transactions t
                       JOIN users u ON t.user_id = u.user_id
                       JOIN branches b ON t.branch_id = b.branch_id
                       ORDER BY t.transaction_date DESC LIMIT 5")->fetchAll();

$weekly = $pdo->query("SELECT DATE(transaction_date) as date, SUM(total_amount) as total 
                       FROM transactions 
                       WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                       GROUP BY DATE(transaction_date)")->fetchAll();

$channelSales = $pdo->query("SELECT oc.channel_name, COUNT(*) as count 
                             FROM transactions t 
                             JOIN order_channels oc ON t.channel_id = oc.channel_id 
                             GROUP BY oc.channel_name")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <span class="branch-badge">Admin</span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= $totalStaff ?></div>
            <div class="stat-label">Total Staff Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value"><?= $totalTransactions ?></div>
            <div class="stat-label">Transactions This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Revenue This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-value"><?= $todayOrders ?></div>
            <div class="stat-label">Orders Today (₱<?= number_format($todayRevenue, 2) ?>)</div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Last 7 Days Sales</h3>
            <div class="chart-placeholder">
                <canvas id="salesChart" width="400" height="250" style="max-width:100%; height:auto"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Sales by Channel</h3>
            <div class="chart-placeholder">
                <canvas id="channelPieChart" width="400" height="250" style="max-width:100%; height:auto"></canvas>
            </div>
        </div>
    </div>

    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">Recent Activity</h3>
        <table>
            <thead>
                <tr><th>Order #</th><th>Branch</th><th>Staff</th><th>Total</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach($recent as $r): ?>
                <tr>
                    <td><?= $r['order_number'] ?></td>
                    <td><?= $r['branch_name'] ?></td>
                    <td><?= $r['staff'] ?></td>
                    <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                    <td><?= date('M d, h:i A', strtotime($r['transaction_date'])) ?></td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($recent) == 0): ?>
                <tr><td colspan="6" style="text-align: center; padding: 40px;">No transactions yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const salesData = <?php 
    $labels = [];
    $values = [];
    foreach($weekly as $w) {
        $labels[] = date('M d', strtotime($w['date']));
        $values[] = $w['total'];
    }
    echo json_encode(['labels' => $labels, 'values' => $values]);
?>;

if(salesData.labels.length > 0) {
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Sales (₱)',
                data: salesData.values,
                borderColor: '#2DA89B',
                backgroundColor: 'rgba(45, 168, 155, 0.12)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
}

const channelLabels = <?= json_encode(array_column($channelSales, 'channel_name')) ?>;
const channelValues = <?= json_encode(array_column($channelSales, 'count')) ?>;

if(channelLabels.length > 0) {
    const pieCtx = document.getElementById('channelPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: channelLabels,
            datasets: [{
                data: channelValues,
                backgroundColor: ['#2DA89B', '#1E8078', '#4ABFB3', '#A8DDD8']
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>