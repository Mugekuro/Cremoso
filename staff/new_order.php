<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/menu_helpers.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

global $pdo;
$categories = getActiveCategories();
$channels = $pdo->query("SELECT * FROM order_channels")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment_methods")->fetchAll();
$toppings = getActiveToppings();
$sauces = getActiveSauces();
$fruits = getActiveFruits();
$extras = getActiveExtras();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/topnav_staff.php';
include __DIR__ . '/../includes/sidebar_staff.php';
?>
<link rel="stylesheet" href="../assets/css/staff.css">

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-cart-plus"></i> New Order</h1>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form id="orderForm" method="POST" action="save_order.php">
        <div class="order-layout">
            <!-- Left: Menu Items -->
            <div class="order-panel">
                <h4 style="color: var(--text-dark); margin-bottom: 20px; font-size: 16px; font-weight: 700;">
                    <i class="fas fa-ice-cream" style="color: var(--primary); margin-right: 8px;"></i>Menu Items
                </h4>
                
                <!-- Category Tabs -->
                <div class="category-tabs">
                    <?php foreach($categories as $idx => $cat): ?>
                    <button type="button" class="tab-btn <?= $idx === 0 ? 'active' : '' ?>" data-category="<?= $cat['category_id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Menu Items Grid -->
                <div class="menu-items-container">
                    <?php foreach($categories as $idx => $cat): 
                        $items = getMenuItemsByCategory($cat['category_id']);
                    ?>
                    <div class="category-content" data-category="<?= $cat['category_id'] ?>" style="<?= $idx === 0 ? '' : 'display:none;' ?>">
                        <div class="items-grid">
                            <?php foreach($items as $item): 
                                $sizes = getItemSizePrices($item['menu_item_id']);
                            ?>
                            <div class="item-card" data-item-id="<?= $item['menu_item_id'] ?>" 
                                 data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                                 data-has-sizes="<?= $item['has_sizes'] ?>"
                                 data-has-flavors="<?= $item['has_flavors'] ?>">
                                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                <div class="item-prices">
                                    <?php if(count($sizes) === 1): ?>
                                        ₱<?= number_format($sizes[0]['price'], 2) ?>
                                    <?php else: ?>
                                        ₱<?= number_format($sizes[0]['price'], 2) ?> - ₱<?= number_format(end($sizes)['price'], 2) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Cart & Order Details -->
            <div class="order-panel">
                <h4 style="color: var(--text-dark); margin-bottom: 20px; font-size: 16px; font-weight: 700;">
                    <i class="fas fa-shopping-cart" style="color: var(--primary); margin-right: 8px;"></i>Cart
                </h4>
                
                <div class="data-table" style="margin-bottom: 20px;">
                    <table class="cart-table">
                        <thead>
                            <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                        <tfoot>
                            <tr style="background: var(--primary-pale);">
                                <td colspan="3" style="text-align: right; font-weight: 700; padding: 14px 20px;">Total:</td>
                                <td colspan="2" style="font-weight: 800; font-size: 18px; color: var(--primary-darker); padding: 14px 20px;">
                                    ₱<span id="totalAmount">0.00</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h4 style="color: var(--text-dark); margin-bottom: 20px; font-size: 16px; font-weight: 700;">
                    <i class="fas fa-clipboard-list" style="color: var(--primary); margin-right: 8px;"></i>Order Details
                </h4>
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" placeholder="Enter customer name (or leave blank for walk-in)">
                </div>
                <div class="form-group">
                    <label>Order Channel</label>
                    <select name="channel_id" required>
                        <option value="">Select Channel</option>
                        <?php foreach($channels as $c): ?>
                        <option value="<?= $c['channel_id'] ?>"><?= htmlspecialchars($c['channel_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method_id" id="paymentMethodSelect" required>
                        <option value="">Select Payment</option>
                        <?php foreach($payments as $p): ?>
                        <option value="<?= $p['payment_method_id'] ?>" data-method="<?= htmlspecialchars($p['method_name']) ?>"><?= htmlspecialchars($p['method_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="paymentReferenceFields" style="display: none;">
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="account_name" id="accountName" placeholder="Enter account holder name">
                    </div>
                    <div class="form-group">
                        <label>Transaction Date</label>
                        <input type="date" name="transaction_date" id="transactionDate">
                    </div>
                </div>
                
                <input type="hidden" name="cart_data" id="cartData">
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-clock"></i> Create Pending Order
                </button>
                <p style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 8px; line-height: 1.4;">
                    Order will be placed in pending queue for payment confirmation
                </p>
            </div>
        </div>
    </form>
</div>

<!-- Customization Modal -->
<div class="modal-overlay" id="customizationModal">
    <div class="modal-box" style="max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <div class="modal-title">Customize Your Order</div>
        <div class="modal-body">
            <div id="selectedItemSummary" style="background: var(--primary-pale); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;"></div>
            
            <div class="customization-section">
                <h5>Toppings</h5>
                <div class="customization-grid">
                    <?php foreach($toppings as $t): ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="topping-check" data-id="<?= $t['topping_id'] ?>" data-name="<?= htmlspecialchars($t['topping_name']) ?>" data-price="<?= $t['price'] ?>">
                        <span><?= htmlspecialchars($t['topping_name']) ?> (+₱<?= number_format($t['price'], 2) ?>)</span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="customization-section">
                <h5>Sauces</h5>
                <div class="customization-grid">
                    <?php foreach($sauces as $s): ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="sauce-check" data-id="<?= $s['sauce_id'] ?>" data-name="<?= htmlspecialchars($s['sauce_name']) ?>" data-price="<?= $s['price'] ?>">
                        <span><?= htmlspecialchars($s['sauce_name']) ?> (+₱<?= number_format($s['price'], 2) ?>)</span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="customization-section">
                <h5>Fruits</h5>
                <div class="customization-grid">
                    <?php foreach($fruits as $f): ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="fruit-check" data-id="<?= $f['fruit_id'] ?>" data-name="<?= htmlspecialchars($f['fruit_name']) ?>" data-price="<?= $f['price'] ?>">
                        <span><?= htmlspecialchars($f['fruit_name']) ?> (+₱<?= number_format($f['price'], 2) ?>)</span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="customization-section">
                <h5>Extras</h5>
                <div class="customization-grid">
                    <?php foreach($extras as $e): ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="extra-check" data-id="<?= $e['extra_id'] ?>" data-name="<?= htmlspecialchars($e['extra_name']) ?>" data-price="<?= $e['price'] ?>">
                        <span><?= htmlspecialchars($e['extra_name']) ?> (+₱<?= number_format($e['price'], 2) ?>)</span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="background: var(--primary-pale); padding: 16px; border-radius: 8px; margin-top: 20px; font-weight: 700; font-size: 16px;">
                Total: ₱<span id="customizationTotal">0.00</span>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeCustomizationModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="addToCart()">Add to Cart</button>
        </div>
    </div>
</div>

<!-- Empty Cart Alert Modal -->
<div class="modal-overlay" id="emptyCartModal">
    <div class="modal-box" style="max-width: 400px;">
        <div class="modal-title" style="color: var(--text-dark); display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-exclamation-circle" style="color: #f59e0b;"></i>
            Cart is Empty
        </div>
        <div class="modal-body">
            <p style="font-size: 15px; color: var(--text-dark); margin: 0;">Please add at least one item to the cart</p>
        </div>
        <div class="modal-actions" style="justify-content: flex-end;">
            <button type="button" class="btn-primary" onclick="closeEmptyCartModal()" style="min-width: 100px;">OK</button>
        </div>
    </div>
</div>


<script>
let cart = [];
let currentItem = null;
let selectedSize = null;
let selectedFlavor = null;

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const categoryId = this.dataset.category;
        document.querySelectorAll('.category-content').forEach(c => c.style.display = 'none');
        document.querySelector(`.category-content[data-category="${categoryId}"]`).style.display = 'block';
    });
});

