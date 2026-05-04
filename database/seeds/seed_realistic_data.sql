-- =====================================================
-- CREMOSO POS - REALISTIC SEED DATA (20 TRANSACTIONS)
-- All transactions set to 'completed' status for testing
-- =====================================================
USE cremoso_db;

-- Cleanup existing data
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM transaction_items;
DELETE FROM transactions;
DELETE FROM customers;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE transaction_items AUTO_INCREMENT = 1;
ALTER TABLE transactions AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;

-- =====================================================
-- CUSTOMERS (25 realistic customers)
-- =====================================================
INSERT INTO customers (customer_name) VALUES
('Walk-in Customer'),
('Juan Dela Cruz'),
('Maria Santos'),
('Pedro Reyes'),
('Ana Garcia'),
('Carlos Mendoza'),
('Rosa Villanueva'),
('Jose Torres'),
('Luz Fernandez'),
('Miguel Castro'),
('Elena Ramos'),
('Andres Bautista'),
('Sofia Cruz'),
('Rafael Aquino'),
('Carmen Lopez'),
('Diego Morales'),
('Isabel Reyes'),
('Francisco Santos'),
('Gabriela Diaz'),
('Ricardo Cruz'),
('Teresa Ramos'),
('Antonio Flores'),
('Beatriz Gonzales'),
('Fernando Silva'),
('Cristina Navarro');

-- =====================================================
-- TRANSACTIONS (20 completed orders)
-- Mix of different times, branches, channels, and payment methods
-- =====================================================
INSERT INTO transactions (order_number, customer_id, user_id, branch_id, channel_id, payment_method_id, account_name, transaction_date, total_amount, status, notes) VALUES
-- Morning rush (8 AM - 11 AM)
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0001'), 2, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 08:15:00'), 75.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0002'), 5, 2, 1, 2, 2, 'Ana G.', CONCAT(CURDATE(), ' 08:45:00'), 158.00, 'completed', 'Extra chocolate sauce'),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0003'), 1, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 09:20:00'), 49.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0004'), 8, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 10:00:00'), 234.00, 'completed', 'Birthday party order'),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0005'), 12, 3, 2, 2, 2, 'Sofia C.', CONCAT(CURDATE(), ' 10:45:00'), 118.00, 'completed', NULL),

-- Lunch time (12 PM - 2 PM)
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0006'), 15, 2, 1, 3, 3, NULL, CONCAT(CURDATE(), ' 12:15:00'), 198.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0007'), 18, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 12:40:00'), 139.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0008'), 3, 2, 1, 2, 2, 'Maria S.', CONCAT(CURDATE(), ' 13:10:00'), 289.00, 'completed', 'Yogurt deluxe with extra fruits'),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0009'), 1, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 13:45:00'), 64.00, 'completed', NULL),

-- Afternoon (3 PM - 5 PM)
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0010'), 20, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 15:00:00'), 178.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0011'), 7, 3, 2, 2, 2, 'Rosa V.', CONCAT(CURDATE(), ' 15:30:00'), 99.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0012'), 22, 2, 1, 3, 3, NULL, CONCAT(CURDATE(), ' 16:00:00'), 267.00, 'completed', 'Family order'),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0013'), 10, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 16:45:00'), 89.00, 'completed', NULL),

-- Evening rush (6 PM - 9 PM)
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0014'), 14, 2, 1, 2, 2, 'Rafael A.', CONCAT(CURDATE(), ' 18:15:00'), 198.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0015'), 1, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 18:50:00'), 125.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0016'), 25, 2, 1, 2, 2, 'Cristina N.', CONCAT(CURDATE(), ' 19:20:00'), 344.00, 'completed', 'Large group order'),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0017'), 17, 3, 2, 1, 1, NULL, CONCAT(CURDATE(), ' 19:45:00'), 158.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0018'), 4, 2, 1, 3, 3, NULL, CONCAT(CURDATE(), ' 20:10:00'), 213.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0019'), 11, 3, 2, 2, 2, 'Elena R.', CONCAT(CURDATE(), ' 20:40:00'), 178.00, 'completed', NULL),
(CONCAT('ORD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-0020'), 1, 2, 1, 1, 1, NULL, CONCAT(CURDATE(), ' 21:00:00'), 98.00, 'completed', 'Last order of the day');

-- =====================================================
-- TRANSACTION ITEMS (Realistic order combinations)
-- =====================================================

-- Transaction 1: Simple soft-serve order
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(1, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 3, NULL, 0.00, 75.00, NULL);

