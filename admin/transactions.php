<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

$filter_branch = $_GET['branch'] ?? '';
$filter_date   = $_GET['date'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build base WHERE clause
$whereClause = "WHERE 1=1";
$params = [];
if($filter_branch) { 
    $whereClause .= " AND t.branch_id = ?"; 
    $params[] = $filter_branch; 
}
if($filter_date) { 
    $whereClause .= " AND DATE(t.transaction_date) = ?"; 
    $params[] = $filter_date; 
}

// Get total count
$countSql = "SELECT COUNT(*) FROM transactions t {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get transactions
$sql = "SELECT t.transaction_id, t.order_number, t.transaction_date, 
               b.branch_name, t.total_amount, pm.method_name, t.status,
               u.fullname as staff
        FROM transactions t
        JOIN branches b ON t.branch_id = b.branch_id
        JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
        JOIN users u ON t.user_id = u.user_id
        {$whereClause}
        ORDER BY t.transaction_date DESC
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$branches = $pdo->query("SELECT * FROM branches ORDER BY branch_name")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>
<?php include __DIR__ . '/../includes/mobile_navbar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-list"></i> All Transactions</h1>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label><i class="fas fa-store"></i> Branch</label>
                <select name="branch">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $b): ?>
                    <option value="<?= $b['branch_id'] ?>" <?= $filter_branch==$b['branch_id']?'selected':'' ?>>
                        <?= htmlspecialchars($b['branch_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar-alt"></i> Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="transactions.php" class="btn-secondary"><i class="fas fa-times"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary stats -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= count($transactions) ?></div>
            <div class="stat-label">Transactions on Page</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-database"></i></div>
            <div class="stat-value"><?= number_format($totalRecords) ?></div>
            <div class="stat-label">Total Records</div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="data-table">
        <table style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 15%;">
                <col style="width: 13%;">
                <col style="width: 18%;">
                <col style="width: 12%;">
                <col style="width: 11%;">
                <col style="width: 14%;">
                <col style="width: 17%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Staff</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($transactions) > 0): ?>
                    <?php foreach($transactions as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t['order_number']) ?></strong></td>
                        <td style="font-size: 12px; color: var(--text-muted);"><?= date('M d, Y g:i A', strtotime($t['transaction_date'])) ?></td>
                        <td><?= htmlspecialchars($t['branch_name']) ?></td>
                        <td class="item-price">₱<?= number_format($t['total_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($t['method_name']) ?></td>
                        <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($t['staff']) ?></td>
                        <td style="text-align: center;">
                            <?php if($t['status'] === 'completed'): ?>
                                <span class="status-badge status-completed">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            <?php elseif($t['status'] === 'pending'): ?>
                                <span class="status-badge status-pending">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php elseif($t['status'] === 'cancelled'): ?>
                                <span class="status-badge status-cancelled">
                                    <i class="fas fa-times-circle"></i> Cancelled
                                </span>
                            <?php else: ?>
                                <span class="status-badge">
                                    <?= htmlspecialchars(ucfirst($t['status'])) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-search" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                            No transactions found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Container -->
    <?php if($totalPages > 1): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?= count($transactions) ?> of <?= number_format($totalRecords) ?> transactions (Page <?= $page ?> of <?= $totalPages ?>)
        </div>
        <div class="pagination-buttons">
            <?php
            $queryString = http_build_query(array_filter([
                'branch' => $filter_branch,
                'date' => $filter_date
            ]));
            $separator = $queryString ? '&' : '';
            ?>
            
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $separator . $queryString ?>" class="btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="btn-disabled">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            <?php endif; ?>

            <span class="page-indicator"><?= $page ?> / <?= $totalPages ?></span>

            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $separator . $queryString ?>" class="btn-primary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="btn-disabled">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>