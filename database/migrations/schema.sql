-- =====================================================
-- CREMOSO DATABASE SCHEMA - SIMPLIFIED
-- Aligned with cremoso items.txt
-- Removed: categories, extras, flavors, fruits, item_sizes, 
--          item_size_prices, menu_items, sauces, time_logs, 
--          toppings, transaction_item_extras, transaction_item_fruits,
--          transaction_item_sauces, transaction_item_toppings
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS cremoso_db;
USE cremoso_db;

-- =====================================================
-- CORE SYSTEM TABLES (KEPT)
-- =====================================================

-- Branches table
CREATE TABLE IF NOT EXISTS branches (
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    google_id VARCHAR(255) NULL UNIQUE COMMENT 'Google OAuth ID',
    email VARCHAR(100) NULL COMMENT 'Email address for Google OAuth',
    role ENUM('admin','staff') NOT NULL,
    branch_id INT NULL,
    is_confirmed BOOLEAN DEFAULT FALSE COMMENT 'Staff accounts need admin approval',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL,
    INDEX idx_google_id (google_id),
    INDEX idx_email (email)
);

-- Order channels
CREATE TABLE IF NOT EXISTS order_channels (
    channel_id INT PRIMARY KEY AUTO_INCREMENT,
    channel_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Payment methods
CREATE TABLE IF NOT EXISTS payment_methods (
    payment_method_id INT PRIMARY KEY AUTO_INCREMENT,
    method_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Customers
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- SIMPLIFIED MENU STRUCTURE
-- =====================================================

-- Items table - stores all menu items with their variations
CREATE TABLE IF NOT EXISTS items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(200) NOT NULL COMMENT 'Full item name with flavor/variant',
    category VARCHAR(100) NOT NULL COMMENT 'Soft-serve, Cremdae, Parfait, Frozen Yogurt, Float, Yogurt',
    base_item VARCHAR(100) NOT NULL COMMENT 'Base item type',
    variant VARCHAR(100) NULL COMMENT 'Flavor or variant name',
    size VARCHAR(50) NOT NULL COMMENT 'Cone, Moyen, Grande, 16oz, etc.',
    price DECIMAL(10,2) NOT NULL,
    description TEXT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_base_item (base_item)
);

-- Add-ons table - stores all toppings, sauces, and fruits
CREATE TABLE IF NOT EXISTS addons (
    addon_id INT PRIMARY KEY AUTO_INCREMENT,
    addon_name VARCHAR(100) NOT NULL,
    addon_type ENUM('topping','sauce','fruit') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_addon_type (addon_type)
);

-- =====================================================
-- TRANSACTION TABLES (KEPT)
-- =====================================================

-- Transactions (main orders)
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    user_id INT NOT NULL COMMENT 'Staff who processed the order',
    branch_id INT NOT NULL,
    channel_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    account_name VARCHAR(100) NULL COMMENT 'For GCash/online payments',
    transaction_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','completed','cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (channel_id) REFERENCES order_channels(channel_id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id),
    INDEX idx_order_number (order_number),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_status (status)
);