// Item card click
document.querySelectorAll('.item-card').forEach(card => {
    card.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const itemName = this.dataset.itemName;
        
        currentItem = { id: itemId, name: itemName, hasFlavors: false };
        selectedFlavor = null;
        
        fetch(`get_item_sizes.php?item_id=${itemId}`)
            .then(r => r.json())
            .then(sizes => {
                if(sizes.length > 0) {
                    selectedSize = sizes[0];
                    showCustomizationModal();
                }
            });
    });
});

function showCustomizationModal() {
    const qty = 1;
    const basePrice = parseFloat(selectedSize.price);
    
    let summaryText = `${currentItem.name} x${qty}`;
    
    document.getElementById('selectedItemSummary').textContent = summaryText;
    document.querySelectorAll('.customization-section input[type="checkbox"]').forEach(cb => cb.checked = false);
    updateCustomizationTotal();
    document.getElementById('customizationModal').classList.add('active');
}

function updateCustomizationTotal() {
    const qty = 1;
    const basePrice = parseFloat(selectedSize.price);
    let customizationPrice = 0;
    
    document.querySelectorAll('.customization-section input[type="checkbox"]:checked').forEach(cb => {
        customizationPrice += parseFloat(cb.dataset.price);
    });
    
    const total = (basePrice + customizationPrice) * qty;
    document.getElementById('customizationTotal').textContent = total.toFixed(2);
}

