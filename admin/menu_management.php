<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/menu_helpers.php';
redirectIfNotAdmin();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Category actions
    if ($action === 'add_category') {
        $name = trim($_POST['category_name'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, display_order) VALUES (?, ?)");
            $stmt->execute([$name, $order]);
            $message = 'Category added successfully!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'edit_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, display_order = ?, is_active = ? WHERE category_id = ?");
            $stmt->execute([$name, $order, $active, $id]);
            $message = 'Category updated!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$id]);
            $message = 'Category deleted!';
            $message_type = 'success';
        }
    }
    
    // Menu item actions
    if ($action === 'add_item') {
        $cat_id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['item_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $has_sizes = isset($_POST['has_sizes']) ? 1 : 0;
        if ($cat_id && $name) {
            $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, item_name, description, has_sizes) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cat_id, $name, $desc, $has_sizes]);
            $message = 'Menu item added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'edit_item') {
        $id = (int)($_POST['menu_item_id'] ?? 0);
        $cat_id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['item_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $has_sizes = isset($_POST['has_sizes']) ? 1 : 0;
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($id && $cat_id && $name) {
            $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, item_name = ?, description = ?, has_sizes = ?, is_active = ? WHERE menu_item_id = ?");
            $stmt->execute([$cat_id, $name, $desc, $has_sizes, $active, $id]);
            $message = 'Menu item updated!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_item') {
        $id = (int)($_POST['menu_item_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_item_id = ?");
            $stmt->execute([$id]);
            $message = 'Menu item deleted!';
            $message_type = 'success';
        }
    }
    
    // Size price actions
    if ($action === 'add_size_price') {
        $item_id = (int)($_POST['menu_item_id'] ?? 0);
        $size_name = trim($_POST['size_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $order = (int)($_POST['display_order'] ?? 0);
        if ($item_id && $size_name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO item_size_prices (menu_item_id, size_name, price, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$item_id, $size_name, $price, $order]);
            $message = 'Size price added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_size_price') {
        $id = (int)($_POST['size_price_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM item_size_prices WHERE size_price_id = ?");
            $stmt->execute([$id]);
            $message = 'Size price deleted!';
            $message_type = 'success';
        }
    }
    
    // Topping actions
    if ($action === 'add_topping') {
        $name = trim($_POST['topping_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        if ($name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO toppings (topping_name, price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $message = 'Topping added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'edit_topping') {
        $id = (int)($_POST['topping_id'] ?? 0);
        $name = trim($_POST['topping_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($id && $name && $price > 0) {
            $stmt = $pdo->prepare("UPDATE toppings SET topping_name = ?, price = ?, is_active = ? WHERE topping_id = ?");
            $stmt->execute([$name, $price, $active, $id]);
            $message = 'Topping updated!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_topping') {
        $id = (int)($_POST['topping_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM toppings WHERE topping_id = ?");
            $stmt->execute([$id]);
            $message = 'Topping deleted!';
            $message_type = 'success';
        }
    }
    
    // Similar handlers for sauces, fruits, extras (abbreviated for brevity)
    if ($action === 'add_sauce') {
        $name = trim($_POST['sauce_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        if ($name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO sauces (sauce_name, price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $message = 'Sauce added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'add_fruit') {
        $name = trim($_POST['fruit_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        if ($name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO fruits (fruit_name, price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $message = 'Fruit added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'add_extra') {
        $name = trim($_POST['extra_name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        if ($name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO extras (extra_name, price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $message = 'Extra added!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_sauce') {
        $id = (int)($_POST['sauce_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM sauces WHERE sauce_id = ?");
            $stmt->execute([$id]);
            $message = 'Sauce deleted!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_fruit') {
        $id = (int)($_POST['fruit_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM fruits WHERE fruit_id = ?");
            $stmt->execute([$id]);
            $message = 'Fruit deleted!';
            $message_type = 'success';
        }
    }
    
    if ($action === 'delete_extra') {
        $id = (int)($_POST['extra_id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM extras WHERE extra_id = ?");
            $stmt->execute([$id]);
            $message = 'Extra deleted!';
            $message_type = 'success';
        }
    }
}

// Fetch data
$categories = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
$all_items = $pdo->query("SELECT mi.*, c.category_name FROM menu_items mi JOIN categories c ON mi.category_id = c.category_id ORDER BY c.display_order, mi.item_name")->fetchAll();
$toppings = $pdo->query("SELECT * FROM toppings ORDER BY topping_name")->fetchAll();
$sauces = $pdo->query("SELECT * FROM sauces ORDER BY sauce_name")->fetchAll();
$fruits = $pdo->query("SELECT * FROM fruits ORDER BY fruit_name")->fetchAll();
$extras = $pdo->query("SELECT * FROM extras ORDER BY extra_name")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_admin.php';
include __DIR__ . '/../includes/topnav_admin.php';
?>


<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-utensils"></i> Menu Management</h1>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Management Tabs -->
    <div class="management-tabs">
        <button class="tab-btn active" data-tab="categories">Categories</button>
        <button class="tab-btn" data-tab="items">Menu Items</button>
        <button class="tab-btn" data-tab="toppings">Toppings</button>
        <button class="tab-btn" data-tab="sauces">Sauces</button>
        <button class="tab-btn" data-tab="fruits">Fruits</button>
        <button class="tab-btn" data-tab="extras">Extras</button>
    </div>

    <!-- Categories Tab -->
    <div class="tab-content active" id="categories">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddCategoryModal()"><i class="fas fa-plus"></i> Add Category</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Order</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['category_id'] ?></td>
                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                        <td><?= $cat['display_order'] ?></td>
                        <td><span class="badge badge-<?= $cat['is_active'] ? 'success' : 'secondary' ?>"><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <button class="btn-action btn-edit" onclick='editCategory(<?= json_encode($cat) ?>)'><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Menu Items Tab -->
    <div class="tab-content" id="items">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddItemModal()"><i class="fas fa-plus"></i> Add Menu Item</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Category</th><th>Name</th><th>Has Sizes</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($all_items as $item): ?>
                    <tr>
                        <td><?= $item['menu_item_id'] ?></td>
                        <td><?= htmlspecialchars($item['category_name']) ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= $item['has_sizes'] ? 'Yes' : 'No' ?></td>
                        <td><span class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <button class="btn-action btn-info" onclick="manageSizes(<?= $item['menu_item_id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>')"><i class="fas fa-dollar-sign"></i></button>
                            <button class="btn-action btn-edit" onclick='editItem(<?= json_encode($item) ?>)'><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this item?')">
                                <input type="hidden" name="action" value="delete_item">
                                <input type="hidden" name="menu_item_id" value="<?= $item['menu_item_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toppings Tab -->
    <div class="tab-content" id="toppings">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddToppingModal()"><i class="fas fa-plus"></i> Add Topping</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($toppings as $t): ?>
                    <tr>
                        <td><?= $t['topping_id'] ?></td>
                        <td><?= htmlspecialchars($t['topping_name']) ?></td>
                        <td>₱<?= number_format($t['price'], 2) ?></td>
                        <td><span class="badge badge-<?= $t['is_active'] ? 'success' : 'secondary' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <button class="btn-action btn-edit" onclick='editTopping(<?= json_encode($t) ?>)'><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this topping?')">
                                <input type="hidden" name="action" value="delete_topping">
                                <input type="hidden" name="topping_id" value="<?= $t['topping_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sauces Tab -->
    <div class="tab-content" id="sauces">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddSauceModal()"><i class="fas fa-plus"></i> Add Sauce</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Price</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($sauces as $s): ?>
                    <tr>
                        <td><?= $s['sauce_id'] ?></td>
                        <td><?= htmlspecialchars($s['sauce_name']) ?></td>
                        <td>₱<?= number_format($s['price'], 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="action" value="delete_sauce">
                                <input type="hidden" name="sauce_id" value="<?= $s['sauce_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fruits Tab -->
    <div class="tab-content" id="fruits">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddFruitModal()"><i class="fas fa-plus"></i> Add Fruit</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Price</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($fruits as $f): ?>
                    <tr>
                        <td><?= $f['fruit_id'] ?></td>
                        <td><?= htmlspecialchars($f['fruit_name']) ?></td>
                        <td>₱<?= number_format($f['price'], 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="action" value="delete_fruit">
                                <input type="hidden" name="fruit_id" value="<?= $f['fruit_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Extras Tab -->
    <div class="tab-content" id="extras">
        <div style="margin-bottom: 20px;">
            <button class="btn-primary" onclick="showAddExtraModal()"><i class="fas fa-plus"></i> Add Extra</button>
        </div>
        <div class="data-table">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Price</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($extras as $e): ?>
                    <tr>
                        <td><?= $e['extra_id'] ?></td>
                        <td><?= htmlspecialchars($e['extra_name']) ?></td>
                        <td>₱<?= number_format($e['price'], 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="action" value="delete_extra">
                                <input type="hidden" name="extra_id" value="<?= $e['extra_id'] ?>">
                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-box">
        <div class="modal-title" id="categoryModalTitle">Add Category</div>
        <form method="POST" id="categoryForm">
            <input type="hidden" name="action" id="categoryAction" value="add_category">
            <input type="hidden" name="category_id" id="categoryId">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" id="categoryName" required>
            </div>
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" id="categoryOrder" value="0">
            </div>
            <div class="form-group" id="categoryActiveGroup" style="display:none;">
                <label><input type="checkbox" name="is_active" id="categoryActive"> Active</label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('categoryModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Menu Item Modal -->
<div class="modal-overlay" id="itemModal">
    <div class="modal-box">
        <div class="modal-title" id="itemModalTitle">Add Menu Item</div>
        <form method="POST" id="itemForm">
            <input type="hidden" name="action" id="itemAction" value="add_item">
            <input type="hidden" name="menu_item_id" id="itemId">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="itemCategory" required>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="item_name" id="itemName" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="itemDesc" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="has_sizes" id="itemHasSizes" checked> Has Multiple Sizes</label>
            </div>
            <div class="form-group" id="itemActiveGroup" style="display:none;">
                <label><input type="checkbox" name="is_active" id="itemActive"> Active</label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('itemModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Size Prices Modal -->
<div class="modal-overlay" id="sizesModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-title" id="sizesModalTitle">Manage Sizes & Prices</div>
        <div id="sizesContent"></div>
        <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--border);">
            <input type="hidden" name="action" value="add_size_price">
            <input type="hidden" name="menu_item_id" id="sizeItemId">
            <h5 style="margin-bottom: 12px;">Add New Size</h5>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label>Size Name</label>
                    <input type="text" name="size_name" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Order</label>
                    <input type="number" name="display_order" value="0">
                </div>
                <button type="submit" class="btn-primary">Add</button>
            </div>
        </form>
        <div class="modal-actions" style="margin-top: 20px;">
            <button type="button" class="btn-secondary" onclick="closeModal('sizesModal')">Close</button>
        </div>
    </div>
</div>

<!-- Topping Modal -->
<div class="modal-overlay" id="toppingModal">
    <div class="modal-box">
        <div class="modal-title" id="toppingModalTitle">Add Topping</div>
        <form method="POST" id="toppingForm">
            <input type="hidden" name="action" id="toppingAction" value="add_topping">
            <input type="hidden" name="topping_id" id="toppingId">
            <div class="form-group">
                <label>Topping Name</label>
                <input type="text" name="topping_name" id="toppingName" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" id="toppingPrice" step="0.01" required>
            </div>
            <div class="form-group" id="toppingActiveGroup" style="display:none;">
                <label><input type="checkbox" name="is_active" id="toppingActive"> Active</label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('toppingModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Simple Add Modals for Sauce, Fruit, Extra -->
<div class="modal-overlay" id="sauceModal">
    <div class="modal-box">
        <div class="modal-title">Add Sauce</div>
        <form method="POST">
            <input type="hidden" name="action" value="add_sauce">
            <div class="form-group">
                <label>Sauce Name</label>
                <input type="text" name="sauce_name" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('sauceModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="fruitModal">
    <div class="modal-box">
        <div class="modal-title">Add Fruit</div>
        <form method="POST">
            <input type="hidden" name="action" value="add_fruit">
            <div class="form-group">
                <label>Fruit Name</label>
                <input type="text" name="fruit_name" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('fruitModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="extraModal">
    <div class="modal-box">
        <div class="modal-title">Add Extra</div>
        <form method="POST">
            <input type="hidden" name="action" value="add_extra">
            <div class="form-group">
                <label>Extra Name</label>
                <input type="text" name="extra_name" required>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('extraModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>



<style>
.management-tabs { display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 2px solid var(--border); }
.management-tabs .tab-btn { background: none; border: none; padding: 12px 24px; cursor: pointer; font-weight: 600; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s; }
.management-tabs .tab-btn:hover { color: var(--primary); }
.management-tabs .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex !important; }
.modal-box { background: #fff; border-radius: 16px; padding: 32px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; }
.modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
.badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.badge-success { background: #d4edda; color: #155724; }
.badge-secondary { background: #e2e3e5; color: #383d41; }
.badge-info { background: #d1ecf1; color: #0c5460; }
</style>

<script>
// Tab switching
document.querySelectorAll('.management-tabs .tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.management-tabs .tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Category functions
function showAddCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categoryAction').value = 'add_category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryActiveGroup').style.display = 'none';
    document.getElementById('categoryModal').classList.add('active');
}

function editCategory(cat) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryAction').value = 'edit_category';
    document.getElementById('categoryId').value = cat.category_id;
    document.getElementById('categoryName').value = cat.category_name;
    document.getElementById('categoryOrder').value = cat.display_order;
    document.getElementById('categoryActive').checked = cat.is_active == 1;
    document.getElementById('categoryActiveGroup').style.display = 'block';
    document.getElementById('categoryModal').classList.add('active');
}

// Menu item functions
function showAddItemModal() {
    document.getElementById('itemModalTitle').textContent = 'Add Menu Item';
    document.getElementById('itemAction').value = 'add_item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemActiveGroup').style.display = 'none';
    document.getElementById('itemModal').classList.add('active');
}

function editItem(item) {
    document.getElementById('itemModalTitle').textContent = 'Edit Menu Item';
    document.getElementById('itemAction').value = 'edit_item';
    document.getElementById('itemId').value = item.menu_item_id;
    document.getElementById('itemCategory').value = item.category_id;
    document.getElementById('itemName').value = item.item_name;
    document.getElementById('itemDesc').value = item.description || '';
    document.getElementById('itemHasSizes').checked = item.has_sizes == 1;
    document.getElementById('itemActive').checked = item.is_active == 1;
    document.getElementById('itemActiveGroup').style.display = 'block';
    document.getElementById('itemModal').classList.add('active');
}

// Size management
function manageSizes(itemId, itemName) {
    document.getElementById('sizesModalTitle').textContent = 'Manage Sizes: ' + itemName;
    document.getElementById('sizeItemId').value = itemId;
    
    fetch('get_item_sizes.php?item_id=' + itemId)
        .then(r => r.json())
        .then(sizes => {
            const html = sizes.length > 0 ? `
                <table class="data-table" style="width: 100%;">
                    <thead><tr><th>Size</th><th>Price</th><th>Order</th><th>Action</th></tr></thead>
                    <tbody>
                        ${sizes.map(s => `
                            <tr>
                                <td>${s.size_name}</td>
                                <td>₱${parseFloat(s.price).toFixed(2)}</td>
                                <td>${s.display_order}</td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                        <input type="hidden" name="action" value="delete_size_price">
                                        <input type="hidden" name="size_price_id" value="${s.size_price_id}">
                                        <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            ` : '<p style="color: var(--text-muted);">No sizes added yet.</p>';
            
            document.getElementById('sizesContent').innerHTML = html;
            document.getElementById('sizesModal').classList.add('active');
        });
}

// Topping functions
function showAddToppingModal() {
    document.getElementById('toppingModalTitle').textContent = 'Add Topping';
    document.getElementById('toppingAction').value = 'add_topping';
    document.getElementById('toppingForm').reset();
    document.getElementById('toppingActiveGroup').style.display = 'none';
    document.getElementById('toppingModal').classList.add('active');
}

function editTopping(topping) {
    document.getElementById('toppingModalTitle').textContent = 'Edit Topping';
    document.getElementById('toppingAction').value = 'edit_topping';
    document.getElementById('toppingId').value = topping.topping_id;
    document.getElementById('toppingName').value = topping.topping_name;
    document.getElementById('toppingPrice').value = topping.price;
    document.getElementById('toppingActive').checked = topping.is_active == 1;
    document.getElementById('toppingActiveGroup').style.display = 'block';
    document.getElementById('toppingModal').classList.add('active');
}

// Simple modal openers
function showAddSauceModal() { document.getElementById('sauceModal').classList.add('active'); }
function showAddFruitModal() { document.getElementById('fruitModal').classList.add('active'); }
function showAddExtraModal() { document.getElementById('extraModal').classList.add('active'); }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
