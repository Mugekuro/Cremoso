<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $stmt = $pdo->prepare("INSERT INTO items (item_name, flavor_id, size_id, base_price, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$_POST['item_name'], $_POST['flavor_id'], $_POST['size_id'], $_POST['base_price']]);
    header("Location: items.php?msg=Item added successfully");
    exit();
}

// Handle Update Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $stmt = $pdo->prepare("UPDATE items SET item_name = ?, flavor_id = ?, size_id = ?, base_price = ? WHERE item_id = ?");
    $stmt->execute([$_POST['item_name'], $_POST['flavor_id'], $_POST['size_id'], $_POST['base_price'], $_POST['item_id']]);
    header("Location: items.php?msg=Item updated successfully");
    exit();
}

// Handle Delete Item
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: items.php?msg=Item deleted successfully");
    exit();
}

// Handle Toggle Status
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE items SET is_active = NOT is_active WHERE item_id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: items.php?msg=Item status updated");
    exit();
}

// Get all items
$items = $pdo->query("SELECT i.*, f.flavor_name, s.size_name
                      FROM items i
                      JOIN flavors f ON i.flavor_id = f.flavor_id
                      JOIN item_sizes s ON i.size_id = s.size_id
                      ORDER BY i.item_id DESC")->fetchAll();

$flavors = $pdo->query("SELECT * FROM flavors")->fetchAll();
$sizes = $pdo->query("SELECT * FROM item_sizes")->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$success_msg = $_GET['msg'] ?? '';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-ice-cream"></i> Menu Items Management</h1>
        <button class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
            <i class="fas fa-plus"></i> Add New Item
        </button>
    </div>

    <?php if($success_msg): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ice-cream"></i></div>
            <div class="stat-value"><?= count($items) ?></div>
            <div class="stat-label">Total Menu Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= count(array_filter($items, fn($i) => $i['is_active'])) ?></div>
            <div class="stat-label">Active Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-value"><?= count(array_filter($items, fn($i) => !$i['is_active'])) ?></div>
            <div class="stat-label">Inactive Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tag"></i></div>
            <div class="stat-value"><?= count($flavors) ?></div>
            <div class="stat-label">Flavors</div>
        </div>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Flavor</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?= $item['item_id'] ?></td>
                    <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($item['flavor_name']) ?></td>
                    <td><?= htmlspecialchars($item['size_name']) ?></td>
                    <td class="item-price">₱<?= number_format($item['base_price'], 2) ?></td>
                    <td>
                        <span class="status-badge <?= $item['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="?edit=<?= $item['item_id'] ?>" class="btn-action btn-edit" title="Edit Item">
                                <i class="fas fa-pen"></i> <span>Edit</span>
                            </a>
                            <a href="?toggle=<?= $item['item_id'] ?>" class="btn-action btn-toggle" title="<?= $item['is_active'] ? 'Deactivate' : 'Activate' ?> Item" onclick="return confirm('Toggle status of this item?')">
                                <i class="fas fa-<?= $item['is_active'] ? 'toggle-on' : 'toggle-off' ?>"></i> <span><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span>
                            </a>
                            <a href="?delete=<?= $item['item_id'] ?>" class="btn-action btn-delete" title="Delete Item" onclick="return confirm('Delete this item permanently? This cannot be undone.')">
                                <i class="fas fa-trash-alt"></i> <span>Delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($items) == 0): ?>
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-ice-cream" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                    No items found. Click "Add New Item" to create one.
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Item Modal -->
    <div id="addModal" class="modal-overlay" onclick="if(event.target===this) this.style.display='none'">
        <div class="modal-content">
            <h3 style="color: var(--primary-darker); margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Add New Item</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" placeholder="e.g., Soft Serve, Milkshake, Sundae" required>
                </div>
                <div class="form-group">
                    <label>Flavor</label>
                    <select name="flavor_id" required>
                        <option value="">Select Flavor</option>
                        <?php foreach($flavors as $f): ?>
                        <option value="<?= $f['flavor_id'] ?>"><?= htmlspecialchars($f['flavor_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Size</label>
                    <select name="size_id" required>
                        <option value="">Select Size</option>
                        <?php foreach($sizes as $s): ?>
                        <option value="<?= $s['size_id'] ?>"><?= htmlspecialchars($s['size_name']) ?> (x<?= $s['price_multiplier'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (₱)</label>
                    <input type="number" step="0.01" name="base_price" placeholder="0.00" required>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" name="add_item" class="btn-primary"><i class="fas fa-save"></i> Add Item</button>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <?php if($editItem): ?>
    <div id="editModal" class="modal-overlay" style="display: flex;" onclick="if(event.target===this) window.location='items.php'">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h3 style="color: var(--primary-darker); margin-bottom: 20px;"><i class="fas fa-edit"></i> Edit Item</h3>
            <form method="POST">
                <input type="hidden" name="item_id" value="<?= $editItem['item_id'] ?>">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" value="<?= htmlspecialchars($editItem['item_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Flavor</label>
                    <select name="flavor_id" required>
                        <?php foreach($flavors as $f): ?>
                        <option value="<?= $f['flavor_id'] ?>" <?= $f['flavor_id'] == $editItem['flavor_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['flavor_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Size</label>
                    <select name="size_id" required>
                        <?php foreach($sizes as $s): ?>
                        <option value="<?= $s['size_id'] ?>" <?= $s['size_id'] == $editItem['size_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['size_name']) ?> (x<?= $s['price_multiplier'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (₱)</label>
                    <input type="number" step="0.01" name="base_price" value="<?= $editItem['base_price'] ?>" required>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" name="update_item" class="btn-primary"><i class="fas fa-save"></i> Update Item</button>
                    <a href="items.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>