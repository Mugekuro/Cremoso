<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $base_item = trim($_POST['base_item'] ?? '');
        $variant = trim($_POST['variant'] ?? '') ?: null;
        $size = trim($_POST['size'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '') ?: null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($item_name && $category && $base_item && $size && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO items (item_name, category, base_item, variant, size, price, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$item_name, $category, $base_item, $variant, $size, $price, $description, $is_active])) {
                $message = 'Item added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to add item. Please try again.';
                $message_type = 'error';
            }
        } else {
            $message = 'Please fill in all required fields.';
            $message_type = 'error';
        }
    }
    
    if ($action === 'edit') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        $item_name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $base_item = trim($_POST['base_item'] ?? '');
        $variant = trim($_POST['variant'] ?? '') ?: null;
        $size = trim($_POST['size'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '') ?: null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($item_id && $item_name && $category && $base_item && $size && $price > 0) {
            $stmt = $pdo->prepare("UPDATE items SET item_name = ?, category = ?, base_item = ?, variant = ?, size = ?, price = ?, description = ?, is_active = ? WHERE item_id = ?");
            if ($stmt->execute([$item_name, $category, $base_item, $variant, $size, $price, $description, $is_active, $item_id])) {
                $message = 'Item updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to update item. Please try again.';
                $message_type = 'error';
            }
        } else {
            $message = 'Please fill in all required fields.';
            $message_type = 'error';
        }
    }
    
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
$filter_size = $_GET['size'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$whereClause = "WHERE 1=1";
$params = [];
if ($filter_category) { $whereClause .= " AND category = ?"; $params[] = $filter_category; }
if ($filter_size) { $whereClause .= " AND size = ?"; $params[] = $filter_size; }
if ($filter_status !== '') { $whereClause .= " AND is_active = ?"; $params[] = (int)$filter_status; }
if ($search) { $whereClause .= " AND (item_name LIKE ? OR variant LIKE ? OR base_item LIKE ?)"; $searchParam = "%{$search}%"; $params[] = $searchParam; $params[] = $searchParam; $params[] = $searchParam; }

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
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Get distinct categories and sizes for filters
$categories = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$sizes = $pdo->query("SELECT DISTINCT size FROM items ORDER BY size")->fetchAll(PDO::FETCH_COLUMN);

// Get edit item if requested
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmt->execute([$edit_id]);
    $edit_item = $stmt->fetch();
}
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
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search items...">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-layer-group"></i> Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $filter_category == $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-ruler"></i> Size</label>
                <select name="size">
                    <option value="">All Sizes</option>
                    <?php foreach ($sizes as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= $filter_size == $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-toggle-on"></i> Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="1" <?= $filter_status === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $filter_status === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="items.php" class="btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
                <?php if (!$edit_item): ?>
                <button type="button" class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
                    <i class="fas fa-plus"></i> Add Item
                </button>
                <?php else: ?>
                <a href="items.php" class="btn-secondary"><i class="fas fa-times"></i> Cancel Edit</a>
                <?php endif; ?>
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

    <?php if ($edit_item): ?>
    <!-- Edit Form -->
    <div class="data-table" style="margin-bottom: 24px;">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">
            <i class="fas fa-edit" style="color: var(--primary); margin-right: 8px;"></i>Edit Item
        </h3>
        <form method="POST" style="padding: 20px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="item_id" value="<?= $edit_item['item_id'] ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" value="<?= htmlspecialchars($edit_item['item_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select...</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $edit_item['category'] == $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                        <?php endforeach; ?>
                        <option value="Other">Other (type below)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Item *</label>
                    <input type="text" name="base_item" value="<?= htmlspecialchars($edit_item['base_item']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Variant/Flavor</label>
                    <input type="text" name="variant" value="<?= htmlspecialchars($edit_item['variant'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Size *</label>
                    <input type="text" name="size" value="<?= htmlspecialchars($edit_item['size']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Price (₱) *</label>
                    <input type="number" step="0.01" min="0" name="price" value="<?= $edit_item['price'] ?>" required>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" rows="2"><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="is_active" <?= $edit_item['is_active'] ? 'checked' : '' ?> style="width: auto;">
                        Active
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 12px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Item</button>
                <a href="items.php" class="btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

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
                            <a href="?edit=<?= $item['item_id'] ?>" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-edit"></i> <span>Edit</span>
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                <button type="submit" class="btn-action btn-toggle" title="<?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-<?= $item['is_active'] ? 'toggle-on' : 'toggle-off' ?>"></i> <span><?= $item['is_active'] ? 'Deactivate' : 'Activate' ?></span>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
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
                'size' => $filter_size,
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

<!-- Add Item Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="color: var(--text-dark); margin: 0;">
                <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Add New Item
            </h2>
            <button onclick="document.getElementById('addModal').style.display='none'" style="background: none; border: none; cursor: pointer; font-size: 24px; color: var(--text-muted);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="item_name" placeholder="e.g., Soft-serve - Vanilla - Cone" required>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="">Select category...</option>
                    <option value="Soft-serve">Soft-serve</option>
                    <option value="Cremdae">Cremdae</option>
                    <option value="Parfait">Parfait</option>
                    <option value="Frozen Yogurt">Frozen Yogurt</option>
                    <option value="Float">Float</option>
                    <option value="Yogurt">Yogurt</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Base Item *</label>
                <input type="text" name="base_item" placeholder="e.g., Soft-serve, Parfait" required>
            </div>
            
            <div class="form-group">
                <label>Variant/Flavor</label>
                <input type="text" name="variant" placeholder="e.g., Vanilla, Chocolate (optional)">
            </div>
            
            <div class="form-group">
                <label>Size *</label>
                <input type="text" name="size" placeholder="e.g., Cone, Moyen (8oz), Grande (12oz)" required>
            </div>
            
            <div class="form-group">
                <label>Price (₱) *</label>
                <input type="number" step="0.01" min="0" name="price" placeholder="0.00" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Optional description"></textarea>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" checked style="width: auto;">
                    Active
                </label>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Add Item
                </button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal when clicking outside
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('addModal').style.display = 'none';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>