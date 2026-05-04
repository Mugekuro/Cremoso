<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { 
    header('Location: ../index.php'); 
    exit(); 
}

$branch_id = $_SESSION['branch_id'];
$date_range = $_GET['range'] ?? '30';

// Date range setup
$date_label = match($date_range) {
    '7' => 'Last 7 Days',
    '30' => 'Last 30 Days',
    '90' => 'Last 90 Days',
    default => 'Last 30 Days'
};

$days_back = match($date_range) {
    '7' => 7,
    '30' => 30,
    '90' => 90,
    default => 30
};

// Get branch name
$branch_stmt = $pdo->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
$branch_stmt->execute([$branch_id]);
$branch_name = $branch_stmt->fetchColumn();

// Get summary metrics
$summary_sql = "SELECT 
    COUNT(*) as total_orders,
    COALESCE(SUM(total_amount), 0) as total_revenue
    FROM transactions 
    WHERE branch_id = ? 
    AND status = 'completed'
    AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";

$summary_stmt = $pdo->prepare($summary_sql);
$summary_stmt->execute([$branch_id, $days_back]);
$summary = $summary_stmt->fetch();

// Get daily sales data
$daily_sql = "SELECT 
    DATE(transaction_date) as sale_date,
    COUNT(*) as orders,
    COALESCE(SUM(total_amount), 0) as revenue
    FROM transactions 
    WHERE branch_id = ? 
    AND status = 'completed'
    AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    GROUP BY DATE(transaction_date)
    ORDER BY sale_date DESC";

$daily_stmt = $pdo->prepare($daily_sql);
$daily_stmt->execute([$branch_id, $days_back]);
$daily_sales = $daily_stmt->fetchAll();

// CSV Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Cremoso Sales Report']);
    fputcsv($output, ['Branch: ' . $branch_name]);
    fputcsv($output, ['Period: ' . $date_label]);
    fputcsv($output, ['Generated: ' . date('F j, Y g:i A')]);
    fputcsv($output, []);
    fputcsv($output, ['Date', 'Orders', 'Revenue']);
    
    foreach ($daily_sales as $row) {
        fputcsv($output, [
            $row['sale_date'], 
            $row['orders'], 
            number_format($row['revenue'], 2)
        ]);
    }
    
    fputcsv($output, []);
    fputcsv($output, ['Total Orders', $summary['total_orders']]);
    fputcsv($output, ['Total Revenue', number_format($summary['total_revenue'], 2)]);
    
    fclose($output);
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/topnav_staff.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_staff.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Sales Report</h1>
        <div class="period-selector">
            <span class="period-label">Period:</span>
            <div class="period-buttons">
                <a href="?range=7" class="period-btn <?= $date_range == '7' ? 'active' : '' ?>">7 Days</a>
                <a href="?range=30" class="period-btn <?= $date_range == '30' ? 'active' : '' ?>">30 Days</a>
                <a href="?range=90" class="period-btn <?= $date_range == '90' ? 'active' : '' ?>">90 Days</a>
            </div>
        </div>
    </div>

    <!-- Export Button -->
    <div style="margin-bottom: 20px;">
        <a href="?range=<?= $date_range ?>&export=csv" class="btn-primary">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($summary['total_revenue'], 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($summary['total_orders']) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    <!-- Daily Sales Table -->
    <div class="data-table">
        <div style="padding: 20px 20px 0;">
            <h3 style="color: var(--text-dark); margin: 0;">
                <i class="fas fa-calendar-day" style="color: var(--primary); margin-right: 8px;"></i>
                Daily Sales - <?= $date_label ?>
            </h3>
        </div>
        
        <?php if (count($daily_sales) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_sales as $row): ?>
                <tr>
                    <td><strong><?= date('M d, Y', strtotime($row['sale_date'])) ?></strong></td>
                    <td><?= $row['orders'] ?></td>
                    <td class="item-price">₱<?= number_format($row['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: var(--primary-pale); font-weight: 700;">
                    <td>TOTAL</td>
                    <td><?= number_format($summary['total_orders']) ?></td>
                    <td class="item-price">₱<?= number_format($summary['total_revenue'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i class="fas fa-chart-bar" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
            <h3 style="color: var(--text-dark); margin-bottom: 8px;">No Data Available</h3>
            <p>No sales found for the selected date range.</p>
        </div>
        <?php endif; ?>
    </div>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
