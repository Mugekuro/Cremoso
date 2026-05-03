<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

// Get item ID
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    $_SESSION['error_message'] = 'Invalid item ID.';
    header('Location: items.php');
    exit;
}

// Fetch item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error_message'] = 'Item not found.';
    header('Location: items.php');
    exit;
}

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $base_item = trim($_POST['base_item'] ?? '');
    $variant = trim($_POST['variant'] ?? '') ?: null;
    $size = trim($_POST['size'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '') ?: null;
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (!$item_name || !$category || !$base_item || !$size || $price <= 0) {
        $message = 'Please fill in all required fields with valid values.';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE items SET item_name = ?, category = ?, base_item = ?, variant = ?, size = ?, price = ?, description = ?, display_order = ?, is_active = ? WHERE item_id = ?");
            if ($stmt->execute([$item_name, $category, $base_item, $variant, $size, $price, $description, $display_order, $is_active, $item_id])) {
                $_SESSION['success_message'] = 'Item updated successfully!';
                header('Location: items.php');
                exit;
            } else {
                $message = 'Failed to update item. Please try again.';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    // Update item array with new values for form repopulation
    $item = array_merge($item, [
        'item_name' => $item_name,
        'category' => $category,
        'base_item' => $base_item,
        'variant' => $variant,
        'size' => $size,
        'price' => $price,
        'description' => $description,
        'display_order' => $display_order,
        'is_active' => $is_active
    ]);
}

// Get existing categories for dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Get existing base items for suggestions
$base_items = $pdo->query("SELECT DISTINCT base_item FROM items ORDER BY base_item")->fetchAll(PDO::FETCH_COLUMN);

// Get existing sizes for suggestions
$sizes = $pdo->query("SELECT DISTINCT size FROM items ORDER BY size")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<?php include __DIR__ . '/../includes/topnav_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Edit Menu Item</h1>
        <div>
            <a href="items.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Items
            </a>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="data-table">
        <form method="POST" id="editItemForm">
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="item_name">Item Name <span class="required">*</span></label>
                        <input type="text" id="item_name" name="item_name" 
                               value="<?= htmlspecialchars($item['item_name']) ?>" 
                               placeholder="e.g., Soft-serve - Vanilla - Cone" required>
                        <small class="form-hint">Full display name of the item</small>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select category...</option>
                            <?php 
                            $default_categories = ['Soft-serve', 'Cremdae', 'Parfait', 'Frozen Yogurt', 'Float', 'Yogurt'];
                            $all_categories = array_unique(array_merge($default_categories, $categories));
                            foreach ($all_categories as $cat): 
                            ?>
                            <option value="<?= htmlspecialchars($cat) ?>" 
                                    <?= $item['category'] == $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-hint">Product category</small>
                    </div>

                    <div class="form-group">
                        <label for="base_item">Base Item <span class="required">*</span></label>
                        <input type="text" id="base_item" name="base_item" 
                               value="<?= htmlspecialchars($item['base_item']) ?>" 
                               placeholder="e.g., Soft-serve, Parfait" 
                               list="base_items_list" required>
                        <datalist id="base_items_list">
                            <?php foreach ($base_items as $bi): ?>
                            <option value="<?= htmlspecialchars($bi) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <small class="form-hint">Base product type</small>
                    </div>

                    <div class="form-group">
                        <label for="variant">Variant/Flavor</label>
                        <input type="text" id="variant" name="variant" 
                               value="<?= htmlspecialchars($item['variant'] ?? '') ?>" 
                               placeholder="e.g., Vanilla, Chocolate (optional)">
                        <small class="form-hint">Flavor or variant name (optional)</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-dollar-sign"></i> Pricing & Size</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="size">Size <span class="required">*</span></label>
                        <input type="text" id="size" name="size" 
                               value="<?= htmlspecialchars($item['size']) ?>" 
                               placeholder="e.g., Cone, Moyen (8oz), Grande (12oz)" 
                               list="sizes_list" required>
                        <datalist id="sizes_list">
                            <?php foreach ($sizes as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <small class="form-hint">Product size</small>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (₱) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0" 
                               value="<?= htmlspecialchars($item['price']) ?>" 
                               placeholder="0.00" required>
                        <small class="form-hint">Price in Philippine Peso</small>
                    </div>

                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" min="0" 
                               value="<?= htmlspecialchars($item['display_order']) ?>">
                        <small class="form-hint">Order in menu</small>
                    </div>

                    <div class="form-group">
                        <label for="is_active" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="is_active" name="is_active" 
                                   <?= $item['is_active'] ? 'checked' : '' ?> 
                                   style="width: auto; cursor: pointer;">
                            <span>Active (visible in menu)</span>
                        </label>
                        <small class="form-hint">Uncheck to hide from menu</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-align-left"></i> Additional Details</h3>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Optional description or notes about this item"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                    <small class="form-hint">Optional description (e.g., ingredients, special notes)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-success">
                    <i class="fas fa-save"></i> Update Item
                </button>
                <a href="items.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-section {
    padding: 24px;
    border-bottom: 1px solid var(--border);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    color: var(--text-dark);
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section h3 i {
    color: var(--primary);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group .required {
    color: var(--error);
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-muted);
}

.form-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-hint {
    color: var(--text-muted);
    font-size: 12px;
    margin-top: 4px;
}

.form-actions {
    padding: 24px;
    display: flex;
    gap: 12px;
    background: var(--surface-2);
    border-radius: 0 0 16px 16px;
}

.form-actions .btn-success,
.form-actions .btn-secondary {
    padding: 12px 24px;
    font-weight: 600;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.form-actions .btn-success {
    background: var(--success);
    color: white;
    border: none;
    cursor: pointer;
}

.form-actions .btn-success:hover {
    background: var(--success-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn-success,
    .form-actions .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
