<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

$filter_branch = $_GET['branch'] ?? '';
$filter_date   = $_GET['date'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build base WHERE clause for counting and fetching
$whereClause = "WHERE 1=1";
$params = [];
if($filter_branch) { $whereClause .= " AND t.branch_id = ?"; $params[] = $filter_branch; }
if($filter_date)   { $whereClause .= " AND DATE(t.transaction_date) = ?"; $params[] = $filter_date; }

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM transactions t {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get paginated transactions
$sql = "SELECT t.*, c.customer_name, u.fullname as staff, b.branch_name, oc.channel_name, pm.method_name
        FROM transactions t
        JOIN customers c ON t.customer_id = c.customer_id
        JOIN users u ON t.user_id = u.user_id
        JOIN branches b ON t.branch_id = b.branch_id
        JOIN order_channels oc ON t.channel_id = oc.channel_id
        JOIN payment_methods pm ON t.payment_method_id = pm.payment_method_id
        {$whereClause}
        ORDER BY t.transaction_date DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

$branches = $pdo->query("SELECT * FROM branches")->fetchAll();

$totalRevenue = array_sum(array_column($transactions, 'total_amount'));
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-list"></i> All Transactions</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <span class="branch-badge">Admin</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label><i class="fas fa-store"></i> Branch</label>
                <select name="branch">
                    <option value="">All Branches</option>
                    <?php foreach($branches as $b): ?>
                    <option value="<?= $b['branch_id'] ?>" <?= $filter_branch==$b['branch_id']?'selected':'' ?>><?= htmlspecialchars($b['branch_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar-alt"></i> Date</label>
                <input type="date" name="date" value="<?= $filter_date ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="transactions.php" class="btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary stats -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= count($transactions) ?></div>
            <div class="stat-label">Transactions on Page <?= $page ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-database"></i></div>
            <div class="stat-value"><?= number_format($totalRecords) ?></div>
            <div class="stat-label">Total Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-label">Page Revenue</div>
        </div>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Branch</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Channel</th>
                    <th>Staff</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $t): ?>
                <tr>
                    <td><strong><?= $t['order_number'] ?></strong></td>
                    <td><?= htmlspecialchars($t['branch_name']) ?></td>
                    <td><?= htmlspecialchars($t['customer_name']) ?></td>
                    <td>₱<?= number_format($t['total_amount'],2) ?></td>
                    <td><?= htmlspecialchars($t['method_name']) ?></td>
                    <td><?= htmlspecialchars($t['channel_name']) ?></td>
                    <td><?= htmlspecialchars($t['staff']) ?></td>
                    <td><?= date('M d, Y h:i A', strtotime($t['transaction_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($transactions) == 0): ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-search" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No transactions found
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if($totalPages > 1): ?>
    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?= count($transactions) ?> of <?= number_format($totalRecords) ?> transactions (Page <?= $page ?> of <?= $totalPages ?>)
        </div>
        <div class="pagination-buttons">
            <?php
            // Build query string preserving filters
            $queryString = http_build_query(array_filter([
                'branch' => $filter_branch,
                'date' => $filter_date
            ]));
            $separator = $queryString ? '&' : '';
            ?>
            
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $separator ? $separator . $queryString : '' ?>" class="btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="btn-disabled" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            <?php endif; ?>

            <span class="page-indicator"><?= $page ?> / <?= $totalPages ?></span>

            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $separator ? $separator . $queryString : '' ?>" class="btn-primary">
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>