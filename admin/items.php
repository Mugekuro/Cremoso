<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Handle form submissions
$message = '';
$message_type = '';

// Check for success message from add_item.php or edit_item.php
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    unset($_SESSION['success_message']);
}

// Check for error message
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'error';
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        if ($item_id) {
            $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
            if ($stmt->execute([$item_id])) {
                $message = 'Item deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to delete item. Please try again.';
                $message_type = 'error';
            }
        }
    }
    
    if ($action === 'toggle_status') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        if ($item_id) {
            $stmt = $pdo->prepare("UPDATE items SET is_active = NOT is_active WHERE item_id = ?");
            if ($stmt->execute([$item_id])) {
                $message = 'Item status updated!';
                $message_type = 'success';
            } else {
                $message = 'Failed to update status. Please try again.';
                $message_type = 'error';
            }
        }
    }
}

// Get filter parameters
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$whereClause = "WHERE 1=1";
$params = [];
if ($filter_category) { 
    $whereClause .= " AND category = ?"; 
    $params[] = $filter_category; 
}
if ($filter_status !== '') { 
    $whereClause .= " AND is_active = ?"; 
    $params[] = (int)$filter_status; 
}
if ($search) { 
    $whereClause .= " AND (item_name LIKE ? OR variant LIKE ? OR base_item LIKE ?)"; 
    $searchParam = "%{$search}%"; 
    $params[] = $searchParam; 
    $params[] = $searchParam; 
    $params[] = $searchParam; 
}

// Get total count
$countSql = "SELECT COUNT(*) FROM items {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get paginated items
$sql = "SELECT * FROM items
        {$whereClause}
        ORDER BY display_order ASC, item_id DESC
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Get distinct categories for filters
$categories = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-ice-cream"></i> Menu Items</h1>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group search-group">
                <label>Search</label>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, variant, or base item...">
                </div>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-layer-group"></i> Category</label>
                <div class="select-wrapper">
                    <i class="fas fa-layer-group select-icon"></i>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $filter_category == $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-toggle-on"></i> Status</label>
                <div class="select-wrapper">
                    <i class="fas fa-toggle-on select-icon"></i>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="1" <?= $filter_status === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $filter_status === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="items.php" class="btn-secondary"><i class="fas fa-times"></i> Clear</a>
                <a href="add_item.php" class="btn-success">
                    <i class="fas fa-plus"></i> Add New Item
                </a>
            </div>
        </form>
    </div>

    <!-- Summary stats -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-value"><?= number_format($totalRecords) ?></div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM items WHERE is_active = 1")->fetchColumn() ?></div>
            <div class="stat-label">Active Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM items WHERE is_active = 0")->fetchColumn() ?></div>
            <div class="stat-label">Inactive Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-value">₱<?= number_format($items ? min(array_column($items, 'price')) : 0, 2) ?></div>
            <div class="stat-label">Lowest Price (Page)</div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Variant</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><span class="status-badge status-active"><?= $item['item_id'] ?></span></td>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['variant'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td class="item-price">₱<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <?php if ($item['is_active']): ?>
                        <span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>
                        <?php else: ?>
                        <span class="status-badge status-inactive"><i class="fas fa-times-circle"></i> Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-edit"></i> <span>Edit</span>
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                <button type="submit" class="btn-action btn-toggle" title="<?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-<?= $item['is_active'] ? 'toggle-on' : 'toggle-off' ?>"></i> <span><?= $item['is_active'] ? 'Deactivate' : 'Activate' ?></span>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                <button type="submit" class="btn-action btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i> <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($items) == 0): ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-search" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No items found
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-container" style="margin-top: 40px;">
        <div class="pagination-info">
            Showing <?= count($items) ?> of <?= number_format($totalRecords) ?> items (Page <?= $page ?> of <?= $totalPages ?>)
        </div>
        <div class="pagination-buttons">
            <?php
            $queryString = http_build_query(array_filter([
                'category' => $filter_category,
                'status' => $filter_status,
                'search' => $search
            ]));
            $separator = $queryString ? '&' : '';
            ?>

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $separator ? $separator . $queryString : '' ?>" class="btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="btn-disabled" style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-chevron-left"></i> Previous
                </span>
            <?php endif; ?>

            <span class="page-indicator"><?= $page ?> / <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
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