-- Transaction items (individual items in an order)
CREATE TABLE IF NOT EXISTS transaction_items (
    transaction_item_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    item_id INT NOT NULL COMMENT 'Reference to items table',
    item_name VARCHAR(200) NOT NULL COMMENT 'Snapshot of item name',
    category VARCHAR(100) NOT NULL COMMENT 'Snapshot of category',
    size VARCHAR(50) NOT NULL COMMENT 'Snapshot of size',
    base_price DECIMAL(10,2) NOT NULL COMMENT 'Base price of item',
    quantity INT NOT NULL DEFAULT 1,
    addons_detail TEXT NULL COMMENT 'JSON array of selected add-ons with names and prices',
    addons_total DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total of all add-ons',
    subtotal DECIMAL(10,2) NOT NULL COMMENT 'base_price * quantity + addons_total',
    notes TEXT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- =====================================================
-- SEED DATA: SYSTEM CONFIGURATION
-- =====================================================

-- Insert branches
INSERT INTO branches (branch_name, location) VALUES
('Cremoso HQ (Main)', 'Malaybalay City'),
('Cremoso Branch', 'Malaybalay City');

-- Insert order channels
INSERT INTO order_channels (channel_name) VALUES
('Walk-in'),
('Facebook Messenger'),
('Foodpanda');

-- Insert payment methods
INSERT INTO payment_methods (method_name) VALUES
('Cash'),
('GCash'),
('Credit/Debit Card');

-- Insert default users
INSERT INTO users (username, fullname, password, role, branch_id, is_confirmed) VALUES
('admin', 'Admin User', 'admin', 'admin', NULL, TRUE),
('staff1', 'Maria Staff - HQ', 'staff1', 'staff', 1, TRUE),
('staff2', 'John Staff', 'staff2', 'staff', 2, TRUE);

-- Insert default customer
INSERT INTO customers (customer_name) VALUES ('Walk-in Customer');

-- =====================================================
-- SEED DATA: MENU ITEMS (Based on cremoso items.txt)
-- =====================================================

-- ITEM 1: SOFT-SERVE
-- Flavors: Vanilla, Chocolate
-- Sizes: Cone - ₱25, Moyen (8oz) - ₱29, Grande (12oz) - ₱39
-- +₱10 for chocolate

INSERT INTO items (item_name, category, base_item, variant, size, price, display_order) VALUES
-- Vanilla Soft-serve
('Soft-serve - Vanilla - Cone', 'Soft-serve', 'Soft-serve', 'Vanilla', 'Cone', 25.00, 1),
('Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Soft-serve', 'Vanilla', 'Moyen (8oz)', 29.00, 2),
('Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Soft-serve', 'Vanilla', 'Grande (12oz)', 39.00, 3),
-- Chocolate Soft-serve (+₱10)
('Soft-serve - Chocolate - Cone', 'Soft-serve', 'Soft-serve', 'Chocolate', 'Cone', 35.00, 4),
('Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Soft-serve', 'Chocolate', 'Moyen (8oz)', 39.00, 5),
('Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Soft-serve', 'Chocolate', 'Grande (12oz)', 49.00, 6);

-- ITEM 2: CREMDAE
-- Flavors: Chocolate, Caramel
-- Size: Grande (12oz) - ₱59

INSERT INTO items (item_name, category, base_item, variant, size, price, display_order) VALUES
('Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Cremdae', 'Chocolate', 'Grande (12oz)', 59.00, 7),
('Cremdae - Caramel - Grande (12oz)', 'Cremdae', 'Cremdae', 'Caramel', 'Grande (12oz)', 59.00, 8);

-- ITEM 3: PARFAIT
-- Flavors: Cremango Royale, Chocky Road, Tiger Creme, Oreo Creme
-- Complex pricing:
-- - Oreo Creme & Tiger Creme: Moyen ₱69, Grande ₱79
-- - Chocky Road: Moyen ₱79, Grande ₱89
-- - Cremango Royale: Grande ₱99 only

INSERT INTO items (item_name, category, base_item, variant, size, price, display_order) VALUES
-- Oreo Creme Parfait
('Parfait - Oreo Creme - Moyen', 'Parfait', 'Parfait', 'Oreo Creme', 'Moyen', 69.00, 9),
('Parfait - Oreo Creme - Grande', 'Parfait', 'Parfait', 'Oreo Creme', 'Grande', 79.00, 10),
-- Tiger Creme Parfait
('Parfait - Tiger Creme - Moyen', 'Parfait', 'Parfait', 'Tiger Creme', 'Moyen', 69.00, 11),
('Parfait - Tiger Creme - Grande', 'Parfait', 'Parfait', 'Tiger Creme', 'Grande', 79.00, 12),
-- Chocky Road Parfait
('Parfait - Chocky Road - Moyen', 'Parfait', 'Parfait', 'Chocky Road', 'Moyen', 79.00, 13),
('Parfait - Chocky Road - Grande', 'Parfait', 'Parfait', 'Chocky Road', 'Grande', 89.00, 14),
-- Cremango Royale Parfait
('Parfait - Cremango Royale - Grande', 'Parfait', 'Parfait', 'Cremango Royale', 'Grande', 99.00, 15);

-- ITEM 4: FROZEN YOGURT
-- Sizes: Moyen (8oz) - ₱89, Grande (12oz) - ₱99

INSERT INTO items (item_name, category, base_item, variant, size, price, display_order) VALUES
('Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Frozen Yogurt', NULL, 'Moyen (8oz)', 89.00, 16),
('Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Frozen Yogurt', NULL, 'Grande (12oz)', 99.00, 17);

-- ITEM 5: FLOAT
-- Flavors: Coffee Latte, Caramel Macchiato, Fizzy Float, Chocolate Float
-- Sizes: 16oz - ₱99 (except Fizzy Float ₱69)

INSERT INTO items (item_name, category, base_item, variant, size, price, display_order) VALUES
('Float - Coffee Latte - 16oz', 'Float', 'Float', 'Coffee Latte', '16oz', 99.00, 18),
('Float - Caramel Macchiato - 16oz', 'Float', 'Float', 'Caramel Macchiato', '16oz', 99.00, 19),
('Float - Chocolate Float - 16oz', 'Float', 'Float', 'Chocolate Float', '16oz', 99.00, 20),
('Float - Fizzy Float - 16oz', 'Float', 'Float', 'Fizzy Float', '16oz', 69.00, 21);

-- ITEM 6: YOGURT
-- Size: Grande (12oz) - ₱189
-- (Frozen Yogurt + choice of: 2 fruits, 2 toppings, 1 sauce)

INSERT INTO items (item_name, category, base_item, variant, size, price, description, display_order) VALUES
('Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Yogurt Deluxe', NULL, 'Grande (12oz)', 189.00, 'Frozen Yogurt + 2 fruits, 2 toppings, 1 sauce', 22);

-- =====================================================
-- SEED DATA: ADD-ONS
-- =====================================================

-- TOPPINGS (16 items)
INSERT INTO addons (addon_name, addon_type, price, display_order) VALUES
('Rice Crispies', 'topping', 10.00, 1),
('Mallows', 'topping', 10.00, 2),
('Honey Graham', 'topping', 10.00, 3),
('Choco Sticks', 'topping', 10.00, 4),
('Chocolate Sprinkles', 'topping', 15.00, 5),
('Rainbow Sprinkles', 'topping', 15.00, 6),
('Koko Crunch', 'topping', 15.00, 7),
('Nips', 'topping', 15.00, 8),
('Biscoff Cookie', 'topping', 15.00, 9),
('Crushed Oreos', 'topping', 15.00, 10),
('Mixed Cereals', 'topping', 15.00, 11),
('Choco Kisses', 'topping', 15.00, 12),
('Kitkat', 'topping', 20.00, 13),
('Almond Slices', 'topping', 20.00, 14),
('Brownies', 'topping', 20.00, 15),
('Biscoff Crumbs', 'topping', 25.00, 16);

-- SAUCES (9 items)
INSERT INTO addons (addon_name, addon_type, price, display_order) VALUES
('Chocolate', 'sauce', 20.00, 17),
('Caramel', 'sauce', 20.00, 18),
('White Chocolate', 'sauce', 20.00, 19),
('Ube', 'sauce', 20.00, 20),
('Tiger Sugar', 'sauce', 20.00, 21),
('Honey', 'sauce', 20.00, 22),
('Strawberry', 'sauce', 30.00, 23),
('Blueberry', 'sauce', 30.00, 24),
('Biscoff', 'sauce', 35.00, 25);

-- FRUITS (4 items)
INSERT INTO addons (addon_name, addon_type, price, display_order) VALUES
('Banana', 'fruit', 15.00, 26),
('Mango', 'fruit', 20.00, 27),
('Grapes', 'fruit', 20.00, 28),
('Watermelon', 'fruit', 20.00, 29);

-- =====================================================
-- SUMMARY & VERIFICATION
-- =====================================================

SELECT '✓ Simplified database schema created successfully!' AS Status;
SELECT '✓ Removed: categories, extras, flavors, fruits, item_sizes, item_size_prices, menu_items, sauces, time_logs, toppings, and transaction_item_* tables' AS Status;
SELECT '✓ Kept: branches, customers, transactions, order_channels, payment_methods, transaction_items, users' AS Status;
SELECT '✓ Added: items (simplified menu), addons (toppings/sauces/fruits combined)' AS Status;

-- Display menu summary by category
SELECT 
    category AS Category,
    COUNT(*) AS 'Item Count'
FROM items
WHERE is_active = TRUE
GROUP BY category
ORDER BY MIN(display_order);

-- Display add-ons summary
SELECT 
    addon_type AS 'Add-on Type',
    COUNT(*) AS Count,
    CONCAT('₱', MIN(price), ' - ₱', MAX(price)) AS 'Price Range'
FROM addons
WHERE is_active = TRUE
GROUP BY addon_type;

-- Display total items
SELECT 
    'Total Menu Items' AS Metric,
    COUNT(*) AS Count
FROM items
WHERE is_active = TRUE;
