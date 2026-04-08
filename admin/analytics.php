<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

$totalSales  = $pdo->query("SELECT SUM(total_amount) FROM transactions")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$avgOrder    = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

$branchSales = $pdo->query("SELECT b.branch_name, COUNT(*) as orders, SUM(t.total_amount) as revenue
                             FROM transactions t
                             JOIN branches b ON t.branch_id = b.branch_id
                             GROUP BY b.branch_id")->fetchAll();

$monthlyTrend = $pdo->query("SELECT DATE_FORMAT(transaction_date, '%M') as month, SUM(total_amount) as total
                              FROM transactions
                              WHERE YEAR(transaction_date) = YEAR(CURDATE())
                              GROUP BY MONTH(transaction_date)")->fetchAll();

$topItems = $pdo->query("SELECT i.item_name, SUM(ti.quantity) as total_sold
                         FROM transaction_items ti
                         JOIN items i ON ti.item_id = i.item_id
                         GROUP BY ti.item_id
                         ORDER BY total_sold DESC LIMIT 5")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Analytics</h1>
        <div class="user-info">
            <i class="fas fa-calendar-alt"></i>
            <span><?= date('F Y') ?></span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value">₱<?= number_format($totalSales ?: 0, 2) ?></div>
            <div class="stat-label">Total Sales (All Time)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= $totalOrders ?: 0 ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calculator"></i></div>
            <div class="stat-value">₱<?= number_format($avgOrder ?: 0, 2) ?></div>
            <div class="stat-label">Average Order Value</div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Sales by Branch</h3>
            <div class="chart-placeholder">
                <canvas id="branchChart" width="400" height="250" style="max-width:100%"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Monthly Trend (<?= date('Y') ?>)</h3>
            <div class="chart-placeholder">
                <canvas id="monthlyChart" width="400" height="250" style="max-width:100%"></canvas>
            </div>
        </div>
    </div>

    <?php if(count($topItems) > 0): ?>
    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">Top Selling Items</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Units Sold</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($topItems as $idx => $item): ?>
                <tr>
                    <td><span class="status-badge status-active"><?= $idx + 1 ?></span></td>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= $item['total_sold'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if(count($branchSales) > 0): ?>
    <div class="data-table" style="margin-top: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">Sales by Branch</h3>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Total Orders</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($branchSales as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                    <td><?= $b['orders'] ?></td>
                    <td>₱<?= number_format($b['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
// Branch sales chart
const branchLabels = <?= json_encode(array_column($branchSales, 'branch_name')) ?>;
const branchValues = <?= json_encode(array_column($branchSales, 'revenue')) ?>;

if(branchLabels.length > 0) {
    const branchCtx = document.getElementById('branchChart').getContext('2d');
    new Chart(branchCtx, {
        type: 'bar',
        data: {
            labels: branchLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: branchValues,
                backgroundColor: '#2DA89B',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
}

// Monthly trend chart
const monthlyLabels = <?= json_encode(array_column($monthlyTrend, 'month')) ?>;
const monthlyValues = <?= json_encode(array_column($monthlyTrend, 'total')) ?>;

if(monthlyLabels.length > 0) {
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Sales (₱)',
                data: monthlyValues,
                borderColor: '#2DA89B',
                backgroundColor: 'rgba(45, 168, 155, 0.12)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>