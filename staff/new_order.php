<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isStaff()) { header('Location: ../index.php'); exit(); }

$items    = $pdo->query("SELECT i.*, f.flavor_name, s.size_name
                         FROM items i
                         JOIN flavors f ON i.flavor_id = f.flavor_id
                         JOIN item_sizes s ON i.size_id = s.size_id
                         WHERE i.is_active = 1
                         ORDER BY i.item_name ASC")->fetchAll();
$channels = $pdo->query("SELECT * FROM order_channels")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment_methods")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar_staff.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-cart-plus"></i> New Order</h1>
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
        </div>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form id="orderForm" method="POST" action="save_order.php">
        <div class="order-layout">
            <!-- Left: Menu Items & Cart -->
            <div class="order-panel">
                <h4 style="color: var(--text-dark); margin-bottom: 20px; font-size: 16px; font-weight: 700;">
                    <i class="fas fa-ice-cream" style="color: var(--primary); margin-right: 8px;"></i>Menu Items
                </h4>
                <div class="item-selector" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 2; min-width: 180px; margin-bottom: 0;">
                        <select id="itemSelect" style="width: 100%;">
                            <option value="">— Select Item —</option>
                            <?php foreach($items as $item): ?>
                            <option value="<?= $item['item_id'] ?>"
                                    data-price="<?= $item['base_price'] ?>"
                                    data-name="<?= htmlspecialchars($item['item_name'] . ' - ' . $item['flavor_name'] . ' (' . $item['size_name'] . ')') ?>">
                                <?= htmlspecialchars($item['item_name']) ?> - <?= htmlspecialchars($item['flavor_name']) ?> (<?= htmlspecialchars($item['size_name']) ?>) — ₱<?= number_format($item['base_price'], 2) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="width: 90px; flex-shrink: 0; margin-bottom: 0;">
                        <input type="number" id="itemQty" value="1" min="1" style="text-align: center; width: 100%;">
                    </div>
                    <button type="button" id="addItemBtn" class="btn-primary" style="white-space: nowrap; flex-shrink: 0; align-self: stretch;">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>

                <div class="data-table" style="margin-top: 16px;">
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
            </div>

            <!-- Right: Order Details -->
            <div class="order-panel">
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
                    <select name="payment_method_id" required>
                        <option value="">Select Payment</option>
                        <?php foreach($payments as $p): ?>
                        <option value="<?= $p['payment_method_id'] ?>"><?= htmlspecialchars($p['method_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="cart_data" id="cartData">
                <div id="cartSummary" style="background: var(--primary-pale); border-radius: 14px; padding: 16px; margin-bottom: 20px; display: none;">
                    <strong style="color: var(--text-dark);">Order Summary</strong>
                    <div id="summaryItems" style="margin-top: 10px; font-size: 14px; color: var(--text-body);"></div>
                    <hr style="border-color: var(--border); margin: 10px 0;">
                    <strong style="color: var(--primary-darker);">Total: ₱<span id="summaryTotal">0.00</span></strong>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-check-circle"></i> Complete Order
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let cart = [];

function updateCartUI() {
    let tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    let total = 0;
    cart.forEach((item, idx) => {
        let subtotal = item.price * item.qty;
        total += subtotal;
        let row = `
            <tr>
                <td style="font-size:13px;">${item.name}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td><input type="number" class="qty-input" data-idx="${idx}" value="${item.qty}" min="1"></td>
                <td>₱${subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn-action btn-delete" data-idx="${idx}"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
    });
    document.getElementById('totalAmount').innerText = total.toFixed(2);
    document.getElementById('summaryTotal').innerText = total.toFixed(2);

    // Update summary
    const summary = document.getElementById('cartSummary');
    const summaryItems = document.getElementById('summaryItems');
    if(cart.length > 0) {
        summary.style.display = 'block';
        summaryItems.innerHTML = cart.map(i => `<div>• ${i.name} x${i.qty} — ₱${(i.price*i.qty).toFixed(2)}</div>`).join('');
    } else {
        summary.style.display = 'none';
    }

    attachCartEvents();
}

function attachCartEvents() {
    document.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('change', function() {
            let idx = parseInt(this.dataset.idx);
            cart[idx].qty = parseInt(this.value) || 1;
            updateCartUI();
        });
    });
    document.querySelectorAll('button[data-idx]').forEach(btn => {
        btn.addEventListener('click', function() {
            let idx = parseInt(this.dataset.idx);
            cart.splice(idx, 1);
            updateCartUI();
        });
    });
}

document.getElementById('addItemBtn').addEventListener('click', function() {
    let select = document.getElementById('itemSelect');
    let opt = select.options[select.selectedIndex];
    if(!opt.value) { alert('Please select an item'); return; }
    let itemId = opt.value;
    let name = opt.dataset.name;
    let price = parseFloat(opt.dataset.price);
    let qty = parseInt(document.getElementById('itemQty').value) || 1;

    // Check if item already in cart
    let existing = cart.find(c => c.id === itemId);
    if(existing) { existing.qty += qty; }
    else { cart.push({ id: itemId, name: name, price: price, qty: qty }); }

    updateCartUI();
    select.selectedIndex = 0;
    document.getElementById('itemQty').value = 1;
});

document.getElementById('orderForm').addEventListener('submit', function(e) {
    if(cart.length === 0) {
        alert('Please add at least one item to the cart');
        e.preventDefault();
        return false;
    }
    document.getElementById('cartData').value = JSON.stringify(cart);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>