function closeCustomizationModal() {
    document.getElementById('customizationModal').classList.remove('active');
}

document.querySelectorAll('.customization-section input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', updateCustomizationTotal);
});

function addToCart() {
    const qty = 1;
    
    const toppings = Array.from(document.querySelectorAll('.topping-check:checked')).map(cb => ({
        id: cb.dataset.id, name: cb.dataset.name, price: parseFloat(cb.dataset.price)
    }));
    const sauces = Array.from(document.querySelectorAll('.sauce-check:checked')).map(cb => ({
        id: cb.dataset.id, name: cb.dataset.name, price: parseFloat(cb.dataset.price)
    }));
    const fruits = Array.from(document.querySelectorAll('.fruit-check:checked')).map(cb => ({
        id: cb.dataset.id, name: cb.dataset.name, price: parseFloat(cb.dataset.price)
    }));
    const extras = [];
    
    const basePrice = parseFloat(selectedSize.price);
    const customizationPrice = [...toppings, ...sauces, ...fruits].reduce((sum, c) => sum + c.price, 0);
    const itemPrice = basePrice + customizationPrice;
    
    // Use selectedSize.size_price_id as the menu_item_id since it's the actual item_id from the items table
    cart.push({
        menu_item_id: selectedSize.size_price_id,
        flavor_id: null,
        size_price_id: selectedSize.size_price_id,
        size_name: selectedSize.size_name,
        name: currentItem.name,
        price: itemPrice,
        base_price: basePrice,
        qty: qty,
        toppings, sauces, fruits, extras
    });
    
    updateCartUI();
    closeCustomizationModal();
}

function updateCartUI() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    let total = 0;
    
    cart.forEach((item, idx) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        
        let customizationText = '';
        const allCustoms = [...item.toppings, ...item.sauces, ...item.fruits, ...item.extras];
        if(allCustoms.length > 0) {
            customizationText = '<br><small style="color: var(--text-muted);">+ ' + allCustoms.map(c => c.name).join(', ') + '</small>';
        }
        
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td style="font-size:13px;">${item.name}${customizationText}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td><input type="number" class="qty-input" data-idx="${idx}" value="${item.qty}" min="1" style="width: 60px;"></td>
                <td>₱${subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn-action btn-delete" data-idx="${idx}"><i class="fas fa-times"></i></button></td>
            </tr>
        `);
    });
    
    document.getElementById('totalAmount').textContent = total.toFixed(2);
    attachCartEvents();
}

function attachCartEvents() {
    document.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('change', function() {
            cart[parseInt(this.dataset.idx)].qty = parseInt(this.value) || 1;
            updateCartUI();
        });
    });
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            cart.splice(parseInt(this.dataset.idx), 1);
            updateCartUI();
        });
    });
}

function closeEmptyCartModal() {
    document.getElementById('emptyCartModal').classList.remove('active');
}

document.getElementById('orderForm').addEventListener('submit', function(e) {
    if(cart.length === 0) {
        document.getElementById('emptyCartModal').classList.add('active');
        e.preventDefault();
        return false;
    }
    document.getElementById('cartData').value = JSON.stringify(cart);
});

document.getElementById('paymentMethodSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const methodName = selectedOption.dataset.method ? selectedOption.dataset.method.toLowerCase() : '';
    const referenceFields = document.getElementById('paymentReferenceFields');
    const accountName = document.getElementById('accountName');
    const transactionDate = document.getElementById('transactionDate');
    
    if (methodName.includes('gcash') || methodName.includes('credit') || methodName.includes('debit') || methodName.includes('card')) {
        referenceFields.style.display = 'block';
        accountName.required = true;
        transactionDate.required = true;
    } else {
        referenceFields.style.display = 'none';
        accountName.required = false;
        transactionDate.required = false;
        accountName.value = '';
        transactionDate.value = '';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
