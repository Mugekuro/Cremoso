<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// === DATE RANGE FILTER ===
$date_range = $_GET['range'] ?? '30';
$date_filter = match($date_range) {
    '7' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = \'completed\'',
    '30' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status = \'completed\'',
    '90' => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND status = \'completed\'',
    default => 'WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND status = \'completed\''
};

$date_label = match($date_range) {
    '7' => 'Last 7 Days',
    '30' => 'Last 30 Days',
    '90' => 'Last 90 Days',
    default => 'Last 30 Days'
};

// === KEY METRICS ===
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM transactions {$date_filter}")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM transactions {$date_filter}")->fetchColumn();
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// === DAILY REVENUE TREND (Simple) ===
$dailyRevenue = $pdo->query("SELECT DATE(transaction_date) as day, SUM(total_amount) as total
                             FROM transactions {$date_filter}
                             GROUP BY DATE(transaction_date)
                             ORDER BY day")->fetchAll();

$revenueLabels = array_map(fn($r) => date('M d', strtotime($r['day'])), $dailyRevenue);
$revenueData = array_map(fn($r) => $r['total'], $dailyRevenue);

// === TOP 5 SELLING ITEMS ===
$topItems = $pdo->query("SELECT ti.item_name, ti.size, SUM(ti.quantity) as qty, COALESCE(SUM(ti.subtotal),0) as revenue
                         FROM transaction_items ti
                         JOIN transactions t ON ti.transaction_id = t.transaction_id
                         {$date_filter}
                         GROUP BY ti.item_name, ti.size
                         ORDER BY revenue DESC
                         LIMIT 5")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/admin.css">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Analytics Overview</h1>
        <div class="period-selector">
            <span class="period-label">Period:</span>
            <div class="period-buttons">
                <a href="?range=7" class="period-btn <?= $date_range == '7' ? 'active' : '' ?>">7 Days</a>
                <a href="?range=30" class="period-btn <?= $date_range == '30' ? 'active' : '' ?>">30 Days</a>
                <a href="?range=90" class="period-btn <?= $date_range == '90' ? 'active' : '' ?>">90 Days</a>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value" style="font-size: 28px; font-weight: 700; color: var(--text-dark);">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Total Sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value" style="font-size: 28px; font-weight: 700; color: var(--text-dark);"><?= number_format($totalOrders) ?></div>
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calculator"></i></div>
            <div class="stat-value" style="font-size: 28px; font-weight: 700; color: var(--text-dark);">₱<?= number_format($avgOrderValue, 2) ?></div>
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Average Order Value</div>
        </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="chart-card" style="margin-bottom: 24px; padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--text-dark);">
            <i class="fas fa-chart-line" style="color: var(--primary); margin-right: 8px;"></i>
            Sales Trend (<?= $date_label ?>)
        </h3>
        <?php if(count($dailyRevenue) > 0): ?>
        <canvas id="revenueChart" style="max-width:100%; max-height: 300px;"></canvas>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-chart-line" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
            <p>No sales data available for this period</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Selling Items -->
    <?php if(count($topItems) > 0): ?>
    <div class="data-table">
        <h3 style="padding: 20px 20px 12px; color: var(--text-dark); font-size: 16px; font-weight: 600;">
            <i class="fas fa-chart-bar" style="color: var(--primary); margin-right: 8px;"></i>
            Top 5 Selling Items
        </h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">Rank</th>
                    <th>Item Name</th>
                    <th style="width: 120px;">Size</th>
                    <th style="width: 120px; text-align: center;">Quantity Sold</th>
                    <th style="width: 150px; text-align: right;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($topItems as $idx => $item): ?>
                <tr>
                    <td style="text-align: center;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 10px; font-weight: 700; font-size: 14px;">
                            <?= $idx + 1 ?>
                        </span>
                    </td>
                    <td><strong style="color: var(--text-dark);"><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td style="text-align: center; font-weight: 600;"><?= $item['qty'] ?></td>
                    <td style="text-align: right; font-weight: 700; color: var(--primary);">₱<?= number_format($item['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="chart-card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
        <i class="fas fa-box-open" style="font-size: 48px; opacity: 0.3; margin-bottom: 12px;"></i>
        <p>No items sold during this period</p>
    </div>
    <?php endif; ?>

</div>

<style>
.period-selector {
    display: flex;
    align-items: center;
    gap: 12px;
}

.period-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
}

.period-buttons {
    display: flex;
    gap: 6px;
    background: var(--surface);
    padding: 4px;
    border-radius: 12px;
    border: 1.5px solid var(--border);
}

.period-btn {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.period-btn:hover {
    color: var(--primary);
    background: var(--primary-pale);
}

.period-btn.active {
    color: white;
    background: var(--primary);
    box-shadow: 0 2px 8px rgba(45, 168, 155, 0.3);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Sales trend chart
const revenueLabels = <?= json_encode($revenueLabels) ?>;
const revenueData = <?= json_encode($revenueData) ?>;

if (revenueLabels.length > 0) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Sales (₱)',
                data: revenueData,
                borderColor: '#2DA89B',
                backgroundColor: 'rgba(45, 168, 155, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2DA89B',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 14, weight: '700' },
                    callbacks: {
                        label: function(context) {
                            return 'Sales: ₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        },
                        font: { size: 11 },
                        color: '#6B7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: { size: 11 },
                        color: '#6B7280',
                        maxRotation: 45,
                        minRotation: 0
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