-- Transaction 2: Parfait with toppings
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(2, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 1, '[{"addon_id":10,"addon_name":"Crushed Oreos","price":15.00},{"addon_id":17,"addon_name":"Chocolate","price":20.00}]', 35.00, 114.00, NULL),
(2, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, '[{"addon_id":5,"addon_name":"Chocolate Sprinkles","price":15.00}]', 15.00, 44.00, NULL);

-- Transaction 3: Single chocolate soft-serve
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(3, 6, 'Soft-serve - Chocolate - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 49.00, 1, NULL, 0.00, 49.00, NULL);

-- Transaction 4: Birthday party - multiple items
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(4, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 5, NULL, 0.00, 125.00, 'Kids party'),
(4, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 3, NULL, 0.00, 105.00, NULL);

-- Transaction 5: Cremdae order
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(5, 7, 'Cremdae - Chocolate - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 2, NULL, 0.00, 118.00, NULL);

-- Transaction 6: Float and Parfait combo
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(6, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL),
(6, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 1, '[{"addon_id":12,"addon_name":"Choco Kisses","price":15.00}]', 15.00, 104.00, NULL);

-- Transaction 7: Tiger Creme Parfait with extras
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(7, 12, 'Parfait - Tiger Creme - Grande', 'Parfait', 'Grande', 79.00, 1, '[{"addon_id":21,"addon_name":"Tiger Sugar","price":20.00},{"addon_id":7,"addon_name":"Koko Crunch","price":15.00},{"addon_id":27,"addon_name":"Mango","price":20.00}]', 55.00, 134.00, NULL);

-- Transaction 8: Premium Yogurt Deluxe
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(8, 22, 'Yogurt Deluxe - Grande (12oz)', 'Yogurt', 'Grande (12oz)', 189.00, 1, '[{"addon_id":26,"addon_name":"Banana","price":15.00},{"addon_id":27,"addon_name":"Mango","price":20.00},{"addon_id":14,"addon_name":"Almond Slices","price":20.00},{"addon_id":16,"addon_name":"Biscoff Crumbs","price":25.00},{"addon_id":22,"addon_name":"Honey","price":20.00}]', 100.00, 289.00, NULL);

-- Transaction 9: Mixed soft-serve
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(9, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, NULL, 0.00, 29.00, NULL),
(9, 5, 'Soft-serve - Chocolate - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 39.00, 1, NULL, 0.00, 39.00, NULL);

-- Transaction 10: Frozen Yogurt and Float
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(10, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, NULL, 0.00, 89.00, NULL),
(10, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL);

-- Transaction 11: Single Float
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(11, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL);

-- Transaction 12: Family order - variety pack
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(12, 3, 'Soft-serve - Vanilla - Grande (12oz)', 'Soft-serve', 'Grande (12oz)', 39.00, 2, NULL, 0.00, 78.00, NULL),
(12, 8, 'Cremdae - Caramel - Grande (12oz)', 'Cremdae', 'Grande (12oz)', 59.00, 1, NULL, 0.00, 59.00, NULL),
(12, 13, 'Parfait - Chocky Road - Moyen', 'Parfait', 'Moyen', 79.00, 1, '[{"addon_id":13,"addon_name":"Kitkat","price":20.00},{"addon_id":18,"addon_name":"Caramel","price":20.00}]', 40.00, 119.00, NULL);

-- Transaction 13: Frozen Yogurt
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(13, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, NULL, 0.00, 89.00, NULL);

-- Transaction 14: Floats for two
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(14, 18, 'Float - Coffee Latte - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL),
(14, 19, 'Float - Caramel Macchiato - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL);

-- Transaction 15: Parfait combo
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(15, 9, 'Parfait - Oreo Creme - Moyen', 'Parfait', 'Moyen', 69.00, 1, '[{"addon_id":10,"addon_name":"Crushed Oreos","price":15.00}]', 15.00, 84.00, NULL),
(15, 11, 'Parfait - Tiger Creme - Moyen', 'Parfait', 'Moyen', 69.00, 1, NULL, 0.00, 69.00, NULL);

-- Transaction 16: Large group order
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(16, 1, 'Soft-serve - Vanilla - Cone', 'Soft-serve', 'Cone', 25.00, 4, NULL, 0.00, 100.00, NULL),
(16, 4, 'Soft-serve - Chocolate - Cone', 'Soft-serve', 'Cone', 35.00, 2, NULL, 0.00, 70.00, NULL),
(16, 10, 'Parfait - Oreo Creme - Grande', 'Parfait', 'Grande', 79.00, 2, '[{"addon_id":10,"addon_name":"Crushed Oreos","price":15.00},{"addon_id":17,"addon_name":"Chocolate","price":20.00}]', 35.00, 228.00, NULL);

-- Transaction 17: Cremango Royale (premium)
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(17, 15, 'Parfait - Cremango Royale - Grande', 'Parfait', 'Grande', 99.00, 1, '[{"addon_id":27,"addon_name":"Mango","price":20.00},{"addon_id":23,"addon_name":"Strawberry","price":30.00}]', 50.00, 149.00, NULL);

-- Transaction 18: Mixed order
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(18, 17, 'Frozen Yogurt - Grande (12oz)', 'Frozen Yogurt', 'Grande (12oz)', 99.00, 1, NULL, 0.00, 99.00, NULL),
(18, 14, 'Parfait - Chocky Road - Grande', 'Parfait', 'Grande', 89.00, 1, '[{"addon_id":15,"addon_name":"Brownies","price":20.00}]', 20.00, 109.00, NULL);

-- Transaction 19: Float and Frozen Yogurt
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(19, 20, 'Float - Chocolate Float - 16oz', 'Float', '16oz', 99.00, 1, NULL, 0.00, 99.00, NULL),
(19, 16, 'Frozen Yogurt - Moyen (8oz)', 'Frozen Yogurt', 'Moyen (8oz)', 89.00, 1, NULL, 0.00, 89.00, NULL);

-- Transaction 20: End of day order
INSERT INTO transaction_items (transaction_id, item_id, item_name, category, size, base_price, quantity, addons_detail, addons_total, subtotal, notes) VALUES
(20, 21, 'Float - Fizzy Float - 16oz', 'Float', '16oz', 69.00, 1, NULL, 0.00, 69.00, NULL),
(20, 2, 'Soft-serve - Vanilla - Moyen (8oz)', 'Soft-serve', 'Moyen (8oz)', 29.00, 1, NULL, 0.00, 29.00, NULL);

-- =====================================================
-- VERIFICATION & SUMMARY
-- =====================================================

SELECT '✅ Realistic seed data loaded successfully!' AS '';
SELECT CONCAT('📊 ', COUNT(*), ' completed transactions created') AS '' FROM transactions WHERE status = 'completed';
SELECT CONCAT('🛒 ', COUNT(*), ' transaction items created') AS '' FROM transaction_items;
SELECT CONCAT('👥 ', COUNT(*), ' customers added') AS '' FROM customers;
SELECT CONCAT('💰 Total Revenue: ₱', FORMAT(SUM(total_amount), 2)) AS '' FROM transactions WHERE status = 'completed';

-- Show transaction summary by time of day
SELECT 
    CASE 
        WHEN HOUR(transaction_date) BETWEEN 8 AND 11 THEN 'Morning (8-11 AM)'
        WHEN HOUR(transaction_date) BETWEEN 12 AND 14 THEN 'Lunch (12-2 PM)'
        WHEN HOUR(transaction_date) BETWEEN 15 AND 17 THEN 'Afternoon (3-5 PM)'
        WHEN HOUR(transaction_date) BETWEEN 18 AND 21 THEN 'Evening (6-9 PM)'
        ELSE 'Other'
    END AS 'Time Period',
    COUNT(*) AS 'Orders',
    CONCAT('₱', FORMAT(SUM(total_amount), 2)) AS 'Revenue'
FROM transactions
WHERE status = 'completed'
GROUP BY 
    CASE 
        WHEN HOUR(transaction_date) BETWEEN 8 AND 11 THEN 'Morning (8-11 AM)'
        WHEN HOUR(transaction_date) BETWEEN 12 AND 14 THEN 'Lunch (12-2 PM)'
        WHEN HOUR(transaction_date) BETWEEN 15 AND 17 THEN 'Afternoon (3-5 PM)'
        WHEN HOUR(transaction_date) BETWEEN 18 AND 21 THEN 'Evening (6-9 PM)'
        ELSE 'Other'
    END
ORDER BY MIN(HOUR(transaction_date));

-- Show popular items
SELECT 
    ti.item_name,
    ti.category,
    SUM(ti.quantity) AS 'Total Sold',
    CONCAT('₱', FORMAT(SUM(ti.subtotal), 2)) AS 'Revenue'
FROM transaction_items ti
JOIN transactions t ON ti.transaction_id = t.transaction_id
WHERE t.status = 'completed'
GROUP BY ti.item_name, ti.category
ORDER BY SUM(ti.quantity) DESC
LIMIT 10;
